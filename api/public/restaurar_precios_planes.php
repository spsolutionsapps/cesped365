<?php
/**
 * Restaura los precios originales de los planes.
 * Ejecutar: .../restaurar_precios_planes.php?ejecutar=1
 * BORRAR después de usar.
 *
 * Precios:
 *   Urbano: $45.000 /mes
 *   Residencial: $90.000 /mes
 *   Parque / Quintas: $120.000 /mes
 */
if (!isset($_GET['ejecutar']) || $_GET['ejecutar'] !== '1') {
    header('Content-Type: text/plain; charset=utf-8');
    die("Agregar ?ejecutar=1 a la URL para ejecutar.\nEj: .../restaurar_precios_planes.php?ejecutar=1\n\nBORRAR este archivo después.");
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

// Precios originales
$planes = [
    ['Urbano', 45000],
    ['Residencial', 90000],
    ['Parque', 120000],  // En BD puede ser "Parque" o "Parque / Quintas"
];

$stmtUpd = $pdo->prepare("UPDATE subscriptions SET price = ?, updated_at = ? WHERE name = ?");

foreach ($planes as $p) {
    $stmtUpd->execute([$p[1], $now, $p[0]]);
    $n = $stmtUpd->rowCount();
    $nombre = ($p[0] === 'Parque') ? 'Parque / Quintas' : $p[0];
    echo ($n > 0 ? "OK" : "—") . " - {$nombre}: \$" . number_format($p[1], 0, ',', '.') . " /mes\n";
}

echo "\nListo. Precios restaurados a los valores originales.\n";
echo "BORRAR restaurar_precios_planes.php después de usar.\n";
