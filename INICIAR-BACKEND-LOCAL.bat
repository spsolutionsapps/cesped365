@echo off
echo ========================================
echo   CESPED365 - Iniciar Backend Local
echo ========================================
echo.

cd api
echo Iniciando servidor CodeIgniter en http://localhost:8080...
echo.
echo IMPORTANTE: Deja esta ventana abierta mientras trabajas
echo Presiona Ctrl+C para detener el servidor
echo.

php spark serve --host=localhost --port=8080

pause
