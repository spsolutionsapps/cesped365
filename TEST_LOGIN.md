# 🔍 TEST DE LOGIN - SIGUE ESTOS PASOS

## ❌ **El dashboard está vacío porque:**

1. Las cookies NO se están guardando correctamente
2. O el login NO está funcionando

---

## 🧪 **TEST COMPLETO (HAZ ESTO AHORA):**

### **Paso 1: Borra TODAS las cookies**
1. Presiona `F12` (abrir DevTools)
2. Ve a la pestaña **"Application"** (o "Almacenamiento")
3. En el menú lateral, busca **"Cookies"**
4. Click en `http://localhost:3000`
5. Click derecho → **"Clear"** (eliminar todas)
6. Click en `http://localhost:8080`
7. Click derecho → **"Clear"** (eliminar todas)

### **Paso 2: Cierra TODAS las pestañas**
Cierra todas las pestañas de `localhost:3000` y `localhost:8080`

### **Paso 3: Abre pestaña nueva**
Abre una pestaña nueva y limpia

### **Paso 4: Ve al login**
Ve a: `http://localhost:3000/login`

### **Paso 5: Abre DevTools ANTES de hacer login**
Presiona `F12` → Ve a la pestaña **"Network"** (Red)

### **Paso 6: Haz login**
Login con:
- Email: `admin@cesped365.com`
- Password: `admin123`

### **Paso 7: OBSERVA la pestaña Network**
Cuando hagas click en "Iniciar Sesión", deberías ver:

1. **Request a:** `http://localhost:8080/api/login`
2. Click en ese request
3. Ve a la pestaña **"Headers"**
4. Busca **"Response Headers"**
5. Deberías ver algo como:
   ```
   Set-Cookie: ci_session=XXXXXX; path=/; HttpOnly; SameSite=Lax
   ```

### **Paso 8: Verifica las cookies**
1. Ve a la pestaña **"Application"** → **"Cookies"** → `http://localhost:8080`
2. Deberías ver una cookie llamada **`ci_session`** con un valor largo

### **Paso 9: Si ves la cookie, verifica el dashboard**
1. Ve a la pestaña **"Console"**
2. Pega este código:
   ```javascript
   fetch('http://localhost:8080/api/me', { credentials: 'include' })
     .then(r => r.json())
     .then(d => console.log('User:', d))
   ```
3. Presiona Enter
4. Deberías ver: `User: { success: true, user: {...} }`

### **Paso 10: Si NO ves la cookie**
Significa que el backend NO está enviando la cookie. En ese caso:

1. Ve a la terminal del **BACKEND** (donde está corriendo `php spark serve`)
2. Presiona `Ctrl + C`
3. Reinicia el backend:
   ```bash
   cd api
   php spark serve
   ```
4. Vuelve al **Paso 1**

---

## 📋 **DIME QUÉ PASA EN:**

1. **Paso 7:** ¿Ves el header `Set-Cookie`? → SI / NO
2. **Paso 8:** ¿Ves la cookie `ci_session`? → SI / NO
3. **Paso 9:** ¿Qué respuesta te da? → Copia el resultado

---

**IMPORTANTE:** No continúes hasta hacer estos pasos. La información que me des determinará la solución exacta.
