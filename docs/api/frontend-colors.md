# API de Colores del Frontend

Este documento describe la API para administrar los colores del frontend público de San Miguel Properties.

## Descripción General

El sistema de colores del frontend permite modificar dinámicamente los colores utilizados en la página pública (home, header, footer y componentes públicos) desde el panel de administración. Los colores son **globales** y afectan a todo el sitio público.

## Autenticación

- **Rutas públicas**: No requieren autenticación (CSS, colores activos, valores por defecto)
- **Rutas de administración**: Requieren Bearer Token via Passport (`auth.api` + `admin.api`)

## Endpoints

### Rutas Públicas (Sin autenticación)

#### Obtener CSS dinámico
```http
GET /api/frontend-colors/css
```

**Response** (Content-Type: text/css):
```css
:root {
  --fe-primary-from: #4f46e5;
  --fe-primary-to: #10b981;
  /* ... más variables */
}
```

#### Obtener configuración activa
```http
GET /api/frontend-colors/active
```

**Response**:
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Default",
    "description": "Configuración por defecto",
    "colors": { /* objeto de colores */ },
    "is_active": true,
    "created_at": "2026-01-09T12:00:00.000000Z",
    "updated_at": "2026-01-09T12:00:00.000000Z"
  },
  "message": "Configuración activa obtenida correctamente"
}
```

#### Obtener colores por defecto
```http
GET /api/frontend-colors/defaults
```

**Response**:
```json
{
  "success": true,
  "data": {
    "primary": { "from": "#4f46e5", "to": "#10b981" },
    "hero": { /* colores hero */ },
    /* ... más grupos */
  },
  "message": "Colores por defecto obtenidos correctamente"
}
```

### Rutas Protegidas (Requieren autenticación)

#### Listar todas las configuraciones
```http
GET /api/frontend-colors
Authorization: Bearer {token}
```

#### Crear nueva configuración
```http
POST /api/frontend-colors
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Tema Navideño",
  "description": "Colores para temporada navideña",
  "colors": { /* opcional, usa defaults si no se proporciona */ }
}
```

#### Obtener configuración específica
```http
GET /api/frontend-colors/{id}
Authorization: Bearer {token}
```

#### Actualizar configuración
```http
PUT /api/frontend-colors/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Nuevo nombre",
  "colors": {
    "primary": {
      "from": "#ff0000",
      "to": "#00ff00"
    }
  }
}
```

#### Eliminar configuración
```http
DELETE /api/frontend-colors/{id}
Authorization: Bearer {token}
```

**Nota**: No se puede eliminar una configuración activa.

#### Activar configuración
```http
POST /api/frontend-colors/{id}/activate
Authorization: Bearer {token}
```

#### Restablecer a valores por defecto
```http
POST /api/frontend-colors/{id}/reset-defaults
Authorization: Bearer {token}
```

#### Duplicar configuración
```http
POST /api/frontend-colors/{id}/duplicate
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Copia de Default"
}
```

#### Obtener grupos disponibles
```http
GET /api/frontend-colors/groups
Authorization: Bearer {token}
```

**Response**:
```json
{
  "success": true,
  "data": {
    "primary": "Colores Primarios",
    "hero": "Sección Hero",
    "stats": "Estadísticas",
    /* ... */
  }
}
```

#### Exportar configuración
```http
GET /api/frontend-colors/{id}/export
Authorization: Bearer {token}
```

#### Importar configuración
```http
POST /api/frontend-colors/import
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Tema Importado",
  "description": "Importado desde backup",
  "colors": { /* estructura completa de colores */ }
}
```

---

## Estructura de Colores

### Grupos Principales

| Grupo | Descripción |
|-------|-------------|
| `primary` | Colores primarios (gradientes principales) |
| `hero` | Sección hero/slider |
| `stats` | Barra de estadísticas |
| `features` | Sección de características/servicios |
| `cta_sale` | Call-to-action de venta |
| `cta_rent` | Call-to-action de renta |
| `process` | Sección de proceso de compra |
| `testimonials` | Sección de testimonios |
| `about` | Sección "Sobre nosotros" |
| `contact` | Formulario de contacto |
| `footer` | Footer |
| `header` | Header/navegación |
| `property_cards` | Tarjetas de propiedades |
| `filters` | Filtros de búsqueda |
| `pagination` | Paginación |
| `ui` | Elementos UI generales |

### Detalle de Variables por Grupo

#### `primary`
| Variable | Descripción | Default |
|----------|-------------|---------|
| `from` | Color inicial del gradiente | `#4f46e5` |
| `to` | Color final del gradiente | `#10b981` |

#### `hero`
| Variable | Descripción | Default |
|----------|-------------|---------|
| `title_gradient_from` | Inicio gradiente título | `#818cf8` |
| `title_gradient_via` | Color medio gradiente | `#c084fc` |
| `title_gradient_to` | Fin gradiente título | `#34d399` |
| `overlay_from` | Color overlay inicial | `rgba(0,0,0,0.6)` |
| `overlay_via` | Color overlay medio | `rgba(0,0,0,0.4)` |
| `overlay_to` | Color overlay final | `rgba(0,0,0,0.7)` |
| `badge_bg` | Fondo del badge | `rgba(255,255,255,0.1)` |
| `badge_dot` | Color del punto animado | `#10b981` |
| `search_bar_bg` | Fondo barra búsqueda | `rgba(255,255,255,0.1)` |
| `search_border` | Borde barra búsqueda | `rgba(255,255,255,0.2)` |
| `search_focus_border` | Borde focus búsqueda | `#818cf8` |
| `quick_filter_bg` | Fondo filtros rápidos | `rgba(255,255,255,0.1)` |
| `quick_filter_border` | Borde filtros rápidos | `rgba(255,255,255,0.1)` |

