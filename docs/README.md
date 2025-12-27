# Documentación del Proyecto

Esta carpeta contiene toda la documentación del proyecto Laravel.

## 📁 Estructura

```
docs/
├── README.md              # Este archivo
├── api/                   # Documentación de APIs REST
│   ├── README.md         # Índice de APIs
│   ├── auth.md           # API de autenticación
│   ├── media.md          # API de media assets
│   ├── users.md          # API de usuarios
│   ├── currencies.md     # API de monedas
│   ├── color-themes.md   # API de temas de color
│   └── rbac.md           # API de roles y permisos
└── database/
    └── migrations/
        └── README.md     # Documentación de migraciones
```

## 🚀 Inicio Rápido

### Autenticación
1. Obtener token: `POST /api/login`
2. Usar token en header: `Authorization: Bearer {token}`

### APIs Principales
- **Media**: Gestión de archivos multimedia
- **Users**: Administración de usuarios
- **Currencies**: Manejo de monedas y tipos de cambio
- **Color Themes**: Temas de color personalizables
- **RBAC**: Control de acceso basado en roles

## 📋 Migraciones de Base de Datos

Consulta [`database/migrations/README.md`](database/migrations/README.md) para información detallada sobre:
- Estructura de todas las tablas
- Relaciones entre entidades
- Propósito de cada migración
- Dependencias y restricciones

## 🔗 APIs REST

Visita [`api/README.md`](api/README.md) para:
- Lista completa de endpoints
- Ejemplos de requests/responses
- Códigos de estado HTTP
- Formatos de autenticación
- Parámetros de paginación y filtros

## 🛠️ Desarrollo

### Requisitos
- PHP 8.1+
- Laravel 11
- MySQL/PostgreSQL
- Composer
- Node.js & npm

### Instalación
```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
```

### Ejecutar
```bash
php artisan serve
npm run dev
```

## 📚 Recursos Adicionales

- [Laravel Documentation](https://laravel.com/docs)
- [Laravel Passport](https://laravel.com/docs/passport)
- [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission)
- [Laravel Sanctum](https://laravel.com/docs/sanctum) (si se usa)

## 🤝 Contribución

1. Lee la documentación existente
2. Sigue los estándares de código
3. Actualiza la documentación cuando hagas cambios
4. Usa commits descriptivos

## 📞 Soporte

Para preguntas sobre la documentación:
- Revisa primero los archivos README específicos
- Consulta los controladores para lógica detallada
- Revisa las migraciones para estructura de BD