<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CmsPage extends Model
{
    protected $table = 'cms_pages';

    protected $fillable = [
        'slug',
        'title_es',
        'title_en',
        'meta_title_es',
        'meta_title_en',
        'meta_description_es',
        'meta_description_en',
        'meta_keywords_es',
        'meta_keywords_en',
        'template',
        'status',
        'is_active',
        'sort_order',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ─── Helpers bilingües ──────────────────────────────

    /**
     * Devuelve el título según el locale actual.
     */
    public function title(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        return $locale === 'en' ? ($this->title_en ?? $this->title_es) : $this->title_es;
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Valores de campo asociados a esta página.
     */
    public function fieldValues(): HasMany
    {
        return $this->hasMany(CmsFieldValue::class, 'entity_id')
            ->where('entity_type', 'page');
    }

    // ─── Scopes ─────────────────────────────────────────

    public function scopePublished($query)
    {
        return $query->where('status', 'published')->where('is_active', true);
    }

    public function scopeBySlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }
}
