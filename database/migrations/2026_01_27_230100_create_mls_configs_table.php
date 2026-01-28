<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tabla de configuración para el MLS AMPI San Miguel de Allende.
     */
    public function up(): void
    {
        Schema::create('mls_configs', function (Blueprint $table) {
            $table->id();
            
            // Nombre de la configuración
            $table->string('name')->default('Principal')->comment('Nombre de la configuración');
            
            // API Key del MLS (encriptada en el modelo)
            $table->text('api_key')->nullable();
            
            // URL base de la API
            $table->string('base_url')->default('https://ampisanmigueldeallende.com/api/v1');
            
            // Configuración de rate limit y timeout
            $table->integer('rate_limit')->default(10)->comment('Requests por segundo');
            $table->integer('timeout')->default(30)->comment('Timeout en segundos');
            
            // Batch size para sincronización
            $table->integer('batch_size')->default(50)->comment('Número de propiedades por lote');
            
            // Estado de la configuración
            $table->boolean('is_active')->default(true);
            
            // Modo de sincronización: 'full' (todo) o 'incremental' (solo cambios)
            $table->string('sync_mode', 20)->default('incremental');
            
            // Última sincronización exitosa
            $table->dateTime('last_sync_at')->nullable();
            $table->integer('last_sync_created')->nullable();
            $table->integer('last_sync_updated')->nullable();
            $table->integer('last_sync_unpublished')->nullable();
            $table->integer('last_sync_errors')->nullable();
            $table->integer('last_sync_total_fetched')->nullable();
            
            // Cursor/página de la última sincronización (para retomar)
            $table->integer('last_sync_page')->nullable();
            $table->string('last_sync_cursor', 255)->nullable();
            
            // Notas o descripción
            $table->text('notes')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mls_configs');
    }
};
