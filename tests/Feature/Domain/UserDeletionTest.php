<?php

namespace Tests\Feature\Domain;

use App\Enums\AccountState;
use App\Models\User;
use App\Services\Governance\AuditRecorder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A person who appears in the institutional record cannot simply be deleted —
 * that would rewrite history. Erasure is satisfied by anonymising them in
 * place, which keeps the record of what was decided without keeping who they
 * were (doc 08 §3).
 */
class UserDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_who_has_acted_cannot_be_deleted(): void
    {
        $user = User::factory()->create();
        app(AuditRecorder::class)->record('identity.login.succeeded', $user, $user);

        $this->expectException(QueryException::class);

        $user->delete();
    }

    public function test_a_user_who_has_acted_can_be_anonymised_in_place(): void
    {
        $user = User::factory()->create(['first_name' => 'Ada', 'last_name' => 'Lovelace']);
        app(AuditRecorder::class)->record('identity.login.succeeded', $user, $user);

        $user->forceFill([
            'first_name' => 'Removed',
            'last_name' => 'Account',
            'email' => 'removed.'.$user->id.'@example.invalid',
            'account_state' => AccountState::Deactivated->value,
        ])->save();

        $fresh = User::query()->findOrFail($user->id);
        $this->assertSame('Removed Account', $fresh->fullName());
        $this->assertFalse($fresh->account_state->canAuthenticate());

        // The institutional record survives the erasure.
        $this->assertDatabaseHas('audit_events', [
            'actor_user_id' => $user->id,
            'event_type' => 'identity.login.succeeded',
        ]);
    }
}
