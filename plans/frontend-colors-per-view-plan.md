# Plan: Gestión de Colores del Frontend por Vista

## Resumen

Este plan describe cómo extender el sistema actual de colores del frontend para soportar **configuraciones de colores independientes por vista/página**. Cada página pública (home, propiedades, contacto, etc.) podrá tener su propia paleta de colores personalizable.

## Arquitectura Propuesta

### Opción A: Una tabla con campo `view_slug` (Recomendada)

```
frontend_color_settings
├── id
├── name (nombre de la configuración)
├── description
├── view_slug (home, properties, contact, about, etc.)
├── colors (JSON con los colores específicos de esa vista)
├── is_active (solo una activa por view_slug)
├── timestamps
```

**Ventajas:**
- Una sola tabla para todo
- Fácil de consultar y mantener
- Permite múltiples configuraciones por vista (ej: "Home Navidad", "Home Verano")
- Solo se cargan los colores de la vista actual

**Índice único:** `UNIQUE(view_slug, is_active)` donde `is_active = true`

### Opción B: Tabla separada por vista

```
frontend_color_home
frontend_color_properties
frontend_color_contact
...
```

**Desventajas:**
- Muchas tablas
- Código duplicado
- Difícil de mantener

### Opción C: Tabla de vistas + tabla de colores

```
frontend_views
├── id
├── slug (home, properties, etc.)
├── name
├── description
├── default_colors (JSON)

frontend_color_settings
├── id
├── frontend_view_id (FK)
├── name
├── colors (JSON)
├── is_active
```

**Ventajas:**
- Normalizado
- Permite definir colores por defecto por vista

**Desventajas:**
- Más complejo
- Requiere joins

---

## Recomendación: Opción A con mejoras

La **Opción A** es la más práctica y escalable. Aquí está el diseño detallado:

### 1. Estructura de la Base de Datos

```sql
-- Modificar tabla existente
ALTER TABLE frontend_color_settings 
ADD COLUMN view_slug VARCHAR(50) DEFAULT 'global' AFTER description;

-- Índice para búsqueda rápida
CREATE INDEX idx_view_slug ON frontend_color_settings(view_slug);

-- Constraint: solo una configuración activa por vista
-- (se maneja en código, no con constraint único porque is_active puede ser false)
```

### 2. Valores de `view_slug`

| Slug | Descripción | Colores Específicos |
|------|-------------|---------------------|
| `global` | Colores compartidos (header, footer, UI) | header, footer, ui, pagination |
| `home` | Página de inicio | hero, stats, features, cta_sale, cta_rent, process, testimonials, about, contact |
| `properties` | Listado de propiedades | property_cards, filters, pagination |
| `property-detail` | Detalle de propiedad | gallery, info, agent, similar |
| `contact` | Página de contacto | form, map, info |
| `about` | Página nosotros | team, history, values |
| `blog` | Blog/Noticias | articles, sidebar, comments |

### 3. Herencia de Colores

```
┌─────────────────────────────────────────────────────────┐
│                      GLOBAL                              │
│  (header, footer, ui, primary, pagination)              │
└─────────────────────────────────────────────────────────┘
                          │
          ┌───────────────┼───────────────┐
          ▼               ▼               ▼
    ┌──────────┐    ┌──────────┐    ┌──────────┐
    │   HOME   │    │PROPERTIES│    │ CONTACT  │
    │(hero,    │    │(cards,   │    │(form,    │
    │ stats,   │    │ filters) │    │ map)     │
    │ features)│    │          │    │          │
    └──────────┘    └──────────┘    └──────────┘
```

**Lógica de carga:**
1. Siempre cargar colores `global` (base)
2. Cargar colores específicos de la vista actual
3. Los colores específicos sobrescriben los globales si hay conflicto

### 4. Modelo Actualizado

