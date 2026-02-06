<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega campos faltantes del MLS AMPI San Miguel de Allende a la tabla properties.
     * 
     * Campos identificados en la documentación API que no estaban en la base de datos:
     * - description_short_en, description_full_en, description_short_es, description_full_es
     * - lot_feet, construction_feet
     * - year_built, payment, parking_type, selling_office_commission, showing_terms
     * - for_rent (boolean)
     * - country (para ubicación completa)
     * - video_url (diferente de virtual_tour_url)
     * 
     * También corrige: bathrooms de integer a decimal para soportar valores como 2.5
     */
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            // Descripciones bilingües del MLS (en/es, corta/completa)
            $table->text('description_short_en')->nullable()->after('description')
                ->comment('Descripción corta en inglés del MLS');
            $table->mediumText('description_full_en')->nullable()->after('description_short_en')
                ->comment('Descripción completa en inglés del MLS');
            $table->text('description_short_es')->nullable()->after('description_full_en')
                ->comment('Descripción corta en español del MLS');
            $table->mediumText('description_full_es')->nullable()->after('description_short_es')
                ->comment('Descripción completa en español del MLS');
            
            // Tamaños en pies (además de metros que ya existen)
            $table->decimal('lot_feet', 12, 2)->nullable()->after('lot_size')
                ->comment('Tamaño del lote en pies cuadrados');
            $table->decimal('construction_feet', 12, 2)->nullable()->after('construction_size')
                ->comment('Tamaño de construcción en pies cuadrados');
            
            // Campos adicionales del MLS no mapeados previamente
            $table->boolean('for_rent')->nullable()->after('allow_integration')
                ->comment('Si la propiedad es de renta');
            $table->integer('year_built')->nullable()->after('age')
                ->comment('Año de construcción');
            $table->string('payment', 50)->nullable()->after('old_price')
                ->comment('Tipo de pago: Any, ALL CASH, FINANCING');
            $table->string('parking_type', 50)->nullable()->after('parking_number')
                ->comment('Tipo de estacionamiento: Any, off_street, on_street');
            $table->string('selling_office_commission', 20)->nullable()->after('payment')
                ->comment('Comisión de la oficina vendedora');
            $table->string('showing_terms', 50)->nullable()->after('selling_office_commission')
                ->comment('Términos de visita: Any, Appointment, Pick Up Keys, Open');
            
            // Video URL del MLS (diferente de virtual_tour_url)
            $table->text('video_url')->nullable()->after('virtual_tour_url')
                ->comment('URL de video de la propiedad del MLS');
        });

        // Cambiar bathrooms de integer a decimal para soportar valores como 2.5
        Schema::table('properties', function (Blueprint $table) {
            $table->decimal('bathrooms', 5, 1)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn([
                'description_short_en',
                'description_full_en',
                'description_short_es',
                'description_full_es',
                'lot_feet',
                'construction_feet',
                'for_rent',
                'year_built',
                'payment',
                'parking_type',
                'selling_office_commission',
                'showing_terms',
                'video_url',
            ]);
        });

        // Revertir bathrooms a integer
        Schema::table('properties', function (Blueprint $table) {
            $table->integer('bathrooms')->nullable()->change();
        });
    }
};
