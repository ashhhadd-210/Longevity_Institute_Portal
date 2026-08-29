<?php

namespace App\Events;

use App\Models\InformationRequest;
use App\Support\ContactPoint;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Fired when the Institute asks a question about an open case.
 *
 * The recipient travels with the event rather than being worked out again by
 * the listener. On a referral that recipient is the referrer, and re-deriving
 * it downstream would be one more place the rule could be got wrong.
 */
class InformationRequested
{
    use Dispatchable;

    public function __construct(
        public readonly InformationRequest $request,
        public readonly ContactPoint $recipient,
    ) {}
}
