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
        Schema::create('easybroker_property_listing_statuses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agency_id');
            $table->string('easybroker_public_id', 50);

            $table->foreignId('property_id')
                ->nullable()
                ->constrained('properties')
                ->nullOnDelete();

            $table->boolean('published');
            $table->dateTime('easybroker_updated_at');
            $table->dateTime('last_polled_at')->nullable();
            $table->json('raw_payload')->nullable();

            // NOTE: MySQL has a 64-character identifier limit. The default Laravel-generated
            // unique index name for this table exceeds that limit on some servers.
            $table->unique(['agency_id', 'easybroker_public_id'], 'eb_pls_agency_public_unique');

            $table->foreign('agency_id')
                ->references('id')
                ->on('agencies')
                ->cascadeOnDelete();

            $table->index('property_id');
            $table->index('published');
            $table->index('easybroker_updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('easybroker_property_listing_statuses');
    }
};

