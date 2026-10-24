<?php

namespace App\Console\Commands;

use App\Enums\CollaboratorStatus;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Remove the accounts created while demonstrating or testing the Portal.
 *
 * Only addresses under example.org qualify. That domain is reserved by the
 * internet's own standards and can never belong to a real person, which is
 * what makes this safe to run on a live system: a real collaborator cannot
 * be matched by it, however the command is invoked.
 *
 * Everything the account owns goes with it. The audit trail stays and is
 * append-only, so the account itself is emptied and locked rather than
 * deleted: the history still names an actor, and nobody can sign in as them.
 */
class RemoveExampleAccounts extends Command
{
    protected $signature = 'portal:remove-example-accounts
        {--confirm : Actually remove them; without this the command only lists}';

    protected $description = 'Remove demonstration accounts whose email address is under example.org';

    public function handle(): int
    {
        $users = User::query()
            ->whereRaw('lower(email) like ?', ['%@example.org'])
            ->whereRaw('lower(email) not like ?', ['removed.%@example.org'])
            ->orderBy('id')
            ->get();

        if ($users->isEmpty()) {
            $this->info('No example.org accounts to remove.');

            return self::SUCCESS;
        }

        foreach ($users as $user) {
            $this->line(sprintf('  %s <%s>', trim($user->first_name.' '.$user->last_name), $user->email));
        }

        if (! $this->option('confirm')) {
            $this->warn(sprintf('%d account(s) listed. Run again with --confirm to remove them.', $users->count()));

            return self::SUCCESS;
        }

        foreach ($users as $user) {
            DB::transaction(function () use ($user): void {
                $collaborator = $user->collaborator;

                if ($collaborator !== null) {
                    // The profile carries everything the person wrote, and the
                    // rest of it follows the profile by the database's own rules.
                    // The collaborator record itself is retired, not deleted:
                    // referrals and invitations name it as their provenance.
                    $collaborator->profile?->forceDelete();
                    DB::table('collaborators')->where('id', $collaborator->id)->update([
                        'status' => CollaboratorStatus::Departed->value,
                        'directory_visible' => false,
                        'updated_at' => now(),
                    ]);
                }

                // A user who has acted cannot be deleted: the audit trail names
                // them and is append-only by design. The account is emptied
                // and locked instead, so the history stays truthful and the
                // person can never sign in again.
                DB::table('role_assignments')->where('user_id', $user->id)->delete();
                DB::table('passkeys')->where('user_id', $user->id)->delete();
                DB::table('sessions')->where('user_id', $user->id)->delete();

                $user->forceFill([
                    'first_name' => 'Removed',
                    'middle_name' => null,
                    'last_name' => 'Account',
                    'name_suffix' => null,
                    'preferred_name' => null,
                    'email' => sprintf('removed.%d@example.org', $user->id),
                    'password' => Hash::make(Str::random(64)),
                    'remember_token' => null,
                    'two_factor_secret' => null,
                    'two_factor_recovery_codes' => null,
                    'two_factor_confirmed_at' => null,
                ])->save();
            });
        }

        $this->info(sprintf('Removed %d example.org account(s).', $users->count()));

        return self::SUCCESS;
    }
}
