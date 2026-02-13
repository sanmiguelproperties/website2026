<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * El API del MLS AMPI expone ubicación incluyendo `country`.
     *
     * - Properties / Office Properties payloads: country
     *
     * En nuestro modelo, la ubicación detallada vive en `property_locations`.
     */
    public function up(): void
    {
        Schema::table('property_locations', function (Blueprint $table) {
            if (!Schema::hasColumn('property_locations', 'country')) {
                $table->string('country', 100)->nullable()->after('region');
                $table->index('country');
            }
        });
    }

    public function down(): void
    {
        Schema::table('property_locations', function (Blueprint $table) {
            if (Schema::hasColumn('property_locations', 'country')) {
                $table->dropIndex(['country']);
                $table->dropColumn('country');
            }
        });
    }
};

