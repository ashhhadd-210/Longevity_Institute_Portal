<?php

namespace Tests\Feature\Admission;

use App\Enums\InvitationChannel;
use App\Enums\InvitationStatus;
use App\Events\InvitationIssued;
use App\Models\AuditEvent;
use App\Models\CandidateCase;
use App\Models\User;
use App\Services\Admission\InvitationService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use InvalidArgumentException;
use Tests\TestCase;

class InvitationServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): InvitationService
    {
        return app(InvitationService::class);
    }

    public function test_issuing_stores_only_a_hash_and_dispatches_the_email_event(): void
    {
        Event::fake([InvitationIssued::class]);
        $issuer = User::factory()->create();

        $issued = $this->service()->issue(
            issuedBy: $issuer,
            firstName: 'Ada',
            lastName: 'Lovelace',
            email: 'candidate@example.org',
        );

        $this->assertSame(InvitationStatus::Issued, $issued->invitation->status);
        $this->assertSame(64, strlen($issued->invitation->token_hash));
        $this->assertTrue($issued->invitation->expires_at->isFuture());

        Event::assertDispatched(InvitationIssued::class, function (InvitationIssued $event) use ($issued): bool {
            // The raw token is never persisted; hashing it must locate the row.
            return $event->invitation->is($issued->invitation)
                && $this->service()->findAcceptable($event->rawToken)?->is($issued->invitation) === true;
        });

        $this->assertTrue(
            AuditEvent::query()
                ->where('event_type', 'admission.invitation.issued')
                ->where('subject_type', 'invitation')
                ->where('subject_id', $issued->invitation->id)
                ->exists()
        );
    }

    public function test_reissuing_rotates_the_token_and_invalidates_the_previous_one(): void
    {
        Event::fake([InvitationIssued::class]);
        $issuer = User::factory()->create();

        $issued = $this->service()->issue(
            issuedBy: $issuer,
            firstName: 'Ada',
            lastName: 'Lovelace',
            email: 'candidate@example.org',
        );

        $firstHash = $issued->invitation->token_hash;

        $this->service()->reissue($issued->invitation, $issuer);

        $this->assertNotSame($firstHash, $issued->invitation->fresh()->token_hash);
        $this->assertNull($this->service()->findAcceptable($issued->rawToken));
        $this->assertTrue(
            AuditEvent::query()->where('event_type', 'admission.invitation.reissued')->exists()
        );
    }

    public function test_a_revoked_invitation_is_no_longer_acceptable(): void
    {
        Event::fake([InvitationIssued::class]);
        $issuer = User::factory()->create();

        $issued = $this->service()->issue(
            issuedBy: $issuer,
            firstName: 'Ada',
            lastName: 'Lovelace',
            email: 'candidate@example.org',
        );

        $this->service()->revoke($issued->invitation, $issuer, 'Issued in error');

        $this->assertSame(InvitationStatus::Revoked, $issued->invitation->fresh()->status);
        $this->assertNull($this->service()->findAcceptable($issued->rawToken));
    }

    public function test_an_expired_invitation_is_not_acceptable(): void
    {
        Event::fake([InvitationIssued::class]);
        $issuer = User::factory()->create();

        $issued = $this->service()->issue(
            issuedBy: $issuer,
            firstName: 'Ada',
            lastName: 'Lovelace',
            email: 'candidate@example.org',
        );

        $issued->invitation->forceFill(['expires_at' => now()->subDay()])->save();

        $this->assertNull($this->service()->findAcceptable($issued->rawToken));
    }

    public function test_an_accepted_invitation_cannot_be_revoked(): void
    {
        Event::fake([InvitationIssued::class]);
        $issuer = User::factory()->create();

        $issued = $this->service()->issue(
            issuedBy: $issuer,
            firstName: 'Ada',
            lastName: 'Lovelace',
            email: 'candidate@example.org',
        );

        $issued->invitation->forceFill([
            'status' => InvitationStatus::Accepted->value,
            'accepted_at' => now(),
        ])->save();

        $this->expectException(InvalidArgumentException::class);

        $this->service()->revoke($issued->invitation->fresh(), $issuer);
    }

    /**
     * The LinkedIn route. Most of these relationships have no email address
     * behind them, and requiring one would force an address to be invented.
     */
    public function test_a_copy_link_invitation_needs_no_email_address(): void
    {
        Event::fake([InvitationIssued::class]);
        $issuer = User::factory()->create();

        $issued = $this->service()->issue(
            issuedBy: $issuer,
            firstName: 'Ada',
            lastName: 'Lovelace',
            channel: InvitationChannel::CopyLink,
        );

        $this->assertNull($issued->invitation->email);
        $this->assertSame(InvitationChannel::CopyLink, $issued->invitation->delivery_channel);
        $this->assertStringContainsString($issued->rawToken, $issued->acceptanceUrl);
    }

    public function test_the_portal_refuses_to_email_an_invitation_with_no_address(): void
    {
        $issuer = User::factory()->create();

        $this->expectException(InvalidArgumentException::class);

        $this->service()->issue(
            issuedBy: $issuer,
            firstName: 'Ada',
            lastName: 'Lovelace',
            channel: InvitationChannel::Email,
        );
    }

    /**
     * One person, one invitation. A referral for somebody already invited must
     * not quietly start a second track for the same human being.
     */
    public function test_a_second_invitation_for_the_same_case_is_refused(): void
    {
        Event::fake([InvitationIssued::class]);
        $issuer = User::factory()->create();
        $case = CandidateCase::query()->create(['display_name' => 'Ada Lovelace', 'email' => 'ada@example.org']);

        $this->service()->issue(
            issuedBy: $issuer,
            firstName: 'Ada',
            lastName: 'Lovelace',
            email: 'ada@example.org',
            case: $case,
        );

        $this->expectException(DomainException::class);

        $this->service()->issue(
            issuedBy: $issuer,
            firstName: 'Ada',
            lastName: 'Lovelace',
            email: 'ada@example.org',
            case: $case->fresh(),
        );
    }
}
