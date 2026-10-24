<?php

namespace App\Enums;

enum InvitationStatus: string
{
    case Issued = 'issued';
    case Sent = 'sent';
    case Accepted = 'accepted';
    case Expired = 'expired';
    case Revoked = 'revoked';

    public function isAcceptable(): bool
    {
        return in_array($this, [self::Issued, self::Sent], true);
    }
}
