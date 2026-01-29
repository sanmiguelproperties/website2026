# Mejoras en el Proceso de Sincronización MLS

## Resumen

Se han implementado mejoras significativas en el proceso de sincronización de propiedades MLS para hacer el sistema más robusto y resistente a errores. Estas mejoras permiten que la sincronización continúe incluso cuando ocurren errores, en lugar de detenerse completamente.

## Problemas Identificados

Antes de las mejoras, el sistema tenía las siguientes vulnerabilidades:

1. **Falta de checkpoint/savepoint**: Si la sincronización fallaba, no había forma de retomar desde donde se quedó
2. **Manejo de errores insuficiente**: Los errores no se capturaban con suficiente detalle para debugging
3. **Sin validación de datos de entrada**: Los datos del API no se validaban antes de procesarlos
4. **Sin circuit breaker**: Si la API fallaba repetidamente, el sistema seguía intentando sin límite
5. **Sin manejo de concurrencia**: Múltiples sincronizaciones podían causar conflictos
6. **Sin notificación de errores críticos**: No había alertas cuando ocurrían errores graves
7. **Timeouts genéricos**: El mismo timeout para todas las operaciones
8. **Sin validación de estructura de respuesta**: Cambios en la API podían causar errores

## Mejoras Implementadas

### 1. Sistema de Checkpoint/Savepoint

**Descripción**: Guarda el progreso de la sincronización en el cache para permitir retomar desde donde se quedó.

**Implementación**:
- `saveCheckpoint(string $mlsId)`: Guarda el último MLS ID procesado exitosamente
- `getLastCheckpoint()`: Obtiene el último checkpoint guardado
- `clearCheckpoint()`: Limpia el checkpoint cuando la sincronización se completa exitosamente

**Uso**:
```php
// Retomar sincronización desde checkpoint
$options = [
    'resume_from_checkpoint' => true,
];
$result = $syncService->sync($options);
```

**Endpoint API**: `POST /api/mls/sync/resume`

### 2. Manejo de Errores Granular

**Descripción**: Captura detalles específicos de cada error para análisis posterior.

**Implementación**:
- `recordError(string $mlsId, string $errorType, string $message, ?\Throwable $exception = null)`: Registra errores detallados
- `getErrorDetails()`: Obtiene todos los errores registrados
- `getFailedProperties()`: Obtiene la lista de propiedades que fallaron

**Tipos de errores**:
- `api`: Errores de conexión a la API
- `database`: Errores de base de datos
- `validation`: Errores de validación de datos
- `sync`: Errores durante la sincronización
- `critical`: Errores críticos que detienen el proceso

**Endpoint API**: `GET /api/mls/error-details`

### 3. Validación de Datos de Entrada

**Descripción**: Valida la estructura de datos del API antes de procesarlos.

**Implementación**:
- `validatePropertyData(array $propertyData)`: Valida campos requeridos y tipos de datos
- `validateApiResponse(?array $response)`: Valida estructura de respuesta del API

**Validaciones**:
- Campos requeridos: `mls_id`, `id`
- Tipos de datos: `price`, `bedrooms`, `bathrooms` deben ser numéricos
- Estructura de respuesta: Debe ser un array y no tener `success: false`

### 4. Circuit Breaker

**Descripción**: Implementa el patrón Circuit Breaker para evitar llamadas repetidas a una API fallida.

**Implementación**:
- `isCircuitBreakerOpen()`: Verifica si el circuit breaker está abierto
- `recordCircuitBreakerFailure()`: Registra un fallo y abre el circuit breaker si se alcanza el umbral
- `recordCircuitBreakerSuccess()`: Registra un éxito y cierra el circuit breaker
- `resetCircuitBreaker()`: Reinicia el circuit breaker manualmente

**Configuración**:
- `circuitBreakerThreshold`: 5 fallos consecutivos para abrir el circuit breaker
- `circuitBreakerTimeoutSeconds`: 300 segundos (5 minutos) antes de intentar recuperar

**Endpoint API**: 
- `GET /api/mls/circuit-breaker`: Obtener estado
- `POST /api/mls/circuit-breaker/reset`: Reiniciar manualmente

### 5. Manejo de Concurrencia

**Descripción**: Usa cache locks para evitar sincronizaciones simultáneas.

**Implementación**:
- `acquireSyncLock()`: Intenta adquirir un lock exclusivo
- `releaseSyncLock()`: Libera el lock cuando termina la sincronización

