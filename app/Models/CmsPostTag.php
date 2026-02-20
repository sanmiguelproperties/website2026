<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CmsPostTag extends Model
{
    protected $table = 'cms_post_tags';

    protected $fillable = [
        'slug',
        'name_es',
        'name_en',
    ];

    // ─── Helpers bilingües ──────────────────────────────

    public function name(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        return $locale === 'en' ? ($this->name_en ?? $this->name_es) : $this->name_es;
    }

    // ─── Relaciones ─────────────────────────────────────

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(
            CmsPost::class,
            'cms_post_tag',
            'cms_post_tag_id',
            'cms_post_id'
        );
    }
}
