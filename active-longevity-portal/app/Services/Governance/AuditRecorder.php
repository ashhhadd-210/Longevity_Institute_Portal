<?php

namespace App\Services\Governance;

use App\Models\AuditEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Single write-path for the institutional record. Services call this
 * synchronously — history must not be lost to a failed queue.
 */
class AuditRecorder
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function record(string $eventType, ?User $actor = null, ?Model $subject = null, array $context = []): AuditEvent
    {
        return AuditEvent::query()->create([
            'actor_user_id' => $actor?->id,
            'event_type' => $eventType,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'context' => $context === [] ? null : $context,
            'occurred_at' => now(),
        ]);
    }
}
