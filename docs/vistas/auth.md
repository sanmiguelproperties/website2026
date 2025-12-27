# Vistas: Autenticación

**Directorio:** `resources/views/auth/`
**Layout:** `layouts.guest`

## Descripción General

Las vistas de autenticación proporcionan la interfaz para el login de usuarios. Están diseñadas con un enfoque minimalista y moderno, utilizando Tailwind CSS para un diseño responsivo y atractivo.

## Estructura del Directorio

```
resources/views/auth/
└── login.blade.php          # Formulario de inicio de sesión
```

## Vista: Login

**Archivo:** `resources/views/auth/login.blade.php`
**Ruta:** `/login`
**Método:** `POST`

### Estructura HTML

```blade
@extends('layouts.guest')

@section('title', 'Iniciar Sesión')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
  <div class="max-w-md w-full space-y-8">
    <!-- Header -->
    <div>
      <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
        Iniciar Sesión
      </h2>
      <p class="mt-2 text-center text-sm text-gray-600">
        Ingresa tus credenciales para acceder
      </p>
    </div>

    <!-- Formulario -->
    <form class="mt-8 space-y-6" action="{{ route('login') }}" method="POST">
      @csrf

      <!-- Campos de entrada -->
      <div class="rounded-md shadow-sm -space-y-px">
        <div>
          <label for="email" class="sr-only">Correo electrónico</label>
          <input id="email" name="email" type="email" autocomplete="email" required
                 class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                 placeholder="Correo electrónico"
                 value="{{ old('email') }}">
        </div>
        <div>
          <label for="password" class="sr-only">Contraseña</label>
          <input id="password" name="password" type="password" autocomplete="current-password" required
                 class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                 placeholder="Contraseña">
        </div>
      </div>

      <!-- Opciones adicionales -->
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <input id="remember-me" name="remember" type="checkbox"
                 class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
          <label for="remember-me" class="ml-2 block text-sm text-gray-900">
            Recordarme
          </label>
        </div>

        <div class="text-sm">
          <a href="#" class="font-medium text-indigo-600 hover:text-indigo-500">
            ¿Olvidaste tu contraseña?
          </a>
        </div>
      </div>

      <!-- Botón de envío -->
      <div>
        <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
          <span class="absolute left-0 inset-y-0 flex items-center pl-3">
            <svg class="h-5 w-5 text-indigo-500 group-hover:text-indigo-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
            </svg>
          </span>
          Iniciar Sesión
        </button>
      </div>

      <!-- Manejo de errores -->
      @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
          <ul>
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif
    </form>
  </div>
</div>
@endsection
```

### Campos del Formulario

| Campo | Tipo | Nombre | Validación | Descripción |
|-------|------|--------|------------|-------------|
| Email | `email` | `email` | `required` | Correo electrónico del usuario |
| Password | `password` | `password` | `required` | Contraseña |
| Remember | `checkbox` | `remember` | opcional | Mantener sesión activa |

### Funcionalidades

#### 1. Diseño Minimalista
- Fondo gris claro (`bg-gray-50`)
- Tarjeta blanca centrada con sombra
- Espaciado generoso y tipografía clara

#### 2. Campos de Entrada Estilizados
- Bordes redondeados solo en extremos
- Estados de foco con anillos azules
- Placeholders descriptivos
- Autocomplete activado

#### 3. Opción "Recordarme"
- Checkbox con label asociada
- Funcionalidad estándar de Laravel

#### 4. Enlace de Recuperación
- Placeholder para funcionalidad futura
- Estilos hover consistentes

#### 5. Icono en Botón
- Icono SVG de candado
- Animación hover en el icono
- Posicionamiento absoluto

#### 6. Manejo de Errores
- Lista de errores de validación
- Estilos de alerta en rojo
- Iteración sobre `$errors->all()`

### Estilos CSS

#### Clases de Tailwind Utilizadas

**Layout y Espaciado:**
- `min-h-screen`: Altura mínima de pantalla completa
- `flex items-center justify-center`: Centrado perfecto
- `max-w-md w-full`: Ancho máximo responsivo

**Formulario:**
- `rounded-md shadow-sm`: Bordes redondeados y sombra sutil
- `-space-y-px`: Espaciado negativo para bordes continuos
- `appearance-none`: Reset de estilos nativos

**Estados de Foco:**
- `focus:outline-none`: Remover outline por defecto
- `focus:ring-indigo-500`: Anillo de foco azul
- `focus:border-indigo-500`: Borde de foco azul
- `focus:z-10`: Elevación en z-index

**Botón:**
- `group`: Para animaciones de grupo
- `relative`: Para posicionamiento absoluto del icono
- `hover:bg-indigo-700`: Cambio de color en hover

### JavaScript

La vista no incluye JavaScript personalizado. La funcionalidad se maneja completamente con:

- **HTML Forms**: Envío estándar del formulario
- **Laravel Validation**: Manejo de errores del lado servidor
- **Browser Autocomplete**: Sugerencias de autocompletado

### Seguridad

#### Medidas Implementadas

1. **CSRF Protection**: Token `@csrf` incluido
2. **Input Sanitization**: Laravel maneja sanitización automática
3. **Password Masking**: Campo password oculto
4. **Autocomplete Security**: Atributos apropiados para seguridad

#### Consideraciones Adicionales

- **Rate Limiting**: Debería implementarse en el controlador
- **Captcha**: Para prevenir ataques automatizados
- **Two-Factor Auth**: Para mayor seguridad

### Accesibilidad

#### Características de Accesibilidad

- **Labels Ocultos**: `sr-only` para lectores de pantalla
- **Atributos ARIA**: No requeridos debido a labels asociados
- **Contraste**: Colores con buen contraste
- **Navegación por Teclado**: Enfoque visible en todos los elementos
- **Semántica**: Estructura HTML correcta

### Personalización

#### Cambiar Colores del Tema

```css
/* Modificar colores base */
.text-gray-900 -> .text-blue-900
.bg-indigo-600 -> .bg-blue-600
.focus:ring-indigo-500 -> .focus:ring-blue-500
```

#### Agregar Campos Adicionales

```blade
<!-- Campo adicional -->
<div>
  <label for="company" class="sr-only">Empresa</label>
  <input id="company" name="company" type="text" ...>
</div>
```

#### Modificar Layout

```blade
<!-- Cambiar colores de fondo -->
<div class="min-h-screen flex items-center justify-center bg-blue-50 py-12 px-4 sm:px-6 lg:px-8">
```

### Testing

#### Casos de Prueba Recomendados

1. **Login exitoso**: Credenciales válidas
2. **Login fallido**: Credenciales inválidas
3. **Validación**: Campos requeridos vacíos
4. **Recordarme**: Funcionalidad de recordar sesión
5. **Responsive**: Diseño en diferentes tamaños de pantalla

### Integración con Backend

#### Controlador Esperado

```php
public function login(Request $request)
{
    $credentials = $request->only('email', 'password');

    if (Auth::attempt($credentials, $request->remember)) {
        return redirect()->intended('/dashboard');
    }

    return back()->withErrors([
        'email' => 'Credenciales inválidas.',
    ]);
}
```

#### Rutas

```php
// routes/web.php
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
```

### Próximas Mejoras

1. **Recuperación de Contraseña**: Implementar funcionalidad completa
2. **Registro de Usuarios**: Vista complementaria
3. **Login Social**: Integración con OAuth
4. **Two-Factor Authentication**: 2FA
5. **Captcha**: Protección contra bots
6. **Animaciones**: Transiciones suaves
7. **Loading States**: Estados de carga