<?php

namespace App\Http\Controllers;

use App\Models\CmsFieldDefinition;
use App\Models\CmsFieldGroup;
use App\Models\CmsFieldValue;
use App\Services\CmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CmsFieldValueController extends Controller
{
    /**
     * Obtener todos los valores de una entidad (página o post).
     * GET /api/cms/field-values/{entityType}/{entityId}
     */
    public function index(string $entityType, int $entityId): JsonResponse
    {
        try {
            if (!in_array($entityType, ['page', 'post', 'global'])) {
                return $this->jsonError('Tipo de entidad inválido', 400);
            }

            $values = CmsFieldValue::where('entity_type', $entityType)
                ->where('entity_id', $entityType === 'global' ? null : $entityId)
                ->with(['fieldDefinition.fieldGroup', 'mediaAsset', 'children.fieldDefinition', 'children.mediaAsset'])
                ->whereNull('parent_value_id') // solo raíz
                ->get();

            // Organizar por grupo
            $organized = [];
            foreach ($values as $value) {
                $groupSlug = $value->fieldDefinition?->fieldGroup?->slug ?? 'ungrouped';
                if (!isset($organized[$groupSlug])) {
                    $organized[$groupSlug] = [];
                }
                $organized[$groupSlug][$value->fieldDefinition->field_key] = $this->formatValueForApi($value);
            }

            return $this->jsonSuccess($organized, 'Valores obtenidos');
        } catch (\Throwable $e) {
            return $this->apiServerError($e);
        }
    }

    /**
     * Guardar/actualizar valores de una entidad (bulk).
     * PUT /api/cms/field-values/{entityType}/{entityId}
     *
     * Body esperado:
     * {
     *   "fields": {
     *     "hero_title": { "value_es": "Texto ES", "value_en": "Text EN" },
     *     "hero_image": { "media_asset_id": 5 },
     *     "stats_items": {
     *       "rows": [
     *         { "stat_number": { "value_es": "500+" }, "stat_label": { "value_es": "Props", "value_en": "Props" } },
     *         { "stat_number": { "value_es": "15+" }, "stat_label": { "value_es": "Años", "value_en": "Years" } }
     *       ]
     *     }
     *   }
     * }
     */
    public function update(Request $request, string $entityType, int $entityId): JsonResponse
    {
        try {
            if (!in_array($entityType, ['page', 'post', 'global'])) {
                return $this->jsonError('Tipo de entidad inválido', 400);
            }

            $validated = $request->validate([
                'fields' => 'required|array',
            ]);

            $actualEntityId = $entityType === 'global' ? null : $entityId;

            foreach ($validated['fields'] as $fieldKey => $fieldData) {
                $fieldDef = CmsFieldDefinition::where('field_key', $fieldKey)->first();
                if (!$fieldDef) {
                    continue; // Skip unknown fields
                }

                if ($fieldDef->isRepeater() && isset($fieldData['rows'])) {
                    $this->saveRepeaterValues($fieldDef, $entityType, $actualEntityId, $fieldData['rows']);
                } else {
                    $this->saveFieldValue($fieldDef, $entityType, $actualEntityId, $fieldData);
                }
            }

            // Limpiar cache según tipo
            if ($entityType === 'page') {
                $page = \App\Models\CmsPage::find($entityId);
                if ($page) {
                    CmsService::clearPageCache($page->slug);
                }
            } elseif ($entityType === 'post') {
                $post = \App\Models\CmsPost::find($entityId);
                if ($post) {
                    CmsService::clearPostCache($post->slug);
                }
            }

            return $this->jsonSuccess(null, 'Valores guardados');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->apiValidationError($e->errors());
        } catch (\Throwable $e) {
            return $this->apiServerError($e);
        }
    }

    /**
     * Agregar una fila a un campo repeater.
     */
    public function addRepeaterRow(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'field_definition_id' => 'required|integer|exists:cms_field_definitions,id',
                'entity_type' => 'required|in:page,post,global',
                'entity_id' => 'nullable|integer',
                'row_data' => 'required|array',
            ]);

            $fieldDef = CmsFieldDefinition::find($validated['field_definition_id']);
            if (!$fieldDef || !$fieldDef->isRepeater()) {
                return $this->jsonError('El campo no es un repeater', 400);
            }

            // Obtener el próximo row_index
            $maxIndex = CmsFieldValue::where('field_definition_id', $fieldDef->id)
                ->where('entity_type', $validated['entity_type'])
                ->where('entity_id', $validated['entity_id'])
                ->whereNull('parent_value_id')
                ->max('row_index') ?? -1;

            $newRowIndex = $maxIndex + 1;

            // Crear el valor parent del repeater row
            $parentValue = CmsFieldValue::create([
                'field_definition_id' => $fieldDef->id,
                'entity_type' => $validated['entity_type'],
                'entity_id' => $validated['entity_id'],
                'row_index' => $newRowIndex,
            ]);

            // Crear sub-valores
            foreach ($validated['row_data'] as $subKey => $subData) {
                $subFieldDef = CmsFieldDefinition::where('parent_id', $fieldDef->id)
                    ->where('field_key', $subKey)
                    ->first();

                if ($subFieldDef) {
                    CmsFieldValue::create([
                        'field_definition_id' => $subFieldDef->id,
                        'entity_type' => $validated['entity_type'],
                        'entity_id' => $validated['entity_id'],
                        'value_es' => $subData['value_es'] ?? null,
                        'value_en' => $subData['value_en'] ?? null,
                        'media_asset_id' => $subData['media_asset_id'] ?? null,
                        'parent_value_id' => $parentValue->id,
                        'row_index' => 0,
                    ]);
                }
            }

            return $this->apiCreated('Fila agregada', 'REPEATER_ROW_ADDED', $parentValue->load('children.fieldDefinition'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->apiValidationError($e->errors());
        } catch (\Throwable $e) {
            return $this->apiServerError($e);
        }
    }

    /**
     * Eliminar una fila de repeater.
     */
    public function deleteRepeaterRow(int $id): JsonResponse
    {
        try {
            $value = CmsFieldValue::find($id);
            if (!$value) {
                return $this->apiNotFound('Fila no encontrada');
            }

            // Eliminar hijos y la fila padre
            $value->children()->delete();
            $value->delete();

            return $this->jsonSuccess(null, 'Fila eliminada');
        } catch (\Throwable $e) {
            return $this->apiServerError($e);
        }
    }

    // ─── Helpers privados ───────────────────────────────

    protected function saveFieldValue(CmsFieldDefinition $fieldDef, string $entityType, ?int $entityId, array $data): void
    {
        $attributes = [
            'field_definition_id' => $fieldDef->id,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'parent_value_id' => null,
        ];

        $values = [
            'value_es' => $data['value_es'] ?? null,
            'value_en' => $data['value_en'] ?? null,
            'media_asset_id' => $data['media_asset_id'] ?? null,
        ];

        CmsFieldValue::updateOrCreate($attributes, $values);
    }

    protected function saveRepeaterValues(CmsFieldDefinition $fieldDef, string $entityType, ?int $entityId, array $rows): void
    {
        // Eliminar filas existentes del repeater
        $existingParents = CmsFieldValue::where('field_definition_id', $fieldDef->id)
            ->where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->whereNull('parent_value_id')
            ->get();

        foreach ($existingParents as $parent) {
            $parent->children()->delete();
            $parent->delete();
        }

        // Crear nuevas filas
        foreach ($rows as $rowIndex => $rowData) {
            $parentValue = CmsFieldValue::create([
                'field_definition_id' => $fieldDef->id,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'row_index' => $rowIndex,
            ]);

            foreach ($rowData as $subKey => $subData) {
                $subFieldDef = CmsFieldDefinition::where('parent_id', $fieldDef->id)
                    ->where('field_key', $subKey)
                    ->first();

                if ($subFieldDef) {
                    CmsFieldValue::create([
                        'field_definition_id' => $subFieldDef->id,
                        'entity_type' => $entityType,
                        'entity_id' => $entityId,
                        'value_es' => $subData['value_es'] ?? null,
                        'value_en' => $subData['value_en'] ?? null,
                        'media_asset_id' => $subData['media_asset_id'] ?? null,
                        'parent_value_id' => $parentValue->id,
                        'row_index' => 0,
                    ]);
                }
            }
        }
    }

    protected function formatValueForApi(CmsFieldValue $value): array
    {
        $result = [
            'id' => $value->id,
            'field_key' => $value->fieldDefinition->field_key,
            'type' => $value->fieldDefinition->type,
            'value_es' => $value->value_es,
            'value_en' => $value->value_en,
            'media_asset_id' => $value->media_asset_id,
            'media_asset' => $value->mediaAsset,
        ];

        // Si es repeater, incluir filas
        if ($value->fieldDefinition->isRepeater() && $value->children->isNotEmpty()) {
            $rows = [];
            $grouped = $value->children->groupBy('row_index');
            foreach ($grouped as $rowIndex => $rowValues) {
                $row = [];
                foreach ($rowValues as $childValue) {
                    $row[$childValue->fieldDefinition->field_key] = [
                        'id' => $childValue->id,
                        'value_es' => $childValue->value_es,
                        'value_en' => $childValue->value_en,
                        'media_asset_id' => $childValue->media_asset_id,
                        'media_asset' => $childValue->mediaAsset,
                    ];
                }
                $rows[] = $row;
            }
            $result['rows'] = $rows;
        }

        return $result;
    }
}
