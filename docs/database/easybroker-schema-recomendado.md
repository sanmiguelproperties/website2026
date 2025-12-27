# Esquema de base de datos recomendado (Laravel/MySQL) adaptado a tu proyecto + EasyBroker

Este documento propone una estructura relacional para integrar EasyBroker **sin duplicar** la administración de archivos (ya existe `media_assets`) y **reutilizando** tu modelo de monedas (`currencies`).

> Objetivo principal: guardar el inventario de EasyBroker (agencias, agentes, propiedades, operaciones, ubicación, features/tags, estado de sincronización y leads) manteniendo intactos los modelos actuales de:
>
> - Usuarios + Roles + Permisos (Spatie)
> - Monedas y tipos de cambio (`currencies`)
> - Archivos / multimedia (`media_assets`)

---

## 0) Tablas existentes (se mantienen)

### 0.1 `users` + RBAC (Spatie)

Tu proyecto ya tiene `users` y las tablas de Spatie (`roles`, `permissions`, pivots). No se recomienda mezclarlas con los “agents” de EasyBroker.

**Relación existente importante**

- `users.profile_image_id` → `media_assets.id` (ya implementado)

### 0.2 `media_assets` (se mantiene)

`media_assets` ya centraliza la gestión de archivos y funciona bien en tu proyecto. La integración con propiedades debe **referenciar** esta tabla, no crear otra tabla “de archivos”.

Campos actuales (resumen):

- `type`, `provider`, `url`, `storage_path`, `mime_type`, `size_bytes`, `duration_seconds`, `name`, `alt`, `created_at`, `deleted_at`

### 0.3 `currencies` (se mantiene)

Tu tabla `currencies` maneja:

- `name`, `code(3)`, `symbol`, `exchange_rate(15,6)`, `is_base`

**Recomendación mínima (opcional pero muy útil):**

- `UNIQUE(currencies.code)` para poder referenciar por código y evitar duplicados.

---

## 1) Convenciones recomendadas para la integración

### 1.1 Prefijo “easybroker_” en campos externos

Para diferenciar claramente los IDs/fechas provenientes de la API:

- `easybroker_public_id`
- `easybroker_created_at`
- `easybroker_updated_at`

### 1.2 Campo comodín `raw_payload` (JSON)

En las entidades que vienen de API (agencias, agentes, propiedades, listing statuses, leads) conviene un `raw_payload JSON NULL` para:

- compatibilidad futura (campos nuevos)
- debugging de sincronización
- auditoría rápida

### 1.3 Evitar duplicación de multimedia

No crees `property_images`, `property_videos` si ya tienes `media_assets`. En lugar de eso, crea **una pivot** que relacione propiedades con `media_assets` y guarde metadata (orden, rol, título, etc.).

---

## 2) Tablas nuevas propuestas

> Nota: los nombres están pensados para que la app pueda evolucionar a “propiedades propias” incluso sin EasyBroker. Si tu app será 100% EasyBroker, se puede simplificar.

### 2.1 `agencies`

Agencias (tenants) que proveen inventario.

| Campo | Tipo | Notas |
|---|---|---|
| `id` | BIGINT PK | Puede ser el `agency_id` de EasyBroker (recomendado si solo existen agencias EasyBroker) |
| `name` | VARCHAR(255) | |
| `account_owner` | VARCHAR(255) NULL | |
| `logo_url` | TEXT NULL | (opcional: también podrías persistir como `media_assets` y referenciar) |
| `phone` | VARCHAR(50) NULL | |
| `email` | VARCHAR(255) NULL | |
| `raw_payload` | JSON NULL | |
| `created_at`, `updated_at` | DATETIME | |

Índices:

- `PK(id)`
- `INDEX(name)`

### 2.2 Agentes como `users` (recomendado según tu requerimiento)

Quieres que **los usuarios también puedan ser agentes**. Con Spatie esto encaja perfecto: el “agente” es simplemente un **rol** (`agent`) y los campos extra del agente viven en `users` como columnas **nullable**.

#### 2.2.1 Rol `agent` (Spatie)

- Crea un rol `agent` (y permisos asociados) vía seeder.
- La app llenará/mostrará campos de agente **solo** cuando el usuario tenga ese rol.

#### 2.2.2 Extensión de la tabla `users` para perfil de agente

