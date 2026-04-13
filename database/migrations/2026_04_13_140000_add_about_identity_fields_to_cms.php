<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $aboutPage = DB::table('cms_pages')->where('slug', 'about')->first();
        if (!$aboutPage) {
            return;
        }

        DB::table('cms_field_groups')
            ->where('slug', 'about-values')
            ->update(['sort_order' => 4, 'updated_at' => $now]);

        DB::table('cms_field_groups')
            ->where('slug', 'about-timeline')
            ->update(['sort_order' => 5, 'updated_at' => $now]);

        DB::table('cms_field_groups')
            ->where('slug', 'about-team')
            ->update(['sort_order' => 6, 'updated_at' => $now]);

        DB::table('cms_field_groups')
            ->where('slug', 'about-cta')
            ->update(['sort_order' => 7, 'updated_at' => $now]);

        $groupId = $this->upsertIdentityGroup($now);

        $fields = [
            [
                'field_key' => 'about_identity_badge',
                'type' => 'text',
                'label_es' => 'Badge',
                'label_en' => 'Badge',
                'value_es' => 'Lo que nos define',
                'value_en' => 'What defines us',
            ],
            [
                'field_key' => 'about_identity_title',
                'type' => 'text',
                'label_es' => 'Titulo de seccion',
                'label_en' => 'Section title',
                'value_es' => 'Historia, mision y vision',
                'value_en' => 'History, mission and vision',
            ],
            [
                'field_key' => 'about_identity_subtitle',
                'type' => 'textarea',
                'label_es' => 'Subtitulo de seccion',
                'label_en' => 'Section subtitle',
                'value_es' => 'Los principios detras de cada recomendacion y cada cierre.',
                'value_en' => 'The principles behind each recommendation and every close.',
            ],
            [
                'field_key' => 'about_history_title',
                'type' => 'text',
                'label_es' => 'Titulo Historia',
                'label_en' => 'History title',
                'value_es' => 'Historia',
                'value_en' => 'History',
            ],
            [
                'field_key' => 'about_history_text',
                'type' => 'textarea',
                'label_es' => 'Texto Historia',
                'label_en' => 'History text',
                'value_es' => 'Desde nuestros inicios hemos evolucionado con procesos claros y enfoque total en el cliente.',
                'value_en' => 'Since our beginnings, we have evolved with clear processes and a client-first mindset.',
            ],
            [
                'field_key' => 'about_mission_title',
                'type' => 'text',
                'label_es' => 'Titulo Mision',
                'label_en' => 'Mission title',
                'value_es' => 'Mision',
                'value_en' => 'Mission',
            ],
            [
                'field_key' => 'about_mission_text',
                'type' => 'textarea',
                'label_es' => 'Texto Mision',
                'label_en' => 'Mission text',
                'value_es' => 'Guiar a cada cliente con asesoria transparente y resultados medibles en cada operacion.',
                'value_en' => 'Guide each client with transparent advice and measurable results in every transaction.',
            ],
            [
                'field_key' => 'about_vision_title',
                'type' => 'text',
                'label_es' => 'Titulo Vision',
                'label_en' => 'Vision title',
                'value_es' => 'Vision',
                'value_en' => 'Vision',
            ],
            [
                'field_key' => 'about_vision_text',
                'type' => 'textarea',
                'label_es' => 'Texto Vision',
                'label_en' => 'Vision text',
                'value_es' => 'Ser el aliado inmobiliario mas confiable de la region, combinando personas y tecnologia.',
                'value_en' => 'Be the most trusted real estate partner in the region, powered by people and technology.',
            ],
        ];

        foreach ($fields as $index => $field) {
            $fieldId = $this->upsertFieldDefinition($groupId, $field, $index, $now);
            $this->insertDefaultValueIfMissing($fieldId, (int) $aboutPage->id, $field['value_es'], $field['value_en'], $now);
        }
    }

    public function down(): void
    {
        $now = now();

        DB::table('cms_field_groups')
            ->where('slug', 'about-values')
            ->update(['sort_order' => 3, 'updated_at' => $now]);

        DB::table('cms_field_groups')
            ->where('slug', 'about-timeline')
            ->update(['sort_order' => 4, 'updated_at' => $now]);

        DB::table('cms_field_groups')
            ->where('slug', 'about-team')
            ->update(['sort_order' => 5, 'updated_at' => $now]);

        DB::table('cms_field_groups')
            ->where('slug', 'about-cta')
            ->update(['sort_order' => 6, 'updated_at' => $now]);

        $group = DB::table('cms_field_groups')->where('slug', 'about-identity')->first();
        if (!$group) {
            return;
        }

        $fieldIds = DB::table('cms_field_definitions')
            ->where('field_group_id', $group->id)
            ->pluck('id');

        if ($fieldIds->isNotEmpty()) {
            DB::table('cms_field_values')->whereIn('field_definition_id', $fieldIds)->delete();
            DB::table('cms_field_definitions')->whereIn('id', $fieldIds)->delete();
        }

        DB::table('cms_field_groups')->where('id', $group->id)->delete();
    }

    private function upsertIdentityGroup($now): int
    {
        $group = DB::table('cms_field_groups')->where('slug', 'about-identity')->first();

        if ($group) {
            DB::table('cms_field_groups')
                ->where('id', $group->id)
                ->update([
                    'name' => 'Historia, Mision y Vision',
                    'description' => 'Campos para historia, mision y vision de la pagina Nosotros.',
                    'location_type' => 'page',
                    'location_identifier' => 'about',
                    'sort_order' => 3,
                    'is_active' => true,
                    'updated_at' => $now,
                ]);

            return (int) $group->id;
        }

        return (int) DB::table('cms_field_groups')->insertGetId([
            'name' => 'Historia, Mision y Vision',
            'slug' => 'about-identity',
            'description' => 'Campos para historia, mision y vision de la pagina Nosotros.',
            'location_type' => 'page',
            'location_identifier' => 'about',
            'sort_order' => 3,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function upsertFieldDefinition(int $groupId, array $field, int $sortOrder, $now): int
    {
        $existing = DB::table('cms_field_definitions')
            ->where('field_group_id', $groupId)
            ->where('field_key', $field['field_key'])
            ->first();

        if ($existing) {
            DB::table('cms_field_definitions')
                ->where('id', $existing->id)
                ->update([
                    'type' => $field['type'],
                    'label_es' => $field['label_es'],
                    'label_en' => $field['label_en'],
                    'is_required' => false,
                    'is_translatable' => true,
                    'sort_order' => $sortOrder,
                    'updated_at' => $now,
                ]);

            return (int) $existing->id;
        }

        return (int) DB::table('cms_field_definitions')->insertGetId([
            'field_group_id' => $groupId,
            'parent_id' => null,
            'field_key' => $field['field_key'],
            'type' => $field['type'],
            'label_es' => $field['label_es'],
            'label_en' => $field['label_en'],
            'is_required' => false,
            'is_translatable' => true,
            'sort_order' => $sortOrder,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function insertDefaultValueIfMissing(int $fieldId, int $aboutPageId, string $valueEs, string $valueEn, $now): void
    {
        $exists = DB::table('cms_field_values')
            ->where('field_definition_id', $fieldId)
            ->where('entity_type', 'page')
            ->where('entity_id', $aboutPageId)
            ->whereNull('parent_value_id')
            ->exists();

        if ($exists) {
            return;
        }

        DB::table('cms_field_values')->insert([
            'field_definition_id' => $fieldId,
            'entity_type' => 'page',
            'entity_id' => $aboutPageId,
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
