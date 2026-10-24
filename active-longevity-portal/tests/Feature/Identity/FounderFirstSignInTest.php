<?php

namespace Tests\Feature\Identity;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

/**
 * The Founder's first sign-in, end to end: password, then a QR code on the
 * very first screen, then a confirmed authenticator and recovery codes.
 * Written after the Founder was left on a setup page with no QR code.
 */
class FounderFirstSignInTest extends TestCase
{
    use RefreshDatabase;

    private function founderWithLeftoverTwoFactor(): User
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create([
            'email' => 'founder@example.org',
            'password' => Hash::make('old-password-1234'),
            // As if two step verification had been set up during testing.
            'two_factor_secret' => encrypt('JBSWY3DPEHPK3PXP'),
            'two_factor_recovery_codes' => encrypt(json_encode(['a', 'b'])),
            'two_factor_confirmed_at' => now(),
        ]);
        $user->roles()->attach(Role::query()->where('name', 'founder')->value('id'), ['assigned_at' => now()]);

        return $user;
    }

    public function test_the_founder_command_clears_old_two_step_setup(): void
    {
        $user = $this->founderWithLeftoverTwoFactor();

        $this->artisan('portal:founder', ['email' => 'founder@example.org'])
            ->expectsOutputToContain('QR code appears at first sign-in')
            ->assertSuccessful();

        $user->refresh();
        $this->assertNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_confirmed_at);
    }

    public function test_after_signing_in_the_founder_sees_the_qr_code_at_once_and_can_confirm(): void
    {
        $user = $this->founderWithLeftoverTwoFactor();
        $user->forceFill(['two_factor_secret' => null, 'two_factor_recovery_codes' => null, 'two_factor_confirmed_at' => null])->save();

        // Signing in with the password, as the Founder does...
        $this->post(route('login'), ['email' => 'founder@example.org', 'password' => 'old-password-1234']);
        $this->assertAuthenticatedAs($user);

        // ...the wall sends an unenrolled Founder to setup...
        $this->get(route('admin.dashboard'))->assertRedirect(route('mfa.setup'));

        // ...and the setup page shows the QR code with no extra step.
        $this->get(route('mfa.setup'))
            ->assertOk()
            ->assertSee('<svg', false)
            ->assertSee('Google Authenticator')
            ->assertDontSee('Begin enrollment');

        $user->refresh();
        $this->assertNotNull($user->two_factor_secret);

        // A code from the app confirms it, and the Admin area opens.
        $code = (new Google2FA)->getCurrentOtp(decrypt($user->two_factor_secret));
        // No second password prompt: the sign-in counts as confirmation.
        $this->post(url('user/confirmed-two-factor-authentication'), ['code' => $code])
            ->assertSessionHasNoErrors()
            ->assertRedirectContains('/security/mfa');

        $this->assertNotNull($user->fresh()->two_factor_confirmed_at);
        $this->get(route('admin.dashboard'))->assertOk();
    }
}
