<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The MFA wall (doc 05 §3): a user holding any role flagged requires_mfa
 * cannot reach a protected route until TOTP enrollment is confirmed. This is
 * a hard redirect to enrollment, not a suggestion.
 */
class EnsureMfaEnrolled
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user instanceof User && $user->requiresMfa() && ! $user->hasConfirmedMfa()) {
            return redirect()->route('mfa.setup');
        }

        return $next($request);
    }
}
