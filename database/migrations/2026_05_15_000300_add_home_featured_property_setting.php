<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('cms_site_settings')->updateOrInsert(
            ['setting_key' => 'home_featured_property_ids'],
            [
                'setting_group' => 'general',
                'label_es' => 'Home - primeras 6 propiedades',
                'label_en' => 'Home - first 6 properties',
                'type' => 'text',
                'value_es' => '',
                'value_en' => '',
                'media_asset_id' => null,
                'sort_order' => 45,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        Cache::forget('cms_site_settings');
    }

    public function down(): void
    {
        DB::table('cms_site_settings')
            ->where('setting_key', 'home_featured_property_ids')
            ->delete();

        Cache::forget('cms_site_settings');
    }
};
