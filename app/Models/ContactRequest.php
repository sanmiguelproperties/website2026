<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactRequest extends Model
{
    protected $fillable = [
        'agency_id',
        'property_id',
        'property_public_id',
        'remote_id',
        'source',
        'name',
        'email',
        'phone',
        'message',
        'happened_at',
        'status',
        'sent_to_easybroker_at',
        'raw_payload',
    ];

    protected $casts = [
        'happened_at' => 'datetime',
        'sent_to_easybroker_at' => 'datetime',
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

