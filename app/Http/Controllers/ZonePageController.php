<?php

namespace App\Http\Controllers;

use App\Models\ZonePage;
use App\Services\PublicLocationMenuService;
use App\Services\ZonePageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ZonePageController extends Controller
{
    /**
     * GET /api/zone-pages
     */
    public function index(Request $request): JsonResponse
    {
        $query = ZonePage::query();

        if ($request->boolean('sync', false)) {
            ZonePageService::syncFromPublishedProperties();
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search): void {
                $q->where('city_area', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('region', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('title_es', 'like', "%{$search}%")
                    ->orWhere('title_en', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $sort = $request->input('sort', 'asc');
        $order = $request->input('order', 'city_area');
        $validOrders = ['id', 'city_area', 'city', 'region', 'slug', 'updated_at', 'last_detected_at'];
        if (!in_array($order, $validOrders, true)) {
            $order = 'city_area';
        }
        $sort = $sort === 'desc' ? 'desc' : 'asc';
        $query->orderBy($order, $sort);

        $perPage = (int) $request->input('per_page', 20);
        $perPage = max(1, min(100, $perPage));

        return $this->apiSuccess('Listado de páginas de zona', 'ZONE_PAGES_LIST', $query->paginate($perPage));
    }

    /**
     * GET /api/zone-pages/{zonePage}
     */
    public function show(Request $request, ZonePage $zonePage): JsonResponse
    {
        return $this->apiSuccess('Zona obtenida', 'ZONE_PAGE_SHOWN', $zonePage);
    }

    /**
     * PUT /api/zone-pages/{zonePage}
     */
    public function update(Request $request, ZonePage $zonePage): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:180',
                Rule::unique('zone_pages', 'slug')->ignore($zonePage->id),
            ],
            'title_es' => 'sometimes|nullable|string|max:255',
            'title_en' => 'sometimes|nullable|string|max:255',
            'description_es' => 'sometimes|nullable|string',
            'description_en' => 'sometimes|nullable|string',
            'meta_title_es' => 'sometimes|nullable|string|max:255',
            'meta_title_en' => 'sometimes|nullable|string|max:255',
            'meta_description_es' => 'sometimes|nullable|string',
            'meta_description_en' => 'sometimes|nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        $data = $validator->validated();

        if (array_key_exists('slug', $data)) {
            $slug = Str::slug((string) $data['slug']);
            if ($slug === '') {
                return $this->apiValidationError(['slug' => ['El slug no puede estar vacío.']]);
            }

            $slugExists = ZonePage::query()
                ->where('slug', $slug)
                ->where('id', '!=', $zonePage->id)
                ->exists();
            if ($slugExists) {
                return $this->apiValidationError(['slug' => ['El slug ya existe.']]);
            }

            $data['slug'] = $slug;
        }

        $zonePage->update($data);

        PublicLocationMenuService::clearCache();

        return $this->apiSuccess('Zona actualizada', 'ZONE_PAGE_UPDATED', $zonePage->fresh());
    }

    /**
     * POST /api/zone-pages/sync
     */
    public function sync(Request $request): JsonResponse
    {
        ZonePageService::syncFromPublishedProperties();
        PublicLocationMenuService::clearCache();

        return $this->apiSuccess('Zonas sincronizadas', 'ZONE_PAGES_SYNCED', [
            'total' => ZonePage::query()->count(),
            'active' => ZonePage::query()->where('is_active', true)->count(),
        ]);
    }
}
