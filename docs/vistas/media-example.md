# Vista: Ejemplo Media Manager

**Archivo:** `resources/views/media-example.blade.php`
**Ruta:** `/media-example`
**Layout:** `layouts.app`

## Descripción

Esta vista demuestra las capacidades del sistema Media Manager del dashboard. Muestra diferentes formas de integrar el componente `x-media-input` para selección de archivos multimedia, incluyendo inputs simples, múltiples y con diferentes configuraciones.

## Estructura HTML

```blade
@extends('layouts.app')

@section('title', 'Ejemplo Media Manager')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
  <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 shadow-soft">
    <h1 class="text-2xl font-bold mb-6">Ejemplo de Media Manager</h1>

    <div class="space-y-6">
      <!-- Input simple -->
      <div>
        <label>Imagen de perfil (única)</label>
        <x-media-input name="profile_image_id" mode="single" placeholder="Selecciona una imagen de perfil" button="Elegir imagen" />
      </div>

      <!-- Input múltiple -->
      <div>
        <label>Galería de imágenes (máximo 6)</label>
        <x-media-input name="gallery_ids" mode="multiple" :max="6" placeholder="Selecciona hasta 6 imágenes" button="Seleccionar imágenes" />
      </div>

      <!-- Input con preview personalizado -->
      <div>
        <label>Archivos adjuntos (máximo 10, 4 columnas)</label>
        <x-media-input name="attachments[]" mode="multiple" :max="10" :columns="4" preview="true" button="Seleccionar archivos" />
      </div>

      <!-- Formulario de ejemplo -->
      <form method="POST" action="#">
        @csrf
        <!-- Campos del formulario -->
        <x-media-input name="featured_image_id" mode="single" placeholder="Selecciona imagen destacada" />
        <x-media-input name="file_ids[]" mode="multiple" :max="5" placeholder="Selecciona archivos adjuntos" />
        <button type="submit">Guardar</button>
      </form>
    </div>
  </div>
</div>
@endsection
```

## Componentes Media Input

### Sintaxis General

```blade
<x-media-input
  name="campo"
  mode="single|multiple"
  :max="número"
  :columns="número"
  placeholder="Texto del placeholder"
  button="Texto del botón"
  preview="true|false"
  readonly="true|false"
/>
```

### Atributos del Componente

| Atributo | Tipo | Default | Descripción |
|----------|------|---------|-------------|
| `name` | string | null | Nombre del campo del formulario |
| `id` | string | auto | ID único del input |
| `mode` | string | 'single' | 'single' o 'multiple' |
| `max` | int | null | Máximo de archivos (solo multiple) |
| `perPage` | int | 10 | Archivos por página en el picker |
| `value` | string | '' | Valor inicial (ID o IDs separados por coma) |
| `placeholder` | string | null | Placeholder del input |
| `button` | string | 'Seleccionar' | Texto del botón |
| `preview` | bool | true | Mostrar contenedor de preview |
| `previewId` | string | auto | ID del contenedor de preview |
| `readonly` | bool | true | Input no editable manualmente |
| `inputClass` | string | '' | Clases CSS adicionales para el input |
| `buttonClass` | string | '' | Clases CSS adicionales para el botón |
| `wrapperClass` | string | '' | Clases CSS adicionales para el wrapper |
| `previewClass` | string | '' | Clases CSS adicionales para el preview |
| `columns` | int | null | Número de columnas en el grid de preview |

## Ejemplos de Uso

### 1. Input Simple (Un Archivo)

```blade
<x-media-input
  name="profile_image_id"
  mode="single"
  placeholder="Selecciona una imagen de perfil"
  button="Elegir imagen"
/>
```

**Características:**
- Solo permite seleccionar un archivo
- Preview automático
- Valor almacenado como ID único

### 2. Input Múltiple con Límite

```blade
<x-media-input
  name="gallery_ids"
  mode="multiple"
  :max="6"
  placeholder="Selecciona hasta 6 imágenes"
  button="Seleccionar imágenes"
/>
```

**Características:**
- Permite múltiples archivos
- Límite máximo de 6 archivos
- Array de IDs como valor

### 3. Input con Configuración Avanzada

```blade
<x-media-input
  name="attachments[]"
  mode="multiple"
  :max="10"
  :columns="4"
  preview="true"
  button="Seleccionar archivos"
/>
```

**Características:**
- Grid de 4 columnas en preview
- Máximo 10 archivos
- Nombre con array notation

## Modal Media Picker

### Funcionalidades del Modal

1. **Biblioteca de Medios**: Grid de archivos disponibles
2. **Upload**: Subir nuevos archivos
3. **URL/Embed**: Agregar videos por URL
4. **Filtros**: Por tipo (imagen, video, audio, documento)
5. **Búsqueda**: Por nombre, URL o MIME type
6. **Paginación**: Navegación entre páginas
7. **Selección Múltiple**: Panel de archivos seleccionados

