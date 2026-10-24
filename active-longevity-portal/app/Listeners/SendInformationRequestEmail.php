<?php

namespace App\Listeners;

use App\Enums\EmailMessageStatus;
use App\Events\InformationRequested;
use App\Mail\InformationRequestMail;
use App\Models\EmailMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

/**
 * Sends the Institute's question to whoever the domain said may receive it.
 *
 * The recipient arrives with the event and is not recomputed here. On a
 * referral that recipient is the referrer, and working it out a second time
 * would be one more place the rule could be got wrong.
 */
class SendInformationRequestEmail implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    public function handle(InformationRequested $event): void
    {
        $emailMessage = EmailMessage::query()->create([
            'message_type' => 'information_request',
            'to_email' => $event->recipient->email,
            'subject' => 'A question from the Active Longevity Institute',
            'status' => EmailMessageStatus::Queued->value,
            'related_type' => $event->request->getMorphClass(),
            'related_id' => $event->request->id,
            'queued_at' => now(),
        ]);

        try {
            Mail::to($event->recipient->email)
                ->send(new InformationRequestMail($event->request, $event->recipient, $emailMessage->id));
        } catch (\Throwable $e) {
            $emailMessage->update([
                'status' => EmailMessageStatus::Failed->value,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
