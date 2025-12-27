# Documentación de Vistas - Dashboard Base

Esta documentación describe todas las vistas disponibles en el sistema de Dashboard Base, incluyendo su estructura, funcionalidad y componentes utilizados.

## Estructura General

El sistema utiliza Laravel Blade como motor de plantillas y está organizado en las siguientes carpetas:

- `resources/views/` - Directorio principal de vistas
  - `layouts/` - Layouts base del sistema
  - `components/` - Componentes reutilizables
  - `auth/` - Vistas de autenticación
  - `dashboard.blade.php` - Dashboard principal
  - `funnel.blade.php` - Embudo de ventas
  - `media-example.blade.php` - Ejemplo del Media Manager
  - `welcome.blade.php` - Página de bienvenida
  - `color-themes/` - Gestión de temas de color
  - `currencies/` - Gestión de monedas
  - `rbac/` - Gestión de roles y permisos
  - `users/` - Gestión de usuarios

## Tecnologías Utilizadas

- **Laravel Blade**: Motor de plantillas
- **Tailwind CSS**: Framework CSS (CDN)
- **JavaScript Vanilla**: Interacciones del lado cliente
- **CSS Variables**: Sistema de temas dinámicos
- **OKLCH Color Space**: Sistema de colores avanzado

## Sistema de Temas

El sistema utiliza un sistema de temas dinámicos basado en CSS variables que se cargan desde la base de datos:

```css
:root {
  --c-bg: oklch(0.17 0.02 255);        /* Fondo */
  --c-surface: oklch(0.21 0.02 255);   /* Superficies */
  --c-elev: oklch(0.25 0.02 255);      /* Elevaciones */
  --c-text: oklch(0.93 0.02 255);      /* Texto principal */
  --c-muted: oklch(0.74 0.02 255);     /* Texto secundario */
  --c-border: oklch(0.35 0.02 255);    /* Bordes */
  --c-primary: oklch(0.72 0.14 260);   /* Color primario */
  --c-primary-ink: oklch(0.12 0.02 260); /* Texto sobre primario */
  --c-accent: oklch(0.75 0.13 170);    /* Acento */
  --c-danger: oklch(0.68 0.21 25);     /* Peligro/Error */
}
```

## Componentes Comunes

### Layouts

#### `layouts/app.blade.php`
Layout principal del dashboard con:
- Sidebar lateral con navegación
- Header superior con búsqueda y acciones
- Área de contenido principal
- Footer
- Sistema de temas dinámico

#### `layouts/guest.blade.php`
Layout simplificado para páginas públicas (login, welcome)

### Componentes Reutilizables

#### `components/header.blade.php`
Barra superior con:
- Botón de menú móvil
- Migas de pan
- Barra de búsqueda
- Botones de acción
- Avatar del usuario

#### `components/footer.blade.php`
Pie de página con enlaces legales y año dinámico

#### `components/preloader.blade.php`
Pantalla de carga con animación CSS

#### `components/json-response-modal.blade.php`
Modal para mostrar respuestas de API con formato JSON

#### `components/media-input.blade.php`
Componente para selección de archivos multimedia

#### `components/media-picker.blade.php`
Modal completo para gestión de medios

## Vistas Principales

### Dashboard (`dashboard.blade.php`)
Vista principal del sistema que muestra:
- Bienvenida al usuario
- Información básica
- Enlace de cierre de sesión

### Embudo de Ventas (`funnel.blade.php`)
Sistema de gestión de leads con:
- 7 etapas del embudo (Prospección, Contacto inicial, Calificación, Propuesta, Negociación, Cierre ganado/perdido)
- Drag & drop entre etapas
- Creación de tarjetas dinámicas
- Contadores por etapa

### Media Manager (`media-example.blade.php`)
Demostración del sistema de gestión de medios con:
- Input simple (un archivo)
- Input múltiple con límite
- Input con preview personalizado
- Formulario de ejemplo

### Página de Bienvenida (`welcome.blade.php`)
Landing page con:
- Logo de Laravel
- Enlaces a documentación
- Información de primeros pasos
- Diseño responsivo con modo oscuro

## Vistas de Gestión

### Gestión de Usuarios (`users/manage.blade.php`)
Interfaz completa para:
- Listado de usuarios con paginación
- Creación/edición de usuarios
- Gestión de imágenes de perfil
- Búsqueda y filtrado

### Gestión de Monedas (`currencies/manage.blade.php`)
Sistema para:
- CRUD de monedas
- Configuración de tipos de cambio
- Moneda base del sistema
- Validación de códigos de moneda

### Gestión de Temas (`color-themes/manage.blade.php`)
Editor avanzado de temas con:
- Selector de colores OKLCH
- Conversión automática RGB ↔ OKLCH
- Preview en tiempo real
- Gestión de temas activos

### Gestión RBAC (`rbac/manage.blade.php`)
Sistema de roles y permisos con:
- Gestión de roles
- Gestión de permisos
- Asignación de permisos a roles
- Tres pestañas: Roles, Permisos, Asignaciones

## Vistas de Autenticación

### Login (`auth/login.blade.php`)
Formulario de inicio de sesión con:
- Campos de email y contraseña
- Opción "Recordarme"
- Enlace de recuperación de contraseña
- Manejo de errores

## Funcionalidades JavaScript

### Sistema de Navegación
- Sidebar responsivo con backdrop
- Acordeón de navegación
- Marcado automático de enlaces activos

### Gestión de Medios
- Modal de selección de archivos
- Upload de archivos
- Preview de imágenes
- Drag & drop

### API Integration
- Fetch API para todas las operaciones
- Manejo de tokens de autenticación
- Modal de respuestas JSON
- Gestión de errores

### Temas Dinámicos
- Conversión OKLCH ↔ RGB
- Aplicación en tiempo real
- Persistencia en base de datos

## Consideraciones Técnicas

### Responsividad
Todas las vistas están diseñadas para ser completamente responsivas, adaptándose desde móviles hasta pantallas grandes.

### Accesibilidad
- Etiquetas ARIA apropiadas
- Navegación por teclado
- Contraste de colores adecuado
- Soporte para lectores de pantalla

### Rendimiento
- Carga diferida de JavaScript
- Optimización de animaciones CSS
- Uso de CSS containment donde es posible

### Seguridad
- Tokens CSRF en formularios
- Validación del lado cliente y servidor
- Sanitización de datos

## Próximos Pasos

Esta documentación cubre la estructura actual del sistema. Para futuras expansiones, considere:

1. Documentación de nuevos componentes
2. Guías de personalización de temas
3. Tutoriales de integración de nuevas vistas
4. Documentación de APIs relacionadas