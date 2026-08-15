<?php

namespace App\Enums;

enum EmailMessageStatus: string
{
    case Queued = 'queued';
    case Sent = 'sent';
    case Delivered = 'delivered';
    case Bounced = 'bounced';
    case SpamComplaint = 'spam_complaint';
    case Failed = 'failed';
}
