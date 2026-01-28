# MLS AMPI San Miguel de Allende - Sincronización API

## Descripción General

Este módulo permite sincronizar propiedades desde el **MLS AMPI San Miguel de Allende** hacia la base de datos local del sistema. La sincronización es similar a la de EasyBroker pero adaptada a la estructura de la API del MLS.

## Documentación de la API del MLS

La API del MLS está documentada en: `https://ampisanmigueldeallende.com/api/documentation`

### Endpoints Disponibles del MLS

#### Properties (Propiedades Públicas)
- `GET /api/v1/property/{id}/features` - Obtener características por ID de propiedad
- `GET /api/v1/property/{id}/agents` - Obtener agentes por ID de propiedad
- `GET /api/v1/property/{id}/photos` - Obtener fotos por ID de propiedad
- `GET /api/v1/properties/search` - Búsqueda avanzada de propiedades
- `GET /api/v1/property/mls/{mls_id}` - Buscar propiedad por MLS ID

#### Agents (Agentes)
- `GET /api/v1/agents` - Obtener todos los agentes
- `GET /api/v1/agent/{id}` - Obtener agente por ID
- `GET /api/v1/agent/{id}/properties` - Obtener propiedades por ID de agente

#### Offices (Oficinas)
- `GET /api/v1/offices` - Obtener todas las oficinas
- `GET /api/v1/offices/{id}` - Obtener oficina por ID
- `GET /api/v1/offices/{id}/properties` - Obtener propiedades por ID de oficina
- `GET /api/v1/offices/{id}/agents` - Obtener agentes por ID de oficina

#### Features (Características)
- `GET /api/v1/features` - Obtener todas las características de propiedades

#### Neighborhoods (Vecindarios)
- `GET /api/v1/neighborhoods` - Obtener todos los vecindarios

#### User (Usuario)
- `GET /api/v1/user` - Obtener información del usuario autenticado

## Endpoints de Sincronización (API Local)

### Estado del Servicio

```http
GET /api/mls/status
Authorization: Bearer {token}
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Estado de configuración del MLS",
  "code": "MLS_STATUS",
  "data": {
    "configured": true,
    "config_source": "database",
    "api_key": "abc1****xyz9",
    "base_url": "https://ampisanmigueldeallende.com/api/v1",
    "rate_limit": 10,
    "timeout": 30,
    "batch_size": 50,
    "sync_mode": "incremental",
    "last_sync": {
      "last_sync_at": "2026-01-27T18:00:00.000000Z",
      "created": 15,
      "updated": 5,
      "unpublished": 2,
      "errors": 0,
      "total_fetched": 150
    },
    "total_properties": 150,
    "published_properties": 148
  }
}
```

### Configuración

#### Obtener Configuración

```http
GET /api/mls/config
Authorization: Bearer {token}
```

#### Actualizar Configuración

```http
PUT /api/mls/config
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Principal",
  "api_key": "tu_api_key_del_mls",
  "base_url": "https://ampisanmigueldeallende.com/api/v1",
  "rate_limit": 10,
  "timeout": 30,
  "batch_size": 50,
  "sync_mode": "incremental",
  "is_active": true,
  "notes": "Configuración principal del MLS"
}
```

**Parámetros:**
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `name` | string | Nombre de la configuración |
| `api_key` | string | API Key del MLS (se encripta) |
| `base_url` | string | URL base de la API |
| `rate_limit` | integer | Requests por segundo (1-100) |
| `timeout` | integer | Timeout en segundos (5-120) |
| `batch_size` | integer | Propiedades por lote (10-200) |
| `sync_mode` | string | Modo: `full` o `incremental` |
| `is_active` | boolean | Si la configuración está activa |
| `notes` | string | Notas adicionales |

#### Eliminar API Key

```http
DELETE /api/mls/config/api-key
Authorization: Bearer {token}
```

### Probar Conexión

```http
GET /api/mls/test-connection
Authorization: Bearer {token}
```

