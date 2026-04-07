<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyLocation extends Model
{
    protected $primaryKey = 'property_id';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'property_id',
        'country',
        'region',
        'state_catalog_id',
        'city',
        'city_catalog_id',
        'city_area',
        'neighborhood_catalog_id',
        'street',
        'postal_code',
        'show_exact_location',
        'latitude',
        'longitude',
        'raw_payload',
    ];

    protected $casts = [
        'state_catalog_id' => 'integer',
        'city_catalog_id' => 'integer',
        'neighborhood_catalog_id' => 'integer',
        'show_exact_location' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'raw_payload' => 'array',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    public function stateCatalog(): BelongsTo
    {
        return $this->belongsTo(LocationCatalog::class, 'state_catalog_id');
    }

    public function cityCatalog(): BelongsTo
    {
        return $this->belongsTo(LocationCatalog::class, 'city_catalog_id');
    }

    public function neighborhoodCatalog(): BelongsTo
    {
        return $this->belongsTo(LocationCatalog::class, 'neighborhood_catalog_id');
    }
}

