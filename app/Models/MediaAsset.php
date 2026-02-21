<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class MediaAsset extends Model
{
    use SoftDeletes;

    protected $table = 'media_assets';

    public $timestamps = false; // Solo created_at

    protected $fillable = [
        'type',
        'provider',
        'url',
        'storage_path',
        'mime_type',
        'size_bytes',
        'duration_seconds',
        'checksum',
        'created_at',
        'downloaded_at',
        'name',
        'alt',
    ];

    protected $casts = [
        'size_bytes' => 'integer',
        'duration_seconds' => 'integer',
        'checksum' => 'string',
        'created_at' => 'datetime',
        'downloaded_at' => 'datetime',
    ];

    /**
     * Atributos agregados a la serialización JSON.
     * `serving_url` devuelve la URL local si el archivo fue descargado,
     * o la URL remota original como fallback.
     */
    protected $appends = ['serving_url'];

    /**
     * Retorna la URL desde la cual servir este media asset:
     * - Si tiene storage_path y el archivo existe localmente → URL local (APP_URL/storage/...)
     * - Si no tiene storage_path → URL original (remota)
     */
    public function getServingUrlAttribute(): ?string
    {
        // Si tiene path local, generar URL desde storage público
        if (!empty($this->storage_path)) {
            return Storage::disk('public')->url($this->storage_path);
        }

        // Fallback: URL original (remota o la que tenga)
        return $this->url;
    }
}