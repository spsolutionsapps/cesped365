<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><title>Estructura de Tablas</title>";
echo "<style>body{font-family:monospace;padding:20px;background:#000;color:#0f0;} .error{color:#f00;} .ok{color:#0f0;} table{border-collapse:collapse;margin:10px 0;} th,td{border:1px solid #0f0;padding:8px;text-align:left;} th{background:#1a1a1a;}</style>";
echo "</head><body>";

echo "<h1>📊 ESTRUCTURA DE TABLAS</h1><hr>";

// Credenciales DB
$hostname = 'localhost';
$database = 'cespvcyi_cesped365_db';
$username = 'cespvcyi_cesped365_user';
$password = 'Sebaspado123';

try {
    $mysqli = new mysqli($hostname, $username, $password, $database);
    
    if ($mysqli->connect_error) {
        throw new Exception($mysqli->connect_error);
    }
    
    echo "<div class='ok'>✅ Conectado a: $database</div><br>";
    
    // Obtener todas las tablas
    $result = $mysqli->query("SHOW TABLES");
    $tables = [];
    
    while ($row = $result->fetch_array()) {
        $tables[] = $row[0];
    }
    
    echo "<h2>Tablas encontradas: " . count($tables) . "</h2>";
    
    // Para cada tabla, mostrar estructura
    foreach ($tables as $table) {
        echo "<h3>📋 Tabla: $table</h3>";
        
        $columns = $mysqli->query("DESCRIBE $table");
        
        echo "<table>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        
        while ($col = $columns->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $col['Field'] . "</td>";
            echo "<td>" . $col['Type'] . "</td>";
            echo "<td>" . $col['Null'] . "</td>";
            echo "<td>" . $col['Key'] . "</td>";
            echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
            echo "<td>" . $col['Extra'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        // Mostrar 1 registro de ejemplo
        $sample = $mysqli->query("SELECT * FROM $table LIMIT 1");
        if ($sample && $sample->num_rows > 0) {
            echo "<div class='ok'>✅ Registro de ejemplo:</div>";
            $row = $sample->fetch_assoc();
            echo "<pre>" . print_r($row, true) . "</pre>";
        } else {
            echo "<div class='error'>⚠️ Tabla vacía</div>";
        }
        
        echo "<hr>";
    }
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "<div class='error'>❌ ERROR: " . $e->getMessage() . "</div>";
}

echo "</body></html>";
