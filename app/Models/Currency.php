<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = [
        'name',
        'code',
        'symbol',
        'exchange_rate',
        'is_base',
    ];

    protected $casts = [
        'exchange_rate' => 'decimal:6',
        'is_base' => 'boolean',
    ];

    /**
     * Scope para obtener la moneda base.
     */
    public function scopeBase($query)
    {
        return $query->where('is_base', true);
    }

    /**
     * Método para convertir un monto a la moneda base.
     */
    public function convertToBase($amount)
    {
        $baseCurrency = self::base()->first();

        if (!$baseCurrency) {
            throw new \Exception('No se encontró una moneda base.');
        }

        return $amount / $this->exchange_rate * $baseCurrency->exchange_rate;
    }
}
