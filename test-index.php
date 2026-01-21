<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><title>Test Index.php</title>";
echo "<style>body{font-family:monospace;padding:20px;background:#1a1a1a;color:#0f0;} .error{color:#f00;} .ok{color:#0f0;} pre{background:#000;padding:10px;border:1px solid #0f0;overflow-x:auto;}</style>";
echo "</head><body>";

echo "<h1>🔍 TEST INDEX.PHP</h1><hr>";

$indexPath = __DIR__ . '/index.php';

echo "<h2>1. Verificar que index.php existe</h2>";
if (file_exists($indexPath)) {
    echo "<div class='ok'>✅ index.php EXISTE</div>";
    echo "<div>Ruta: $indexPath</div>";
    echo "<div>Tamaño: " . filesize($indexPath) . " bytes</div><br>";
    
    echo "<h2>2. Contenido de index.php</h2>";
    $content = file_get_contents($indexPath);
    
    echo "<div>Total caracteres: " . strlen($content) . "</div>";
    echo "<div>Total líneas: " . substr_count($content, "\n") . "</div><br>";
    
    echo "<h2>3. Verificar que tiene el require del autoloader</h2>";
    
    if (strpos($content, "require FCPATH . '../vendor/autoload.php';") !== false) {
        echo "<div class='ok'>✅ Tiene require FCPATH . '../vendor/autoload.php'</div>";
    } elseif (strpos($content, "vendor/autoload.php") !== false) {
        echo "<div class='error'>⚠️ Tiene vendor/autoload.php pero con sintaxis diferente</div>";
        
        // Encontrar la línea exacta
        $lines = explode("\n", $content);
        foreach ($lines as $num => $line) {
            if (strpos($line, 'vendor/autoload.php') !== false) {
                echo "<div class='error'>Línea " . ($num + 1) . ": <code>" . htmlspecialchars(trim($line)) . "</code></div>";
            }
        }
    } else {
        echo "<div class='error'>❌ NO tiene require del autoloader</div>";
    }
    
    echo "<br><h2>4. Buscar otros requires</h2>";
    $lines = explode("\n", $content);
    foreach ($lines as $num => $line) {
        if (stripos($line, 'require') !== false && stripos($line, '//') === false) {
            echo "<div>Línea " . ($num + 1) . ": <code>" . htmlspecialchars(trim($line)) . "</code></div>";
        }
    }
    
    echo "<br><h2>5. Mostrar index.php completo (primeras 40 líneas)</h2>";
    echo "<pre>" . htmlspecialchars(implode("\n", array_slice($lines, 0, 40))) . "</pre>";
    
    if (count($lines) > 40) {
        echo "<br><h2>6. Últimas 30 líneas</h2>";
        echo "<pre>" . htmlspecialchars(implode("\n", array_slice($lines, -30))) . "</pre>";
    }
    
} else {
    echo "<div class='error'>❌ index.php NO EXISTE</div>";
    echo "<div class='error'>Ruta buscada: $indexPath</div>";
}

echo "<br><h2>7. Información del sistema</h2>";
echo "<div>__DIR__: " . __DIR__ . "</div>";
echo "<div>DOCUMENT_ROOT: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "</div>";
echo "<div>SCRIPT_FILENAME: " . ($_SERVER['SCRIPT_FILENAME'] ?? 'N/A') . "</div>";

echo "</body></html>";
