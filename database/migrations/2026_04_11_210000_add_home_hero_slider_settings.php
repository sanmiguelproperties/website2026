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

        $settings = [
            [
                'setting_key' => 'hero_slider_source_type',
                'setting_group' => 'general',
                'label_es' => 'Hero Slider - Fuente',
                'label_en' => 'Hero Slider - Source',
                'type' => 'text',
                'value_es' => 'properties',
                'value_en' => 'properties',
                'sort_order' => 42,
            ],
            [
                'setting_key' => 'hero_slider_property_ids',
                'setting_group' => 'general',
                'label_es' => 'Hero Slider - Propiedades',
                'label_en' => 'Hero Slider - Properties',
                'type' => 'text',
                'value_es' => '',
                'value_en' => '',
                'sort_order' => 43,
            ],
            [
                'setting_key' => 'hero_slider_image_ids',
                'setting_group' => 'general',
                'label_es' => 'Hero Slider - Imagenes',
                'label_en' => 'Hero Slider - Images',
                'type' => 'text',
                'value_es' => '',
                'value_en' => '',
                'sort_order' => 44,
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('cms_site_settings')->updateOrInsert(
                ['setting_key' => $setting['setting_key']],
                [
                    'setting_group' => $setting['setting_group'],
                    'label_es' => $setting['label_es'],
                    'label_en' => $setting['label_en'],
                    'type' => $setting['type'],
                    'value_es' => $setting['value_es'],
                    'value_en' => $setting['value_en'],
                    'media_asset_id' => null,
                    'sort_order' => $setting['sort_order'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }

        Cache::forget('cms_site_settings');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('cms_site_settings')
            ->whereIn('setting_key', [
                'hero_slider_source_type',
                'hero_slider_property_ids',
                'hero_slider_image_ids',
            ])
            ->delete();

        Cache::forget('cms_site_settings');
    }
};
