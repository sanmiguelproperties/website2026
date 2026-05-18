<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $fields = [
        [
            'group_slug' => 'home-cta-sale',
            'field_key' => 'cta_sale_image',
            'label_es' => 'Imagen de fondo',
            'label_en' => 'Background image',
            'instructions_es' => 'Imagen principal de la seccion Propiedades en Venta.',
            'instructions_en' => 'Main image for the Properties for Sale section.',
            'sort_order' => 0,
        ],
        [
            'group_slug' => 'home-cta-rent',
            'field_key' => 'cta_rent_image',
            'label_es' => 'Imagen de fondo',
            'label_en' => 'Background image',
            'instructions_es' => 'Imagen principal de la seccion Propiedades en Renta.',
            'instructions_en' => 'Main image for the Properties for Rent section.',
            'sort_order' => 0,
        ],
        [
            'group_slug' => 'home-about',
            'field_key' => 'home_about_image',
            'label_es' => 'Imagen principal',
            'label_en' => 'Main image',
            'instructions_es' => 'Imagen de la seccion Sobre Nosotros del inicio.',
            'instructions_en' => 'Image for the About section on the home page.',
            'sort_order' => 0,
        ],
    ];

    public function up(): void
    {
        $page = DB::table('cms_pages')->where('slug', 'home')->first();

        if (!$page) {
            return;
        }

        $now = now();

        foreach ($this->fields as $field) {
            $groupId = DB::table('cms_field_groups')
                ->where('slug', $field['group_slug'])
                ->value('id');

            if (!$groupId) {
                continue;
            }

            $this->upsertField((int) $groupId, $field, $now);
        }

        Cache::forget('cms_page_home');
    }

    public function down(): void
    {
        $fieldIds = DB::table('cms_field_definitions')
            ->whereIn('field_key', array_column($this->fields, 'field_key'))
            ->pluck('id');

        if ($fieldIds->isNotEmpty()) {
            DB::table('cms_field_values')->whereIn('field_definition_id', $fieldIds)->delete();
            DB::table('cms_field_definitions')->whereIn('id', $fieldIds)->delete();
        }

        Cache::forget('cms_page_home');
    }

    private function upsertField(int $groupId, array $field, $now): int
    {
        $payload = [
            'parent_id' => null,
            'type' => 'image',
            'label_es' => $field['label_es'],
            'label_en' => $field['label_en'],
            'instructions_es' => $field['instructions_es'],
            'instructions_en' => $field['instructions_en'],
            'default_value_es' => null,
            'default_value_en' => null,
            'is_required' => false,
            'is_translatable' => false,
            'sort_order' => $field['sort_order'],
            'updated_at' => $now,
        ];

        $existingId = DB::table('cms_field_definitions')
            ->where('field_group_id', $groupId)
            ->where('field_key', $field['field_key'])
            ->value('id');

        if ($existingId) {
            DB::table('cms_field_definitions')->where('id', $existingId)->update($payload);

            return (int) $existingId;
        }

        return (int) DB::table('cms_field_definitions')->insertGetId(array_merge($payload, [
            'field_group_id' => $groupId,
            'field_key' => $field['field_key'],
            'created_at' => $now,
        ]));
    }
};
