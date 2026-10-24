<?php

namespace Tests\Feature\Profiles;

use App\Enums\CollaboratorStatus;
use App\Models\Collaborator;
use App\Models\Discipline;
use App\Models\Profile;
use App\Models\Role;
use App\Models\User;
use App\Services\Expertise\ProfileClassification;
use App\Services\Profiles\ProfileEditor;
use Database\Seeders\ExpertiseVocabularySeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The approved positions addition, and the two items that had no milestone
 * until they were placed in this one: collaboration interest and feedback.
 */
class PositionsInterestFeedbackTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ExpertiseVocabularySeeder::class);
        $this->seed(RolePermissionSeeder::class);
        Role::query()->firstOrCreate(['name' => 'collaborator'], ['requires_mfa' => false]);
    }

    private function collaborator(string $first, string $last): User
    {
        $user = User::factory()->create([
            'first_name' => $first, 'middle_name' => null, 'last_name' => $last,
            'name_suffix' => null, 'preferred_name' => null,
        ]);
        Collaborator::factory()->create([
            'user_id' => $user->id, 'status' => CollaboratorStatus::Active, 'directory_visible' => true,
        ]);

        return $user;
    }

    private function complete(User $user): Profile
    {
        $profile = $user->collaborator->profile()->firstOrCreate([]);
        app(ProfileEditor::class)->saveDetails($profile, [
            'professional_title' => 'Professor', 'one_line_summary' => 'Summary.', 'country_code' => 'GB',
        ]);
        app(ProfileClassification::class)->setFields($profile, [Discipline::query()->where('name', 'Medicine')->value('id')]);
        // Readiness needs a photograph; the image itself is not under test here.
        $profile->forceFill(['photo_path' => 'photos/test.jpg'])->save();
        app(ProfileEditor::class)->refreshCompletion($profile->fresh());

        return $profile->fresh();
    }

    // ------------------------------------------------------------ positions

    public function test_up_to_three_positions_each_with_their_own_organisation_and_department(): void
    {
        $user = $this->collaborator('Amara', 'Nwosu');

        $this->actingAs($user)->put(route('profile.positions'), ['positions' => [
            ['title' => 'Professor of Health Systems', 'organisation' => 'LSHTM', 'department' => 'Global Health'],
            ['title' => 'Consultant Geriatrician', 'organisation' => 'UCLH', 'department' => ''],
            ['title' => 'Senior Adviser', 'organisation' => 'National Centre for Population Ageing'],
        ]])->assertRedirect(route('profile.edit'));

        $positions = $user->fresh()?->collaborator?->profile?->currentPositions;

        $this->assertCount(3, $positions);
        $this->assertSame('Global Health', $positions[0]->payload['department']);
        $this->assertNull($positions[1]->payload['department']);
        $this->assertSame([1, 2, 3], $positions->pluck('position')->all());
    }

    public function test_blank_position_rows_are_dropped_rather_than_stored(): void
    {
        $user = $this->collaborator('Amara', 'Nwosu');

        $this->actingAs($user)->put(route('profile.positions'), ['positions' => [
            ['title' => '', 'organisation' => '', 'department' => ''],
            ['title' => 'Professor', 'organisation' => 'LSHTM'],
            ['title' => '', 'organisation' => ''],
        ]])->assertRedirect();

        $this->assertCount(1, $user->fresh()?->collaborator?->profile?->currentPositions);
    }

    /** The database refuses a fourth if any screen ever forgets to. */
    public function test_a_fourth_position_is_refused_by_the_database(): void
    {
        $user = $this->collaborator('Amara', 'Nwosu');
        $profile = $user->collaborator->profile()->firstOrCreate([]);

        $this->expectException(QueryException::class);
        foreach ([1, 2, 3, 4] as $slot) {
            $profile->entries()->create([
                'section' => 'current_position', 'position' => $slot, 'payload' => ['title' => 'Role '.$slot, 'organisation' => 'Org'],
            ]);
        }
    }

    public function test_positions_appear_on_the_published_profile(): void
    {
        $viewer = $this->collaborator('Kestutis', 'Jankauskas');
        $listed = $this->collaborator('Amara', 'Nwosu');
        $profile = $this->complete($listed);
        app(ProfileEditor::class)->savePositions($profile, [
            ['title' => 'Consultant Geriatrician', 'organisation' => 'UCLH', 'department' => 'Medicine for the Elderly'],
        ]);

        $this->actingAs($viewer)->get(route('profile.show', $profile))
            ->assertOk()
            ->assertSee('Consultant Geriatrician')
            ->assertSee('Medicine for the Elderly');
    }

    // ------------------------------------------------------------ interest

    public function test_a_collaborator_can_register_an_interest_once_and_the_institute_sees_it(): void
    {
        $from = $this->collaborator('Kestutis', 'Jankauskas');
        $this->complete($from);
        $to = $this->collaborator('Amara', 'Nwosu');
        $target = $this->complete($to);

        $this->actingAs($from)->post(route('profile.interest', $target), ['note' => 'Frailty and pensions overlap.'])
            ->assertRedirect(route('profile.show', $target))
            ->assertSessionHas('status');

        // Registered, not repeated.
        $this->actingAs($from)->post(route('profile.interest', $target), ['note' => 'Again'])->assertRedirect();
        $this->assertDatabaseCount('collaboration_interests', 1);
        $this->assertDatabaseHas('collaboration_interests', ['note' => 'Frailty and pensions overlap.']);

        // And nothing was sent to anybody; the Institute passes it on.
        $this->actingAs($from)->get(route('profile.show', $target))->assertSee('The Institute has it and will pass it on');
    }

    /** Untick "open to approaches" and nobody can. */
    public function test_a_collaborator_who_is_not_open_to_approaches_receives_none(): void
    {
        $from = $this->collaborator('Kestutis', 'Jankauskas');
        $this->complete($from);
        $to = $this->collaborator('Amara', 'Nwosu');
        $target = $this->complete($to);
        $target->forceFill(['accepts_collaboration_interest' => false])->save();

        $this->actingAs($from)->get(route('profile.show', $target))
            ->assertOk()
            ->assertSee('not currently taking approaches')
            ->assertDontSee('Register an interest');

        $this->actingAs($from)->post(route('profile.interest', $target))->assertSessionHasErrors('note');
        $this->assertDatabaseCount('collaboration_interests', 0);
    }

    public function test_nobody_can_register_an_interest_in_themselves(): void
    {
        $user = $this->collaborator('Amara', 'Nwosu');
        $profile = $this->complete($user);

        $this->actingAs($user)->get(route('profile.show', $profile))->assertDontSee('Register an interest');
        $this->actingAs($user)->post(route('profile.interest', $profile))->assertRedirect();
        $this->assertDatabaseCount('collaboration_interests', 0);
    }

    /** An unticked checkbox means no, not "leave it as it was". */
    public function test_unticking_open_to_approaches_saves_as_no(): void
    {
        $user = $this->collaborator('Amara', 'Nwosu');

        $this->actingAs($user)->put(route('profile.details'), ['professional_title' => 'Professor'])->assertRedirect();

        $this->assertFalse($user->fresh()?->collaborator?->profile?->accepts_collaboration_interest);
    }

    // ------------------------------------------------------------ feedback

    public function test_feedback_reaches_the_institute_with_the_page_it_came_from(): void
    {
        $user = $this->collaborator('Amara', 'Nwosu');

        $this->actingAs($user)->get(route('feedback.create', ['from' => 'https://portal.test/directory']))
            ->assertOk()
            ->assertSee('Tell the Institute what you think');

        $this->actingAs($user)->post(route('feedback.store'), [
            'message' => 'The Field search worked well. The photo crop surprised me.',
            'page' => 'https://portal.test/directory',
        ])->assertRedirect(route('home'));

        $this->assertDatabaseHas('feedback_notes', [
            'user_id' => $user->id,
            'page' => 'https://portal.test/directory',
        ]);
    }

    public function test_the_institute_pages_for_interests_and_feedback_are_administrators_only(): void
    {
        $user = $this->collaborator('Amara', 'Nwosu');

        $this->actingAs($user)->get(route('admin.interests.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.feedback.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.taxonomy.index'))->assertForbidden();
    }

    public function test_a_position_keeps_country_and_start_year_and_needs_no_organisation(): void
    {
        $user = $this->collaborator('Amara', 'Nwosu');
        $profile = $user->collaborator->profile()->firstOrCreate([]);

        $this->actingAs($user)->put(route('profile.positions'), [
            'positions' => [
                ['title' => 'Independent consultant', 'country' => 'us', 'start_year' => '2019'],
            ],
        ])->assertSessionHasNoErrors();

        $payload = $profile->currentPositions()->first()->payload;
        $this->assertSame('Independent consultant', $payload['title']);
        $this->assertNull($payload['organisation']);
        $this->assertSame('US', $payload['country']);
        $this->assertSame(2019, $payload['start_year']);
    }
}
