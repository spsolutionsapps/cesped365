<?php
echo "<h2>🔍 Test de Permisos - Writable Directory</h2>";

// Directorio writable está un nivel arriba de public/
$writableDir = dirname(__DIR__) . '/writable';
$logsDir = $writableDir . '/logs';
$cacheDir = $writableDir . '/cache';
$sessionDir = $writableDir . '/session';
$uploadsDir = $writableDir . '/uploads';

echo "<h3>📁 Información del Sistema</h3>";
echo "<strong>Directorio actual:</strong> " . __DIR__ . "<br>";
echo "<strong>Directorio writable:</strong> " . $writableDir . "<br>";
echo "<strong>Usuario PHP:</strong> " . get_current_user() . "<br>";
echo "<strong>PHP Version:</strong> " . phpversion() . "<br><br>";

echo "<h3>🔧 Permisos de Carpetas</h3>";

$folders = [
    'writable' => $writableDir,
    'writable/cache' => $cacheDir,
    'writable/logs' => $logsDir,
    'writable/session' => $sessionDir,
    'writable/uploads' => $uploadsDir,
];

foreach ($folders as $name => $path) {
    if (file_exists($path)) {
        $perms = substr(sprintf('%o', fileperms($path)), -3);
        $isWritable = is_writable($path);
        
        $status = $isWritable ? '✅ ESCRIBIBLE' : '❌ NO ESCRIBIBLE';
        $color = $isWritable ? 'green' : 'red';
        
        echo "<div style='padding: 10px; margin: 5px 0; background: " . ($isWritable ? '#d1fae5' : '#fee2e2') . "; border-left: 4px solid $color;'>";
        echo "<strong>$name/</strong><br>";
        echo "Permisos: <code>$perms</code> | Estado: <strong>$status</strong>";
        echo "</div>";
    } else {
        echo "<div style='padding: 10px; margin: 5px 0; background: #fee2e2; border-left: 4px solid red;'>";
        echo "<strong>$name/</strong> - ❌ NO EXISTE";
        echo "</div>";
    }
}

echo "<h3>🧪 Test de Escritura en logs/</h3>";

$testFile = $logsDir . '/test-' . date('Y-m-d-H-i-s') . '.txt';
$testContent = "Test de escritura\nFecha: " . date('Y-m-d H:i:s') . "\nUsuario: " . get_current_user();

if (@file_put_contents($testFile, $testContent)) {
    echo "<div style='padding: 15px; background: #d1fae5; border-left: 4px solid green;'>";
    echo "✅ <strong>ÉXITO:</strong> Se pudo escribir en writable/logs/<br>";
    echo "Archivo creado: <code>$testFile</code><br><br>";
    echo "<strong>Contenido:</strong><br>";
    echo "<pre>" . htmlspecialchars(file_get_contents($testFile)) . "</pre>";
    echo "</div>";
    
    // Limpiar archivo de test
    @unlink($testFile);
    echo "<div style='padding: 10px; background: #d1fae5; margin-top: 10px;'>";
    echo "✅ Archivo de test eliminado correctamente";
    echo "</div>";
} else {
    echo "<div style='padding: 15px; background: #fee2e2; border-left: 4px solid red;'>";
    echo "❌ <strong>ERROR:</strong> NO se pudo escribir en writable/logs/<br>";
    echo "Ruta intentada: <code>$testFile</code><br>";
    
    $lastError = error_get_last();
    if ($lastError) {
        echo "<strong>Error:</strong> <code>" . htmlspecialchars($lastError['message']) . "</code><br>";
    }
    
    echo "<br><strong>🔧 SOLUCIÓN:</strong><br>";
    echo "1. Ir a cPanel → File Manager<br>";
    echo "2. Navegar a: <code>public_html/api/writable/</code><br>";
    echo "3. Click derecho en 'writable' → Change Permissions<br>";
    echo "4. Poner: <strong>755</strong> o <strong>777</strong><br>";
    echo "5. Marcar: <strong>Recurse into subdirectories</strong><br>";
    echo "6. Click: Change Permissions";
    echo "</div>";
}

echo "<hr>";
echo "<h3>📋 Resumen</h3>";

$allOk = true;
foreach ($folders as $path) {
    if (!is_writable($path)) {
        $allOk = false;
        break;
    }
}

if ($allOk) {
    echo "<div style='padding: 15px; background: #d1fae5; border-left: 4px solid green;'>";
    echo "✅ <strong>Todo está correcto.</strong> Los permisos están OK.<br><br>";
    echo "Si el login sigue fallando, verifica:<br>";
    echo "1. <code>app.baseURL</code> en .env debe ser: <code>'https://cesped365.com/api/'</code><br>";
    echo "2. La base de datos existe y tiene las tablas<br>";
    echo "3. El usuario admin existe en la tabla users";
    echo "</div>";
} else {
    echo "<div style='padding: 15px; background: #fee2e2; border-left: 4px solid red;'>";
    echo "❌ <strong>HAY PROBLEMAS DE PERMISOS</strong><br><br>";
    echo "<strong>Cambiar permisos ahora:</strong><br>";
    echo "cPanel → File Manager → public_html/api/writable/ → Change Permissions → 755 o 777";
    echo "</div>";
}
?>

<style>
body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    padding: 20px;
    background: #f3f4f6;
}
code {
    background: #1f2937;
    color: #10b981;
    padding: 2px 6px;
    border-radius: 3px;
}
</style>
