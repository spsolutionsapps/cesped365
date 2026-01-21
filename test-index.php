<?php
// Activar todos los errores
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<h2>🔍 Test de index.php con errores visibles</h2>";

echo "<h3>📊 Información del Sistema</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Current Directory: " . __DIR__ . "<br>";
echo "FCPATH: " . __DIR__ . DIRECTORY_SEPARATOR . "<br><br>";

define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

echo "<h3>✅ FCPATH definido correctamente</h3><br>";

$pathsFile = FCPATH . '../app/Config/Paths.php';
echo "<strong>Intentando cargar:</strong> <code>$pathsFile</code><br>";

if (file_exists($pathsFile)) {
    echo "✅ <strong>Archivo Paths.php existe</strong><br><br>";
    
    try {
        require $pathsFile;
        echo "✅ <strong>Paths.php cargado sin errores</strong><br><br>";
        
        if (class_exists('Config\Paths')) {
            echo "✅ <strong>Clase Config\Paths existe</strong><br><br>";
            
            $paths = new Config\Paths();
            echo "✅ <strong>Instancia de Paths creada</strong><br><br>";
            
            echo "<h3>📁 Rutas Configuradas</h3>";
            echo "<strong>systemDirectory:</strong> " . $paths->systemDirectory . "<br>";
            echo "<strong>appDirectory:</strong> " . $paths->appDirectory . "<br>";
            echo "<strong>writableDirectory:</strong> " . $paths->writableDirectory . "<br>";
            echo "<strong>testsDirectory:</strong> " . $paths->testsDirectory . "<br>";
            echo "<strong>viewDirectory:</strong> " . $paths->viewDirectory . "<br><br>";
            
            // Verificar que los directorios existen
            echo "<h3>🔍 Verificando Directorios</h3>";
            
            $dirs = [
                'systemDirectory' => $paths->systemDirectory,
                'appDirectory' => $paths->appDirectory,
                'writableDirectory' => $paths->writableDirectory,
            ];
            
            foreach ($dirs as $name => $dir) {
                $exists = is_dir($dir);
                $status = $exists ? '✅' : '❌';
                echo "$status <strong>$name:</strong> <code>$dir</code><br>";
            }
            
            echo "<br>";
            
            // Intentar cargar Boot.php
            $bootFile = $paths->systemDirectory . '/Boot.php';
            echo "<h3>🚀 Intentando cargar Boot.php</h3>";
            echo "<strong>Ruta:</strong> <code>$bootFile</code><br>";
            
            if (file_exists($bootFile)) {
                echo "✅ <strong>Boot.php existe</strong><br><br>";
                
                try {
                    require $bootFile;
                    echo "✅ <strong>Boot.php cargado sin errores</strong><br><br>";
                    
                    if (class_exists('CodeIgniter\Boot')) {
                        echo "✅ <strong>Clase CodeIgniter\Boot existe</strong><br><br>";
                        
                        echo "<h3>🎯 Todo está correcto hasta aquí</h3>";
                        echo "<div style='padding: 15px; background: #d1fae5; border-left: 4px solid green;'>";
                        echo "El problema puede estar en <strong>Boot::bootWeb()</strong><br>";
                        echo "O en alguna configuración específica del servidor.";
                        echo "</div>";
                    } else {
                        echo "❌ <strong>Clase CodeIgniter\Boot NO existe</strong><br>";
                    }
                } catch (Exception $e) {
                    echo "<div style='padding: 15px; background: #fee2e2; border-left: 4px solid red;'>";
                    echo "❌ <strong>ERROR al cargar Boot.php:</strong><br>";
                    echo "<code>" . htmlspecialchars($e->getMessage()) . "</code><br>";
                    echo "<strong>Trace:</strong><br><pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
                    echo "</div>";
                }
            } else {
                echo "❌ <strong>Boot.php NO existe en la ruta especificada</strong><br>";
            }
            
        } else {
            echo "❌ <strong>Clase Config\Paths NO existe después de cargar el archivo</strong><br>";
        }
        
    } catch (Exception $e) {
        echo "<div style='padding: 15px; background: #fee2e2; border-left: 4px solid red;'>";
        echo "❌ <strong>ERROR al cargar Paths.php:</strong><br>";
        echo "<code>" . htmlspecialchars($e->getMessage()) . "</code><br>";
        echo "<strong>Trace:</strong><br><pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        echo "</div>";
    }
} else {
    echo "❌ <strong>Archivo Paths.php NO existe en la ruta especificada</strong><br>";
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
    background: #1f2937;
    color: #10b981;
    padding: 2px 6px;
    border-radius: 3px;
    font-family: monospace;
}
h2, h3 {
    color: #1f2937;
}
</style>
