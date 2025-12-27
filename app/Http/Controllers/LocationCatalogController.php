<?php

namespace App\Http\Controllers;

use App\Models\LocationCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LocationCatalogController extends Controller
{
    /**
     * GET /api/locations-catalog
     */
    public function index(Request $request): JsonResponse
    {
        $query = LocationCatalog::query();

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', (string) $request->input('type'));
        }

        if ($request->filled('parent_id')) {
            $query->where('parent_id', (int) $request->input('parent_id'));
        }

        $sort = $request->input('sort', 'asc');
        $order = $request->input('order', 'full_name');
        $validOrders = ['id', 'full_name', 'name', 'type', 'created_at', 'updated_at'];
        if (!in_array($order, $validOrders, true)) {
            $order = 'full_name';
        }
        $sort = $sort === 'desc' ? 'desc' : 'asc';
        $query->orderBy($order, $sort);

        $perPage = (int) $request->input('per_page', 15);
        $perPage = max(1, min(100, $perPage));

        return $this->apiSuccess('Listado de locations catalog', 'LOCATIONS_CATALOG_LIST', $query->paginate($perPage));
    }

    /**
     * POST /api/locations-catalog
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255|unique:locations_catalog,full_name',
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:Country,State,City,Neighborhood',
            'parent_id' => 'nullable|exists:locations_catalog,id',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        $loc = LocationCatalog::create($validator->validated());
        return $this->apiCreated('Location creada', 'LOCATION_CREATED', $loc);
    }

    /**
     * GET /api/locations-catalog/{locationCatalog}
     */
    public function show(Request $request, LocationCatalog $locationCatalog): JsonResponse
    {
        $locationCatalog->load(['parent', 'children']);
        return $this->apiSuccess('Location obtenida', 'LOCATION_SHOWN', $locationCatalog);
    }

    /**
     * PATCH /api/locations-catalog/{locationCatalog}
     */
    public function update(Request $request, LocationCatalog $locationCatalog): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'sometimes|required|string|max:255|unique:locations_catalog,full_name,' . $locationCatalog->id,
            'name' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|string|in:Country,State,City,Neighborhood',
            'parent_id' => 'sometimes|nullable|exists:locations_catalog,id',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        $data = $validator->validated();
        if (array_key_exists('parent_id', $data) && (int) $data['parent_id'] === (int) $locationCatalog->id) {
            return $this->apiValidationError(['parent_id' => ['Un nodo no puede ser padre de sí mismo']]);
        }

        $locationCatalog->update($data);
        return $this->apiSuccess('Location actualizada', 'LOCATION_UPDATED', $locationCatalog->fresh());
    }

    /**
     * DELETE /api/locations-catalog/{locationCatalog}
     */
    public function destroy(Request $request, LocationCatalog $locationCatalog): JsonResponse
    {
        $locationCatalog->delete();
        return $this->apiSuccess('Location eliminada', 'LOCATION_DELETED', null);
    }
}

