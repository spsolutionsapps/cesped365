<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><title>Test Conexión DB</title>";
echo "<style>body{font-family:monospace;padding:20px;background:#000;color:#0f0;} .error{color:#f00;} .ok{color:#0f0;}</style>";
echo "</head><body>";

echo "<h1>🔍 TEST DE CONEXIÓN A BASE DE DATOS</h1><hr>";

// Credenciales (CÁMBIALAS POR LAS TUYAS)
$hostname = 'localhost';
$database = 'cespvcyi_cesped365_db';
$username = 'cespvcyi_cesped365_user';
$password = 'Sebaspado123'; // <-- CAMBIA ESTO

echo "<h2>1. Información de Conexión</h2>";
echo "<div>Host: $hostname</div>";
echo "<div>Database: $database</div>";
echo "<div>Username: $username</div>";
echo "<div>Password: " . str_repeat('*', strlen($password)) . "</div><br>";

echo "<h2>2. Intentando conectar...</h2>";

try {
    $mysqli = new mysqli($hostname, $username, $password, $database);
    
    if ($mysqli->connect_error) {
        throw new Exception($mysqli->connect_error);
    }
    
    echo "<div class='ok'>✅ CONEXIÓN EXITOSA!</div><br>";
    
    echo "<h2>3. Información de MySQL</h2>";
    echo "<div>MySQL Version: " . $mysqli->server_info . "</div>";
    echo "<div>Host Info: " . $mysqli->host_info . "</div>";
    echo "<div>Character Set: " . $mysqli->character_set_name() . "</div><br>";
    
    echo "<h2>4. Verificar Tablas</h2>";
    $result = $mysqli->query("SHOW TABLES");
    
    if ($result) {
        $tables = [];
        while ($row = $result->fetch_array()) {
            $tables[] = $row[0];
        }
        
        if (count($tables) > 0) {
            echo "<div class='ok'>✅ Tablas encontradas (" . count($tables) . "):</div>";
            echo "<ul>";
            foreach ($tables as $table) {
                echo "<li>$table</li>";
            }
            echo "</ul>";
        } else {
            echo "<div class='error'>❌ No hay tablas en la base de datos</div>";
            echo "<div class='error'>Ejecuta el script database_setup_simple.sql en phpMyAdmin</div>";
        }
    }
    
    echo "<br><h2>5. Verificar Usuario Admin</h2>";
    $result = $mysqli->query("SELECT id, name, email, role FROM users WHERE role = 'admin' LIMIT 1");
    
    if ($result && $result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        echo "<div class='ok'>✅ Usuario admin encontrado:</div>";
        echo "<div>ID: " . $admin['id'] . "</div>";
        echo "<div>Name: " . $admin['name'] . "</div>";
        echo "<div>Email: " . $admin['email'] . "</div>";
        echo "<div>Role: " . $admin['role'] . "</div>";
    } else {
        echo "<div class='error'>❌ No se encontró usuario admin</div>";
        echo "<div class='error'>Crear uno manualmente en phpMyAdmin o ejecutar el script SQL</div>";
    }
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "<div class='error'>❌ ERROR DE CONEXIÓN:</div>";
    echo "<div class='error'>" . $e->getMessage() . "</div><br>";
    
    echo "<h2>🔧 Posibles Soluciones:</h2>";
    echo "<ul>";
    echo "<li class='error'>1. Verificar que el usuario MySQL existe en cPanel</li>";
    echo "<li class='error'>2. Verificar que la contraseña es correcta</li>";
    echo "<li class='error'>3. Verificar que el usuario tiene permisos sobre la base de datos</li>";
    echo "<li class='error'>4. En cPanel → MySQL Databases → Add User To Database</li>";
    echo "</ul>";
}

echo "</body></html>";
