# API de Monedas

## GET /api/currencies

Lista todas las monedas con paginación y filtros.

### Headers
```
Authorization: Bearer {token}
```

### Query Parameters
- `page`: Número de página (default: 1)
- `per_page`: Elementos por página (1-100, default: 15)
- `search`: Buscar en nombre y código
- `sort`: Campo de ordenamiento (created_at, updated_at, name, code, exchange_rate)
- `order`: Dirección del orden (asc, desc)

### Respuesta Exitosa (200)
```json
{
  "success": true,
  "message": "Listado de monedas",
  "code": "CURRENCIES_LIST",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "name": "Dólar Estadounidense",
        "code": "USD",
        "symbol": "$",
        "exchange_rate": "1.00",
        "is_base": true,
        "created_at": "2025-10-29T17:44:25.000000Z",
        "updated_at": "2025-10-29T17:44:25.000000Z"
      },
      {
        "id": 2,
        "name": "Euro",
        "code": "EUR",
        "symbol": "€",
        "exchange_rate": "0.85",
        "is_base": false,
        "created_at": "2025-10-29T17:44:25.000000Z",
        "updated_at": "2025-10-29T17:44:25.000000Z"
      }
    ],
    "per_page": 15,
    "total": 2
  }
}
```

## POST /api/currencies

Crea una nueva moneda.

### Headers
```
Authorization: Bearer {token}
Content-Type: application/json
```

### Body
```json
{
  "name": "Peso Mexicano",
  "code": "MXN",
  "symbol": "$",
  "exchange_rate": 0.050,
  "is_base": false
}
```

### Validación
- `name`: Requerido, máximo 255 caracteres
- `code`: Requerido, exactamente 3 caracteres, único
- `symbol`: Requerido, máximo 10 caracteres
- `exchange_rate`: Requerido, numérico, 0-999999.99
- `is_base`: Booleano opcional (default: false)

### Lógica Especial
- Si `is_base` es true, automáticamente se quita la marca de base de otras monedas
- Los `exchange_rate` se redondean a 2 decimales

### Respuesta Exitosa (201)
```json
{
  "success": true,
  "message": "Moneda creada exitosamente",
  "code": "CURRENCY_CREATED",
  "data": {
    "id": 3,
    "name": "Peso Mexicano",
    "code": "MXN",
    "symbol": "$",
    "exchange_rate": "0.05",
    "is_base": false,
    "created_at": "2025-10-29T17:44:25.000000Z",
    "updated_at": "2025-10-29T17:44:25.000000Z"
  }
}
```

## GET /api/currencies/{id}

Obtiene una moneda específica.

### Headers
```
Authorization: Bearer {token}
```

### Respuesta Exitosa (200)
```json
{
  "success": true,
  "message": "Moneda obtenida",
  "code": "CURRENCY_SHOWN",
  "data": {
    "id": 1,
    "name": "Dólar Estadounidense",
    "code": "USD",
    "symbol": "$",
    "exchange_rate": "1.00",
    "is_base": true
  }
}
```

## PATCH /api/currencies/{id}

Actualiza una moneda.

### Headers
```
Authorization: Bearer {token}
Content-Type: application/json
```

### Body
```json
{
  "name": "Dólar Americano",
  "exchange_rate": 1.05,
  "is_base": true
}
```

### Validación
- `name`: Opcional, máximo 255 caracteres
- `code`: Opcional, exactamente 3 caracteres, único (ignorando la moneda actual)
- `symbol`: Opcional, máximo 10 caracteres
- `exchange_rate`: Opcional, numérico, 0-999999.99
- `is_base`: Booleano opcional

### Lógica Especial
- Si se marca como base, se quita la marca de otras monedas
- Los `exchange_rate` se redondean a 2 decimales

### Respuesta Exitosa (200)
```json
{
  "success": true,
  "message": "Moneda actualizada",
  "code": "CURRENCY_UPDATED",
  "data": { ... }
}
```

## DELETE /api/currencies/{id}

Elimina una moneda.

### Headers
```
Authorization: Bearer {token}
```

### Respuesta Exitosa (200)
```json
{
  "success": true,
  "message": "Moneda eliminada",
  "code": "CURRENCY_DELETED",
  "data": null
}
```

### Restricciones
- No se puede eliminar la moneda base (`is_base: true`)

### Notas
- Los tipos de cambio se muestran con 2 decimales en las respuestas
- Solo puede haber una moneda base en el sistema
- Las monedas se usan para conversiones monetarias en la aplicación