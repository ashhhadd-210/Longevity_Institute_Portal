<?php

namespace Tests\Feature\Admission;

use App\Mail\InformationRequestMail;
use App\Models\Application;
use App\Models\Collaborator;
use App\Models\Referral;
use App\Models\User;
use App\Services\Admission\ReviewService;
use App\Support\Capacity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Who the Portal may write to, and when (agreed 18 August 2026).
 *
 * The hard rule under test: the Portal never contacts a referred professional
 * before the Institute approves them. Until then they are being discussed, not
 * dealt with, and they may not know the conversation is happening at all.
 */
class ContactRulesTest extends TestCase
{
    use RefreshDatabase;

    private function service(): ReviewService
    {
        return app(ReviewService::class);
    }

    public function test_a_question_about_a_referral_goes_to_the_referrer_never_the_candidate(): void
    {
        Mail::fake();

        $referrerUser = User::factory()->create(['email' => 'referrer@example.org']);
        $collaborator = Collaborator::factory()->create(['user_id' => $referrerUser->id]);

        $referral = Referral::factory()->create([
            'referrer_collaborator_id' => $collaborator->id,
            'candidate_email' => 'candidate@example.org',
        ]);

        $recipient = $this->service()->informationRecipient($referral);

        $this->assertSame('referrer@example.org', $recipient->email);
        $this->assertSame(Capacity::Referrer, $recipient->capacity);

        $this->service()->requestInformation($referral, User::factory()->create(), 'How long have you known them?');

        Mail::assertSent(InformationRequestMail::class, fn (InformationRequestMail $mail): bool => $mail->hasTo('referrer@example.org'));

        Mail::assertNotSent(InformationRequestMail::class, fn (InformationRequestMail $mail): bool => $mail->hasTo('candidate@example.org'));
    }

    public function test_a_question_about_an_application_goes_to_the_applicant(): void
    {
        Mail::fake();

        $application = Application::factory()->create(['email' => 'applicant@example.org']);

        $recipient = $this->service()->informationRecipient($application);

        $this->assertSame('applicant@example.org', $recipient->email);
        $this->assertSame(Capacity::Applicant, $recipient->capacity);

        $this->service()->requestInformation($application, User::factory()->create(), 'Which institution?');

        Mail::assertSent(InformationRequestMail::class, fn (InformationRequestMail $mail): bool => $mail->hasTo('applicant@example.org'));
    }

    public function test_declining_a_referral_tells_nobody(): void
    {
        Mail::fake();

        $referral = Referral::factory()->create();

        $this->service()->decline($referral, User::factory()->create(), 'Not within scope.');

        Mail::assertNothingSent();
    }

    public function test_setting_a_case_aside_tells_nobody(): void
    {
        Mail::fake();

        $this->service()->setAside(Referral::factory()->create(), User::factory()->create(), 'Later.');
        $this->service()->setAside(Application::factory()->create(), User::factory()->create(), 'Later.');

        Mail::assertNothingSent();
    }

    /**
     * The referred person's own address is never used as a contact point at any
     * point in review, regardless of which action is taken.
     */
    public function test_the_candidate_address_is_never_a_contact_point_during_review(): void
    {
        $referral = Referral::factory()->create(['candidate_email' => 'candidate@example.org']);

        $recipient = $this->service()->informationRecipient($referral);

        $this->assertNotSame('candidate@example.org', $recipient?->email);
        $this->assertNotSame(Capacity::InvitedPerson, $recipient?->capacity);
    }
}
