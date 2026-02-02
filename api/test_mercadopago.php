<?php
/**
 * Script de prueba para verificar la conexión con Mercado Pago
 */

require __DIR__ . '/vendor/autoload.php';

use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;

// Cargar variables de entorno
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

$accessToken = $_ENV['MERCADOPAGO_ACCESS_TOKEN'] ?? null;

if (!$accessToken) {
    die("Error: MERCADOPAGO_ACCESS_TOKEN no está configurado\n");
}

echo "Access Token: " . substr($accessToken, 0, 15) . "...\n\n";

// Configurar SDK
MercadoPagoConfig::setAccessToken($accessToken);
MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::SERVER);

try {
    $client = new PreferenceClient();
    
    $request = [
        "items" => [
            [
                "title" => "Test - Suscripción Urbano",
                "quantity" => 1,
                "unit_price" => 22000.00
            ]
        ],
        "back_urls" => [
            "success" => "http://localhost:5173/dashboard/pagos/exito",
            "failure" => "http://localhost:5173/dashboard/pagos/fallo",
            "pending" => "http://localhost:5173/dashboard/pagos/pendiente"
        ],
        "external_reference" => "test|1|" . time()
    ];
    
    echo "Creando preferencia de prueba...\n";
    echo "Request: " . json_encode($request, JSON_PRETTY_PRINT) . "\n\n";
    
    $preference = $client->create($request);
    
    echo "✓ Preferencia creada exitosamente!\n";
    echo "Preference ID: " . $preference->id . "\n";
    echo "Init Point: " . $preference->init_point . "\n";
    
} catch (MPApiException $e) {
    echo "✗ Error de API de Mercado Pago:\n";
    echo "Status Code: " . $e->getApiResponse()->getStatusCode() . "\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "Content: " . json_encode($e->getApiResponse()->getContent(), JSON_PRETTY_PRINT) . "\n";
} catch (\Exception $e) {
    echo "✗ Error general:\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
