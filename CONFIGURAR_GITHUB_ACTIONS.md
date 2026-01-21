# ⚙️ Configurar GitHub Actions para Deployment Automático

Esta guía te muestra cómo configurar GitHub Actions para que automáticamente despliegue tu sitio cada vez que hagas push a la rama `main`.

---

## 🎯 ¿Qué hace GitHub Actions?

Cuando haces `git push` a GitHub, automáticamente:

1. ✅ **Build del frontend** (Svelte → archivos optimizados)
2. ✅ **Prepara el backend** (instala dependencias de Composer)
3. ✅ **Sube todo al servidor vía FTP**
4. ✅ **Verifica que todo esté correcto**

**Tiempo:** 5-10 minutos por deployment.

---

## 📋 Prerequisitos

- [ ] Tienes acceso FTP a tu servidor
- [ ] Conoces las credenciales FTP (usuario y contraseña)
- [ ] Tu proyecto está en GitHub
- [ ] Tienes permisos de administrador en el repositorio

---

## 🔐 PASO 1: Obtener Credenciales FTP

Necesitas estos datos de tu hosting:

### 1.1 Desde cPanel

1. Ir a **cPanel → FTP Accounts**
2. Ver tus cuentas FTP existentes, o crear una nueva
3. Anotar:
   - **Servidor FTP:** Generalmente es `ftp.tudominio.com` o la IP del servidor
   - **Usuario FTP:** Tu usuario (ej: `usuario@tudominio.com`)
   - **Contraseña:** La contraseña del usuario FTP
   - **Puerto:** Generalmente `21` (FTP) o `22` (SFTP)
   - **Directorio:** Generalmente `public_html/` o `/`

### 1.2 Ejemplo de Credenciales

```
Servidor FTP:  ftp.cesped365.com
Usuario:       cesped365@cesped365.com
Contraseña:    MiPasswordSegura123!
Puerto:        21
Directorio:    public_html/
```

⚠️ **IMPORTANTE:** El servidor FTP NO debe incluir `ftp://` ni `http://`. Solo el dominio o IP.

✅ **Correcto:** `ftp.tudominio.com`
❌ **Incorrecto:** `ftp://ftp.tudominio.com`

---

## 🔧 PASO 2: Configurar Secrets en GitHub

### 2.1 Ir a la configuración de Secrets

1. Ve a tu repositorio en GitHub
2. Click en **Settings** (⚙️)
3. En el menú lateral izquierdo, click en **Secrets and variables**
4. Click en **Actions**
5. Click en el botón verde **New repository secret**

### 2.2 Agregar cada Secret

Vas a crear 5 secrets. Para cada uno:

1. Click en **New repository secret**
2. Poner el **Name** (exactamente como se indica abajo)
3. Pegar el **Value** (tu valor)
4. Click en **Add secret**

---

### Secret 1: FTP_SERVER

**Name:**
```
FTP_SERVER
```

**Value:** (ejemplo)
```
ftp.cesped365.com
```

**Notas:**
- Sin `ftp://` al inicio
- Sin `/` al final
- Puede ser dominio o IP
- Ejemplo: `ftp.tudominio.com` o `123.45.67.89`

---

### Secret 2: FTP_USERNAME

**Name:**
```
FTP_USERNAME
```

**Value:** (ejemplo)
```
cesped365@cesped365.com
```

**Notas:**
- Exactamente como aparece en cPanel
- Puede incluir `@tudominio.com` o solo el usuario

---

### Secret 3: FTP_PASSWORD

**Name:**
```
FTP_PASSWORD
```

**Value:** (ejemplo)
```
MiPasswordSegura123!
```

**Notas:**
- La contraseña del usuario FTP
- Puede contener caracteres especiales
- ⚠️ **Asegúrate de que sea correcta** - copiar y pegar

---

### Secret 4: FTP_PORT (Opcional)

**Name:**
```
FTP_PORT
```

**Value:** (ejemplo)
```
21
```

**Notas:**
- `21` para FTP normal
- `22` para SFTP/SSH
- Si no lo configuras, usa `21` por defecto

---

### Secret 5: FTP_SERVER_DIR (Opcional)

**Name:**
```
FTP_SERVER_DIR
```

**Value:** (ejemplo)
```
public_html/
```

**Notas:**
- La carpeta donde se deben subir los archivos
- Generalmente `public_html/` o `www/` o `/`
- **Importante:** Debe terminar con `/`
- Si no lo configuras, usa `public_html/` por defecto

---

## ✅ PASO 3: Verificar Configuración

Después de agregar los secrets, deberías ver algo así en la página de Secrets:

```
Repository secrets (5)

FTP_SERVER          •••••••••••••••
FTP_USERNAME        •••••••••••••••
FTP_PASSWORD        •••••••••••••••
FTP_PORT            •••
FTP_SERVER_DIR      •••••••••••
```

