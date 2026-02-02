# Cómo ejecutar migraciones en producción

## Requisitos

- PHP en el servidor (CLI) con la extensión que usa tu proyecto (p. ej. `intl`, `mysqli`).
- El archivo **`.env`** en producción debe tener la base de datos de producción:

```ini
CI_ENVIRONMENT = production

database.default.hostname = tu-servidor-mysql
database.default.database = nombre_bd_produccion
database.default.username = usuario_bd
database.default.password = contraseña_bd
```

## Pasos en el servidor

1. **Subir el código** (incluyendo la carpeta `app/Database/Migrations/` con las nuevas migraciones).

2. **Conectarte al servidor** por SSH (o usar el panel que te permita ejecutar comandos en la carpeta del proyecto).

3. **Ir a la carpeta de la API**:

   ```bash
   cd /ruta/donde/esta/cesped365/api
   ```

   En Windows (ej. hosting con PowerShell):

   ```powershell
   cd "C:\ruta\cesped365\api"
   ```

4. **Ver qué migraciones faltan** (opcional):

   ```bash
   php spark migrate:status
   ```

   Verás cuáles están aplicadas y cuáles pendientes.

5. **Ejecutar las migraciones pendientes**:

   ```bash
   php spark migrate
   ```

   CodeIgniter solo ejecuta las migraciones que aún no están en la tabla `migrations`, así que es seguro ejecutarlo cada vez que subas código con nuevas migraciones.

## Migraciones nuevas (reportes)

Las que agregamos son:

- `2026-02-02-000001_AddGrassColorToReports.php` – agrega columna `grass_color` a `reports`.
- `2026-02-02-000002_AddReportVisualFlagsToReports.php` – agrega `grass_even`, `spots`, `weeds_visible` a `reports`.

Si algo falla, revisa que el usuario de la BD tenga permisos para `ALTER TABLE` en la base de datos de producción.

## Comandos útiles

| Comando | Descripción |
|--------|-------------|
| `php spark migrate` | Ejecuta todas las migraciones pendientes |
| `php spark migrate:status` | Lista migraciones y si están aplicadas o no |
| `php spark migrate:rollback` | Revierte la última migración (usar con cuidado en producción) |

## Resumen rápido

```bash
cd api
php spark migrate
```

Con el `.env` de producción ya configurado, con eso se aplican las nuevas migraciones en producción.
