<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><title>Ver Password Admin</title>";
echo "<style>body{font-family:monospace;padding:20px;background:#000;color:#0f0;} .error{color:#f00;} .ok{color:#0f0;} code{background:#1a1a1a;padding:2px 5px;border:1px solid #0f0;}</style>";
echo "</head><body>";

echo "<h1>🔍 VERIFICAR/RESETEAR PASSWORD ADMIN</h1><hr>";

// Credenciales DB
$hostname = 'localhost';
$database = 'cespvcyi_cesped365_db';
$username = 'cespvcyi_cesped365_user';
$password = 'Sebaspado123';

echo "<h2>1. Conectar a la Base de Datos</h2>";

try {
    $mysqli = new mysqli($hostname, $username, $password, $database);
    
    if ($mysqli->connect_error) {
        throw new Exception($mysqli->connect_error);
    }
    
    echo "<div class='ok'>✅ Conectado a la base de datos</div><br>";
    
    // Obtener usuario admin
    echo "<h2>2. Usuario Admin Actual</h2>";
    $result = $mysqli->query("SELECT id, name, email, password FROM users WHERE role = 'admin' LIMIT 1");
    
    if ($result && $result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        
        echo "<div class='ok'>✅ Usuario encontrado:</div>";
        echo "<div>ID: " . $admin['id'] . "</div>";
        echo "<div>Name: " . $admin['name'] . "</div>";
        echo "<div>Email: " . $admin['email'] . "</div>";
        echo "<div>Password Hash: <code>" . substr($admin['password'], 0, 50) . "...</code></div><br>";
        
        echo "<h2>3. Verificar Password</h2>";
        
        // Probar passwords comunes
        $passwords_to_test = ['admin123', 'password', 'admin', 'Password123', 'Admin123'];
        $password_found = false;
        
        foreach ($passwords_to_test as $test_pass) {
            if (password_verify($test_pass, $admin['password'])) {
                echo "<div class='ok'>✅ PASSWORD ENCONTRADO: <code>$test_pass</code></div>";
                echo "<div class='ok'>Usa este password para iniciar sesión</div>";
                $password_found = true;
                break;
            }
        }
        
        if (!$password_found) {
            echo "<div class='error'>❌ Ninguno de los passwords comunes coincide</div>";
            echo "<div class='error'>Passwords probados: " . implode(', ', $passwords_to_test) . "</div><br>";
            
            echo "<h2>4. Resetear Password</h2>";
            
            // Si viene del form, resetear
            if (isset($_POST['reset_password'])) {
                $new_password = 'admin123';
                $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
                
                $stmt = $mysqli->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $new_hash, $admin['id']);
                
                if ($stmt->execute()) {
                    echo "<div class='ok'>✅ PASSWORD RESETEADO EXITOSAMENTE!</div>";
                    echo "<div class='ok'>Nuevo password: <code>admin123</code></div>";
                    echo "<div class='ok'>Email: <code>" . $admin['email'] . "</code></div>";
                    echo "<div class='ok'>Ahora puedes iniciar sesión con estas credenciales</div>";
                } else {
                    echo "<div class='error'>❌ Error al actualizar: " . $stmt->error . "</div>";
                }
                
                $stmt->close();
            } else {
                // Mostrar form para resetear
                echo "<form method='POST'>";
                echo "<div>¿Resetear password del admin a: <code>admin123</code>?</div><br>";
                echo "<button type='submit' name='reset_password' style='background:#0f0;color:#000;border:none;padding:10px 20px;cursor:pointer;font-weight:bold;'>RESETEAR PASSWORD</button>";
                echo "</form>";
            }
        }
        
    } else {
        echo "<div class='error'>❌ No se encontró usuario admin</div>";
        echo "<div class='error'>Necesitas crear un usuario admin manualmente</div>";
    }
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "<div class='error'>❌ ERROR: " . $e->getMessage() . "</div>";
}

echo "</body></html>";
