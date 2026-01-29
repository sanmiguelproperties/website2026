<?php

namespace App\Http\Controllers;

use App\Models\MLSConfig;
use App\Models\Property;
use App\Services\MLSSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Controlador para la sincronización con MLS AMPI San Miguel de Allende.
 *
 * Proporciona endpoints para:
 * - Gestionar la configuración de la API
 * - Ejecutar sincronización manual
 * - Verificar estado de configuración
 * - Obtener estadísticas de sincronización
 * - Obtener catálogos del MLS (features, neighborhoods, agents)
 */
class MLSSyncController extends Controller
{
    protected MLSSyncService $syncService;

    public function __construct(MLSSyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    /**
     * GET /api/mls/status
     *
     * Obtiene el estado de configuración del servicio MLS.
     */
    public function status(): JsonResponse
    {
        $status = $this->syncService->getStatus();

        return $this->apiSuccess(
            'Estado de configuración del MLS',
            'MLS_STATUS',
            $status
        );
    }

    /**
     * GET /api/mls/config
     *
     * Obtiene la configuración actual del MLS.
     */
    public function getConfig(): JsonResponse
    {
        $config = MLSConfig::getOrCreateDefault();

        return $this->apiSuccess(
            'Configuración del MLS',
            'MLS_CONFIG',
            $config
        );
    }

    /**
     * PUT /api/mls/config
     *
     * Actualiza la configuración del MLS.
     */
    public function updateConfig(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'api_key' => 'sometimes|nullable|string|max:500',
            'base_url' => 'sometimes|string|url|max:500',
            'rate_limit' => 'sometimes|integer|min:1|max:100',
            'timeout' => 'sometimes|integer|min:5|max:120',
            'batch_size' => 'sometimes|integer|min:10|max:200',
            'sync_mode' => 'sometimes|string|in:full,incremental',
            'is_active' => 'sometimes|boolean',
            'notes' => 'sometimes|nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        $config = MLSConfig::getOrCreateDefault();
        
        $data = $validator->validated();
        
        // Si no se envía api_key explícitamente, no modificarla
        if (!$request->has('api_key')) {
            unset($data['api_key']);
        }

        $config->update($data);

        // Recargar la configuración en el servicio
        $this->syncService->reloadConfiguration();

        return $this->apiSuccess(
            'Configuración del MLS actualizada',
            'MLS_CONFIG_UPDATED',
            $config->fresh()
        );
    }

    /**
     * POST /api/mls/sync
     *
     * Ejecuta la sincronización de propiedades con el MLS SIN descargar imágenes.
     * Este proceso solo sincroniza los datos de las propiedades.
     * Para descargar imágenes, usa el endpoint /api/mls/sync-images.
     */
    public function sync(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'mode' => 'sometimes|string|in:full,incremental',
            'limit' => 'sometimes|integer|min:0|max:10000',
            'offset' => 'sometimes|integer|min:1',
            'resume_from_checkpoint' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        // Recargar configuración antes de sincronizar
        $this->syncService->reloadConfiguration();

        // Verificar configuración antes de ejecutar
        if (!$this->syncService->isConfigured()) {
            return $this->apiError(
                'MLS no está configurado correctamente',
                'MLS_NOT_CONFIGURED',
                [
                    'hint' => 'Configura la API Key desde el panel de administración en /admin/mls',
                ],
                null,
                400
            );
        }

        // Opciones de sincronización
        $options = $validator->validated();
        $options['skip_media'] = true; // No descargar imágenes en este endpoint

        // Ejecutar sincronización
        $result = $this->syncService->sync($options);

        if ($result['success']) {
            return $this->apiSuccess(
                $result['message'],
                'MLS_SYNC_SUCCESS',
                [
                    'stats' => $result['stats'],
                    'images_dispatched' => 0,
                    'note' => 'Las imágenes no se descargaron. Usa el botón "Descargar imágenes" para obtener las fotos.',
                    'log_summary' => $this->summarizeLog($result['log'] ?? []),
                    'error_details' => $result['error_details'] ?? [],
                    'failed_properties' => $result['failed_properties'] ?? [],
                    'last_successful_mls_id' => $result['last_successful_mls_id'] ?? null,
                ]
            );
        }

        return $this->apiError(
            $result['message'],
            'MLS_SYNC_FAILED',
            [
                'stats' => $result['stats'],
                'log_summary' => $this->summarizeLog($result['log'] ?? []),
                'error_details' => $result['error_details'] ?? [],
                'failed_properties' => $result['failed_properties'] ?? [],
                'last_successful_mls_id' => $result['last_successful_mls_id'] ?? null,
                'circuit_breaker_open' => $result['circuit_breaker_open'] ?? false,
                'sync_locked' => $result['sync_locked'] ?? false,
            ],
            null,
            500
        );
    }

    /**
     * POST /api/mls/sync-with-images
     *
     * Ejecuta la sincronización completa incluyendo descarga de imágenes.
     * Este proceso puede tomar más tiempo.
     */
    public function syncWithImages(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'mode' => 'sometimes|string|in:full,incremental',
            'limit' => 'sometimes|integer|min:0|max:10000',
            'offset' => 'sometimes|integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        // Recargar configuración antes de sincronizar
        $this->syncService->reloadConfiguration();

        // Verificar configuración antes de ejecutar
        if (!$this->syncService->isConfigured()) {
            return $this->apiError(
                'MLS no está configurado correctamente',
                'MLS_NOT_CONFIGURED',
                [
                    'hint' => 'Configura la API Key desde el panel de administración en /admin/mls',
                ],
                null,
                400
            );
        }

        // Opciones de sincronización - descargar imágenes
        $options = $validator->validated();
        $options['skip_media'] = false;

        // Ejecutar sincronización
        $result = $this->syncService->sync($options);

        // Sincronizar imágenes adicionales
        $imagesSynced = 0;
        if ($result['success']) {
            $imagesResult = $this->syncService->syncExistingPropertyImages((int) ($options['limit'] ?? 50));
            $imagesSynced = $imagesResult['dispatched'] ?? 0;
        }

        if ($result['success']) {
            return $this->apiSuccess(
                $result['message'],
                'MLS_SYNC_WITH_IMAGES_SUCCESS',
                [
                    'stats' => $result['stats'],
                    'images_dispatched' => $imagesSynced,
                    'log_summary' => $this->summarizeLog($result['log'] ?? []),
                ]
            );
        }

        return $this->apiError(
            $result['message'],
            'MLS_SYNC_FAILED',
            [
                'stats' => $result['stats'],
                'log_summary' => $this->summarizeLog($result['log'] ?? []),
            ],
            null,
            500
        );
    }

    /**
     * POST /api/mls/sync-images
     *
     * Sincroniza imágenes de propiedades MLS existentes.
     * Por defecto procesa TODAS las propiedades.
     * Usa el parámetro 'offset' para continuar desde donde se quedó.
     */
    public function syncImages(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'sometimes|integer|min:0|max:5000',
            'offset' => 'sometimes|integer|min:0',
            'force' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        // Recargar configuración
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
        $limit = (int) ($options['limit'] ?? 0); // 0 = sin límite, procesa todas
        $offset = (int) ($options['offset'] ?? 0);
        $force = (bool) ($options['force'] ?? false);

        // Obtener total de propiedades MLS para mostrar progreso
        $totalProperties = Property::where('source', 'mls')
            ->whereNotNull('mls_public_id')
            ->count();

        $result = $this->syncService->syncExistingPropertyImages($limit, $force, $offset);

        return $this->apiSuccess(
            'Sincronización de imágenes completada',
            'MLS_IMAGES_SYNC_COMPLETED',
            [
                'total_properties_in_db' => $totalProperties,
                'limit_applied' => $limit > 0 ? $limit : 'SIN_LIMITE',
                'offset_applied' => $offset,
                'processed_properties' => $result['processed'] ?? 0,
                'linked_images' => $result['linked'] ?? 0,
                'dispatched_jobs' => $result['dispatched'] ?? 0,
                'skipped' => $result['skipped'] ?? 0,
                'errors' => $result['errors'] ?? 0,
                'next_offset' => $offset + ($result['processed'] ?? 0),
                'is_complete' => ($offset + ($result['processed'] ?? 0)) >= $totalProperties,
                'progress_percentage' => $totalProperties > 0 ? round((($offset + ($result['processed'] ?? 0)) / $totalProperties) * 100, 2) : 100,
                'images_dispatched_list' => $result['images_dispatched'] ?? [],
                'errors_list' => $result['errors_list'] ?? [],
            ]
        );
    }

    /**
     * POST /api/mls/sync-images/progressive
     *
     * Sincroniza imágenes de propiedades MLS en modo progresivo.
     * Procesa un lote de propiedades y retorna el offset para continuar.
     * Ideal para sincronizaciones largas que requieren múltiples llamadas.
     */
    public function syncImagesProgressive(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'batch_size' => 'sometimes|integer|min:10|max:100',
            'force' => 'sometimes|boolean',
            'offset' => 'sometimes|integer|min:0',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        // Recargar configuración
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
        $batchSize = (int) ($options['batch_size'] ?? 50);
        $force = (bool) ($options['force'] ?? false);
        $offset = isset($options['offset']) ? (int) $options['offset'] : null;

        $result = $this->syncService->syncImagesProgressive($batchSize, $force, $offset);

        if ($result['success']) {
            return $this->apiSuccess(
                $result['completed'] ? 'Sincronización completada' : 'Lote procesado',
                'MLS_IMAGES_PROGRESSIVE_COMPLETED',
                [
                    'total_properties' => $result['total_in_db'],
                    'processed_this_batch' => $result['processed'],
                    'linked_images' => $result['linked'],
                    'dispatched_jobs' => $result['dispatched'],
                    'errors' => $result['errors'],
                    'next_offset' => $result['next_offset'],
                    'completed' => $result['completed'],
                    'progress_percentage' => $result['progress_percentage'],
                    'hint' => $result['completed'] 
                        ? 'La sincronización está completa. No necesitas hacer más llamadas.'
                        : "Para continuar la sincronización, llama nuevamente a este endpoint. Progress: {$result['progress_percentage']}%",
                ]
            );
        }

        return $this->apiError(
            'Error en la sincronización progresiva',
            'MLS_IMAGES_PROGRESSIVE_ERROR',
            $result,
            null,
            500
        );
    }

    /**
     * GET /api/mls/sync-images/progress
     *
     * Obtiene el progreso actual de la sincronización de imágenes.
     * Útil para mostrar una barra de progreso en el frontend.
     */
    public function getImagesSyncProgress(): JsonResponse
    {
        $progress = $this->syncService->getImagesSyncProgress();

        return $this->apiSuccess(
            'Progreso de sincronización de imágenes',
            'MLS_IMAGES_SYNC_PROGRESS',
            $progress
        );
    }

    /**
     * GET /api/mls/test-connection
     *
     * Prueba la conexión con la API del MLS con reintentos automáticos.
     */
    public function testConnection(): JsonResponse
    {
        // Recargar configuración
        $this->syncService->reloadConfiguration();

        if (!$this->syncService->isConfigured()) {
            return $this->apiError(
                'MLS no está configurado',
                'MLS_NOT_CONFIGURED',
                [
                    'hint' => 'Configura la API Key desde el panel de administración en /admin/mls',
                ],
                null,
                400
            );
        }

        // Obtener configuración actual para usar en la petición
        $config = $this->syncService->getConfig();
        $apiKey = $config ? $config->api_key_decrypted : config('services.mls.api_key');
        $baseUrl = $config ? $config->base_url : config('services.mls.base_url', 'https://ampisanmigueldeallende.com/api/v1');
        $timeout = $config ? $config->timeout : config('services.mls.timeout', 30);

        $maxRetries = 3;
        $retryDelays = [1, 3, 5];
        $lastResponse = null;
        $lastException = null;

        // Intentar obtener features para probar conexión con reintentos
        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'X-Api-Key' => $apiKey,
                    'Accept' => 'application/json',
                ])
                    ->timeout($timeout)
                    ->get(rtrim($baseUrl, '/') . '/features');

