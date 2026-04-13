<?php

namespace App\Models;

use App\Support\PriceFormatter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyOperation extends Model
{
    protected $fillable = [
        'property_id',
        'operation_type',
        'amount',
        'currency_id',
        'currency_code',
        'formatted_amount',
        'unit',
        'raw_payload',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'raw_payload' => 'array',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function getFormattedAmountAttribute(?string $value): ?string
    {
        $currencyCode = $this->attributes['currency_code'] ?? null;
        if (($currencyCode === null || $currencyCode === '') && $this->relationLoaded('currency')) {
            $currencyCode = $this->currency?->code;
        }

        $formattedFromAmount = PriceFormatter::format(
            $this->attributes['amount'] ?? null,
            $currencyCode
        );

        if ($formattedFromAmount !== null) {
            return $formattedFromAmount;
        }

        $parsedAmount = PriceFormatter::extractNumericAmount($value);
        if ($parsedAmount !== null) {
            $formattedFromString = PriceFormatter::format($parsedAmount, $currencyCode);
            if ($formattedFromString !== null) {
                return $formattedFromString;
            }
        }

        return PriceFormatter::ensureCurrencySuffix($value, $currencyCode);
    }
}

