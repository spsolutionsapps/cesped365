@echo off
echo ========================================
echo   CESPED365 - Crear Base de Datos Local
echo ========================================
echo.

echo Este script creara la base de datos local 'cesped365'
echo y la poblara con datos de prueba.
echo.
echo IMPORTANTE: Asegurate de tener MySQL corriendo.
echo.
pause

echo.
echo [1/2] Creando base de datos...
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS cesped365 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

echo.
echo [2/2] Importando estructura y datos...
mysql -u root -p cesped365 < database_setup_simple.sql

echo.
echo ========================================
echo   Base de datos creada exitosamente!
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
