<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Plan;
use Carbon\Carbon;

class SubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = User::where('role', 'client')->get();
        $plans = Plan::all();

        if ($clients->isEmpty() || $plans->isEmpty()) {
            $this->command->warn('No hay clientes o planes disponibles. Ejecuta primero AdminSeeder, ClientSeeder y PlanSeeder.');
            return;
        }

        // Crear suscripciones activas
        foreach ($clients->take(2) as $client) {
            $plan = $plans->random();
            $startDate = Carbon::now()->subDays(rand(1, 30));
            $endDate = $startDate->copy()->addMonths($plan->duration_months);

            Subscription::create([
                'user_id' => $client->id,
                'plan_id' => $plan->id,
                'status' => 'active',
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);
        }

        // Crear suscripción expirada
        if ($clients->count() > 2) {
            $plan = $plans->random();
            $startDate = Carbon::now()->subMonths(2);
            $endDate = $startDate->copy()->addMonths($plan->duration_months);

            Subscription::create([
                'user_id' => $clients->skip(2)->first()->id,
                'plan_id' => $plan->id,
                'status' => 'expired',
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);
        }
    }
}