**Respuesta exitosa:**
```json
{
  "success": true,
  "message": "Conexión exitosa con el MLS",
  "code": "MLS_CONNECTION_OK",
  "data": {
    "features_count": 45,
    "api_version": "v1",
    "base_url": "https://ampisanmigueldeallende.com/api/v1"
  }
}
```

### Ejecutar Sincronización

```http
POST /api/mls/sync
Authorization: Bearer {token}
Content-Type: application/json

{
  "mode": "incremental",
  "limit": 100,
  "offset": 1
}
```

**Parámetros opcionales:**
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `mode` | string | `full` (todo) o `incremental` (solo cambios) |
| `limit` | integer | Máximo de propiedades a sincronizar (0 = sin límite) |
| `offset` | integer | Página inicial para retomar |

**Respuesta:**
```json
{
  "success": true,
  "message": "Sincronización completada exitosamente",
  "code": "MLS_SYNC_SUCCESS",
  "data": {
    "stats": {
      "created": 10,
      "updated": 25,
      "unpublished": 2,
      "errors": 0,
      "total_fetched": 150
    },
    "log_summary": {
      "total_entries": 45,
      "errors": 0,
      "warnings": 1,
      "info": 44,
      "last_entries": [...]
    }
  }
}
```

### Catálogos del MLS

#### Obtener Características

```http
GET /api/mls/features
Authorization: Bearer {token}
```

#### Obtener Vecindarios

```http
GET /api/mls/neighborhoods
Authorization: Bearer {token}
```

#### Obtener Agentes

```http
GET /api/mls/agents
Authorization: Bearer {token}
```

#### Obtener Valores Permitidos

```http
GET /api/mls/allowed-values
Authorization: Bearer {token}
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Valores permitidos del MLS",
  "code": "MLS_ALLOWED_VALUES",
  "data": {
    "statuses": ["For Sale", "For Rent", "Price Reduction", "Contract Pending", "Under Contract"],
    "categories": ["Residential", "Land and Lots", "Commercial", "Pre Sales"],
    "currencies": ["MXN", "USD", "CAD", "EUR"],
    "furnished": ["Any", "yes", "no", "partially"]
  }
}
```

#### Obtener Propiedad Específica

```http
GET /api/mls/property/{mlsId}
Authorization: Bearer {token}
```

## Mapeo de Campos

### Campos del MLS → Campos Locales

| Campo MLS | Campo Local | Descripción |
|-----------|-------------|-------------|
| `id` | `mls_id` | ID interno del MLS |
| `mls_id` | `mls_public_id` | MLS ID público |
| `folder_name` | `mls_folder_name` | Nombre de carpeta |
| `neighborhood` | `mls_neighborhood` | Vecindario |
| `office_id` | `mls_office_id` | ID de oficina |
| `name` | `title` | Título de la propiedad |
| `price` | `expenses` | Precio actual |
| `old_price` | `old_price` | Precio anterior |
| `currency` | (operaciones) | Moneda |
| `status` | `status` | Estado (For Sale, etc.) |
| `category` | `category` | Categoría |
| `lot_meters` | `lot_size` | Tamaño del lote (m²) |
| `construction_meters` | `construction_size` | Construcción (m²) |
| `bedrooms` | `bedrooms` | Recámaras |
| `bathrooms` | `bathrooms` | Baños |
| `half_bathrooms` | `half_bathrooms` | Medios baños |
| `floors` | `floors` | Pisos |
| `parking_number` | `parking_number` | Estacionamientos |
| `furnished` | `furnished` | Amueblado |
| `with_yard` | `with_yard` | Con jardín |
| `with_view` | `with_view` | Vista |
| `gated_comm` | `gated_comm` | Comunidad cerrada |
| `pool` | `pool` | Alberca |
| `casita` | `casita` | Casita |
| `casita_bedrooms` | `casita_bedrooms` | Recámaras casita |
| `casita_bathrooms` | `casita_bathrooms` | Baños casita |
| `is_approved` | `is_approved` | Aprobado |
| `allow_integration` | `allow_integration` | Permite integración |
| `created_at` | `mls_created_at` | Fecha creación MLS |
| `updated_at` | `mls_updated_at` | Fecha actualización MLS |