**No puedes ver los valores** después de crearlos (por seguridad). Si te equivocaste:
1. Click en el secret
2. Click en **Update**
3. Pegar el valor correcto
4. **Save**

---

## 🚀 PASO 4: Probar el Deployment

### 4.1 Hacer un cambio y push

```bash
# En tu proyecto local
git add .
git commit -m "Test deployment"
git push origin main
```

### 4.2 Ver el progreso en GitHub

1. Ve a tu repositorio en GitHub
2. Click en la pestaña **Actions** (🎬)
3. Verás un workflow corriendo: "Deploy Cesped365 to Production"
4. Click en él para ver el progreso en tiempo real

### 4.3 Verificar el resultado

**✅ Si sale todo bien:**
- Verás checkmarks verdes ✓
- Al final dice: "✅ DEPLOYMENT EXITOSO"
- Los archivos se actualizan en tu servidor

**❌ Si hay errores:**
- Verás una X roja ✗
- Click en el paso que falló
- Ver el log de error
- Generalmente es por credenciales FTP incorrectas

---

## 🔍 PASO 5: Solución de Problemas

### Error: "FTP connection failed"

**Causa:** Credenciales incorrectas o servidor inaccesible.

**Solución:**
1. Verificar `FTP_SERVER` no tiene `ftp://` ni `/`
2. Verificar `FTP_USERNAME` es correcto
3. Verificar `FTP_PASSWORD` es correcto
4. Probar conectar con FileZilla usando las mismas credenciales

---

### Error: "cannot resolve DNS"

**Causa:** GitHub no puede encontrar tu servidor FTP.

**Solución:**
1. Usar la IP del servidor en lugar del dominio
   - En cPanel, buscar "Server Information" → "Shared IP Address"
   - Usar esa IP en `FTP_SERVER`
2. O esperar propagación de DNS (24-48 horas si es dominio nuevo)

---

### Error: "Permission denied"

**Causa:** El usuario FTP no tiene permisos de escritura.

**Solución:**
1. En cPanel, verificar permisos del usuario FTP
2. Asegurarse de que puede escribir en `FTP_SERVER_DIR`
3. Verificar que `public_html/` tiene permisos 755

---

### Error: "Path not found"

**Causa:** `FTP_SERVER_DIR` está mal configurado.

**Solución:**
1. Verificar ruta en cPanel
2. Común: `public_html/`, `www/`, `httpdocs/`, `/`
3. Debe terminar con `/`

---

## 🎛️ Configuración Avanzada

### Deployment manual (sin push)

1. Ir a **Actions** en GitHub
2. Click en **Deploy Cesped365 to Production** en el lado izquierdo
3. Click en **Run workflow** (botón gris)
4. Seleccionar rama `main`
5. Click en **Run workflow** (botón verde)

### Deshabilitar deployment automático

Si NO quieres que se despliegue automáticamente al hacer push:

Editar `.github/workflows/deploy.yaml`:

```yaml
# Comentar o borrar estas líneas:
# on:
#   push:
#     branches:
#       - main

# Dejar solo:
on:
  workflow_dispatch:  # Solo deployment manual
```

---

## 📝 Resumen de Secrets

| Secret Name | Ejemplo | Requerido | Notas |
|------------|---------|-----------|-------|
| `FTP_SERVER` | `ftp.tudominio.com` | ✅ Sí | Sin `ftp://` |
| `FTP_USERNAME` | `usuario@tudominio.com` | ✅ Sí | Usuario FTP |
| `FTP_PASSWORD` | `MiPass123!` | ✅ Sí | Contraseña FTP |
| `FTP_PORT` | `21` | ⚪ Opcional | Default: 21 |
| `FTP_SERVER_DIR` | `public_html/` | ⚪ Opcional | Default: `public_html/` |

---

## ✅ Checklist

- [ ] Obtuve credenciales FTP de cPanel
- [ ] Agregué `FTP_SERVER` a GitHub Secrets
- [ ] Agregué `FTP_USERNAME` a GitHub Secrets
- [ ] Agregué `FTP_PASSWORD` a GitHub Secrets
- [ ] (Opcional) Agregué `FTP_PORT`
- [ ] (Opcional) Agregué `FTP_SERVER_DIR`
- [ ] Hice push a `main` para probar
- [ ] Vi el workflow en GitHub Actions
- [ ] El deployment fue exitoso ✅

---

## 🎉 ¡Listo!

Ahora cada vez que hagas:

```bash
git push origin main
```

Tu sitio se actualizará automáticamente en producción. 🚀

---

## 📞 ¿Necesitas Ayuda?

- Ver logs detallados en GitHub Actions
- Probar conexión FTP con FileZilla primero
- Verificar que las credenciales sean exactamente las mismas
