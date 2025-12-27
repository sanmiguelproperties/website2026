<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EasybrokerPropertyListingStatus extends Model
{
    protected $table = 'easybroker_property_listing_statuses';

    public $timestamps = false;

    protected $fillable = [
        'id',
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
}

