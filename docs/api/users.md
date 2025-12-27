# API de Usuarios

## GET /api/users

Lista todos los usuarios con paginación y filtros.

### Headers
```
Authorization: Bearer {token}
```

### Query Parameters
- `page`: Número de página (default: 1)
- `per_page`: Elementos por página (1-100, default: 15)
- `search`: Buscar en nombre y email
- `sort`: Campo de ordenamiento (created_at, updated_at, name, email)
- `order`: Dirección del orden (asc, desc)

### Respuesta Exitosa (200)
```json
{
  "success": true,
  "message": "Listado de usuarios",
  "code": "USERS_LIST",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "name": "Usuario Administrador",
        "email": "admin@ejemplo.com",
        "email_verified_at": null,
        "profile_image_id": null,
        "color_theme_id": 1,
        "created_at": "2025-10-29T17:44:25.000000Z",
        "updated_at": "2025-10-29T17:44:25.000000Z",
        "profile_image": null
      }
    ],
    "per_page": 15,
    "total": 1
  }
}
```

## POST /api/users

Crea un nuevo usuario.

### Headers
```
Authorization: Bearer {token}
Content-Type: application/json
```

### Body
```json
{
  "name": "Nuevo Usuario",
  "email": "usuario@ejemplo.com",
  "password": "contraseña_segura",
  "profile_image_id": 1
}
```

### Validación
- `name`: Requerido, máximo 255 caracteres
- `email`: Requerido, email único, máximo 255 caracteres
- `password`: Requerido, mínimo 8 caracteres
- `profile_image_id`: Opcional, debe existir en media_assets

### Respuesta Exitosa (201)
```json
{
  "success": true,
  "message": "Usuario creado exitosamente",
  "code": "USER_CREATED",
  "data": {
    "id": 2,
    "name": "Nuevo Usuario",
    "email": "usuario@ejemplo.com",
    "profile_image_id": 1,
    "color_theme_id": null,
    "created_at": "2025-10-29T17:44:25.000000Z",
    "updated_at": "2025-10-29T17:44:25.000000Z",
    "profile_image": {
      "id": 1,
      "name": "avatar.jpg",
      "url": "http://localhost:8000/storage/uploads/1/2025/10/avatar.jpg"
    }
  }
}
```

## GET /api/users/{id}

Obtiene un usuario específico.

### Headers
```
Authorization: Bearer {token}
```

### Respuesta Exitosa (200)
```json
{
  "success": true,
  "message": "Usuario obtenido",
  "code": "USER_SHOWN",
  "data": {
    "id": 1,
    "name": "Usuario Administrador",
    "email": "admin@ejemplo.com",
    "profile_image_id": null,
    "color_theme_id": 1,
    "profile_image": null
  }
}
```

## PATCH /api/users/{id}

Actualiza un usuario.

### Headers
```
Authorization: Bearer {token}
Content-Type: application/json
```

### Body
```json
{
  "name": "Nombre Actualizado",
  "email": "nuevo@ejemplo.com",
  "password": "nueva_contraseña",
  "profile_image_id": 2
}
```

### Validación
- `name`: Opcional, máximo 255 caracteres
- `email`: Opcional, email único (ignorando el usuario actual)
- `password`: Opcional, mínimo 8 caracteres (se hashea automáticamente)
- `profile_image_id`: Opcional, debe existir en media_assets

### Respuesta Exitosa (200)
```json
{
  "success": true,
  "message": "Usuario actualizado",
  "code": "USER_UPDATED",
  "data": { ... }
}
```

## DELETE /api/users/{id}

Elimina un usuario.

### Headers
```
Authorization: Bearer {token}
```

### Respuesta Exitosa (200)
```json
{
  "success": true,
  "message": "Usuario eliminado",
  "code": "USER_DELETED",
  "data": null
}
```

### Restricciones
- No se puede eliminar el usuario actualmente autenticado

## GET /api/users/{userId}/roles

Obtiene los roles asignados a un usuario.

### Headers
```
Authorization: Bearer {token}
```

### Respuesta Exitosa (200)
```json
{
  "success": true,
  "message": "Roles del usuario obtenidos",
  "code": "USER_ROLES",
  "data": [
    {
      "id": 1,
      "name": "admin",
      "guard_name": "web",
      "created_at": "2025-10-29T17:44:25.000000Z",
      "updated_at": "2025-10-29T17:44:25.000000Z"
    }
  ]
}
```

## GET /api/users/{userId}/permissions

Obtiene todos los permisos de un usuario (directos y a través de roles).

### Headers
```
Authorization: Bearer {token}
```

### Respuesta Exitosa (200)
```json
{
  "success": true,
  "message": "Permisos del usuario obtenidos",
  "code": "USER_PERMISSIONS",
  "data": [
    {
      "id": 1,
      "name": "manage_users",
      "guard_name": "web",
      "created_at": "2025-10-29T17:44:25.000000Z",
      "updated_at": "2025-10-29T17:44:25.000000Z"
    }
  ]
}
```

## POST /api/users/{userId}/roles/assign

Asigna roles a un usuario.

### Headers
```
Authorization: Bearer {token}
Content-Type: application/json
```

### Body
```json
{
  "roles": [1, 2, 3]
}
```

### Validación
- `roles`: Requerido, array de IDs de roles existentes

### Respuesta Exitosa (200)
```json
{
  "success": true,
  "message": "Roles asignados al usuario",
  "code": "USER_ROLES_ASSIGNED",
  "data": [ ...roles asignados... ]
}
```

## POST /api/users/{userId}/roles/revoke

Revoca roles de un usuario.

### Headers
```
Authorization: Bearer {token}
Content-Type: application/json
```

### Body
```json
{
  "roles": [1, 2]
}
```

### Validación
- `roles`: Requerido, array de IDs de roles existentes

### Respuesta Exitosa (200)
```json
{
  "success": true,
  "message": "Roles revocados del usuario",
  "code": "USER_ROLES_REVOKED",
  "data": [ ...roles restantes... ]
}