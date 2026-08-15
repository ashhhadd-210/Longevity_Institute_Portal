<?php

namespace Tests\Feature\Identity;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesUsersWithRoles;
use Tests\TestCase;

/**
 * MFA is a hard wall for admin-tier roles, not a suggestion (doc 05 §3): an
 * un-enrolled administrator is redirected to enrollment before any admin
 * route, and never shown the admin surface.
 */
class MfaWallTest extends TestCase
{
    use CreatesUsersWithRoles;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    public function test_an_administrator_without_mfa_is_redirected_to_enrollment(): void
    {
        $user = $this->userWithRole('administrator');

        $this->actingAs($user)->get('/admin')->assertRedirect('/security/mfa');
    }

    public function test_the_enrollment_page_itself_is_reachable_without_mfa(): void
    {
        $user = $this->userWithRole('administrator');

        $this->actingAs($user)->get('/security/mfa')->assertOk();
    }

    public function test_a_collaborator_is_not_subject_to_the_wall(): void
    {
        $user = $this->userWithRole('collaborator');

        $this->actingAs($user)->get('/home')->assertOk();
    }
}
