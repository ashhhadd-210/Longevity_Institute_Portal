<?php

namespace Tests\Concerns;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

trait CreatesUsersWithRoles
{
    protected function seedRoles(): void
    {
        $this->seed(RolePermissionSeeder::class);
    }

    protected function userWithRole(string $role, bool $mfaConfirmed = false): User
    {
        $user = User::factory()->create(
            $mfaConfirmed ? ['two_factor_confirmed_at' => now()] : []
        );

        $user->roleAssignments()->create([
            'role_id' => Role::query()->where('name', $role)->firstOrFail()->id,
            'assigned_at' => now(),
        ]);

        return $user;
    }
}
