# Configuración de Colas para Descarga de Imágenes MLS

## Resumen

Los jobs de descarga de imágenes ([`DownloadPropertyImageJob`](app/Jobs/DownloadPropertyImageJob.php)) se ejecutan de forma asíncrona usando el sistema de colas de Laravel. Para que funcionen correctamente en un servidor externo, necesitas configurar y ejecutar un worker de colas.

## Configuración Actual

El proyecto está configurado para usar **database** como driver de colas:

```env
# En .env
QUEUE_CONNECTION=database
```

Esta configuración almacena los jobs en la tabla `jobs` de la base de datos.

## Requisitos Previos

### 1. Verificar que la tabla de jobs existe

Ejecuta las migraciones para crear las tablas necesarias:

```bash
php artisan migrate
```

Las tablas creadas son:
- `jobs` - Almacena los jobs pendientes
- `job_batches` - Almacena información de batches de jobs
- `failed_jobs` - Almacena jobs que fallaron

### 2. Configurar el archivo .env

Asegúrate de tener estas variables configuradas en tu `.env`:

```env
# Configuración de colas
QUEUE_CONNECTION=database

# Configuración de base de datos (ejemplo para MySQL)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tu_base_de_datos
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña

# Configuración de colas específica
DB_QUEUE=default
DB_QUEUE_TABLE=jobs
DB_QUEUE_RETRY_AFTER=90
```

## Opciones de Ejecución del Worker

### Opción 1: Ejecutar Worker en Desarrollo (Local)

Para desarrollo local, puedes ejecutar el worker manualmente:

```bash
# Ejecutar worker en primer plano
php artisan queue:work

# Ejecutar worker con límite de intentos
php artisan queue:work --tries=3

# Ejecutar worker con timeout
php artisan queue:work --timeout=60

# Ejecutar worker en modo verbose (muestra más información)
php artisan queue:work --verbose

# Ejecutar worker para una cola específica
php artisan queue:work --queue=default
```

### Opción 2: Ejecutar Worker en Producción (Servidor Externo)

Para producción, necesitas ejecutar el worker como un proceso en segundo plano. Hay varias opciones:

#### 2.1. Usando Supervisor (Recomendado para Linux)

Supervisor es un gestor de procesos para Linux que mantiene el worker ejecutándose y lo reinicia si falla.

**Instalar Supervisor:**

```bash
# Ubuntu/Debian
sudo apt-get install supervisor

# CentOS/RHEL
sudo yum install supervisor

# macOS
brew install supervisor
```

**Crear configuración de Supervisor:**

Crea el archivo `/etc/supervisor/conf.d/laravel-worker.conf`:

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /ruta/a/tu/proyecto/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/ruta/a/tu/proyecto/storage/logs/worker.log
stopwaitsecs=3600
```

**Parámetros importantes:**
- `numprocs=2`: Número de workers paralelos (ajusta según tu servidor)
- `--sleep=3`: Esperar 3 segundos entre jobs
- `--tries=3`: Reintentar jobs fallidos hasta 3 veces
- `--max-time=3600`: Timeout máximo de 1 hora por job
- `stopwaitsecs=3600`: Esperar hasta 1 hora antes de forzar la detención

**Iniciar y habilitar el worker:**

```bash
# Leer la nueva configuración
sudo supervisorctl reread

# Actualizar los procesos
sudo supervisorctl update

# Iniciar el worker
sudo supervisorctl start laravel-worker:*

# Verificar estado
sudo supervisorctl status

# Ver logs en tiempo real
sudo supervisorctl tail -f laravel-worker:*
```

#### 2.2. Usando Systemd (Alternativa a Supervisor)

Si prefieres usar systemd en lugar de Supervisor:

**Crear archivo de servicio:**

Crea el archivo `/etc/systemd/system/laravel-worker.service`:

```ini
[Unit]
Description=Laravel Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
WorkingDirectory=/ruta/a/tu/proyecto
ExecStart=/usr/bin/php /ruta/a/tu/proyecto/artisan queue:work --sleep=3 --tries=3 --max-time=3600
Restart=always
RestartSec=5s

[Install]
WantedBy=multi-user.target
```

**Iniciar y habilitar el servicio:**

```bash
# Recargar systemd
sudo systemctl daemon-reload

# Habilitar el servicio para que inicie automáticamente
sudo systemctl enable laravel-worker

# Iniciar el servicio
sudo systemctl start laravel-worker

# Verificar estado
sudo systemctl status laravel-worker

# Ver logs
sudo journalctl -u laravel-worker -f
```

#### 2.3. Usando nohup (Simple pero no recomendado para producción)

Para una solución simple sin Supervisor o Systemd:

```bash
# Ejecutar worker en segundo plano
nohup php artisan queue:work --sleep=3 --tries=3 --max-time=3600 > storage/logs/worker.log 2>&1 &

# Verificar que esté ejecutándose
ps aux | grep "queue:work"
```

**Nota:** Esta opción no reinicia el worker si falla, por lo que no es recomendada para producción.

#### 2.4. Usando Docker (Si usas Docker)

Si tu aplicación está en Docker, puedes ejecutar el worker en un contenedor separado:

**Docker Compose:**

```yaml
version: '3.8'
services:
  app:
    build: .
    # ... configuración de la app ...

  worker:
    build: .
    command: php artisan queue:work --sleep=3 --tries=3 --max-time=3600
    depends_on:
      - app
      - db
    restart: unless-stopped
    volumes:
      - .:/var/www/html
