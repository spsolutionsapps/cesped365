# 🔍 Diagnóstico Error 404 en Producción

Tu sitio muestra `404 Not Found` en `https://cesped365.com/`

---

## ✅ PASO 1: Verificar que los archivos estén en el servidor

### Vía cPanel File Manager:

1. **Ir a cPanel → File Manager**
2. **Navegar a `public_html/`**
3. **Verificar que existan estos archivos:**

```
public_html/
├── index.html          ← ⚠️ DEBE EXISTIR
├── assets/             ← Carpeta con CSS y JS
│   ├── index-[hash].js
│   └── index-[hash].css
├── .htaccess           ← Importante para routing
└── favicon.ico
```

### ❌ Si NO existe `index.html`:

**Causa:** Los archivos del frontend no se subieron correctamente.

**Solución:**
1. En tu PC local, ejecutar: `npm run build`
2. Subir TODO el contenido de `dist/` → `public_html/`
3. Usar FileZilla o cPanel File Manager

---

## ✅ PASO 2: Verificar configuración de Apache

### En cPanel:

1. **MultiPHP Manager:**
   - Verificar que tu dominio tenga **PHP 8.1** o superior seleccionado

2. **MultiPHP INI Editor:**
   - Seleccionar tu dominio
   - Buscar: `display_errors = On` (temporal, para ver errores)

3. **Apache Handlers (si existe):**
   - Verificar que no haya handlers conflictivos

---

## ✅ PASO 3: Verificar archivo .htaccess

### 3.1 Verificar que existe `public_html/.htaccess`

**Vía File Manager:**
1. **Ir a `public_html/`**
2. **Buscar `.htaccess`**
3. **Si NO existe, crearlo**

### 3.2 Contenido del .htaccess

El archivo `public_html/.htaccess` debe tener:

```apache
<IfModule mod_rewrite.c>
  RewriteEngine On
  
  # Redirigir HTTP a HTTPS (opcional, comentar si no tienes SSL)
  RewriteCond %{HTTPS} off
  RewriteRule ^(.*)$ https://%{HTTP_HOST}/$1 [R=301,L]
  
  # No redirigir si es API
  RewriteCond %{REQUEST_URI} ^/api/
  RewriteRule ^ - [L]
  
  # Servir archivos estáticos directamente
  RewriteCond %{REQUEST_FILENAME} -f [OR]
  RewriteCond %{REQUEST_FILENAME} -d
  RewriteRule ^ - [L]
  
  # Redirigir todo lo demás a index.html (SPA)
  RewriteRule ^ index.html [L]
</IfModule>

# Página de error personalizada
ErrorDocument 404 /index.html

# Seguridad
<IfModule mod_headers.c>
  Header set X-Content-Type-Options "nosniff"
  Header set X-Frame-Options "SAMEORIGIN"
  Header set X-XSS-Protection "1; mode=block"
</IfModule>
```

### 3.3 Si el hosting NO soporta .htaccess:

**Crear archivo `index.php` en `public_html/`:**

```php
<?php
// Redirección simple a index.html
header('Location: /index.html');
exit;
?>
```

---

## ✅ PASO 4: Verificar configuración del dominio

### 4.1 En cPanel → Domains:

- **Document Root** del dominio debe apuntar a: `public_html/`
- NO debe apuntar a `public_html/public/` ni a otra carpeta

### 4.2 Verificar redirecciones:

- **cPanel → Redirects**
- Verificar que NO haya redirecciones activas que interfieran

---

## ✅ PASO 5: Probar desde el servidor directamente

### 5.1 Via cPanel File Manager:

1. Ir a `public_html/index.html`
2. Click derecho → **View**
3. Si se ve el contenido HTML → archivo correcto
4. Si da error 404 → archivo no existe o permisos incorrectos

### 5.2 Verificar permisos:

```
index.html → 644 (-rw-r--r--)
.htaccess  → 644 (-rw-r--r--)
assets/    → 755 (drwxr-xr-x)
```

**En cPanel File Manager:**
- Click derecho en `index.html` → **Permissions**
- Marcar: Owner: Read+Write, Group: Read, Public: Read
- Valor numérico: **644**

---

## ✅ PASO 6: Posibles causas específicas de hosting barato

### Causa 1: `public_html` no es la carpeta correcta

Algunos hostings usan:
- `www/` en lugar de `public_html/`
- `httpdocs/`
- `public_html/www/`
- La raíz `/` directamente

**Solución:**
1. En cPanel, buscar "Document Root"
2. Ver qué carpeta usa tu dominio
3. Subir archivos a ESA carpeta

---

### Causa 2: Dominio apunta a carpeta incorrecta

