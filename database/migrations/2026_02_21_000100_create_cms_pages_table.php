<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 100)->unique();
            $table->string('title_es', 255);
            $table->string('title_en', 255)->nullable();
            $table->string('meta_title_es', 255)->nullable();
            $table->string('meta_title_en', 255)->nullable();
            $table->text('meta_description_es')->nullable();
            $table->text('meta_description_en')->nullable();
            $table->string('meta_keywords_es', 500)->nullable();
            $table->string('meta_keywords_en', 500)->nullable();
            $table->string('template', 100)->nullable()->comment('Blade template name');
            $table->enum('status', ['draft', 'published', 'archived'])->default('published');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_pages');
    }
};
