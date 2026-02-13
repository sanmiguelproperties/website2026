<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Modelo para agentes del MLS AMPI San Miguel de Allende.
 * 
 * Los agentes se sincronizan desde la API del MLS y se almacenan localmente
 * para poder relacionarlos con las propiedades.
 */
class MLSAgent extends Model
{
    protected $table = 'mls_agents';

    protected $fillable = [
        'mls_agent_id',
        'name',
        'first_name',
        'last_name',
        'email',
        'phone',
        'mobile',
        'fax',
        'address',
        'state_province',
        'city',
        'mls_office_id',
        'office_name',
        'photo_url',
        'photo_media_asset_id',
        'license_number',
        'bio',
        'bio_es',
        'website',
        'facebook',
        'instagram',
        'x_twitter',
        'tiktok',
        'youtube',
        'pinterest',
        'linkedin',
        'is_active',
        'user_id',
        'last_synced_at',
        'raw_payload',
    ];

    protected $casts = [
        'mls_agent_id' => 'integer',
        'mls_office_id' => 'integer',
        'is_active' => 'boolean',
        'last_synced_at' => 'datetime',
        'raw_payload' => 'array',
    ];

    /**
     * Oficina MLS a la que pertenece el agente.
     */
    public function office(): BelongsTo
    {
        return $this->belongsTo(MLSOffice::class, 'mls_office_id', 'mls_office_id');
    }

    /**
     * Atributos que se añaden automáticamente a la serialización JSON.
     */
    protected $appends = ['photo', 'has_photo', 'full_name'];

    /**
     * Relación con el usuario local vinculado.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relación con el MediaAsset de la foto del agente.
     */
    public function photoMediaAsset(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'photo_media_asset_id');
    }

    /**
     * Propiedades asociadas a este agente.
     */
    public function properties(): BelongsToMany
    {
        return $this->belongsToMany(Property::class, 'property_mls_agent', 'mls_agent_id', 'property_id')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    /**
     * Scope para agentes activos.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para buscar por oficina MLS.
     */
    public function scopeFromOffice($query, int $officeId)
    {
        return $query->where('mls_office_id', $officeId);
    }

    /**
     * Busca un agente por su ID del MLS.
     */
    public static function findByMlsId(int $mlsAgentId): ?self
    {
        return static::where('mls_agent_id', $mlsAgentId)->first();
    }

    /**
     * Obtiene el nombre completo del agente.
     */
    public function getFullNameAttribute(): string
    {
        if ($this->first_name || $this->last_name) {
            return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
        }
        return $this->name ?? 'Agente #' . $this->mls_agent_id;
    }

    /**
     * Obtiene la URL de la foto (local descargada > URL del MediaAsset > URL directa del MLS).
     */
    public function getPhotoAttribute(): ?string
    {
        // Prioridad 1: Foto local descargada (storage_path del MediaAsset)
        if ($this->photoMediaAsset) {
            if ($this->photoMediaAsset->storage_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->photoMediaAsset->storage_path)) {
                return \Illuminate\Support\Facades\Storage::disk('public')->url($this->photoMediaAsset->storage_path);
            }
            // Prioridad 2: URL del MediaAsset (URL remota registrada)
            if ($this->photoMediaAsset->url) {
                return $this->photoMediaAsset->url;
            }
        }
        // Prioridad 3: URL directa del MLS (campo photo_url)
        return $this->photo_url;
    }

    /**
     * Verifica si el agente tiene foto de perfil disponible.
     */
    public function getHasPhotoAttribute(): bool
    {
        return !empty($this->photo);
    }
}
