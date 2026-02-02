<?php
/**
 * Muestra el usuario asociado al Access Token configurado.
 * Ejecutar: php whoami_mp.php
 */

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

$ch = curl_init('https://api.mercadopago.com/users/me');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $token],
    CURLOPT_TIMEOUT => 30,
]);
$resp = curl_exec($ch);
$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err = curl_error($ch);
curl_close($ch);

echo "HTTP: {$http}\n";
if ($resp === false) {
    echo "CURL error: {$err}\n";
    exit(1);
}
echo $resp . "\n";

