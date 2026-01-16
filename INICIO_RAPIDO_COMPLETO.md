# 🚀 Inicio Rápido - Cesped365

Guía para arrancar el proyecto completo (Frontend + Backend)

---

## ✅ Requisitos Previos

- ✅ Node.js 16+ instalado
- ✅ PHP 8.0+ instalado
- ✅ MySQL corriendo (XAMPP o similar)
- ✅ Composer instalado (opcional, backend ya tiene vendor/)

---

## 🎯 Arrancar Todo el Sistema

### 1️⃣ Backend (Terminal 1)

```bash
# Desde la raíz del proyecto
cd api

# Arrancar servidor CodeIgniter
php spark serve
```

**✅ Backend disponible en: http://localhost:8080**

### 2️⃣ Frontend (Terminal 2)

```bash
# Desde la raíz del proyecto (no dentro de api/)
npm install

# Arrancar servidor Vite
npm run dev
```

**✅ Frontend disponible en: http://localhost:5173**

---

## 🧪 Probar que Funciona

### Opción 1: Navegador
1. Abre http://localhost:5173
2. Click en "Iniciar Sesión"
3. Usa las credenciales:
   - **Admin**: admin@cesped365.com / admin123
   - **Cliente**: cliente@example.com / cliente123

### Opción 2: Tests Automatizados
```bash
cd api

# Test de autenticación
php test_auth.php

# Test del panel admin
php test_admin_panel.php

# Test de suscripciones
php test_subscriptions.php
```

---

## 📊 Base de Datos

### ¿Las tablas ya están creadas?
✅ **Sí**, si seguiste las Fases 1-5.

### ¿Necesitas recrearlas?
```bash
cd api

# Verificar conexión (opcional)
php quick_check.php

# Poblar datos de prueba
php spark db:seed SubscriptionSeeder
php spark db:seed UserSubscriptionSeeder
```

---

## 🔑 Credenciales de Prueba

### Admin
- **Email**: admin@cesped365.com
- **Password**: admin123
- **Permisos**: Acceso total (clientes, reportes, suscripciones)

### Cliente
- **Email**: cliente@example.com
- **Password**: cliente123
- **Permisos**: Ver su jardín, reportes e historial

---

## 📁 Estructura de Carpetas

```
cesped365/
├── src/              ← Frontend (Svelte)
├── api/              ← Backend (CodeIgniter)
├── public/           ← Assets frontend
├── package.json      ← Deps frontend
└── README.md
```

---

## 🛑 Detener los Servidores

**Backend**: `Ctrl + C` en la terminal del backend  
**Frontend**: `Ctrl + C` en la terminal del frontend

---

## 🐛 Problemas Comunes

### ❌ Error: "Class 'Locale' not found"
**Solución**: Habilita la extensión `intl` en `php.ini`:
```ini
extension=intl
```

### ❌ Error: "Can't connect to MySQL"
**Solución**: 
1. Verifica que MySQL esté corriendo en XAMPP
2. Verifica credenciales en `api/.env` (o `api/app/Config/Database.php`)

### ❌ Error: Puerto 8080 en uso
**Solución**: Usa otro puerto:
```bash
php spark serve --port=8000
```

### ❌ Frontend no carga
**Solución**: 
```bash
# Limpia e instala
rm -rf node_modules
npm install
npm run dev
```

---

## 📝 Próximos Pasos

1. **Conectar Frontend con Backend**
   - Actualmente el frontend usa mock data
   - Actualizar `src/services/api.js` para usar endpoints reales

2. **Deploy**
   - Frontend: Vercel, Netlify
   - Backend: VPS con Apache/Nginx

3. **Integrar Mercado Pago**
   - Ver `api/FASE5_COMPLETADA.md` para instrucciones

---

## 📚 Documentación Adicional

- **Backend Fases**: Ver carpeta `api/FASE*_COMPLETADA.md`
- **API Endpoints**: Ver `api/README_BACKEND.md`
- **Frontend**: Ver `README.md` en la raíz

---

**¡Todo listo! 🎉**

El sistema está funcionando con:
- ✅ Backend en http://localhost:8080
- ✅ Frontend en http://localhost:5173
- ✅ Base de datos con datos de prueba
- ✅ Sistema de autenticación funcional
