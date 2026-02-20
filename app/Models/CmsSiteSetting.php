<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class CmsSiteSetting extends Model
{
    protected $table = 'cms_site_settings';

    const CACHE_KEY = 'cms_site_settings';
    const CACHE_TTL = 3600; // 1 hora

    protected $fillable = [
        'setting_key',
        'setting_group',
        'label_es',
        'label_en',
        'type',
        'value_es',
        'value_en',
        'media_asset_id',
        'sort_order',
    ];

    // ─── Helpers bilingües ──────────────────────────────

    public function label(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        return $locale === 'en' ? ($this->label_en ?? $this->label_es) : $this->label_es;
    }

    public function value(?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();

        // Tipos que no se traducen
        $nonTranslatable = ['email', 'phone', 'boolean', 'image'];
        if (in_array($this->type, $nonTranslatable)) {
            return $this->value_es;
        }

        return $locale === 'en' ? ($this->value_en ?? $this->value_es) : $this->value_es;
    }

    // ─── Relaciones ─────────────────────────────────────

    public function mediaAsset(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'media_asset_id');
    }

    // ─── Métodos estáticos ──────────────────────────────

    /**
     * Obtener un setting por clave.
     */
    public static function get(string $key, ?string $locale = null): ?string
    {
        $settings = self::getAllCached();
        $setting = $settings->firstWhere('setting_key', $key);
        return $setting?->value($locale);
    }

    /**
     * Obtener settings de un grupo.
     */
    public static function getGroup(string $group, ?string $locale = null): array
    {
        $settings = self::getAllCached()->where('setting_group', $group);
        $result = [];
        foreach ($settings as $setting) {
            $result[$setting->setting_key] = $setting->value($locale);
        }
        return $result;
    }

    /**
     * Obtener todos los settings cacheados.
     */
    public static function getAllCached()
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return self::with('mediaAsset')->orderBy('setting_group')->orderBy('sort_order')->get();
        });
    }

    /**
     * Limpiar el cache.
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    // ─── Scopes ─────────────────────────────────────────

    public function scopeByGroup($query, string $group)
    {
        return $query->where('setting_group', $group);
    }

    // ─── Boot ───────────────────────────────────────────

    protected static function boot()
    {
        parent::boot();

        static::saved(function () {
            self::clearCache();
        });

        static::deleted(function () {
            self::clearCache();
        });
    }
}
