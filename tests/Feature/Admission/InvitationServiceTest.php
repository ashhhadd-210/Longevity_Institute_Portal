<?php

namespace Tests\Feature\Admission;

use App\Enums\InvitationStatus;
use App\Events\InvitationIssued;
use App\Models\AuditEvent;
use App\Models\User;
use App\Services\Admission\InvitationService;
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

        $invitation = $this->service()->issue('candidate@example.org', 'Ada', 'Lovelace', $issuer);

        $this->assertSame(InvitationStatus::Issued, $invitation->status);
        $this->assertSame(64, strlen($invitation->token_hash));
        $this->assertTrue($invitation->expires_at->isFuture());

        Event::assertDispatched(InvitationIssued::class, function (InvitationIssued $event) use ($invitation): bool {
            // The raw token is never persisted; hashing it must locate the row.
            return $event->invitation->is($invitation)
                && $this->service()->findAcceptable($event->rawToken)?->is($invitation) === true;
        });

        $this->assertTrue(
            AuditEvent::query()
                ->where('event_type', 'admission.invitation.issued')
                ->where('subject_type', 'invitation')
                ->where('subject_id', $invitation->id)
                ->exists()
        );
    }

    public function test_resending_rotates_the_token_and_invalidates_the_previous_one(): void
    {
        Event::fake([InvitationIssued::class]);
        $issuer = User::factory()->create();

        $invitation = $this->service()->issue('candidate@example.org', 'Ada', 'Lovelace', $issuer);
        $firstHash = $invitation->token_hash;

        $this->service()->resend($invitation, $issuer);

        $this->assertNotSame($firstHash, $invitation->fresh()->token_hash);
        $this->assertTrue(
            AuditEvent::query()->where('event_type', 'admission.invitation.resent')->exists()
        );
    }

    public function test_a_revoked_invitation_is_no_longer_acceptable(): void
    {
        Event::fake([InvitationIssued::class]);
        $issuer = User::factory()->create();

        $invitation = $this->service()->issue('candidate@example.org', 'Ada', 'Lovelace', $issuer);
        $raw = null;
        Event::assertDispatched(InvitationIssued::class, function (InvitationIssued $event) use (&$raw): bool {
            $raw = $event->rawToken;

            return true;
        });

        $this->service()->revoke($invitation, $issuer, 'Issued in error');

        $this->assertSame(InvitationStatus::Revoked, $invitation->fresh()->status);
        $this->assertNull($this->service()->findAcceptable((string) $raw));
    }

    public function test_an_expired_invitation_is_not_acceptable(): void
    {
        Event::fake([InvitationIssued::class]);
        $issuer = User::factory()->create();

        $invitation = $this->service()->issue('candidate@example.org', 'Ada', 'Lovelace', $issuer);
        $invitation->forceFill(['expires_at' => now()->subDay()])->save();

        Event::assertDispatched(InvitationIssued::class, function (InvitationIssued $event): bool {
            $this->assertNull($this->service()->findAcceptable($event->rawToken));

            return true;
        });
    }

    public function test_an_accepted_invitation_cannot_be_revoked(): void
    {
        Event::fake([InvitationIssued::class]);
        $issuer = User::factory()->create();

        $invitation = $this->service()->issue('candidate@example.org', 'Ada', 'Lovelace', $issuer);
        $invitation->forceFill(['status' => InvitationStatus::Accepted->value, 'accepted_at' => now()])->save();

        $this->expectException(InvalidArgumentException::class);

        $this->service()->revoke($invitation->fresh(), $issuer);
    }
}
