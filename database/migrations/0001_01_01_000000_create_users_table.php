<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // Separate name fields; no professional prefixes. Full Unicode
            // extended Latin is supported end to end — names are never
            // simplified because of a technical limitation.
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');

            $table->string('email')->unique();
            $table->timestampTz('email_verified_at')->nullable();
            $table->string('password');

            // TOTP multi-factor authentication. Mandatory for admin-tier
            // roles, enforced by middleware before any admin route.
            $table->text('mfa_secret')->nullable();
            $table->timestampTz('mfa_confirmed_at')->nullable();
            $table->text('mfa_recovery_codes')->nullable();

            $table->string('account_state')->default('active');

            $table->rememberToken();
            $table->timestampsTz();
        });

        DB::statement('ALTER TABLE users ALTER COLUMN email TYPE citext');
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_account_state_check
            CHECK (account_state IN ('active', 'suspended', 'deactivated'))");

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestampTz('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