```php
// app/Models/FrontendColorSetting.php

class FrontendColorSetting extends Model
{
    protected $fillable = ['name', 'description', 'view_slug', 'colors', 'is_active'];
    
    protected $casts = [
        'colors' => 'array',
        'is_active' => 'boolean',
    ];

    // Vistas disponibles con sus grupos de colores
    public static function getAvailableViews(): array
    {
        return [
            'global' => [
                'name' => 'Global (Compartido)',
                'description' => 'Colores compartidos en todas las páginas',
                'groups' => ['primary', 'header', 'footer', 'ui', 'pagination'],
            ],
            'home' => [
                'name' => 'Página de Inicio',
                'description' => 'Colores específicos del home',
                'groups' => ['hero', 'stats', 'features', 'cta_sale', 'cta_rent', 'process', 'testimonials', 'about', 'contact'],
            ],
            'properties' => [
                'name' => 'Listado de Propiedades',
                'description' => 'Colores para la página de propiedades',
                'groups' => ['property_cards', 'filters', 'search'],
            ],
            'property-detail' => [
                'name' => 'Detalle de Propiedad',
                'description' => 'Colores para la vista de detalle',
                'groups' => ['gallery', 'property_info', 'agent_card', 'similar_properties'],
            ],
            'contact' => [
                'name' => 'Página de Contacto',
                'description' => 'Colores para la página de contacto',
                'groups' => ['contact_form', 'contact_info', 'map'],
            ],
            'about' => [
                'name' => 'Página Nosotros',
                'description' => 'Colores para la página about',
                'groups' => ['team', 'history', 'values'],
            ],
        ];
    }

    // Obtener configuración activa para una vista
    public static function getActiveForView(string $viewSlug): ?self
    {
        return Cache::remember(
            "frontend_colors_{$viewSlug}",
            3600,
            fn() => self::where('view_slug', $viewSlug)
                       ->where('is_active', true)
                       ->first()
        );
    }

    // Obtener colores combinados (global + vista específica)
    public static function getMergedColorsForView(string $viewSlug): array
    {
        $globalColors = self::getActiveForView('global')?->colors ?? [];
        
        if ($viewSlug === 'global') {
            return $globalColors;
        }
        
        $viewColors = self::getActiveForView($viewSlug)?->colors ?? [];
        
        // Merge: los colores de la vista sobrescriben los globales
        return array_replace_recursive($globalColors, $viewColors);
    }

    // Colores por defecto según la vista
    public static function getDefaultColorsForView(string $viewSlug): array
    {
        $allDefaults = self::getDefaultColors();
        $viewConfig = self::getAvailableViews()[$viewSlug] ?? null;
        
        if (!$viewConfig) {
            return [];
        }
        
        $viewColors = [];
        foreach ($viewConfig['groups'] as $group) {
            if (isset($allDefaults[$group])) {
                $viewColors[$group] = $allDefaults[$group];
            }
        }
        
        return $viewColors;
    }
}
```

### 5. Servicio Actualizado

```php
// app/Services/FrontendColorService.php

class FrontendColorService
{
    // Generar CSS para una vista específica
    public function generateCssForView(string $viewSlug): string
    {
        $colors = FrontendColorSetting::getMergedColorsForView($viewSlug);
        
        if (empty($colors)) {
            return '';
        }
        
        $css = ":root {\n";
        foreach ($colors as $group => $groupColors) {
            foreach ($groupColors as $key => $value) {
                $css .= "  --fe-{$group}-{$key}: {$value};\n";
            }
        }
        $css .= "}\n";
        
        return $css;
    }

    // Obtener todas las configuraciones agrupadas por vista
    public function getAllGroupedByView(): array
    {
        $configs = FrontendColorSetting::all();
        $grouped = [];
        
        foreach (FrontendColorSetting::getAvailableViews() as $slug => $info) {
            $grouped[$slug] = [
                'info' => $info,
                'configs' => $configs->where('view_slug', $slug)->values(),
                'active' => $configs->where('view_slug', $slug)->where('is_active', true)->first(),
            ];
        }
        
        return $grouped;
    }
}
```

### 6. Layout Público Actualizado

```php
// resources/views/layouts/public.blade.php

@php
    // Detectar la vista actual
    $currentView = match(Route::currentRouteName()) {
        'home' => 'home',
        'properties', 'properties.index' => 'properties',
        'properties.show' => 'property-detail',
        'contact' => 'contact',
        'about' => 'about',
        default => 'global',
    };
    
    $frontendColorService = app(\App\Services\FrontendColorService::class);
    $frontendCss = $frontendColorService->generateCssForView($currentView);
@endphp

<style id="frontend-color-variables">
    {!! $frontendCss !!}
</style>
```

### 7. Vista de Administración

La vista de administración tendría:

1. **Selector de Vista** (tabs o dropdown)
   - Global
   - Home
   - Properties
   - Property Detail
   - Contact
   - About

2. **Por cada vista:**
   - Lista de configuraciones disponibles
   - Configuración activa marcada
   - Editor de colores (solo los grupos de esa vista)
   - Botones: Crear, Editar, Duplicar, Activar, Eliminar

