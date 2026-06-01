<?php

namespace App\Models;

use App\Support\AdminMenu;
use App\Support\Rbac;
use App\Support\RichTextSanitizer;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Route;

class ManualArticle extends Model
{
    protected $fillable = [
        'manual_section_id',
        'slug',
        'title',
        'summary',
        'content',
        'required_permission',
        'related_route_name',
        'sort_order',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(ManualSection::class, 'manual_section_id');
    }

    public function videos(): BelongsToMany
    {
        return $this->belongsToMany(
            TutorialVideo::class,
            'manual_article_tutorial_video',
            'manual_article_id',
            'tutorial_video_id'
        )->withPivot('sort_order')->orderByPivot('sort_order');
    }

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

    public function isVisibleTo(?Authenticatable $user): bool
    {
        return ! $this->required_permission
            || Rbac::canAny($user, $this->required_permission);
    }

    public function relatedRouteUrl(?Authenticatable $user): ?string
    {
        if (
            ! $this->related_route_name
            || ! Route::has($this->related_route_name)
            || ! AdminMenu::canAccessRoute($user, $this->related_route_name)
        ) {
            return null;
        }

        return route($this->related_route_name);
    }

    public function toManualArray(?Authenticatable $user, bool $withContent = false, bool $withRawContent = false): array
    {
        $videos = Rbac::canAny($user, 'manual.manage')
            ? $this->videos
            : $this->videos->filter(static fn (TutorialVideo $video): bool => $video->is_active);

        $data = [
            'id' => $this->id,
            'manual_section_id' => $this->manual_section_id,
            'slug' => $this->slug,
            'title' => $this->title,
            'summary' => $this->summary,
            'required_permission' => $this->required_permission,
            'related_route_name' => $this->related_route_name,
            'related_route_url' => $this->relatedRouteUrl($user),
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'updated_at' => $this->updated_at?->toIso8601String(),
            'videos' => $videos->map(static fn (TutorialVideo $video): array => $video->toManualArray())->values()->all(),
        ];

        if ($withContent) {
            $data['content_html'] = RichTextSanitizer::sanitize($this->content);
        }

        if ($withRawContent) {
            $data['content'] = $this->content;
        }

        return $data;
    }
}
