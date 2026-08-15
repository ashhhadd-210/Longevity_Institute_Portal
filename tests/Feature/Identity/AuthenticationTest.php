<?php

namespace Tests\Feature\Identity;

use App\Enums\AccountState;
use App\Models\AuditEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_sign_in_and_the_event_is_audited(): void
    {
        $user = User::factory()->create(['password' => 'a-long-enough-password']);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'a-long-enough-password',
        ])->assertRedirect();

        $this->assertAuthenticatedAs($user);
        $this->assertTrue(
            AuditEvent::query()
                ->where('event_type', 'identity.login.succeeded')
                ->where('actor_user_id', $user->id)
                ->exists()
        );
    }

    public function test_a_failed_attempt_is_audited(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'not-the-password',
        ]);

        $this->assertGuest();
        $this->assertTrue(
            AuditEvent::query()->where('event_type', 'identity.login.failed')->exists()
        );
    }

    public function test_a_suspended_account_cannot_sign_in_and_the_failure_is_generic(): void
    {
        $user = User::factory()->create([
            'password' => 'a-long-enough-password',
            'account_state' => AccountState::Suspended->value,
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'a-long-enough-password',
        ]);

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_a_suspension_takes_effect_on_a_live_session(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/home')->assertOk();

        $user->forceFill(['account_state' => AccountState::Suspended->value])->save();

        $this->actingAs($user)->get('/home')->assertRedirect('/login');
    }

    public function test_password_reset_requests_do_not_reveal_whether_an_address_exists(): void
    {
        Notification::fake();

        $known = User::factory()->create();

        $knownResponse = $this->from('/forgot-password')->post('/forgot-password', ['email' => $known->email]);
        $unknownResponse = $this->from('/forgot-password')->post('/forgot-password', ['email' => 'nobody@example.org']);

        $knownResponse->assertRedirect('/forgot-password');
        $unknownResponse->assertRedirect('/forgot-password');
        $this->assertSame(
            $knownResponse->getSession()->get('status'),
            $unknownResponse->getSession()->get('status'),
        );
    }
}
