<?php

namespace App\Services;

use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class HomeStatsService
{
    public const HAPPY_CLIENTS_FIELD = 'stats_happy_clients_number';

    private const HOUSE_TERMS = [
        'casa',
        'house',
        'home',
        'residential',
        'villa',
    ];

    private const LOT_TERMS = [
        'land and lots',
        'land lots',
        'lot',
        'lots',
        'lote',
        'lotes',
        'terreno',
        'terrenos',
    ];

    private const APARTMENT_TERMS = [
        'apartamento',
        'apartamentos',
        'apartment',
        'apartments',
        'departamento',
        'departamentos',
    ];

    public static function make(?CmsPageData $pageData = null, ?string $locale = null): array
    {
        return (new self())->items($pageData, $locale);
    }

    public static function saleCta(?string $locale = null): array
    {
        return (new self())->saleCtaItems($locale);
    }

    public function items(?CmsPageData $pageData = null, ?string $locale = null): array
    {
        $locale = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'es';

        return [
            [
                'key' => 'houses',
                'number' => $this->formatCount($this->countHouses()),
                'label' => $locale === 'en' ? 'Houses' : 'Casas',
            ],
            [
                'key' => 'lots',
                'number' => $this->formatCount($this->countLots()),
                'label' => $locale === 'en' ? 'Lots' : 'Lotes',
            ],
            [
                'key' => 'agents',
                'number' => $this->formatCount($this->countAgentUsers()),
                'label' => $locale === 'en' ? 'Agents' : 'Agentes',
            ],
            [
                'key' => 'happy_clients',
                'number' => $this->happyClientsNumber($pageData, $locale),
                'label' => $locale === 'en' ? 'Happy clients' : 'Clientes felices',
            ],
        ];
    }

    public function saleCtaItems(?string $locale = null): array
    {
        $locale = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'es';

        return [
            'houses' => [
                'number' => $this->formatCount($this->countSalePropertiesByTerms(self::HOUSE_TERMS)),
                'label' => $locale === 'en' ? 'Available houses' : 'Casas disponibles',
            ],
            'apartments' => [
                'number' => $this->formatCount($this->countSalePropertiesByTerms(self::APARTMENT_TERMS)),
                'label' => $locale === 'en' ? 'Apartments' : 'Departamentos',
            ],
            'lots' => [
                'number' => $this->formatCount($this->countSalePropertiesByTerms(self::LOT_TERMS)),
                'label' => $locale === 'en' ? 'Lots' : 'Lotes',
            ],
        ];
    }

    private function countHouses(): int
    {
        return $this->publishedProperties()
            ->where(function (Builder $query) {
                $this->whereAnyTerm($query, ['property_type_name', 'category'], self::HOUSE_TERMS);
            })
            ->count();
    }

    private function countLots(): int
    {
        return $this->publishedProperties()
            ->where(function (Builder $query) {
                $this->whereAnyTerm($query, ['property_type_name', 'category'], self::LOT_TERMS);
            })
            ->count();
    }

    private function countSalePropertiesByTerms(array $terms): int
    {
        return $this->publishedSaleProperties()
            ->where(function (Builder $query) use ($terms) {
                $this->whereAnyTerm($query, ['property_type_name', 'category'], $terms);
            })
            ->count();
    }

    private function countAgentUsers(): int
    {
        return User::query()
            ->withAgentRole()
            ->count();
    }

    private function happyClientsNumber(?CmsPageData $pageData, string $locale): string
    {
        $directValue = trim((string) ($pageData?->field(self::HAPPY_CLIENTS_FIELD, $locale) ?? ''));

        if ($directValue !== '') {
            return $directValue;
        }

        return $this->legacyHappyClientsNumber($pageData, $locale) ?: '1000+';
    }

    private function legacyHappyClientsNumber(?CmsPageData $pageData, string $locale): ?string
    {
        $rows = $pageData?->repeater('stats_items', $locale) ?? [];
        $fallback = null;

        foreach ($rows as $index => $row) {
            $number = trim((string) ($row->field('stat_number', $locale) ?? ''));
            $label = $this->normalize((string) ($row->field('stat_label', $locale) ?? ''));

            if ($index === 3 && $number !== '') {
                $fallback = $number;
            }

            if ($number !== '' && (str_contains($label, 'clientes felices') || str_contains($label, 'happy clients'))) {
                return $number;
            }
        }

        return $fallback;
    }

    private function publishedProperties(): Builder
    {
        return Property::query()->where('published', true);
    }

    private function publishedSaleProperties(): Builder
    {
        return $this->publishedProperties()
            ->whereHas('operations', fn (Builder $query) => $query->where('operation_type', 'sale'));
    }

    private function whereAnyTerm(Builder $query, array $columns, array $terms): void
    {
        foreach ($columns as $column) {
            foreach ($terms as $term) {
                $query->orWhereRaw("LOWER(COALESCE({$column}, '')) LIKE ?", ['%' . strtolower($term) . '%']);
            }
        }
    }

    private function formatCount(int $count): string
    {
        return number_format($count);
    }

    private function normalize(string $value): string
    {
        return strtolower(trim($value));
    }
}
