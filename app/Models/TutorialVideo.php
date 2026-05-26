<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TutorialVideo extends Model
{
    protected $fillable = [
        'title',
        'youtube_url',
        'youtube_video_id',
        'description',
        'sort_order',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $appends = [
        'youtube_embed_url',
        'youtube_thumbnail_url',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('sort_order')
            ->orderBy('title');
    }

    public function getYoutubeEmbedUrlAttribute(): string
    {
        return "https://www.youtube.com/embed/{$this->youtube_video_id}";
    }

    public function getYoutubeThumbnailUrlAttribute(): string
    {
        return "https://img.youtube.com/vi/{$this->youtube_video_id}/hqdefault.jpg";
    }

    public static function extractYoutubeVideoId(?string $url): ?string
    {
        $url = trim((string) $url);

        if ($url === '') {
            return null;
        }

        $parts = parse_url($url);

        if (! is_array($parts) || empty($parts['host'])) {
            return null;
        }

        $host = strtolower((string) $parts['host']);
        $host = str_starts_with($host, 'www.') ? substr($host, 4) : $host;
        $path = trim((string) ($parts['path'] ?? ''), '/');
        $query = [];
        parse_str((string) ($parts['query'] ?? ''), $query);

        $videoId = null;

        if ($host === 'youtu.be') {
            $videoId = explode('/', $path)[0] ?? null;
        } elseif (in_array($host, ['youtube.com', 'm.youtube.com', 'music.youtube.com', 'youtube-nocookie.com'], true)) {
            if (isset($query['v'])) {
                $videoId = (string) $query['v'];
            } elseif (preg_match('~^(embed|shorts|live)/([^/?#]+)~', $path, $matches)) {
                $videoId = $matches[2];
            }
        }

        if (! is_string($videoId) || ! preg_match('/^[A-Za-z0-9_-]{11}$/', $videoId)) {
            return null;
        }

        return $videoId;
    }
}
