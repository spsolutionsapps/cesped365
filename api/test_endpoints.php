<?php

function testEndpoint($url, $method = 'GET', $data = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'code' => $httpCode,
        'response' => json_decode($response, true)
    ];
}

echo "=== PROBANDO ENDPOINTS ===\n\n";

// 1. Test Dashboard
echo "1. GET /api/dashboard\n";
$result = testEndpoint('http://localhost:8080/api/dashboard');
echo "   Status: {$result['code']}\n";
if ($result['response']) {
    echo "   Total clientes: " . ($result['response']['data']['estadisticas']['totalClientes'] ?? 'N/A') . "\n";
    echo "   Total reportes: " . ($result['response']['data']['totalReportes'] ?? 'N/A') . "\n";
}
echo "\n";

// 2. Test Login
echo "2. POST /api/login\n";
$result = testEndpoint('http://localhost:8080/api/login', 'POST', [
    'email' => 'cliente@example.com',
    'password' => 'cliente123'
]);
echo "   Status: {$result['code']}\n";
if ($result['response'] && isset($result['response']['success'])) {
    echo "   Success: " . ($result['response']['success'] ? 'YES' : 'NO') . "\n";
    echo "   User: " . ($result['response']['user']['name'] ?? 'N/A') . "\n";
    $token = $result['response']['token'] ?? null;
}
echo "\n";

// 3. Test Reportes
echo "3. GET /api/reportes\n";
$result = testEndpoint('http://localhost:8080/api/reportes');
echo "   Status: {$result['code']}\n";
if ($result['response']) {
    $count = count($result['response']['data'] ?? []);
    echo "   Reportes encontrados: $count\n";
    if ($count > 0) {
        echo "   Primer reporte: {$result['response']['data'][0]['fecha']} - {$result['response']['data'][0]['estadoGeneral']}\n";
    }
}
echo "\n";

// 4. Test Historial
echo "4. GET /api/historial\n";
$result = testEndpoint('http://localhost:8080/api/historial');
echo "   Status: {$result['code']}\n";
if ($result['response']) {
    $count = count($result['response']['data'] ?? []);
    echo "   Visitas en historial: $count\n";
}
echo "\n";

// 5. Test Clientes
echo "5. GET /api/clientes\n";
$result = testEndpoint('http://localhost:8080/api/clientes');
echo "   Status: {$result['code']}\n";
if ($result['response']) {
    $count = count($result['response']['data'] ?? []);
    echo "   Clientes encontrados: $count\n";
    if ($count > 0) {
        echo "   Primer cliente: {$result['response']['data'][0]['nombre']} - {$result['response']['data'][0]['email']}\n";
    }
}
echo "\n";

echo "=== PRUEBAS COMPLETADAS ===\n";
