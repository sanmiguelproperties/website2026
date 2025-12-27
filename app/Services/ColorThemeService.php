<?php

namespace App\Services;

use App\Models\ColorTheme;
use Illuminate\Support\Facades\Cache;

class ColorThemeService
{
    /**
     * Obtener el tema activo actual
     */
    public function getActiveTheme()
    {
        return Cache::remember('active_color_theme', 3600, function () {
            return ColorTheme::getActiveTheme() ?? ColorTheme::getDefaultTheme();
        });
    }

    /**
     * Obtener el tema del usuario actual
     */
    public function getUserTheme($user = null)
    {
        $user = $user ?? auth()->user();
        if (!$user) {
            return $this->getActiveTheme();
        }

        return $user->colorTheme ?? $this->getActiveTheme();
    }

    /**
     * Cambiar el tema activo
     */
    public function setActiveTheme(int $themeId): bool
    {
        $theme = ColorTheme::find($themeId);
        if (!$theme) {
            return false;
        }

        $theme->activate();
        Cache::forget('active_color_theme');
        return true;
    }

    /**
     * Generar CSS variables para el tema activo
     */
    public function generateCssVariables(): string
    {
        $theme = $this->getActiveTheme();
        if (!$theme) {
            return '';
        }

        $cssVars = $theme->getCssVariables();
        $css = ":root {\n";

        foreach ($cssVars as $var => $value) {
            $css .= "  {$var}: {$value};\n";
        }

        $css .= "}\n";
        return $css;
    }

    /**
     * Generar CSS variables para el tema del usuario
     */
    public function generateUserCssVariables($user = null): string
    {
        $theme = $this->getUserTheme($user);
        if (!$theme) {
            return '';
        }

        $cssVars = $theme->getCssVariables();
        $css = ":root {\n";

        foreach ($cssVars as $var => $value) {
            $css .= "  {$var}: {$value};\n";
        }

        $css .= "}\n";
        return $css;
    }

    /**
     * Obtener todos los temas disponibles
     */
    public function getAllThemes()
    {
        return ColorTheme::orderBy('name');
    }

    /**
     * Crear un nuevo tema
     */
    public function createTheme(array $data): ColorTheme
    {
        // Si es el primer tema, marcarlo como activo y por defecto
        if (ColorTheme::count() === 0) {
            $data['is_active'] = true;
            $data['is_default'] = true;
        }

        return ColorTheme::create($data);
    }

    /**
     * Actualizar un tema
     */
    public function updateTheme(int $themeId, array $data): bool
    {
        $theme = ColorTheme::find($themeId);
        if (!$theme) {
            return false;
        }

        $theme->update($data);
        Cache::forget('active_color_theme');
        return true;
    }

    /**
     * Eliminar un tema
     */
    public function deleteTheme(int $themeId): bool
    {
        $theme = ColorTheme::find($themeId);
        if (!$theme || $theme->is_default) {
            return false;
        }

        // Si el tema a eliminar está activo, activar el tema por defecto
        if ($theme->is_active) {
            $defaultTheme = ColorTheme::getDefaultTheme();
            if ($defaultTheme && $defaultTheme->id !== $themeId) {
                $defaultTheme->activate();
            }
        }

        $theme->delete();
        Cache::forget('active_color_theme');
        return true;
    }

    /**
     * Limpiar cache del tema
     */
    public function clearCache(): void
    {
        Cache::forget('active_color_theme');
    }

    /**
     * Establecer tema para un usuario
     */
    public function setUserTheme(int $userId, int $themeId): bool
    {
        $user = \App\Models\User::find($userId);
        if (!$user) {
            return false;
        }

        $theme = ColorTheme::find($themeId);
        if (!$theme) {
            return false;
        }

        $user->update(['color_theme_id' => $themeId]);
        return true;
    }
}
