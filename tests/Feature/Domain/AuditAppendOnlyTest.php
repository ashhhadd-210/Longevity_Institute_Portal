<?php

namespace Tests\Feature\Domain;

use App\Models\AuditEvent;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Institutional history cannot be rewritten: the database itself refuses
 * UPDATE and DELETE on audit_events, regardless of connection role.
 */
class AuditAppendOnlyTest extends TestCase
{
    use RefreshDatabase;

    private function makeEvent(): AuditEvent
    {
        return AuditEvent::query()->create([
            'actor_user_id' => null,
            'event_type' => 'admission.invitation.issued',
            'subject_type' => 'invitation',
            'subject_id' => 1,
            'context' => ['email' => 'candidate@example.org'],
            'occurred_at' => now(),
        ]);
    }

    public function test_audit_events_cannot_be_updated(): void
    {
        $event = $this->makeEvent();

        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('append-only');

        $event->update(['event_type' => 'rewritten.history']);
    }

    public function test_audit_events_cannot_be_deleted(): void
    {
        $event = $this->makeEvent();

        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('append-only');

        $event->delete();
    }
}
