<?php

namespace App\Http\Controllers;

use App\Models\CmsFieldDefinition;
use App\Models\CmsFieldGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CmsFieldGroupController extends Controller
{
    // ─── Field Groups CRUD ──────────────────────────────

    public function index(Request $request): JsonResponse
    {
        try {
            $query = CmsFieldGroup::with(['fieldDefinitions.children'])
                ->orderBy('sort_order');

            if ($request->filled('location_type')) {
                $query->where('location_type', $request->location_type);
            }

            if ($request->filled('location_identifier')) {
                $query->where('location_identifier', $request->location_identifier);
            }

            $groups = $query->get();

            return $this->jsonSuccess($groups, 'Grupos de campos obtenidos');
        } catch (\Throwable $e) {
            return $this->apiServerError($e);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'slug' => 'nullable|string|max:100|unique:cms_field_groups,slug',
                'description' => 'nullable|string',
                'location_type' => 'required|in:page,post,post_category,global',
                'location_identifier' => 'nullable|string|max:100',
                'sort_order' => 'nullable|integer',
                'is_active' => 'nullable|boolean',
            ]);

            if (empty($validated['slug'])) {
                $validated['slug'] = Str::slug($validated['name']);
            }

            $group = CmsFieldGroup::create($validated);

            return $this->apiCreated('Grupo creado', 'CMS_FIELD_GROUP_CREATED', $group);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->apiValidationError($e->errors());
        } catch (\Throwable $e) {
            return $this->apiServerError($e);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $group = CmsFieldGroup::with(['fieldDefinitions.children'])->find($id);
            if (!$group) {
                return $this->apiNotFound('Grupo no encontrado');
            }

            return $this->jsonSuccess($group, 'Grupo obtenido');
        } catch (\Throwable $e) {
            return $this->apiServerError($e);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $group = CmsFieldGroup::find($id);
            if (!$group) {
                return $this->apiNotFound('Grupo no encontrado');
            }

            $validated = $request->validate([
                'name' => 'nullable|string|max:255',
                'slug' => ['nullable', 'string', 'max:100', Rule::unique('cms_field_groups', 'slug')->ignore($group->id)],
                'description' => 'nullable|string',
                'location_type' => 'nullable|in:page,post,post_category,global',
                'location_identifier' => 'nullable|string|max:100',
                'sort_order' => 'nullable|integer',
                'is_active' => 'nullable|boolean',
            ]);

            $group->update(array_filter($validated, fn ($v) => $v !== null));

            return $this->jsonSuccess($group->fresh(['fieldDefinitions.children']), 'Grupo actualizado');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->apiValidationError($e->errors());
        } catch (\Throwable $e) {
            return $this->apiServerError($e);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $group = CmsFieldGroup::find($id);
            if (!$group) {
                return $this->apiNotFound('Grupo no encontrado');
            }

            $group->delete();

            return $this->jsonSuccess(null, 'Grupo eliminado');
        } catch (\Throwable $e) {
            return $this->apiServerError($e);
        }
    }

    // ─── Field Definitions CRUD ─────────────────────────

    public function storeField(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'field_group_id' => 'required|integer|exists:cms_field_groups,id',
                'parent_id' => 'nullable|integer|exists:cms_field_definitions,id',
                'field_key' => 'required|string|max:100',
                'type' => ['required', 'string', Rule::in(CmsFieldDefinition::TYPES)],
                'label_es' => 'required|string|max:255',
                'label_en' => 'nullable|string|max:255',
                'instructions_es' => 'nullable|string',
                'instructions_en' => 'nullable|string',
                'placeholder_es' => 'nullable|string|max:255',
                'placeholder_en' => 'nullable|string|max:255',
                'default_value_es' => 'nullable|string',
                'default_value_en' => 'nullable|string',
                'validation_rules' => 'nullable|array',
                'options' => 'nullable|array',
                'is_required' => 'nullable|boolean',
                'is_translatable' => 'nullable|boolean',
                'char_limit' => 'nullable|integer|min:1',
                'sort_order' => 'nullable|integer',
            ]);

            // Auto-set is_translatable based on type
            if (!isset($validated['is_translatable'])) {
                $validated['is_translatable'] = !in_array(
                    $validated['type'],
                    CmsFieldDefinition::NON_TRANSLATABLE_TYPES
                );
            }

            $field = CmsFieldDefinition::create($validated);

            return $this->apiCreated('Campo creado', 'CMS_FIELD_CREATED', $field->load('children'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->apiValidationError($e->errors());
        } catch (\Throwable $e) {
            return $this->apiServerError($e);
        }
    }

    public function updateField(Request $request, int $id): JsonResponse
    {
        try {
            $field = CmsFieldDefinition::find($id);
            if (!$field) {
                return $this->apiNotFound('Campo no encontrado');
            }

            $validated = $request->validate([
                'field_key' => 'nullable|string|max:100',
                'type' => ['nullable', 'string', Rule::in(CmsFieldDefinition::TYPES)],
                'label_es' => 'nullable|string|max:255',
                'label_en' => 'nullable|string|max:255',
                'instructions_es' => 'nullable|string',
                'instructions_en' => 'nullable|string',
                'placeholder_es' => 'nullable|string|max:255',
                'placeholder_en' => 'nullable|string|max:255',
                'default_value_es' => 'nullable|string',
                'default_value_en' => 'nullable|string',
                'validation_rules' => 'nullable|array',
                'options' => 'nullable|array',
                'is_required' => 'nullable|boolean',
                'is_translatable' => 'nullable|boolean',
                'char_limit' => 'nullable|integer|min:1',
                'sort_order' => 'nullable|integer',
            ]);

            $field->update(array_filter($validated, fn ($v) => $v !== null));

            return $this->jsonSuccess($field->fresh('children'), 'Campo actualizado');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->apiValidationError($e->errors());
        } catch (\Throwable $e) {
            return $this->apiServerError($e);
        }
    }

    public function destroyField(int $id): JsonResponse
    {
        try {
            $field = CmsFieldDefinition::find($id);
            if (!$field) {
                return $this->apiNotFound('Campo no encontrado');
            }

            $field->delete();

            return $this->jsonSuccess(null, 'Campo eliminado');
        } catch (\Throwable $e) {
            return $this->apiServerError($e);
        }
    }

    public function reorderFields(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'items' => 'required|array',
                'items.*.id' => 'required|integer|exists:cms_field_definitions,id',
                'items.*.sort_order' => 'required|integer',
            ]);

            foreach ($validated['items'] as $item) {
                CmsFieldDefinition::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
            }

            return $this->jsonSuccess(null, 'Campos reordenados');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->apiValidationError($e->errors());
        } catch (\Throwable $e) {
            return $this->apiServerError($e);
        }
    }
}
