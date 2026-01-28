<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Hace easybroker_public_id nullable para soportar propiedades de MLS
     * que no tienen un ID de EasyBroker.
     */
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            // Eliminar el índice único que incluye easybroker_public_id
            $table->dropUnique(['agency_id', 'easybroker_public_id']);
        });

        Schema::table('properties', function (Blueprint $table) {
            // Hacer easybroker_public_id nullable
            $table->string('easybroker_public_id', 50)->nullable()->change();
        });

        Schema::table('properties', function (Blueprint $table) {
            // Crear un índice simple (no único) para easybroker_public_id
            // El índice único ahora solo aplica cuando el campo no es null
            $table->index(['agency_id', 'easybroker_public_id'], 'properties_agency_easybroker_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropIndex('properties_agency_easybroker_idx');
        });

        Schema::table('properties', function (Blueprint $table) {
            // Revertir a NOT NULL (solo funcionará si no hay nulls)
            $table->string('easybroker_public_id', 50)->nullable(false)->change();
        });

        Schema::table('properties', function (Blueprint $table) {
            // Restaurar índice único
            $table->unique(['agency_id', 'easybroker_public_id']);
        });
    }
};
