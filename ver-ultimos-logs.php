<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>📋 ÚLTIMOS LOGS DE CODEIGNITER</h1>";
echo "<style>
body { font-family: monospace; background: #1e1e1e; color: #d4d4d4; padding: 20px; }
h1 { color: #4ec9b0; }
.error { background: #5a1d1d; color: #f48771; padding: 10px; margin: 10px 0; border-left: 4px solid #f48771; }
.critical { background: #5a1d1d; color: #ff6b6b; padding: 10px; margin: 10px 0; border-left: 4px solid #ff6b6b; }
.info { background: #1e3a5f; color: #9cdcfe; padding: 10px; margin: 10px 0; border-left: 4px solid #9cdcfe; }
pre { white-space: pre-wrap; word-wrap: break-word; }
</style>";

// Ruta al directorio de logs
$logDir = __DIR__ . '/api/writable/logs/';

// Obtener archivo de log de hoy
$today = date('Y-m-d');
$logFile = $logDir . 'log-' . $today . '.php';

echo "<p><strong>📁 Buscando log:</strong> $logFile</p>";

if (!file_exists($logFile)) {
    echo "<div class='error'>❌ No existe el archivo de log de hoy</div>";
    
    // Buscar el log más reciente
    $files = glob($logDir . 'log-*.php');
    if ($files) {
        rsort($files);
        $logFile = $files[0];
        echo "<p>📄 Usando el log más reciente: " . basename($logFile) . "</p>";
    } else {
        echo "<div class='error'>❌ No hay archivos de log disponibles</div>";
        exit;
    }
}

// Leer el contenido del log
$logContent = file_get_contents($logFile);

// Quitar las primeras 4 líneas (código PHP de seguridad de CodeIgniter)
$lines = explode("\n", $logContent);
$lines = array_slice($lines, 4);
$logContent = implode("\n", $lines);

// Dividir por líneas y mostrar solo las últimas 100
$allLines = explode("\n", $logContent);
$recentLines = array_slice($allLines, -100);

echo "<h2>📊 Estadísticas</h2>";
echo "<p><strong>Total de líneas:</strong> " . count($allLines) . "</p>";
echo "<p><strong>Mostrando:</strong> Últimas " . count($recentLines) . " líneas</p>";

// Contar errores
$errors = 0;
$critical = 0;
foreach ($recentLines as $line) {
    if (strpos($line, 'ERROR') !== false) $errors++;
    if (strpos($line, 'CRITICAL') !== false) $critical++;
}

echo "<p><strong>🔴 Errores:</strong> $errors</p>";
echo "<p><strong>💀 Críticos:</strong> $critical</p>";

echo "<hr>";
echo "<h2>📝 Últimas 100 líneas del log</h2>";

foreach ($recentLines as $line) {
    if (empty(trim($line))) continue;
    
    $class = '';
    if (strpos($line, 'ERROR') !== false) {
        $class = 'error';
    } elseif (strpos($line, 'CRITICAL') !== false) {
        $class = 'critical';
    } elseif (strpos($line, 'INFO') !== false) {
        $class = 'info';
    }
    
    if ($class) {
        echo "<div class='$class'><pre>" . htmlspecialchars($line) . "</pre></div>";
    } else {
        echo "<div><pre>" . htmlspecialchars($line) . "</pre></div>";
    }
}

echo "<hr>";
echo "<p><em>Actualizado: " . date('Y-m-d H:i:s') . "</em></p>";
?>
