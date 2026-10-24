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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The profile a collaborator writes, and the directory that finds them.
 */
class ProfileAndDirectoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ExpertiseVocabularySeeder::class);
        $this->seed(RolePermissionSeeder::class);
        Role::query()->firstOrCreate(['name' => 'collaborator'], ['requires_mfa' => false]);
    }

    private function collaborator(string $first = 'Amara', string $last = 'Nwosu'): User
    {
        // Pin every part of the name. The assertions below look for the full
        // name as rendered, and a factory-generated middle name or suffix would
        // make them fail at random rather than for a reason.
        $user = User::factory()->create([
            'first_name' => $first,
            'middle_name' => null,
            'last_name' => $last,
            'name_suffix' => null,
            'preferred_name' => null,
        ]);

        Collaborator::factory()->create([
            'user_id' => $user->id,
            'status' => CollaboratorStatus::Active,
            'directory_visible' => true,
        ]);

        return $user;
    }

    /**
     * Completion is what admits a profile to the directory, because admitted is
     * not the same as visible.
     */
    private function completeProfileFor(User $user, string $fieldName = 'Medicine'): Profile
    {
        $profile = $user->collaborator->profile()->firstOrCreate([]);

        app(ProfileEditor::class)->saveDetails($profile, [
            'professional_title' => 'Professor of Health Systems',
            'one_line_summary' => 'How health systems behave when people live far longer.',
            'country_code' => 'GB',
            'city' => 'London',
        ]);

        // Readiness needs a photograph; the image itself is not under test here.
        $profile->forceFill(['photo_path' => 'photos/test.jpg'])->save();

        app(ProfileClassification::class)->setFields(
            $profile,
            [Discipline::query()->where('name', $fieldName)->value('id')],
        );

        app(ProfileEditor::class)->refreshCompletion($profile->fresh());

        return $profile->fresh();
    }

    public function test_a_profile_saves_in_pieces_rather_than_all_at_once(): void
    {
        $user = $this->collaborator();

        $this->actingAs($user)->put(route('profile.details'), [
            'professional_title' => 'Professor of Pension Economics',
        ])->assertRedirect(route('profile.edit'));

        $this->assertSame(
            'Professor of Pension Economics',
            $user->fresh()?->collaborator?->profile?->professional_title,
        );
    }

    public function test_a_profile_stays_out_of_the_directory_until_it_says_enough(): void
    {
        $user = $this->collaborator();
        $profile = $user->collaborator->profile()->firstOrCreate([]);

        app(ProfileEditor::class)->saveDetails($profile, ['professional_title' => 'Professor']);

        $this->assertFalse(app(ProfileEditor::class)->isComplete($profile->fresh()));
        $this->assertNull($profile->fresh()->completed_at);

        $this->completeProfileFor($user);

        $this->assertNotNull($user->fresh()?->collaborator?->profile?->completed_at);
    }

    /**
     * People fill the sections in whatever order suits them. Details first and
     * Field last must complete the profile just as surely as the reverse, or
     * a collaborator ends up invisible for no reason they can see.
     */
    public function test_choosing_a_field_last_still_completes_the_profile(): void
    {
        $user = $this->collaborator();
        $profile = $user->collaborator->profile()->firstOrCreate([]);

        $profile->forceFill(['photo_path' => 'photos/test.jpg'])->save();
        app(ProfileEditor::class)->saveDetails($profile, [
            'professional_title' => 'Professor of Health Systems',
            'one_line_summary' => 'How health systems behave when people live far longer.',
            'country_code' => 'GB',
        ]);
        $this->assertNull($profile->fresh()->completed_at, 'No Field yet, so not complete.');

        app(ProfileClassification::class)->setFields(
            $profile->fresh(),
            [Discipline::query()->where('name', 'Medicine')->value('id')],
        );

        $this->assertNotNull($profile->fresh()->completed_at, 'The Field was the last missing piece.');
    }

    public function test_the_directory_lists_a_complete_profile(): void
    {
        $viewer = $this->collaborator('Kestutis', 'Jankauskas');
        $listed = $this->collaborator();
        $this->completeProfileFor($listed);

        $this->actingAs($viewer)
            ->get(route('directory.index'))
            ->assertOk()
            ->assertSee('Amara Nwosu')
            ->assertSee('Professor of Health Systems');
    }

    /** With eight people across sixty seven Fields, empty ones would dominate. */
    public function test_only_fields_containing_somebody_are_offered(): void
    {
        $viewer = $this->collaborator('Kestutis', 'Jankauskas');
        $this->completeProfileFor($this->collaborator(), 'Medicine');

        $response = $this->actingAs($viewer)->get(route('directory.index'))->assertOk();

        $response->assertSee('Medicine');
        $response->assertDontSee('Audiology');
        $response->assertDontSee('Venture Capital');
    }

    public function test_a_search_finds_a_person_by_name_and_by_field(): void
    {
        $viewer = $this->collaborator('Kestutis', 'Jankauskas');
        $this->completeProfileFor($this->collaborator());

        $this->actingAs($viewer)->get(route('directory.index', ['q' => 'Nwosu']))
            ->assertOk()->assertSee('Amara Nwosu');

        $this->actingAs($viewer)->get(route('directory.index', ['q' => 'Medicine']))
            ->assertOk()->assertSee('Amara Nwosu');

        $this->actingAs($viewer)->get(route('directory.index', ['q' => 'Zzzznothing']))
            ->assertOk()->assertDontSee('Amara Nwosu');
    }

    /**
     * The promise made when city and region were collected: they are
     * administrative and never appear on a profile.
     */
    public function test_a_city_is_collected_but_never_published(): void
    {
        $viewer = $this->collaborator('Kestutis', 'Jankauskas');
        $listed = $this->collaborator();
        $profile = $this->completeProfileFor($listed);

        $this->assertSame('London', $profile->city);

        $this->actingAs($viewer)
            ->get(route('profile.show', $profile))
            ->assertOk()
            ->assertSee('Amara Nwosu')
            ->assertSee('United Kingdom')
            ->assertDontSee('London');
    }

    /**
     * A link shows its label, never its address. The browser reveals the
     * address on hover, which is what makes that safe.
     */
    public function test_links_show_their_label_and_are_capped_at_five(): void
    {
        $user = $this->collaborator();
        $this->completeProfileFor($user);

        $rows = [];
        for ($i = 0; $i < 7; $i++) {
            $rows[] = ['label' => 'Link '.$i, 'url' => 'https://example.org/'.$i];
        }

        $this->actingAs($user)->put(route('profile.links'), ['links' => array_slice($rows, 0, 5)])
            ->assertRedirect();

        $this->assertCount(5, $user->fresh()?->collaborator?->profile?->links);
    }

    public function test_a_link_must_be_a_web_address(): void
    {
        $user = $this->collaborator();

        $this->actingAs($user)
            ->from(route('profile.edit'))
            ->put(route('profile.links'), [
                'links' => [['label' => 'Somewhere', 'url' => 'javascript:alert(1)']],
            ])
            ->assertSessionHasErrors('links.0.url');
    }

    /** Guessing at addresses must reveal nothing about who is a collaborator. */
    public function test_an_incomplete_profile_is_not_found_rather_than_forbidden(): void
    {
        $viewer = $this->collaborator('Kestutis', 'Jankauskas');
        $hidden = $this->collaborator('Quiet', 'Person');
        $profile = $hidden->collaborator->profile()->firstOrCreate([]);

        $this->actingAs($viewer)
            ->get(route('profile.show', $profile))
            ->assertNotFound();
    }

    /**
     * The reconciled readiness rule: name, Professional Identity, About Your
     * Work, a Field, a country and a photograph. Optional sections never count.
     */
    public function test_readiness_needs_each_of_the_six_and_nothing_optional(): void
    {
        $user = $this->collaborator();
        $profile = $this->completeProfileFor($user);
        $this->assertNotNull($profile->completed_at);

        foreach (['photo_path', 'country_code', 'one_line_summary', 'professional_title'] as $required) {
            $copy = $profile->fresh();
            $copy->forceFill([$required => null])->save();
            app(ProfileEditor::class)->refreshCompletion($copy);
            $this->assertNull($copy->fresh()->completed_at, "Without {$required} it is not ready.");

            $profile->fresh()->forceFill([$required => $profile->getAttribute($required)])->save();
            app(ProfileEditor::class)->refreshCompletion($profile->fresh());
        }

        // About, links and positions are optional and play no part.
        $this->assertNull($profile->fresh()->about);
        $this->assertNotNull($profile->fresh()->completed_at);
    }

    /** A missing Field is never a dead end: a pending proposal counts. */
    public function test_a_pending_field_proposal_counts_towards_readiness(): void
    {
        $user = $this->collaborator();
        $profile = $user->collaborator->profile()->firstOrCreate([]);
        $profile->forceFill(['photo_path' => 'photos/test.jpg'])->save();
        app(ProfileEditor::class)->saveDetails($profile, [
            'professional_title' => 'Independent longevity economist',
            'one_line_summary' => 'What a longer life costs, and who pays for it.',
            'country_code' => 'US',
        ]);
        $this->assertNull($profile->fresh()->completed_at);

        app(ProfileClassification::class)->proposeTerm($profile->fresh(), $user, 'field', 'Longevity Economics');

        $this->assertNotNull($profile->fresh()->completed_at);
    }

    public function test_about_your_work_takes_five_hundred_characters_and_about_two_thousand(): void
    {
        $user = $this->collaborator();

        $this->actingAs($user)->put(route('profile.details'), [
            'one_line_summary' => str_repeat('a', 500),
            'about' => str_repeat('b', 2000),
        ])->assertSessionHasNoErrors();

        $this->actingAs($user)->from(route('profile.edit'))->put(route('profile.details'), [
            'one_line_summary' => str_repeat('a', 501),
            'about' => str_repeat('b', 2001),
        ])->assertSessionHasErrors(['one_line_summary', 'about']);
    }

    /** The Standards are shown once, recorded with their version, and readable afterwards. */
    public function test_the_profile_standards_are_shown_once_and_recorded(): void
    {
        $user = $this->collaborator();

        $this->actingAs($user)->get(route('profile.edit'))
            ->assertOk()->assertSee('Collaborator Profile Standards')->assertSee('You will not be shown this again');

        $user->refresh();
        $this->assertNotNull($user->profile_standards_shown_at);
        $this->assertSame(config('portal.profile_standards_version'), $user->profile_standards_version);

        $this->actingAs($user)->get(route('profile.edit'))
            ->assertOk()->assertDontSee('You will not be shown this again');

        $this->actingAs($user)->get(route('profile.standards'))
            ->assertOk()->assertSee('selected representation');
    }

    /** The directory card is restrained: no Field badges and no About Your Work. */
    public function test_the_directory_card_shows_identity_and_country_only(): void
    {
        $viewer = $this->collaborator('Kestutis', 'Jankauskas');
        $this->completeProfileFor($this->collaborator());

        $this->actingAs($viewer)->get(route('directory.index'))
            ->assertOk()
            ->assertSee('Amara Nwosu')
            ->assertSee('Professor of Health Systems')
            ->assertDontSee('How health systems behave when people live far longer.');
    }
}
