# 🔧 Solución: Error "Can't find a route for 'POST: login'" en Producción

## 🐛 Problema

En producción, al intentar hacer login, aparece el error:
```
Can't find a route for 'POST: login'
```

## 🔍 Causa

El problema ocurre porque CodeIgniter está instalado en un subdirectorio (`/api/`) y necesita configuración adicional para funcionar correctamente.

## ✅ Solución

### Paso 1: Verificar `.htaccess` en la raíz (`public_html/.htaccess`)

**⚠️ CRÍTICO:** Este archivo DEBE excluir las peticiones `/api/*` ANTES de redirigir a `index.html`.

Asegúrate de que tenga esta configuración:

```apache
<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteBase /
  
  # Redirigir HTTP a HTTPS (producción)
  RewriteCond %{HTTPS} off
  RewriteRule ^(.*)$ https://%{HTTP_HOST}/$1 [R=301,L]
  
  # CRÍTICO: Excluir peticiones /api/* ANTES de redirigir a index.html
  # Esto permite que las peticiones del backend pasen correctamente
  RewriteCond %{REQUEST_URI} ^/api/
  RewriteRule ^ - [L]
  
  # Servir archivos estáticos directamente
  RewriteCond %{REQUEST_FILENAME} -f [OR]
  RewriteCond %{REQUEST_FILENAME} -d
  RewriteRule ^ - [L]
  
  # Redirigir todo lo demás a index.html (SPA routing)
  RewriteRule ^ index.html [QSA,L]
</IfModule>
```

**⚠️ IMPORTANTE:** La regla que excluye `/api/*` DEBE estar ANTES de la regla que redirige a `index.html`.

### Paso 2: Verificar `.htaccess` en `api/.htaccess` (`public_html/api/.htaccess`)

Este archivo DEBE existir y tener:

```apache
# Redirigir todas las peticiones a api/public/
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>

# Proteger archivos de configuración
<FilesMatch "^\.env|composer\.(json|lock)|phpunit\.xml">
    Order allow,deny
    Deny from all
</FilesMatch>

# Proteger carpetas sensibles
RedirectMatch 403 ^/api/(app|writable|vendor|\.git)(/|$)
```

### Paso 3: Verificar `.htaccess` en `api/public/.htaccess` (`public_html/api/public/.htaccess`)

Este archivo DEBE tener:

```apache
<IfModule mod_rewrite.c>
    Options -Indexes
    RewriteEngine On
    
    # IMPORTANTE: Configurar RewriteBase para subdirectorio
    RewriteBase /api/public/
    
    # Redirigir a index.php si no es un archivo o directorio real
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php/$1 [L,NC,QSA]
</IfModule>

<IfModule !mod_rewrite.c>
    ErrorDocument 404 index.php
</IfModule>

# Deshabilitar listado de directorios
Options -Indexes
```

**⚠️ IMPORTANTE:** La línea `RewriteBase /api/public/` es CRÍTICA para que funcione en producción.

### Paso 4: Verificar configuración en `api/.env`

Asegúrate de que el archivo `api/.env` tenga:

```env
#--------------------------------------------------------------------
# ENVIRONMENT
#--------------------------------------------------------------------

CI_ENVIRONMENT = production

#--------------------------------------------------------------------
# APP
#--------------------------------------------------------------------

# IMPORTANTE: La baseURL debe ser la raíz del dominio (SIN /api/)
# CodeIgniter está en /api/public/ pero el baseURL es relativo al dominio completo
app.baseURL = 'https://tudominio.com/'
app.indexPage = ''

# ... resto de la configuración
```

**⚠️ CRÍTICO:** 
- `app.baseURL` DEBE ser la raíz del dominio: `https://tudominio.com/` (SIN `/api/`)
- `app.indexPage` DEBE estar vacío (`''`)
- Las rutas están dentro del grupo `api`, así que funcionan tanto en local como en producción

### Paso 5: Verificar estructura de archivos en producción

La estructura debe ser:

```
public_html/
├── .htaccess                    ← Raíz (permite /api/*)
├── index.html                   ← Frontend
├── assets/                      ← Frontend
└── api/
    ├── .htaccess                ← Redirige a api/public/
    ├── .env                     ← Configuración
    └── public/
        ├── .htaccess            ← Procesa rutas (con RewriteBase)
        └── index.php            ← Punto de entrada de CodeIgniter
```

## 🧪 Verificación

### 1. Probar directamente la API

Accede directamente a:
```
https://tudominio.com/api/login
```

Deberías ver un error de CodeIgniter (no un 404), lo que significa que está llegando al backend.

### 2. Probar con curl

```bash
curl -X POST https://tudominio.com/api/login \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "email=admin@cesped365.com&password=admin123"
```

### 3. Verificar logs

Revisa los logs en `api/writable/logs/` para ver errores específicos.

## 🔄 Si sigue sin funcionar

### Opción A: Verificar que mod_rewrite está habilitado

En cPanel:
1. Ir a **Select PHP Version**
2. Verificar que **mod_rewrite** esté habilitado

### Opción B: Verificar permisos

```bash
# Los archivos .htaccess deben tener permisos 644
chmod 644 api/.htaccess
chmod 644 api/public/.htaccess
```

### Opción C: Verificar que PHP está procesando correctamente

Crea un archivo `api/public/test.php`:

```php
<?php
phpinfo();
```

Accede a `https://tudominio.com/api/public/test.php` y verifica que PHP esté funcionando.

## 📝 Checklist Final

- [ ] `.htaccess` en raíz permite `/api/*`
- [ ] `.htaccess` en `api/` redirige a `public/`
- [ ] `.htaccess` en `api/public/` tiene `RewriteBase /api/public/`
- [ ] `api/.env` tiene `app.baseURL = 'https://tudominio.com/api/'`
- [ ] `api/.env` tiene `app.indexPage = ''`
- [ ] `mod_rewrite` está habilitado en el servidor
- [ ] Permisos de `.htaccess` son 644

## 🎯 Solución Rápida

Si necesitas una solución rápida, actualiza estos archivos en producción:

1. **`public_html/api/public/.htaccess`** - Agregar `RewriteBase /api/public/`
2. **`public_html/api/.env`** - Verificar que `app.baseURL = 'https://cesped365.com/'` (SIN `/api/`)

**⚠️ IMPORTANTE:** El `baseURL` debe ser la raíz del dominio (`https://cesped365.com/`), NO `https://cesped365.com/api/`. Las rutas están dentro del grupo `api`, así que funcionan correctamente.

¡Esto debería resolver el problema!
