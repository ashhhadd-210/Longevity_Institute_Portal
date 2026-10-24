<?php

namespace App\Services\Admission;

use App\Enums\ApplicationStatus;
use App\Enums\DecisionOutcome;
use App\Enums\ReferralStatus;
use App\Enums\ReviewAction;
use App\Events\InformationRequested;
use App\Models\AdmissionDecision;
use App\Models\Application;
use App\Models\InformationRequest;
use App\Models\Referral;
use App\Models\User;
use App\Services\Governance\AuditRecorder;
use App\Support\Capacity;
use App\Support\ContactPoint;
use DomainException;
use Illuminate\Support\Facades\DB;

/**
 * The four review actions (agreed with the Institute, 18 and 25 August 2026).
 *
 * Two of them are judgments and write an AdmissionDecision. The other two do
 * not, and the distinction is enforced here rather than trusted to the
 * interface: a candidate who is questioned twice and then approved must appear
 * in the record as one decision, not three.
 *
 * Issuing an invitation is deliberately not among these methods. It is a
 * consequence of approval and belongs to InvitationService, so that no screen
 * can ever present it as a fifth way of judging someone.
 */
class ReviewService
{
    public function __construct(private readonly AuditRecorder $audit) {}

    /**
     * A final judgment in the candidate's favor. It records the decision and
     * nothing else: it does not invite anybody. That remains a separate,
     * deliberate act.
     */
    public function approve(Application|Referral $case, User $actor, ?string $rationale = null): AdmissionDecision
    {
        $this->guardOpen($case, ReviewAction::Approve);

        return DB::transaction(function () use ($case, $actor, $rationale): AdmissionDecision {
            $decision = $this->recordDecision($case, $actor, DecisionOutcome::Approved, $rationale);

            $this->setStatus($case, 'approved');

            $this->audit->record('admission.review.approved', $actor, $case, [
                'decision_id' => $decision->id,
                'candidate_case_id' => $case->candidate_case_id,
            ]);

            return $decision;
        });
    }

    /**
     * A final judgment against, recorded with reasoning.
     *
     * Silent by default on both pathways. A referrer is never told that someone
     * they recommended was declined, and no message goes anywhere unless the
     * Institute separately chooses to send a courteous reply to an applicant.
     * That choice is hers to make afterwards, not a consequence of this call.
     */
    public function decline(Application|Referral $case, User $actor, string $rationale): AdmissionDecision
    {
        $this->guardOpen($case, ReviewAction::Decline);

        if (trim($rationale) === '') {
            throw new DomainException('A decline is a permanent judgment and must be recorded with its reasoning.');
        }

        return DB::transaction(function () use ($case, $actor, $rationale): AdmissionDecision {
            $decision = $this->recordDecision($case, $actor, DecisionOutcome::Declined, $rationale);

            $this->setStatus($case, 'declined');

            $this->audit->record('admission.review.declined', $actor, $case, [
                'decision_id' => $decision->id,
                'candidate_case_id' => $case->candidate_case_id,
            ]);

            return $decision;
        });
    }

    /**
     * A question, not a decision. The case stays open and this may be asked
     * more than once.
     *
     * On a referral the question goes to the referrer. The candidate is not
     * written to, because the Institute has not yet decided anything about them
     * and they may not know they were recommended at all.
     */
    public function requestInformation(Application|Referral $case, User $actor, string $required): InformationRequest
    {
        $this->guardOpen($case, ReviewAction::RequestInformation);

        if (trim($required) === '') {
            throw new DomainException('A request for more information must say what is being asked for.');
        }

        $recipient = $this->informationRecipient($case);

        if ($recipient === null) {
            throw new DomainException('There is nobody the Portal may write to about this case.');
        }

        $request = DB::transaction(function () use ($case, $actor, $required, $recipient): InformationRequest {
            $request = $case->informationRequests()->create([
                'requested_by' => $actor->id,
                'required' => $required,
                'requested_at' => now(),
            ]);

            $this->setStatus($case, 'awaiting_information');

            $this->audit->record('admission.review.information_requested', $actor, $case, [
                'information_request_id' => $request->id,
                'recipient_capacity' => $recipient->capacity->value,
            ]);

            return $request;
        });

        InformationRequested::dispatch($request, $recipient);

        return $request;
    }

