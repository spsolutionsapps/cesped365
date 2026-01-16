<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SubscriptionSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'name' => 'Plan Básico',
                'description' => 'Plan ideal para jardines pequeños con mantenimiento básico',
                'price' => 15000.00,
                'frequency' => 'mensual',
                'visits_per_month' => 2,
                'features' => json_encode([
                    'Corte de césped',
                    'Riego básico',
                    '2 visitas al mes',
                    'Informe fotográfico'
                ]),
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Plan Premium',
                'description' => 'Plan completo con mantenimiento integral del jardín',
                'price' => 28000.00,
                'frequency' => 'mensual',
                'visits_per_month' => 4,
                'features' => json_encode([
                    'Corte de césped profesional',
                    'Control de malezas',
                    'Fertilización',
                    'Control de plagas',
                    'Riego automático',
                    '4 visitas al mes',
                    'Informe detallado con fotos',
                    'Soporte prioritario'
                ]),
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Plan Trimestral',
                'description' => 'Plan económico con pago trimestral anticipado',
                'price' => 75000.00,
                'frequency' => 'trimestral',
                'visits_per_month' => 4,
                'features' => json_encode([
                    'Corte de césped',
                    'Control de malezas',
                    'Fertilización',
                    '4 visitas al mes',
                    'Informe mensual',
                    '10% descuento vs mensual'
                ]),
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Plan Anual',
                'description' => 'Mejor precio del año con servicio garantizado',
                'price' => 280000.00,
                'frequency' => 'anual',
                'visits_per_month' => 4,
                'features' => json_encode([
                    'Corte de césped profesional',
                    'Control de malezas',
                    'Fertilización premium',
                    'Control de plagas',
                    'Riego automático',
                    'Resembrado anual',
                    '4 visitas al mes',
                    'Informe mensual detallado',
                    'Soporte 24/7',
                    '20% descuento vs mensual'
                ]),
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        // Insertar planes
        $this->db->table('subscriptions')->insertBatch($data);
    }
}
