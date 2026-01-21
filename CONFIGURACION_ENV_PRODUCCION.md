# Configuración del archivo .env para Producción

## Instrucciones

1. **Conectar al servidor vía FTP**
2. **Crear un archivo llamado `.env`** en la carpeta `api/`
3. **Copiar el contenido de abajo** y pegarlo en el archivo
4. **Reemplazar todos los valores entre [CORCHETES]**
5. **Guardar el archivo**

---

## Contenido del archivo .env

```env
#--------------------------------------------------------------------
# CESPED365 - Configuración de Producción
#--------------------------------------------------------------------

CI_ENVIRONMENT = production

#--------------------------------------------------------------------
# APP
#--------------------------------------------------------------------
app.baseURL = 'https://[TU-DOMINIO.com]/'
app.indexPage = ''
app.defaultTimezone = 'America/Argentina/Buenos_Aires'

#--------------------------------------------------------------------
# DATABASE
#--------------------------------------------------------------------
database.default.hostname = localhost
database.default.database = [NOMBRE_BASE_DATOS]
database.default.username = [USUARIO_MYSQL]
database.default.password = [CONTRASEÑA_MYSQL]
database.default.DBDriver = MySQLi
database.default.DBPrefix = 
database.default.port = 3306
database.default.charset = utf8mb4
database.default.DBCollat = utf8mb4_unicode_ci

#--------------------------------------------------------------------
# CORS
#--------------------------------------------------------------------
cors.allowedOrigins = https://[TU-DOMINIO.com],https://www.[TU-DOMINIO.com]

#--------------------------------------------------------------------
# ENCRYPTION
#--------------------------------------------------------------------
encryption.key = [GENERAR_CLAVE_SEGURA_AQUI_MINIMO_32_CARACTERES]

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
session.cookieDomain = '.[TU-DOMINIO.com]'
session.cookiePath = '/'
session.cookieSecure = true
session.cookieHTTPOnly = true
session.cookieSameSite = 'Lax'

#--------------------------------------------------------------------
# LOGGER
#--------------------------------------------------------------------
logger.threshold = 1
```

---

## Valores a reemplazar

### [TU-DOMINIO.com]
Ejemplo: Si tu sitio es `cesped365.com`, reemplazar:
- `app.baseURL = 'https://cesped365.com/'`
- `cors.allowedOrigins = https://cesped365.com,https://www.cesped365.com`
- `session.cookieDomain = '.cesped365.com'`

### [NOMBRE_BASE_DATOS]
El nombre de la base de datos que creaste en cPanel (ej: `cesped365_db`)

### [USUARIO_MYSQL]
El usuario de MySQL que creaste en cPanel (ej: `cesped365_user`)

### [CONTRASEÑA_MYSQL]
La contraseña del usuario MySQL (la que guardaste al crear el usuario)

### [GENERAR_CLAVE_SEGURA_AQUI_MINIMO_32_CARACTERES]

**Opción 1: Generar localmente**
```bash
php -r "echo bin2hex(random_bytes(32));"
```

**Opción 2: Online**
Visitar: https://randomkeygen.com/ → "CodeIgniter Encryption Keys"

Ejemplo de clave (NO USAR ESTA, generar la tuya):
```
a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6a1b2c3d4e5f6g7h8
```

---

## Ejemplo Completo

Si tu sitio es `cesped365.com`, la base de datos es `cesped365_db`, usuario `cesped365_user`, contraseña `MiPass123!`, y generaste la clave `abc123...xyz`, el archivo quedaría así:

```env
CI_ENVIRONMENT = production

app.baseURL = 'https://cesped365.com/'
app.indexPage = ''
app.defaultTimezone = 'America/Argentina/Buenos_Aires'

database.default.hostname = localhost
database.default.database = cesped365_db
database.default.username = cesped365_user
database.default.password = MiPass123!
database.default.DBDriver = MySQLi
database.default.DBPrefix = 
database.default.port = 3306
database.default.charset = utf8mb4
database.default.DBCollat = utf8mb4_unicode_ci

cors.allowedOrigins = https://cesped365.com,https://www.cesped365.com

encryption.key = abc123def456ghi789jkl012mno345pqr678stu901vwx234yz567890abcdef12

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

logger.threshold = 1
```

---

## ⚠️ Importante

- El archivo `.env` es **MUY SENSIBLE** - contiene contraseñas y claves
- **NUNCA** subir `.env` a GitHub o repositorios públicos
- Asegurarse de que `.htaccess` proteja el archivo `.env`
- Cada instalación (desarrollo, producción) debe tener su propio `.env`
