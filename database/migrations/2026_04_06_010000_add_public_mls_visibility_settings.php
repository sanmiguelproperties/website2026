<?php

use Illuminate\Database\Migrations\Migration;
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
            ['setting_key' => 'public_show_mls_offices'],
            [
                'setting_group' => 'general',
                'label_es' => 'Mostrar agencias MLS en el sitio',
                'label_en' => 'Show MLS agencies on site',
                'type' => 'boolean',
                'value_es' => '1',
                'value_en' => '1',
                'media_asset_id' => null,
                'sort_order' => 40,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        DB::table('cms_site_settings')->updateOrInsert(
            ['setting_key' => 'public_show_mls_agents'],
            [
                'setting_group' => 'general',
                'label_es' => 'Mostrar agentes MLS en el sitio',
                'label_en' => 'Show MLS agents on site',
                'type' => 'boolean',
                'value_es' => '1',
                'value_en' => '1',
                'media_asset_id' => null,
                'sort_order' => 41,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('cms_site_settings')
            ->whereIn('setting_key', ['public_show_mls_offices', 'public_show_mls_agents'])
            ->delete();
    }
};

