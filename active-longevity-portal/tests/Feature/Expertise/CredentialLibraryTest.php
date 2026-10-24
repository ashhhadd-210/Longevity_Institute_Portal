<?php

namespace Tests\Feature\Expertise;

use App\Enums\CollaboratorStatus;
use App\Models\Collaborator;
use App\Models\Credential;
use App\Models\Role;
use App\Models\User;
use App\Services\Expertise\CredentialLibrary;
use Database\Seeders\CredentialSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The credential library as it was specified to the Institute in September.
 */
class CredentialLibraryTest extends TestCase
{
    use RefreshDatabase;

    private CredentialLibrary $library;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CredentialSeeder::class);
        $this->seed(RolePermissionSeeder::class);
        Role::query()->firstOrCreate(['name' => 'collaborator'], ['requires_mfa' => false]);
        $this->library = app(CredentialLibrary::class);
    }

    private function collaborator(): User
    {
        $user = User::factory()->create();
        Collaborator::factory()->create([
            'user_id' => $user->id,
            'status' => CollaboratorStatus::Active,
            'directory_visible' => true,
        ]);

        return $user;
    }

    /**
     * Somebody who holds a Master of Public Health but does not think of
     * themselves as an MPH must still find it.
     */
    public function test_the_meaning_is_searchable_as_well_as_the_letters(): void
    {
        $byLetters = $this->library->search('MPH')->pluck('acronym')->all();
        $byMeaning = $this->library->search('public health')->pluck('acronym')->all();

        $this->assertContains('MPH', $byLetters);
        $this->assertContains('MPH', $byMeaning);
    }

    /**
     * The same letters, several records. The meaning and jurisdiction beside
     * each is how a person picks the FRCP they actually hold.
     */
    public function test_the_same_letters_return_every_credential_they_stand_for(): void
    {
        $frcp = $this->library->search('FRCP');

        $this->assertGreaterThanOrEqual(3, $frcp->where('acronym', 'FRCP')->count());
        $this->assertEqualsCanonicalizing(
            ['London', 'Edinburgh', 'Glasgow'],
            $frcp->where('acronym', 'FRCP')->pluck('jurisdiction')->all(),
        );
    }

    public function test_an_exact_acronym_ranks_first(): void
    {
        $this->assertSame('PhD', $this->library->search('PhD')->first()?->acronym);
    }

    public function test_a_collaborator_displays_up_to_three(): void
    {
        $user = $this->collaborator();
        $profile = $user->collaborator->profile()->firstOrCreate([]);

        $ids = Credential::query()->selectable()->limit(3)->pluck('id')->all();
        $this->library->setDisplayed($profile, $ids);

        $this->assertCount(3, $profile->approvedCredentials()->get());
    }

    /** The screen refuses a fourth, and so does the database if a screen forgets. */
    public function test_a_fourth_credential_is_refused_by_the_service_and_by_the_database(): void
    {
        $user = $this->collaborator();
        $profile = $user->collaborator->profile()->firstOrCreate([]);
        $ids = Credential::query()->selectable()->limit(4)->pluck('id')->all();

        try {
            $this->library->setDisplayed($profile, $ids);
            $this->fail('The service should refuse four credentials.');
        } catch (\InvalidArgumentException) {
        }

        // The cap is a deferred constraint trigger, which fires at commit. In
        // production that is the end of each statement under autocommit; here
        // the test runs inside a transaction that is never committed, so ask
        // PostgreSQL to check immediately and prove the trigger really bites.
        DB::statement('SET CONSTRAINTS ALL IMMEDIATE');

        $this->expectException(QueryException::class);
        foreach ($ids as $i => $id) {
            $profile->approvedCredentials()->attach($id, ['position' => $i]);
        }
    }

    /** Nobody is ever offered somebody else's unapproved request. */
    public function test_an_unapproved_credential_cannot_be_displayed_or_found(): void
    {
        $pending = Credential::query()->create([
            'acronym' => 'XYZ', 'meaning' => 'Something Pending', 'approved' => false, 'active' => true,
        ]);

        $this->assertCount(0, $this->library->search('XYZ'));

        $user = $this->collaborator();
        $profile = $user->collaborator->profile()->firstOrCreate([]);
        $this->library->setDisplayed($profile, [$pending->id]);

        $this->assertCount(0, $profile->approvedCredentials()->get());
    }

    public function test_the_request_form_shows_near_matches_before_recording_anything(): void
    {
        $user = $this->collaborator();

        $this->actingAs($user)
            ->post(route('profile.credentials.propose'), [
                'acronym' => 'MPH', 'meaning' => 'Masters in Public Health',
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHas('credential_near_matches');

        $this->assertDatabaseCount('vocabulary_proposals', 0);

        $this->actingAs($user)
            ->post(route('profile.credentials.propose'), [
                'acronym' => 'MPH', 'meaning' => 'Masters in Public Health', 'confirmed' => 1,
            ])
            ->assertRedirect(route('profile.edit'));

        $this->assertDatabaseHas('vocabulary_proposals', ['kind' => 'credential', 'term' => 'MPH', 'status' => 'pending']);
    }

    public function test_the_library_is_not_exposed_to_the_open_web(): void
    {
        $this->getJson(route('credentials.search', ['q' => 'PhD']))->assertUnauthorized();
    }
}
