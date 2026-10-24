<?php

namespace Tests\Feature\Domain;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Authorization is data, not booleans (ADR-004). Application code checks
 * permissions; role names are configuration.
 */
class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function assign(User $user, string $role): void
    {
        $user->roleAssignments()->create([
            'role_id' => Role::query()->where('name', $role)->firstOrFail()->id,
            'assigned_at' => now(),
        ]);
    }

    public function test_founder_can_assign_roles_and_administrator_cannot(): void
    {
        $founder = User::factory()->create();
        $admin = User::factory()->create();
        $this->assign($founder, 'founder');
        $this->assign($admin, 'administrator');

        $this->assertTrue($founder->hasPermission('governance.role.assign'));
        $this->assertFalse($admin->hasPermission('governance.role.assign'));
        $this->assertTrue($admin->hasPermission('admission.invitation.issue'));
    }

    public function test_collaborator_role_holds_no_administrative_permissions(): void
    {
        $user = User::factory()->create();
        $this->assign($user, 'collaborator');

        $this->assertFalse($user->hasPermission('admission.application.review'));
        $this->assertFalse($user->hasPermission('collaborator.view_admin'));
    }

    public function test_admin_tier_roles_require_mfa_and_collaborator_does_not(): void
    {
        $founder = User::factory()->create();
        $collaborator = User::factory()->create();
        $this->assign($founder, 'founder');
        $this->assign($collaborator, 'collaborator');

        $this->assertTrue($founder->requiresMfa());
        $this->assertFalse($collaborator->requiresMfa());
    }
}
