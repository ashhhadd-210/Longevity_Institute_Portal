<?php

namespace App\Providers;

use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\FailedPasswordResetLinkRequestResponse;
use Laravel\Fortify\Contracts\SuccessfulPasswordResetLinkRequestResponse;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Account enumeration protection (doc 08 §1, threat 2): requesting a
        // password reset responds identically whether or not the address
        // exists. The membership list is confidential.
        $this->app->singleton(SuccessfulPasswordResetLinkRequestResponse::class, fn () => new class implements SuccessfulPasswordResetLinkRequestResponse
        {
            public function toResponse($request): RedirectResponse
            {
                return back()->with('status', __('If that address belongs to an account, a password reset link has been sent to it.'));
            }
        });

        $this->app->singleton(FailedPasswordResetLinkRequestResponse::class, fn () => new class implements FailedPasswordResetLinkRequestResponse
        {
            public function toResponse($request): RedirectResponse
            {
                return back()->with('status', __('If that address belongs to an account, a password reset link has been sent to it.'));
            }
        });
    }

    public function boot(): void
    {
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);

        Fortify::loginView(fn () => view('auth.login'));
        Fortify::twoFactorChallengeView(fn () => view('auth.two-factor-challenge'));
        Fortify::requestPasswordResetLinkView(fn () => view('auth.forgot-password'));
        Fortify::resetPasswordView(fn (Request $request) => view('auth.reset-password', ['request' => $request]));
        Fortify::confirmPasswordView(fn () => view('auth.confirm-password'));

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(
                Str::lower((string) $request->input(Fortify::username())).'|'.$request->ip()
            );

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        // Suspended or deactivated accounts cannot authenticate, and the
        // failure is indistinguishable from wrong credentials.
        Fortify::authenticateUsing(function (Request $request): ?User {
            $user = User::query()->where('email', $request->string('email')->value())->first();

            if ($user === null
                || ! $user->account_state->canAuthenticate()
                || ! Hash::check($request->string('password')->value(), $user->password)) {
                return null;
            }

            return $user;
        });
    }
}
