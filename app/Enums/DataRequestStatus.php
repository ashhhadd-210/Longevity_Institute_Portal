<?php

namespace App\Enums;

enum DataRequestStatus: string
{
    case Received = 'received';
    case Processing = 'processing';
    case Completed = 'completed';
}
