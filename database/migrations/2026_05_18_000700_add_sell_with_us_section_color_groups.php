<?php

use App\Models\FrontendColorSetting;
use App\Services\CmsService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $defaults = FrontendColorSetting::getDefaultColorsForView('sell-with-us');

        $configs = DB::table('frontend_color_settings')
            ->where('view_slug', 'sell-with-us')
            ->get();

        if ($configs->isEmpty()) {
            DB::table('frontend_color_settings')->insert([
                'name' => 'Default Vende con nosotros',
                'description' => 'Colores para la pagina de vendedores separados por seccion',
                'view_slug' => 'sell-with-us',
                'colors' => json_encode($defaults, JSON_UNESCAPED_SLASHES),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } else {
            foreach ($configs as $config) {
                $colors = json_decode((string) $config->colors, true);
                $colors = is_array($colors) ? $colors : [];
                $merged = array_replace_recursive($defaults, $colors);

                if ($merged === $colors) {
                    continue;
                }

                DB::table('frontend_color_settings')
                    ->where('id', $config->id)
                    ->update([
                        'colors' => json_encode($merged, JSON_UNESCAPED_SLASHES),
                        'updated_at' => $now,
                    ]);
            }
        }

        $heroGroupId = DB::table('cms_field_groups')
            ->where('slug', 'sell-with-us-hero')
            ->value('id');

        if (! $heroGroupId) {
            $heroGroupId = DB::table('cms_field_groups')->insertGetId([
                'name' => 'Vende con nosotros - Hero',
                'slug' => 'sell-with-us-hero',
                'description' => 'Encabezado principal, imagen y llamada a la accion.',
                'location_type' => 'page',
                'location_identifier' => 'sell-with-us',
                'sort_order' => 10,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $heroImageField = DB::table('cms_field_definitions')
            ->where('field_group_id', $heroGroupId)
            ->where('field_key', 'seller_hero_image')
            ->first();

        $heroImagePayload = [
            'parent_id' => null,
            'type' => 'image',
            'label_es' => 'Imagen de fondo del hero',
            'label_en' => 'Hero background image',
            'instructions_es' => 'Selecciona la imagen de fondo del hero de la pagina Vende con nosotros.',
            'instructions_en' => 'Select the hero background image for the Sell With Us page.',
            'is_required' => false,
            'is_translatable' => false,
            'sort_order' => 50,
            'updated_at' => $now,
        ];

        if ($heroImageField) {
            DB::table('cms_field_definitions')
                ->where('id', $heroImageField->id)
                ->update($heroImagePayload);
        } else {
            DB::table('cms_field_definitions')->insert(array_merge($heroImagePayload, [
                'field_group_id' => $heroGroupId,
                'field_key' => 'seller_hero_image',
                'created_at' => $now,
            ]));
        }

        FrontendColorSetting::clearCacheForView('sell-with-us');
        CmsService::clearPageCache('sell-with-us');
    }

    public function down(): void
    {
        // Keep existing admin color values and CMS instructions intact.
    }
};
