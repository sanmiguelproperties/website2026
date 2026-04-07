<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZonePage extends Model
{
    protected $table = 'zone_pages';

    protected $fillable = [
        'slug',
        'region',
        'city',
        'city_area',
        'region_key',
        'city_key',
        'city_area_key',
        'title_es',
        'title_en',
        'description_es',
        'description_en',
        'meta_title_es',
        'meta_title_en',
        'meta_description_es',
        'meta_description_en',
        'is_active',
        'last_detected_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_detected_at' => 'datetime',
    ];

    public function title(?string $locale = null): string
    {
        $locale = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'es';

        if ($locale === 'en') {
            return trim((string) ($this->title_en ?: $this->title_es ?: $this->city_area));
        }

        return trim((string) ($this->title_es ?: $this->title_en ?: $this->city_area));
    }

    public function description(?string $locale = null): ?string
    {
        $locale = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'es';

        if ($locale === 'en') {
            $value = $this->description_en ?: $this->description_es;
            return $value !== null ? trim((string) $value) : null;
        }

        $value = $this->description_es ?: $this->description_en;
        return $value !== null ? trim((string) $value) : null;
    }

    public function metaTitle(?string $locale = null): ?string
    {
        $locale = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'es';

        if ($locale === 'en') {
            return $this->meta_title_en ?: $this->meta_title_es;
        }

        return $this->meta_title_es ?: $this->meta_title_en;
    }

    public function metaDescription(?string $locale = null): ?string
    {
        $locale = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'es';

        if ($locale === 'en') {
            return $this->meta_description_en ?: $this->meta_description_es;
        }

        return $this->meta_description_es ?: $this->meta_description_en;
    }
}

