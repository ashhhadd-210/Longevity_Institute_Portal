<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The audit stream is append-only, so the previous ON DELETE SET NULL on
 * audit_events.actor_user_id could never actually run: deleting a user who had
 * acted attempted to rewrite history, and the trigger correctly refused. The
 * schema therefore promised something the database would not allow.
 *
 * RESTRICT states the real rule: a user who appears in the institutional record
 * cannot be deleted. Erasure requests are satisfied by anonymising the person's
 * details in place (doc 08 §3), which preserves the fact that a decision was
 * taken and by whom, without retaining who that person was.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE audit_events DROP CONSTRAINT audit_events_actor_user_id_foreign');
        DB::statement('ALTER TABLE audit_events ADD CONSTRAINT audit_events_actor_user_id_foreign
            FOREIGN KEY (actor_user_id) REFERENCES users(id) ON DELETE RESTRICT');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE audit_events DROP CONSTRAINT audit_events_actor_user_id_foreign');
        DB::statement('ALTER TABLE audit_events ADD CONSTRAINT audit_events_actor_user_id_foreign
            FOREIGN KEY (actor_user_id) REFERENCES users(id) ON DELETE SET NULL');
    }
};
