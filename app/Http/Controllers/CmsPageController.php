<?php

namespace App\Http\Controllers;

use App\Models\CmsPage;
use App\Services\CmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CmsPageController extends Controller
{
    // ─── Admin CRUD ─────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        try {
            $query = CmsPage::query()->orderBy('sort_order');

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('search')) {
                $query->where(function ($q) use ($request) {
                    $q->where('title_es', 'like', "%{$request->search}%")
                      ->orWhere('title_en', 'like', "%{$request->search}%")
                      ->orWhere('slug', 'like', "%{$request->search}%");
                });
            }

            $perPage = $request->integer('per_page', 20);
            $pages = $query->paginate($perPage);

            return $this->jsonSuccess($pages, 'Páginas obtenidas');
        } catch (\Throwable $e) {
            return $this->apiServerError($e);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'slug' => 'nullable|string|max:100|unique:cms_pages,slug',
                'title_es' => 'required|string|max:255',
                'title_en' => 'nullable|string|max:255',
                'meta_title_es' => 'nullable|string|max:255',
                'meta_title_en' => 'nullable|string|max:255',
                'meta_description_es' => 'nullable|string',
                'meta_description_en' => 'nullable|string',
                'meta_keywords_es' => 'nullable|string|max:500',
                'meta_keywords_en' => 'nullable|string|max:500',
                'template' => 'nullable|string|max:100',
                'status' => 'nullable|in:draft,published,archived',
                'is_active' => 'nullable|boolean',
                'sort_order' => 'nullable|integer',
            ]);

            if (empty($validated['slug'])) {
                $validated['slug'] = Str::slug($validated['title_es']);
            }

            $validated['created_by'] = $request->user()?->id;

            $page = CmsPage::create($validated);

            return $this->apiCreated('Página creada', 'CMS_PAGE_CREATED', $page);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->apiValidationError($e->errors());
        } catch (\Throwable $e) {
            return $this->apiServerError($e);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $page = CmsPage::with('creator')->find($id);
            if (!$page) {
                return $this->apiNotFound('Página no encontrada');
            }

            return $this->jsonSuccess($page, 'Página obtenida');
        } catch (\Throwable $e) {
            return $this->apiServerError($e);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $page = CmsPage::find($id);
            if (!$page) {
                return $this->apiNotFound('Página no encontrada');
            }

            $validated = $request->validate([
                'slug' => ['nullable', 'string', 'max:100', Rule::unique('cms_pages', 'slug')->ignore($page->id)],
                'title_es' => 'nullable|string|max:255',
                'title_en' => 'nullable|string|max:255',
                'meta_title_es' => 'nullable|string|max:255',
                'meta_title_en' => 'nullable|string|max:255',
                'meta_description_es' => 'nullable|string',
                'meta_description_en' => 'nullable|string',
                'meta_keywords_es' => 'nullable|string|max:500',
                'meta_keywords_en' => 'nullable|string|max:500',
                'template' => 'nullable|string|max:100',
                'status' => 'nullable|in:draft,published,archived',
                'is_active' => 'nullable|boolean',
                'sort_order' => 'nullable|integer',
            ]);

            $page->update(array_filter($validated, fn ($v) => $v !== null));

            CmsService::clearPageCache($page->slug);

            return $this->jsonSuccess($page->fresh(), 'Página actualizada');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->apiValidationError($e->errors());
        } catch (\Throwable $e) {
            return $this->apiServerError($e);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $page = CmsPage::find($id);
            if (!$page) {
                return $this->apiNotFound('Página no encontrada');
            }

            $slug = $page->slug;
            $page->delete();

            CmsService::clearPageCache($slug);

            return $this->jsonSuccess(null, 'Página eliminada');
        } catch (\Throwable $e) {
            return $this->apiServerError($e);
        }
    }

    // ─── Público ────────────────────────────────────────

    /**
     * Obtener una página pública con todos sus campos resueltos.
     */
    public function showPublic(string $slug): JsonResponse
    {
        try {
            $page = CmsPage::published()->bySlug($slug)->first();
            if (!$page) {
                return $this->apiNotFound('Página no encontrada');
            }

            $pageData = CmsService::getPageData($slug);
            if (!$pageData) {
                return $this->apiNotFound('Página no encontrada');
            }

            // Construir respuesta con datos resueltos
            $response = [
                'page' => $page,
                'fields' => $this->resolveFieldsForApi($pageData),
            ];

            return $this->jsonSuccess($response, 'Página obtenida');
        } catch (\Throwable $e) {
            return $this->apiServerError($e);
        }
    }

    /**
     * Resolver todos los campos de una página para la respuesta API.
     */
    protected function resolveFieldsForApi($pageData): array
    {
        $fields = [];

        foreach ($pageData->fieldGroups as $group) {
            $groupFields = [];

            foreach ($group->fieldDefinitions as $fieldDef) {
                if ($fieldDef->isRepeater()) {
                    $rows = $pageData->repeater($fieldDef->field_key);
                    $repeaterData = [];
                    foreach ($rows as $row) {
                        $rowData = [];
                        if ($fieldDef->children) {
                            foreach ($fieldDef->children as $subField) {
                                $rowData[$subField->field_key] = [
                                    'value_es' => $row->field($subField->field_key, 'es'),
                                    'value_en' => $row->field($subField->field_key, 'en'),
                                    'image' => $subField->isMediaField() ? $row->image($subField->field_key) : null,
                                ];
                            }
                        }
                        $repeaterData[] = $rowData;
                    }
                    $groupFields[$fieldDef->field_key] = [
                        'type' => 'repeater',
                        'rows' => $repeaterData,
                    ];
                } else {
                    $groupFields[$fieldDef->field_key] = [
                        'type' => $fieldDef->type,
                        'value_es' => $pageData->field($fieldDef->field_key, 'es'),
                        'value_en' => $pageData->field($fieldDef->field_key, 'en'),
                        'image' => $fieldDef->isMediaField() ? $pageData->image($fieldDef->field_key) : null,
                    ];
                }
            }

            $fields[$group->slug] = [
                'name' => $group->name,
                'fields' => $groupFields,
            ];
        }

        return $fields;
    }
}
