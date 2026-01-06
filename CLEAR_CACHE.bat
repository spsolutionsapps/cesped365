@echo off
echo Limpiando cachés de Laravel...
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear
echo Cachés limpiados exitosamente!

