<?php

namespace App\Http\Controllers;

use App\Models\CmsPost;
use App\Services\CmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CmsPostController extends Controller
{
    // ─── Admin CRUD ─────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        try {
            $query = CmsPost::with(['coverMediaAsset', 'author', 'categories', 'tags'])
                ->orderByDesc('created_at');

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('is_featured')) {
                $query->where('is_featured', filter_var($request->is_featured, FILTER_VALIDATE_BOOLEAN));
            }

            if ($request->filled('category_id')) {
                $query->whereHas('categories', fn ($q) => $q->where('cms_post_categories.id', $request->category_id));
            }

            if ($request->filled('search')) {
                $query->where(function ($q) use ($request) {
                    $q->where('title_es', 'like', "%{$request->search}%")
                      ->orWhere('title_en', 'like', "%{$request->search}%")
                      ->orWhere('slug', 'like', "%{$request->search}%");
                });
            }

            $perPage = $request->integer('per_page', 15);
            $posts = $query->paginate($perPage);

            return $this->jsonSuccess($posts, 'Posts obtenidos');
        } catch (\Throwable $e) {
            return $this->apiServerError($e);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'slug' => 'nullable|string|max:255|unique:cms_posts,slug',
                'title_es' => 'required|string|max:255',
                'title_en' => 'nullable|string|max:255',
                'excerpt_es' => 'nullable|string',
                'excerpt_en' => 'nullable|string',
                'body_es' => 'nullable|string',
                'body_en' => 'nullable|string',
                'cover_media_asset_id' => 'nullable|integer|exists:media_assets,id',
                'status' => 'nullable|in:draft,published,scheduled,archived',
                'is_featured' => 'nullable|boolean',
                'published_at' => 'nullable|date',
                'meta_title_es' => 'nullable|string|max:255',
                'meta_title_en' => 'nullable|string|max:255',
                'meta_description_es' => 'nullable|string',
                'meta_description_en' => 'nullable|string',
                'sort_order' => 'nullable|integer',
                'category_ids' => 'nullable|array',
                'category_ids.*' => 'integer|exists:cms_post_categories,id',
                'tag_ids' => 'nullable|array',
                'tag_ids.*' => 'integer|exists:cms_post_tags,id',
            ]);

            if (empty($validated['slug'])) {
                $validated['slug'] = Str::slug($validated['title_es']);
            }

            $validated['author_id'] = $request->user()?->id;

            // Si se publica y no se especifica fecha, usar ahora
            if (($validated['status'] ?? 'draft') === 'published' && empty($validated['published_at'])) {
                $validated['published_at'] = now();
            }

            $categoryIds = $validated['category_ids'] ?? [];
            $tagIds = $validated['tag_ids'] ?? [];
            unset($validated['category_ids'], $validated['tag_ids']);

            $post = CmsPost::create($validated);

            if (!empty($categoryIds)) {
                $post->categories()->sync($categoryIds);
            }
            if (!empty($tagIds)) {
                $post->tags()->sync($tagIds);
            }

            $post->load(['coverMediaAsset', 'author', 'categories', 'tags']);

            return $this->apiCreated('Post creado', 'CMS_POST_CREATED', $post);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->apiValidationError($e->errors());
        } catch (\Throwable $e) {
            return $this->apiServerError($e);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $post = CmsPost::with(['coverMediaAsset', 'author', 'categories', 'tags'])->find($id);
            if (!$post) {
                return $this->apiNotFound('Post no encontrado');
            }

            return $this->jsonSuccess($post, 'Post obtenido');
        } catch (\Throwable $e) {
            return $this->apiServerError($e);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $post = CmsPost::find($id);
            if (!$post) {
                return $this->apiNotFound('Post no encontrado');
            }

            $validated = $request->validate([
                'slug' => ['nullable', 'string', 'max:255', Rule::unique('cms_posts', 'slug')->ignore($post->id)],
                'title_es' => 'nullable|string|max:255',
                'title_en' => 'nullable|string|max:255',
                'excerpt_es' => 'nullable|string',
                'excerpt_en' => 'nullable|string',
                'body_es' => 'nullable|string',
                'body_en' => 'nullable|string',
                'cover_media_asset_id' => 'nullable|integer|exists:media_assets,id',
                'status' => 'nullable|in:draft,published,scheduled,archived',
                'is_featured' => 'nullable|boolean',
                'published_at' => 'nullable|date',
                'meta_title_es' => 'nullable|string|max:255',
                'meta_title_en' => 'nullable|string|max:255',
                'meta_description_es' => 'nullable|string',
                'meta_description_en' => 'nullable|string',
                'sort_order' => 'nullable|integer',
                'category_ids' => 'nullable|array',
                'category_ids.*' => 'integer|exists:cms_post_categories,id',
                'tag_ids' => 'nullable|array',
                'tag_ids.*' => 'integer|exists:cms_post_tags,id',
            ]);

            $categoryIds = $validated['category_ids'] ?? null;
            $tagIds = $validated['tag_ids'] ?? null;
            unset($validated['category_ids'], $validated['tag_ids']);

            $post->update(array_filter($validated, fn ($v) => $v !== null));

            if ($categoryIds !== null) {
                $post->categories()->sync($categoryIds);
            }
            if ($tagIds !== null) {
                $post->tags()->sync($tagIds);
            }

            CmsService::clearPostCache($post->slug);

            $post->load(['coverMediaAsset', 'author', 'categories', 'tags']);

            return $this->jsonSuccess($post, 'Post actualizado');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->apiValidationError($e->errors());
        } catch (\Throwable $e) {
            return $this->apiServerError($e);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $post = CmsPost::find($id);
            if (!$post) {
                return $this->apiNotFound('Post no encontrado');
            }

            $slug = $post->slug;
            $post->categories()->detach();
            $post->tags()->detach();
            $post->delete();

            CmsService::clearPostCache($slug);

            return $this->jsonSuccess(null, 'Post eliminado');
        } catch (\Throwable $e) {
            return $this->apiServerError($e);
        }
    }

    // ─── Público ────────────────────────────────────────

    public function indexPublic(Request $request): JsonResponse
    {
        try {
            $query = CmsPost::published()
                ->with(['coverMediaAsset', 'author', 'categories', 'tags'])
                ->orderByDesc('published_at');

            if ($request->filled('category')) {
                $query->whereHas('categories', fn ($q) => $q->where('slug', $request->category));
            }

            if ($request->filled('tag')) {
                $query->whereHas('tags', fn ($q) => $q->where('slug', $request->tag));
            }

            if ($request->filled('featured')) {
                $query->featured();
            }

            if ($request->filled('search')) {
                $query->where(function ($q) use ($request) {
                    $q->where('title_es', 'like', "%{$request->search}%")
                      ->orWhere('title_en', 'like', "%{$request->search}%")
                      ->orWhere('excerpt_es', 'like', "%{$request->search}%")
                      ->orWhere('excerpt_en', 'like', "%{$request->search}%");
                });
            }

            $perPage = $request->integer('per_page', 12);
            $posts = $query->paginate($perPage);

            return $this->jsonSuccess($posts, 'Posts obtenidos');
        } catch (\Throwable $e) {
            return $this->apiServerError($e);
        }
    }

    public function showPublic(string $slug): JsonResponse
    {
        try {
            $post = CmsPost::published()
                ->bySlug($slug)
                ->with(['coverMediaAsset', 'author', 'categories', 'tags'])
                ->first();

            if (!$post) {
                return $this->apiNotFound('Post no encontrado');
            }

            return $this->jsonSuccess($post, 'Post obtenido');
        } catch (\Throwable $e) {
            return $this->apiServerError($e);
        }
    }

    public function categoriesPublic(): JsonResponse
    {
        try {
            $categories = \App\Models\CmsPostCategory::active()
                ->root()
                ->with('children')
                ->orderBy('sort_order')
                ->get();

            return $this->jsonSuccess($categories, 'Categorías obtenidas');
        } catch (\Throwable $e) {
            return $this->apiServerError($e);
        }
    }

    public function tagsPublic(): JsonResponse
    {
        try {
            $tags = \App\Models\CmsPostTag::orderBy('name_es')->get();
            return $this->jsonSuccess($tags, 'Tags obtenidos');
        } catch (\Throwable $e) {
            return $this->apiServerError($e);
        }
    }
}
