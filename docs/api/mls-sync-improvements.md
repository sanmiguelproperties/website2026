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