**Comportamiento**:
- Si ya hay una sincronización en curso, retorna un error indicando que debe intentarse más tarde
- El lock tiene una duración máxima de 3600 segundos (1 hora)
- El lock se libera automáticamente en el bloque `finally`

### 6. Timeouts Específicos

**Descripción**: Permite timeouts personalizados para diferentes tipos de operaciones.

**Implementación**:
- `makeRequest()` ahora acepta un parámetro opcional `$customTimeout`
- Timeout por defecto: 30 segundos
- Timeout personalizado para operaciones específicas

**Ejemplo**:
```php
// Timeout personalizado para descarga de imágenes
$response = $this->makeRequest('GET', '/property/mls/' . $mlsId, [], 60);
```

### 7. Manejo de Memoria

**Descripción**: Libera memoria periódicamente durante la sincronización de muchas propiedades.

**Implementación**:
- Llama a `gc_collect_cycles()` cada 50 propiedades procesadas
- Evita agotamiento de memoria en sincronizaciones largas

### 8. Logging Detallado

**Descripción**: Implementa logging estructurado con diferentes niveles de detalle.

**Niveles de logging**:
- `debug`: Información detallada para debugging
- `info`: Información general del proceso
- `warning`: Advertencias que no detienen el proceso
- `error`: Errores que se manejan pero se registran

**Información registrada**:
- Timestamp de cada evento
- Nivel de severidad
- Mensaje descriptivo
- Contexto adicional (MLS ID, tipo de error, etc.)

### 9. Validación de Estructura de Respuesta

**Descripción**: Valida que la respuesta del API tenga la estructura esperada.

**Implementación**:
- `validateApiResponse(?array $response)`: Verifica que la respuesta sea válida
- Maneja cambios en la estructura de respuesta del API
- Previene errores por datos inesperados

### 10. Manejo de Excepciones Específicas

**Descripción**: Captura diferentes tipos de excepciones con manejo específico.

**Tipos de excepciones**:
- `ConnectionException`: Errores de conexión a la API
- `TimeoutException`: Timeouts de las peticiones HTTP
- `Throwable`: Cualquier otra excepción

**Comportamiento**:
- Cada tipo de excepción tiene su propio logging
- Reintentos automáticos con backoff exponencial
- Información detallada del error para debugging

## Nuevos Endpoints API

### GET /api/mls/error-details
Obtiene los detalles de errores de la última sincronización.

**Respuesta**:
```json
{
  "success": true,
  "message": "Detalles de errores de sincronización",
  "code": "MLS_ERROR_DETAILS",
  "data": {
    "error_details": [
      {
        "mls_id": "12345",
        "error_type": "api",
        "message": "Error de conexión",
        "timestamp": "2026-01-28T20:00:00.000000Z",
        "exception": {
          "class": "Illuminate\\Http\\Client\\ConnectionException",
          "file": "/path/to/file.php",
          "line": 123,
          "trace": "..."
        }
      }
    ],
    "failed_properties": ["12345", "67890"],
    "total_errors": 2,
    "total_failed_properties": 2
  }
}
```

### GET /api/mls/circuit-breaker
Obtiene el estado del circuit breaker.

**Respuesta**:
```json
{
  "success": true,
  "message": "Estado del circuit breaker",
  "code": "MLS_CIRCUIT_BREAKER_STATUS",
  "data": {
    "open": false,
    "failures": 0,
    "threshold": 5,
    "opened_at": null,
    "timeout_seconds": 300,
    "will_close_at": null
  }
}
```

### POST /api/mls/circuit-breaker/reset
Reinicia el circuit breaker manualmente.

**Respuesta**:
```json
{
  "success": true,
  "message": "Circuit breaker reiniciado",
  "code": "MLS_CIRCUIT_BREAKER_RESET",
  "data": {
    "message": "El circuit breaker ha sido reiniciado. Las solicitudes a la API se reanudarán."
  }
}
```

### GET /api/mls/checkpoint
Obtiene el último checkpoint de sincronización.

**Respuesta**:
```json
{
  "success": true,
  "message": "Checkpoint de sincronización",
  "code": "MLS_CHECKPOINT",
  "data": {
    "checkpoint": {
      "last_mls_id": "12345",
      "timestamp": "2026-01-28T20:00:00.000000Z"
    },
    "message": "Hay un checkpoint disponible. Puedes retomar la sincronización desde este punto."
  }
}
```

### DELETE /api/mls/checkpoint
Limpia el checkpoint de sincronización.

