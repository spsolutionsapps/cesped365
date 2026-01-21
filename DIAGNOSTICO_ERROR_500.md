# 🔍 DIAGNÓSTICO: Error 500 en test-permisos.php

## 🐛 **Problema**

Si `test-permisos.php` da error 500, hay varias causas posibles:

1. ❌ PHP con errores de sintaxis (poco probable)
2. ❌ Permisos del archivo incorrecto
3. ❌ **El archivo está en la carpeta incorrecta** (MÁS PROBABLE)
4. ❌ `.htaccess` bloqueando la ejecución
5. ❌ PHP deshabilitado en esa carpeta

---

## ✅ **SOLUCIÓN 1: Verificar ubicación del archivo**

### **¿Dónde está tu sitio?**

En tu hosting, la estructura debería ser:

```
public_html/
  ├── index.html          (Frontend - landing)
  ├── assets/             (Frontend - CSS, JS)
  └── api/                (Backend - CodeIgniter)
      └── public/
          ├── index.php   (Punto de entrada del API)
          └── .htaccess
```

### **Problema Común: Document Root incorrecto**

Tu hosting puede estar configurado para servir archivos desde:

**Opción A:** `public_html/api/` (raíz de api)
**Opción B:** `public_html/api/public/` (carpeta public dentro de api)

---

## 🧪 **TEST: ¿Dónde subir los archivos?**

Vamos a probar 3 ubicaciones diferentes:

### **Test 1: Subir a `public_html/api/`**

1. **Crear archivo:** `test1.php`
   ```php
   <?php echo "Test 1: Carpeta api/"; ?>
   ```

2. **Subir a:** `public_html/api/test1.php`

3. **Visitar:** `https://cesped365.com/api/test1.php`

**Resultado esperado:**
- ✅ Si muestra "Test 1: Carpeta api/" → Los archivos van en `api/`
- ❌ Si da 404 o 500 → Probar siguiente ubicación

---

### **Test 2: Subir a `public_html/api/public/`**

1. **Crear archivo:** `test2.php`
   ```php
   <?php echo "Test 2: Carpeta api/public/"; ?>
   ```

2. **Subir a:** `public_html/api/public/test2.php`

3. **Visitar:** `https://cesped365.com/api/test2.php`

**Resultado esperado:**
- ✅ Si muestra "Test 2: Carpeta api/public/" → Los archivos van en `api/public/`
- ❌ Si da 404 o 500 → Revisar configuración

---

### **Test 3: Verificar Document Root en cPanel**

1. **cPanel → Domains** (o "Addon Domains" o "Subdomains")

2. **Buscar:** `cesped365.com`

3. **Ver "Document Root":**
   - Si dice: `public_html` → Tu API debe estar en `public_html/api/public/`
   - Si dice: `public_html/api` → Configuración especial

---

## ✅ **SOLUCIÓN 2: Verificar permisos del archivo PHP**

Si el archivo está en la ubicación correcta pero da 500:

### **En cPanel File Manager:**

1. **Click derecho en `test-permisos.php`** → **Change Permissions**

2. **Debe ser:** `644`
   - ✅ Read (Owner)
   - ✅ Write (Owner)
   - ✅ Read (Group)
   - ✅ Read (Public)

3. **NO debe ser:** `777` (los archivos PHP no necesitan execute)

---

## ✅ **SOLUCIÓN 3: Revisar estructura de CodeIgniter**

CodeIgniter 4 tiene esta estructura:

```
api/                          ← Carpeta principal (NO accesible desde web)
├── app/                      ← Código de la aplicación
├── writable/                 ← Logs, cache, sessions
├── vendor/                   ← Dependencias de Composer
└── public/                   ← Única carpeta accesible desde web
    ├── index.php             ← Punto de entrada
    ├── .htaccess             ← Reglas de reescritura
    └── uploads/              ← Archivos subidos
```

### **Configuración correcta en el servidor:**

**Opción A: Subcarpeta dentro de public_html**

