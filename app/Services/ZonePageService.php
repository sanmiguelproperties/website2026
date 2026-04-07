<?php

namespace App\Services;

use App\Models\Property;
use App\Models\PropertyLocation;
use App\Models\ZonePage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ZonePageService
{
    /**
     * Genera o actualiza zone pages automáticas usando la data real de propiedades publicadas.
     */
    public static function syncFromPublishedProperties(): void
    {
        LocationTaxonomyService::backfillFromPropertyLocations();

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
                ->whereRaw("TRIM(COALESCE(neighborhood_catalog.name, property_locations.city_area, '')) != ''")
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
                ->whereNotNull('property_locations.city_area')
                ->where('property_locations.city_area', '!=', '')
                ->select([
                    'property_locations.region',
                    'property_locations.city',
                    'property_locations.city_area',
                ])
                ->distinct()
                ->get();
        }

        if ($rows->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($rows): void {
            foreach ($rows as $row) {
                $region = self::normalizeLabel($row->region);
                $city = self::normalizeLabel($row->city);
                $cityArea = self::normalizeLabel($row->city_area);

                if ($region === null || $city === null || $cityArea === null) {
                    continue;
                }

                $regionKey = self::normalizeKey($region);
                $cityKey = self::normalizeKey($city);
                $cityAreaKey = self::normalizeKey($cityArea);

                if ($regionKey === '' || $cityKey === '' || $cityAreaKey === '') {
                    continue;
                }

                $zonePage = ZonePage::query()
                    ->where('region_key', $regionKey)
                    ->where('city_key', $cityKey)
                    ->where('city_area_key', $cityAreaKey)
                    ->first();

                if (!$zonePage) {
                    $zonePage = new ZonePage();
                    $zonePage->slug = self::generateUniqueSlug($cityArea, $city, $region);
                    $zonePage->is_active = true;
                    $zonePage->title_es = self::defaultTitle($cityArea, $city, 'es');
                    $zonePage->title_en = self::defaultTitle($cityArea, $city, 'en');
                    $zonePage->description_es = self::defaultDescription($cityArea, $city, $region, 'es');
                    $zonePage->description_en = self::defaultDescription($cityArea, $city, $region, 'en');
                    $zonePage->meta_title_es = self::defaultMetaTitle($cityArea, $city, 'es');
                    $zonePage->meta_title_en = self::defaultMetaTitle($cityArea, $city, 'en');
                    $zonePage->meta_description_es = self::defaultMetaDescription($cityArea, $city, $region, 'es');
                    $zonePage->meta_description_en = self::defaultMetaDescription($cityArea, $city, $region, 'en');
                }

                $zonePage->region = $region;
                $zonePage->city = $city;
                $zonePage->city_area = $cityArea;
                $zonePage->region_key = $regionKey;
                $zonePage->city_key = $cityKey;
                $zonePage->city_area_key = $cityAreaKey;
                $zonePage->last_detected_at = now();
                $zonePage->save();
            }
        });
    }

    /**
     * Devuelve mapa [region|city|area => slug] de páginas activas.
     */
    public static function activeSlugMapByLocation(): array
    {
        return ZonePage::query()
            ->where('is_active', true)
            ->get(['slug', 'region_key', 'city_key', 'city_area_key'])
            ->mapWithKeys(function (ZonePage $zonePage): array {
                return [
                    self::compositeKey($zonePage->region_key, $zonePage->city_key, $zonePage->city_area_key) => $zonePage->slug,
                ];
            })
            ->all();
    }

    /**
     * Devuelve mapa [region|city|area => {show_in_menu, menu_order}] para menu publico.
     */
    public static function menuConfigMapByLocation(): array
    {
        return ZonePage::query()
            ->get(['region_key', 'city_key', 'city_area_key', 'show_in_menu', 'menu_order'])
            ->mapWithKeys(function (ZonePage $zonePage): array {
                return [
                    self::compositeKey($zonePage->region_key, $zonePage->city_key, $zonePage->city_area_key) => [
                        'show_in_menu' => $zonePage->show_in_menu !== false,
                        'menu_order' => $zonePage->menu_order,
                    ],
                ];
            })
            ->all();
    }

    public static function compositeKeyFromLabels(string $region, string $city, string $cityArea): string
    {
        return self::compositeKey(
            self::normalizeKey($region),
            self::normalizeKey($city),
            self::normalizeKey($cityArea)
        );
    }

    public static function normalizeLabel(?string $value): ?string
    {
        $value = trim((string) $value);
        $value = preg_replace('/\s+/u', ' ', $value ?? '');

        return $value === '' ? null : $value;
    }

    public static function normalizeKey(?string $value): string
    {
        $normalized = self::normalizeLabel($value);
        if ($normalized === null) {
            return '';
        }

        return Str::lower($normalized);
    }

    private static function compositeKey(string $regionKey, string $cityKey, string $cityAreaKey): string
    {
        return "{$regionKey}|{$cityKey}|{$cityAreaKey}";
    }

    private static function generateUniqueSlug(string $cityArea, string $city, string $region): string
    {
        $base = Str::slug($cityArea);
        if ($base === '') {
            $base = Str::slug($city);
        }
        if ($base === '') {
            $base = Str::slug($region);
        }
        if ($base === '') {
            $base = 'zona';
        }

        $candidate = $base;
        $suffix = 1;

        while (
            ZonePage::query()
                ->where('slug', $candidate)
                ->exists()
        ) {
            $suffix++;
            $candidate = "{$base}-{$suffix}";
        }

        return $candidate;
    }

    private static function defaultTitle(string $cityArea, string $city, string $locale): string
    {
        if ($locale === 'en') {
            return "Properties in {$cityArea}, {$city}";
        }

        return "Propiedades en {$cityArea}, {$city}";
    }

    private static function defaultDescription(string $cityArea, string $city, string $region, string $locale): string
    {
        if ($locale === 'en') {
            return "Explore available homes and investments in {$cityArea}, {$city}, {$region}.";
        }

        return "Explora casas e inversiones disponibles en {$cityArea}, {$city}, {$region}.";
    }

    private static function defaultMetaTitle(string $cityArea, string $city, string $locale): string
    {
        if ($locale === 'en') {
            return "Real Estate in {$cityArea}, {$city}";
        }

        return "Bienes raíces en {$cityArea}, {$city}";
    }

    private static function defaultMetaDescription(string $cityArea, string $city, string $region, string $locale): string
    {
        if ($locale === 'en') {
            return "Find properties for sale and rent in {$cityArea}, {$city}, {$region}.";
        }

        return "Encuentra propiedades en venta y renta en {$cityArea}, {$city}, {$region}.";
    }
}
