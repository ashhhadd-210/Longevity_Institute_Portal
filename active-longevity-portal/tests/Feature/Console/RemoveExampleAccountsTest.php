<?php

namespace Tests\Feature\Console;

use App\Enums\CollaboratorStatus;
use App\Models\Collaborator;
use App\Models\Discipline;
use App\Models\User;
use App\Services\Expertise\ProfileClassification;
use App\Services\Profiles\ProfileEditor;
use Database\Seeders\ExpertiseVocabularySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Demonstration accounts can be removed from a live system, and nothing
 * else can be, however the command is invoked.
 */
class RemoveExampleAccountsTest extends TestCase
{
    use RefreshDatabase;

    private function collaboratorWithProfile(string $email): User
    {
        $user = User::factory()->create(['email' => $email]);
        Collaborator::factory()->create(['user_id' => $user->id, 'status' => CollaboratorStatus::Active]);

        $profile = $user->collaborator->profile()->firstOrCreate([]);
        app(ProfileEditor::class)->saveDetails($profile, ['professional_title' => 'Professor']);
        app(ProfileClassification::class)->setFields($profile->fresh(), [
            Discipline::query()->where('name', 'Medicine')->value('id'),
        ]);

        return $user;
    }

    public function test_only_example_org_accounts_are_removed_and_only_when_confirmed(): void
    {
        $this->seed(ExpertiseVocabularySeeder::class);

        $demo = $this->collaboratorWithProfile('helena.marsh.123456@example.org');
        $real = $this->collaboratorWithProfile('helena@university.ac.uk');
        $demoProfileId = $demo->collaborator->profile->id;

        $this->artisan('portal:remove-example-accounts')
            ->expectsOutputToContain('helena.marsh.123456@example.org')
            ->expectsOutputToContain('--confirm')
            ->assertSuccessful();

        // Listing must not remove anything.
        $this->assertDatabaseHas('users', ['id' => $demo->id]);

        $this->artisan('portal:remove-example-accounts', ['--confirm' => true])->assertSuccessful();
        // Running again finds nothing: an emptied account is not offered twice.
        $this->artisan('portal:remove-example-accounts')->expectsOutputToContain('No example.org accounts')->assertSuccessful();

        // The audit trail is append-only and names the actor, so the account
        // is emptied and locked rather than deleted.
        $this->assertDatabaseHas('users', ['id' => $demo->id, 'email' => 'removed.'.$demo->id.'@example.org', 'last_name' => 'Account']);
        $this->assertDatabaseMissing('role_assignments', ['user_id' => $demo->id]);
        $this->assertDatabaseHas('collaborators', ['user_id' => $demo->id, 'status' => 'departed', 'directory_visible' => false]);
        $this->assertDatabaseMissing('profiles', ['id' => $demoProfileId]);
        $this->assertDatabaseMissing('profile_fields', ['profile_id' => $demoProfileId]);

        $this->assertDatabaseHas('users', ['id' => $real->id]);
        $this->assertDatabaseHas('profiles', ['collaborator_id' => $real->collaborator->id]);
    }
}
