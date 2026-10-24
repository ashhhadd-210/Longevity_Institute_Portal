<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Two things agreed in conversation that never had a milestone to live in, both
 * placed in Milestone 3 once that was noticed.
 *
 * Collaboration interest is what the first Beta uses instead of messaging.
 * Finding a person is only half of a directory; this is the other half. A
 * collaborator viewing a profile may register an interest in working together,
 * it is recorded against both people, and the Institute sees it. Deliberately a
 * record and a note rather than a conversation: threads and replies are not
 * needed before a community exists. Whether a collaborator accepts approaches
 * at all is theirs to decide, under the visibility rules the Institute
 * approved.
 *
 * Feedback is what the Alpha group needs before anything else. Gathering five
 * thoughtful people with no way for them to tell the Institute anything would
 * waste the most valuable group it will ever have.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->boolean('accepts_collaboration_interest')->default(true)->after('collaboration_availability');
        });

        Schema::create('collaboration_interests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_profile_id')->constrained('profiles')->cascadeOnDelete();
            $table->foreignId('to_profile_id')->constrained('profiles')->cascadeOnDelete();
            $table->string('note', 1000)->nullable();
            $table->timestampsTz();

            // One approach per pair. Interest is registered, not repeated.
            $table->unique(['from_profile_id', 'to_profile_id'], 'collaboration_interest_once');
            $table->index('to_profile_id');
        });

        Schema::create('feedback_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('message');
            $table->string('page', 300)->nullable();
            $table->timestampsTz();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback_notes');
        Schema::dropIfExists('collaboration_interests');

        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn('accepts_collaboration_interest');
        });
    }
};
