<?php

namespace App\Http\Controllers\Admin;

use App\Enums\InvitationChannel;
use App\Http\Controllers\Controller;
use App\Models\CandidateCase;
use App\Models\Invitation;
use App\Services\Admission\InvitationService;
use App\Services\Admission\ReviewService;
use App\Support\FieldLimits;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Creating and managing invitations.
 *
 * The form is deliberately tiny. A name is all that is required, because many
 * of these relationships begin on LinkedIn where the Institute has no email
 * address and inventing one would be worse than having none. Everything else
 * the invited person supplies themselves, about their own life, at registration.
 *
 * The link appears exactly once, on the page that follows. Only its hash is
 * stored, so there is no page anywhere that can show it again. Losing it is not
 * a problem: reissuing is one click and retires the old link at the same moment.
 */
class InvitationController extends Controller
{
    public function __construct(
        private readonly InvitationService $invitations,
        private readonly ReviewService $review,
    ) {}

    /**
     * Invite somebody the Institute has already approved.
     */
    public function createForCase(CandidateCase $case): View
    {
        $case->load(['applications', 'referrals.referrer.user', 'referrals.relationship']);

        $approved = $case->applications
            ->concat($case->referrals)
            ->first(fn ($subject): bool => $this->review->isInvitable($subject));

        abort_if($approved === null, 403, 'This person has not been approved yet.');

        $referral = $case->referrals->first(fn ($r): bool => $r->referrer !== null);

        return view('admin.invitations.create', [
            'case' => $case,
            'subject' => $approved,
            'suggestedEmail' => $case->email,
            'suggestedName' => $case->display_name,
            'referral' => $referral,
        ]);
    }

    /**
     * Invite somebody directly, typing their name from scratch. This is the
     * LinkedIn route, and the reason the form asks for so little.
     */
    public function createDirect(): View
    {
        return view('admin.invitations.create', [
            'case' => null,
            'subject' => null,
            'suggestedEmail' => null,
            'suggestedName' => null,
            'referral' => null,
        ]);
    }

    public function store(Request $request, ?CandidateCase $case = null): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:'.FieldLimits::NAME],
            'preferred_name' => ['nullable', 'string', 'max:'.FieldLimits::NAME],
            'middle_name' => ['nullable', 'string', 'max:'.FieldLimits::NAME],
            'last_name' => ['required', 'string', 'max:'.FieldLimits::NAME],
            'delivery_channel' => ['required', 'in:email,copy_link'],
            'email' => ['nullable', 'string', 'email:rfc', 'max:255', 'required_if:delivery_channel,email'],
            'personal_message' => ['nullable', 'string', 'max:2000'],
        ], [
            'email.required_if' => 'To send the invitation by email the Portal needs an address. Choose the copy-link option if you would rather send it yourself.',
        ]);

        $channel = InvitationChannel::from($validated['delivery_channel']);

        $decision = null;
        $referrer = null;

        if ($case !== null) {
            $case->load(['applications', 'referrals.referrer', 'decisions']);

            $approved = $case->applications
                ->concat($case->referrals)
                ->first(fn ($subject): bool => $this->review->isInvitable($subject));

            abort_if($approved === null, 403, 'This person has not been approved yet.');

            $decision = $approved->decision;
            $referrer = $case->referrals->first(fn ($r): bool => $r->referrer !== null)?->referrer;
        }

        try {
            $issued = $this->invitations->issue(
                issuedBy: $request->user(),
                firstName: $validated['first_name'],
                lastName: $validated['last_name'],
                email: $validated['email'] ?? null,
                channel: $channel,
                preferredName: $validated['preferred_name'] ?? null,
                middleName: $validated['middle_name'] ?? null,
                decision: $decision,
                case: $case,
                referrer: $referrer,
                personalMessage: $validated['personal_message'] ?? null,
            );
        } catch (DomainException $e) {
            return back()->withErrors(['invitation' => $e->getMessage()])->withInput();
        }

        // The one and only moment the usable link exists. Flashed, shown once,
        // and never persisted anywhere in readable form.
        return redirect()
            ->route('admin.invitations.issued', $issued->invitation)
            ->with('invitation_link', $issued->acceptanceUrl);
    }

    /**
     * The page that shows the link, once.
     */
    public function issued(Invitation $invitation): View
    {
        return view('admin.invitations.issued', [
            'invitation' => $invitation->load(['candidateCase', 'referrer.user']),
            'link' => session('invitation_link'),
        ]);
    }

    public function reissue(Request $request, Invitation $invitation): RedirectResponse
    {
        try {
            $issued = $this->invitations->reissue($invitation, $request->user());
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['invitation' => $e->getMessage()]);
        }

        return redirect()
            ->route('admin.invitations.issued', $invitation)
            ->with('invitation_link', $issued->acceptanceUrl)
            ->with('status', 'A new link has been created. The previous one no longer works.');
    }

    public function revoke(Request $request, Invitation $invitation): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $this->invitations->revoke($invitation, $request->user(), $validated['reason'] ?? null);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['invitation' => $e->getMessage()]);
        }

        $target = $invitation->candidate_case_id !== null
            ? redirect()->route('admin.cases.show', $invitation->candidate_case_id)
            : redirect()->route('admin.queue.index');

        return $target->with('status', 'The invitation has been withdrawn and its link no longer works.');
    }
}
