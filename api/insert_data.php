<?php

$pdo = new PDO("mysql:host=localhost;dbname=cesped365;charset=utf8mb4", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "Insertando usuarios...\n";
$stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, phone, address, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");

$users = [
    ['Administrador', 'admin@cesped365.com', password_hash('admin123', PASSWORD_DEFAULT), 'admin', null, null],
    ['Juan Pérez', 'cliente@example.com', password_hash('cliente123', PASSWORD_DEFAULT), 'cliente', '+54 11 1234-5678', 'Av. Siempre Viva 123'],
    ['María García', 'maria.garcia@example.com', password_hash('cliente123', PASSWORD_DEFAULT), 'cliente', '+54 11 2345-6789', 'Calle Falsa 456'],
    ['Roberto López', 'roberto.lopez@example.com', password_hash('cliente123', PASSWORD_DEFAULT), 'cliente', '+54 11 3456-7890', 'Av. Libertador 789'],
    ['Ana Martínez', 'ana.martinez@example.com', password_hash('cliente123', PASSWORD_DEFAULT), 'cliente', '+54 11 4567-8901', 'Calle Mayor 321'],
];

foreach ($users as $user) {
    $stmt->execute($user);
}
echo "✓ 5 usuarios insertados\n";

echo "Insertando jardines...\n";
$stmt = $pdo->prepare("INSERT INTO gardens (user_id, address, notes, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");

$gardens = [
    [2, 'Av. Siempre Viva 123', 'Jardín principal del cliente Juan Pérez. Césped de 200m².'],
    [3, 'Calle Falsa 456', 'Jardín pequeño, 80m². Zona con sombra parcial.'],
    [4, 'Av. Libertador 789', 'Jardín grande, 350m². Incluye área de juegos.'],
    [5, 'Calle Mayor 321', 'Jardín mediano, 150m². Piscina incluida.'],
];

foreach ($gardens as $garden) {
    $stmt->execute($garden);
}
echo "✓ 4 jardines insertados\n";

echo "Insertando reportes...\n";
$stmt = $pdo->prepare("INSERT INTO reports (garden_id, date, estado_general, cesped_parejo, color_ok, manchas, zonas_desgastadas, malezas_visibles, crecimiento_cm, compactacion, humedad, plagas, observaciones, jardinero, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");

$reports = [
    [1, '2026-01-10', 'Bueno', 1, 1, 0, 0, 0, 2.5, 'Normal', 'Adecuada', 0, 'El césped está en excelente estado. Se realizó corte regular y fertilización. Recomendamos mantener el riego actual.', 'Carlos Rodríguez'],
    [1, '2025-12-15', 'Regular', 1, 0, 1, 0, 1, 3.2, 'Ligera', 'Baja', 0, 'Se detectaron algunas manchas amarillas en la zona norte. Se aplicó tratamiento para malezas. Recomendamos aumentar el riego.', 'María González'],
    [1, '2025-11-20', 'Bueno', 1, 1, 0, 1, 0, 2.8, 'Normal', 'Adecuada', 0, 'Estado general bueno. Se identificó una pequeña zona desgastada cerca del árbol. Se realizó resembrado.', 'Carlos Rodríguez'],
    [2, '2026-01-08', 'Bueno', 1, 1, 0, 0, 0, 2.1, 'Normal', 'Adecuada', 0, 'Jardín en perfecto estado. Mantenimiento regular completado.', 'Carlos Rodríguez'],
    [3, '2026-01-09', 'Bueno', 1, 1, 0, 0, 0, 3.0, 'Normal', 'Adecuada', 0, 'Jardín grande en excelentes condiciones. Se realizó corte y perfilado de bordes.', 'María González'],
];

foreach ($reports as $report) {
    $stmt->execute($report);
}
echo "✓ 5 reportes insertados\n";

echo "\n✅ Datos insertados correctamente!\n";
