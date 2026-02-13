<?php

namespace App\Http\Controllers;

use App\Models\MLSAgent;
use App\Models\MLSOffice;
use App\Models\Property;
use App\Services\MLSSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Controlador para gestionar agentes del MLS AMPI San Miguel de Allende.
 * 
 * Proporciona endpoints para:
 * - Listar agentes MLS locales
 * - Ver detalle de un agente
 * - Sincronizar agentes desde el MLS
 * - Asociar/desasociar agentes a propiedades
 */
class MLSAgentController extends Controller
{
    protected MLSSyncService $syncService;

    public function __construct(MLSSyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    /**
     * GET /api/mls-agents
     * Lista todos los agentes MLS locales con paginación.
     */
    public function index(Request $request): JsonResponse
    {
        $query = MLSAgent::query()->with(['photoMediaAsset', 'user'])->withCount('properties');

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('office_name', 'like', "%{$search}%")
                    ->orWhere('mls_agent_id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('office_id')) {
            $query->where('mls_office_id', (int) $request->input('office_id'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $sort = $request->input('sort', 'asc');
        $order = $request->input('order', 'name');
        $validOrders = ['name', 'email', 'mls_agent_id', 'office_name', 'created_at', 'updated_at', 'last_synced_at'];
        if (!in_array($order, $validOrders, true)) {
            $order = 'name';
        }
        $sort = $sort === 'desc' ? 'desc' : 'asc';
        $query->orderBy($order, $sort);

        $perPage = (int) $request->input('per_page', 15);
        $perPage = max(1, min(100, $perPage));

        return $this->apiSuccess('Listado de agentes MLS', 'MLS_AGENTS_LIST', $query->paginate($perPage));
    }

    /**
     * GET /api/public/mls-agents
     * Listado público de agentes MLS (paginación + búsqueda + filtro por office).
     */
    public function indexPublic(Request $request): JsonResponse
    {
        $query = MLSAgent::query()
            ->with(['photoMediaAsset', 'office'])
            ->withCount('properties')
            ->where('is_active', true);

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('office_name', 'like', "%{$search}%")
                    ->orWhere('mls_agent_id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('office_id')) {
            $query->where('mls_office_id', (int) $request->input('office_id'));
        }

        // Por compatibilidad permitimos desactivar filtro de activos si se manda explícitamente.
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $sort = $request->input('sort', 'asc');
        $order = $request->input('order', 'name');
        $validOrders = ['name', 'email', 'mls_agent_id', 'office_name', 'created_at', 'updated_at', 'last_synced_at'];
        if (!in_array($order, $validOrders, true)) {
            $order = 'name';
        }
        $sort = $sort === 'desc' ? 'desc' : 'asc';
        $query->orderBy($order, $sort);

        $perPage = (int) $request->input('per_page', 12);
        $perPage = max(1, min(50, $perPage));

        return $this->apiSuccess('Listado público de agentes MLS', 'PUBLIC_MLS_AGENTS_LIST', $query->paginate($perPage));
    }

    /**
     * GET /api/public/mls-agents/{mlsAgentId}
     * Detalle público de un agente MLS por su mls_agent_id (ID del MLS).
     */
    public function showPublicByMlsId(int $mlsAgentId): JsonResponse
    {
        $agent = MLSAgent::query()
            ->where('mls_agent_id', $mlsAgentId)
            ->with(['photoMediaAsset', 'office.imageMediaAsset'])
            ->withCount('properties')
            ->first();

        if (!$agent) {
            return $this->apiNotFound('Agente no encontrado', 'MLS_AGENT_NOT_FOUND');
        }

        // Conteos de la agencia (si existe) para mostrar badge rápido en el frontend.
        $officeSummary = null;
        if (!empty($agent->mls_office_id)) {
            $office = MLSOffice::query()
                ->where('mls_office_id', (int) $agent->mls_office_id)
                ->withCount(['agents', 'properties'])
                ->with(['imageMediaAsset'])
                ->first();
            if ($office) {
                $officeSummary = $office;
            }
        }

        return $this->apiSuccess('Agente MLS obtenido', 'PUBLIC_MLS_AGENT_SHOWN', [
            'agent' => $agent,
            'office' => $officeSummary,
        ]);
    }

    /**
     * GET /api/mls-agents/{mlsAgent}
     * Muestra el detalle de un agente MLS con sus propiedades.
     */
    public function show(MLSAgent $mlsAgent): JsonResponse
    {
        $mlsAgent->load(['photoMediaAsset', 'user', 'properties']);

        return $this->apiSuccess('Agente MLS obtenido', 'MLS_AGENT_SHOWN', $mlsAgent);
    }

    /**
     * POST /api/mls-agents
     * Crea un agente MLS manualmente.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'mls_agent_id' => 'required|integer|unique:mls_agents,mls_agent_id',
            'name' => 'nullable|string|max:255',
            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'mls_office_id' => 'nullable|integer',
            'office_name' => 'nullable|string|max:255',
            'photo_url' => 'nullable|string',
            'photo_media_asset_id' => 'nullable|exists:media_assets,id',
            'license_number' => 'nullable|string|max:100',
            'bio' => 'nullable|string',
            'website' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'user_id' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        $agent = MLSAgent::create($validator->validated());
        $agent->load(['photoMediaAsset', 'user']);

        return $this->apiCreated('Agente MLS creado', 'MLS_AGENT_CREATED', $agent);
    }

    /**
     * PATCH /api/mls-agents/{mlsAgent}
     * Actualiza un agente MLS.
     */
    public function update(Request $request, MLSAgent $mlsAgent): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'mls_agent_id' => 'sometimes|integer|unique:mls_agents,mls_agent_id,' . $mlsAgent->id,
            'name' => 'sometimes|nullable|string|max:255',
            'first_name' => 'sometimes|nullable|string|max:100',
            'last_name' => 'sometimes|nullable|string|max:100',
            'email' => 'sometimes|nullable|email|max:255',
            'phone' => 'sometimes|nullable|string|max:50',
            'mobile' => 'sometimes|nullable|string|max:50',
            'mls_office_id' => 'sometimes|nullable|integer',
            'office_name' => 'sometimes|nullable|string|max:255',
            'photo_url' => 'sometimes|nullable|string',
            'photo_media_asset_id' => 'sometimes|nullable|exists:media_assets,id',
            'license_number' => 'sometimes|nullable|string|max:100',
            'bio' => 'sometimes|nullable|string',
            'website' => 'sometimes|nullable|string|max:255',
            'is_active' => 'sometimes|nullable|boolean',
            'user_id' => 'sometimes|nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        $mlsAgent->update($validator->validated());
        $mlsAgent->load(['photoMediaAsset', 'user']);

        return $this->apiSuccess('Agente MLS actualizado', 'MLS_AGENT_UPDATED', $mlsAgent);
    }

    /**
     * DELETE /api/mls-agents/{mlsAgent}
     * Elimina un agente MLS.
     */
    public function destroy(MLSAgent $mlsAgent): JsonResponse
    {
        $mlsAgent->properties()->detach();
        $mlsAgent->delete();

        return $this->apiSuccess('Agente MLS eliminado', 'MLS_AGENT_DELETED', null);
    }

    /**
     * POST /api/mls-agents/sync
     * Sincroniza agentes desde el MLS API en modo progresivo (por lotes).
     * Soporta parámetros batch_size y offset para procesar en múltiples llamadas.
     */
    public function syncAgents(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'batch_size' => 'sometimes|integer|min:5|max:100',
            'offset' => 'sometimes|integer|min:0',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        $this->syncService->reloadConfiguration();

        if (!$this->syncService->isConfigured()) {
            return $this->apiError(
                'MLS no está configurado',
                'MLS_NOT_CONFIGURED',
                null,
                null,
                400
            );
        }

        $options = $validator->validated();
        $batchSize = (int) ($options['batch_size'] ?? 20);
        $offset = isset($options['offset']) ? (int) $options['offset'] : null;

        $result = $this->syncService->syncAgentsProgressive($batchSize, $offset);

        if ($result['success']) {
            return $this->apiSuccess(
                $result['completed'] ? 'Sincronización de agentes completada' : 'Lote de agentes procesado',
                'MLS_AGENTS_SYNC_COMPLETED',
                [
                    'total_in_mls' => $result['total_in_mls'] ?? null,
                    'processed' => $result['processed'] ?? 0,
                    'created' => $result['created'] ?? 0,
                    'updated' => $result['updated'] ?? 0,
                    'errors' => $result['errors'] ?? 0,
                    'next_offset' => $result['next_offset'] ?? 0,
                    'completed' => $result['completed'] ?? false,
                    'progress_percentage' => $result['progress_percentage'] ?? 0,
                    'hint' => $result['completed']
                        ? 'La sincronización está completa.'
                        : "Para continuar, llama nuevamente con offset={$result['next_offset']}. Progreso: {$result['progress_percentage']}%",
                ]
            );
        }

        return $this->apiError(
            $result['message'] ?? 'Error en la sincronización de agentes',
            'MLS_AGENTS_SYNC_ERROR',
            $result,
            null,
            500
        );
    }

    /**
     * POST /api/mls-agents/sync-property-agents
     * Re-sincroniza las relaciones agente-propiedad para todas las propiedades MLS existentes.
     * Útil cuando las propiedades se sincronizaron antes de que existiera la tabla de agentes.
     */
    public function syncPropertyAgentsRelations(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'batch_size' => 'sometimes|integer|min:5|max:50',
            'offset' => 'sometimes|integer|min:0',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        $this->syncService->reloadConfiguration();

        if (!$this->syncService->isConfigured()) {
            return $this->apiError('MLS no está configurado', 'MLS_NOT_CONFIGURED', null, null, 400);
        }

        $batchSize = (int) ($validator->validated()['batch_size'] ?? 10);
        $offset = (int) ($validator->validated()['offset'] ?? 0);

        // Obtener propiedades MLS que tienen mls_id (ID interno del MLS)
        $totalProperties = Property::where('source', 'mls')
            ->whereNotNull('mls_id')
            ->count();

        $properties = Property::where('source', 'mls')
            ->whereNotNull('mls_id')
            ->orderBy('id')
            ->offset($offset)
            ->limit($batchSize)
            ->get();

        if ($properties->isEmpty()) {
            return $this->apiSuccess('No hay más propiedades que procesar', 'MLS_AGENT_RELATIONS_COMPLETE', [
                'total_properties' => $totalProperties,
                'processed' => 0,
                'next_offset' => 0,
                'completed' => true,
                'progress_percentage' => 100,
            ]);
        }

        $processed = 0;
        $linked = 0;
        $errors = 0;

        foreach ($properties as $property) {
            try {
                // Obtener agentes de esta propiedad del API
                // mls_id corresponde al campo 'id' interno de la propiedad en el MLS
                $propertyMlsId = $property->mls_id;
                
                if (empty($propertyMlsId)) {
                    \Illuminate\Support\Facades\Log::debug("[AGENT-RELATIONS] Property #{$property->id} sin mls_id, omitiendo");
                    $processed++;
                    continue;
                }

                \Illuminate\Support\Facades\Log::debug("[AGENT-RELATIONS] Obteniendo agentes para property #{$property->id} (mls_id: {$propertyMlsId})");
                
                $agentResponse = $this->syncService->fetchPropertyAgentIds((int) $propertyMlsId);

                // Normalizar respuesta del API: puede venir como
                // { id: 123, agents: [27,45] } ó agents: [{id:27}, {id:45}] ó envuelto en data.
                $agentIdsRaw = null;
                if (is_array($agentResponse)) {
                    if (array_key_exists('agents', $agentResponse)) {
                        $agentIdsRaw = $agentResponse['agents'];
                    } elseif (isset($agentResponse['data']) && is_array($agentResponse['data']) && array_key_exists('agents', $agentResponse['data'])) {
                        $agentIdsRaw = $agentResponse['data']['agents'];
                    }
                }

                if (!empty($agentIdsRaw) && is_array($agentIdsRaw)) {
                    // Convertir a lista de IDs enteros
                    $agentIds = [];
                    foreach ($agentIdsRaw as $a) {
                        if (is_numeric($a)) {
                            $agentIds[] = (int) $a;
                            continue;
                        }

                        if (is_array($a)) {
                            $candidate = $a['id'] ?? $a['agent_id'] ?? $a['mls_agent_id'] ?? null;
                            if (is_numeric($candidate)) {
                                $agentIds[] = (int) $candidate;
                                continue;
                            }
                        }

                        // Evitar romper el lote por datos inesperados
                        \Illuminate\Support\Facades\Log::warning(
                            "[AGENT-RELATIONS] Item de agente inesperado en property #{$property->id} (mls_id: {$propertyMlsId})",
                            ['item' => $a]
                        );
                    }

                    $localAgentIds = [];

                    // Usar json_encode para evitar 'Array to string conversion'
                    \Illuminate\Support\Facades\Log::debug(
                        "[AGENT-RELATIONS] Property #{$property->id}: API devolvió " . count($agentIds) . " agentes: " . json_encode($agentIds)
                    );

                    foreach ($agentIds as $index => $mlsAgentId) {
                        // Ya normalizamos a int, pero validar por seguridad
                        if (!is_numeric($mlsAgentId)) {
                            continue;
                        }

                        $mlsAgentIdInt = (int) $mlsAgentId;
                        $agent = MLSAgent::where('mls_agent_id', $mlsAgentIdInt)->first();
                        
                        // Si el agente no existe localmente, crear un placeholder
                        // (similar al comportamiento de syncPropertyMlsAgents en MLSSyncService)
                        if (!$agent) {
                            $agent = MLSAgent::create([
                                'mls_agent_id' => $mlsAgentIdInt,
                                'name' => "Agente MLS #{$mlsAgentIdInt}",
                                'mls_office_id' => $property->mls_office_id ?? null,
                                'is_active' => true,
                            ]);
                            \Illuminate\Support\Facades\Log::info("[AGENT-RELATIONS] Agente placeholder creado: MLS ID #{$mlsAgentIdInt}");
                        }
                        
                        $localAgentIds[$agent->id] = ['is_primary' => $index === 0];
                    }

                    if (!empty($localAgentIds)) {
                        $property->mlsAgents()->sync($localAgentIds);
                        $linked += count($localAgentIds);
                        \Illuminate\Support\Facades\Log::info("[AGENT-RELATIONS] Property #{$property->id}: vinculados " . count($localAgentIds) . " agentes");
                    }
                } else {
                    // Log diagnóstico adicional cuando la respuesta es nula o no trae campo agents
                    $keys = is_array($agentResponse) ? implode(', ', array_keys($agentResponse)) : (is_null($agentResponse) ? 'NULL' : gettype($agentResponse));
                    \Illuminate\Support\Facades\Log::debug(
                        "[AGENT-RELATIONS] Property #{$property->id} (mls_id: {$propertyMlsId}): API devolvió 0 agentes o respuesta nula. Response keys/type: {$keys}"
                    );
                }
                
                $processed++;
            } catch (\Throwable $e) {
                $errors++;
                \Illuminate\Support\Facades\Log::error("[AGENT-RELATIONS] Error en property #{$property->id}: " . $e->getMessage());
            }

            // Rate limiting
            usleep(100000); // 100ms entre propiedades
        }

        $newOffset = $offset + $processed;
        $completed = $newOffset >= $totalProperties;
        $progressPercentage = $totalProperties > 0 ? round(($newOffset / $totalProperties) * 100, 2) : 100;

        return $this->apiSuccess(
            $completed ? 'Relaciones agente-propiedad sincronizadas' : 'Lote procesado',
            'MLS_AGENT_RELATIONS_SYNCED',
            [
                'total_properties' => $totalProperties,
                'processed' => $processed,
                'linked' => $linked,
                'errors' => $errors,
                'next_offset' => $completed ? 0 : $newOffset,
                'completed' => $completed,
                'progress_percentage' => $progressPercentage,
            ]
        );
    }

    /**
     * POST /api/mls-agents/{mlsAgent}/properties
     * Asocia propiedades a un agente MLS.
     */
    public function attachProperties(Request $request, MLSAgent $mlsAgent): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'property_ids' => 'required|array|min:1',
            'property_ids.*' => 'integer|exists:properties,id',
            'is_primary' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        $data = $validator->validated();
        $isPrimary = $data['is_primary'] ?? false;

        $syncData = [];
        foreach ($data['property_ids'] as $propertyId) {
            $syncData[$propertyId] = ['is_primary' => $isPrimary];
        }

        $mlsAgent->properties()->syncWithoutDetaching($syncData);

        $mlsAgent->load('properties');

        return $this->apiSuccess(
            'Propiedades asociadas al agente',
            'MLS_AGENT_PROPERTIES_ATTACHED',
            $mlsAgent
        );
    }

    /**
     * DELETE /api/mls-agents/{mlsAgent}/properties
     * Desasocia propiedades de un agente MLS.
     */
    public function detachProperties(Request $request, MLSAgent $mlsAgent): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'property_ids' => 'required|array|min:1',
            'property_ids.*' => 'integer|exists:properties,id',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        $mlsAgent->properties()->detach($validator->validated()['property_ids']);

        $mlsAgent->load('properties');

        return $this->apiSuccess(
            'Propiedades desasociadas del agente',
            'MLS_AGENT_PROPERTIES_DETACHED',
            $mlsAgent
        );
    }

    /**
     * GET /api/properties/{property}/mls-agents
     * Obtiene los agentes MLS de una propiedad.
     */
    public function propertyAgents(Property $property): JsonResponse
    {
        $property->load('mlsAgents.photoMediaAsset');

        return $this->apiSuccess(
            'Agentes MLS de la propiedad',
            'PROPERTY_MLS_AGENTS',
            $property->mlsAgents
        );
    }

    /**
     * POST /api/properties/{property}/mls-agents
     * Sincroniza los agentes MLS de una propiedad específica.
     */
    public function syncPropertyAgents(Request $request, Property $property): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'mls_agent_ids' => 'required|array|min:1',
            'mls_agent_ids.*' => 'integer|exists:mls_agents,id',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        $agentIds = $validator->validated()['mls_agent_ids'];
        
        // El primer agente es el principal
        $syncData = [];
        foreach ($agentIds as $index => $agentId) {
            $syncData[$agentId] = ['is_primary' => $index === 0];
        }

        $property->mlsAgents()->sync($syncData);
        $property->load('mlsAgents.photoMediaAsset');

        return $this->apiSuccess(
            'Agentes MLS de la propiedad actualizados',
            'PROPERTY_MLS_AGENTS_SYNCED',
            $property->mlsAgents
        );
    }
}
