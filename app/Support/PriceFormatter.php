<?php

namespace App\Support;

final class PriceFormatter
{
    /**
     * Formats prices as:
     * - $12,345,678 MXN
     * - $12,345,678.90 USD
     */
    public static function format(float|int|string|null $amount, ?string $currencyCode = null, string $fallbackCode = 'MXN'): ?string
    {
        if ($amount === null || $amount === '' || !is_numeric($amount)) {
            return null;
        }

        $numericAmount = round((float) $amount, 2);
        $normalizedCode = self::normalizeCurrencyCode($currencyCode, $fallbackCode);
        $decimals = self::hasCents($numericAmount) ? 2 : 0;
        $formattedNumber = number_format($numericAmount, $decimals, '.', ',');
        $symbol = self::symbolFor($normalizedCode);

        if ($symbol === '') {
            return sprintf('%s %s', $formattedNumber, $normalizedCode);
        }

        return sprintf('%s%s %s', $symbol, $formattedNumber, $normalizedCode);
    }

    public static function normalizeCurrencyCode(?string $currencyCode, string $fallbackCode = 'MXN'): string
    {
        $normalized = strtoupper(trim((string) $currencyCode));
        if ($normalized === '') {
            return strtoupper(trim($fallbackCode)) ?: 'MXN';
        }

        return $normalized;
    }

    public static function extractNumericAmount(?string $value): ?float
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        $clean = preg_replace('/[^0-9,.\-]/', '', $raw);
        if ($clean === null || $clean === '') {
            return null;
        }

        $lastDot = strrpos($clean, '.');
        $lastComma = strrpos($clean, ',');

        if ($lastDot !== false && $lastComma !== false) {
            if ($lastDot > $lastComma) {
                $normalized = str_replace(',', '', $clean);
            } else {
                $normalized = str_replace('.', '', $clean);
                $normalized = str_replace(',', '.', $normalized);
            }
        } elseif ($lastComma !== false) {
            $commaCount = substr_count($clean, ',');
            if ($commaCount > 1) {
                $normalized = str_replace(',', '', $clean);
            } else {
                $parts = explode(',', $clean);
                $normalized = strlen((string) end($parts)) <= 2
                    ? str_replace(',', '.', $clean)
                    : str_replace(',', '', $clean);
            }
        } elseif ($lastDot !== false) {
            $dotCount = substr_count($clean, '.');
            if ($dotCount > 1) {
                $normalized = str_replace('.', '', $clean);
            } else {
                $parts = explode('.', $clean);
                $normalized = strlen((string) end($parts)) <= 2
                    ? $clean
                    : str_replace('.', '', $clean);
            }
        } else {
            $normalized = $clean;
        }

        if (!is_numeric($normalized)) {
            return null;
        }

        return round((float) $normalized, 2);
    }

    public static function ensureCurrencySuffix(?string $value, ?string $currencyCode = null, string $fallbackCode = 'MXN'): ?string
    {
        $trimmed = trim((string) $value);
        if ($trimmed === '') {
            return null;
        }

        $normalizedCode = self::normalizeCurrencyCode($currencyCode, $fallbackCode);

        if (preg_match('/\b([A-Za-z]{3})\s*$/', $trimmed, $matches) === 1) {
            $existingCode = strtoupper($matches[1]);
            $replacement = preg_replace('/\b([A-Za-z]{3})\s*$/', $existingCode, $trimmed);

            return $replacement ?: $trimmed;
        }

        return sprintf('%s %s', $trimmed, $normalizedCode);
    }

    private static function hasCents(float $amount): bool
    {
        return abs($amount - round($amount)) > 0.00001;
    }

    private static function symbolFor(string $currencyCode): string
    {
        return in_array($currencyCode, ['MXN', 'USD'], true) ? '$' : '';
    }
}