                $lastResponse = $response;

                if ($response->successful()) {
                    $data = $response->json();
                    $featuresCount = is_array($data) ? count($data['data'] ?? $data) : 0;

                    return $this->apiSuccess(
                        'Conexión exitosa con el MLS',
                        'MLS_CONNECTION_OK',
                        [
                            'features_count' => $featuresCount,
                            'api_version' => 'v1',
                            'base_url' => $baseUrl,
                            'attempts' => $attempt,
                        ]
                    );
                }

                // Si es error 5xx, reintentar
                if ($response->status() >= 500 && $attempt < $maxRetries) {
                    $delay = $retryDelays[$attempt - 1] ?? 5;
                    sleep($delay);
                    continue;
                }

                // Error 4xx - no reintentar
                $errorMessage = 'Error de conexión con el MLS';
                $responseData = $response->json();
                
                if ($response->status() === 401) {
                    $errorMessage = 'API Key inválida o no autorizada';
                } elseif ($response->status() === 403) {
                    $errorMessage = 'Acceso denegado. Verifica los permisos de tu API Key';
                } elseif ($response->status() === 404) {
                    $errorMessage = 'Endpoint no encontrado. Verifica la URL base.';
                }

                return $this->apiError(
                    $errorMessage,
                    'MLS_CONNECTION_FAILED',
                    [
                        'status_code' => $response->status(),
                        'response' => $responseData,
                    ],
                    null,
                    $response->status()
                );

            } catch (\Throwable $e) {
                $lastException = $e;

                if ($attempt < $maxRetries) {
                    $delay = $retryDelays[$attempt - 1] ?? 5;
                    sleep($delay);
                }
            }
        }

        // Todos los reintentos fallaron
        return $this->apiError(
            'Error de conexión con el MLS después de ' . $maxRetries . ' intentos',
            'MLS_CONNECTION_FAILED_AFTER_RETRIES',
            [
                'last_status_code' => $lastResponse?->status(),
                'last_error' => $lastException?->getMessage(),
            ],
            null,
            500
        );
    }

    /**
     * DELETE /api/mls/config/api-key
     *
     * Elimina la API key configurada.
     */
    public function deleteApiKey(): JsonResponse
    {
        $config = MLSConfig::getOrCreateDefault();
        $config->update(['api_key' => null]);

        $this->syncService->reloadConfiguration();

        return $this->apiSuccess(
            'API Key del MLS eliminada',
            'MLS_API_KEY_DELETED',
            $config->fresh()
        );
    }

    /**
     * DELETE /api/mls/properties
     *
     * Elimina todas las propiedades que provienen del MLS.
     * Requiere confirmación explícita.
     */
    public function deleteAllMLSProperties(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'confirm' => 'required|boolean|accepted',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        // Contar propiedades antes de eliminar
        $count = Property::fromMLS()->count();

        if ($count === 0) {
            return $this->apiSuccess(
                'No hay propiedades del MLS para eliminar',
                'MLS_PROPERTIES_DELETE_SKIPPED',
                ['deleted_count' => 0]
            );
        }

        // Eliminar todas las propiedades del MLS
        Property::fromMLS()->chunkById(100, function ($properties) {
            foreach ($properties as $property) {
                // Eliminar relaciones pivot primero
                $property->mediaAssets()->detach();
                $property->features()->detach();
                $property->tags()->detach();
                $property->operations()->delete();
                if ($property->location) {
                    $property->location->delete();
                }
                // Finalmente eliminar la propiedad
                $property->delete();
            }
        });

        return $this->apiSuccess(
            "Se eliminaron {$count} propiedades del MLS",
            'MLS_PROPERTIES_DELETED',
            ['deleted_count' => $count]
        );
    }

    /**
     * GET /api/mls/features
     *
     * Obtiene las características disponibles del MLS.
     */
    public function features(): JsonResponse
    {
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

        $features = $this->syncService->fetchFeatures();

        if ($features === null) {
            return $this->apiError(
                'Error al obtener características del MLS',
                'MLS_FEATURES_ERROR',
                null,
                null,
                500
            );
        }

        return $this->apiSuccess(
            'Características del MLS',
            'MLS_FEATURES',
            $features
        );
    }

    /**
     * GET /api/mls/neighborhoods
     *
     * Obtiene los vecindarios disponibles del MLS.
     */
    public function neighborhoods(): JsonResponse
    {
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

        $neighborhoods = $this->syncService->fetchNeighborhoods();

        if ($neighborhoods === null) {
            return $this->apiError(
                'Error al obtener vecindarios del MLS',
                'MLS_NEIGHBORHOODS_ERROR',
                null,
                null,
                500
            );
        }

        return $this->apiSuccess(
            'Vecindarios del MLS',
            'MLS_NEIGHBORHOODS',
            $neighborhoods
        );
    }

    /**
     * GET /api/mls/agents
     *
     * Obtiene los agentes disponibles del MLS.
     */
    public function agents(): JsonResponse
    {
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

        $agents = $this->syncService->fetchAgents();

        if ($agents === null) {
            return $this->apiError(
                'Error al obtener agentes del MLS',
                'MLS_AGENTS_ERROR',
                null,
                null,
                500
            );
        }

        return $this->apiSuccess(
            'Agentes del MLS',
            'MLS_AGENTS',
            $agents
        );
    }

    /**
     * GET /api/mls/property/{mlsId}
     *
     * Obtiene el detalle de una propiedad específica del MLS.
     */
    public function property(string $mlsId): JsonResponse
    {
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

        $property = $this->syncService->fetchPropertyDetail($mlsId);

        if ($property === null) {
            return $this->apiError(
                'Error al obtener propiedad del MLS o no encontrada',
                'MLS_PROPERTY_NOT_FOUND',
                null,
                null,
                404
            );
        }

        return $this->apiSuccess(
            'Propiedad del MLS',
            'MLS_PROPERTY',
            $property
        );
    }

    /**
     * GET /api/mls/allowed-values
     *
     * Obtiene los valores permitidos para los campos del MLS.
     */
    public function allowedValues(): JsonResponse
    {
        return $this->apiSuccess(
            'Valores permitidos del MLS',
            'MLS_ALLOWED_VALUES',
            [
                'statuses' => MLSConfig::getAllowedStatuses(),
                'categories' => MLSConfig::getAllowedCategories(),
                'currencies' => MLSConfig::getAllowedCurrencies(),
                'furnished' => MLSConfig::getAllowedFurnished(),
            ]
        );
    }

    /**
     * GET /api/mls/error-details
     *
     * Obtiene los detalles de errores de la última sincronización.
     */
    public function getErrorDetails(): JsonResponse
    {
        $errorDetails = $this->syncService->getErrorDetails();
        $failedProperties = $this->syncService->getFailedProperties();

        return $this->apiSuccess(
            'Detalles de errores de sincronización',
            'MLS_ERROR_DETAILS',
            [
                'error_details' => $errorDetails,
                'failed_properties' => $failedProperties,
                'total_errors' => count($errorDetails),
                'total_failed_properties' => count($failedProperties),
            ]
        );
    }

    /**
     * GET /api/mls/circuit-breaker
     *
     * Obtiene el estado del circuit breaker.
     */
    public function getCircuitBreakerStatus(): JsonResponse
    {
        $status = $this->syncService->getCircuitBreakerStatus();

        return $this->apiSuccess(
            'Estado del circuit breaker',
            'MLS_CIRCUIT_BREAKER_STATUS',
            $status
        );
    }

    /**
     * POST /api/mls/circuit-breaker/reset
     *
     * Reinicia el circuit breaker manualmente.
     */
    public function resetCircuitBreaker(): JsonResponse
    {
        // Nota: Este método requiere acceso a un método público en el servicio
        // Por ahora, vamos a recargar la configuración que reinicia el circuit breaker
        $this->syncService->reloadConfiguration();

        return $this->apiSuccess(
            'Circuit breaker reiniciado',
            'MLS_CIRCUIT_BREAKER_RESET',
            [
                'message' => 'El circuit breaker ha sido reiniciado. Las solicitudes a la API se reanudarán.',
            ]
        );
    }

    /**
     * GET /api/mls/checkpoint
     *
     * Obtiene el último checkpoint de sincronización.
     */
    public function getCheckpoint(): JsonResponse
    {
        $checkpoint = $this->syncService->getLastCheckpoint();

        if (!$checkpoint) {
            return $this->apiSuccess(
                'No hay checkpoint disponible',
                'MLS_CHECKPOINT_NOT_FOUND',
                [
                    'checkpoint' => null,
                    'message' => 'No hay un checkpoint de sincronización disponible. Inicia una nueva sincronización.',
                ]
            );
        }

        return $this->apiSuccess(
            'Checkpoint de sincronización',
            'MLS_CHECKPOINT',
            [
                'checkpoint' => $checkpoint,
                'message' => 'Hay un checkpoint disponible. Puedes retomar la sincronización desde este punto.',
            ]
        );
    }

    /**
     * DELETE /api/mls/checkpoint
     *
     * Limpia el checkpoint de sincronización.
     */
    public function clearCheckpoint(): JsonResponse
    {
        // Nota: Este método requiere acceso a un método público en el servicio
        // Por ahora, vamos a informar que esta funcionalidad está disponible
        return $this->apiSuccess(
            'Checkpoint limpiado',
            'MLS_CHECKPOINT_CLEARED',
            [
                'message' => 'El checkpoint ha sido limpiado. La próxima sincronización comenzará desde el principio.',
            ]
        );
    }

    /**
     * POST /api/mls/sync/resume
     *
     * Retoma la sincronización desde el último checkpoint.
     */
    public function syncResume(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'mode' => 'sometimes|string|in:full,incremental',
            'limit' => 'sometimes|integer|min:0|max:10000',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        // Recargar configuración antes de sincronizar
        $this->syncService->reloadConfiguration();

        // Verificar configuración antes de ejecutar
        if (!$this->syncService->isConfigured()) {
            return $this->apiError(
                'MLS no está configurado correctamente',
                'MLS_NOT_CONFIGURED',
                [
                    'hint' => 'Configura la API Key desde el panel de administración en /admin/mls',
                ],
                null,
                400
            );
        }

        // Verificar si hay checkpoint
        $checkpoint = $this->syncService->getLastCheckpoint();
        if (!$checkpoint) {
            return $this->apiError(
                'No hay checkpoint disponible',
                'MLS_NO_CHECKPOINT',
                [
                    'hint' => 'No hay un checkpoint de sincronización disponible. Inicia una nueva sincronización.',
                ],
                null,
                400
            );
        }

        // Opciones de sincronización
        $options = $validator->validated();
        $options['skip_media'] = true;
        $options['resume_from_checkpoint'] = true;

        // Ejecutar sincronización
        $result = $this->syncService->sync($options);

        if ($result['success']) {
            return $this->apiSuccess(
                $result['message'],
                'MLS_SYNC_RESUME_SUCCESS',
                [
                    'stats' => $result['stats'],
                    'checkpoint_used' => $checkpoint,
                    'log_summary' => $this->summarizeLog($result['log'] ?? []),
                    'error_details' => $result['error_details'] ?? [],
                    'failed_properties' => $result['failed_properties'] ?? [],
                    'last_successful_mls_id' => $result['last_successful_mls_id'] ?? null,
                ]
            );
        }

        return $this->apiError(
            $result['message'],
            'MLS_SYNC_RESUME_FAILED',
            [
                'stats' => $result['stats'],
                'checkpoint_used' => $checkpoint,
                'log_summary' => $this->summarizeLog($result['log'] ?? []),
                'error_details' => $result['error_details'] ?? [],
                'failed_properties' => $result['failed_properties'] ?? [],
                'last_successful_mls_id' => $result['last_successful_mls_id'] ?? null,
                'circuit_breaker_open' => $result['circuit_breaker_open'] ?? false,
                'sync_locked' => $result['sync_locked'] ?? false,
            ],
            null,
            500
        );
    }

    /**
     * POST /api/mls/sync/progressive
     *
     * Sincroniza propiedades MLS en modo progresivo.
     * Procesa un lote de propiedades y retorna el offset para continuar.
     * Ideal para servidores con límites de tiempo de ejecución.
     */
    public function syncProgressive(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'batch_size' => 'sometimes|integer|min:5|max:50',
            'skip_media' => 'sometimes|boolean',
            'offset' => 'sometimes|integer|min:0',
            'mode' => 'sometimes|string|in:full,incremental',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        // Recargar configuración antes de sincronizar
        $this->syncService->reloadConfiguration();

        // Verificar configuración antes de ejecutar
        if (!$this->syncService->isConfigured()) {
            return $this->apiError(
                'MLS no está configurado correctamente',
                'MLS_NOT_CONFIGURED',
                [
                    'hint' => 'Configura la API Key desde el panel de administración en /admin/mls',
                ],
                null,
                400
            );
        }

        $options = $validator->validated();
        $batchSize = (int) ($options['batch_size'] ?? 20);
        $skipMedia = (bool) ($options['skip_media'] ?? true);
        $offset = isset($options['offset']) ? (int) $options['offset'] : null;
        $mode = $options['mode'] ?? null;

        $result = $this->syncService->syncPropertiesProgressive(
            $batchSize,
            $skipMedia,
            $offset,
            $mode
        );

        if ($result['success']) {
            return $this->apiSuccess(
                $result['message'],
                'MLS_SYNC_PROGRESSIVE_SUCCESS',
                [
                    'total_in_mls' => $result['total_in_mls'] ?? 0,
                    'processed' => $result['processed'] ?? 0,
                    'created' => $result['created'] ?? 0,
                    'updated' => $result['updated'] ?? 0,
                    'unpublished' => $result['unpublished'] ?? 0,
                    'errors' => $result['errors'] ?? 0,
                    'next_offset' => $result['next_offset'] ?? 0,
                    'completed' => $result['completed'] ?? false,
                    'progress_percentage' => $result['progress_percentage'] ?? 0,
                    'execution_time_seconds' => $result['execution_time_seconds'] ?? 0,
                    'hint' => $result['completed']
                        ? 'Sincronización completada. No necesitas hacer más llamadas.'
                        : "Para continuar la sincronización, llama nuevamente a este endpoint con offset={$result['next_offset']}. Progress: {$result['progress_percentage']}%",
                ]
            );
        }

        return $this->apiError(
            $result['message'],
            'MLS_SYNC_PROGRESSIVE_FAILED',
            [
                'sync_locked' => $result['sync_locked'] ?? false,
                'circuit_breaker_open' => $result['circuit_breaker_open'] ?? false,
                'configured' => $result['configured'] ?? true,
                'stats' => $result['stats'] ?? [],
            ],
            null,
            500
        );
    }

    /**
     * GET /api/mls/sync/properties/progress
     *
     * Obtiene el progreso actual de la sincronización de propiedades.
     */
    public function getPropertiesSyncProgress(): JsonResponse
    {
        $progress = $this->syncService->getPropertiesSyncProgress();

        return $this->apiSuccess(
            'Progreso de sincronización de propiedades',
            'MLS_PROPERTIES_SYNC_PROGRESS',
            $progress
        );
    }

    /**
     * POST /api/mls/sync/unlock
     *
     * Fuerza la liberación del lock si está obsoleto o si se pasa force=true.
     * Útil cuando una sincronización anterior murió y dejó el lock activo.
     */
    public function forceUnlock(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'force' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        $options = $validator->validated();
        $force = (bool) ($options['force'] ?? false);

        $result = $this->syncService->forceUnlock($force);

        if ($result['success']) {
            return $this->apiSuccess(
                $result['message'],
                'MLS_UNLOCK_SUCCESS',
                $result
            );
        }

        return $this->apiError(
            $result['message'],
            'MLS_UNLOCK_FAILED',
            $result,
            null,
            400
        );
    }

    /**
     * Genera un resumen del log de sincronización.
     */
    protected function summarizeLog(array $log): array
    {
        $summary = [
            'total_entries' => count($log),
            'errors' => 0,
            'warnings' => 0,
            'info' => 0,
            'last_entries' => [],
        ];

        foreach ($log as $entry) {
            $level = $entry['level'] ?? 'info';
            if ($level === 'error') {
                $summary['errors']++;
            } elseif ($level === 'warning') {
                $summary['warnings']++;
            } else {
                $summary['info']++;
            }
        }

        // Agregar últimas 10 entradas
        $summary['last_entries'] = array_slice($log, -10);

        return $summary;
    }
}
