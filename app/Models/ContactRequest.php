<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'agency_id',
        'property_id',
        'owner_id',
        'property_public_id',
        'remote_id',
        'source',
        'name',
        'email',
        'phone',
        'message',
        'happened_at',
        'status',
        'assignment_status',
        'assigned_at',
        'sent_to_easybroker_at',
        'raw_payload',
    ];

    protected $casts = [
        'happened_at' => 'datetime',
        'assigned_at' => 'datetime',
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

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(ContactNote::class);
    }
}

