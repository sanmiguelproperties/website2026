<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Agrega campos para soportar sincronización con MLS AMPI San Miguel de Allende
     * y un campo source para identificar el origen de la propiedad.
     */
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            // Origen de la propiedad: 'manual', 'easybroker', 'mls'
            $table->string('source', 20)->default('manual')->after('agency_id');
            
            // Campos específicos del MLS
            $table->unsignedBigInteger('mls_id')->nullable()->after('easybroker_agent_id')
                ->comment('ID interno del MLS');
            $table->string('mls_public_id', 50)->nullable()->after('mls_id')
                ->comment('MLS ID público de la propiedad');
            $table->string('mls_folder_name', 255)->nullable()->after('mls_public_id')
                ->comment('Nombre de carpeta del MLS');
            $table->string('mls_neighborhood', 100)->nullable()->after('mls_folder_name')
                ->comment('Vecindario del MLS');
            $table->unsignedBigInteger('mls_office_id')->nullable()->after('mls_neighborhood')
                ->comment('ID de oficina del MLS');
            
            // Campos adicionales del MLS
            $table->string('status', 50)->nullable()->after('published')
                ->comment('Estado: For Sale, For Rent, etc.');
            $table->string('category', 50)->nullable()->after('status')
                ->comment('Categoría: Residential, Commercial, etc.');
            $table->boolean('is_approved')->default(false)->after('category')
                ->comment('Si está aprobado en el MLS');
            $table->boolean('allow_integration')->default(true)->after('is_approved')
                ->comment('Si permite integración');
            $table->decimal('old_price', 18, 2)->nullable()->after('expenses')
                ->comment('Precio anterior');
            
            // Campos adicionales de características
            $table->string('furnished', 20)->nullable()->after('age')
                ->comment('Amueblado: yes, no, partially');
            $table->boolean('with_yard')->nullable()->after('furnished');
            $table->string('with_view', 100)->nullable()->after('with_yard')
                ->comment('Vista: Mountain, Lake, etc.');
            $table->boolean('gated_comm')->nullable()->after('with_view')
                ->comment('Comunidad cerrada');
            $table->boolean('pool')->nullable()->after('gated_comm');
            $table->boolean('casita')->nullable()->after('pool');
            $table->string('casita_bedrooms', 10)->nullable()->after('casita');
            $table->string('casita_bathrooms', 10)->nullable()->after('casita_bedrooms');
            $table->integer('parking_number')->nullable()->after('parking_spaces');
            
            // Fechas del MLS
            $table->dateTime('mls_created_at')->nullable()->after('easybroker_updated_at');
            $table->dateTime('mls_updated_at')->nullable()->after('mls_created_at');
            
            // Índices
            $table->index('source');
            $table->index('mls_id');
            $table->index('mls_public_id');
            $table->index('status');
            $table->index('category');
            
            // Índice único para MLS (agency_id + mls_public_id)
            // Solo si mls_public_id no es null
            $table->unique(['agency_id', 'mls_public_id'], 'properties_agency_mls_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            // Eliminar índices
            $table->dropUnique('properties_agency_mls_unique');
            $table->dropIndex(['source']);
            $table->dropIndex(['mls_id']);
            $table->dropIndex(['mls_public_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['category']);
            
            // Eliminar columnas
            $table->dropColumn([
                'source',
                'mls_id',
                'mls_public_id',
                'mls_folder_name',
                'mls_neighborhood',
                'mls_office_id',
                'status',
                'category',
                'is_approved',
                'allow_integration',
                'old_price',
                'furnished',
                'with_yard',
                'with_view',
                'gated_comm',
                'pool',
                'casita',
                'casita_bedrooms',
                'casita_bathrooms',
                'parking_number',
                'mls_created_at',
                'mls_updated_at',
            ]);
        });
    }
};
