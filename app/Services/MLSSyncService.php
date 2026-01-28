<?php

namespace App\Services;

use App\Jobs\DownloadPropertyImageJob;
use App\Models\Agency;
use App\Models\Currency;
use App\Models\Feature;
use App\Models\MediaAsset;
use App\Models\MLSConfig;
use App\Models\Property;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Servicio de sincronización con MLS AMPI San Miguel de Allende API.
 *
 * Este servicio maneja la sincronización de propiedades desde la API del MLS
 * hacia la base de datos local.
 *
 * Documentación API MLS:
 * - Properties Search: GET /api/v1/properties/search (búsqueda de propiedades)
 * - Property Detail: GET /api/v1/property/mls/{mls_id} (detalle por MLS ID)
 * - Features: GET /api/v1/features (características disponibles)
 * - Neighborhoods: GET /api/v1/neighborhoods (vecindarios disponibles)
 * - Agents: GET /api/v1/agents (agentes disponibles)
 *
 * La configuración se obtiene de la base de datos (tabla mls_configs)
 * o del archivo .env como fallback.
 */
class MLSSyncService
{
    protected ?MLSConfig $config = null;
    protected string $apiKey = '';
    protected string $baseUrl = 'https://ampisanmigueldeallende.com/api/v1';
    protected int $rateLimit = 10;
    protected int $timeout = 30;
    protected int $batchSize = 50;

    protected array $syncLog = [];
    protected int $created = 0;
    protected int $updated = 0;
    protected int $unpublished = 0;
    protected int $errors = 0;
    protected int $totalFetched = 0;
    protected bool $skipMedia = false;
    
    // Nuevas propiedades para manejo robusto de errores
    protected array $errorDetails = [];
    protected array $failedProperties = [];
    protected ?string $lastSuccessfulMlsId = null;
    protected bool $circuitBreakerOpen = false;
    protected int $circuitBreakerFailures = 0;
    protected ?\Carbon\Carbon $circuitBreakerOpenedAt = null;
    protected int $circuitBreakerThreshold = 5;
    protected int $circuitBreakerTimeoutSeconds = 300; // 5 minutos
    protected ?string $syncLockKey = null;
    protected bool $isLocked = false;

    public function __construct()
    {
        $this->loadConfiguration();
    }

    /**
     * Carga la configuración desde la base de datos o .env como fallback.
     */
    protected function loadConfiguration(): void
    {
        // Intentar cargar desde la base de datos primero
        try {
            $this->config = MLSConfig::getActive();
        } catch (\Exception $e) {
            // Si la tabla no existe todavía, usar config
            $this->config = null;
        }

        if ($this->config && $this->config->isConfigured()) {
            // Usar configuración de la base de datos
            $decryptedKey = $this->config->api_key_decrypted;
            $this->apiKey = is_string($decryptedKey) ? $decryptedKey : '';
            $this->baseUrl = $this->config->base_url ?? 'https://ampisanmigueldeallende.com/api/v1';
            $this->rateLimit = $this->config->rate_limit ?? 10;
            $this->timeout = $this->config->timeout ?? 30;
            $this->batchSize = $this->config->batch_size ?? 50;
        } else {
            // Fallback a configuración de .env
            $envKey = config('services.mls.api_key');
            $this->apiKey = is_string($envKey) ? $envKey : '';
            $envBaseUrl = config('services.mls.base_url');
            $this->baseUrl = is_string($envBaseUrl) ? $envBaseUrl : 'https://ampisanmigueldeallende.com/api/v1';
            $this->rateLimit = (int) config('services.mls.rate_limit', 10);
            $this->timeout = (int) config('services.mls.timeout', 30);
            $this->batchSize = (int) config('services.mls.batch_size', 50);
        }
    }

    /**
     * Recarga la configuración (útil después de actualizarla).
     */
    public function reloadConfiguration(): void
    {
        $this->loadConfiguration();
    }

    /**
     * Verifica si el servicio está configurado correctamente.
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Obtiene el API key configurado (ofuscado para logs).
     */
    public function getObfuscatedApiKey(): string
    {
        if (empty($this->apiKey)) {
            return '[no configurada]';
        }
        
        $length = strlen($this->apiKey);
        if ($length <= 8) {
            return str_repeat('*', $length);
        }
        
        return substr($this->apiKey, 0, 4) . str_repeat('*', $length - 8) . substr($this->apiKey, -4);
    }

    /**
     * Obtiene la configuración actual.
     */
    public function getConfig(): ?MLSConfig
    {
        return $this->config;
    }

    /**
     * Ejecuta la sincronización completa.
     *
     * @param array $options Opciones de sincronización
     *   - 'mode': 'full' o 'incremental' (default: configuración)
     *   - 'limit': Número máximo de propiedades a sincronizar (0 = sin límite)
     *   - 'offset': Página inicial (para retomar)
     *   - 'skip_media': Si es true, no sincroniza medios (imágenes/videos)
     *   - 'resume_from_checkpoint': Si es true, retoma desde el último checkpoint
     * @return array Resultado de la sincronización con estadísticas
     */
    public function sync(array $options = []): array
    {
        $this->resetCounters();

        $this->log('info', "================================================");
        $this->log('info', '[SYNC START] INICIANDO SINCRONIZACIÓN MLS');
        $this->log('info', "[SYNC CONFIG] Mode: " . ($options['mode'] ?? 'default') . " | Limit: " . ($options['limit'] ?? 0) . " | Offset: " . ($options['offset'] ?? 1) . " | Skip Media: " . (($options['skip_media'] ?? false) ? 'SÍ' : 'NO') . " | Resume from Checkpoint: " . (($options['resume_from_checkpoint'] ?? false) ? 'SÍ' : 'NO'));
        $this->log('info', "[SYNC API] Base URL: {$this->baseUrl} | Rate Limit: {$this->rateLimit}/seg | Timeout: {$this->timeout}s | Batch Size: {$this->batchSize}");

        // Guardar opción skip_media para usar en syncProperty
        $this->skipMedia = $options['skip_media'] ?? false;

        if (!$this->isConfigured()) {
            $this->log('error', '[SYNC ERROR] MLS no está configurado');
            return [
                'success' => false,
                'message' => 'MLS no está configurado. Configura la API Key desde el panel de administración o en el archivo .env',
                'stats' => $this->getStats(),
            ];
        }

        // Verificar circuit breaker
        if ($this->isCircuitBreakerOpen()) {
            $this->log('error', '[SYNC ERROR] Circuit breaker está abierto. La API ha fallado repetidamente. Intenta más tarde.');
            return [
                'success' => false,
                'message' => 'Circuit breaker abierto. La API ha fallado repetidamente. Intenta más tarde.',
                'stats' => $this->getStats(),
                'circuit_breaker_open' => true,
            ];
        }

        // Adquirir lock para evitar sincronizaciones simultáneas
        if (!$this->acquireSyncLock()) {
            return [
                'success' => false,
                'message' => 'Ya existe una sincronización en curso. Intenta más tarde.',
                'stats' => $this->getStats(),
                'sync_locked' => true,
            ];
        }

        try {
            $this->log('info', '[SYNC] Obteniendo propiedades del MLS...');

            // Opciones de sincronización
            $mode = $options['mode'] ?? ($this->config?->sync_mode ?? 'incremental');
            $limit = $options['limit'] ?? 0;
            $startPage = $options['offset'] ?? 1;
            $resumeFromCheckpoint = $options['resume_from_checkpoint'] ?? false;

            // Obtener checkpoint si se solicita retomar
            $checkpoint = null;
            if ($resumeFromCheckpoint) {
                $checkpoint = $this->getLastCheckpoint();
                if ($checkpoint) {
                    $this->log('info', "[SYNC CHECKPOINT] Retomando desde checkpoint: MLS ID {$checkpoint['last_mls_id']} | Timestamp: {$checkpoint['timestamp']}");
                } else {
                    $this->log('warning', '[SYNC CHECKPOINT] No se encontró checkpoint, iniciando desde el principio');
                }
            }

            // Paso 1: Obtener propiedades del MLS usando el endpoint de búsqueda
            $properties = $this->fetchAllProperties($startPage, $limit);

            if ($properties === null) {
                $this->log('error', '[SYNC ERROR] Error al obtener propiedades del MLS');
                $this->recordCircuitBreakerFailure();
                return [
                    'success' => false,
                    'message' => 'Error al obtener propiedades del MLS',
                    'stats' => $this->getStats(),
                    'circuit_breaker_failures' => $this->circuitBreakerFailures,
                ];
            }

            $this->log('info', '[SYNC] Propiedades obtenidas del MLS: ' . count($properties));
            $this->totalFetched = count($properties);

            // Paso 2: Sincronizar cada propiedad
            $mlsIds = [];
            $this->log('info', '[SYNC] Iniciando sincronización de propiedades...');

            $resumeMode = $resumeFromCheckpoint && $checkpoint !== null;
            $foundCheckpoint = !$resumeMode;

            foreach ($properties as $index => $propertyData) {
                $mlsId = $propertyData['mls_id'] ?? null;
                
                // Validar datos de la propiedad
                $validation = $this->validatePropertyData($propertyData);
                if (!$validation['valid']) {
                    $this->log('warning', "[SYNC VALIDATION] Propiedad {$mlsId} tiene errores de validación: " . implode(', ', $validation['errors']));
                    $this->recordError($mlsId ?? 'unknown', 'validation', implode(', ', $validation['errors']));
                    $this->errors++;
                    continue;
                }

                // Si estamos en modo resume, saltar hasta encontrar el checkpoint
                if ($resumeMode && !$foundCheckpoint) {
                    if ($mlsId === $checkpoint['last_mls_id']) {
                        $foundCheckpoint = true;
                        $this->log('info', "[SYNC CHECKPOINT] Checkpoint encontrado en MLS ID {$mlsId}, continuando...");
                    } else {
                        $this->log('debug', "[SYNC CHECKPOINT] Saltando MLS ID {$mlsId} (buscando checkpoint...)");
                        continue;
                    }
                }

                if ($mlsId) {
                    $mlsIds[] = $mlsId;
                    $this->log('info', "[SYNC PROPERTY] ({" . ($index + 1) . "/" . count($properties) . ") MLS ID: {$mlsId}");
                    
                    try {
                        $this->syncProperty($propertyData);
                        
                        // Guardar checkpoint después de cada propiedad exitosa
                        $this->saveCheckpoint($mlsId);
                        $this->lastSuccessfulMlsId = $mlsId;
                        
                        // Registrar éxito en circuit breaker
                        $this->recordCircuitBreakerSuccess();
                    } catch (\Throwable $e) {
                        $this->log('error', "[SYNC PROPERTY ERROR] Error al sincronizar propiedad {$mlsId}: " . $e->getMessage());
                        $this->recordError($mlsId, 'sync', $e->getMessage(), $e);
                        $this->errors++;
                        $this->recordCircuitBreakerFailure();
                        
                        // Continuar con la siguiente propiedad
                        continue;
                    }
                }

                // Rate limiting
                usleep((int) (1000000 / $this->rateLimit));
                
                // Liberar memoria periódicamente
                if (($index + 1) % 50 === 0) {
                    gc_collect_cycles();
                }
            }

            $this->log('info', "[SYNC] Propiedades procesadas: " . count($mlsIds));

            // Paso 3: Despublicar propiedades que ya no están en el MLS (solo en modo full)
            if ($mode === 'full' && !empty($mlsIds)) {
                $this->log('info', '[SYNC] Ejecutando despublicación de propiedades removidas...');
                $this->unpublishRemovedProperties($mlsIds);
            } elseif ($mode === 'incremental') {
                $this->log('info', '[SYNC] Modo incremental - omitiendo despublicación');
            }

            // Limpiar checkpoint si la sincronización fue exitosa
            if ($this->errors === 0 || $this->errors < count($properties) * 0.1) {
                $this->clearCheckpoint();
            }

            $this->log('info', '[SYNC COMPLETE] Sincronización completada');
            $this->log('info', '================================================');
            $this->log('info', '[SYNC STATS] Creadas: ' . $this->created . ' | Actualizadas: ' . $this->updated . ' | Despublicadas: ' . $this->unpublished . ' | Errores: ' . $this->errors);
            $this->log('info', '================================================');

            // Guardar resultado en la configuración
            if ($this->config) {
                $this->config->recordSyncResult($this->getStats());
            }

            return [
                'success' => true,
                'message' => 'Sincronización completada exitosamente',
                'stats' => $this->getStats(),
                'log' => $this->syncLog,
                'error_details' => $this->errorDetails,
                'failed_properties' => $this->failedProperties,
                'last_successful_mls_id' => $this->lastSuccessfulMlsId,
            ];

        } catch (\Throwable $e) {
            $this->log('error', '[SYNC CRITICAL ERROR] ' . $e->getMessage());
            $this->recordError('critical', 'critical', $e->getMessage(), $e);
            Log::error('MLS sync error: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return [
                'success' => false,
                'message' => 'Error durante la sincronización: ' . $e->getMessage(),
                'stats' => $this->getStats(),
                'log' => $this->syncLog,
                'error_details' => $this->errorDetails,
                'last_successful_mls_id' => $this->lastSuccessfulMlsId,
            ];
        } finally {
            // Siempre liberar el lock
            $this->releaseSyncLock();
        }
    }

