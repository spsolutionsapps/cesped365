<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'name'     => 'Administrador',
                'email'    => 'admin@cesped365.com',
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
                'role'     => 'admin',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name'     => 'Juan Pérez',
                'email'    => 'cliente@example.com',
                'password' => password_hash('cliente123', PASSWORD_DEFAULT),
                'role'     => 'cliente',
                'phone'    => '+54 11 1234-5678',
                'address'  => 'Av. Siempre Viva 123',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name'     => 'María García',
                'email'    => 'maria.garcia@example.com',
                'password' => password_hash('cliente123', PASSWORD_DEFAULT),
                'role'     => 'cliente',
                'phone'    => '+54 11 2345-6789',
                'address'  => 'Calle Falsa 456',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name'     => 'Roberto López',
                'email'    => 'roberto.lopez@example.com',
                'password' => password_hash('cliente123', PASSWORD_DEFAULT),
                'role'     => 'cliente',
                'phone'    => '+54 11 3456-7890',
                'address'  => 'Av. Libertador 789',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name'     => 'Ana Martínez',
                'email'    => 'ana.martinez@example.com',
                'password' => password_hash('cliente123', PASSWORD_DEFAULT),
                'role'     => 'cliente',
                'phone'    => '+54 11 4567-8901',
                'address'  => 'Calle Mayor 321',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('users')->insertBatch($data);
    }
}
