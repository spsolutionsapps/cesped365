<?php
/**
 * Test de Permisos - Cesped365
 * 
 * INSTRUCCIONES:
 * 1. Subir este archivo a: public_html/api/test-permisos.php
 * 2. Visitar: https://cesped365.com/api/test-permisos.php
 * 3. Ver los resultados
 * 4. ¡BORRAR después de usar!
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Permisos - Cesped365</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f3f4f6;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        h1 { color: #1f2937; margin-bottom: 20px; }
        h2 { color: #374151; margin: 25px 0 15px 0; font-size: 20px; }
        .success {
            background: #d1fae5;
            border-left: 4px solid #10b981;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .error {
            background: #fee2e2;
            border-left: 4px solid #ef4444;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .warning {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
        }
        pre {
            background: #1f2937;
            color: #10b981;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            font-size: 13px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        th {
            background: #f3f4f6;
            font-weight: 600;
        }
        code {
            background: #e5e7eb;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Test de Permisos - Cesped365</h1>
        <p style="color: #6b7280; margin-bottom: 20px;">
            Diagnóstico de permisos de escritura en el servidor
        </p>
        
        <div class="warning">
            ⚠️ <strong>BORRAR este archivo después de usarlo</strong><br>
            Ubicación: <code>public_html/api/test-permisos.php</code>
        </div>

        <?php
        echo "<h2>📊 Información del Sistema</h2>";
        echo "<table>";
        echo "<tr><th>Configuración</th><th>Valor</th></tr>";
        echo "<tr><td>PHP User</td><td><code>" . get_current_user() . "</code></td></tr>";
        echo "<tr><td>PHP Version</td><td><code>" . phpversion() . "</code></td></tr>";
        echo "<tr><td>Directorio Base</td><td><code>" . __DIR__ . "</code></td></tr>";
        echo "<tr><td>Safe Mode</td><td><code>" . (ini_get('safe_mode') ? 'ON' : 'OFF') . "</code></td></tr>";
        echo "<tr><td>Open BaseDir</td><td><code>" . (ini_get('open_basedir') ?: 'No restriction') . "</code></td></tr>";
        echo "</table>";
        
        echo "<h2>🔧 Permisos de Carpetas</h2>";
        
        $folders = [
            'writable' => __DIR__ . '/writable',
            'writable/cache' => __DIR__ . '/writable/cache',
            'writable/logs' => __DIR__ . '/writable/logs',
            'writable/session' => __DIR__ . '/writable/session',
            'writable/uploads' => __DIR__ . '/writable/uploads',
        ];
        
        foreach ($folders as $name => $path) {
            if (file_exists($path)) {
                $perms = substr(sprintf('%o', fileperms($path)), -3);
                $isWritable = is_writable($path);
                $owner = fileowner($path);
                
                if ($isWritable) {
                    echo "<div class='success'>";
                    echo "✅ <strong>$name/</strong><br>";
                    echo "Permisos: <code>$perms</code> | Escribible: <strong>SÍ</strong>";
                    echo "</div>";
                } else {
                    echo "<div class='error'>";
                    echo "❌ <strong>$name/</strong><br>";
                    echo "Permisos: <code>$perms</code> | Escribible: <strong>NO</strong><br>";
                    echo "<strong>Solución:</strong> Cambiar permisos a 755 o 777";
                    echo "</div>";
                }
            } else {
                echo "<div class='error'>";
                echo "❌ <strong>$name/</strong> - <strong>NO EXISTE</strong><br>";
                echo "<strong>Solución:</strong> Crear la carpeta";
                echo "</div>";
            }
        }
        
        echo "<h2>🧪 Test de Escritura en logs/</h2>";
        
        $logsDir = __DIR__ . '/writable/logs';
        $testFile = $logsDir . '/test-permissions-' . date('Y-m-d-H-i-s') . '.txt';
        
        // Test de escritura
        $testContent = "Test de escritura\nFecha: " . date('Y-m-d H:i:s') . "\nPHP User: " . get_current_user();
        
        if (@file_put_contents($testFile, $testContent)) {
            echo "<div class='success'>";
            echo "✅ <strong>ÉXITO:</strong> Se pudo escribir archivo<br>";
            echo "Archivo creado: <code>$testFile</code><br>";
            echo "Contenido:<br>";
            echo "<pre>" . htmlspecialchars(file_get_contents($testFile)) . "</pre>";
            echo "</div>";
            
            // Limpiar archivo de test
            @unlink($testFile);
            echo "<div class='success'>✅ Archivo de test eliminado correctamente</div>";
        } else {
            echo "<div class='error'>";
            echo "❌ <strong>ERROR:</strong> NO se pudo escribir archivo<br>";
            echo "Ruta intentada: <code>$testFile</code><br>";
            
            $lastError = error_get_last();
            if ($lastError) {
                echo "Error: <code>" . htmlspecialchars($lastError['message']) . "</code><br>";
            }
            
            echo "<br><strong>Posibles causas:</strong><br>";
            echo "1. Permisos incorrectos (cambiar a 777)<br>";
            echo "2. Carpeta no existe<br>";
            echo "3. Restricciones de PHP (safe_mode, open_basedir)<br>";
            echo "4. Dueño de la carpeta diferente al usuario de PHP";
            echo "</div>";
        }
        
        echo "<h2>🎯 Comandos para Solucionar (si tienes SSH)</h2>";
        echo "<pre>";
        echo "# Cambiar permisos de writable/\n";
        echo "chmod -R 777 ~/public_html/api/writable/\n\n";
        echo "# Verificar permisos\n";
        echo "ls -lah ~/public_html/api/\n\n";
        echo "# Ver logs más recientes\n";
        echo "tail -50 ~/public_html/api/writable/logs/log-*.log\n";
        echo "</pre>";
        
        echo "<h2>📞 Siguiente Paso</h2>";
        
        $allOk = true;
        foreach ($folders as $path) {
            if (!is_writable($path)) {
                $allOk = false;
                break;
            }
        }
        
        if ($allOk) {
            echo "<div class='success'>";
            echo "✅ Los permisos están OK. Si sigue fallando el login, el problema es otro:<br><br>";
            echo "1. Verificar <code>app.baseURL</code> en .env<br>";
            echo "2. Verificar conexión a base de datos<br>";
            echo "3. Verificar que tabla users existe<br>";
            echo "4. Revisar logs en: <code>writable/logs/</code>";
            echo "</div>";
        } else {
            echo "<div class='error'>";
            echo "❌ HAY PROBLEMAS DE PERMISOS<br><br>";
            echo "<strong>SOLUCIÓN:</strong><br>";
            echo "1. cPanel → File Manager<br>";
            echo "2. Ir a: public_html/api/writable/<br>";
            echo "3. Click derecho → Change Permissions<br>";
            echo "4. Poner: <strong>777</strong><br>";
            echo "5. Marcar: <strong>Recurse into subdirectories</strong><br>";
            echo "6. Click: Change Permissions<br><br>";
            echo "Después de que funcione, cambiar a 755 por seguridad.";
            echo "</div>";
        }
        ?>
    </div>
</body>
</html>
