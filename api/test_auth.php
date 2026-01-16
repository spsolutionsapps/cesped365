<?php

function makeRequest($url, $method = 'GET', $data = null, $sessionId = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HEADER, true);
    
    // Para mantener las cookies/sesiones
    if ($sessionId) {
        curl_setopt($ch, CURLOPT_COOKIE, "ci_session=$sessionId");
    }
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
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
    
    // Extraer session ID de las cookies
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

echo "=== FASE 3: PRUEBAS DE AUTENTICACIÓN Y AUTORIZACIÓN ===\n\n";

// 1. Intentar acceder a dashboard sin autenticación
echo "1. Acceder a /api/dashboard SIN autenticación\n";
$result = makeRequest('http://localhost:8080/api/dashboard');
echo "   Status: {$result['code']} ";
echo ($result['code'] == 401 ? "✅ (Correctamente bloqueado)" : "❌ (Debería dar 401)") . "\n";
if ($result['body']) {
    echo "   Mensaje: {$result['body']['message']}\n";
}
echo "\n";

// 2. Login con cliente
echo "2. Login como CLIENTE\n";
$result = makeRequest('http://localhost:8080/api/login', 'POST', [
    'email' => 'cliente@example.com',
    'password' => 'cliente123'
]);
echo "   Status: {$result['code']} ";
echo ($result['code'] == 200 ? "✅" : "❌") . "\n";
if ($result['body']) {
    echo "   User: " . ($result['body']['user']['name'] ?? 'N/A') . "\n";
    echo "   Role: " . ($result['body']['user']['role'] ?? 'N/A') . "\n";
    echo "   Session ID: " . substr($result['sessionId'] ?? '', 0, 20) . "...\n";
}
$clienteSession = $result['sessionId'];
echo "\n";

// 3. Acceder a dashboard con sesión de cliente
echo "3. Acceder a /api/dashboard CON sesión de cliente\n";
$result = makeRequest('http://localhost:8080/api/dashboard', 'GET', null, $clienteSession);
echo "   Status: {$result['code']} ";
echo ($result['code'] == 200 ? "✅" : "❌") . "\n";
if ($result['body']) {
    echo "   Total clientes: " . ($result['body']['data']['estadisticas']['totalClientes'] ?? 'N/A') . "\n";
}
echo "\n";

// 4. Cliente intenta acceder a /api/clientes (solo admin)
echo "4. Cliente intenta acceder a /api/clientes (solo admin)\n";
$result = makeRequest('http://localhost:8080/api/clientes', 'GET', null, $clienteSession);
echo "   Status: {$result['code']} ";
echo ($result['code'] == 403 ? "✅ (Correctamente bloqueado)" : "❌ (Debería dar 403)") . "\n";
if ($result['body']) {
    echo "   Mensaje: {$result['body']['message']}\n";
}
echo "\n";

// 5. Login con admin
echo "5. Login como ADMIN\n";
$result = makeRequest('http://localhost:8080/api/login', 'POST', [
    'email' => 'admin@cesped365.com',
    'password' => 'admin123'
]);
echo "   Status: {$result['code']} ";
echo ($result['code'] == 200 ? "✅" : "❌") . "\n";
if ($result['body']) {
    echo "   User: " . ($result['body']['user']['name'] ?? 'N/A') . "\n";
    echo "   Role: " . ($result['body']['user']['role'] ?? 'N/A') . "\n";
}
$adminSession = $result['sessionId'];
echo "\n";

// 6. Admin accede a /api/clientes
echo "6. Admin accede a /api/clientes\n";
$result = makeRequest('http://localhost:8080/api/clientes', 'GET', null, $adminSession);
echo "   Status: {$result['code']} ";
echo ($result['code'] == 200 ? "✅" : "❌") . "\n";
if ($result['body']) {
    $count = count($result['body']['data'] ?? []);
    echo "   Clientes encontrados: $count\n";
}
echo "\n";

// 7. Endpoint /me con sesión de admin
echo "7. Endpoint /api/me con sesión de admin\n";
$result = makeRequest('http://localhost:8080/api/me', 'GET', null, $adminSession);
echo "   Status: {$result['code']} ";
echo ($result['code'] == 200 ? "✅" : "❌") . "\n";
if ($result['body']) {
    echo "   User: " . ($result['body']['user']['name'] ?? 'N/A') . "\n";
    echo "   Email: " . ($result['body']['user']['email'] ?? 'N/A') . "\n";
}
echo "\n";

// 8. Logout
echo "8. Logout de admin\n";
$result = makeRequest('http://localhost:8080/api/logout', 'POST', null, $adminSession);
echo "   Status: {$result['code']} ";
echo ($result['code'] == 200 ? "✅" : "❌") . "\n";
if ($result['body']) {
    echo "   Mensaje: {$result['body']['message']}\n";
}
echo "\n";

// 9. Intentar acceder después del logout
echo "9. Intentar acceder a /api/me después del logout\n";
$result = makeRequest('http://localhost:8080/api/me', 'GET', null, $adminSession);
echo "   Status: {$result['code']} ";
echo ($result['code'] == 401 ? "✅ (Correctamente bloqueado)" : "❌ (Debería dar 401)") . "\n";
echo "\n";

echo "=== PRUEBAS COMPLETADAS ===\n";
