<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('cms_site_settings')
            ->where('setting_key', 'contact_address')
            ->update([
                'label_es' => 'Direccion - Oficina corporativa',
                'label_en' => 'Address - Corporate office',
                'sort_order' => 5,
            ]);

        DB::table('cms_site_settings')->updateOrInsert(
            ['setting_key' => 'contact_address_center'],
            [
                'setting_group' => 'contact',
                'label_es' => 'Direccion - Oficina centro',
                'label_en' => 'Address - Downtown office',
                'type' => 'textarea',
                'value_es' => '',
                'value_en' => '',
                'media_asset_id' => null,
                'sort_order' => 6,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        Cache::forget('cms_site_settings');
    }

    public function down(): void
    {
        DB::table('cms_site_settings')
            ->where('setting_key', 'contact_address_center')
            ->delete();

        DB::table('cms_site_settings')
            ->where('setting_key', 'contact_address')
            ->update([
                'label_es' => 'Direccion',
                'label_en' => 'Address',
                'sort_order' => 5,
            ]);

        Cache::forget('cms_site_settings');
    }
};
