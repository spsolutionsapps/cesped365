# 🚀 Guía Completa de Despliegue a Producción - Cesped365

Esta guía te llevará paso a paso para configurar el sitio en producción con frontend (landing + dashboard) y backend (API de CodeIgniter).

---

## 📋 PASO 1: Requisitos del Servidor

### Hosting requerido:
- ✅ **PHP 8.0+** (idealmente 8.1 o 8.2)
- ✅ **MySQL 5.7+** o **MariaDB 10.3+**
- ✅ **Acceso FTP/SFTP**
- ✅ **Acceso a cPanel o phpMyAdmin**
- ✅ **Soporte para `.htaccess`** (Apache con mod_rewrite)
- ✅ **Acceso SSH** (opcional pero recomendado)

### Verificar versión de PHP en cPanel:
1. Ir a **cPanel → MultiPHP Manager** o **Select PHP Version**
2. Seleccionar **PHP 8.0** o superior
3. Habilitar extensiones necesarias:
   - ✅ `mysqli`
   - ✅ `intl`
   - ✅ `mbstring`
   - ✅ `json`
   - ✅ `curl`

---

## 📁 PASO 2: Estructura de Archivos en el Servidor

Tu hosting debe quedar así:

```
public_html/                    ← Carpeta pública del servidor
├── index.html                  ← Landing page (build de Svelte)
├── assets/                     ← CSS, JS, imágenes del frontend
│   ├── index-[hash].js
│   ├── index-[hash].css
│   └── ...
├── .htaccess                   ← Redirecciones del frontend
├── favicon.ico
├── android-chrome-*.png
└── api/                        ← Backend CodeIgniter
    ├── app/                    ← Código de la aplicación
    ├── public/                 ← IMPORTANTE: Punto de entrada del backend
    │   ├── index.php
    │   └── .htaccess
    ├── writable/               ← Logs y cache (necesita permisos 755)
    ├── vendor/                 ← Dependencias de Composer
    ├── .env                    ← Configuración de producción
    └── ...
```

---

## 🗄️ PASO 3: Crear la Base de Datos

### 3.1 Acceder a phpMyAdmin (vía cPanel)
1. Ir a **cPanel → phpMyAdmin**
2. Clic en **"Nueva base de datos"**

### 3.2 Crear la base de datos
```sql
-- Nombre de la base de datos: cesped365_db
-- Collation: utf8mb4_unicode_ci
```

**En cPanel:**
1. Ir a **MySQL® Databases**
2. Crear nueva base de datos: `cesped365_db`
3. Crear usuario: `cesped365_user`
4. Contraseña: **[genera una segura]** ← ¡Guárdala!
5. Asignar usuario a la base de datos con **TODOS LOS PRIVILEGIOS**

### 3.3 Ejecutar las migraciones (crear tablas)

**Opción A: Via phpMyAdmin (Recomendado para hosting compartido)**

Ir a phpMyAdmin → seleccionar `cesped365_db` → pestaña **SQL** → ejecutar estos scripts en orden:

