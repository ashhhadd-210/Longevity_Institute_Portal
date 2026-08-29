<?php

use App\Http\Controllers\Admin\CandidateCaseController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InvitationController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\ReviewQueueController;
use Illuminate\Support\Facades\Route;

/**
 * Administrative routes: authenticated, active account, MFA enrollment wall,
 * then the data-driven permission gate (docs 05 §3, 06 §4). Order matters —
 * the wall runs before the permission check so an un-enrolled administrator
 * is sent to enrollment, not shown a 403.
 */
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'account.active', 'mfa.enrolled', 'can:platform.admin.access'])
    ->group(function (): void {
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::get('/queue', [ReviewQueueController::class, 'index'])->name('queue.index');

        Route::get('/cases/{case}', [CandidateCaseController::class, 'show'])->name('cases.show');
        Route::post('/cases/{case}/join', [CandidateCaseController::class, 'join'])->name('cases.join');

        /*
        | The four review actions, each on its own route so that the record
        | shows precisely which one was taken. Issuing an invitation is
        | deliberately not among them: it is a consequence of approval.
        */
        Route::prefix('review/{type}/{id}')->name('review.')->group(function (): void {
            Route::post('/approve', [ReviewController::class, 'approve'])->name('approve');
            Route::post('/decline', [ReviewController::class, 'decline'])->name('decline');
            Route::post('/information', [ReviewController::class, 'requestInformation'])->name('information');
            Route::post('/set-aside', [ReviewController::class, 'setAside'])->name('set-aside');
            Route::post('/resume', [ReviewController::class, 'resume'])->name('resume');
        });

        Route::post('/information-requests/{informationRequest}/response', [ReviewController::class, 'recordResponse'])
            ->name('information.response');

        /*
        | Invitations. The direct route exists because many of these
        | relationships begin on LinkedIn, where a name is all the Institute
        | has.
        */
        Route::get('/invitations/new', [InvitationController::class, 'createDirect'])->name('invitations.create');
        Route::post('/invitations', [InvitationController::class, 'store'])->name('invitations.store');
        Route::get('/cases/{case}/invite', [InvitationController::class, 'createForCase'])->name('invitations.create-for-case');
        Route::post('/cases/{case}/invite', [InvitationController::class, 'store'])->name('invitations.store-for-case');

        // The page that shows the link, once.
        Route::get('/invitations/{invitation}/issued', [InvitationController::class, 'issued'])->name('invitations.issued');
        Route::post('/invitations/{invitation}/reissue', [InvitationController::class, 'reissue'])->name('invitations.reissue');
        Route::post('/invitations/{invitation}/revoke', [InvitationController::class, 'revoke'])->name('invitations.revoke');
    });
