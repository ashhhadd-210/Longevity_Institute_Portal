<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Http\Requests\StoreApplicationRequest;
use App\Models\Application;
use App\Models\ExpertiseArea;
use App\Services\Admission\CandidateCaseService;
use App\Services\Governance\AuditRecorder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * The public application to join, reached from the Institute website.
 *
 * Unauthenticated by necessity: the applicant has no account and, if the
 * Institute decides against them, never will. Nothing here judges anybody or
 * sends anything. It records that somebody asked, and places them in the queue.
 */
class ApplicationController extends Controller
{
    public function __construct(
        private readonly CandidateCaseService $cases,
        private readonly AuditRecorder $audit,
    ) {}

    public function create(): View
    {
        return view('public.apply', [
            'expertiseAreas' => ExpertiseArea::query()
                ->where('active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(StoreApplicationRequest $request): RedirectResponse
    {
        $application = DB::transaction(function () use ($request): Application {
            $application = Application::query()->create([
                ...$request->safe()->except('expertise_claimed'),
                'expertise_claimed' => $request->safe()->array('expertise_claimed'),
                'status' => ApplicationStatus::Submitted->value,
                'submitted_at' => now(),
            ]);

            // Every submission opens its own case. Convergence with an existing
            // case is offered to a reviewer as a suggestion and confirmed by
            // hand: deciding that two records are one person is the Institute's
            // judgment, not the software's.
            $this->cases->openFor($application);

            $this->audit->record('admission.application.submitted', null, $application, [
                'email' => $application->email,
            ]);

            return $application;
        });

        return redirect()
            ->route('applications.received')
            ->with('applicant_name', $application->first_name);
    }

    public function received(): View
    {
        return view('public.apply-received', [
            'applicantName' => session('applicant_name'),
        ]);
    }
}
