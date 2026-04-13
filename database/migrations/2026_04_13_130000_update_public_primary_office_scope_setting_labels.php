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
        DB::table('cms_site_settings')
            ->where('setting_key', 'public_mls_only_primary_office')
            ->update([
                'label_es' => 'Restringir agentes publicos a la agencia principal',
                'label_en' => 'Restrict public agents to the primary office',
                'updated_at' => now(),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('cms_site_settings')
            ->where('setting_key', 'public_mls_only_primary_office')
            ->update([
                'label_es' => 'Mostrar solo propiedades y agentes de la agencia principal',
                'label_en' => 'Show only properties and agents from the primary office',
                'updated_at' => now(),
            ]);
    }
};