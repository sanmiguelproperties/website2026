<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CmsFieldValue extends Model
{
    protected $table = 'cms_field_values';

    protected $fillable = [
        'field_definition_id',
        'entity_type',
        'entity_id',
        'value_es',
        'value_en',
        'media_asset_id',
        'parent_value_id',
        'row_index',
    ];

    // ─── Helpers bilingües ──────────────────────────────

    /**
     * Devuelve el valor según el locale actual.
     * Si el campo no es traducible, siempre devuelve value_es.
     */
    public function value(?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();

        // Si el campo no es traducible, devolver siempre value_es
        if ($this->fieldDefinition && !$this->fieldDefinition->is_translatable) {
            return $this->value_es;
        }

        return $locale === 'en' ? ($this->value_en ?? $this->value_es) : $this->value_es;
    }

    /**
     * Devuelve la URL del media asset asociado (para campos image/file).
     */
    public function mediaUrl(): ?string
    {
        return $this->mediaAsset?->url;
    }

    // ─── Relaciones ─────────────────────────────────────

    public function fieldDefinition(): BelongsTo
    {
        return $this->belongsTo(CmsFieldDefinition::class, 'field_definition_id');
    }

    public function mediaAsset(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'media_asset_id');
    }

    /**
     * Valor padre (para campos dentro de un repeater/group).
     */
    public function parentValue(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_value_id');
    }

    /**
     * Valores hijos (filas de un repeater / sub-campos de group).
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_value_id')->orderBy('row_index');
    }

    /**
     * Relación polimórfica manual: entidad asociada (page o post).
     */
    public function entity()
    {
        return match ($this->entity_type) {
            'page' => $this->belongsTo(CmsPage::class, 'entity_id'),
            'post' => $this->belongsTo(CmsPost::class, 'entity_id'),
            default => null,
        };
    }

    // ─── Scopes ─────────────────────────────────────────

    public function scopeForPage($query, int $pageId)
    {
        return $query->where('entity_type', 'page')->where('entity_id', $pageId);
    }

    public function scopeForPost($query, int $postId)
    {
        return $query->where('entity_type', 'post')->where('entity_id', $postId);
    }

    public function scopeForGlobal($query)
    {
        return $query->where('entity_type', 'global')->whereNull('entity_id');
    }

    public function scopeRootLevel($query)
    {
        return $query->whereNull('parent_value_id');
    }
}
