<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Expertise becomes a two-level vocabulary: discipline, and specialties within
 * it (agreed with the Institute, 18 August 2026).
 *
 * A collaborator selects specialties only; the discipline follows from the
 * specialty, so the two levels can never contradict one another and nobody
 * answers the same question twice. Searching a discipline returns everyone in
 * any of its specialties.
 *
 * The *shape* is settled here; the *contents* remain the Institute's to grow
 * indefinitely. Adding a discipline or specialty later is an administrative
 * act, not development work, and orphans nothing.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disciplines', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->boolean('active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestampsTz();
        });

        Schema::table('expertise_areas', function (Blueprint $table) {
            // Nullable so that existing vocabulary is never orphaned while the
            // Institute reviews the discipline list.
            $table->foreignId('discipline_id')->nullable()->after('id')
                ->constrained('disciplines')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('expertise_areas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('discipline_id');
        });
        Schema::dropIfExists('disciplines');
    }
};
