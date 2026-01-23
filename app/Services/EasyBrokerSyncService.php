<?php

namespace App\Services;

use App\Models\Agency;
use App\Models\Currency;
use App\Models\EasyBrokerConfig;
use App\Models\EasybrokerPropertyListingStatus;
use App\Models\Feature;
use App\Models\MediaAsset;
use App\Models\Property;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Servicio de sincronización con EasyBroker API.
 *
 * Este servicio maneja la sincronización bidireccional de propiedades
 * entre la base de datos local y la API de EasyBroker.
 *
 * Documentación API EasyBroker:
 * - Listing Statuses: GET /listing_statuses (obtiene estado de publicación)
 * - Properties: GET /properties (obtiene listado de propiedades)
 * - Property Detail: GET /properties/{public_id} (obtiene detalle de una propiedad)
 *
 * Nota importante: En EasyBroker, el estado de publicación se obtiene
 * desde el endpoint listing_statuses, NO desde el endpoint de propiedades.
 * 
 * La configuración se obtiene de la base de datos (tabla easybroker_configs)
 * o del archivo .env como fallback.
 */
class EasyBrokerSyncService
{
    protected ?EasyBrokerConfig $config = null;
    protected string $apiKey = '';
    protected string $baseUrl = 'https://api.easybroker.com/v1';
    protected int $rateLimit = 20;
    protected int $timeout = 30;

