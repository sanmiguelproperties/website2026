<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $pageId = DB::table('cms_pages')->where('slug', 'footer')->value('id');

        if (!$pageId) {
            return;
        }

        DB::table('cms_field_groups')->updateOrInsert(
            ['slug' => 'footer-partners'],
            [
                'name' => 'Empresas que trabajan con nosotros',
                'description' => 'Logos, enlaces externos y fondo de la franja superior del footer.',
                'location_type' => 'page',
                'location_identifier' => 'footer',
                'sort_order' => 0,
                'is_active' => true,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        $groupId = DB::table('cms_field_groups')->where('slug', 'footer-partners')->value('id');

        if (!$groupId) {
            return;
        }

        $bgFieldId = $this->upsertField($groupId, [
            'field_key' => 'footer_partners_bg_color',
            'type' => 'color',
            'label_es' => 'Color de fondo',
            'label_en' => 'Background color',
            'instructions_es' => 'Color de fondo para la seccion de empresas del footer.',
            'instructions_en' => 'Background color for the footer partner companies section.',
            'is_translatable' => false,
            'sort_order' => 1,
        ], $now);

        DB::table('cms_field_values')->updateOrInsert(
            [
                'field_definition_id' => $bgFieldId,
                'entity_type' => 'page',
                'entity_id' => $pageId,
                'parent_value_id' => null,
            ],
            [
                'value_es' => '#020202',
                'value_en' => null,
                'media_asset_id' => null,
                'row_index' => 0,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        $repeaterId = $this->upsertField($groupId, [
            'field_key' => 'footer_partner_items',
            'type' => 'repeater',
            'label_es' => 'Empresas',
            'label_en' => 'Partner companies',
            'instructions_es' => 'Agrega las empresas que trabajan con nosotros. Cada logo puede abrir un link externo.',
            'instructions_en' => 'Add partner companies. Each logo can open an external link.',
            'is_translatable' => false,
            'sort_order' => 10,
        ], $now);

        $children = [
            [
                'field_key' => 'footer_partner_name',
                'type' => 'text',
                'label_es' => 'Nombre de la empresa',
                'label_en' => 'Company name',
                'instructions_es' => 'Se usa como texto alternativo y tooltip.',
                'instructions_en' => 'Used as alt text and tooltip.',
                'is_translatable' => false,
                'sort_order' => 1,
            ],
            [
                'field_key' => 'footer_partner_logo',
                'type' => 'image',
                'label_es' => 'Logo blanco',
                'label_en' => 'White logo',
                'instructions_es' => 'Sube preferiblemente un PNG/SVG blanco con fondo transparente.',
                'instructions_en' => 'Prefer a white PNG/SVG with transparent background.',
                'is_translatable' => false,
                'sort_order' => 2,
            ],
            [
                'field_key' => 'footer_partner_url',
                'type' => 'url',
                'label_es' => 'Link externo',
                'label_en' => 'External link',
                'instructions_es' => 'Usa una URL completa, por ejemplo https://empresa.com.',
                'instructions_en' => 'Use a complete URL, for example https://company.com.',
                'is_translatable' => false,
                'sort_order' => 3,
            ],
        ];

        foreach ($children as $child) {
            $this->upsertField($groupId, array_merge($child, ['parent_id' => $repeaterId]), $now);
        }

        Cache::forget('cms_page_footer');
    }

    public function down(): void
    {
        $groupId = DB::table('cms_field_groups')->where('slug', 'footer-partners')->value('id');

        if ($groupId) {
            $fieldIds = DB::table('cms_field_definitions')
                ->where('field_group_id', $groupId)
                ->pluck('id');

            if ($fieldIds->isNotEmpty()) {
                DB::table('cms_field_values')->whereIn('field_definition_id', $fieldIds)->delete();
                DB::table('cms_field_definitions')->whereIn('id', $fieldIds)->delete();
            }

            DB::table('cms_field_groups')->where('id', $groupId)->delete();
        }

        Cache::forget('cms_page_footer');
    }

    /**
     * @param array<string, mixed> $data
     */
    private function upsertField(int $groupId, array $data, object $now): int
    {
        DB::table('cms_field_definitions')->updateOrInsert(
            [
                'field_group_id' => $groupId,
                'field_key' => $data['field_key'],
            ],
            [
                'parent_id' => $data['parent_id'] ?? null,
                'type' => $data['type'],
                'label_es' => $data['label_es'],
                'label_en' => $data['label_en'] ?? null,
                'instructions_es' => $data['instructions_es'] ?? null,
                'instructions_en' => $data['instructions_en'] ?? null,
                'is_required' => false,
                'is_translatable' => $data['is_translatable'] ?? true,
                'sort_order' => $data['sort_order'] ?? 0,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        return (int) DB::table('cms_field_definitions')
            ->where('field_group_id', $groupId)
            ->where('field_key', $data['field_key'])
            ->value('id');
    }
};
