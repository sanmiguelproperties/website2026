<?php

namespace App\Http\Controllers;

use App\Models\CmsSiteSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CmsSiteSettingController extends Controller
{
    // ─── Admin ──────────────────────────────────────────

    /**
     * Listar todos los settings agrupados.
     */
    public function index(): JsonResponse
    {
        try {
            $settings = CmsSiteSetting::with('mediaAsset')
                ->orderBy('setting_group')
                ->orderBy('sort_order')
                ->get()
                ->groupBy('setting_group');

            return $this->jsonSuccess($settings, 'Settings obtenidos');
        } catch (\Throwable $e) {
            return $this->apiServerError($e);
        }
    }

    /**
     * Settings de un grupo específico.
     */
    public function byGroup(string $group): JsonResponse
    {
        try {
            $settings = CmsSiteSetting::byGroup($group)
                ->with('mediaAsset')
                ->orderBy('sort_order')
                ->get();

            return $this->jsonSuccess($settings, "Settings del grupo '{$group}' obtenidos");
        } catch (\Throwable $e) {
            return $this->apiServerError($e);
        }
    }

    /**
     * Crear un nuevo setting.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'setting_key' => 'required|string|max:100|unique:cms_site_settings,setting_key',
                'setting_group' => 'required|string|max:50',
                'label_es' => 'required|string|max:255',
                'label_en' => 'nullable|string|max:255',
                'type' => 'required|string|max:50',
                'value_es' => 'nullable|string',
                'value_en' => 'nullable|string',
                'media_asset_id' => 'nullable|integer|exists:media_assets,id',
                'sort_order' => 'nullable|integer',
            ]);

            $setting = CmsSiteSetting::create($validated);

            return $this->apiCreated('Setting creado', 'CMS_SETTING_CREATED', $setting);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->apiValidationError($e->errors());
        } catch (\Throwable $e) {
            return $this->apiServerError($e);
        }
    }

    /**
     * Actualizar un setting por su key.
     */
    public function updateByKey(Request $request, string $key): JsonResponse
    {
        try {
            $setting = CmsSiteSetting::where('setting_key', $key)->first();
            if (!$setting) {
                return $this->apiNotFound("Setting '{$key}' no encontrado");
            }

            $validated = $request->validate([
                'value_es' => 'nullable|string',
                'value_en' => 'nullable|string',
                'media_asset_id' => 'nullable|integer|exists:media_assets,id',
                'label_es' => 'nullable|string|max:255',
                'label_en' => 'nullable|string|max:255',
            ]);

            $setting->update($validated);

            return $this->jsonSuccess($setting->fresh(), 'Setting actualizado');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->apiValidationError($e->errors());
        } catch (\Throwable $e) {
            return $this->apiServerError($e);
        }
    }

    /**
     * Actualización masiva de settings.
     * Body: { "settings": { "contact_phone": { "value_es": "+52...", "value_en": null }, "site_logo": { "media_asset_id": 5 }, ... } }
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'settings' => 'required|array',
                'settings.*.value_es' => 'nullable|string',
                'settings.*.value_en' => 'nullable|string',
                'settings.*.media_asset_id' => 'nullable|integer',
            ]);

            $updated = 0;
            foreach ($validated['settings'] as $key => $data) {
                $setting = CmsSiteSetting::where('setting_key', $key)->first();
                if ($setting) {
                    $updateData = [];

                    // Campos de texto
                    if (array_key_exists('value_es', $data)) {
                        $updateData['value_es'] = $data['value_es'];
                    }
                    if (array_key_exists('value_en', $data)) {
                        $updateData['value_en'] = $data['value_en'];
                    }

                    // Campo de imagen (media_asset_id)
                    if (array_key_exists('media_asset_id', $data)) {
                        $updateData['media_asset_id'] = $data['media_asset_id'];
                    }

                    if (!empty($updateData)) {
                        $setting->update($updateData);
                        $updated++;
                    }
                }
            }

            CmsSiteSetting::clearCache();

            return $this->jsonSuccess(['updated' => $updated], "{$updated} settings actualizados");
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->apiValidationError($e->errors());
        } catch (\Throwable $e) {
            return $this->apiServerError($e);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $setting = CmsSiteSetting::find($id);
            if (!$setting) {
                return $this->apiNotFound('Setting no encontrado');
            }

            $setting->delete();

            return $this->jsonSuccess(null, 'Setting eliminado');
        } catch (\Throwable $e) {
            return $this->apiServerError($e);
        }
    }

    // ─── Público ────────────────────────────────────────

    /**
     * Obtener todos los settings públicos (lectura).
     */
    public function allPublic(): JsonResponse
    {
        try {
            $settings = CmsSiteSetting::getAllCached()
                ->groupBy('setting_group')
                ->map(function ($group) {
                    $result = [];
                    foreach ($group as $setting) {
                        $result[$setting->setting_key] = [
                            'value_es' => $setting->value_es,
                            'value_en' => $setting->value_en,
                            'media_url' => $setting->mediaAsset?->url,
                        ];
                    }
                    return $result;
                });

            return $this->jsonSuccess($settings, 'Settings obtenidos');
        } catch (\Throwable $e) {
            return $this->apiServerError($e);
        }
    }

    /**
     * Obtener settings de un grupo específico (público).
     */
    public function groupPublic(string $group): JsonResponse
    {
        try {
            $settings = CmsSiteSetting::getAllCached()
                ->where('setting_group', $group)
                ->mapWithKeys(function ($setting) {
                    return [$setting->setting_key => [
                        'value_es' => $setting->value_es,
                        'value_en' => $setting->value_en,
                        'media_url' => $setting->mediaAsset?->url,
                    ]];
                });

            return $this->jsonSuccess($settings, "Settings del grupo '{$group}' obtenidos");
        } catch (\Throwable $e) {
            return $this->apiServerError($e);
        }
    }
}
