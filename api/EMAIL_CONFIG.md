# Configuración de email (notificación al cliente)

Cuando un **admin crea un reporte**, el sistema envía un correo al cliente (dueño del jardín) con el resumen del reporte y un enlace para verlo online y valorar el servicio.

## Credenciales en el proyecto

En el archivo **`api/.env`** descomentá y completá estas variables con los datos del correo de tu hosting:

```env
EMAIL_FROM=noreply@cesped365.com
EMAIL_FROM_NAME=Cesped365
EMAIL_PROTOCOL=smtp
EMAIL_SMTP_HOST=cesped365.com
EMAIL_SMTP_USER=noreply@cesped365.com
EMAIL_SMTP_PASS="tu_contraseña"
EMAIL_SMTP_PORT=465
EMAIL_SMTP_CRYPTO=ssl
```

En cPanel, usá los datos de **“Mail Client Manual Settings”** (Secure SSL/TLS): servidor saliente = tu dominio (`cesped365.com`), puerto **465**, cifrado **ssl**. No uses `mail.cesped365.com` ni puerto 587 si el panel recomienda 465.

**Importante:** Si la contraseña tiene caracteres especiales (por ejemplo `}`, `$`, `#`, espacios), ponela entre **comillas dobles** en el .env:
```env
EMAIL_SMTP_PASS="atUqAY}0kv6m"
```

### Qué poner en cada variable

| Variable | Descripción | Ejemplo |
|----------|-------------|---------|
| **EMAIL_FROM** | Dirección desde la que se envía (debe ser una cuenta que exista en tu hosting) | `noreply@cesped365.com` |
| **EMAIL_FROM_NAME** | Nombre que ve el cliente en "De:" | `Cesped365` |
| **EMAIL_PROTOCOL** | Siempre `smtp` para usar el correo del hosting | `smtp` |
| **EMAIL_SMTP_HOST** | Servidor de correo. En hosting compartido suele ser `mail.tudominio.com` o el que te indique el panel | `mail.midominio.com` |
| **EMAIL_SMTP_USER** | Usuario del correo (normalmente el mismo que EMAIL_FROM) | `noreply@cesped365.com` |
| **EMAIL_SMTP_PASS** | Contraseña de esa cuenta de correo | La que configuraste en el hosting |
| **EMAIL_SMTP_PORT** | Puerto: **587** con TLS, o **465** con SSL (en ese caso usá `EMAIL_SMTP_CRYPTO=ssl`) | `587` |
| **EMAIL_SMTP_CRYPTO** | `tls` para puerto 587, `ssl` para 465, o `''` si el hosting no usa cifrado | `tls` |

Si **dejás todas las variables de email comentadas o vacías**, el reporte se crea igual pero **no se envía ningún correo** (útil para desarrollo local).

### Logo y enlace en el correo (evitar imagen rota)

El correo incluye el **logo** y un **enlace "Ver reporte"**. Esas URLs deben apuntar a tu sitio en internet, no a `localhost`, sino el logo se ve roto y el enlace no funciona.

En **producción**, agregá en `api/.env` la URL pública del frontend (con `https://`):

```env
# URL pública del frontend (para logo y enlaces en los emails)
APP_PUBLIC_URL=https://www.cesped365.com
```

Usá el dominio real donde está publicado tu frontend (por ejemplo `https://www.cesped365.com` o `https://cesped365.com`). Si no definís `APP_PUBLIC_URL`, se usará `FRONTEND_BASE_URL`; en producción conviene tener al menos una de las dos con la URL pública.

---

## Qué hacer en el hosting

1. **Crear una cuenta de correo**  
   En el panel del hosting (cPanel, Plesk, etc.):
   - Creá una cuenta tipo `noreply@tudominio.com` (o el dominio que uses).
   - Anotá la contraseña que le pongas.

2. **Datos del servidor SMTP**  
   En el mismo panel suele decir:
   - Servidor entrante/saliente: por ejemplo `mail.tudominio.com`.
   - Puerto saliente: **587** (recomendado) o **465**.
   - Seguridad: **TLS** (puerto 587) o **SSL** (puerto 465).

3. **No bloquear el envío desde PHP**  
   Algunos hostings tienen “envío de mail desde scripts” desactivado. Revisá en la sección de correo o preguntá al soporte que esté permitido el envío SMTP desde la aplicación.

