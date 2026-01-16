# Guía de Integración con Backend (CodeIgniter 4)

Este documento describe cómo integrar el frontend de Svelte con el backend de CodeIgniter 4.

## 📋 Estructura de API Esperada

### Autenticación

#### POST /api/auth/login
```json
// Request
{
  "email": "usuario@example.com",
  "password": "password123"
}

// Response
{
  "success": true,
  "token": "jwt_token_here",
  "user": {
    "id": 1,
    "name": "Juan Pérez",
    "email": "usuario@example.com",
    "role": "cliente" // o "admin"
  }
}
```

#### POST /api/auth/logout
```json
// Headers: Authorization: Bearer {token}

// Response
{
  "success": true,
  "message": "Sesión cerrada correctamente"
}
```

#### GET /api/auth/me
```json
// Headers: Authorization: Bearer {token}

// Response
{
  "success": true,
  "user": {
    "id": 1,
    "name": "Juan Pérez",
    "email": "usuario@example.com",
    "role": "cliente",
    "phone": "+54 11 1234-5678",
    "address": "Av. Siempre Viva 123"
  }
}
```

### Reportes

#### GET /api/reportes
```json
// Headers: Authorization: Bearer {token}
// Query params: ?page=1&limit=10

// Response
{
  "success": true,
  "data": [
    {
      "id": 1,
      "fecha": "2026-01-10",
      "estadoGeneral": "Bueno",
      "cespedParejo": true,
      "colorOk": true,
      "manchas": false,
      "zonasDesgastadas": false,
      "malezasVisibles": false,
      "crecimientoCm": 2.5,
      "notaJardinero": "El césped está en excelente estado...",
      "jardinero": "Carlos Rodríguez",
      "imagenes": [
        "https://api.cesped365.com/uploads/reportes/1/img1.jpg",
        "https://api.cesped365.com/uploads/reportes/1/img2.jpg"
      ]
    }
  ],
  "pagination": {
    "page": 1,
    "limit": 10,
    "total": 25,
    "pages": 3
  }
}
```

#### GET /api/reportes/:id
```json
// Headers: Authorization: Bearer {token}

// Response
{
  "success": true,
  "data": {
    "id": 1,
    "fecha": "2026-01-10",
    "estadoGeneral": "Bueno",
    // ... resto de campos
  }
}
```

#### POST /api/reportes (Admin only)
```json
// Headers: Authorization: Bearer {token}
// Content-Type: multipart/form-data

// Request (FormData)
{
  "clienteId": 2,
  "fecha": "2026-01-15",
  "estadoGeneral": "Bueno",
  "cespedParejo": true,
  "colorOk": true,
  "manchas": false,
  "zonasDesgastadas": false,
  "malezasVisibles": false,
  "crecimientoCm": 2.8,
  "notaJardinero": "Mantenimiento regular realizado...",
  "imagenes[]": [File, File] // Archivos de imagen
}

// Response
{
  "success": true,
  "message": "Reporte creado correctamente",
  "data": {
    "id": 10,
    // ... datos del reporte creado
  }
}
```

### Clientes (Admin only)

#### GET /api/clientes
```json
// Headers: Authorization: Bearer {token}
// Query params: ?search=juan&plan=premium&estado=activo

// Response
{
  "success": true,
  "data": [
    {
      "id": 1,
      "nombre": "Juan Pérez",
      "email": "juan.perez@example.com",
      "telefono": "+54 11 1234-5678",
      "direccion": "Av. Siempre Viva 123",
      "plan": "Premium",
      "estado": "Activo",
      "ultimaVisita": "2026-01-10",
      "proximaVisita": "2026-01-17"
    }
  ]
}
```

#### GET /api/clientes/:id
```json
// Headers: Authorization: Bearer {token}

// Response
{
  "success": true,
  "data": {
    "id": 1,
    "nombre": "Juan Pérez",
    // ... resto de campos
    "suscripcion": {
      "plan": "Premium",
      "estado": "Activo",
      "fechaInicio": "2025-06-01",
      "proximoPago": "2026-02-01"
    }
  }
}
```

#### PUT /api/clientes/:id
```json
// Headers: Authorization: Bearer {token}

// Request
{
  "nombre": "Juan Pérez",
  "telefono": "+54 11 1234-5678",
  "direccion": "Nueva Dirección 456"
}

// Response
{
  "success": true,
  "message": "Cliente actualizado correctamente",
  "data": {
    // ... datos actualizados
  }
}
```

### Historial

#### GET /api/historial
```json
// Headers: Authorization: Bearer {token}

// Response
{
  "success": true,
  "data": [
    {
      "id": 1,
      "fecha": "2026-01-10",
      "tipo": "Mantenimiento Regular",
      "estadoGeneral": "Bueno",
      "jardinero": "Carlos Rodríguez",
      "reporteId": 1
    }
  ]
}
```

