<?php

namespace App\Http\Controllers;

use App\Enums\ReferralStatus;
use App\Http\Requests\StoreReferralRequest;
use App\Models\ExpertiseArea;
use App\Models\Referral;
use App\Models\ReferralRelationship;
use App\Services\Admission\CandidateCaseService;
use App\Services\Governance\AuditRecorder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * A collaborator recommending someone to the Institute.
 *
 * The referred professional is not contacted by anything here, and will not be
 * until the Institute approves them (agreed 18 August 2026). Until then they
 * are being discussed, not dealt with, and they may well not know that the
 * conversation is happening at all.
 */
class ReferralController extends Controller
{
    public function __construct(
        private readonly CandidateCaseService $cases,
        private readonly AuditRecorder $audit,
    ) {}

    public function create(): View
    {
        return view('collaborator.refer', [
            'relationships' => ReferralRelationship::query()->selectable()->get(),
            'expertiseAreas' => ExpertiseArea::query()
                ->where('active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(StoreReferralRequest $request): RedirectResponse
    {
        $collaborator = $request->user()->collaborator;

        $referral = DB::transaction(function () use ($request, $collaborator): Referral {
            $referral = Referral::query()->create([
                ...$request->safe()->except('candidate_expertise'),
                'candidate_expertise' => $request->safe()->array('candidate_expertise'),
                'referrer_collaborator_id' => $collaborator->id,
                'status' => ReferralStatus::Submitted->value,
            ]);

            $this->cases->openFor($referral);

            $this->audit->record('admission.referral.submitted', $request->user(), $referral, [
                'candidate_email' => $referral->candidate_email,
                'relationship' => $referral->relationship?->key,
            ]);

            return $referral;
        });

        return redirect()
            ->route('referrals.received')
            ->with('candidate_name', $referral->candidate_first_name);
    }

    public function received(): View
    {
        return view('collaborator.refer-received', [
            'candidateName' => session('candidate_name'),
        ]);
    }
}
