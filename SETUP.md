# Guía de Configuración - Césped365

## Variables de Entorno Requeridas

Agrega las siguientes variables a tu archivo `.env`:

```env
# Mercado Pago (Preparado para futura integración)
MERCADOPAGO_ACCESS_TOKEN=your_access_token_here
MERCADOPAGO_PUBLIC_KEY=your_public_key_here
MERCADOPAGO_WEBHOOK_SECRET=your_webhook_secret_here
```

## Instalación

1. **Instalar dependencias:**
   ```bash
   composer install
   npm install
   ```

2. **Configurar entorno:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Configurar base de datos:**
   - Edita el archivo `.env` con tus credenciales de base de datos
   - Agrega las variables de Mercado Pago (opcional por ahora)

4. **Ejecutar migraciones:**
   ```bash
   php artisan migrate
   ```

5. **Ejecutar seeders:**
   ```bash
   php artisan db:seed
   ```

6. **Crear enlace simbólico para storage:**
   ```bash
   php artisan storage:link
   ```

## Credenciales por Defecto

Después de ejecutar los seeders, puedes iniciar sesión con:

**Administrador:**
- Email: `admin@cesped365.com`
- Password: `password`

**Cliente Demo:**
- Email: `cliente@cesped365.com`
- Password: `password`

## Estructura del Sistema

### Rutas Públicas
- `/` - Landing page principal
- `/planes` - Página de planes
- `/contacto` - Página de contacto
- `/register` - Registro de usuarios
- `/login` - Inicio de sesión

### Dashboard Cliente
- `/dashboard` - Dashboard principal del cliente
- `/dashboard/subscription` - Gestión de suscripciones
- `/dashboard/garden-reports` - Reportes del jardín

### Panel Administrador
- `/admin` - Dashboard del administrador
- `/admin/users` - Gestión de usuarios
- `/admin/plans` - Gestión de planes
- `/admin/subscriptions` - Gestión de suscripciones
- `/admin/garden-reports` - Gestión de reportes del jardín

## Notas Importantes

- El servicio de Mercado Pago está preparado pero NO implementado todavía
- Las variables de Mercado Pago son opcionales por ahora
- El sistema usa Soft UI Dashboard Laravel como base
- La landing page es independiente del dashboard

