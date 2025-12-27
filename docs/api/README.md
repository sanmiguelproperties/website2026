# Documentación de APIs REST

Esta documentación describe todas las APIs REST disponibles en el proyecto Laravel.

## Autenticación

Todas las APIs (excepto login) requieren autenticación OAuth2 mediante Bearer token.

### Headers Requeridos
```
Authorization: Bearer {access_token}
Content-Type: application/json
```

### Obtener Token
```bash
POST /api/login
Content-Type: application/json

{
  "email": "usuario@ejemplo.com",
  "password": "contraseña"
}
```

## APIs Disponibles

### 🔐 [Autenticación](auth.md)
- `POST /api/login` - Iniciar sesión
- `GET /api/user` - Obtener usuario autenticado

### 📁 [Media Assets](media.md)
- `GET /api/media` - Listar medios
- `POST /api/media` - Crear medio
- `GET /api/media/{id}` - Obtener medio
- `PATCH /api/media/{id}` - Actualizar medio
- `DELETE /api/media/{id}` - Eliminar medio (soft delete)

### 👥 [Usuarios](users.md)
- `GET /api/users` - Listar usuarios
- `POST /api/users` - Crear usuario
- `GET /api/users/{id}` - Obtener usuario
- `PATCH /api/users/{id}` - Actualizar usuario
- `DELETE /api/users/{id}` - Eliminar usuario
- `GET /api/users/{id}/roles` - Roles del usuario
- `GET /api/users/{id}/permissions` - Permisos del usuario
- `POST /api/users/{id}/roles/assign` - Asignar roles
- `POST /api/users/{id}/roles/revoke` - Revocar roles

### 💰 [Monedas](currencies.md)
- `GET /api/currencies` - Listar monedas
- `POST /api/currencies` - Crear moneda
- `GET /api/currencies/{id}` - Obtener moneda
- `PATCH /api/currencies/{id}` - Actualizar moneda
- `DELETE /api/currencies/{id}` - Eliminar moneda

### 🎨 [Temas de Color](color-themes.md)
- `GET /api/color-themes` - Listar temas
- `POST /api/color-themes` - Crear tema
- `GET /api/color-themes/{id}` - Obtener tema
- `PATCH /api/color-themes/{id}` - Actualizar tema
- `DELETE /api/color-themes/{id}` - Eliminar tema
- `POST /api/color-themes/{id}/activate` - Activar tema
- `GET /api/color-themes/active` - Tema activo

### 🛡️ [RBAC (Roles y Permisos)](rbac.md)
- `GET /api/rbac/roles` - Listar roles
- `POST /api/rbac/roles` - Crear rol
- `GET /api/rbac/roles/{id}` - Obtener rol
- `PATCH /api/rbac/roles/{id}` - Actualizar rol
- `DELETE /api/rbac/roles/{id}` - Eliminar rol
- `GET /api/rbac/permissions` - Listar permisos
- `POST /api/rbac/permissions` - Crear permiso
- `GET /api/rbac/permissions/{id}` - Obtener permiso
- `PATCH /api/rbac/permissions/{id}` - Actualizar permiso
- `DELETE /api/rbac/permissions/{id}` - Eliminar permiso
- `GET /api/rbac/roles/{id}/permissions` - Permisos del rol
- `POST /api/rbac/roles/{id}/permissions/attach` - Asignar permisos
- `POST /api/rbac/roles/{id}/permissions/sync` - Sincronizar permisos
- `POST /api/rbac/roles/{id}/permissions/detach` - Remover permisos

## Códigos de Respuesta

### Éxito
- `200` - OK (GET, PATCH)
- `201` - Created (POST)
- `204` - No Content (DELETE exitoso)

### Errores del Cliente
- `400` - Bad Request (datos inválidos)
- `401` - Unauthorized (token inválido o expirado)
- `403` - Forbidden (permisos insuficientes)
- `404` - Not Found (recurso no existe)
- `409` - Conflict (duplicado, restricción de unicidad)
- `422` - Unprocessable Entity (validación fallida)

### Errores del Servidor
- `500` - Internal Server Error

## Formato de Respuesta Estándar

### Respuesta Exitosa
```json
{
  "success": true,
  "message": "Operación exitosa",
  "code": "OPERATION_SUCCESS",
  "data": { ... }
}
```

### Respuesta de Error
```json
{
  "success": false,
  "message": "Error descriptivo",
  "code": "ERROR_CODE",
  "errors": {
    "campo": ["Error específico"]
  }
}
```

## Paginación

Las APIs que retornan listas incluyen paginación automática:

```json
{
  "data": [...],
  "current_page": 1,
  "per_page": 15,
  "total": 100,
  "last_page": 7
}
```

### Parámetros de Paginación
- `page`: Número de página (default: 1)
- `per_page`: Elementos por página (1-100, default: 15)

## Filtros y Búsqueda

### Parámetros Comunes
- `search`: Búsqueda de texto en campos relevantes
- `sort`: Campo para ordenar
- `order`: Dirección del orden (asc, desc)

## Permisos Requeridos

La mayoría de las APIs requieren permisos de administrador (`admin.api` middleware). Consulta cada endpoint específico para ver los requisitos.

## Rate Limiting

No implementado actualmente, pero se recomienda agregar límites de tasa para producción.

## Versionado

- **Versión actual**: v1
- **Prefijo**: `/api`
- Las versiones futuras usarán `/api/v2`, etc.

## Notas Importantes

- Todos los timestamps están en formato ISO 8601 UTC
- Los IDs son enteros auto-incrementales
- Las respuestas incluyen campos `created_at` y `updated_at`
- Soft deletes están habilitados donde corresponde
- Las validaciones incluyen mensajes en español