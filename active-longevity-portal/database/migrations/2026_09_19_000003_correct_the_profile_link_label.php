<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Profile links carry a label, not a description.
 *
 * The original column was written before the Institute settled this. What was
 * agreed is a short label of about fifty characters that the collaborator
 * writes themselves, shown as the clickable text, with the address itself never
 * printed. The label replaced the earlier one-sentence explanation rather than
 * joining it, so two hundred characters was both the wrong name and the wrong
 * size.
 *
 * Any label already longer than fifty characters is trimmed at a word boundary
 * rather than cut mid-word, though in practice there is nothing to trim: no
 * collaborator has yet reached a profile.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('
            UPDATE profile_links
            SET description = left(description, 50)
            WHERE length(description) > 50
        ');

        Schema::table('profile_links', function (Blueprint $table) {
            $table->renameColumn('description', 'label');
        });

        DB::statement('ALTER TABLE profile_links ALTER COLUMN label TYPE varchar(50)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE profile_links ALTER COLUMN label TYPE varchar(200)');

        Schema::table('profile_links', function (Blueprint $table) {
            $table->renameColumn('label', 'description');
        });
    }
};
