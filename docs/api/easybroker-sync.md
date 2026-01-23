# Servicio de Sincronización EasyBroker

Este documento describe el servicio de sincronización con la API de EasyBroker, que permite importar y mantener sincronizadas las propiedades del inventario.

## Configuración

### Opción 1: Configuración desde el Panel de Administración (Recomendado)

La forma más sencilla de configurar el servicio es desde el panel de administración:

1. Accede a `/admin/easybroker`
2. Completa el formulario de configuración con tu API Key
3. Guarda la configuración

La API Key se almacena de forma segura (encriptada) en la base de datos.

### Opción 2: Variables de entorno (Fallback)

Si no hay configuración en la base de datos, el servicio usará las variables de entorno como fallback:

```env
EASYBROKER_API_KEY=tu_api_key_aqui
EASYBROKER_BASE_URL=https://api.easybroker.com/v1
EASYBROKER_RATE_LIMIT=20
EASYBROKER_TIMEOUT=30
```

### Obtener credenciales

1. Accede al panel de EasyBroker
2. Ve a **Configuración → API**
3. Copia tu API Key

## Endpoints API

### `GET /api/easybroker/status`

Obtiene el estado de configuración del servicio.

**Autenticación:** Bearer Token (Passport)

**Respuesta exitosa:**
```json
{
  "success": true,
  "message": "Estado de configuración de EasyBroker",
  "code": "EASYBROKER_STATUS",
  "data": {
    "configured": true,
    "api_key": "ab1234cd...**89",
    "config_source": "database",
    "base_url": "https://api.easybroker.com/v1",
    "rate_limit": 20,
    "total_properties": 48,
    "published_properties": 35,
    "last_sync": {
      "last_sync_at": "2026-01-23T03:00:00+00:00",
      "created": 5,
      "updated": 12,
      "errors": 0
    }
  }
}
```

### `GET /api/easybroker/config`

Obtiene la configuración actual del servicio.

**Autenticación:** Bearer Token (Passport)

**Respuesta exitosa:**
```json
{
  "success": true,
  "message": "Configuración de EasyBroker",
  "code": "EASYBROKER_CONFIG",
  "data": {
    "id": 1,
    "name": "Principal",
    "has_api_key": true,
    "api_key_masked": "ab12****ef89",
    "base_url": "https://api.easybroker.com/v1",
    "rate_limit": 20,
    "timeout": 30,
    "is_active": true,
    "notes": "Configuración principal",
    "last_sync_at": "2026-01-23T03:00:00+00:00",
    "last_sync_created": 5,
    "last_sync_updated": 12,
    "last_sync_errors": 0
  }
}
```

### `PUT /api/easybroker/config`

Actualiza la configuración del servicio.

**Autenticación:** Bearer Token (Passport)

**Body:**
```json
{
  "name": "Principal",
  "api_key": "tu_nueva_api_key",
  "base_url": "https://api.easybroker.com/v1",
  "rate_limit": 20,
  "timeout": 30,
  "notes": "Notas opcionales"
}
```

**Notas:**
- El campo `api_key` es opcional. Si no se envía, se mantiene la API Key actual.
- La API Key se almacena encriptada en la base de datos.

**Respuesta exitosa:**
```json
{
  "success": true,
  "message": "Configuración actualizada exitosamente",
  "code": "EASYBROKER_CONFIG_UPDATED",
  "data": {
    "id": 1,
    "name": "Principal",
    "has_api_key": true,
    "api_key_masked": "ab12****ef89",
    "base_url": "https://api.easybroker.com/v1",
    "rate_limit": 20,
    "timeout": 30,
    "is_active": true
  }
}
```

### `DELETE /api/easybroker/config/api-key`

Elimina la API Key de la configuración.

**Autenticación:** Bearer Token (Passport)

**Respuesta exitosa:**
```json
{
  "success": true,
  "message": "API Key eliminada exitosamente",
  "code": "EASYBROKER_API_KEY_DELETED"
}
```

