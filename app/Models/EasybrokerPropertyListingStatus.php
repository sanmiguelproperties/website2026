<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo para el tracking de listing statuses de EasyBroker.
 *
 * Esta tabla no tiene columna `id` autoincremental. La clave primaria
 * es compuesta: (agency_id, easybroker_public_id).
 */
class EasybrokerPropertyListingStatus extends Model
{
    protected $table = 'easybroker_property_listing_statuses';

    /**
     * Indica que el modelo no tiene auto-incrementing ID.
     */
    public $incrementing = false;

    /**
     * No hay columna de clave primaria simple.
     */
    protected $primaryKey = null;

    /**
     * No hay timestamps.
     */
    public $timestamps = false;

    protected $fillable = [
        'agency_id',
        'easybroker_public_id',
        'property_id',
        'published',
        'easybroker_updated_at',
        'last_polled_at',
        'raw_payload',
    ];

    protected $casts = [
        'published' => 'boolean',
        'easybroker_updated_at' => 'datetime',
        'last_polled_at' => 'datetime',
        'raw_payload' => 'array',
    ];

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class, 'agency_id');
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    /**
     * Busca o crea un registro por su clave compuesta.
     */
    public static function findOrCreateByKey(int $agencyId, string $publicId, array $attributes = []): self
    {
        $record = static::where('agency_id', $agencyId)
            ->where('easybroker_public_id', $publicId)
            ->first();

        if ($record) {
            return $record;
        }

        return static::create(array_merge([
            'agency_id' => $agencyId,
            'easybroker_public_id' => $publicId,
        ], $attributes));
    }

    /**
     * Actualiza o crea un registro por su clave compuesta.
     *
     * Nota: No se puede usar el método update() de Eloquent porque la tabla
     * no tiene una clave primaria simple. Usamos query builder directamente.
     */
    public static function updateOrCreateByKey(int $agencyId, string $publicId, array $attributes): self
    {
        $exists = static::where('agency_id', $agencyId)
            ->where('easybroker_public_id', $publicId)
            ->exists();

        if ($exists) {
            // Usar query builder directamente para evitar el problema de la clave primaria
            static::where('agency_id', $agencyId)
                ->where('easybroker_public_id', $publicId)
                ->update($attributes);

            return static::where('agency_id', $agencyId)
                ->where('easybroker_public_id', $publicId)
                ->first();
        }

        return static::create(array_merge([
            'agency_id' => $agencyId,
            'easybroker_public_id' => $publicId,
        ], $attributes));
    }
}

