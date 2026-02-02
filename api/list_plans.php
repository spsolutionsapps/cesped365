<?php

$host = 'localhost';
$user = 'root';
$pass = '';
$dbName = 'cesped365';
$port = 3306;

$db = new mysqli($host, $user, $pass, $dbName, $port);
if ($db->connect_errno) {
    echo "DBERR: {$db->connect_error}\n";
    exit(1);
}

$res = $db->query("SELECT id, name, price, frequency, is_active FROM subscriptions ORDER BY id");
while ($row = $res->fetch_assoc()) {
    echo "{$row['id']}|{$row['name']}|{$row['price']}|{$row['frequency']}|{$row['is_active']}\n";
}

