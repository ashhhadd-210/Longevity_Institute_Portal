<?php

namespace App\Services\Admission;

use App\Enums\DecisionOutcome;
use App\Enums\InvitationChannel;
use App\Enums\InvitationStatus;
use App\Models\Application;
use App\Models\CandidateCase;
use App\Models\Referral;
use App\Support\HistoryEntry;
use Illuminate\Support\Collection;

/**
 * The admission history of one person, assembled from the records that
 * actually happened.
 *
 * Deliberately not a second log. Every entry here is derived from a submission,
 * a question, a decision or an invitation that exists in its own right, so the
 * history cannot drift away from the facts it describes, and no code path can
 * forget to write to it. A parallel timeline table would eventually disagree
 * with the records it was supposed to summarise.
 *
 * This is what makes "one case, one decision, one invitation, one admission
 * history" visible rather than merely true.
 */
class CaseHistoryService
{
    /**
     * @return Collection<int, HistoryEntry>
     */
    public function for(CandidateCase $case): Collection
    {
        $case->loadMissing([
            'applications.informationRequests.requestedBy',
            'applications.setAsideBy',
            'referrals.informationRequests.requestedBy',
            'referrals.setAsideBy',
            'referrals.referrer.user',
            'decisions.decidedBy',
            'invitations.issuedBy',
        ]);

        /** @var Collection<int, HistoryEntry> $entries */
        $entries = collect();

        foreach ($case->applications as $application) {
            $entries->push(new HistoryEntry(
                at: $application->submitted_at,
                summary: 'Applied through the Institute website',
                detail: $application->email,
            ));

            $this->pushQuestions($entries, $application, 'the applicant');
            $this->pushPause($entries, $application);
        }

        foreach ($case->referrals as $referral) {
            $referrerName = $referral->referrer?->user?->fullName();

            $entries->push(new HistoryEntry(
                at: $referral->created_at,
                summary: $referrerName === null
                    ? 'Referred by a collaborator'
                    : 'Referred by '.$referrerName,
                // The relationship is shown here because this history is seen
                // by the Institute alone. It never travels any further.
                detail: $referral->relationshipLabel(),
            ));

            $this->pushQuestions($entries, $referral, 'the referrer');
            $this->pushPause($entries, $referral);
        }

        foreach ($case->decisions as $decision) {
            $entries->push(new HistoryEntry(
                at: $decision->decided_at,
                summary: $decision->decision === DecisionOutcome::Approved
                    ? 'Approved for admission'
                    : 'Declined',
                detail: $decision->rationale,
                actor: $decision->decidedBy?->fullName(),
                isDecision: true,
            ));
        }

        foreach ($case->invitations as $invitation) {
            $entries->push(new HistoryEntry(
                at: $invitation->created_at,
                summary: $invitation->delivery_channel === InvitationChannel::CopyLink
                    ? 'Invitation created, link copied to send personally'
                    : 'Invitation sent by the Portal to '.$invitation->email,
                actor: $invitation->issuedBy?->fullName(),
            ));

            if ($invitation->accepted_at !== null) {
                $entries->push(new HistoryEntry(
                    at: $invitation->accepted_at,
                    summary: 'Registered and admitted as a collaborator',
                ));
            }

            if ($invitation->revoked_at !== null) {
                $entries->push(new HistoryEntry(
                    at: $invitation->revoked_at,
                    summary: 'Invitation withdrawn',
                ));
            }

            if ($invitation->status === InvitationStatus::Expired) {
                $entries->push(new HistoryEntry(
                    at: $invitation->expires_at,
                    summary: 'Invitation lapsed without being used',
                ));
            }
        }

        return $entries->sortBy(fn (HistoryEntry $entry): string => $entry->at->toIso8601String())->values();
    }

    /**
     * @param  Collection<int, HistoryEntry>  $entries
     */
    private function pushQuestions(Collection $entries, Application|Referral $case, string $recipient): void
    {
        foreach ($case->informationRequests as $request) {
            $entries->push(new HistoryEntry(
                at: $request->requested_at,
                summary: 'More information asked of '.$recipient,
                detail: $request->required,
                actor: $request->requestedBy?->fullName(),
            ));

            if ($request->responded_at !== null) {
                $entries->push(new HistoryEntry(
                    at: $request->responded_at,
                    summary: 'Reply received',
                    detail: $request->response,
                ));
            }
        }
    }

    /**
     * @param  Collection<int, HistoryEntry>  $entries
     */
    private function pushPause(Collection $entries, Application|Referral $case): void
    {
        if ($case->set_aside_at === null) {
            return;
        }

        $entries->push(new HistoryEntry(
            at: $case->set_aside_at,
            summary: 'Set aside',
            detail: $case->set_aside_reason,
            actor: $case->setAsideBy?->fullName(),
        ));
    }
}
