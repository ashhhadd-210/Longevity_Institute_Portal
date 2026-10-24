<?php

namespace App\Mail;

use App\Models\VocabularyProposal;
use App\Support\Salutation;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Telling a collaborator that a term they proposed was not added.
 *
 * The Institute decided (October 2026) that the person should be told, with
 * the Institute's note if one was written, so nobody is left wondering.
 */
class VocabularyDeclinedMail extends Mailable
{
    public function __construct(public readonly VocabularyProposal $proposal) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'About the term you proposed');
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.vocabulary-declined',
            with: [
                'greeting' => Salutation::greeting($this->proposal->proposedBy, 'Hello'),
                'proposed' => $this->proposal->term,
                'kind' => $this->proposal->kind,
                'note' => $this->proposal->review_note,
                'url' => route('profile.edit'),
            ],
        );
    }
}
