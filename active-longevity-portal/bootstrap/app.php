<?php

use App\Http\Middleware\EnsureAccountIsActive;
use App\Http\Middleware\EnsureMfaEnrolled;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'account.active' => EnsureAccountIsActive::class,
            'mfa.enrolled' => EnsureMfaEnrolled::class,
        ]);

        // Postmark delivery webhooks authenticate with a shared token, not a
        // session (doc 07 §3); CSRF does not apply to server-to-server calls.
        $middleware->validateCsrfTokens(except: [
            'webhooks/postmark',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
