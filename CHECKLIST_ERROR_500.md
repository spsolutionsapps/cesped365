# ✅ CHECKLIST: Solucionar Error 500

## 🎯 **Sigue estos pasos EN ORDEN**

---

## ☐ PASO 1: Corregir app.baseURL en .env

**Archivo:** `public_html/api/.env`

**Cambiar:**
```env
# INCORRECTO ❌
app.baseURL = 'https://cesped365.com/'

# CORRECTO ✅
app.baseURL = 'https://cesped365.com/api/'
```

**Cómo:**
1. cPanel → File Manager
2. Ir a `public_html/api/.env`
3. Click derecho → Edit
4. Cambiar la línea
5. Guardar (Ctrl + S)

---

## ☐ PASO 2: Cambiar permisos de writable/

**Carpeta:** `public_html/api/writable/`

**Permisos:** `777` (temporalmente)

**Cómo:**
1. cPanel → File Manager
2. Ir a `public_html/api/`
3. Click derecho en carpeta `writable` → **Change Permissions**
4. Escribir: **777** en el campo numérico
5. ✅ Marcar: **"Recurse into subdirectories"**
6. Click: **"Change Permissions"**

**Debe quedar así:**
```
✅ Read, Write, Execute
✅ Read, Write, Execute
✅ Read, Write, Execute
```

---

## ☐ PASO 3: Verificar estructura de carpetas

**En:** `public_html/api/writable/`

**Debe haber:**
- ✅ `cache/`
- ✅ `logs/`
- ✅ `session/`
- ✅ `uploads/`

**Si falta alguna:**
1. Click derecho → New Folder
2. Crear la carpeta
3. Cambiar permisos a 777

---

## ☐ PASO 4: Limpiar cache del navegador

**Opción A:**
- Abrir navegador en **modo incógnito** (Ctrl + Shift + N)

**Opción B:**
- Ctrl + Shift + Del
- Marcar "Imágenes y archivos en caché"
- Borrar

---

## ☐ PASO 5: Probar login

1. **Ir a:** `https://cesped365.com/login`
2. **Credenciales:**
   - Email: `admin@cesped365.com`
   - Password: `password`
3. **Click en "Iniciar sesión"**

---

## ✅ **Si Funciona**

Deberías:
- ✅ Entrar al dashboard
- ✅ Ver tu nombre en la esquina superior derecha

**Siguiente paso:**
- Cambiar permisos de `writable/` de 777 a **755** por seguridad

---

## ❌ **Si SIGUE Fallando**

### **Verificar que se creó el log:**

1. **cPanel → File Manager**
2. **Ir a:** `public_html/api/writable/logs/`
3. **Buscar archivo:** `log-2026-01-13.log` (fecha de hoy)
4. **Si existe:** Abrirlo y buscar la palabra `ERROR` o `CRITICAL`
5. **Si NO existe:** Problema de permisos persiste

---

### **Test de permisos:**

1. **Subir** `test-permisos.php` a `public_html/api/`
2. **Visitar:** `https://cesped365.com/api/test-permisos.php`
3. **Screenshot** y enviarme

---

### **Verificar base de datos:**

**phpMyAdmin:**

```sql
-- Verificar que tabla users existe
SHOW TABLES;

-- Debe aparecer: users, gardens, reports, report_images

-- Verificar que admin existe
SELECT * FROM users WHERE role = 'admin';

-- Debe aparecer 1 fila con email: admin@cesped365.com
```

---

## 🚨 **Errores Comunes**

### **Error: Permisos no cambian**

**Causa:** Hosting restrictivo

**Solución:**
- Usar 777 en lugar de 755
- Contactar soporte del hosting
- Pedir que cambien el dueño de las carpetas al usuario de PHP

---

### **Error: "Permission denied"**

**Causa:** PHP no puede escribir en `writable/`

**Solución:**
1. Eliminar carpeta `writable/`
2. Volver a crear desde cPanel
3. Cambiar permisos a 777
4. Crear subcarpetas: `cache/`, `logs/`, `session/`, `uploads/`

---

### **Error: "Table 'users' doesn't exist"**

**Causa:** Base de datos no configurada

**Solución:**
1. phpMyAdmin → Seleccionar tu base de datos
2. Pestaña SQL
3. Ejecutar `database_setup_simple.sql`

---

## 📋 **Resumen de Permisos**

```
public_html/api/writable/           → 777 (o 755)
public_html/api/writable/cache/     → 777 (o 755)
public_html/api/writable/logs/      → 777 (o 755)
public_html/api/writable/session/   → 777 (o 755)
public_html/api/writable/uploads/   → 777 (o 755)
```

**Temporalmente usar 777, después cambiar a 755**

---

## 🎯 **Estado Esperado**

Después de seguir todos los pasos:

```
✅ app.baseURL = 'https://cesped365.com/api/'
✅ writable/ con permisos 777
✅ Subcarpetas creadas (cache, logs, session, uploads)
✅ Base de datos configurada con tablas
✅ Usuario admin existe en la tabla users
```

---

**¿Cambiaste los permisos de `writable/` a 777?**

**¿Se creó el archivo de log después de intentar login?**
