<?php

namespace Tests\Feature\Expertise;

use App\Enums\CollaboratorStatus;
use App\Models\Collaborator;
use App\Models\Discipline;
use App\Models\ExpertiseAlias;
use App\Models\ExpertiseArea;
use App\Models\Role;
use App\Models\User;
use App\Services\Expertise\ExpertiseSearch;
use App\Services\Expertise\ProfileClassification;
use App\Services\Expertise\TaxonomyAdmin;
use App\Support\ExpertiseMatch;
use Database\Seeders\ExpertiseVocabularySeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The Institute maintaining its own vocabulary, and the two promises that
 * make that safe: renaming changes every profile at once, and retiring never
 * removes a term from anybody who already holds it.
 */
class TaxonomyAdminTest extends TestCase
{
    use RefreshDatabase;

    private TaxonomyAdmin $admin;

    private User $founder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ExpertiseVocabularySeeder::class);
        $this->seed(RolePermissionSeeder::class);
        Role::query()->firstOrCreate(['name' => 'collaborator'], ['requires_mfa' => false]);
        $this->admin = app(TaxonomyAdmin::class);
        $this->founder = User::factory()->create();
    }

    public function test_renaming_a_field_changes_every_profile_that_uses_it_at_once(): void
    {
        $user = User::factory()->create();
        Collaborator::factory()->create(['user_id' => $user->id, 'status' => CollaboratorStatus::Active, 'directory_visible' => true]);
        $profile = $user->collaborator->profile()->firstOrCreate([]);

        $field = Discipline::query()->where('name', 'Aging Biology')->firstOrFail();
        app(ProfileClassification::class)->setFields($profile, [$field->id]);

        $this->admin->updateField($this->founder, $field, 'Biology of Ageing', 'How and why organisms age.', true);

        $this->assertSame(['Biology of Ageing'], $profile->fields()->pluck('name')->all());
        // Found under the new wording, and the slug the release seeder matches on is untouched.
        $this->assertTrue(app(ExpertiseSearch::class)->searchFields('Biology of Ageing')->contains(fn (ExpertiseMatch $m): bool => $m->id === $field->id));
        $this->assertSame('aging-biology', $field->fresh()->slug);
    }

    /** Which is exactly why the release seeder must not put the old name back. */
    public function test_a_release_does_not_undo_the_institutes_rename(): void
    {
        $field = Discipline::query()->where('name', 'Aging Biology')->firstOrFail();
        $this->admin->updateField($this->founder, $field, 'Biology of Ageing', null, true);

        $this->seed(ExpertiseVocabularySeeder::class);

        $this->assertSame('Biology of Ageing', $field->fresh()->name);
        $this->assertDatabaseCount('disciplines', 67);
    }

    public function test_retiring_a_term_stops_offering_it_but_leaves_it_on_existing_profiles(): void
    {
        $user = User::factory()->create();
        Collaborator::factory()->create(['user_id' => $user->id, 'status' => CollaboratorStatus::Active, 'directory_visible' => true]);
        $profile = $user->collaborator->profile()->firstOrCreate([]);

        $specialty = ExpertiseArea::query()->where('name', 'Survival Analysis')->firstOrFail();
        app(ProfileClassification::class)->setSpecialties($profile, [$specialty->id]);

        $this->admin->updateSpecialty($this->founder, $specialty, $specialty->name, false, $specialty->disciplines->pluck('id')->all());

        $this->assertContains('Survival Analysis', $profile->specialties()->pluck('name')->all(), 'Retiring must not rewrite anybody\'s history.');
        $this->assertFalse(app(ExpertiseSearch::class)->search('Survival Analysis')->contains(fn (ExpertiseMatch $m): bool => $m->id === $specialty->id && ! $m->isField()));
    }

    public function test_the_institute_can_add_a_field_a_specialty_and_an_alias_and_they_are_found(): void
    {
        $field = $this->admin->createField($this->founder, 'Longevity Finance', 'Capital markets built around longer lives.');
        $specialty = $this->admin->createSpecialty($this->founder, 'Longevity Bonds', [$field->id]);
        $this->admin->createAlias($this->founder, 'Longevity-linked securities', $specialty);

        $search = app(ExpertiseSearch::class);
        $this->assertTrue($search->searchFields('Longevity Finance')->contains(fn (ExpertiseMatch $m): bool => $m->name === 'Longevity Finance'));
        $this->assertSame('Longevity Bonds', $search->search('longevity linked securities')->first()?->name);
        $this->assertSame('Capital markets built around longer lives.', $field->descriptor);
    }

    /**
     * Two Fields both called Medicine would defeat a controlled vocabulary. The
     * refusal is in plain words, and it is case-blind so that "medicine" is
     * caught too.
     */
    public function test_a_field_with_a_name_already_in_use_is_refused_in_plain_words(): void
    {
        try {
            $this->admin->createField($this->founder, 'medicine');
            $this->fail('A second Medicine should have been refused.');
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString('already exists', $e->getMessage());
        }

        $this->assertSame(1, Discipline::query()->where('name', 'Medicine')->count());

        // Renaming onto another Field's name is refused too; renaming to itself is not.
        $sociology = Discipline::query()->where('name', 'Sociology')->firstOrFail();
        try {
            $this->admin->updateField($this->founder, $sociology, 'Medicine', null, true);
            $this->fail('Renaming onto an existing Field should have been refused.');
        } catch (\InvalidArgumentException) {
        }
        $this->admin->updateField($this->founder, $sociology, 'Sociology', 'The study of society.', true);
        $this->assertSame('The study of society.', $sociology->fresh()->descriptor);
    }

    public function test_the_registers_screen_saves_a_descriptor_line(): void
    {
        $founderRole = Role::query()->where('name', 'founder')->firstOrFail();
        $this->founder->roleAssignments()->create(['role_id' => $founderRole->id, 'assigned_at' => now()]);
        $this->founder->forceFill(['two_factor_confirmed_at' => now()])->save();

        $field = Discipline::query()->where('name', 'Social Care')->firstOrFail();

        $this->actingAs($this->founder)
            ->put(route('admin.taxonomy.fields.update', $field), [
                'name' => 'Social Care',
                'descriptor' => 'The sector and its services, rather than the profession.',
                'active' => 1,
                'sort_order' => $field->sort_order,
            ])
            ->assertRedirect(route('admin.taxonomy.index'));

        $this->assertSame('The sector and its services, rather than the profession.', $field->fresh()->descriptor);
    }

    public function test_a_retired_alias_no_longer_leads_anywhere(): void
    {
        $alias = ExpertiseAlias::query()->where('term', 'Physiotherapy')->firstOrFail();

        $this->admin->retireAlias($this->founder, $alias);

        $this->assertFalse(app(ExpertiseSearch::class)->search('Physiotherapy')->contains(fn (ExpertiseMatch $m): bool => $m->matchedVia === 'Physiotherapy'));
    }
}
