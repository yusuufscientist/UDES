<?php

namespace Database\Seeders;

use App\Models\Alert;
use App\Models\SolarSystem;
use Illuminate\Database\Seeder;

class AlertSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $systems = SolarSystem::all();

        if ($systems->isEmpty()) {
            $this->command->info('No solar systems found. Please create solar systems first.');

            return;
        }

        $alerts = [
            [
                'title' => 'Low Production Detected',
                'message' => 'Solar system production is below expected levels. Check panel cleanliness and inverter status.',
                'type' => 'low_production',
                'severity' => 'high',
                'status' => 'active',
            ],
            [
                'title' => 'Panel Maintenance Recommended',
                'message' => 'Panel cleaning is recommended due to accumulated dust and debris.',
                'type' => 'maintenance_due',
                'severity' => 'medium',
                'status' => 'active',
            ],
            [
                'title' => 'Weather Warning: Heavy Rain',
                'message' => 'Heavy rain expected in the next 24 hours. Monitor system performance.',
                'type' => 'weather_warning',
                'severity' => 'low',
                'status' => 'active',
            ],
            [
                'title' => 'Inverter Temperature High',
                'message' => 'Inverter operating temperature is above normal range. Ensure proper ventilation.',
                'type' => 'panel_fault',
                'severity' => 'critical',
                'status' => 'active',
            ],
            [
                'title' => 'Grid Connection Issue',
                'message' => 'Intermittent grid connection detected. Check wiring and connections.',
                'type' => 'system_offline',
                'severity' => 'critical',
                'status' => 'active',
            ],
        ];

        foreach ($systems as $system) {
            // Create 2-3 random alerts for each system
            $numAlerts = rand(2, 3);
            $selectedAlerts = array_rand($alerts, $numAlerts);

            if (! is_array($selectedAlerts)) {
                $selectedAlerts = [$selectedAlerts];
            }

            foreach ($selectedAlerts as $index) {
                $alertData = $alerts[$index];
                Alert::create([
                    'solar_system_id' => $system->id,
                    'title' => $alertData['title'],
                    'message' => $alertData['message'],
                    'type' => $alertData['type'],
                    'severity' => $alertData['severity'],
                    'status' => $alertData['status'],
                    'triggered_at' => now()->subHours(rand(1, 48)),
                ]);
            }
        }

        $this->command->info('Alerts seeded successfully!');
    }
}
