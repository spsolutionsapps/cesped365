<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔧 ARREGLAR TODO - Cesped365</h1>";
echo "<style>
body { font-family: monospace; background: #1e1e1e; color: #d4d4d4; padding: 20px; line-height: 1.6; }
h1, h2 { color: #4ec9b0; }
.success { background: #1d5a1d; color: #7ff47f; padding: 10px; margin: 10px 0; border-left: 4px solid #7ff47f; }
.error { background: #5a1d1d; color: #f48771; padding: 10px; margin: 10px 0; border-left: 4px solid #f48771; }
.warning { background: #5a4d1d; color: #f4d77f; padding: 10px; margin: 10px 0; border-left: 4px solid #f4d77f; }
.info { background: #1e3a5f; color: #9cdcfe; padding: 10px; margin: 10px 0; border-left: 4px solid #9cdcfe; }
pre { white-space: pre-wrap; word-wrap: break-word; background: #2d2d2d; padding: 10px; border-radius: 5px; }
button { background: #4ec9b0; color: #1e1e1e; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; margin: 10px 5px; }
button:hover { background: #6ee7c0; }
</style>";

// Conectar a la base de datos
$host = 'localhost';
$db = 'cespvcyi_cesped365_db';
$user = 'cespvcyi_cesped365_user';
$pass = 'Sebaspado123';

try {
    $mysqli = new mysqli($host, $user, $pass, $db);
    
    if ($mysqli->connect_error) {
        throw new Exception("Error de conexión: " . $mysqli->connect_error);
    }
    
    echo "<div class='success'>✅ Conectado a la base de datos</div>";
    
    // ============================================
    // PASO 1: Verificar estructura actual
    // ============================================
    echo "<h2>📋 PASO 1: Estructura Actual de 'reports'</h2>";
    
    $result = $mysqli->query("DESCRIBE reports");
    $columns = [];
    
    echo "<div class='info'><strong>Columnas actuales:</strong><br>";
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
        $nullable = $row['Null'] === 'YES' ? 'NULL' : 'NOT NULL';
        echo "- {$row['Field']} ({$row['Type']}) $nullable<br>";
    }
    echo "</div>";
    
    // ============================================
    // PASO 2: Detectar si tiene estructura vieja o nueva
    // ============================================
    echo "<h2>🔍 PASO 2: Análisis de Estructura</h2>";
    
    $tieneViejos = in_array('date', $columns) || in_array('estado_general', $columns) || in_array('jardinero', $columns);
    $tieneNuevos = in_array('visit_date', $columns) && in_array('grass_health', $columns) && in_array('technician_notes', $columns);
    
    if ($tieneNuevos && !$tieneViejos) {
        echo "<div class='success'>✅ <strong>Estructura CORRECTA (nueva)</strong><br>";
        echo "La tabla tiene las columnas correctas: visit_date, grass_health, technician_notes, etc.</div>";
    } elseif ($tieneViejos && !$tieneNuevos) {
        echo "<div class='error'>❌ <strong>Estructura DESACTUALIZADA (vieja)</strong><br>";
        echo "La tabla tiene columnas viejas: date, estado_general, jardinero<br>";
        echo "<strong>ACCIÓN REQUERIDA:</strong> Necesitas migrar la estructura</div>";
        
        echo "<div class='warning'>";
        echo "<h3>🔧 Solución: Ejecutar migración</h3>";
        echo "<p>Ejecuta este SQL en phpMyAdmin:</p>";
        echo "<pre>";
        echo "-- Renombrar columnas\n";
        echo "ALTER TABLE `reports` CHANGE `date` `visit_date` date NOT NULL;\n";
        echo "ALTER TABLE `reports` CHANGE `estado_general` `grass_health` enum('excelente','bueno','regular','malo') DEFAULT NULL;\n";
        echo "ALTER TABLE `reports` CHANGE `jardinero` `technician_notes` text DEFAULT NULL;\n";
        echo "ALTER TABLE `reports` CHANGE `observaciones` `recommendations` text DEFAULT NULL;\n";
        echo "ALTER TABLE `reports` CHANGE `crecimiento_cm` `growth_cm` decimal(5,2) DEFAULT NULL;\n\n";
        echo "-- Agregar columnas nuevas\n";
        echo "ALTER TABLE `reports` ADD COLUMN `user_id` int(11) UNSIGNED NOT NULL AFTER `garden_id`;\n";
        echo "ALTER TABLE `reports` ADD COLUMN `status` enum('completado','pendiente','cancelado') DEFAULT 'pendiente' AFTER `visit_date`;\n";
        echo "ALTER TABLE `reports` ADD COLUMN `grass_height_cm` decimal(5,2) DEFAULT NULL AFTER `status`;\n";
        echo "ALTER TABLE `reports` ADD COLUMN `watering_status` enum('optimo','requiere_ajuste','insuficiente') DEFAULT NULL AFTER `grass_health`;\n";
        echo "ALTER TABLE `reports` ADD COLUMN `pest_detected` tinyint(1) DEFAULT 0 AFTER `watering_status`;\n";
        echo "ALTER TABLE `reports` ADD COLUMN `pest_description` text DEFAULT NULL AFTER `pest_detected`;\n";
        echo "ALTER TABLE `reports` ADD COLUMN `work_done` text DEFAULT NULL AFTER `pest_description`;\n";
        echo "ALTER TABLE `reports` ADD COLUMN `next_visit` date DEFAULT NULL AFTER `recommendations`;\n";
        echo "ALTER TABLE `reports` ADD COLUMN `fertilizer_applied` tinyint(1) DEFAULT 0 AFTER `growth_cm`;\n";
        echo "ALTER TABLE `reports` ADD COLUMN `fertilizer_type` varchar(100) DEFAULT NULL AFTER `fertilizer_applied`;\n";
        echo "ALTER TABLE `reports` ADD COLUMN `weather_conditions` varchar(100) DEFAULT NULL AFTER `fertilizer_type`;\n\n";
        echo "-- Eliminar columnas viejas que no se usan\n";
        echo "ALTER TABLE `reports` DROP COLUMN IF EXISTS `cesped_parejo`;\n";
        echo "ALTER TABLE `reports` DROP COLUMN IF EXISTS `color_ok`;\n";
        echo "ALTER TABLE `reports` DROP COLUMN IF EXISTS `manchas`;\n";
        echo "ALTER TABLE `reports` DROP COLUMN IF EXISTS `zonas_desgastadas`;\n";
        echo "ALTER TABLE `reports` DROP COLUMN IF EXISTS `malezas_visibles`;\n";
        echo "ALTER TABLE `reports` DROP COLUMN IF EXISTS `compactacion`;\n";
        echo "ALTER TABLE `reports` DROP COLUMN IF EXISTS `humedad`;\n";
        echo "ALTER TABLE `reports` DROP COLUMN IF EXISTS `plagas`;\n";
        echo "ALTER TABLE `reports` DROP COLUMN IF EXISTS `altura_cm`;\n";
        echo "ALTER TABLE `reports` DROP COLUMN IF EXISTS `color`;\n";
        echo "ALTER TABLE `reports` DROP COLUMN IF EXISTS `densidad`;\n";
        echo "ALTER TABLE `reports` DROP COLUMN IF EXISTS `riego`;\n";
        echo "ALTER TABLE `reports` DROP COLUMN IF EXISTS `direccion`;\n\n";
        echo "-- Agregar foreign key para user_id\n";
        echo "ALTER TABLE `reports` ADD CONSTRAINT `reports_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;\n";
        echo "</pre>";
        echo "</div>";
    } else {
        echo "<div class='warning'>⚠️ <strong>Estructura MIXTA</strong><br>";
        echo "La tabla tiene una mezcla de columnas viejas y nuevas.<br>";
        echo "Columnas viejas detectadas: " . ($tieneViejos ? 'SÍ' : 'NO') . "<br>";
        echo "Columnas nuevas detectadas: " . ($tieneNuevos ? 'SÍ' : 'NO') . "</div>";
    }
    
    // ============================================
    // PASO 3: Verificar reportes existentes
    // ============================================
    echo "<h2>📊 PASO 3: Reportes Existentes</h2>";
    
    $result = $mysqli->query("SELECT COUNT(*) as total FROM reports");
    $row = $result->fetch_assoc();
    $totalReportes = $row['total'];
    
    echo "<div class='info'><strong>Total de reportes:</strong> $totalReportes</div>";
    
    if ($totalReportes > 0) {
        echo "<div class='info'><strong>Últimos 3 reportes:</strong><br>";
        $result = $mysqli->query("SELECT * FROM reports ORDER BY id DESC LIMIT 3");
        while ($row = $result->fetch_assoc()) {
            echo "<pre>" . print_r($row, true) . "</pre>";
        }
        echo "</div>";
    }
    
    // ============================================
    // PASO 4: Verificar logs directory
    // ============================================
    echo "<h2>📁 PASO 4: Verificar Logs</h2>";
    
    $logsDir = __DIR__ . '/api/writable/logs/';
    
    if (!is_dir($logsDir)) {
        echo "<div class='error'>❌ Directorio de logs NO existe: $logsDir</div>";
        echo "<div class='warning'>Intentando crear directorio...</div>";
        if (mkdir($logsDir, 0755, true)) {
            echo "<div class='success'>✅ Directorio creado</div>";
        } else {
            echo "<div class='error'>❌ No se pudo crear el directorio</div>";
        }
    } else {
        echo "<div class='success'>✅ Directorio de logs existe</div>";
        
        // Verificar permisos
        $perms = substr(sprintf('%o', fileperms($logsDir)), -4);
        echo "<div class='info'>Permisos: $perms</div>";
        
        // Listar archivos de log
        $logFiles = glob($logsDir . 'log-*.php');
        if (empty($logFiles)) {
            echo "<div class='warning'>⚠️ No hay archivos de log</div>";
            echo "<div class='info'>Esto puede significar que logger.threshold está muy alto o que writable/ no tiene permisos de escritura</div>";
        } else {
            echo "<div class='success'>✅ Archivos de log encontrados: " . count($logFiles) . "</div>";
            foreach ($logFiles as $file) {
                $size = filesize($file);
                $modified = date('Y-m-d H:i:s', filemtime($file));
                echo "- " . basename($file) . " ($size bytes, modificado: $modified)<br>";
            }
        }
    }
    
    // ============================================
    // PASO 5: Verificar permisos de writable/
    // ============================================
    echo "<h2>🔐 PASO 5: Permisos de writable/</h2>";
    
    $writableDir = __DIR__ . '/api/writable/';
    
    if (is_dir($writableDir)) {
        $perms = substr(sprintf('%o', fileperms($writableDir)), -4);
        $isWritable = is_writable($writableDir);
        
        echo "<div class='" . ($isWritable ? 'success' : 'error') . "'>";
        echo ($isWritable ? '✅' : '❌') . " writable/ - Permisos: $perms - " . ($isWritable ? 'ESCRIBIBLE' : 'NO ESCRIBIBLE') . "<br>";
        echo "</div>";
        
        // Verificar subdirectorios
        $subdirs = ['cache', 'logs', 'session', 'uploads'];
        foreach ($subdirs as $subdir) {
            $path = $writableDir . $subdir;
            if (is_dir($path)) {
                $perms = substr(sprintf('%o', fileperms($path)), -4);
                $isWritable = is_writable($path);
                echo "<div class='" . ($isWritable ? 'success' : 'error') . "'>";
                echo ($isWritable ? '✅' : '❌') . " writable/$subdir/ - Permisos: $perms - " . ($isWritable ? 'ESCRIBIBLE' : 'NO ESCRIBIBLE') . "<br>";
                echo "</div>";
            } else {
                echo "<div class='error'>❌ writable/$subdir/ NO EXISTE</div>";
            }
        }
    } else {
        echo "<div class='error'>❌ writable/ NO EXISTE</div>";
    }
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "❌ <strong>ERROR:</strong><br>";
    echo $e->getMessage();
    echo "</div>";
}

echo "<hr>";
echo "<p><em>Diagnóstico completado: " . date('Y-m-d H:i:s') . "</em></p>";
?>
