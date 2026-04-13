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
            ['setting_key' => 'public_mls_only_primary_office'],
            [
                'setting_group' => 'general',
                'label_es' => 'Restringir agentes publicos a la agencia principal',
                'label_en' => 'Restrict public agents to the primary office',
                'type' => 'boolean',
                'value_es' => '0',
                'value_en' => '0',
                'media_asset_id' => null,
                'sort_order' => 42,
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
            ->where('setting_key', 'public_mls_only_primary_office')
            ->delete();
    }
};