```
┌─────────────────────────────────────────────────────────────┐
│  Colores del Frontend                                        │
├─────────────────────────────────────────────────────────────┤
│  [Global] [Home] [Properties] [Property Detail] [Contact]   │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  Vista: Home                                                 │
│  ─────────────────────────────────────────────────────────  │
│                                                              │
│  Configuraciones:                                            │
│  ┌──────────────────────────────────────────────────────┐   │
│  │ ● Default Home (Activa)              [Editar] [...]  │   │
│  │ ○ Home Navidad                       [Editar] [...]  │   │
│  │ ○ Home Verano                        [Editar] [...]  │   │
│  └──────────────────────────────────────────────────────┘   │
│                                                              │
│  [+ Nueva Configuración]                                     │
│                                                              │
│  ─────────────────────────────────────────────────────────  │
│  Editor de Colores (Default Home)                            │
│  ─────────────────────────────────────────────────────────  │
│                                                              │
│  [Hero] [Stats] [Features] [CTA Venta] [CTA Renta] ...      │
│                                                              │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐            │
│  │ Overlay     │ │ Badge BG    │ │ Badge Text  │            │
│  │ [#000000]   │ │ [#ffffff]   │ │ [#10b981]   │            │
│  └─────────────┘ └─────────────┘ └─────────────┘            │
│                                                              │
│                              [Cancelar] [Guardar Cambios]    │
└─────────────────────────────────────────────────────────────┘
```

### 8. API Endpoints Actualizados

```
GET    /api/frontend-colors/views                    # Lista de vistas disponibles
GET    /api/frontend-colors/view/{slug}              # Configuraciones de una vista
GET    /api/frontend-colors/view/{slug}/active       # Configuración activa de una vista
GET    /api/frontend-colors/view/{slug}/css          # CSS de una vista (público)
POST   /api/frontend-colors/view/{slug}              # Crear configuración para vista
PUT    /api/frontend-colors/{id}                     # Actualizar configuración
DELETE /api/frontend-colors/{id}                     # Eliminar configuración
POST   /api/frontend-colors/{id}/activate            # Activar configuración
```

---

## Migración del Sistema Actual

### Paso 1: Nueva migración

```php
// database/migrations/xxxx_add_view_slug_to_frontend_color_settings.php

public function up()
{
    Schema::table('frontend_color_settings', function (Blueprint $table) {
        $table->string('view_slug', 50)->default('global')->after('description');
        $table->index('view_slug');
    });
    
    // Migrar datos existentes: separar colores globales de colores de home
    // Los colores actuales se dividirán en 'global' y 'home'
}
```

### Paso 2: Migrar datos existentes

```php
// En el seeder o migración

$existing = FrontendColorSetting::first();
if ($existing) {
    $allColors = $existing->colors;
    
    // Separar colores globales
    $globalGroups = ['primary', 'header', 'footer', 'ui', 'pagination'];
    $globalColors = array_intersect_key($allColors, array_flip($globalGroups));
    
    // Separar colores de home
    $homeGroups = ['hero', 'stats', 'features', 'cta_sale', 'cta_rent', 'process', 'testimonials', 'about', 'contact'];
    $homeColors = array_intersect_key($allColors, array_flip($homeGroups));
    
    // Actualizar registro existente como global
    $existing->update([
        'view_slug' => 'global',
        'colors' => $globalColors,
    ]);
    
    // Crear registro para home
    FrontendColorSetting::create([
        'name' => 'Default Home',
        'description' => 'Colores por defecto para la página de inicio',
        'view_slug' => 'home',
        'colors' => $homeColors,
        'is_active' => true,
    ]);
}
```

---

## Beneficios de este Enfoque

1. **Independencia por vista**: Cada página tiene sus propios colores
2. **Herencia**: Los colores globales se comparten automáticamente
3. **Múltiples configuraciones**: Puedes tener "Home Navidad", "Home Verano", etc.
4. **Rendimiento**: Solo se cargan los colores necesarios para cada vista
5. **Escalabilidad**: Fácil agregar nuevas vistas
6. **Retrocompatibilidad**: El sistema actual sigue funcionando

---

## Próximos Pasos

1. [ ] Crear migración para agregar `view_slug`
2. [ ] Actualizar modelo `FrontendColorSetting`
3. [ ] Actualizar servicio `FrontendColorService`
4. [ ] Actualizar controlador con nuevos endpoints
5. [ ] Migrar datos existentes
6. [ ] Actualizar layout público para detectar vista
7. [ ] Actualizar vista de administración con selector de vistas
8. [ ] Crear seeders para cada vista
9. [ ] Documentar API actualizada

---

## Preguntas para Definir

1. ¿Qué vistas públicas tendrás además de home?
2. ¿Quieres que los colores globales siempre se carguen, o solo cuando la vista los necesite?
3. ¿Prefieres una vista de administración con tabs por vista, o una lista desplegable?
4. ¿Necesitas previsualización en vivo de los cambios?
