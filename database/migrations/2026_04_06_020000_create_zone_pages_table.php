<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('zone_pages', function (Blueprint $table) {
            $table->id();

            $table->string('slug', 180)->unique();

            $table->string('region', 255);
            $table->string('city', 255);
            $table->string('city_area', 255);

            // Llaves normalizadas para deduplicar por ubicación.
            $table->string('region_key', 255);
            $table->string('city_key', 255);
            $table->string('city_area_key', 255);

            // Contenido administrable bilingüe.
            $table->string('title_es', 255)->nullable();
            $table->string('title_en', 255)->nullable();
            $table->text('description_es')->nullable();
            $table->text('description_en')->nullable();

            // Metadatos SEO bilingües opcionales.
            $table->string('meta_title_es', 255)->nullable();
            $table->string('meta_title_en', 255)->nullable();
            $table->text('meta_description_es')->nullable();
            $table->text('meta_description_en')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamp('last_detected_at')->nullable();

            $table->timestamps();

            $table->index(['is_active']);
            $table->index(['region']);
            $table->index(['city']);
            $table->index(['city_area']);
            $table->unique(['region_key', 'city_key', 'city_area_key'], 'zone_pages_location_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zone_pages');
    }
};