```
public_html/
├── index.html                ← Frontend
├── assets/                   ← Frontend assets
└── api/                      ← Copiar TODA la carpeta api/
    ├── app/
    ├── writable/
    ├── vendor/
    └── public/               ← Esta es la que debe servir el servidor
        └── index.php
```

**Configurar en cPanel:**

1. cPanel → **PHP** (o "Select PHP Version")
2. Buscar **"Document Root"** o crear un **Subdomain/Addon Domain**
3. Document Root para `/api` debe apuntar a: `public_html/api/public`

---

**Opción B: Todo en public_html (estructura plana)**

```
public_html/
├── index.html                ← Frontend
├── assets/                   ← Frontend
├── index.php                 ← Backend (api/public/index.php copiado aquí)
├── .htaccess                 ← Backend htaccess
├── app/                      ← Backend app/
├── writable/                 ← Backend writable/
├── vendor/                   ← Backend vendor/
└── uploads/                  ← Backend uploads/
```

Pero esto requiere modificar el `.htaccess` del frontend para no interferir.

---

## ✅ **SOLUCIÓN 4: Crear archivo de diagnóstico simple**

Sube estos 2 archivos para diagnosticar:

### **Archivo 1: `test-simple.php`**

```php
<?php
echo "PHP funciona!<br>";
echo "Versión: " . phpversion() . "<br>";
echo "Directorio actual: " . __DIR__ . "<br>";
echo "Usuario PHP: " . get_current_user() . "<br>";
?>
```

### **Archivo 2: `test-writable.php`**

```php
<?php
$dir = __DIR__ . '/writable/logs/';
echo "Directorio: $dir<br>";
echo "Existe: " . (file_exists($dir) ? 'SÍ' : 'NO') . "<br>";
echo "Escribible: " . (is_writable($dir) ? 'SÍ' : 'NO') . "<br>";

$testFile = $dir . 'test.txt';
if (@file_put_contents($testFile, 'test')) {
    echo "✅ Se pudo escribir en writable/logs/<br>";
    @unlink($testFile);
} else {
    echo "❌ NO se pudo escribir en writable/logs/<br>";
    echo "Error: " . error_get_last()['message'];
}
?>
```

---

## 🎯 **Pasos a Seguir AHORA**

1. **Sube `test-simple.php` a `public_html/api/`**
2. **Visita:** `https://cesped365.com/api/test-simple.php`
3. **¿Qué pasa?**
   - ✅ Si funciona → Problema es de permisos o ruta de `writable/`
   - ❌ Si da 500 → Problema de configuración del servidor

4. **Si da 500, sube `test-simple.php` a `public_html/api/public/`**
5. **Visita:** `https://cesped365.com/api/test-simple.php`
6. **¿Qué pasa ahora?**

---

## 📊 **Tabla de Diagnóstico**

| Ubicación del archivo | URL | Resultado | Acción |
|----------------------|-----|-----------|--------|
| `public_html/api/test-simple.php` | `/api/test-simple.php` | ✅ Funciona | Archivos PHP van en `api/` |
| `public_html/api/test-simple.php` | `/api/test-simple.php` | ❌ 500 | Probar en `api/public/` |
| `public_html/api/public/test-simple.php` | `/api/test-simple.php` | ✅ Funciona | Archivos PHP van en `api/public/` |
| `public_html/api/public/test-simple.php` | `/api/test-simple.php` | ❌ 404 | Configurar Document Root |

---

## 🚨 **Causa Más Probable**

Si `test-permisos.php` da error 500, probablemente:

1. **El archivo está en la carpeta incorrecta**
2. **El Document Root está mal configurado**
3. **Los archivos PHP deben estar en `api/public/`, no en `api/`**

---

## 📞 **Siguiente Paso**

Sube `test-simple.php` a:
1. `public_html/api/test-simple.php`
2. `public_html/api/public/test-simple.php`

Visita ambas URLs y dime cuál funciona:
- `https://cesped365.com/api/test-simple.php`

**Eso me dirá exactamente dónde están los archivos accesibles.**
