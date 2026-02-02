<?php
/**
 * Obtiene datos de un usuario de Mercado Pago por ID.
 * Uso: php get_mp_user.php <user_id>
 */

$id = $argv[1] ?? null;
if (!$id) die("Uso: php get_mp_user.php <user_id>\n");

// Cargar .env simple
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            [$key, $value] = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

$token = $_ENV['MERCADOPAGO_ACCESS_TOKEN'] ?? null;
if (!$token) die("Falta MERCADOPAGO_ACCESS_TOKEN\n");

$url = 'https://api.mercadopago.com/users/' . urlencode($id);
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $token,
    ],
    CURLOPT_TIMEOUT => 30,
]);
$resp = curl_exec($ch);
$err = curl_error($ch);
$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($resp === false) {
    die("CURL error: {$err}\n");
}

echo "HTTP: {$http}\n";
echo $resp . "\n";

