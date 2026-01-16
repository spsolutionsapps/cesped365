<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSubscriptionSeeder extends Seeder
{
    public function run()
    {
        // Asignar suscripciones a clientes existentes
        $data = [
            [
                'user_id' => 2, // Juan Pérez
                'subscription_id' => 2, // Plan Premium
                'status' => 'activa',
                'start_date' => date('Y-m-d', strtotime('-2 months')),
                'end_date' => null,
                'next_billing_date' => date('Y-m-d', strtotime('+1 month')),
                'auto_renew' => 1,
                'payment_method' => 'mercadopago',
                'external_payment_id' => 'MP-' . rand(100000, 999999),
                'notes' => 'Cliente Premium desde el inicio',
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 months')),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'user_id' => 3, // María García
                'subscription_id' => 1, // Plan Básico
                'status' => 'activa',
                'start_date' => date('Y-m-d', strtotime('-1 month')),
                'end_date' => null,
                'next_billing_date' => date('Y-m-d', strtotime('+15 days')),
                'auto_renew' => 1,
                'payment_method' => 'mercadopago',
                'external_payment_id' => 'MP-' . rand(100000, 999999),
                'notes' => 'Cliente nuevo con plan básico',
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 month')),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'user_id' => 4, // Carlos López
                'subscription_id' => 3, // Plan Trimestral
                'status' => 'activa',
                'start_date' => date('Y-m-d', strtotime('-45 days')),
                'end_date' => null,
                'next_billing_date' => date('Y-m-d', strtotime('+45 days')),
                'auto_renew' => 1,
                'payment_method' => 'transferencia',
                'external_payment_id' => null,
                'notes' => 'Pago por transferencia bancaria',
                'created_at' => date('Y-m-d H:i:s', strtotime('-45 days')),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'user_id' => 5, // Ana Martínez
                'subscription_id' => 2, // Plan Premium
                'status' => 'pausada',
                'start_date' => date('Y-m-d', strtotime('-3 months')),
                'end_date' => null,
                'next_billing_date' => null,
                'auto_renew' => 0,
                'payment_method' => 'mercadopago',
                'external_payment_id' => 'MP-' . rand(100000, 999999),
                'notes' => 'Pausada temporalmente por vacaciones',
                'created_at' => date('Y-m-d H:i:s', strtotime('-3 months')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-1 week')),
            ],
        ];

        // Insertar suscripciones de usuarios
        $this->db->table('user_subscriptions')->insertBatch($data);
    }
}
