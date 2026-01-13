@echo off
echo ========================================
echo Limpiando todos los caches de Laravel
echo ========================================
echo.

echo [1/5] Limpiando cache de vistas...
php artisan view:clear

echo [2/5] Limpiando cache de aplicacion...
php artisan cache:clear

echo [3/5] Limpiando cache de configuracion...
php artisan config:clear

echo [4/5] Limpiando cache de rutas...
php artisan route:clear

echo [5/5] Eliminando archivos compilados...
powershell -Command "Remove-Item -Path 'storage\framework\views\*' -Force -ErrorAction SilentlyContinue"

echo.
echo ========================================
echo LIMPIEZA COMPLETA!
echo ========================================
echo.
echo Ahora debes limpiar el cache del navegador:
echo 1. Presiona Ctrl + Shift + R en el navegador
echo 2. O abre DevTools (F12), click derecho en recargar
echo    y selecciona "Vaciar cache y recargar de manera forzada"
echo.
echo Si el error persiste, cierra COMPLETAMENTE el navegador
echo y abrelo de nuevo.
echo.
pause
