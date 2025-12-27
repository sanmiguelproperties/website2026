# Dashboard Base para Administradores de Contenido

Un dashboard base construido con Laravel 12 que proporciona una plataforma sólida para proyectos que requieren administración de contenido. Incluye autenticación OAuth2, gestión de usuarios, roles y permisos, manejo de medios, monedas y temas de color personalizables.

## 🚀 Características Principales

### 🔐 Autenticación y Autorización
- Autenticación OAuth2 con Laravel Passport
- Sistema RBAC (Role-Based Access Control) con Spatie Laravel Permission
- Middleware de API para protección de endpoints
- Gestión completa de usuarios, roles y permisos

### 📁 Gestión de Medios
- Subida y gestión de archivos multimedia
- Soporte para imágenes, videos y documentos
- Almacenamiento organizado por directorios
- Validación de tipos MIME y extensiones
- Soft deletes para recuperación de archivos

### 👥 Administración de Usuarios
- CRUD completo de usuarios
- Asignación y revocación de roles
- Gestión de permisos por usuario
- Perfiles con imágenes de usuario

### 💰 Sistema de Monedas
- Gestión de múltiples monedas
- Conversión automática a moneda base
- Tasas de cambio configurables
- Soporte para operaciones financieras

### 🎨 Temas de Color Personalizables
- Sistema de temas CSS variables
- Activación/desactivación de temas
- Tema por defecto y activo
- Personalización completa de colores

### 🛠️ Arquitectura Técnica
- **Framework**: Laravel 12
- **Autenticación**: Laravel Passport (OAuth2)
- **Permisos**: Spatie Laravel Permission
- **Base de Datos**: SQLite (configurable para MySQL/PostgreSQL)
- **Frontend**: Blade templates con Tailwind CSS
- **API**: RESTful con respuestas JSON estandarizadas

## 📋 Requisitos del Sistema

- PHP 8.2+
- Laravel 12
- Composer
- Node.js & npm
- Base de datos (SQLite/MySQL/PostgreSQL)

## 🛠️ Instalación

1. **Clonar el repositorio**
   ```bash
   git clone <url-del-repositorio>
   cd gusgusweb
   ```

2. **Instalar dependencias**
   ```bash
   composer install
   npm install
   ```

3. **Configurar entorno**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configurar base de datos**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

5. **Generar claves OAuth**
   ```bash
   php artisan passport:install
   ```

## 🚀 Ejecución

### Desarrollo
```bash
composer run dev
```
Esto iniciará:
- Servidor Laravel (http://localhost:8000)
- Queue worker
- Logs en tiempo real
- Vite dev server para assets

### Producción
```bash
php artisan serve
npm run build
```

## 📚 Documentación de API

La documentación completa de las APIs REST está disponible en [`docs/api/README.md`](docs/api/README.md).

### Endpoints Principales

- **Autenticación**: `/api/login`, `/api/user`
- **Usuarios**: `/api/users` (CRUD + roles/permisos)
- **Medios**: `/api/media` (gestión de archivos)
- **Monedas**: `/api/currencies` (CRUD + conversiones)
- **Temas**: `/api/color-themes` (gestión de temas)
- **RBAC**: `/api/rbac/roles`, `/api/rbac/permissions`

### Autenticación API
```bash
# Obtener token
POST /api/login
{
  "email": "admin@example.com",
  "password": "password"
}

# Usar token
Authorization: Bearer {access_token}
```

## 🗄️ Base de Datos

### Migraciones Principales
- `users` - Usuarios del sistema
- `media_assets` - Archivos multimedia
- `currencies` - Monedas y tasas de cambio
- `color_themes` - Temas de color
- `roles`, `permissions`, `role_has_permissions` - Sistema RBAC

### Seeders Disponibles
- `DatabaseSeeder` - Ejecuta todos los seeders
- `UserSeeder` - Usuario administrador por defecto
- `RbacSeeder` - Roles y permisos base
- `CurrencySeeder` - Monedas comunes
- `ColorThemeSeeder` - Tema por defecto

## 🎨 Frontend

### Vistas Principales
- `dashboard.blade.php` - Panel principal
- `welcome.blade.php` - Página de bienvenida
- `auth/login.blade.php` - Formulario de login
- Vistas de gestión en `resources/views/` (users, currencies, etc.)

### Componentes
- Sistema de layouts (`layouts/app.blade.php`, `layouts/guest.blade.php`)
- Componentes reutilizables (header, footer, preloader)
- Componentes de media (media-input, media-picker)

## 🔧 Configuración

### Variables de Entorno (.env)
```env
APP_NAME="Dashboard Base"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_TIMEZONE=UTC
APP_URL=http://localhost

DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

PASSPORT_PERSONAL_ACCESS_CLIENT_ID=
PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET=
```

### Configuraciones Adicionales
- `config/permission.php` - Configuración de permisos
- `config/passport.php` - Configuración OAuth2
- `config/filesystems.php` - Almacenamiento de archivos

## 📦 Dependencias Principales

### PHP
- `laravel/framework: ^12.0`
- `laravel/passport: ^13.2`
- `spatie/laravel-permission: ^6.21`

### JavaScript
- `axios` - Cliente HTTP
- `alpinejs` - Framework frontend
- `tailwindcss` - Framework CSS

## 🧪 Testing

```bash
# Ejecutar tests
php artisan test

# Con coverage
php artisan test --coverage
```

## 📁 Estructura del Proyecto

```
├── app/
│   ├── Http/Controllers/     # Controladores API
│   ├── Models/              # Modelos Eloquent
│   ├── Services/            # Lógica de negocio
│   └── Providers/           # Service providers
├── database/
│   ├── migrations/          # Migraciones BD
│   └── seeders/            # Datos iniciales
├── docs/                   # Documentación
├── public/                 # Assets públicos
├── resources/
│   ├── css/               # Estilos
│   ├── js/               # JavaScript
│   └── views/            # Templates Blade
├── routes/
│   ├── api.php           # Rutas API
│   └── web.php          # Rutas web
└── tests/               # Tests
```

## 🤝 Contribución

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/nueva-funcionalidad`)
3. Commit tus cambios (`git commit -am 'Agrega nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Abre un Pull Request

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

## 🆘 Soporte

Para soporte técnico:
- Revisa la documentación en `docs/`
- Consulta los logs de Laravel
- Verifica la configuración de permisos

---

**Desarrollado con ❤️ usando Laravel 12**