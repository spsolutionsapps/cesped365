# Cesped365 - Sistema de Gestión de Jardinería

Sistema web completo para gestión de servicios de jardinería por suscripción, con dashboard para clientes y administradores.

## 🚀 DESPLIEGUE A PRODUCCIÓN

### ¿Acabas de subir el sitio?
**Empieza aquí:** [`INICIO_RAPIDO_PRODUCCION.md`](./INICIO_RAPIDO_PRODUCCION.md) ⭐

Te tomará 30 minutos configurar:
- ✅ Base de datos MySQL
- ✅ Login funcional (admin@cesped365.com)
- ✅ Dashboard de administrador

### Otros archivos útiles:
- 📋 [`CHECKLIST_PRODUCCION.md`](./CHECKLIST_PRODUCCION.md) - Lista de verificación completa
- 📖 [`DESPLIEGUE_PRODUCCION.md`](./DESPLIEGUE_PRODUCCION.md) - Guía detallada + troubleshooting
- 🗄️ [`database_setup.sql`](./database_setup.sql) - Script SQL para crear tablas
- ⚙️ [`CONFIGURACION_ENV_PRODUCCION.md`](./CONFIGURACION_ENV_PRODUCCION.md) - Plantilla del .env
- 🛠️ [`COMANDOS_UTILES.md`](./COMANDOS_UTILES.md) - Comandos de administración
- 📚 [`ARCHIVOS_DESPLIEGUE_README.md`](./ARCHIVOS_DESPLIEGUE_README.md) - Guía de qué archivo usar

---

## 🚀 Tecnologías

### Frontend
- **Framework**: Svelte 4 + Vite
- **Estilos**: Tailwind CSS 3
- **Routing**: svelte-routing
- **Template Base**: Windmill Dashboard (adaptado)

### Backend
- **Framework**: CodeIgniter 4
- **Base de Datos**: MySQL
- **API**: RESTful con autenticación por sesiones
- **Autenticación**: PHP Sessions + Middleware de roles

## 📋 Características Implementadas

### Landing Page
- Hero section con CTA
- Sección de características del servicio
- Beneficios y propuesta de valor
- Footer con información de contacto
- Diseño responsive y moderno

### Sistema de Autenticación (Mock)
- Login con credenciales de prueba
- Separación de roles (Admin/Cliente)
- Redirección automática según rol

**Credenciales de prueba:**
- **Admin**: admin@cesped365.com / admin123
- **Cliente**: cliente@example.com / cliente123

### Dashboard Cliente
- **Resumen**: Estado actual del jardín, último reporte, estadísticas
- **Reportes**: Lista de reportes con detalles técnicos, fotos (placeholder), observaciones
- **Historial**: Tabla completa de visitas anteriores
- **Perfil**: Información personal y de suscripción

### Dashboard Admin
- **Resumen**: Estadísticas generales, clientes activos, visitas programadas
- **Clientes**: Gestión completa de clientes con búsqueda y filtros
- **Reportes**: Acceso a todos los reportes del sistema
- **Historial**: Vista general de todas las visitas

### Componentes Reutilizables
- `Card`: Contenedor con título opcional
- `StatCard`: Tarjeta de estadística con icono
- `Badge`: Etiquetas de estado con colores
- `Sidebar`: Navegación lateral adaptativa
- `Header`: Barra superior con búsqueda y perfil

## 🛠️ Instalación y Arranque

### Requisitos Previos
- Node.js 16+
- PHP 8.0+
- MySQL 5.7+
- Composer

### 1. Frontend (Svelte)

```bash
# Instalar dependencias
npm install

# Iniciar servidor de desarrollo
npm run dev
# Frontend disponible en: http://localhost:5173
```

### 2. Backend (CodeIgniter)

```bash
# Ir a la carpeta del backend
cd api

# Iniciar servidor de desarrollo
php spark serve
# Backend disponible en: http://localhost:8080
```

### 3. Base de Datos

Las tablas ya están creadas. Si necesitas recrearlas:

```bash
cd api

# Poblar datos de prueba
php spark db:seed SubscriptionSeeder
php spark db:seed UserSubscriptionSeeder
```

### Credenciales de Prueba

**Admin:**
- Email: `admin@cesped365.com`
- Password: `admin123`

**Cliente:**
- Email: `cliente@example.com`
- Password: `cliente123`

### Probar el Backend

```bash
cd api

# Test de autenticación
php test_auth.php

# Test del panel admin
php test_admin_panel.php

# Test de suscripciones
php test_subscriptions.php
```

## 📁 Estructura del Proyecto

