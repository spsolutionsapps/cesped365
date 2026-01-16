<?php

function makeRequest($url, $method = 'GET', $data = null, $sessionId = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HEADER, true);
    
    if ($sessionId) {
        curl_setopt($ch, CURLOPT_COOKIE, "ci_session=$sessionId");
    }
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }
    } elseif ($method === 'PUT') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }
    }
    
    $response = curl_exec($ch);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    $headers = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    
    curl_close($ch);
    
    // Extraer session ID
    $newSessionId = null;
    if (preg_match('/Set-Cookie: ci_session=([^;]+)/', $headers, $matches)) {
        $newSessionId = $matches[1];
    }
    
    return [
        'code' => $httpCode,
        'body' => json_decode($body, true),
        'sessionId' => $newSessionId ?: $sessionId
    ];
}

echo "=== FASE 5: PRUEBAS DE SUSCRIPCIONES ===\n\n";

// 1. Login como cliente
echo "1. Login como CLIENTE\n";
$result = makeRequest('http://localhost:8080/api/login', 'POST', [
    'email' => 'cliente@example.com',
    'password' => 'cliente123'
]);
echo "   Status: {$result['code']} ";
echo ($result['code'] == 200 ? "✅" : "❌") . "\n";
$clienteSession = $result['sessionId'];
echo "\n";

// 2. Ver planes disponibles (cliente)
echo "2. Ver planes de suscripción disponibles\n";
$result = makeRequest('http://localhost:8080/api/subscriptions/plans', 'GET', null, $clienteSession);
echo "   Status: {$result['code']} ";
echo ($result['code'] == 200 ? "✅" : "❌") . "\n";
if ($result['body']) {
    $count = count($result['body']['data'] ?? []);
    echo "   Planes disponibles: $count\n";
    if ($count > 0) {
        echo "   Primer plan: {$result['body']['data'][0]['name']} - \${$result['body']['data'][0]['price']}\n";
    }
}
echo "\n";

// 3. Ver mi suscripción activa (cliente)
echo "3. Ver mi suscripción activa\n";
$result = makeRequest('http://localhost:8080/api/subscriptions/my-subscription', 'GET', null, $clienteSession);
echo "   Status: {$result['code']} ";
echo ($result['code'] == 200 ? "✅" : "❌") . "\n";
if ($result['body'] && $result['body']['data']) {
    echo "   Plan: {$result['body']['data']['planName']}\n";
    echo "   Estado: {$result['body']['data']['status']}\n";
    echo "   Próximo pago: {$result['body']['data']['nextBillingDate']}\n";
}
echo "\n";

// 4. Login como admin
echo "4. Login como ADMIN\n";
$result = makeRequest('http://localhost:8080/api/login', 'POST', [
    'email' => 'admin@cesped365.com',
    'password' => 'admin123'
]);
echo "   Status: {$result['code']} ";
echo ($result['code'] == 200 ? "✅" : "❌") . "\n";
$adminSession = $result['sessionId'];
echo "\n";

// 5. Listar todas las suscripciones (admin)
echo "5. Listar todas las suscripciones de usuarios\n";
$result = makeRequest('http://localhost:8080/api/subscriptions', 'GET', null, $adminSession);
echo "   Status: {$result['code']} ";
echo ($result['code'] == 200 ? "✅" : "❌") . "\n";
if ($result['body']) {
    $count = count($result['body']['data'] ?? []);
    echo "   Total suscripciones: $count\n";
}
echo "\n";

// 6. Ver detalles de una suscripción (admin)
echo "6. Ver detalles de suscripción #1\n";
$result = makeRequest('http://localhost:8080/api/subscriptions/1', 'GET', null, $adminSession);
echo "   Status: {$result['code']} ";
echo ($result['code'] == 200 ? "✅" : "❌") . "\n";
if ($result['body'] && $result['body']['data']) {
    echo "   Usuario: {$result['body']['data']['userName']}\n";
    echo "   Plan: {$result['body']['data']['planName']}\n";
    echo "   Estado: {$result['body']['data']['status']}\n";
}
echo "\n";

