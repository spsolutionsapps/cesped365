<?php
/**
 * Archivo de prueba para verificar PHP en producción.
 * Subir a: public_html/api/public/test.php
 * Visitar: https://cesped365.com/api/test.php
 * IMPORTANTE: Borrar después de usar (seguridad)
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "PHP OK\n";
echo "Versión: " . PHP_VERSION . "\n";
echo "Extensión MySQLi: " . (extension_loaded('mysqli') ? 'Sí' : 'No') . "\n";
echo "Extensión JSON: " . (extension_loaded('json') ? 'Sí' : 'No') . "\n";
