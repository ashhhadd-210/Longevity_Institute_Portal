<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The character limits committed to the Institute on 25 August 2026, and the
 * preferred name the salutation rule depends on (confirmed 28 August 2026).
 *
 * Limits are a ceiling against accidents, not a saving: in PostgreSQL a
 * varchar occupies only the space its text actually needs, so a generous limit
 * costs nothing. They are counted in characters, not bytes, so a Lithuanian or
 * Chinese name is allowed exactly as much room as an English one.
 *
 * Titles get 300 because a named chair carrying a donor, a department and a
 * joint appointment genuinely passes 150, and nobody should abbreviate their
 * real title to fit a form we designed around "Professor".
 *
 * Whatever is accepted here is displayed in full. Truncation with an ellipsis
 * is not permitted anywhere in the interface.
 */
return new class extends Migration
{
    /** @var array<string, array<string, int>> */
    private const LIMITS = [
        'users' => [
            'first_name' => 100,
            'middle_name' => 100,
            'last_name' => 100,
        ],
        'applications' => [
            'first_name' => 100,
            'middle_name' => 100,
            'last_name' => 100,
            'organisation_text' => 200,
            'professional_title' => 300,
        ],
        'referrals' => [
            'candidate_first_name' => 100,
            'candidate_last_name' => 100,
            'candidate_organisation' => 200,
            'candidate_title' => 300,
        ],
        'invitations' => [
            'first_name' => 100,
            'last_name' => 100,
        ],
        'profiles' => [
            'professional_title' => 300,
            'city' => 200,
            'state_region' => 200,
        ],
        'organisations' => [
            'name' => 200,
            'name_original' => 200,
        ],
    ];

    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // "How would you like the Portal to address you?" The field also
            // absorbs the prefix question the Institute chose not to model:
            // someone who wishes to be addressed as Doctor Smith writes that.
            $table->string('preferred_name', 100)->nullable()->after('first_name');
        });

        foreach (self::LIMITS as $table => $columns) {
            foreach ($columns as $column => $limit) {
                DB::statement("ALTER TABLE {$table} ALTER COLUMN {$column} TYPE varchar({$limit})");
            }
        }
    }

    public function down(): void
    {
        foreach (self::LIMITS as $table => $columns) {
            foreach ($columns as $column => $limit) {
                DB::statement("ALTER TABLE {$table} ALTER COLUMN {$column} TYPE varchar(255)");
            }
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('preferred_name');
        });
    }
};
