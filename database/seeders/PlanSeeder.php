<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plan;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Plan::create([
            'name' => 'Plan Mensual',
            'type' => 'monthly',
            'price' => 5000.00,
            'duration_months' => 1,
            'active' => true,
        ]);

        Plan::create([
            'name' => 'Plan Anual',
            'type' => 'yearly',
            'price' => 50000.00,
            'duration_months' => 12,
            'active' => true,
        ]);
    }
}

