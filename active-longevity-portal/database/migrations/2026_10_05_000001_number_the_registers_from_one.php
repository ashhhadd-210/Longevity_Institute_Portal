<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The Registers screen showed the first Field as number 0. People count from
 * one, so the order numbers are shifted up by one where they began at zero.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['disciplines', 'expertise_areas'] as $table) {
            if (DB::table($table)->where('sort_order', 0)->exists()) {
                DB::table($table)->update(['sort_order' => DB::raw('sort_order + 1')]);
            }
        }
    }

    public function down(): void
    {
        // Ordering is relative, so there is nothing to undo.
    }
};
