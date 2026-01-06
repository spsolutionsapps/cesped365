<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GardenReport;
use App\Models\Subscription;
use Carbon\Carbon;

class GardenReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subscriptions = Subscription::where('status', 'active')->get();

        if ($subscriptions->isEmpty()) {
            $this->command->warn('No hay suscripciones activas disponibles. Ejecuta primero SubscriptionSeeder.');
            return;
        }

        foreach ($subscriptions as $subscription) {
            // Crear 2-3 reportes por suscripción
            $reportCount = rand(2, 3);
            
            for ($i = 0; $i < $reportCount; $i++) {
                $reportDate = Carbon::now()->subDays(rand(1, 60));
                
                GardenReport::create([
                    'user_id' => $subscription->user_id,
                    'subscription_id' => $subscription->id,
                    'report_date' => $reportDate,
                    'general_status' => ['good', 'regular', 'improve'][rand(0, 2)],
                    'grass_even' => rand(0, 1),
                    'grass_color' => ['ok', 'regular', 'bad'][rand(0, 2)],
                    'grass_spots' => rand(0, 1),
                    'worn_areas' => rand(0, 1),
                    'visible_weeds' => rand(0, 1),
                    'grass_note' => rand(0, 1) ? 'Césped en buen estado general' : null,
                    'growth_cm' => rand(50, 150) / 10,
                    'growth_category' => ['low', 'normal', 'high'][rand(0, 2)],
                    'growth_estimated' => rand(0, 1) ? rand(50, 200) / 10 : null,
                    'growth_note' => rand(0, 1) ? 'Crecimiento normal para la época' : null,
                    'soil_condition' => ['loose', 'compact'][rand(0, 1)],
                    'aeration_recommended' => rand(0, 1),
                    'soil_note' => rand(0, 1) ? 'Suelo en condiciones adecuadas' : null,
                    'humidity_status' => ['dry', 'correct', 'excess'][rand(0, 2)],
                    'humidity_note' => rand(0, 1) ? 'Humedad adecuada' : null,
                    'pests_status' => ['none', 'mild', 'observe'][rand(0, 2)],
                    'pests_note' => rand(0, 1) ? 'Sin plagas detectadas' : null,
                    'flowerbeds_status' => ['clean', 'weeds', 'maintenance'][rand(0, 2)],
                    'flowerbeds_note' => rand(0, 1) ? 'Canteros limpios' : null,
                    'seasonal_recommendations' => rand(0, 1) ? 'Continuar con el mantenimiento regular' : null,
                    'general_observations' => rand(0, 1) ? 'Jardín en buen estado general. Continuar con el cuidado regular.' : null,
                ]);
            }
        }
    }
}

