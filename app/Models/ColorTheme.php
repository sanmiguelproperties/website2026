<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ColorTheme extends Model
{
    protected $fillable = [
        'name',
        'description',
        'colors',
        'is_active',
        'is_default'
    ];

    protected $casts = [
        'colors' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean'
    ];

    /**
     * Obtener el tema activo
     */
    public static function getActiveTheme()
    {
        return static::where('is_active', true)->first();
    }

    /**
     * Obtener el tema por defecto
     */
    public static function getDefaultTheme()
    {
        return static::where('is_default', true)->first();
    }

    /**
     * Activar este tema
     */
    public function activate()
    {
        // Desactivar todos los temas
        static::where('is_active', true)->update(['is_active' => false]);

        // Activar este tema
        $this->update(['is_active' => true]);
    }

    /**
     * Obtener colores del tema como variables CSS
     */
    public function getCssVariables()
    {
        $colors = $this->colors ?? [];
        $cssVars = [];

        foreach ($colors as $key => $value) {
            $cssVars["--c-{$key}"] = $value;
        }

        return $cssVars;
    }
}
