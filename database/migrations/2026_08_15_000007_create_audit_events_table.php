<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The permanent institutional record (doc 02): who did what, to which subject,
 * and when. The admission history of a person is a filtered query over this
 * stream. Append-only, enforced in the database itself by trigger so that no
 * connection role — including the application's — can rewrite history.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_user_id')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->string('event_type');
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->jsonb('context')->nullable();
            $table->timestampTz('occurred_at')->useCurrent();

            $table->index(['subject_type', 'subject_id', 'occurred_at']);
            $table->index('event_type');
            $table->index('occurred_at');
        });

        DB::statement(<<<'SQL'
            CREATE OR REPLACE FUNCTION prevent_audit_event_mutation() RETURNS trigger AS $$
            BEGIN
                RAISE EXCEPTION 'audit_events is append-only: % is not permitted', TG_OP;
            END;
            $$ LANGUAGE plpgsql
        SQL);

        DB::statement(<<<'SQL'
            CREATE TRIGGER audit_events_append_only
            BEFORE UPDATE OR DELETE ON audit_events
            FOR EACH ROW EXECUTE FUNCTION prevent_audit_event_mutation()
        SQL);
    }

    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS audit_events_append_only ON audit_events');
        DB::statement('DROP FUNCTION IF EXISTS prevent_audit_event_mutation');
        Schema::dropIfExists('audit_events');
    }
};
