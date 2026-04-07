<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = now();

        DB::table('cms_site_settings')->updateOrInsert(
            ['setting_key' => 'header_logo_height_desktop'],
            [
                'setting_group' => 'header',
                'label_es' => 'Altura del logo en desktop (px)',
                'label_en' => 'Logo height on desktop (px)',
                'type' => 'number',
                'value_es' => '44',
                'value_en' => null,
                'media_asset_id' => null,
                'sort_order' => 1,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        DB::table('cms_site_settings')->updateOrInsert(
            ['setting_key' => 'header_logo_height_mobile'],
            [
                'setting_group' => 'header',
                'label_es' => 'Altura del logo en movil (px)',
                'label_en' => 'Logo height on mobile (px)',
                'type' => 'number',
                'value_es' => '36',
                'value_en' => null,
                'media_asset_id' => null,
                'sort_order' => 2,
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
            ->whereIn('setting_key', [
                'header_logo_height_desktop',
                'header_logo_height_mobile',
            ])
            ->delete();

        Cache::forget('cms_site_settings');
    }
};