Agregar columnas **opcionales** a `users`:

| Campo | Tipo | Notas |
|---|---|---|
| `agency_id` | BIGINT NULL FK → `agencies.id` | para asociar el usuario-agente a una agencia (si aplica) |
| `agent_phone` | VARCHAR(50) NULL | móvil/teléfono para publicar |
| `agent_public_email` | VARCHAR(255) NULL | email público/para leads (opcional, puede ser distinto a `users.email`) |
| `agent_bio` | TEXT NULL | descripción del agente (opcional) |
| `agent_profile_media_asset_id` | BIGINT NULL FK → `media_assets.id` | foto de perfil “como agente” (si quieres separarla del `profile_image_id`) |
| `easybroker_agent_id` | VARCHAR(50) NULL UNIQUE | ej. `edi_3` |
| `easybroker_agent_payload` | JSON NULL | raw de EasyBroker para ese agente |

Índices recomendados:

- `INDEX(agency_id)`
- `UNIQUE(easybroker_agent_id)`

> Nota: puedes omitir `agent_profile_media_asset_id` si te basta con `users.profile_image_id` (ya existe). Lo dejé como opcional porque a veces la foto del “perfil de usuario” y del “perfil público de agente” no es la misma.

### 2.3 `properties`

Entidad principal del inventario.

| Campo | Tipo | Notas |
|---|---|---|
| `id` | BIGINT PK autoincrement | interno |
| `agency_id` | BIGINT FK → `agencies.id` | obligatorio |
| `agent_user_id` | BIGINT NULL FK → `users.id` | puede ser NULL (usuario con rol `agent`) |
| `easybroker_public_id` | VARCHAR(50) NOT NULL | ej. `EB-B0579` |
| `easybroker_agent_id` | VARCHAR(50) NULL | respaldo: id del agente en EasyBroker (si no se pudo mapear a user) |
| `published` | TINYINT(1) NOT NULL DEFAULT 0 | desde `listing_statuses` |
| `easybroker_created_at` | DATETIME NULL | |
| `easybroker_updated_at` | DATETIME NULL | |
| `last_synced_at` | DATETIME NULL | |
| `title` | VARCHAR(255) NULL | |
| `description` | MEDIUMTEXT NULL | |
| `url` | TEXT NULL | |
| `ad_type` | VARCHAR(50) NULL | |
| `property_type_name` | VARCHAR(100) NULL | |
| `bedrooms` | INT NULL | |
| `bathrooms` | INT NULL | |
| `half_bathrooms` | INT NULL | |
| `parking_spaces` | INT NULL | |
| `lot_size` | DECIMAL(12,2) NULL | |
| `construction_size` | DECIMAL(12,2) NULL | |
| `expenses` | DECIMAL(14,2) NULL | normalizado |
| `lot_length` | DECIMAL(12,2) NULL | |
| `lot_width` | DECIMAL(12,2) NULL | |
| `floors` | INT NULL | |
| `floor` | VARCHAR(20) NULL | |
| `age` | VARCHAR(20) NULL | |
| `virtual_tour_url` | TEXT NULL | |
| `cover_media_asset_id` | BIGINT NULL FK → `media_assets.id` | portada (opcional, recomendado) |
| `raw_payload` | JSON NULL | |
| `created_at`, `updated_at` | DATETIME | |

Índices:

- `UNIQUE(agency_id, easybroker_public_id)` (multi-agencia)
- `INDEX(published)`
- `INDEX(easybroker_updated_at)`
- `INDEX(property_type_name)`

### 2.4 `property_locations` (1:1)

Separada para mantener orden y evitar columnas excesivas en `properties`.

| Campo | Tipo | Notas |
|---|---|---|
| `property_id` | BIGINT PK FK → `properties.id` | 1:1 |
| `region` | VARCHAR(255) NULL | |
| `city` | VARCHAR(255) NULL | |
| `city_area` | VARCHAR(255) NULL | |
| `street` | VARCHAR(255) NULL | |
| `postal_code` | VARCHAR(20) NULL | |
| `show_exact_location` | TINYINT(1) NULL | |
| `latitude` | DECIMAL(10,7) NULL | |
| `longitude` | DECIMAL(10,7) NULL | |
| `raw_payload` | JSON NULL | |

Índices:

