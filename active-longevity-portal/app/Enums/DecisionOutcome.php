<?php

namespace App\Enums;

enum DecisionOutcome: string
{
    case Approved = 'approved';
    case Declined = 'declined';
}
