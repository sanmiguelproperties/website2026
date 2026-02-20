<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CmsFieldDefinition extends Model
{
    protected $table = 'cms_field_definitions';

    /**
     * Tipos de campo soportados.
     */
    const TYPES = [
        'text',
        'textarea',
        'wysiwyg',
        'number',
        'url',
        'email',
        'phone',
        'image',
        'gallery',
        'file',
        'select',
        'checkbox',
        'radio',
        'boolean',
        'color',
        'date',
        'datetime',
        'link',
        'repeater',
        'group',
        'icon',
    ];

    /**
     * Tipos que no necesitan traducción.
     */
    const NON_TRANSLATABLE_TYPES = [
        'number',
        'boolean',
        'color',
        'date',
        'datetime',
        'image',
        'gallery',
        'file',
        'email',
        'phone',
    ];

    protected $fillable = [
        'field_group_id',
        'parent_id',
        'field_key',
        'type',
        'label_es',
        'label_en',
        'instructions_es',
        'instructions_en',
        'placeholder_es',
        'placeholder_en',
        'default_value_es',
        'default_value_en',
        'validation_rules',
        'options',
        'is_required',
        'is_translatable',
        'char_limit',
        'sort_order',
    ];

    protected $casts = [
        'validation_rules' => 'array',
        'options' => 'array',
        'is_required' => 'boolean',
        'is_translatable' => 'boolean',
    ];

    // ─── Helpers bilingües ──────────────────────────────

    public function label(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        return $locale === 'en' ? ($this->label_en ?? $this->label_es) : $this->label_es;
    }

    public function instructions(?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();
        return $locale === 'en' ? ($this->instructions_en ?? $this->instructions_es) : $this->instructions_es;
    }

    public function placeholder(?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();
        return $locale === 'en' ? ($this->placeholder_en ?? $this->placeholder_es) : $this->placeholder_es;
    }

    // ─── Helpers de tipo ────────────────────────────────

    public function isRepeater(): bool
    {
        return $this->type === 'repeater';
    }

    public function isGroup(): bool
    {
        return $this->type === 'group';
    }

    public function isMediaField(): bool
    {
        return in_array($this->type, ['image', 'gallery', 'file']);
    }

    public function hasSubFields(): bool
    {
        return $this->isRepeater() || $this->isGroup();
    }

    // ─── Relaciones ─────────────────────────────────────

    public function fieldGroup(): BelongsTo
    {
        return $this->belongsTo(CmsFieldGroup::class, 'field_group_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Sub-campos (para repeater / group).
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * Todos los valores almacenados para este campo.
     */
    public function values(): HasMany
    {
        return $this->hasMany(CmsFieldValue::class, 'field_definition_id');
    }
}
