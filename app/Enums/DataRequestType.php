<?php

namespace App\Enums;

enum DataRequestType: string
{
    case Export = 'export';
    case Erasure = 'erasure';
}
