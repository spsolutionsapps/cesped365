# 📁 Estructura del Proyecto Cesped365

Proyecto completo con Frontend (Svelte) y Backend (CodeIgniter 4) en un solo repositorio.

---

## 🏗️ Arquitectura

```
┌─────────────────────────────────────────────────────────────┐
│                     CESPED365 PROJECT                        │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  ┌──────────────────┐              ┌──────────────────┐    │
│  │    FRONTEND      │              │     BACKEND      │    │
│  │    (Svelte)      │  ◄────────►  │  (CodeIgniter)   │    │
│  │                  │   REST API   │                  │    │
│  │  localhost:5173  │              │  localhost:8080  │    │
│  └──────────────────┘              └──────────────────┘    │
│          │                                  │               │
│          │                                  │               │
│          ▼                                  ▼               │
│    Mock Data (temp)                   MySQL Database       │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

---

## 📂 Estructura de Carpetas

```
cesped365/
│
├── 🎨 FRONTEND (Svelte + Vite)
│   ├── src/
│   │   ├── components/      # Componentes reutilizables
│   │   │   ├── Badge.svelte
│   │   │   ├── Card.svelte
│   │   │   ├── Header.svelte
│   │   │   ├── Sidebar.svelte
│   │   │   └── StatCard.svelte
│   │   │
│   │   ├── pages/          # Páginas de la aplicación
│   │   │   ├── Landing.svelte
│   │   │   ├── Login.svelte
│   │   │   ├── Dashboard.svelte
│   │   │   └── dashboard/
│   │   │       ├── Resumen.svelte
│   │   │       ├── Reportes.svelte
│   │   │       ├── Historial.svelte
│   │   │       ├── Perfil.svelte
│   │   │       └── admin/
│   │   │           └── Clientes.svelte
│   │   │
│   │   ├── services/       # Servicios y API
│   │   │   └── api.js      # Funciones para llamar al backend
│   │   │
│   │   ├── stores/         # Estado global (Svelte Stores)
│   │   │   ├── auth.js     # Autenticación
│   │   │   └── mockData.js # Datos temporales
│   │   │
│   │   ├── App.svelte      # Componente raíz + Router
│   │   ├── main.js         # Entry point
│   │   └── app.css         # Estilos globales
│   │
│   ├── public/             # Assets estáticos
│   ├── index.html          # HTML base
│   ├── package.json        # Dependencias npm
│   ├── vite.config.js      # Config Vite
│   └── tailwind.config.js  # Config Tailwind
│
├── 🔧 BACKEND (CodeIgniter 4)
│   └── api/
│       ├── app/
│       │   ├── Controllers/
│       │   │   └── Api/    # Controladores REST
│       │   │       ├── AuthController.php
│       │   │       ├── DashboardController.php
│       │   │       ├── ReportesController.php
│       │   │       ├── HistorialController.php
│       │   │       ├── ClientesController.php
│       │   │       └── SubscriptionsController.php
│       │   │
│       │   ├── Models/     # Modelos de BD
│       │   │   ├── UserModel.php
│       │   │   ├── GardenModel.php
│       │   │   ├── ReportModel.php
│       │   │   ├── ReportImageModel.php
│       │   │   ├── SubscriptionModel.php
│       │   │   └── UserSubscriptionModel.php
│       │   │
│       │   ├── Database/
│       │   │   ├── Migrations/  # Estructura de BD
│       │   │   │   ├── CreateUsersTable.php
│       │   │   │   ├── CreateGardensTable.php
│       │   │   │   ├── CreateReportsTable.php
│       │   │   │   ├── CreateReportImagesTable.php
│       │   │   │   ├── CreateSubscriptionsTable.php
│       │   │   │   └── CreateUserSubscriptionsTable.php
│       │   │   │
│       │   │   └── Seeds/      # Datos de prueba
│       │   │       ├── UserSeeder.php
│       │   │       ├── GardenSeeder.php
│       │   │       ├── ReportSeeder.php
│       │   │       ├── SubscriptionSeeder.php
│       │   │       └── UserSubscriptionSeeder.php
│       │   │
│       │   ├── Filters/    # Middleware
│       │   │   ├── AuthFilter.php      # Autenticación
│       │   │   ├── RoleFilter.php      # Autorización por rol
│       │   │   └── CorsFilter.php      # CORS
│       │   │
│       │   └── Config/     # Configuración
│       │       ├── Routes.php          # Rutas API
│       │       ├── Filters.php         # Registro de filtros
│       │       └── Database.php        # Config MySQL
│       │
│       ├── public/         # Punto de entrada HTTP
│       │   └── index.php
│       │
│       ├── writable/       # Logs, cache, sessions
│       │   ├── logs/
│       │   ├── cache/
│       │   ├── session/
│       │   └── uploads/
│       │
│       ├── vendor/         # Dependencias Composer
│       │
│       ├── test_*.php      # Scripts de prueba
│       │   ├── test_auth.php
│       │   ├── test_admin_panel.php
│       │   └── test_subscriptions.php
│       │
│       ├── spark            # CLI de CodeIgniter
│       │
│       └── FASE*_COMPLETADA.md  # Documentación desarrollo
│
├── 📄 DOCUMENTACIÓN
│   ├── README.md                    # Readme principal
│   ├── INICIO_RAPIDO_COMPLETO.md   # Guía de inicio
│   ├── ESTRUCTURA_PROYECTO.md      # Este archivo
│   └── .gitignore                  # Ignorar archivos
│
└── 🔒 CONFIGURACIÓN
    ├── .env (frontend)              # Variables de entorno frontend
    └── api/.env (backend)           # Variables de entorno backend
