<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

/**
 * Oficina/Agencia proveniente del MLS AMPI (endpoint: /api/v1/offices).
 *
 * Nota: el API usa el nombre "offices" para representar agencias/oficinas.
 */
class MLSOffice extends Model
{
    protected $table = 'mls_offices';

    /**
     * Usamos el ID del MLS como PK (no autoincrement).
     */
    protected $primaryKey = 'mls_office_id';
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'mls_office_id',
        'name',
        'business_hours',
        'state_province',
        'city',
        'address',
        'zip_code',
        'latitude',
        'longitude',
        'image_path',
        'image_url',
        'image_media_asset_id',
        'description',
        'description_es',
        'phone_1',
        'phone_2',
        'phone_3',
        'fax',
        'email',
        'website',
        'facebook',
        'youtube',
        'x_twitter',
        'tiktok',
        'instagram',
        'paid',
        // NOTA: is_managed_by_us es un campo manual y NO debería actualizarse vía sync.
        // Se edita por endpoint dedicado (manual) desde el dashboard.
        'mls_created_at',
        'mls_updated_at',
        'last_synced_at',
        'raw_payload',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'paid' => 'boolean',
        'is_managed_by_us' => 'boolean',
        'mls_created_at' => 'datetime',
        'mls_updated_at' => 'datetime',
        'last_synced_at' => 'datetime',
        'raw_payload' => 'array',
    ];

    /**
     * Atributos que se añaden automáticamente a la serialización JSON.
     */
    protected $appends = ['image', 'has_image'];

    public function imageMediaAsset(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'image_media_asset_id');
    }

    /**
     * URL de imagen (prioridad: archivo local > url de MediaAsset > image_url > image_path).
     */
    public function getImageAttribute(): ?string
    {
        if ($this->relationLoaded('imageMediaAsset') && $this->imageMediaAsset) {
            $asset = $this->imageMediaAsset;

            if (!empty($asset->storage_path) && Storage::disk('public')->exists($asset->storage_path)) {
                return Storage::disk('public')->url($asset->storage_path);
            }

            if (!empty($asset->url)) {
                return $asset->url;
            }
        }

        if (!empty($this->image_url)) {
            return $this->image_url;
        }

        // image_path suele venir como path relativo del MLS (ej: "offices/xxx.jpg").
        // Si en el futuro se define una base URL en configuración, se puede resolver aquí.
        return !empty($this->image_path) ? $this->image_path : null;
    }

    public function getHasImageAttribute(): bool
    {
        return !empty($this->image);
    }

    public function agents(): HasMany
    {
        return $this->hasMany(MLSAgent::class, 'mls_office_id', 'mls_office_id');
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class, 'mls_office_id', 'mls_office_id');
    }
}

