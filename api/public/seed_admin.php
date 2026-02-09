<?php
/**
 * Crea el usuario admin en producción (ejecutar UNA vez).
 * URL: https://tu-dominio.com/api/public/seed_admin.php?ejecutar=1
 * BORRAR este archivo después de usarlo.
 */
if (!isset($_GET['ejecutar']) || $_GET['ejecutar'] !== '1') {
    header('Content-Type: text/plain; charset=utf-8');
    die("Agregar ?ejecutar=1 a la URL para ejecutar.\nEj: .../seed_admin.php?ejecutar=1\n\nBORRAR este archivo después.");
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

$email = 'admin@cesped365.com';

// Verificar si ya existe
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    die("El usuario admin (admin@cesped365.com) ya existe. No se hizo nada.\n\nBORRAR este archivo por seguridad.");
}

$now = date('Y-m-d H:i:s');
$passwordHash = password_hash('admin123', PASSWORD_DEFAULT);

// Insertar admin con todas las columnas de la tabla users
$sql = "INSERT INTO users (name, email, password, role, phone, address, plan, estado, referido_por, lat, lng, created_at, updated_at)
        VALUES (:name, :email, :password, 'admin', NULL, NULL, 'Urbano', 'Pendiente', NULL, NULL, NULL, :created_at, :updated_at)";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    'name'       => 'Administrador',
    'email'      => $email,
    'password'   => $passwordHash,
    'created_at' => $now,
    'updated_at' => $now,
]);

echo "OK - Usuario admin creado.\n";
echo "  Email: admin@cesped365.com\n";
echo "  Contraseña: admin123\n";
echo "\nBORRAR este archivo (seed_admin.php) por seguridad.\n";
