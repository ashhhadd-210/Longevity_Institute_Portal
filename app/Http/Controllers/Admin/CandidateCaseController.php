<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CandidateCase;
use App\Services\Admission\CandidateCaseService;
use App\Services\Admission\CaseHistoryService;
use App\Services\Admission\ReviewService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * One person, everything the Institute knows about how they arrived, and every
 * action available.
 *
 * Both submissions appear here when somebody reached the Institute twice, since
 * each carries something the other lacks, but there is one history and one set
 * of actions, because there is one person and one judgement to make.
 */
class CandidateCaseController extends Controller
{
    public function __construct(
        private readonly CandidateCaseService $cases,
        private readonly CaseHistoryService $history,
        private readonly ReviewService $review,
    ) {}

    public function show(CandidateCase $case): View
    {
        $case->load([
            'applications.informationRequests',
            'referrals.informationRequests',
            'referrals.referrer.user',
            'referrals.relationship',
            'decisions.decidedBy',
            'invitations.issuedBy',
        ]);

        $openSubjects = $case->applications
            ->filter(fn ($application): bool => $application->status->isOpen())
            ->concat($case->referrals->filter(fn ($referral): bool => $referral->status->isOpen()));

        $approvedSubject = $case->applications
            ->concat($case->referrals)
            ->first(fn ($subject): bool => $this->review->isInvitable($subject));

        return view('admin.cases.show', [
            'case' => $case,
            'history' => $this->history->for($case),
            'duplicates' => $this->cases->possibleDuplicatesFor($case),
            'openSubjects' => $openSubjects,
            'approvedSubject' => $approvedSubject,
            'blockingInvitation' => $this->cases->invitationBlocking($case),
        ]);
    }

    /**
     * Join two cases on the Institute's judgement. Never automatic, and never
     * offered for a case that has already been decided.
     */
    public function join(Request $request, CandidateCase $case): RedirectResponse
    {
        $validated = $request->validate([
            'absorbed_case_id' => ['required', 'integer', 'exists:candidate_cases,id'],
        ]);

        $absorbed = CandidateCase::query()->findOrFail($validated['absorbed_case_id']);

        try {
            $this->cases->join($absorbed, $case, $request->user());
        } catch (DomainException $e) {
            return back()->withErrors(['join' => $e->getMessage()]);
        }

        return redirect()
            ->route('admin.cases.show', $case)
            ->with('status', 'The two records are now treated as one person.');
    }
}
