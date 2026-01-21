# ✅ Checklist Rápido - Poner Cesped365 en Producción

Sigue estos pasos en orden. Marca cada uno cuando lo completes.

---

## 🎯 ANTES DE EMPEZAR

- [ ] Tienes acceso a cPanel de tu hosting
- [ ] Tienes las credenciales FTP
- [ ] Tu dominio ya apunta al servidor (DNS configurado)
- [ ] Tienes SSL/HTTPS habilitado en el hosting

---

## 📝 PASO 1: CONFIGURAR BASE DE DATOS (cPanel)

### 1.1 Crear Base de Datos
- [ ] Ir a **cPanel → MySQL® Databases**
- [ ] Crear nueva base de datos: `cesped365_db`
- [ ] Anotar el nombre completo (puede ser `usuario_cesped365_db`)

### 1.2 Crear Usuario MySQL
- [ ] Crear usuario: `cesped365_user`
- [ ] Generar contraseña segura → **📝 GUARDAR EN UN LUGAR SEGURO**
- [ ] Anotar usuario completo (puede ser `usuario_cesped365_user`)

### 1.3 Asignar Permisos
- [ ] Agregar usuario a la base de datos
- [ ] Seleccionar **TODOS LOS PRIVILEGIOS**
- [ ] Guardar cambios

### 1.4 Ejecutar Script SQL
- [ ] Ir a **cPanel → phpMyAdmin**
- [ ] Seleccionar la base de datos `cesped365_db`
- [ ] Ir a pestaña **SQL**
- [ ] Abrir el archivo `database_setup.sql` de este proyecto
- [ ] Copiar TODO el contenido
- [ ] Pegar en phpMyAdmin
- [ ] Click en **"Continuar"**
- [ ] Verificar que aparezcan 4 tablas: `users`, `gardens`, `reports`, `report_images`
- [ ] Verificar que exista 1 usuario (admin@cesped365.com)

**✅ Base de datos lista!**

---

## 🚀 PASO 2: DESPLEGAR ARCHIVOS

### Opción A: Usando GitHub Actions (Recomendado)

- [ ] Ir a tu repositorio en GitHub
- [ ] Click en **Settings → Secrets and variables → Actions**
- [ ] Agregar estos secrets:

| Secret Name | Valor | Ejemplo |
|------------|-------|---------|
| `FTP_SERVER` | Dirección del servidor FTP | `ftp.tudominio.com` |
| `FTP_USERNAME` | Usuario FTP | `usuario@tudominio.com` |
| `FTP_PASSWORD` | Contraseña FTP | `tu_password` |
| `FTP_PORT` | Puerto (opcional) | `21` |
| `FTP_SERVER_DIR` | Carpeta destino | `public_html/` |

- [ ] Hacer commit y push a la rama `main`
- [ ] Ir a **Actions** en GitHub
- [ ] Esperar a que termine el deployment (5-10 minutos)
- [ ] Verificar que diga "✅ DEPLOYMENT EXITOSO"

### Opción B: Manualmente vía FTP

- [ ] En tu PC local, ejecutar: `npm run build`
- [ ] Conectar a FTP usando FileZilla
- [ ] Subir TODO el contenido de `dist/` → `public_html/`
- [ ] Subir la carpeta `api/` completa → `public_html/api/`
- [ ] Verificar estructura:
  ```
  public_html/
  ├── index.html ← del build
  ├── assets/ ← del build
  └── api/
      ├── app/
      ├── public/
      ├── writable/
      └── vendor/
  ```

**✅ Archivos subidos!**

---

## ⚙️ PASO 3: CONFIGURAR .env EN EL SERVIDOR

- [ ] Conectar vía FTP
- [ ] Navegar a `public_html/api/`
- [ ] Crear nuevo archivo llamado `.env`
- [ ] Abrir el archivo `CONFIGURACION_ENV_PRODUCCION.md` de este proyecto
- [ ] Copiar el contenido del `.env` de ejemplo
- [ ] Pegar en el `.env` del servidor
- [ ] **REEMPLAZAR estos valores:**

