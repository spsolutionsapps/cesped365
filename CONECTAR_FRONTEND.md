# 🔗 Guía para Conectar Frontend con Backend

## ✅ Lo que ya se hizo:

1. ✅ Actualizado `src/services/api.js` con llamadas reales al backend
2. ✅ Actualizado `src/stores/auth.js` para usar autenticación real
3. ✅ Actualizado `src/pages/Login.svelte` para login async
4. ✅ Iniciado actualización de `src/pages/dashboard/Resumen.svelte`

---

## 🔧 **Solución Rápida** (Para ver datos YA):

### Paso 1: Asegúrate de que ambos servidores estén corriendo

**Terminal 1 - Backend:**
```bash
cd api
php spark serve
```
✅ Backend en: http://localhost:8080

**Terminal 2 - Frontend:**
```bash
npm run dev
```
✅ Frontend en: http://localhost:5173

### Paso 2: Prueba el login

1. Abre http://localhost:5173
2. Click en "Iniciar Sesión"
3. Usa credenciales:
   - **Admin**: admin@cesped365.com / admin123
   - **Cliente**: cliente@example.com / cliente123

### Paso 3: Verifica en consola

Abre las **DevTools** del navegador (F12) y mira:
- ✅ La consola NO debe mostrar errores de CORS
- ✅ En la pestaña **Network** deberías ver:
  - `POST http://localhost:8080/api/login` (200 OK)
  - `GET http://localhost:8080/api/dashboard` (200 OK)

---

## 🐛 **Si ves errores:**

### Error: "CORS policy"
**Problema**: El backend no permite peticiones del frontend

**Solución**: El backend ya tiene CORS configurado, pero verifica:
```bash
cd api
# Verifica que CorsFilter esté activo
grep -r "CorsFilter" app/Config/Filters.php
```

### Error: "Failed to fetch"
**Problema**: El backend no está corriendo

**Solución**: 
```bash
cd api
php spark serve
```

### Error: "404 Not Found"
**Problema**: La ruta no existe

**Solución**: Verifica las rutas:
```bash
cd api
php spark routes
```

---

## 📝 **Archivos pendientes de actualizar:**

Para que TODO el dashboard muestre datos reales, actualiza estos archivos:

### 1. `src/pages/dashboard/Resumen.svelte`
Reemplaza:
```javascript
import { mockReportes, mockHistorial, mockEstadisticas } from '../../stores/mockData';
```
Por:
```javascript
import { dashboardAPI, reportesAPI, historialAPI } from '../../services/api';
```

### 2. `src/pages/dashboard/Reportes.svelte`
Usa:
```javascript
import { reportesAPI } from '../../services/api';

onMount(async () => {
  const response = await reportesAPI.getAll();
  reportes = response.data;
});
```

### 3. `src/pages/dashboard/Historial.svelte`
Usa:
```javascript
import { historialAPI } from '../../services/api';

onMount(async () => {
  const response = await historialAPI.getHistorial();
  historial = response.data;
});
```

### 4. `src/pages/dashboard/admin/Clientes.svelte`
Usa:
```javascript
import { clientesAPI } from '../../services/api';

onMount(async () => {
  const response = await clientesAPI.getAll();
  clientes = response.data;
});
```

---

## ✨ **Formato de Respuesta del Backend:**

Todas las respuestas del backend tienen este formato:

```json
{
  "success": true,
  "data": { /* datos aquí */ },
  "message": "Opcional"
}
```

Por ejemplo, `/api/dashboard` devuelve:
```json
{
  "success": true,
  "data": {
    "estadisticas": {
      "totalClientes": 4,
      "clientesActivos": 4,
      "visitasEsteMes": 12,
      "reportesTotales": 4
    }
  }
}
```

---

## 🎯 **Testing Rápido:**

```bash
# Desde la carpeta api/
php test_auth.php        # Probar autenticación
php test_endpoints.php   # Probar todos los endpoints
```

---

## 💡 **Tip:**

Si quieres ver los datos sin actualizar el frontend completo, puedes:

1. Abrir la consola del navegador (F12)
2. Pegar este código:

```javascript
// Test login
fetch('http://localhost:8080/api/login', {
  method: 'POST',
  credentials: 'include',
  headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
  body: new URLSearchParams({
    email: 'admin@cesped365.com',
    password: 'admin123'
  })
})
.then(r => r.json())
.then(d => console.log('Login:', d));

// Test dashboard (después del login)
fetch('http://localhost:8080/api/dashboard', {
  credentials: 'include'
})
.then(r => r.json())
.then(d => console.log('Dashboard:', d));
```

---

**¿Necesitas ayuda con algo específico?** Dime qué página quieres que actualice primero.
