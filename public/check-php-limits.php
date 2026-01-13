<?php
/**
 * Verificador de Límites PHP para Subida de Archivos
 * Acceder via: http://localhost:8000/check-php-limits.php
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>PHP Limits Checker</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 10px;
        }
        .setting {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .setting:last-child {
            border-bottom: none;
        }
        .label {
            font-weight: bold;
            color: #555;
        }
        .value {
            color: #2196F3;
            font-family: monospace;
        }
        .warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin-top: 20px;
        }
        .success {
            background: #d4edda;
            border-left: 4px solid #28a745;
            padding: 15px;
            margin-top: 20px;
        }
        .error {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            padding: 15px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>📊 Configuración PHP para Subida de Archivos</h1>
        
        <div class="setting">
            <span class="label">upload_max_filesize:</span>
            <span class="value"><?php echo ini_get('upload_max_filesize'); ?></span>
        </div>
        
        <div class="setting">
            <span class="label">post_max_size:</span>
            <span class="value"><?php echo ini_get('post_max_size'); ?></span>
        </div>
        
        <div class="setting">
            <span class="label">max_execution_time:</span>
            <span class="value"><?php echo ini_get('max_execution_time'); ?> segundos</span>
        </div>
        
        <div class="setting">
            <span class="label">max_input_time:</span>
            <span class="value"><?php echo ini_get('max_input_time'); ?> segundos</span>
        </div>
        
        <div class="setting">
            <span class="label">memory_limit:</span>
            <span class="value"><?php echo ini_get('memory_limit'); ?></span>
        </div>
        
        <div class="setting">
            <span class="label">max_file_uploads:</span>
            <span class="value"><?php echo ini_get('max_file_uploads'); ?> archivos</span>
        </div>

        <?php
        // Convertir a bytes para comparar
        function convertToBytes($value) {
            $value = trim($value);
            $last = strtolower($value[strlen($value)-1]);
            $value = (int)$value;
            switch($last) {
                case 'g': $value *= 1024;
                case 'm': $value *= 1024;
                case 'k': $value *= 1024;
            }
            return $value;
        }

        $upload_max = convertToBytes(ini_get('upload_max_filesize'));
        $post_max = convertToBytes(ini_get('post_max_size'));
        $max_files = (int)ini_get('max_file_uploads');
        
        // 6 imágenes de 2MB cada una = 12MB
        $required_for_6_images = 6 * 2 * 1024 * 1024;
        
        $all_ok = true;
        ?>
        
        <?php if ($upload_max < 2 * 1024 * 1024): ?>
            <div class="error">
                <strong>⚠️ Problema:</strong> upload_max_filesize es menor a 2M. 
                Las imágenes de 2MB fallarán.
            </div>
            <?php $all_ok = false; ?>
        <?php endif; ?>
        
        <?php if ($post_max < $required_for_6_images): ?>
            <div class="warning">
                <strong>⚠️ Advertencia:</strong> post_max_size es menor a 12M. 
                Subir 6 imágenes de 2MB cada una puede fallar.
                <br>Actual: <?php echo ini_get('post_max_size'); ?> | Recomendado: 16M o más
            </div>
            <?php $all_ok = false; ?>
        <?php endif; ?>
        
        <?php if ($max_files < 6): ?>
            <div class="error">
                <strong>⚠️ Problema:</strong> max_file_uploads es <?php echo $max_files; ?>. 
                No puedes subir 6 imágenes a la vez.
            </div>
            <?php $all_ok = false; ?>
        <?php endif; ?>
        
        <?php if ($all_ok): ?>
            <div class="success">
                <strong>✅ Todo bien!</strong> Tu configuración PHP permite subir hasta 6 imágenes de 2MB cada una.
            </div>
        <?php endif; ?>
    </div>
    
    <div class="card">
        <h1>🔧 Cómo Aumentar los Límites</h1>
        <p>Si necesitas aumentar estos límites, edita tu archivo <code>php.ini</code>:</p>
        <pre style="background: #f5f5f5; padding: 15px; border-radius: 4px;">
upload_max_filesize = 3M
post_max_size = 20M
max_file_uploads = 10
max_execution_time = 300
memory_limit = 256M
        </pre>
        <p><strong>Ubicación de php.ini:</strong> <?php echo php_ini_loaded_file(); ?></p>
        <p><em>Después de editar, reinicia tu servidor web.</em></p>
    </div>
    
    <div class="card">
        <h1>📍 Información del Sistema</h1>
        <div class="setting">
            <span class="label">PHP Version:</span>
            <span class="value"><?php echo phpversion(); ?></span>
        </div>
        <div class="setting">
            <span class="label">Server Software:</span>
            <span class="value"><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></span>
        </div>
        <div class="setting">
            <span class="label">Document Root:</span>
            <span class="value"><?php echo $_SERVER['DOCUMENT_ROOT']; ?></span>
        </div>
    </div>
    
    <p style="text-align: center; color: #999; margin-top: 30px;">
        <small>⚠️ Elimina este archivo (check-php-limits.php) después de verificar</small>
    </p>
</body>
</html>
