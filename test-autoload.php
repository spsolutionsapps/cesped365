<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><title>Test Autoload</title>";
echo "<style>body{font-family:monospace;padding:20px;background:#1a1a1a;color:#0f0;} .error{color:#f00;} .ok{color:#0f0;}</style>";
echo "</head><body>";

echo "<h1>🔍 TEST AUTOLOADER</h1><hr>";

$autoloadPath = __DIR__ . '/../vendor/autoload.php';

echo "<h2>1. Verificar ruta del autoloader</h2>";
echo "<div>Ruta esperada: <code>$autoloadPath</code></div>";

if (file_exists($autoloadPath)) {
    echo "<div class='ok'>✅ autoload.php EXISTE</div>";
    echo "<div>Tamaño: " . filesize($autoloadPath) . " bytes</div><br>";
    
    echo "<h2>2. Cargar autoloader</h2>";
    try {
        require $autoloadPath;
        echo "<div class='ok'>✅ Autoloader cargado sin errores</div><br>";
        
        echo "<h2>3. Verificar clases de CodeIgniter</h2>";
        
        $classes = [
            'CodeIgniter\Boot',
            'CodeIgniter\Config\DotEnv',
            'CodeIgniter\Exceptions\InvalidArgumentException',
            'CodeIgniter\HTTP\Request',
            'CodeIgniter\Model',
        ];
        
        foreach ($classes as $class) {
            $exists = class_exists($class);
            $status = $exists ? '✅' : '❌';
            $color = $exists ? 'ok' : 'error';
            echo "<div class='$color'>$status $class</div>";
        }
        
        echo "<br><h2>4. Verificar vendor/codeigniter4/</h2>";
        $ciPath = __DIR__ . '/../vendor/codeigniter4/framework/';
        
        if (is_dir($ciPath)) {
            echo "<div class='ok'>✅ Carpeta codeigniter4 existe</div>";
            
            $systemPath = $ciPath . 'system/';
            if (is_dir($systemPath)) {
                echo "<div class='ok'>✅ Carpeta system/ existe</div>";
                
                $exceptionsPath = $systemPath . 'Exceptions/';
                if (is_dir($exceptionsPath)) {
                    echo "<div class='ok'>✅ Carpeta Exceptions/ existe</div>";
                    
                    $invalidArgPath = $exceptionsPath . 'InvalidArgumentException.php';
                    if (file_exists($invalidArgPath)) {
                        echo "<div class='ok'>✅ InvalidArgumentException.php EXISTE</div>";
                        echo "<div>Ruta: $invalidArgPath</div>";
                    } else {
                        echo "<div class='error'>❌ InvalidArgumentException.php NO EXISTE</div>";
                        echo "<div class='error'>Archivos en Exceptions/:</div>";
                        $files = scandir($exceptionsPath);
                        foreach ($files as $file) {
                            if ($file !== '.' && $file !== '..') {
                                echo "<div>- $file</div>";
                            }
                        }
                    }
                } else {
                    echo "<div class='error'>❌ Carpeta Exceptions/ NO existe</div>";
                }
            } else {
                echo "<div class='error'>❌ Carpeta system/ NO existe</div>";
            }
        } else {
            echo "<div class='error'>❌ Carpeta codeigniter4 NO existe</div>";
            echo "<div class='error'>Esto significa que vendor/ no está completo</div>";
            echo "<div class='error'>Ejecutar: composer install</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ ERROR al cargar autoloader: " . $e->getMessage() . "</div>";
    }
    
} else {
    echo "<div class='error'>❌ autoload.php NO EXISTE</div>";
    echo "<div class='error'>Ruta buscada: $autoloadPath</div>";
    echo "<div class='error'>Ejecutar: composer install en la carpeta api/</div>";
}

echo "<br><h2>5. Estructura de vendor/</h2>";
$vendorPath = __DIR__ . '/../vendor/';

if (is_dir($vendorPath)) {
    echo "<div class='ok'>✅ Carpeta vendor/ existe</div>";
    
    $dirs = scandir($vendorPath);
    $importantDirs = array_filter($dirs, function($dir) {
        return $dir !== '.' && $dir !== '..' && is_dir(__DIR__ . '/../vendor/' . $dir);
    });
    
    echo "<div>Carpetas en vendor/ (" . count($importantDirs) . "):</div>";
    echo "<ul>";
    foreach (array_slice($importantDirs, 0, 10) as $dir) {
        echo "<li>$dir</li>";
    }
    if (count($importantDirs) > 10) {
        echo "<li>... y " . (count($importantDirs) - 10) . " más</li>";
    }
    echo "</ul>";
} else {
    echo "<div class='error'>❌ Carpeta vendor/ NO existe</div>";
}

echo "</body></html>";
