<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agency extends Model
{
    /**
     * La PK es el `agency_id` de EasyBroker (no autoincrement).
     */
    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'name',
        'account_owner',
        'logo_url',
        'phone',
        'email',
        'raw_payload',
    ];

    protected $casts = [
        'raw_payload' => 'array',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'agency_id');
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class, 'agency_id');
    }
}


