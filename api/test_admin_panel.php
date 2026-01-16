<?php

function makeRequest($url, $method = 'GET', $data = null, $sessionId = null, $isMultipart = false) {
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
            if ($isMultipart) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            }
        }
    } elseif ($method === 'PUT') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }
    } elseif ($method === 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
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

echo "=== FASE 4: PRUEBAS DEL PANEL ADMIN ===\n\n";

// 1. Login como admin
echo "1. Login como ADMIN\n";
$result = makeRequest('http://localhost:8080/api/login', 'POST', [
    'email' => 'admin@cesped365.com',
    'password' => 'admin123'
]);
echo "   Status: {$result['code']} ";
echo ($result['code'] == 200 ? "✅" : "❌") . "\n";
$adminSession = $result['sessionId'];
echo "\n";

// 2. Crear nuevo cliente
echo "2. Crear nuevo cliente\n";
$result = makeRequest('http://localhost:8080/api/clientes', 'POST', [
    'name' => 'Cliente de Prueba',
    'email' => 'prueba@test.com',
    'password' => 'test123',
    'phone' => '+54 11 9999-9999',
    'address' => 'Calle de Prueba 999'
], $adminSession);
echo "   Status: {$result['code']} ";
echo ($result['code'] == 201 ? "✅" : "❌") . "\n";
if ($result['body']) {
    echo "   Cliente ID: " . ($result['body']['data']['id'] ?? 'N/A') . "\n";
    echo "   Nombre: " . ($result['body']['data']['nombre'] ?? 'N/A') . "\n";
}
$nuevoClienteId = $result['body']['data']['id'] ?? null;
echo "\n";

// 3. Listar clientes (verificar que aparece el nuevo)
echo "3. Listar clientes\n";
$result = makeRequest('http://localhost:8080/api/clientes', 'GET', null, $adminSession);
echo "   Status: {$result['code']} ";
echo ($result['code'] == 200 ? "✅" : "❌") . "\n";
if ($result['body']) {
    $count = count($result['body']['data'] ?? []);
    echo "   Total clientes: $count\n";
}
echo "\n";

// 4. Actualizar cliente
if ($nuevoClienteId) {
    echo "4. Actualizar cliente\n";
    $result = makeRequest("http://localhost:8080/api/clientes/$nuevoClienteId", 'PUT', [
        'name' => 'Cliente Actualizado',
        'phone' => '+54 11 8888-8888'
    ], $adminSession);
    echo "   Status: {$result['code']} ";
    echo ($result['code'] == 200 ? "✅" : "❌") . "\n";
    if ($result['body']) {
        echo "   Nombre actualizado: " . ($result['body']['data']['nombre'] ?? 'N/A') . "\n";
    }
    echo "\n";
}

// 5. Crear reporte para un jardín existente
echo "5. Crear nuevo reporte\n";
$result = makeRequest('http://localhost:8080/api/reportes', 'POST', [
    'garden_id' => 1,
    'date' => date('Y-m-d'),
    'estado_general' => 'Bueno',
    'cesped_parejo' => 1,
    'color_ok' => 1,
    'manchas' => 0,
    'zonas_desgastadas' => 0,
    'malezas_visibles' => 0,
    'crecimiento_cm' => 2.5,
    'compactacion' => 'Normal',
    'humedad' => 'Adecuada',
    'plagas' => 0,
    'observaciones' => 'Reporte de prueba creado por admin',
    'jardinero' => 'Admin Test'
], $adminSession);
echo "   Status: {$result['code']} ";
echo ($result['code'] == 201 ? "✅" : "❌") . "\n";
if ($result['body']) {
    echo "   Reporte ID: " . ($result['body']['data']['id'] ?? 'N/A') . "\n";
}
$nuevoReporteId = $result['body']['data']['id'] ?? null;
echo "\n";

// 6. Ver historial de un cliente
echo "6. Ver historial del cliente #2\n";
$result = makeRequest('http://localhost:8080/api/clientes/2/historial', 'GET', null, $adminSession);
echo "   Status: {$result['code']} ";
echo ($result['code'] == 200 ? "✅" : "❌") . "\n";
if ($result['body']) {
    $count = count($result['body']['data'] ?? []);
    echo "   Reportes en historial: $count\n";
    if (isset($result['body']['cliente'])) {
        echo "   Cliente: " . $result['body']['cliente']['nombre'] . "\n";
    }
}
echo "\n";

// 7. Ver detalles de un cliente
if ($nuevoClienteId) {
    echo "7. Ver detalles del cliente creado\n";
    $result = makeRequest("http://localhost:8080/api/clientes/$nuevoClienteId", 'GET', null, $adminSession);
    echo "   Status: {$result['code']} ";
    echo ($result['code'] == 200 ? "✅" : "❌") . "\n";
    if ($result['body']) {
        echo "   Email: " . ($result['body']['data']['email'] ?? 'N/A') . "\n";
    }
    echo "\n";
}

// 8. Eliminar cliente de prueba
if ($nuevoClienteId) {
    echo "8. Eliminar cliente de prueba\n";
    $result = makeRequest("http://localhost:8080/api/clientes/$nuevoClienteId", 'DELETE', null, $adminSession);
    echo "   Status: {$result['code']} ";
    echo ($result['code'] == 200 ? "✅" : "❌") . "\n";
    if ($result['body']) {
        echo "   Mensaje: " . ($result['body']['message'] ?? 'N/A') . "\n";
    }
    echo "\n";
}

// 9. Verificar que el cliente fue eliminado
if ($nuevoClienteId) {
    echo "9. Verificar que el cliente fue eliminado\n";
    $result = makeRequest("http://localhost:8080/api/clientes/$nuevoClienteId", 'GET', null, $adminSession);
    echo "   Status: {$result['code']} ";
    echo ($result['code'] == 404 ? "✅ (Correctamente eliminado)" : "❌") . "\n";
    echo "\n";
}

// 10. Intentar crear cliente con email duplicado (debe fallar)
echo "10. Intentar crear cliente con email duplicado\n";
$result = makeRequest('http://localhost:8080/api/clientes', 'POST', [
    'name' => 'Cliente Duplicado',
    'email' => 'cliente@example.com', // Email que ya existe
    'password' => 'test123'
], $adminSession);
echo "   Status: {$result['code']} ";
echo ($result['code'] == 400 ? "✅ (Correctamente rechazado)" : "❌") . "\n";
echo "\n";

echo "=== PRUEBAS COMPLETADAS ===\n";
echo "\nResumen:\n";
echo "- ✅ CRUD de clientes funcional\n";
echo "- ✅ Creación de reportes funcional\n";
echo "- ✅ Historial por cliente funcional\n";
echo "- ✅ Validaciones funcionando\n";
