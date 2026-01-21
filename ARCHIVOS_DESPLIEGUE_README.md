# 📚 Guía de Archivos para Despliegue - Cesped365

Esta guía te indica qué archivo usar según lo que necesites.

---

## 🚀 Para Poner el Sitio en Producción

### 1️⃣ **INICIO_RAPIDO_PRODUCCION.md** ⭐ EMPIEZA AQUÍ
**¿Para qué?** Guía rápida en 4 pasos (30 minutos)
- Crear base de datos
- Ejecutar script SQL
- Configurar .env
- Probar login

**¿Cuándo usarlo?** Cuando ya subiste los archivos y necesitas activar el login/dashboard.

---

### 2️⃣ **CHECKLIST_PRODUCCION.md**
**¿Para qué?** Lista de verificación detallada con checkboxes
- Paso a paso muy específico
- Marcar cada tarea cuando la completes
- No te saltes nada

**¿Cuándo usarlo?** Si quieres asegurarte de que no olvidas ningún paso.

---

### 3️⃣ **DESPLIEGUE_PRODUCCION.md**
**¿Para qué?** Guía completa con todos los detalles técnicos
- Explicación profunda de cada paso
- Solución de problemas comunes
- Configuración avanzada

**¿Cuándo usarlo?** 
- Si encuentras errores y necesitas debug
- Si quieres entender qué hace cada cosa
- Como referencia cuando algo falla

---

## 🗄️ Archivos de Configuración

### **database_setup.sql**
**¿Para qué?** Script SQL para crear todas las tablas
- Ejecutar en phpMyAdmin
- Crea: users, gardens, reports, report_images
- Inserta usuario admin inicial

**¿Cuándo usarlo?** Primera vez que configuras la base de datos.

---

### **CONFIGURACION_ENV_PRODUCCION.md**
**¿Para qué?** Plantilla del archivo `.env` con explicaciones
- Configuración completa del backend
- Explicación de cada variable
- Ejemplos de valores

**¿Cuándo usarlo?** Cuando crees el archivo `.env` en el servidor.

---

## ⚙️ GitHub Actions (Deployment Automático)

### **.github/workflows/deploy.yaml**
**¿Para qué?** Automatización del deployment
- Build automático del frontend
- Deploy automático vía FTP
- Se ejecuta al hacer push a `main`

**¿Cuándo usarlo?** Ya está configurado. Solo hacer push a GitHub.

**¿Cómo configurarlo?**
1. GitHub → Settings → Secrets
2. Agregar: `FTP_SERVER`, `FTP_USERNAME`, `FTP_PASSWORD`
3. Hacer push a `main`

---

## 🛠️ Herramientas y Utilidades

### **COMANDOS_UTILES.md**
**¿Para qué?** Comandos para administrar el sitio
- Backups de base de datos
- Limpiar logs y cache
- Debug y troubleshooting
- Queries SQL útiles

**¿Cuándo usarlo?** 
- Para mantenimiento regular
- Cuando necesites hacer cambios en la BD
- Para debugging

---

## 📖 Documentación General

### **README.md**
**¿Para qué?** Descripción general del proyecto
- Tecnologías usadas
- Estructura del proyecto
- Cómo correr en local

**¿Cuándo usarlo?** Para entender el proyecto completo.

---

## 🎯 Flujo de Trabajo Recomendado

### Primera Vez (Setup Inicial)

1. **Subir archivos al servidor** (FTP o GitHub Actions)
   ```
   Frontend: dist/ → public_html/
   Backend: api/ → public_html/api/
   ```

2. **Seguir:** `INICIO_RAPIDO_PRODUCCION.md`
   - Crear BD
   - Ejecutar `database_setup.sql`
   - Crear `.env` (usar `CONFIGURACION_ENV_PRODUCCION.md`)
   - Probar login

3. **Verificar con:** `CHECKLIST_PRODUCCION.md`
   - Marcar cada paso completado

4. **Si hay problemas:** `DESPLIEGUE_PRODUCCION.md`
   - Ir a sección "Solución de Problemas"

---

### Actualizaciones Posteriores

1. **Desarrollo local:**
   ```bash
   git add .
   git commit -m "Descripción"
   git push origin main
   ```

2. **GitHub Actions automáticamente:**
   - Build del frontend
   - Deploy vía FTP

3. **Si necesitas actualizar manualmente:**
   - Build: `npm run build`
   - Subir `dist/` vía FTP

---

### Mantenimiento Regular

**Semanal:**
- Backup de base de datos (ver `COMANDOS_UTILES.md`)

**Mensual:**
- Limpiar logs antiguos
- Verificar espacio en disco
- Revisar reportes de errores

**Cuando hay problemas:**
- Revisar logs: `api/writable/logs/`
- Consultar: `DESPLIEGUE_PRODUCCION.md` → Solución de Problemas
- Ejecutar comandos de debug de `COMANDOS_UTILES.md`

---

## 📋 Resumen de Archivos

| Archivo | Uso | Prioridad |
|---------|-----|-----------|
| `INICIO_RAPIDO_PRODUCCION.md` | Guía rápida de setup | ⭐⭐⭐ |
| `CHECKLIST_PRODUCCION.md` | Lista de verificación | ⭐⭐⭐ |
| `DESPLIEGUE_PRODUCCION.md` | Guía completa + troubleshooting | ⭐⭐ |
| `database_setup.sql` | Script SQL | ⭐⭐⭐ |
| `CONFIGURACION_ENV_PRODUCCION.md` | Plantilla .env | ⭐⭐⭐ |
| `COMANDOS_UTILES.md` | Comandos de admin | ⭐ |
| `.github/workflows/deploy.yaml` | CI/CD automático | ⭐⭐ |
| `README.md` | Documentación general | ⭐ |

---

## 🆘 ¿Qué Archivo Usar?

### "Acabo de subir el sitio y necesito activar el login"
→ **INICIO_RAPIDO_PRODUCCION.md**

### "Quiero asegurarme de no olvidar nada"
→ **CHECKLIST_PRODUCCION.md**

### "Tengo un error y necesito solucionarlo"
→ **DESPLIEGUE_PRODUCCION.md** (sección Solución de Problemas)

### "Necesito crear el archivo .env"
→ **CONFIGURACION_ENV_PRODUCCION.md**

### "Quiero hacer un backup de la base de datos"
→ **COMANDOS_UTILES.md**

### "Quiero automatizar el deployment"
→ Configurar secrets en GitHub y usar `.github/workflows/deploy.yaml`

---

## ✅ Checklist Rápido

- [ ] Archivos subidos al servidor
- [ ] Base de datos creada
- [ ] `database_setup.sql` ejecutado
- [ ] `.env` creado y configurado
- [ ] Permisos de `writable/` configurados (755)
- [ ] Login funciona (admin@cesped365.com / admin123)
- [ ] Dashboard carga sin errores
- [ ] Contraseña de admin cambiada
- [ ] GitHub Actions configurado (opcional)
- [ ] Backups automáticos configurados

---

**¡Éxito con tu deployment!** 🚀🌱
