<?php

namespace App\Http\Controllers;

use App\Models\CmsMenu;
use App\Models\CmsMenuItem;
use App\Services\CmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CmsMenuController extends Controller
{
    // ─── Admin: Menús CRUD ──────────────────────────────

    public function index(): JsonResponse
    {
        try {
            $menus = CmsMenu::with(['rootItems.children'])
                ->orderBy('location')
                ->get();

            return $this->jsonSuccess($menus, 'Menús obtenidos');
        } catch (\Throwable $e) {
            return $this->apiServerError($e);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'slug' => 'nullable|string|max:100|unique:cms_menus,slug',
                'location' => 'required|string|max:100',
                'description' => 'nullable|string',
                'is_active' => 'nullable|boolean',
            ]);

            if (empty($validated['slug'])) {
                $validated['slug'] = Str::slug($validated['name']);
            }

            $menu = CmsMenu::create($validated);

            return $this->apiCreated('Menú creado', 'CMS_MENU_CREATED', $menu);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->apiValidationError($e->errors());
        } catch (\Throwable $e) {
            return $this->apiServerError($e);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $menu = CmsMenu::with(['rootItems.children.children'])->find($id);
            if (!$menu) {
                return $this->apiNotFound('Menú no encontrado');
            }

            return $this->jsonSuccess($menu, 'Menú obtenido');
        } catch (\Throwable $e) {
            return $this->apiServerError($e);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $menu = CmsMenu::find($id);
            if (!$menu) {
                return $this->apiNotFound('Menú no encontrado');
            }

            $validated = $request->validate([
                'name' => 'nullable|string|max:255',
                'slug' => ['nullable', 'string', 'max:100', Rule::unique('cms_menus', 'slug')->ignore($menu->id)],
                'location' => 'nullable|string|max:100',
                'description' => 'nullable|string',
                'is_active' => 'nullable|boolean',
            ]);

            $oldSlug = $menu->slug;
            $menu->update(array_filter($validated, fn ($v) => $v !== null));

            CmsService::clearMenuCache($oldSlug);
            if ($menu->slug !== $oldSlug) {
                CmsService::clearMenuCache($menu->slug);
            }

            return $this->jsonSuccess($menu->fresh(['rootItems.children']), 'Menú actualizado');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->apiValidationError($e->errors());
        } catch (\Throwable $e) {
            return $this->apiServerError($e);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $menu = CmsMenu::find($id);
            if (!$menu) {
                return $this->apiNotFound('Menú no encontrado');
            }

            $slug = $menu->slug;
            $menu->delete();
            CmsService::clearMenuCache($slug);

            return $this->jsonSuccess(null, 'Menú eliminado');
        } catch (\Throwable $e) {
            return $this->apiServerError($e);
        }
    }

    // ─── Admin: Menu Items CRUD ─────────────────────────

    public function storeItem(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'menu_id' => 'required|integer|exists:cms_menus,id',
                'parent_id' => 'nullable|integer|exists:cms_menu_items,id',
                'label_es' => 'required|string|max:255',
                'label_en' => 'nullable|string|max:255',
                'url' => 'nullable|string|max:500',
                'route_name' => 'nullable|string|max:255',
                'page_id' => 'nullable|integer|exists:cms_pages,id',
                'target' => 'nullable|in:_self,_blank',
                'icon' => 'nullable|string',
                'css_class' => 'nullable|string|max:255',
                'sort_order' => 'nullable|integer',
                'is_active' => 'nullable|boolean',
            ]);

            $item = CmsMenuItem::create($validated);

            // Clear menu cache
            $menu = CmsMenu::find($validated['menu_id']);
            if ($menu) {
                CmsService::clearMenuCache($menu->slug);
            }

            return $this->apiCreated('Item creado', 'CMS_MENU_ITEM_CREATED', $item->load('children'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->apiValidationError($e->errors());
        } catch (\Throwable $e) {
            return $this->apiServerError($e);
        }
    }

    public function updateItem(Request $request, int $id): JsonResponse
    {
        try {
            $item = CmsMenuItem::find($id);
            if (!$item) {
                return $this->apiNotFound('Item no encontrado');
            }

            $validated = $request->validate([
                'parent_id' => 'nullable|integer|exists:cms_menu_items,id',
                'label_es' => 'nullable|string|max:255',
                'label_en' => 'nullable|string|max:255',
                'url' => 'nullable|string|max:500',
                'route_name' => 'nullable|string|max:255',
                'page_id' => 'nullable|integer|exists:cms_pages,id',
                'target' => 'nullable|in:_self,_blank',
                'icon' => 'nullable|string',
                'css_class' => 'nullable|string|max:255',
                'sort_order' => 'nullable|integer',
                'is_active' => 'nullable|boolean',
            ]);

            $item->update(array_filter($validated, fn ($v) => $v !== null));

            $menu = $item->menu;
            if ($menu) {
                CmsService::clearMenuCache($menu->slug);
            }

            return $this->jsonSuccess($item->fresh('children'), 'Item actualizado');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->apiValidationError($e->errors());
        } catch (\Throwable $e) {
            return $this->apiServerError($e);
        }
    }

    public function destroyItem(int $id): JsonResponse
    {
        try {
            $item = CmsMenuItem::find($id);
            if (!$item) {
                return $this->apiNotFound('Item no encontrado');
            }

            $menu = $item->menu;
            $item->delete();

            if ($menu) {
                CmsService::clearMenuCache($menu->slug);
            }

            return $this->jsonSuccess(null, 'Item eliminado');
        } catch (\Throwable $e) {
            return $this->apiServerError($e);
        }
    }

    public function reorderItems(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'items' => 'required|array',
                'items.*.id' => 'required|integer|exists:cms_menu_items,id',
                'items.*.sort_order' => 'required|integer',
                'items.*.parent_id' => 'nullable|integer',
            ]);

            foreach ($validated['items'] as $item) {
                CmsMenuItem::where('id', $item['id'])->update([
                    'sort_order' => $item['sort_order'],
                    'parent_id' => $item['parent_id'] ?? null,
                ]);
            }

            return $this->jsonSuccess(null, 'Items reordenados');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->apiValidationError($e->errors());
        } catch (\Throwable $e) {
            return $this->apiServerError($e);
        }
    }

    // ─── Público ────────────────────────────────────────

    public function showPublic(string $slug): JsonResponse
    {
        try {
            $menu = CmsService::getMenu($slug);
            if (!$menu) {
                return $this->apiNotFound('Menú no encontrado');
            }

            return $this->jsonSuccess($menu, 'Menú obtenido');
        } catch (\Throwable $e) {
            return $this->apiServerError($e);
        }
    }
}
