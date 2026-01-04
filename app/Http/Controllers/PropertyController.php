<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PropertyController extends Controller
{
    /**
     * GET /api/public/properties
     * Public endpoint for the website (no authentication required)
     */
    public function indexPublic(Request $request): JsonResponse
    {
        $query = Property::query()
            ->where('published', true)
            ->with([
                'agency',
                'agentUser.profileImage',
                'coverMediaAsset',
                'location',
                'operations.currency',
            ]);

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('property_type_name', 'like', "%{$search}%")
                    ->orWhereHas('location', function ($loc) use ($search) {
                        $loc->where('city', 'like', "%{$search}%")
                            ->orWhere('city_area', 'like', "%{$search}%")
                            ->orWhere('region', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('property_type_name')) {
            $query->where('property_type_name', (string) $request->input('property_type_name'));
        }

        if ($request->filled('operation_type')) {
            $operationType = $request->input('operation_type');
            $query->whereHas('operations', function ($op) use ($operationType) {
                $op->where('operation_type', $operationType);
            });
        }

        if ($request->filled('min_price')) {
            $minPrice = (float) $request->input('min_price');
            $query->whereHas('operations', function ($op) use ($minPrice) {
                $op->where('amount', '>=', $minPrice);
            });
        }

        if ($request->filled('max_price')) {
            $maxPrice = (float) $request->input('max_price');
            $query->whereHas('operations', function ($op) use ($maxPrice) {
                $op->where('amount', '<=', $maxPrice);
            });
        }

        if ($request->filled('bedrooms')) {
            $query->where('bedrooms', '>=', (int) $request->input('bedrooms'));
        }

        if ($request->filled('bathrooms')) {
            $query->where('bathrooms', '>=', (int) $request->input('bathrooms'));
        }

        $sort = $request->input('sort', 'desc');
        $order = $request->input('order', 'updated_at');
        $validOrders = ['created_at', 'updated_at', 'title', 'property_type_name'];
        if (!in_array($order, $validOrders, true)) {
            $order = 'updated_at';
        }
        $sort = $sort === 'asc' ? 'asc' : 'desc';
        $query->orderBy($order, $sort);

        $perPage = (int) $request->input('per_page', 6);
        $perPage = max(1, min(50, $perPage));

        return $this->apiSuccess('Listado de propiedades públicas', 'PUBLIC_PROPERTIES_LIST', $query->paginate($perPage));
    }

    /**
     * GET /api/public/properties/{property}
     * Public endpoint to show a single property (no authentication required)
     */
    public function showPublic(Request $request, Property $property): JsonResponse
    {
        if (!$property->published) {
            return $this->apiError('Propiedad no disponible', 'PROPERTY_NOT_PUBLISHED', null, null, 404);
        }

        $property->load([
            'agency',
            'agentUser.profileImage',
            'coverMediaAsset',
            'location',
            'operations.currency',
            'features',
            'tags',
            'mediaAssets'
        ]);

        return $this->apiSuccess('Propiedad obtenida', 'PUBLIC_PROPERTY_SHOWN', $property);
    }

    /**
     * GET /api/properties
     */
    public function index(Request $request): JsonResponse
    {
        $query = Property::query()->with([
            'agency',
            'agentUser.profileImage',
            'coverMediaAsset',
        ]);

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('easybroker_public_id', 'like', "%{$search}%")
                    ->orWhere('property_type_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('agency_id')) {
            $query->where('agency_id', (int) $request->input('agency_id'));
        }

        if ($request->filled('published')) {
            $query->where('published', (bool) $request->boolean('published'));
        }

        if ($request->filled('property_type_name')) {
            $query->where('property_type_name', (string) $request->input('property_type_name'));
        }

        $sort = $request->input('sort', 'desc');
        $order = $request->input('order', 'updated_at');
        $validOrders = [
            'created_at', 'updated_at',
            'easybroker_updated_at',
            'title', 'published',
            'property_type_name',
        ];
        if (!in_array($order, $validOrders, true)) {
            $order = 'updated_at';
        }
        $sort = $sort === 'asc' ? 'asc' : 'desc';
        $query->orderBy($order, $sort);

        $perPage = (int) $request->input('per_page', 15);
        $perPage = max(1, min(100, $perPage));

        return $this->apiSuccess('Listado de propiedades', 'PROPERTIES_LIST', $query->paginate($perPage));
    }

    /**
     * POST /api/properties
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'agency_id' => 'required|exists:agencies,id',
            'agent_user_id' => 'nullable|exists:users,id',
            'easybroker_public_id' => 'required|string|max:50',
            'easybroker_agent_id' => 'nullable|string|max:50',

            'published' => 'boolean',
            'easybroker_created_at' => 'nullable|date',
            'easybroker_updated_at' => 'nullable|date',
            'last_synced_at' => 'nullable|date',

            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'url' => 'nullable|string',
            'ad_type' => 'nullable|string|max:50',
            'property_type_name' => 'nullable|string|max:100',

            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'half_bathrooms' => 'nullable|integer|min:0',
            'parking_spaces' => 'nullable|integer|min:0',

            'lot_size' => 'nullable|numeric|min:0',
            'construction_size' => 'nullable|numeric|min:0',
            'expenses' => 'nullable|numeric|min:0',
            'lot_length' => 'nullable|numeric|min:0',
            'lot_width' => 'nullable|numeric|min:0',

            'floors' => 'nullable|integer|min:0',
            'floor' => 'nullable|string|max:20',
            'age' => 'nullable|string|max:20',

            'virtual_tour_url' => 'nullable|string',
            'cover_media_asset_id' => 'nullable|exists:media_assets,id',

            'raw_payload' => 'nullable|array',

            // Relaciones opcionales
            'location' => 'nullable|array',
            'location.region' => 'nullable|string|max:255',
            'location.city' => 'nullable|string|max:255',
            'location.city_area' => 'nullable|string|max:255',
            'location.street' => 'nullable|string|max:255',
            'location.postal_code' => 'nullable|string|max:20',
            'location.show_exact_location' => 'nullable|boolean',
            'location.latitude' => 'nullable|numeric',
            'location.longitude' => 'nullable|numeric',
            'location.raw_payload' => 'nullable|array',

            'operations' => 'nullable|array',
            'operations.*.operation_type' => 'required_with:operations|string|max:20',
            'operations.*.amount' => 'nullable|numeric',
            'operations.*.currency_id' => 'nullable|exists:currencies,id',
            'operations.*.currency_code' => 'nullable|string|size:3',
            'operations.*.formatted_amount' => 'nullable|string|max:50',
            'operations.*.unit' => 'nullable|string|max:20',
            'operations.*.raw_payload' => 'nullable|array',

            'feature_ids' => 'nullable|array',
            'feature_ids.*' => 'integer|exists:features,id',

            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'integer|exists:tags,id',

            'media' => 'nullable|array',
            'media.*.media_asset_id' => 'required_with:media|integer|exists:media_assets,id',
            'media.*.role' => 'nullable|string|max:20',
            'media.*.title' => 'nullable|string|max:255',
            'media.*.position' => 'nullable|integer|min:0',
            'media.*.checksum' => 'nullable|string|size:32',
            'media.*.source_url' => 'nullable|string',
            'media.*.raw_payload' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        $data = $validator->validated();

        $uniqueExists = Property::where('agency_id', $data['agency_id'])
            ->where('easybroker_public_id', $data['easybroker_public_id'])
            ->exists();
        if ($uniqueExists) {
            return $this->apiError('La propiedad ya existe para esa agencia', 'PROPERTY_ALREADY_EXISTS', null, null, 409);
        }

        try {
            $property = DB::transaction(function () use ($data) {
                $location = $data['location'] ?? null;
                $operations = $data['operations'] ?? null;
                $featureIds = $data['feature_ids'] ?? null;
                $tagIds = $data['tag_ids'] ?? null;
                $media = $data['media'] ?? null;

                unset($data['location'], $data['operations'], $data['feature_ids'], $data['tag_ids'], $data['media']);

                $property = Property::create($data);

                if (is_array($location)) {
                    $property->location()->updateOrCreate(
                        ['property_id' => $property->id],
                        array_merge($location, ['property_id' => $property->id])
                    );
                }

                if (is_array($operations)) {
                    $property->operations()->delete();
                    foreach ($operations as $op) {
                        $property->operations()->create($op);
                    }
                }

                if (is_array($featureIds)) {
                    $property->features()->sync($featureIds);
                }

                if (is_array($tagIds)) {
                    $property->tags()->sync($tagIds);
                }

                if (is_array($media)) {
                    $sync = [];
                    foreach ($media as $m) {
                        $id = (int) $m['media_asset_id'];
                        $sync[$id] = [
                            'role' => $m['role'] ?? 'image',
                            'title' => $m['title'] ?? null,
                            'position' => $m['position'] ?? null,
                            'checksum' => $m['checksum'] ?? null,
                            'source_url' => $m['source_url'] ?? null,
                            'raw_payload' => isset($m['raw_payload']) && is_array($m['raw_payload']) ? json_encode($m['raw_payload']) : null,
                        ];
                    }
                    $property->mediaAssets()->sync($sync);
                }

                return $property;
            });
        } catch (\Throwable $e) {
            return $this->apiServerError($e, 'PROPERTY_CREATE_FAILED');
        }

        $property->load(['agency', 'agentUser.profileImage', 'coverMediaAsset', 'location', 'operations', 'features', 'tags', 'mediaAssets']);

        return $this->apiCreated('Propiedad creada', 'PROPERTY_CREATED', $property);
    }

    /**
     * GET /api/properties/{property}
     */
    public function show(Request $request, Property $property): JsonResponse
    {
        $property->load(['agency', 'agentUser.profileImage', 'coverMediaAsset', 'location', 'operations', 'features', 'tags', 'mediaAssets']);
        return $this->apiSuccess('Propiedad obtenida', 'PROPERTY_SHOWN', $property);
    }

    /**
     * PATCH /api/properties/{property}
     */
    public function update(Request $request, Property $property): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'agency_id' => 'sometimes|required|exists:agencies,id',
            'agent_user_id' => 'sometimes|nullable|exists:users,id',
            'easybroker_public_id' => 'sometimes|required|string|max:50',
            'easybroker_agent_id' => 'sometimes|nullable|string|max:50',

            'published' => 'sometimes|boolean',
            'easybroker_created_at' => 'sometimes|nullable|date',
            'easybroker_updated_at' => 'sometimes|nullable|date',
            'last_synced_at' => 'sometimes|nullable|date',

            'title' => 'sometimes|nullable|string|max:255',
            'description' => 'sometimes|nullable|string',
            'url' => 'sometimes|nullable|string',
            'ad_type' => 'sometimes|nullable|string|max:50',
            'property_type_name' => 'sometimes|nullable|string|max:100',

            'bedrooms' => 'sometimes|nullable|integer|min:0',
            'bathrooms' => 'sometimes|nullable|integer|min:0',
            'half_bathrooms' => 'sometimes|nullable|integer|min:0',
            'parking_spaces' => 'sometimes|nullable|integer|min:0',

            'lot_size' => 'sometimes|nullable|numeric|min:0',
            'construction_size' => 'sometimes|nullable|numeric|min:0',
            'expenses' => 'sometimes|nullable|numeric|min:0',
            'lot_length' => 'sometimes|nullable|numeric|min:0',
            'lot_width' => 'sometimes|nullable|numeric|min:0',

            'floors' => 'sometimes|nullable|integer|min:0',
            'floor' => 'sometimes|nullable|string|max:20',
            'age' => 'sometimes|nullable|string|max:20',

            'virtual_tour_url' => 'sometimes|nullable|string',
            'cover_media_asset_id' => 'sometimes|nullable|exists:media_assets,id',

            'raw_payload' => 'sometimes|nullable|array',

            // Relaciones opcionales
            'location' => 'sometimes|nullable|array',
            'location.region' => 'nullable|string|max:255',
            'location.city' => 'nullable|string|max:255',
            'location.city_area' => 'nullable|string|max:255',
            'location.street' => 'nullable|string|max:255',
            'location.postal_code' => 'nullable|string|max:20',
            'location.show_exact_location' => 'nullable|boolean',
            'location.latitude' => 'nullable|numeric',
            'location.longitude' => 'nullable|numeric',
            'location.raw_payload' => 'nullable|array',

            'operations' => 'sometimes|nullable|array',
            'operations.*.operation_type' => 'required_with:operations|string|max:20',
            'operations.*.amount' => 'nullable|numeric',
            'operations.*.currency_id' => 'nullable|exists:currencies,id',
            'operations.*.currency_code' => 'nullable|string|size:3',
            'operations.*.formatted_amount' => 'nullable|string|max:50',
            'operations.*.unit' => 'nullable|string|max:20',
            'operations.*.raw_payload' => 'nullable|array',

            'feature_ids' => 'sometimes|nullable|array',
            'feature_ids.*' => 'integer|exists:features,id',

            'tag_ids' => 'sometimes|nullable|array',
            'tag_ids.*' => 'integer|exists:tags,id',

            'media' => 'sometimes|nullable|array',
            'media.*.media_asset_id' => 'required_with:media|integer|exists:media_assets,id',
            'media.*.role' => 'nullable|string|max:20',
            'media.*.title' => 'nullable|string|max:255',
            'media.*.position' => 'nullable|integer|min:0',
            'media.*.checksum' => 'nullable|string|size:32',
            'media.*.source_url' => 'nullable|string',
            'media.*.raw_payload' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        $data = $validator->validated();

        // Validación de unique(agency_id, easybroker_public_id) si cambian.
        $agencyId = $data['agency_id'] ?? $property->agency_id;
        $publicId = $data['easybroker_public_id'] ?? $property->easybroker_public_id;
        $uniqueExists = Property::where('agency_id', $agencyId)
            ->where('easybroker_public_id', $publicId)
            ->where('id', '!=', $property->id)
            ->exists();
        if ($uniqueExists) {
            return $this->apiError('La propiedad ya existe para esa agencia', 'PROPERTY_ALREADY_EXISTS', null, null, 409);
        }

        try {
            DB::transaction(function () use ($property, $data) {
                $location = $data['location'] ?? null;
                $operations = $data['operations'] ?? null;
                $featureIds = $data['feature_ids'] ?? null;
                $tagIds = $data['tag_ids'] ?? null;
                $media = $data['media'] ?? null;

                unset($data['location'], $data['operations'], $data['feature_ids'], $data['tag_ids'], $data['media']);

                $property->update($data);

                // Nota: si el cliente envía `location: null`, no borramos la location existente.
                if (is_array($location)) {
                    $property->location()->updateOrCreate(
                        ['property_id' => $property->id],
                        array_merge($location, ['property_id' => $property->id])
                    );
                }

                if (is_array($operations)) {
                    $property->operations()->delete();
                    foreach ($operations as $op) {
                        $property->operations()->create($op);
                    }
                }

                if (is_array($featureIds)) {
                    $property->features()->sync($featureIds);
                }

                if (is_array($tagIds)) {
                    $property->tags()->sync($tagIds);
                }

                if (is_array($media)) {
                    $sync = [];
                    foreach ($media as $m) {
                        $id = (int) $m['media_asset_id'];
                        $sync[$id] = [
                            'role' => $m['role'] ?? 'image',
                            'title' => $m['title'] ?? null,
                            'position' => $m['position'] ?? null,
                            'checksum' => $m['checksum'] ?? null,
                            'source_url' => $m['source_url'] ?? null,
                            'raw_payload' => isset($m['raw_payload']) && is_array($m['raw_payload']) ? json_encode($m['raw_payload']) : null,
                        ];
                    }
                    $property->mediaAssets()->sync($sync);
                }
            });
        } catch (\Throwable $e) {
            return $this->apiServerError($e, 'PROPERTY_UPDATE_FAILED');
        }

        $property->load(['agency', 'agentUser.profileImage', 'coverMediaAsset', 'location', 'operations', 'features', 'tags', 'mediaAssets']);
        return $this->apiSuccess('Propiedad actualizada', 'PROPERTY_UPDATED', $property);
    }

    /**
     * DELETE /api/properties/{property}
     */
    public function destroy(Request $request, Property $property): JsonResponse
    {
        $property->delete();
        return $this->apiSuccess('Propiedad eliminada', 'PROPERTY_DELETED', null);
    }
}

