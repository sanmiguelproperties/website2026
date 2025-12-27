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
        Schema::create('locations_catalog', function (Blueprint $table) {
            // Usamos un id numérico para facilitar controladores REST y route-model-binding.
            $table->id();

            // Clave natural (EasyBroker) para deduplicar.
            $table->string('full_name', 255)->unique();

            $table->string('name', 255);
            $table->enum('type', ['Country', 'State', 'City', 'Neighborhood']);

            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('locations_catalog')
                ->nullOnDelete();

            $table->timestamps();

            $table->index('type');
            $table->index('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations_catalog');
    }
};

