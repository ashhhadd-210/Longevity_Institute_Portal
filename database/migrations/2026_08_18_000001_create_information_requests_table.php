<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * "Need more information" is a question, not a judgement (agreed with the
 * Institute, 18 August 2026). It keeps a case open and may be asked more than
 * once, so it is recorded as its own kind of event rather than as an admission
 * decision — otherwise a candidate questioned twice before approval would
 * appear in the record to have been decided upon three times.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('information_requests', function (Blueprint $table) {
            $table->id();
            $table->string('subject_type');
            $table->unsignedBigInteger('subject_id');
            $table->foreignId('requested_by')->constrained('users')->restrictOnDelete();
            $table->text('required')->comment('What the Institute has asked the candidate to provide');
            $table->timestampTz('requested_at');
            $table->text('response')->nullable();
            $table->timestampTz('responded_at')->nullable();
            $table->timestampsTz();

            $table->index(['subject_type', 'subject_id']);
            $table->index('responded_at');
        });

        DB::statement("ALTER TABLE information_requests ADD CONSTRAINT information_requests_subject_type_check
            CHECK (subject_type IN ('application', 'referral'))");

        // A case awaiting a response is open, not decided.
        DB::statement('ALTER TABLE applications DROP CONSTRAINT applications_status_check');
        DB::statement("ALTER TABLE applications ADD CONSTRAINT applications_status_check
            CHECK (status IN ('submitted', 'under_review', 'awaiting_information', 'approved', 'declined', 'withdrawn'))");

        DB::statement('ALTER TABLE referrals DROP CONSTRAINT referrals_status_check');
        DB::statement("ALTER TABLE referrals ADD CONSTRAINT referrals_status_check
            CHECK (status IN ('submitted', 'under_review', 'awaiting_information', 'approved', 'declined'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('information_requests');

        DB::statement('ALTER TABLE applications DROP CONSTRAINT applications_status_check');
        DB::statement("ALTER TABLE applications ADD CONSTRAINT applications_status_check
            CHECK (status IN ('submitted', 'under_review', 'approved', 'declined', 'withdrawn'))");

        DB::statement('ALTER TABLE referrals DROP CONSTRAINT referrals_status_check');
        DB::statement("ALTER TABLE referrals ADD CONSTRAINT referrals_status_check
            CHECK (status IN ('submitted', 'under_review', 'approved', 'declined'))");
    }
};
