<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CmsFieldGroup extends Model
{
    protected $table = 'cms_field_groups';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'location_type',
        'location_identifier',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ─── Relaciones ─────────────────────────────────────

    /**
     * Definiciones de campo de este grupo (solo raíz, sin sub-campos).
     */
    public function fieldDefinitions(): HasMany
    {
        return $this->hasMany(CmsFieldDefinition::class, 'field_group_id')
            ->whereNull('parent_id')
            ->orderBy('sort_order');
    }

    /**
     * TODAS las definiciones de campo (incluidas sub-campos de repeaters).
     */
    public function allFieldDefinitions(): HasMany
    {
        return $this->hasMany(CmsFieldDefinition::class, 'field_group_id')
            ->orderBy('sort_order');
    }

    // ─── Scopes ─────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Grupos asignados a una página específica.
     */
    public function scopeForPage($query, string $pageSlug)
    {
        return $query->where('location_type', 'page')
            ->where(function ($q) use ($pageSlug) {
                $q->where('location_identifier', $pageSlug)
                  ->orWhereNull('location_identifier');
            });
    }

    /**
     * Grupos asignados a posts (todos o específicos).
     */
    public function scopeForPost($query, ?string $postSlug = null)
    {
        return $query->where('location_type', 'post')
            ->where(function ($q) use ($postSlug) {
                $q->whereNull('location_identifier');
                if ($postSlug) {
                    $q->orWhere('location_identifier', $postSlug);
                }
            });
    }

    /**
     * Grupos globales.
     */
    public function scopeGlobal($query)
    {
        return $query->where('location_type', 'global');
    }
}
