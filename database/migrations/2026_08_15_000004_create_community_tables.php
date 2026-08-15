<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * User ≠ Collaborator ≠ Profile (ADR-008): authentication identity,
 * institutional membership, and professional presentation have different
 * lifecycles and are modeled separately.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collaborators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->restrictOnDelete();
            // Provenance: how this person entered the community. Nullable only
            // for exceptional administrative creation; every normal admission
            // traces to an invitation.
            $table->foreignId('invitation_id')->nullable()
                ->constrained('invitations')->restrictOnDelete();
            $table->string('status')->default('active');
            // Phased launch: wave_0, wave_1, ... (doc 02 §4)
            $table->string('cohort')->nullable();
            $table->boolean('directory_visible')->default(false);
            $table->string('visibility_override')->nullable();
            $table->timestampTz('admitted_at');
            $table->timestampsTz();
        });

        DB::statement("ALTER TABLE collaborators ADD CONSTRAINT collaborators_status_check
            CHECK (status IN ('active', 'inactive', 'departed'))");
        DB::statement("ALTER TABLE collaborators ADD CONSTRAINT collaborators_visibility_override_check
            CHECK (visibility_override IS NULL OR visibility_override IN ('force_hidden', 'force_visible'))");
        DB::statement("CREATE INDEX collaborators_directory_idx
            ON collaborators (directory_visible) WHERE status = 'active'");

        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collaborator_id')->unique()->constrained()->restrictOnDelete();
            $table->string('professional_title')->nullable();
            $table->string('one_line_summary', 160)->nullable();
            $table->text('about')->nullable();
            $table->string('photo_path')->nullable();
            // Up to three displayed credentials after the name; never prefixes.
            $table->jsonb('credentials')->nullable();
            $table->char('country_code', 2)->nullable();   // public
            $table->string('city')->nullable();            // admin-only
            $table->string('state_region')->nullable();    // admin-only
            $table->jsonb('languages')->nullable();
            $table->string('collaboration_availability')->nullable();
            $table->foreignId('organisation_id')->nullable()
                ->constrained('organisations')->restrictOnDelete();
            $table->timestampTz('completed_at')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        DB::statement('ALTER TABLE profiles ADD CONSTRAINT profiles_credentials_max_three_check
            CHECK (credentials IS NULL OR jsonb_array_length(credentials) <= 3)');

        // Structured CV rows in Institute-defined sections: standardized
        // structure, unrestricted quantity.
        Schema::create('profile_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->string('section');
            $table->integer('position')->default(0);
            $table->jsonb('payload');
            $table->timestampsTz();
            $table->index(['profile_id', 'section', 'position']);
        });

        DB::statement("ALTER TABLE profile_entries ADD CONSTRAINT profile_entries_section_check
            CHECK (section IN ('experience', 'education', 'appointment', 'publication'))");

        // Up to five external links, each explained in one sentence. The
        // position CHECK plus the unique constraint make five a hard cap.
        Schema::create('profile_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->string('url', 2048);
            $table->string('description', 200);
            $table->integer('position');
            $table->timestampsTz();
            $table->unique(['profile_id', 'position']);
        });

        DB::statement('ALTER TABLE profile_links ADD CONSTRAINT profile_links_position_check
            CHECK (position BETWEEN 1 AND 5)');

        Schema::create('collaborator_expertise', function (Blueprint $table) {
            $table->foreignId('collaborator_id')->constrained()->cascadeOnDelete();
            $table->foreignId('expertise_area_id')->constrained()->restrictOnDelete();
            $table->primary(['collaborator_id', 'expertise_area_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collaborator_expertise');
        Schema::dropIfExists('profile_links');
        Schema::dropIfExists('profile_entries');
        Schema::dropIfExists('profiles');
        Schema::dropIfExists('collaborators');
    }
};
