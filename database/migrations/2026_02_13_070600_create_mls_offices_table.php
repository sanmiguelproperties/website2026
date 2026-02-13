<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea tabla para "Offices" del MLS AMPI (equivalente a agencias/oficinas).
     *
     * Basado en payload real del API:
     * - GET /api/v1/offices
     * - GET /api/v1/offices/{id}
     */
    public function up(): void
    {
        Schema::create('mls_offices', function (Blueprint $table) {
            // Usamos el id del MLS como PK (no autoincrement).
            $table->unsignedBigInteger('mls_office_id')->primary();

            $table->string('name')->nullable();
            $table->string('business_hours')->nullable();
            $table->string('state_province', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->text('address')->nullable();
            $table->string('zip_code', 20)->nullable();

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // El API devuelve la imagen como path relativo (ej: "offices/xxx.jpg").
            $table->text('image_path')->nullable();
            $table->text('image_url')->nullable();

            $table->foreignId('image_media_asset_id')->nullable()
                ->constrained('media_assets')
                ->nullOnDelete();

            $table->longText('description')->nullable();
            $table->longText('description_es')->nullable();

            $table->string('phone_1', 50)->nullable();
            $table->string('phone_2', 50)->nullable();
            $table->string('phone_3', 50)->nullable();
            $table->string('fax', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();

            $table->string('facebook')->nullable();
            $table->string('youtube')->nullable();
            $table->string('x_twitter')->nullable();
            $table->string('tiktok')->nullable();
            $table->string('instagram')->nullable();

            $table->boolean('paid')->default(false);

            // Timestamps propios del MLS (pueden ser null)
            $table->dateTime('mls_created_at')->nullable();
            $table->dateTime('mls_updated_at')->nullable();
            $table->dateTime('last_synced_at')->nullable();

            $table->json('raw_payload')->nullable();

            $table->timestamps();

            $table->index('name');
            $table->index('city');
            $table->index('state_province');
            $table->index('paid');
            $table->index('last_synced_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mls_offices');
    }
};