```
cesped365/
├── src/                    # Frontend (Svelte)
│   ├── components/         # Componentes reutilizables
│   ├── pages/             # Páginas principales
│   ├── services/          # API services
│   └── stores/            # Svelte stores
├── api/                   # Backend (CodeIgniter 4)
│   ├── app/
│   │   ├── Controllers/   # Controladores API
│   │   │   └── Api/
│   │   │       ├── AuthController.php
│   │   │       ├── DashboardController.php
│   │   │       ├── ReportesController.php
│   │   │       ├── ClientesController.php
│   │   │       └── SubscriptionsController.php
│   │   ├── Models/        # Modelos de base de datos
│   │   │   ├── UserModel.php
│   │   │   ├── GardenModel.php
│   │   │   ├── ReportModel.php
│   │   │   ├── SubscriptionModel.php
│   │   │   └── UserSubscriptionModel.php
│   │   ├── Database/
│   │   │   ├── Migrations/ # Migraciones de BD
│   │   │   └── Seeds/      # Datos de prueba
│   │   └── Filters/       # Middleware
│   │       ├── AuthFilter.php
│   │       ├── RoleFilter.php
│   │       └── CorsFilter.php
│   ├── public/            # Entrada del servidor
│   ├── test_*.php         # Scripts de prueba
│   └── FASE*_COMPLETADA.md # Documentación de desarrollo
├── public/                # Assets estáticos (frontend)
├── index.html
├── package.json
├── tailwind.config.js
└── README.md
```

## 🔄 Rutas Disponibles

- `/` - Landing Page
- `/login` - Página de login
- `/dashboard/resumen` - Dashboard principal
- `/dashboard/reportes` - Lista de reportes
- `/dashboard/historial` - Historial de visitas
- `/dashboard/perfil` - Perfil de usuario
- `/dashboard/clientes` - Gestión de clientes (solo admin)

## 🗄️ Base de Datos

El backend incluye:

### Tablas Implementadas
- **users** - Usuarios del sistema (admin y clientes)
- **gardens** - Jardines de los clientes
- **reports** - Reportes de estado del jardín
- **report_images** - Imágenes de los reportes
- **subscriptions** - Planes de suscripción
- **user_subscriptions** - Suscripciones activas de usuarios

### Datos de Prueba
- 1 usuario admin
- 4 usuarios clientes
- 4 jardines
- Múltiples reportes con diferentes estados
- 4 planes de suscripción (Básico, Premium, Trimestral, Anual)

## 🔌 API Backend

El backend expone una API REST completa:

### Endpoints Públicos
```
POST   /api/login
```

### Endpoints Autenticados (Cliente y Admin)
```
GET    /api/me
POST   /api/logout
GET    /api/dashboard
GET    /api/reportes
GET    /api/reportes/:id
GET    /api/historial
GET    /api/subscriptions/plans
GET    /api/subscriptions/my-subscription
```

### Endpoints Solo Admin
```
GET    /api/clientes
POST   /api/clientes
PUT    /api/clientes/:id
DELETE /api/clientes/:id
GET    /api/clientes/:id/historial
POST   /api/reportes
POST   /api/reportes/:id/imagen
GET    /api/subscriptions
POST   /api/subscriptions
PUT    /api/subscriptions/:id
POST   /api/subscriptions/:id/pause
POST   /api/subscriptions/:id/reactivate
POST   /api/subscriptions/:id/cancel
```

### Documentación Completa
Ver carpeta `api/` para documentación detallada:
- `FASE1_MIGRACIONES.md` - Base de datos
- `FASE2_COMPLETADA.md` - Controladores
- `FASE3_COMPLETADA.md` - Autenticación
- `FASE4_COMPLETADA.md` - Panel admin
- `FASE5_COMPLETADA.md` - Suscripciones

## 🎨 Personalización

### Colores
Los colores principales se configuran en `tailwind.config.js`:

```javascript
colors: {
  primary: {
    50: '#f0fdf4',
    // ... hasta 900
  }
}
```

### Componentes
Todos los componentes están en `src/components/` y son fácilmente personalizables.

## ✅ Estado del Proyecto

### Completado
- ✅ Frontend completo en Svelte
- ✅ Backend API REST en CodeIgniter 4
- ✅ Base de datos MySQL completa
- ✅ Sistema de autenticación con sesiones
- ✅ CRUD completo de clientes
- ✅ Gestión de reportes con imágenes
- ✅ Sistema de suscripciones
- ✅ Panel admin funcional
- ✅ Middleware de autenticación y roles

### Próximos Pasos (Opcionales)
1. **Conectar Frontend con Backend Real** - Reemplazar mock data
2. **Mercado Pago**: Integración de pagos reales
3. **Notificaciones**: Sistema de alertas en tiempo real
4. **Calendario**: Vista de calendario para visitas programadas
5. **Reportes PDF**: Exportación de reportes
6. **Dashboard mejorado**: Gráficos en tiempo real

## 📄 Licencia

Este proyecto es privado y pertenece a Cesped365.

## 👥 Autor

Desarrollado para Cesped365 - Sistema de Jardinería Profesional
