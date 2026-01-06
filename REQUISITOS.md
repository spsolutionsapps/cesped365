# Requisitos del Sistema - Césped365

## ⚠️ IMPORTANTE: Requisitos de PHP

El proyecto requiere **PHP 8.1 o superior** (Laravel 11 requiere PHP 8.2).

Tu versión actual de PHP es: **8.0.30**

## Pasos para Configurar el Proyecto

### 1. Actualizar PHP (REQUERIDO)

Necesitas actualizar PHP a la versión 8.1 o superior. Opciones:

#### Opción A: Usar XAMPP con PHP 8.2+
- Descargar XAMPP con PHP 8.2 desde: https://www.apachefriends.org/
- O actualizar tu instalación actual de XAMPP

#### Opción B: Usar Laragon (Recomendado para Windows)
- Descargar Laragon desde: https://laragon.org/
- Laragon permite cambiar fácilmente entre versiones de PHP

#### Opción C: Usar PHP directamente
- Descargar PHP 8.2 desde: https://windows.php.net/download/
- Configurar PATH de Windows

### 2. Verificar Versión de PHP

Después de actualizar, verifica la versión:
```bash
php -v
```

Debe mostrar PHP 8.1 o superior.

### 3. Instalar Dependencias

```bash
composer install
```

### 4. Configurar Entorno

```bash
# Copiar archivo de entorno (si no existe)
copy .env.example .env

# Generar clave de aplicación
php artisan key:generate
```

### 5. Configurar Base de Datos

Edita el archivo `.env` y configura:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cesped365
DB_USERNAME=root
DB_PASSWORD=
```

### 6. Ejecutar Migraciones

```bash
php artisan migrate
```

### 7. Ejecutar Seeders

```bash
php artisan db:seed
```

### 8. Crear Enlace Simbólico para Storage

```bash
php artisan storage:link
```

## Credenciales por Defecto

Después de ejecutar los seeders:

**Administrador:**
- Email: `admin@cesped365.com`
- Password: `password`

**Cliente Demo:**
- Email: `cliente@cesped365.com`
- Password: `password`

## Notas Adicionales

- Asegúrate de que MySQL/MariaDB esté corriendo
- El proyecto usa Laravel 11, que requiere PHP 8.2
- Si tienes problemas con Composer, ejecuta: `composer update`

## Solución Rápida (Si no puedes actualizar PHP)

Si necesitas usar PHP 8.0, tendrías que:
1. Cambiar Laravel 11 a Laravel 10 (no recomendado)
2. O actualizar PHP (recomendado)

La mejor opción es actualizar PHP a 8.2+.

