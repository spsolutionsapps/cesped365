# 🚀 Inicio Rápido - Cesped365 en Producción

**¿Landing funcionando? ¡Perfecto! Ahora falta activar el login y el dashboard.**

---

## 📋 Lo que necesitas hacer (30-45 minutos)

1. ✅ Crear base de datos MySQL
2. ✅ Ejecutar un script SQL
3. ✅ Crear un archivo `.env` en el servidor
4. ✅ Probar el login

**¡Eso es todo!** El sitio ya está subido, solo falta la configuración.

---

## 🎯 GUÍA RÁPIDA - 4 PASOS

### PASO 1: Crear Base de Datos (5 minutos)

1. **Ir a cPanel → MySQL® Databases**

2. **Crear base de datos:**
   - Nombre: `cesped365_db`
   - Click en "Crear base de datos"
   - 📝 Anotar el nombre completo (ej: `usuario_cesped365_db`)

3. **Crear usuario:**
   - Usuario: `cesped365_user`
   - Contraseña: [Generar una segura]
   - 📝 **¡GUARDAR ESTA CONTRASEÑA!**
   - 📝 Anotar el nombre completo (ej: `usuario_cesped365_user`)

4. **Asignar permisos:**
   - "Add User to Database"
   - Seleccionar: base de datos + usuario
   - Marcar: **ALL PRIVILEGES** (todos los privilegios)
   - Click en "Make Changes"

✅ Base de datos creada!

---

### PASO 2: Ejecutar Script SQL (5 minutos)

1. **Ir a cPanel → phpMyAdmin**

2. **Seleccionar tu base de datos** (cesped365_db) en el panel izquierdo

3. **Ir a la pestaña "SQL"**

4. **Abrir el archivo `database_setup.sql`** (está en la raíz de este proyecto)

5. **Copiar TODO el contenido del archivo**

6. **Pegar en el campo SQL de phpMyAdmin**

7. **Click en "Continuar"**

8. **Verificar que se crearon las tablas:**
   - Debe aparecer: "4 tablas creadas correctamente"
   - Ver en panel izquierdo: `users`, `gardens`, `reports`, `report_images`

✅ Base de datos configurada!

---

### PASO 3: Crear archivo .env (10 minutos)

1. **Conectar vía FTP** (FileZilla, WinSCP, o cPanel File Manager)

2. **Navegar a:** `public_html/api/`

3. **Crear nuevo archivo llamado:** `.env` (con el punto al inicio)

4. **Copiar este contenido** y pegarlo en el archivo:

```env
CI_ENVIRONMENT = production

app.baseURL = 'https://TU-DOMINIO.com/'
app.indexPage = ''
app.defaultTimezone = 'America/Argentina/Buenos_Aires'

database.default.hostname = localhost
database.default.database = NOMBRE_BD_COMPLETO
database.default.username = USUARIO_MYSQL_COMPLETO
database.default.password = CONTRASEÑA_MYSQL
database.default.DBDriver = MySQLi
database.default.port = 3306
database.default.charset = utf8mb4

cors.allowedOrigins = https://TU-DOMINIO.com,https://www.TU-DOMINIO.com

encryption.key = GENERAR_CLAVE_AQUI

session.driver = 'CodeIgniter\Session\Handlers\FileHandler'
session.cookieName = 'cesped365_session'
session.expiration = 7200
session.savePath = WRITEPATH . 'session'
session.matchIP = false
session.timeToUpdate = 300
session.regenerateDestroy = false
session.cookieDomain = '.TU-DOMINIO.com'
session.cookiePath = '/'
session.cookieSecure = true
session.cookieHTTPOnly = true
session.cookieSameSite = 'Lax'

logger.threshold = 1
```

5. **REEMPLAZAR estos valores:**

| Valor a reemplazar | Con |
|-------------------|-----|
| `TU-DOMINIO.com` | Tu dominio real (ej: `cesped365.com`) |
| `NOMBRE_BD_COMPLETO` | El nombre completo de tu BD (ej: `usuario_cesped365_db`) |
| `USUARIO_MYSQL_COMPLETO` | El usuario completo (ej: `usuario_cesped365_user`) |
| `CONTRASEÑA_MYSQL` | La contraseña que guardaste en PASO 1 |
| `GENERAR_CLAVE_AQUI` | Ver abajo cómo generar 👇 |

