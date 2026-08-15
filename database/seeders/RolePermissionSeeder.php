<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

/**
 * The initial catalog from docs/06-authorization-model.md. Idempotent: safe to
 * re-run in every environment. New roles later are configuration, not schema.
 */
class RolePermissionSeeder extends Seeder
{
    /** @var array<string, string> */
    private array $permissions = [
        'admission.application.review' => 'Review website applications in the admission queue',
        'admission.referral.review' => 'Review collaborator referrals in the admission queue',
        'admission.decision.record' => 'Record approve/decline admission decisions',
        'admission.invitation.issue' => 'Issue secure invitations',
        'admission.invitation.revoke' => 'Revoke outstanding invitations',
        'admission.invitation.resend' => 'Resend an invitation',
        'admission.history.view' => 'View the permanent admission record',
        'collaborator.view_admin' => 'View administrative collaborator data (including admin-only fields)',
        'collaborator.manage' => 'Manage collaborator records and profiles',
        'collaborator.status.change' => 'Change collaborator status (active/inactive/departed)',
        'collaborator.visibility.override' => 'Override directory visibility in either direction',
        'expertise.manage' => 'Manage the Institute-controlled expertise vocabulary',
        'governance.role.assign' => 'Assign or remove roles',
        'governance.audit.view' => 'View the audit event stream',
        'governance.user.suspend' => 'Suspend or reinstate user accounts',
        'privacy.export.fulfil' => 'Fulfil data export requests',
        'privacy.erasure.fulfil' => 'Fulfil data erasure requests',
        'platform.admin.access' => 'Access the administrative area',
        'platform.email_log.view' => 'View the outbound email delivery log',
        'platform.settings.manage' => 'Manage platform settings',
    ];

    public function run(): void
    {
        foreach ($this->permissions as $name => $description) {
            Permission::query()->updateOrCreate(['name' => $name], ['description' => $description]);
        }

        $founder = Role::query()->updateOrCreate(['name' => 'founder'], [
            'display_name' => 'Founder',
            'description' => 'Full institutional authority, including role assignment.',
            'requires_mfa' => true,
        ]);
        $founder->permissions()->sync(Permission::query()->pluck('id'));

        $administrator = Role::query()->updateOrCreate(['name' => 'administrator'], [
            'display_name' => 'Administrator',
            'description' => 'Full admission, collaborator and platform administration. Role assignment is Founder-reserved.',
            'requires_mfa' => true,
        ]);
        $administrator->permissions()->sync(
            Permission::query()->where('name', '!=', 'governance.role.assign')->pluck('id')
        );

        Role::query()->updateOrCreate(['name' => 'collaborator'], [
            'display_name' => 'Collaborator',
            'description' => 'Admitted member of the Institute community.',
            'requires_mfa' => false,
        ]);
    }
}
