<?php

namespace App\Mail;

use App\Models\Invitation;
use App\Support\Salutation;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;

/**
 * Telling a referrer that the person they recommended has been invited.
 *
 * This is the only moment a referrer hears the outcome of a recommendation, and
 * it exists because being told nothing at all would be discourteous to someone
 * who did the Institute a favour (agreed 18 August 2026).
 *
 * The silence in the other direction is deliberate and must be preserved: a
 * referrer is never told that somebody was declined or set aside. So this
 * message exists and its counterpart does not.
 *
 * It also says nothing about what was decided, only that an invitation has been
 * extended, and it does not promise that the person will accept.
 */
class ReferrerInvitationNoticeMail extends Mailable
{
    public function __construct(
        public readonly Invitation $invitation,
        public readonly string $referrerGreeting,
        private readonly int $emailMessageId,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'The colleague you recommended has been invited',
        );
    }

    public function headers(): Headers
    {
        return new Headers(
            text: ['X-Portal-Message-Id' => (string) $this->emailMessageId],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.referrer-invitation-notice',
            with: [
                'greeting' => $this->referrerGreeting,
                'candidateName' => $this->invitation->fullName(),
            ],
        );
    }

    public static function greetingFor(Invitation $invitation): string
    {
        return Salutation::greeting($invitation->referrer?->user, 'Dear');
    }
}
