# API de Control de Acceso Basado en Roles (RBAC)

## Roles

### GET /api/rbac/roles

Lista todos los roles con paginación y filtros.

#### Headers
```
Authorization: Bearer {token}
```

#### Query Parameters
- `guard`: Guard a filtrar (web, api) - default: web
- `page`: Número de página
- `per_page`: Elementos por página (1-100) - default: 15
- `sort`: Campo de ordenamiento (name) - default: name
- `order`: Dirección (asc, desc) - default: asc
- `q`: Búsqueda por nombre

#### Respuesta Exitosa (200)
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "admin",
      "guard_name": "web",
      "created_at": "2025-10-29T17:44:25.000000Z",
      "updated_at": "2025-10-29T17:44:25.000000Z"
    }
  ],
  "message": "",
  "pagination": {
    "current_page": 1,
    "per_page": 15,
    "total": 1,
    "last_page": 1
  }
}
```

### POST /api/rbac/roles

Crea un nuevo rol.

#### Headers
```
Authorization: Bearer {token}
Content-Type: application/json
```

#### Body
```json
{
  "name": "editor",
  "guard_name": "web"
}
```

#### Validación
- `name`: Requerido, máximo 255 caracteres, único por guard
- `guard_name`: Opcional (web, api), default: web

#### Respuesta Exitosa (201)
```json
{
  "success": true,
  "data": {
    "id": 2,
    "name": "editor",
    "guard_name": "web",
    "created_at": "2025-10-29T17:44:25.000000Z",
    "updated_at": "2025-10-29T17:44:25.000000Z"
  },
  "message": "Rol creado"
}
```

### GET /api/rbac/roles/{id}

Obtiene un rol específico.

#### Query Parameters
- `guard`: Guard específico (web, api) - default: web

### PATCH /api/rbac/roles/{id}

Actualiza un rol.

#### Body
```json
{
  "name": "super_editor",
  "guard_name": "api"
}
```

### DELETE /api/rbac/roles/{id}

Elimina un rol.

#### Restricciones
- No se puede eliminar un rol que esté asignado a usuarios

## Permisos

### GET /api/rbac/permissions

Lista todos los permisos con paginación y filtros.

#### Query Parameters
- `guard`: Guard a filtrar (web, api) - default: web
- `page`: Número de página
- `per_page`: Elementos por página (1-100) - default: 15
- `sort`: Campo de ordenamiento (name) - default: name
- `order`: Dirección (asc, desc) - default: asc
- `q`: Búsqueda por nombre

#### Respuesta Exitosa (200)
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "manage_users",
      "guard_name": "web",
      "created_at": "2025-10-29T17:44:25.000000Z",
      "updated_at": "2025-10-29T17:44:25.000000Z"
    }
  ],
  "pagination": { ... }
}
```

### POST /api/rbac/permissions

Crea un nuevo permiso.

#### Body
```json
{
  "name": "view_reports",
  "guard_name": "web"
}
```

### GET /api/rbac/permissions/{id}

Obtiene un permiso específico.

### PATCH /api/rbac/permissions/{id}

Actualiza un permiso.

### DELETE /api/rbac/permissions/{id}

Elimina un permiso.

#### Restricciones
- No se puede eliminar un permiso asignado directamente a usuarios
- No se puede eliminar un permiso asignado a roles

## Gestión de Permisos en Roles

### GET /api/rbac/roles/{roleId}/permissions

Obtiene los permisos asignados a un rol.

#### Query Parameters
- `guard`: Guard específico - default: web

#### Respuesta Exitosa (200)
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "manage_users",
      "guard_name": "web",
      "created_at": "2025-10-29T17:44:25.000000Z",
      "updated_at": "2025-10-29T17:44:25.000000Z"
    }
  ],
  "message": "Permisos obtenidos"
}
```

### POST /api/rbac/roles/{roleId}/permissions/attach

Asigna permisos a un rol.

#### Body
```json
{
  "permissions": [1, 2, 3],
  "mode": "by_id",
  "guard_name": "web"
}
```

#### Validación
- `permissions`: Requerido, array de IDs o nombres
- `mode`: Requerido (by_id, by_name)
- `guard_name`: Opcional (web, api) - default: web

#### Modos
- `by_id`: Array de IDs de permisos
- `by_name`: Array de nombres de permisos

#### Respuesta Exitosa (200)
```json
{
  "success": true,
  "data": [ ...permisos asignados... ],
  "message": "Permisos asignados"
}
```

### POST /api/rbac/roles/{roleId}/permissions/sync

Sincroniza permisos de un rol (reemplaza todos los permisos existentes).

#### Body
```json
{
  "permissions": ["manage_users", "view_reports"],
  "mode": "by_name",
  "guard_name": "web"
}
```

#### Respuesta Exitosa (200)
```json
{
  "success": true,
  "data": [ ...permisos sincronizados... ],
  "message": "Permisos sincronizados"
}
```

### POST /api/rbac/roles/{roleId}/permissions/detach

Remueve permisos de un rol.

#### Body
```json
{
  "permissions": [1, 2],
  "mode": "by_id",
  "guard_name": "web"
}
```

#### Respuesta Exitosa (200)
```json
{
  "success": true,
  "data": [ ...permisos restantes... ],
  "message": "Permisos removidos"
}
```

## Guards y Replicación

### Guards Soportados
- `web`: Para autenticación web tradicional
- `api`: Para autenticación API (OAuth)

### Replicación Automática
El sistema incluye un servicio `RbacMirror` que automáticamente:
- Replica roles y permisos entre guards `web` y `api`
- Mantiene consistencia entre ambos guards
- Limpia caché de permisos cuando es necesario

### Notas Importantes
- Los guards `web` y `api` están separados pero pueden replicarse
- Los permisos pueden asignarse directamente a usuarios o a través de roles
- El sistema usa Spatie Laravel Permission package
- Los cambios en un guard pueden replicarse automáticamente al guard espejo