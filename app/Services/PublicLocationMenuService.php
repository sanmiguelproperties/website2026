<?php

namespace App\Services;

use App\Models\Property;
use App\Models\PropertyLocation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PublicLocationMenuService
{
    private const CACHE_KEY = 'public_mls_location_tree_v4';
    private const CACHE_MINUTES = 10;

    /**
     * Devuelve estructura para menú: estado -> [ciudades].
     */
    public static function stateCityTree(): array
    {
        return Cache::remember(
            self::CACHE_KEY,
            now()->addMinutes(self::CACHE_MINUTES),
            static fn () => self::buildStateCityTree()
        );
    }

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private static function buildStateCityTree(): array
    {
        LocationTaxonomyService::backfillFromPropertyLocations();
        ZonePageService::syncFromPublishedProperties();
        $zoneSlugsByLocation = ZonePageService::activeSlugMapByLocation();
        $zoneMenuConfigByLocation = ZonePageService::menuConfigMapByLocation();

        $hasTaxonomyIds = LocationTaxonomyService::hasTaxonomyColumns();

        if ($hasTaxonomyIds) {
            $rows = PropertyLocation::query()
                ->join('properties', 'properties.id', '=', 'property_locations.property_id')
                ->leftJoin('locations_catalog as state_catalog', 'state_catalog.id', '=', 'property_locations.state_catalog_id')
                ->leftJoin('locations_catalog as city_catalog', 'city_catalog.id', '=', 'property_locations.city_catalog_id')
                ->leftJoin('locations_catalog as neighborhood_catalog', 'neighborhood_catalog.id', '=', 'property_locations.neighborhood_catalog_id')
                ->where('properties.published', true)
                ->where('properties.source', Property::SOURCE_MLS)
                ->whereRaw("TRIM(COALESCE(state_catalog.name, property_locations.region, '')) != ''")
                ->whereRaw("TRIM(COALESCE(city_catalog.name, property_locations.city, '')) != ''")
                ->selectRaw('COALESCE(state_catalog.name, property_locations.region) as region')
                ->selectRaw('COALESCE(city_catalog.name, property_locations.city) as city')
                ->selectRaw('COALESCE(neighborhood_catalog.name, property_locations.city_area) as city_area')
                ->distinct()
                ->get();
        } else {
            $rows = PropertyLocation::query()
                ->join('properties', 'properties.id', '=', 'property_locations.property_id')
                ->where('properties.published', true)
                ->where('properties.source', Property::SOURCE_MLS)
                ->whereNotNull('property_locations.region')
                ->where('property_locations.region', '!=', '')
                ->whereNotNull('property_locations.city')
                ->where('property_locations.city', '!=', '')
                ->select([
                    'property_locations.region',
                    'property_locations.city',
                    'property_locations.city_area',
                ])
                ->distinct()
                ->get();
        }

        $grouped = [];

        foreach ($rows as $row) {
            $state = self::normalizeLocationLabel($row->region);
            $city = self::normalizeLocationLabel($row->city);
            $zone = self::normalizeLocationLabel($row->city_area);

            if ($state === null || $city === null) {
                continue;
            }

            $stateKey = Str::lower($state);
            $cityKey = Str::lower($city);
            $zoneKey = $zone !== null ? Str::lower($zone) : null;

            if (!isset($grouped[$stateKey])) {
                $grouped[$stateKey] = [
                    'state' => $state,
                    'cities' => [],
                ];
            }

            if (!isset($grouped[$stateKey]['cities'][$cityKey])) {
                $grouped[$stateKey]['cities'][$cityKey] = [
                    'city' => $city,
                    'zones' => [],
                ];
            }

            if ($zoneKey !== null) {
                $zoneCompositeKey = ZonePageService::compositeKeyFromLabels($state, $city, $zone);
                $menuConfig = $zoneMenuConfigByLocation[$zoneCompositeKey] ?? null;
                $showInMenu = (bool) ($menuConfig['show_in_menu'] ?? true);

                if (!$showInMenu) {
                    continue;
                }

                $menuOrder = $menuConfig['menu_order'] ?? null;

                $grouped[$stateKey]['cities'][$cityKey]['zones'][$zoneKey] = [
                    'name' => $zone,
                    'menu_order' => is_numeric($menuOrder) ? (int) $menuOrder : null,
                ];
            }
        }

        ksort($grouped, SORT_NATURAL | SORT_FLAG_CASE);

        $result = [];
        foreach ($grouped as $entry) {
            $cities = $entry['cities'];
            uasort($cities, static function (array $a, array $b): int {
                return strnatcasecmp($a['city'], $b['city']);
            });

            $cityEntries = [];
            foreach ($cities as $cityEntry) {
                $zones = array_values($cityEntry['zones']);
                usort($zones, [self::class, 'compareZoneEntries']);

                $zoneEntries = [];
                foreach ($zones as $zoneEntry) {
                    $zoneName = $zoneEntry['name'];
                    $zoneEntries[] = [
                        'name' => $zoneName,
                        'url' => self::resolveZoneUrl(
                            $entry['state'],
                            $cityEntry['city'],
                            $zoneName,
                            $zoneSlugsByLocation
                        ),
                    ];
                }

                $cityEntries[] = [
                    'city' => $cityEntry['city'],
                    'url' => route('public.properties.index', [
                        'region' => $entry['state'],
                        'city' => $cityEntry['city'],
                    ]),
                    'zones' => $zoneEntries,
                ];
            }

            $result[] = [
                'state' => $entry['state'],
                'url' => route('public.properties.index', [
                    'region' => $entry['state'],
                ]),
                'cities' => $cityEntries,
            ];
        }

        return $result;
    }

    private static function normalizeLocationLabel(?string $value): ?string
    {
        $value = preg_replace('/\s+/u', ' ', trim((string) $value));
        return $value === '' ? null : $value;
    }

    private static function resolveZoneUrl(
        string $state,
        string $city,
        string $zone,
        array $zoneSlugsByLocation
    ): string {
        $compositeKey = ZonePageService::compositeKeyFromLabels($state, $city, $zone);
        $slug = $zoneSlugsByLocation[$compositeKey] ?? null;

        if (is_string($slug) && $slug !== '') {
            return route('public.zones.show', ['zoneSlug' => $slug]);
        }

        return route('public.properties.index', [
            'region' => $state,
            'city' => $city,
            'city_area' => $zone,
        ]);
    }

    private static function compareZoneEntries(array $a, array $b): int
    {
        $orderA = $a['menu_order'] ?? null;
        $orderB = $b['menu_order'] ?? null;

        $hasOrderA = is_int($orderA);
        $hasOrderB = is_int($orderB);

        if ($hasOrderA && $hasOrderB && $orderA !== $orderB) {
            return $orderA <=> $orderB;
        }

        if ($hasOrderA && !$hasOrderB) {
            return -1;
        }

        if (!$hasOrderA && $hasOrderB) {
            return 1;
        }

        return strnatcasecmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? ''));
    }
}
