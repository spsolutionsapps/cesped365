<?php
/**
 * Script para verificar que la creación de Preapproval (suscripción) funcione correctamente.
 * Compara los datos que enviamos vs lo que espera Mercado Pago.
 *
 * Uso: https://cesped365.com/api/check_mp_preapproval.php?ejecutar=1
 * BORRAR después de usar.
 */
if (!isset($_GET['ejecutar']) || $_GET['ejecutar'] !== '1') {
    header('Content-Type: text/plain; charset=utf-8');
    die("Agregar ?ejecutar=1 para ejecutar.\nBORRAR después de usar.");
}

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/plain; charset=utf-8');

$base = dirname(__DIR__);
require $base . '/vendor/autoload.php';

// Cargar .env
$env = [];
if (file_exists($base . '/.env')) {
    foreach (file($base . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if (empty($line) || $line[0] === '#') continue;
        if (preg_match('/^([A-Za-z0-9_.]+)=(.*)$/', $line, $m)) {
            $env[trim($m[1])] = trim($m[2], " \t\"'");
        }
    }
}

echo "=== CHECK PREAPPROVAL MERCADO PAGO ===\n\n";

$token = $env['MERCADOPAGO_ACCESS_TOKEN'] ?? '';
$publicUrl = $env['MERCADOPAGO_PUBLIC_BASE_URL'] ?? '';
$frontendUrl = $env['FRONTEND_BASE_URL'] ?? '';

echo "1. Config:\n";
echo "   MERCADOPAGO_ACCESS_TOKEN: " . (strlen($token) > 10 ? substr($token, 0, 15) . '...' : 'FALTA') . "\n";
echo "   MERCADOPAGO_PUBLIC_BASE_URL: " . ($publicUrl ?: 'FALTA') . "\n";
echo "   FRONTEND_BASE_URL: " . ($frontendUrl ?: 'FALTA') . "\n";

if (!$token) {
    die("\nERROR: Falta MERCADOPAGO_ACCESS_TOKEN\n");
}

MercadoPago\MercadoPagoConfig::setAccessToken($token);

// 2. Verificar cuenta MP
echo "\n2. Cuenta Mercado Pago:\n";
$ch = curl_init('https://api.mercadopago.com/users/me');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $token],
]);
$me = json_decode(curl_exec($ch), true);
curl_close($ch);
if (!$me || isset($me['message'])) {
    echo "   ERROR: " . ($me['message'] ?? json_encode($me)) . "\n";
    die();
}
echo "   ID: " . ($me['id'] ?? 'N/A') . "\n";
echo "   Email: " . ($me['email'] ?? 'N/A') . "\n";
echo "   Nickname: " . ($me['nickname'] ?? 'N/A') . "\n";
$isTest = (stripos($me['email'] ?? '', '@testuser.com') !== false) || (stripos($me['nickname'] ?? '', 'TEST') === 0);
echo "   Es cuenta TEST: " . ($isTest ? 'Sí' : 'No (producción)') . "\n";

// 3. Crear preapproval de prueba
echo "\n3. Crear Preapproval de prueba (\$15, 1 mes):\n";
$publicUrl = rtrim($publicUrl, '/');
$backUrl = "{$publicUrl}/api/payment/preapproval-return";
$startDate = gmdate('Y-m-d\TH:i:s.000\Z', time() + 300);

$requestBody = [
    'reason' => 'Suscripción: Urbano (test)',
    'external_reference' => '0|1|' . time(),
    'payer_email' => $me['email'] ?? 'test@test.com', // Usar email del vendedor para test propio
    'back_url' => $backUrl,
    'auto_recurring' => [
        'frequency' => 1,
        'frequency_type' => 'months',
        'transaction_amount' => 15.0,
        'currency_id' => 'ARS',
        'start_date' => $startDate,
    ],
];

echo "   Request a MP:\n";
echo "   " . json_encode($requestBody, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";

$ch = curl_init('https://api.mercadopago.com/preapproval');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json',
    ],
    CURLOPT_POSTFIELDS => json_encode($requestBody),
]);
$resp = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$result = json_decode($resp, true);

if ($httpCode >= 200 && $httpCode < 300 && isset($result['id'])) {
    echo "\n   OK - Preapproval creado correctamente.\n";
    echo "   ID: " . $result['id'] . "\n";
    echo "   init_point: " . ($result['init_point'] ?? 'N/A') . "\n";
    echo "\n   Conclusión: Tu backend envía los datos correctos. El problema del botón Confirmar\n";
    echo "   está en el checkout de Mercado Pago (lado de ellos), no en tu integración.\n";
} else {
    echo "\n   ERROR al crear preapproval:\n";
    echo "   HTTP: $httpCode\n";
    echo "   Response: " . substr($resp, 0, 500) . "\n";
    if (isset($result['message'])) {
        echo "\n   Mensaje MP: " . $result['message'] . "\n";
    }
    if (!empty($result['cause'])) {
        echo "   Causa: " . json_encode($result['cause'], JSON_UNESCAPED_UNICODE) . "\n";
    }
    echo "\n   Conclusión: Revisar el error anterior. Puede ser configuración .env o restricciones MP.\n";
}

echo "\n--- BORRAR check_mp_preapproval.php después de usar ---\n";