### Suscripciones

#### GET /api/suscripciones/mi-suscripcion
```json
// Headers: Authorization: Bearer {token}

// Response
{
  "success": true,
  "data": {
    "plan": "Premium",
    "estado": "Activo",
    "fechaInicio": "2025-06-01",
    "proximoPago": "2026-02-01",
    "monto": 15000,
    "frecuencia": "Mensual"
  }
}
```

## 🔧 Pasos para Integración

### 1. Configurar Variables de Entorno

Crear archivo `.env` en la raíz del proyecto:

```env
VITE_API_URL=http://localhost:8080/api
VITE_ENV=development
```

Para producción:
```env
VITE_API_URL=https://api.cesped365.com/api
VITE_ENV=production
```

### 2. Actualizar el Store de Autenticación

En `src/stores/auth.js`, descomentar las llamadas a la API:

```javascript
login: async (email, password) => {
  try {
    const result = await authAPI.login(email, password);
    
    if (result.success) {
      // Guardar token
      localStorage.setItem('auth_token', result.token);
      
      set({
        isAuthenticated: true,
        user: result.user,
        role: result.user.role
      });
      
      return { success: true, role: result.user.role };
    }
  } catch (error) {
    return { success: false, error: error.message };
  }
}
```

### 3. Actualizar Funciones de API

En `src/services/api.js`, descomentar las llamadas fetch:

```javascript
export const reportesAPI = {
  getAll: async () => {
    return await request('/reportes');
  },
  // ... resto de funciones
};
```

### 4. Actualizar Componentes

Reemplazar datos mock por llamadas a la API:

**Antes (mock):**
```javascript
import { mockReportes } from '../../stores/mockData';
let reportes = mockReportes;
```

**Después (API):**
```javascript
import { reportesAPI } from '../../services/api';
import { onMount } from 'svelte';

let reportes = [];
let loading = true;

onMount(async () => {
  try {
    const response = await reportesAPI.getAll();
    reportes = response.data;
  } catch (error) {
    console.error('Error cargando reportes:', error);
  } finally {
    loading = false;
  }
});
```

### 5. Manejo de Errores

Agregar manejo de errores en todos los componentes:

```javascript
let error = null;

try {
  const response = await reportesAPI.getAll();
  reportes = response.data;
} catch (err) {
  error = err.message;
  // Mostrar notificación al usuario
}
```

### 6. Loading States

Agregar estados de carga:

```svelte
{#if loading}
  <div class="flex justify-center items-center py-12">
    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
  </div>
{:else if error}
  <div class="bg-red-50 text-red-700 p-4 rounded-lg">
    {error}
  </div>
{:else}
  <!-- Contenido normal -->
{/if}
```

## 🔐 Seguridad

### Tokens JWT

- Los tokens se guardan en `localStorage`
- Se envían en el header `Authorization: Bearer {token}`
- Implementar refresh token para sesiones largas
- Limpiar token al hacer logout

### CORS

Configurar CORS en CodeIgniter:

```php
// app/Config/Filters.php
public $globals = [
    'before' => [
        'cors',
    ],
];
```

### Validación

- Validar todos los inputs en el backend
- Sanitizar datos antes de guardar
- Usar prepared statements para SQL

## 📤 Subida de Archivos

Para subir imágenes de reportes:

```javascript
async function uploadReporte(formData) {
  const config = {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
      // NO incluir Content-Type, el browser lo agrega automáticamente con boundary
    },
    body: formData
  };
  
  const response = await fetch(`${API_BASE_URL}/reportes`, config);
  return await response.json();
}
```

## 🧪 Testing

Antes de integrar con el backend real, probar con:

1. **JSON Server**: Mock API rápido
2. **Postman**: Probar endpoints
3. **Thunder Client**: Extensión de VS Code

## 📝 Checklist de Integración

- [ ] Configurar variables de entorno
- [ ] Implementar autenticación real
- [ ] Reemplazar datos mock por API calls
- [ ] Agregar loading states
- [ ] Implementar manejo de errores
- [ ] Configurar CORS en backend
- [ ] Probar subida de archivos
- [ ] Implementar refresh token
- [ ] Agregar validación de formularios
- [ ] Testing end-to-end

## 🚀 Deployment

### Frontend (Netlify/Vercel)

```bash
npm run build
# Subir carpeta dist/
```

### Variables de entorno en producción

Configurar en el panel de Netlify/Vercel:
- `VITE_API_URL=https://api.cesped365.com/api`
- `VITE_ENV=production`

## 📞 Soporte

Para dudas sobre la integración, contactar al equipo de desarrollo.