#### `stats`
| Variable | Descripción | Default |
|----------|-------------|---------|
| `properties_from` | Gradiente propiedades inicio | `#4f46e5` |
| `properties_to` | Gradiente propiedades fin | `#818cf8` |
| `experience_from` | Gradiente experiencia inicio | `#059669` |
| `experience_to` | Gradiente experiencia fin | `#34d399` |
| `clients_from` | Gradiente clientes inicio | `#9333ea` |
| `clients_to` | Gradiente clientes fin | `#c084fc` |
| `zones_from` | Gradiente zonas inicio | `#d97706` |
| `zones_to` | Gradiente zonas fin | `#fbbf24` |

#### `features`
| Variable | Descripción | Default |
|----------|-------------|---------|
| `search_from` | Búsqueda - inicio | `#4f46e5` |
| `search_to` | Búsqueda - fin | `#818cf8` |
| `search_hover_border` | Búsqueda - borde hover | `#c7d2fe` |
| `security_from` | Seguridad - inicio | `#059669` |
| `security_to` | Seguridad - fin | `#34d399` |
| `security_hover_border` | Seguridad - borde hover | `#a7f3d0` |
| `tours_from` | Tours - inicio | `#9333ea` |
| `tours_to` | Tours - fin | `#c084fc` |
| `tours_hover_border` | Tours - borde hover | `#e9d5ff` |
| `advisors_from` | Asesores - inicio | `#f59e0b` |
| `advisors_to` | Asesores - fin | `#fbbf24` |
| `advisors_hover_border` | Asesores - borde hover | `#fde68a` |
| `financing_from` | Financiamiento - inicio | `#f43f5e` |
| `financing_to` | Financiamiento - fin | `#fb7185` |
| `financing_hover_border` | Financiamiento - borde hover | `#fecdd3` |
| `app_from` | App - inicio | `#06b6d4` |
| `app_to` | App - fin | `#22d3ee` |
| `app_hover_border` | App - borde hover | `#a5f3fc` |

#### `property_cards`
| Variable | Descripción | Default |
|----------|-------------|---------|
| `price_from` | Precio gradiente inicio | `#4f46e5` |
| `price_to` | Precio gradiente fin | `#10b981` |
| `sale_badge` | Badge "En Venta" | `#10b981` |
| `rent_badge` | Badge "En Renta" | `#f59e0b` |
| `favorite_hover` | Color favorito hover | `#f43f5e` |
| `title_hover` | Color título hover | `#4f46e5` |

#### `ui`
| Variable | Descripción | Default |
|----------|-------------|---------|
| `back_to_top_from` | Botón volver arriba inicio | `#4f46e5` |
| `back_to_top_to` | Botón volver arriba fin | `#10b981` |
| `preloader_border_1` | Preloader color 1 | `#4f46e5` |
| `preloader_border_2` | Preloader color 2 | `#10b981` |
| `scrollbar_from` | Scrollbar inicio | `#6366f1` |
| `scrollbar_to` | Scrollbar fin | `#10b981` |
| `scrollbar_hover_from` | Scrollbar hover inicio | `#4f46e5` |
| `scrollbar_hover_to` | Scrollbar hover fin | `#059669` |

---

## Variables CSS Generadas

Las variables CSS se generan automáticamente con el prefijo `--fe-` seguido del path del color:

```css
/* Ejemplo de variables generadas */
:root {
  --fe-primary-from: #4f46e5;
  --fe-primary-to: #10b981;
  --fe-hero-title_gradient_from: #818cf8;
  --fe-hero-title_gradient_via: #c084fc;
  --fe-hero-title_gradient_to: #34d399;
  --fe-stats-properties_from: #4f46e5;
  --fe-stats-properties_to: #818cf8;
  /* ... */
}
```

## Uso en Blade/CSS

```css
/* Usar variables en CSS */
.mi-elemento {
  background: linear-gradient(to right, var(--fe-primary-from), var(--fe-primary-to));
}

.mi-texto {
  color: var(--fe-property_cards-price_from);
}
```

```html
<!-- Usar clases de utilidad predefinidas -->
<button class="bg-fe-gradient-primary">Botón Primario</button>
<span class="text-fe-price-gradient">$1,500,000</span>
<span class="bg-fe-sale-badge">En Venta</span>
<span class="bg-fe-rent-badge">En Renta</span>
```

---

## Notas de Implementación

1. **Cache**: Los colores activos se cachean por 1 hora. Al actualizar colores, el cache se limpia automáticamente.

2. **Fallbacks**: Todas las variables CSS incluyen valores por defecto como fallback para compatibilidad.

3. **Independencia**: Este sistema es completamente independiente del sistema de `ColorTheme` usado para el dashboard de administración.

4. **Sin Breaking Changes**: La implementación usa valores por defecto que coinciden con el diseño original.
