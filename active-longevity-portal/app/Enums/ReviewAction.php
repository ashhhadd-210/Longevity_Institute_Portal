<?php

namespace App\Enums;

/**
 * The four actions available to a reviewer (agreed with the Institute,
 * 18 and 25 August 2026). Only two of them are decisions.
 *
 * RequestInformation asks a question and keeps the case open, and may be used
 * more than once. SetAside is a silent internal pause: the case stays in the
 * queue and nobody outside the Institute learns of it. Neither is a judgment,
 * and neither may be recorded as one — a candidate questioned twice and then
 * approved must not appear in the record to have been decided upon three times.
 *
 * Issuing an invitation is deliberately absent: it is a consequence of
 * approval, not a review action.
 */
enum ReviewAction: string
{
    case Approve = 'approve';
    case Decline = 'decline';
    case RequestInformation = 'request_information';
    case SetAside = 'set_aside';

    /**
     * Only Approve and Decline are final judgments that write an
     * AdmissionDecision.
     */
    public function isDecision(): bool
    {
        return in_array($this, [self::Approve, self::Decline], true);
    }

    /**
     * Whether taking this action causes the Portal to contact anybody at all.
     * Set Aside and Decline are silent on the referral pathway; the Portal
     * never contacts a referred professional before the Institute approves.
     */
    public function isSilent(): bool
    {
        return in_array($this, [self::SetAside, self::Decline], true);
    }

    public function label(): string
    {
        return match ($this) {
            self::Approve => 'Approve',
            self::Decline => 'Decline',
            self::RequestInformation => 'Need more information',
            self::SetAside => 'Set aside',
        };
    }
}
