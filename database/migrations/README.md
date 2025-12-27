# Documentación de Migraciones de Base de Datos

Esta documentación describe todas las migraciones de base de datos actuales en el proyecto Laravel, explicando su propósito, estructura y relaciones.

## Migraciones por Fecha

### 0001_01_01_000001_create_cache_table.php
**Propósito:** Crea las tablas necesarias para el sistema de caché de Laravel.
- **Tablas creadas:**
  - `cache`: Almacena datos de caché con clave, valor y expiración.
  - `cache_locks`: Maneja bloqueos de caché para evitar conflictos.
- **Campos principales:**
  - `key` (string, primary): Clave única del caché.
  - `value` (mediumText): Valor almacenado.
  - `expiration` (integer): Timestamp de expiración.

### 0001_01_01_000002_create_jobs_table.php
**Propósito:** Crea las tablas para el sistema de colas (queues) de Laravel.
- **Tablas creadas:**
  - `jobs`: Almacena trabajos pendientes en la cola.
  - `job_batches`: Maneja lotes de trabajos.
  - `failed_jobs`: Registra trabajos fallidos.
- **Campos principales:**
  - `queue` (string): Nombre de la cola.
  - `payload` (longText): Datos serializados del trabajo.
  - `attempts` (tinyInteger): Número de intentos.

### 2025_10_27_160545_create_oauth_auth_codes_table.php
**Propósito:** Crea tabla para códigos de autorización OAuth2 (Passport).
- **Tabla:** `oauth_auth_codes`
- **Campos principales:**
  - `id` (char 80, primary): ID único del código.
  - `user_id` (foreignId): Referencia al usuario.
  - `client_id` (foreignUuid): Referencia al cliente OAuth.
  - `scopes` (text): Alcances autorizados.
  - `revoked` (boolean): Si el código está revocado.
  - `expires_at` (dateTime): Fecha de expiración.

### 2025_10_27_160546_create_oauth_access_tokens_table.php
**Propósito:** Crea tabla para tokens de acceso OAuth2.
- **Tabla:** `oauth_access_tokens`
- **Campos principales:**
  - `id` (char 80, primary): ID único del token.
  - `user_id` (foreignId, nullable): Usuario propietario.
  - `client_id` (foreignUuid): Cliente OAuth.
  - `name` (string, nullable): Nombre del token.
  - `scopes` (text): Alcances del token.
  - `revoked` (boolean): Estado de revocación.
  - `expires_at` (dateTime): Expiración del token.

### 2025_10_27_160547_create_oauth_refresh_tokens_table.php
**Propósito:** Crea tabla para tokens de refresco OAuth2.
- **Tabla:** `oauth_refresh_tokens`
- **Campos principales:**
  - `id` (char 80, primary): ID único.
  - `access_token_id` (char 80): Token de acceso relacionado.
  - `revoked` (boolean): Estado de revocación.
  - `expires_at` (dateTime): Fecha de expiración.

### 2025_10_27_160548_create_oauth_clients_table.php
**Propósito:** Crea tabla para clientes OAuth2.
- **Tabla:** `oauth_clients`
- **Campos principales:**
  - `id` (uuid, primary): ID único del cliente.
  - `owner` (morphs, nullable): Propietario polimórfico.
  - `name` (string): Nombre del cliente.
  - `secret` (string, nullable): Secreto del cliente.
  - `redirect_uris` (text): URIs de redirección.
  - `grant_types` (text): Tipos de concesión permitidos.
  - `revoked` (boolean): Estado de revocación.

### 2025_10_27_160549_create_oauth_device_codes_table.php
**Propósito:** Crea tabla para códigos de dispositivo OAuth2 (Device Code Flow).
- **Tabla:** `oauth_device_codes`
- **Campos principales:**
  - `id` (char 80, primary): ID único.
  - `user_id` (foreignId, nullable): Usuario.
  - `client_id` (foreignUuid): Cliente OAuth.
  - `user_code` (char 8, unique): Código visible al usuario.
  - `scopes` (text): Alcances solicitados.
  - `user_approved_at` (dateTime, nullable): Aprobación del usuario.

