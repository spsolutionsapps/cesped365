<?php
/**
 * Script para cargar planes en producción (ejecutar UNA vez).
 * Acceder: https://cesped365.com/api/seed_plans_produccion.php?ejecutar=1
 * BORRAR después de usar.
 */
if (!isset($_GET['ejecutar']) || $_GET['ejecutar'] !== '1') {
    header('Content-Type: text/plain; charset=utf-8');
    die("Agregar ?ejecutar=1 a la URL para ejecutar.\nEj: .../seed_plans_produccion.php?ejecutar=1\n\nBORRAR este archivo después.");
}

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/plain; charset=utf-8');

$base = dirname(__DIR__);
require $base . '/vendor/autoload.php';

// Cargar .env
$env = [];
if (file_exists($base . '/.env')) {
    $lines = file($base . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || $line[0] === '#') continue;
        if (preg_match('/^([A-Za-z0-9_.]+)=(.*)$/', $line, $m)) {
            $k = trim($m[1]);
            $env[$k] = trim($m[2], " \t\"'");
        }
    }
}

$host = $env['database.default.hostname'] ?? 'localhost';
$dbname = $env['database.default.database'] ?? 'cesped365';
$user = $env['database.default.username'] ?? 'root';
$pass = $env['database.default.password'] ?? '';

try {
    $pdo = new PDO(
        "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("Error conexión DB: " . $e->getMessage());
}

$now = date('Y-m-d H:i:s');
$plans = [
    ['Urbano', 'Hasta 500 m² de tu jardín', 45000, ['Corte, bordes y repaso', 'Monitoreo mensual con fotos', 'Recomendaciones estacionales']],
    ['Residencial', '500 a 2.500 m² de tu jardín', 90000, ['Corte, bordes y repaso', 'Monitoreo mensual con fotos', 'Recomendaciones estacionales']],
    ['Parque', '2.500 a 4.000 m² de tu jardín', 120000, ['Corte, bordes y repaso', 'Monitoreo mensual con fotos', 'Recomendaciones estacionales']],
];

// Desactivar todos
$pdo->exec("UPDATE subscriptions SET is_active = 0, updated_at = '{$now}'");

$stmtSel = $pdo->prepare("SELECT id FROM subscriptions WHERE name = ?");
$stmtIns = $pdo->prepare("INSERT INTO subscriptions (name, description, price, frequency, visits_per_month, features, is_active, created_at, updated_at) VALUES (?, ?, ?, 'mensual', 1, ?, 1, ?, ?)");
$stmtUpd = $pdo->prepare("UPDATE subscriptions SET description=?, price=?, features=?, is_active=1, updated_at=? WHERE id=?");

foreach ($plans as $p) {
    $features = json_encode($p[3], JSON_UNESCAPED_UNICODE);
    $stmtSel->execute([$p[0]]);
    $row = $stmtSel->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $stmtUpd->execute([$p[1], $p[2], $features, $now, $row['id']]);
    } else {
        $stmtIns->execute([$p[0], $p[1], $p[2], $features, $now, $now]);
    }
}

echo "OK - Planes cargados: Urbano, Residencial, Parque.\n";
echo "BORRAR seed_plans_produccion.php ahora.\n";