    /**
     * Obtiene todas las propiedades del MLS usando el endpoint de búsqueda.
     * El MLS requiere autenticación con API Key.
     *
     * @param int $startPage Página inicial
     * @param int $limit Límite de propiedades (0 = sin límite)
     * @return array|null
     */
    protected function fetchAllProperties(int $startPage = 1, int $limit = 0): ?array
    {
        $allProperties = [];
        $page = $startPage;
        $hasMore = true;
        $perPage = min($this->batchSize, 100); // El MLS puede tener un límite máximo

        while ($hasMore) {
            // Si hay límite y ya alcanzamos, terminar
            if ($limit > 0 && count($allProperties) >= $limit) {
                break;
            }

            $this->log('debug', "Obteniendo página {$page} del MLS...");

            $response = $this->makeRequest('GET', '/properties/search', [
                'page' => $page,
                'per_page' => $perPage,
            ]);

            if ($response === null) {
                // Si falla en la primera página, es un error crítico
                if ($page === $startPage) {
                    return null;
                }
                // Si falla en páginas posteriores, terminar con lo que tenemos
                $this->log('warning', "Error al obtener página {$page}, continuando con " . count($allProperties) . " propiedades");
                break;
            }

            // La estructura de respuesta del MLS puede variar
            $properties = $response['data'] ?? $response['properties'] ?? $response;
            
            if (!is_array($properties)) {
                $this->log('warning', "Respuesta inesperada del MLS en página {$page}");
                break;
            }

            if (empty($properties)) {
                $hasMore = false;
                break;
            }

            $allProperties = array_merge($allProperties, $properties);

            // Verificar paginación
            $pagination = $response['pagination'] ?? $response['meta'] ?? [];
            $totalPages = $pagination['total_pages'] ?? $pagination['last_page'] ?? null;
            $currentPage = $pagination['current_page'] ?? $page;
            
            if ($totalPages !== null) {
                $hasMore = $currentPage < $totalPages;
            } else {
                // Si no hay info de paginación, continuar mientras haya resultados
                $hasMore = count($properties) >= $perPage;
            }

            $page++;

            // Rate limiting entre páginas
            usleep((int) (1000000 / $this->rateLimit));
        }

        return $allProperties;
    }

    /**
     * Sincroniza una propiedad individual desde el MLS.
     */
    protected function syncProperty(array $propertyData): void
    {
        $mlsId = $propertyData['mls_id'] ?? null;
        if (!$mlsId) {
            $this->log('warning', 'Propiedad sin mls_id, omitiendo');
            return;
        }

        $this->log('info', "[SYNC START] ============================================");
        $this->log('info', "[SYNC START] Iniciando sincronización de propiedad MLS: {$mlsId}");
        $this->log('debug', "[SYNC DATA] Datos recibidos: " . json_encode(array_keys($propertyData)));

        try {
            DB::transaction(function () use ($mlsId, $propertyData) {
                $agencyId = $this->getDefaultAgencyId();
                
                // Buscar por mls_id (ID interno del MLS, campo 'id' en el API)
                // que corresponde al campo mls_id en la tabla properties
                $internalMlsId = $propertyData['id'] ?? null;
                
                $existingProperty = null;
                if ($internalMlsId) {
                    $existingProperty = Property::where('agency_id', $agencyId)
                        ->where('mls_id', $internalMlsId)
                        ->first();
                }
                
                // Si no encontramos por mls_id, buscar por mls_public_id como fallback
                if (!$existingProperty && $mlsId) {
                    $existingProperty = Property::where('agency_id', $agencyId)
                        ->where('mls_public_id', (string) $mlsId)
                        ->first();
                }

                $isNew = $existingProperty === null;
                $this->log('info', "[SYNC STATUS] Propiedad: {$mlsId} | Es nueva: " . ($isNew ? 'SÍ' : 'NO'));

                // LOG: Estado actual de media antes de sincronizar
                if (!$isNew && $existingProperty) {
                    $existingMediaCount = $existingProperty->mediaAssets()->count();
                    $existingImages = $existingProperty->mediaAssets()->wherePivot('role', 'image')->count();
                    $this->log('info', "[SYNC PRE-MEDIA] Propiedad: {$mlsId} | Media assets actuales: {$existingMediaCount} | Imágenes: {$existingImages}");
                }

                // Determinar si necesitamos obtener el detalle completo
                $needsDetail = $isNew || !$existingProperty?->raw_payload;

                // Si es propiedad nueva o no tiene payload, obtener el detalle del API
                $fullData = $propertyData;
                if ($needsDetail) {
                    $this->log('debug', "[SYNC DETAIL] Obteniendo detalle de propiedad MLS: {$mlsId}");
                    $detailData = $this->fetchPropertyDetail((string) $mlsId);
                    if ($detailData) {
                        // El detalle puede tener una estructura diferente, fusionar
                        $fullData = array_merge($propertyData, $detailData);
                        $this->log('info', "[SYNC DETAIL] Detalle obtenido | Photos: " . count($detailData['photos'] ?? []) . " | Videos: " . count($detailData['videos'] ?? []));
                    } else {
                        $this->log('warning', "[SYNC DETAIL] No se pudo obtener detalle, usando datos básicos");
                    }
                }

                // Preparar datos de la propiedad
                $propertyAttributes = $this->mapPropertyData($fullData);

                if ($isNew) {
                    $property = Property::create($propertyAttributes);
                    $this->created++;
                    $this->log('info', "[SYNC CREATE] Propiedad MLS creada: {$mlsId}");
                } else {
                    $existingProperty->update($propertyAttributes);
                    $property = $existingProperty;
                    $this->updated++;
                    $this->log('info', "[SYNC UPDATE] Propiedad MLS actualizada: {$mlsId}");
                }

                // Sincronizar relaciones - envolver en try-catch individual para no fallar toda la propiedad
                $this->log('info', "[SYNC RELATIONS] Iniciando sync de relaciones para: {$mlsId}");
                
                try {
                    $this->syncPropertyLocation($property, $fullData);
                    $this->log('info', "[SYNC LOCATION] Completado para: {$mlsId}");
                } catch (\Throwable $e) {
                    $this->log('error', "[SYNC LOCATION ERROR] {$mlsId}: " . $e->getMessage());
                }
                
                try {
                    $this->syncPropertyOperations($property, $fullData);
                    $this->log('info', "[SYNC OPERATIONS] Completado para: {$mlsId}");
                } catch (\Throwable $e) {
                    $this->log('error', "[SYNC OPERATIONS ERROR] {$mlsId}: " . $e->getMessage());
                }
                
                try {
                    $this->syncPropertyFeatures($property, $fullData);
                    $this->log('info', "[SYNC FEATURES] Completado para: {$mlsId}");
                } catch (\Throwable $e) {
                    $this->log('error', "[SYNC FEATURES ERROR] {$mlsId}: " . $e->getMessage());
                }
                
                // Los medios son opcionales - si fallan, solo se loguea pero no falla la propiedad
                try {
                    $this->syncPropertyMedia($property, $fullData);
                    $this->log('info', "[SYNC MEDIA] Completado para: {$mlsId}");
                } catch (\Throwable $e) {
                    $this->log('warning', "[SYNC MEDIA SKIP] {$mlsId}: " . $e->getMessage() . " | La propiedad se sincronizó sin medios");
                }

                $this->log('info', "[SYNC END] ============================================");
            });
        } catch (\Throwable $e) {
            $this->log('error', "[SYNC ERROR] Error al sincronizar propiedad MLS {$mlsId}: " . $e->getMessage());
            $this->errors++;
        }
    }

