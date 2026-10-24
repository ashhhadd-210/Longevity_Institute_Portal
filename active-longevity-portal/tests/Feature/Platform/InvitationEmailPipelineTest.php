<?php

namespace Tests\Feature\Platform;

use App\Enums\InvitationStatus;
use App\Events\InvitationIssued;
use App\Listeners\SendInvitationEmail;
use App\Mail\InvitationMail;
use App\Models\EmailMessage;
use App\Models\Invitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * The email rails (doc 07 §3): every outbound message is logged before it is
 * handed to the mailer, and the invitation records its send.
 */
class InvitationEmailPipelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_listener_logs_the_message_and_sends_the_invitation_mail(): void
    {
        Mail::fake();
        $invitation = Invitation::factory()->create();

        app(SendInvitationEmail::class)->handle(
            new InvitationIssued($invitation, 'raw-token-value')
        );

        $log = EmailMessage::query()->where('related_type', 'invitation')
            ->where('related_id', $invitation->id)
            ->first();

        $this->assertNotNull($log);
        $this->assertSame('invitation', $log->message_type);
        $this->assertSame($invitation->email, $log->to_email);

        Mail::assertSent(InvitationMail::class, function (InvitationMail $mail) use ($invitation): bool {
            return $mail->invitation->is($invitation)
                && str_contains($mail->acceptanceUrl, 'raw-token-value')
                && $mail->hasTo($invitation->email);
        });

        $fresh = $invitation->fresh();
        $this->assertSame(InvitationStatus::Sent, $fresh->status);
        $this->assertSame(1, $fresh->send_count);
        $this->assertNotNull($fresh->last_sent_at);
    }
}
