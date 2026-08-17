<?php

use App\Http\Controllers\MfaSetupController;
use App\Http\Controllers\Webhooks\PostmarkWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

Route::middleware(['auth', 'account.active'])->group(function (): void {
    // MFA enrollment for admin-tier users. Deliberately outside the
    // mfa.enrolled wall: it is the page the wall redirects to.
    Route::get('/security/mfa', [MfaSetupController::class, 'show'])->name('mfa.setup');

    // Everything else behind the wall, so an administrator who has not yet
    // enrolled is taken to enrollment at sign-in rather than at the moment
    // they first happen to open an administrative page. There is then no
    // window in which an administrative session exists without its second
    // factor.
    Route::middleware('mfa.enrolled')->group(function (): void {
        Route::get('/home', fn () => view('home'))->name('home');
    });
});

Route::post('/webhooks/postmark', PostmarkWebhookController::class)
    ->name('webhooks.postmark');

require __DIR__.'/admin.php';
