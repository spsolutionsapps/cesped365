<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🧪 TEST: Crear Reporte</h1>";
echo "<style>
body { font-family: monospace; background: #1e1e1e; color: #d4d4d4; padding: 20px; }
h1 { color: #4ec9b0; }
.success { background: #1d5a1d; color: #7ff47f; padding: 10px; margin: 10px 0; border-left: 4px solid #7ff47f; }
.error { background: #5a1d1d; color: #f48771; padding: 10px; margin: 10px 0; border-left: 4px solid #f48771; }
pre { white-space: pre-wrap; word-wrap: break-word; }
</style>";

// Cargar CodeIgniter
define('FCPATH', __DIR__ . '/api/public/');
chdir(FCPATH);

require FCPATH . '../app/Config/Paths.php';
$paths = new \Config\Paths();

require FCPATH . '../vendor/autoload.php';
require $paths->systemDirectory . '/Boot.php';

// Boot CodeIgniter
$app = \CodeIgniter\Boot::bootWeb($paths);

// Obtener database
$db = \Config\Database::connect();

echo "<h2>1. Verificar estructura de tabla 'reports'</h2>";
$query = $db->query("DESCRIBE reports");
$columns = $query->getResultArray();

echo "<div class='success'><strong>Columnas de la tabla 'reports':</strong><br>";
foreach ($columns as $col) {
    echo "- {$col['Field']} ({$col['Type']}) " . ($col['Null'] === 'YES' ? 'NULL' : 'NOT NULL') . "<br>";
}
echo "</div>";

echo "<h2>2. Verificar jardín con ID 1</h2>";
$garden = $db->table('gardens')->where('id', 1)->get()->getRowArray();

if ($garden) {
    echo "<div class='success'>";
    echo "<strong>✅ Jardín encontrado:</strong><br>";
    echo "ID: {$garden['id']}<br>";
    echo "User ID: {$garden['user_id']}<br>";
    echo "Address: {$garden['address']}<br>";
    echo "</div>";
} else {
    echo "<div class='error'>❌ No se encontró jardín con ID 1</div>";
    exit;
}

echo "<h2>3. Intentar insertar reporte de prueba</h2>";

$testData = [
    'garden_id' => 1,
    'user_id' => $garden['user_id'],
    'visit_date' => '2026-01-22',
    'status' => 'completado',
    'grass_health' => 'bueno',
    'watering_status' => 'optimo',
    'pest_detected' => 0,
    'fertilizer_applied' => 0,
    'work_done' => 'Test desde script',
    'recommendations' => '',
    'technician_notes' => '',
    'grass_height_cm' => null,
    'growth_cm' => null,
    'pest_description' => null,
    'fertilizer_type' => null,
    'weather_conditions' => '',
    'next_visit' => null
];

echo "<div class='success'><strong>Datos a insertar:</strong><pre>" . print_r($testData, true) . "</pre></div>";

try {
    $result = $db->table('reports')->insert($testData);
    
    if ($result) {
        $insertId = $db->insertID();
        echo "<div class='success'>";
        echo "✅ <strong>INSERCIÓN EXITOSA!</strong><br>";
        echo "ID del reporte creado: $insertId<br>";
        echo "</div>";
        
        // Verificar el reporte insertado
        $report = $db->table('reports')->where('id', $insertId)->get()->getRowArray();
        echo "<div class='success'><strong>Reporte insertado:</strong><pre>" . print_r($report, true) . "</pre></div>";
        
        // Eliminar el reporte de prueba
        $db->table('reports')->where('id', $insertId)->delete();
        echo "<div class='success'>🗑️ Reporte de prueba eliminado</div>";
    } else {
        echo "<div class='error'>❌ La inserción falló pero no lanzó excepción</div>";
    }
} catch (\Exception $e) {
    echo "<div class='error'>";
    echo "❌ <strong>ERROR:</strong><br>";
    echo "Mensaje: " . $e->getMessage() . "<br>";
    echo "Archivo: " . $e->getFile() . "<br>";
    echo "Línea: " . $e->getLine() . "<br>";
    echo "<strong>Stack trace:</strong><pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}

echo "<h2>4. Probar con ReportModel</h2>";

try {
    $reportModel = new \App\Models\ReportModel();
    
    $modelData = [
        'garden_id' => 1,
        'user_id' => $garden['user_id'],
        'visit_date' => '2026-01-22',
        'status' => 'completado',
        'grass_health' => 'bueno',
        'watering_status' => 'optimo',
        'pest_detected' => false,
        'fertilizer_applied' => false,
        'work_done' => 'Test desde ReportModel',
        'recommendations' => '',
        'technician_notes' => ''
    ];
    
    echo "<div class='success'><strong>Datos para el modelo:</strong><pre>" . print_r($modelData, true) . "</pre></div>";
    
    $reportId = $reportModel->insert($modelData);
    
    if ($reportId) {
        echo "<div class='success'>";
        echo "✅ <strong>INSERCIÓN CON MODELO EXITOSA!</strong><br>";
        echo "ID del reporte: $reportId<br>";
        echo "</div>";
        
        // Eliminar
        $reportModel->delete($reportId);
        echo "<div class='success'>🗑️ Reporte de prueba eliminado</div>";
    } else {
        $errors = $reportModel->errors();
        echo "<div class='error'>";
        echo "❌ <strong>VALIDACIÓN FALLÓ:</strong><br>";
        echo "<pre>" . print_r($errors, true) . "</pre>";
        echo "</div>";
    }
} catch (\Exception $e) {
    echo "<div class='error'>";
    echo "❌ <strong>ERROR EN MODELO:</strong><br>";
    echo "Mensaje: " . $e->getMessage() . "<br>";
    echo "Archivo: " . $e->getFile() . "<br>";
    echo "Línea: " . $e->getLine() . "<br>";
    echo "<strong>Stack trace:</strong><pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}

echo "<hr>";
echo "<p><em>Test completado: " . date('Y-m-d H:i:s') . "</em></p>";
?>
