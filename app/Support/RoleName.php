<?php

namespace App\Support;

class RoleName
{
    public static function normalize(mixed $name): string
    {
        return strtolower(trim((string) $name));
    }

    /**
     * @return array<int, string>
     */
    public static function normalizeMany(iterable $names): array
    {
        return collect($names)
            ->map(fn ($name) => self::normalize($name))
            ->filter(fn (string $name) => $name !== '')
            ->unique()
            ->values()
            ->all();
    }
}
