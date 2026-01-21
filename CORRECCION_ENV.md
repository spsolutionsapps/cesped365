# 🔧 Corrección del archivo .env en producción

## ❌ **Problema Encontrado**

Tu `.env` tiene:
```env
app.baseURL = 'https://cesped365.com/'
```

Pero debería ser:
```env
app.baseURL = 'https://cesped365.com/api/'
```

**El `/api/` al final es CRÍTICO** porque CodeIgniter necesita saber que está ejecutándose desde la subcarpeta `api/`.

---

## ✅ **Archivo .env Corregido**

Reemplaza tu archivo `public_html/api/.env` con este contenido:

```env
#--------------------------------------------------------------------
# ENVIRONMENT
#--------------------------------------------------------------------

CI_ENVIRONMENT = production

#--------------------------------------------------------------------
# APP
#--------------------------------------------------------------------

app.baseURL = 'https://cesped365.com/api/'
app.indexPage = ''
app.defaultTimezone = 'America/Argentina/Buenos_Aires'

#--------------------------------------------------------------------
# DATABASE
#--------------------------------------------------------------------

database.default.hostname = localhost
database.default.database = cespvcyi_cesped365_db
database.default.username = cespvcyi_cesped365_user
database.default.password = Gojira2019!
database.default.DBDriver = MySQLi
database.default.port = 3306
database.default.charset = utf8mb4
database.default.DBCollat = utf8mb4_general_ci

#--------------------------------------------------------------------
# CORS
#--------------------------------------------------------------------

cors.allowedOrigins = https://cesped365.com,https://www.cesped365.com

#--------------------------------------------------------------------
# ENCRYPTION
#--------------------------------------------------------------------

encryption.key = e691e059a3025c9e0227dc45186b75929372d3de6f196bb0c04de762a2e2d50d

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
session.cookieDomain = '.cesped365.com'
session.cookiePath = '/'
session.cookieSecure = true
session.cookieHTTPOnly = true
session.cookieSameSite = 'Lax'

#--------------------------------------------------------------------
# LOGGER
#--------------------------------------------------------------------

logger.threshold = 4
```

---

## 📝 **Cambios Realizados**

| Campo | Antes | Después | ¿Por qué? |
|-------|-------|---------|-----------|
| `app.baseURL` | `'https://cesped365.com/'` | `'https://cesped365.com/api/'` | **CRÍTICO**: CodeIgniter necesita saber su ruta base |
| `logger.threshold` | `1` | `4` | Más logs para debugging (1=ERROR, 4=DEBUG) |
| `database.default.DBCollat` | (no existía) | `utf8mb4_general_ci` | Collation recomendada |

---

## 🚀 **Cómo Aplicar el Cambio**

### **Opción A: Editar directamente en cPanel**

1. **cPanel → File Manager**
2. **Navegar a:** `public_html/api/.env`
3. **Click derecho → Edit**
4. **Cambiar la línea:**
   ```env
   # ANTES:
   app.baseURL = 'https://cesped365.com/'
   
   # DESPUÉS:
   app.baseURL = 'https://cesped365.com/api/'
   ```
5. **Guardar** (Ctrl + S o botón "Save Changes")

---

### **Opción B: Subir archivo completo**

1. **Crear archivo local** llamado `.env` con el contenido corregido arriba
2. **Subir vía FTP** (FileZilla) a: `public_html/api/.env`
3. **Reemplazar** cuando pregunte

---

## 🔍 **Verificar el Cambio**

Después de hacer el cambio:

### **Test 1: Verificar que se guardó**

En cPanel File Manager:
1. Abrir `public_html/api/.env`
2. Verificar que diga: `app.baseURL = 'https://cesped365.com/api/'`

### **Test 2: Revisar logs del servidor**

1. **cPanel → File Manager**
2. **Ir a:** `public_html/api/writable/logs/`
3. **Abrir el archivo más reciente** (ejemplo: `log-2026-01-13.log`)
4. **Ver qué error específico está dando el login**

**Los logs deberían mostrar el error exacto**, por ejemplo:
- Error de conexión a base de datos
- Tabla no encontrada
- Error de sintaxis SQL
- etc.

---

## 🧪 **Probar el Login**

Después de corregir el `.env`:

1. **Limpiar cache del navegador**:
   - Modo incógnito (Ctrl + Shift + N)
   - O Ctrl + Shift + Del → Borrar caché

2. **Ir a:** `https://cesped365.com/login`

3. **Abrir consola** (F12)

4. **Intentar login** con:
   - Email: `admin@cesped365.com`
   - Password: `password`

5. **Ver los logs en la consola**

---

## 📊 **Logs a Revisar**

Si sigue fallando después del cambio, necesito ver:

### **1. Logs del navegador (Consola F12)**

Debe mostrar algo como:
```
API Request: POST https://cesped365.com/api/login
Response status: 500
Response no es JSON: (el error aquí)
```

### **2. Logs del servidor**

**Archivo:** `public_html/api/writable/logs/log-2026-01-13.log` (fecha de hoy)

**Buscar líneas que contengan:**
- `CRITICAL`
- `ERROR`
- `login`
- `database`

---

## 🎯 **Causas Comunes de Error 500**

Si después de corregir `app.baseURL` sigue dando error 500:

### **Causa 1: Permisos de carpeta `writable/`**

```bash
# En cPanel, verificar permisos:
public_html/api/writable/ → 755
public_html/api/writable/session/ → 755
public_html/api/writable/logs/ → 755
public_html/api/writable/cache/ → 755
```

### **Causa 2: Base de datos incorrecta**

Verifica que el usuario tenga permisos:
1. **cPanel → MySQL Databases**
2. **Add User To Database**
3. **Seleccionar:** Usuario `cespvcyi_cesped365_user` → Base de datos `cespvcyi_cesped365_db`
4. **Marcar:** ALL PRIVILEGES

### **Causa 3: Tabla `users` no existe**

En phpMyAdmin:
```sql
SELECT * FROM users;
```

Si da error "Table doesn't exist":
- Ejecutar `database_setup_simple.sql` de nuevo

### **Causa 4: Password del admin incorrecto**

En phpMyAdmin:
```sql
SELECT id, name, email, role FROM users WHERE role = 'admin';
```

Si no aparece el admin o el email es diferente:
```sql
-- Actualizar el admin
UPDATE users 
SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
WHERE email = 'admin@cesped365.com';
```

---

## 🆘 **Si Sigue Fallando**

1. **Corrige el `app.baseURL`** (CRÍTICO)

2. **Revisa los logs del servidor:**
   - `public_html/api/writable/logs/log-YYYY-MM-DD.log`
   - Copia las últimas 50 líneas y envíamelas

3. **Prueba este comando en la consola del navegador:**
   ```javascript
   fetch('https://cesped365.com/api/login', {
     method: 'POST',
     credentials: 'include',
     headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
     body: 'email=admin@cesped365.com&password=password'
   })
   .then(r => r.text())
   .then(t => console.log(t))
   .catch(e => console.error(e));
   ```
   
   Copia el resultado completo.

---

## ✅ **Resumen**

**Cambio principal:**
```env
# ANTES (❌ INCORRECTO):
app.baseURL = 'https://cesped365.com/'

# DESPUÉS (✅ CORRECTO):
app.baseURL = 'https://cesped365.com/api/'
```

**Después de corregir:**
- Limpiar cache del navegador
- Intentar login
- Ver logs del servidor si sigue fallando
