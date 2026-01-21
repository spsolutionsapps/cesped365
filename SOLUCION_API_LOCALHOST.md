# 🔧 Solución: Error "Failed to fetch" en Producción

## 🐛 **Problema**

El frontend en producción intenta conectarse a `localhost:8080/api` en lugar de `cesped365.com/api`.

**Error en consola:**
```
Failed to load resource: net::ERR_CONNECTION_REFUSED
localhost:8080/api/login:1
```

---

## ✅ **Causa**

El frontend fue compilado (`npm run build`) **sin** la variable de entorno `VITE_API_URL` configurada para producción.

Por defecto, `api.js` usa:
```javascript
const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8080/api';
```

Como no había un archivo `.env.production`, Vite usó el valor por defecto: `http://localhost:8080/api` ❌

---

## ✅ **Solución**

### **Paso 1: Archivos de Configuración Creados**

He creado 2 archivos de configuración:

#### **`.env.production`** (para producción)
```env
VITE_API_URL=https://cesped365.com/api
```

#### **`.env.development`** (para desarrollo local)
```env
VITE_API_URL=http://localhost:8080/api
```

---

### **Paso 2: Recompilar el Frontend**

Ahora debes recompilar el frontend con la configuración de producción:

```bash
npm run build
```

Este comando:
1. Lee automáticamente `.env.production`
2. Reemplaza `import.meta.env.VITE_API_URL` por `https://cesped365.com/api`
3. Genera archivos nuevos en `dist/`

---

### **Paso 3: Subir al Servidor**

Una vez recompilado:

#### **Opción A: Automático (vía GitHub Actions)**

Si ya tienes GitHub Actions configurado:

```bash
git add .env.production .env.development .gitignore
git commit -m "Agregar configuración de producción para API"
git push origin main
```

El workflow se ejecutará automáticamente y subirá los archivos nuevos.

---

#### **Opción B: Manual (vía FTP/cPanel)**

1. **Conectar vía FTP** (FileZilla) o **cPanel File Manager**

2. **Eliminar archivos viejos en `public_html/`:**
   - Eliminar todos los archivos `.js` y `.css` de la carpeta `assets/`
   - O eliminar toda la carpeta `assets/` y volver a subir

3. **Subir archivos nuevos:**
   - Subir TODO el contenido de `dist/` → `public_html/`
   - Reemplazar cuando pregunte

4. **Verificar:**
   - El archivo `public_html/assets/index-[hash].js` debe tener un hash nuevo
   - Ejemplo: 
     - Antes: `index-BsJO8fhs.js`
     - Después: `index-xY9kLm3n.js` (diferente)

---

### **Paso 4: Limpiar Cache del Navegador**

Después de subir los archivos:

1. **Abrir navegador en modo incógnito** (Ctrl + Shift + N)
2. **O limpiar cache:** Ctrl + Shift + Del → Marcar "Imágenes y archivos en caché" → Borrar

3. **Visitar:** `https://cesped365.com/`

4. **Abrir consola (F12)** y verificar que ahora intente conectar a:
   ```
   https://cesped365.com/api/login
   ```
   En lugar de:
   ```
   localhost:8080/api/login ❌
   ```

---

## 🧪 **Verificación**

### **Test 1: URL de la API**

1. Abrir consola del navegador (F12)
2. Ir a la pestaña **Network**
3. Intentar hacer login
4. Verificar que las peticiones vayan a:
   - ✅ `https://cesped365.com/api/login`
   - ❌ NO `localhost:8080/api/login`

### **Test 2: Login**

1. Intentar login con:
   - Email: `admin@cesped365.com`
   - Password: `password`

2. Si sale otro error (NO "Failed to fetch"), es progreso:
   - Puede ser CORS (solución en `SOLUCION_CORS.md`)
   - Puede ser credenciales incorrectas (revisar base de datos)
   - Puede ser error del backend (revisar logs en `api/writable/logs/`)

---

## 📝 **Cómo Funciona**

### **En Desarrollo (`npm run dev`):**
- Vite lee `.env.development`
- `VITE_API_URL` = `http://localhost:8080/api`
- El frontend se conecta a tu backend local

### **En Producción (`npm run build`):**
- Vite lee `.env.production`
- `VITE_API_URL` = `https://cesped365.com/api`
- El frontend se conecta al backend en producción

---

## 🔄 **Ciclo de Deployment Correcto**

Cada vez que hagas cambios al frontend:

```bash
# 1. Hacer cambios en el código
# ...

# 2. Probar localmente
npm run dev

# 3. Compilar para producción
npm run build

# 4. Subir a servidor (automático o manual)
git push origin main
# O vía FTP: subir dist/ → public_html/

# 5. Limpiar cache del navegador
# Modo incógnito o Ctrl + Shift + Del
```

---

## 🚨 **Si el Problema Persiste**

### **Verificar que el build fue correcto:**

1. Abrir `dist/index.html` en un editor de texto
2. Buscar la línea del script:
   ```html
   <script type="module" src="/assets/index-xY9kLm3n.js"></script>
   ```
3. El hash debe ser **diferente** al anterior

4. Si el hash es igual, el build no se actualizó:
   ```bash
   # Eliminar dist/ y volver a compilar
   rm -rf dist
   npm run build
   ```

---

### **Verificar archivo subido en el servidor:**

1. **cPanel → File Manager**
2. **Ir a `public_html/assets/`**
3. **Buscar `index-*.js` más reciente** (ver fecha de modificación)
4. **Click derecho → Edit**
5. **Buscar (Ctrl + F):** `cesped365.com/api`
6. Debe aparecer varias veces ✅
7. NO debe aparecer `localhost:8080` ❌

---

## 🎯 **Resumen Rápido**

| Paso | Comando | Resultado |
|------|---------|-----------|
| 1 | Archivos creados | `.env.production` y `.env.development` |
| 2 | `npm run build` | Compila con URL de producción |
| 3 | Subir `dist/` al servidor | Manual (FTP) o Automático (GitHub) |
| 4 | Limpiar cache navegador | Ctrl + Shift + Del |
| 5 | Probar login | Debe conectar a `cesped365.com/api` |

---

## 📞 **Siguiente Paso**

Ejecuta:

```bash
npm run build
```

Y avísame cuando termine para verificar que se compiló correctamente. Luego te guío para subirlo al servidor.