    protected array $syncLog = [];
    protected int $created = 0;
    protected int $updated = 0;
    protected int $unpublished = 0;
    protected int $errors = 0;

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
            $this->config = EasyBrokerConfig::getActive();
        } catch (\Exception $e) {
            // Si la tabla no existe todavía, usar config
            $this->config = null;
        }

        if ($this->config && $this->config->isConfigured()) {
            // Usar configuración de la base de datos
            $decryptedKey = $this->config->api_key_decrypted;
            $this->apiKey = is_string($decryptedKey) ? $decryptedKey : '';
            $this->baseUrl = $this->config->base_url ?? 'https://api.easybroker.com/v1';
            $this->rateLimit = $this->config->rate_limit ?? 20;
            $this->timeout = $this->config->timeout ?? 30;
        } else {
            // Fallback a configuración de .env
            $envKey = config('services.easybroker.api_key');
            $this->apiKey = is_string($envKey) ? $envKey : '';
            $envBaseUrl = config('services.easybroker.base_url');
            $this->baseUrl = is_string($envBaseUrl) ? $envBaseUrl : 'https://api.easybroker.com/v1';
            $this->rateLimit = (int) config('services.easybroker.rate_limit', 20);
            $this->timeout = (int) config('services.easybroker.timeout', 30);
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
    public function getConfig(): ?EasyBrokerConfig
    {
        return $this->config;
    }

    /**
     * Ejecuta la sincronización completa.
     *
     * @return array Resultado de la sincronización con estadísticas
     */
    public function sync(): array
    {
        $this->resetCounters();

        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'EasyBroker no está configurado. Configura la API Key desde el panel de administración o en el archivo .env',
                'stats' => $this->getStats(),
            ];
        }

        try {
            $this->log('info', 'Iniciando sincronización con EasyBroker...');
            
            // Paso 1: Obtener listing_statuses para saber qué propiedades existen y están publicadas
            $listingStatuses = $this->fetchAllListingStatuses();

            if ($listingStatuses === null) {
                return [
                    'success' => false,
                    'message' => 'Error al obtener listing_statuses de EasyBroker',
                    'stats' => $this->getStats(),
                ];
            }

            $this->log('info', 'Se obtuvieron ' . count($listingStatuses) . ' listing statuses');

            // Paso 2: Actualizar tabla easybroker_property_listing_statuses
            $this->updateListingStatuses($listingStatuses);

            // Paso 3: Identificar propiedades que necesitan sincronización
            $propertiesToSync = $this->getPropertiesToSync($listingStatuses);

            $this->log('info', 'Propiedades a sincronizar: ' . count($propertiesToSync));

            // Paso 4: Sincronizar cada propiedad
            foreach ($propertiesToSync as $publicId) {
                $this->syncProperty($publicId);
                // Rate limiting: esperar para no exceder el límite
                usleep((int) (1000000 / $this->rateLimit));
            }

            // Paso 5: Despublicar propiedades que ya no están en EasyBroker
            $this->unpublishRemovedProperties($listingStatuses);

            $this->log('info', 'Sincronización completada');

            // Guardar resultado en la configuración
            if ($this->config) {
                $this->config->recordSyncResult($this->getStats());
            }

            return [
                'success' => true,
                'message' => 'Sincronización completada exitosamente',
                'stats' => $this->getStats(),
                'log' => $this->syncLog,
            ];

        } catch (\Throwable $e) {
            Log::error('EasyBroker sync error: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return [
                'success' => false,
                'message' => 'Error durante la sincronización: ' . $e->getMessage(),
                'stats' => $this->getStats(),
                'log' => $this->syncLog,
            ];
        }
    }

    /**
     * Obtiene todos los listing statuses de EasyBroker (paginados).
     *
     * @return array|null
     */
    protected function fetchAllListingStatuses(): ?array
    {
        $allStatuses = [];
        $page = 1;
        $hasMore = true;

        while ($hasMore) {
            $response = $this->makeRequest('GET', '/listing_statuses', [
                'page' => $page,
                'limit' => 50,
            ]);

            if ($response === null) {
                return null;
            }

            $content = $response['content'] ?? [];
            $pagination = $response['pagination'] ?? [];

            $allStatuses = array_merge($allStatuses, $content);

            $nextPage = $pagination['next_page'] ?? null;
            $hasMore = $nextPage !== null;
            $page++;

            // Rate limiting
            usleep((int) (1000000 / $this->rateLimit));
        }

        return $allStatuses;
    }

    /**
     * Actualiza la tabla de listing statuses local.
     *
     * Nota: La API de EasyBroker devuelve el campo "status" con valores como "published" o "not_published",
     * en lugar de un booleano "published".
     */
    protected function updateListingStatuses(array $statuses): void
    {
        $agencyId = $this->getDefaultAgencyId();

        foreach ($statuses as $status) {
            $publicId = $status['public_id'] ?? null;
            if (!$publicId) {
                continue;
            }

            // Determinar si está publicado basándose en el campo "status" o "published"
            $isPublished = false;
            if (isset($status['published'])) {
                $isPublished = (bool) $status['published'];
            } elseif (isset($status['status'])) {
                $isPublished = $status['status'] === 'published';
            }

            $ebUpdatedAt = isset($status['updated_at'])
                ? \Carbon\Carbon::parse($status['updated_at'])
                : now();

            // Usar el método personalizado para tablas sin columna `id`
            EasybrokerPropertyListingStatus::updateOrCreateByKey(
                $agencyId,
                $publicId,
                [
                    'published' => $isPublished,
                    'easybroker_updated_at' => $ebUpdatedAt,
                    'last_polled_at' => now(),
                    'raw_payload' => $status,
                ]
            );
        }
    }

    /**
     * Obtiene el ID de agencia por defecto (crea una si no existe).
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
     * Determina qué propiedades necesitan sincronización.
     *
     * Una propiedad necesita sincronización si:
     * - Es nueva (no existe en properties)
     * - Ha sido actualizada (updated_at de EasyBroker > last_synced_at local)
     */
    protected function getPropertiesToSync(array $listingStatuses): array
    {
        $propertiesToSync = [];
        $agencyId = $this->getDefaultAgencyId();

        foreach ($listingStatuses as $status) {
            $publicId = $status['public_id'] ?? null;
            if (!$publicId) {
                continue;
            }

            // Solo sincronizar propiedades publicadas
            // La API puede devolver "published" (booleano) o "status" (string)
            $isPublished = false;
            if (isset($status['published'])) {
                $isPublished = (bool) $status['published'];
            } elseif (isset($status['status'])) {
                $isPublished = $status['status'] === 'published';
            }

            if (!$isPublished) {
                continue;
            }

            $existingProperty = Property::where('agency_id', $agencyId)
                ->where('easybroker_public_id', $publicId)
                ->first();

            if (!$existingProperty) {
                // Nueva propiedad
                $propertiesToSync[] = $publicId;
                continue;
            }

            // Verificar si necesita actualización
            $ebUpdatedAt = isset($status['updated_at'])
                ? \Carbon\Carbon::parse($status['updated_at'])
                : null;
            $lastSynced = $existingProperty->last_synced_at;

            if ($ebUpdatedAt && (!$lastSynced || $ebUpdatedAt->gt($lastSynced))) {
                $propertiesToSync[] = $publicId;
            }
        }

        return $propertiesToSync;
    }

    /**
     * Sincroniza una propiedad individual desde EasyBroker.
     */
    protected function syncProperty(string $publicId): void
    {
        $this->log('debug', "Sincronizando propiedad: {$publicId}");

        $propertyData = $this->fetchPropertyDetail($publicId);

        if ($propertyData === null) {
            $this->log('error', "Error al obtener detalle de propiedad: {$publicId}");
            $this->errors++;
            return;
        }

        try {
            DB::transaction(function () use ($publicId, $propertyData) {
                $agencyId = $this->getDefaultAgencyId();
                
                $existingProperty = Property::where('agency_id', $agencyId)
                    ->where('easybroker_public_id', $publicId)
                    ->first();

                $isNew = $existingProperty === null;

                // Preparar datos de la propiedad
                $propertyAttributes = $this->mapPropertyData($propertyData);

                if ($isNew) {
                    $property = Property::create($propertyAttributes);
                    $this->created++;
                    $this->log('info', "Propiedad creada: {$publicId}");
                } else {
                    $existingProperty->update($propertyAttributes);
                    $property = $existingProperty;
                    $this->updated++;
                    $this->log('info', "Propiedad actualizada: {$publicId}");
                }

                // Sincronizar relaciones
                $this->syncPropertyLocation($property, $propertyData);
                $this->syncPropertyOperations($property, $propertyData);
                $this->syncPropertyFeatures($property, $propertyData);
                $this->syncPropertyTags($property, $propertyData);
                $this->syncPropertyMedia($property, $propertyData);

                // Vincular con listing status
                EasybrokerPropertyListingStatus::where('agency_id', $agencyId)
                    ->where('easybroker_public_id', $publicId)
                    ->update(['property_id' => $property->id]);
            });
        } catch (\Throwable $e) {
            $this->log('error', "Error al sincronizar propiedad {$publicId}: " . $e->getMessage());
            $this->errors++;
        }
    }

    /**
     * Obtiene el detalle de una propiedad de EasyBroker.
     */
    protected function fetchPropertyDetail(string $publicId): ?array
    {
        return $this->makeRequest('GET', "/properties/{$publicId}");
    }

    /**
     * Mapea los datos de EasyBroker al formato de la tabla properties.
     */
    protected function mapPropertyData(array $data): array
    {
        // Extraer el ID del agente (puede ser un objeto con 'id' o directamente un ID)
        $agentId = null;
        if (isset($data['agent'])) {
            if (is_array($data['agent']) && isset($data['agent']['id'])) {
                $agentId = (string) $data['agent']['id'];
            } elseif (is_scalar($data['agent'])) {
                $agentId = (string) $data['agent'];
            }
        }

        return [
            'agency_id' => $this->getDefaultAgencyId(),
            'easybroker_public_id' => $data['public_id'] ?? '',
            'easybroker_agent_id' => $agentId,
            'published' => true, // Si llegamos aquí, está publicada
            'easybroker_created_at' => isset($data['created_at'])
                ? \Carbon\Carbon::parse($data['created_at'])
                : null,
            'easybroker_updated_at' => isset($data['updated_at'])
                ? \Carbon\Carbon::parse($data['updated_at'])
                : null,
            'last_synced_at' => now(),
            'title' => $data['title'] ?? null,
            'description' => $data['description'] ?? null,
            'url' => $data['public_url'] ?? null,
            'ad_type' => $data['ad_type'] ?? null,
            'property_type_name' => $data['property_type'] ?? null,
            'bedrooms' => $this->parseNumeric($data['bedrooms'] ?? null),
            'bathrooms' => $this->parseNumeric($data['bathrooms'] ?? null),
            'half_bathrooms' => $this->parseNumeric($data['half_bathrooms'] ?? null),
            'parking_spaces' => $this->parseNumeric($data['parking_spaces'] ?? null),
            'lot_size' => $this->parseDecimal($data['lot_size'] ?? null),
            'construction_size' => $this->parseDecimal($data['construction_size'] ?? null),
            'expenses' => $this->parseDecimal($data['expenses'] ?? null),
            'lot_length' => $this->parseDecimal($data['lot_length'] ?? null),
            'lot_width' => $this->parseDecimal($data['lot_width'] ?? null),
            'floors' => $this->parseNumeric($data['floors'] ?? null),
            'floor' => $data['floor'] ?? null, // Puede ser string como "6" o "PB"
            'age' => $data['age'] ?? null, // Puede ser string como "new_construction"
            'virtual_tour_url' => $data['virtual_tour'] ?? null,
            'raw_payload' => $data, // Laravel lo convierte a JSON automáticamente por el cast 'array'
        ];
    }

    /**
     * Parsea un valor a decimal, extrayendo solo la parte numérica.
     * Maneja casos como "1312.19 marzo" → 1312.19
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
            // Extraer el primer número decimal del string
            if (preg_match('/[\d.,]+/', $value, $matches)) {
                // Convertir formato según separadores
                $number = str_replace(',', '.', $matches[0]);
                // Si hay múltiples puntos, eliminar los de miles
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
     * Parsea un valor a entero, extrayendo solo la parte numérica.
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
            // Extraer el primer número del string
            if (preg_match('/\d+/', $value, $matches)) {
                return (int) $matches[0];
            }
        }

        return null;
    }

    /**
     * Sincroniza la ubicación de la propiedad.
     */
    protected function syncPropertyLocation(Property $property, array $data): void
    {
        $location = $data['location'] ?? [];

        if (empty($location)) {
            return;
        }

        $property->location()->updateOrCreate(
            ['property_id' => $property->id],
            [
                'region' => $location['state'] ?? null,
                'city' => $location['city'] ?? null,
                'city_area' => $location['neighborhood'] ?? null,
                'street' => $location['street'] ?? null,
                'postal_code' => $location['postal_code'] ?? null,
                'show_exact_location' => $location['show_exact_location'] ?? null,
                'latitude' => $location['latitude'] ?? null,
                'longitude' => $location['longitude'] ?? null,
                'raw_payload' => $location,
            ]
        );
    }

    /**
     * Sincroniza las operaciones (venta/renta) de la propiedad.
     */
    protected function syncPropertyOperations(Property $property, array $data): void
    {
        $operations = $data['operations'] ?? [];

        if (empty($operations)) {
            return;
        }

        // Eliminar operaciones existentes
        $property->operations()->delete();

        foreach ($operations as $op) {
            $currencyCode = $op['currency'] ?? null;
            $currency = $currencyCode
                ? Currency::where('code', $currencyCode)->first()
                : null;

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
    }

    /**
     * Sincroniza las características de la propiedad.
     * 
     * La API de EasyBroker devuelve las características en el campo 'features'
     * con la estructura: [{ "name": "Piscina", "category": "Recreación" }, ...]
     */
    protected function syncPropertyFeatures(Property $property, array $data): void
    {
        // EasyBroker usa 'features' (no 'property_features')
        $features = $data['features'] ?? [];

        if (empty($features)) {
            $property->features()->sync([]);
            return;
        }

        $featureIds = [];

        foreach ($features as $featureData) {
            // Las características de EasyBroker son objetos con 'name' y 'category'
            $featureName = null;
            $featureCategory = null;

            if (is_array($featureData)) {
                // Estructura: { "name": "Piscina", "category": "Recreación" }
                $featureName = $featureData['name'] ?? null;
                $featureCategory = $featureData['category'] ?? null;
            } elseif (is_string($featureData)) {
                // Fallback: si viene como string simple
                $featureName = $featureData;
            }

            if (empty($featureName)) {
                continue;
            }

            // Buscar o crear la característica con nombre y categoría
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
    }

    /**
     * Sincroniza los tags de la propiedad.
     */
    protected function syncPropertyTags(Property $property, array $data): void
    {
        $tags = $data['tags'] ?? [];

        if (empty($tags)) {
            $property->tags()->sync([]);
            return;
        }

        $tagIds = [];

        foreach ($tags as $tagName) {
            if (empty($tagName)) {
                continue;
            }

            $tag = Tag::firstOrCreate(
                ['name' => $tagName],
                [
                    'slug' => \Illuminate\Support\Str::slug($tagName),
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );

            $tagIds[] = $tag->id;
        }

        $property->tags()->sync($tagIds);
    }

    /**
     * Sincroniza los medios (imágenes/videos) de la propiedad.
     */
    protected function syncPropertyMedia(Property $property, array $data): void
    {
        $images = $data['images'] ?? [];
        $videos = $data['videos'] ?? [];

        $mediaSync = [];
        $position = 0;
        $coverSet = false;

        // Procesar imágenes
        foreach ($images as $image) {
            $url = $image['url'] ?? null;
            if (!$url) {
                continue;
            }

            $mediaAsset = $this->getOrCreateMediaAsset($url, 'image', $image);

            $mediaSync[$mediaAsset->id] = [
                'role' => 'image',
                'title' => $image['title'] ?? null,
                'position' => $position,
                'source_url' => $url,
                'raw_payload' => json_encode($image),
            ];

            // Primera imagen como portada
            if (!$coverSet) {
                $property->update(['cover_media_asset_id' => $mediaAsset->id]);
                $coverSet = true;
            }

            $position++;
        }

        // Procesar videos
        foreach ($videos as $video) {
            $url = $video['url'] ?? null;
            if (!$url) {
                continue;
            }

            $mediaAsset = $this->getOrCreateMediaAsset($url, 'video', $video);

            $mediaSync[$mediaAsset->id] = [
                'role' => 'video',
                'title' => $video['title'] ?? null,
                'position' => $position,
                'source_url' => $url,
                'raw_payload' => json_encode($video),
            ];

            $position++;
        }

        $property->mediaAssets()->sync($mediaSync);
    }

    /**
     * Obtiene o crea un MediaAsset para una URL.
     */
    protected function getOrCreateMediaAsset(string $url, string $type, array $metadata): MediaAsset
    {
        // Buscar por URL existente
        $existing = MediaAsset::where('url', $url)->first();
        if ($existing) {
            return $existing;
        }

        // Crear nuevo
        return MediaAsset::create([
            'type' => $type,
            'provider' => 'easybroker',
            'url' => $url,
            'name' => $metadata['title'] ?? basename(parse_url($url, PHP_URL_PATH)),
            'alt' => $metadata['title'] ?? null,
        ]);
    }

    /**
     * Despublica propiedades que ya no están publicadas en EasyBroker.
     */
    protected function unpublishRemovedProperties(array $listingStatuses): void
    {
        $agencyId = $this->getDefaultAgencyId();
        
        // Obtener IDs públicos de propiedades publicadas en EasyBroker
        // La API puede devolver "published" (booleano) o "status" (string)
        $publishedIds = collect($listingStatuses)
            ->filter(function ($s) {
                if (isset($s['published'])) {
                    return (bool) $s['published'];
                }
                if (isset($s['status'])) {
                    return $s['status'] === 'published';
                }
                return false;
            })
            ->pluck('public_id')
            ->toArray();

        // Despublicar propiedades locales que ya no están publicadas en EasyBroker
        // Solo afecta propiedades que tienen easybroker_public_id (provienen de EasyBroker)
        $unpublished = Property::where('agency_id', $agencyId)
            ->where('published', true)
            ->whereNotNull('easybroker_public_id')
            ->where('easybroker_public_id', '!=', '')
            ->whereNotIn('easybroker_public_id', $publishedIds)
            ->update(['published' => false]);

        $this->unpublished = $unpublished;

        if ($unpublished > 0) {
            $this->log('info', "Propiedades despublicadas: {$unpublished}");
        }
    }

    /**
     * Realiza una petición HTTP a la API de EasyBroker.
     */
    protected function makeRequest(string $method, string $endpoint, array $query = []): ?array
    {
        try {
            $response = Http::withHeaders([
                'X-Authorization' => $this->apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
                ->timeout($this->timeout)
                ->$method($this->baseUrl . $endpoint, $query);

            if ($response->failed()) {
                $this->log('error', "API Error [{$response->status()}]: " . $response->body());
                return null;
            }

            return $response->json();

        } catch (\Throwable $e) {
            $this->log('error', "HTTP Error: " . $e->getMessage());
            return null;
        }
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

        Log::log($level, "[EasyBrokerSync] {$message}");
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
                    ->whereNotNull('easybroker_public_id')
                    ->count();
                $publishedProperties = Property::where('agency_id', $agencyId)
                    ->whereNotNull('easybroker_public_id')
                    ->where('published', true)
                    ->count();
            } catch (\Exception $e) {
                // Tabla no existe aún
            }
        }

        return [
            'configured' => $this->isConfigured(),
            'config_source' => $this->config ? 'database' : 'env',
            'api_key' => $this->getObfuscatedApiKey(),
            'base_url' => $this->baseUrl,
            'rate_limit' => $this->rateLimit,
            'timeout' => $this->timeout,
            'last_sync' => $lastSync ?: [
                'last_sync_at' => null,
                'total_properties' => $totalProperties,
                'published_properties' => $publishedProperties,
            ],
            'total_properties' => $totalProperties,
            'published_properties' => $publishedProperties,
        ];
    }
}
