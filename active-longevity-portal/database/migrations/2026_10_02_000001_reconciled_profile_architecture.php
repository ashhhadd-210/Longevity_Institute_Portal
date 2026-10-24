<?php

use App\Models\Profile;
use App\Services\Profiles\ProfileEditor;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The Milestone 3 reconciliation, as approved by the Institute.
 *
 * About Your Work grows from a 160 character directory line into a short
 * passage of up to 500 characters written for collaborators outside the
 * person's own field. And the Portal records when each person was shown the
 * Collaborator Profile Standards, and which version, so the Institute can
 * evidence it without making anybody tick a box.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('one_line_summary', 500)->nullable()->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->timestampTz('profile_standards_shown_at')->nullable();
            $table->string('profile_standards_version', 20)->nullable();
        });

        // Readiness has new requirements, so every existing profile is judged
        // again under them rather than keeping a decision made under the old.
        Profile::query()->each(fn (Profile $profile) => app(ProfileEditor::class)->refreshCompletion($profile));
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['profile_standards_shown_at', 'profile_standards_version']);
        });

        Schema::table('profiles', function (Blueprint $table) {
            $table->string('one_line_summary', 160)->nullable()->change();
        });
    }
};
