<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_posts', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 255)->unique();
            $table->string('title_es', 255);
            $table->string('title_en', 255)->nullable();
            $table->text('excerpt_es')->nullable();
            $table->text('excerpt_en')->nullable();
            $table->longText('body_es')->nullable();
            $table->longText('body_en')->nullable();
            $table->foreignId('cover_media_asset_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['draft', 'published', 'scheduled', 'archived'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->string('meta_title_es', 255)->nullable();
            $table->string('meta_title_en', 255)->nullable();
            $table->text('meta_description_es')->nullable();
            $table->text('meta_description_en')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('status');
            $table->index('is_featured');
            $table->index('published_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_posts');
    }
};
