<?php

namespace App\Events;

use App\Models\Invitation;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Fired when an invitation is issued or re-sent. Side effects (the email)
 * subscribe to this event; the audit record is written synchronously by the
 * service (doc 01 §2).
 */
class InvitationIssued
{
    use Dispatchable;

    public function __construct(
        public readonly Invitation $invitation,
        public readonly string $rawToken,
        public readonly bool $isResend = false,
    ) {}
}
