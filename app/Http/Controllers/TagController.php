<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TagController extends Controller
{
    /**
     * GET /api/tags
     */
    public function index(Request $request): JsonResponse
    {
        $query = Tag::query();

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where('name', 'like', "%{$search}%");
        }

        $sort = $request->input('sort', 'asc');
        $order = $request->input('order', 'name');
        $validOrders = ['id', 'name', 'created_at', 'updated_at'];
        if (!in_array($order, $validOrders, true)) {
            $order = 'name';
        }
        $sort = $sort === 'desc' ? 'desc' : 'asc';
        $query->orderBy($order, $sort);

        $perPage = (int) $request->input('per_page', 15);
        $perPage = max(1, min(100, $perPage));

        return $this->apiSuccess('Listado de tags', 'TAGS_LIST', $query->paginate($perPage));
    }

    /**
     * POST /api/tags
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:120',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        $data = $validator->validated();
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        if (Tag::where('name', $data['name'])->exists()) {
            return $this->apiError('El tag ya existe', 'TAG_ALREADY_EXISTS', ['name' => ['Ya existe un tag con este nombre']], null, 409);
        }

        $tag = Tag::create($data);
        return $this->apiCreated('Tag creado', 'TAG_CREATED', $tag);
    }

    /**
     * GET /api/tags/{tag}
     */
    public function show(Request $request, Tag $tag): JsonResponse
    {
        return $this->apiSuccess('Tag obtenido', 'TAG_SHOWN', $tag);
    }

    /**
     * PATCH /api/tags/{tag}
     */
    public function update(Request $request, Tag $tag): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:100',
            'slug' => 'sometimes|nullable|string|max:120',
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        $data = $validator->validated();
        if (array_key_exists('name', $data) && Tag::where('name', $data['name'])->where('id', '!=', $tag->id)->exists()) {
            return $this->apiError('El tag ya existe', 'TAG_ALREADY_EXISTS', ['name' => ['Ya existe un tag con este nombre']], null, 409);
        }

        if (array_key_exists('name', $data) && (!array_key_exists('slug', $data) || $data['slug'] === null || $data['slug'] === '')) {
            $data['slug'] = Str::slug($data['name']);
        }

        $tag->update($data);
        return $this->apiSuccess('Tag actualizado', 'TAG_UPDATED', $tag->fresh());
    }

    /**
     * DELETE /api/tags/{tag}
     */
    public function destroy(Request $request, Tag $tag): JsonResponse
    {
        $tag->delete();
        return $this->apiSuccess('Tag eliminado', 'TAG_DELETED', null);
    }
}

