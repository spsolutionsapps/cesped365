# Instrucciones para Iniciar el Backend Cesped365 API

## ⚠️ Requisito Importante: Habilitar extensión intl en PHP

El servidor CodeIgniter 4 requiere la extensión `intl` de PHP. Sigue estos pasos:

### 1. Habilitar extensión intl

**Ubicación del archivo**: `C:\xampp\php\php.ini`

1. Abre el archivo `php.ini`
2. Busca la línea: `;extension=intl`
3. Quita el punto y coma (`;`) para descomentarla:
   ```
   extension=intl
   ```
4. Guarda el archivo
5. Reinicia Apache si está corriendo

### 2. Verificar que la extensión está habilitada

```bash
php -m | findstr intl
```

Debería mostrar: `intl`

---

## 🚀 Iniciar el Servidor

Una vez habilitada la extensión `intl`:

```bash
cd "c:\Users\sebas\OneDrive\Documentos\sp-solutions webs\cesped365-api"
php spark serve --port=8080
```

El servidor iniciará en: **http://localhost:8080**

---

## 🧪 Probar los Endpoints

### 1. Login (POST)

```bash
curl -X POST http://localhost:8080/api/login ^
  -H "Content-Type: application/json" ^
  -d "{\"email\":\"admin@cesped365.com\",\"password\":\"admin123\"}"
```

**Respuesta esperada:**
```json
{
  "success": true,
  "token": "YWRtaW5AY2VzcGVkMzY1LmNvbToxNzM2ODA...",
  "user": {
    "id": 1,
    "name": "Administrador",
    "email": "admin@cesped365.com",
    "role": "admin"
  }
}
```

### 2. Dashboard (GET)

```bash
curl http://localhost:8080/api/dashboard
```

### 3. Reportes (GET)

```bash
curl http://localhost:8080/api/reportes
```

### 4. Historial (GET)

```bash
curl http://localhost:8080/api/historial
```

### 5. Clientes (GET)

```bash
curl http://localhost:8080/api/clientes
```

### 6. Cliente por ID (GET)

```bash
curl http://localhost:8080/api/clientes/1
```

---

## 🔧 Alternativa: Usar Postman

1. Abre Postman
2. Crea una nueva colección "Cesped365 API"
3. Agrega los siguientes requests:

### POST Login
- **URL**: `http://localhost:8080/api/login`
- **Method**: POST
- **Headers**: `Content-Type: application/json`
- **Body** (raw JSON):
```json
{
  "email": "admin@cesped365.com",
  "password": "admin123"
}
```

### GET Dashboard
- **URL**: `http://localhost:8080/api/dashboard`
- **Method**: GET

### GET Reportes
- **URL**: `http://localhost:8080/api/reportes`
- **Method**: GET

### GET Historial
- **URL**: `http://localhost:8080/api/historial`
- **Method**: GET

### GET Clientes
- **URL**: `http://localhost:8080/api/clientes`
- **Method**: GET

---

## ✅ Verificación

Si todos los endpoints responden correctamente:

1. ✅ El backend está funcionando
2. ✅ CORS está configurado
3. ✅ Los datos mock se están devolviendo
4. ✅ Listo para integrar con el frontend

---

## 🔗 Integración con Frontend

Una vez que el backend esté funcionando:

1. Ve al proyecto frontend: `cd cesped365`
2. Crea archivo `.env` en la raíz:
   ```
   VITE_API_URL=http://localhost:8080/api
   ```
3. Actualiza `src/stores/auth.js` para usar la API real
4. Reinicia el frontend: `npm run dev`

---

## 📝 Credenciales de Prueba

### Admin
- Email: `admin@cesped365.com`
- Password: `admin123`

### Cliente
- Email: `cliente@example.com`
- Password: `cliente123`

---

## 🐛 Problemas Comunes

### Error: "Class Locale not found"
**Solución**: Habilita la extensión `intl` en `php.ini` (ver paso 1)

### Error: "Port 8080 is already in use"
**Solución**: Usa otro puerto:
```bash
php spark serve --port=8081
```
Y actualiza la URL en el frontend.

### Error: CORS
**Solución**: Verifica que el filtro CORS esté registrado en `app/Config/Filters.php`

---

## 📊 Estructura de Archivos Creados

```
cesped365-api/
├── .env                                    # Configuración
├── app/
│   ├── Config/
│   │   ├── Filters.php                    # Filtros (CORS registrado)
│   │   └── Routes.php                     # Rutas API
│   ├── Controllers/
│   │   └── Api/
│   │       ├── AuthController.php         # Login y autenticación
│   │       ├── ClientesController.php     # Gestión de clientes
│   │       ├── DashboardController.php    # Estadísticas
│   │       ├── HistorialController.php    # Historial de visitas
│   │       └── ReportesController.php     # Reportes de jardín
│   └── Filters/
│       └── CorsFilter.php                 # Filtro CORS personalizado
└── INSTRUCCIONES_INICIO.md               # Este archivo
```

---

## 🎉 ¡Listo!

El backend está completamente configurado y listo para usar.

**Siguiente paso**: Habilitar `intl` e iniciar el servidor.
