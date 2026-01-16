# 📖 Cómo Usar el Sistema Cesped365

Guía completa para usar el sistema de gestión de jardinería.

---

## 🚀 **Inicio Rápido**

### **1. Arrancar el Sistema**

**Terminal 1 - Backend:**
```bash
cd api
php spark serve
```

**Terminal 2 - Frontend:**
```bash
npm run dev
```

**Abrir navegador:**
```
http://localhost:5173
```

---

## 🔑 **Credenciales de Acceso**

### **Administrador**
- **Email**: admin@cesped365.com
- **Password**: admin123
- **Permisos**: Acceso completo al sistema

### **Cliente de Prueba**
- **Email**: cliente@example.com
- **Password**: cliente123
- **Permisos**: Ver su jardín y reportes

---

## 👨‍💼 **Guía para ADMIN**

### **1. Dashboard Principal** (`/dashboard/resumen`)

**Estadísticas que verás:**
- Total de clientes en el sistema
- Clientes activos con suscripción
- Visitas programadas este mes
- Total de reportes generados

**Acciones:**
- Ver últimas visitas programadas
- Acceso rápido a reportes recientes

---

### **2. Gestión de Clientes** (`/dashboard/clientes`)

**Funcionalidades:**
- ✅ Ver lista completa de clientes
- ✅ Buscar por nombre, email o dirección
- ✅ Filtrar por plan o estado
- ✅ Ver detalles de cada cliente

**Datos que verás por cliente:**
- Nombre y email
- Teléfono y dirección
- Plan de suscripción
- Estado (Activo/Inactivo)
- Última visita realizada
- Próxima visita programada

**Acciones disponibles:**
- 👁️ Ver detalles completos
- ✏️ Editar información
- 🗑️ Eliminar cliente

---

### **3. Reportes** (`/dashboard/reportes`)

**Ver todos los reportes del sistema:**
- Fecha del reporte
- Estado general del césped
- Jardinero que realizó la visita
- Observaciones técnicas

**Detalles técnicos incluidos:**
- ✅ Césped parejo
- ✅ Color saludable
- ⚠️ Manchas detectadas
- ⚠️ Zonas desgastadas
- ⚠️ Malezas visibles
- 📏 Crecimiento en cm
- 💧 Nivel de humedad
- 🦟 Plagas detectadas

---

### **4. Historial** (`/dashboard/historial`)

**Ver todas las visitas:**
- Tabla con todas las visitas realizadas
- Tipo de servicio (Mantenimiento/Tratamiento/Resembrado)
- Estado general del jardín
- Jardinero asignado
- Observaciones

**Estadísticas:**
- Total de visitas
- Visitas con estado "Bueno"
- Visitas que requieren atención

---

## 👤 **Guía para CLIENTE**

### **1. Dashboard Principal** (`/dashboard/resumen`)

**Información de tu jardín:**
- Estado actual del jardín
- Fecha de última visita
- Total de reportes recibidos
- Próximas visitas programadas

**Ver último reporte:**
- Estado general
- Detalles técnicos del césped
- Observaciones del jardinero

---

### **2. Reportes** (`/dashboard/reportes`)

**Ver todos tus reportes:**
- Histórico completo de visitas
- Fotos de cada visita
- Detalles técnicos del estado del césped
- Recomendaciones del jardinero

**Información técnica:**
- Estado general (Bueno/Regular/Malo)
- Condiciones del césped
- Crecimiento y salud
- Problemas detectados

---

### **3. Historial** (`/dashboard/historial`)

**Tabla completa de visitas:**
- Todas las visitas realizadas
- Tipo de mantenimiento aplicado
- Estado después de la visita
- Fecha y jardinero

---

### **4. Perfil** (`/dashboard/perfil`)

**Tu información:**
- Nombre y email
- Teléfono y dirección
- Información de tu jardín

**Tu suscripción:**
- Plan contratado (Básico/Premium/Trimestral/Anual)
- Estado de la suscripción
- Fecha de inicio
- Próxima fecha de pago
- Monto y frecuencia
- Visitas incluidas por mes

---

## 💼 **Flujo de Trabajo Típico**

### **Como Admin:**

1. **Inicio del día:**
   - Login → Ver dashboard
   - Revisar visitas programadas del día
   - Ver estadísticas generales

2. **Gestión de clientes:**
   - Ir a "Clientes"
   - Buscar cliente específico
   - Ver su historial de visitas
   - Actualizar información si es necesario

3. **Después de una visita:**
   - Crear nuevo reporte
   - Subir fotos del jardín
   - Agregar observaciones

4. **Fin del día:**
   - Revisar reportes del día
   - Programar próximas visitas

---

### **Como Cliente:**

1. **Consulta regular:**
   - Login → Ver estado de tu jardín
   - Revisar último reporte recibido
   - Ver próxima visita programada

2. **Ver historial:**
   - Ir a "Reportes" para ver todas las visitas
   - Ver fotos de cada visita
   - Leer observaciones del jardinero

3. **Gestión de cuenta:**
   - Ir a "Perfil"
   - Ver detalles de suscripción
   - Verificar próximo pago

---

## 📸 **Imágenes de Reportes**

**Dónde se guardan:**
```
api/public/uploads/reportes/
```

**Formatos aceptados:**
- JPG / JPEG
- PNG
- Máximo 2MB por imagen

**Acceso:**
```
http://localhost:8080/uploads/reportes/nombre_archivo.jpg
```

---

## 🔐 **Seguridad**

### **Sesiones:**
- Duración: 2 horas
- Renovación automática con actividad
- Cookie HttpOnly (no accesible desde JS)

### **Permisos:**
- **Admin**: Acceso completo
- **Cliente**: Solo sus propios datos

### **Endpoints protegidos:**
- Todos requieren autenticación
- Admin-only están bloqueados para clientes
- 401 si no hay sesión
- 403 si no tienes permisos

---

## 📊 **Estructura de Datos**

### **Usuario**
```json
{
  "id": 2,
  "name": "Juan Pérez",
  "email": "cliente@example.com",
  "role": "cliente",
  "phone": "+54 11 1234-5678",
  "address": "Av. Siempre Viva 123"
}
```

### **Reporte**
```json
{
  "id": 1,
  "fecha": "2026-01-10",
  "estadoGeneral": "Bueno",
  "cespedParejo": true,
  "colorOk": true,
  "manchas": false,
  "crecimientoCm": 2.5,
  "jardinero": "Carlos Rodríguez",
  "notaJardinero": "Césped en excelente estado...",
  "imagenes": ["http://localhost:8080/uploads/reportes/img.jpg"]
}
```

### **Suscripción**
```json
{
  "id": 1,
  "planName": "Plan Premium",
  "price": 28000.0,
  "frequency": "mensual",
  "status": "activa",
  "nextBillingDate": "2026-02-14"
}
```

---

## 🎯 **Próximos Pasos (Opcional)**

1. **Integrar Mercado Pago**
   - Pagos automáticos
   - Webhooks para renovación

2. **Notificaciones**
   - Email cuando hay nuevo reporte
   - WhatsApp para recordatorios

3. **Calendario**
   - Vista de calendario para visitas
   - Programación inteligente

4. **Reportes PDF**
   - Descargar reportes en PDF
   - Enviar por email

---

## 📞 **Soporte**

Si tienes problemas:

1. Verifica que ambos servidores estén corriendo
2. Revisa la consola del navegador (F12)
3. Verifica los logs del backend: `api/writable/logs/`
4. Ejecuta los tests: `cd api && php test_auth.php`

---

**¡Disfruta usando Cesped365!** 🌱
