<?php
/**
 * Script para actualizar el precio de un plan.
 * Uso:
 *   php update_plan_price.php "Urbano" 15
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

// Args
$planName = $argv[1] ?? 'Urbano';
$newPrice = $argv[2] ?? null;

if ($newPrice === null || !is_numeric($newPrice)) {
    die("Uso: php update_plan_price.php \"{$planName}\" <precio>\nEj: php update_plan_price.php \"Urbano\" 15\n");
}

$newPrice = (float) $newPrice;

// Actualizar precio del plan
$stmt = $db->prepare("UPDATE subscriptions SET price = ? WHERE name = ?");
$stmt->bind_param("ds", $newPrice, $planName);
if ($stmt->execute()) {
    $affected = $stmt->affected_rows;
    echo "✓ Precio del plan '{$planName}' actualizado a \$" . number_format($newPrice, 2) . "\n";
    echo "Filas afectadas: {$affected}\n";
    
    // Verificar el cambio
    $result = $db->query("SELECT name, price FROM subscriptions WHERE name = '" . $db->real_escape_string($planName) . "'");
    if ($row = $result->fetch_assoc()) {
        echo "\nVerificación:\n";
        echo "Plan: {$row['name']}\n";
        echo "Precio: \$" . number_format($row['price'], 2) . "\n";
    }
} else {
    echo "✗ Error al actualizar: " . $stmt->error . "\n";
}

$stmt->close();
$db->close();

echo "\n¡Listo!\n";
