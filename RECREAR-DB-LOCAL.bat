@echo off
echo ========================================
echo   CESPED365 - RECREAR Base de Datos Local
echo ========================================
echo.

echo ATENCION: Este script ELIMINARA la base de datos
echo 'cesped365' existente y la creara de nuevo.
echo.
echo Todos los datos locales se perderan.
echo.
pause

echo.
echo [1/3] Eliminando base de datos anterior...
mysql -u root -e "DROP DATABASE IF EXISTS cesped365;"

echo.
echo [2/3] Creando base de datos nueva...
mysql -u root -e "CREATE DATABASE cesped365 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

echo.
echo [3/3] Importando estructura y datos...
mysql -u root cesped365 < database_setup_simple.sql

echo.
echo ========================================
echo   Base de datos recreada exitosamente!
echo ========================================
echo.
echo Credenciales de prueba:
echo.
echo Admin:
echo   Email: admin@cesped365.com
echo   Pass:  admin123
echo.
echo Cliente:
echo   Email: cliente@example.com
echo   Pass:  cliente123
echo.
pause
