# 🛠️ Comandos Útiles - Cesped365

Comandos y scripts útiles para administrar el sitio en producción.

---

## 📊 Verificar Estado del Sitio

### Ver logs del backend
```bash
# Vía SSH (si tienes acceso)
tail -f ~/public_html/api/writable/logs/log-$(date +%Y-%m-%d).php

# O descargar vía FTP:
# Archivo: api/writable/logs/log-YYYY-MM-DD.php
```

### Verificar versión de PHP
```bash
php -v
```

### Verificar extensiones de PHP
```bash
php -m | grep -E 'mysqli|mbstring|intl|json|curl'
```

---

## 🗄️ Base de Datos

### Backup de la base de datos (vía SSH)
```bash
mysqldump -u USUARIO_MYSQL -p NOMBRE_BASE_DATOS > backup_$(date +%Y%m%d).sql
```

### Restaurar base de datos (vía SSH)
```bash
mysql -u USUARIO_MYSQL -p NOMBRE_BASE_DATOS < backup_20260118.sql
```

### Ver tablas de la base de datos
```sql
SHOW TABLES;
```

### Ver usuarios en la base de datos
```sql
SELECT id, name, email, role, plan, estado FROM users;
```

### Contar registros
```sql
SELECT 
  (SELECT COUNT(*) FROM users) as total_usuarios,
  (SELECT COUNT(*) FROM users WHERE role='cliente') as total_clientes,
  (SELECT COUNT(*) FROM gardens) as total_jardines,
  (SELECT COUNT(*) FROM reports) as total_reportes;
```

---

## 👤 Gestión de Usuarios

### Crear nuevo usuario admin (vía SQL)
```sql
INSERT INTO users (name, email, password, role, phone, plan, estado)
VALUES (
  'Nuevo Admin',
  'nuevo@admin.com',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: admin123
  'admin',
  '+54 11 1234-5678',
  'Urbano',
  'Activo'
);
```

### Cambiar contraseña de un usuario (vía SQL)
```sql
-- Generar hash primero (en PHP local):
-- php -r "echo password_hash('nueva_password', PASSWORD_DEFAULT);"

UPDATE users 
SET password = '$2y$10$HASH_GENERADO_AQUI'
WHERE email = 'admin@cesped365.com';
```

### Ver último login (si implementas auditoría en el futuro)
```sql
SELECT name, email, role, created_at, updated_at 
FROM users 
ORDER BY updated_at DESC 
LIMIT 10;
```

---

## 🧹 Mantenimiento

### Limpiar logs antiguos (vía SSH)
```bash
# Borrar logs de más de 30 días
find ~/public_html/api/writable/logs/ -name "log-*.php" -mtime +30 -delete
```

### Limpiar sesiones antiguas (vía SSH)
```bash
# Borrar sesiones de más de 7 días
find ~/public_html/api/writable/session/ -name "ci_session*" -mtime +7 -delete
```

### Limpiar cache (vía SSH)
```bash
rm -rf ~/public_html/api/writable/cache/*
```

### Ver espacio usado
```bash
du -sh ~/public_html/
du -sh ~/public_html/api/writable/uploads/
```

---

## 🔍 Debugging

### Test de conexión a la base de datos (crear archivo test_db.php)
```php
<?php
// Subir a public_html/test_db.php
// Visitar: https://tudominio.com/test_db.php
// ¡BORRAR después de probar!

$hostname = 'localhost';
$database = 'NOMBRE_BD';
$username = 'USUARIO';
$password = 'PASSWORD';

try {
    $conn = new mysqli($hostname, $username, $password, $database);
    
    if ($conn->connect_error) {
        die("❌ Error de conexión: " . $conn->connect_error);
    }
    
    echo "✅ Conexión exitosa a la base de datos!<br>";
    echo "📊 Base de datos: $database<br>";
    
    $result = $conn->query("SHOW TABLES");
    echo "📋 Tablas encontradas: " . $result->num_rows . "<br><br>";
    
    while($row = $result->fetch_array()) {
        echo "- " . $row[0] . "<br>";
    }
    
    $conn->close();
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
```

### Test de API (vía curl)
```bash
# Test login
curl -X POST https://tudominio.com/api/login \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "email=admin@cesped365.com&password=admin123"

# Test endpoint autenticado (necesitas cookie de sesión)
curl -X GET https://tudominio.com/api/me \
  -H "Cookie: cesped365_session=VALOR_DE_SESION"
```

### Ver errores de PHP (temporal, solo para debug)
```php
<?php
// Agregar al inicio de api/public/index.php (SOLO para debug)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ¡REMOVER después de solucionar el problema!
```

