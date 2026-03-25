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
            'street' => $street,
            'location' => [
                'name' => $locationName,
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
        if ($this->isBlank($payload['street'] ?? null)) {
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

        if (!empty($property->easybroker_public_id)) {
            $action = 'updated';
            $response = $this->makeRequest('PATCH', '/properties/' . $property->easybroker_public_id, $payload);

            if (!$response['ok'] && $response['status'] === 404 && $createIfMissing) {
                $action = 'created';
                $response = $this->makeRequest('POST', '/properties', $payload);
            }
        } else {
            $response = $this->makeRequest('POST', '/properties', $payload);
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
            'residential' => 'Casa',
            'land and lots' => 'Terreno',
            'commercial' => 'Local Comercial',
            'pre sales' => 'Casa',
        ];

        $category = $this->firstNonEmpty([
            $property->category,
            $rawPayload['category'] ?? null,
        ]);

        $mappedCategoryType = null;
        if ($category !== null) {
            $normalizedCategory = $this->normalizeString($category);
            $mappedCategoryType = $categoryMap[$normalizedCategory] ?? null;
        }

        $candidates = array_values(array_filter([
            $property->property_type_name,
            $rawPayload['property_type'] ?? null,
            $rawPayload['property_type_name'] ?? null,
            $property->category,
            $rawPayload['category'] ?? null,
            $mappedCategoryType,
            $fallbackPropertyType,
        ], fn ($value) => is_string($value) && trim($value) !== ''));

        if (empty($candidates)) {
            return null;
        }

        if (empty($allowedPropertyTypes)) {
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

        return null;
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

