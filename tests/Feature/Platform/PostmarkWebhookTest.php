<?php

namespace Tests\Feature\Platform;

use App\Enums\EmailMessageStatus;
use App\Models\EmailMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostmarkWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.postmark.webhook_token' => 'test-webhook-token']);
    }

    private function makeMessage(): EmailMessage
    {
        return EmailMessage::query()->create([
            'message_type' => 'invitation',
            'to_email' => 'candidate@example.org',
            'status' => EmailMessageStatus::Sent->value,
            'postmark_message_id' => 'pm-message-1',
            'queued_at' => now(),
            'sent_at' => now(),
        ]);
    }

    public function test_a_delivery_event_marks_the_message_delivered(): void
    {
        $message = $this->makeMessage();

        $this->postJson('/webhooks/postmark?token=test-webhook-token', [
            'RecordType' => 'Delivery',
            'MessageID' => 'pm-message-1',
        ])->assertOk();

        $this->assertSame(EmailMessageStatus::Delivered, $message->fresh()->status);
        $this->assertNotNull($message->fresh()->delivered_at);
    }

    public function test_a_bounce_event_marks_the_message_bounced_with_the_reason(): void
    {
        $message = $this->makeMessage();

        $this->postJson('/webhooks/postmark?token=test-webhook-token', [
            'RecordType' => 'Bounce',
            'MessageID' => 'pm-message-1',
            'Description' => 'The address does not exist.',
        ])->assertOk();

        $fresh = $message->fresh();
        $this->assertSame(EmailMessageStatus::Bounced, $fresh->status);
        $this->assertSame('The address does not exist.', $fresh->error);
    }

    public function test_a_wrong_token_is_rejected(): void
    {
        $this->postJson('/webhooks/postmark?token=wrong', [
            'RecordType' => 'Delivery',
            'MessageID' => 'pm-message-1',
        ])->assertForbidden();
    }

    public function test_an_unknown_message_id_is_acknowledged_without_error(): void
    {
        $this->postJson('/webhooks/postmark?token=test-webhook-token', [
            'RecordType' => 'Delivery',
            'MessageID' => 'never-seen',
        ])->assertOk()->assertJson(['status' => 'unknown-message']);
    }
}
