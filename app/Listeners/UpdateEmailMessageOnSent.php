<?php

namespace App\Listeners;

use App\Enums\EmailMessageStatus;
use App\Models\EmailMessage;
use Illuminate\Mail\Events\MessageSent;
use Symfony\Component\Mime\Message;

/**
 * Marks the email log row as handed to the transport and stores the
 * provider's message id, which Postmark webhooks later use to report
 * delivery, bounce or complaint (doc 07 §3).
 */
class UpdateEmailMessageOnSent
{
    public function handle(MessageSent $event): void
    {
        $original = $event->sent->getOriginalMessage();

        if (! $original instanceof Message) {
            return;
        }

        $header = $original->getHeaders()->get('X-Portal-Message-Id');

        if ($header === null) {
            return;
        }

        EmailMessage::query()
            ->whereKey((int) $header->getBodyAsString())
            ->update([
                'status' => EmailMessageStatus::Sent->value,
                'postmark_message_id' => $event->sent->getMessageId(),
                'sent_at' => now(),
            ]);
    }
}
