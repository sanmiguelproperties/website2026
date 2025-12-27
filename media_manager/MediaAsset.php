<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'created_at',
        'name',
        'alt',
    ];

    protected $casts = [
        'size_bytes' => 'integer',
        'duration_seconds' => 'integer',
        'created_at' => 'datetime',
    ];
}