- `INDEX(region, city)`
- (opcional) índice por `latitude/longitude` si harás búsquedas por mapa

### 2.5 `property_operations` (1:N)

Operaciones (venta/renta, etc.).

| Campo | Tipo | Notas |
|---|---|---|
| `id` | BIGINT PK autoincrement | |
| `property_id` | BIGINT FK → `properties.id` | |
| `operation_type` | VARCHAR(20) NOT NULL | ej. `sale`, `rental` |
| `amount` | DECIMAL(18,2) NULL | |
| `currency_id` | BIGINT NULL FK → `currencies.id` | usa tu tabla actual |
| `currency_code` | CHAR(3) NULL | respaldo / dato crudo EasyBroker |
| `formatted_amount` | VARCHAR(50) NULL | |
| `unit` | VARCHAR(20) NULL | ej. `total` |
| `raw_payload` | JSON NULL | |

Índices:

- `INDEX(property_id)`
- `INDEX(operation_type)`
- `INDEX(currency_id)`
- `INDEX(currency_code)`

### 2.6 `property_media_assets` (pivot hacia `media_assets`) ✅ (reemplaza property_images/property_videos)

Esta tabla es la pieza clave para **no duplicar** el manejo de archivos.

| Campo | Tipo | Notas |
|---|---|---|
| `property_id` | BIGINT FK → `properties.id` | |
| `media_asset_id` | BIGINT FK → `media_assets.id` | |
| `role` | VARCHAR(20) | ej. `image`, `video`, `floorplan`, `document` |
| `title` | VARCHAR(255) NULL | título desde EasyBroker (si aplica) |
| `position` | INT NULL | orden |
| `checksum` | CHAR(32) NULL | opcional para detectar cambios |
| `source_url` | TEXT NULL | si quieres conservar la URL original sin tocar `media_assets.url` |
| `raw_payload` | JSON NULL | |

Clave/Índices:

- `PK(property_id, media_asset_id)`
- `INDEX(property_id, role)`
- (opcional) `UNIQUE(property_id, source_url(191))` si usas `source_url`

> Nota: `media_assets` ya trae `name` y `alt`; úsalo cuando puedas. En la pivot solo guarda lo que sea **contextual a la propiedad** (posición, rol, título específico, etc.).

### 2.7 Features y Tags (normalización)

#### 2.7.1 `features`

| Campo | Tipo | Notas |
|---|---|---|
| `id` | BIGINT PK autoincrement | |
| `name` | VARCHAR(255) NOT NULL | |
| `locale` | VARCHAR(10) NULL | opcional |
| `created_at`, `updated_at` | DATETIME | |

Índices:

- `UNIQUE(name, locale)`

#### 2.7.2 `property_feature`

Pivot N:N

- `property_id` BIGINT FK → `properties.id`
- `feature_id` BIGINT FK → `features.id`
- `PK(property_id, feature_id)`

#### 2.7.3 `tags`

| Campo | Tipo |
|---|---|
| `id` | BIGINT PK autoincrement |
| `name` | VARCHAR(100) NOT NULL |
| `slug` | VARCHAR(120) NULL |
| `created_at`, `updated_at` | DATETIME |

Índices:

- `UNIQUE(name)`

#### 2.7.4 `property_tag`

- `property_id` BIGINT FK → `properties.id`
- `tag_id` BIGINT FK → `tags.id`
- `PK(property_id, tag_id)`

### 2.8 `easybroker_property_listing_statuses` (sync rápido)

Basada en `listing_statuses`: te permite decidir si necesitas pedir el detalle o solo despublicar.

| Campo | Tipo | Notas |
|---|---|---|
| `id` | BIGINT PK autoincrement | PK interna para facilitar controladores REST |
| `agency_id` | BIGINT FK → `agencies.id` | |
| `easybroker_public_id` | VARCHAR(50) | |
| `property_id` | BIGINT NULL FK → `properties.id` | enlace opcional a tu propiedad local |
| `published` | TINYINT(1) NOT NULL | |
| `easybroker_updated_at` | DATETIME NOT NULL | |
| `last_polled_at` | DATETIME NULL | |
| `raw_payload` | JSON NULL | |

Clave/Índices:

- `UNIQUE(agency_id, easybroker_public_id)`
- `INDEX(property_id)`
- `INDEX(published)`
- `INDEX(easybroker_updated_at)`

