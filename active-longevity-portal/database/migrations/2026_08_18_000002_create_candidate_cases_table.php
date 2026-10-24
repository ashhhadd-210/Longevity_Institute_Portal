<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * One person may reach the Institute by more than one route — applying
 * unprompted while an existing collaborator independently recommends them.
 * Both submissions are kept, because each carries something the other does not,
 * but they converge on a single case which receives one decision, one
 * invitation and one admission history (agreed with the Institute, 18 August 2026).
 *
 * ADR-008 anticipated this: it recorded that a consolidating entity could be
 * introduced later, additively, without disturbing provenance. This is that
 * addition, and nothing existing changes shape to accommodate it.
 *
 * Note that only *confirmed* links are stored. Possible duplicates are
 * recomputed on demand and presented to a reviewer for judgement, because
 * deciding that two records describe one human being belongs to the Institute,
 * not to the software.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidate_cases', function (Blueprint $table) {
            $table->id();
            $table->string('email')->nullable();
            $table->string('display_name');

            // Who confirmed that two submissions describe one human being, and
            // when. Null until somebody does, because a case opened by a public
            // application has no reviewer present and nothing yet to confirm.
            // Deciding that two records are one person belongs to the
            // Institute, never to the software, so these stay empty rather than
            // being filled in by whatever process happened to create the row.
            $table->foreignId('linked_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestampTz('linked_at')->nullable();

            $table->timestampsTz();
            $table->index('email');
        });

        DB::statement('ALTER TABLE candidate_cases ALTER COLUMN email TYPE citext');

        Schema::table('applications', function (Blueprint $table) {
            $table->foreignId('candidate_case_id')->nullable()->after('id')
                ->constrained('candidate_cases')->nullOnDelete();
        });

        Schema::table('referrals', function (Blueprint $table) {
            $table->foreignId('candidate_case_id')->nullable()->after('id')
                ->constrained('candidate_cases')->nullOnDelete();
        });

        // A decision may be recorded against a linked case rather than a single
        // submission, so that two routes never produce two verdicts.
        Schema::table('admission_decisions', function (Blueprint $table) {
            $table->foreignId('candidate_case_id')->nullable()->after('subject_id')
                ->constrained('candidate_cases')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('admission_decisions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('candidate_case_id');
        });
        Schema::table('referrals', function (Blueprint $table) {
            $table->dropConstrainedForeignId('candidate_case_id');
        });
        Schema::table('applications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('candidate_case_id');
        });
        Schema::dropIfExists('candidate_cases');
    }
};
