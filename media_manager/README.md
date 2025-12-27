# Media Manager - Implementación Independiente

Este paquete contiene una implementación completa del administrador de archivos/media que puede ser integrado en cualquier proyecto Laravel existente.

## Estructura del Paquete

```
media_manager/
├── create_media_assets_table.php    # Migración de la tabla media_assets
├── MediaAsset.php                   # Modelo Eloquent
├── MediaAssetController.php         # Controlador API REST
├── components/
│   ├── media-input.blade.php        # Componente Blade para inputs
│   └── media-picker.blade.php       # Modal del selector de archivos
├── js/
│   └── media-picker.js              # JavaScript del picker
└── README.md                        # Este archivo
```

## Requisitos

- Laravel 10+
- PHP 8.1+
- Base de datos MySQL/PostgreSQL
- Sistema de archivos configurado (storage:link)

## Instalación

### 1. Copiar archivos al proyecto

Copia todos los archivos de esta carpeta a tu proyecto Laravel:

```bash
# Copiar migración
cp media_manager/create_media_assets_table.php database/migrations/

# Copiar modelo
cp media_manager/MediaAsset.php app/Models/

# Copiar controlador
cp media_manager/MediaAssetController.php app/Http/Controllers/

# Copiar componentes Blade
cp media_manager/components/* resources/views/components/

# Copiar JavaScript
cp media_manager/js/media-picker.js public/js/
```

### 2. Ejecutar migración

```bash
php artisan migrate
```

### 3. Configurar rutas API

Agrega estas rutas a `routes/api.php`:

```php
use App\Http\Controllers\MediaAssetController;

Route::apiResource('media', MediaAssetController::class);
```

### 4. Configurar rutas web (opcional, para vistas)

Si necesitas vistas web, agrega a `routes/web.php`:

```php
// Para vistas de administración (opcional)
Route::get('/admin/media', function () {
    return view('media.index');
})->name('media.index');
```

### 5. Incluir componentes en layouts

En tu layout principal (`resources/views/layouts/app.blade.php`), incluye:

```blade
{{-- Meta tokens para API --}}
<meta name="api-token" content="{{ auth()->user()->api_token ?? '' }}">
<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- Incluir el picker modal --}}
<x-media-picker />

{{-- Incluir JavaScript --}}
<script src="{{ asset('js/media-picker.js') }}"></script>
```

## Uso Básico

### Input simple (un archivo)

```blade
<x-media-input
    name="thumbnail_id"
    mode="single"
    placeholder="Selecciona una imagen"
/>
```

### Input múltiple (varios archivos)

```blade
<x-media-input
    name="gallery_ids"
    mode="multiple"
    :max="6"
    placeholder="Selecciona hasta 6 imágenes"
/>
```

### Con preview personalizado

```blade
<x-media-input
    name="files[]"
    mode="multiple"
    :max="10"
    :columns="4"
    preview="true"
    button="Seleccionar archivos"
/>
```

## Atributos del Componente media-input

- `name`: Nombre del input (opcional si no se envía por POST)
- `id`: ID explícito del input
- `mode`: `'single'` o `'multiple'` (default: single)
- `max`: Máximo de archivos seleccionables (solo en multiple)
- `perPage`: Archivos por página en el picker (default: 10)
- `value`: Valor inicial (ID o IDs separados por coma)
- `placeholder`: Placeholder del input
- `button`: Texto del botón (default: "Seleccionar")
- `preview`: Mostrar preview (default: true)
- `previewId`: ID personalizado del contenedor preview
- `readonly`: Input no editable manualmente (default: true)
- `columns`: Columnas del grid de preview (default: 8)

## API Endpoints

### GET /api/media
Lista archivos con filtros y paginación.

Parámetros:
- `page`: Página actual
- `per_page`: Elementos por página (máx 100)
- `type`: Filtrar por tipo (image, video, audio, document)
- `provider`: Filtrar por proveedor
- `search`: Buscar por nombre o alt
- `sort`: Campo de orden (created_at, name, type)
- `order`: Dirección (asc, desc)

### POST /api/media
Subir archivo o agregar URL externa.

Body (form-data para archivos):
- `file`: Archivo a subir (máx 200MB)
- `url`: URL externa (alternativo a file)
- `type`: Tipo forzado (image, video, audio, document)
- `provider`: Proveedor (youtube, vimeo, etc.)
- `name`: Nombre legible
- `alt`: Texto alternativo

### GET /api/media/{id}
Obtener detalles de un archivo.

### PATCH /api/media/{id}
Actualizar metadatos de un archivo.

Body:
- `name`: Nuevo nombre
- `alt`: Nuevo texto alternativo
- `url`: Nueva URL (solo para videos)
- `type`: Nuevo tipo
- `provider`: Nuevo proveedor

### DELETE /api/media/{id}
Eliminar archivo (soft delete).

## Configuración de Almacenamiento

Los archivos se almacenan en `storage/app/public/uploads/{user_id}/{year}/{month}/`.

Asegúrate de que el enlace simbólico esté creado:

```bash
php artisan storage:link
```

## Personalización

### Cambiar directorio de subida

En `MediaAssetController.php`, modifica la línea:

```php
$userId = 1; // Cambia por auth()->id() si tienes usuarios
$dir = "uploads/{$userId}/{$year}/{$month}";
```

### Agregar validaciones adicionales

En el método `store()` del controlador, agrega validaciones según necesites.

### Personalizar tipos MIME permitidos

Modifica el método `allowedMimes()` en el controlador.

## Notas de Seguridad

- Los archivos se validan por MIME type y extensión
- Se sanitizan los paths de directorio
- Se usa soft delete para recuperación
- Los usuarios solo ven sus propios archivos (modificable)

## Soporte

Este paquete es independiente y no requiere dependencias adicionales más allá de Laravel.