<?php

namespace App\Enums;

enum ReferralStatus: string
{
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case AwaitingInformation = 'awaiting_information';
    case SetAside = 'set_aside';
    case Approved = 'approved';
    case Declined = 'declined';

    /**
     * Awaiting information and set aside are both open states. On the referral
     * pathway a question goes to the referrer, never to the candidate, who has
     * not yet been contacted at all.
     */
    public function isOpen(): bool
    {
        return in_array($this, [self::Submitted, self::UnderReview, self::AwaitingInformation, self::SetAside], true);
    }

    public function isDecided(): bool
    {
        return in_array($this, [self::Approved, self::Declined], true);
    }

    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'Submitted',
            self::UnderReview => 'Under review',
            self::AwaitingInformation => 'Awaiting information',
            self::SetAside => 'Set aside',
            self::Approved => 'Approved',
            self::Declined => 'Declined',
        };
    }
}
