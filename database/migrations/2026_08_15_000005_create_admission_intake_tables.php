<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The two inbound admission pathways. Candidate identity before registration
 * lives in these records (ADR-008); pipeline records are never deleted, they
 * change status.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('organisation_text')->nullable();
            $table->string('professional_title')->nullable();
            $table->char('country_code', 2)->nullable();
            $table->jsonb('expertise_claimed')->nullable();
            $table->text('motivation')->nullable();
            $table->string('status')->default('submitted');
            $table->timestampTz('submitted_at');
            $table->timestampsTz();
            $table->index(['status', 'submitted_at']);
            $table->index('email');
        });

        DB::statement('ALTER TABLE applications ALTER COLUMN email TYPE citext');
        DB::statement("ALTER TABLE applications ADD CONSTRAINT applications_status_check
            CHECK (status IN ('submitted', 'under_review', 'approved', 'declined', 'withdrawn'))");

        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            // No limit on how many qualified professionals a collaborator may refer.
            $table->foreignId('referrer_collaborator_id')
                ->constrained('collaborators')->restrictOnDelete();
            $table->string('candidate_email');
            $table->string('candidate_first_name');
            $table->string('candidate_last_name');
            $table->string('candidate_organisation')->nullable();
            $table->string('candidate_title')->nullable();
            $table->char('candidate_country_code', 2)->nullable();
            $table->jsonb('candidate_expertise')->nullable();
            $table->text('reason');
            $table->string('relationship_to_referrer')->nullable();
            $table->text('comments')->nullable();
            $table->string('status')->default('submitted');
            $table->timestampsTz();
            $table->index('status');
            $table->index('candidate_email');
        });

        DB::statement('ALTER TABLE referrals ALTER COLUMN candidate_email TYPE citext');
        DB::statement("ALTER TABLE referrals ADD CONSTRAINT referrals_status_check
            CHECK (status IN ('submitted', 'under_review', 'approved', 'declined'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('referrals');
        Schema::dropIfExists('applications');
    }
};