**Respuesta**:
```json
{
  "success": true,
  "message": "Checkpoint limpiado",
  "code": "MLS_CHECKPOINT_CLEARED",
  "data": {
    "message": "El checkpoint ha sido limpiado. La próxima sincronización comenzará desde el principio."
  }
}
```

### POST /api/mls/sync/resume
Retoma la sincronización desde el último checkpoint.

**Parámetros**:
- `mode` (opcional): 'full' o 'incremental'
- `limit` (opcional): Número máximo de propiedades a sincronizar

**Respuesta**:
```json
{
  "success": true,
  "message": "Sincronización completada exitosamente",
  "code": "MLS_SYNC_RESUME_SUCCESS",
  "data": {
    "stats": {
      "created": 10,
      "updated": 20,
      "unpublished": 5,
      "errors": 2,
      "total_fetched": 30,
      "failed_properties_count": 2,
      "error_details_count": 2,
      "last_successful_mls_id": "67890",
      "circuit_breaker_open": false,
      "circuit_breaker_failures": 0
    },
    "checkpoint_used": {
      "last_mls_id": "12345",
      "timestamp": "2026-01-28T20:00:00.000000Z"
    },
    "log_summary": {...},
    "error_details": [...],
    "failed_properties": [...],
    "last_successful_mls_id": "67890"
  }
}
```

## Actualizaciones en Endpoints Existentes

### POST /api/mls/sync
**Nuevos parámetros**:
- `resume_from_checkpoint` (opcional, boolean): Si es true, retoma desde el último checkpoint

**Nuevos campos en respuesta**:
- `error_details`: Array con detalles de errores
- `failed_properties`: Array con MLS IDs de propiedades que fallaron
- `last_successful_mls_id`: Último MLS ID procesado exitosamente
- `circuit_breaker_open`: Indica si el circuit breaker está abierto
- `sync_locked`: Indica si hay una sincronización en curso

### GET /api/mls/status
**Nuevos campos en respuesta**:
- `circuit_breaker`: Estado del circuit breaker
- `checkpoint`: Último checkpoint guardado
- `sync_locked`: Indica si hay una sincronización en curso

## Estrategias de Recuperación

### 1. Retomar desde Checkpoint
Si una sincronización falla, puedes retomarla desde el último punto exitoso:

```bash
curl -X POST http://localhost:8000/api/mls/sync/resume \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"resume_from_checkpoint": true}'
```

### 2. Verificar Errores
Obtén detalles de los errores que ocurrieron:

```bash
curl -X GET http://localhost:8000/api/mls/error-details \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 3. Reiniciar Circuit Breaker
Si el circuit breaker está abierto, puedes reiniciarlo manualmente:

```bash
curl -X POST http://localhost:8000/api/mls/circuit-breaker/reset \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 4. Limpiar Checkpoint
Si quieres comenzar una sincronización desde el principio:

