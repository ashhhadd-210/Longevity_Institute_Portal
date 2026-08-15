<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use App\Services\Governance\AuditRecorder;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Bootstraps administrative accounts (the Founder and the developer's
 * delegated account) without touching the database by hand — an operational
 * requirement of the Foundation Release (doc 09).
 */
class CreateAdminUser extends Command
{
    protected $signature = 'portal:create-admin
        {email : Email address of the administrator}
        {first_name : First name}
        {last_name : Last name}
        {--founder : Assign the founder role instead of administrator}';

    protected $description = 'Create an administrative user and print a one-time initial password';

    public function handle(AuditRecorder $audit): int
    {
        $email = (string) $this->argument('email');
        $roleName = $this->option('founder') ? 'founder' : 'administrator';

        if (User::query()->where('email', $email)->exists()) {
            $this->error("A user with the address {$email} already exists.");

            return self::FAILURE;
        }

        $password = Str::password(20);

        $user = User::query()->create([
            'first_name' => (string) $this->argument('first_name'),
            'last_name' => (string) $this->argument('last_name'),
            'email' => $email,
            'password' => $password,
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        $user->roleAssignments()->create([
            'role_id' => Role::query()->where('name', $roleName)->firstOrFail()->id,
            'assigned_at' => now(),
        ]);

        $audit->record('governance.admin.created', null, $user, ['role' => $roleName]);

        $this->info("Administrator created with the {$roleName} role.");
        $this->line('One-time initial password (change it after first sign-in):');
        $this->line($password);
        $this->warn('Two-factor enrollment will be required at first sign-in.');

        return self::SUCCESS;
    }
}