### 2.9 `locations_catalog` (catálogo jerárquico)

Como `/locations` no expone IDs estables, usa `full_name` como clave natural.

| Campo | Tipo | Notas |
|---|---|---|
| `id` | BIGINT PK autoincrement | PK interna para facilitar controladores REST |
| `full_name` | VARCHAR(255) UNIQUE | clave natural (EasyBroker) |
| `name` | VARCHAR(255) NOT NULL | |
| `type` | ENUM('Country','State','City','Neighborhood') | |
| `parent_id` | BIGINT NULL FK → `locations_catalog.id` | jerarquía |
| `created_at`, `updated_at` | DATETIME | |

Índices:

- `INDEX(type)`
- `INDEX(parent_id)`

### 2.10 `contact_requests` (leads)

Guarda lo que llega en tu portal y lo que envías a EasyBroker.

| Campo | Tipo | Notas |
|---|---|---|
| `id` | BIGINT PK autoincrement | |
| `agency_id` | BIGINT NULL FK → `agencies.id` | si la puedes inferir |
| `property_id` | BIGINT NULL FK → `properties.id` | recomendado (mejor que solo public_id) |
| `property_public_id` | VARCHAR(50) NOT NULL | respaldo EasyBroker |
| `remote_id` | VARCHAR(100) NOT NULL UNIQUE | tu id único |
| `source` | VARCHAR(100) NULL | portal/origen |
| `name` | VARCHAR(255) NULL | |
| `email` | VARCHAR(255) NULL | |
| `phone` | VARCHAR(50) NULL | |
| `message` | TEXT NOT NULL | |
| `happened_at` | DATETIME NULL | al listar en EB |
| `status` | VARCHAR(50) NULL | ej. `processed` |
| `sent_to_easybroker_at` | DATETIME NULL | |
| `raw_payload` | JSON NULL | |
| `created_at`, `updated_at` | DATETIME | |

Índices:

- `UNIQUE(remote_id)`
- `INDEX(property_public_id)`
- `INDEX(property_id)`
- `INDEX(happened_at)`

---

## 3) Relaciones (resumen)

- `agencies (1) ── (N) agents`
- `agencies (1) ── (N) properties`
- `agents (1) ── (N) properties` (opcional)
- `properties (1) ── (1) property_locations`
- `properties (1) ── (N) property_operations`
- `properties (N) ── (N) features` vía `property_feature`
- `properties (N) ── (N) tags` vía `property_tag`
- `properties (N) ── (N) media_assets` vía `property_media_assets`
- `properties (1) ── (N) contact_requests` (si guardas `property_id`)
- `agencies (1) ── (N) easybroker_property_listing_statuses`

---

## 4) Recomendación de migraciones (orden lógico)

1. `create_agencies_table`
2. `add_agent_fields_to_users_table` (campos nullable + FK a `agencies` y `media_assets`)
3. `create_properties_table` (con FK a `users` como agente + FK a `media_assets` para portada)
4. `create_property_locations_table`
5. `create_property_operations_table` (con FK a `currencies`)
6. `create_property_media_assets_table`
7. `create_features_table` + `create_property_feature_table`
8. `create_tags_table` + `create_property_tag_table`
9. `create_easybroker_property_listing_statuses_table`
10. `create_locations_catalog_table`
11. `create_contact_requests_table`

---

## 5) Notas prácticas para tu implementación

### 5.1 Mapeo de imágenes/videos de EasyBroker a `media_assets`

- Por cada imagen/video URL de EasyBroker, crea/actualiza un registro en `media_assets`.
- Relaciónalo a la propiedad mediante `property_media_assets`.
- El “orden” (`position`) y “tipo” (`role`) viven en la pivot.

### 5.2 Monedas (EasyBroker → `currencies`)

- Cuando recibas `currency` (`MXN`, `USD`), busca `currencies.code`.
- Si no existe, decide si lo creas (seed ISO-4217 recomendado) o lo dejas NULL y guardas `currency_code`.

### 5.3 Sync eficiente (rate limit 20 req/s)

- Poll de `listing_statuses` → actualiza `easybroker_property_listing_statuses`.
- Solo pide detalle (`properties/{public_id}`) cuando cambie `easybroker_updated_at` o cuando no exista localmente.

