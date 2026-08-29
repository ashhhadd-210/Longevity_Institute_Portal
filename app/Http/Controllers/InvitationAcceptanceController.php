<?php

namespace App\Http\Controllers;

use App\Http\Requests\AcceptInvitationRequest;
use App\Services\Admission\InvitationService;
use App\Services\Admission\RegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Where an invitation link leads.
 *
 * Two rules govern this controller and both are easy to break by accident.
 *
 * First, opening the link changes nothing. LinkedIn and Outlook fetch links to
 * scan them before a human ever sees them, so a token consumed on GET would be
 * spent by a machine and the real recipient would meet a dead link. Only the
 * completed registration below spends it.
 *
 * Second, a link that no longer works is met with courtesy rather than an
 * error. Arriving at a lapsed invitation is not a mistake the visitor made, and
 * they should be told plainly what happened and what to do next.
 */
class InvitationAcceptanceController extends Controller
{
    public function __construct(
        private readonly InvitationService $invitations,
        private readonly RegistrationService $registrations,
    ) {}

    public function show(string $token): View
    {
        $invitation = $this->invitations->findByToken($token);

        // An unrecognised token and a lapsed one are shown the same page. There
        // is no reason to help anybody distinguish a wrong guess from a real
        // invitation that has expired.
        if ($invitation === null) {
            return view('public.invitation-unavailable', ['reason' => 'unknown']);
        }

        if (! $invitation->isAcceptable()) {
            return view('public.invitation-unavailable', [
                'reason' => $invitation->unacceptableReason(),
            ]);
        }

        return view('public.invitation-accept', [
            'invitation' => $invitation,
            'token' => $token,
            'referrerName' => $invitation->referrer?->user?->fullName(),
        ]);
    }

    public function accept(AcceptInvitationRequest $request, string $token): RedirectResponse
    {
        $invitation = $this->invitations->findAcceptable($token);

        if ($invitation === null) {
            return redirect()->route('invitations.show', ['token' => $token]);
        }

        $collaborator = $this->registrations->register($invitation, [
            ...$request->safe()->only([
                'first_name', 'preferred_name', 'middle_name', 'last_name', 'email', 'password',
            ]),
            'privacy_consent' => true,
            'ip_address' => $request->ip(),
        ]);

        Auth::login($collaborator->user);
        $request->session()->regenerate();

        return redirect()->route('home')->with('just_admitted', true);
    }
}
