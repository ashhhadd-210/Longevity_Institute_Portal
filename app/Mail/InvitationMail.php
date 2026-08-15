<?php

namespace App\Mail;

use App\Models\Invitation;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;

class InvitationMail extends Mailable
{
    public function __construct(
        public readonly Invitation $invitation,
        public readonly string $acceptanceUrl,
        private readonly int $emailMessageId,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'An invitation from the Active Longevity Institute',
        );
    }

    public function headers(): Headers
    {
        // Correlates the sent message with its email_messages row so the
        // delivery log stays truthful (doc 07 §3).
        return new Headers(
            text: ['X-Portal-Message-Id' => (string) $this->emailMessageId],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.invitation',
            with: [
                'firstName' => $this->invitation->first_name,
                'acceptanceUrl' => $this->acceptanceUrl,
                'expiresAt' => $this->invitation->expires_at,
            ],
        );
    }
}
