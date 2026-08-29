<?php

namespace App\Listeners;

use App\Enums\EmailMessageStatus;
use App\Enums\InvitationChannel;
use App\Enums\InvitationStatus;
use App\Events\InvitationIssued;
use App\Mail\InvitationMail;
use App\Models\EmailMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

/**
 * Queued so that a slow mail provider never delays the administrative action
 * that issued the invitation (doc 01 §2).
 */
class SendInvitationEmail implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    public function handle(InvitationIssued $event): void
    {
        $invitation = $event->invitation;

        // A copy-link invitation is carried by the Founder herself, usually
        // through LinkedIn, and frequently to somebody whose email address the
        // Institute does not have. The Portal sends nothing and claims nothing:
        // it will show the invitation as issued, and later as accepted, and
        // never as delivered, because it did not deliver it (agreed 25 August
        // 2026).
        if ($invitation->delivery_channel === InvitationChannel::CopyLink) {
            return;
        }

        $emailMessage = EmailMessage::query()->create([
            'message_type' => 'invitation',
            'to_email' => $invitation->email,
            'subject' => 'An invitation from the Active Longevity Institute',
            'status' => EmailMessageStatus::Queued->value,
            'related_type' => $invitation->getMorphClass(),
            'related_id' => $invitation->id,
            'queued_at' => now(),
        ]);

        $acceptanceUrl = route('invitations.show', ['token' => $event->rawToken]);

        try {
            Mail::to($invitation->email)
                ->send(new InvitationMail($invitation, $acceptanceUrl, $emailMessage->id));
        } catch (\Throwable $e) {
            $emailMessage->update([
                'status' => EmailMessageStatus::Failed->value,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }

        $invitation->update([
            'status' => InvitationStatus::Sent->value,
            'send_count' => $invitation->send_count + 1,
            'last_sent_at' => now(),
        ]);
    }
}
