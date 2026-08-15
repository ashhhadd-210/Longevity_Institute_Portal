<?php

namespace App\Enums;

/**
 * Administrative override of computed directory eligibility, in either
 * direction. Every change of override is audited.
 */
enum VisibilityOverride: string
{
    case ForceHidden = 'force_hidden';
    case ForceVisible = 'force_visible';
}
