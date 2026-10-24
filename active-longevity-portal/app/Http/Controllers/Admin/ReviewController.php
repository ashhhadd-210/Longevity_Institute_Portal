<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\InformationRequest;
use App\Models\Referral;
use App\Services\Admission\ReviewService;
use App\Support\FieldLimits;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * The four review actions, each on its own route so that the institutional
 * record shows precisely which one was taken.
 *
 * There is no fifth route for issuing an invitation. That is a consequence of
 * approval and lives elsewhere, so that no screen can present it as another way
 * of judging somebody.
 */
class ReviewController extends Controller
{
    public function __construct(private readonly ReviewService $review) {}

    public function approve(Request $request, string $type, int $id): RedirectResponse
    {
        $case = $this->resolve($type, $id);

        $validated = $request->validate([
            'rationale' => ['nullable', 'string', 'max:5000'],
        ]);

        return $this->attempt(function () use ($case, $request, $validated): string {
            $this->review->approve($case, $request->user(), $validated['rationale'] ?? null);

            return 'Approved. You can now issue an invitation when you are ready.';
        }, $case);
    }

    public function decline(Request $request, string $type, int $id): RedirectResponse
    {
        $case = $this->resolve($type, $id);

        $validated = $request->validate([
            'rationale' => ['required', 'string', 'min:3', 'max:5000'],
        ], [
            'rationale.required' => 'A decline is permanent, so please record why.',
        ]);

        return $this->attempt(function () use ($case, $request, $validated): string {
            $this->review->decline($case, $request->user(), $validated['rationale']);

            return 'Declined and recorded. Nobody has been contacted.';
        }, $case);
    }

    public function requestInformation(Request $request, string $type, int $id): RedirectResponse
    {
        $case = $this->resolve($type, $id);

        $validated = $request->validate([
            'required' => ['required', 'string', 'min:3', 'max:'.FieldLimits::NOTE],
        ], [
            'required.required' => 'Please say what you would like to know.',
        ]);

        return $this->attempt(function () use ($case, $request, $validated): string {
            $this->review->requestInformation($case, $request->user(), $validated['required']);

            return $case instanceof Referral
                ? 'Your question has gone to the referrer. The person they recommended has not been contacted.'
                : 'Your question has gone to the applicant.';
        }, $case);
    }

    public function setAside(Request $request, string $type, int $id): RedirectResponse
    {
        $case = $this->resolve($type, $id);

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:'.FieldLimits::NOTE],
        ]);

        return $this->attempt(function () use ($case, $request, $validated): string {
            $this->review->setAside($case, $request->user(), $validated['reason'] ?? null);

            return 'Set aside. Nothing has been sent, and it stays in the queue.';
        }, $case);
    }

    public function resume(Request $request, string $type, int $id): RedirectResponse
    {
        $case = $this->resolve($type, $id);

        return $this->attempt(function () use ($case, $request): string {
            $this->review->resume($case, $request->user());

            return 'Back under consideration.';
        }, $case);
    }

    public function recordResponse(Request $request, InformationRequest $informationRequest): RedirectResponse
    {
        $validated = $request->validate([
            'response' => ['required', 'string', 'max:5000'],
        ]);

        $subject = $informationRequest->subject;

        try {
            $this->review->recordInformationResponse($informationRequest, $request->user(), $validated['response']);
        } catch (DomainException $e) {
            return back()->withErrors(['response' => $e->getMessage()]);
        }

        return $this->backToCase($subject, 'Reply recorded.');
    }

    /**
     * @param  callable(): string  $action
     */
    private function attempt(callable $action, Application|Referral $case): RedirectResponse
    {
        try {
            $message = $action();
        } catch (DomainException $e) {
            return back()->withErrors(['review' => $e->getMessage()]);
        }

        return $this->backToCase($case, $message);
    }

    private function backToCase(mixed $case, string $message): RedirectResponse
    {
        $caseId = ($case instanceof Application || $case instanceof Referral)
            ? $case->candidate_case_id
            : null;

        if ($caseId === null) {
            return redirect()->route('admin.queue.index')->with('status', $message);
        }

        return redirect()->route('admin.cases.show', $caseId)->with('status', $message);
    }

    private function resolve(string $type, int $id): Application|Referral
    {
        return match ($type) {
            'application' => Application::query()->findOrFail($id),
            'referral' => Referral::query()->findOrFail($id),
            default => abort(404),
        };
    }
}
