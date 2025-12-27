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
        Schema::create('property_operations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('property_id')
                ->constrained('properties')
                ->cascadeOnDelete();

            $table->string('operation_type', 20);
            $table->decimal('amount', 18, 2)->nullable();

            // Reutiliza tu catálogo actual de monedas.
            $table->foreignId('currency_id')
                ->nullable()
                ->constrained('currencies')
                ->nullOnDelete();

            // Respaldo: código crudo que llega de EasyBroker.
            $table->char('currency_code', 3)->nullable();

            $table->string('formatted_amount', 50)->nullable();
            $table->string('unit', 20)->nullable();
            $table->json('raw_payload')->nullable();

            $table->timestamps();

            $table->index('operation_type');
            $table->index('currency_id');
            $table->index('currency_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_operations');
    }
};

