<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use App\Services\Governance\AuditRecorder;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Give an existing person Founder access with a fresh one-time password.
 *
 * For the case where the account already exists, for example from earlier
 * testing, and simply needs the Founder role and a password the person can
 * use. Short to type on a server console, which does not always allow pasting.
 */
class GrantFounderAccess extends Command
{
    protected $signature = 'portal:founder {email : Email address of the existing account}';

    protected $description = 'Give an existing account the Founder role and print a one-time password';

    public function handle(AuditRecorder $audit): int
    {
        $user = User::query()->whereRaw('lower(email) = ?', [mb_strtolower((string) $this->argument('email'))])->first();

        if ($user === null) {
            $this->error('No account uses that address. Use portal:create-admin with --founder instead.');

            return self::FAILURE;
        }

        $founder = Role::query()->where('name', 'founder')->firstOrFail();

        if (! $user->roles()->where('roles.id', $founder->id)->exists()) {
            $user->roles()->attach($founder->id, ['assigned_at' => now()]);
        }

        // Letters and numbers only: it is often read off a console and typed by hand.
        $password = Str::password(16, symbols: false);
        $user->forceFill([
            'password' => $password,
            'account_state' => 'active',
            // Start two step verification afresh: an authenticator set up
            // during testing would otherwise ask for codes the person never had.
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        $audit->record('governance.founder.granted', null, $user);

        $this->info(trim($user->first_name.' '.$user->last_name).' now has Founder access.');
        $this->line('Two step verification: reset; the QR code appears at first sign-in.');
        $this->line('One-time password (send privately):');
        $this->line($password);

        return self::SUCCESS;
    }
}
