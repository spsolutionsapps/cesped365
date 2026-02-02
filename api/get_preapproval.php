<?php
/**
 * Consultar un Preapproval por ID.
 * Uso: php get_preapproval.php <preapproval_id>
 */

require __DIR__ . '/vendor/autoload.php';

use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\PreApproval\PreApprovalClient;
use MercadoPago\Exceptions\MPApiException;

$id = $argv[1] ?? null;
if (!$id) {
    die("Uso: php get_preapproval.php <preapproval_id>\n");
}

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
if (!$token) die("Falta MERCADOPAGO_ACCESS_TOKEN en .env\n");

MercadoPagoConfig::setAccessToken($token);
MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::SERVER);

$client = new PreApprovalClient();

try {
    $p = $client->get($id);
    echo "ID: " . ($p->id ?? 'N/A') . PHP_EOL;
    echo "STATUS: " . ($p->status ?? 'N/A') . PHP_EOL;
    echo "PAYER_EMAIL: " . ($p->payer_email ?? 'N/A') . PHP_EOL;
    echo "BACK_URL: " . ($p->back_url ?? 'N/A') . PHP_EOL;
    echo "INIT_POINT: " . ($p->init_point ?? 'N/A') . PHP_EOL;
    echo "NEXT_PAYMENT_DATE: " . ($p->next_payment_date ?? 'N/A') . PHP_EOL;
    echo "EXTERNAL_REFERENCE: " . ($p->external_reference ?? 'N/A') . PHP_EOL;
    if (!empty($p->auto_recurring)) {
        echo "AUTO_RECURRING: " . json_encode($p->auto_recurring, JSON_UNESCAPED_SLASHES) . PHP_EOL;
    }
} catch (MPApiException $e) {
    $r = $e->getApiResponse();
    echo "MPApiException\n";
    echo "Status: " . ($r ? $r->getStatusCode() : 'N/A') . PHP_EOL;
    echo "Content: " . json_encode($r ? $r->getContent() : null) . PHP_EOL;
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . PHP_EOL;
}

