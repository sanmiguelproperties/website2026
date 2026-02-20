<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_field_definitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('field_group_id')->constrained('cms_field_groups')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('cms_field_definitions')->cascadeOnDelete();
            $table->string('field_key', 100);
            $table->string('type', 50)->comment('text, textarea, wysiwyg, number, url, email, phone, image, gallery, file, select, checkbox, radio, boolean, color, date, datetime, link, repeater, group, icon');
            $table->string('label_es', 255);
            $table->string('label_en', 255)->nullable();
            $table->text('instructions_es')->nullable()->comment('Help text for admin');
            $table->text('instructions_en')->nullable();
            $table->string('placeholder_es', 255)->nullable();
            $table->string('placeholder_en', 255)->nullable();
            $table->text('default_value_es')->nullable();
            $table->text('default_value_en')->nullable();
            $table->json('validation_rules')->nullable()->comment('JSON: {min, max, regex, etc.}');
            $table->json('options')->nullable()->comment('JSON: choices for select/radio/checkbox, config for repeater, etc.');
            $table->boolean('is_required')->default(false);
            $table->boolean('is_translatable')->default(true)->comment('false for numbers, colors, booleans');
            $table->unsignedInteger('char_limit')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['field_group_id', 'field_key']);
            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_field_definitions');
    }
};
