<?php
/**
 * Crea un usuario de prueba en Mercado Pago vía API.
 *
 * Uso:
 *   php create_mp_test_user.php "Comprador SebaTest"
 *
 * Requiere en api/.env:
 *   MERCADOPAGO_ACCESS_TOKEN=TEST-...
 */

$desc = $argv[1] ?? 'Comprador de prueba';

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
if (!$token) {
    fwrite(STDERR, "Falta MERCADOPAGO_ACCESS_TOKEN en api/.env\n");
    exit(1);
}

$payload = json_encode([
    'site_id' => 'MLA',
    'description' => $desc,
], JSON_UNESCAPED_SLASHES);

$ch = curl_init('https://api.mercadopago.com/users/test');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token,
    ],
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_TIMEOUT => 30,
]);

$resp = curl_exec($ch);
$err = curl_error($ch);
$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($resp === false) {
    fwrite(STDERR, "Error CURL: {$err}\n");
    exit(1);
}

$data = json_decode($resp, true);

echo "HTTP: {$http}\n";
echo $resp . "\n";

if ($http >= 400) {
    exit(1);
}

if (!is_array($data) || empty($data['id'])) {
    fwrite(STDERR, "Respuesta inesperada: no vino 'id'.\n");
    exit(1);
}

$id = (string) $data['id'];
$nickname = (string) ($data['nickname'] ?? '');

// Mercado Pago test users suelen venir como nickname "TESTUSER<digits>"
// y el email suele ser "test_user_<digits>@testuser.com" (ver users/me).
$digitsFromNickname = null;
if ($nickname && preg_match('/^TESTUSER(\d+)$/i', $nickname, $m)) {
    $digitsFromNickname = $m[1];
}

$guessedEmailByNicknameDigits = $digitsFromNickname ? ("test_user_{$digitsFromNickname}@testuser.com") : null;
$guessedEmailById = "test_user_{$id}@testuser.com";
$guessedEmailByNickname = $nickname ? ($nickname . "@testuser.com") : null;

$recommendedEmail = $guessedEmailByNicknameDigits ?: $guessedEmailById;

echo "\n=== COPIAR ESTO (1) ===\n";
echo "MERCADOPAGO_SANDBOX_PAYER_EMAIL={$recommendedEmail}\n";
echo "MERCADOPAGO_SANDBOX_PAYER_USER_ID={$id}\n";
if ($nickname) {
    echo "MERCADOPAGO_SANDBOX_PAYER_USERNAME={$nickname}\n";
}
echo "\nDatos del test user:\n";
echo "Nickname: " . ($data['nickname'] ?? '') . "\n";
echo "Password: " . ($data['password'] ?? '') . "\n";
echo "User ID: " . ($data['id'] ?? '') . "\n";
if ($guessedEmailByNickname) {
    echo "\n(Alternativo) Email por nickname: {$guessedEmailByNickname}\n";
}
if ($guessedEmailByNicknameDigits) {
    echo "(Recomendado) Email por digits del nickname: {$guessedEmailByNicknameDigits}\n";
}

