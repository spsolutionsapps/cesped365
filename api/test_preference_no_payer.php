<?php
require __DIR__ . '/vendor/autoload.php';

use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;

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

MercadoPagoConfig::setAccessToken($_ENV['MERCADOPAGO_ACCESS_TOKEN']);
MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::SERVER);

$client = new PreferenceClient();

// Preferencia SIN especificar payer - Mercado Pago lo solicitará en el checkout
$request = [
    "items" => [
        [
            "title" => "Suscripción: Urbano",
            "quantity" => 1,
            "unit_price" => 1.00
        ]
    ],
    "back_urls" => [
        "success" => "http://localhost:5173/dashboard/pagos/exito",
        "failure" => "http://localhost:5173/dashboard/pagos/fallo",
        "pending" => "http://localhost:5173/dashboard/pagos/pendiente"
    ],
    "external_reference" => "test|1|" . time()
];

echo "Creando preferencia SIN especificar payer...\n";
$preference = $client->create($request);

echo "✓ Preferencia creada!\n";
echo "ID: " . $preference->id . "\n";
echo "Sandbox Init Point: " . ($preference->sandbox_init_point ?? $preference->init_point) . "\n";
