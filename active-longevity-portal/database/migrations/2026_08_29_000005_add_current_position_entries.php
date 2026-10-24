<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Up to three concurrent position and organisation combinations per
 * collaborator (approved addition, Item 1, $350). Structural work belongs to
 * this milestone; the entry screens belong to Milestone 3.
 *
 * A senior professional frequently holds more than one appointment at once — a
 * university chair, a hospital role and a government advisory seat — and asking
 * them to choose one would misrepresent them. This is distinct from the
 * existing 'experience' section, which is career history: these are the roles
 * held *now*, and they are what the directory and a profile headline read from.
 *
 * The cap of three is enforced by the database using the same technique as
 * profile links: a bounded position plus a partial unique index. An application
 * bug therefore cannot produce a fourth.
 *
 * Each entry's payload carries title, organisation and department. Those obey
 * the agreed limits of 300, 200 and 200 characters, enforced in validation
 * because they live inside a JSON document rather than in columns of their own.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE profile_entries DROP CONSTRAINT profile_entries_section_check');
        DB::statement("ALTER TABLE profile_entries ADD CONSTRAINT profile_entries_section_check
            CHECK (section IN ('current_position', 'experience', 'education', 'appointment', 'publication'))");

        DB::statement("ALTER TABLE profile_entries ADD CONSTRAINT profile_entries_current_position_max_three_check
            CHECK (section <> 'current_position' OR position BETWEEN 1 AND 3)");

        DB::statement("CREATE UNIQUE INDEX profile_entries_current_position_slot_idx
            ON profile_entries (profile_id, position)
            WHERE section = 'current_position'");
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS profile_entries_current_position_slot_idx');
        DB::statement('ALTER TABLE profile_entries DROP CONSTRAINT profile_entries_current_position_max_three_check');

        DB::table('profile_entries')->where('section', 'current_position')->delete();

        DB::statement('ALTER TABLE profile_entries DROP CONSTRAINT profile_entries_section_check');
        DB::statement("ALTER TABLE profile_entries ADD CONSTRAINT profile_entries_section_check
            CHECK (section IN ('experience', 'education', 'appointment', 'publication'))");
    }
};
