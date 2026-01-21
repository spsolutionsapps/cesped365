<?php
echo "<h2>🔍 Test de CodeIgniter</h2>";

echo "<h3>📁 Estructura de Archivos</h3>";

$baseDir = dirname(__DIR__);
$publicDir = __DIR__;

echo "<strong>Directorio base:</strong> " . $baseDir . "<br>";
echo "<strong>Directorio public:</strong> " . $publicDir . "<br><br>";

$requiredFiles = [
    'index.php' => $publicDir . '/index.php',
    '.htaccess' => $publicDir . '/.htaccess',
    'app/Config/Routes.php' => $baseDir . '/app/Config/Routes.php',
    'app/Config/App.php' => $baseDir . '/app/Config/App.php',
    'vendor/autoload.php' => $baseDir . '/vendor/autoload.php',
    '.env' => $baseDir . '/.env',
];

echo "<h3>📋 Archivos Requeridos</h3>";

$allExists = true;
foreach ($requiredFiles as $name => $path) {
    $exists = file_exists($path);
    $status = $exists ? '✅' : '❌';
    $color = $exists ? '#d1fae5' : '#fee2e2';
    
    if (!$exists) $allExists = false;
    
    echo "<div style='padding: 10px; margin: 5px 0; background: $color; border-left: 4px solid " . ($exists ? 'green' : 'red') . ";'>";
    echo "$status <strong>$name</strong><br>";
    echo "<code style='font-size: 11px;'>$path</code>";
    echo "</div>";
}

echo "<h3>🔍 Contenido de .htaccess</h3>";

$htaccessPath = $publicDir . '/.htaccess';
if (file_exists($htaccessPath)) {
    $htaccess = file_get_contents($htaccessPath);
    echo "<div style='background: #1f2937; color: #10b981; padding: 15px; border-radius: 8px; overflow-x: auto;'>";
    echo "<pre style='margin: 0; font-family: monospace; font-size: 12px;'>";
    echo htmlspecialchars($htaccess);
    echo "</pre>";
    echo "</div>";
} else {
    echo "<div style='padding: 15px; background: #fee2e2; border-left: 4px solid red;'>";
    echo "❌ <strong>NO SE ENCONTRÓ .htaccess</strong><br>";
    echo "Este archivo es CRÍTICO para que CodeIgniter funcione.";
    echo "</div>";
}

echo "<h3>🔍 Contenido de .env (primeras 20 líneas)</h3>";

$envPath = $baseDir . '/.env';
if (file_exists($envPath)) {
    $envLines = file($envPath);
    $displayLines = array_slice($envLines, 0, 20);
    
    echo "<div style='background: #1f2937; color: #10b981; padding: 15px; border-radius: 8px; overflow-x: auto;'>";
    echo "<pre style='margin: 0; font-family: monospace; font-size: 12px;'>";
    
    foreach ($displayLines as $line) {
        // Ocultar passwords
        if (strpos($line, 'password') !== false || strpos($line, 'Password') !== false) {
            $line = preg_replace('/=.*/', '= ********', $line);
        }
        echo htmlspecialchars($line);
    }
    
    echo "</pre>";
    echo "</div>";
} else {
    echo "<div style='padding: 15px; background: #fee2e2; border-left: 4px solid red;'>";
    echo "❌ <strong>NO SE ENCONTRÓ .env</strong><br>";
    echo "Este archivo contiene la configuración del servidor.";
    echo "</div>";
}

echo "<h3>🧪 Test de Carga de CodeIgniter</h3>";

if (file_exists($baseDir . '/vendor/autoload.php')) {
    try {
        require_once $baseDir . '/vendor/autoload.php';
        
        echo "<div style='padding: 15px; background: #d1fae5; border-left: 4px solid green;'>";
        echo "✅ <strong>Autoload de Composer funciona</strong>";
        echo "</div>";
        
        // Verificar constantes de CodeIgniter
        if (defined('SYSTEMPATH')) {
            echo "<div style='padding: 15px; background: #d1fae5; border-left: 4px solid green;'>";
            echo "✅ <strong>CodeIgniter cargado correctamente</strong><br>";
            echo "SYSTEMPATH: <code>" . SYSTEMPATH . "</code>";
            echo "</div>";
        } else {
            echo "<div style='padding: 15px; background: #fef3c7; border-left: 4px solid orange;'>";
            echo "⚠️ <strong>Autoload funciona pero CodeIgniter no se cargó</strong>";
            echo "</div>";
        }
        
    } catch (Exception $e) {
        echo "<div style='padding: 15px; background: #fee2e2; border-left: 4px solid red;'>";
        echo "❌ <strong>ERROR al cargar autoload:</strong><br>";
        echo "<code>" . htmlspecialchars($e->getMessage()) . "</code>";
        echo "</div>";
    }
} else {
    echo "<div style='padding: 15px; background: #fee2e2; border-left: 4px solid red;'>";
    echo "❌ <strong>vendor/autoload.php NO EXISTE</strong><br>";
    echo "Ejecutar: <code>composer install</code> en la carpeta api/";
    echo "</div>";
}

echo "<h3>📊 Resumen</h3>";

if ($allExists) {
    echo "<div style='padding: 15px; background: #d1fae5; border-left: 4px solid green;'>";
    echo "✅ <strong>Todos los archivos necesarios existen</strong><br><br>";
    echo "El problema puede ser:<br>";
    echo "1. El .htaccess no está siendo procesado<br>";
    echo "2. mod_rewrite no está habilitado<br>";
    echo "3. La ruta en Routes.php no existe<br>";
    echo "4. El archivo index.php tiene errores de sintaxis";
    echo "</div>";
} else {
    echo "<div style='padding: 15px; background: #fee2e2; border-left: 4px solid red;'>";
    echo "❌ <strong>FALTAN ARCHIVOS CRÍTICOS</strong><br><br>";
    echo "El backend NO está completamente desplegado.<br>";
    echo "Verifica que se subieron todos los archivos de la carpeta api/.";
    echo "</div>";
}

echo "<h3>🔗 Siguiente Paso</h3>";
echo "<div style='padding: 15px; background: #e0e7ff; border-left: 4px solid #6366f1;'>";
echo "<strong>Intenta acceder directamente a index.php:</strong><br>";
echo "<a href='/api/index.php' target='_blank'>https://cesped365.com/api/index.php</a><br><br>";
echo "Si eso funciona, el problema es el .htaccess.<br>";
echo "Si eso NO funciona, hay un error de sintaxis en index.php.";
echo "</div>";
?>

<style>
body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    padding: 20px;
    background: #f3f4f6;
    max-width: 1200px;
    margin: 0 auto;
}
code {
    background: #e5e7eb;
    padding: 2px 6px;
    border-radius: 3px;
    font-family: monospace;
}
h2, h3 {
    color: #1f2937;
}
a {
    color: #2563eb;
    text-decoration: none;
}
a:hover {
    text-decoration: underline;
}
</style>
