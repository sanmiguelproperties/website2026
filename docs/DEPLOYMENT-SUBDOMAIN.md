# 🚀 Guía de Despliegue en Subdominio (Hosting Compartido)

## Escenario

El proyecto Laravel se subió completo a una subcarpeta dentro del hosting:
```
/home/usuario/admin.sanmiguelproperties.com/
├── .htaccess          ← NUEVO: redirige todo a public/
├── app/
├── bootstrap/
├── config/
├── database/
├── public/
│   ├── .htaccess      ← Laravel default
│   ├── index.php
│   ├── storage/       ← SYMLINK → ../../storage/app/public
│   └── js/
├── storage/
│   └── app/
│       └── public/
│           ├── mls/            ← Imágenes MLS
│           └── uploads/        ← Uploads de media
├── vendor/
├── .env               ← Configurar APP_URL
└── ...
```

El subdominio `https://admin.sanmiguelproperties.com` apunta a la **raíz** de esta carpeta.

---

## 📋 Pasos para Configurar (ejecutar en orden)

### 1. Conectarse al servidor vía SSH

```bash
ssh usuario@tu-servidor.com
cd /home/usuario/admin.sanmiguelproperties.com
```

### 2. Configurar el `.env` del servidor

```bash
# Editar el archivo .env
nano .env
```

**Cambios necesarios en `.env`:**
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://admin.sanmiguelproperties.com

# Base de datos del servidor
DB_HOST=localhost
DB_DATABASE=tu_base_de_datos
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña

# Cache y sesiones
CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=database
```

> ⚠️ **IMPORTANTE**: `APP_URL` DEBE ser exactamente `https://admin.sanmiguelproperties.com` (sin `/` al final)

### 3. Verificar permisos de carpetas

```bash
# Dar permisos de escritura a storage y cache
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# ⚠️ IMPORTANTE: Passport OAuth keys requieren permisos restrictivos (600 o 660)
# Si no existen, generarlos primero:
php artisan passport:keys --force

# Ajustar permisos de las claves OAuth (OBLIGATORIO después de chmod -R 775):
chmod 600 storage/oauth-private.key
chmod 600 storage/oauth-public.key

# Asegurar que el propietario sea correcto
chown -R www-data:www-data storage bootstrap/cache
# En algunos hostings compartidos puede ser:
# chown -R usuario:usuario storage bootstrap/cache
```

### 4. Crear el symlink de storage

```bash
# Primero verificar si ya existe
ls -la public/storage

# Si no existe, crearlo con artisan:
php artisan storage:link

# Si artisan falla (por permisos), crear manualmente:
cd public
ln -s ../storage/app/public storage
cd ..

# Verificar que funciona:
ls -la public/storage
# Debe mostrar algo como: storage -> ../storage/app/public
```

### 5. Verificar el .htaccess de la raíz

```bash
# Verificar que el .htaccess de la raíz existe
cat .htaccess

# Debe contener las reglas de redirección a public/
# Si no existe, créalo con:
cat > .htaccess << 'EOF'
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_URI} !^/public/
    RewriteCond %{DOCUMENT_ROOT}/public%{REQUEST_URI} -f
    RewriteRule ^(.*)$ public/$1 [L]
    RewriteCond %{DOCUMENT_ROOT}/public%{REQUEST_URI} -d
    RewriteRule ^(.*)$ public/$1 [L]
    RewriteRule ^(.*)$ public/index.php [L]
</IfModule>
EOF
```

### 6. Limpiar cachés

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Regenerar cachés de producción
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 7. Ejecutar diagnóstico

```bash
# Verificar que todo está configurado correctamente
php artisan storage:diagnostic
```

Este comando verifica:
- ✅ Variables de entorno
- ✅ Rutas del sistema
- ✅ Permisos de directorios
- ✅ Symlink de storage
- ✅ Generación de URLs
- ✅ Contenido de storage
- ✅ Media assets en base de datos
- ✅ Archivos físicos

