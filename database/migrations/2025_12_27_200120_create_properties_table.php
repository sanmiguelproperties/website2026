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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('agency_id');
            $table->foreign('agency_id')
                ->references('id')
                ->on('agencies')
                ->cascadeOnDelete();

            // El agente es un user con rol `agent`.
            $table->foreignId('agent_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('easybroker_public_id', 50);
            $table->string('easybroker_agent_id', 50)->nullable();

            // Publicación / sync
            $table->boolean('published')->default(false);
            $table->dateTime('easybroker_created_at')->nullable();
            $table->dateTime('easybroker_updated_at')->nullable();
            $table->dateTime('last_synced_at')->nullable();

            // Contenido
            $table->string('title')->nullable();
            $table->mediumText('description')->nullable();
            $table->text('url')->nullable();
            $table->string('ad_type', 50)->nullable();
            $table->string('property_type_name', 100)->nullable();

            // Características numéricas
            $table->integer('bedrooms')->nullable();
            $table->integer('bathrooms')->nullable();
            $table->integer('half_bathrooms')->nullable();
            $table->integer('parking_spaces')->nullable();
            $table->decimal('lot_size', 12, 2)->nullable();
            $table->decimal('construction_size', 12, 2)->nullable();
            $table->decimal('expenses', 14, 2)->nullable();
            $table->decimal('lot_length', 12, 2)->nullable();
            $table->decimal('lot_width', 12, 2)->nullable();
            $table->integer('floors')->nullable();
            $table->string('floor', 20)->nullable();
            $table->string('age', 20)->nullable();

            $table->text('virtual_tour_url')->nullable();

            // Portada (reutiliza media_assets)
            $table->foreignId('cover_media_asset_id')
                ->nullable()
                ->constrained('media_assets')
                ->nullOnDelete();

            $table->json('raw_payload')->nullable();

            $table->timestamps();

            $table->unique(['agency_id', 'easybroker_public_id']);

            $table->index('published');
            $table->index('easybroker_updated_at');
            $table->index('property_type_name');
            $table->index('easybroker_agent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};

