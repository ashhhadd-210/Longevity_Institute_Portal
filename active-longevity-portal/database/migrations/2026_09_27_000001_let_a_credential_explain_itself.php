<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A credential that is obvious in one profession or country is opaque in
 * another. The Institute wants an unfamiliar credential to be understandable
 * without leaving the profile, which needs two things the record did not
 * carry: who issues it, and a plain-language line on what it represents.
 *
 * Both are optional and empty until somebody writes them. The panel that
 * shows them is a separate decision; the record is made ready now so that
 * the library can be enriched over time without a rebuild.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('credentials', function (Blueprint $table) {
            $table->string('issuing_body', 200)->nullable()->after('jurisdiction');
            $table->string('description', 600)->nullable()->after('issuing_body');
        });
    }

    public function down(): void
    {
        Schema::table('credentials', function (Blueprint $table) {
            $table->dropColumn(['issuing_body', 'description']);
        });
    }
};
