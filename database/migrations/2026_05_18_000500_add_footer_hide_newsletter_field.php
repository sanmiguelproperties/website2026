<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private string $fieldKey = 'footer_hide_newsletter';

    public function up(): void
    {
        $now = now();
        $pageId = DB::table('cms_pages')->where('slug', 'footer')->value('id');
        $groupId = DB::table('cms_field_groups')->where('slug', 'footer-content')->value('id');

        if (!$pageId || !$groupId) {
            return;
        }

        DB::table('cms_field_definitions')->updateOrInsert(
            ['field_group_id' => $groupId, 'field_key' => $this->fieldKey],
            [
                'parent_id' => null,
                'type' => 'boolean',
                'label_es' => 'Ocultar seccion newsletter',
                'label_en' => 'Hide newsletter section',
                'instructions_es' => 'Activa este boton para ocultar el newsletter del footer en toda la web.',
                'instructions_en' => 'Turn this on to hide the footer newsletter across the website.',
                'is_required' => false,
                'is_translatable' => false,
                'sort_order' => 7,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        $fieldId = DB::table('cms_field_definitions')
            ->where('field_group_id', $groupId)
            ->where('field_key', $this->fieldKey)
            ->value('id');

        if ($fieldId) {
            DB::table('cms_field_values')->updateOrInsert(
                [
                    'field_definition_id' => $fieldId,
                    'entity_type' => 'page',
                    'entity_id' => $pageId,
                    'parent_value_id' => null,
                ],
                [
                    'value_es' => '0',
                    'value_en' => null,
                    'media_asset_id' => null,
                    'row_index' => 0,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }

        Cache::forget('cms_page_footer');
    }

    public function down(): void
    {
        $fieldIds = DB::table('cms_field_definitions')
            ->where('field_key', $this->fieldKey)
            ->pluck('id');

        if ($fieldIds->isNotEmpty()) {
            DB::table('cms_field_values')->whereIn('field_definition_id', $fieldIds)->delete();
            DB::table('cms_field_definitions')->whereIn('id', $fieldIds)->delete();
        }

        Cache::forget('cms_page_footer');
    }
};
