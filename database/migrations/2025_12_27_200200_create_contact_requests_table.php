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
        Schema::create('contact_requests', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('agency_id')->nullable();
            $table->foreign('agency_id')
                ->references('id')
                ->on('agencies')
                ->nullOnDelete();

            $table->foreignId('property_id')
                ->nullable()
                ->constrained('properties')
                ->nullOnDelete();

            $table->string('property_public_id', 50);

            $table->string('remote_id', 100)->unique();
            $table->string('source', 100)->nullable();

            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->text('message');

            $table->dateTime('happened_at')->nullable();
            $table->string('status', 50)->nullable();
            $table->dateTime('sent_to_easybroker_at')->nullable();
            $table->json('raw_payload')->nullable();

            $table->timestamps();

            $table->index('property_public_id');
            $table->index('property_id');
            $table->index('happened_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_requests');
    }
};

