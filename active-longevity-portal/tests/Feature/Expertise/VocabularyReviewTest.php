<?php

namespace Tests\Feature\Expertise;

use App\Enums\CollaboratorStatus;
use App\Mail\VocabularyDecidedMail;
use App\Models\Collaborator;
use App\Models\Discipline;
use App\Models\ExpertiseArea;
use App\Models\Profile;
use App\Models\Role;
use App\Models\User;
use App\Models\VocabularyProposal;
use App\Services\Expertise\CredentialLibrary;
use App\Services\Expertise\ProfileClassification;
use App\Services\Expertise\VocabularyReview;
use Database\Seeders\CredentialSeeder;
use Database\Seeders\ExpertiseVocabularySeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;
use Tests\TestCase;

/**
 * The Institute deciding what joins its vocabulary, and what each decision
 * does to the profile of the person who asked.
 */
class VocabularyReviewTest extends TestCase
{
    use RefreshDatabase;

    private VocabularyReview $review;

    private User $founder;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->seed(ExpertiseVocabularySeeder::class);
        $this->seed(CredentialSeeder::class);
        $this->seed(RolePermissionSeeder::class);
        Role::query()->firstOrCreate(['name' => 'collaborator'], ['requires_mfa' => false]);
        $this->review = app(VocabularyReview::class);
        $this->founder = User::factory()->create();
    }

    /** @return array{0: User, 1: Profile} */
    private function collaboratorWithProfile(): array
    {
        $user = User::factory()->create();
        Collaborator::factory()->create([
            'user_id' => $user->id, 'status' => CollaboratorStatus::Active, 'directory_visible' => true,
        ]);

        return [$user, $user->collaborator->profile()->firstOrCreate([])];
    }

    public function test_approving_a_field_creates_it_and_puts_it_on_the_proposer(): void
    {
        [$user, $profile] = $this->collaboratorWithProfile();
        $proposal = app(ProfileClassification::class)->proposeTerm($profile, $user, 'field', 'Longevity Economics');

        $this->review->approve($proposal, $this->founder);

        $this->assertDatabaseHas('disciplines', ['name' => 'Longevity Economics']);
        $this->assertContains('Longevity Economics', $profile->fields()->pluck('name')->all());
        $this->assertSame(VocabularyProposal::STATUS_APPROVED, $proposal->fresh()->status);
    }

    /**
     * The Institute's wording wins, and because people select entries rather
     * than typing text it is what every profile shows, including the proposer's.
     */
    public function test_approving_under_standardised_wording_shows_the_institute_wording(): void
    {
        [$user, $profile] = $this->collaboratorWithProfile();
        $proposal = app(ProfileClassification::class)->proposeTerm($profile, $user, 'specialty', 'sarcopenia research');

        $fieldId = Discipline::query()->where('name', 'Aging Biology')->value('id');
        $this->review->approve($proposal, $this->founder, 'Sarcopenia', null, [$fieldId]);

        $this->assertDatabaseMissing('expertise_areas', ['name' => 'sarcopenia research']);
        $this->assertDatabaseHas('expertise_areas', ['name' => 'Sarcopenia']);
        $this->assertContains('Sarcopenia', $profile->specialties()->pluck('name')->all());

        // Linked to the Field for search, and the Field is not assigned.
        $specialty = ExpertiseArea::query()->where('name', 'Sarcopenia')->firstOrFail();
        $this->assertContains('Aging Biology', $specialty->disciplines->pluck('name')->all());
        $this->assertNotContains('Aging Biology', $profile->fields()->pluck('name')->all());
    }

    public function test_mapping_attaches_the_existing_term_instead_of_creating_one(): void
    {
        [$user, $profile] = $this->collaboratorWithProfile();
        $proposal = app(ProfileClassification::class)->proposeTerm($profile, $user, 'field', 'Actuarial Studies');
        $existing = Discipline::query()->where('name', 'Actuarial Science')->firstOrFail();

        $this->review->mapTo($proposal, $this->founder, $existing);

        $this->assertDatabaseMissing('disciplines', ['name' => 'Actuarial Studies']);
        $this->assertContains('Actuarial Science', $profile->fields()->pluck('name')->all());
        $this->assertSame(VocabularyProposal::STATUS_MAPPED, $proposal->fresh()->status);
    }

    public function test_approving_a_credential_places_it_after_the_name(): void
    {
        [$user, $profile] = $this->collaboratorWithProfile();
        $proposal = app(CredentialLibrary::class)->propose($profile, $user, 'FGSA', 'Fellow of the Gerontological Society of Australia', 'Australia');

        $this->review->approve($proposal, $this->founder);

        $this->assertDatabaseHas('credentials', ['acronym' => 'FGSA', 'jurisdiction' => 'Australia', 'approved' => true]);
        $this->assertContains('FGSA', $profile->approvedCredentials()->pluck('acronym')->all());
    }

    /** The person will be wondering. Approval and mapping say so; a decline says nothing by itself. */
    public function test_the_proposer_is_told_when_it_goes_their_way_and_not_on_a_decline(): void
    {
        [$user, $profile] = $this->collaboratorWithProfile();

        $approved = app(ProfileClassification::class)->proposeTerm($profile, $user, 'field', 'Term A');
        $this->review->approve($approved, $this->founder);
        Mail::assertQueued(VocabularyDecidedMail::class, fn (VocabularyDecidedMail $m): bool => $m->hasTo($user->email));

        $declined = app(ProfileClassification::class)->proposeTerm($profile, $user, 'field', 'Term B');
        $this->review->decline($declined, $this->founder, 'Too narrow.');
        Mail::assertQueued(VocabularyDecidedMail::class, 1);
    }

    public function test_a_decline_leaves_the_vocabulary_untouched(): void
    {
        [$user, $profile] = $this->collaboratorWithProfile();
        $proposal = app(ProfileClassification::class)->proposeTerm($profile, $user, 'field', 'Not A Field');

        $this->review->decline($proposal, $this->founder);

        $this->assertDatabaseMissing('disciplines', ['name' => 'Not A Field']);
        $this->assertCount(0, $profile->fields()->get());
        $this->assertSame(VocabularyProposal::STATUS_DECLINED, $proposal->fresh()->status);
    }

    /** A decision is final and is not quietly re-made. */
    public function test_a_settled_proposal_cannot_be_decided_again(): void
    {
        [$user, $profile] = $this->collaboratorWithProfile();
        $proposal = app(ProfileClassification::class)->proposeTerm($profile, $user, 'field', 'Once Only');
        $this->review->decline($proposal, $this->founder);

        $this->expectException(InvalidArgumentException::class);
        $this->review->approve($proposal->fresh(), $this->founder);
    }

    public function test_the_queue_is_for_administrators_only(): void
    {
        [$user] = $this->collaboratorWithProfile();

        $this->actingAs($user)->get(route('admin.vocabulary.index'))->assertForbidden();
    }
}
