<?php

namespace App\Models;

use App\Support\Rbac;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ManualSection extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'description',
        'icon',
        'required_permission',
        'tutorial_video_id',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'tutorial_video_id' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function articles(): HasMany
    {
        return $this->hasMany(ManualArticle::class);
    }

    public function video(): BelongsTo
    {
        return $this->belongsTo(TutorialVideo::class, 'tutorial_video_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('sort_order')
            ->orderBy('title');
    }

    public function isVisibleTo(?Authenticatable $user): bool
    {
        return ! $this->required_permission
            || Rbac::canAny($user, $this->required_permission);
    }
}
