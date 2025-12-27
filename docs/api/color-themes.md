# API de Temas de Color

## GET /api/color-themes

Lista todos los temas de color con paginación y filtros.

### Headers
```
Authorization: Bearer {token}
```

### Query Parameters
- `page`: Número de página (default: 1)
- `per_page`: Elementos por página (default: 15)
- `search`: Buscar en nombre y descripción

### Respuesta Exitosa (200)
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "name": "Tema Oscuro",
        "description": "Tema con colores oscuros para uso nocturno",
        "colors": {
          "primary": "#1f2937",
          "secondary": "#374151",
          "accent": "#3b82f6",
          "danger": "#ef4444",
          "success": "#10b981",
          "warning": "#f59e0b"
        },
        "is_active": true,
        "is_default": true,
        "created_at": "2025-10-29T17:44:25.000000Z",
        "updated_at": "2025-10-29T17:44:25.000000Z"
      }
    ],
    "per_page": 15,
    "total": 1
  },
  "message": "Temas obtenidos correctamente"
}
```

## POST /api/color-themes

Crea un nuevo tema de color.

### Headers
```
Authorization: Bearer {token}
Content-Type: application/json
```

### Body
```json
{
  "name": "Tema Claro",
  "description": "Tema con colores claros para uso diurno",
  "colors": {
    "primary": "#ffffff",
    "secondary": "#f3f4f6",
    "accent": "#2563eb",
    "danger": "#dc2626",
    "success": "#059669",
    "warning": "#d97706"
  }
}
```

### Validación
- `name`: Requerido, máximo 255 caracteres, único
- `description`: Opcional, máximo 500 caracteres
- `colors`: Requerido, objeto con claves de colores
- `colors.*`: Cada color debe ser un string válido (hex, rgb, etc.)

### Respuesta Exitosa (201)
```json
{
  "success": true,
  "data": {
    "id": 2,
    "name": "Tema Claro",
    "description": "Tema con colores claros para uso diurno",
    "colors": {
      "primary": "#ffffff",
      "secondary": "#f3f4f6",
      "accent": "#2563eb",
      "danger": "#dc2626",
      "success": "#059669",
      "warning": "#d97706"
    },
    "is_active": false,
    "is_default": false,
    "created_at": "2025-10-29T17:44:25.000000Z",
    "updated_at": "2025-10-29T17:44:25.000000Z"
  },
  "message": "Tema creado correctamente"
}
```

## GET /api/color-themes/{id}

Obtiene un tema de color específico.

### Headers
```
Authorization: Bearer {token}
```

### Respuesta Exitosa (200)
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Tema Oscuro",
    "description": "Tema con colores oscuros para uso nocturno",
    "colors": { ... },
    "is_active": true,
    "is_default": true
  },
  "message": "Tema obtenido correctamente"
}
```

## PATCH /api/color-themes/{id}

Actualiza un tema de color.

### Headers
```
Authorization: Bearer {token}
Content-Type: application/json
```

### Body
```json
{
  "name": "Tema Oscuro Actualizado",
  "description": "Descripción actualizada",
  "colors": {
    "primary": "#111827",
    "secondary": "#1f2937"
  }
}
```

### Validación
- `name`: Opcional, máximo 255 caracteres, único (ignorando el tema actual)
- `description`: Opcional, máximo 500 caracteres
- `colors`: Opcional, objeto con claves de colores
- `colors.*`: Cada color debe ser un string válido

### Respuesta Exitosa (200)
```json
{
  "success": true,
  "message": "Tema actualizado correctamente"
}
```

## DELETE /api/color-themes/{id}

Elimina un tema de color.

### Headers
```
Authorization: Bearer {token}
```

### Respuesta Exitosa (200)
```json
{
  "success": true,
  "message": "Tema eliminado correctamente"
}
```

### Restricciones
- No se pueden eliminar temas que estén marcados como default o active

## POST /api/color-themes/{id}/activate

Activa un tema específico como el tema activo del sistema.

### Headers
```
Authorization: Bearer {token}
```

### Respuesta Exitosa (200)
```json
{
  "success": true,
  "message": "Tema activado correctamente"
}
```

### Notas
- Solo un tema puede estar activo a la vez
- Activar un tema automáticamente desactiva los demás

## GET /api/color-themes/active

Obtiene el tema actualmente activo.

### Headers
```
Authorization: Bearer {token}
```

### Respuesta Exitosa (200)
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Tema Oscuro",
    "colors": { ... },
    "is_active": true,
    "is_default": true
  },
  "message": "Tema activo obtenido correctamente"
}
```

### Notas
- Los colores se almacenan como JSON en la base de datos
- Los temas pueden ser asignados a usuarios individuales
- El sistema mantiene un tema activo global y temas por defecto