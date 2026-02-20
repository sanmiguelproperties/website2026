<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting_key', 100)->unique();
            $table->string('setting_group', 50)->comment('contact, social, general, seo, company');
            $table->string('label_es', 255);
            $table->string('label_en', 255)->nullable();
            $table->string('type', 50)->comment('text, textarea, image, url, email, phone, boolean');
            $table->text('value_es')->nullable();
            $table->text('value_en')->nullable();
            $table->foreignId('media_asset_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('setting_group');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_site_settings');
    }
};