### 8. Corregir URLs de imágenes existentes

Si ya tenías imágenes en la base de datos con URLs incorrectas (por ejemplo, con el dominio local):

```bash
# Primero verificar qué se va a cambiar (modo dry-run):
php artisan storage:fix-urls --dry-run

# Si todo se ve bien, aplicar los cambios:
php artisan storage:fix-urls

# Si las URLs antiguas tienen un dominio específico:
php artisan storage:fix-urls --old-url=https://sanmiguelproperties.test
```

### 9. Migrar base de datos (si es necesario)

```bash
php artisan migrate --force
```

### 10. Configurar Queue Worker (para descarga de imágenes MLS)

```bash
# Probar que funciona:
php artisan queue:work --queue=mls-images --once

# Para producción, configurar un cron o supervisor:
# Añadir al crontab:
* * * * * cd /home/usuario/admin.sanmiguelproperties.com && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🔍 Verificación Final

### Verificar que las imágenes se sirven correctamente:

```bash
# Verificar que una imagen es accesible
curl -I https://admin.sanmiguelproperties.com/storage/mls/ALGUNA_PROPIEDAD/imagen.jpg

# Debe retornar HTTP 200 y Content-Type: image/jpeg
```

### Verificar desde el navegador:

1. Visitar `https://admin.sanmiguelproperties.com` → Debe cargar el sitio
2. Visitar `https://admin.sanmiguelproperties.com/storage/` → Las imágenes deben ser accesibles
3. Verificar que las propiedades muestran sus imágenes correctamente

---

## 🐛 Troubleshooting

### Problema: "403 Forbidden" al acceder al sitio
```bash
# Verificar permisos del .htaccess
chmod 644 .htaccess
chmod 644 public/.htaccess

# Verificar que mod_rewrite está habilitado (preguntar al hosting)
```

### Problema: "500 Internal Server Error"
```bash
# Revisar logs
cat storage/logs/laravel.log | tail -50

# Verificar permisos
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### Problema: Imágenes no se muestran (404)
```bash
# Verificar symlink
ls -la public/storage
# Debe ser: storage -> ../storage/app/public

# Si el symlink es absoluto y no funciona, recrear como relativo:
cd public
rm storage
ln -s ../storage/app/public storage
cd ..

# Verificar que las imágenes existen físicamente
ls -la storage/app/public/mls/
```

### Problema: Las imágenes nuevas se suben con URL incorrecta
```bash
# Verificar APP_URL:
php artisan tinker
>>> config('app.url')
# Debe mostrar: "https://admin.sanmiguelproperties.com"

# Si es incorrecto, editar .env y limpiar caché:
nano .env
php artisan config:clear
php artisan config:cache
```

### Problema: FollowSymLinks no permitido
En algunos hostings compartidos, los symlinks no funcionan. En ese caso:
```bash
# Añadir a public/.htaccess:
Options +FollowSymLinks
```

Si el hosting bloquea completamente los symlinks, usa este enfoque alternativo:
```bash
# En lugar de symlink, mueve el contenido:
rm -f public/storage
cp -r storage/app/public public/storage
```
> ⚠️ Si usas este enfoque, tendrás que repetirlo cada vez que subas nuevas imágenes.

---

## 📝 Resumen de Comandos Rápidos

```bash
# Conectar y navegar al proyecto
ssh usuario@servidor
cd /ruta/a/tu/proyecto

# Verificar estado
php artisan storage:diagnostic

# Corregir URLs
php artisan storage:fix-urls --dry-run
php artisan storage:fix-urls

# Limpiar y regenerar cachés
php artisan config:clear && php artisan config:cache
php artisan route:clear && php artisan route:cache
php artisan view:clear && php artisan view:cache

# Symlink de storage
php artisan storage:link

# Queue worker para imágenes MLS
php artisan queue:work --queue=mls-images --tries=3
```
