<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * TOTP enrollment page. Fortify provides the endpoints (enable, confirm,
 * recovery codes); this page is the interface the MFA wall redirects to.
 */
class MfaSetupController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();

        return view('auth.two-factor-setup', [
            'enabled' => $user->two_factor_secret !== null,
            'confirmed' => $user->hasConfirmedMfa(),
            'required' => $user->requiresMfa(),
        ]);
    }
}
