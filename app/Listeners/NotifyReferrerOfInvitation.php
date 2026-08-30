<?php

namespace App\Listeners;

use App\Enums\EmailMessageStatus;
use App\Events\InvitationIssued;
use App\Mail\ReferrerInvitationNoticeMail;
use App\Models\EmailMessage;
use App\Services\Governance\AuditRecorder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

/**
 * Tells a referrer that the person they recommended has been invited.
 *
 * This is the single point at which a referrer learns the outcome of a
 * recommendation (agreed 18 August 2026). The silence in every other direction
 * is deliberate: they are never told about a decline, and never told that a
 * case was set aside.
 *
 * Three conditions guard it. There must be a referrer, so direct invitations
 * notify nobody. It must not be a reissue, or a lost link would send a second
 * congratulatory message about the same person. And the referrer must not be
 * the invited person themselves.
 */
class NotifyReferrerOfInvitation implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    public function __construct(private readonly AuditRecorder $audit) {}

    public function handle(InvitationIssued $event): void
    {
        // Reissuing replaces a link; it does not invite somebody afresh.
        if ($event->isResend) {
            return;
        }

        $invitation = $event->invitation;
        $referrer = $invitation->referrer?->user;

        if ($referrer === null) {
            return;
        }

        if ($invitation->email !== null && strcasecmp($referrer->email, $invitation->email) === 0) {
            return;
        }

        $emailMessage = EmailMessage::query()->create([
            'message_type' => 'referrer_invitation_notice',
            'to_email' => $referrer->email,
            'subject' => 'The colleague you recommended has been invited',
            'status' => EmailMessageStatus::Queued->value,
            'related_type' => $invitation->getMorphClass(),
            'related_id' => $invitation->id,
            'queued_at' => now(),
        ]);

        try {
            Mail::to($referrer->email)->send(new ReferrerInvitationNoticeMail(
                $invitation,
                ReferrerInvitationNoticeMail::greetingFor($invitation),
                $emailMessage->id,
            ));
        } catch (\Throwable $e) {
            $emailMessage->update([
                'status' => EmailMessageStatus::Failed->value,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }

        $this->audit->record('admission.referrer.notified_of_invitation', null, $invitation, [
            'referrer_collaborator_id' => $invitation->referrer_collaborator_id,
        ]);
    }
}
