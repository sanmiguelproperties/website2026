<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        foreach ($this->settings() as $setting) {
            $exists = DB::table('cms_site_settings')
                ->where('setting_key', $setting['setting_key'])
                ->exists();

            if ($exists) {
                DB::table('cms_site_settings')
                    ->where('setting_key', $setting['setting_key'])
                    ->update(array_merge($setting, [
                        'setting_group' => 'social',
                        'type' => 'url',
                        'updated_at' => $now,
                    ]));

                continue;
            }

            DB::table('cms_site_settings')->insert(array_merge($setting, [
                'setting_group' => 'social',
                'type' => 'url',
                'value_es' => '',
                'value_en' => '',
                'media_asset_id' => null,
                'updated_at' => $now,
                'created_at' => $now,
            ]));
        }

        Cache::forget('cms_site_settings');
    }

    public function down(): void
    {
        DB::table('cms_site_settings')
            ->whereIn('setting_key', array_column($this->settings(), 'setting_key'))
            ->delete();

        Cache::forget('cms_site_settings');
    }

    /**
     * @return array<int, array{setting_key: string, label_es: string, label_en: string, sort_order: int}>
     */
    private function settings(): array
    {
        return [
            [
                'setting_key' => 'social_tiktok',
                'label_es' => 'TikTok',
                'label_en' => 'TikTok',
                'sort_order' => 6,
            ],
            [
                'setting_key' => 'social_pinterest',
                'label_es' => 'Pinterest',
                'label_en' => 'Pinterest',
                'sort_order' => 7,
            ],
        ];
    }
};
