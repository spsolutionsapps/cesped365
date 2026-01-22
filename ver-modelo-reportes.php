<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 VERIFICAR ReportModel.php</h1>";
echo "<style>
body { font-family: monospace; background: #1e1e1e; color: #d4d4d4; padding: 20px; }
h1 { color: #4ec9b0; }
.success { background: #1d5a1d; color: #7ff47f; padding: 10px; margin: 10px 0; border-left: 4px solid #7ff47f; }
.error { background: #5a1d1d; color: #f48771; padding: 10px; margin: 10px 0; border-left: 4px solid #f48771; }
.warning { background: #5a4d1d; color: #f4d77f; padding: 10px; margin: 10px 0; border-left: 4px solid #f4d77f; }
pre { white-space: pre-wrap; word-wrap: break-word; background: #2d2d2d; padding: 10px; border-radius: 5px; }
</style>";

$modelPath = __DIR__ . '/api/app/Models/ReportModel.php';

echo "<p><strong>📁 Ruta del modelo:</strong> $modelPath</p>";

if (!file_exists($modelPath)) {
    echo "<div class='error'>❌ El archivo NO existe</div>";
    exit;
}

echo "<div class='success'>✅ El archivo existe</div>";

// Leer el contenido
$content = file_get_contents($modelPath);

// Verificar tamaño
$size = filesize($modelPath);
$lastModified = date('Y-m-d H:i:s', filemtime($modelPath));

echo "<div class='success'>";
echo "<strong>📊 Información del archivo:</strong><br>";
echo "Tamaño: " . number_format($size) . " bytes<br>";
echo "Última modificación: $lastModified<br>";
echo "</div>";

// Buscar $allowedFields
echo "<h2>1. Verificar \$allowedFields</h2>";

if (preg_match('/protected \$allowedFields\s*=\s*\[(.*?)\];/s', $content, $matches)) {
    $fields = $matches[1];
    $fieldsArray = array_map('trim', explode(',', str_replace(["\n", "\r", "'", '"'], '', $fields)));
    $fieldsArray = array_filter($fieldsArray);
    
    echo "<div class='success'><strong>Campos permitidos encontrados (" . count($fieldsArray) . "):</strong><br>";
    foreach ($fieldsArray as $field) {
        echo "- $field<br>";
    }
    echo "</div>";
    
    // Verificar si tiene los campos nuevos
    $camposNuevos = ['visit_date', 'grass_health', 'technician_notes', 'growth_cm'];
    $camposViejos = ['date', 'estado_general', 'jardinero', 'crecimiento_cm'];
    
    $tieneNuevos = 0;
    $tieneViejos = 0;
    
    foreach ($camposNuevos as $campo) {
        if (in_array($campo, $fieldsArray)) $tieneNuevos++;
    }
    
    foreach ($camposViejos as $campo) {
        if (in_array($campo, $fieldsArray)) $tieneViejos++;
    }
    
    echo "<h3>Análisis:</h3>";
    
    if ($tieneNuevos > 0 && $tieneViejos == 0) {
        echo "<div class='success'>✅ <strong>MODELO ACTUALIZADO</strong><br>";
        echo "Tiene campos nuevos: $tieneNuevos/4<br>";
        echo "Tiene campos viejos: $tieneViejos/4</div>";
    } elseif ($tieneViejos > 0) {
        echo "<div class='error'>❌ <strong>MODELO DESACTUALIZADO</strong><br>";
        echo "Tiene campos nuevos: $tieneNuevos/4<br>";
        echo "Tiene campos viejos: $tieneViejos/4<br>";
        echo "<strong>ACCIÓN REQUERIDA:</strong> Subir el archivo actualizado desde GitHub</div>";
    } else {
        echo "<div class='warning'>⚠️ <strong>ESTADO DESCONOCIDO</strong></div>";
    }
} else {
    echo "<div class='error'>❌ No se encontró \$allowedFields en el archivo</div>";
}

// Verificar validationRules
echo "<h2>2. Verificar \$validationRules</h2>";

if (preg_match('/protected \$validationRules\s*=\s*\[(.*?)\];/s', $content, $matches)) {
    $rules = $matches[1];
    echo "<div class='success'><strong>Reglas de validación encontradas:</strong><pre>" . htmlspecialchars(trim($rules)) . "</pre></div>";
    
    if (strpos($rules, 'visit_date') !== false) {
        echo "<div class='success'>✅ Tiene 'visit_date' (CORRECTO)</div>";
    } else {
        echo "<div class='error'>❌ NO tiene 'visit_date' (INCORRECTO)</div>";
    }
    
    if (strpos($rules, 'date') !== false && strpos($rules, 'visit_date') === false) {
        echo "<div class='error'>❌ Tiene 'date' sin 'visit_date' (DESACTUALIZADO)</div>";
    }
} else {
    echo "<div class='error'>❌ No se encontró \$validationRules</div>";
}

echo "<hr>";
echo "<h2>3. Mostrar primeras 80 líneas del modelo</h2>";
$lines = explode("\n", $content);
$firstLines = array_slice($lines, 0, 80);

echo "<pre>" . htmlspecialchars(implode("\n", $firstLines)) . "</pre>";

echo "<hr>";
echo "<p><em>Verificación completada: " . date('Y-m-d H:i:s') . "</em></p>";
?>
