<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const PAGE_SLUG = 'properties';
    private const CANONICAL_GROUP_SLUG = 'properties-texts-auto';
    private const LEGACY_GROUP_SLUG = 'properties-texts-legacy';
    private const LEGACY_LOCATION = 'properties-legacy';

    public function up(): void
    {
        if (
            !Schema::hasTable('cms_pages')
            || !Schema::hasTable('cms_field_groups')
            || !Schema::hasTable('cms_field_definitions')
            || !Schema::hasTable('cms_field_values')
        ) {
            return;
        }

        $pageId = $this->ensurePage();
        $groupId = $this->ensureCanonicalGroup();
        $legacyGroupId = $this->ensureLegacyGroup();

        $legacyTitle = $this->firstValueForKey($pageId, 'page_title');
        $existingPrefix = $this->firstValueForKey($pageId, 'page_title_prefix');
        $useLegacyTitleAsPrefix = $this->isBlank($existingPrefix['value_es'] ?? null)
            && $this->isBlank($existingPrefix['value_en'] ?? null)
            && (!$this->isBlank($legacyTitle['value_es'] ?? null) || !$this->isBlank($legacyTitle['value_en'] ?? null));

        $fields = $this->fieldsForCurrentPropertiesView();
        $validKeys = [];

        foreach ($fields as $index => $field) {
            $field['sort_order'] = $index + 1;
            $validKeys[$field['field_key']] = true;

            if ($field['field_key'] === 'page_title_prefix' && $useLegacyTitleAsPrefix) {
                $field['value_es'] = $legacyTitle['value_es'] ?: $field['value_es'];
                $field['value_en'] = $legacyTitle['value_en'] ?: $field['value_en'];
                $field['source_key'] = 'page_title';
            }

            if ($field['field_key'] === 'page_title_highlight' && $useLegacyTitleAsPrefix) {
                $field['value_es'] = '';
                $field['value_en'] = '';
                $field['source_key'] = null;
            }

            $fieldId = $this->ensureField($groupId, $field);
            $this->ensureValue(
                $fieldId,
                $pageId,
                $field['value_es'] ?? null,
                $field['value_en'] ?? null,
                $field['source_key'] ?? $field['field_key']
            );
        }

        $this->moveUnusedCanonicalFieldsToLegacy($groupId, $legacyGroupId, array_keys($validKeys));
        $this->moveLegacyPropertyGroups();

        Cache::forget('cms_page_properties');
    }

    public function down(): void
    {
        Cache::forget('cms_page_properties');
    }

    private function ensurePage(): int
    {
        $now = now();
        $pageId = DB::table('cms_pages')->where('slug', self::PAGE_SLUG)->value('id');

        $data = [
            'title_es' => 'Propiedades',
            'title_en' => 'Properties',
            'template' => 'public.properties-index',
            'status' => 'published',
            'is_active' => true,
            'sort_order' => 4,
            'updated_at' => $now,
        ];

        if ($pageId) {
            DB::table('cms_pages')->where('id', $pageId)->update($data);
            return (int) $pageId;
        }

        $data += [
            'slug' => self::PAGE_SLUG,
            'meta_title_es' => 'Propiedades - San Miguel Properties',
            'meta_title_en' => 'Properties - San Miguel Properties',
            'meta_description_es' => 'Explora propiedades en venta y renta en San Miguel de Allende.',
            'meta_description_en' => 'Browse properties for sale and rent in San Miguel de Allende.',
            'created_at' => $now,
        ];

        return (int) DB::table('cms_pages')->insertGetId($data);
    }

    private function ensureCanonicalGroup(): int
    {
        $now = now();
        $groupId = DB::table('cms_field_groups')->where('slug', self::CANONICAL_GROUP_SLUG)->value('id');
        $data = [
            'name' => 'Textos de propiedades',
            'description' => 'Campos usados por la vista publica actual de propiedades.',
            'location_type' => 'page',
            'location_identifier' => self::PAGE_SLUG,
            'sort_order' => 1,
            'is_active' => true,
            'updated_at' => $now,
        ];

        if ($groupId) {
            DB::table('cms_field_groups')->where('id', $groupId)->update($data);
            return (int) $groupId;
        }

        $data['slug'] = self::CANONICAL_GROUP_SLUG;
        $data['created_at'] = $now;

        return (int) DB::table('cms_field_groups')->insertGetId($data);
    }

    private function ensureLegacyGroup(): int
    {
        $now = now();
        $groupId = DB::table('cms_field_groups')->where('slug', self::LEGACY_GROUP_SLUG)->value('id');
        $data = [
            'name' => 'Textos legacy propiedades',
            'description' => 'Campos antiguos conservados para respaldo; no los usa la vista publica actual.',
            'location_type' => 'page',
            'location_identifier' => self::LEGACY_LOCATION,
            'sort_order' => 999,
            'is_active' => false,
            'updated_at' => $now,
        ];

        if ($groupId) {
            DB::table('cms_field_groups')->where('id', $groupId)->update($data);
            return (int) $groupId;
        }

        $data['slug'] = self::LEGACY_GROUP_SLUG;
        $data['created_at'] = $now;

        return (int) DB::table('cms_field_groups')->insertGetId($data);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function fieldsForCurrentPropertiesView(): array
    {
        $fieldsByKey = [];

        foreach ($this->forcedFields() as $field) {
            $fieldsByKey[$field['field_key']] = $field;
        }

        foreach ($this->extractFieldsFromViews() as $field) {
            if (!isset($fieldsByKey[$field['field_key']])) {
                $fieldsByKey[$field['field_key']] = $field;
            }
        }

        return array_values($fieldsByKey);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function forcedFields(): array
    {
        return [
            $this->field('page_title_prefix', 'text', 'Titulo: prefijo', 'Title prefix', 'Explora nuestras', 'Explore our'),
            $this->field('page_title_highlight', 'text', 'Titulo: destacado', 'Title highlight', 'propiedades', 'properties'),
            $this->field('page_subtitle', 'textarea', 'Subtitulo', 'Subtitle', 'Filtra por tipo y encuentra la propiedad ideal.', 'Filter by type and find the right property for you.'),
            $this->field('search_label', 'text', 'Etiqueta de busqueda', 'Search label', 'Buscar', 'Search'),
            $this->field('search_placeholder', 'text', 'Placeholder de busqueda', 'Search placeholder', 'Buscar por ciudad, zona, tipo...', 'Search by city, area, type...'),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function extractFieldsFromViews(): array
    {
        $paths = [
            resource_path('views/public/properties-index.blade.php'),
            resource_path('views/layouts/public.blade.php'),
        ];

        $fields = [];

        foreach ($paths as $path) {
            if (!is_file($path)) {
                continue;
            }

            $content = file_get_contents($path);
            if (!is_string($content) || $content === '') {
                continue;
            }

            foreach ($this->extractBladeTextFields($content) as $field) {
                $fields[$field['field_key']] = $field;
            }

            foreach ($this->extractPublicTranslationFields($content) as $field) {
                if (!isset($fields[$field['field_key']])) {
                    $fields[$field['field_key']] = $field;
                }
            }
        }

        return array_values($fields);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function extractBladeTextFields(string $content): array
    {
        preg_match_all("/\\\$txt\\('([^']+)'\\s*,\\s*'((?:\\\\'|[^'])*)'\\s*,\\s*'((?:\\\\'|[^'])*)'\\)/", $content, $matches, PREG_SET_ORDER);

        return array_map(function (array $match): array {
            $key = trim((string) ($match[1] ?? ''));
            $valueEs = stripcslashes((string) ($match[2] ?? ''));
            $valueEn = stripcslashes((string) ($match[3] ?? ''));

            return $this->field($key, $this->typeForValues($valueEs, $valueEn), $this->labelForKey($key), $this->englishLabelForKey($key), $valueEs, $valueEn);
        }, $matches);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function extractPublicTranslationFields(string $content): array
    {
        $fields = [];

        preg_match_all("/tPublic\\('([a-zA-Z0-9._-]+)'\\s*,\\s*isEnLocale\\s*\\?\\s*'((?:\\\\'|[^'])*)'\\s*:\\s*'((?:\\\\'|[^'])*)'\\s*\\)/", $content, $ternaryMatches, PREG_SET_ORDER);
        foreach ($ternaryMatches as $match) {
            $dotKey = trim((string) ($match[1] ?? ''));
            $valueEn = stripcslashes((string) ($match[2] ?? ''));
            $valueEs = stripcslashes((string) ($match[3] ?? ''));
            $fieldKey = 'i18n_' . str_replace('.', '_', $dotKey);

            $fields[$fieldKey] = $this->field($fieldKey, $this->typeForValues($valueEs, $valueEn), $this->labelForKey($fieldKey), $this->englishLabelForKey($fieldKey), $valueEs, $valueEn);
        }

        preg_match_all("/tPublic\\('([a-zA-Z0-9._-]+)'\\s*,\\s*'((?:\\\\'|[^'])*)'\\s*\\)/", $content, $simpleMatches, PREG_SET_ORDER);
        foreach ($simpleMatches as $match) {
            $dotKey = trim((string) ($match[1] ?? ''));
            $fallback = stripcslashes((string) ($match[2] ?? ''));
            $fieldKey = 'i18n_' . str_replace('.', '_', $dotKey);

            if (!isset($fields[$fieldKey])) {
                $fields[$fieldKey] = $this->field($fieldKey, $this->typeForValues($fallback, $fallback), $this->labelForKey($fieldKey), $this->englishLabelForKey($fieldKey), $fallback, $fallback);
            }
        }

        return array_values($fields);
    }

    private function field(string $key, string $type, string $labelEs, string $labelEn, string $valueEs, string $valueEn): array
    {
        return [
            'field_key' => $key,
            'type' => $type,
            'label_es' => $labelEs,
            'label_en' => $labelEn,
            'value_es' => $valueEs,
            'value_en' => $valueEn,
            'is_translatable' => true,
        ];
    }

    private function typeForValues(string $valueEs, string $valueEn): string
    {
        return mb_strlen($valueEs) > 120 || mb_strlen($valueEn) > 120 ? 'textarea' : 'text';
    }

    private function labelForKey(string $key): string
    {
        $labels = [
            'page_title_prefix' => 'Titulo: prefijo',
            'page_title_highlight' => 'Titulo: destacado',
            'page_subtitle' => 'Subtitulo',
            'search_label' => 'Etiqueta de busqueda',
            'search_placeholder' => 'Placeholder de busqueda',
            'advanced_filters' => 'Filtros avanzados',
            'modal_subtitle' => 'Subtitulo del modal',
            'property_type_label' => 'Tipo de propiedad',
            'operation_type_label' => 'Tipo de operacion',
            'price_range_label' => 'Rango de precio',
            'features_label' => 'Caracteristicas',
            'size_label' => 'Tamano',
            'location_label' => 'Ubicacion',
            'sort_label' => 'Orden',
            'empty_title' => 'Titulo sin resultados',
            'empty_subtitle' => 'Texto sin resultados',
        ];

        return $labels[$key] ?? 'Texto: ' . str_replace('_', ' ', $key);
    }

    private function englishLabelForKey(string $key): string
    {
        return 'Text: ' . str_replace('_', ' ', $key);
    }

    private function ensureField(int $groupId, array $field): int
    {
        $now = now();
        $fieldKey = (string) $field['field_key'];
        $fieldId = DB::table('cms_field_definitions')
            ->where('field_group_id', $groupId)
            ->where('field_key', $fieldKey)
            ->whereNull('parent_id')
            ->value('id');

        if (!$fieldId) {
            $fieldId = DB::table('cms_field_definitions as fields')
                ->join('cms_field_groups as groups', 'groups.id', '=', 'fields.field_group_id')
                ->where('fields.field_key', $fieldKey)
                ->whereNull('fields.parent_id')
                ->where('groups.location_type', 'page')
                ->where('groups.location_identifier', self::PAGE_SLUG)
                ->orderBy('fields.id')
                ->value('fields.id');
        }

        $data = [
            'field_group_id' => $groupId,
            'parent_id' => null,
            'type' => $field['type'],
            'label_es' => $field['label_es'],
            'label_en' => $field['label_en'],
            'is_required' => false,
            'is_translatable' => $field['is_translatable'] ?? true,
            'sort_order' => $field['sort_order'] ?? 0,
            'updated_at' => $now,
        ];

        if ($fieldId) {
            DB::table('cms_field_definitions')->where('id', $fieldId)->update($data);
            return (int) $fieldId;
        }

        $data['field_key'] = $fieldKey;
        $data['created_at'] = $now;

        return (int) DB::table('cms_field_definitions')->insertGetId($data);
    }

    private function ensureValue(int $fieldId, int $pageId, ?string $defaultEs, ?string $defaultEn, ?string $sourceKey): void
    {
        $now = now();
        $source = $sourceKey ? $this->firstValueForKey($pageId, $sourceKey, $fieldId) : null;
        $value = DB::table('cms_field_values')
            ->where('field_definition_id', $fieldId)
            ->where('entity_type', 'page')
            ->where('entity_id', $pageId)
            ->whereNull('parent_value_id')
            ->first();

        $valueEs = $this->pick($source['value_es'] ?? null, $defaultEs);
        $valueEn = $this->pick($source['value_en'] ?? null, $defaultEn);

        if ($value) {
            $updates = ['updated_at' => $now];

            if ($this->isBlank($value->value_es)) {
                $updates['value_es'] = $valueEs;
            }

            if ($this->isBlank($value->value_en)) {
                $updates['value_en'] = $valueEn;
            }

            if (count($updates) > 1) {
                DB::table('cms_field_values')->where('id', $value->id)->update($updates);
            }

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

    private function firstValueForKey(int $pageId, string $fieldKey, ?int $exceptFieldId = null): ?array
    {
        $query = DB::table('cms_field_values as values')
            ->join('cms_field_definitions as fields', 'fields.id', '=', 'values.field_definition_id')
            ->join('cms_field_groups as groups', 'groups.id', '=', 'fields.field_group_id')
            ->where('values.entity_type', 'page')
            ->where('values.entity_id', $pageId)
            ->whereNull('values.parent_value_id')
            ->whereNull('fields.parent_id')
            ->where('fields.field_key', $fieldKey)
            ->where('groups.location_type', 'page')
            ->whereIn('groups.location_identifier', [self::PAGE_SLUG, self::LEGACY_LOCATION])
            ->select('values.value_es', 'values.value_en', 'fields.id as field_id')
            ->orderByRaw('CASE WHEN groups.slug = ? THEN 0 ELSE 1 END', [self::CANONICAL_GROUP_SLUG])
            ->orderBy('fields.id');

        if ($exceptFieldId) {
            $query->where('fields.id', '!=', $exceptFieldId);
        }

        $row = $query->first();

        return $row ? [
            'field_id' => (int) $row->field_id,
            'value_es' => $row->value_es,
            'value_en' => $row->value_en,
        ] : null;
    }

    /**
     * @param array<int, string> $validKeys
     */
    private function moveUnusedCanonicalFieldsToLegacy(int $groupId, int $legacyGroupId, array $validKeys): void
    {
        DB::table('cms_field_definitions')
            ->where('field_group_id', $groupId)
            ->whereNull('parent_id')
            ->whereNotIn('field_key', $validKeys)
            ->update([
                'field_group_id' => $legacyGroupId,
                'sort_order' => 999,
                'updated_at' => now(),
            ]);
    }

    private function moveLegacyPropertyGroups(): void
    {
        DB::table('cms_field_groups')
            ->where('location_type', 'page')
            ->where('location_identifier', self::PAGE_SLUG)
            ->where('slug', '!=', self::CANONICAL_GROUP_SLUG)
            ->update([
                'location_identifier' => self::LEGACY_LOCATION,
                'is_active' => false,
                'sort_order' => 999,
                'updated_at' => now(),
            ]);
    }

    private function isBlank(mixed $value): bool
    {
        return $value === null || trim((string) $value) === '';
    }

    private function pick(mixed $preferred, mixed $fallback): ?string
    {
        if (!$this->isBlank($preferred)) {
            return (string) $preferred;
        }

        return $fallback === null ? null : (string) $fallback;
    }
};
