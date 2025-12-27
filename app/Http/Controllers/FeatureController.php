<?php

namespace App\Http\Controllers;

use App\Models\Feature;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FeatureController extends Controller
{
    /**
     * GET /api/features
     */
    public function index(Request $request): JsonResponse
    {
        $query = Feature::query();

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->filled('locale')) {
            $query->where('locale', (string) $request->input('locale'));
        }

        $sort = $request->input('sort', 'asc');
        $order = $request->input('order', 'name');
        $validOrders = ['id', 'name', 'locale', 'created_at', 'updated_at'];
        if (!in_array($order, $validOrders, true)) {
            $order = 'name';
        }
        $sort = $sort === 'desc' ? 'desc' : 'asc';
        $query->orderBy($order, $sort);

        $perPage = (int) $request->input('per_page', 15);
        $perPage = max(1, min(100, $perPage));

        return $this->apiSuccess('Listado de features', 'FEATURES_LIST', $query->paginate($perPage));
    }

    /**
     * POST /api/features
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'locale' => 'nullable|string|max:10',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        $data = $validator->validated();

        // Respeta el unique(name, locale)
        $exists = Feature::where('name', $data['name'])
            ->where('locale', $data['locale'] ?? null)
            ->exists();

        if ($exists) {
            return $this->apiError('El feature ya existe', 'FEATURE_ALREADY_EXISTS', ['name' => ['Ya existe este feature para el locale dado']], null, 409);
        }

        $feature = Feature::create($data);
        return $this->apiCreated('Feature creado', 'FEATURE_CREATED', $feature);
    }

    /**
     * GET /api/features/{feature}
     */
    public function show(Request $request, Feature $feature): JsonResponse
    {
        return $this->apiSuccess('Feature obtenido', 'FEATURE_SHOWN', $feature);
    }

    /**
     * PATCH /api/features/{feature}
     */
    public function update(Request $request, Feature $feature): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'locale' => 'sometimes|nullable|string|max:10',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        $data = $validator->validated();
        $feature->fill($data);

        $exists = Feature::where('name', $feature->name)
            ->where('locale', $feature->locale)
            ->where('id', '!=', $feature->id)
            ->exists();

        if ($exists) {
            return $this->apiError('El feature ya existe', 'FEATURE_ALREADY_EXISTS', ['name' => ['Ya existe este feature para el locale dado']], null, 409);
        }

        $feature->save();
        return $this->apiSuccess('Feature actualizado', 'FEATURE_UPDATED', $feature->fresh());
    }

    /**
     * DELETE /api/features/{feature}
     */
    public function destroy(Request $request, Feature $feature): JsonResponse
    {
        $feature->delete();
        return $this->apiSuccess('Feature eliminado', 'FEATURE_DELETED', null);
    }
}