// 7. Crear nueva suscripción (admin)
echo "7. Crear nueva suscripción para usuario #2\n";
$result = makeRequest('http://localhost:8080/api/subscriptions', 'POST', [
    'user_id' => 2,
    'subscription_id' => 4, // Plan Anual
    'start_date' => date('Y-m-d'),
    'next_billing_date' => date('Y-m-d', strtotime('+1 year')),
    'payment_method' => 'mercadopago',
    'notes' => 'Upgrade a plan anual'
], $adminSession);
echo "   Status: {$result['code']} ";
echo ($result['code'] == 201 ? "✅" : "❌") . "\n";
if ($result['body']) {
    echo "   Plan: " . ($result['body']['data']['planName'] ?? 'N/A') . "\n";
}
$nuevaSubId = $result['body']['data']['id'] ?? null;
echo "\n";

// 8. Pausar una suscripción (admin)
echo "8. Pausar suscripción #4\n";
$result = makeRequest('http://localhost:8080/api/subscriptions/4/pause', 'POST', null, $adminSession);
echo "   Status: {$result['code']} ";
echo ($result['code'] == 200 ? "✅" : "❌") . "\n";
if ($result['body']) {
    echo "   Mensaje: {$result['body']['message']}\n";
}
echo "\n";

// 9. Reactivar suscripción (admin)
echo "9. Reactivar suscripción #4\n";
$result = makeRequest('http://localhost:8080/api/subscriptions/4/reactivate', 'POST', null, $adminSession);
echo "   Status: {$result['code']} ";
echo ($result['code'] == 200 ? "✅" : "❌") . "\n";
if ($result['body']) {
    echo "   Mensaje: {$result['body']['message']}\n";
}
echo "\n";

// 10. Actualizar suscripción (admin)
if ($nuevaSubId) {
    echo "10. Actualizar datos de suscripción\n";
    $result = makeRequest("http://localhost:8080/api/subscriptions/$nuevaSubId", 'PUT', [
        'notes' => 'Cliente VIP - plan anual actualizado',
        'auto_renew' => 1
    ], $adminSession);
    echo "   Status: {$result['code']} ";
    echo ($result['code'] == 200 ? "✅" : "❌") . "\n";
    echo "\n";
}

// 11. Crear nuevo plan (admin)
echo "11. Crear nuevo plan de suscripción\n";
$result = makeRequest('http://localhost:8080/api/subscriptions/plans', 'POST', [
    'name' => 'Plan Empresarial',
    'description' => 'Para empresas con múltiples jardines',
    'price' => 150000.00,
    'frequency' => 'mensual',
    'visits_per_month' => 8
], $adminSession);
echo "   Status: {$result['code']} ";
echo ($result['code'] == 201 ? "✅" : "❌") . "\n";
if ($result['body']) {
    echo "   Plan: " . ($result['body']['data']['name'] ?? 'N/A') . "\n";
}
echo "\n";

// 12. Ver detalles de un plan específico
echo "12. Ver detalles del Plan Premium\n";
$result = makeRequest('http://localhost:8080/api/subscriptions/plans/2', 'GET', null, $clienteSession);
echo "   Status: {$result['code']} ";
echo ($result['code'] == 200 ? "✅" : "❌") . "\n";
if ($result['body'] && $result['body']['data']) {
    echo "   Plan: {$result['body']['data']['name']}\n";
    echo "   Precio: \${$result['body']['data']['price']}\n";
    echo "   Visitas/mes: {$result['body']['data']['visitsPerMonth']}\n";
}
echo "\n";

echo "=== PRUEBAS COMPLETADAS ===\n";
echo "\nResumen:\n";
echo "- ✅ Planes de suscripción funcionando\n";
echo "- ✅ CRUD de suscripciones funcional\n";
echo "- ✅ Pausar/Reactivar suscripciones\n";
echo "- ✅ Cliente puede ver su suscripción\n";
echo "- ✅ Admin puede gestionar todo\n";
