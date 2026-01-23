<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Agrega el campo 'category' a la tabla features para almacenar
     * la categoría de las características provenientes de EasyBroker.
     * 
     * Ejemplos de categorías en EasyBroker:
     * - Exterior (Jardín, Terraza, etc.)
     * - General (Cocina integral, Seguridad 24 horas, etc.)
     * - Recreación (Alberca, Gimnasio, etc.)
     * - Políticas (Mascotas permitidas, Permitido fumar, etc.)
     */
    public function up(): void
    {
        Schema::table('features', function (Blueprint $table) {
            $table->string('category', 100)->nullable()->after('name');
        });

        // Actualizar el índice único para incluir la categoría
        // Esto permite tener la misma característica en diferentes categorías
        Schema::table('features', function (Blueprint $table) {
            // Primero eliminar el índice único existente
            $table->dropUnique(['name', 'locale']);
        });

        Schema::table('features', function (Blueprint $table) {
            // Recrear el índice único incluyendo la categoría
            $table->unique(['name', 'category', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('features', function (Blueprint $table) {
            $table->dropUnique(['name', 'category', 'locale']);
        });

        Schema::table('features', function (Blueprint $table) {
            $table->unique(['name', 'locale']);
        });

        Schema::table('features', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};
