<?php

namespace App\Http\Controllers;

use App\Models\MLSOffice;
use App\Models\MLSAgent;
use App\Services\MLSSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Controlador para gestionar Offices (agencias/oficinas) del MLS AMPI.
 *
 * Endpoints:
 * - CRUD local de mls_offices
 * - Sincronización desde la API del MLS (progresiva por lotes)
 */
class MLSOfficeController extends Controller
{
    public function __construct(
        protected MLSSyncService $syncService
    ) {
    }

    /**
     * GET /api/mls-offices
     * Lista offices locales con paginación.
     */
    public function index(Request $request): JsonResponse
    {
        $query = MLSOffice::query()->withCount(['agents', 'properties']);

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('state_province', 'like', "%{$search}%")
                    ->orWhere('mls_office_id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('paid')) {
            $query->where('paid', $request->boolean('paid'));
        }

        // Filtro opcional por campo manual (a nuestro cargo)
        if ($request->filled('is_managed_by_us')) {
            $val = $request->input('is_managed_by_us');
            if ($val === '1' || $val === 1 || $val === true || $val === 'true') {
                $query->where('is_managed_by_us', true);
            } elseif ($val === '0' || $val === 0 || $val === false || $val === 'false') {
                $query->where('is_managed_by_us', false);
            }
        }

        if ($request->filled('city')) {
            $query->where('city', $request->input('city'));
        }

        if ($request->filled('state_province')) {
            $query->where('state_province', $request->input('state_province'));
        }

        $sort = $request->input('sort', 'asc');
        $order = $request->input('order', 'name');
        $validOrders = [
            'mls_office_id',
            'name',
            'city',
            'state_province',
            'paid',
            'is_managed_by_us',
            'last_synced_at',
            'created_at',
            'updated_at',
        ];
        if (!in_array($order, $validOrders, true)) {
            $order = 'name';
        }
        $sort = $sort === 'desc' ? 'desc' : 'asc';
        $query->orderBy($order, $sort);

        $perPage = (int) $request->input('per_page', 15);
        $perPage = max(1, min(100, $perPage));

        return $this->apiSuccess('Listado de offices MLS', 'MLS_OFFICES_LIST', $query->paginate($perPage));
    }

    /**
     * PATCH /api/mls-offices/{mlsOffice}/managed-by-us
     *
     * Campo MANUAL: no debe ser modificado por la sincronización del MLS.
     * Tampoco debe ser parte del CRUD estándar (store/update).
     */
    public function updateManagedByUs(Request $request, MLSOffice $mlsOffice): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'is_managed_by_us' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        $data = $validator->validated();
        $mlsOffice->is_managed_by_us = (bool) $data['is_managed_by_us'];
        $mlsOffice->save();

