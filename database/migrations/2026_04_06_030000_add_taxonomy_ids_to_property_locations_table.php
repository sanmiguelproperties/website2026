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
        Schema::table('property_locations', function (Blueprint $table) {
            if (!Schema::hasColumn('property_locations', 'state_catalog_id')) {
                $table->foreignId('state_catalog_id')
                    ->nullable()
                    ->after('region')
                    ->constrained('locations_catalog')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('property_locations', 'city_catalog_id')) {
                $table->foreignId('city_catalog_id')
                    ->nullable()
                    ->after('city')
                    ->constrained('locations_catalog')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('property_locations', 'neighborhood_catalog_id')) {
                $table->foreignId('neighborhood_catalog_id')
                    ->nullable()
                    ->after('city_area')
                    ->constrained('locations_catalog')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('property_locations', function (Blueprint $table) {
            if (Schema::hasColumn('property_locations', 'neighborhood_catalog_id')) {
                $table->dropForeign(['neighborhood_catalog_id']);
                $table->dropColumn('neighborhood_catalog_id');
            }

            if (Schema::hasColumn('property_locations', 'city_catalog_id')) {
                $table->dropForeign(['city_catalog_id']);
                $table->dropColumn('city_catalog_id');
            }

            if (Schema::hasColumn('property_locations', 'state_catalog_id')) {
                $table->dropForeign(['state_catalog_id']);
                $table->dropColumn('state_catalog_id');
            }
        });
    }
};

