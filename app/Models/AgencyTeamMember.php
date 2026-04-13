<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgencyTeamMember extends Model
{
    protected $table = 'agency_team_members';

    protected $fillable = [
        'full_name',
        'position_es',
        'position_en',
        'department_es',
        'department_en',
        'bio_es',
        'bio_en',
        'specialties_es',
        'specialties_en',
        'email',
        'phone',
        'whatsapp',
        'linkedin_url',
        'photo_media_asset_id',
        'sort_order',
        'is_active',
        'is_featured',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function photoMediaAsset(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'photo_media_asset_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('full_name');
    }

    public function position(?string $locale = null): string
    {
        $locale = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'es';

        if ($locale === 'en') {
            return (string) ($this->position_en ?: $this->position_es);
        }

        return (string) ($this->position_es ?: $this->position_en);
    }

    public function department(?string $locale = null): ?string
    {
        $locale = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'es';

        if ($locale === 'en') {
            return $this->department_en ?: $this->department_es;
        }

        return $this->department_es ?: $this->department_en;
    }

    public function bio(?string $locale = null): ?string
    {
        $locale = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'es';

        if ($locale === 'en') {
            return $this->bio_en ?: $this->bio_es;
        }

        return $this->bio_es ?: $this->bio_en;
    }

    public function specialties(?string $locale = null): array
    {
        $locale = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'es';
        $raw = $locale === 'en'
            ? ($this->specialties_en ?: $this->specialties_es)
            : ($this->specialties_es ?: $this->specialties_en);

        if ($raw === null || trim($raw) === '') {
            return [];
        }

        return collect(preg_split('/\r\n|\r|\n|,/', $raw))
            ->map(fn ($item) => trim((string) $item))
            ->filter(fn ($item) => $item !== '')
            ->values()
            ->all();
    }

    public function photoUrl(): ?string
    {
        return $this->photoMediaAsset?->serving_url ?? $this->photoMediaAsset?->url;
    }
}