```

---

## 🔗 Flujo de Datos

### 1. Usuario hace login
```
Usuario ingresa credenciales
    ↓
Frontend (Login.svelte) → POST /api/login
    ↓
Backend (AuthController) valida y crea sesión
    ↓
Devuelve token + datos de usuario
    ↓
Frontend guarda en store (auth.js)
    ↓
Redirige a /dashboard
```

### 2. Usuario ve reportes
```
Usuario navega a Reportes
    ↓
Frontend (Reportes.svelte) → GET /api/reportes
    ↓
Backend (ReportesController) verifica sesión (AuthFilter)
    ↓
Consulta MySQL y devuelve reportes
    ↓
Frontend muestra los datos
```

### 3. Admin gestiona clientes
```
Admin navega a Clientes
    ↓
Frontend (Clientes.svelte) → GET /api/clientes
    ↓
Backend verifica sesión (AuthFilter) y rol admin (RoleFilter)
    ↓
Consulta MySQL y devuelve clientes
    ↓
Frontend muestra tabla con clientes
```

---

## 🌐 Puertos y URLs

| Servicio | Puerto | URL | Descripción |
|----------|--------|-----|-------------|
| Frontend Dev | 5173 | http://localhost:5173 | Servidor Vite |
| Backend Dev | 8080 | http://localhost:8080 | Servidor PHP |
| MySQL | 3306 | localhost:3306 | Base de datos |

---

## 📦 Dependencias

### Frontend
```json
{
  "svelte": "^4.0.0",
  "vite": "^5.0.0",
  "tailwindcss": "^3.3.0",
  "svelte-routing": "^2.0.0"
}
```

### Backend
```json
{
  "codeigniter4/framework": "^4.6",
  "php": "^8.0"
}
```

---

## 🗄️ Base de Datos

### Tablas
1. **users** - Usuarios (admin y clientes)
2. **gardens** - Jardines de clientes
3. **reports** - Reportes de estado
4. **report_images** - Fotos de reportes
5. **subscriptions** - Planes disponibles
6. **user_subscriptions** - Suscripciones activas

### Relaciones
```
users (1) ──── (N) gardens
gardens (1) ──── (N) reports
reports (1) ──── (N) report_images
users (1) ──── (N) user_subscriptions
subscriptions (1) ──── (N) user_subscriptions
```

---

## 🚀 Comandos Útiles

### Frontend
```bash
npm install          # Instalar dependencias
npm run dev          # Servidor desarrollo
npm run build        # Compilar producción
npm run preview      # Preview producción
```

### Backend
```bash
cd api
php spark serve      # Servidor desarrollo
php spark migrate    # Ejecutar migraciones
php spark db:seed X  # Ejecutar seeder
php test_*.php       # Ejecutar tests
```

---

## 📊 Estado del Proyecto

### ✅ Completado (5/5 Fases)
- ✅ Fase 1: Base de datos y modelos
- ✅ Fase 2: Controladores con datos reales
- ✅ Fase 3: Autenticación y autorización
- ✅ Fase 4: Panel admin funcional
- ✅ Fase 5: Sistema de suscripciones

### 🔄 Siguiente: Conectar Frontend con Backend
Actualmente el frontend usa datos mock. Para conectarlo:

1. Actualizar `src/services/api.js` para usar URLs reales
2. Modificar stores para usar API en lugar de mock data
3. Configurar CORS si es necesario
4. Implementar manejo de errores

---

## 📝 Notas Importantes

- **Git**: Un solo repositorio para todo el proyecto
- **Deploy**: Frontend y backend se pueden deployar separadamente
- **Desarrollo**: Dos terminales (una para frontend, otra para backend)
- **Datos**: Frontend tiene mock data, backend tiene datos reales en MySQL
- **Sesiones**: Backend usa sesiones PHP (no JWT aún)

---

**Última actualización**: 2026-01-14
