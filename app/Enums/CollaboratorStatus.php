<?php

namespace App\Enums;

enum CollaboratorStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Departed = 'departed';
}
