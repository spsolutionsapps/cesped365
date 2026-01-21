<?php
// ACTIVAR TODOS LOS ERRORES
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<!DOCTYPE html><html><head><title>Ver Error Real</title>";
echo "<style>body{font-family:monospace;padding:20px;background:#000;color:#0f0;} .error{color:#f00;font-weight:bold;} pre{background:#1a1a1a;padding:15px;border:2px solid #0f0;overflow-x:auto;}</style>";
echo "</head><body>";

echo "<h1>🔍 CAPTURAR ERROR REAL DE INDEX.PHP</h1><hr>";

echo "<h2>1. Información del Sistema</h2>";
echo "<div>PHP Version: " . phpversion() . "</div>";
echo "<div>Current Dir: " . __DIR__ . "</div>";
echo "<div>Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "</div><br>";

// Registrar handler de errores fatales
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        echo "<div class='error'>";
        echo "<h2>❌ ERROR FATAL CAPTURADO:</h2>";
        echo "<pre>";
        echo "Tipo: " . $error['type'] . "\n";
        echo "Mensaje: " . $error['message'] . "\n";
        echo "Archivo: " . $error['file'] . "\n";
        echo "Línea: " . $error['line'] . "\n";
        echo "</pre>";
        echo "</div>";
    }
});

echo "<h2>2. Intentando ejecutar el código de index.php paso a paso</h2><br>";

try {
    echo "<div>✅ Paso 1: Definir FCPATH</div>";
    define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
    echo "<div>FCPATH = " . FCPATH . "</div><br>";
    
    echo "<div>✅ Paso 2: Cargar Paths.php</div>";
    $pathsFile = FCPATH . '../app/Config/Paths.php';
    echo "<div>Archivo: $pathsFile</div>";
    
    if (file_exists($pathsFile)) {
        echo "<div>✅ Paths.php existe</div>";
        require $pathsFile;
        echo "<div>✅ Paths.php cargado</div><br>";
    } else {
        echo "<div class='error'>❌ Paths.php NO EXISTE</div>";
        exit;
    }
    
    echo "<div>✅ Paso 3: Cargar autoloader</div>";
    $autoloadFile = FCPATH . '../vendor/autoload.php';
    echo "<div>Archivo: $autoloadFile</div>";
    
    if (file_exists($autoloadFile)) {
        echo "<div>✅ autoload.php existe</div>";
        require $autoloadFile;
        echo "<div>✅ autoload.php cargado</div><br>";
    } else {
        echo "<div class='error'>❌ autoload.php NO EXISTE</div>";
        exit;
    }
    
    echo "<div>✅ Paso 4: Instanciar Config\\Paths</div>";
    $paths = new \Config\Paths();
    echo "<div>✅ Paths instanciado</div>";
    echo "<div>systemDirectory: " . $paths->systemDirectory . "</div><br>";
    
    echo "<div>✅ Paso 5: Cargar Boot.php</div>";
    $bootFile = $paths->systemDirectory . '/Boot.php';
    echo "<div>Archivo: $bootFile</div>";
    
    if (file_exists($bootFile)) {
        echo "<div>✅ Boot.php existe</div>";
        require $bootFile;
        echo "<div>✅ Boot.php cargado</div><br>";
    } else {
        echo "<div class='error'>❌ Boot.php NO EXISTE en: $bootFile</div>";
        exit;
    }
    
    echo "<div>✅ Paso 6: Intentar bootWeb()</div>";
    echo "<div class='error'>⚠️ Este es el paso que probablemente falla...</div><br>";
    
    // Aquí es donde normalmente falla
    ob_start();
    $result = \CodeIgniter\Boot::bootWeb($paths);
    $output = ob_get_clean();
    
    echo "<div>✅ bootWeb() ejecutado sin errores fatales</div>";
    if ($output) {
        echo "<h3>Output de bootWeb():</h3>";
        echo "<pre>" . htmlspecialchars($output) . "</pre>";
    }
    
} catch (\Throwable $e) {
    echo "<div class='error'>";
    echo "<h2>❌ EXCEPCIÓN CAPTURADA:</h2>";
    echo "<pre>";
    echo "Tipo: " . get_class($e) . "\n";
    echo "Mensaje: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n\n";
    echo "Stack Trace:\n" . $e->getTraceAsString();
    echo "</pre>";
    echo "</div>";
}

echo "</body></html>";
