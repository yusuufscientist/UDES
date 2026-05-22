<?php

namespace Database\Factories;

use App\Models\Panel;
use App\Models\SolarSystem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Production>
 */
class ProductionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $energyProduced = fake()->randomFloat(4, 0.5, 5);

        return [
            'solar_system_id' => SolarSystem::factory(),
            'panel_id' => fake()->optional()->randomElement([Panel::factory(), null]),
            'production_date' => fake()->dateTimeBetween('-30 days', 'now'),
            'production_time' => fake()->optional()->time(),
            'energy_produced_kwh' => $energyProduced,
            'energy_consumed_kwh' => fake()->randomFloat(4, 0, $energyProduced * 0.3),
            'peak_power_kw' => fake()->randomFloat(4, 0.2, 0.5),
            'average_power_kw' => fake()->randomFloat(4, 0.1, 0.3),
            'irradiance_wm2' => fake()->randomFloat(2, 200, 1000),
            'temperature_celsius' => fake()->randomFloat(2, 15, 40),
            'efficiency_percentage' => fake()->randomFloat(2, 70, 95),
            'weather_condition' => fake()->randomElement(['sunny', 'cloudy', 'rainy', 'partly_cloudy']),
        ];
    }

    /**
     * Set production for today.
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'production_date' => today(),
        ]);
    }

    /**
     * Set production for a specific date.
     */
    public function forDate($date): static
    {
        return $this->state(fn (array $attributes) => [
            'production_date' => $date,
        ]);
    }
}