```

**Ejecutar:**

```bash
docker-compose up -d worker
```

### Opción 3: Usando Redis (Mejor rendimiento)

Si quieres mejor rendimiento, puedes usar Redis en lugar de database:

**Instalar Redis:**

```bash
# Ubuntu/Debian
sudo apt-get install redis-server

# CentOS/RHEL
sudo yum install redis

# macOS
brew install redis
```

**Configurar .env:**

```env
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_QUEUE=default
```

**Ejecutar worker:**

```bash
php artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
```

## Monitoreo del Worker

### Ver Jobs Pendientes

```bash
# Ver jobs en la cola
php artisan queue:monitor

# Ver jobs fallidos
php artisan queue:failed

# Reintentar todos los jobs fallidos
php artisan queue:retry all

# Limpiar jobs fallidos
php artisan queue:flush
```

### Ver Logs

Los logs del worker se guardan en:

```bash
# Ver logs de Laravel
tail -f storage/logs/laravel.log

# Ver logs específicos del worker (si configuraste en Supervisor)
tail -f storage/logs/worker.log
```

### Verificar que el Worker esté Ejecutándose

```bash
# Ver procesos de queue:work
ps aux | grep "queue:work"

# Verificar que haya workers activos
php artisan queue:work --status
```

## Configuración del Job de Descarga de Imágenes

El job [`DownloadPropertyImageJob`](app/Jobs/DownloadPropertyImageJob.php) tiene las siguientes características:

- **Timeout**: 60 segundos por defecto
- **Intentos**: 3 reintentos antes de marcar como fallido
- **Backoff**: Espera exponencial entre reintentos

### Ajustar Configuración del Job

Si necesitas ajustar la configuración del job, puedes modificarlo en [`app/Jobs/DownloadPropertyImageJob.php`](app/Jobs/DownloadPropertyImageJob.php):

```php
class DownloadPropertyImageJob implements ShouldQueue
{
    // Número máximo de intentos
    public $tries = 3;

    // Timeout en segundos
    public $timeout = 60;

    // Tiempo de espera antes de reintentar (en segundos)
    public $backoff = [10, 30, 60]; // 10s, 30s, 60s
}
```

## Solución de Problemas

### Los jobs no se ejecutan

**Síntoma:** Los jobs se crean en la tabla `jobs` pero no se procesan.

**Soluciones:**
1. Verifica que el worker esté ejecutándose: `ps aux | grep "queue:work"`
2. Verifica los logs del worker: `tail -f storage/logs/worker.log`
3. Reinicia el worker: `sudo supervisorctl restart laravel-worker:*`

### Jobs fallan repetidamente

**Síntoma:** Los jobs aparecen en la tabla `failed_jobs`.

**Soluciones:**
1. Verifica los logs de jobs fallidos: `php artisan queue:failed`
2. Revisa el error específico: `php artisan queue:failed <id>`
3. Aumenta el timeout del job si es necesario
4. Verifica que haya suficiente espacio en disco
5. Verifica que la URL de la imagen sea accesible

### Worker consume mucha memoria

**Síntoma:** El worker consume mucha memoria y se detiene.

**Soluciones:**
1. Reduce el número de workers paralelos en Supervisor
2. Aumenta el `--memory` limit: `php artisan queue:work --memory=128`
3. Ejecuta `php artisan queue:restart` periódicamente

### Worker se detiene inesperadamente

**Síntoma:** El worker se detiene sin razón aparente.

**Soluciones:**
1. Verifica que Supervisor esté configurado con `autorestart=true`
2. Aumenta el `--max-time` del worker
3. Verifica que no haya límites de recursos en el servidor
4. Revisa los logs del sistema: `journalctl -xe`

## Comandos Útiles

```bash
# Ejecutar worker en modo daemon (segundo plano)
php artisan queue:work --daemon

# Ejecutar worker una sola vez (procesa todos los jobs y termina)
php artisan queue:work --once

# Ejecutar worker con límite de memoria (en MB)
php artisan queue:work --memory=128

# Ejecutar worker sin modo daemon (para debugging)
php artisan queue:listen

# Ver lista de jobs pendientes
php artisan queue:work --queue=default --stop-when-empty

# Reiniciar worker (limpia memoria caché)
php artisan queue:restart

# Ver estadísticas de la cola
php artisan queue:monitor
```

## Configuración Recomendada para Producción

### Supervisor (Linux)

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /ruta/a/tu/proyecto/artisan queue:work --sleep=3 --tries=3 --max-time=3600 --memory=128
autostart=true
autorestart=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/ruta/a/tu/proyecto/storage/logs/worker.log
stopwaitsecs=3600
```

### Variables de Entorno (.env)

```env
QUEUE_CONNECTION=database
DB_QUEUE=default
DB_QUEUE_RETRY_AFTER=90
```

## Resumen

Para que los jobs de descarga de imágenes funcionen en un servidor externo:

1. ✅ Configurar `QUEUE_CONNECTION=database` en `.env`
2. ✅ Ejecutar las migraciones para crear tablas de colas
3. ✅ Ejecutar un worker de colas (Supervisor recomendado)
4. ✅ Monitorear el worker regularmente
5. ✅ Verificar logs para detectar problemas

El worker procesará automáticamente los jobs de descarga de imágenes que se crean durante la sincronización MLS.
