<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case Approved = 'approved';
    case Declined = 'declined';
    case Withdrawn = 'withdrawn';

    public function isOpen(): bool
    {
        return in_array($this, [self::Submitted, self::UnderReview], true);
    }
}
