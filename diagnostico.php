<?php
/**
 * Script de Diagnóstico - Cesped365
 * 
 * INSTRUCCIONES:
 * 1. Subir este archivo a public_html/diagnostico.php
 * 2. Visitar: https://cesped365.com/diagnostico.php
 * 3. Ver los resultados
 * 4. ¡BORRAR después de usar! (contiene información sensible)
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico Cesped365</title>
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
        h1 {
            color: #1f2937;
            margin-bottom: 10px;
        }
        .warning {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
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
        .section {
            margin: 25px 0;
            padding: 20px;
            background: #f9fafb;
            border-radius: 8px;
        }
        h2 {
            color: #374151;
            margin-bottom: 15px;
            font-size: 20px;
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
            color: #1f2937;
        }
        code {
            background: #1f2937;
            color: #10b981;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 13px;
        }
        .ok { color: #10b981; font-weight: bold; }
        .fail { color: #ef4444; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Diagnóstico del Servidor - Cesped365</h1>
        <p style="color: #6b7280; margin-bottom: 20px;">
            Información del servidor y configuración
        </p>
        
        <div class="warning">
            ⚠️ <strong>IMPORTANTE:</strong> Borrar este archivo después de usarlo. Contiene información sensible del servidor.
        </div>

        <!-- ========================================
             1. INFORMACIÓN DEL SERVIDOR
             ======================================== -->
        <div class="section">
            <h2>📊 Información del Servidor</h2>
            <table>
                <tr>
                    <th>Configuración</th>
                    <th>Valor</th>
                </tr>
                <tr>
                    <td>Versión de PHP</td>
                    <td><strong><?php echo phpversion(); ?></strong>
                        <?php if (version_compare(phpversion(), '8.1.0', '>=')): ?>
                            <span class="ok">✓ Compatible</span>
                        <?php else: ?>
                            <span class="fail">✗ Requiere PHP 8.1+</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td>Sistema Operativo</td>
                    <td><?php echo php_uname('s') . ' ' . php_uname('r'); ?></td>
                </tr>
                <tr>
                    <td>Servidor Web</td>
                    <td><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Desconocido'; ?></td>
                </tr>
                <tr>
                    <td>Document Root</td>
                    <td><code><?php echo $_SERVER['DOCUMENT_ROOT']; ?></code></td>
                </tr>
                <tr>
                    <td>Ruta actual</td>
                    <td><code><?php echo __DIR__; ?></code></td>
                </tr>
                <tr>
                    <td>Dominio</td>
                    <td><?php echo $_SERVER['HTTP_HOST'] ?? 'N/A'; ?></td>
                </tr>
            </table>
        </div>

        <!-- ========================================
             2. EXTENSIONES PHP
             ======================================== -->
        <div class="section">
            <h2>🔧 Extensiones PHP Requeridas</h2>
            <table>
                <tr>
                    <th>Extensión</th>
                    <th>Estado</th>
                </tr>
                <?php
                $required = ['mysqli', 'mbstring', 'intl', 'json', 'curl'];
                foreach ($required as $ext) {
                    $loaded = extension_loaded($ext);
                    echo "<tr>";
                    echo "<td><code>$ext</code></td>";
                    echo "<td>";
                    echo $loaded 
                        ? "<span class='ok'>✓ Instalada</span>" 
                        : "<span class='fail'>✗ NO instalada</span>";
                    echo "</td>";
                    echo "</tr>";
                }
                ?>
            </table>
        </div>

        <!-- ========================================
             3. ARCHIVOS DEL SITIO
             ======================================== -->
        <div class="section">
            <h2>📁 Archivos del Sitio</h2>
            <table>
                <tr>
                    <th>Archivo/Carpeta</th>
                    <th>Existe</th>
                    <th>Permisos</th>
                </tr>
                <?php
                $files = [
                    'index.html' => 'Página principal',
                    '.htaccess' => 'Configuración Apache',
                    'assets' => 'Recursos del frontend',
                    'api' => 'Backend CodeIgniter',
                    'api/public' => 'Entrada del backend',
                    'api/public/index.php' => 'Index del backend',
                    'api/app' => 'Código del backend',
                    'api/vendor' => 'Dependencias del backend',
                    'api/writable' => 'Logs y cache',
                    'api/.env' => 'Configuración del backend'
                ];
                
                foreach ($files as $file => $desc) {
                    $path = __DIR__ . '/' . $file;
                    $exists = file_exists($path);
                    $perms = $exists ? substr(sprintf('%o', fileperms($path)), -3) : 'N/A';
                    
                    echo "<tr>";
                    echo "<td><strong>$file</strong><br><small style='color:#6b7280'>$desc</small></td>";
                    echo "<td>";
                    echo $exists 
                        ? "<span class='ok'>✓ Existe</span>" 
                        : "<span class='fail'>✗ NO existe</span>";
                    echo "</td>";
                    echo "<td><code>$perms</code></td>";
                    echo "</tr>";
                }
                ?>
            </table>
        </div>

        <!-- ========================================
             4. TESTS DE CONECTIVIDAD
             ======================================== -->
        <div class="section">
            <h2>🧪 Tests de Conectividad</h2>
            
            <?php
            // Test 1: index.html
            if (file_exists(__DIR__ . '/index.html')) {
                echo '<div class="success">✓ <strong>index.html</strong> encontrado</div>';
                echo '<p style="margin-left: 20px; color: #6b7280;">Tamaño: ' . 
                     round(filesize(__DIR__ . '/index.html') / 1024, 2) . ' KB</p>';
            } else {
                echo '<div class="error">✗ <strong>index.html</strong> NO encontrado</div>';
                echo '<p style="margin-left: 20px;">Debes subir el contenido de dist/ a public_html/</p>';
            }
            
            // Test 2: API
            if (file_exists(__DIR__ . '/api/public/index.php')) {
                echo '<div class="success" style="margin-top:10px">✓ <strong>Backend API</strong> encontrado</div>';
            } else {
                echo '<div class="error" style="margin-top:10px">✗ <strong>Backend API</strong> NO encontrado</div>';
            }
            
            // Test 3: .htaccess
            if (file_exists(__DIR__ . '/.htaccess')) {
                echo '<div class="success" style="margin-top:10px">✓ <strong>.htaccess</strong> existe</div>';
                $htaccess_content = file_get_contents(__DIR__ . '/.htaccess');
                if (strpos($htaccess_content, 'RewriteEngine') !== false) {
                    echo '<p style="margin-left: 20px; color: #6b7280;">mod_rewrite configurado</p>';
                }
            } else {
                echo '<div class="error" style="margin-top:10px">✗ <strong>.htaccess</strong> NO existe</div>';
            }
            ?>
        </div>

        <!-- ========================================
             5. RECOMENDACIONES
             ======================================== -->
        <div class="section">
            <h2>💡 Recomendaciones</h2>
            
            <?php if (!file_exists(__DIR__ . '/index.html')): ?>
                <div class="error">
                    <strong>Problema detectado:</strong> index.html no existe<br><br>
                    <strong>Solución:</strong><br>
                    1. En tu PC local: <code>npm run build</code><br>
                    2. Subir contenido de <code>dist/</code> a <code>public_html/</code><br>
                    3. Verificar que index.html esté en la raíz de public_html/
                </div>
            <?php endif; ?>
            
            <?php if (version_compare(phpversion(), '8.1.0', '<')): ?>
                <div class="error">
                    <strong>Problema detectado:</strong> PHP version muy antigua<br><br>
                    <strong>Solución:</strong><br>
                    1. cPanel → MultiPHP Manager<br>
                    2. Seleccionar tu dominio<br>
                    3. Cambiar a PHP 8.1 o superior
                </div>
            <?php endif; ?>
            
            <?php 
            $missing_ext = [];
            foreach (['mysqli', 'mbstring', 'intl', 'json', 'curl'] as $ext) {
                if (!extension_loaded($ext)) {
                    $missing_ext[] = $ext;
                }
            }
            if (!empty($missing_ext)): 
            ?>
                <div class="error">
                    <strong>Extensiones faltantes:</strong> <?php echo implode(', ', $missing_ext); ?><br><br>
                    <strong>Solución:</strong><br>
                    1. cPanel → MultiPHP INI Editor<br>
                    2. Habilitar extensiones faltantes<br>
                    3. O contactar soporte del hosting
                </div>
            <?php else: ?>
                <div class="success">
                    ✓ Todas las extensiones PHP necesarias están instaladas
                </div>
            <?php endif; ?>
        </div>

        <!-- ========================================
             6. INFORMACIÓN ADICIONAL
             ======================================== -->
        <div class="section">
            <h2>📝 Información Adicional</h2>
            <table>
                <tr>
                    <td>max_execution_time</td>
                    <td><?php echo ini_get('max_execution_time'); ?> segundos</td>
                </tr>
                <tr>
                    <td>memory_limit</td>
                    <td><?php echo ini_get('memory_limit'); ?></td>
                </tr>
                <tr>
                    <td>upload_max_filesize</td>
                    <td><?php echo ini_get('upload_max_filesize'); ?></td>
                </tr>
                <tr>
                    <td>post_max_size</td>
                    <td><?php echo ini_get('post_max_size'); ?></td>
                </tr>
                <tr>
                    <td>Zona horaria</td>
                    <td><?php echo date_default_timezone_get(); ?></td>
                </tr>
            </table>
        </div>

        <div class="note">
            <strong>🔒 Seguridad:</strong> Después de revisar esta información, <strong>BORRA</strong> este archivo del servidor.<br>
            Comando: Eliminar <code>public_html/diagnostico.php</code>
        </div>
    </div>
</body>
</html>
