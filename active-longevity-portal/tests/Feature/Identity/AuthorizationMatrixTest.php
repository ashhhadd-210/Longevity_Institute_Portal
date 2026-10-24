<?php

namespace Tests\Feature\Identity;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesUsersWithRoles;
use Tests\TestCase;

/**
 * The standing regression net (doc 06 §5): for each role and representative
 * endpoint, assert allow or deny. Future surfaces extend this matrix.
 */
class AuthorizationMatrixTest extends TestCase
{
    use CreatesUsersWithRoles;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    public function test_guests_are_redirected_to_login_from_the_admin_area(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_a_collaborator_is_denied_the_admin_area(): void
    {
        $user = $this->userWithRole('collaborator');

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_an_administrator_with_confirmed_mfa_reaches_the_admin_area(): void
    {
        $user = $this->userWithRole('administrator', mfaConfirmed: true);

        $this->actingAs($user)->get('/admin')->assertOk();
    }

    public function test_the_founder_with_confirmed_mfa_reaches_the_admin_area(): void
    {
        $user = $this->userWithRole('founder', mfaConfirmed: true);

        $this->actingAs($user)->get('/admin')->assertOk();
    }

    public function test_any_active_user_reaches_the_authenticated_home(): void
    {
        $user = $this->userWithRole('collaborator');

        $this->actingAs($user)->get('/home')->assertOk();
    }
}