```bash
curl -X DELETE http://localhost:8000/api/mls/checkpoint \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## Configuración

### Circuit Breaker
Puedes ajustar la configuración del circuit breaker en el servicio:

```php
// En MLSSyncService.php
protected int $circuitBreakerThreshold = 5; // Fallos consecutivos para abrir
protected int $circuitBreakerTimeoutSeconds = 300; // Segundos antes de recuperar
```

### Timeouts
Ajusta los timeouts según tus necesidades:

```php
// En MLSSyncService.php
protected int $timeout = 30; // Timeout por defecto en segundos
```

### Reintentos
Configura el número máximo de reintentos y los delays:

```php
// En MLSSyncService.php
protected int $maxRetries = 3;
protected array $retryDelays = [1, 3, 5]; // Segundos entre reintentos
```

## Monitoreo

### Logs
Todos los eventos de sincronización se registran en:
- `storage/logs/laravel.log` con el prefijo `[MLSSync]`

### Métricas
El endpoint `/api/mls/status` proporciona métricas en tiempo real:
- Estado del circuit breaker
- Último checkpoint
- Propiedades totales y publicadas
- Estadísticas de la última sincronización

## Mejores Prácticas

1. **Usa checkpoints para sincronizaciones largas**: Habilita `resume_from_checkpoint` para sincronizaciones que pueden fallar
2. **Monitorea el circuit breaker**: Verifica `/api/mls/circuit-breaker` regularmente
3. **Revisa los errores después de cada sincronización**: Usa `/api/mls/error-details` para identificar problemas
4. **Ajusta los timeouts según tu infraestructura**: Aumenta el timeout si tienes conexiones lentas
5. **Usa modo incremental para sincronizaciones frecuentes**: El modo `incremental` es más eficiente que `full`
6. **Limpia checkpoints periódicamente**: Usa `DELETE /api/mls/checkpoint` para comenzar desde cero

## Troubleshooting

### El circuit breaker está abierto
**Síntoma**: La sincronización falla con el mensaje "Circuit breaker abierto"

**Solución**:
1. Espera 5 minutos para que se cierre automáticamente
2. O reinícialo manualmente con `POST /api/mls/circuit-breaker/reset`
3. Verifica que la API del MLS esté funcionando correctamente

### Hay una sincronización en curso
**Síntoma**: La sincronización falla con el mensaje "Ya existe una sincronización en curso"

**Solución**:
1. Espera a que la sincronización actual termine
2. O verifica si hay un proceso zombie y reinicia el servidor

### Muchos errores de validación
**Síntoma**: Muchas propiedades fallan con errores de validación

**Solución**:
1. Revisa `/api/mls/error-details` para ver los errores específicos
2. Verifica que la API del MLS no haya cambiado su estructura
3. Ajusta las validaciones en `validatePropertyData()` si es necesario

### Timeout en peticiones
**Síntoma**: Las peticiones a la API fallan por timeout

**Solución**:
1. Aumenta el timeout en la configuración
2. Verifica tu conexión a internet
3. Verifica que la API del MLS esté respondiendo correctamente

## Conclusión

Estas mejoras hacen el proceso de sincronización MLS mucho más robusto y resistente a errores. El sistema ahora puede:

- Continuar sincronizando incluso cuando algunas propiedades fallan
- Retomar desde donde se quedó si ocurre un error
- Evitar llamadas repetidas a una API fallida
- Proporcionar información detallada para debugging
- Manejar múltiples sincronizaciones sin conflictos

Esto reduce significativamente el tiempo de inactividad y mejora la confiabilidad del sistema.

---

# Sincronización Progresiva para Servidores con Límites de Tiempo

## Problema Original

En servidores de producción con límites de tiempo de ejecución (max_execution_time) más estrictos que en desarrollo local, la sincronización MLS fallaba porque:

1. **Timeouts de PHP**: Los servidores web tienen límites de tiempo que pueden ser más bajos (30-60 segundos vs 300+ en local)
2. **Conexiones perdidas**: La API se cerraba durante sincronizaciones largas
3. **Locks bloqueados**: Si el proceso moría por timeout, el lock quedaba activo y阻止 nuevas sincronizaciones
4. **Memoria agotada**: Los servidores tienen menos memoria disponible

## Solución: Sincronización Progresiva por Lotes

Se implementó un nuevo método de sincronización que divide el trabajo en múltiples requests HTTP cortos, cada uno procesando un lote pequeño de propiedades.

### Nuevos Parámetros de Configuración

```php
// En MLSSyncService.php
protected int $progressiveBatchSize = 20;          // Lote pequeño para servidores limitados
protected int $maxExecutionTimeWarning = 45;       // Warning a los 45 segundos
protected int $lockTtlSeconds = 7200;              // 2 horas TTL para sync completo
protected int $progressiveLockTtlSeconds = 300;    // 5 minutos TTL para sync progresivo
```

### Nuevos Endpoints API

#### POST /api/mls/sync/progressive

Sincroniza un lote de propiedades y retorna el offset para continuar.

**Parámetros**:
- `batch_size` (opcional): Tamaño del lote (default: 20, rango: 5-50)
- `skip_media` (opcional): Si true, no sincroniza medios (default: true)
- `offset` (opcional): Offset inicial (si es null, usa checkpoint o comienza desde 0)
- `mode` (opcional): 'full' o 'incremental'

**Ejemplo de uso**:
```bash
# Primera llamada - comienza desde el principio
curl -X POST http://localhost:8000/api/mls/sync/progressive \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"batch_size": 20, "skip_media": true}'

# Respuesta
{
  "success": true,
  "message": "Lote procesado. Continúa con offset 20",
  "data": {
    "total_in_mls": 150,
    "processed": 20,
    "created": 5,
    "updated": 15,
    "errors": 0,
    "next_offset": 20,
    "completed": false,
    "progress_percentage": 13.33,
    "execution_time_seconds": 12.5
  }
}

