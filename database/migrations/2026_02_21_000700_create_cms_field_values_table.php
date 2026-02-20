<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_field_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('field_definition_id')->constrained('cms_field_definitions')->cascadeOnDelete();
            $table->string('entity_type', 50)->comment('page, post, global');
            $table->unsignedBigInteger('entity_id')->nullable()->comment('FK to cms_pages.id or cms_posts.id, null for global');
            $table->longText('value_es')->nullable();
            $table->longText('value_en')->nullable();
            $table->foreignId('media_asset_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->foreignId('parent_value_id')->nullable()->constrained('cms_field_values')->cascadeOnDelete();
            $table->unsignedInteger('row_index')->default(0)->comment('Order within repeater rows');
            $table->timestamps();

            $table->index(['entity_type', 'entity_id']);
            $table->index('field_definition_id');
            $table->index('parent_value_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_field_values');
    }
};