## Campo `source` (Origen de Propiedad)

Se agregó un nuevo campo `source` a la tabla `properties` para identificar el origen de cada propiedad:

| Valor | Descripción |
|-------|-------------|
| `manual` | Propiedad creada manualmente en el sistema |
| `easybroker` | Propiedad sincronizada desde EasyBroker |
| `mls` | Propiedad sincronizada desde el MLS |

### Filtros por Origen

```php
// Obtener solo propiedades del MLS
Property::fromMLS()->get();

// Obtener solo propiedades de EasyBroker
Property::fromEasyBroker()->get();

// Obtener solo propiedades manuales
Property::manual()->get();

// Filtrar por origen específico
Property::fromSource('mls')->where('published', true)->get();
```

## Configuración de Entorno (.env)

Variables de entorno opcionales (fallback si no hay configuración en BD):

```env
MLS_API_KEY=tu_api_key_aqui
MLS_BASE_URL=https://ampisanmigueldeallende.com/api/v1
MLS_RATE_LIMIT=10
MLS_TIMEOUT=30
MLS_BATCH_SIZE=50
```

## Migraciones

### Campos agregados a `properties`

```
php artisan migrate
```

Esto ejecutará:
1. `2026_01_27_230000_add_mls_fields_to_properties_table.php` - Agrega campos del MLS
2. `2026_01_27_230100_create_mls_configs_table.php` - Crea tabla de configuración

## Diferencias con EasyBroker

| Aspecto | EasyBroker | MLS |
|---------|------------|-----|
| Autenticación | Header `X-Authorization` | Bearer Token |
| Endpoint principal | `/listing_statuses` | `/properties/search` |
| Identificador | `public_id` | `mls_id` |
| Paginación | `pagination.next_page` | `pagination.total_pages` |
| Fotos | `images[].url` | `photos[].url` o array de URLs |

## Ejemplo de Uso

### Configurar y Sincronizar

```bash
# 1. Configurar la API Key
curl -X PUT "http://tu-dominio.com/api/mls/config" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"api_key": "tu_api_key"}'

# 2. Probar conexión
curl -X GET "http://tu-dominio.com/api/mls/test-connection" \
  -H "Authorization: Bearer {token}"

# 3. Ejecutar sincronización
curl -X POST "http://tu-dominio.com/api/mls/sync" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"mode": "incremental"}'
```

## Manejo de Errores

### Códigos de Error

| Código | Descripción |
|--------|-------------|
| `MLS_NOT_CONFIGURED` | API Key no configurada |
| `MLS_CONNECTION_FAILED` | Error de conexión |
| `MLS_SYNC_FAILED` | Error durante sincronización |
| `MLS_PROPERTY_NOT_FOUND` | Propiedad no encontrada |
| `MLS_FEATURES_ERROR` | Error al obtener características |
| `MLS_NEIGHBORHOODS_ERROR` | Error al obtener vecindarios |
| `MLS_AGENTS_ERROR` | Error al obtener agentes |

## Notas Importantes

1. **Rate Limiting**: El MLS tiene límites de requests por segundo. El servicio implementa automáticamente pausas entre requests.

2. **Propiedades Grandes**: Para sincronizaciones con muchas propiedades, considerar usar modo `incremental` y/o establecer un `limit`.

3. **Imágenes**: Las URLs de imágenes se almacenan en `media_assets` con `provider = 'mls'`.

4. **Conflictos**: Si una propiedad existe tanto en EasyBroker como en MLS, cada una tendrá su propio registro con diferente `source`.

5. **Despublicación**: Solo en modo `full`, las propiedades que ya no estén en el MLS se despublicarán automáticamente.
