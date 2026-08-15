<?php

namespace App\Enums;

/**
 * Institute-defined CV sections: standardized structure so profiles remain
 * visually consistent and searchable, without restricting quantity.
 */
enum ProfileEntrySection: string
{
    case Experience = 'experience';
    case Education = 'education';
    case Appointment = 'appointment';
    case Publication = 'publication';
}
