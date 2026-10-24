<?php

namespace Tests\Feature\Admission;

use App\Enums\ApplicationStatus;
use App\Enums\ReferralStatus;
use App\Enums\ReviewAction;
use App\Models\AdmissionDecision;
use App\Models\Application;
use App\Models\AuditEvent;
use App\Models\Referral;
use App\Models\User;
use App\Services\Admission\ReviewService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * The four review actions, and above all the distinction between the two that
 * are judgements and the two that are not (agreed 18 and 25 August 2026).
 */
class ReviewActionsTest extends TestCase
{
    use RefreshDatabase;

    private function service(): ReviewService
    {
        return app(ReviewService::class);
    }

    public function test_there_are_exactly_four_actions_and_only_two_are_decisions(): void
    {
        $this->assertCount(4, ReviewAction::cases());

        $decisions = array_filter(ReviewAction::cases(), fn (ReviewAction $a): bool => $a->isDecision());

        $this->assertEqualsCanonicalizing(
            [ReviewAction::Approve, ReviewAction::Decline],
            array_values($decisions),
        );
    }

    public function test_approving_records_a_decision_but_invites_nobody(): void
    {
        $application = Application::factory()->create();
        $actor = User::factory()->create();

        $decision = $this->service()->approve($application, $actor, 'A clear fit.');

        $this->assertSame(ApplicationStatus::Approved, $application->fresh()->status);
        $this->assertSame('approved', $decision->decision->value);

        // Approval enables an invitation. It does not perform one.
        $this->assertSame(0, $decision->invitation()->count());
        $this->assertTrue($this->service()->isInvitable($application->fresh()));
    }

    public function test_declining_requires_reasoning_and_is_permanent(): void
    {
        $application = Application::factory()->create();
        $actor = User::factory()->create();

        $this->service()->decline($application, $actor, 'Outside the Institute\'s scope.');

        $this->assertSame(ApplicationStatus::Declined, $application->fresh()->status);

        // Final means final: a decided case is not open to further action.
        $this->expectException(DomainException::class);
        $this->service()->approve($application->fresh(), $actor);
    }

    public function test_declining_without_reasoning_is_refused(): void
    {
        $application = Application::factory()->create();

        $this->expectException(DomainException::class);

        $this->service()->decline($application, User::factory()->create(), '   ');
    }

    /**
     * The rule that keeps the record honest: a candidate questioned twice and
     * then approved must appear as one decision, not three.
     */
    public function test_asking_for_information_is_not_a_decision_and_may_repeat(): void
    {
        Mail::fake();

        $application = Application::factory()->create();
        $actor = User::factory()->create();

        $this->service()->requestInformation($application, $actor, 'Which institution are you at now?');
        $this->service()->requestInformation($application->fresh(), $actor, 'And for how long?');

        $application->refresh();

        $this->assertSame(ApplicationStatus::AwaitingInformation, $application->status);
        $this->assertTrue($application->status->isOpen());
        $this->assertSame(2, $application->informationRequests()->count());
        $this->assertSame(0, AdmissionDecision::query()->count(), 'A question is not a judgement.');

        $this->service()->approve($application, $actor);

        // Two questions and one approval leave exactly one decision behind.
        $this->assertSame(1, AdmissionDecision::query()->count());
    }

    public function test_recording_a_reply_returns_the_case_to_active_review(): void
    {
        Mail::fake();

        $application = Application::factory()->create();
        $actor = User::factory()->create();

        $request = $this->service()->requestInformation($application, $actor, 'Which institution?');
        $this->service()->recordInformationResponse($request, $actor, 'Governors State University.');

        $this->assertSame(ApplicationStatus::UnderReview, $application->fresh()->status);
        $this->assertNotNull($request->fresh()->responded_at);
    }

    /**
     * Set aside is a pause, not a disposal. Nothing is decided, nothing is
     * sent, and the case stays where it can be seen.
     */
    public function test_setting_aside_decides_nothing_sends_nothing_and_stays_open(): void
    {
        Mail::fake();

        $referral = Referral::factory()->create();
        $actor = User::factory()->create();

        $this->service()->setAside($referral, $actor, 'Waiting until the taxonomy is settled.');

        $referral->refresh();

        $this->assertSame(ReferralStatus::SetAside, $referral->status);
        $this->assertTrue($referral->status->isOpen(), 'A set-aside case must remain in the queue.');
        $this->assertSame('Waiting until the taxonomy is settled.', $referral->set_aside_reason);
        $this->assertNotNull($referral->set_aside_at);

        // No judgement was recorded against this case. Counted against the
        // referral itself rather than the whole table, because the referrer's
        // own admission legitimately carries a decision of its own.
        $this->assertSame(0, $referral->decision()->count());
        Mail::assertNothingSent();
    }

    public function test_a_set_aside_case_can_be_returned_to_consideration(): void
    {
        $referral = Referral::factory()->create();
        $actor = User::factory()->create();

        $this->service()->setAside($referral, $actor, 'Later.');
        $this->service()->resume($referral->fresh(), $actor);

        $referral->refresh();

        $this->assertSame(ReferralStatus::UnderReview, $referral->status);
        $this->assertNull($referral->set_aside_at);
        $this->assertNull($referral->set_aside_reason);
    }

    public function test_every_action_leaves_its_own_trace_in_the_record(): void
    {
        Mail::fake();

        $actor = User::factory()->create();

        $this->service()->approve(Application::factory()->create(), $actor);
        $this->service()->decline(Application::factory()->create(), $actor, 'Not a fit.');
        $this->service()->requestInformation(Application::factory()->create(), $actor, 'A question.');
        $this->service()->setAside(Application::factory()->create(), $actor, 'Pausing.');

        foreach ([
            'admission.review.approved',
            'admission.review.declined',
            'admission.review.information_requested',
            'admission.review.set_aside',
        ] as $eventType) {
            $this->assertTrue(
                AuditEvent::query()->where('event_type', $eventType)->exists(),
                "The record should show a distinct {$eventType} event.",
            );
        }
    }
}
