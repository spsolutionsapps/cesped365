<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><title>Ver Logs</title>";
echo "<style>body{font-family:monospace;padding:20px;background:#000;color:#0f0;} .error{color:#f00;font-weight:bold;} .warning{color:#ff0;} pre{background:#1a1a1a;padding:15px;border:2px solid #0f0;overflow-x:auto;white-space:pre-wrap;}</style>";
echo "</head><body>";

echo "<h1>📋 LOGS DE CODEIGNITER - HOY</h1><hr>";

$logsDir = __DIR__ . '/../writable/logs/';
$today = date('Y-m-d');
$logFile = $logsDir . 'log-' . $today . '.log';

echo "<h2>Información</h2>";
echo "<div>Fecha de hoy: $today</div>";
echo "<div>Archivo de log: $logFile</div>";
echo "<div>Directorio logs: $logsDir</div><br>";

if (file_exists($logFile)) {
    echo "<div style='color:#0f0;'>✅ Archivo de log encontrado</div>";
    echo "<div>Tamaño: " . round(filesize($logFile) / 1024, 2) . " KB</div><br>";
    
    $content = file_get_contents($logFile);
    $lines = explode("\n", $content);
    
    echo "<h2>📝 Contenido Completo del Log (" . count($lines) . " líneas)</h2>";
    echo "<pre>";
    
    foreach ($lines as $line) {
        $line = htmlspecialchars($line);
        
        // Colorear según tipo
        if (stripos($line, 'ERROR') !== false || stripos($line, 'CRITICAL') !== false) {
            echo "<span class='error'>$line</span>\n";
        } elseif (stripos($line, 'WARNING') !== false) {
            echo "<span class='warning'>$line</span>\n";
        } else {
            echo "$line\n";
        }
    }
    
    echo "</pre>";
    
    // Resumen de errores
    $errors = array_filter($lines, function($line) {
        return stripos($line, 'ERROR') !== false || stripos($line, 'CRITICAL') !== false;
    });
    
    if (count($errors) > 0) {
        echo "<br><h2>🔍 Resumen de Errores (" . count($errors) . ")</h2>";
        echo "<pre class='error'>";
        foreach ($errors as $error) {
            echo htmlspecialchars($error) . "\n\n";
        }
        echo "</pre>";
    }
    
} else {
    echo "<div class='error'>❌ No se encontró el archivo de log de hoy</div><br>";
    
    // Listar todos los archivos de log disponibles
    if (is_dir($logsDir)) {
        $files = scandir($logsDir);
        $logFiles = array_filter($files, function($file) {
            return strpos($file, 'log-') === 0;
        });
        
        echo "<h2>Archivos de log disponibles:</h2>";
        if (count($logFiles) > 0) {
            echo "<ul>";
            foreach ($logFiles as $file) {
                $filePath = $logsDir . $file;
                $size = round(filesize($filePath) / 1024, 2);
                $date = filemtime($filePath);
                echo "<li><a href='?file=$file'>$file</a> ($size KB, " . date('Y-m-d H:i:s', $date) . ")</li>";
            }
            echo "</ul>";
        } else {
            echo "<div class='error'>No hay archivos de log en writable/logs/</div>";
        }
    } else {
        echo "<div class='error'>El directorio writable/logs/ no existe o no es accesible</div>";
    }
}

// Si se pasó un parámetro de archivo, mostrarlo
if (isset($_GET['file']) && preg_match('/^log-[\d-]+\.log$/', $_GET['file'])) {
    $requestedFile = $logsDir . $_GET['file'];
    if (file_exists($requestedFile)) {
        echo "<hr><h2>📄 Contenido de {$_GET['file']}</h2>";
        echo "<pre>" . htmlspecialchars(file_get_contents($requestedFile)) . "</pre>";
    }
}

echo "</body></html>";
