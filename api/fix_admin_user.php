<?php

/**
 * Script para verificar/crear/actualizar el usuario admin
 * Ejecutar: php fix_admin_user.php
 */

// Definir constantes necesarias
define('ROOTPATH', __DIR__ . DIRECTORY_SEPARATOR);
define('SYSTEMPATH', ROOTPATH . 'vendor' . DIRECTORY_SEPARATOR . 'codeigniter4' . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR);
define('APPPATH', ROOTPATH . 'app' . DIRECTORY_SEPARATOR);
define('WRITEPATH', ROOTPATH . 'writable' . DIRECTORY_SEPARATOR);
define('FCPATH', ROOTPATH . 'public' . DIRECTORY_SEPARATOR);

require __DIR__ . '/vendor/autoload.php';

// Cargar CodeIgniter
$pathsConfig = APPPATH . 'Config/Paths.php';
require realpath($pathsConfig) ?: $pathsConfig;
$paths = new Config\Paths();
$bootstrap = rtrim($paths->systemDirectory, '\\/ ') . '/bootstrap.php';
require realpath($bootstrap) ?: $bootstrap;

// Inicializar CodeIgniter
$app = Config\Services::codeigniter();
$app->initialize();

// Obtener la base de datos
$db = \Config\Database::connect();

echo "=== Verificando usuario admin ===\n\n";

// Buscar usuario admin
$builder = $db->table('users');
$admin = $builder->where('email', 'admin@cesped365.com')->get()->getRowArray();

if ($admin) {
    echo "✓ Usuario admin encontrado:\n";
    echo "  ID: {$admin['id']}\n";
    echo "  Nombre: {$admin['name']}\n";
    echo "  Email: {$admin['email']}\n";
    echo "  Rol: {$admin['role']}\n";
    echo "  Password hash: " . substr($admin['password'], 0, 20) . "...\n\n";
    
    // Verificar si la contraseña funciona
    $testPasswords = ['admin123', 'password'];
    $passwordWorks = false;
    
    foreach ($testPasswords as $testPwd) {
        if (password_verify($testPwd, $admin['password'])) {
            echo "✓ La contraseña '{$testPwd}' funciona correctamente\n";
            $passwordWorks = true;
            break;
        }
    }
    
    if (!$passwordWorks) {
        echo "✗ Ninguna de las contraseñas probadas funciona\n";
        echo "  Actualizando contraseña a 'admin123'...\n";
        
        $newHash = password_hash('admin123', PASSWORD_DEFAULT);
        $builder->where('id', $admin['id'])
                ->update(['password' => $newHash]);
        
        echo "✓ Contraseña actualizada correctamente\n";
        echo "  Ahora puedes iniciar sesión con: admin@cesped365.com / admin123\n";
    } else {
        echo "\n✓ El usuario admin está configurado correctamente\n";
    }
} else {
    echo "✗ Usuario admin NO encontrado\n";
    echo "  Creando usuario admin...\n";
    
    $adminData = [
        'name' => 'Administrador',
        'email' => 'admin@cesped365.com',
        'password' => password_hash('admin123', PASSWORD_DEFAULT),
        'role' => 'admin',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
    ];
    
    $builder->insert($adminData);
    $newId = $db->insertID();
    
    echo "✓ Usuario admin creado correctamente (ID: {$newId})\n";
    echo "  Credenciales: admin@cesped365.com / admin123\n";
}

echo "\n=== Verificación completada ===\n";