    /**
     * Obtiene el detalle de una propiedad específica del MLS.
     */
    public function fetchPropertyDetail(string $mlsId): ?array
    {
        return $this->makeRequest('GET', "/property/mls/{$mlsId}");
    }

    /**
     * Mapea los datos del MLS al formato de la tabla properties.
     */
    protected function mapPropertyData(array $data): array
    {
        // Extraer el ID de la propiedad
        $mlsId = $data['mls_id'] ?? null;
        $internalId = $data['id'] ?? null;
        
        // Extraer agentes (el MLS puede devolver un array de IDs)
        $agents = $data['agents'] ?? [];
        $primaryAgentId = is_array($agents) && !empty($agents) ? (string) $agents[0] : null;

        // Determinar si está publicado basándose en varios campos
        $isPublished = false;
        if (isset($data['is_published'])) {
            $isPublished = (bool) $data['is_published'];
        } elseif (isset($data['allow_integration'])) {
            $isPublished = (bool) $data['allow_integration'];
        } else {
            // Por defecto, si viene del MLS y tiene allow_integration, está publicado
            $isPublished = true;
        }

        return [
            'agency_id' => $this->getDefaultAgencyId(),
            'source' => 'mls',
            
            // Campos del MLS
            'mls_id' => $internalId,
            'mls_public_id' => (string) $mlsId,
            'mls_folder_name' => $data['folder_name'] ?? null,
            'mls_neighborhood' => $data['neighborhood'] ?? null,
            'mls_office_id' => $data['office_id'] ?? null,
            
            // Estado y publicación
            'published' => $isPublished,
            'status' => $data['status'] ?? null,
            'category' => $data['category'] ?? null,
            'is_approved' => $data['is_approved'] ?? false,
            'allow_integration' => $data['allow_integration'] ?? true,
            
            // Fechas del MLS
            'mls_created_at' => isset($data['created_at'])
                ? \Carbon\Carbon::parse($data['created_at'])
                : null,
            'mls_updated_at' => isset($data['updated_at'])
                ? \Carbon\Carbon::parse($data['updated_at'])
                : null,
            'last_synced_at' => now(),
            
            // Contenido básico
            'title' => $data['name'] ?? $data['title'] ?? null,
            'description' => $data['description'] ?? null,
            'url' => $data['url'] ?? null,
            'property_type_name' => $data['property_type'] ?? $data['category'] ?? null,
            
            // Características numéricas
            'bedrooms' => $this->parseNumeric($data['bedrooms'] ?? null),
            'bathrooms' => $this->parseDecimal($data['bathrooms'] ?? null),
            'half_bathrooms' => $this->parseNumeric($data['half_bathrooms'] ?? null),
            'parking_spaces' => $this->parseNumeric($data['parking_spaces'] ?? $data['parking_number'] ?? null),
            'parking_number' => $this->parseNumeric($data['parking_number'] ?? null),
            'lot_size' => $this->parseDecimal($data['lot_meters'] ?? $data['lot_size'] ?? null),
            'construction_size' => $this->parseDecimal($data['construction_meters'] ?? $data['construction_size'] ?? null),
            'floors' => $this->parseNumeric($data['floors'] ?? null),
            
            // Precios
            'expenses' => $this->parseDecimal($data['price'] ?? null),
            'old_price' => $this->parseDecimal($data['old_price'] ?? null),
            
            // Características adicionales del MLS
            'furnished' => $data['furnished'] ?? null,
            'with_yard' => $this->parseBoolean($data['with_yard'] ?? null),
            'with_view' => $data['with_view'] ?? null,
            'gated_comm' => $this->parseBoolean($data['gated_comm'] ?? null),
            'pool' => $this->parseBoolean($data['pool'] ?? null),
            'casita' => $this->parseBoolean($data['casita'] ?? null),
            'casita_bedrooms' => $data['casita_bedrooms'] ?? null,
            'casita_bathrooms' => $data['casita_bathrooms'] ?? null,
            
            'virtual_tour_url' => $data['virtual_tour_url'] ?? $data['virtual_tour'] ?? null,
            'raw_payload' => $data,
        ];
    }

    /**
     * Parsea un valor a booleano.
     */
    protected function parseBoolean(mixed $value): ?bool
    {
        if ($value === null) {
            return null;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            $value = strtolower($value);
            if (in_array($value, ['yes', 'true', '1', 'si', 'sí'])) {
                return true;
            }
            if (in_array($value, ['no', 'false', '0'])) {
                return false;
            }
        }

        return (bool) $value;
    }

