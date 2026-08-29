<?php

namespace App\Support;

/**
 * The capacity in which someone is being contacted about a case.
 *
 * Candidate is deliberately absent from the referral pathway: the Portal never
 * contacts a referred professional before the Institute approves them (agreed
 * 18 August 2026). Until approval they are being discussed, not dealt with.
 */
enum Capacity: string
{
    case Applicant = 'applicant';
    case Referrer = 'referrer';
    case InvitedPerson = 'invited_person';
}
