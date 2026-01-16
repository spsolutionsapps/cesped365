# Backend Cesped365 API - CodeIgniter 4

## 📋 Descripción

Backend API REST para el sistema Cesped365, desarrollado en CodeIgniter 4. Proporciona endpoints para autenticación, gestión de reportes, clientes e historial de visitas.

**Estado actual**: ✅ Funcional con datos mock

---

## 🎯 Características Implementadas

### ✅ Autenticación Mock
- Login con email/password
- Generación de tokens (base64)
- Endpoint `/me` para obtener usuario actual
- Roles: `admin` y `cliente`

### ✅ Endpoints Disponibles

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| POST | `/api/login` | Login de usuarios |
| GET | `/api/me` | Usuario actual |
| GET | `/api/dashboard` | Estadísticas generales |
| GET | `/api/reportes` | Lista de reportes |
| GET | `/api/reportes/:id` | Reporte específico |
| GET | `/api/historial` | Historial de visitas |
| GET | `/api/clientes` | Lista de clientes |
| GET | `/api/clientes/:id` | Cliente específico |

### ✅ CORS Configurado
- Permite peticiones desde `http://localhost:3001`
- Headers configurados para frontend Svelte

---

## 🚀 Instalación y Configuración

### Requisitos
- PHP 8.0+
- Composer
- Extensión `intl` de PHP habilitada

### 1. Habilitar extensión intl

Edita `C:\xampp\php\php.ini`:
```ini
extension=intl
```

### 2. Verificar instalación

```bash
cd "c:\Users\sebas\OneDrive\Documentos\sp-solutions webs\cesped365-api"
composer install
```

### 3. Configurar entorno

El archivo `.env` ya está configurado con:
- `CI_ENVIRONMENT = development`
- `app.baseURL = 'http://localhost:8080'`
- Configuración de base de datos (no usada aún)

### 4. Iniciar servidor

```bash
php spark serve --port=8080
```

---

## 📊 Estructura del Proyecto

```
cesped365-api/
├── app/
│   ├── Config/
│   │   ├── Filters.php          # Registro de filtros
│   │   └── Routes.php           # Definición de rutas API
│   ├── Controllers/
│   │   └── Api/
│   │       ├── AuthController.php
│   │       ├── ClientesController.php
│   │       ├── DashboardController.php
│   │       ├── HistorialController.php
│   │       └── ReportesController.php
│   └── Filters/
│       └── CorsFilter.php       # Filtro CORS personalizado
├── .env                         # Configuración
├── INSTRUCCIONES_INICIO.md     # Guía de inicio
└── README_BACKEND.md           # Este archivo
```

---

## 🧪 Testing

### Con cURL

```bash
# Login
curl -X POST http://localhost:8080/api/login ^
  -H "Content-Type: application/json" ^
  -d "{\"email\":\"admin@cesped365.com\",\"password\":\"admin123\"}"

# Dashboard
curl http://localhost:8080/api/dashboard

# Reportes
curl http://localhost:8080/api/reportes
```

### Con Postman

Importa la colección de endpoints desde `INSTRUCCIONES_INICIO.md`

---

## 👥 Usuarios de Prueba

### Administrador
```json
{
  "email": "admin@cesped365.com",
  "password": "admin123"
}
```

### Cliente
```json
{
  "email": "cliente@example.com",
  "password": "cliente123"
}
```

---

## 📝 Datos Mock

Todos los controladores usan datos simulados:

- **AuthController**: 2 usuarios (admin y cliente)
- **ReportesController**: 3 reportes de ejemplo
- **HistorialController**: 5 visitas de ejemplo
- **ClientesController**: 4 clientes de ejemplo
- **DashboardController**: Estadísticas agregadas

---

## 🔄 Próximos Pasos (No Implementados)

### Fase 2: Base de Datos
- [ ] Crear migraciones
- [ ] Crear modelos
- [ ] Conectar controladores con BD

### Fase 3: Autenticación Real
- [ ] Implementar JWT
- [ ] Hash de contraseñas
- [ ] Refresh tokens
- [ ] Middleware de autenticación

### Fase 4: Validaciones
- [ ] Validación de inputs
- [ ] Sanitización de datos
- [ ] Manejo de errores robusto

### Fase 5: Funcionalidades Avanzadas
- [ ] Subida de imágenes
- [ ] Integración con Mercado Pago
- [ ] Sistema de notificaciones
- [ ] Logs y auditoría

---

## 🔗 Integración con Frontend

El frontend Svelte está en: `../cesped365/`

Para conectar:

1. Asegúrate que el backend esté corriendo en puerto 8080
2. En el frontend, crea `.env`:
   ```
   VITE_API_URL=http://localhost:8080/api
   ```
3. Actualiza `src/stores/auth.js` para usar API real
4. Reinicia el frontend

---

## 🐛 Troubleshooting

### Error: "Class Locale not found"
**Causa**: Extensión `intl` no habilitada  
**Solución**: Habilitar en `php.ini` y reiniciar

### Error: "Port already in use"
**Causa**: Puerto 8080 ocupado  
**Solución**: Usar otro puerto: `php spark serve --port=8081`

### Error: CORS
**Causa**: Frontend en puerto diferente  
**Solución**: Actualizar `app/Filters/CorsFilter.php`

---

## 📚 Documentación

- [CodeIgniter 4 Docs](https://codeigniter.com/user_guide/)
- [RESTful API Guide](https://codeigniter.com/user_guide/incoming/restful.html)
- [Filters](https://codeigniter.com/user_guide/incoming/filters.html)

---

## ✅ Checklist de Implementación

- [x] Instalación de CodeIgniter 4
- [x] Configuración de .env
- [x] Filtro CORS
- [x] Rutas API configuradas
- [x] AuthController con login mock
- [x] DashboardController con estadísticas
- [x] ReportesController con datos mock
- [x] HistorialController con visitas
- [x] ClientesController con gestión
- [x] Documentación completa

---

## 🎉 Estado del Proyecto

**Backend**: ✅ **100% COMPLETADO** (Fase 1 - Mock Data)

El backend está funcional y listo para:
- Consumir desde el frontend
- Demos y pruebas
- Desarrollo de Fase 2 (Base de Datos)

---

**Desarrollado para**: Cesped365 - Sistema de Jardinería Profesional  
**Versión**: 1.0.0  
**Fecha**: 13 de Enero, 2026
