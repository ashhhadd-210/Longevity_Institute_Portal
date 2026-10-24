<?php

namespace App\Enums;

/**
 * How a candidate entered the Institute's pipeline. Provenance is part of the
 * domain, not incidental application data.
 *
 * Direct is derived, never stored: a decision with a NULL subject is a direct
 * invitation (see AdmissionDecision::subjectType()).
 */
enum AdmissionSubjectType: string
{
    case Application = 'application';
    case Referral = 'referral';
    case Direct = 'direct';
}
