<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Admitted is not the same as visible, but the thing that separates them is
 * the profile, not a switch nobody has flipped.
 *
 * The directory flag was created before the directory existed, defaulting to
 * hidden until the Institute decided otherwise. The Milestone 3 rule is that
 * a profile appears once it says enough, so the flag now means what its name
 * says: the Institute's recorded decision to withhold somebody, off by
 * default and used deliberately.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('collaborators', function (Blueprint $table) {
            $table->boolean('directory_visible')->default(true)->change();
        });

        DB::table('collaborators')
            ->where('status', 'active')
            ->update(['directory_visible' => true]);
    }

    public function down(): void
    {
        Schema::table('collaborators', function (Blueprint $table) {
            $table->boolean('directory_visible')->default(false)->change();
        });
    }
};
