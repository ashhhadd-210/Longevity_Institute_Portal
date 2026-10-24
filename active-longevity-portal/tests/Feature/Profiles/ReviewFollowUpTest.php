<?php

namespace Tests\Feature\Profiles;

use App\Enums\CollaboratorStatus;
use App\Events\CollaboratorAdmitted;
use App\Listeners\NotifyInstituteOfRegistration;
use App\Mail\CollaboratorRegisteredMail;
use App\Mail\VocabularyDeclinedMail;
use App\Models\Collaborator;
use App\Models\Discipline;
use App\Models\Role;
use App\Models\User;
use App\Services\Expertise\ProfileClassification;
use App\Services\Expertise\VocabularyReview;
use App\Services\Profiles\ProfileEditor;
use App\Support\Countries;
use Database\Seeders\ExpertiseVocabularySeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * The Institute's review of the reconciled Milestone 3 (October 2026).
 */
class ReviewFollowUpTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ExpertiseVocabularySeeder::class);
        $this->seed(RolePermissionSeeder::class);
        Role::query()->firstOrCreate(['name' => 'collaborator'], ['requires_mfa' => false]);
    }

    private function collaborator(string $first = 'Peter', string $last = 'Smith'): User
    {
        $user = User::factory()->create([
            'first_name' => $first, 'middle_name' => null, 'last_name' => $last,
            'name_suffix' => null, 'preferred_name' => null,
        ]);
        Collaborator::factory()->create(['user_id' => $user->id, 'status' => CollaboratorStatus::Active, 'directory_visible' => true]);

        return $user;
    }

    /** "P. Thomas Smith", who goes by Thomas. */
    public function test_a_collaborator_can_set_an_initial_based_name_and_the_name_they_go_by(): void
    {
        $user = $this->collaborator();

        $this->actingAs($user)->put(route('profile.name'), [
            'first_name' => 'P.', 'middle_name' => 'Thomas', 'last_name' => 'Smith', 'preferred_name' => 'Thomas',
        ])->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertSame('P. Thomas Smith', $user->fullName());
        $this->assertSame('Thomas', $user->preferred_name);
    }

    /** The profile says exactly what is still needed, and links to it. */
    public function test_the_profile_lists_what_is_still_needed_for_readiness(): void
    {
        $user = $this->collaborator();
        $profile = $user->collaborator->profile()->firstOrCreate([]);

        $labels = collect(app(ProfileEditor::class)->missingForReadiness($profile))->pluck('label')->all();
        $this->assertSame(['A photograph', 'Professional Identity', 'About Your Work', 'Country', 'At least one Field'], $labels);

        $this->actingAs($user)->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Not yet ready for the Directory')
            ->assertSee('href="#photograph"', false)
            ->assertSee('href="#fields"', false);
    }

    public function test_countries_are_shown_by_their_full_name(): void
    {
        $this->assertSame('United Kingdom', Countries::name('GB'));
        $this->assertSame('United States', Countries::name('us'));

        $viewer = $this->collaborator('Kestutis', 'Jankauskas');
        $user = $this->collaborator('Amara', 'Nwosu');
        $profile = $user->collaborator->profile()->firstOrCreate([]);
        $profile->forceFill(['photo_path' => 'photos/test.jpg'])->save();
        app(ProfileEditor::class)->saveDetails($profile, [
            'professional_title' => 'Professor', 'one_line_summary' => 'Summary.', 'country_code' => 'GB',
        ]);
        app(ProfileClassification::class)->setFields($profile->fresh(), [Discipline::query()->where('name', 'Medicine')->value('id')]);

        $this->actingAs($viewer)->get(route('directory.index'))->assertOk()->assertSee('United Kingdom');
        $this->actingAs($viewer)->get(route('profile.show', $profile))->assertOk()->assertSee('United Kingdom');
    }

    public function test_declining_a_term_tells_the_collaborator_with_the_note(): void
    {
        Mail::fake();
        $user = $this->collaborator();
        $profile = $user->collaborator->profile()->firstOrCreate([]);
        $proposal = app(ProfileClassification::class)->proposeTerm($profile, $user, 'field', 'Retirement Studies');

        app(VocabularyReview::class)->decline($proposal, User::factory()->create(), 'Please consider Gerontology.');

        Mail::assertQueued(VocabularyDeclinedMail::class, fn ($mail) => $mail->hasTo($user->email));
    }

    public function test_the_founder_is_emailed_when_an_invited_person_registers(): void
    {
        Mail::fake();
        $founder = User::factory()->create(['email' => 'founder@example.org']);
        $founder->roles()->attach(Role::query()->where('name', 'founder')->value('id'), ['assigned_at' => now()]);
        $newcomer = $this->collaborator('Ingrid', 'Halvorsen');

        app(NotifyInstituteOfRegistration::class)->handle(new CollaboratorAdmitted($newcomer->collaborator));

        Mail::assertSent(CollaboratorRegisteredMail::class, fn ($mail) => $mail->hasTo('founder@example.org'));
    }

    /** The Institute's full logo, never the compass alone, on signed-in and signed-out pages. */
    public function test_every_page_carries_the_full_institute_logo(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('images/longevity-compass-logo.svg', false)->assertDontSee('ali-logo.svg', false);

        $this->actingAs($this->collaborator())->get(route('home'))
            ->assertOk()->assertSee('images/longevity-compass-logo.svg', false)->assertSee('Collaborator Portal');
    }
}