### 2025_10_27_160628_create_permission_tables.php
**Propósito:** Crea las tablas del sistema de permisos (Spatie Laravel Permission).
- **Tablas creadas:**
  - `permissions`: Almacena permisos individuales.
  - `roles`: Almacena roles.
  - `model_has_permissions`: Relación muchos-a-muchos entre modelos y permisos.
  - `model_has_roles`: Relación muchos-a-muchos entre modelos y roles.
  - `role_has_permissions`: Relación muchos-a-muchos entre roles y permisos.
- **Características:**
  - Soporte para equipos (teams) si está configurado.
  - Índices y claves foráneas para integridad referencial.
  - Limpieza del caché de permisos al ejecutar.

### 2025_10_27_164246_create_currencies_table.php
**Propósito:** Crea tabla para gestionar monedas y tipos de cambio.
- **Tabla:** `currencies`
- **Campos principales:**
  - `name` (string): Nombre de la moneda.
  - `code` (string 3): Código ISO de la moneda (ej: USD, EUR).
  - `symbol` (string 10): Símbolo de la moneda (ej: $, €).
  - `exchange_rate` (decimal 15,6): Tasa de cambio respecto a la base.
  - `is_base` (boolean): Indica si es la moneda base del sistema.

### 2025_10_28_155214_create_media_assets_table.php
**Propósito:** Crea tabla para gestionar activos multimedia (imágenes, videos, etc.).
- **Tabla:** `media_assets`
- **Campos principales:**
  - `type` (string): Tipo de medio (image, video, audio, etc.).
  - `provider` (string, nullable): Proveedor externo (AWS S3, etc.).
  - `url` (string): URL del activo.
  - `storage_path` (string, nullable): Ruta en almacenamiento local.
  - `mime_type` (string, nullable): Tipo MIME.
  - `size_bytes` (bigInteger, nullable): Tamaño en bytes.
  - `duration_seconds` (integer, nullable): Duración para videos/audio.
  - `name` (string, nullable): Nombre del archivo.
  - `alt` (string, nullable): Texto alternativo.
  - Soft deletes habilitado.

### 2025_10_28_155216_create_users_table.php
**Propósito:** Crea tabla de usuarios y tablas relacionadas de autenticación.
- **Tablas creadas:**
  - `users`: Usuarios principales.
  - `password_reset_tokens`: Tokens para reset de contraseña.
  - `sessions`: Sesiones de usuario.
- **Campos principales de users:**
  - `name` (string): Nombre del usuario.
  - `email` (string, unique): Email único.
  - `password` (string): Contraseña hasheada.
  - `profile_image_id` (foreignId, nullable): Imagen de perfil (relacionada con media_assets).
  - `color_theme_id` (foreignId, nullable): Tema de color (agregado posteriormente).

### 2025_10_28_193750_create_color_themes_table.php
**Propósito:** Crea tabla para gestionar temas de color personalizables.
- **Tabla:** `color_themes`
- **Campos principales:**
  - `name` (string, unique): Nombre único del tema.
  - `description` (string, nullable): Descripción del tema.
  - `colors` (json): Colores almacenados como JSON (primary, secondary, accent, danger, etc.).
  - `is_active` (boolean): Si el tema está activo.
  - `is_default` (boolean): Si es el tema por defecto.

### 2025_10_28_195100_add_color_theme_id_to_users_table.php
**Propósito:** Agrega relación de tema de color a la tabla de usuarios.
- **Modificación:** Agrega columna `color_theme_id` a tabla `users`.
- **Relación:** Foreign key hacia `color_themes` con eliminación en cascada (set null).

## Dependencias y Relaciones

- **OAuth Tables:** Todas las tablas oauth_* dependen de Passport y están relacionadas entre sí.
- **Permissions:** Las tablas de permisos usan configuración externa y pueden incluir soporte para equipos.
- **Users:** Depende de `media_assets` para imágenes de perfil y `color_themes` para personalización.
- **Media Assets:** Independiente, pero referenciado por users.
- **Currencies:** Independiente, usado para conversiones monetarias.
- **Color Themes:** Independiente, pero referenciado por users.

## Notas Importantes

- Todas las migraciones incluyen métodos `up()` y `down()` para migrar y rollback.
- Las tablas OAuth usan conexiones específicas configuradas en Passport.
- El sistema de permisos limpia el caché al ejecutar migraciones.
- Soft deletes está habilitado en media_assets para recuperación de datos.
- Las claves foráneas están configuradas con eliminación en cascada donde apropiado.