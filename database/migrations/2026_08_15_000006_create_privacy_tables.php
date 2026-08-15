<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * GDPR applies from the first EU/UK registration. Consent is captured against
 * a versioned privacy notice; export and erasure are first-class requests.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('document')->default('privacy_notice');
            $table->string('version');
            $table->timestampTz('granted_at');
            $table->string('ip_address', 45)->nullable();
            $table->timestampsTz();
            $table->index(['user_id', 'document']);
        });

        Schema::create('data_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('type');
            $table->string('status')->default('received');
            $table->timestampTz('requested_at');
            $table->timestampTz('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestampsTz();
        });

        DB::statement("ALTER TABLE data_requests ADD CONSTRAINT data_requests_type_check
            CHECK (type IN ('export', 'erasure'))");
        DB::statement("ALTER TABLE data_requests ADD CONSTRAINT data_requests_status_check
            CHECK (status IN ('received', 'processing', 'completed'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('data_requests');
        Schema::dropIfExists('consents');
    }
};
