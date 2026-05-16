<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $features = [
        1 => ['color' => '#D1A054', 'label' => 'Busqueda inteligente'],
        2 => ['color' => '#768D59', 'label' => 'Transacciones seguras'],
        3 => ['color' => '#A52A2A', 'label' => 'Tours virtuales'],
        4 => ['color' => '#5B5B5B', 'label' => 'Asesores expertos'],
        5 => ['color' => '#A52A2A', 'label' => 'Financiamiento flexible'],
        6 => ['color' => '#768D59', 'label' => 'App movil'],
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

            $this->upsertField(
                (int) $group->id,
                "services_feature{$index}_icon",
                'image',
                'Icono - ' . $feature['label'],
                'Icon - ' . $feature['label'],
                'Selecciona un archivo desde el administrador de archivos.',
                'Select a file from the file manager.',
                false,
                $baseSort,
                $now
            );

            $colorFieldId = $this->upsertField(
                (int) $group->id,
                "services_feature{$index}_icon_bg_color",
                'color',
                'Color fondo icono - ' . $feature['label'],
                'Icon background color - ' . $feature['label'],
                'Color solido del recuadro que envuelve el icono.',
                'Solid color for the square around the icon.',
                false,
                $baseSort + 1,
                $now,
                $feature['color']
            );

            $this->ensureValue((int) $colorFieldId, (int) $page->id, $feature['color'], $now);
        }

        Cache::forget('cms_page_home');
    }

    public function down(): void
    {
        $keys = [];
        foreach (array_keys($this->features) as $index) {
            $keys[] = "services_feature{$index}_icon";
            $keys[] = "services_feature{$index}_icon_bg_color";
        }

        $fieldIds = DB::table('cms_field_definitions')
            ->whereIn('field_key', $keys)
            ->pluck('id');

        if ($fieldIds->isEmpty()) {
            return;
        }

        DB::table('cms_field_values')->whereIn('field_definition_id', $fieldIds)->delete();
        DB::table('cms_field_definitions')->whereIn('id', $fieldIds)->delete();

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
        ?string $defaultValue = null
    ): int {
        $payload = [
            'parent_id' => null,
            'type' => $type,
            'label_es' => $labelEs,
            'label_en' => $labelEn,
            'instructions_es' => $instructionsEs,
            'instructions_en' => $instructionsEn,
            'default_value_es' => $defaultValue,
            'default_value_en' => $defaultValue,
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

    private function ensureValue(int $fieldId, int $pageId, string $value, $now): void
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
            'value_es' => $value,
            'value_en' => $value,
            'media_asset_id' => null,
            'parent_value_id' => null,
            'row_index' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
};