### `GET /api/easybroker/test-connection`

Prueba la conexión con la API de EasyBroker.

**Autenticación:** Bearer Token (Passport)

**Respuesta exitosa:**
```json
{
  "success": true,
  "message": "Conexión exitosa con EasyBroker",
  "code": "EASYBROKER_CONNECTION_OK",
  "data": {
    "total_properties": 48,
    "api_version": "v1"
  }
}
```

### `POST /api/easybroker/sync`

Ejecuta la sincronización completa de propiedades.

**Autenticación:** Bearer Token (Passport)

**Respuesta exitosa:**
```json
{
  "success": true,
  "message": "Sincronización completada exitosamente",
  "code": "EASYBROKER_SYNC_SUCCESS",
  "data": {
    "stats": {
      "created": 5,
      "updated": 12,
      "unpublished": 2,
      "errors": 0
    },
    "log_summary": {
      "total_entries": 25,
      "errors": 0,
      "warnings": 1,
      "info": 24,
      "last_entries": [...]
    }
  }
}
```

## Flujo de sincronización

El servicio de sincronización sigue este proceso:

### 1. Obtención de Listing Statuses

Se consulta el endpoint `GET /listing_statuses` de EasyBroker para obtener el estado de publicación de todas las propiedades. Este endpoint es diferente al de propiedades y proporciona:

- `public_id`: Identificador único de la propiedad
- `published`: Estado de publicación (true/false)
- `updated_at`: Fecha de última actualización

### 2. Actualización de tabla de control

Los datos de `listing_statuses` se guardan en la tabla `easybroker_property_listing_statuses` para:

- Tracking de cambios
- Comparación con propiedades locales
- Identificación de propiedades nuevas o actualizadas

### 3. Identificación de propiedades a sincronizar

Una propiedad necesita sincronización si:

- **Es nueva:** No existe en la tabla `properties` con ese `easybroker_public_id`
- **Fue actualizada:** El `updated_at` de EasyBroker es más reciente que `last_synced_at` local
- **Está publicada:** Solo se sincronizan propiedades con `published: true`

### 4. Sincronización de detalles

Para cada propiedad que necesita sincronización:

1. Se obtiene el detalle completo desde `GET /properties/{public_id}`
2. Se actualiza o crea el registro en `properties`
3. Se sincronizan las relaciones:
   - `property_locations` (ubicación)
   - `property_operations` (operaciones de venta/renta)
   - `features` y `property_feature` (características)
   - `tags` y `property_tag` (etiquetas)
   - `media_assets` y `property_media_assets` (imágenes/videos)

### 5. Despublicación de propiedades removidas

Las propiedades que:

- Tienen `easybroker_public_id` (provienen de EasyBroker)
- Están marcadas como `published: true` localmente
- Ya no aparecen en los `listing_statuses` como publicadas

Son automáticamente marcadas como `published: false`.

## Identificación de propiedades de EasyBroker

Las propiedades sincronizadas desde EasyBroker se identifican por el campo `easybroker_public_id`:

- Si es `NOT NULL` → Proviene de EasyBroker
- Si es `NULL` o vacío → Es una propiedad local/manual

Esto asegura que:

- Las propiedades manuales no sean afectadas por la sincronización
- Solo las propiedades de EasyBroker sean despublicadas automáticamente
- Se pueda hacer una comparación masiva eficiente

## Rate Limiting

La API de EasyBroker tiene un límite de 20 requests por segundo. El servicio implementa:

- Pausas entre requests usando `usleep()`
- Configuración del rate limit vía panel de administración o `EASYBROKER_RATE_LIMIT`
- Manejo de errores y reintentos

## Vista de administración

Accede a la vista de sincronización en:

```
/admin/easybroker
```

La vista permite:

1. **Configurar credenciales** - Guarda la API Key de forma segura en la base de datos
2. **Ver estado de configuración** - Verifica que las credenciales estén correctas
3. **Probar conexión** - Valida la conectividad con la API
4. **Ejecutar sincronización** - Inicia el proceso de sincronización
5. **Ver resultados** - Muestra estadísticas y logs de la última sincronización

