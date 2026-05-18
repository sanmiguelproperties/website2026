<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $features = [
        1 => [
            'title_es' => 'Búsqueda Inteligente',
            'title_en' => 'Smart Search',
            'desc_es' => 'Filtros avanzados y búsqueda por mapa para encontrar exactamente lo que necesitas en segundos.',
            'desc_en' => 'Advanced filters and map search to find exactly what you need in seconds.',
        ],
        2 => [
            'title_es' => 'Transacciones Seguras',
            'title_en' => 'Secure Transactions',
            'desc_es' => 'Proceso de compra transparente con asesoría legal incluida y documentación verificada.',
            'desc_en' => 'Transparent buying process with legal guidance and verified documentation.',
        ],
        3 => [
            'title_es' => 'Tours Virtuales 360°',
            'title_en' => '360 Virtual Tours',
            'desc_es' => 'Recorre las propiedades desde la comodidad de tu hogar con nuestros tours virtuales inmersivos.',
            'desc_en' => 'Explore properties from home with our immersive virtual tours.',
        ],
        4 => [
            'title_es' => 'Asesores Expertos',
            'title_en' => 'Expert Advisors',
            'desc_es' => 'Un equipo de profesionales certificados te acompaña en cada paso del proceso.',
            'desc_en' => 'A team of certified professionals supports you at every step.',
        ],
        5 => [
            'title_es' => 'Financiamiento Flexible',
            'title_en' => 'Flexible Financing',
            'desc_es' => 'Opciones de crédito con las mejores tasas del mercado y planes a tu medida.',
            'desc_en' => 'Credit options with competitive rates and plans tailored to you.',
        ],
        6 => [
            'title_es' => 'App Móvil',
            'title_en' => 'Mobile App',
            'desc_es' => 'Gestiona tus favoritos, agenda visitas y recibe alertas desde cualquier lugar.',
            'desc_en' => 'Manage favorites, schedule visits and receive alerts from anywhere.',
        ],
    ];

    public function up(): void
    {
        $page = DB::table('cms_pages')->where('slug', 'home')->first();
        $targetGroupId = DB::table('cms_field_groups')->where('slug', 'home-services')->value('id');
        $sourceGroupId = DB::table('cms_field_groups')->where('slug', 'home-texts-auto')->value('id');

        if (!$page || !$targetGroupId || !$sourceGroupId) {
            return;
        }

        $now = now();
        $sourceFieldIds = [];

        foreach ($this->fieldDefaults() as $fieldKey => $defaults) {
            $sourceFieldId = DB::table('cms_field_definitions')
                ->where('field_group_id', $sourceGroupId)
                ->where('field_key', $fieldKey)
                ->value('id');

            $targetFieldId = DB::table('cms_field_definitions')
                ->where('field_group_id', $targetGroupId)
                ->where('field_key', $fieldKey)
                ->value('id');

            if (!$sourceFieldId || !$targetFieldId) {
                continue;
            }

            $sourceFieldIds[] = (int) $sourceFieldId;

            $sourceValue = DB::table('cms_field_values')
                ->where('field_definition_id', $sourceFieldId)
                ->where('entity_type', 'page')
                ->where('entity_id', $page->id)
                ->whereNull('parent_value_id')
                ->first();

            if (!$sourceValue) {
                continue;
            }

            $targetValue = DB::table('cms_field_values')
                ->where('field_definition_id', $targetFieldId)
                ->where('entity_type', 'page')
                ->where('entity_id', $page->id)
                ->whereNull('parent_value_id')
                ->first();

            if (!$targetValue) {
                DB::table('cms_field_values')->insert([
                    'field_definition_id' => $targetFieldId,
                    'entity_type' => 'page',
                    'entity_id' => $page->id,
                    'value_es' => $sourceValue->value_es,
                    'value_en' => $sourceValue->value_en,
                    'media_asset_id' => null,
                    'parent_value_id' => null,
                    'row_index' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                continue;
            }

            $targetIsDefault = (string) $targetValue->value_es === $defaults['es']
                && (string) $targetValue->value_en === $defaults['en'];

            $targetIsEmpty = $targetValue->value_es === null && $targetValue->value_en === null;

            if ($targetIsEmpty || $targetIsDefault) {
                DB::table('cms_field_values')
                    ->where('id', $targetValue->id)
                    ->update([
                        'value_es' => $sourceValue->value_es,
                        'value_en' => $sourceValue->value_en,
                        'updated_at' => $now,
                    ]);
            }
        }

        if ($sourceFieldIds) {
            DB::table('cms_field_values')->whereIn('field_definition_id', $sourceFieldIds)->delete();
            DB::table('cms_field_definitions')->whereIn('id', $sourceFieldIds)->delete();
        }

        Cache::forget('cms_page_home');
    }

    public function down(): void
    {
        Cache::forget('cms_page_home');
    }

    private function fieldDefaults(): array
    {
        $defaults = [];

        foreach ($this->features as $index => $feature) {
            $defaults["services_feature{$index}_title"] = [
                'es' => $feature['title_es'],
                'en' => $feature['title_en'],
            ];
            $defaults["services_feature{$index}_desc"] = [
                'es' => $feature['desc_es'],
                'en' => $feature['desc_en'],
            ];
        }

        return $defaults;
    }
};
