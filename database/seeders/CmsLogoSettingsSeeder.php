<?php

namespace Database\Seeders;

use App\Models\CmsSiteSetting;
use Illuminate\Database\Seeder;

class CmsLogoSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $logos = [
            [
                'setting_key' => 'site_logo',
                'setting_group' => 'general',
                'label_es' => 'Logo principal (Header)',
                'label_en' => 'Main Logo (Header)',
                'type' => 'image',
                'value_es' => null,
                'sort_order' => 4,
            ],
            [
                'setting_key' => 'site_logo_dark',
                'setting_group' => 'general',
                'label_es' => 'Logo tema oscuro (Footer)',
                'label_en' => 'Dark Logo (Footer)',
                'type' => 'image',
                'value_es' => null,
                'sort_order' => 5,
            ],
            [
                'setting_key' => 'site_favicon',
                'setting_group' => 'general',
                'label_es' => 'Favicon',
                'label_en' => 'Favicon',
                'type' => 'image',
                'value_es' => null,
                'sort_order' => 6,
            ],
        ];

        foreach ($logos as $logo) {
            CmsSiteSetting::updateOrCreate(
                ['setting_key' => $logo['setting_key']],
                $logo
            );
        }

        $this->command->info('✅ Logo settings creados: site_logo, site_logo_dark, site_favicon');
    }
}