6. **Generar clave de encriptación:**

   **Opción 1:** En tu PC local (si tienes PHP):
   ```bash
   php -r "echo bin2hex(random_bytes(32));"
   ```

   **Opción 2:** Online:
   - Ir a: https://randomkeygen.com/
   - Copiar una clave de "CodeIgniter Encryption Keys"

   Ejemplo de clave: `a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6`

7. **Guardar el archivo**

✅ Configuración completada!

---

### PASO 4: Configurar Permisos (2 minutos)

**Vía FTP o cPanel File Manager:**

Cambiar permisos de estas carpetas a **755**:

```
public_html/api/writable/         → 755
public_html/api/writable/cache/   → 755
public_html/api/writable/logs/    → 755
public_html/api/writable/session/ → 755
public_html/api/writable/uploads/ → 755
```

**En FileZilla:** Click derecho → Permisos de archivo → Escribir `755`

✅ Permisos configurados!

---

## 🧪 PROBAR QUE TODO FUNCIONE

### Test 1: Landing ✅
```
https://tudominio.com/
```
→ Debe cargar la landing correctamente

### Test 2: Login 🔐
```
https://tudominio.com/login
```
**Credenciales:**
- Email: `admin@cesped365.com`
- Password: `admin123`

→ Debe iniciar sesión y redirigir al dashboard

### Test 3: Dashboard 📊
```
https://tudominio.com/dashboard/resumen
```
→ Debe mostrar el panel de administrador

---

## ✅ ¡LISTO!

Si los 3 tests funcionan, **tu sitio está 100% operativo** 🎉

### Tareas finales:

1. **Cambiar contraseña del admin:**
   - Login → Perfil → Cambiar contraseña
   - Usar una contraseña segura (NO dejar `admin123`)

2. **Crear tu primer cliente:**
   - Dashboard → Clientes → Nuevo Cliente

3. **Configurar backups:**
   - cPanel → Backup → Configurar backups automáticos

---

## 🚨 ¿Problemas?

### Error: "500 Internal Server Error"
**Solución:**
1. Verificar que PHP sea versión 8.0 o superior (cPanel → MultiPHP Manager)
2. Verificar permisos de `writable/` (deben ser 755)
3. Revisar archivo `.env` (verificar que no tenga errores de sintaxis)
4. Ver logs en: `api/writable/logs/log-YYYY-MM-DD.php`

### Error: "Database connection failed"
**Solución:**
1. Verificar credenciales en `.env`:
   - `database.default.database` ← nombre completo de la BD
   - `database.default.username` ← usuario completo
   - `database.default.password` ← contraseña correcta
2. En cPanel, verificar que el usuario tenga permisos sobre la BD

### Error: "404 Not Found" en /login
**Solución:**
1. Verificar que existe `public_html/.htaccess`
2. En cPanel → Software → MultiPHP INI Editor → Activar `mod_rewrite`

### Login no funciona / CORS error
**Solución:**
1. Verificar en `.env`:
   - `app.baseURL` debe ser `https://tudominio.com/` (con HTTPS y barra final)
   - `cors.allowedOrigins` debe incluir tu dominio con HTTPS

---

## 📚 Archivos de Referencia

- **CHECKLIST_PRODUCCION.md** ← Checklist detallado paso a paso
- **DESPLIEGUE_PRODUCCION.md** ← Guía completa con solución de problemas
- **CONFIGURACION_ENV_PRODUCCION.md** ← Referencia completa del archivo .env
- **database_setup.sql** ← Script SQL para la base de datos

---

## 🎯 Ejemplo Real

Si tu sitio es `cesped365.com` y en cPanel creaste:
- Base de datos: `mi_usuario_cesped365_db`
- Usuario: `mi_usuario_cesped365_user`
- Contraseña: `MiPass123!`

Tu `.env` quedaría:

```env
app.baseURL = 'https://cesped365.com/'
database.default.database = mi_usuario_cesped365_db
database.default.username = mi_usuario_cesped365_user
database.default.password = MiPass123!
cors.allowedOrigins = https://cesped365.com,https://www.cesped365.com
encryption.key = abc123...xyz789 [la que generaste]
session.cookieDomain = '.cesped365.com'
```

---

**¡Éxito con tu proyecto!** 🌱🚀

Si necesitas ayuda, revisa los archivos de referencia listados arriba.
