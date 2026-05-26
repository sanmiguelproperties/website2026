<?php

namespace App\Http\Controllers;

use App\Models\TutorialVideo;
use App\Support\Rbac;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TutorialVideoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user('api');
        $canManage = Rbac::canAny($user, 'tutorials.manage');

        $query = TutorialVideo::query()
            ->with(['creator:id,name', 'updater:id,name']);

        if (! $canManage || ! $request->boolean('include_inactive')) {
            $query->active();
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function (Builder $builder) use ($search): void {
                $builder
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('youtube_url', 'like', "%{$search}%");
            });
        }

        if ($canManage && $request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $order = (string) $request->input('order', 'sort_order');
        if (! in_array($order, ['title', 'sort_order', 'created_at', 'updated_at'], true)) {
            $order = 'sort_order';
        }

        $sort = $request->input('sort', 'asc') === 'desc' ? 'desc' : 'asc';
        $query->orderBy($order, $sort)->orderBy('title');

        $perPage = (int) $request->input('per_page', 20);
        $perPage = max(1, min(100, $perPage));

        return $this->apiSuccess('Videos tutoriales', 'TUTORIAL_VIDEOS_LIST', $query->paginate($perPage), 200, [
            'can_manage' => $canManage,
        ]);
    }

    public function show(Request $request, TutorialVideo $tutorialVideo): JsonResponse
    {
        $canManage = Rbac::canAny($request->user('api'), 'tutorials.manage');

        if (! $canManage && ! $tutorialVideo->is_active) {
            return $this->apiNotFound('Video tutorial no encontrado', 'TUTORIAL_VIDEO_NOT_FOUND');
        }

        return $this->apiSuccess(
            'Video tutorial obtenido',
            'TUTORIAL_VIDEO_SHOWN',
            $tutorialVideo->load(['creator:id,name', 'updater:id,name'])
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validator = $this->validator($request);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        $data = $validator->validated();
        $data['youtube_video_id'] = TutorialVideo::extractYoutubeVideoId($data['youtube_url']);
        $data['is_active'] = array_key_exists('is_active', $data) ? (bool) $data['is_active'] : true;
        $data['sort_order'] = $data['sort_order'] ?? $this->nextSortOrder();
        $data['created_by'] = $request->user('api')?->id;
        $data['updated_by'] = $request->user('api')?->id;

        $tutorialVideo = TutorialVideo::create($data);

        return $this->apiCreated(
            'Video tutorial creado',
            'TUTORIAL_VIDEO_CREATED',
            $tutorialVideo->load(['creator:id,name', 'updater:id,name'])
        );
    }

    public function update(Request $request, TutorialVideo $tutorialVideo): JsonResponse
    {
        $validator = $this->validator($request, true);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        $data = $validator->validated();

        if (array_key_exists('youtube_url', $data)) {
            $data['youtube_video_id'] = TutorialVideo::extractYoutubeVideoId($data['youtube_url']);
        }

        if (array_key_exists('sort_order', $data) && $data['sort_order'] === null) {
            $data['sort_order'] = 0;
        }

        $data['updated_by'] = $request->user('api')?->id;

        $tutorialVideo->update($data);

        return $this->apiSuccess(
            'Video tutorial actualizado',
            'TUTORIAL_VIDEO_UPDATED',
            $tutorialVideo->fresh()->load(['creator:id,name', 'updater:id,name'])
        );
    }

    public function destroy(Request $request, TutorialVideo $tutorialVideo): JsonResponse
    {
        $tutorialVideo->delete();

        return $this->apiSuccess('Video tutorial eliminado', 'TUTORIAL_VIDEO_DELETED', null);
    }

    private function validator(Request $request, bool $isUpdate = false): \Illuminate\Validation\Validator
    {
        $titleRule = $isUpdate ? 'sometimes|required|string|max:180' : 'required|string|max:180';
        $urlRule = $isUpdate ? 'sometimes|required|url|max:2048' : 'required|url|max:2048';

        $validator = Validator::make($request->all(), [
            'title' => $titleRule,
            'youtube_url' => $urlRule,
            'description' => 'nullable|string|max:5000',
            'sort_order' => 'nullable|integer|min:0|max:1000000',
            'is_active' => 'sometimes|boolean',
        ]);

        $validator->after(function ($validator) use ($request): void {
            if (! $request->has('youtube_url')) {
                return;
            }

            if (! TutorialVideo::extractYoutubeVideoId((string) $request->input('youtube_url'))) {
                $validator->errors()->add('youtube_url', 'Ingresa una URL valida de YouTube.');
            }
        });

        return $validator;
    }

    private function nextSortOrder(): int
    {
        return ((int) TutorialVideo::max('sort_order')) + 10;
    }
}
