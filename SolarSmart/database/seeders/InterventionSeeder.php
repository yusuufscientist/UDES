<?php

namespace Database\Seeders;

use App\Models\Intervention;
use App\Models\SolarSystem;
use App\Models\User;
use Illuminate\Database\Seeder;

class InterventionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $systems = SolarSystem::all();
        $technicians = User::where('role', 'technician')->where('is_active', true)->get();

        if ($systems->isEmpty()) {
            $this->command->info('No solar systems found. Please create solar systems first.');

            return;
        }

        // If no technicians exist, fall back to any active user so FK is still valid
        if ($technicians->isEmpty()) {
            $technicians = User::where('is_active', true)->get();
        }

        if ($technicians->isEmpty()) {
            $this->command->info('No users found. Please seed users first.');

            return;
        }

        $interventions = [
            [
                'type' => 'routine_maintenance',
                'description' => 'Regular cleaning and inspection of solar panels.',
                'priority' => 'medium',
                'status' => 'scheduled',
            ],
            [
                'type' => 'repair',
                'description' => 'Replace faulty panel PNL-003.',
                'priority' => 'high',
                'status' => 'scheduled',
            ],
            [
                'type' => 'inspection',
                'description' => 'Check inverter performance and connections.',
                'priority' => 'low',
                'status' => 'in_progress',
            ],
            [
                'type' => 'emergency_repair',
                'description' => 'Repair damaged cable from panel array.',
                'priority' => 'urgent',
                'status' => 'scheduled',
            ],
            [
                'type' => 'cleaning',
                'description' => 'Install additional monitoring equipment.',
                'priority' => 'low',
                'status' => 'completed',
            ],
        ];

        foreach ($systems as $system) {
            // Create 2-3 interventions per system
            $numInterventions = rand(2, 3);
            $selectedInterventions = array_rand($interventions, $numInterventions);

            if (! is_array($selectedInterventions)) {
                $selectedInterventions = [$selectedInterventions];
            }

            foreach ($selectedInterventions as $index) {
                $interventionData = $interventions[$index];

                Intervention::create([
                    'solar_system_id' => $system->id,
                    'technician_id' => $technicians->random()->id,
                    'type' => $interventionData['type'],
                    'description' => $interventionData['description'],
                    'priority' => $interventionData['priority'],
                    'status' => $interventionData['status'],
                    'scheduled_date' => now()->addDays(rand(1, 14)),
                    'completed_date' => $interventionData['status'] === 'completed' ? now()->subDays(rand(1, 7)) : null,
                ]);
            }
        }

        $this->command->info('Interventions seeded successfully!');
    }
}
