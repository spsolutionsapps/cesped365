<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PublicPlansSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');
        $table = $this->db->table('subscriptions');

        // Planes "reales" mostrados en la landing (sección Planes)
        $publicPlans = [
            [
                'name' => 'Urbano',
                'description' => 'Hasta 500 m² de tu jardín',
                'price' => 45000.00,
                'frequency' => 'mensual',
                'visits_per_month' => 1,
                'features' => json_encode([], JSON_UNESCAPED_UNICODE),
                'is_active' => 1,
            ],
            [
                'name' => 'Residencial',
                'description' => '500 a 2.500 m² de tu jardín',
                'price' => 90000.00,
                'frequency' => 'mensual',
                'visits_per_month' => 1,
                'features' => json_encode([], JSON_UNESCAPED_UNICODE),
                'is_active' => 1,
            ],
            [
                'name' => 'Parque',
                'description' => '2.500 a 4.000 m² de tu jardín',
                'price' => 120000.00,
                'frequency' => 'mensual',
                'visits_per_month' => 1,
                'features' => json_encode([], JSON_UNESCAPED_UNICODE),
                'is_active' => 1,
            ],
        ];

        // 1) Desactivar todos los planes existentes para no mezclar catálogo público
        $table->set(['is_active' => 0, 'updated_at' => $now])->update();

        // 2) Upsert de los planes públicos
        foreach ($publicPlans as $p) {
            $existing = $this->db->table('subscriptions')
                ->where('name', $p['name'])
                ->get()
                ->getRowArray();

            if ($existing) {
                $table->where('id', $existing['id'])->update(array_merge($p, ['updated_at' => $now]));
            } else {
                $table->insert(array_merge($p, ['created_at' => $now, 'updated_at' => $now]));
            }
        }
    }
}

