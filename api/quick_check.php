<?php

$pdo = new PDO("mysql:host=localhost;dbname=cesped365", "root", "");

echo "Usuarios:\n";
$users = $pdo->query("SELECT id, name, email, role FROM users LIMIT 5")->fetchAll();
foreach ($users as $u) {
    echo "  {$u['id']}. {$u['name']} ({$u['email']}) - {$u['role']}\n";
}

echo "\nJardines:\n";
$gardens = $pdo->query("SELECT id, user_id, address FROM gardens LIMIT 5")->fetchAll();
foreach ($gardens as $g) {
    echo "  {$g['id']}. User#{$g['user_id']} - {$g['address']}\n";
}

echo "\nReportes:\n";
$reports = $pdo->query("SELECT id, garden_id, date, estado_general FROM reports LIMIT 5")->fetchAll();
foreach ($reports as $r) {
    echo "  {$r['id']}. Garden#{$r['garden_id']} - {$r['date']} - {$r['estado_general']}\n";
}
