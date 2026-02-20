<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CmsPost extends Model
{
    protected $table = 'cms_posts';

    protected $fillable = [
        'slug',
        'title_es',
        'title_en',
        'excerpt_es',
        'excerpt_en',
        'body_es',
        'body_en',
        'cover_media_asset_id',
        'author_id',
        'status',
        'is_featured',
        'published_at',
        'meta_title_es',
        'meta_title_en',
        'meta_description_es',
        'meta_description_en',
        'sort_order',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
    ];

    // ─── Helpers bilingües ──────────────────────────────

    public function title(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        return $locale === 'en' ? ($this->title_en ?? $this->title_es) : $this->title_es;
    }

    public function excerpt(?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();
        return $locale === 'en' ? ($this->excerpt_en ?? $this->excerpt_es) : $this->excerpt_es;
    }

    public function body(?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();
        return $locale === 'en' ? ($this->body_en ?? $this->body_es) : $this->body_es;
    }

    public function metaTitle(?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();
        return $locale === 'en' ? ($this->meta_title_en ?? $this->meta_title_es) : $this->meta_title_es;
    }

    public function metaDescription(?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();
        return $locale === 'en' ? ($this->meta_description_en ?? $this->meta_description_es) : $this->meta_description_es;
    }

    // ─── Relaciones ─────────────────────────────────────

    public function coverMediaAsset(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'cover_media_asset_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            CmsPostCategory::class,
            'cms_post_category',
            'cms_post_id',
            'cms_post_category_id'
        );
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            CmsPostTag::class,
            'cms_post_tag',
            'cms_post_id',
            'cms_post_tag_id'
        );
    }

    /**
     * Valores de campo asociados a este post.
     */
    public function fieldValues(): HasMany
    {
        return $this->hasMany(CmsFieldValue::class, 'entity_id')
            ->where('entity_type', 'post');
    }

    // ─── Scopes ─────────────────────────────────────────

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeBySlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }
}
