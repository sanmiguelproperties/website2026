# API de Media Assets

## GET /api/media

Lista todos los activos multimedia con paginación y filtros.

### Headers
```
Authorization: Bearer {token}
```

### Query Parameters
- `page`: Número de página (default: 1)
- `per_page`: Elementos por página (1-100, default: 15)
- `type`: Filtrar por tipo (image, video, audio, document) - array
- `provider`: Filtrar por proveedor - array
- `search`: Buscar en nombre y alt text
- `sort`: Campo de ordenamiento (created_at, updated_at, name, type)
- `order`: Dirección del orden (asc, desc)
- `only_trashed`: Mostrar solo elementos eliminados (soft delete)
- `with_trashed`: Incluir elementos eliminados
- `with_inactive`: Alias de with_trashed

### Respuesta Exitosa (200)
```json
{
  "success": true,
  "message": "Listado de medios",
  "code": "MEDIA_LIST",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "type": "image",
        "provider": null,
        "url": "http://localhost:8000/storage/uploads/1/2025/10/txKHc999AQWZps8fGnQXXmJ6EsDO7hnEVe0KckPj.jpg",
        "storage_path": "uploads/1/2025/10/txKHc999AQWZps8fGnQXXmJ6EsDO7hnEVe0KckPj.jpg",
        "mime_type": "image/jpeg",
        "size_bytes": 12345,
        "duration_seconds": null,
        "name": "imagen-ejemplo.jpg",
        "alt": "Texto alternativo",
        "created_at": "2025-10-29T17:44:25.000000Z",
        "updated_at": "2025-10-29T17:44:25.000000Z",
        "deleted_at": null
      }
    ],
    "per_page": 15,
    "total": 1
  }
}
```

## POST /api/media

Crea un nuevo activo multimedia.

### Headers
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

### Form Data
- `file`: Archivo a subir (máximo 200MB)
- `url`: URL externa (alternativo a file)
- `type`: Tipo (image, video, audio, document) - opcional, se detecta automáticamente
- `provider`: Proveedor externo - opcional
- `duration_seconds`: Duración en segundos - opcional
- `name`: Nombre personalizado - opcional
- `alt`: Texto alternativo - opcional

### Respuesta Exitosa (201)
```json
{
  "success": true,
  "message": "Medio creado exitosamente",
  "code": "MEDIA_CREATED",
  "data": {
    "id": 1,
    "type": "image",
    "url": "http://localhost:8000/storage/uploads/1/2025/10/txKHc999AQWZps8fGnQXXmJ6EsDO7hnEVe0KckPj.jpg",
    "storage_path": "uploads/1/2025/10/txKHc999AQWZps8fGnQXXmJ6EsDO7hnEVe0KckPj.jpg",
    "mime_type": "image/jpeg",
    "size_bytes": 12345,
    "name": "imagen-ejemplo.jpg",
    "alt": "Texto alternativo",
    "created_at": "2025-10-29T17:44:25.000000Z"
  }
}
```

### Tipos MIME Permitidos
- **Imagen**: image/jpeg, image/png, image/gif, image/webp, image/svg+xml
- **Video**: video/mp4, video/avi, video/quicktime, video/mov
- **Audio**: audio/mpeg, audio/mp3, audio/wav, audio/x-wav, audio/webm, audio/ogg, audio/opus
- **Documento**: application/pdf, text/plain, application/vnd.openxmlformats-officedocument.*, etc.

## GET /api/media/{id}

Obtiene un activo multimedia específico.

### Headers
```
Authorization: Bearer {token}
```

### Query Parameters
- `only_trashed`: Mostrar solo si está eliminado
- `with_trashed`: Incluir si está eliminado
- `with_inactive`: Alias de with_trashed

### Respuesta Exitosa (200)
```json
{
  "success": true,
  "message": "Medio obtenido",
  "code": "MEDIA_SHOWN",
  "data": {
    "id": 1,
    "type": "image",
    "url": "http://localhost:8000/storage/uploads/1/2025/10/txKHc999AQWZps8fGnQXXmJ6EsDO7hnEVe0KckPj.jpg",
    "mime_type": "image/jpeg",
    "size_bytes": 12345,
    "name": "imagen-ejemplo.jpg",
    "alt": "Texto alternativo"
  }
}
```

## PATCH /api/media/{id}

Actualiza un activo multimedia.

### Headers
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

### Form Data
- `file`: Nuevo archivo (opcional)
- `url`: Nueva URL externa (opcional)
- `type`: Nuevo tipo (opcional)
- `provider`: Nuevo proveedor (opcional)
- `duration_seconds`: Nueva duración (opcional)
- `name`: Nuevo nombre (opcional)
- `alt`: Nuevo texto alternativo (opcional)

### Respuesta Exitosa (200)
```json
{
  "success": true,
  "message": "Medio actualizado",
  "code": "MEDIA_UPDATED",
  "data": { ... }
}
```

## DELETE /api/media/{id}

Elimina un activo multimedia (soft delete).

### Headers
```
Authorization: Bearer {token}
```

### Respuesta Exitosa (200)
```json
{
  "success": true,
  "message": "Medio enviado a la papelera",
  "code": "MEDIA_TRASHED",
  "data": null
}
```

### Notas
- Los archivos eliminados pueden restaurarse
- Los archivos físicos se eliminan solo al reemplazar o eliminar permanentemente
- La estructura de directorios es: `uploads/{user_id}/{year}/{month}/`
- Los nombres de archivo se sanitizan automáticamente