#### Script 1: Tabla `users`
```sql
CREATE TABLE `users` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','cliente') DEFAULT 'cliente',
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `plan` varchar(50) DEFAULT 'Urbano',
  `estado` varchar(20) DEFAULT 'Pendiente',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### Script 2: Tabla `gardens`
```sql
CREATE TABLE `gardens` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL,
  `address` varchar(255) NOT NULL,
  `size_m2` decimal(10,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `gardens_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### Script 3: Tabla `reports`
```sql
CREATE TABLE `reports` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `garden_id` int(11) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `estado_general` enum('excelente','bueno','regular','malo') DEFAULT 'bueno',
  `altura_cm` decimal(5,2) DEFAULT NULL,
  `crecimiento_cm` decimal(5,2) DEFAULT NULL,
  `color` enum('verde_intenso','verde','amarillento','marron') DEFAULT 'verde',
  `densidad` enum('muy_densa','densa','media','rala') DEFAULT 'densa',
  `malezas_visibles` tinyint(1) DEFAULT 0,
  `manchas` tinyint(1) DEFAULT 0,
  `zonas_desgastadas` tinyint(1) DEFAULT 0,
  `riego` enum('optimo','excesivo','insuficiente') DEFAULT 'optimo',
  `observaciones` text DEFAULT NULL,
  `jardinero` varchar(100) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `garden_id` (`garden_id`),
  CONSTRAINT `reports_garden_id_foreign` FOREIGN KEY (`garden_id`) REFERENCES `gardens` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### Script 4: Tabla `report_images`
```sql
CREATE TABLE `report_images` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `report_id` int(11) UNSIGNED NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `report_id` (`report_id`),
  CONSTRAINT `report_images_report_id_foreign` FOREIGN KEY (`report_id`) REFERENCES `reports` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### Script 5: Crear usuario admin inicial
```sql
INSERT INTO `users` (`name`, `email`, `password`, `role`, `phone`, `address`, `plan`, `estado`)
VALUES (
  'Administrador',
  'admin@cesped365.com',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: admin123
  'admin',
  '+54 11 1234-5678',
  'Oficina Central',
  'Urbano',
  'Activo'
);
```

**⚠️ IMPORTANTE:** Después del primer login, cambia la contraseña del admin desde el panel.

---

## 📤 PASO 4: Subir Archivos al Servidor

### 4.1 Build del Frontend

En tu computadora local, ejecutar:

```bash
# Construir la aplicación frontend
npm run build
```

Esto crea la carpeta `dist/` con todos los archivos optimizados.

### 4.2 Subir vía FTP

**Usando FileZilla o cualquier cliente FTP:**

1. **Conectar al servidor:**
   - Host: `ftp.tudominio.com`
   - Usuario: tu usuario FTP
   - Contraseña: tu contraseña FTP
   - Puerto: 21 (o 22 si es SFTP)

2. **Subir el frontend:**
   - Todo el contenido de `dist/` → `public_html/`
   - Asegúrate de que `index.html` esté en la raíz de `public_html/`

3. **Subir el backend:**
   - Toda la carpeta `api/` → `public_html/api/`
   - Estructura final: `public_html/api/app/`, `public_html/api/public/`, etc.

### 4.3 Configurar permisos (vía FTP o cPanel File Manager)

```
api/writable/              → 755 (o 775 si es necesario)
api/writable/cache/        → 755
api/writable/logs/         → 755
api/writable/session/      → 755
api/writable/uploads/      → 755
```

**En FileZilla:** Click derecho → Permisos de archivo → 755

---

## ⚙️ PASO 5: Configurar el Backend (.env)

### 5.1 Crear archivo `.env` en producción

Conectar vía FTP y crear el archivo `api/.env` con este contenido:

```env
#--------------------------------------------------------------------
# ENVIRONMENT
#--------------------------------------------------------------------

CI_ENVIRONMENT = production

#--------------------------------------------------------------------
# APP
#--------------------------------------------------------------------

app.baseURL = 'https://tudominio.com/'
app.indexPage = ''

#--------------------------------------------------------------------
# DATABASE
#--------------------------------------------------------------------

database.default.hostname = localhost
database.default.database = cesped365_db
database.default.username = cesped365_user
database.default.password = TU_CONTRASEÑA_SEGURA_AQUI
database.default.DBDriver = MySQLi
database.default.DBPrefix = 
database.default.port = 3306

#--------------------------------------------------------------------
# CORS
#--------------------------------------------------------------------

# En producción, cambia esto por tu dominio real
cors.allowedOrigins = https://tudominio.com,https://www.tudominio.com

#--------------------------------------------------------------------
# SECURITY
#--------------------------------------------------------------------

# Genera una clave segura con: php -r "echo bin2hex(random_bytes(32));"
# O usa: https://randomkeygen.com/
encryption.key = TU_CLAVE_DE_ENCRIPTACION_AQUI_32_CARACTERES_MINIMO

#--------------------------------------------------------------------
# SESSION
#--------------------------------------------------------------------

session.driver = 'CodeIgniter\Session\Handlers\FileHandler'
session.cookieName = 'cesped365_session'
session.expiration = 7200
session.savePath = WRITEPATH . 'session'
session.matchIP = false
session.timeToUpdate = 300
session.regenerateDestroy = false
session.cookieDomain = '.tudominio.com'
session.cookiePath = '/'
session.cookieSecure = true
session.cookieHTTPOnly = true
session.cookieSameSite = 'Lax'

#--------------------------------------------------------------------
# LOGGER
#--------------------------------------------------------------------

logger.threshold = 4
```

**⚠️ REEMPLAZAR:**
- `tudominio.com` → tu dominio real
- `cesped365_db` → nombre de tu base de datos
- `cesped365_user` → nombre de tu usuario MySQL
- `TU_CONTRASEÑA_SEGURA_AQUI` → contraseña de MySQL
- `TU_CLAVE_DE_ENCRIPTACION_AQUI` → generar una clave segura

### 5.2 Generar clave de encriptación

**Opción 1: Localmente en tu PC**
```bash
php -r "echo bin2hex(random_bytes(32));"
```

**Opción 2: Online**
Visitar: https://randomkeygen.com/ → "CodeIgniter Encryption Keys"

---

## 🔧 PASO 6: Configurar .htaccess

### 6.1 `.htaccess` en la raíz (`public_html/.htaccess`)

Este archivo ya debería estar creado por el build de Vite, pero verifica que tenga:

```apache
<IfModule mod_rewrite.c>
  RewriteEngine On
  
  # Redirigir HTTP a HTTPS (producción)
  RewriteCond %{HTTPS} off
  RewriteRule ^(.*)$ https://%{HTTP_HOST}/$1 [R=301,L]
  
  # No redirigir si es API
  RewriteCond %{REQUEST_URI} ^/api/
  RewriteRule ^ - [L]
  
  # Servir archivos estáticos directamente
  RewriteCond %{REQUEST_FILENAME} -f [OR]
  RewriteCond %{REQUEST_FILENAME} -d
  RewriteRule ^ - [L]
  
  # Redirigir todo lo demás a index.html (SPA routing)
  RewriteRule ^ index.html [L]
</IfModule>

# Configuración de seguridad
<IfModule mod_headers.c>
  Header set X-Content-Type-Options "nosniff"
  Header set X-Frame-Options "SAMEORIGIN"
  Header set X-XSS-Protection "1; mode=block"
</IfModule>

# Cache de recursos estáticos
<IfModule mod_expires.c>
  ExpiresActive On
  ExpiresByType image/jpg "access plus 1 year"
  ExpiresByType image/jpeg "access plus 1 year"
  ExpiresByType image/gif "access plus 1 year"
  ExpiresByType image/png "access plus 1 year"
  ExpiresByType image/webp "access plus 1 year"
  ExpiresByType text/css "access plus 1 month"
  ExpiresByType application/javascript "access plus 1 month"
  ExpiresByType application/x-javascript "access plus 1 month"
  ExpiresByType text/javascript "access plus 1 month"
</IfModule>
```

### 6.2 `.htaccess` del backend (`public_html/api/public/.htaccess`)

```apache
<IfModule mod_rewrite.c>
    Options -Indexes
    RewriteEngine On

    # Redirigir a index.php si no es un archivo o directorio real
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php/$1 [L]
</IfModule>

<IfModule !mod_rewrite.c>
    ErrorDocument 404 index.php
</IfModule>

# Deshabilitar listado de directorios
Options -Indexes

# Proteger archivos sensibles
<FilesMatch "^\.">
    Order allow,deny
    Deny from all
</FilesMatch>
```

### 6.3 `.htaccess` en la raíz del backend (`public_html/api/.htaccess`)

```apache
# Denegar acceso directo a la carpeta api
# Todos los accesos deben ir a api/public/
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>

# Proteger archivos de configuración
<FilesMatch "^\.env|composer\.(json|lock)|phpunit\.xml">
    Order allow,deny
    Deny from all
</FilesMatch>

# Proteger carpetas sensibles
RedirectMatch 403 ^/api/(app|writable|vendor|\.git)(/|$)
```

---

## 🔐 PASO 7: Verificar Seguridad

### 7.1 Archivos que NO deben ser accesibles públicamente:

Probar en el navegador (deben dar error 403 o 404):
- ❌ `https://tudominio.com/api/.env`
- ❌ `https://tudominio.com/api/app/`
- ❌ `https://tudominio.com/api/writable/`
- ❌ `https://tudominio.com/api/composer.json`

### 7.2 Archivos que SÍ deben ser accesibles:
- ✅ `https://tudominio.com/` (landing page)
- ✅ `https://tudominio.com/api/login` (debe responder JSON)

---

## 🧪 PASO 8: Probar el Sistema

### 8.1 Probar la Landing Page
Visitar: `https://tudominio.com/`
- ✅ La landing debe cargar correctamente
- ✅ El botón "Iniciar Sesión" debe llevar a `/login`

### 8.2 Probar el Login
1. Ir a: `https://tudominio.com/login`
2. Credenciales:
   - Email: `admin@cesped365.com`
   - Password: `admin123`
3. Debería redirigir a: `https://tudominio.com/dashboard/resumen`

### 8.3 Probar la API directamente

**Usando curl o Postman:**

```bash
# Test 1: Endpoint público
curl https://tudominio.com/api/login \
  -X POST \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "email=admin@cesped365.com&password=admin123"

# Respuesta esperada:
{
  "success": true,
  "message": "Login exitoso",
  "user": { "id": 1, "name": "Administrador", "role": "admin", ... }
}
```

### 8.4 Verificar logs del backend

Si algo falla, revisar: `api/writable/logs/log-YYYY-MM-DD.php`

Vía FTP descargar el archivo y buscar errores.

---

## 🚨 SOLUCIÓN DE PROBLEMAS COMUNES

### Problema 1: "500 Internal Server Error"
**Causas:**
- PHP version < 8.0
- Permisos incorrectos en `writable/`
- Configuración incorrecta en `.env`

**Solución:**
1. Verificar PHP version en cPanel
2. Permisos de `api/writable/` deben ser 755
3. Revisar `api/writable/logs/` para ver error específico

### Problema 2: "Database connection failed"
**Solución:**
1. Verificar credenciales en `api/.env`:
   - `database.default.hostname` (generalmente `localhost`)
   - `database.default.database` (nombre correcto de la BD)
   - `database.default.username`
   - `database.default.password`
2. Verificar que el usuario tenga permisos en la base de datos (cPanel → MySQL Databases)

### Problema 3: "CORS error" en producción
**Solución:**
En `api/.env`, actualizar:
```env
cors.allowedOrigins = https://tudominio.com,https://www.tudominio.com
```

En `api/app/Filters/CorsFilter.php`, agregar tu dominio al array:
```php
$allowedOrigins = [
    'https://tudominio.com',
    'https://www.tudominio.com'
];
```

### Problema 4: "404 Not Found" en rutas del frontend (ej. /login)
**Solución:**
Verificar que `public_html/.htaccess` tenga las reglas de rewrite correctas (ver PASO 6.1)

### Problema 5: API responde "404" o "CSRF token mismatch"
**Solución:**
1. Verificar que `api/public/index.php` exista
2. Verificar que `api/public/.htaccess` tenga reglas de rewrite
3. En producción, CSRF debe estar deshabilitado para API. Verificar `api/app/Config/Filters.php`:
   ```php
   // La ruta 'api/*' NO debe tener el filtro 'csrf'
   ```

---

## 📝 PASO 9: Configuración Post-Despliegue

### 9.1 Cambiar contraseña de admin
1. Login como admin
2. Ir a **Perfil**
3. Cambiar la contraseña de `admin123` a algo seguro

### 9.2 Crear clientes de prueba
1. Dashboard → **Clientes** → **Nuevo Cliente**
2. Rellenar datos
3. Asignar plan (Urbano, Residencial, Parque, Quintas)

### 9.3 Configurar backups automáticos
En cPanel:
1. **Backup Wizard** → Configurar backups diarios
2. Incluir: base de datos + archivos

---

## ✅ CHECKLIST FINAL

- [ ] Base de datos creada y tablas ejecutadas
- [ ] Usuario admin creado en la BD
- [ ] Archivos del frontend subidos a `public_html/`
- [ ] Archivos del backend subidos a `public_html/api/`
- [ ] Archivo `.env` configurado correctamente
- [ ] Permisos de `writable/` configurados (755)
- [ ] `.htaccess` en raíz configurado
- [ ] `.htaccess` en `api/public/` configurado
- [ ] Dominio apunta correctamente al servidor
- [ ] SSL/HTTPS habilitado (certificado instalado)
- [ ] Login funciona correctamente
- [ ] Dashboard carga sin errores
- [ ] Contraseña de admin cambiada
- [ ] Backups automáticos configurados

---

## 🎉 ¡LISTO!

Tu sitio **Cesped365** está ahora en producción.

### URLs principales:
- 🏠 Landing: `https://tudominio.com/`
- 🔐 Login: `https://tudominio.com/login`
- 📊 Dashboard Admin: `https://tudominio.com/dashboard/resumen`
- 🔌 API Base: `https://tudominio.com/api/`

---

## 📞 SOPORTE

Si encuentras problemas:
1. Revisar logs: `api/writable/logs/`
2. Verificar `.env` está correctamente configurado
3. Verificar permisos de archivos
4. Verificar que PHP >= 8.0 esté activo

**¡Éxito con tu proyecto!** 🚀🌱
