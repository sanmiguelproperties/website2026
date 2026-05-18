<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $features = [
        1 => [
            'label_es' => 'Busqueda inteligente',
            'label_en' => 'Smart search',
            'title_es' => 'Búsqueda Inteligente',
            'title_en' => 'Smart Search',
            'desc_es' => 'Filtros avanzados y búsqueda por mapa para encontrar exactamente lo que necesitas en segundos.',
            'desc_en' => 'Advanced filters and map search to find exactly what you need in seconds.',
        ],
        2 => [
            'label_es' => 'Transacciones seguras',
            'label_en' => 'Secure transactions',
            'title_es' => 'Transacciones Seguras',
            'title_en' => 'Secure Transactions',
            'desc_es' => 'Proceso de compra transparente con asesoría legal incluida y documentación verificada.',
            'desc_en' => 'Transparent buying process with legal guidance and verified documentation.',
        ],
        3 => [
            'label_es' => 'Tours virtuales',
            'label_en' => 'Virtual tours',
            'title_es' => 'Tours Virtuales 360°',
            'title_en' => '360 Virtual Tours',
            'desc_es' => 'Recorre las propiedades desde la comodidad de tu hogar con nuestros tours virtuales inmersivos.',
            'desc_en' => 'Explore properties from home with our immersive virtual tours.',
        ],
        4 => [
            'label_es' => 'Asesores expertos',
            'label_en' => 'Expert advisors',
            'title_es' => 'Asesores Expertos',
            'title_en' => 'Expert Advisors',
            'desc_es' => 'Un equipo de profesionales certificados te acompaña en cada paso del proceso.',
            'desc_en' => 'A team of certified professionals supports you at every step.',
        ],
        5 => [
            'label_es' => 'Financiamiento flexible',
            'label_en' => 'Flexible financing',
            'title_es' => 'Financiamiento Flexible',
            'title_en' => 'Flexible Financing',
            'desc_es' => 'Opciones de crédito con las mejores tasas del mercado y planes a tu medida.',
            'desc_en' => 'Credit options with competitive rates and plans tailored to you.',
        ],
        6 => [
            'label_es' => 'App movil',
            'label_en' => 'Mobile app',
            'title_es' => 'App Móvil',
            'title_en' => 'Mobile App',
            'desc_es' => 'Gestiona tus favoritos, agenda visitas y recibe alertas desde cualquier lugar.',
            'desc_en' => 'Manage favorites, schedule visits and receive alerts from anywhere.',
        ],
    ];

    public function up(): void
    {
        $page = DB::table('cms_pages')->where('slug', 'home')->first();
        $group = DB::table('cms_field_groups')->where('slug', 'home-services')->first();

        if (!$page || !$group) {
            return;
        }

        $now = now();

        foreach ($this->features as $index => $feature) {
            $baseSort = 20 + ($index * 10);

            $titleFieldId = $this->upsertField(
                (int) $group->id,
                "services_feature{$index}_title",
                'text',
                'Titulo - ' . $feature['label_es'],
                'Title - ' . $feature['label_en'],
                'Texto principal de la tarjeta de servicio.',
                'Main text for the service card.',
                true,
                $baseSort,
                $now,
                $feature['title_es'],
                $feature['title_en']
            );

            $descFieldId = $this->upsertField(
                (int) $group->id,
                "services_feature{$index}_desc",
                'textarea',
                'Descripcion - ' . $feature['label_es'],
                'Description - ' . $feature['label_en'],
                'Texto descriptivo que aparece debajo del titulo.',
                'Description shown below the title.',
                true,
                $baseSort + 1,
                $now,
                $feature['desc_es'],
                $feature['desc_en']
            );

            $this->ensureValue((int) $titleFieldId, (int) $page->id, $feature['title_es'], $feature['title_en'], $now);
            $this->ensureValue((int) $descFieldId, (int) $page->id, $feature['desc_es'], $feature['desc_en'], $now);

            DB::table('cms_field_definitions')
                ->where('field_group_id', $group->id)
                ->where('field_key', "services_feature{$index}_icon")
                ->update(['sort_order' => $baseSort + 2, 'updated_at' => $now]);

            DB::table('cms_field_definitions')
                ->where('field_group_id', $group->id)
                ->where('field_key', "services_feature{$index}_icon_bg_color")
                ->update(['sort_order' => $baseSort + 3, 'updated_at' => $now]);
        }

        Cache::forget('cms_page_home');
    }

    public function down(): void
    {
        $group = DB::table('cms_field_groups')->where('slug', 'home-services')->first();

        if (!$group) {
            return;
        }

        $keys = [];
        foreach (array_keys($this->features) as $index) {
            $keys[] = "services_feature{$index}_title";
            $keys[] = "services_feature{$index}_desc";

            $baseSort = 20 + ($index * 10);
            DB::table('cms_field_definitions')
                ->where('field_group_id', $group->id)
                ->where('field_key', "services_feature{$index}_icon")
                ->update(['sort_order' => $baseSort]);

            DB::table('cms_field_definitions')
                ->where('field_group_id', $group->id)
                ->where('field_key', "services_feature{$index}_icon_bg_color")
                ->update(['sort_order' => $baseSort + 1]);
        }

        $fieldIds = DB::table('cms_field_definitions')
            ->where('field_group_id', $group->id)
            ->whereIn('field_key', $keys)
            ->pluck('id');

        if ($fieldIds->isNotEmpty()) {
            DB::table('cms_field_values')->whereIn('field_definition_id', $fieldIds)->delete();
            DB::table('cms_field_definitions')->whereIn('id', $fieldIds)->delete();
        }

        Cache::forget('cms_page_home');
    }

    private function upsertField(
        int $groupId,
        string $fieldKey,
        string $type,
        string $labelEs,
        string $labelEn,
        string $instructionsEs,
        string $instructionsEn,
        bool $isTranslatable,
        int $sortOrder,
        $now,
        ?string $defaultValueEs = null,
        ?string $defaultValueEn = null
    ): int {
        $payload = [
            'parent_id' => null,
            'type' => $type,
            'label_es' => $labelEs,
            'label_en' => $labelEn,
            'instructions_es' => $instructionsEs,
            'instructions_en' => $instructionsEn,
            'default_value_es' => $defaultValueEs,
            'default_value_en' => $defaultValueEn,
            'is_required' => false,
            'is_translatable' => $isTranslatable,
            'sort_order' => $sortOrder,
            'updated_at' => $now,
        ];

        $existingId = DB::table('cms_field_definitions')
            ->where('field_group_id', $groupId)
            ->where('field_key', $fieldKey)
            ->value('id');

        if ($existingId) {
            DB::table('cms_field_definitions')->where('id', $existingId)->update($payload);

            return (int) $existingId;
        }

        return (int) DB::table('cms_field_definitions')->insertGetId(array_merge($payload, [
            'field_group_id' => $groupId,
            'field_key' => $fieldKey,
            'created_at' => $now,
        ]));
    }

    private function ensureValue(int $fieldId, int $pageId, string $valueEs, string $valueEn, $now): void
    {
        $exists = DB::table('cms_field_values')
            ->where('field_definition_id', $fieldId)
            ->where('entity_type', 'page')
            ->where('entity_id', $pageId)
            ->whereNull('parent_value_id')
            ->exists();

        if ($exists) {
            return;
        }

        DB::table('cms_field_values')->insert([
            'field_definition_id' => $fieldId,
            'entity_type' => 'page',
            'entity_id' => $pageId,
            'value_es' => $valueEs,
            'value_en' => $valueEn,
            'media_asset_id' => null,
            'parent_value_id' => null,
            'row_index' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
};
