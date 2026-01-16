<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class GardenSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'user_id' => 2, // Juan Pérez
                'address' => 'Av. Siempre Viva 123',
                'notes'   => 'Jardín principal del cliente Juan Pérez. Césped de 200m².',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'user_id' => 3, // María García
                'address' => 'Calle Falsa 456',
                'notes'   => 'Jardín pequeño, 80m². Zona con sombra parcial.',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'user_id' => 4, // Roberto López
                'address' => 'Av. Libertador 789',
                'notes'   => 'Jardín grande, 350m². Incluye área de juegos.',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'user_id' => 5, // Ana Martínez
                'address' => 'Calle Mayor 321',
                'notes'   => 'Jardín mediano, 150m². Piscina incluida.',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('gardens')->insertBatch($data);
    }
}
