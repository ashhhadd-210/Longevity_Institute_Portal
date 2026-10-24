<?php

namespace App\Enums;

enum AccountState: string
{
    case Active = 'active';
    case Suspended = 'suspended';
    case Deactivated = 'deactivated';

    public function canAuthenticate(): bool
    {
        return $this === self::Active;
    }
}
