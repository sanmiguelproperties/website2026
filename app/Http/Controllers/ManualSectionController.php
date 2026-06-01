<?php

namespace App\Http\Controllers;

use App\Models\ManualSection;
use App\Support\Rbac;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ManualSectionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user('api');
        $canManage = Rbac::canAny($user, 'manual.manage');
        $includeInactive = $canManage && $request->boolean('include_inactive');

        $sections = ManualSection::query()
            ->with(['video', 'articles' => static function ($query): void {
                $query->with('videos')->ordered();
            }])
            ->ordered()
            ->get()
            ->filter(static fn (ManualSection $section): bool => $includeInactive
                || ($section->is_active && $section->isVisibleTo($user)))
            ->map(static function (ManualSection $section) use ($user, $includeInactive): array {
                $video = $section->video && ($includeInactive || $section->video->is_active)
                    ? $section->video->toManualArray()
                    : null;
                $articles = $section->articles
                    ->filter(static fn ($article): bool => $includeInactive
                        || ($article->is_active && $article->isVisibleTo($user)))
                    ->map(static fn ($article): array => $article->toManualArray($user))
                    ->values()
                    ->all();

                return [
                    'id' => $section->id,
                    'slug' => $section->slug,
                    'title' => $section->title,
                    'description' => $section->description,
                    'icon' => $section->icon,
                    'required_permission' => $section->required_permission,
                    'tutorial_video_id' => $section->tutorial_video_id,
                    'video' => $video,
                    'sort_order' => $section->sort_order,
                    'is_active' => $section->is_active,
                    'articles' => $articles,
                    'article_count' => count($articles),
                ];
            })
            ->filter(static fn (array $section): bool => $canManage || $section['article_count'] > 0)
            ->values()
            ->all();

        return $this->apiSuccess('Capitulos del manual', 'MANUAL_SECTIONS_LIST', $sections, 200, [
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
        $data['slug'] = $data['slug'] ?: Str::slug($data['title']);
        $data['is_active'] = array_key_exists('is_active', $data) ? (bool) $data['is_active'] : true;
        $data['sort_order'] = $data['sort_order'] ?? $this->nextSortOrder();

        $section = ManualSection::create($data);

        return $this->apiCreated('Capitulo creado', 'MANUAL_SECTION_CREATED', $section);
    }

    public function update(Request $request, ManualSection $manualSection): JsonResponse
    {
        $validator = $this->validator($request, $manualSection);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        $data = $validator->validated();

        if (array_key_exists('slug', $data) && ! $data['slug']) {
            $data['slug'] = Str::slug($data['title'] ?? $manualSection->title);
        }

        $manualSection->update($data);

        return $this->apiSuccess('Capitulo actualizado', 'MANUAL_SECTION_UPDATED', $manualSection->fresh());
    }

    public function destroy(ManualSection $manualSection): JsonResponse
    {
        if ($manualSection->articles()->exists()) {
            return $this->apiError(
                'No se puede eliminar un capitulo que contiene articulos.',
                'MANUAL_SECTION_NOT_EMPTY',
                null,
                null,
                409
            );
        }

        $manualSection->delete();

        return $this->apiSuccess('Capitulo eliminado', 'MANUAL_SECTION_DELETED');
    }

    private function validator(Request $request, ?ManualSection $section = null): \Illuminate\Validation\Validator
    {
        $uniqueSlug = Rule::unique('manual_sections', 'slug');

        if ($section) {
            $uniqueSlug->ignore($section->id);
        }

        return validator($request->all(), [
            'slug' => ['nullable', 'string', 'max:180', $uniqueSlug],
            'title' => [$section ? 'sometimes' : 'required', 'required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:2000'],
            'icon' => ['nullable', 'string', 'max:60'],
            'required_permission' => ['nullable', 'string', Rule::in(config('rbac.permissions', []))],
            'tutorial_video_id' => ['nullable', 'integer', 'exists:tutorial_videos,id'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }

    private function nextSortOrder(): int
    {
        return ((int) ManualSection::max('sort_order')) + 10;
    }
}
