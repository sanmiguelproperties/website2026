<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CmsMenuItem extends Model
{
    protected $table = 'cms_menu_items';

    protected $fillable = [
        'menu_id',
        'parent_id',
        'label_es',
        'label_en',
        'url',
        'route_name',
        'page_id',
        'target',
        'icon',
        'css_class',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ─── Helpers bilingües ──────────────────────────────

    public function label(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        return $locale === 'en' ? ($this->label_en ?? $this->label_es) : $this->label_es;
    }

    /**
     * Resuelve la URL final del item.
     * Prioridad: page_id > route_name > url
     */
    public function resolvedUrl(): ?string
    {
        // Si apunta a una página CMS
        if ($this->page_id && $this->page) {
            return '/' . $this->page->slug;
        }

        // Si tiene un route name de Laravel
        if ($this->route_name) {
            try {
                return route($this->route_name, [], false);
            } catch (\Exception $e) {
                return '#';
            }
        }

        // URL directa
        return $this->url;
    }

    // ─── Relaciones ─────────────────────────────────────

    public function menu(): BelongsTo
    {
        return $this->belongsTo(CmsMenu::class, 'menu_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(CmsPage::class, 'page_id');
    }

    // ─── Scopes ─────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
