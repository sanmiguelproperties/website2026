<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Feature extends Model
{
    protected $fillable = [
        'name',
        'category',
        'locale',
    ];

    public function properties(): BelongsToMany
    {
        return $this->belongsToMany(Property::class, 'property_feature', 'feature_id', 'property_id');
    }
}

