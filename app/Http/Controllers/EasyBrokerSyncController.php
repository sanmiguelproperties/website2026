<?php

namespace App\Http\Controllers;

use App\Models\EasyBrokerConfig;
use App\Services\EasyBrokerSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Controlador para la sincronización con EasyBroker.
 *
 * Proporciona endpoints para:
 * - Gestionar la configuración de la API
 * - Ejecutar sincronización manual
 * - Verificar estado de configuración
 * - Obtener estadísticas de sincronización
 */
class EasyBrokerSyncController extends Controller
{
    protected EasyBrokerSyncService $syncService;

    public function __construct(EasyBrokerSyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    /**
     * GET /api/easybroker/status
     *
     * Obtiene el estado de configuración del servicio de EasyBroker.
     */
    public function status(): JsonResponse
    {
        $status = $this->syncService->getStatus();

        return $this->apiSuccess(
            'Estado de configuración de EasyBroker',
            'EASYBROKER_STATUS',
            $status
        );
    }

    /**
     * GET /api/easybroker/config
     *
     * Obtiene la configuración actual de EasyBroker.
     */
    public function getConfig(): JsonResponse
    {
        $config = EasyBrokerConfig::getOrCreateDefault();

        return $this->apiSuccess(
            'Configuración de EasyBroker',
            'EASYBROKER_CONFIG',
            $config
        );
    }

    /**
     * PUT /api/easybroker/config
     *
     * Actualiza la configuración de EasyBroker.
     */
    public function updateConfig(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'api_key' => 'sometimes|nullable|string|max:500',
            'base_url' => 'sometimes|string|url|max:500',
            'rate_limit' => 'sometimes|integer|min:1|max:100',
            'timeout' => 'sometimes|integer|min:5|max:120',
            'is_active' => 'sometimes|boolean',
            'notes' => 'sometimes|nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        $config = EasyBrokerConfig::getOrCreateDefault();
        
        $data = $validator->validated();
        
        // Si no se envía api_key explícitamente, no modificarla
        // Si se envía vacío, se borra
        // Si se envía un valor nuevo, se actualiza
        if (!$request->has('api_key')) {
            unset($data['api_key']);
        }

        $config->update($data);

        // Recargar la configuración en el servicio
        $this->syncService->reloadConfiguration();

        return $this->apiSuccess(
            'Configuración actualizada',
            'EASYBROKER_CONFIG_UPDATED',
            $config->fresh()
        );
    }

    /**
     * POST /api/easybroker/sync
     *
     * Ejecuta la sincronización con EasyBroker.
     * Este proceso puede tomar varios minutos dependiendo del número de propiedades.
     */
    public function sync(Request $request): JsonResponse
    {
        // Recargar configuración antes de sincronizar
        $this->syncService->reloadConfiguration();

        // Verificar configuración antes de ejecutar
        if (!$this->syncService->isConfigured()) {
            return $this->apiError(
                'EasyBroker no está configurado correctamente',
                'EASYBROKER_NOT_CONFIGURED',
                [
                    'hint' => 'Configura la API Key desde el panel de administración en /admin/easybroker',
                ],
                null,
                400
            );
        }

        // Ejecutar sincronización
        $result = $this->syncService->sync();

        if ($result['success']) {
            return $this->apiSuccess(
                $result['message'],
                'EASYBROKER_SYNC_SUCCESS',
                [
                    'stats' => $result['stats'],
                    'log_summary' => $this->summarizeLog($result['log'] ?? []),
                ]
            );
        }

        return $this->apiError(
            $result['message'],
            'EASYBROKER_SYNC_FAILED',
            [
                'stats' => $result['stats'],
                'log_summary' => $this->summarizeLog($result['log'] ?? []),
            ],
            null,
            500
        );
    }

    /**
     * GET /api/easybroker/test-connection
     *
     * Prueba la conexión con la API de EasyBroker.
     */
    public function testConnection(): JsonResponse
    {
        // Recargar configuración
        $this->syncService->reloadConfiguration();

        if (!$this->syncService->isConfigured()) {
            return $this->apiError(
                'EasyBroker no está configurado',
                'EASYBROKER_NOT_CONFIGURED',
                [
                    'hint' => 'Configura la API Key desde el panel de administración en /admin/easybroker',
                ],
                null,
                400
            );
        }

        // Obtener configuración actual para usar en la petición
        $config = $this->syncService->getConfig();
        $apiKey = $config ? $config->api_key_decrypted : config('services.easybroker.api_key');
        $baseUrl = $config ? $config->base_url : config('services.easybroker.base_url', 'https://api.easybroker.com/v1');
        $timeout = $config ? $config->timeout : config('services.easybroker.timeout', 30);

        // Intentar obtener listing_statuses con limit=1 para probar conexión
        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'X-Authorization' => $apiKey,
                'Accept' => 'application/json',
            ])
                ->timeout($timeout)
                ->get($baseUrl . '/listing_statuses', [
                    'limit' => 1,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $total = $data['pagination']['total'] ?? 0;

                return $this->apiSuccess(
                    'Conexión exitosa con EasyBroker',
                    'EASYBROKER_CONNECTION_OK',
                    [
                        'total_properties' => $total,
                        'api_version' => 'v1',
                    ]
                );
            }

            $errorMessage = 'Error de conexión con EasyBroker';
            $responseData = $response->json();
            
            if ($response->status() === 401) {
                $errorMessage = 'API Key inválida o no autorizada';
            } elseif ($response->status() === 403) {
                $errorMessage = 'Acceso denegado. Verifica los permisos de tu API Key';
            }

            return $this->apiError(
                $errorMessage,
                'EASYBROKER_CONNECTION_FAILED',
                [
                    'status_code' => $response->status(),
                    'response' => $responseData,
                ],
                null,
                $response->status()
            );

        } catch (\Throwable $e) {
            return $this->apiError(
                'Error al conectar con EasyBroker: ' . $e->getMessage(),
                'EASYBROKER_CONNECTION_ERROR',
                null,
                null,
                500
            );
        }
    }

    /**
     * DELETE /api/easybroker/config/api-key
     *
     * Elimina la API key configurada.
     */
    public function deleteApiKey(): JsonResponse
    {
        $config = EasyBrokerConfig::getOrCreateDefault();
        $config->update(['api_key' => null]);

        $this->syncService->reloadConfiguration();

        return $this->apiSuccess(
            'API Key eliminada',
            'EASYBROKER_API_KEY_DELETED',
            $config->fresh()
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
