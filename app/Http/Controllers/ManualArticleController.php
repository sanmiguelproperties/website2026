<?php

namespace App\Http\Controllers;

use App\Models\ManualArticle;
use App\Models\TutorialVideo;
use App\Support\Rbac;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ManualArticleController extends Controller
{
    public function videos(): JsonResponse
    {
        return $this->apiSuccess(
            'Videos disponibles para el manual',
            'MANUAL_VIDEOS_LIST',
            TutorialVideo::query()->ordered()->get()
        );
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user('api');
        $canManage = Rbac::canAny($user, 'manual.manage');
        $includeInactive = $canManage && $request->boolean('include_inactive');

        $query = ManualArticle::query()
            ->with(['section', 'videos'])
            ->ordered();

        if ($request->filled('section_id')) {
            $query->where('manual_section_id', $request->integer('section_id'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(static function (Builder $builder) use ($search): void {
                $builder
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('summary', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if (! $includeInactive) {
            $query->active()->whereHas('section', static fn (Builder $builder): Builder => $builder->active());
        }

        $articles = $query
            ->get()
            ->filter(static fn (ManualArticle $article): bool => $includeInactive
                || ($article->isVisibleTo($user) && $article->section?->isVisibleTo($user)))
            ->map(static function (ManualArticle $article) use ($user): array {
                $data = $article->toManualArray($user);
                $data['section'] = $article->section?->only(['id', 'slug', 'title']);

                return $data;
            })
            ->values()
            ->all();

        return $this->apiSuccess('Articulos del manual', 'MANUAL_ARTICLES_LIST', $articles, 200, [
            'can_manage' => $canManage,
        ]);
    }

    public function show(Request $request, ManualArticle $manualArticle): JsonResponse
    {
        $user = $request->user('api');
        $canManage = Rbac::canAny($user, 'manual.manage');
        $manualArticle->load(['section', 'videos']);

        if (
            ! $canManage
            && (
                ! $manualArticle->is_active
                || ! $manualArticle->section?->is_active
                || ! $manualArticle->isVisibleTo($user)
                || ! $manualArticle->section?->isVisibleTo($user)
            )
        ) {
            return $this->apiNotFound('Articulo no encontrado', 'MANUAL_ARTICLE_NOT_FOUND');
        }

        $data = $manualArticle->toManualArray($user, true, $canManage);
        $data['section'] = $manualArticle->section?->only(['id', 'slug', 'title']);

        return $this->apiSuccess('Articulo obtenido', 'MANUAL_ARTICLE_SHOWN', $data, 200, [
            'can_manage' => $canManage,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = $this->validator($request);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        $data = $validator->validated();
        $videoIds = $data['tutorial_video_ids'] ?? [];
        unset($data['tutorial_video_ids']);

        $data['slug'] = $data['slug'] ?: Str::slug($data['title']);
        $data['is_active'] = array_key_exists('is_active', $data) ? (bool) $data['is_active'] : true;
        $data['sort_order'] = $data['sort_order'] ?? $this->nextSortOrder((int) $data['manual_section_id']);
        $data['created_by'] = $request->user('api')?->id;
        $data['updated_by'] = $request->user('api')?->id;

        $article = ManualArticle::create($data);
        $this->syncVideos($article, $videoIds);

        return $this->apiCreated(
            'Articulo creado',
            'MANUAL_ARTICLE_CREATED',
            $article->fresh()->load(['section', 'videos'])->toManualArray($request->user('api'), true, true)
        );
    }

    public function update(Request $request, ManualArticle $manualArticle): JsonResponse
    {
        $validator = $this->validator($request, $manualArticle);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        $data = $validator->validated();
        $videoIds = $data['tutorial_video_ids'] ?? null;
        unset($data['tutorial_video_ids']);

        if (array_key_exists('slug', $data) && ! $data['slug']) {
            $data['slug'] = Str::slug($data['title'] ?? $manualArticle->title);
        }

        $data['updated_by'] = $request->user('api')?->id;
        $manualArticle->update($data);

        if ($videoIds !== null) {
            $this->syncVideos($manualArticle, $videoIds);
        }

        return $this->apiSuccess(
            'Articulo actualizado',
            'MANUAL_ARTICLE_UPDATED',
            $manualArticle->fresh()->load(['section', 'videos'])->toManualArray($request->user('api'), true, true)
        );
    }

    public function destroy(ManualArticle $manualArticle): JsonResponse
    {
        $manualArticle->delete();

        return $this->apiSuccess('Articulo eliminado', 'MANUAL_ARTICLE_DELETED');
    }

    private function validator(Request $request, ?ManualArticle $article = null): \Illuminate\Validation\Validator
    {
        $uniqueSlug = Rule::unique('manual_articles', 'slug');

        if ($article) {
            $uniqueSlug->ignore($article->id);
        }

        return validator($request->all(), [
            'manual_section_id' => [$article ? 'sometimes' : 'required', 'required', 'integer', 'exists:manual_sections,id'],
            'slug' => ['nullable', 'string', 'max:180', $uniqueSlug],
            'title' => [$article ? 'sometimes' : 'required', 'required', 'string', 'max:180'],
            'summary' => ['nullable', 'string', 'max:2000'],
            'content' => [$article ? 'sometimes' : 'required', 'required', 'string'],
            'required_permission' => ['nullable', 'string', Rule::in(config('rbac.permissions', []))],
            'related_route_name' => ['nullable', 'string', 'max:180'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'is_active' => ['sometimes', 'boolean'],
            'tutorial_video_ids' => ['sometimes', 'array'],
            'tutorial_video_ids.*' => ['integer', 'distinct', 'exists:tutorial_videos,id'],
        ]);
    }

    private function nextSortOrder(int $sectionId): int
    {
        return ((int) ManualArticle::where('manual_section_id', $sectionId)->max('sort_order')) + 10;
    }

    private function syncVideos(ManualArticle $article, array $videoIds): void
    {
        $sync = [];

        foreach (array_values($videoIds) as $index => $videoId) {
            $sync[$videoId] = ['sort_order' => ($index + 1) * 10];
        }

        $article->videos()->sync($sync);
    }
}
