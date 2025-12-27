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
        Schema::create('features', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('locale', 10)->nullable();
            $table->timestamps();

            $table->unique(['name', 'locale']);
        });

        Schema::create('property_feature', function (Blueprint $table) {
            $table->unsignedBigInteger('property_id');
            $table->unsignedBigInteger('feature_id');

            $table->primary(['property_id', 'feature_id']);

            $table->foreign('property_id')
                ->references('id')
                ->on('properties')
                ->cascadeOnDelete();

            $table->foreign('feature_id')
                ->references('id')
                ->on('features')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_feature');
        Schema::dropIfExists('features');
    }
};

