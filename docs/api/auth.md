# API de Autenticación

## POST /api/login

Inicia sesión y obtiene un token de acceso OAuth2.

### Headers
```
Content-Type: application/json
```

### Body
```json
{
  "email": "usuario@ejemplo.com",
  "password": "contraseña_segura"
}
```

### Respuesta Exitosa (200)
```json
{
  "success": true,
  "user": {
    "id": 1,
    "name": "Usuario",
    "email": "usuario@ejemplo.com",
    "email_verified_at": null,
    "profile_image_id": null,
    "color_theme_id": null,
    "created_at": "2025-10-29T17:44:25.000000Z",
    "updated_at": "2025-10-29T17:44:25.000000Z"
  },
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...",
  "token_type": "Bearer"
}
```

### Respuesta de Error (401)
```json
{
  "success": false,
  "message": "Credenciales inválidas."
}
```

### Validación
- `email`: Requerido, debe ser un email válido
- `password`: Requerido, mínimo 6 caracteres

### Notas
- El sistema mantiene máximo 2 tokens por usuario
- Los tokens anteriores se revocan automáticamente al iniciar sesión
- El token debe incluirse en el header `Authorization: Bearer {token}` para rutas protegidas

## GET /api/user

Obtiene información del usuario autenticado.

### Headers
```
Authorization: Bearer {token}
```

### Respuesta Exitosa (200)
```json
{
  "id": 1,
  "name": "Usuario",
  "email": "usuario@ejemplo.com",
  "email_verified_at": null,
  "profile_image_id": null,
  "color_theme_id": null,
  "created_at": "2025-10-29T17:44:25.000000Z",
  "updated_at": "2025-10-29T17:44:25.000000Z"
}
```

### Respuesta de Error (401)
```json
{
  "message": "Unauthenticated."
}
```

### Notas
- Requiere autenticación previa
- Devuelve los datos del usuario actualmente autenticado