**Solución:**
1. **cPanel → Addon Domains** o **Domains**
2. Ver a qué carpeta apunta `cesped365.com`
3. Cambiar Document Root a `public_html/` o a donde subiste los archivos

---

### Causa 3: Cache del navegador o CDN

**Solución:**
1. Abrir navegador en **modo incógnito**
2. Ir a `https://cesped365.com/`
3. O limpiar cache: Ctrl + Shift + Del

---

### Causa 4: mod_rewrite no habilitado

**Solución:**
1. Comentar temporalmente las líneas de rewrite en `.htaccess`
2. Dejar solo:
   ```apache
   ErrorDocument 404 /index.html
   ```
3. Probar acceder al sitio
4. Si funciona, el problema es mod_rewrite

---

### Causa 5: PHP está interceptando las peticiones

Algunos hostings configuran PHP como handler por defecto.

**Solución:**
Crear `index.php` en `public_html/`:

```php
<?php
// Servir index.html
if (file_exists(__DIR__ . '/index.html')) {
    readfile(__DIR__ . '/index.html');
    exit;
} else {
    http_response_code(404);
    echo "Error: index.html no encontrado";
    exit;
}
?>
```

---

## 🧪 PRUEBAS RÁPIDAS

### Test 1: Archivo directo
Probar: `https://cesped365.com/index.html`
- ✅ Si funciona: problema de routing (.htaccess o default document)
- ❌ Si da 404: archivos no están en la carpeta correcta

### Test 2: Carpeta assets
Probar: `https://cesped365.com/assets/`
- Si aparece listado de archivos: bien
- Si da 403: normal (mejor seguridad)
- Si da 404: archivos no subidos

### Test 3: API
Probar: `https://cesped365.com/api/`
- Debe mostrar "Welcome to CodeIgniter 4"
- Si da error: backend no configurado

---

## 🎯 SOLUCIÓN MÁS PROBABLE (hosting barato)

La mayoría de hostings baratos tienen problemas con `.htaccess` o mod_rewrite.

### Solución Rápida:

1. **Renombrar o eliminar temporalmente `.htaccess`**
   ```
   public_html/.htaccess → public_html/.htaccess.backup
   ```

2. **Crear archivo `index.php` en `public_html/`:**
   ```php
   <?php
   $html_file = __DIR__ . '/index.html';
   if (file_exists($html_file)) {
       header('Content-Type: text/html; charset=utf-8');
       readfile($html_file);
   } else {
       http_response_code(404);
       die('index.html no encontrado');
   }
   ?>
   ```

3. **Probar:** `https://cesped365.com/`

Si funciona con esto, el problema era el `.htaccess`.

---

## 📞 VERIFICACIÓN FINAL

### En cPanel File Manager, verificar:

```
public_html/
├── index.html      ← Debe existir
├── index.php       ← Crear si .htaccess no funciona
├── .htaccess       ← Debe existir
├── assets/         ← Debe tener archivos .js y .css
│   ├── index-*.js
│   └── index-*.css
└── api/            ← Backend
    ├── app/
    ├── public/
    └── vendor/
```

### Comandos de verificación (si tienes SSH):

```bash
# Ver archivos en public_html
ls -lah ~/public_html/

# Verificar contenido de index.html (primeras líneas)
head -20 ~/public_html/index.html

# Verificar .htaccess existe
cat ~/public_html/.htaccess
```

---

## 🚨 SOLUCIÓN RÁPIDA SI NADA FUNCIONA

1. **Subir solo index.html:**
   - Crear un archivo `test.html` con:
     ```html
     <!DOCTYPE html>
     <html>
     <head><title>Test</title></head>
     <body><h1>Sitio funcionando!</h1></body>
     </html>
     ```
   - Subirlo a `public_html/test.html`
   - Abrir: `https://cesped365.com/test.html`
   - Si funciona: problema de configuración
   - Si no funciona: problema de DNS o dominio

2. **Verificar DNS:**
   - Usar: https://www.whatsmydns.net/
   - Buscar: `cesped365.com`
   - Verificar que apunte a la IP de tu hosting

---

## 📝 RESUMEN DE ACCIONES

1. [ ] Verificar que `index.html` existe en `public_html/`
2. [ ] Probar acceder a `https://cesped365.com/index.html` directamente
3. [ ] Verificar Document Root apunta a `public_html/`
4. [ ] Verificar permisos de `index.html` (644)
5. [ ] Crear `index.php` como alternativa si `.htaccess` no funciona
6. [ ] Verificar que el dominio apunte correctamente (DNS)

---

**¿Cuál es el resultado de estos tests?** Avísame y te ayudo más específicamente.
