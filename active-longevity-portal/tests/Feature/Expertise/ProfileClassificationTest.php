<?php

namespace Tests\Feature\Expertise;

use App\Models\Discipline;
use App\Models\ExpertiseArea;
use App\Models\Profile;
use App\Models\User;
use App\Models\VocabularyProposal;
use App\Services\Expertise\ProfileClassification;
use Database\Seeders\ExpertiseVocabularySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * How a collaborator says where they belong, and the promises that govern it.
 */
class ProfileClassificationTest extends TestCase
{
    use RefreshDatabase;

    private ProfileClassification $classification;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ExpertiseVocabularySeeder::class);
        $this->classification = app(ProfileClassification::class);
    }

    private function field(string $name): Discipline
    {
        return Discipline::query()->where('name', $name)->firstOrFail();
    }

    private function specialty(string $name): ExpertiseArea
    {
        return ExpertiseArea::query()->where('name', $name)->firstOrFail();
    }

    /**
     * The decision the Institute reversed, and the one that matters most. A
     * journalist who writes about health policy is a journalist.
     */
    public function test_choosing_a_specialty_never_assigns_a_field(): void
    {
        $profile = Profile::factory()->create();

        $this->classification->setFields($profile, [$this->field('Journalism')->id]);
        $this->classification->setSpecialties($profile, [$this->specialty('Social Policy')->id]);

        $claimed = $profile->fields()->pluck('name')->all();

        // Social Policy is recognisable within Political Science and Public
        // Policy. Neither may be attached to somebody who did not choose it.
        $this->assertSame(['Journalism'], $claimed);
        $this->assertNotContains('Political Science', $claimed);
        $this->assertNotContains('Public Policy', $claimed);
    }

    /**
     * But the search relationship still does its work, which is the whole
     * reason the links exist.
     */
    public function test_a_specialty_still_makes_a_person_findable_under_its_fields(): void
    {
        $profile = Profile::factory()->create();

        $this->classification->setFields($profile, [$this->field('Journalism')->id]);
        $this->classification->setSpecialties($profile, [$this->specialty('Nutritional Epidemiology')->id]);

        $reach = $this->classification->fieldReach($profile);

        $this->assertSame([$this->field('Journalism')->id], $reach['claimed']);
        $this->assertContains($this->field('Epidemiology')->id, $reach['searchable']);
        $this->assertContains($this->field('Nutrition Science')->id, $reach['searchable']);
    }

    /** Specialties are genuinely optional. */
    public function test_a_collaborator_may_have_a_field_and_no_specialty_at_all(): void
    {
        $profile = Profile::factory()->create();

        $this->classification->setFields($profile, [$this->field('Venture Capital')->id]);

        $this->assertCount(1, $profile->fields()->get());
        $this->assertCount(0, $profile->specialties()->get());
    }

    /** No maximum, and no primary. Some people genuinely bring several. */
    public function test_there_is_no_limit_on_how_many_fields_a_person_may_claim(): void
    {
        $profile = Profile::factory()->create();

        $ids = Discipline::query()->limit(9)->pluck('id')->all();
        $this->classification->setFields($profile, $ids);

        $this->assertCount(9, $profile->fields()->get());
    }

    public function test_the_order_a_collaborator_chose_is_kept(): void
    {
        $profile = Profile::factory()->create();

        $order = [
            $this->field('Sociology')->id,
            $this->field('Medicine')->id,
            $this->field('Economics')->id,
        ];

        $this->classification->setFields($profile, $order);

        $this->assertSame($order, $profile->fields()->pluck('disciplines.id')->all());
    }

    /**
     * Never a dead end. The profile saves and the person carries on while the
     * Institute considers the term at its leisure.
     */
    public function test_a_missing_term_is_recorded_without_blocking_anybody(): void
    {
        $profile = Profile::factory()->create();
        $user = User::factory()->create();

        $proposal = $this->classification->proposeTerm(
            $profile, $user, VocabularyProposal::KIND_FIELD, 'Longevity Economics'
        );

        $this->assertSame(VocabularyProposal::STATUS_PENDING, $proposal->status);
        $this->assertSame('Awaiting review by the Institute', $proposal->statusForCollaborator());

        // And it is nobody else's vocabulary until the Institute says so.
        $this->assertDatabaseMissing('disciplines', ['name' => 'Longevity Economics']);
    }

    public function test_proposing_the_same_term_twice_does_not_fill_the_queue(): void
    {
        $profile = Profile::factory()->create();
        $user = User::factory()->create();

        $first = $this->classification->proposeTerm($profile, $user, VocabularyProposal::KIND_SPECIALTY, 'Sarcopenia Research');
        $again = $this->classification->proposeTerm($profile, $user, VocabularyProposal::KIND_SPECIALTY, 'sarcopenia research');

        $this->assertSame($first->id, $again->id);
        $this->assertDatabaseCount('vocabulary_proposals', 1);
    }

    /** A term that is not in the Institute's vocabulary cannot be claimed. */
    public function test_an_unknown_field_is_quietly_ignored_rather_than_invented(): void
    {
        $profile = Profile::factory()->create();

        $this->classification->setFields($profile, [$this->field('Medicine')->id, 999999]);

        $this->assertSame(['Medicine'], $profile->fields()->pluck('name')->all());
    }
}
