<?php

namespace App\Mail;

use App\Models\VocabularyProposal;
use App\Support\Salutation;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Telling a collaborator that a term they proposed is now on their profile.
 *
 * Sent when the Institute approves a proposal, or maps it to a term the
 * vocabulary already held. Not sent on a decline: in the same spirit as the
 * Portal's other decisions, whether to explain a decline is left to the
 * Institute's judgment rather than automated.
 *
 * Short, because the person will simply be wondering, and this answers that.
 */
class VocabularyDecidedMail extends Mailable
{
    public function __construct(
        public readonly VocabularyProposal $proposal,
        public readonly string $greeting,
        public readonly string $shownAs,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'The term you proposed is now on your profile');
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.vocabulary-decided',
            with: [
                'greeting' => $this->greeting,
                'proposed' => $this->proposal->term,
                'shownAs' => $this->shownAs,
                'wasMapped' => $this->proposal->status === VocabularyProposal::STATUS_MAPPED,
                'kind' => $this->proposal->kind,
            ],
        );
    }

    public static function greetingFor(VocabularyProposal $proposal): string
    {
        return Salutation::greeting($proposal->proposedBy, 'Hello');
    }
}