---

## 📦 Deployment

### Build local del frontend
```bash
# En tu PC local
npm run build

# Los archivos se generan en dist/
# Subir contenido de dist/ vía FTP a public_html/
```

### Actualizar solo el backend vía FTP
Subir solo estas carpetas:
```
api/app/          ← Controladores, modelos
api/public/       ← Entrada del backend
```

**NO sobrescribir:**
```
api/.env          ← Configuración de producción
api/writable/     ← Logs, sesiones, uploads
```

### Deployment automático (GitHub Actions)
```bash
# 1. Hacer cambios en local
git add .
git commit -m "Descripción de cambios"
git push origin main

# 2. GitHub Actions automáticamente:
#    - Build del frontend
#    - Deploy vía FTP
```

---

## 🔐 Seguridad

### Verificar permisos de archivos
```bash
# Vía SSH
ls -la ~/public_html/api/writable/

# Permisos correctos:
# drwxr-xr-x (755) para carpetas
# -rw-r--r-- (644) para archivos
```

### Cambiar permisos si es necesario
```bash
# Carpetas
chmod 755 ~/public_html/api/writable/
chmod 755 ~/public_html/api/writable/cache/
chmod 755 ~/public_html/api/writable/logs/
chmod 755 ~/public_html/api/writable/session/
chmod 755 ~/public_html/api/writable/uploads/

# Archivos dentro de writable
find ~/public_html/api/writable/ -type f -exec chmod 644 {} \;
```

### Verificar que archivos sensibles estén protegidos
```bash
# Estos comandos NO deben mostrar el contenido:
curl https://tudominio.com/api/.env
curl https://tudominio.com/api/composer.json
curl https://tudominio.com/api/app/

# Todos deben dar error 403 o 404
```

---

## 📈 Optimización

### Optimizar tablas de MySQL (vía phpMyAdmin)
```sql
OPTIMIZE TABLE users, gardens, reports, report_images;
```

### Ver uso de espacio por tabla
```sql
SELECT 
  table_name AS 'Tabla',
  ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Tamaño (MB)'
FROM information_schema.TABLES 
WHERE table_schema = 'NOMBRE_BASE_DATOS'
ORDER BY (data_length + index_length) DESC;
```

### Habilitar compresión GZIP (en .htaccess)
```apache
# Agregar a public_html/.htaccess
<IfModule mod_deflate.c>
  AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript
</IfModule>
```

---

## 🔄 Actualización de Datos

### Cambiar plan de un cliente
```sql
UPDATE users 
SET plan = 'Residencial', estado = 'Activo'
WHERE email = 'cliente@example.com';
```

### Ver reportes recientes
```sql
SELECT r.id, r.date, u.name as cliente, r.estado_general, r.jardinero
FROM reports r
INNER JOIN gardens g ON r.garden_id = g.id
INNER JOIN users u ON g.user_id = u.id
ORDER BY r.date DESC
LIMIT 10;
```

### Eliminar reportes antiguos (más de 1 año)
```sql
DELETE FROM reports 
WHERE date < DATE_SUB(CURDATE(), INTERVAL 1 YEAR);
```

---

## 📞 Comandos de Emergencia

### Sitio caído - Verificar
```bash
# 1. Verificar que Apache/Nginx esté corriendo
ps aux | grep apache
ps aux | grep nginx

# 2. Verificar que MySQL esté corriendo
ps aux | grep mysql

# 3. Ver últimos errores de PHP
tail -n 50 ~/public_html/api/writable/logs/log-$(date +%Y-%m-%d).php

# 4. Ver errores del servidor
tail -n 50 /var/log/apache2/error.log  # o la ruta de tu servidor
```

### Reset completo de sesiones (si hay problemas de login)
```bash
# Vía SSH
rm -rf ~/public_html/api/writable/session/*

# O vía FTP: Borrar todos los archivos dentro de api/writable/session/
```

### Restaurar desde backup
```bash
# 1. Restaurar archivos vía FTP
# 2. Restaurar base de datos:
mysql -u USUARIO -p NOMBRE_BD < backup.sql
```

---

## 📝 Notas

- **Siempre hacer backup antes de cambios importantes**
- **Probar en local/staging antes de actualizar producción**
- **Los comandos SSH pueden variar según tu hosting**
- **Algunos hostings no permiten acceso SSH - usar cPanel**

---

## 🆘 Soporte

Si algo sale mal:
1. Revisar logs: `api/writable/logs/`
2. Verificar `.env` está configurado correctamente
3. Verificar permisos de `writable/`
4. Consultar `DESPLIEGUE_PRODUCCION.md` para solución de problemas