### Estructura del Modal

```html
<div id="archive_manager-root" class="fixed inset-0 z-[9999] hidden">
  <!-- Backdrop -->
  <div class="fixed inset-0 bg-black/45 backdrop-blur-sm"></div>

  <!-- Modal -->
  <div class="relative mx-auto my-0 w-screen max-w-[98vw] md:my-10 md:w-[98vw]">
    <div class="rounded-none md:rounded-2xl border border-[var(--c-border)] bg-[var(--c-surface)]/90 backdrop-blur-xl shadow-2xl overflow-hidden flex flex-col h-[100vh] md:h-[80vh]">

      <!-- Header -->
      <div class="flex items-center justify-between px-3 md:px-4 py-3 border-b border-[var(--c-border)]">
        <div class="text-lg font-semibold text-[var(--c-text)]">Biblioteca de medios</div>
        <button data-mf="close">✕</button>
      </div>

      <!-- Toolbar -->
      <div class="px-3 md:px-4 py-3 border-b border-[var(--c-border)]">
        <!-- Controles de búsqueda y filtros -->
      </div>

      <!-- Cuerpo -->
      <div class="px-3 md:px-4 pt-3 pb-2 flex-1 min-h-0 overflow-y-auto">
        <!-- Panel de seleccionados -->
        <!-- Grid principal -->
        <!-- Paginación -->
      </div>

      <!-- Footer -->
      <div class="px-3 md:px-4 py-3 border-t border-[var(--c-border)]">
        <!-- Controles de acción -->
      </div>
    </div>
  </div>

  <!-- Panel Editor -->
  <!-- Modal Upload -->
  <!-- Modal URL -->
  <!-- Toast -->
</div>
```

## JavaScript Integration

### Eventos del Sistema

Los inputs media se integran automáticamente con el modal a través de:

```javascript
// Atributos data-* en el input
data-filepicker="single|multiple"
data-fp-max="número"
data-fp-per-page="número"
data-fp-preview="#selector"

// Botón para abrir
data-fp-open
```

### Callbacks y Eventos

```javascript
// Evento cuando se seleccionan archivos
window.addEventListener('media:selected', (e) => {
  const { files, input } = e.detail;
  // Manejar selección
});

// Evento cuando se cierra el modal
window.addEventListener('media:closed', (e) => {
  // Limpiar estado si es necesario
});
```

## Sistema de Preview

### Preview Simple
Para inputs `mode="single"`, muestra una miniatura del archivo seleccionado.

### Preview Grid
Para inputs `mode="multiple"`, muestra un grid de miniaturas con:
- Imágenes con `object-cover`
- Controles de eliminación
- Indicadores de tipo de archivo

## Consideraciones Técnicas

### Rendimiento
- Carga diferida de imágenes
- Virtual scrolling para grandes bibliotecas
- Optimización de thumbnails

### Seguridad
- Validación de tipos de archivo
- Límites de tamaño
- Sanitización de URLs

### Accesibilidad
- Navegación por teclado
- Etiquetas ARIA
- Soporte para lectores de pantalla

## Personalización

### Estilos Personalizados

```blade
<x-media-input
  name="custom_field"
  mode="multiple"
  wrapperClass="border-red-500"
  inputClass="text-red-600"
  buttonClass="bg-red-500 hover:bg-red-600"
  previewClass="border-red-200"
/>
```

### Configuración del Modal

```javascript
// Configuración global
window.mediaPickerConfig = {
  maxFileSize: 10 * 1024 * 1024, // 10MB
  allowedTypes: ['image/*', 'video/*', 'audio/*'],
  defaultPerPage: 20
};
```

## API Backend

### Endpoints Requeridos

- `GET /api/media` - Listar archivos
- `POST /api/media` - Subir archivo
- `POST /api/media/url` - Crear desde URL
- `PUT /api/media/{id}` - Actualizar metadatos
- `DELETE /api/media/{id}` - Eliminar archivo

### Formato de Respuesta

```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "imagen.jpg",
    "url": "https://example.com/storage/1/imagen.jpg",
    "type": "image",
    "mime_type": "image/jpeg",
    "size": 1024000,
    "alt": "Descripción alternativa"
  }
}
```

## Próximas Mejoras

1. **Drag & Drop Directo**: Arrastrar archivos al input
2. **Crop/Resize**: Edición de imágenes
3. **Conversión**: Cambiar formatos automáticamente
4. **CDN Integration**: Soporte para servicios externos
5. **Bulk Operations**: Acciones masivas en el modal