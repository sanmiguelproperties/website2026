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
        Schema::create('frontend_color_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique()->default('default');
            $table->text('description')->nullable();
            $table->json('colors'); // Almacena todos los colores del frontend como JSON
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('frontend_color_settings');
    }
};
