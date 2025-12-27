# Vista: Embudo de Ventas

**Archivo:** `resources/views/funnel.blade.php`
**Ruta:** `/funnel`
**Layout:** `layouts.app`

## Descripción

El embudo de ventas es un sistema de gestión visual de leads que permite arrastrar y soltar tarjetas entre diferentes etapas del proceso de ventas. Incluye 7 etapas estándar y funcionalidad completa de CRUD para leads.

## Estructura HTML

```blade
@extends('layouts.app')

@section('title', 'Embudo de Ventas • Dashboard Base')

@section('content')
<!-- Header con título y botón de nuevo lead -->
<header class="flex items-center justify-between">
  <!-- Migas de pan -->
  <!-- Botón "Nuevo lead" -->
</header>

<!-- Board principal -->
<section id="dash-funnel-board" class="relative overflow-x-auto pb-2">
  <div class="min-w-[1200px] grid grid-cols-7 gap-4 xl:min-w-0">
    <!-- 7 etapas del embudo -->
  </div>
</section>

<!-- Script del embudo -->
<script id="dash-funnel-script">
  // JavaScript para drag & drop y gestión de tarjetas
</script>
@endsection
```

## Etapas del Embudo

1. **Prospección**: Leads iniciales
2. **Contacto inicial**: Primer contacto establecido
3. **Calificación**: Leads calificados
4. **Propuesta**: Propuestas enviadas
5. **Negociación**: En proceso de negociación
6. **Cierre ganado**: Ventas exitosas
7. **Cierre perdido**: Ventas no concretadas

## Funcionalidades

### 1. Drag & Drop
- Arrastrar tarjetas entre etapas
- Validación visual con resaltado
- Actualización automática de contadores

### 2. Gestión de Tarjetas
- Creación de tarjetas con título, propietario y monto
- Edición inline básica
- Eliminación de tarjetas

### 3. Contadores por Etapa
- Actualización automática al mover tarjetas
- Visualización clara del número de leads por etapa

### 4. Creación de Leads
- Botón global "Nuevo lead"
- Botones por etapa para agregar leads específicos
- Valores por defecto configurables

## JavaScript Principal

### Variables Globales
```javascript
const board = document.getElementById('dash-funnel-board');
const tpl = document.getElementById('dash-funnel-card-tpl');
const addGlobal = document.getElementById('dash-funnel-add');
```

### Funciones Clave

#### `createCard(options)`
Crea una nueva tarjeta de lead con opciones personalizables.

```javascript
function createCard({title='Nuevo lead', owner='Sin asignar', amount='S/ 0'}={}) {
  const node = tpl.content.firstElementChild.cloneNode(true);
  // Configuración de la tarjeta
  return node;
}
```

#### `updateCounts()`
Actualiza los contadores de cada etapa del embudo.

```javascript
function updateCounts(){
  document.querySelectorAll('.dash-stage').forEach(stage=>{
    const count = stage.querySelectorAll('.dash-card').length;
    const badge = stage.querySelector('.dash-count');
    if(badge) badge.textContent = count;
  });
}
```

### Eventos Drag & Drop

- **`dragstart`**: Inicia el arrastre, marca la tarjeta como arrastrada
- **`dragover`**: Permite soltar en zonas válidas
- **`drop`**: Maneja la lógica de soltar tarjetas
- **`dragend`**: Limpia el estado de arrastre

## Estructura de Tarjetas

Cada tarjeta contiene:
- **Header**: Título del lead y monto
- **Contenido**: Propietario del lead
- **Atributos**: `draggable="true"`, `role="article"`

## Template de Tarjeta

```html
<template id="dash-funnel-card-tpl">
  <article class="dash-card select-none rounded-xl ring-1 ring-[var(--c-border)] bg-[var(--c-surface)] p-3 shadow-soft cursor-move" draggable="true" role="article" aria-grabbed="false">
    <header class="flex items-center justify-between gap-2">
      <h4 class="text-sm font-semibold">Nuevo lead</h4>
      <span class="text-[10px] px-2 py-0.5 rounded-lg bg-[var(--c-elev)] ring-1 ring-[var(--c-border)]">S/ 0</span>
    </header>
    <p class="mt-1 text-xs text-[var(--c-muted)]">Sin asignar</p>
  </article>
</template>
```

## Estilos CSS

Utiliza variables CSS del sistema de temas:
- `--c-border`: Color de bordes
- `--c-surface`: Color de fondo de tarjetas
- `--c-elev`: Color de elementos elevados
- `--c-muted`: Color de texto secundario

## Clases CSS Personalizadas

- `.dash-card`: Estilos de las tarjetas de leads
- `.dash-stage`: Contenedores de cada etapa
- `.dash-dropzone`: Áreas donde se pueden soltar tarjetas
- `.dash-count`: Badges de contador

## Funcionalidades Avanzadas

### Inicialización
Al cargar la página, se crean tarjetas de ejemplo en la etapa de Prospección:

```javascript
const firstZone = document.getElementById('dash-stage-prospeccion');
['Acme S.A.', 'Globex', 'Soylent Corp.', 'Initech'].forEach((n,i)=>{
  firstZone.appendChild(createCard({title: n, owner: 'Sin asignar', amount: 'S/ ' + (i*500+500)}));
});
```

### Delegación de Eventos
Usa event delegation para manejar clicks en botones dinámicos:

```javascript
board.addEventListener('click', (e)=>{
  if(e.target.classList.contains('dash-add')){
    // Lógica para agregar tarjeta
  }
});
```

## API y Backend

Esta vista es principalmente frontend. Para persistir los datos, necesitaría:

1. **API Endpoints** para leads
2. **Base de datos** para almacenar estado del embudo
3. **WebSockets** para sincronización en tiempo real

## Consideraciones de Rendimiento

- **Virtual Scrolling**: Para embudos con muchos leads
- **Lazy Loading**: Cargar tarjetas bajo demanda
- **Debouncing**: Para actualizaciones de contadores

## Accesibilidad

- Atributos ARIA (`role="article"`, `aria-grabbed`)
- Navegación por teclado para drag & drop
- Contraste adecuado con el sistema de temas

## Personalización

### Agregar Nuevas Etapas
```javascript
<section class="dash-stage" data-stage="nueva-etapa">
  <header>...</header>
  <div id="dash-stage-nueva-etapa" class="dash-dropzone">...</div>
</section>
```

### Modificar Tarjetas
Editar el template y la función `createCard()` para agregar campos adicionales como fecha, prioridad, etc.

## Próximas Mejoras

1. **Persistencia**: Guardar estado en base de datos
2. **Colaboración**: Múltiples usuarios editando simultáneamente
3. **Filtros**: Filtrar leads por criterios
4. **Exportación**: Exportar datos del embudo
5. **Analytics**: Métricas y reportes del embudo