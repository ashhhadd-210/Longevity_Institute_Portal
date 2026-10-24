<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;

/**
 * Signing in with a password is a confirmation of that password.
 *
 * Without this, a Founder setting up two step verification straight after
 * signing in was sent to a second "confirm your password" page before the
 * setup could be saved (October 2026). The usual confirmation window still
 * applies, so sensitive actions later in a session ask again.
 */
class TreatSignInAsPasswordConfirmation
{
    public function handle(Login $event): void
    {
        if (app()->runningInConsole() && ! app()->runningUnitTests()) {
            return;
        }

        request()->session()->put('auth.password_confirmed_at', time());
    }
}
