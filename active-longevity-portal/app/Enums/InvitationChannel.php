<?php

namespace App\Enums;

/**
 * How an invitation reaches the person it is meant for (agreed 18 and 25
 * August 2026).
 *
 * The distinction exists for honesty rather than mechanics. Every protection —
 * single use, expiry, revocation, the token surviving a link scanner — holds
 * identically either way. What differs is what the Institute can truthfully
 * observe: an emailed invitation can be tracked as sent, delivered or bounced,
 * whereas one the Founder pastes into LinkedIn herself can only ever be known
 * to have been issued, and later accepted.
 *
 * The Portal must never imply a delivery it did not perform.
 */
enum InvitationChannel: string
{
    case Email = 'email';
    case CopyLink = 'copy_link';

    /** Whether delivery of this invitation is observable by the Portal. */
    public function isTrackable(): bool
    {
        return $this === self::Email;
    }

    public function label(): string
    {
        return match ($this) {
            self::Email => 'Sent by the Portal',
            self::CopyLink => 'Link copied and sent personally',
        };
    }
}
