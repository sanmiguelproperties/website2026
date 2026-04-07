<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = now();

        DB::table('cms_site_settings')->updateOrInsert(
            ['setting_key' => 'header_height_desktop'],
            [
                'setting_group' => 'header',
                'label_es' => 'Altura del header en desktop (px)',
                'label_en' => 'Header height on desktop (px)',
                'type' => 'number',
                'value_es' => '80',
                'value_en' => null,
                'media_asset_id' => null,
                'sort_order' => 3,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        Cache::forget('cms_site_settings');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('cms_site_settings')
            ->where('setting_key', 'header_height_desktop')
            ->delete();

        Cache::forget('cms_site_settings');
    }
};
