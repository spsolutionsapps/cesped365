# 🔧 Solución: Error "Can't find a route for 'POST: login'" en Producción - V2

## 🐛 Problema

En producción, al intentar hacer login, aparece el error:
```
Login error: Error: Can't find a route for 'POST: login'.
```

## 🔍 Causa Raíz

El problema ocurría porque:

1. **Variable de entorno no disponible**: Cuando el código se compilaba para producción, la variable de entorno `VITE_API_URL` no estaba disponible o no se estaba usando correctamente.

2. **URL base incorrecta**: El código estaba usando `http://localhost:8080/api` como valor por defecto, lo que causaba que en producción se intentara hacer peticiones a localhost en lugar del dominio real.

3. **Detección automática faltante**: No había lógica para detectar automáticamente el dominio en producción cuando la variable de entorno no estaba disponible.

## ✅ Solución Implementada

### Cambios Realizados

1. **Función `getApiBaseUrl()` mejorada** en `src/services/api.js`:
   - Detecta automáticamente si está en producción (no localhost)
   - Usa el dominio actual (`window.location.host`) + `/api` en producción
   - Mantiene compatibilidad con variable de entorno `VITE_API_URL`
   - Fallback a `http://localhost:8080/api` solo en desarrollo

2. **Normalización de endpoints**: Se asegura que todos los endpoints comiencen con `/`

3. **Consistencia en todo el código**: Todos los lugares que usaban `import.meta.env.VITE_API_URL` ahora usan la función `getApiBaseUrl()`

### Archivos Modificados

- ✅ `src/services/api.js` - Función mejorada y exportada
- ✅ `src/pages/dashboard/MiJardin.svelte` - Usa `getApiBaseUrl()`
- ✅ `src/pages/Registro.svelte` - Usa `getApiBaseUrl()`

## 🚀 Cómo Aplicar en Producción

### Opción 1: Recompilar con Variable de Entorno (Recomendado)

1. **Configurar variable de entorno antes de compilar**:
   ```bash
   # En el servidor de build o localmente
   export VITE_API_URL=https://cesped365.com/api
   npm run build
   ```

2. **O crear archivo `.env.production`**:
   ```env
   VITE_API_URL=https://cesped365.com/api
   ```

3. **Compilar**:
   ```bash
   npm run build
   ```

4. **Subir la carpeta `dist/` a producción**

### Opción 2: Usar Detección Automática (Ya Implementado)

La solución ya implementada detecta automáticamente el dominio en producción, así que:

1. **Solo necesitas recompilar**:
   ```bash
   npm run build
   ```

2. **Subir la carpeta `dist/` a producción**

3. **El código detectará automáticamente** que está en `cesped365.com` y usará `https://cesped365.com/api`

## 🧪 Verificación

### 1. Verificar en Consola del Navegador

Abre la consola del navegador (F12) y busca:
```
API Request: POST https://cesped365.com/api/login
```

Si ves `localhost:8080` en lugar de `cesped365.com`, el código no se compiló correctamente.

### 2. Verificar Variable de Entorno

En la consola del navegador, ejecuta:
```javascript
console.log(import.meta.env.VITE_API_URL);
```

- Si muestra `undefined` o `null`: El código usará detección automática ✅
- Si muestra `https://cesped365.com/api`: Está usando la variable de entorno ✅
- Si muestra `http://localhost:8080/api`: El código está usando el fallback (solo en desarrollo)

### 3. Probar Login

1. Ir a `https://cesped365.com/login`
2. Ingresar credenciales
3. Verificar en la consola que la petición vaya a `https://cesped365.com/api/login`
4. Verificar que el login funcione correctamente

## 📝 Código de la Solución

### Función `getApiBaseUrl()` en `src/services/api.js`:

```javascript
export function getApiBaseUrl() {
  // Si hay variable de entorno, usarla
  if (import.meta.env.VITE_API_URL) {
    return import.meta.env.VITE_API_URL;
  }
  
  // En producción (cuando no hay localhost), usar el dominio actual
  if (typeof window !== 'undefined') {
    const isProduction = window.location.hostname !== 'localhost' && 
                        window.location.hostname !== '127.0.0.1';
    if (isProduction) {
      // En producción, usar el dominio actual + /api
      return `${window.location.protocol}//${window.location.host}/api`;
    }
  }
  
  // Por defecto, desarrollo local
  return 'http://localhost:8080/api';
}
```

## 🔄 Pasos para Aplicar

1. **Hacer pull de los cambios** (si trabajas en equipo)
2. **Recompilar el proyecto**:
   ```bash
   npm run build
   ```
3. **Subir la carpeta `dist/` a producción**
4. **Limpiar caché del navegador** (Ctrl + Shift + Delete)
5. **Probar el login**

## ⚠️ Importante

- **No es necesario** configurar `VITE_API_URL` en producción si usas la detección automática
- **Sí es recomendable** configurarla para mayor control y rendimiento
- **Siempre recompila** después de cambios en el código
- **Limpia la caché** del navegador después de subir cambios

## 🐛 Si Sigue Sin Funcionar

### Verificar:

1. **¿La URL en la consola es correcta?**
   - Debe ser `https://cesped365.com/api/login`
   - NO debe ser `http://localhost:8080/api/login`

2. **¿El backend está respondiendo?**
   - Probar directamente: `https://cesped365.com/api/login`
   - Debe mostrar un error de CodeIgniter (no un 404)

3. **¿Los archivos están actualizados?**
   - Verificar fecha de modificación de archivos en `dist/`
   - Asegurarse de que se subieron los archivos nuevos

4. **¿Hay errores de CORS?**
   - Verificar que el backend tenga CORS configurado correctamente
   - Verificar que las cookies se estén enviando (`credentials: 'include'`)

## ✅ Checklist Final

- [ ] Código actualizado con `getApiBaseUrl()`
- [ ] Proyecto recompilado (`npm run build`)
- [ ] Archivos de `dist/` subidos a producción
- [ ] Caché del navegador limpiada
- [ ] URL en consola muestra `https://cesped365.com/api/login`
- [ ] Login funciona correctamente

---

**Fecha**: 30 de Enero, 2026  
**Estado**: ✅ Solucionado  
**Versión**: 2.0