## Estructura de archivos

```
app/
├── Services/
│   └── EasyBrokerSyncService.php    # Servicio principal
├── Http/Controllers/
│   └── EasyBrokerSyncController.php # Controlador API
├── Models/
│   ├── EasybrokerPropertyListingStatus.php # Modelo de control
│   └── EasyBrokerConfig.php         # Modelo de configuración

resources/views/easybroker/
└── sync.blade.php                   # Vista de administración

routes/
├── api.php                          # Rutas API
└── views.php                        # Ruta de vista

config/
└── services.php                     # Configuración fallback

database/migrations/
├── 2025_12_27_200180_create_easybroker_property_listing_statuses_table.php
└── 2026_01_23_031500_create_easybroker_configs_table.php

docs/api/
└── easybroker-sync.md               # Esta documentación
```

## Tablas relacionadas

### `easybroker_configs`

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | BIGINT | PK autoincrement |
| name | VARCHAR(255) | Nombre de la configuración |
| api_key | TEXT | API Key encriptada |
| base_url | VARCHAR(500) | URL base de la API |
| rate_limit | INT | Límite de requests por segundo |
| timeout | INT | Timeout en segundos |
| is_active | BOOLEAN | Si es la configuración activa |
| notes | TEXT | Notas opcionales |
| last_sync_at | DATETIME | Última sincronización |
| last_sync_created | INT | Propiedades creadas en última sync |
| last_sync_updated | INT | Propiedades actualizadas en última sync |
| last_sync_errors | INT | Errores en última sync |

### `easybroker_property_listing_statuses`

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | BIGINT | PK autoincrement |
| agency_id | BIGINT | FK a agencies |
| easybroker_public_id | VARCHAR(50) | ID público en EasyBroker |
| property_id | BIGINT NULL | FK a properties (cuando está vinculada) |
| published | BOOLEAN | Estado de publicación |
| easybroker_updated_at | DATETIME | Fecha de actualización en EB |
| last_polled_at | DATETIME | Última vez que se consultó |
| raw_payload | JSON | Datos originales de la API |

### `properties` (campos de EasyBroker)

| Campo | Tipo | Descripción |
|-------|------|-------------|
| easybroker_public_id | VARCHAR(50) | ID público (ej: EB-B0579) |
| easybroker_agent_id | VARCHAR(50) | ID del agente en EB |
| easybroker_created_at | DATETIME | Fecha de creación en EB |
| easybroker_updated_at | DATETIME | Fecha de actualización en EB |
| last_synced_at | DATETIME | Última sincronización local |
| raw_payload | JSON | Datos originales de la API |

## Ejemplos de uso

### Sincronización desde código

```php
use App\Services\EasyBrokerSyncService;

$syncService = app(EasyBrokerSyncService::class);

// Verificar configuración
if ($syncService->isConfigured()) {
    // Ejecutar sincronización
    $result = $syncService->sync();
    
    if ($result['success']) {
        // Estadísticas
        $stats = $result['stats'];
        echo "Creadas: {$stats['created']}";
        echo "Actualizadas: {$stats['updated']}";
    }
}
```

### Sincronización programada (Job/Command)

Puedes crear un comando Artisan para ejecutar la sincronización periódicamente:

```php
// app/Console/Commands/SyncEasyBrokerCommand.php
namespace App\Console\Commands;

use App\Services\EasyBrokerSyncService;
use Illuminate\Console\Command;

class SyncEasyBrokerCommand extends Command
{
    protected $signature = 'easybroker:sync';
    protected $description = 'Sincroniza propiedades desde EasyBroker';

    public function handle(EasyBrokerSyncService $service)
    {
        if (!$service->isConfigured()) {
            $this->error('EasyBroker no está configurado');
            return 1;
        }

        $this->info('Iniciando sincronización...');
        $result = $service->sync();

        if ($result['success']) {
            $stats = $result['stats'];
            $this->info("✓ Sincronización completada");
            $this->table(
                ['Métrica', 'Valor'],
                [
                    ['Creadas', $stats['created']],
                    ['Actualizadas', $stats['updated']],
                    ['Despublicadas', $stats['unpublished']],
                    ['Errores', $stats['errors']],
                ]
            );
            return 0;
        }

        $this->error($result['message']);
        return 1;
    }
}
```

