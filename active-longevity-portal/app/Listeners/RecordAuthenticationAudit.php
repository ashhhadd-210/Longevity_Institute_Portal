<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\Governance\AuditRecorder;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;

/**
 * Every security-relevant authentication event enters the institutional
 * record with IP and user agent (doc 05 §5).
 */
class RecordAuthenticationAudit
{
    public function __construct(private readonly AuditRecorder $audit) {}

    /**
     * @return array<string, mixed>
     */
    private function requestContext(): array
    {
        return [
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ];
    }

    public function handleLogin(Login $event): void
    {
        if ($event->user instanceof User) {
            $this->audit->record('identity.login.succeeded', $event->user, $event->user, $this->requestContext());
        }
    }

    public function handleLogout(Logout $event): void
    {
        if ($event->user instanceof User) {
            $this->audit->record('identity.logout', $event->user, $event->user, $this->requestContext());
        }
    }

    public function handleFailed(Failed $event): void
    {
        $subject = $event->user instanceof User ? $event->user : null;

        $this->audit->record('identity.login.failed', null, $subject, $this->requestContext() + [
            'email' => (string) ($event->credentials['email'] ?? ''),
        ]);
    }

    public function handleLockout(Lockout $event): void
    {
        $this->audit->record('identity.login.lockout', null, null, $this->requestContext() + [
            'email' => (string) $event->request->input('email', ''),
        ]);
    }

    public function handlePasswordReset(PasswordReset $event): void
    {
        if ($event->user instanceof User) {
            $this->audit->record('identity.password.reset', $event->user, $event->user, $this->requestContext());
        }
    }
}
