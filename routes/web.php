<?php

use App\Http\Controllers\MfaSetupController;
use App\Http\Controllers\Webhooks\PostmarkWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

Route::middleware(['auth', 'account.active'])->group(function (): void {
    // MFA enrollment for admin-tier users. Deliberately outside the
    // mfa.enrolled wall: it is the page the wall redirects to.
    Route::get('/security/mfa', [MfaSetupController::class, 'show'])->name('mfa.setup');

    Route::get('/home', fn () => view('home'))->name('home');
});

Route::post('/webhooks/postmark', PostmarkWebhookController::class)
    ->name('webhooks.postmark');

require __DIR__.'/admin.php';
