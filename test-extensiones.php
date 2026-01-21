<?php
echo "<h2>🔍 Test de Extensiones PHP Requeridas por CodeIgniter 4</h2>";

echo "<h3>📊 Información del Sistema</h3>";
echo "<strong>PHP Version:</strong> " . phpversion() . "<br>";
echo "<strong>Servidor:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "<br><br>";

echo "<h3>✅ Extensiones Requeridas</h3>";

$required = [
    'intl' => 'Internacionalización (CRÍTICO para CodeIgniter 4)',
    'mbstring' => 'Multi-byte string',
    'json' => 'JSON',
    'mysqlnd' => 'MySQL Native Driver',
    'libxml' => 'XML',
    'xml' => 'XML',
];

$allOk = true;
foreach ($required as $ext => $desc) {
    $loaded = extension_loaded($ext);
    $status = $loaded ? '✅' : '❌';
    $color = $loaded ? '#d1fae5' : '#fee2e2';
    
    if (!$loaded) $allOk = false;
    
    echo "<div style='padding: 10px; margin: 5px 0; background: $color; border-left: 4px solid " . ($loaded ? 'green' : 'red') . ";'>";
    echo "$status <strong>$ext</strong> - $desc";
    echo "</div>";
}

echo "<h3>📋 Extensiones Opcionales (Recomendadas)</h3>";

$optional = [
    'curl' => 'cURL',
    'fileinfo' => 'File Info',
    'gd' => 'GD (imágenes)',
    'imagick' => 'ImageMagick',
    'mysqli' => 'MySQL Improved',
    'pdo_mysql' => 'PDO MySQL',
    'openssl' => 'OpenSSL',
    'zip' => 'ZIP',
];

foreach ($optional as $ext => $desc) {
    $loaded = extension_loaded($ext);
    $status = $loaded ? '✅' : '⚠️';
    $color = $loaded ? '#d1fae5' : '#fef3c7';
    
    echo "<div style='padding: 10px; margin: 5px 0; background: $color; border-left: 4px solid " . ($loaded ? 'green' : 'orange') . ";'>";
    echo "$status <strong>$ext</strong> - $desc";
    echo "</div>";
}

echo "<h3>📊 Resumen</h3>";

if ($allOk) {
    echo "<div style='padding: 15px; background: #d1fae5; border-left: 4px solid green;'>";
    echo "✅ <strong>Todas las extensiones requeridas están instaladas</strong><br><br>";
    echo "El problema debe estar en otro lugar:<br>";
    echo "1. Algún archivo de configuración con error de sintaxis<br>";
    echo "2. El archivo Routes.php<br>";
    echo "3. Algún Controller con errores<br>";
    echo "4. El archivo .env mal formateado";
    echo "</div>";
} else {
    echo "<div style='padding: 15px; background: #fee2e2; border-left: 4px solid red;'>";
    echo "❌ <strong>FALTAN EXTENSIONES CRÍTICAS</strong><br><br>";
    echo "CodeIgniter 4 REQUIERE la extensión <strong>intl</strong>.<br><br>";
    echo "<strong>Solución:</strong><br>";
    echo "1. Contacta a tu hosting<br>";
    echo "2. Pide que habiliten la extensión <code>php-intl</code><br>";
    echo "3. O actívala tú mismo en cPanel → Select PHP Version → Extensions";
    echo "</div>";
}

echo "<h3>📝 Todas las Extensiones Cargadas</h3>";
echo "<details>";
echo "<summary>Click para ver todas las extensiones (total: " . count(get_loaded_extensions()) . ")</summary>";
echo "<div style='background: #1f2937; color: #10b981; padding: 15px; border-radius: 8px; margin-top: 10px;'>";
echo "<pre style='margin: 0; font-size: 12px;'>";

$extensions = get_loaded_extensions();
sort($extensions);
foreach ($extensions as $ext) {
    echo "✅ " . $ext . "\n";
}

echo "</pre>";
echo "</div>";
echo "</details>";

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
details {
    margin: 15px 0;
    cursor: pointer;
}
summary {
    padding: 10px;
    background: #e0e7ff;
    border-left: 4px solid #6366f1;
    font-weight: bold;
}
</style>
