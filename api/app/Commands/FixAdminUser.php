<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\UserModel;

class FixAdminUser extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'db:fix-admin';
    protected $description = 'Verifica y crea/actualiza el usuario admin';

    public function run(array $params)
    {
        $userModel = new UserModel();
        
        CLI::write('=== Verificando usuario admin ===', 'yellow');
        CLI::newLine();
        
        // Buscar usuario admin
        $admin = $userModel->findByEmail('admin@cesped365.com');
        
        if ($admin) {
            CLI::write('✓ Usuario admin encontrado:', 'green');
            CLI::write("  ID: {$admin['id']}");
            CLI::write("  Nombre: {$admin['name']}");
            CLI::write("  Email: {$admin['email']}");
            CLI::write("  Rol: {$admin['role']}");
            CLI::write("  Password hash: " . substr($admin['password'], 0, 20) . "...");
            CLI::newLine();
            
            // Verificar si la contraseña 'admin123' funciona
            $passwordIsAdmin123 = $userModel->verifyPassword('admin123', $admin['password']);
            
            if (!$passwordIsAdmin123) {
                CLI::write('✗ La contraseña no es \'admin123\'', 'yellow');
                CLI::write('  Actualizando contraseña a \'admin123\'...', 'yellow');
                
                $userModel->update($admin['id'], [
                    'password' => 'admin123' // El modelo hará el hash automáticamente
                ]);
                
                CLI::write('✓ Contraseña actualizada correctamente', 'green');
                CLI::write('  Ahora puedes iniciar sesión con: admin@cesped365.com / admin123', 'cyan');
            } else {
                CLI::write("\n✓ El usuario admin está configurado correctamente", 'green');
                CLI::write("  Contraseña: admin123", 'cyan');
            }
        } else {
            CLI::write('✗ Usuario admin NO encontrado', 'red');
            CLI::write('  Creando usuario admin...', 'yellow');
            
            $adminData = [
                'name' => 'Administrador',
                'email' => 'admin@cesped365.com',
                'password' => 'admin123', // El modelo hará el hash automáticamente
                'role' => 'admin',
            ];
            
            $newId = $userModel->insert($adminData);
            
            CLI::write("✓ Usuario admin creado correctamente (ID: {$newId})", 'green');
            CLI::write('  Credenciales: admin@cesped365.com / admin123', 'cyan');
        }
        
        CLI::newLine();
        CLI::write('=== Verificación completada ===', 'yellow');
    }
}
