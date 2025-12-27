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
        Schema::create('property_media_assets', function (Blueprint $table) {
            $table->unsignedBigInteger('property_id');
            $table->unsignedBigInteger('media_asset_id');

            $table->string('role', 20)->default('image');
            $table->string('title')->nullable();
            $table->integer('position')->nullable();
            $table->char('checksum', 32)->nullable();
            $table->text('source_url')->nullable();
            $table->json('raw_payload')->nullable();

            $table->primary(['property_id', 'media_asset_id']);

            $table->foreign('property_id')
                ->references('id')
                ->on('properties')
                ->cascadeOnDelete();

            $table->foreign('media_asset_id')
                ->references('id')
                ->on('media_assets')
                ->cascadeOnDelete();

            $table->index(['property_id', 'role']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_media_assets');
    }
};

