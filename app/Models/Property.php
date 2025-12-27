<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Property extends Model
{
    protected $fillable = [
        'agency_id',
        'agent_user_id',
        'easybroker_public_id',
        'easybroker_agent_id',
        'published',
        'easybroker_created_at',
        'easybroker_updated_at',
        'last_synced_at',
        'title',
        'description',
        'url',
        'ad_type',
        'property_type_name',
        'bedrooms',
        'bathrooms',
        'half_bathrooms',
        'parking_spaces',
        'lot_size',
        'construction_size',
        'expenses',
        'lot_length',
        'lot_width',
        'floors',
        'floor',
        'age',
        'virtual_tour_url',
        'cover_media_asset_id',
        'raw_payload',
    ];

    protected $casts = [
        'published' => 'boolean',
        'easybroker_created_at' => 'datetime',
        'easybroker_updated_at' => 'datetime',
        'last_synced_at' => 'datetime',
        'lot_size' => 'decimal:2',
        'construction_size' => 'decimal:2',
        'expenses' => 'decimal:2',
        'lot_length' => 'decimal:2',
        'lot_width' => 'decimal:2',
        'raw_payload' => 'array',
    ];

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class, 'agency_id');
    }

    public function agentUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_user_id');
    }

    public function coverMediaAsset(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'cover_media_asset_id');
    }

    public function location(): HasOne
    {
        return $this->hasOne(PropertyLocation::class, 'property_id');
    }

    public function operations(): HasMany
    {
        return $this->hasMany(PropertyOperation::class, 'property_id');
    }

    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class, 'property_feature', 'property_id', 'feature_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'property_tag', 'property_id', 'tag_id');
    }

    public function mediaAssets(): BelongsToMany
    {
        return $this->belongsToMany(MediaAsset::class, 'property_media_assets', 'property_id', 'media_asset_id')
            ->withPivot(['role', 'title', 'position', 'checksum', 'source_url', 'raw_payload']);
    }
}

