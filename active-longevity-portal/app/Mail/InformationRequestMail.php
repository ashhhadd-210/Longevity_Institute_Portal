<?php

namespace App\Mail;

use App\Models\InformationRequest;
use App\Support\Capacity;
use App\Support\ContactPoint;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;

/**
 * A question from the Institute about an open case.
 *
 * The wording differs by capacity because the situations differ. An applicant
 * is being asked about themselves. A referrer is being asked about somebody
 * else, who has not been contacted and may not know they were recommended, so
 * the message must not imply that a conversation with them has begun.
 */
class InformationRequestMail extends Mailable
{
    public function __construct(
        public readonly InformationRequest $request,
        public readonly ContactPoint $recipient,
        private readonly int $emailMessageId,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->recipient->capacity === Capacity::Referrer
                ? 'A question about the person you recommended'
                : 'A question about your application',
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
            markdown: 'mail.information-request',
            with: [
                'greeting' => $this->recipient->greeting(),
                'asksAboutSomeoneElse' => $this->recipient->capacity === Capacity::Referrer,
                'question' => $this->request->required,
            ],
        );
    }
}
