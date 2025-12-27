# Vista: Dashboard Principal

**Archivo:** `resources/views/dashboard.blade.php`
**Ruta:** `/dashboard`
**Layout:** `layouts.app`

## Descripción

La vista principal del dashboard muestra una pantalla de bienvenida básica para usuarios autenticados. Es la página de destino después del login y proporciona acceso rápido a funcionalidades principales.

## Estructura HTML

```blade
@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="bg-white overflow-hidden shadow rounded-lg">
  <div class="p-6">
    <div class="flex items-center">
      <div class="flex-shrink-0">
        <!-- Icono SVG de información -->
      </div>
      <div class="ml-5 w-0 flex-1">
        <dl>
          <dt class="text-sm font-medium text-gray-500 truncate">
            Bienvenido al Dashboard
          </dt>
          <dd class="text-lg font-medium text-gray-900">
            {{ Auth::user()->name }}
          </dd>
        </dl>
      </div>
    </div>
  </div>
  <div class="bg-gray-50 px-6 py-4">
    <div class="text-sm">
      <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
        Cerrar Sesión
      </a>
    </div>
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
      @csrf
    </form>
  </div>
</div>
@endsection
```

## Funcionalidades

### 1. Mensaje de Bienvenida
- Muestra el nombre del usuario autenticado
- Icono informativo (círculo con signo de interrogación)
- Diseño limpio con tarjeta blanca

### 2. Cierre de Sesión
- Enlace que previene navegación por defecto
- Envío automático de formulario POST
- Protección CSRF incluida

## Componentes Utilizados

- **Layout App**: Proporciona sidebar, header y estructura general
- **Tailwind CSS**: Estilos responsivos y diseño moderno

## Variables Blade

- `Auth::user()->name`: Nombre del usuario actual
- `route('logout')`: URL para cerrar sesión

## Estilos CSS

Utiliza clases de Tailwind CSS para:
- Diseño de tarjeta con sombra
- Espaciado y tipografía
- Colores grises estándar

## JavaScript

No incluye JavaScript personalizado. El cierre de sesión se maneja mediante formulario HTML estándar.

## Consideraciones de Seguridad

- Protección CSRF en el formulario de logout
- Validación de autenticación en el backend

## Personalización

Para modificar el contenido del dashboard:

1. **Cambiar el mensaje de bienvenida:**
   ```blade
   <dt class="text-sm font-medium text-gray-500 truncate">
     Tu mensaje personalizado
   </dt>
   ```

2. **Agregar más contenido:**
   ```blade
   @section('content')
   <!-- Contenido existente -->
   <div class="mt-6">
     <!-- Nuevo contenido aquí -->
   </div>
   @endsection
   ```

## Próximas Mejoras

Esta vista es básica y podría expandirse con:
- Estadísticas del usuario
- Accesos rápidos a módulos
- Gráficos de actividad
- Notificaciones recientes