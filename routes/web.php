<?php

use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\InvitationAcceptanceController;
use App\Http\Controllers\MfaSetupController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\Webhooks\PostmarkWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

/*
|--------------------------------------------------------------------------
| Public admission routes
|--------------------------------------------------------------------------
|
| Unauthenticated by necessity. An applicant has no account and, if the
| Institute decides against them, never will.
*/

Route::get('/apply', [ApplicationController::class, 'create'])->name('applications.create');
Route::post('/apply', [ApplicationController::class, 'store'])
    ->middleware('throttle:10,60')
    ->name('applications.store');
Route::get('/apply/received', [ApplicationController::class, 'received'])->name('applications.received');

/*
| The invitation link.
|
| GET must never consume the token. LinkedIn and Outlook fetch links to scan
| them before a human sees them, so a token spent on sight would be spent by a
| machine (agreed 18 August 2026). Only the POST below spends it.
*/
Route::get('/invitations/{token}', [InvitationAcceptanceController::class, 'show'])
    ->name('invitations.show');
Route::post('/invitations/{token}', [InvitationAcceptanceController::class, 'accept'])
    ->middleware('throttle:10,60')
    ->name('invitations.accept');

/*
|--------------------------------------------------------------------------
| Signed-in routes
|--------------------------------------------------------------------------
*/

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

        // Referring a professional is a collaborator's act, not the public's.
        Route::get('/refer', [ReferralController::class, 'create'])->name('referrals.create');
        Route::post('/refer', [ReferralController::class, 'store'])->name('referrals.store');
        Route::get('/refer/received', [ReferralController::class, 'received'])->name('referrals.received');
    });
});

Route::post('/webhooks/postmark', PostmarkWebhookController::class)
    ->name('webhooks.postmark');

require __DIR__.'/admin.php';
