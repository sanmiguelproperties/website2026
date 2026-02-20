<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CmsPostCategory extends Model
{
    protected $table = 'cms_post_categories';

    protected $fillable = [
        'slug',
        'name_es',
        'name_en',
        'description_es',
        'description_en',
        'cover_media_asset_id',
        'parent_id',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ─── Helpers bilingües ──────────────────────────────

    public function name(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        return $locale === 'en' ? ($this->name_en ?? $this->name_es) : $this->name_es;
    }

    public function description(?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();
        return $locale === 'en' ? ($this->description_en ?? $this->description_es) : $this->description_es;
    }

    // ─── Relaciones ─────────────────────────────────────

    public function coverMediaAsset(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'cover_media_asset_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(
            CmsPost::class,
            'cms_post_category',
            'cms_post_category_id',
            'cms_post_id'
        );
    }

    // ─── Scopes ─────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }
}