# Segunda llamada - continúa desde offset 20
curl -X POST http://localhost:8000/api/mls/sync/progressive \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"batch_size": 20, "offset": 20}'

# ... continuar hasta que completed sea true
```

#### GET /api/mls/sync/properties/progress

Obtiene el progreso actual de la sincronización de propiedades.

**Respuesta**:
```json
{
  "success": true,
  "message": "Progreso de sincronización de propiedades",
  "data": {
    "has_checkpoint": true,
    "checkpoint": {
      "offset": 40,
      "mode": "incremental",
      "skip_media": true,
      "last_mls_id": "67890",
      "timestamp": "2026-01-29T10:00:00.000000Z"
    },
    "local_properties_count": 100,
    "lock_active": false,
    "lock_stale": false
  }
}
```

#### POST /api/mls/sync/unlock

Fuerza la liberación del lock si está obsoleto. Útil cuando una sincronización anterior murió y dejó el lock activo.

**Respuesta exitosa**:
```json
{
  "success": true,
  "message": "Lock obsoleto liberado exitosamente",
  "data": {
    "was_stale": true
  }
}
```

**Respuesta cuando no está obsoleto**:
```json
{
  "success": false,
  "message": "El lock no está obsoleto, no se puede forzar liberación",
  "data": {
    "was_stale": false
  }
}
```

### Mejoras en el Sistema de Locks

#### Lock con Timestamp

Ahora el lock guarda un timestamp para detectar locks obsoletos:

```php
// Guardar timestamp del lock cuando se adquiere
\Illuminate\Support\Facades\Cache::put('mls_sync_locked_at', now()->toIso8601String(), $ttl);
```

#### Detección de Locks Obsoletos

Ahora el sistema detecta locks obsoletos de manera más inteligente:

```php
public function isLockStale(): bool
{
    // El lock se considera obsoleto si:
    // 1. Ha estado activo por más de 30 minutos (tiempo máximo de sincronización)
    // 2. Ha estado activo por más de 5 minutos en sync progresivo (un lote debería tomar menos de 1 minuto)
    // 3. El proceso actual no puede adquirir el lock (indicando que podría estar muerto)
    
    $minutesAgo = $lockedAtCarbon->diffInMinutes(now());
    $secondsAgo = $lockedAtCarbon->diffInSeconds(now());
    
    if ($minutesAgo > 30) {
        return true; // Lock muy antiguo
    }
    
    if ($secondsAgo > 300) {
        return true; // Lock de más de 5 minutos - probablemente proceso muerto
    }
    
    return false;
}
```

El sistema ahora guarda múltiples timestamps para mejor detección:

```php
// Guardar cuando se adquiere el lock
\Illuminate\Support\Facades\Cache::put('mls_sync_locked_at', $now, $ttl);
\Illuminate\Support\Facades\Cache::put('mls_sync_lock_age', $now, $ttl);
\Illuminate\Support\Facades\Cache::put('mls_sync_lock_ttl', $ttl, $ttl);
```

#### Liberación Forzada

```php
public function forceReleaseLock(): bool
{
    \Illuminate\Support\Facades\Cache::lock($this->syncLockKey, 1, 'default')->forceRelease();
    \Illuminate\Support\Facades\Cache::forget('mls_sync_locked_at');
    return true;
}
```

### Configuración Recomendada para Producción

#### Ajuste de Timeouts de PHP

En el servidor de producción, ajusta los timeouts en `php.ini`:

```ini
max_execution_time = 120
max_input_time = 120
memory_limit = 256M
```

O en el archivo `.htaccess` (Apache):

```apache
<IfModule mod_php7.c>
    php_value max_execution_time 120
    php_value memory_limit 256M
</IfModule>
```

O en `public/index.php` (Laravel):

```php
<?php
set_time_limit(120);
// ... resto del archivo
```

#### Configuración de Laravel

En `.env`, ajusta los timeouts de la API:

```env
MLS_TIMEOUT=60
MLS_RATE_LIMIT=10
MLS_BATCH_SIZE=20
```

### Estrategias de Sincronización para Servidores Limitados

#### 1. Sincronización Progresiva Manual

Llama al endpoint `/api/mls/sync/progressive` repetidamente hasta que `completed` sea true:

```javascript
async function syncAllProperties() {
    let offset = 0;
    let completed = false;
    
    while (!completed) {
        const response = await fetch('/api/mls/sync/progressive', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + token,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                batch_size: 20,
                skip_media: true,
                offset: offset
            })
        });
        
        const result = await response.json();
        
        if (result.data.completed) {
            completed = true;
            console.log('Sincronización completada');
        } else {
            offset = result.data.next_offset;
            console.log(`Progreso: ${result.data.progress_percentage}%`);
            
            // Esperar un poco entre llamadas para no sobrecargar el servidor
            await new Promise(r => setTimeout(r, 2000));
        }
    }
}
```

#### 2. Sincronización Automática con Cron

Crea un comando de Laravel que se ejecute cada cierto tiempo:

```php
// app/Console/Commands/ProgressiveMLSSyncCommand.php
protected $signature = 'mls:sync-progressive {--offset=0} {--batch=20}';

