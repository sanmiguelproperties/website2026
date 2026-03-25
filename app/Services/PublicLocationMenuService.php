<?php

namespace App\Services;

use App\Models\Property;
use App\Models\PropertyLocation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PublicLocationMenuService
{
    private const CACHE_KEY = 'public_mls_location_tree_v2';
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

    private static function buildStateCityTree(): array
    {
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
                $grouped[$stateKey]['cities'][$cityKey]['zones'][$zoneKey] = $zone;
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
                $zones = $cityEntry['zones'];
                asort($zones, SORT_NATURAL | SORT_FLAG_CASE);

                $cityEntries[] = [
                    'city' => $cityEntry['city'],
                    'zones' => array_values($zones),
                ];
            }

            $result[] = [
                'state' => $entry['state'],
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
}
