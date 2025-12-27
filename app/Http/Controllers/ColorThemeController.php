<?php

namespace App\Http\Controllers;

use App\Services\ColorThemeService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ColorThemeController extends Controller
{
    protected ColorThemeService $colorThemeService;

    public function __construct(ColorThemeService $colorThemeService)
    {
        $this->colorThemeService = $colorThemeService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $search = $request->get('search', '');

        $query = $this->colorThemeService->getAllThemes();

        if ($search) {
            $query = $query->filter(function ($theme) use ($search) {
                return str_contains(strtolower($theme->name), strtolower($search)) ||
                       str_contains(strtolower($theme->description ?? ''), strtolower($search));
            });
        }

        $themes = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $themes,
            'message' => 'Temas obtenidos correctamente'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:color_themes,name',
            'description' => 'nullable|string|max:500',
            'colors' => 'required|array',
            'colors.*' => 'required|string'
        ]);

        try {
            $theme = $this->colorThemeService->createTheme($request->all());

            return response()->json([
                'success' => true,
                'data' => $theme,
                'message' => 'Tema creado correctamente'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el tema',
                'errors' => ['general' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): JsonResponse
    {
        $theme = $this->colorThemeService->getAllThemes()->find($id);

        if (!$theme) {
            return response()->json([
                'success' => false,
                'message' => 'Tema no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $theme,
            'message' => 'Tema obtenido correctamente'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:color_themes,name,' . $id,
            'description' => 'nullable|string|max:500',
            'colors' => 'sometimes|required|array',
            'colors.*' => 'required|string'
        ]);

        try {
            $updated = $this->colorThemeService->updateTheme($id, $request->all());

            if (!$updated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tema no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Tema actualizado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el tema',
                'errors' => ['general' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->colorThemeService->deleteTheme($id);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar este tema'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Tema eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el tema',
                'errors' => ['general' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Activar un tema específico
     */
    public function activate(int $id): JsonResponse
    {
        try {
            $activated = $this->colorThemeService->setActiveTheme($id);

            if (!$activated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tema no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Tema activado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al activar el tema',
                'errors' => ['general' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Obtener el tema activo actual
     */
    public function active(): JsonResponse
    {
        $theme = $this->colorThemeService->getActiveTheme();

        return response()->json([
            'success' => true,
            'data' => $theme,
            'message' => 'Tema activo obtenido correctamente'
        ]);
    }
}