public function handle()
{
    $result = $this->syncService->syncPropertiesProgressive(
        (int) $this->option('batch'),
        true, // skip_media
        (int) $this->option('offset')
    );
    
    if (!$result['completed']) {
        $this->info("No completado. Offset: {$result['next_offset']}");
        $this->info("Ejecuta: php artisan mls:sync-progressive --offset={$result['next_offset']}");
    } else {
        $this->info("Sincronización completada");
    }
}
```

Agrega al crontab:
```bash
# Ejecutar cada hora, procesando 20 propiedades por vez
0 * * * * cd /path/to/project && php artisan mls:sync-progressive --batch=20 >> /dev/null 2>&1
```

#### 3. Configuración de Supervisor para el Worker de Colas

Si usas colas para descargar imágenes, configura Supervisor:

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /ruta/a/tu/proyecto/artisan queue:work --sleep=3 --tries=3 --max-time=3600 --memory=128
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/ruta/a/tu/proyecto/storage/logs/worker.log
stopwaitsecs=3600
```

### Monitoreo del Lock

El endpoint `/api/mls/status` ahora incluye información del lock:

```json
{
  "sync_locked": false,
  "lock_stale": false,
  "can_force_unlock": false,
  "checkpoint": {
    "offset": 40,
    "mode": "incremental",
    "skip_media": true
  }
}
```

### Solución de Problemas

#### "Ya existe una sincronización en curso"

1. Verifica si hay un proceso ejecutándose: `ps aux | grep "php artisan"
2. Si no hay proceso pero el lock está activo, espera 30 minutos o usa:
   ```bash
   curl -X POST http://localhost:8000/api/mls/sync/unlock \
     -H "Authorization: Bearer YOUR_TOKEN"
   ```

#### La sincronización no progresa

1. Verifica el checkpoint: `GET /api/mls/checkpoint`
2. Verifica el progreso: `GET /api/mls/sync/properties/progress`
3. Limpia el checkpoint si está corrupto: `DELETE /api/mls/checkpoint`
4. Comienza de nuevo: `POST /api/mls/sync/progressive`

#### El frontend muestra "Ya existe una sincronización en curso"

El frontend ahora tiene lógica automática para manejar esta situación:

1. **Detección automática**: Antes de iniciar, verifica el estado del lock
2. **Liberación automática**: Si el lock está obsoleto (>5 minutos), intenta liberarlo automáticamente
3. **Reintentos inteligentes**: Si hay un lock activo pero no obsoleto, espera y reintenta hasta 3 veces
4. **Mensajes claros**: Muestra al usuario qué está pasando y qué acción se está tomando

El código del frontend hace lo siguiente:

```javascript
// Verificar estado del lock
const statusPayload = await apiFetch(`${API_BASE}/mls/status`);

if (statusPayload?.success && statusPayload.data) {
  const isLocked = statusPayload.data.sync_locked;
  const isStale = statusPayload.data.lock_stale;
  
  if (!isLocked) {
    // No hay lock, podemos proceder
    break;
  }
  
  if (isLocked && isStale) {
    // Lock obsoleto, intentar liberar automáticamente
    const unlockResult = await apiFetch(`${API_BASE}/mls/sync/unlock`, { method: 'POST' });
    if (unlockResult?.success) {
      break; // Lock liberado, continuar
    }
  } else if (isLocked && !isStale) {
    // Lock activo y no obsoleto, esperar
    await new Promise(resolve => setTimeout(resolve, 5000));
    // Reintentar hasta 3 veces
  }
}
```

#### Errores de memoria

1. Reduce el `batch_size` a 10 o menos
2. Asegúrate de que `skip_media` sea true
3. Verifica la memoria del servidor: `free -m`
4. Aumenta el memory_limit en php.ini
