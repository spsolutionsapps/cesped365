# 🔧 Desarrollo Local - Cesped365

## 🚀 Inicio Rápido

### Opción 1: Usar el script automático (RECOMENDADO)

1. Haz doble clic en **`INICIAR-TODO-LOCAL.bat`**
2. Se abrirán 2 ventanas:
   - Backend en `http://localhost:8080`
   - Frontend en `http://localhost:3000`
3. Ve a `http://localhost:3000` en tu navegador

### Opción 2: Manual

**Terminal 1 - Backend:**
```bash
cd api
php spark serve --host=localhost --port=8080
```

**Terminal 2 - Frontend:**
```bash
npm run dev
```

---

## ⚙️ Configuración Requerida

### 1. Base de Datos Local

Crea la base de datos en MySQL/MariaDB:

```sql
CREATE DATABASE cesped365 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Luego ejecuta el archivo SQL:
- `database_setup_simple.sql` (en phpMyAdmin o desde terminal)

### 2. Archivo `.env` del Backend

El archivo `api/.env` debe tener esta configuración:

```env
CI_ENVIRONMENT=development

app.baseURL=http://localhost:8080/api/
app.indexPage=
app.defaultTimezone=America/Argentina/Buenos_Aires
app.forceGlobalSecureRequests=false

database.default.hostname=localhost
database.default.database=cesped365
database.default.username=root
database.default.password=
database.default.DBDriver=MySQLi
database.default.port=3306
database.default.charset=utf8mb4
database.default.DBCollat=utf8mb4_general_ci

encryption.key=hex2bin:c28bc69227541672817df949c70fd2e06123f86051c4cf104fef94c9de001556

session.driver=CodeIgniter\Session\Handlers\FileHandler
session.cookieName=ci_session
session.expiration=7200
session.savePath=writable/session
session.matchIP=false
session.timeToUpdate=300
session.regenerateDestroy=false

logger.threshold=9

cors.allowedOrigins=http://localhost:3000
cors.allowedMethods=GET,POST,PUT,DELETE,OPTIONS
cors.allowedHeaders=Content-Type,Authorization,X-Requested-With
cors.allowCredentials=true
```

**IMPORTANTE:** Ajusta `database.default.username` y `database.default.password` según tu configuración de MySQL local.

### 3. Archivo `.env.development` del Frontend

Ya está configurado correctamente en `.env.development`:

```env
VITE_API_URL=http://localhost:8080/api
```

---

## 🔑 Credenciales de Prueba

### Admin
```
Email: admin@cesped365.com
Password: admin123
```

### Cliente
```
Email: cliente@example.com
Password: cliente123
```

---

## 🐛 Solución de Problemas

### Error: "CORS policy: No 'Access-Control-Allow-Origin'"

**Causa:** El backend no está configurado para permitir `http://localhost:3000`

**Solución:**
1. Verifica que `api/.env` tenga: `cors.allowedOrigins=http://localhost:3000`
2. **Reinicia el servidor backend** (Ctrl+C y vuelve a ejecutar `php spark serve`)

### Error: "404 Not Found" en `/api/me`

**Causa:** El backend no está corriendo o la ruta está mal

**Solución:**
1. Verifica que el backend esté corriendo en `http://localhost:8080`
2. Prueba acceder a: `http://localhost:8080/api/` (debería devolver JSON)
3. Verifica que `app.baseURL` en `api/.env` sea `http://localhost:8080/api/`

### Error: "Database connection failed"

**Causa:** Credenciales de MySQL incorrectas

**Solución:**
1. Verifica que MySQL esté corriendo
2. Verifica las credenciales en `api/.env`
3. Crea la base de datos si no existe: `CREATE DATABASE cesped365;`

---

## 📝 Notas Importantes

- **NO commitees** el archivo `api/.env` (ya está en `.gitignore`)
- Cada desarrollador debe tener su propio `api/.env` con sus credenciales locales
- El backend debe reiniciarse cada vez que cambies el `.env`
- El frontend se recarga automáticamente con Vite

---

## ✅ Verificación

Si todo está bien configurado:

1. `http://localhost:8080/api/` → Debería devolver JSON con `{"success":true,...}`
2. `http://localhost:3000` → Debería cargar la landing page
3. Login debería funcionar sin errores de CORS

---

**¡Listo para desarrollar!** 🚀
