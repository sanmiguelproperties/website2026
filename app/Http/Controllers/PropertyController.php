<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\PropertyLocation;
use App\Models\PropertyOperation;
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

        // ===== Filtros avanzados (opcionales) =====
        // Nota: si el frontend envía estos parámetros, la API los soporta sin romper compatibilidad.
        if ($request->filled('parking_spaces')) {
            $query->where('parking_spaces', '>=', (int) $request->input('parking_spaces'));
        }

        if ($request->filled('min_construction_size')) {
            $query->where('construction_size', '>=', (float) $request->input('min_construction_size'));
        }

        if ($request->filled('min_lot_size')) {
            $query->where('lot_size', '>=', (float) $request->input('min_lot_size'));
        }

        if (
            $request->filled('region') ||
            $request->filled('city') ||
            $request->filled('city_area')
        ) {
            $region = $request->filled('region') ? trim((string) $request->input('region')) : null;
            $city = $request->filled('city') ? trim((string) $request->input('city')) : null;
            $cityArea = $request->filled('city_area') ? trim((string) $request->input('city_area')) : null;

            $query->whereHas('location', function ($loc) use ($region, $city, $cityArea) {
                if ($region) {
                    $loc->where('region', 'like', "%{$region}%");
                }
                if ($city) {
                    $loc->where('city', 'like', "%{$city}%");
                }
                if ($cityArea) {
                    $loc->where('city_area', 'like', "%{$cityArea}%");
                }
            });
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
                    ->orWhere('mls_public_id', 'like', "%{$search}%")
                    ->orWhere('property_type_name', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->filled('agency_id')) {
            $query->where('agency_id', (int) $request->input('agency_id'));
        }

        if ($request->filled('published')) {
            $query->where('published', (bool) $request->boolean('published'));
        }

        if ($request->filled('source')) {
            $query->where('source', (string) $request->input('source'));
        }

        if ($request->filled('property_type_name')) {
            $query->where('property_type_name', (string) $request->input('property_type_name'));
        }

        $sort = $request->input('sort', 'desc');
        $order = $request->input('order', 'updated_at');
        $validOrders = [
            'created_at', 'updated_at',
            'easybroker_updated_at',
            'mls_updated_at', 'last_synced_at',
            'title', 'published',
            'property_type_name', 'source',
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
            'source' => 'nullable|string|in:manual,easybroker,mls',
            'agent_user_id' => 'nullable|exists:users,id',
            
            // EasyBroker (opcionales - el centro de datos es el LMS)
            'easybroker_public_id' => 'nullable|string|max:50',
            'easybroker_agent_id' => 'nullable|string|max:50',
            
            // MLS fields
            'mls_id' => 'nullable|integer',
            'mls_public_id' => 'nullable|string|max:50',
            'mls_folder_name' => 'nullable|string|max:255',
            'mls_neighborhood' => 'nullable|string|max:100',
            'mls_office_id' => 'nullable|integer',

            // Estado y publicación
            'published' => 'boolean',
            'status' => 'nullable|string|max:50',
            'category' => 'nullable|string|max:50',
            'is_approved' => 'nullable|boolean',
            'allow_integration' => 'nullable|boolean',
            'for_rent' => 'nullable|boolean',
            
            // Fechas
            'easybroker_created_at' => 'nullable|date',
            'easybroker_updated_at' => 'nullable|date',
            'mls_created_at' => 'nullable|date',
            'mls_updated_at' => 'nullable|date',
            'last_synced_at' => 'nullable|date',

            // Contenido
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_short_en' => 'nullable|string',
            'description_full_en' => 'nullable|string',
            'description_short_es' => 'nullable|string',
            'description_full_es' => 'nullable|string',
            'url' => 'nullable|string',
            'ad_type' => 'nullable|string|max:50',
            'property_type_name' => 'nullable|string|max:100',

            // Características numéricas
            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|numeric|min:0',
            'half_bathrooms' => 'nullable|integer|min:0',
            'parking_spaces' => 'nullable|integer|min:0',
            'parking_number' => 'nullable|integer|min:0',
            'parking_type' => 'nullable|string|max:50',

            // Tamaños
            'lot_size' => 'nullable|numeric|min:0',
            'lot_feet' => 'nullable|numeric|min:0',
            'construction_size' => 'nullable|numeric|min:0',
            'construction_feet' => 'nullable|numeric|min:0',
            'expenses' => 'nullable|numeric|min:0',
            'old_price' => 'nullable|numeric|min:0',
            'lot_length' => 'nullable|numeric|min:0',
            'lot_width' => 'nullable|numeric|min:0',

            'floors' => 'nullable|integer|min:0',
            'floor' => 'nullable|string|max:20',
            'age' => 'nullable|string|max:20',
            'year_built' => 'nullable|integer|min:1800|max:2100',
            
            // Características MLS
            'furnished' => 'nullable|string|max:20',
            'with_yard' => 'nullable|boolean',
            'with_view' => 'nullable|string|max:100',
            'gated_comm' => 'nullable|boolean',
            'pool' => 'nullable|boolean',
            'casita' => 'nullable|boolean',
            'casita_bedrooms' => 'nullable|string|max:10',
            'casita_bathrooms' => 'nullable|string|max:10',
            'payment' => 'nullable|string|max:50',
            'selling_office_commission' => 'nullable|string|max:20',
            'showing_terms' => 'nullable|string|max:50',

            'virtual_tour_url' => 'nullable|string',
            'video_url' => 'nullable|string',
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
        
        // Asignar source por defecto si no se especifica
        if (!isset($data['source'])) {
            $data['source'] = 'manual';
        }

        // Verificar unicidad solo si hay easybroker_public_id
        if (!empty($data['easybroker_public_id'])) {
            $uniqueExists = Property::where('agency_id', $data['agency_id'])
                ->where('easybroker_public_id', $data['easybroker_public_id'])
                ->exists();
            if ($uniqueExists) {
                return $this->apiError('La propiedad ya existe para esa agencia (EasyBroker ID)', 'PROPERTY_ALREADY_EXISTS', null, null, 409);
            }
        }
        
        // Verificar unicidad de MLS public ID si se proporciona
        if (!empty($data['mls_public_id'])) {
            $uniqueExists = Property::where('agency_id', $data['agency_id'])
                ->where('mls_public_id', $data['mls_public_id'])
                ->exists();
            if ($uniqueExists) {
                return $this->apiError('La propiedad ya existe para esa agencia (MLS ID)', 'PROPERTY_ALREADY_EXISTS', null, null, 409);
            }
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
            'source' => 'sometimes|nullable|string|in:manual,easybroker,mls',
            'agent_user_id' => 'sometimes|nullable|exists:users,id',
            
            // EasyBroker (opcionales)
            'easybroker_public_id' => 'sometimes|nullable|string|max:50',
            'easybroker_agent_id' => 'sometimes|nullable|string|max:50',
            
            // MLS fields
            'mls_id' => 'sometimes|nullable|integer',
            'mls_public_id' => 'sometimes|nullable|string|max:50',
            'mls_folder_name' => 'sometimes|nullable|string|max:255',
            'mls_neighborhood' => 'sometimes|nullable|string|max:100',
            'mls_office_id' => 'sometimes|nullable|integer',

            // Estado y publicación
            'published' => 'sometimes|boolean',
            'status' => 'sometimes|nullable|string|max:50',
            'category' => 'sometimes|nullable|string|max:50',
            'is_approved' => 'sometimes|nullable|boolean',
            'allow_integration' => 'sometimes|nullable|boolean',
            'for_rent' => 'sometimes|nullable|boolean',
            
            // Fechas
            'easybroker_created_at' => 'sometimes|nullable|date',
            'easybroker_updated_at' => 'sometimes|nullable|date',
            'mls_created_at' => 'sometimes|nullable|date',
            'mls_updated_at' => 'sometimes|nullable|date',
            'last_synced_at' => 'sometimes|nullable|date',

            // Contenido
            'title' => 'sometimes|nullable|string|max:255',
            'description' => 'sometimes|nullable|string',
            'description_short_en' => 'sometimes|nullable|string',
            'description_full_en' => 'sometimes|nullable|string',
            'description_short_es' => 'sometimes|nullable|string',
            'description_full_es' => 'sometimes|nullable|string',
            'url' => 'sometimes|nullable|string',
            'ad_type' => 'sometimes|nullable|string|max:50',
            'property_type_name' => 'sometimes|nullable|string|max:100',

            // Características numéricas
            'bedrooms' => 'sometimes|nullable|integer|min:0',
            'bathrooms' => 'sometimes|nullable|numeric|min:0',
            'half_bathrooms' => 'sometimes|nullable|integer|min:0',
            'parking_spaces' => 'sometimes|nullable|integer|min:0',
            'parking_number' => 'sometimes|nullable|integer|min:0',
            'parking_type' => 'sometimes|nullable|string|max:50',

            // Tamaños
            'lot_size' => 'sometimes|nullable|numeric|min:0',
            'lot_feet' => 'sometimes|nullable|numeric|min:0',
            'construction_size' => 'sometimes|nullable|numeric|min:0',
            'construction_feet' => 'sometimes|nullable|numeric|min:0',
            'expenses' => 'sometimes|nullable|numeric|min:0',
            'old_price' => 'sometimes|nullable|numeric|min:0',
            'lot_length' => 'sometimes|nullable|numeric|min:0',
            'lot_width' => 'sometimes|nullable|numeric|min:0',

            'floors' => 'sometimes|nullable|integer|min:0',
            'floor' => 'sometimes|nullable|string|max:20',
            'age' => 'sometimes|nullable|string|max:20',
            'year_built' => 'sometimes|nullable|integer|min:1800|max:2100',
            
            // Características MLS
            'furnished' => 'sometimes|nullable|string|max:20',
            'with_yard' => 'sometimes|nullable|boolean',
            'with_view' => 'sometimes|nullable|string|max:100',
            'gated_comm' => 'sometimes|nullable|boolean',
            'pool' => 'sometimes|nullable|boolean',
            'casita' => 'sometimes|nullable|boolean',
            'casita_bedrooms' => 'sometimes|nullable|string|max:10',
            'casita_bathrooms' => 'sometimes|nullable|string|max:10',
            'payment' => 'sometimes|nullable|string|max:50',
            'selling_office_commission' => 'sometimes|nullable|string|max:20',
            'showing_terms' => 'sometimes|nullable|string|max:50',

            'virtual_tour_url' => 'sometimes|nullable|string',
            'video_url' => 'sometimes|nullable|string',
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

        // Validación de unique solo si hay easybroker_public_id
        $agencyId = $data['agency_id'] ?? $property->agency_id;
        $publicId = $data['easybroker_public_id'] ?? $property->easybroker_public_id;
        if (!empty($publicId)) {
            $uniqueExists = Property::where('agency_id', $agencyId)
                ->where('easybroker_public_id', $publicId)
                ->where('id', '!=', $property->id)
                ->exists();
            if ($uniqueExists) {
                return $this->apiError('La propiedad ya existe para esa agencia (EasyBroker ID)', 'PROPERTY_ALREADY_EXISTS', null, null, 409);
            }
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

    /**
     * GET /api/public/properties/filter-options
     * Returns dynamic filter options based on existing published property data.
     */
    public function filterOptions(Request $request): JsonResponse
    {
        $publishedScope = fn ($q) => $q->where('published', true);

        // Tipos de propiedad distintos
        $propertyTypes = Property::where('published', true)
            ->whereNotNull('property_type_name')
            ->where('property_type_name', '!=', '')
            ->distinct()
            ->pluck('property_type_name')
            ->sort()
            ->values();

        // Tipos de operación distintos
        $operationTypes = PropertyOperation::whereHas('property', $publishedScope)
            ->whereNotNull('operation_type')
            ->where('operation_type', '!=', '')
            ->distinct()
            ->pluck('operation_type')
            ->sort()
            ->values();

        // Ciudades distintas
        $cities = PropertyLocation::whereHas('property', $publishedScope)
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->distinct()
            ->pluck('city')
            ->sort()
            ->values();

        // Regiones/estados distintos
        $regions = PropertyLocation::whereHas('property', $publishedScope)
            ->whereNotNull('region')
            ->where('region', '!=', '')
            ->distinct()
            ->pluck('region')
            ->sort()
            ->values();

        // Zonas/colonias distintas
        $cityAreas = PropertyLocation::whereHas('property', $publishedScope)
            ->whereNotNull('city_area')
            ->where('city_area', '!=', '')
            ->distinct()
            ->pluck('city_area')
            ->sort()
            ->values();

        // Valores distintos de recámaras (ordenados)
        $bedroomValues = Property::where('published', true)
            ->whereNotNull('bedrooms')
            ->where('bedrooms', '>', 0)
            ->distinct()
            ->pluck('bedrooms')
            ->sort()
            ->values()
            ->map(fn ($v) => (int) $v);

        // Valores distintos de baños (ordenados)
        $bathroomValues = Property::where('published', true)
            ->whereNotNull('bathrooms')
            ->where('bathrooms', '>', 0)
            ->distinct()
            ->pluck('bathrooms')
            ->sort()
            ->values()
            ->map(fn ($v) => (float) $v);

        // Valores distintos de estacionamientos (ordenados)
        $parkingValues = Property::where('published', true)
            ->whereNotNull('parking_spaces')
            ->where('parking_spaces', '>', 0)
            ->distinct()
            ->pluck('parking_spaces')
            ->sort()
            ->values()
            ->map(fn ($v) => (int) $v);

        // Rango de precios
        $priceRange = PropertyOperation::whereHas('property', $publishedScope)
            ->whereNotNull('amount')
            ->where('amount', '>', 0)
            ->selectRaw('MIN(amount) as min_price, MAX(amount) as max_price')
            ->first();

        // Rango de tamaño de construcción
        $constructionRange = Property::where('published', true)
            ->whereNotNull('construction_size')
            ->where('construction_size', '>', 0)
            ->selectRaw('MIN(construction_size) as min_size, MAX(construction_size) as max_size')
            ->first();

        // Rango de tamaño de terreno
        $lotRange = Property::where('published', true)
            ->whereNotNull('lot_size')
            ->where('lot_size', '>', 0)
            ->selectRaw('MIN(lot_size) as min_size, MAX(lot_size) as max_size')
            ->first();

        // Total de propiedades publicadas
        $totalCount = Property::where('published', true)->count();

        return $this->apiSuccess('Opciones de filtro', 'FILTER_OPTIONS', [
            'property_types'   => $propertyTypes,
            'operation_types'  => $operationTypes,
            'cities'           => $cities,
            'regions'          => $regions,
            'city_areas'       => $cityAreas,
            'bedrooms'         => $bedroomValues,
            'bathrooms'        => $bathroomValues,
            'parking_spaces'   => $parkingValues,
            'price_range'      => [
                'min' => (float) ($priceRange->min_price ?? 0),
                'max' => (float) ($priceRange->max_price ?? 0),
            ],
            'construction_size_range' => [
                'min' => (float) ($constructionRange->min_size ?? 0),
                'max' => (float) ($constructionRange->max_size ?? 0),
            ],
            'lot_size_range' => [
                'min' => (float) ($lotRange->min_size ?? 0),
                'max' => (float) ($lotRange->max_size ?? 0),
            ],
            'total_properties' => $totalCount,
        ]);
    }
}

