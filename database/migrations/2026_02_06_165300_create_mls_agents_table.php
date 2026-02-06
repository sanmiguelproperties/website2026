<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla mls_agents para almacenar agentes del MLS AMPI San Miguel de Allende.
     * 
     * Campos basados en la documentación del API:
     * GET /api/v1/agents → lista paginada
     * GET /api/v1/agent/{id} → detalle del agente
     * GET /api/v1/offices/{id}/agents → agentes por oficina
     */
    public function up(): void
    {
        Schema::create('mls_agents', function (Blueprint $table) {
            $table->id();
            
            // ID del agente en el MLS (campo 'id' del API)
            $table->unsignedBigInteger('mls_agent_id')->unique()
                ->comment('ID del agente en el MLS API');
            
            // Datos personales
            $table->string('name')->nullable()
                ->comment('Nombre completo del agente');
            $table->string('first_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('mobile', 50)->nullable();
            
            // Oficina
            $table->unsignedBigInteger('mls_office_id')->nullable()
                ->comment('ID de la oficina en el MLS');
            $table->string('office_name')->nullable()
                ->comment('Nombre de la oficina');
            
            // Foto del agente
            $table->text('photo_url')->nullable()
                ->comment('URL de la foto del agente en el MLS');
            $table->foreignId('photo_media_asset_id')->nullable()
                ->constrained('media_assets')->nullOnDelete()
                ->comment('MediaAsset local de la foto descargada');
            
            // Datos adicionales
            $table->string('license_number', 100)->nullable()
                ->comment('Número de licencia del agente');
            $table->text('bio')->nullable()
                ->comment('Biografía del agente');
            $table->string('website')->nullable();
            
            // Estado
            $table->boolean('is_active')->default(true);
            
            // Relación opcional con usuario local
            $table->foreignId('user_id')->nullable()
                ->constrained('users')->nullOnDelete()
                ->comment('Usuario local vinculado al agente MLS');
            
            // Sincronización
            $table->dateTime('last_synced_at')->nullable();
            $table->json('raw_payload')->nullable()
                ->comment('Datos crudos del API del MLS');
            
            $table->timestamps();
            
            // Índices
            $table->index('mls_office_id');
            $table->index('email');
            $table->index('is_active');
        });

        // Tabla pivot: property_mls_agent (relación muchos a muchos)
        Schema::create('property_mls_agent', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('property_id')
                ->constrained('properties')
                ->cascadeOnDelete();
            
            $table->foreignId('mls_agent_id')
                ->constrained('mls_agents')
                ->cascadeOnDelete();
            
            $table->boolean('is_primary')->default(false)
                ->comment('Si es el agente principal de la propiedad');
            
            $table->timestamps();
            
            // Índice único para evitar duplicados
            $table->unique(['property_id', 'mls_agent_id'], 'prop_mls_agent_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_mls_agent');
        Schema::dropIfExists('mls_agents');
    }
};
