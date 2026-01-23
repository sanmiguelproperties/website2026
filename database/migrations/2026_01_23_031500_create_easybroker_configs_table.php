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
        Schema::create('easybroker_configs', function (Blueprint $table) {
            $table->id();
            
            // API Key de EasyBroker (encriptada en el modelo)
            $table->text('api_key')->nullable();
            
            // URL base de la API
            $table->string('base_url')->default('https://api.easybroker.com/v1');
            
            // Configuración de rate limit y timeout
            $table->integer('rate_limit')->default(20)->comment('Requests por segundo');
            $table->integer('timeout')->default(30)->comment('Timeout en segundos');
            
            // Estado de la configuración
            $table->boolean('is_active')->default(true);
            
            // Última sincronización exitosa
            $table->dateTime('last_sync_at')->nullable();
            $table->integer('last_sync_created')->nullable();
            $table->integer('last_sync_updated')->nullable();
            $table->integer('last_sync_unpublished')->nullable();
            $table->integer('last_sync_errors')->nullable();
            
            // Notas o descripción (para identificar la configuración)
            $table->string('name')->default('Principal')->comment('Nombre de la configuración');
            $table->text('notes')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('easybroker_configs');
    }
};
