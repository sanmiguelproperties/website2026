<?php

namespace App\Http\Controllers;

use App\Models\FrontendColorSetting;
use App\Services\FrontendColorService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class FrontendColorController extends Controller
{
    protected FrontendColorService $colorService;

    public function __construct(FrontendColorService $colorService)
    {
        $this->colorService = $colorService;
    }

    /**
     * Listar todas las configuraciones de colores
     */
    public function index(Request $request): JsonResponse
    {
        $viewSlug = $request->query('view');
        
        if ($viewSlug) {
            $settings = $this->colorService->getColorSettingsForView($viewSlug);
        } else {
            $settings = $this->colorService->getAllColorSettings();
        }

        return response()->json([
            'success' => true,
            'data' => $settings,
            'message' => 'Configuraciones de colores obtenidas correctamente'
        ]);
    }

    /**
     * Obtener todas las configuraciones agrupadas por vista
     */
    public function groupedByView(): JsonResponse
    {
        $grouped = $this->colorService->getAllGroupedByView();

        return response()->json([
            'success' => true,
            'data' => $grouped,
            'message' => 'Configuraciones agrupadas por vista obtenidas correctamente'
        ]);
    }

    /**
     * Obtener las vistas disponibles
     */
    public function views(): JsonResponse
    {
        $views = $this->colorService->getAvailableViews();

        return response()->json([
            'success' => true,
            'data' => $views,
            'message' => 'Vistas disponibles obtenidas correctamente'
        ]);
    }

    /**
     * Obtener configuraciones para una vista específica
     */
    public function viewConfigs(string $viewSlug): JsonResponse
    {
        $availableViews = FrontendColorSetting::getAvailableViews();
        
        if (!isset($availableViews[$viewSlug])) {
            return response()->json([
                'success' => false,
                'message' => 'Vista no encontrada'
            ], 404);
        }

        $configs = $this->colorService->getColorSettingsForView($viewSlug);
        $active = $this->colorService->getActiveColorsForView($viewSlug);

        return response()->json([
            'success' => true,
            'data' => [
                'view' => $availableViews[$viewSlug],
                'configs' => $configs,
                'active' => $active,
            ],
            'message' => 'Configuraciones de la vista obtenidas correctamente'
        ]);
    }

    /**
     * Obtener la configuración de colores activa para una vista
     */
    public function activeForView(string $viewSlug): JsonResponse
    {
        $setting = $this->colorService->getActiveColorsForView($viewSlug);

        return response()->json([
            'success' => true,
            'data' => $setting,
            'message' => 'Configuración activa obtenida correctamente'
        ]);
    }

    /**
     * Obtener la configuración de colores activa (legacy - global)
     */
    public function active(): JsonResponse
    {
        $setting = $this->colorService->getActiveColors();

        return response()->json([
            'success' => true,
            'data' => $setting,
            'message' => 'Configuración activa obtenida correctamente'
        ]);
    }

    /**
     * Mostrar una configuración específica
     */
    public function show(int $id): JsonResponse
    {
        $setting = $this->colorService->getColorSetting($id);

        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'Configuración de colores no encontrada'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $setting,
            'message' => 'Configuración obtenida correctamente'
        ]);
    }

    /**
     * Crear una nueva configuración de colores
     */
    public function store(Request $request): JsonResponse
    {
        $availableViews = array_keys(FrontendColorSetting::getAvailableViews());
        
        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'view_slug' => 'required|string|in:' . implode(',', $availableViews),
            'colors' => 'nullable|array',
        ]);

        // Verificar nombre único por vista
        $exists = FrontendColorSetting::where('name', $request->name)
                                      ->where('view_slug', $request->view_slug)
                                      ->exists();
        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Ya existe una configuración con ese nombre para esta vista',
                'errors' => ['name' => ['El nombre ya está en uso para esta vista']]
            ], 422);
        }

        // Validar estructura de colores si se proporcionan
        if ($request->has('colors') && !empty($request->colors)) {
            $errors = $this->colorService->validateColors($request->colors, $request->view_slug);
            if (!empty($errors)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación en los colores',
                    'errors' => ['colors' => $errors]
                ], 422);
            }
        }

        try {
            $setting = $this->colorService->createColorSetting($request->only([
                'name', 'description', 'view_slug', 'colors'
            ]));

            return response()->json([
                'success' => true,
                'data' => $setting,
                'message' => 'Configuración de colores creada correctamente'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la configuración de colores',
                'errors' => ['general' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Actualizar una configuración de colores
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $setting = $this->colorService->getColorSetting($id);
        
        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'Configuración de colores no encontrada'
            ], 404);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'description' => 'nullable|string|max:500',
            'colors' => 'sometimes|array',
        ]);

        // Verificar nombre único por vista (excluyendo el actual)
        if ($request->has('name')) {
            $exists = FrontendColorSetting::where('name', $request->name)
                                          ->where('view_slug', $setting->view_slug)
                                          ->where('id', '!=', $id)
                                          ->exists();
            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe una configuración con ese nombre para esta vista',
                    'errors' => ['name' => ['El nombre ya está en uso para esta vista']]
                ], 422);
            }
        }

        // Validar estructura de colores si se proporcionan
        if ($request->has('colors') && !empty($request->colors)) {
            $errors = $this->colorService->validateColors($request->colors, $setting->view_slug);
            if (!empty($errors)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación en los colores',
                    'errors' => ['colors' => $errors]
                ], 422);
            }
        }

        try {
            $updated = $this->colorService->updateColorSetting($id, $request->only([
                'name', 'description', 'colors'
            ]));

            if (!$updated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Configuración de colores no encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Configuración de colores actualizada correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la configuración de colores',
                'errors' => ['general' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Eliminar una configuración de colores
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->colorService->deleteColorSetting($id);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar esta configuración (puede estar activa o no existir)'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Configuración de colores eliminada correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la configuración de colores',
                'errors' => ['general' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Activar una configuración de colores
     */
    public function activate(int $id): JsonResponse
    {
        try {
            $activated = $this->colorService->activateColorSetting($id);

            if (!$activated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Configuración de colores no encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Configuración de colores activada correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al activar la configuración de colores',
                'errors' => ['general' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Restablecer colores a los valores por defecto
     */
    public function resetDefaults(int $id): JsonResponse
    {
        try {
            $reset = $this->colorService->resetToDefaults($id);

            if (!$reset) {
                return response()->json([
                    'success' => false,
                    'message' => 'Configuración de colores no encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Colores restablecidos a los valores por defecto'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al restablecer los colores',
                'errors' => ['general' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Duplicar una configuración de colores
     */
    public function duplicate(Request $request, int $id): JsonResponse
    {
        $setting = $this->colorService->getColorSetting($id);
        
        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'Configuración de colores no encontrada'
            ], 404);
        }

        $request->validate([
            'name' => 'required|string|max:100',
        ]);

        // Verificar nombre único por vista
        $exists = FrontendColorSetting::where('name', $request->name)
                                      ->where('view_slug', $setting->view_slug)
                                      ->exists();
        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Ya existe una configuración con ese nombre para esta vista',
                'errors' => ['name' => ['El nombre ya está en uso para esta vista']]
            ], 422);
        }

        try {
            $duplicate = $this->colorService->duplicateColorSetting($id, $request->name);

            if (!$duplicate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Configuración de colores no encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $duplicate,
                'message' => 'Configuración de colores duplicada correctamente'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al duplicar la configuración de colores',
                'errors' => ['general' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Obtener CSS generado para una vista específica
     */
    public function cssForView(string $viewSlug): Response
    {
        $css = $this->colorService->generateCssForView($viewSlug);

        return response($css, 200)
            ->header('Content-Type', 'text/css')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    /**
     * Obtener CSS generado con las variables de colores activas (legacy - global)
     */
    public function css(): Response
    {
        $css = $this->colorService->generateCss();

        return response($css, 200)
            ->header('Content-Type', 'text/css')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    /**
     * Obtener la lista de grupos de colores para una vista
     */
    public function groupsForView(string $viewSlug): JsonResponse
    {
        $groups = $this->colorService->getColorGroupsForView($viewSlug);

        return response()->json([
            'success' => true,
            'data' => $groups,
            'message' => 'Grupos de colores obtenidos correctamente'
        ]);
    }

    /**
     * Obtener la lista de todos los grupos de colores disponibles
     */
    public function groups(): JsonResponse
    {
        $groups = $this->colorService->getColorGroups();

        return response()->json([
            'success' => true,
            'data' => $groups,
            'message' => 'Grupos de colores obtenidos correctamente'
        ]);
    }

    /**
     * Obtener colores por defecto para una vista
     */
    public function defaultsForView(string $viewSlug): JsonResponse
    {
        $defaults = FrontendColorSetting::getDefaultColorsForView($viewSlug);

        return response()->json([
            'success' => true,
            'data' => $defaults,
            'message' => 'Colores por defecto obtenidos correctamente'
        ]);
    }

    /**
     * Obtener todos los colores por defecto del sistema
     */
    public function defaults(): JsonResponse
    {
        $defaults = FrontendColorSetting::getDefaultColors();

        return response()->json([
            'success' => true,
            'data' => $defaults,
            'message' => 'Colores por defecto obtenidos correctamente'
        ]);
    }

    /**
     * Exportar configuración de colores como JSON
     */
    public function export(int $id): JsonResponse
    {
        $exported = $this->colorService->exportColors($id);

        if (!$exported) {
            return response()->json([
                'success' => false,
                'message' => 'Configuración de colores no encontrada'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $exported,
            'message' => 'Configuración exportada correctamente'
        ]);
    }

    /**
     * Importar configuración de colores desde JSON
     */
    public function import(Request $request): JsonResponse
    {
        $availableViews = array_keys(FrontendColorSetting::getAvailableViews());
        
        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'view_slug' => 'required|string|in:' . implode(',', $availableViews),
            'colors' => 'required|array',
        ]);

        try {
            $imported = $this->colorService->importColors($request->all());

            if (!$imported) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo importar la configuración de colores'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => $imported,
                'message' => 'Configuración de colores importada correctamente'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al importar la configuración de colores',
                'errors' => ['general' => [$e->getMessage()]]
            ], 500);
        }
    }
}
