<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $page = DB::table('cms_pages')->where('slug', 'home')->first();
        $group = DB::table('cms_field_groups')->where('slug', 'home-stats')->first();

        if (!$page || !$group) {
            return;
        }

        $now = now();
        $legacyValue = $this->legacyHappyClientsValue((int) $page->id, (int) $group->id);

        $fieldId = DB::table('cms_field_definitions')
            ->where('field_group_id', $group->id)
            ->where('field_key', 'stats_happy_clients_number')
            ->value('id');

        if (!$fieldId) {
            $fieldId = DB::table('cms_field_definitions')->insertGetId([
                'field_group_id' => $group->id,
                'parent_id' => null,
                'field_key' => 'stats_happy_clients_number',
                'type' => 'text',
                'label_es' => 'Clientes felices',
                'label_en' => 'Happy clients',
                'instructions_es' => 'Valor manual del contador Clientes felices.',
                'instructions_en' => 'Manual value for the Happy clients counter.',
                'placeholder_es' => '1000+',
                'placeholder_en' => '1000+',
                'default_value_es' => '1000+',
                'default_value_en' => '1000+',
                'validation_rules' => null,
                'options' => null,
                'is_required' => false,
                'is_translatable' => true,
                'char_limit' => 30,
                'sort_order' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } else {
            DB::table('cms_field_definitions')
                ->where('id', $fieldId)
                ->update([
                    'type' => 'text',
                    'label_es' => 'Clientes felices',
                    'label_en' => 'Happy clients',
                    'instructions_es' => 'Valor manual del contador Clientes felices.',
                    'instructions_en' => 'Manual value for the Happy clients counter.',
                    'is_translatable' => true,
                    'char_limit' => 30,
                    'sort_order' => 0,
                    'updated_at' => $now,
                ]);
        }

        $existingValue = DB::table('cms_field_values')
            ->where('field_definition_id', $fieldId)
            ->where('entity_type', 'page')
            ->where('entity_id', $page->id)
            ->whereNull('parent_value_id')
            ->first();

        if (!$existingValue) {
            DB::table('cms_field_values')->insert([
                'field_definition_id' => $fieldId,
                'entity_type' => 'page',
                'entity_id' => $page->id,
                'value_es' => $legacyValue['es'],
                'value_en' => $legacyValue['en'],
                'media_asset_id' => null,
                'parent_value_id' => null,
                'row_index' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        $fieldIds = DB::table('cms_field_definitions')
            ->where('field_key', 'stats_happy_clients_number')
            ->pluck('id');

        if ($fieldIds->isEmpty()) {
            return;
        }

        DB::table('cms_field_values')->whereIn('field_definition_id', $fieldIds)->delete();
        DB::table('cms_field_definitions')->whereIn('id', $fieldIds)->delete();
    }

    private function legacyHappyClientsValue(int $pageId, int $groupId): array
    {
        $repeaterId = DB::table('cms_field_definitions')
            ->where('field_group_id', $groupId)
            ->where('field_key', 'stats_items')
            ->value('id');

        $numberId = DB::table('cms_field_definitions')
            ->where('field_group_id', $groupId)
            ->where('field_key', 'stat_number')
            ->value('id');

        $labelId = DB::table('cms_field_definitions')
            ->where('field_group_id', $groupId)
            ->where('field_key', 'stat_label')
            ->value('id');

        if (!$repeaterId || !$numberId || !$labelId) {
            return ['es' => '1000+', 'en' => '1000+'];
        }

        $parents = DB::table('cms_field_values')
            ->where('field_definition_id', $repeaterId)
            ->where('entity_type', 'page')
            ->where('entity_id', $pageId)
            ->whereNull('parent_value_id')
            ->orderBy('row_index')
            ->get();

        $fallback = ['es' => '1000+', 'en' => '1000+'];

        foreach ($parents as $parent) {
            $number = DB::table('cms_field_values')
                ->where('field_definition_id', $numberId)
                ->where('parent_value_id', $parent->id)
                ->first();

            $label = DB::table('cms_field_values')
                ->where('field_definition_id', $labelId)
                ->where('parent_value_id', $parent->id)
                ->first();

            if ((int) $parent->row_index === 2 && $number) {
                $fallback = [
                    'es' => $number->value_es ?: $fallback['es'],
                    'en' => $number->value_en ?: $number->value_es ?: $fallback['en'],
                ];
            }

            $labelEs = strtolower((string) ($label->value_es ?? ''));
            $labelEn = strtolower((string) ($label->value_en ?? ''));

            if ($number && (str_contains($labelEs, 'clientes felices') || str_contains($labelEn, 'happy clients'))) {
                return [
                    'es' => $number->value_es ?: '1000+',
                    'en' => $number->value_en ?: $number->value_es ?: '1000+',
                ];
            }
        }

        return $fallback;
    }
};
