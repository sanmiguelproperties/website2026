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
        Schema::create('property_locations', function (Blueprint $table) {
            $table->unsignedBigInteger('property_id')->primary();
            $table->foreign('property_id')
                ->references('id')
                ->on('properties')
                ->cascadeOnDelete();

            $table->string('region')->nullable();
            $table->string('city')->nullable();
            $table->string('city_area')->nullable();
            $table->string('street')->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->boolean('show_exact_location')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->json('raw_payload')->nullable();

            $table->index(['region', 'city']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_locations');
    }
};

