<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_post_tag', function (Blueprint $table) {
            $table->foreignId('cms_post_id')->constrained('cms_posts')->cascadeOnDelete();
            $table->foreignId('cms_post_tag_id')->constrained('cms_post_tags')->cascadeOnDelete();
            $table->primary(['cms_post_id', 'cms_post_tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_post_tag');
    }
};
