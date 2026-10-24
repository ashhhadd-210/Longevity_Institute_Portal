<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Generational suffixes as a field of their own (agreed with the Institute,
 * absorbed at no charge).
 *
 * "Jr.", "Sr.", "II", "III" are part of a person's name but not part of their
 * family name, and the distinction is not cosmetic. Folded into the family name
 * they would sort "Lovelace Jr." apart from "Lovelace", scattering relatives
 * through a directory and separating a person from their own namesake. Held
 * separately, the family name sorts correctly and the suffix still displays.
 *
 * Short by nature, so the agreed 100-character name allowance does not apply:
 * no generational suffix approaches it, and a long value here would signal a
 * misunderstood field rather than a long name.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name_suffix', 20)->nullable()->after('last_name');
        });

        Schema::table('invitations', function (Blueprint $table) {
            $table->string('name_suffix', 20)->nullable()->after('last_name');
        });
    }

    public function down(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->dropColumn('name_suffix');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('name_suffix');
        });
    }
};
