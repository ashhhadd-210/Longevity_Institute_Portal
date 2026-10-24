<?php

namespace App\Http\Controllers\Webhooks;

use App\Enums\EmailMessageStatus;
use App\Http\Controllers\Controller;
use App\Models\EmailMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Postmark delivery webhooks keep the email log truthful: delivered, bounced,
 * or spam-complained, per message (doc 07 §3). Authenticated with a shared
 * token compared in constant time.
 */
class PostmarkWebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $expected = (string) config('services.postmark.webhook_token');

        if ($expected === '' || ! hash_equals($expected, (string) $request->query('token'))) {
            abort(403);
        }

        $messageId = $request->string('MessageID')->value();
        $recordType = $request->string('RecordType')->value();

        if ($messageId === '' || $recordType === '') {
            return response()->json(['status' => 'ignored']);
        }

        $message = EmailMessage::query()->where('postmark_message_id', $messageId)->first();

        if ($message === null) {
            return response()->json(['status' => 'unknown-message']);
        }

        match ($recordType) {
            'Delivery' => $message->update([
                'status' => EmailMessageStatus::Delivered->value,
                'delivered_at' => now(),
            ]),
            'Bounce' => $message->update([
                'status' => EmailMessageStatus::Bounced->value,
                'bounced_at' => now(),
                'error' => $request->string('Description')->value(),
            ]),
            'SpamComplaint' => $message->update([
                'status' => EmailMessageStatus::SpamComplaint->value,
                'error' => 'Spam complaint received',
            ]),
            default => null,
        };

        return response()->json(['status' => 'ok']);
    }
}
