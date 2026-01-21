<?php
// Activar TODOS los errores de PHP
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);

echo "<!DOCTYPE html><html><head><title>Debug Completo</title>";
echo "<style>body{font-family:monospace;padding:20px;background:#1a1a1a;color:#0f0;} .error{color:#f00;} .ok{color:#0f0;} .warn{color:#ff0;}</style>";
echo "</head><body>";

echo "<h1>🔍 DEBUG COMPLETO - CESPED365</h1><hr>";

// Test 1: PHP Básico
echo "<h2>1. PHP Básico</h2>";
echo "<div class='ok'>✅ PHP Version: " . phpversion() . "</div>";
echo "<div class='ok'>✅ Current Dir: " . __DIR__ . "</div><br>";

// Test 2: Definir FCPATH
echo "<h2>2. Definir FCPATH</h2>";
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
echo "<div class='ok'>✅ FCPATH: " . FCPATH . "</div><br>";

// Test 3: Cargar Paths.php
echo "<h2>3. Cargar Paths.php</h2>";
$pathsFile = FCPATH . '../app/Config/Paths.php';

try {
    if (file_exists($pathsFile)) {
        echo "<div class='ok'>✅ Paths.php existe</div>";
        require $pathsFile;
        echo "<div class='ok'>✅ Paths.php cargado</div>";
        
        $paths = new Config\Paths();
        echo "<div class='ok'>✅ Paths instanciado</div>";
        echo "<div>- systemDirectory: " . $paths->systemDirectory . "</div>";
        echo "<div>- writableDirectory: " . $paths->writableDirectory . "</div><br>";
    } else {
        echo "<div class='error'>❌ Paths.php NO existe en: $pathsFile</div>";
        exit;
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ ERROR en Paths.php: " . $e->getMessage() . "</div>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    exit;
}

// Test 4: Cargar Boot.php
echo "<h2>4. Cargar Boot.php</h2>";
$bootFile = $paths->systemDirectory . '/Boot.php';

try {
    if (file_exists($bootFile)) {
        echo "<div class='ok'>✅ Boot.php existe</div>";
        require $bootFile;
        echo "<div class='ok'>✅ Boot.php cargado</div><br>";
    } else {
        echo "<div class='error'>❌ Boot.php NO existe en: $bootFile</div>";
        exit;
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ ERROR en Boot.php: " . $e->getMessage() . "</div>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    exit;
}

// Test 5: Intentar bootear CodeIgniter
echo "<h2>5. Intentar CodeIgniter\Boot::bootWeb()</h2>";

try {
    // Capturar output
    ob_start();
    
    echo "<div class='warn'>⚠️ Intentando bootear CodeIgniter...</div>";
    
    $exitCode = CodeIgniter\Boot::bootWeb($paths);
    
    $output = ob_get_clean();
    
    echo "<div class='ok'>✅ bootWeb() completado</div>";
    echo "<div>Exit code: $exitCode</div>";
    
    if (!empty($output)) {
        echo "<div class='warn'>⚠️ Output capturado:</div>";
        echo "<pre>" . htmlspecialchars($output) . "</pre>";
    }
    
} catch (Throwable $e) {
    ob_end_clean();
    
    echo "<div class='error'>❌ FATAL ERROR en bootWeb():</div>";
    echo "<div class='error'>Tipo: " . get_class($e) . "</div>";
    echo "<div class='error'>Mensaje: " . $e->getMessage() . "</div>";
    echo "<div class='error'>Archivo: " . $e->getFile() . ":" . $e->getLine() . "</div>";
    echo "<br>";
    echo "<div class='warn'>Stack Trace:</div>";
    echo "<pre style='color:#ff0;font-size:11px;'>" . $e->getTraceAsString() . "</pre>";
    
    // Intentar leer archivo .env para ver si está bien formateado
    echo "<hr><h2>6. Verificar archivo .env</h2>";
    $envFile = dirname(__DIR__) . '/.env';
    
    if (file_exists($envFile)) {
        echo "<div class='ok'>✅ .env existe</div>";
        $envContent = file_get_contents($envFile);
        $envLines = explode("\n", $envContent);
        
        echo "<div>Total líneas: " . count($envLines) . "</div>";
        echo "<div>Primeras 30 líneas:</div>";
        echo "<pre style='color:#0ff;font-size:11px;'>";
        
        for ($i = 0; $i < min(30, count($envLines)); $i++) {
            $line = $envLines[$i];
            // Ocultar passwords
            if (stripos($line, 'password') !== false) {
                $line = preg_replace('/=.*/', '= ********', $line);
            }
            echo ($i + 1) . ": " . htmlspecialchars($line) . "\n";
        }
        
        echo "</pre>";
    } else {
        echo "<div class='error'>❌ .env NO existe</div>";
    }
}

echo "</body></html>";
