<?php
/**
 * Prueba rápida: crear Preapproval y mostrar init_point.
 * Ejecutar: php test_preapproval.php
 */

require __DIR__ . '/vendor/autoload.php';

use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\PreApproval\PreApprovalClient;
use MercadoPago\Exceptions\MPApiException;

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
$publicBase = $_ENV['MERCADOPAGO_PUBLIC_BASE_URL'] ?? null;
$payerEmail = $argv[1] ?? ($_ENV['MERCADOPAGO_SANDBOX_PAYER_EMAIL'] ?? 'test_user@testuser.com');
if (!$token) die("Falta MERCADOPAGO_ACCESS_TOKEN\n");
if (!$publicBase) die("Falta MERCADOPAGO_PUBLIC_BASE_URL\n");

MercadoPagoConfig::setAccessToken($token);
MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::SERVER);

$client = new PreApprovalClient();
$backUrl = rtrim($publicBase, '/') . '/api/payment/preapproval-return';
$startDate = gmdate('Y-m-d\TH:i:s.000\Z', time() + 300);

$req = [
    "reason" => "Test Suscripción",
    "external_reference" => "test|3|" . time(),
    "payer_email" => $payerEmail,
    "back_url" => $backUrl,
    "auto_recurring" => [
        "frequency" => 1,
        "frequency_type" => "months",
        "transaction_amount" => 15,
        "currency_id" => "ARS",
        "start_date" => $startDate,
    ],
];

echo "BACK_URL: {$backUrl}\n";
echo "START_DATE: {$startDate}\n";
echo "PAYER_EMAIL: {$payerEmail}\n";

try {
    $p = $client->create($req);
    echo "ID: " . ($p->id ?? 'N/A') . "\n";
    echo "INIT_POINT: " . ($p->init_point ?? 'N/A') . "\n";
} catch (MPApiException $e) {
    $r = $e->getApiResponse();
    echo "MPApiException\n";
    echo "Status: " . ($r ? $r->getStatusCode() : 'N/A') . "\n";
    echo "Content: " . json_encode($r ? $r->getContent() : null) . "\n";
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}

