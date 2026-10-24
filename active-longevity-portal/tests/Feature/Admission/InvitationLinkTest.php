<?php

namespace Tests\Feature\Admission;

use App\Enums\InvitationStatus;
use App\Models\Invitation;
use App\Models\Role;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The behaviour of an invitation link (agreed 18 and 25 August 2026).
 *
 * The first test here is the important one. LinkedIn and Outlook fetch links to
 * scan them before any human sees them, so an invitation consumed on GET would
 * be spent by a machine and the real recipient would meet a dead link.
 */
class InvitationLinkTest extends TestCase
{
    use RefreshDatabase;

    private function issue(array $attributes = []): array
    {
        $token = Invitation::generateToken();

        $invitation = Invitation::factory()->create([
            'token_hash' => $token['hash'],
            ...$attributes,
        ]);

        return [$invitation, $token['raw']];
    }

    public function test_opening_the_link_does_not_consume_the_invitation(): void
    {
        [$invitation, $raw] = $this->issue();

        // Opened repeatedly, exactly as a link scanner would.
        $this->get(route('invitations.show', ['token' => $raw]))->assertOk();
        $this->get(route('invitations.show', ['token' => $raw]))->assertOk();
        $this->get(route('invitations.show', ['token' => $raw]))->assertOk();

        $invitation->refresh();

        $this->assertSame(InvitationStatus::Issued, $invitation->status);
        $this->assertNull($invitation->accepted_at);
        $this->assertTrue($invitation->isAcceptable());
    }

    public function test_only_completing_registration_consumes_the_invitation(): void
    {
        $this->seed(RolePermissionSeeder::class);
        Role::query()->firstOrCreate(['name' => 'collaborator'], ['requires_mfa' => false]);

        [$invitation, $raw] = $this->issue(['email' => 'ada@example.org']);

        $this->post(route('invitations.accept', ['token' => $raw]), [
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'password' => 'a-long-enough-passphrase-1',
            'password_confirmation' => 'a-long-enough-passphrase-1',
            'privacy_consent' => '1',
        ])->assertRedirect();

        $invitation->refresh();

        $this->assertSame(InvitationStatus::Accepted, $invitation->status);
        $this->assertNotNull($invitation->accepted_at);
    }

    public function test_the_same_link_cannot_be_used_twice(): void
    {
        [$invitation, $raw] = $this->issue([
            'status' => InvitationStatus::Accepted->value,
            'accepted_at' => now(),
        ]);

        $this->get(route('invitations.show', ['token' => $raw]))
            ->assertOk()
            ->assertSee('already been used');
    }

    /**
     * A lapsed link is met with courtesy. Arriving at one is not a mistake the
     * visitor made, so they are never shown an error.
     */
    public function test_a_lapsed_link_is_met_with_a_courteous_page_not_an_error(): void
    {
        [, $raw] = $this->issue(['expires_at' => now()->subDay()]);

        $this->get(route('invitations.show', ['token' => $raw]))
            ->assertOk()
            ->assertSee('lapsed')
            ->assertSee('Nothing has gone wrong');
    }

    public function test_a_withdrawn_link_is_met_with_a_courteous_page(): void
    {
        [, $raw] = $this->issue([
            'status' => InvitationStatus::Revoked->value,
            'revoked_at' => now(),
        ]);

        $this->get(route('invitations.show', ['token' => $raw]))
            ->assertOk()
            ->assertSee('no longer open');
    }

    /**
     * An unrecognised token and a lapsed one look identical from outside. There
     * is no reason to help anybody tell a wrong guess from a real invitation.
     */
    public function test_an_unknown_token_reveals_nothing(): void
    {
        $this->get(route('invitations.show', ['token' => str_repeat('f', 64)]))
            ->assertOk()
            ->assertSee('lapsed');
    }

    public function test_a_copy_link_invitation_asks_the_person_for_their_own_address(): void
    {
        [, $raw] = $this->issue(['delivery_channel' => 'copy_link', 'email' => null]);

        $this->get(route('invitations.show', ['token' => $raw]))
            ->assertOk()
            ->assertSee('This is the address you will sign in with');
    }
}
