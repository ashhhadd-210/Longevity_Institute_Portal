<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;

/**
 * Two step verification setup, the page the MFA wall sends people to.
 *
 * Somebody whose role requires two step verification and who has not set it
 * up yet is shown the QR code straight away. They have just signed in with
 * their password, so a "Begin" button followed by a second password prompt
 * adds nothing but a page with no QR code on it, which is where the Founder
 * was left in October 2026.
 */
class MfaSetupController extends Controller
{
    public function show(Request $request, EnableTwoFactorAuthentication $enable): View
    {
        $user = $request->user();

        if ($user->requiresMfa() && ! $user->hasConfirmedMfa() && $user->two_factor_secret === null) {
            $enable($user);
            $user->refresh();
        }

        return view('auth.two-factor-setup', [
            'enabled' => $user->two_factor_secret !== null,
            'confirmed' => $user->hasConfirmedMfa(),
            'required' => $user->requiresMfa(),
        ]);
    }
}
