# 🚀 INSTRUCCIONES DE DEPLOY A PRODUCCIÓN

## ✅ CAMBIOS IMPORTANTES EN ESTE DEPLOY:

### 1. **Restaurado sistema de guardado de imágenes** 
- Ahora usa automáticamente `public_uploads` disk en producción
- Guarda directamente en `public_html/storage/garden-reports/`
- No requiere symlinks

### 2. **Sistema de notificaciones toast arreglado**
- Cache busting dinámico para `notifications.js`
- Orden de carga de scripts corregido

### 3. **Rutas de imágenes corregidas**
- Prioriza `public_uploads` disk (producción)
- Fallback a `public` disk (desarrollo)

---

## 📋 PASOS PARA ACTUALIZAR EN PRODUCCIÓN:

### **1. Conectarse al servidor vía SSH o acceder vía cPanel Terminal**

### **2. Navegar al directorio del proyecto**
```bash
cd /ruta/a/cesped365
# O si tu proyecto está en: cd cesped365
```

### **3. Hacer pull de los cambios**
```bash
git pull origin main
```

### **4. VERIFICAR que el archivo notifications.js existe**
```bash
ls -la public/js/notifications.js
```

**Si NO existe**, significa que hay un problema con la estructura de directorios.

### **5. Copiar archivos públicos a public_html (SI ES NECESARIO)**

Si tu estructura en producción es:
```
/home/usuario/
├── cesped365/          ← Proyecto Laravel aquí
│   └── public/
│       └── js/
│           └── notifications.js
└── public_html/        ← Document root del servidor
    └── js/             ← Aquí debe estar notifications.js
```

Entonces necesitas copiar:
```bash
cp cesped365/public/js/notifications.js public_html/js/notifications.js
```

O si usas un script de deploy, asegúrate que copie todo el contenido de `public/` a `public_html/`

### **6. Limpiar cachés de Laravel**
```bash
cd /ruta/a/cesped365  # O donde esté el proyecto
php artisan view:clear
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

### **7. Verificar permisos del directorio de storage**
```bash
chmod -R 775 storage/
chmod -R 775 public_html/storage/
```

Si no existe el directorio `public_html/storage/garden-reports/`:
```bash
mkdir -p public_html/storage/garden-reports
chmod -R 775 public_html/storage/
```

---

## 🔍 VERIFICACIÓN POST-DEPLOY:

### **1. Verificar que el archivo notifications.js se carga**
Abrir en el navegador:
```
https://cesped365.com/js/notifications.js
```

Debería mostrar el contenido del archivo JavaScript (no un error 404).

### **2. Verificar que las notificaciones funcionan**
1. Ir a: https://cesped365.com/admin/garden-reports
2. Abrir consola del navegador (F12)
3. Crear o eliminar un reporte
4. Deberías ver:
   - ✅ Toast de éxito en la pantalla
   - ✅ En consola: `🟢 Mensaje de éxito detectado:`
   - ✅ En consola: `✅ NotificationSystem disponible`

### **3. Verificar que las imágenes se guardan**
1. Crear un nuevo reporte con imágenes
2. Las imágenes deberían guardarse en: `public_html/storage/garden-reports/`
3. Verificar que las imágenes se muestran correctamente en la vista del reporte

---

## ⚠️ PROBLEMAS COMUNES:

### **Problema 1: "notifications.js 404 Not Found"**
**Solución:** El archivo no está en `public_html/js/`. Cópialo manualmente:
```bash
cp cesped365/public/js/notifications.js public_html/js/notifications.js
```

### **Problema 2: "Las imágenes no se guardan"**
**Solución:** Verificar permisos del directorio storage:
```bash
chmod -R 775 public_html/storage/
```

### **Problema 3: "Las notificaciones no aparecen"**
**Solución:** Limpiar caché del navegador con `Ctrl + Shift + R` o `Cmd + Shift + R`

### **Problema 4: "Error 500 al crear reporte"**
**Solución:** Revisar logs:
```bash
tail -50 storage/logs/laravel.log
```

---

## 📝 ESTRUCTURA DE DIRECTORIOS ESPERADA EN PRODUCCIÓN:

```
/home/usuario/
├── cesped365/                          ← Proyecto Laravel
│   ├── app/
│   ├── config/
│   ├── storage/
│   │   └── logs/                       ← Logs de Laravel
│   └── public/
│       └── js/
│           └── notifications.js
│
└── public_html/                        ← Document root
    ├── index.php                       ← Laravel public/index.php
    ├── js/
    │   └── notifications.js            ← DEBE EXISTIR AQUÍ
    ├── css/
    ├── assets/
    └── storage/                        ← Imágenes públicas
        └── garden-reports/             ← Aquí se guardan las imágenes
            └── [archivos.jpg]
```

---

## ✅ CHECKLIST DE DEPLOY:

- [ ] Ejecutado `git pull origin main`
- [ ] Verificado que `public_html/js/notifications.js` existe
- [ ] Ejecutado `php artisan view:clear`
- [ ] Ejecutado `php artisan cache:clear`
- [ ] Verificado permisos de `public_html/storage/` (775)
- [ ] Verificado que `https://cesped365.com/js/notifications.js` carga correctamente
- [ ] Probado crear reporte con imágenes
- [ ] Verificado que aparecen las notificaciones toast
- [ ] Probado eliminar reporte y ver notificación de éxito

---

## 📞 EN CASO DE PROBLEMAS:

Si algo falla después del deploy:
1. Revisar logs: `tail -100 storage/logs/laravel.log`
2. Revisar consola del navegador (F12)
3. Verificar que todos los archivos se copiaron correctamente
4. Limpiar caché del navegador con `Ctrl + Shift + R`

---

**Última actualización:** 2026-01-13
**Commit:** 1512bdc
