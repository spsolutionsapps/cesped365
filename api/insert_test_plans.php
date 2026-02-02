<?php
/**
 * Script para insertar planes de prueba en la base de datos
 * Ejecutar: php insert_test_plans.php
 */

// Cargar variables de entorno
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Configuración de base de datos
$dbHost = $_ENV['database.default.hostname'] ?? 'localhost';
$dbName = $_ENV['database.default.database'] ?? 'cesped365';
$dbUser = $_ENV['database.default.username'] ?? 'root';
$dbPass = $_ENV['database.default.password'] ?? '';
$dbDriver = $_ENV['database.default.DBDriver'] ?? 'MySQLi';

// Conectar a la base de datos
try {
    $db = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    if ($db->connect_error) {
        die("Error de conexión: " . $db->connect_error . "\n");
    }
    echo "✓ Conectado a la base de datos: {$dbName}\n\n";
} catch (Exception $e) {
    die("Error: " . $e->getMessage() . "\n");
}

// Verificar si ya existen planes
$result = $db->query("SELECT COUNT(*) as count FROM subscriptions");
$row = $result->fetch_assoc();
$existingPlans = $row['count'];

if ($existingPlans > 0) {
    echo "Ya existen {$existingPlans} planes en la base de datos.\n";
    echo "Se insertarán solo los planes que no existan.\n\n";
}

$plans = [
    [
        'name' => 'Plan Básico',
        'description' => 'Plan ideal para jardines pequeños con mantenimiento básico',
        'price' => 15000.00,
        'frequency' => 'mensual',
        'visits_per_month' => 2,
        'features' => json_encode([
            'Corte de césped',
            'Riego básico',
            '2 visitas al mes',
            'Informe fotográfico'
        ]),
        'is_active' => 1,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
    ],
    [
        'name' => 'Plan Premium',
        'description' => 'Plan completo con mantenimiento integral del jardín',
        'price' => 28000.00,
        'frequency' => 'mensual',
        'visits_per_month' => 4,
        'features' => json_encode([
            'Corte de césped profesional',
            'Control de malezas',
            'Fertilización',
            'Control de plagas',
            'Riego automático',
            '4 visitas al mes',
            'Informe detallado con fotos',
            'Soporte prioritario'
        ]),
        'is_active' => 1,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
    ],
    [
        'name' => 'Urbano',
        'description' => 'Plan ideal para espacios urbanos con mantenimiento regular',
        'price' => 22000.00,
        'frequency' => 'mensual',
        'visits_per_month' => 3,
        'features' => json_encode([
            'Corte de césped profesional',
            'Control de malezas',
            'Fertilización',
            '3 visitas al mes',
            'Informe mensual con fotos',
            'Soporte por WhatsApp'
        ]),
        'is_active' => 1,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
    ],
    [
        'name' => 'Plan Trimestral',
        'description' => 'Plan económico con pago trimestral anticipado',
        'price' => 75000.00,
        'frequency' => 'trimestral',
        'visits_per_month' => 4,
        'features' => json_encode([
            'Corte de césped',
            'Control de malezas',
            'Fertilización',
            '4 visitas al mes',
            'Informe mensual',
            '10% descuento vs mensual'
        ]),
        'is_active' => 1,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
    ],
    [
        'name' => 'Plan Anual',
        'description' => 'Mejor precio del año con servicio garantizado',
        'price' => 280000.00,
        'frequency' => 'anual',
        'visits_per_month' => 4,
        'features' => json_encode([
            'Corte de césped profesional',
            'Control de malezas',
            'Fertilización premium',
            'Control de plagas',
            'Riego automático',
            'Resembrado anual',
            '4 visitas al mes',
            'Informe mensual detallado',
            'Soporte 24/7',
            '20% descuento vs mensual'
        ]),
        'is_active' => 1,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
    ],
];

// Insertar planes
$inserted = 0;
foreach ($plans as $plan) {
    // Verificar si el plan ya existe
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM subscriptions WHERE name = ?");
    $stmt->bind_param("s", $plan['name']);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    if ($row['count'] > 0) {
        echo "Plan '{$plan['name']}' ya existe, omitiendo...\n";
        continue;
    }
    
    // Insertar plan
    $stmt = $db->prepare("INSERT INTO subscriptions (name, description, price, frequency, visits_per_month, features, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdsisiss", 
        $plan['name'],
        $plan['description'],
        $plan['price'],
        $plan['frequency'],
        $plan['visits_per_month'],
        $plan['features'],
        $plan['is_active'],
        $plan['created_at'],
        $plan['updated_at']
    );
    
    if ($stmt->execute()) {
        $inserted++;
        echo "✓ Plan '{$plan['name']}' insertado correctamente\n";
    } else {
        echo "✗ Error al insertar plan '{$plan['name']}': " . $stmt->error . "\n";
    }
    $stmt->close();
}

$db->close();

echo "\nTotal de planes insertados: {$inserted}\n";
echo "¡Listo! Los planes están disponibles en la base de datos.\n";
