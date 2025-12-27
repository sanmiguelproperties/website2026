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
        'region',
        'city',
        'city_area',
        'street',
        'postal_code',
        'show_exact_location',
        'latitude',
        'longitude',
        'raw_payload',
    ];

    protected $casts = [
        'show_exact_location' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'raw_payload' => 'array',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id');
    }
}