4. **SPF y DKIM (necesario para Gmail y otros)**  
   Ver sección más abajo: [Gmail bloquea el correo: SPF y DKIM](#gmail-bloquea-el-correo-spf-y-dkim).

---

## Gmail bloquea el correo: SPF y DKIM

Si el servidor envía el correo pero Gmail (u otro proveedor) lo rechaza con un mensaje como *"The sender is unauthenticated"*, *"SPF = did not pass"* o *"DKIM = did not pass"*, tenés que **autenticar el dominio** con SPF y DKIM. Sin eso, Gmail no acepta correos enviados desde tu servidor (IP 192.64.117.211 en tu caso).

### Si cPanel dice "This system does not control DNS" (DNS en el registrador)

Cuando el aviso amarillo dice que el DNS de **cesped365.com** lo controlan **dns1.registrar-servers.com** y **dns2.registrar-servers.com**, **no sirve** hacer clic en "Install The Suggested Record" en cPanel. Tenés que agregar los registros donde **gestionás el DNS del dominio** (el registrador donde compraste el dominio, o Cloudflare/otro si apuntás ahí los nameservers).

1. **Entrá al panel del registrador** (Namecheap, GoDaddy, DonWeb, etc.) o donde tengas los nameservers `dns1.registrar-servers.com` / `dns2.registrar-servers.com`.
2. Buscá **DNS**, **Zone Editor**, **Manage DNS** o **Registros DNS** para **cesped365.com**.
3. Agregá estos **tres registros TXT** (copiando **Nombre** y **Valor** exactos desde la pantalla de cPanel "Email Deliverability" para tu dominio):

   **SPF** (si ya existe un TXT para `@` o `cesped365.com`, revisá si es el SPF; si es otro, agregá uno nuevo o combiná según lo que permita el panel):
   - **Nombre / Host:** `@` o `cesped365.com` (según lo que use tu panel; a veces con punto final `cesped365.com.`)
   - **Valor:** `v=spf1 +ip4:192.64.117.211 +include:spf.web-hosting.com ~all`

   **DKIM** (el valor largo lo copiás completo desde cPanel, botón "Copy" del Value):
   - **Nombre / Host:** `default._domainkey` (o `default._domainkey.cesped365.com` si pide nombre completo; a veces con punto final)
   - **Valor:** el que muestra cPanel en "Suggested DKIM (TXT) Record", empieza con `v=DKIM1; k=rsa; p=MII...`

   **DMARC** (opcional pero recomendado; conviene agregarlo después de que SPF y DKIM estén bien):
   - **Nombre / Host:** `_dmarc` (o `_dmarc.cesped365.com` según el panel)
   - **Valor:** `v=DMARC1; p=none;`

4. Guardá los cambios y **esperá propagación** (unos minutos a 24–48 h). Luego probá de nuevo enviar un correo a Gmail.

En cPanel, los valores exactos (sobre todo el DKIM) los ves en **Email Deliverability** → dominio **cesped365.com**; usá "Copy" para no equivocaros.

### Si el DNS sí lo controla el hosting (sin aviso de "does not control DNS")

Entrá a **Email Deliverability** / **Authentication**, seleccioná **cesped365.com** y usá **"Install The Suggested Record"** para SPF y para DKIM. DMARC opcional después.

---

## Cómo probar

1. Configurá las variables en `api/.env` y reiniciá el servidor (o volvé a cargar la app).
2. Entrá como **admin** y creá un reporte para un jardín cuyo cliente tenga un **email válido**.
3. Revisá la bandeja de entrada (y spam) de ese correo: debería llegar el email con el logo, el resumen del reporte y el botón “Ver reporte online y valorar el servicio”.

Si no llega, revisá los logs en **`api/writable/logs/`** (buscar líneas con “Email reporte”) para ver si hubo error de conexión SMTP o de envío.

---

## Si no funciona en producción

### 1. Contraseña con caracteres especiales
Si tu contraseña tiene `}`, `$`, `#`, espacios o comillas, **ponela entre comillas dobles** en el .env:
```env
EMAIL_SMTP_PASS="atUqAY}0kv6m"
```
Sin comillas, el `}` puede cortar el valor y la contraseña llega mal al SMTP.

### 2. Revisar el log del servidor
En el servidor, abrí el archivo más reciente en **`api/writable/logs/`** (ej. `log-2025-02-02.log`) y buscá líneas que digan **"Email reporte"**. Ahí vas a ver:
- Si el envío falló y el mensaje de error que devolvió el servidor SMTP (autenticación, conexión rechazada, etc.).

### 3. Checklist en el hosting
- **Cuenta de correo:** Que exista `noreply@cesped365.com` y que la contraseña sea la que está en `EMAIL_SMTP_PASS`.
- **Servidor SMTP:** Que sea el que te indica el panel (a veces es `mail.cesped365.com`, otras `smtp.cesped365.com` o el hostname del servidor). Revisá la ayuda de “Correo” o “Email” en el panel.
- **Puerto:** 587 con TLS es lo más común. Si tu hosting dice “SSL”, probá puerto **465** y en el .env: `EMAIL_SMTP_PORT=465` y `EMAIL_SMTP_CRYPTO=ssl`.
- **Envío desde PHP:** Algunos hostings bloquean el envío SMTP desde scripts. En el panel buscá opciones como “SMTP” o “envío desde aplicaciones” o contactá al soporte.
- **Firewall:** Que el servidor permita salida por el puerto 587 (o 465).

### 4. Probar sin TLS/SSL
Si nada funciona, probá temporalmente sin cifrado (solo si el hosting lo permite, puerto 25):
```env
EMAIL_SMTP_PORT=25
EMAIL_SMTP_CRYPTO=
```
(dejá `EMAIL_SMTP_CRYPTO` vacío o comentado).