    /**
     * A silent internal pause. Nothing is sent, nothing is judged, and the case
     * stays in the queue where it can be seen: a case that vanishes when paused
     * is a case that gets forgotten.
     */
    public function setAside(Application|Referral $case, User $actor, ?string $reason = null): void
    {
        $this->guardOpen($case, ReviewAction::SetAside);

        DB::transaction(function () use ($case, $actor, $reason): void {
            $case->forceFill([
                'set_aside_at' => now(),
                'set_aside_reason' => $reason,
                'set_aside_by' => $actor->id,
            ]);

            $this->setStatus($case, 'set_aside');

            $this->audit->record('admission.review.set_aside', $actor, $case, [
                'reason' => $reason,
            ]);
        });
    }

    /**
     * Lift a pause and return the case to active consideration. Not one of the
     * four actions: it is the undoing of one, and it is recorded as such.
     */
    public function resume(Application|Referral $case, User $actor): void
    {
        if ($this->statusValue($case) !== 'set_aside') {
            throw new DomainException('Only a case that was set aside can be returned to the queue.');
        }

        DB::transaction(function () use ($case, $actor): void {
            $case->forceFill([
                'set_aside_at' => null,
                'set_aside_reason' => null,
                'set_aside_by' => null,
            ]);

            $this->setStatus($case, 'under_review');

            $this->audit->record('admission.review.resumed', $actor, $case);
        });
    }

    /**
     * Record what came back. The case returns to active review, and the
     * question and its answer both remain in the history: the Institute should
     * be able to see what was asked, not merely that something was.
     */
    public function recordInformationResponse(InformationRequest $request, User $actor, string $response): void
    {
        if (! $request->isOutstanding()) {
            throw new DomainException('This request has already been answered.');
        }

        DB::transaction(function () use ($request, $actor, $response): void {
            $request->update([
                'response' => $response,
                'responded_at' => now(),
            ]);

            $case = $request->subject;

            if ($case instanceof Application || $case instanceof Referral) {
                // Only lift the waiting state once nothing else is outstanding.
                $stillWaiting = $case->informationRequests()->whereNull('responded_at')->exists();

                if (! $stillWaiting && $this->statusValue($case) === 'awaiting_information') {
                    $this->setStatus($case, 'under_review');
                }
            }

            $this->audit->record('admission.review.information_received', $actor, $request);
        });
    }

    /**
     * Who, if anyone, the Portal may write to about this case.
     *
     * The referral answer is the whole point: it is the referrer, and it is
     * never the candidate. Returning a value rather than leaving each template
     * to decide is what makes the rule testable.
     */
    public function informationRecipient(Application|Referral $case): ?ContactPoint
    {
        if ($case instanceof Application) {
            return new ContactPoint(
                email: $case->email,
                salutationName: $case->first_name,
                capacity: Capacity::Applicant,
            );
        }

        $referrer = $case->referrer?->user;

        if ($referrer === null) {
            return null;
        }

        return new ContactPoint(
            email: $referrer->email,
            salutationName: $referrer->salutationName(),
            capacity: Capacity::Referrer,
        );
    }

    /**
     * Whether an approved case may now be turned into an invitation. Approval
     * enables an invitation; it does not perform one.
     */
    public function isInvitable(Application|Referral $case): bool
    {
        return $this->statusValue($case) === 'approved';
    }

    private function recordDecision(
        Application|Referral $case,
        User $actor,
        DecisionOutcome $outcome,
        ?string $rationale,
    ): AdmissionDecision {
        return AdmissionDecision::query()->create([
            'subject_type' => $case->getMorphClass(),
            'subject_id' => $case->getKey(),
            'candidate_case_id' => $case->candidate_case_id,
            'decision' => $outcome->value,
            'rationale' => $rationale,
            'decided_by' => $actor->id,
            'decided_at' => now(),
        ]);
    }

    /**
     * Refuse to act on a case that has already been judged. A decline is final;
     * reopening one would quietly overwrite a judgment somebody made.
     */
    private function guardOpen(Application|Referral $case, ReviewAction $action): void
    {
        $status = $case->status;

        if ($status->isDecided()) {
            throw new DomainException(sprintf(
                'This case was already %s and cannot be %s again.',
                $status->value,
                $action->label(),
            ));
        }
    }

    private function setStatus(Application|Referral $case, string $status): void
    {
        $case->status = $case instanceof Application
            ? ApplicationStatus::from($status)
            : ReferralStatus::from($status);

        $case->save();
    }

    private function statusValue(Application|Referral $case): string
    {
        return $case->status->value;
    }
}
