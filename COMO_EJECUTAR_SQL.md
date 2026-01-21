# 🗄️ Cómo Ejecutar los Scripts SQL en phpMyAdmin

Si tuviste errores al ejecutar el SQL, sigue esta guía paso a paso.

---

## 🎯 Opción 1: Script Completo (Recomendado)

Usa el archivo: **`database_setup_simple.sql`**

### Pasos:

1. **Abrir cPanel → phpMyAdmin**

2. **Seleccionar tu base de datos** (ejemplo: `cesped365_db`)
   - En el panel izquierdo, click en el nombre de tu base de datos

3. **Ir a la pestaña "SQL"**
   - Está en el menú superior

4. **Abrir el archivo `database_setup_simple.sql` en Notepad**
   - Click derecho → Abrir con → Notepad (o cualquier editor de texto)

5. **Copiar TODO el contenido** (Ctrl + A, Ctrl + C)

6. **Pegar en phpMyAdmin**
   - En el cuadro grande de texto de la pestaña SQL
   - Ctrl + V

7. **Scroll hasta abajo y click en "Go" o "Ejecutar"**

8. **Verificar resultado:**
   - Deberías ver mensajes verdes de éxito
   - Ejemplo: "4 tablas creadas", "1 fila insertada"

---

## 🔧 Opción 2: Paso a Paso (Si Opción 1 falla)

Usa el archivo: **`database_paso_a_paso.sql`**

Este archivo tiene las queries **separadas** por pasos.

### Si phpMyAdmin da errores con el script completo:

1. **Abrir `database_paso_a_paso.sql`**

2. **Copiar SOLO desde `SET FOREIGN_KEY_CHECKS=0;` hasta `SET FOREIGN_KEY_CHECKS=1;`**
   ```sql
   SET FOREIGN_KEY_CHECKS=0;
   DROP TABLE IF EXISTS `report_images`;
   DROP TABLE IF EXISTS `reports`;
   DROP TABLE IF EXISTS `gardens`;
   DROP TABLE IF EXISTS `users`;
   SET FOREIGN_KEY_CHECKS=1;
   ```
   - Pegar en phpMyAdmin → Ejecutar

3. **Copiar SOLO la query de CREATE TABLE users**
   ```sql
   CREATE TABLE `users` (
     ...
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
   ```
   - Pegar en phpMyAdmin → Ejecutar

4. **Repetir para cada tabla:**
   - `CREATE TABLE gardens`
   - `CREATE TABLE reports`
   - `CREATE TABLE report_images`

5. **Finalmente, copiar y ejecutar el INSERT del admin:**
   ```sql
   INSERT INTO `users` (`name`, `email`, `password`, `role`, `phone`, `address`, `plan`, `estado`) 
   VALUES (...);
   ```

---

## 🚨 Opción 3: Tabla por Tabla (Hosting muy problemático)

Si aún así falla, ejecuta **UNA QUERY A LA VEZ**.

### PASO 1: Eliminar tablas viejas

```sql
SET FOREIGN_KEY_CHECKS=0;
```
**Ejecutar** ✓

```sql
DROP TABLE IF EXISTS `report_images`;
```
**Ejecutar** ✓

```sql
DROP TABLE IF EXISTS `reports`;
```
**Ejecutar** ✓

```sql
DROP TABLE IF EXISTS `gardens`;
```
**Ejecutar** ✓

```sql
DROP TABLE IF EXISTS `users`;
```
**Ejecutar** ✓

```sql
SET FOREIGN_KEY_CHECKS=1;
```
**Ejecutar** ✓

---

### PASO 2: Crear tabla USERS

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
**Ejecutar** ✓

---

### PASO 3: Crear tabla GARDENS

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
**Ejecutar** ✓

---

### PASO 4: Crear tabla REPORTS