```env
app.baseURL = 'https://[TU-DOMINIO.com]/'          → tu dominio real
database.default.database = [NOMBRE_BASE_DATOS]    → nombre de tu BD
database.default.username = [USUARIO_MYSQL]        → usuario MySQL
database.default.password = [CONTRASEÑA_MYSQL]     → contraseña MySQL
encryption.key = [CLAVE_SEGURA]                    → generar clave
cors.allowedOrigins = https://[TU-DOMINIO.com]     → tu dominio
session.cookieDomain = '.[TU-DOMINIO.com]'         → tu dominio
```

### Generar clave de encriptación:
En tu PC local:
```bash
php -r "echo bin2hex(random_bytes(32));"
```
O usar: https://randomkeygen.com/

- [ ] **Guardar el archivo .env**

**✅ Configuración completa!**

---

## 🔐 PASO 4: CONFIGURAR PERMISOS

Vía FTP o cPanel File Manager:

- [ ] `public_html/api/writable/` → Permisos: **755**
- [ ] `public_html/api/writable/cache/` → Permisos: **755**
- [ ] `public_html/api/writable/logs/` → Permisos: **755**
- [ ] `public_html/api/writable/session/` → Permisos: **755**
- [ ] `public_html/api/writable/uploads/` → Permisos: **755**

**En FileZilla:** Click derecho → Permisos de archivo → 755

**✅ Permisos configurados!**

---

## 🧪 PASO 5: PROBAR EL SITIO

### 5.1 Probar Landing
- [ ] Abrir: `https://tudominio.com/`
- [ ] La landing debe cargar correctamente
- [ ] Click en "Iniciar Sesión"

### 5.2 Probar Login
- [ ] Ir a: `https://tudominio.com/login`
- [ ] Usuario: `admin@cesped365.com`
- [ ] Contraseña: `admin123`
- [ ] Click en "Iniciar Sesión"
- [ ] Debe redirigir al dashboard

### 5.3 Probar Dashboard
- [ ] El dashboard debe cargar sin errores
- [ ] Abrir consola del navegador (F12) → No debe haber errores rojos
- [ ] Navegar por las secciones:
  - [ ] Resumen
  - [ ] Clientes
  - [ ] Reportes

### 5.4 Probar API directamente
Abrir en el navegador:
- [ ] `https://tudominio.com/api/` → Debe mostrar "Welcome to CodeIgniter 4"

**✅ Todo funciona!**

---

## 🔒 PASO 6: SEGURIDAD POST-INSTALACIÓN

- [ ] **CAMBIAR contraseña del admin:**
  - Login → Perfil → Cambiar contraseña
  - Usar una contraseña segura diferente a `admin123`

- [ ] **Verificar archivos protegidos:**
  Estos NO deben ser accesibles (deben dar error 403/404):
  - [ ] `https://tudominio.com/api/.env` → ❌ Debe dar error
  - [ ] `https://tudominio.com/api/app/` → ❌ Debe dar error
  - [ ] `https://tudominio.com/api/writable/` → ❌ Debe dar error

- [ ] **Configurar backups en cPanel:**
  - cPanel → Backup Wizard
  - Configurar backups automáticos diarios

**✅ Sitio seguro!**

---

## 🎉 ¡COMPLETADO!

Tu sitio Cesped365 está ahora en producción y funcionando.

### URLs importantes:
- 🏠 **Landing:** `https://tudominio.com/`
- 🔐 **Login:** `https://tudominio.com/login`
- 📊 **Dashboard:** `https://tudominio.com/dashboard/resumen`
- 🔌 **API:** `https://tudominio.com/api/`

### Credenciales iniciales:
- **Admin:** admin@cesped365.com / [tu nueva contraseña segura]

---

## 🚨 ¿Algo no funciona?

Ver el archivo **DESPLIEGUE_PRODUCCION.md** para solución de problemas detallada.

### Problemas comunes:

**500 Error:**
- Revisar `api/writable/logs/` para ver el error específico
- Verificar permisos de `writable/`
- Verificar versión de PHP (debe ser 8.0+)

**Database connection failed:**
- Verificar credenciales en `api/.env`
- Verificar que usuario tenga permisos en la BD

**CORS errors:**
- Verificar `cors.allowedOrigins` en `api/.env`

**404 en /login o /dashboard:**
- Verificar que `.htaccess` esté en `public_html/`
- Verificar que mod_rewrite esté habilitado en el servidor
