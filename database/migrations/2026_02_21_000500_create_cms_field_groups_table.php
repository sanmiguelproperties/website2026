<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_field_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('slug', 100)->unique();
            $table->text('description')->nullable();
            $table->enum('location_type', ['page', 'post', 'post_category', 'global']);
            $table->string('location_identifier', 100)->nullable()->comment('Slug of specific page, null = applies to all');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['location_type', 'location_identifier']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_field_groups');
    }
};