```sql
CREATE TABLE `reports` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `garden_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `visit_date` date NOT NULL,
  `status` enum('completado','pendiente','cancelado') DEFAULT 'pendiente',
  `grass_height_cm` decimal(5,2) DEFAULT NULL,
  `grass_health` enum('excelente','bueno','regular','malo') DEFAULT NULL,
  `watering_status` enum('optimo','requiere_ajuste','insuficiente') DEFAULT NULL,
  `pest_detected` tinyint(1) DEFAULT 0,
  `pest_description` text DEFAULT NULL,
  `work_done` text DEFAULT NULL,
  `recommendations` text DEFAULT NULL,
  `next_visit` date DEFAULT NULL,
  `growth_cm` decimal(5,2) DEFAULT NULL,
  `fertilizer_applied` tinyint(1) DEFAULT 0,
  `fertilizer_type` varchar(100) DEFAULT NULL,
  `weather_conditions` varchar(100) DEFAULT NULL,
  `technician_notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `garden_id` (`garden_id`),
  KEY `user_id` (`user_id`),
  KEY `visit_date` (`visit_date`),
  KEY `status` (`status`),
  CONSTRAINT `reports_garden_id_foreign` FOREIGN KEY (`garden_id`) REFERENCES `gardens` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reports_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```
**Ejecutar** ✓

---

### PASO 5: Crear tabla REPORT_IMAGES

```sql
CREATE TABLE `report_images` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `report_id` int(11) UNSIGNED NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `image_type` enum('before','after','detail','problem') DEFAULT 'detail',
  `description` varchar(255) DEFAULT NULL,
  `uploaded_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `report_id` (`report_id`),
  CONSTRAINT `report_images_report_id_foreign` FOREIGN KEY (`report_id`) REFERENCES `reports` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```
**Ejecutar** ✓

---

### PASO 6: Insertar usuario ADMIN

```sql
INSERT INTO `users` (`name`, `email`, `password`, `role`, `phone`, `address`, `plan`, `estado`) 
VALUES (
  'Administrador',
  'admin@cesped365.com',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  'admin',
  NULL,
  NULL,
  'Urbano',
  'Activo'
);
```
**Ejecutar** ✓

---

## ✅ Verificación

Después de ejecutar todo:

1. **En phpMyAdmin, panel izquierdo**, deberías ver **4 tablas**:
   - ✓ users
   - ✓ gardens
   - ✓ reports
   - ✓ report_images

2. **Click en la tabla `users`**
   - Debería haber **1 fila** (el admin)

3. **Probar login:**
   - Email: `admin@cesped365.com`
   - Password: `password`

---

## 🐛 Errores Comunes

### Error: "Unknown collation: 'utf8mb4_unicode_ci'"

Tu MySQL es muy viejo. Cambia en TODAS las queries:

```sql
# ANTES:
CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci

# DESPUÉS:
CHARSET=utf8 COLLATE=utf8_general_ci
```

---

### Error: "Syntax error near 'DEFAULT CURRENT_TIMESTAMP'"

Tu MySQL no soporta `DEFAULT CURRENT_TIMESTAMP` en `datetime`.

**Solución 1:** Cambiar `datetime` por `timestamp`:

```sql
# ANTES:
`created_at` datetime DEFAULT CURRENT_TIMESTAMP

# DESPUÉS:
`created_at` timestamp DEFAULT CURRENT_TIMESTAMP
```

**Solución 2:** Eliminar el `DEFAULT`:

```sql
`created_at` datetime
```

---

### Error: "Cannot add foreign key constraint"

Las tablas deben crearse **en este orden**:
1. `users` (primero)
2. `gardens` (depende de users)
3. `reports` (depende de gardens y users)
4. `report_images` (depende de reports)

Si cambiaste el orden, elimina todas y empieza de nuevo.

---

### Error: "Duplicate entry 'admin@cesped365.com'"

El admin ya existe.

**Solución:**

```sql
# Eliminar admin viejo
DELETE FROM `users` WHERE `email` = 'admin@cesped365.com';

# Insertar de nuevo
INSERT INTO `users` ...
```

---

## 📞 Ayuda Adicional

Si NINGUNA opción funciona:

1. **Exportar error completo:**
   - Copiar TODO el mensaje de error de phpMyAdmin
   - Enviarme el error

2. **Verificar versión de MySQL:**
   ```sql
   SELECT VERSION();
   ```
   - Ejecutar esto en phpMyAdmin
   - Enviame la versión

3. **Verificar que la base de datos existe:**
   - cPanel → MySQL Databases
   - Debe aparecer tu base de datos en la lista

---

## 🎯 Resumen Rápido

| Archivo | Cuándo usarlo |
|---------|---------------|
| `database_setup_simple.sql` | Hosting normal (PRIMERO INTENTAR ESTO) |
| `database_paso_a_paso.sql` | Si el anterior falla |
| Este documento (Opción 3) | Si todo falla, query por query |

---

**¿Qué error específico te apareció en phpMyAdmin?** Cópialo completo y te ayudo a resolverlo.
