<?php

namespace Tests\Feature\Identity;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesUsersWithRoles;
use Tests\TestCase;

/**
 * MFA is a hard wall for admin-tier roles, not a suggestion (doc 05 §3). An
 * un-enrolled administrator is sent to enrollment at sign-in — not merely when
 * they first open an administrative page — so no administrative session ever
 * exists without its second factor.
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

    public function test_an_administrator_without_mfa_is_redirected_from_the_landing_page(): void
    {
        $user = $this->userWithRole('administrator');

        $this->actingAs($user)->get('/home')->assertRedirect('/security/mfa');
    }

    public function test_an_administrator_without_mfa_is_redirected_from_the_admin_area(): void
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

    public function test_an_enrolled_administrator_passes_the_wall(): void
    {
        $user = $this->userWithRole('administrator', mfaConfirmed: true);

        $this->actingAs($user)->get('/home')->assertOk();
        $this->actingAs($user)->get('/admin')->assertOk();
    }

    public function test_an_administrator_is_shown_a_link_to_the_administrative_area(): void
    {
        $user = $this->userWithRole('administrator', mfaConfirmed: true);

        $this->actingAs($user)->get('/home')
            ->assertOk()
            ->assertSee('Administration')
            ->assertSee(route('admin.dashboard'), escape: false);
    }

    public function test_a_collaborator_is_not_shown_the_administrative_link(): void
    {
        $user = $this->userWithRole('collaborator');

        $this->actingAs($user)->get('/home')
            ->assertOk()
            ->assertDontSee('Administration');
    }
}