Luego programa el comando en `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('easybroker:sync')
        ->hourly()
        ->withoutOverlapping();
}
```

## Seguridad

### Encriptación de API Key

La API Key se almacena encriptada en la base de datos usando el sistema de encriptación de Laravel (`Illuminate\Support\Facades\Crypt`). Esto asegura que:

- La API Key no sea visible en texto plano en la base de datos
- Solo la aplicación con la clave de encriptación correcta pueda descifrarla
- Se cumplan las mejores prácticas de seguridad para credenciales

### Fallback a .env

Si no hay configuración en la base de datos, el servicio usará las variables de entorno como fallback. Esto permite:

- Configuración inicial sin acceso al panel de administración
- Compatibilidad con despliegues que usan variables de entorno
- Flexibilidad en diferentes ambientes (desarrollo, staging, producción)

## Estructura de datos de EasyBroker

### Features (Características)

La API de EasyBroker devuelve las características en el campo `features` con la siguiente estructura:

```json
{
  "features": [
    { "name": "Piscina", "category": "Recreación" },
    { "name": "Jardín", "category": "Exterior" },
    { "name": "Cocina integral", "category": "General" },
    { "name": "Mascotas permitidas", "category": "Políticas" }
  ]
}
```

**Categorías típicas en EasyBroker:**
- `Exterior` - Jardín, Terraza, Patio, Balcón
- `General` - Cocina integral, Aire acondicionado, Seguridad 24 horas
- `Recreación` - Alberca, Gimnasio, Área de juegos
- `Políticas` - Mascotas permitidas, Permitido fumar

La tabla `features` almacena esta información:

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | BIGINT | PK autoincrement |
| name | VARCHAR(255) | Nombre de la característica |
| category | VARCHAR(100) | Categoría de EasyBroker (nullable) |
| locale | VARCHAR(10) | Idioma (nullable) |

### Tags (Etiquetas)

La API de EasyBroker devuelve los tags como un array de strings:

```json
{
  "tags": ["exclusivo", "premium", "oportunidad"]
}
```

**Nota:** Si no hay tags asignados en EasyBroker, el array estará vacío (`[]`).

## Comandos de mantenimiento

### Re-sincronizar Features

Si necesitas re-sincronizar los features de propiedades existentes (por ejemplo, después de una actualización del código):

```bash
# Ver qué se haría sin hacer cambios
php artisan easybroker:resync-features --dry-run

# Re-sincronizar todas las propiedades
php artisan easybroker:resync-features --force-all

# Re-sincronizar solo propiedades sin features con categoría
php artisan easybroker:resync-features
```

Este comando:
1. Lee el `raw_payload` almacenado de cada propiedad
2. Extrae los features con sus categorías
3. Crea features faltantes en la tabla `features`
4. Actualiza las relaciones en `property_feature`

## Notas importantes

1. **Primera sincronización:** Puede tomar varios minutos si hay muchas propiedades
2. **Rate limiting:** Respeta los límites de la API para evitar bloqueos
3. **Monedas:** Las operaciones intentan vincular con `currencies` por código
4. **Imágenes:** Se crean MediaAssets con `provider: 'easybroker'`
5. **Idempotencia:** La sincronización es segura de ejecutar múltiples veces
6. **Configuración segura:** La API Key se almacena encriptada en la base de datos
7. **Prioridad de configuración:** Base de datos > Variables de entorno
8. **Features con categorías:** Las características incluyen la categoría de EasyBroker
9. **Tags vacíos:** Si las propiedades no tienen tags en EasyBroker, no se crearán localmente
