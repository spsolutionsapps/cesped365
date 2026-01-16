# Fase 1: Base de Datos Real - Instrucciones

## ✅ Lo que se creó

### Migraciones (4 tablas):
1. `users` - Usuarios del sistema
2. `gardens` - Jardines de clientes
3. `reports` - Reportes de mantenimiento
4. `report_images` - Imágenes de reportes

### Modelos (4 archivos):
1. `UserModel.php` - Con hash de passwords
2. `GardenModel.php` - Con relaciones
3. `ReportModel.php` - Con métodos helper
4. `ReportImageModel.php` - Para imágenes

### Seeders (3 archivos):
1. `UserSeeder.php` - 5 usuarios (1 admin, 4 clientes)
2. `GardenSeeder.php` - 4 jardines
3. `ReportSeeder.php` - 5 reportes de ejemplo
4. `DatabaseSeeder.php` - Ejecuta todos

---

## 🚀 Cómo Ejecutar

### Paso 1: Habilitar extensión intl (OBLIGATORIO)

```bash
# 1. Abrir: C:\xampp\php\php.ini
# 2. Buscar: ;extension=intl
# 3. Cambiar a: extension=intl
# 4. Guardar y reiniciar Apache
```

### Paso 2: Crear la base de datos

```sql
-- En phpMyAdmin o MySQL CLI:
CREATE DATABASE cesped365 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Paso 3: Verificar configuración de base de datos

El archivo `.env` ya tiene la configuración:
```
database.default.hostname = localhost
database.default.database = cesped365
database.default.username = root
database.default.password = ''
```

**Si usas otro usuario/password, actualiza el `.env`**

### Paso 4: Ejecutar migraciones

```bash
cd "c:\Users\sebas\OneDrive\Documentos\sp-solutions webs\cesped365-api"
php spark migrate
```

**Deberías ver**:
```
Running: 2026-01-13-000001_CreateUsersTable
Running: 2026-01-13-000002_CreateGardensTable
Running: 2026-01-13-000003_CreateReportsTable
Running: 2026-01-13-000004_CreateReportImagesTable
Done
```

### Paso 5: Poblar con datos de prueba

```bash
php spark db:seed DatabaseSeeder
```

**Deberías ver**:
```
Seeded: App\Database\Seeds\UserSeeder
Seeded: App\Database\Seeds\GardenSeeder
Seeded: App\Database\Seeds\ReportSeeder
```

### Paso 6: Verificar datos

```sql
-- En phpMyAdmin o MySQL:
SELECT * FROM users;      -- Debería tener 5 usuarios
SELECT * FROM gardens;    -- Debería tener 4 jardines
SELECT * FROM reports;    -- Debería tener 5 reportes
```

---

## 📊 Estructura de Base de Datos

```
users (5 registros)
├── id, name, email, password, role
├── phone, address
└── created_at, updated_at

gardens (4 registros)
├── id, user_id (FK)
├── address, notes
└── created_at, updated_at

reports (5 registros)
├── id, garden_id (FK), date
├── estado_general, cesped_parejo, color_ok
├── manchas, zonas_desgastadas, malezas_visibles
├── crecimiento_cm, compactacion, humedad, plagas
├── observaciones, jardinero
└── created_at, updated_at

report_images (vacía por ahora)
├── id, report_id (FK)
├── image_path
└── created_at
```

---

## 👥 Datos de Prueba Incluidos

### Usuarios
1. **Admin**: admin@cesped365.com (password: admin123)
2. **Juan Pérez**: cliente@example.com (password: cliente123)
3. **María García**: maria.garcia@example.com
4. **Roberto López**: roberto.lopez@example.com
5. **Ana Martínez**: ana.martinez@example.com

### Jardines
- 4 jardines asignados a los 4 clientes

### Reportes
- 3 reportes para Juan Pérez
- 1 reporte para María García
- 1 reporte para Roberto López

---

## ✅ Verificación

Después de ejecutar las migraciones y seeders:

```bash
# 1. Verificar tablas creadas
php spark db:table users

# 2. Contar registros
mysql -u root -e "USE cesped365; SELECT COUNT(*) FROM users;"
mysql -u root -e "USE cesped365; SELECT COUNT(*) FROM gardens;"
mysql -u root -e "USE cesped365; SELECT COUNT(*) FROM reports;"
```

---

## 🔄 Comandos Útiles

```bash
# Ver estado de migraciones
php spark migrate:status

# Revertir última migración
php spark migrate:rollback

# Revertir todas las migraciones
php spark migrate:rollback -all

# Re-ejecutar migraciones
php spark migrate:refresh

# Re-ejecutar migraciones y seeders
php spark migrate:refresh --all
php spark db:seed DatabaseSeeder
```

---

## 🎯 Siguiente Paso

Una vez que las migraciones estén ejecutadas:

**Fase 2**: Modificar los controladores para usar datos reales de la base de datos en lugar de arrays mock.

---

## 🐛 Problemas Comunes

### Error: "Class Locale not found"
**Solución**: Habilitar `extension=intl` en php.ini

### Error: "Database connection failed"
**Solución**: Verificar que MySQL esté corriendo y que los datos en `.env` sean correctos

### Error: "Table already exists"
**Solución**: 
```bash
php spark migrate:rollback -all
php spark migrate
```

### Error: "Foreign key constraint fails"
**Solución**: Las migraciones se ejecutan en orden. Asegúrate de ejecutar `php spark migrate` (ejecuta todas en orden)

---

**Estado**: ✅ Migraciones y modelos creados  
**Siguiente**: Ejecutar migraciones y poblar datos
