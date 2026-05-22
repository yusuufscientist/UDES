<?php

namespace Database\Factories;

use App\Models\SolarSystem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Panel>
 */
class PanelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'solar_system_id' => SolarSystem::factory(),
            'serial_number' => 'PANEL-'.fake()->unique()->regexify('[A-Z0-9]{10}'),
            'model' => fake()->randomElement(['SolarMax 300', 'SunPower 400', 'LG Neon 350', 'Canadian 325']),
            'manufacturer' => fake()->randomElement(['SolarMax', 'SunPower', 'LG', 'Canadian Solar']),
            'capacity_watts' => fake()->randomElement([300, 325, 350, 375, 400, 450]),
            'efficiency_rating' => fake()->randomFloat(2, 18, 22),
            'installation_date' => fake()->dateTimeBetween('-5 years', 'now'),
            'status' => fake()->randomElement(['active', 'inactive', 'faulty', 'maintenance']),
            'current_voltage' => fake()->randomFloat(2, 30, 40),
            'current_amperage' => fake()->randomFloat(2, 8, 12),
            'current_power_output' => fake()->randomFloat(2, 200, 350),
            'last_reading_at' => fake()->dateTimeBetween('-1 day', 'now'),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the panel is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the panel is faulty.
     */
    public function faulty(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'faulty',
            'current_power_output' => 0,
        ]);
    }
}
