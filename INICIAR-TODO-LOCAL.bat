@echo off
echo ========================================
echo   CESPED365 - Iniciar TODO (Frontend + Backend)
echo ========================================
echo.

echo [1/2] Iniciando Backend en http://localhost:8080...
start "Backend CodeIgniter" cmd /k "cd api && php spark serve --host=localhost --port=8080"

timeout /t 3 /nobreak >nul

echo [2/2] Iniciando Frontend en http://localhost:3000...
start "Frontend Svelte" cmd /k "npm run dev"

echo.
echo ========================================
echo   CESPED365 - Servidores Iniciados
echo ========================================
echo.
echo Frontend: http://localhost:3000
echo Backend:  http://localhost:8080
echo.
echo Se abrieron 2 ventanas de terminal.
echo NO las cierres mientras trabajas.
echo.
pause
