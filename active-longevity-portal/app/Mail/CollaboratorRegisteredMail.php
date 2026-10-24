<?php

namespace App\Mail;

use App\Models\Collaborator;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Tells the Institute that an invited person has used their invitation and
 * registered, so the Founder knows without having to look.
 */
class CollaboratorRegisteredMail extends Mailable
{
    public function __construct(public readonly Collaborator $collaborator) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->collaborator->user?->fullName().' has registered');
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.collaborator-registered',
            with: [
                'name' => $this->collaborator->user?->fullName(),
                'url' => route('admin.collaborators.index'),
            ],
        );
    }
}
