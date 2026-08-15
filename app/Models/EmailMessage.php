<?php

namespace App\Models;

use App\Enums\EmailMessageStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Delivery log for every outbound message, updated by Postmark webhooks.
 * Makes per-invitation delivery status visible and reissue possible — the
 * answer to the project's largest operational risk (ADR-006).
 */
class EmailMessage extends Model
{
    protected $fillable = [
        'message_type',
        'to_email',
        'subject',
        'postmark_message_id',
        'status',
        'related_type',
        'related_id',
        'error',
        'queued_at',
        'sent_at',
        'delivered_at',
        'bounced_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => EmailMessageStatus::class,
            'queued_at' => 'datetime',
            'sent_at' => 'datetime',
            'delivered_at' => 'datetime',
            'bounced_at' => 'datetime',
        ];
    }

    /** @return MorphTo<Model, $this> */
    public function related(): MorphTo
    {
        return $this->morphTo();
    }
}
