<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Institutions as first-class entities rather than free-text employer
        // strings. Official names may also appear in their original language.
        Schema::create('organisations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_original')->nullable();
            $table->string('website')->nullable();
            $table->char('country_code', 2)->nullable();
            $table->timestampsTz();
        });

        // Expertise is an Institute-controlled vocabulary, never free text.
        // This single decision is what makes search, grouping, matching and
        // reporting possible later without a data-cleaning project.
        Schema::create('expertise_areas', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->boolean('active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expertise_areas');
        Schema::dropIfExists('organisations');
    }
};
