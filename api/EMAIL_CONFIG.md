# Configuración de email (notificación al cliente)

Cuando un **admin crea un reporte**, el sistema envía un correo al cliente (dueño del jardín) con el resumen del reporte y un enlace para verlo online y valorar el servicio.

## Credenciales en el proyecto

En el archivo **`api/.env`** descomentá y completá estas variables con los datos del correo de tu hosting:

```env
EMAIL_FROM=noreply@cesped365.com
EMAIL_FROM_NAME=Cesped365
EMAIL_PROTOCOL=smtp
EMAIL_SMTP_HOST=mail.cesped365.com
EMAIL_SMTP_USER=noreply@tudominio.com
EMAIL_SMTP_PASS=atUqAY}0kv6m
EMAIL_SMTP_PORT=587
EMAIL_SMTP_CRYPTO=tls

clave atUqAY}0kv6m
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

4. **SPF / reputación (opcional)**  
   Para que no caiga en spam, en el DNS del dominio podés configurar un registro SPF que permita enviar desde el servidor de correo del hosting. El soporte del hosting suele indicar el valor exacto.

---

## Cómo probar

1. Configurá las variables en `api/.env` y reiniciá el servidor (o volvé a cargar la app).
2. Entrá como **admin** y creá un reporte para un jardín cuyo cliente tenga un **email válido**.
3. Revisá la bandeja de entrada (y spam) de ese correo: debería llegar el email con el logo, el resumen del reporte y el botón “Ver reporte online y valorar el servicio”.

Si no llega, revisá los logs en **`api/writable/logs/`** (buscar líneas con “Email reporte”) para ver si hubo error de conexión SMTP o de envío.
