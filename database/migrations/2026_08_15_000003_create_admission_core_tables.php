<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The judgement layer of admission. A referral or application *receives* a
 * decision; it does not become one. Direct invitations by the Founder carry
 * a decision with no subject record.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission_decisions', function (Blueprint $table) {
            $table->id();
            // NULL subject = a direct invitation by the Institute: there is no
            // inbound record to point at, and "direct" is derived, never stored
            // (a stored non-class morph value would break relation loading).
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('decision');
            $table->text('rationale')->nullable();
            $table->foreignId('decided_by')->constrained('users')->restrictOnDelete();
            $table->timestampTz('decided_at');
            $table->timestampsTz();
            $table->index(['subject_type', 'subject_id']);
        });

        DB::statement("ALTER TABLE admission_decisions ADD CONSTRAINT admission_decisions_subject_type_check
            CHECK (subject_type IS NULL OR subject_type IN ('application', 'referral'))");
        DB::statement("ALTER TABLE admission_decisions ADD CONSTRAINT admission_decisions_decision_check
            CHECK (decision IN ('approved', 'declined'))");
        DB::statement('ALTER TABLE admission_decisions ADD CONSTRAINT admission_decisions_subject_pair_check
            CHECK ((subject_type IS NULL) = (subject_id IS NULL))');

        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('first_name');
            $table->string('last_name');
            // Only the SHA-256 hash of the invitation token is stored; the raw
            // token exists solely inside the invitation link (doc 08 §1).
            $table->string('token_hash', 64)->unique();
            $table->timestampTz('expires_at');
            $table->string('status')->default('issued');
            $table->foreignId('admission_decision_id')->nullable()
                ->constrained('admission_decisions')->restrictOnDelete();
            $table->foreignId('issued_by')->constrained('users')->restrictOnDelete();
            $table->unsignedInteger('send_count')->default(0);
            $table->timestampTz('last_sent_at')->nullable();
            $table->timestampTz('accepted_at')->nullable();
            $table->timestampTz('revoked_at')->nullable();
            $table->timestampsTz();
            $table->index(['status', 'expires_at']);
            $table->index('email');
        });

        DB::statement('ALTER TABLE invitations ALTER COLUMN email TYPE citext');
        DB::statement("ALTER TABLE invitations ADD CONSTRAINT invitations_status_check
            CHECK (status IN ('issued', 'sent', 'accepted', 'expired', 'revoked'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('invitations');
        Schema::dropIfExists('admission_decisions');
    }
};
