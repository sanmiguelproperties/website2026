<?php

namespace App\Services;

use App\Models\LocationCatalog;
use App\Models\PropertyLocation;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class LocationTaxonomyService
{
    /**
     * In-memory cache to avoid repeated lookups during mass sync.
     *
     * @var array<string, LocationCatalog>
     */
    protected static array $nodeCache = [];
    protected static bool $didBackfillDuringRequest = false;
    /** @var array<string, true>|null */
    protected static ?array $catalogColumnMap = null;
    protected static ?bool $catalogHasIdColumn = null;

    /**
     * Create/get taxonomy hierarchy and return canonical labels + IDs.
     */
    public static function resolveHierarchy(
        ?string $country,
        ?string $state,
        ?string $city,
        ?string $neighborhood
    ): array {
        $countryName = self::normalizeLabel($country);
        $stateName = self::normalizeLabel($state);
        $cityName = self::normalizeLabel($city);
        $neighborhoodName = self::normalizeLabel($neighborhood);

        $countryNode = $countryName ? self::findOrCreateNode('Country', $countryName, null) : null;
        $stateNode = $stateName ? self::findOrCreateNode('State', $stateName, $countryNode) : null;
        $cityNode = $cityName ? self::findOrCreateNode('City', $cityName, $stateNode) : null;
        $neighborhoodNode = $neighborhoodName ? self::findOrCreateNode('Neighborhood', $neighborhoodName, $cityNode) : null;

        return [
            'country' => $countryNode?->name ?? $countryName,
            'state' => $stateNode?->name ?? $stateName,
            'city' => $cityNode?->name ?? $cityName,
            'neighborhood' => $neighborhoodNode?->name ?? $neighborhoodName,
            'country_catalog_id' => self::nodeId($countryNode),
            'state_catalog_id' => self::nodeId($stateNode),
            'city_catalog_id' => self::nodeId($cityNode),
            'neighborhood_catalog_id' => self::nodeId($neighborhoodNode),
        ];
    }

    /**
     * Convert legacy property_locations values to taxonomy-backed data.
     */
    public static function backfillFromPropertyLocations(int $chunkSize = 300, bool $force = false): void
    {
        if (self::$didBackfillDuringRequest && !$force) {
            return;
        }

        self::$didBackfillDuringRequest = true;

        $hasStateColumn = Schema::hasColumn('property_locations', 'state_catalog_id');
        $hasCityColumn = Schema::hasColumn('property_locations', 'city_catalog_id');
        $hasNeighborhoodColumn = Schema::hasColumn('property_locations', 'neighborhood_catalog_id');
        $hasCountryColumn = Schema::hasColumn('property_locations', 'country');

        PropertyLocation::query()
            ->where(function ($query): void {
                $query->whereNotNull('region')
                    ->orWhereNotNull('city')
                    ->orWhereNotNull('city_area');
            })
            ->orderBy('property_id')
            ->chunk($chunkSize, function ($locations) use (
                $hasCountryColumn,
                $hasStateColumn,
                $hasCityColumn,
                $hasNeighborhoodColumn
            ): void {
                foreach ($locations as $location) {
                    $resolved = self::resolveHierarchy(
                        $location->country,
                        $location->region,
                        $location->city,
                        $location->city_area
                    );

                    $updates = [];

                    if ($hasCountryColumn && !empty($resolved['country']) && $location->country !== $resolved['country']) {
                        $updates['country'] = $resolved['country'];
                    }
                    if (!empty($resolved['state']) && $location->region !== $resolved['state']) {
                        $updates['region'] = $resolved['state'];
                    }
                    if (!empty($resolved['city']) && $location->city !== $resolved['city']) {
                        $updates['city'] = $resolved['city'];
                    }
                    if (!empty($resolved['neighborhood']) && $location->city_area !== $resolved['neighborhood']) {
                        $updates['city_area'] = $resolved['neighborhood'];
                    }

                    if (
                        $hasStateColumn
                        && 
                        isset($resolved['state_catalog_id'])
                        && (int) ($location->state_catalog_id ?? 0) !== (int) ($resolved['state_catalog_id'] ?? 0)
                    ) {
                        $updates['state_catalog_id'] = $resolved['state_catalog_id'];
                    }

                    if (
                        $hasCityColumn
                        &&
                        isset($resolved['city_catalog_id'])
                        && (int) ($location->city_catalog_id ?? 0) !== (int) ($resolved['city_catalog_id'] ?? 0)
                    ) {
                        $updates['city_catalog_id'] = $resolved['city_catalog_id'];
                    }

                    if (
                        $hasNeighborhoodColumn
                        &&
                        isset($resolved['neighborhood_catalog_id'])
                        && (int) ($location->neighborhood_catalog_id ?? 0) !== (int) ($resolved['neighborhood_catalog_id'] ?? 0)
                    ) {
                        $updates['neighborhood_catalog_id'] = $resolved['neighborhood_catalog_id'];
                    }

                    if (!empty($updates)) {
                        $location->update($updates);
                    }
                }
            });
    }

    public static function hasTaxonomyColumns(): bool
    {
        return self::hasCatalogIdColumn()
            && Schema::hasColumn('property_locations', 'state_catalog_id')
            && Schema::hasColumn('property_locations', 'city_catalog_id')
            && Schema::hasColumn('property_locations', 'neighborhood_catalog_id');
    }

    public static function hasCatalogIdColumn(): bool
    {
        if (self::$catalogHasIdColumn !== null) {
            return self::$catalogHasIdColumn;
        }

        self::$catalogHasIdColumn = self::catalogHasColumn('id');
        return self::$catalogHasIdColumn;
    }

    public static function normalizeLabel(?string $value): ?string
    {
        $value = trim((string) $value);
        $value = preg_replace('/\s+/u', ' ', $value ?? '');

        return $value === '' ? null : $value;
    }

    protected static function normalizeKey(?string $value): string
    {
        $label = self::normalizeLabel($value);
        return $label ? Str::lower($label) : '';
    }

    protected static function findOrCreateNode(string $type, string $name, ?LocationCatalog $parentNode): LocationCatalog
    {
        $name = self::normalizeLabel($name) ?? $name;
        $cacheParentKey = self::catalogUsesParentId()
            ? (string) ($parentNode?->getAttribute('id') ?? 0)
            : (string) ($parentNode?->getAttribute('full_name') ?? '');
        $cacheKey = $type . '|' . $cacheParentKey . '|' . self::normalizeKey($name);

        if (isset(self::$nodeCache[$cacheKey])) {
            return self::$nodeCache[$cacheKey];
        }

        $query = LocationCatalog::query()
            ->where('type', $type)
            ->whereRaw('LOWER(name) = ?', [self::normalizeKey($name)]);

        if (self::catalogUsesParentId()) {
            $parentId = $parentNode?->getAttribute('id');
            if ($parentId === null) {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', (int) $parentId);
            }
        } elseif (self::catalogUsesParentFullName()) {
            $parentFullName = $parentNode?->getAttribute('full_name');
            if ($parentFullName === null || $parentFullName === '') {
                $query->whereNull('parent_full_name');
            } else {
                $query->where('parent_full_name', (string) $parentFullName);
            }
        }

        $node = $query->first();

        if (!$node) {
            $fullName = self::buildFullName($name, $parentNode);
            $candidate = $fullName;
            $suffix = 1;
            while (LocationCatalog::query()->where('full_name', $candidate)->exists()) {
                $suffix++;
                $candidate = $fullName . ' #' . $suffix;
            }

            $attributes = [
                'full_name' => $candidate,
                'name' => $name,
                'type' => $type,
            ];

            if (self::catalogUsesParentId()) {
                $attributes['parent_id'] = $parentNode?->getAttribute('id');
            } elseif (self::catalogUsesParentFullName()) {
                $attributes['parent_full_name'] = $parentNode?->getAttribute('full_name');
            }

            $node = LocationCatalog::create($attributes);
        }

        self::$nodeCache[$cacheKey] = $node;

        return $node;
    }

    protected static function buildFullName(string $name, ?LocationCatalog $parentNode): string
    {
        if (!$parentNode) {
            return $name;
        }

        $parentFullName = self::normalizeLabel((string) ($parentNode->getAttribute('full_name') ?? ''));

        if ($parentFullName === null && self::catalogUsesParentId()) {
            $parentId = $parentNode->getAttribute('id');
            if ($parentId !== null && self::hasCatalogIdColumn()) {
                $parentFullName = LocationCatalog::query()
                    ->where('id', (int) $parentId)
                    ->value('full_name');
            }
        }

        if ($parentFullName === null) {
            return $name;
        }

        return trim($parentFullName . ' > ' . $name);
    }

    protected static function nodeId(?LocationCatalog $node): ?int
    {
        if (!$node || !self::hasCatalogIdColumn()) {
            return null;
        }

        $value = $node->getAttribute('id');
        return $value === null ? null : (int) $value;
    }

    protected static function catalogUsesParentId(): bool
    {
        return self::catalogHasColumn('parent_id');
    }

    protected static function catalogUsesParentFullName(): bool
    {
        return self::catalogHasColumn('parent_full_name');
    }

    protected static function catalogHasColumn(string $column): bool
    {
        if (self::$catalogColumnMap === null) {
            $columns = Schema::getColumnListing('locations_catalog');
            self::$catalogColumnMap = array_fill_keys($columns, true);
        }

        return isset(self::$catalogColumnMap[$column]);
    }
}
