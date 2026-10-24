<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The classification orientation appears automatically the first time and then
 * stops interrupting.
 *
 * It stays reachable afterwards, because somebody returning months later to
 * revise their profile may well want to read it again, but nobody should have
 * to dismiss the same explanation on every visit.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestampTz('classification_oriented_at')->nullable()->after('email_verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('classification_oriented_at');
        });
    }
};
