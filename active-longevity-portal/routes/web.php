<?php

use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\CollaborationInterestController;
use App\Http\Controllers\DirectoryController;
use App\Http\Controllers\ExpertiseSearchController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\InvitationAcceptanceController;
use App\Http\Controllers\MfaSetupController;
use App\Http\Controllers\ProfileClassificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProfileCredentialController;
use App\Http\Controllers\ProfilePhotoController;
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

        // Saying where your work belongs. The search endpoint is for admitted
        // collaborators only: the vocabulary is the Institute's, and there is
        // no reason to expose it to the open web.
        Route::get('/expertise/search', ExpertiseSearchController::class)->name('expertise.search');

        // The collaborator's own account of their work. Each section saves on
        // its own, because these are filled in over several sittings.
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::get('/profile/standards', [ProfileController::class, 'standards'])->name('profile.standards');
        Route::put('/profile/details', [ProfileController::class, 'updateDetails'])->name('profile.details');
        Route::put('/profile/name', [ProfileController::class, 'updateName'])->name('profile.name');
        Route::put('/profile/links', [ProfileController::class, 'updateLinks'])->name('profile.links');
        Route::put('/profile/positions', [ProfileController::class, 'updatePositions'])->name('profile.positions');

        // What the first Beta uses instead of messaging: a registered interest,
        // seen by the Institute and passed on.
        Route::post('/directory/{profile}/interest', [CollaborationInterestController::class, 'store'])->name('profile.interest');

        // Somewhere to say what you found. Needed before Alpha.
        Route::get('/feedback', [FeedbackController::class, 'create'])->name('feedback.create');
        Route::post('/feedback', [FeedbackController::class, 'store'])->name('feedback.store');
        Route::post('/profile/photo', [ProfilePhotoController::class, 'store'])->name('profile.photo');
        Route::delete('/profile/photo', [ProfilePhotoController::class, 'destroy'])->name('profile.photo.destroy');

        // Up to three credentials after the name, chosen from the approved
        // library rather than typed. Requests for what it lacks never block.
        Route::get('/credentials/search', [ProfileCredentialController::class, 'search'])->name('credentials.search');
        Route::put('/profile/credentials', [ProfileCredentialController::class, 'update'])->name('profile.credentials');
        Route::post('/profile/credentials/propose', [ProfileCredentialController::class, 'propose'])->name('profile.credentials.propose');

        // The community. Admitted is not the same as visible: a profile appears
        // once it carries enough for somebody else to recognize the person.
        Route::get('/directory', [DirectoryController::class, 'index'])->name('directory.index');
        Route::get('/directory/{profile}', [DirectoryController::class, 'show'])->name('profile.show');

        Route::get('/profile/classification', [ProfileClassificationController::class, 'edit'])
            ->name('profile.classification.edit');
        Route::put('/profile/classification', [ProfileClassificationController::class, 'update'])
            ->name('profile.classification.update');
        Route::post('/profile/classification/propose', [ProfileClassificationController::class, 'propose'])
            ->name('profile.classification.propose');
    });
});

Route::post('/webhooks/postmark', PostmarkWebhookController::class)
    ->name('webhooks.postmark');

require __DIR__.'/admin.php';
