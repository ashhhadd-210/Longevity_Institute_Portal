<?php

use App\Http\Controllers\Admin\DashboardController;
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
    });