        return $this->apiSuccess('Campo actualizado', 'MLS_OFFICE_MANAGED_BY_US_UPDATED', $mlsOffice->fresh());
    }

    /**
     * GET /api/public/mls-offices
     * Listado público de agencias/oficinas MLS (paginación + búsqueda).
     */
    public function indexPublic(Request $request): JsonResponse
    {
        $query = MLSOffice::query()->withCount(['agents', 'properties']);

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('state_province', 'like', "%{$search}%")
                    ->orWhere('mls_office_id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('paid')) {
            $query->where('paid', $request->boolean('paid'));
        }

        $sort = $request->input('sort', 'asc');
        $order = $request->input('order', 'name');
        $validOrders = ['mls_office_id', 'name', 'city', 'state_province', 'paid', 'updated_at', 'created_at'];
        if (!in_array($order, $validOrders, true)) {
            $order = 'name';
        }
        $sort = $sort === 'desc' ? 'desc' : 'asc';
        $query->orderBy($order, $sort);

        $perPage = (int) $request->input('per_page', 12);
        $perPage = max(1, min(50, $perPage));

        return $this->apiSuccess('Listado público de agencias MLS', 'PUBLIC_MLS_OFFICES_LIST', $query->paginate($perPage));
    }

    /**
     * GET /api/public/mls-offices/{mlsOffice}
     * Detalle público de una agencia/oficina MLS.
     */
    public function showPublic(MLSOffice $mlsOffice): JsonResponse
    {
        $mlsOffice->load(['imageMediaAsset']);

        // Nota: no incluimos propiedades aquí para evitar payload gigante;
        // el frontend consulta /api/public/properties?mls_office_id=...
        return $this->apiSuccess('Agencia MLS obtenida', 'PUBLIC_MLS_OFFICE_SHOWN', $mlsOffice);
    }

    /**
     * GET /api/public/mls-offices/{mlsOffice}/agents
     * Lista pública (paginada) de agentes de una agencia/oficina.
     */
    public function agentsPublic(Request $request, MLSOffice $mlsOffice): JsonResponse
    {
        $query = MLSAgent::query()
            ->where('mls_office_id', $mlsOffice->mls_office_id)
            ->with(['photoMediaAsset'])
            ->withCount('properties');

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mls_agent_id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $sort = $request->input('sort', 'asc');
        $order = $request->input('order', 'name');
        $validOrders = ['name', 'email', 'mls_agent_id', 'created_at', 'updated_at', 'last_synced_at'];
        if (!in_array($order, $validOrders, true)) {
            $order = 'name';
        }
        $sort = $sort === 'desc' ? 'desc' : 'asc';
        $query->orderBy($order, $sort);

        $perPage = (int) $request->input('per_page', 12);
        $perPage = max(1, min(50, $perPage));

        return $this->apiSuccess('Agentes públicos de la agencia', 'PUBLIC_MLS_OFFICE_AGENTS', $query->paginate($perPage));
    }

    /**
     * GET /api/mls-offices/{mlsOffice}
     */
    public function show(MLSOffice $mlsOffice): JsonResponse
    {
        $mlsOffice->load([
            'agents.photoMediaAsset',
            'properties' => function ($q) {
                $q->select(['id', 'agency_id', 'source', 'mls_public_id', 'mls_id', 'title', 'published', 'status', 'category', 'mls_office_id']);
            },
        ]);

        return $this->apiSuccess('Office MLS obtenido', 'MLS_OFFICE_SHOWN', $mlsOffice);
    }

    /**
     * POST /api/mls-offices
     * Crea un office manualmente.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'mls_office_id' => 'required|integer|min:1|unique:mls_offices,mls_office_id',
            'name' => 'nullable|string|max:255',
            'business_hours' => 'nullable|string|max:255',
            'state_province' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'zip_code' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'image_path' => 'nullable|string',
            'image_url' => 'nullable|string',
            'image_media_asset_id' => 'nullable|exists:media_assets,id',
            'description' => 'nullable|string',
            'description_es' => 'nullable|string',
            'phone_1' => 'nullable|string|max:50',
            'phone_2' => 'nullable|string|max:50',
            'phone_3' => 'nullable|string|max:50',
            'fax' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|string|max:255',
            'facebook' => 'nullable|string|max:255',
            'youtube' => 'nullable|string|max:255',
            'x_twitter' => 'nullable|string|max:255',
            'tiktok' => 'nullable|string|max:255',
            'instagram' => 'nullable|string|max:255',
            'paid' => 'nullable|boolean',
            'mls_created_at' => 'nullable|date',
            'mls_updated_at' => 'nullable|date',
            'last_synced_at' => 'nullable|date',
            'raw_payload' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        $data = $validator->validated();
        $office = MLSOffice::create($data);

        return $this->apiCreated('Office MLS creado', 'MLS_OFFICE_CREATED', $office);
    }

    /**
     * PATCH /api/mls-offices/{mlsOffice}
     */
    public function update(Request $request, MLSOffice $mlsOffice): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|nullable|string|max:255',
            'business_hours' => 'sometimes|nullable|string|max:255',
            'state_province' => 'sometimes|nullable|string|max:100',
            'city' => 'sometimes|nullable|string|max:100',
            'address' => 'sometimes|nullable|string',
            'zip_code' => 'sometimes|nullable|string|max:20',
            'latitude' => 'sometimes|nullable|numeric',
            'longitude' => 'sometimes|nullable|numeric',
            'image_path' => 'sometimes|nullable|string',
            'image_url' => 'sometimes|nullable|string',
            'image_media_asset_id' => 'sometimes|nullable|exists:media_assets,id',
            'description' => 'sometimes|nullable|string',
            'description_es' => 'sometimes|nullable|string',
            'phone_1' => 'sometimes|nullable|string|max:50',
            'phone_2' => 'sometimes|nullable|string|max:50',
            'phone_3' => 'sometimes|nullable|string|max:50',
            'fax' => 'sometimes|nullable|string|max:50',
            'email' => 'sometimes|nullable|email|max:255',
            'website' => 'sometimes|nullable|string|max:255',
            'facebook' => 'sometimes|nullable|string|max:255',
            'youtube' => 'sometimes|nullable|string|max:255',
            'x_twitter' => 'sometimes|nullable|string|max:255',
            'tiktok' => 'sometimes|nullable|string|max:255',
            'instagram' => 'sometimes|nullable|string|max:255',
            'paid' => 'sometimes|nullable|boolean',
            'mls_created_at' => 'sometimes|nullable|date',
            'mls_updated_at' => 'sometimes|nullable|date',
            'last_synced_at' => 'sometimes|nullable|date',
            'raw_payload' => 'sometimes|nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        $mlsOffice->update($validator->validated());

        return $this->apiSuccess('Office MLS actualizado', 'MLS_OFFICE_UPDATED', $mlsOffice->fresh());
    }

    /**
     * DELETE /api/mls-offices/{mlsOffice}
     */
    public function destroy(MLSOffice $mlsOffice): JsonResponse
    {
        $mlsOffice->delete();

        return $this->apiSuccess('Office MLS eliminado', 'MLS_OFFICE_DELETED', null);
    }

    /**
     * GET /api/mls-offices/{mlsOffice}/agents
     * Lista los agentes de una office.
     */
    public function agents(MLSOffice $mlsOffice): JsonResponse
    {
        $mlsOffice->load('agents.photoMediaAsset');

        return $this->apiSuccess('Agentes de la office', 'MLS_OFFICE_AGENTS', $mlsOffice->agents);
    }

    /**
     * GET /api/mls-offices/{mlsOffice}/properties
     * Lista las propiedades de una office.
     */
    public function officeProperties(Request $request, MLSOffice $mlsOffice): JsonResponse
    {
        $query = $mlsOffice->properties()
            ->select(['id', 'agency_id', 'source', 'mls_public_id', 'mls_id', 'title', 'published', 'status', 'category', 'mls_office_id', 'updated_at']);

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('mls_public_id', 'like', "%{$search}%")
                    ->orWhere('mls_id', 'like', "%{$search}%");
            });
        }

        $perPage = max(1, min(100, (int) $request->input('per_page', 15)));

        return $this->apiSuccess('Propiedades de la office', 'MLS_OFFICE_PROPERTIES', $query->paginate($perPage));
    }

    /**
     * POST /api/mls-offices/sync
     * Sincroniza offices desde el MLS en modo progresivo.
     */
    public function syncOffices(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'batch_size' => 'sometimes|integer|min:1|max:100',
            'page' => 'sometimes|integer|min:1',
            'with_detail' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        $this->syncService->reloadConfiguration();

        if (!$this->syncService->isConfigured()) {
            return $this->apiError('MLS no está configurado', 'MLS_NOT_CONFIGURED', null, null, 400);
        }

        $opts = $validator->validated();
        $batchSize = (int) ($opts['batch_size'] ?? 25);
        $page = (int) ($opts['page'] ?? 1);
        $withDetail = (bool) ($opts['with_detail'] ?? false);

        $result = $this->syncService->syncOfficesProgressive($page, $batchSize, $withDetail);

        if ($result['success']) {
            return $this->apiSuccess(
                $result['completed'] ? 'Sincronización de offices completada' : 'Lote de offices procesado',
                'MLS_OFFICES_SYNCED',
                $result
            );
        }

        return $this->apiError(
            $result['message'] ?? 'Error en la sincronización de offices',
            'MLS_OFFICES_SYNC_ERROR',
            $result,
            null,
            500
        );
    }
}

