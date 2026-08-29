<?php

namespace App\Enums;

/**
 * Institute-defined CV sections: standardized structure so profiles remain
 * visually consistent and searchable, without restricting quantity.
 *
 * CurrentPosition is the exception to "without restricting quantity": it holds
 * the roles a collaborator occupies now, and is capped at three (approved
 * addition, Item 1). Career history belongs in Experience and remains unbounded.
 */
enum ProfileEntrySection: string
{
    case CurrentPosition = 'current_position';
    case Experience = 'experience';
    case Education = 'education';
    case Appointment = 'appointment';
    case Publication = 'publication';

    /**
     * The most positions a collaborator may hold concurrently on a profile.
     * Enforced in the database as well as here.
     */
    public const CURRENT_POSITION_LIMIT = 3;

    public function isCapped(): bool
    {
        return $this === self::CurrentPosition;
    }
}
