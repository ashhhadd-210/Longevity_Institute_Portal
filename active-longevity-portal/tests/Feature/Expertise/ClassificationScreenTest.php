<?php

namespace Tests\Feature\Expertise;

use App\Models\Collaborator;
use App\Models\Discipline;
use App\Models\ExpertiseArea;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\ExpertiseVocabularySeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The screen where a collaborator says where their work belongs.
 */
class ClassificationScreenTest extends TestCase
{
    use RefreshDatabase;

    private function collaborator(): User
    {
        $this->seed(RolePermissionSeeder::class);
        Role::query()->firstOrCreate(['name' => 'collaborator'], ['requires_mfa' => false]);

        $user = User::factory()->create();
        Collaborator::factory()->create(['user_id' => $user->id]);

        return $user;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ExpertiseVocabularySeeder::class);
    }

    public function test_the_vocabulary_is_not_exposed_to_the_open_web(): void
    {
        $this->getJson(route('expertise.search', ['q' => 'medicine']))
            ->assertUnauthorized();
    }

    public function test_a_collaborator_can_search_and_every_result_is_labelled(): void
    {
        $response = $this->actingAs($this->collaborator())
            ->getJson(route('expertise.search', ['q' => 'Architecture']))
            ->assertOk();

        $results = $response->json('results');

        $this->assertNotEmpty($results);

        foreach ($results as $result) {
            $this->assertContains($result['label'], ['Field', 'Specialty']);
        }
    }

    public function test_searching_by_a_familiar_word_explains_where_it_led(): void
    {
        $result = $this->actingAs($this->collaborator())
            ->getJson(route('expertise.search', ['q' => 'Physiotherapy']))
            ->assertOk()
            ->json('results.0');

        $this->assertSame('Physical Therapy', $result['name']);
        $this->assertSame('also known as Physiotherapy', $result['explanation']);
    }

    public function test_the_orientation_appears_the_first_time_and_then_stops(): void
    {
        $user = $this->collaborator();

        $this->actingAs($user)
            ->get(route('profile.classification.edit'))
            ->assertOk()
            ->assertSee('Classify lightly. Describe richly.');

        $this->actingAs($user)
            ->put(route('profile.classification.update'), [
                'field_ids' => [Discipline::query()->where('name', 'Medicine')->value('id')],
            ])
            ->assertRedirect(route('profile.classification.edit'));

        $this->assertNotNull($user->fresh()?->classification_oriented_at);
    }

    public function test_saving_records_fields_without_touching_specialties(): void
    {
        $user = $this->collaborator();
        $journalism = Discipline::query()->where('name', 'Journalism')->value('id');
        $socialPolicy = ExpertiseArea::query()->where('name', 'Social Policy')->value('id');

        $this->actingAs($user)->put(route('profile.classification.update'), [
            'field_ids' => [$journalism],
            'specialty_ids' => [$socialPolicy],
        ])->assertRedirect();

        $profile = $user->fresh()?->collaborator?->profile;

        $this->assertNotNull($profile);
        $this->assertSame(['Journalism'], $profile->fields()->pluck('name')->all());
        $this->assertSame(['Social Policy'], $profile->specialties()->pluck('name')->all());
    }

    /**
     * The promise that matters most on this screen: nobody is held up waiting
     * for the Institute.
     */
    public function test_a_missing_term_can_be_sent_without_interrupting_anything(): void
    {
        $user = $this->collaborator();

        $this->actingAs($user)->post(route('profile.classification.propose'), [
            'kind' => 'field',
            'term' => 'Longevity Economics',
        ])->assertRedirect(route('profile.classification.edit'));

        $this->assertDatabaseHas('vocabulary_proposals', [
            'term' => 'Longevity Economics',
            'status' => 'pending',
        ]);

        // And it has not quietly become part of anybody else's vocabulary.
        $this->assertDatabaseMissing('disciplines', ['name' => 'Longevity Economics']);

        $this->actingAs($user)
            ->get(route('profile.classification.edit'))
            ->assertSee('Awaiting review by the Institute');
    }
}
