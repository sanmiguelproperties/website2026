<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private string $fieldKey = 'process_background_image';

    public function up(): void
    {
        $groupId = DB::table('cms_field_groups')
            ->where('slug', 'home-process')
            ->value('id');

        if (!$groupId) {
            return;
        }

        $now = now();

        DB::table('cms_field_definitions')->updateOrInsert(
            [
                'field_group_id' => $groupId,
                'field_key' => $this->fieldKey,
            ],
            [
                'parent_id' => null,
                'type' => 'image',
                'label_es' => 'Imagen de fondo',
                'label_en' => 'Background image',
                'instructions_es' => 'Imagen opcional para el fondo de la seccion Proceso de Compra en el inicio.',
                'instructions_en' => 'Optional background image for the Buying Process section on the home page.',
                'default_value_es' => null,
                'default_value_en' => null,
                'is_required' => false,
                'is_translatable' => false,
                'sort_order' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        Cache::forget('cms_page_home');
    }

    public function down(): void
    {
        $query = DB::table('cms_field_definitions')
            ->where('field_key', $this->fieldKey);

        $groupId = DB::table('cms_field_groups')
            ->where('slug', 'home-process')
            ->value('id');

        if ($groupId) {
            $query->where('field_group_id', $groupId);
        }

        $fieldIds = $query->pluck('id');

        if ($fieldIds->isNotEmpty()) {
            DB::table('cms_field_values')->whereIn('field_definition_id', $fieldIds)->delete();
            DB::table('cms_field_definitions')->whereIn('id', $fieldIds)->delete();
        }

        Cache::forget('cms_page_home');
    }
};