    /**
     * Parsea un valor a decimal.
     */
    protected function parseDecimal(mixed $value): ?float
    {
        if ($value === null) {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        if (is_string($value)) {
            if (preg_match('/[\d.,]+/', $value, $matches)) {
                $number = str_replace(',', '.', $matches[0]);
                $parts = explode('.', $number);
                if (count($parts) > 2) {
                    $decimal = array_pop($parts);
                    $number = implode('', $parts) . '.' . $decimal;
                }
                return is_numeric($number) ? (float) $number : null;
            }
        }

        return null;
    }

    /**
     * Parsea un valor a entero.
     */
    protected function parseNumeric(mixed $value): ?int
    {
        if ($value === null) {
            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        if (is_string($value)) {
            if (preg_match('/\d+/', $value, $matches)) {
                return (int) $matches[0];
            }
        }

        return null;
    }

    /**
     * Obtiene el ID de agencia por defecto.
     */
    protected function getDefaultAgencyId(): int
    {
        $agency = Agency::first();
        
        if (!$agency) {
            $agency = Agency::create([
                'id' => 1,
                'name' => 'Agencia Principal',
            ]);
        }
        
        return $agency->id;
    }

    /**
     * Sincroniza la ubicación de la propiedad.
     */
    protected function syncPropertyLocation(Property $property, array $data): void
    {
        $location = $data['location'] ?? [];

        // Si no hay objeto location, usar campos de nivel superior
        if (empty($location)) {
            $location = [
                'city' => $data['city'] ?? null,
                'neighborhood' => $data['neighborhood'] ?? null,
                'state' => $data['state'] ?? $data['region'] ?? null,
                'street' => $data['street'] ?? $data['address'] ?? null,
                'postal_code' => $data['postal_code'] ?? $data['zip_code'] ?? null,
                'latitude' => $data['latitude'] ?? $data['lat'] ?? null,
                'longitude' => $data['longitude'] ?? $data['lng'] ?? $data['lon'] ?? null,
            ];
        }

        // Si todos los campos están vacíos, no crear ubicación
        $hasData = collect($location)->filter()->isNotEmpty();
        if (!$hasData) {
            $this->log('debug', "[LOCATION SYNC] No hay datos de ubicación para: {$property->mls_public_id}");
            return;
        }

        $hasExisting = $property->location()->exists();
        $this->log('debug', "[LOCATION SYNC] Propiedad: {$property->mls_public_id} | Tiene ubicación existente: " . ($hasExisting ? 'SÍ' : 'NO'));

        $property->location()->updateOrCreate(
            ['property_id' => $property->id],
            [
                'region' => $location['state'] ?? $location['region'] ?? null,
                'city' => $location['city'] ?? null,
                'city_area' => $location['neighborhood'] ?? $location['city_area'] ?? null,
                'street' => $location['street'] ?? $location['address'] ?? null,
                'postal_code' => $location['postal_code'] ?? $location['zip_code'] ?? null,
                'show_exact_location' => $location['show_exact_location'] ?? null,
                'latitude' => $location['latitude'] ?? $location['lat'] ?? null,
                'longitude' => $location['longitude'] ?? $location['lng'] ?? $location['lon'] ?? null,
                'raw_payload' => $location,
            ]
        );

        $this->log('debug', "[LOCATION SYNC END] Propiedad: {$property->mls_public_id} | Ubicación sincronizada");
    }

    /**
     * Sincroniza las operaciones (venta/renta) de la propiedad.
     */
    protected function syncPropertyOperations(Property $property, array $data): void
    {
        // El MLS puede tener la operación en diferentes formatos
        $operations = $data['operations'] ?? [];

        // LOG: Estado antes de sincronizar
        $existingOperationsCount = $property->operations()->count();
        $this->log('debug', "[OPERATIONS SYNC START] Propiedad: {$property->mls_public_id} | Operaciones actuales: {$existingOperationsCount}");

        // Si no hay operaciones explícitas, crear una basada en status y price
        if (empty($operations)) {
            $status = $data['status'] ?? null;
            $price = $data['price'] ?? null;
            $currency = $data['currency'] ?? 'USD';

            if ($price && $status) {
                $operationType = 'sale';
                if (stripos($status, 'rent') !== false) {
                    $operationType = 'rental';
                }

                $operations = [
                    [
                        'type' => $operationType,
                        'amount' => $price,
                        'currency' => $currency,
                    ]
                ];
            }
        }

        if (empty($operations)) {
            $this->log('debug', "[OPERATIONS SYNC] No hay operaciones que sincronizar");
            return;
        }

        $this->log('info', "[OPERATIONS SYNC] Propiedad: {$property->mls_public_id} | Operaciones a sincronizar: " . count($operations));

        // Eliminar operaciones existentes
        $property->operations()->delete();

        foreach ($operations as $op) {
            $currencyCode = $op['currency'] ?? 'USD';
            $currency = Currency::where('code', $currencyCode)->first();

            $property->operations()->create([
                'operation_type' => $op['type'] ?? 'sale',
                'amount' => $op['amount'] ?? null,
                'currency_id' => $currency?->id,
                'currency_code' => $currencyCode,
                'formatted_amount' => $op['formatted_amount'] ?? null,
                'unit' => $op['unit'] ?? 'total',
                'raw_payload' => $op,
            ]);
        }

        $finalOperationsCount = $property->operations()->count();
        $this->log('info', "[OPERATIONS SYNC END] Propiedad: {$property->mls_public_id} | Operaciones finales: {$finalOperationsCount}");
    }

    /**
     * Sincroniza las características de la propiedad.
     */
    protected function syncPropertyFeatures(Property $property, array $data): void
    {
        $features = $data['features'] ?? [];

        // LOG: Estado antes de sincronizar
        $existingFeaturesCount = $property->features()->count();
        $this->log('debug', "[FEATURES SYNC START] Propiedad: {$property->mls_public_id} | Features actuales: {$existingFeaturesCount}");

        if (empty($features)) {
            $this->log('debug', "[FEATURES SYNC] No hay features en los datos, limpiando existentes");
            $property->features()->sync([]);
            return;
        }

        $this->log('info', "[FEATURES SYNC] Propiedad: {$property->mls_public_id} | Features a sincronizar: " . count($features));

        $featureIds = [];

        foreach ($features as $index => $featureData) {
            $featureName = null;
            $featureCategory = null;

            if (is_array($featureData)) {
                $featureName = $featureData['name'] ?? null;
                $featureCategory = $featureData['category'] ?? null;
            } elseif (is_string($featureData)) {
                $featureName = $featureData;
            }

            if (empty($featureName)) {
                continue;
            }

            $feature = Feature::firstOrCreate(
                [
                    'name' => $featureName,
                    'category' => $featureCategory,
                ],
                [
                    'locale' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $featureIds[] = $feature->id;
        }

        $property->features()->sync($featureIds);

        $finalFeaturesCount = $property->features()->count();
        $this->log('info', "[FEATURES SYNC END] Propiedad: {$property->mls_public_id} | Features finales: {$finalFeaturesCount}");
    }

    /**
     * Sincroniza los medios (imágenes/videos) de la propiedad.
     * Crea las relaciones directamente y dispatcha jobs solo para descarga de imágenes nuevas.
     */
    protected function syncPropertyMedia(Property $property, array $data): void
    {
        // Si skipMedia está activo, no sincronizar medios
        if ($this->skipMedia) {
            $this->log('info', "[MEDIA SKIP] skipMedia=true, omitiendo sincronización de medios para: {$property->mls_public_id}");
            return;
        }
        
        $photos = $data['photos'] ?? $data['images'] ?? [];
        $videos = $data['videos'] ?? [];

        // LOG: Estado antes de sincronizar
        $existingMediaCount = $property->mediaAssets()->count();
        $existingImageCount = $property->mediaAssets()->wherePivot('role', 'image')->count();
        $this->log('info', "[MEDIA SYNC START] Propiedad: {$property->mls_public_id} | Imágenes existentes: {$existingImageCount} | Videos existentes: " . ($existingMediaCount - $existingImageCount));

        // Si no hay fotos en los datos nuevos, verificar en el raw_payload existente
        if (empty($photos) && !empty($data['raw_payload']['photos'])) {
            $photos = $data['raw_payload']['photos'];
            $this->log('info', "[MEDIA FROM PAYLOAD] Usando fotos del raw_payload existente para: {$property->mls_public_id} | Fotos: " . count($photos));
        }

        if (empty($videos) && !empty($data['raw_payload']['videos'])) {
            $videos = $data['raw_payload']['videos'];
            $this->log('info', "[MEDIA FROM PAYLOAD] Usando videos del raw_payload existente para: {$property->mls_public_id} | Videos: " . count($videos));
        }

        if (empty($photos) && empty($videos)) {
            $this->log('warning', "[MEDIA SYNC SKIP] Propiedad: {$property->mls_public_id} | No tiene photos ni videos (ni en datos nuevos ni en raw_payload)");
            return;
        }

        $this->log('info', "[MEDIA SYNC] Propiedad: {$property->mls_public_id} | Photos a procesar: " . count($photos) . " | Videos a procesar: " . count($videos));

        $position = 0;
        $imageUrls = [];
        $imagesLinked = 0;
        $imagesDispatched = 0;
        $imagesSkipped = 0;

        // Procesar fotos -收集 URLs y crear relaciones para imágenes existentes
        foreach ($photos as $index => $photo) {
            $url = is_string($photo) ? $photo : ($photo['url'] ?? $photo['src'] ?? null);
            if (!$url) {
                $this->log('warning', "[MEDIA SKIP] Foto sin URL en posición {$index} | Propiedad: {$property->mls_public_id}");
                $imagesSkipped++;
                continue;
            }

            $imageUrls[] = $url;

            // LOG: Procesando cada imagen
            $this->log('debug', "[MEDIA PROCESS] Foto posición {$index}: {$url}");

            // Verificar si ya existe un MediaAsset para esta URL
            $existingMediaAsset = MediaAsset::where('url', $url)->first();

            if ($existingMediaAsset && $existingMediaAsset->storage_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($existingMediaAsset->storage_path)) {
                // La imagen ya existe localmente, crear/vincular relación directamente
                $this->log('info', "[MEDIA LINK] Imagen ya existe localmente, vinculando: {$url}");
                $this->linkMediaAssetToProperty($property, $existingMediaAsset, $photo, $position);
                $imagesLinked++;
            } else {
                // La imagen no existe localmente, preparar metadata para el job
                $mediaData = [
                    'url' => $url,
                    'title' => is_array($photo) ? ($photo['title'] ?? $photo['alt'] ?? null) : null,
                    'alt' => is_array($photo) ? ($photo['alt'] ?? null) : null,
                    'position' => $position,
                ];

                // LOG: Dispatch job
                $this->log('info', "[MEDIA DISPATCH] Imagen no existe, dispatchando job: {$url}");

                // Dispatch job para descargar la imagen
                DownloadPropertyImageJob::dispatch($property->id, $mediaData);
                $imagesDispatched++;
            }

            $position++;
        }

        // Procesar videos (solo registrar, sin descarga)
        foreach ($videos as $video) {
            $url = is_string($video) ? $video : ($video['url'] ?? null);
            if (!$url) {
                continue;
            }

            $mediaAsset = $this->getOrCreateMediaAsset($url, 'video', is_array($video) ? $video : []);

            $property->mediaAssets()->syncWithoutDetaching([
                $mediaAsset->id => [
                    'role' => 'video',
                    'title' => is_array($video) ? ($video['title'] ?? null) : null,
                    'position' => $position,
                    'source_url' => $url,
                    'raw_payload' => json_encode(is_array($video) ? $video : ['url' => $video]),
                ]
            ]);

            $position++;
        }

        // LOG: Resumen de sincronización de media
        $finalMediaCount = $property->mediaAssets()->count();
        $finalImageCount = $property->mediaAssets()->wherePivot('role', 'image')->count();
        $this->log('info', "[MEDIA SYNC END] Propiedad: {$property->mls_public_id} | Inicial: {$existingImageCount} | Final: {$finalImageCount} | Vinculadas: {$imagesLinked} | Dispatched: {$imagesDispatched} | Skippeadas: {$imagesSkipped}");
    }

    /**
     * Vincula un MediaAsset existente a una propiedad.
     */
    protected function linkMediaAssetToProperty(Property $property, MediaAsset $mediaAsset, mixed $photoData, int $position): void
    {
        $title = is_array($photoData) ? ($photoData['title'] ?? $photoData['alt'] ?? null) : null;
        $sourceUrl = is_array($photoData) ? ($photoData['url'] ?? null) : $photoData;

        // Verificar si ya está vinculado
        $existingPivot = $property->mediaAssets()
            ->where('media_asset_id', $mediaAsset->id)
            ->first();

        if ($existingPivot) {
            // Actualizar datos del pivot si es necesario
            $existingPivot->pivot->update([
                'title' => $title,
                'position' => $position,
                'checksum' => $mediaAsset->checksum,
                'source_url' => $sourceUrl,
                'raw_payload' => json_encode($photoData),
            ]);

            $this->log('info', "[MEDIA LINK UPDATE] MediaAsset ID: {$mediaAsset->id} | Propiedad: {$property->mls_public_id} | Position: {$position} | URL: {$sourceUrl}");
        } else {
            // Primera imagen como portada
            if ($position === 0) {
                $property->update(['cover_media_asset_id' => $mediaAsset->id]);
                $this->log('info', "[MEDIA COVER] Imagen {$position} establecida como cover | Propiedad: {$property->mls_public_id}");
            }

            // Vincular
            $property->mediaAssets()->attach($mediaAsset->id, [
                'role' => 'image',
                'title' => $title,
                'position' => $position,
                'checksum' => $mediaAsset->checksum,
                'source_url' => $sourceUrl,
                'raw_payload' => json_encode($photoData),
            ]);

            $this->log('info', "[MEDIA LINK ATTACH] MediaAsset ID: {$mediaAsset->id} | Propiedad: {$property->mls_public_id} | Position: {$position}");
        }
    }

    /**
     * Obtiene o crea un MediaAsset para una URL.
     */
    protected function getOrCreateMediaAsset(string $url, string $type, array $metadata): MediaAsset
    {
        $existing = MediaAsset::where('url', $url)->first();
        if ($existing) {
            return $existing;
        }

        return MediaAsset::create([
            'type' => $type,
            'provider' => 'mls',
            'url' => $url,
            'name' => $metadata['title'] ?? $metadata['name'] ?? basename(parse_url($url, PHP_URL_PATH)),
            'alt' => $metadata['alt'] ?? $metadata['title'] ?? null,
        ]);
    }

    /**
     * Despublica propiedades que ya no están en el MLS.
     */
    protected function unpublishRemovedProperties(array $currentMlsIds): void
    {
        $agencyId = $this->getDefaultAgencyId();
        
        // Convertir a strings para comparación
        $currentMlsIds = array_map('strval', $currentMlsIds);

        // Despublicar propiedades locales del MLS que ya no están en la lista
        $unpublished = Property::where('agency_id', $agencyId)
            ->where('source', 'mls')
            ->where('published', true)
            ->whereNotNull('mls_public_id')
            ->where('mls_public_id', '!=', '')
            ->whereNotIn('mls_public_id', $currentMlsIds)
            ->update(['published' => false]);

        $this->unpublished = $unpublished;

        if ($unpublished > 0) {
            $this->log('info', "Propiedades MLS despublicadas: {$unpublished}");
        }
    }

    /**
     * Número máximo de reintentos en caso de error de conexión.
     */
    protected int $maxRetries = 3;

    /**
     * Segundos de espera entre reintentos (backoff exponencial).
     */
    protected array $retryDelays = [1, 3, 5];

    /**
     * Realiza una petición HTTP a la API del MLS con reintentos automáticos.
     *
     * @param string $method Método HTTP (GET, POST, etc.)
     * @param string $endpoint Endpoint de la API
     * @param array $query Parámetros de query string
     * @param int|null $customTimeout Timeout personalizado para esta petición
     * @return array|null Los datos de respuesta o null si todos los reintentos fallan
     */
    protected function makeRequest(string $method, string $endpoint, array $query = [], ?int $customTimeout = null): ?array
    {
        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/');
        $lastException = null;
        $lastResponseBody = null;
        $requestStartTime = microtime(true);
        $timeout = $customTimeout ?? $this->timeout;

        for ($attempt = 1; $attempt <= $this->maxRetries; $attempt++) {
            try {
                $requestStartTime = microtime(true);
                $this->log('debug', "[API REQUEST] ════════════════════════════════════════════");
                $this->log('debug', "[API REQUEST] Intento {$attempt}/{$this->maxRetries} | {$method} {$endpoint}");
                $this->log('debug', "[API REQUEST] URL: {$url}");
                $this->log('debug', "[API REQUEST] Query: " . json_encode($query));
                $this->log('debug', "[API REQUEST] Timeout: {$timeout}s");

                $response = Http::withHeaders([
                    'X-Api-Key' => $this->apiKey,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                    ->timeout($timeout)
                    ->$method($url, $query);

                $responseTime = round((microtime(true) - $requestStartTime) * 1000, 2);

                // Log de respuesta detallada
                $this->log('debug', "[API RESPONSE] ════════════════════════════════════════════");
                $this->log('debug', "[API RESPONSE] Status: {$response->status()}");
                $this->log('debug', "[API RESPONSE] Tiempo de respuesta: {$responseTime}ms");

                // Verificar si headers es un objeto o array
                $headers = $response->headers();
                if (is_object($headers) && method_exists($headers, 'all')) {
                    $this->log('debug', "[API RESPONSE] Headers: " . json_encode($headers->all()));
                } else {
                    $this->log('debug', "[API RESPONSE] Headers: " . json_encode($headers));
                }

                if ($response->failed()) {
                    $statusCode = $response->status();
                    $responseBody = $response->body();
                    $this->log('error', "[API ERROR] ════════════════════════════════════════════");
                    $this->log('error', "[API ERROR] HTTP Status: {$statusCode}");
                    $this->log('error', "[API ERROR] Response Body: {$responseBody}");
                    $this->log('error', "[API ERROR] Intento {$attempt}/{$this->maxRetries}");

                    // Si es error 5xx (error del servidor), reintentar
                    if ($statusCode >= 500 && $attempt < $this->maxRetries) {
                        $delay = $this->retryDelays[$attempt - 1] ?? 5;
                        $this->log('warning', "[API RETRY] Error del servidor ({$statusCode}), reintentando en {$delay}s...");
                        sleep($delay);
                        continue;
                    }

                    // Error 4xx (cliente) no reintentar
                    return null;
                }

                // Verificar si la respuesta JSON contiene un error (success: false)
                $responseData = $response->json();
                $lastResponseBody = $responseData;

                $this->log('debug', "[API RESPONSE] Raw JSON: " . json_encode($responseData, JSON_PRETTY_PRINT));

                // Validar estructura de respuesta
                if (!$this->validateApiResponse($responseData)) {
                    $this->log('error', "[API VALIDATION] Estructura de respuesta inválida");
                    return null;
                }

                if (is_array($responseData) && isset($responseData['success']) && $responseData['success'] === false) {
                    $errorCode = $responseData['code'] ?? 'UNKNOWN';
                    $errorMessage = $responseData['message'] ?? 'Error desconocido';
                    $errorData = $responseData['data'] ?? null;
                    $errorErrors = $responseData['errors'] ?? null;

                    $this->log('error', "[API ERROR JSON] ════════════════════════════════════════════");
                    $this->log('error', "[API ERROR JSON] success: false");
                    $this->log('error', "[API ERROR JSON] code: {$errorCode}");
                    $this->log('error', "[API ERROR JSON] message: {$errorMessage}");
                    $this->log('error', "[API ERROR JSON] data: " . json_encode($errorData));
                    $this->log('error', "[API ERROR JSON] errors: " . json_encode($errorErrors));
                    $this->log('error', "[API ERROR JSON] Intento {$attempt}/{$this->maxRetries}");

                    // Errores de servidor (como SERVER_ERROR) requieren reintento
                    if (in_array($errorCode, ['SERVER_ERROR', 'TIMEOUT', 'CONNECTION_ERROR', 'SERVICE_UNAVAILABLE']) && $attempt < $this->maxRetries) {
                        $delay = $this->retryDelays[$attempt - 1] ?? 5;
                        $this->log('warning', "[API RETRY] Error de API ({$errorCode}), reintentando en {$delay}s...");
                        sleep($delay);
                        continue;
                    }

                    // Otros errores de API (auth, validation, etc.) no reintentar
                    $this->log('warning', "[API SKIP] Error de API ({$errorCode}) no es reintentable, continuando...");
                    return null;
                }

                $this->log('debug', "[API SUCCESS] {$method} {$endpoint} | Status: {$response->status()} | Tiempo: {$responseTime}ms");
                return $responseData;

            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                $lastException = $e;
                $errorClass = get_class($e);
                $errorFile = $e->getFile();
                $errorLine = $e->getLine();

                $this->log('error', "[API EXCEPTION] ════════════════════════════════════════════");
                $this->log('error', "[API EXCEPTION] Tipo: {$errorClass}");
                $this->log('error', "[API EXCEPTION] Mensaje: " . $e->getMessage());
                $this->log('error', "[API EXCEPTION] Archivo: {$errorFile}:{$errorLine}");
                $this->log('error', "[API EXCEPTION] Intento {$attempt}/{$this->maxRetries}");

                if ($attempt < $this->maxRetries) {
                    $delay = $this->retryDelays[$attempt - 1] ?? 5;
                    $this->log('warning', "[API RETRY] Error de conexión, reintentando en {$delay}s...");
                    sleep($delay);
                }
            } catch (\Illuminate\Http\Client\TimeoutException $e) {
                $lastException = $e;
                $errorClass = get_class($e);
                $errorFile = $e->getFile();
                $errorLine = $e->getLine();

                $this->log('error', "[API TIMEOUT] ════════════════════════════════════════════");
                $this->log('error', "[API TIMEOUT] Tipo: {$errorClass}");
                $this->log('error', "[API TIMEOUT] Mensaje: " . $e->getMessage());
                $this->log('error', "[API TIMEOUT] Archivo: {$errorFile}:{$errorLine}");
                $this->log('error', "[API TIMEOUT] Intento {$attempt}/{$this->maxRetries}");

                if ($attempt < $this->maxRetries) {
                    $delay = $this->retryDelays[$attempt - 1] ?? 5;
                    $this->log('warning', "[API RETRY] Timeout, reintentando en {$delay}s...");
                    sleep($delay);
                }
            } catch (\Throwable $e) {
                $lastException = $e;
                $errorClass = get_class($e);
                $errorFile = $e->getFile();
                $errorLine = $e->getLine();

                $this->log('error', "[API EXCEPTION] ════════════════════════════════════════════");
                $this->log('error', "[API EXCEPTION] Tipo: {$errorClass}");
                $this->log('error', "[API EXCEPTION] Mensaje: " . $e->getMessage());
                $this->log('error', "[API EXCEPTION] Archivo: {$errorFile}:{$errorLine}");
                $this->log('error', "[API EXCEPTION] Intento {$attempt}/{$this->maxRetries}");

                if ($attempt < $this->maxRetries) {
                    $delay = $this->retryDelays[$attempt - 1] ?? 5;
                    $this->log('warning', "[API RETRY] Error de conexión, reintentando en {$delay}s...");
                    sleep($delay);
                }
            }
        }

        // Todos los reintentos fallaron
        $this->log('error', "[API FAIL] ════════════════════════════════════════════");
        $this->log('error', "[API FAIL] Todos los {$this->maxRetries} reintentos fallaron para {$method} {$endpoint}");
        $this->log('error', "[API FAIL] Última respuesta: " . json_encode($lastResponseBody));

        Log::error('MLS API - Todos los reintentos fallaron', [
            'method' => $method,
            'endpoint' => $endpoint,
            'max_retries' => $this->maxRetries,
            'exception' => $lastException,
            'last_response' => $lastResponseBody,
        ]);

        return null;
    }

    /**
     * Obtiene las características disponibles del MLS.
     */
    public function fetchFeatures(): ?array
    {
        return $this->makeRequest('GET', '/features');
    }

    /**
     * Obtiene los vecindarios disponibles del MLS.
     */
    public function fetchNeighborhoods(): ?array
    {
        return $this->makeRequest('GET', '/neighborhoods');
    }

    /**
     * Obtiene los agentes disponibles del MLS.
     */
    public function fetchAgents(): ?array
    {
        return $this->makeRequest('GET', '/agents');
    }

    /**
     * Agrega una entrada al log de sincronización.
     */
    protected function log(string $level, string $message): void
    {
        $this->syncLog[] = [
            'time' => now()->toIso8601String(),
            'level' => $level,
            'message' => $message,
        ];

        Log::log($level, "[MLSSync] {$message}");
    }

    /**
     * Reinicia los contadores de sincronización.
     */
    protected function resetCounters(): void
    {
        $this->syncLog = [];
        $this->created = 0;
        $this->updated = 0;
        $this->unpublished = 0;
        $this->errors = 0;
        $this->totalFetched = 0;
        $this->errorDetails = [];
        $this->failedProperties = [];
        $this->lastSuccessfulMlsId = null;
    }

    /**
     * Verifica si el circuit breaker está abierto.
     * El circuit breaker se abre después de N fallos consecutivos y se cierra después de un período de tiempo.
     */
    protected function isCircuitBreakerOpen(): bool
    {
        if (!$this->circuitBreakerOpen) {
            return false;
        }

        // Verificar si ha pasado el tiempo de espera para intentar recuperar
        if ($this->circuitBreakerOpenedAt && $this->circuitBreakerOpenedAt->addSeconds($this->circuitBreakerTimeoutSeconds)->isPast()) {
            $this->log('info', '[CIRCUIT BREAKER] Tiempo de espera cumplido, intentando recuperar...');
            $this->resetCircuitBreaker();
            return false;
        }

        return true;
    }

    /**
     * Registra un fallo en el circuit breaker.
     * Si se alcanza el umbral de fallos, se abre el circuit breaker.
     */
    protected function recordCircuitBreakerFailure(): void
    {
        $this->circuitBreakerFailures++;
        
        if ($this->circuitBreakerFailures >= $this->circuitBreakerThreshold) {
            $this->circuitBreakerOpen = true;
            $this->circuitBreakerOpenedAt = now();
            $this->log('error', "[CIRCUIT BREAKER] Circuit breaker abierto después de {$this->circuitBreakerFailures} fallos consecutivos");
            $this->log('error', "[CIRCUIT BREAKER] Se cerrará automáticamente en {$this->circuitBreakerTimeoutSeconds} segundos");
        }
    }

    /**
     * Registra un éxito en el circuit breaker.
     * Si el circuit breaker estaba abierto, se cierra.
     */
    protected function recordCircuitBreakerSuccess(): void
    {
        $this->circuitBreakerFailures = 0;
        
        if ($this->circuitBreakerOpen) {
            $this->circuitBreakerOpen = false;
            $this->circuitBreakerOpenedAt = null;
            $this->log('info', '[CIRCUIT BREAKER] Circuit breaker cerrado exitosamente');
        }
    }

    /**
     * Reinicia el circuit breaker.
     */
    protected function resetCircuitBreaker(): void
    {
        $this->circuitBreakerOpen = false;
        $this->circuitBreakerFailures = 0;
        $this->circuitBreakerOpenedAt = null;
    }

    /**
     * Intenta adquirir un lock para evitar sincronizaciones simultáneas.
     * 
     * @return bool True si se adquirió el lock, false si ya existe una sincronización en curso
     */
    protected function acquireSyncLock(): bool
    {
        $this->syncLockKey = 'mls_sync_lock';
        
        try {
            $this->isLocked = \Illuminate\Support\Facades\Cache::lock($this->syncLockKey, 3600)->block(0);
            
            if (!$this->isLocked) {
                $this->log('warning', '[SYNC LOCK] Ya existe una sincronización en curso. Intenta más tarde.');
                return false;
            }
            
            $this->log('info', '[SYNC LOCK] Lock adquirido exitosamente');
            return true;
        } catch (\Throwable $e) {
            $this->log('error', '[SYNC LOCK] Error al adquirir lock: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Libera el lock de sincronización.
     */
    protected function releaseSyncLock(): void
    {
        if ($this->syncLockKey && $this->isLocked) {
            try {
                \Illuminate\Support\Facades\Cache::lock($this->syncLockKey)->release();
                $this->isLocked = false;
                $this->log('info', '[SYNC LOCK] Lock liberado exitosamente');
            } catch (\Throwable $e) {
                $this->log('error', '[SYNC LOCK] Error al liberar lock: ' . $e->getMessage());
            }
        }
    }

    /**
     * Registra un error detallado para análisis posterior.
     * 
     * @param string $mlsId ID de la propiedad que falló
     * @param string $errorType Tipo de error (api, database, validation, etc.)
     * @param string $message Mensaje de error
     * @param \Throwable|null $exception Excepción si está disponible
     */
    protected function recordError(string $mlsId, string $errorType, string $message, ?\Throwable $exception = null): void
    {
        $errorDetail = [
            'mls_id' => $mlsId,
            'error_type' => $errorType,
            'message' => $message,
            'timestamp' => now()->toIso8601String(),
        ];

        if ($exception) {
            $errorDetail['exception'] = [
                'class' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ];
        }

        $this->errorDetails[] = $errorDetail;
        
        if ($mlsId) {
            $this->failedProperties[] = $mlsId;
        }

        // Log adicional para debugging
        $this->log('error', "[ERROR DETAIL] Type: {$errorType} | MLS ID: {$mlsId} | Message: {$message}");
    }

    /**
     * Valida la estructura de datos de una propiedad del API.
     * 
     * @param array $propertyData Datos de la propiedad
     * @return array Array con ['valid' => bool, 'errors' => array]
     */
    protected function validatePropertyData(array $propertyData): array
    {
        $errors = [];
        
        // Campos requeridos
        $requiredFields = ['mls_id', 'id'];
        foreach ($requiredFields as $field) {
            if (!isset($propertyData[$field]) || empty($propertyData[$field])) {
                $errors[] = "Campo requerido faltante: {$field}";
            }
        }

        // Validar tipos de datos
        if (isset($propertyData['price']) && !is_numeric($propertyData['price'])) {
            $errors[] = "El campo 'price' debe ser numérico";
        }

        if (isset($propertyData['bedrooms']) && !is_numeric($propertyData['bedrooms'])) {
            $errors[] = "El campo 'bedrooms' debe ser numérico";
        }

        if (isset($propertyData['bathrooms']) && !is_numeric($propertyData['bathrooms'])) {
            $errors[] = "El campo 'bathrooms' debe ser numérico";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Valida la estructura de respuesta del API.
     * 
     * @param array|null $response Respuesta del API
     * @return bool True si la respuesta es válida
     */
    protected function validateApiResponse(?array $response): bool
    {
        if ($response === null) {
            return false;
        }

        // Verificar que sea un array
        if (!is_array($response)) {
            $this->log('error', '[API VALIDATION] La respuesta no es un array');
            return false;
        }

        // Verificar que no tenga el campo success: false
        if (isset($response['success']) && $response['success'] === false) {
            return false;
        }

        return true;
    }

    /**
     * Obtiene el último checkpoint de sincronización.
     * 
     * @return array|null Checkpoint con ['last_mls_id' => string, 'timestamp' => string]
     */
    protected function getLastCheckpoint(): ?array
    {
        try {
            $checkpoint = \Illuminate\Support\Facades\Cache::get('mls_sync_checkpoint');
            return $checkpoint;
        } catch (\Throwable $e) {
            $this->log('error', '[CHECKPOINT] Error al obtener checkpoint: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Guarda un checkpoint de sincronización.
     * 
     * @param string $mlsId Último MLS ID procesado exitosamente
     */
    protected function saveCheckpoint(string $mlsId): void
    {
        try {
            $checkpoint = [
                'last_mls_id' => $mlsId,
                'timestamp' => now()->toIso8601String(),
            ];
            
            \Illuminate\Support\Facades\Cache::put('mls_sync_checkpoint', $checkpoint, 86400); // 24 horas
            $this->log('info', "[CHECKPOINT] Guardado: MLS ID {$mlsId}");
        } catch (\Throwable $e) {
            $this->log('error', '[CHECKPOINT] Error al guardar checkpoint: ' . $e->getMessage());
        }
    }

    /**
     * Limpia el checkpoint de sincronización.
     */
    protected function clearCheckpoint(): void
    {
        try {
            \Illuminate\Support\Facades\Cache::forget('mls_sync_checkpoint');
            $this->log('info', '[CHECKPOINT] Checkpoint limpiado');
        } catch (\Throwable $e) {
            $this->log('error', '[CHECKPOINT] Error al limpiar checkpoint: ' . $e->getMessage());
        }
    }

    /**
     * Sincroniza imágenes de propiedades MLS existentes.
     * Obtiene el detalle de cada propiedad para extraer las fotos.
     * Vincula imágenes existentes directamente y dispatcha jobs solo para imágenes nuevas.
     *
     * @param int $limit Número máximo de propiedades a procesar (0 = sin límite, procesa todas)
     * @param bool $force Forzar re-descarga de imágenes existentes
     * @param int $offset Número de propiedades a saltar (para paginación)
     * @return array Resultado con estadísticas detalladas
     */
    public function syncExistingPropertyImages(int $limit = 0, bool $force = false, int $offset = 0): array
    {
        $limitText = $limit > 0 ? (string) $limit : 'TODAS';
        $this->log('info', "[IMAGES SYNC START] ================================");
        $this->log('info', "[IMAGES SYNC START] Iniciando sincronización de imágenes | Límite: {$limitText} | Force: " . ($force ? 'SÍ' : 'NO') . " | Offset: {$offset}");

        // Obtener el total de propiedades MLS para mostrar progreso
        $totalMlsProperties = Property::where('source', 'mls')
            ->whereNotNull('mls_public_id')
            ->count();

        $this->log('info', "[IMAGES SYNC] Total de propiedades MLS en BD: {$totalMlsProperties}");

        // Construir query para propiedades con offset
        $query = Property::where('source', 'mls')
            ->whereNotNull('mls_public_id')
            ->offset($offset);
        
        // Aplicar límite si es > 0
        if ($limit > 0) {
            $query->limit($limit);
        }
        
        $properties = $query->orderBy('id')->get();

        $this->log('info', "[IMAGES SYNC] Propiedades a procesar: " . $properties->count() . " / {$totalMlsProperties} (offset: {$offset})");

        if ($properties->isEmpty()) {
            $this->log('warning', "[IMAGES SYNC] No se encontraron propiedades MLS para procesar");
            return [
                'linked' => 0,
                'dispatched' => 0,
                'processed' => 0,
                'total_in_db' => $totalMlsProperties,
                'message' => 'No se encontraron propiedades MLS',
            ];
        }

        $linked = 0;
        $dispatched = 0;
        $processed = 0;
        $skipped = 0;
        $errors = 0;
        $imagesWithoutUrl = 0;
        $imagesAlreadyLinked = 0;
        $imagesDispatched = [];
        $imagesSkippedList = [];
        $errorsList = [];

        foreach ($properties as $property) {
            $this->log('info', "[IMAGES SYNC PROCESS] ---------------------------");
            $this->log('info', "[IMAGES SYNC] Procesando propiedad: {$property->mls_public_id} | ID: {$property->id}");

            // LOG: Estado actual de media assets
            $currentMediaCount = $property->mediaAssets()->count();
            $currentImagesCount = $property->mediaAssets()->wherePivot('role', 'image')->count();
            $currentLocalImagesCount = $property->mediaAssets()
                ->wherePivot('role', 'image')
                ->whereNotNull('storage_path')
                ->count();
            $this->log('info', "[IMAGES SYNC PRE] Propiedad: {$property->mls_public_id} | Media: {$currentMediaCount} | Imágenes: {$currentImagesCount} | Imágenes locales: {$currentLocalImagesCount}");

            try {
                // Obtener el detalle de la propiedad
                $this->log('debug', "[IMAGES SYNC] Obteniendo detalle del API para: {$property->mls_public_id}");
                $detailData = $this->fetchPropertyDetail($property->mls_public_id);

                if (!$detailData) {
                    $this->log('error', "[IMAGES SYNC ERROR] No se pudo obtener detalle de propiedad: {$property->mls_public_id}");
                    $errors++;
                    $errorsList[] = [
                        'mls_public_id' => $property->mls_public_id,
                        'error' => 'No se pudo obtener detalle del API',
                    ];
                    continue;
                }

                $photos = $detailData['photos'] ?? [];
                $this->log('info', "[IMAGES SYNC] Propiedad: {$property->mls_public_id} | Fotos en API: " . count($photos));

                if (empty($photos)) {
                    $this->log('warning', "[IMAGES SYNC] Propiedad: {$property->mls_public_id} | No hay fotos en el API");
                    $skipped++;
                    $imagesSkippedList[] = [
                        'mls_public_id' => $property->mls_public_id,
                        'reason' => 'Sin fotos en el API',
                    ];
                    $processed++;
                    // Actualizar last_synced_at incluso si no hay fotos
                    $property->update(['last_synced_at' => now()]);
                    continue;
                }

                // URLs de imágenes ya vinculadas (solo si no es force)
                $existingUrls = $force ? [] : $property->mediaAssets()
                    ->wherePivot('role', 'image')
                    ->pluck('url')
                    ->toArray();

                $existingUrlsCount = count($existingUrls);
                $this->log('info', "[IMAGES SYNC] Propiedad: {$property->mls_public_id} | URLs ya vinculadas: {$existingUrlsCount}");

                $imagesInThisProperty = 0;
                $imagesLinkedInThisProperty = 0;
                $imagesDispatchedInThisProperty = 0;
                $imagesSkippedInThisProperty = 0;

                foreach ($photos as $index => $photo) {
                    $url = is_string($photo) ? $photo : ($photo['url'] ?? $photo['src'] ?? null);

                    if (!$url) {
                        $this->log('warning', "[IMAGES SKIP] Propiedad: {$property->mls_public_id} | Foto posición {$index} sin URL");
                        $imagesWithoutUrl++;
                        $imagesSkippedInThisProperty++;
                        continue;
                    }

                    $imagesInThisProperty++;

                    // Si no es force y ya existe, skip
                    if (!$force && in_array($url, $existingUrls)) {
                        $this->log('debug', "[IMAGES ALREADY] Propiedad: {$property->mls_public_id} | Imagen ya vinculada: " . substr($url, 0, 80) . "...");
                        $imagesAlreadyLinked++;
                        continue;
                    }

                    $this->log('info', "[IMAGES PROCESS] Propiedad: {$property->mls_public_id} | Foto posición {$index}: " . substr($url, 0, 80) . "...");

                    // Verificar si ya existe un MediaAsset para esta URL
                    $existingMediaAsset = MediaAsset::where('url', $url)->first();

                    if ($existingMediaAsset && $existingMediaAsset->storage_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($existingMediaAsset->storage_path)) {
                        // La imagen ya existe localmente, vincular directamente
                        $this->log('info', "[IMAGES LINK] Propiedad: {$property->mls_public_id} | Imagen ya existe localmente, vinculando: " . substr($url, 0, 60) . "...");
                        $this->linkMediaAssetToProperty($property, $existingMediaAsset, $photo, $index);
                        $linked++;
                        $imagesLinkedInThisProperty++;
                    } else {
                        // La imagen no existe localmente, dispatchar job
                        $mediaData = [
                            'url' => $url,
                            'title' => is_array($photo) ? ($photo['title'] ?? null) : null,
                            'position' => $index,
                        ];

                        $this->log('info', "[IMAGES DISPATCH] Propiedad: {$property->mls_public_id} | Dispatching job para: " . substr($url, 0, 60) . "...");
                        DownloadPropertyImageJob::dispatch($property->id, $mediaData);
                        $dispatched++;
                        $imagesDispatchedInThisProperty++;
                        $imagesDispatched[] = [
                            'property_mls_id' => $property->mls_public_id,
                            'url' => $url,
                            'position' => $index,
                        ];
                    }
                }

                // Actualizar el raw_payload con los datos completos y last_synced_at
                $property->update([
                    'raw_payload' => array_merge($property->raw_payload ?: [], $detailData),
                    'last_synced_at' => now(),
                ]);

                // LOG: Resumen por propiedad
                $finalMediaCount = $property->mediaAssets()->count();
                $finalImagesCount = $property->mediaAssets()->wherePivot('role', 'image')->count();
                $this->log('info', "[IMAGES SYNC POST] Propiedad: {$property->mls_public_id} | " .
                    "Procesadas: {$imagesInThisProperty} | " .
                    "Vinculadas: {$imagesLinkedInThisProperty} | " .
                    "Dispatched: {$imagesDispatchedInThisProperty} | " .
                    "Skipped: {$imagesSkippedInThisProperty} | " .
                    "Total imágenes: {$finalImagesCount}");

                $processed++;

                // Rate limiting entre propiedades
                usleep((int) (1000000 / $this->rateLimit));

            } catch (\Throwable $e) {
                $this->log('error', "[IMAGES SYNC ERROR] Error sincronizando imágenes de {$property->mls_public_id}: " . $e->getMessage());
                $errors++;
                $errorsList[] = [
                    'mls_public_id' => $property->mls_public_id,
                    'error' => $e->getMessage(),
                ];
            }
        }

        $this->log('info', "[IMAGES SYNC END] ================================");
        $this->log('info', "[IMAGES SYNC SUMMARY]");
        $this->log('info', "[IMAGES SYNC SUMMARY] Total propiedades procesadas: {$processed}");
        $this->log('info', "[IMAGES SYNC SUMMARY] Imágenes vinculadas directamente: {$linked}");
        $this->log('info', "[IMAGES SYNC SUMMARY] Jobs dispatchados: {$dispatched}");
        $this->log('info', "[IMAGES SYNC SUMMARY] Propiedades sin fotos (skipped): {$skipped}");
        $this->log('info', "[IMAGES SYNC SUMMARY] Fotos sin URL: {$imagesWithoutUrl}");
        $this->log('info', "[IMAGES SYNC SUMMARY] Fotos ya vinculadas (skipped): {$imagesAlreadyLinked}");
        $this->log('info', "[IMAGES SYNC SUMMARY] Errores: {$errors}");
        $this->log('info', "[IMAGES SYNC END] ================================");

        // Log de errores detallados si los hay
        if (!empty($errorsList)) {
            $this->log('error', "[IMAGES SYNC ERRORS LIST]");
            foreach ($errorsList as $error) {
                $this->log('error', "[IMAGES SYNC ERROR] MLS ID: {$error['mls_public_id']} | Error: {$error['error']}");
            }
        }

        return [
            'total_in_db' => $totalMlsProperties,
            'processed' => $processed,
            'linked' => $linked,
            'dispatched' => $dispatched,
            'skipped' => $skipped,
            'errors' => $errors,
            'images_without_url' => $imagesWithoutUrl,
            'images_already_linked' => $imagesAlreadyLinked,
            'images_dispatched' => $imagesDispatched,
            'errors_list' => $errorsList,
            'message' => "{$processed}/{$totalMlsProperties} propiedades procesadas | {$linked} vinculadas | {$dispatched} dispatched | {$errors} errores",
        ];
    }

    /**
     * Sincroniza imágenes de propiedades MLS existentes en modo progresivo.
     * Procesa todas las propiedades en lotes hasta completar la sincronización.
     * Ideal para sincronizaciones largas que no caben en una sola request HTTP.
     *
     * @param int $batchSize Tamaño del lote (default: 50)
     * @param bool $force Forzar re-descarga de imágenes existentes
     * @param int|null $startOffset Offset inicial (si es null, usa el offset basado en last_synced_at)
     * @return array Resultado con estadísticas detalladas y next_offset para continuar
     */
    public function syncImagesProgressive(int $batchSize = 50, bool $force = false, ?int $startOffset = null): array
    {
        $this->log('info', '[IMAGES SYNC PROGRESSIVE START] ================================');
        $this->log('info', "[IMAGES SYNC PROGRESSIVE] Iniciando sincronización progresiva | Batch size: {$batchSize} | Force: " . ($force ? 'SÍ' : 'NO') . " | Offset inicial: " . ($startOffset !== null ? (string) $startOffset : 'AUTO'));

        // Obtener el total de propiedades MLS
        $totalMlsProperties = Property::where('source', 'mls')
            ->whereNotNull('mls_public_id')
            ->count();

        $this->log('info', "[IMAGES SYNC PROGRESSIVE] Total de propiedades MLS en BD: {$totalMlsProperties}");

        if ($totalMlsProperties === 0) {
            return [
                'success' => true,
                'total_in_db' => 0,
                'processed' => 0,
                'linked' => 0,
                'dispatched' => 0,
                'errors' => 0,
                'next_offset' => 0,
                'completed' => true,
                'progress_percentage' => 100,
                'message' => 'No hay propiedades MLS para procesar',
            ];
        }

        // Determinar el offset a usar
        $currentOffset = $startOffset ?? 0;

        // NOTA: No calculamos offset automáticamente basado en last_synced_at porque
        // ese campo se actualiza al sincronizar DATOS, no imágenes. El frontend es responsable
        // de controlar la progresión pasando el offset en cada llamada.
        $this->log('info', "[IMAGES SYNC PROGRESSIVE] Offset a usar: {$currentOffset}");

        // Verificar si ya procesamos todo
        if ($currentOffset >= $totalMlsProperties) {
            $this->log('info', '[IMAGES SYNC PROGRESSIVE] Ya se procesaron todas las propiedades');
            return [
                'success' => true,
                'total_in_db' => $totalMlsProperties,
                'processed' => 0,
                'linked' => 0,
                'dispatched' => 0,
                'errors' => 0,
                'next_offset' => 0,
                'completed' => true,
                'progress_percentage' => 100,
                'message' => 'Sincronización completada',
            ];
        }

        // Procesar un lote
        $result = $this->syncExistingPropertyImages($batchSize, $force, $currentOffset);
        $processedInThisBatch = $result['processed'] ?? 0;

        // Calcular si hay más propiedades por procesar
        $newOffset = $currentOffset + $processedInThisBatch;
        $completed = $newOffset >= $totalMlsProperties || $processedInThisBatch === 0;

        $progressPercentage = $totalMlsProperties > 0 ? round(($newOffset / $totalMlsProperties) * 100, 2) : 100;

        $this->log('info', "[IMAGES SYNC PROGRESSIVE] Lote completado | Procesadas: {$processedInThisBatch} | Offset actual: {$newOffset} | Total: {$totalMlsProperties} | Progreso: {$progressPercentage}% | Completado: " . ($completed ? 'SÍ' : 'NO'));

        return [
            'success' => true,
            'total_in_db' => $totalMlsProperties,
            'processed' => $processedInThisBatch,
            'linked' => $result['linked'] ?? 0,
            'dispatched' => $result['dispatched'] ?? 0,
            'errors' => $result['errors'] ?? 0,
            'next_offset' => $completed ? 0 : $newOffset,
            'completed' => $completed,
            'progress_percentage' => $progressPercentage,
            'message' => $completed
                ? "Sincronización completada: {$newOffset}/{$totalMlsProperties} propiedades"
                : "Procesado lote {$currentOffset}-{$newOffset}/{$totalMlsProperties}. Continúa con offset {$newOffset}",
        ];
    }

    /**
     * Obtiene el progreso actual de la sincronización de imágenes.
     * Útil para mostrar una barra de progreso en el frontend.
     *
     * @return array Información del progreso
     */
    public function getImagesSyncProgress(): array
    {
        $totalProperties = Property::where('source', 'mls')
            ->whereNotNull('mls_public_id')
            ->count();

        if ($totalProperties === 0) {
            return [
                'total' => 0,
                'synced' => 0,
                'pending' => 0,
                'progress_percentage' => 100,
                'last_synced_at' => null,
            ];
        }

        // Contar propiedades sincronizadas recientemente (última hora)
        $syncedRecently = Property::where('source', 'mls')
            ->whereNotNull('mls_public_id')
            ->where('last_synced_at', '>=', now()->subHours(1))
            ->count();

        // Contar propiedades con imágenes locales usando JOIN directo
        $withLocalImages = Property::where('source', 'mls')
            ->whereNotNull('mls_public_id')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('media_assets')
                    ->join('property_media_assets', 'media_assets.id', '=', 'property_media_assets.media_asset_id')
                    ->whereColumn('property_media_assets.property_id', 'properties.id')
                    ->where('property_media_assets.role', 'image')
                    ->whereNotNull('media_assets.storage_path');
            })
            ->count();

        $lastSyncedProperty = Property::where('source', 'mls')
            ->whereNotNull('mls_public_id')
            ->whereNotNull('last_synced_at')
            ->orderBy('last_synced_at', 'desc')
            ->first();

        return [
            'total' => $totalProperties,
            'synced_recently' => $syncedRecently,
            'with_local_images' => $withLocalImages,
            'pending' => $totalProperties - $syncedRecently,
            'progress_percentage' => round(($syncedRecently / $totalProperties) * 100, 2),
            'last_synced_at' => $lastSyncedProperty?->last_synced_at?->toIso8601String(),
            'last_synced_property_id' => $lastSyncedProperty?->id,
        ];
    }

    /**
     * Obtiene las estadísticas de la sincronización.
     */
    public function getStats(): array
    {
        return [
            'created' => $this->created,
            'updated' => $this->updated,
            'unpublished' => $this->unpublished,
            'errors' => $this->errors,
            'total_fetched' => $this->totalFetched,
            'failed_properties_count' => count($this->failedProperties),
            'error_details_count' => count($this->errorDetails),
            'last_successful_mls_id' => $this->lastSuccessfulMlsId,
            'circuit_breaker_open' => $this->circuitBreakerOpen,
            'circuit_breaker_failures' => $this->circuitBreakerFailures,
        ];
    }

    /**
     * Obtiene los detalles de errores de la sincronización.
     */
    public function getErrorDetails(): array
    {
        return $this->errorDetails;
    }

    /**
     * Obtiene la lista de propiedades que fallaron durante la sincronización.
     */
    public function getFailedProperties(): array
    {
        return $this->failedProperties;
    }

    /**
     * Obtiene el estado del circuit breaker.
     */
    public function getCircuitBreakerStatus(): array
    {
        return [
            'open' => $this->circuitBreakerOpen,
            'failures' => $this->circuitBreakerFailures,
            'threshold' => $this->circuitBreakerThreshold,
            'opened_at' => $this->circuitBreakerOpenedAt?->toIso8601String(),
            'timeout_seconds' => $this->circuitBreakerTimeoutSeconds,
            'will_close_at' => $this->circuitBreakerOpenedAt 
                ? $this->circuitBreakerOpenedAt->addSeconds($this->circuitBreakerTimeoutSeconds)->toIso8601String()
                : null,
        ];
    }

    /**
     * Obtiene el estado de configuración del servicio.
     */
    public function getStatus(): array
    {
        $lastSync = null;
        
        if ($this->config) {
            $lastSync = [
                'last_sync_at' => $this->config->last_sync_at?->toIso8601String(),
                'created' => $this->config->last_sync_created,
                'updated' => $this->config->last_sync_updated,
                'unpublished' => $this->config->last_sync_unpublished,
                'errors' => $this->config->last_sync_errors,
                'total_fetched' => $this->config->last_sync_total_fetched,
            ];
        }

        $agencyId = null;
        try {
            $agencyId = $this->getDefaultAgencyId();
        } catch (\Exception $e) {
            // Tabla no existe aún
        }

        $totalProperties = 0;
        $publishedProperties = 0;
        
        if ($agencyId) {
            try {
                $totalProperties = Property::where('agency_id', $agencyId)
                    ->where('source', 'mls')
                    ->count();
                $publishedProperties = Property::where('agency_id', $agencyId)
                    ->where('source', 'mls')
                    ->where('published', true)
                    ->count();
            } catch (\Exception $e) {
                // Tabla no existe aún
            }
        }

        // Obtener checkpoint
        $checkpoint = $this->getLastCheckpoint();

        return [
            'configured' => $this->isConfigured(),
            'config_source' => $this->config ? 'database' : 'env',
            'api_key' => $this->getObfuscatedApiKey(),
            'base_url' => $this->baseUrl,
            'rate_limit' => $this->rateLimit,
            'timeout' => $this->timeout,
            'batch_size' => $this->batchSize,
            'sync_mode' => $this->config?->sync_mode ?? 'incremental',
            'last_sync' => $lastSync ?: [
                'last_sync_at' => null,
                'total_properties' => $totalProperties,
                'published_properties' => $publishedProperties,
            ],
            'total_properties' => $totalProperties,
            'published_properties' => $publishedProperties,
            'circuit_breaker' => $this->getCircuitBreakerStatus(),
            'checkpoint' => $checkpoint,
            'sync_locked' => $this->isLocked,
        ];
    }
}
