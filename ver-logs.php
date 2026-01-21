<?php
echo "<h2>📋 Últimos Logs de CodeIgniter</h2>";

$logsDir = dirname(__DIR__) . '/writable/logs/';
$today = date('Y-m-d');
$logFile = $logsDir . 'log-' . $today . '.log';

echo "<h3>📁 Información</h3>";
echo "<strong>Directorio logs:</strong> " . $logsDir . "<br>";
echo "<strong>Archivo buscado:</strong> " . $logFile . "<br><br>";

if (file_exists($logFile)) {
    echo "<div style='padding: 15px; background: #d1fae5; border-left: 4px solid green;'>";
    echo "✅ <strong>Archivo de log encontrado</strong><br>";
    echo "Tamaño: " . round(filesize($logFile) / 1024, 2) . " KB";
    echo "</div>";
    
    echo "<h3>📝 Contenido del Log (últimas 100 líneas)</h3>";
    
    $lines = file($logFile);
    $totalLines = count($lines);
    $startLine = max(0, $totalLines - 100);
    
    echo "<div style='background: #1f2937; color: #10b981; padding: 15px; border-radius: 8px; overflow-x: auto; max-height: 600px; overflow-y: auto;'>";
    echo "<pre style='margin: 0; font-family: monospace; font-size: 12px;'>";
    
    for ($i = $startLine; $i < $totalLines; $i++) {
        $line = htmlspecialchars($lines[$i]);
        
        // Resaltar errores
        if (strpos($line, 'ERROR') !== false || strpos($line, 'CRITICAL') !== false) {
            echo "<span style='color: #ef4444; font-weight: bold;'>$line</span>";
        } elseif (strpos($line, 'WARNING') !== false) {
            echo "<span style='color: #f59e0b;'>$line</span>";
        } else {
            echo $line;
        }
    }
    
    echo "</pre>";
    echo "</div>";
    
    echo "<br>";
    echo "<div style='padding: 15px; background: #fef3c7; border-left: 4px solid #f59e0b;'>";
    echo "📊 <strong>Total de líneas en el log:</strong> $totalLines<br>";
    echo "📋 <strong>Mostrando:</strong> Últimas 100 líneas";
    echo "</div>";
    
    // Buscar errores específicos
    echo "<h3>🔍 Errores Encontrados</h3>";
    
    $errors = [];
    foreach ($lines as $lineNum => $line) {
        if (strpos($line, 'ERROR') !== false || strpos($line, 'CRITICAL') !== false) {
            $errors[] = ($lineNum + 1) . ': ' . trim($line);
        }
    }
    
    if (count($errors) > 0) {
        echo "<div style='padding: 15px; background: #fee2e2; border-left: 4px solid #ef4444;'>";
        echo "❌ <strong>Se encontraron " . count($errors) . " errores:</strong><br><br>";
        echo "<pre style='margin: 0; font-size: 12px; white-space: pre-wrap;'>";
        
        $lastErrors = array_slice($errors, -10); // Últimos 10 errores
        foreach ($lastErrors as $error) {
            echo htmlspecialchars($error) . "\n\n";
        }
        
        echo "</pre>";
        echo "</div>";
    } else {
        echo "<div style='padding: 15px; background: #d1fae5; border-left: 4px solid green;'>";
        echo "✅ <strong>No se encontraron errores en el log</strong>";
        echo "</div>";
    }
    
} else {
    echo "<div style='padding: 15px; background: #fee2e2; border-left: 4px solid red;'>";
    echo "❌ <strong>No se encontró el archivo de log de hoy</strong><br><br>";
    echo "Esto puede significar:<br>";
    echo "1. CodeIgniter no ha registrado ningún error aún<br>";
    echo "2. El formato del nombre de archivo es diferente<br><br>";
    
    echo "<strong>Archivos en el directorio logs/:</strong><br>";
    
    if (is_dir($logsDir)) {
        $files = scandir($logsDir);
        $logFiles = array_filter($files, function($file) {
            return strpos($file, 'log-') === 0;
        });
        
        if (count($logFiles) > 0) {
            echo "<ul>";
            foreach ($logFiles as $file) {
                $filePath = $logsDir . $file;
                $size = round(filesize($filePath) / 1024, 2);
                echo "<li><code>$file</code> ($size KB)</li>";
            }
            echo "</ul>";
        } else {
            echo "<em>No hay archivos de log</em>";
        }
    }
    
    echo "</div>";
}
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
</style>
