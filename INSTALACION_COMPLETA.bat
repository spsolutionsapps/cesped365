@echo off
echo ========================================
echo   INSTALACION COMPLETA - CESPE365
echo ========================================
echo.

echo [1/6] Verificando version de PHP...
php -v
if %errorlevel% neq 0 (
    echo ERROR: PHP no encontrado. Por favor instala PHP 8.1 o superior.
    pause
    exit /b 1
)
echo.

echo [2/6] Instalando dependencias de Composer...
composer install
if %errorlevel% neq 0 (
    echo ERROR: Fallo al instalar dependencias. Verifica que tengas PHP 8.1+
    pause
    exit /b 1
)
echo.

echo [3/6] Verificando archivo .env...
if not exist .env (
    echo Creando archivo .env desde .env.example...
    copy .env.example .env
    echo.
    echo IMPORTANTE: Edita el archivo .env y configura tu base de datos antes de continuar.
    echo Presiona cualquier tecla cuando hayas configurado .env...
    pause >nul
)
echo.

echo [4/6] Generando clave de aplicacion...
php artisan key:generate
echo.

echo [5/6] Ejecutando migraciones...
php artisan migrate
if %errorlevel% neq 0 (
    echo ERROR: Fallo al ejecutar migraciones. Verifica tu configuracion de base de datos en .env
    pause
    exit /b 1
)
echo.

echo [6/6] Ejecutando seeders...
php artisan db:seed
echo.

echo [7/7] Creando enlace simbolico para storage...
php artisan storage:link
echo.

echo ========================================
echo   INSTALACION COMPLETADA
echo ========================================
echo.
echo Credenciales por defecto:
echo   Admin: admin@cesped365.com / password
echo   Cliente: cliente@cesped365.com / password
echo.
pause

