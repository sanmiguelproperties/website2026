<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Property extends Model
{
    /**
     * Constantes para el campo source (origen de la propiedad)
     */
    const SOURCE_MANUAL = 'manual';
    const SOURCE_EASYBROKER = 'easybroker';
    const SOURCE_MLS = 'mls';

    protected $fillable = [
        'agency_id',
        'source',
        'agent_user_id',
        
        // Campos de EasyBroker
        'easybroker_public_id',
        'easybroker_agent_id',
        
        // Campos del MLS
        'mls_id',
        'mls_public_id',
        'mls_folder_name',
        'mls_neighborhood',
        'mls_office_id',
        
        // Estado y publicación
        'published',
        'status',
        'category',
        'is_approved',
        'allow_integration',
        'for_rent',
        
        // Fechas de sincronización
        'easybroker_created_at',
        'easybroker_updated_at',
        'mls_created_at',
        'mls_updated_at',
        'last_synced_at',
        
        // Contenido básico
        'title',
        'description',
        'description_short_en',
        'description_full_en',
        'description_short_es',
        'description_full_es',
        'url',
        'ad_type',
        'property_type_name',
        
        // Características numéricas
        'bedrooms',
        'bathrooms',
        'half_bathrooms',
        'parking_spaces',
        'parking_number',
        'parking_type',
        'lot_size',
        'lot_feet',
        'construction_size',
        'construction_feet',
        'expenses',
        'old_price',
        'payment',
        'selling_office_commission',
        'showing_terms',
        'lot_length',
        'lot_width',
        'floors',
        'floor',
        'age',
        'year_built',
        
        // Características adicionales del MLS
        'furnished',
        'with_yard',
        'with_view',
        'gated_comm',
        'pool',
        'casita',
        'casita_bedrooms',
        'casita_bathrooms',
        
        'virtual_tour_url',
        'video_url',
        'cover_media_asset_id',
        'raw_payload',
    ];

    protected $casts = [
        'published' => 'boolean',
        'is_approved' => 'boolean',
        'allow_integration' => 'boolean',
        'for_rent' => 'boolean',
        'with_yard' => 'boolean',
        'gated_comm' => 'boolean',
        'pool' => 'boolean',
        'casita' => 'boolean',
        'easybroker_created_at' => 'datetime',
        'easybroker_updated_at' => 'datetime',
        'mls_created_at' => 'datetime',
        'mls_updated_at' => 'datetime',
        'last_synced_at' => 'datetime',
        'bathrooms' => 'decimal:1',
        'lot_size' => 'decimal:2',
        'lot_feet' => 'decimal:2',
        'construction_size' => 'decimal:2',
        'construction_feet' => 'decimal:2',
        'expenses' => 'decimal:2',
        'old_price' => 'decimal:2',
        'lot_length' => 'decimal:2',
        'lot_width' => 'decimal:2',
        'raw_payload' => 'array',
    ];

    /**
     * Scope para filtrar propiedades por origen.
     */
    public function scopeFromSource($query, string $source)
    {
        return $query->where('source', $source);
    }

    /**
     * Scope para propiedades de EasyBroker.
     */
    public function scopeFromEasyBroker($query)
    {
        return $query->fromSource(self::SOURCE_EASYBROKER);
    }

    /**
     * Scope para propiedades del MLS.
     */
    public function scopeFromMLS($query)
    {
        return $query->fromSource(self::SOURCE_MLS);
    }

    /**
     * Scope para propiedades manuales.
     */
    public function scopeManual($query)
    {
        return $query->fromSource(self::SOURCE_MANUAL);
    }

    /**
     * Verifica si la propiedad es de EasyBroker.
     */
    public function isFromEasyBroker(): bool
    {
        return $this->source === self::SOURCE_EASYBROKER;
    }

    /**
     * Verifica si la propiedad es del MLS.
     */
    public function isFromMLS(): bool
    {
        return $this->source === self::SOURCE_MLS;
    }

    /**
     * Verifica si la propiedad fue creada manualmente.
     */
    public function isManual(): bool
    {
        return $this->source === self::SOURCE_MANUAL;
    }

    /**
     * Obtiene el ID externo según el origen.
     */
    public function getExternalIdAttribute(): ?string
    {
        return match ($this->source) {
            self::SOURCE_EASYBROKER => $this->easybroker_public_id,
            self::SOURCE_MLS => $this->mls_public_id,
            default => null,
        };
    }

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

