<?php

namespace App\Services;

use App\Models\EasyBrokerConfig;
use App\Models\Property;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EasyBrokerMlsExportService
{
    protected ?EasyBrokerConfig $config = null;

    protected string $apiKey = '';

    protected string $baseUrl = 'https://api.easybroker.com/v1';

    protected int $timeout = 30;

    protected int $rateLimit = 20;

    /**
     * Cache en memoria para consultas de ubicación dentro del mismo request.
     *
     * @var array<string, array<string, mixed>|null>
     */
    protected array $locationQueryCache = [];

    /**
     * Cache de validación de URLs de imágenes para evitar múltiples HEAD/GET.
     *
     * @var array<string, bool>
     */
    protected array $imageUrlValidationCache = [];

    public function __construct()
    {
        $this->reloadConfiguration();
    }

    public function reloadConfiguration(): void
    {
        try {
            $this->config = EasyBrokerConfig::getActive();
        } catch (\Throwable $e) {
            $this->config = null;
        }

        if ($this->config && $this->config->isConfigured()) {
            $decryptedKey = $this->config->api_key_decrypted;
            $this->apiKey = is_string($decryptedKey) ? $decryptedKey : '';
            $this->baseUrl = $this->config->base_url ?? 'https://api.easybroker.com/v1';
            $this->timeout = $this->config->timeout ?? 30;
            $this->rateLimit = $this->config->rate_limit ?? 20;

            return;
        }

        $envKey = config('services.easybroker.api_key');
        $this->apiKey = is_string($envKey) ? $envKey : '';

        $envBaseUrl = config('services.easybroker.base_url');
        $this->baseUrl = is_string($envBaseUrl) ? $envBaseUrl : 'https://api.easybroker.com/v1';

        $this->timeout = (int) config('services.easybroker.timeout', 30);
        $this->rateLimit = (int) config('services.easybroker.rate_limit', 20);
    }

    public function isConfigured(): bool
    {
        return trim($this->apiKey) !== '';
    }

    /**
     * Obtiene tipos de propiedad disponibles en EasyBroker.
     */
    public function fetchPropertyTypes(): array
    {
        $response = $this->makeRequest('GET', '/property_types');

        if (!$response['ok']) {
            return [];
        }

        $body = $response['body'];
        $items = [];

        if (is_array($body)) {
            if (array_is_list($body)) {
                $items = $body;
            } else {
                $items = $body['content'] ?? $body['data'] ?? [];
            }
        }

        $types = [];
        foreach ($items as $item) {
            if (is_string($item) && trim($item) !== '') {
                $types[] = trim($item);
                continue;
            }

            if (is_array($item)) {
                $candidate = $item['name'] ?? $item['title'] ?? $item['property_type'] ?? null;
                if (is_string($candidate) && trim($candidate) !== '') {
                    $types[] = trim($candidate);
                }
            }
        }

        $types = array_values(array_unique($types));
        sort($types, SORT_NATURAL | SORT_FLAG_CASE);

        return $types;
    }

    /**
     * Construye el payload a enviar a EasyBroker desde una propiedad MLS local.
     */
    public function buildDraftPayload(
        Property $property,
        ?string $fallbackPropertyType = null,
        ?array $allowedPropertyTypes = null,
        ?string $targetStatus = null
    ): array {
        $rawPayload = is_array($property->raw_payload) ? $property->raw_payload : [];

        $location = $property->relationLoaded('location')
            ? $property->location
            : $property->location()->first();

        $operations = $property->relationLoaded('operations')
            ? $property->operations
            : $property->operations()->with('currency')->get();

        $tags = $property->relationLoaded('tags')
            ? $property->tags
            : $property->tags()->get();

        $mediaAssets = $property->relationLoaded('mediaAssets')
            ? $property->mediaAssets
            : $property->mediaAssets()->get();

        $title = $this->firstNonEmpty([
            $property->title,
            $rawPayload['name'] ?? null,
            $rawPayload['title'] ?? null,
        ]);

        $description = $this->firstNonEmpty([
            $property->description,
            $property->description_full_es,
            $property->description_short_es,
            $property->description_full_en,
            $property->description_short_en,
            $rawPayload['description'] ?? null,
            $rawPayload['description_full_es'] ?? null,
            $rawPayload['description_short_es'] ?? null,
            $rawPayload['description_full_en'] ?? null,
            $rawPayload['description_short_en'] ?? null,
        ]);

        if ($description !== null) {
            $description = mb_substr($description, 0, 4000);
        }

        $street = $this->firstNonEmpty([
            $location?->street,
            $rawPayload['street'] ?? null,
            $rawPayload['address'] ?? null,
            $rawPayload['location']['street'] ?? null,
            $rawPayload['location']['address'] ?? null,
        ]);

        $locationName = $this->resolveLocationName($property, $rawPayload, $location);
        $propertyType = $this->resolvePropertyType($property, $rawPayload, $fallbackPropertyType, $allowedPropertyTypes);

        $targetStatus = in_array($targetStatus, ['published', 'not_published'], true)
            ? $targetStatus
            : 'not_published';

        $operationsPayload = $this->buildOperationsPayload($property, $rawPayload, $operations);

        $payload = [
            'property_type' => $propertyType,
            'operations' => $operationsPayload,
            'title' => $title,
            'description' => $description,
            'status' => $targetStatus,
            'location' => [
                'name' => $locationName,
                'street' => $street,
            ],
        ];

        // Campos opcionales frecuentes (solo enviar si tienen valor)
        $optional = [
            'bedrooms' => $this->asInt($property->bedrooms),
            'bathrooms' => $this->asFloat($property->bathrooms),
            'half_bathrooms' => $this->asInt($property->half_bathrooms),
            'parking_spaces' => $this->asInt($property->parking_spaces),
            'lot_size' => $this->asFloat($property->lot_size),
            'construction_size' => $this->asFloat($property->construction_size),
            'expenses' => $this->asFloat($property->expenses),
            'floors' => $this->asInt($property->floors),
            'age' => $this->firstNonEmpty([$property->age]),
            'virtual_tour' => $this->firstNonEmpty([$property->virtual_tour_url]),
        ];

        foreach ($optional as $key => $value) {
            if ($value !== null && $value !== '') {
                $payload[$key] = $value;
            }
        }

        if ($location?->latitude !== null && $location?->longitude !== null) {
            $payload['location']['latitude'] = $this->asFloat($location->latitude);
            $payload['location']['longitude'] = $this->asFloat($location->longitude);
        }

        $tagNames = collect($tags)
            ->map(fn ($tag) => is_string($tag->name ?? null) ? trim($tag->name) : null)
            ->filter()
            ->values()
            ->all();

        if (!empty($tagNames)) {
            $payload['tags'] = array_values(array_unique($tagNames));
        }

        $imagesPayload = $this->buildImagesPayload($property, $rawPayload, $mediaAssets);
        if (!empty($imagesPayload)) {
            $payload['images'] = $imagesPayload;
        }

        $missing = [];
        if ($this->isBlank($payload['property_type'] ?? null)) {
            $missing[] = 'property_type';
        }
        if (empty($payload['operations'])) {
            $missing[] = 'operations';
        }
        if ($this->isBlank($payload['title'] ?? null)) {
            $missing[] = 'title';
        }
        if ($this->isBlank($payload['description'] ?? null)) {
            $missing[] = 'description';
        }
        if ($this->isBlank($payload['status'] ?? null)) {
            $missing[] = 'status';
        }
        if ($this->isBlank($payload['location']['street'] ?? null)) {
            $missing[] = 'street';
        }
        if ($this->isBlank($payload['location']['name'] ?? null)) {
            $missing[] = 'location';
        }

        return [
            'payload' => $payload,
            'missing_required' => $missing,
            'resolved' => [
                'property_type' => $propertyType,
                'street' => $street,
                'location_name' => $locationName,
                'operations_count' => count($operationsPayload),
                'images_count' => count($imagesPayload),
            ],
        ];
    }

    /**
     * Crea/actualiza una propiedad en EasyBroker a partir de una propiedad MLS local.
     */
    public function pushProperty(Property $property, array $options = []): array
    {
        $draft = $this->buildDraftPayload(
            $property,
            $options['fallback_property_type'] ?? null,
            $options['allowed_property_types'] ?? null,
            $options['target_status'] ?? 'not_published'
        );

        if (!empty($draft['missing_required'])) {
            return [
                'success' => false,
                'action' => 'skipped',
                'property_id' => $property->id,
                'easybroker_public_id' => $property->easybroker_public_id,
                'missing_required' => $draft['missing_required'],
                'message' => 'Faltan campos obligatorios para crear/actualizar en EasyBroker.',
                'request_payload' => $draft['payload'],
            ];
        }

        $payload = $draft['payload'];
        $createIfMissing = (bool) ($options['create_if_missing_on_404'] ?? true);

        $action = 'created';
        $response = null;
        $requestMethod = 'POST';
        $requestEndpoint = '/properties';

        if (!empty($property->easybroker_public_id)) {
            $action = 'updated';
            $requestMethod = 'PATCH';
            $requestEndpoint = '/properties/' . $property->easybroker_public_id;
            $response = $this->makeRequest($requestMethod, $requestEndpoint, $payload);

            if (!$response['ok'] && $response['status'] === 404 && $createIfMissing) {
                $action = 'created';
                $requestMethod = 'POST';
                $requestEndpoint = '/properties';
                $response = $this->makeRequest($requestMethod, $requestEndpoint, $payload);
            }
        } else {
            $response = $this->makeRequest($requestMethod, $requestEndpoint, $payload);
        }

        // Si la API rechaza ubicación, intentar resolver nombre válido de colonia/ciudad y reintentar una sola vez.
        if (
            !$response['ok']
            && $response['status'] === 422
            && $this->shouldRetryLocationWithCatalog($response['body'])
        ) {
            $retryPayload = $this->buildLocationFallbackPayload($payload, $property);
            if ($retryPayload !== null) {
                $retryResponse = $this->makeRequest($requestMethod, $requestEndpoint, $retryPayload);
                $payload = $retryPayload;
                $response = $retryResponse;
            }
        }

        if (!$response['ok']) {
            return [
                'success' => false,
                'action' => $action,
                'property_id' => $property->id,
                'easybroker_public_id' => $property->easybroker_public_id,
                'message' => $this->extractErrorMessage($response['body'], $response['raw_body']),
                'status' => $response['status'],
                'api_response' => $response['body'],
                'request_payload' => $payload,
            ];
        }

        $body = $response['body'];
        $remotePublicId = $body['public_id'] ?? $body['property']['public_id'] ?? $property->easybroker_public_id;
        $remoteAgentId = $body['agent']['id'] ?? $body['agent_id'] ?? $body['property']['agent']['id'] ?? null;

        $updatedAt = $this->parseDate(
            $body['updated_at'] ?? $body['property']['updated_at'] ?? null
        ) ?? now();

        $createdAt = $this->parseDate(
            $body['created_at'] ?? $body['property']['created_at'] ?? null
        );

        $updates = [
            'last_synced_at' => now(),
            'easybroker_updated_at' => $updatedAt,
        ];

        if (!empty($remotePublicId) && is_string($remotePublicId)) {
            $updates['easybroker_public_id'] = $remotePublicId;
        }

        if (!empty($remoteAgentId)) {
            $updates['easybroker_agent_id'] = (string) $remoteAgentId;
        }

        if ($createdAt !== null) {
            $updates['easybroker_created_at'] = $createdAt;
        }

        $property->update($updates);

        // Rate limiting mínimo entre peticiones de lote.
        if ($this->rateLimit > 0) {
            usleep((int) (1000000 / $this->rateLimit));
        }

        return [
            'success' => true,
            'action' => $action,
            'property_id' => $property->id,
            'easybroker_public_id' => $property->fresh()->easybroker_public_id,
            'message' => $action === 'created'
                ? 'Propiedad creada en EasyBroker.'
                : 'Propiedad actualizada en EasyBroker.',
            'api_response' => $body,
            'request_payload' => $payload,
        ];
    }

    protected function resolvePropertyType(
        Property $property,
        array $rawPayload,
        ?string $fallbackPropertyType,
        ?array $allowedPropertyTypes = null
    ): ?string {
        $categoryMap = [
            'residential' => ['Casa', 'House', 'Apartment', 'Villa'],
            'land and lots' => ['Terreno', 'Lot', 'Commercial Lot', 'Industrial Lot', 'Orchard', 'Ranch'],
            'commercial' => ['Local Comercial', 'Retail Space', 'Office', 'Commercial Lot', 'Shopping Mall Space'],
            'pre sales' => ['Casa', 'House'],
        ];

        $category = $this->firstNonEmpty([
            $property->category,
            $rawPayload['category'] ?? null,
        ]);

        $mappedCategoryTypes = [];
        if ($category !== null) {
            $normalizedCategory = $this->normalizeString($category);
            $mappedCategoryTypes = $categoryMap[$normalizedCategory] ?? [];
        }

        $baseCandidates = array_values(array_filter([
            $property->property_type_name,
            $rawPayload['property_type'] ?? null,
            $rawPayload['property_type_name'] ?? null,
            $property->category,
            $rawPayload['category'] ?? null,
            $fallbackPropertyType,
        ], fn ($value) => is_string($value) && trim($value) !== ''));

        $candidates = array_values(array_unique(array_merge(
            $baseCandidates,
            $mappedCategoryTypes
        )));

        if (empty($candidates)) {
            return null;
        }

        if (empty($allowedPropertyTypes)) {
            foreach ($candidates as $candidate) {
                $aliases = $this->propertyTypeAliases($this->normalizeString($candidate));
                foreach ($aliases as $alias) {
                    if (is_string($alias) && trim($alias) !== '') {
                        return trim($alias);
                    }
                }
            }

            return trim($candidates[0]);
        }

        $allowedMap = [];
        foreach ($allowedPropertyTypes as $type) {
            if (!is_string($type) || trim($type) === '') {
                continue;
            }
            $allowedMap[$this->normalizeString($type)] = trim($type);
        }

        foreach ($candidates as $candidate) {
            $normalized = $this->normalizeString($candidate);
            if (isset($allowedMap[$normalized])) {
                return $allowedMap[$normalized];
            }
        }

        // Segunda pasada: equivalencias comunes (es/en) para cuentas con catálogos en otro idioma.
        foreach ($candidates as $candidate) {
            $aliases = $this->propertyTypeAliases($this->normalizeString($candidate));
            foreach ($aliases as $alias) {
                $normalizedAlias = $this->normalizeString($alias);
                if (isset($allowedMap[$normalizedAlias])) {
                    return $allowedMap[$normalizedAlias];
                }
            }
        }

        return null;
    }

    /**
     * Equivalencias de tipos entre catálogos en español/inglés.
     *
     * @return array<int, string>
     */
    protected function propertyTypeAliases(string $normalizedType): array
    {
        $aliases = [
            'land and lots' => ['Lot', 'Terreno', 'Commercial Lot', 'Industrial Lot', 'Orchard', 'Ranch'],
            'land lots' => ['Lot', 'Terreno', 'Commercial Lot', 'Industrial Lot', 'Orchard', 'Ranch'],
            'lot' => ['Lot', 'Terreno', 'Commercial Lot', 'Industrial Lot'],
            'lots' => ['Lot', 'Terreno', 'Commercial Lot', 'Industrial Lot'],
            'terreno' => ['Terreno', 'Lot', 'Commercial Lot', 'Industrial Lot'],
            'residential' => ['House', 'Casa', 'Apartment', 'Villa'],
            'house' => ['House', 'Casa'],
            'casa' => ['Casa', 'House'],
            'commercial' => ['Retail Space', 'Local Comercial', 'Office', 'Commercial Lot', 'Shopping Mall Space'],
            'local comercial' => ['Local Comercial', 'Retail Space', 'Shopping Mall Space'],
            'retail space' => ['Retail Space', 'Local Comercial', 'Shopping Mall Space'],
            'office' => ['Office', 'Oficina'],
            'oficina' => ['Oficina', 'Office'],
        ];

        return $aliases[$normalizedType] ?? [];
    }

    protected function resolveLocationName(Property $property, array $rawPayload, mixed $location): ?string
    {
        $directName = $this->firstNonEmpty([
            $rawPayload['location']['name'] ?? null,
            $rawPayload['location']['full_name'] ?? null,
        ]);

        if ($directName !== null) {
            return $directName;
        }

        $neighborhood = $this->firstNonEmpty([
            $location?->city_area,
            $property->mls_neighborhood,
            $rawPayload['neighborhood'] ?? null,
            $rawPayload['location']['neighborhood'] ?? null,
            $rawPayload['location']['city_area'] ?? null,
        ]);

        $city = $this->firstNonEmpty([
            $location?->city,
            $rawPayload['city'] ?? null,
            $rawPayload['location']['city'] ?? null,
        ]);

        $state = $this->firstNonEmpty([
            $location?->region,
            $rawPayload['state_province'] ?? null,
            $rawPayload['state'] ?? null,
            $rawPayload['location']['state'] ?? null,
        ]);

        $parts = array_values(array_filter([$neighborhood, $city, $state], fn ($v) => is_string($v) && trim($v) !== ''));

        if (!empty($parts)) {
            return implode(', ', $parts);
        }

        return $this->firstNonEmpty([
            $city,
            $state,
        ]);
    }

    /**
     * Construye el arreglo `images` para EasyBroker a partir de media_assets/pivot.
     * Solo incluye URLs válidas para evitar rechazos 422.
     *
     * @return array<int, array{url: string, title?: string}>
     */
    protected function buildImagesPayload(Property $property, array $rawPayload, Collection $mediaAssets): array
    {
        $images = [];
        $seen = [];

        $coverId = $this->asInt($property->cover_media_asset_id);

        $orderedMedia = $mediaAssets
            ->filter(function ($asset) {
                $role = strtolower(trim((string) ($asset->pivot?->role ?? '')));
                return $role === '' || $role === 'image';
            })
            ->sortBy(function ($asset) use ($coverId) {
                $isCover = $coverId !== null && $this->asInt($asset->id) === $coverId ? 0 : 1;
                $position = $this->asInt($asset->pivot?->position) ?? 9999;

                return "{$isCover}-" . str_pad((string) $position, 6, '0', STR_PAD_LEFT);
            })
            ->values();

        foreach ($orderedMedia as $asset) {
            $url = $this->resolveMediaAssetImageUrl($asset);
            if ($url === null) {
                continue;
            }

            $dedupKey = $this->normalizeUrlKey($url);
            if (isset($seen[$dedupKey])) {
                continue;
            }

            $seen[$dedupKey] = true;

            $title = $this->sanitizeImageTitle($this->firstNonEmpty([
                $asset->pivot?->title ?? null,
                $asset->alt ?? null,
                $asset->name ?? null,
            ]));

            $item = ['url' => $url];
            if ($title !== null) {
                $item['title'] = $title;
            }

            $images[] = $item;
            if (count($images) >= 50) {
                return $images;
            }
        }

        // Fallback: usar imágenes en raw_payload cuando no hay vínculos locales.
        if (empty($images)) {
            $rawImages = [];

            if (isset($rawPayload['images']) && is_array($rawPayload['images'])) {
                $rawImages = array_merge($rawImages, $rawPayload['images']);
            }
            if (isset($rawPayload['photos']) && is_array($rawPayload['photos'])) {
                $rawImages = array_merge($rawImages, $rawPayload['photos']);
            }

            foreach ($rawImages as $rawImage) {
                $candidate = $this->parseRawImageCandidate($rawImage);
                if ($candidate === null) {
                    continue;
                }

                $dedupKey = $this->normalizeUrlKey($candidate['url']);
                if (isset($seen[$dedupKey])) {
                    continue;
                }

                $seen[$dedupKey] = true;
                $images[] = $candidate;

                if (count($images) >= 50) {
                    break;
                }
            }
        }

        if ($mediaAssets->isNotEmpty() && empty($images)) {
            Log::warning('[EasyBrokerMlsExport] Sin URLs de imágenes públicas/alcanzables para enviar', [
                'property_id' => $property->id,
                'media_assets_count' => $mediaAssets->count(),
            ]);
        }

        return $images;
    }

    /**
     * @return array{url: string, title?: string}|null
     */
    protected function parseRawImageCandidate(mixed $rawImage): ?array
    {
        $url = null;
        $title = null;

        if (is_string($rawImage)) {
            $url = trim($rawImage);
        } elseif (is_array($rawImage)) {
            $url = $this->firstNonEmpty([
                $rawImage['url'] ?? null,
                $rawImage['src'] ?? null,
                $rawImage['source_url'] ?? null,
            ]);
            $title = $this->sanitizeImageTitle($this->firstNonEmpty([
                $rawImage['title'] ?? null,
                $rawImage['alt'] ?? null,
                $rawImage['name'] ?? null,
            ]));
        }

        if ($url === null || !$this->isValidEasyBrokerImageUrl($url)) {
            return null;
        }

        $item = ['url' => $url];
        if ($title !== null) {
            $item['title'] = $title;
        }

        return $item;
    }

    protected function resolveMediaAssetImageUrl(mixed $asset): ?string
    {
        $candidates = [
            $asset->serving_url ?? null,
            $asset->pivot?->source_url ?? null,
            $asset->url ?? null,
        ];

        foreach ($candidates as $candidate) {
            if (!is_string($candidate) || trim($candidate) === '') {
                continue;
            }

            $url = trim($candidate);
            if ($this->isValidEasyBrokerImageUrl($url) && $this->isReachableImageUrl($url)) {
                return $url;
            }
        }

        return null;
    }

    protected function sanitizeImageTitle(?string $title): ?string
    {
        if ($title === null) {
            return null;
        }

        $title = trim($title);
        if ($title === '') {
            return null;
        }

        return mb_substr($title, 0, 200);
    }

    protected function isValidEasyBrokerImageUrl(string $url): bool
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $parts = parse_url($url);
        if (!is_array($parts)) {
            return false;
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        if (!in_array($scheme, ['http', 'https'], true)) {
            return false;
        }

        $host = strtolower((string) ($parts['host'] ?? ''));
        if ($host === '' || !$this->isPublicHost($host)) {
            return false;
        }

        $path = (string) ($parts['path'] ?? '');
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'heic'];

        return in_array($extension, $allowedExtensions, true);
    }

    protected function isPublicHost(string $host): bool
    {
        if (
            in_array($host, ['localhost', '127.0.0.1', '::1'], true)
            || str_ends_with($host, '.localhost')
            || str_ends_with($host, '.local')
            || str_ends_with($host, '.test')
        ) {
            return false;
        }

        if (!str_contains($host, '.')) {
            return false;
        }

        if (!filter_var($host, FILTER_VALIDATE_IP)) {
            return true;
        }

        return filter_var(
            $host,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) !== false;
    }

    protected function normalizeUrlKey(string $url): string
    {
        return strtolower(trim($url));
    }

    protected function isReachableImageUrl(string $url): bool
    {
        $cacheKey = $this->normalizeUrlKey($url);
        if (array_key_exists($cacheKey, $this->imageUrlValidationCache)) {
            return $this->imageUrlValidationCache[$cacheKey];
        }

        $timeout = max(3, min($this->timeout, 8));
        $reachable = false;

        try {
            $head = Http::timeout($timeout)
                ->withHeaders([
                    'Accept' => 'image/*,*/*;q=0.8',
                    'User-Agent' => 'SanMiguelProperties-EasyBroker/1.0',
                ])
                ->head($url);

            if ($head->successful()) {
                $contentType = strtolower((string) $head->header('Content-Type'));
                $reachable = $contentType === '' || str_starts_with($contentType, 'image/');
            } elseif (in_array($head->status(), [403, 405], true)) {
                // Algunos orígenes bloquean HEAD; intentar GET con rango mínimo.
                $get = Http::timeout($timeout)
                    ->withHeaders([
                        'Accept' => 'image/*,*/*;q=0.8',
                        'Range' => 'bytes=0-0',
                        'User-Agent' => 'SanMiguelProperties-EasyBroker/1.0',
                    ])
                    ->get($url);

                if ($get->successful() || $get->status() === 206) {
                    $contentType = strtolower((string) $get->header('Content-Type'));
                    $reachable = $contentType === '' || str_starts_with($contentType, 'image/');
                }
            }
        } catch (\Throwable $e) {
            $reachable = false;
        }

        $this->imageUrlValidationCache[$cacheKey] = $reachable;

        return $reachable;
    }

    protected function buildOperationsPayload(Property $property, array $rawPayload, Collection $operations): array
    {
        $payload = [];

        foreach ($operations as $op) {
            $amount = $this->asFloat($op->amount);
            if ($amount === null || $amount <= 0) {
                continue;
            }

            $type = $this->normalizeOperationType(
                is_string($op->operation_type ?? null) ? $op->operation_type : null,
                (bool) $property->for_rent,
                is_string($property->status ?? null) ? $property->status : null
            );

            $currency = $this->firstNonEmpty([
                is_string($op->currency_code ?? null) ? strtoupper($op->currency_code) : null,
                is_string($op->currency?->code ?? null) ? strtoupper($op->currency->code) : null,
                is_string($rawPayload['currency'] ?? null) ? strtoupper($rawPayload['currency']) : null,
            ]);

            $item = [
                'type' => $type,
                'active' => true,
                'amount' => $amount,
            ];

            if ($currency !== null) {
                $item['currency'] = $currency;
            }

            $payload[] = $item;
        }

        if (!empty($payload)) {
            return $this->uniqueOperations($payload);
        }

        $rawPrice = $this->asFloat($rawPayload['price'] ?? $rawPayload['amount'] ?? null);
        if ($rawPrice !== null && $rawPrice > 0) {
            $type = $this->normalizeOperationType(
                is_string($rawPayload['operation_type'] ?? null) ? $rawPayload['operation_type'] : null,
                (bool) ($rawPayload['for_rent'] ?? $property->for_rent),
                is_string($rawPayload['status'] ?? null) ? $rawPayload['status'] : (is_string($property->status ?? null) ? $property->status : null)
            );

            $currency = $this->firstNonEmpty([
                is_string($rawPayload['currency'] ?? null) ? strtoupper($rawPayload['currency']) : null,
                'MXN',
            ]);

            $item = [
                'type' => $type,
                'active' => true,
                'amount' => $rawPrice,
            ];
            if ($currency !== null) {
                $item['currency'] = $currency;
            }

            return [$item];
        }

        return [];
    }

    protected function normalizeOperationType(?string $rawType, bool $forRent = false, ?string $status = null): string
    {
        $joined = strtolower(trim((string) ($rawType ?? $status ?? '')));

        if (
            $forRent ||
            str_contains($joined, 'rent') ||
            str_contains($joined, 'renta') ||
            str_contains($joined, 'alquiler')
        ) {
            return 'rental';
        }

        return 'sale';
    }

    protected function uniqueOperations(array $operations): array
    {
        $seen = [];
        $result = [];

        foreach ($operations as $item) {
            $key = implode('|', [
                $item['type'] ?? '',
                (string) ($item['amount'] ?? ''),
                strtoupper((string) ($item['currency'] ?? '')),
            ]);

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $result[] = $item;
        }

        return $result;
    }

    /**
     * Determina si vale la pena reintentar con fallback de ubicación.
     */
    protected function shouldRetryLocationWithCatalog(array $body): bool
    {
        $errors = $body['errors'] ?? null;
        if (!is_array($errors)) {
            return false;
        }

        return isset($errors['city_id'])
            || isset($errors['administrative_division_id'])
            || isset($errors['neighborhood'])
            || isset($errors['location']);
    }

    /**
     * Recalcula location.name contra catálogo de EasyBroker usando ciudad/estado
     * y, cuando sea posible, una colonia equivalente.
     */
    protected function buildLocationFallbackPayload(array $payload, Property $property): ?array
    {
        $currentLocation = $payload['location'] ?? null;
        if (!is_array($currentLocation)) {
            return null;
        }

        $location = $property->relationLoaded('location')
            ? $property->location
            : $property->location()->first();

        $rawPayload = is_array($property->raw_payload) ? $property->raw_payload : [];

        $neighborhood = $this->firstNonEmpty([
            $location?->city_area,
            $property->mls_neighborhood,
            $rawPayload['neighborhood'] ?? null,
            $rawPayload['location']['neighborhood'] ?? null,
            $rawPayload['location']['city_area'] ?? null,
        ]);

        $city = $this->firstNonEmpty([
            $location?->city,
            $rawPayload['city'] ?? null,
            $rawPayload['location']['city'] ?? null,
        ]);

        $state = $this->firstNonEmpty([
            $location?->region,
            $rawPayload['state_province'] ?? null,
            $rawPayload['state'] ?? null,
            $rawPayload['location']['state'] ?? null,
        ]);

        $resolvedName = $this->resolveLocationFromCatalog($neighborhood, $city, $state);
        if ($resolvedName === null) {
            return null;
        }

        $currentName = $this->firstNonEmpty([$currentLocation['name'] ?? null]);
        if (
            $currentName !== null
            && $this->normalizeString($currentName) === $this->normalizeString($resolvedName)
        ) {
            return null;
        }

        $payload['location']['name'] = $resolvedName;

        return $payload;
    }

    /**
     * Resuelve un full_name de ubicación válido para EasyBroker.
     */
    protected function resolveLocationFromCatalog(?string $neighborhood, ?string $city, ?string $state): ?string
    {
        $cityState = $this->firstNonEmpty([
            $city !== null && $state !== null ? "{$city}, {$state}" : null,
        ]);

        if ($cityState === null) {
            return null;
        }

        $cityData = $this->lookupLocationByQuery($cityState);
        if ($cityData === null) {
            return null;
        }

        $cityFullName = $this->firstNonEmpty([
            $cityData['full_name'] ?? null,
            $cityData['name'] ?? null,
        ]);

        $localities = $cityData['localities'] ?? [];
        if ($neighborhood !== null && is_array($localities)) {
            $bestNeighborhood = $this->findBestNeighborhoodFullName($neighborhood, $localities);
            if ($bestNeighborhood !== null) {
                return $bestNeighborhood;
            }
        }

        return $cityFullName;
    }

    /**
     * Obtiene ubicación desde /locations por query, con caché local.
     *
     * @return array<string, mixed>|null
     */
    protected function lookupLocationByQuery(string $query): ?array
    {
        $query = trim($query);
        if ($query === '') {
            return null;
        }

        $cacheKey = $this->normalizeString($query);
        if (array_key_exists($cacheKey, $this->locationQueryCache)) {
            return $this->locationQueryCache[$cacheKey];
        }

        $response = $this->makeRequest('GET', '/locations', [
            'query' => $query,
        ]);

        if (!$response['ok'] || !is_array($response['body'])) {
            $this->locationQueryCache[$cacheKey] = null;
            return null;
        }

        $this->locationQueryCache[$cacheKey] = $response['body'];

        return $response['body'];
    }

    /**
     * Busca la colonia más cercana al texto de origen dentro de localities.
     */
    protected function findBestNeighborhoodFullName(string $neighborhood, array $localities): ?string
    {
        $needle = $this->normalizeString($neighborhood);
        if ($needle === '') {
            return null;
        }

        $bestScore = 0.0;
        $bestFullName = null;

        foreach ($localities as $item) {
            if (!is_array($item)) {
                continue;
            }

            $name = $this->firstNonEmpty([
                $item['name'] ?? null,
                $item['full_name'] ?? null,
            ]);
            $fullName = $this->firstNonEmpty([
                $item['full_name'] ?? null,
                $item['name'] ?? null,
            ]);

            if ($name === null || $fullName === null) {
                continue;
            }

            $normalizedName = $this->normalizeString($name);
            $normalizedFull = $this->normalizeString($fullName);

            $score = 0.0;
            if ($normalizedName === $needle || $normalizedFull === $needle) {
                $score = 100.0;
            } elseif (
                str_contains($normalizedName, $needle)
                || str_contains($normalizedFull, $needle)
            ) {
                $score = 90.0;
            } elseif (
                str_contains($needle, $normalizedName)
                || str_contains($needle, $normalizedFull)
            ) {
                $score = 85.0;
            } else {
                similar_text($needle, $normalizedName, $nameSimilarity);
                similar_text($needle, $normalizedFull, $fullSimilarity);
                $score = max((float) $nameSimilarity, (float) $fullSimilarity);
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestFullName = $fullName;
            }
        }

        // Umbral conservador para evitar empates débiles.
        return $bestScore >= 70.0 ? $bestFullName : null;
    }

    protected function makeRequest(string $method, string $endpoint, array $payload = []): array
    {
        $method = strtoupper($method);
        $url = rtrim($this->baseUrl, '/') . $endpoint;

        try {
            $client = Http::withHeaders([
                'X-Authorization' => $this->apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->timeout($this->timeout);

            $response = match ($method) {
                'GET' => $client->get($url, $payload),
                'POST' => $client->post($url, $payload),
                'PATCH' => $client->patch($url, $payload),
                default => throw new \InvalidArgumentException("Método HTTP no soportado: {$method}"),
            };

            $body = null;
            try {
                $body = $response->json();
            } catch (\Throwable $e) {
                $body = null;
            }

            if ($response->failed()) {
                Log::warning('[EasyBrokerMlsExport] Error de API', [
                    'method' => $method,
                    'url' => $url,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }

            return [
                'ok' => $response->successful(),
                'status' => $response->status(),
                'body' => is_array($body) ? $body : [],
                'raw_body' => $response->body(),
            ];
        } catch (\Throwable $e) {
            Log::error('[EasyBrokerMlsExport] Excepción HTTP', [
                'method' => $method,
                'url' => $url,
                'exception' => $e->getMessage(),
            ]);

            return [
                'ok' => false,
                'status' => 0,
                'body' => [],
                'raw_body' => $e->getMessage(),
            ];
        }
    }

    protected function extractErrorMessage(array $body, string $rawBody): string
    {
        $message = $body['message'] ?? $body['error'] ?? null;

        if (is_string($message) && trim($message) !== '') {
            return trim($message);
        }

        if (isset($body['errors']) && is_array($body['errors'])) {
            $flattened = [];
            foreach ($body['errors'] as $field => $errors) {
                if (is_array($errors)) {
                    foreach ($errors as $error) {
                        if (is_string($error) && trim($error) !== '') {
                            $flattened[] = "{$field}: {$error}";
                        }
                    }
                } elseif (is_string($errors) && trim($errors) !== '') {
                    $flattened[] = "{$field}: {$errors}";
                }
            }

            if (!empty($flattened)) {
                return implode(' | ', $flattened);
            }
        }

        return trim($rawBody) !== '' ? trim($rawBody) : 'Error desconocido al enviar propiedad a EasyBroker.';
    }

    protected function parseDate(mixed $value): ?Carbon
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function firstNonEmpty(array $candidates): ?string
    {
        foreach ($candidates as $candidate) {
            if (!is_string($candidate)) {
                continue;
            }

            $value = trim($candidate);
            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    protected function asFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        if (is_string($value) && preg_match('/[\d.,]+/', $value, $matches)) {
            $number = str_replace(',', '.', $matches[0]);
            $parts = explode('.', $number);
            if (count($parts) > 2) {
                $decimal = array_pop($parts);
                $number = implode('', $parts) . '.' . $decimal;
            }

            if (is_numeric($number)) {
                return (float) $number;
            }
        }

        return null;
    }

    protected function asInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        if (is_string($value) && preg_match('/\d+/', $value, $matches)) {
            return (int) $matches[0];
        }

        return null;
    }

    protected function normalizeString(string $value): string
    {
        $value = strtolower(trim($value));
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
        $value = preg_replace('/[^a-z0-9]+/', ' ', $value) ?? $value;

        return trim($value);
    }

    protected function isBlank(mixed $value): bool
    {
        return !is_string($value) || trim($value) === '';
    }
}

