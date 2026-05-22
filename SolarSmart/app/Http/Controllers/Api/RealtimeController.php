<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Production;
use App\Models\SolarSystem;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class RealtimeController extends Controller
{
    /**
     * Get real-time production data for the current day
     */
    public function realtimeProduction()
    {
        $user = Auth::user();
        $systemIds = $user->solarSystems()->pluck('id');

        // Get today's production data grouped by hour
        $productions = Production::whereIn('solar_system_id', $systemIds)
            ->whereDate('production_date', today())
            ->selectRaw('HOUR(production_time) as hour, SUM(energy_produced_kwh) as energy')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->keyBy('hour');

        // Generate data for all 24 hours
        $labels = [];
        $data = [];

        // Get total system capacity for simulation
        $totalCapacity = SolarSystem::whereIn('id', $systemIds)->sum('total_capacity_kw');

        for ($i = 0; $i < 24; $i++) {
            $labels[] = sprintf('%02d:00', $i);

            // If we have real data, use it; otherwise simulate
            if ($productions->has($i)) {
                $data[] = round($productions->get($i)->energy ?? 0, 2);
            } else {
                // Generate realistic simulated data based on solar curve
                // Peak at noon, zero at night
                $simulatedValue = $this->generateSimulatedHourlyProduction($i, $totalCapacity);
                $data[] = round($simulatedValue, 2);
            }
        }

        // Add current reading
        $currentHour = now()->hour;
        $currentProduction = Production::whereIn('solar_system_id', $systemIds)
            ->whereDate('production_date', today())
            ->whereHour('production_time', $currentHour)
            ->sum('energy_produced_kwh');

        // If no real data, simulate current hour production
        if ($currentProduction == 0 && $totalCapacity > 0) {
            $currentProduction = $this->generateSimulatedHourlyProduction($currentHour, $totalCapacity);
        }

        // Get monthly data
        $monthlyLabels = [];
        $monthlyData = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthlyLabels[] = Carbon::create()->month($i)->format('M');
            $monthlyProduction = Production::whereIn('solar_system_id', $systemIds)
                ->whereYear('production_date', now()->year)
                ->whereMonth('production_date', $i)
                ->sum('energy_produced_kwh');
            $monthlyData[] = round($monthlyProduction, 2);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'labels' => $labels,
                'production' => $data,
                'monthly_labels' => $monthlyLabels,
                'monthly_production' => $monthlyData,
                'current_hour' => $currentHour,
                'current_production' => round($currentProduction, 2),
                'total_today' => round(array_sum($data), 2),
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Generate new random data and return real-time data
     */
    public function generateAndGetRealtimeData()
    {
        $user = Auth::user();
        $systems = $user->solarSystems;

        $currentHour = now()->hour;
        $weatherConditions = ['sunny', 'partly_cloudy', 'cloudy', 'rainy'];
        $weatherCondition = $weatherConditions[array_rand($weatherConditions)];

        // Use default capacity for demo if no systems exist
        $totalCapacity = $systems->sum('total_capacity_kw');
        if ($totalCapacity == 0) {
            $totalCapacity = 50; // Default 50kW for demo purposes
        }

        foreach ($systems as $system) {
            $systemCapacity = $system->total_capacity_kw > 0 ? $system->total_capacity_kw : ($totalCapacity / max(1, $systems->count()));
            $hourlyProduction = ($systemCapacity / 12) * (0.7 + (rand(0, 60) / 100));

            if ($currentHour >= 6 && $currentHour <= 18) {
                Production::updateOrCreate(
                    [
                        'solar_system_id' => $system->id,
                        'production_date' => today(),
                        'production_time' => sprintf('%02d:00:00', $currentHour),
                    ],
                    [
                        'energy_produced_kwh' => max(0, $hourlyProduction),
                        'energy_consumed_kwh' => 0,
                        'peak_power_kw' => max(0, $hourlyProduction * 1.2),
                        'average_power_kw' => max(0, $hourlyProduction * 0.8),
                        'weather_condition' => $weatherCondition,
                        'irradiance_wm2' => rand(400, 1000),
                        'temperature_celsius' => rand(20, 40),
                    ]
                );
            }
        }

        $systemIds = $user->solarSystems()->pluck('id');

        // Get weekly data - Last 7 days - ALWAYS generate new random data for real-time effect
        $weeklyLabels = [];
        $weeklyData = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $weeklyLabels[] = $date->format('M d');

            // Get actual production for this day or generate simulated data with variation
            $dayProduction = Production::whereIn('solar_system_id', $systemIds)
                ->whereDate('production_date', $date->toDateString())
                ->sum('energy_produced_kwh');

            // Always generate dynamic simulated data with random fluctuation
            // This ensures real-time updates every second
            $simulatedDayProduction = $this->generateSimulatedDailyProduction($date, $totalCapacity);

            // Add small random variation each time for real-time effect (±5%)
            $variation = 1 + (mt_rand(-50, 50) / 1000);
            $simulatedDayProduction *= $variation;

            // Use simulated data (it will update in real-time)
            $dayProduction = $simulatedDayProduction;

            $weeklyData[] = round($dayProduction, 2);
        }

        // Get monthly data - All 12 months of 2026 - ALWAYS generate new random data
        $monthlyLabels = [];
        $monthlyData = [];

        for ($month = 1; $month <= 12; $month++) {
            $monthlyLabels[] = Carbon::create(2026, $month)->format('M');

            // Get actual production for this month or generate simulated data
            $monthProduction = Production::whereIn('solar_system_id', $systemIds)
                ->whereYear('production_date', 2026)
                ->whereMonth('production_date', $month)
                ->sum('energy_produced_kwh');

            // Always generate dynamic simulated data with variation
            $simulatedMonthProduction = $this->generateSimulatedMonthlyProduction($month, $totalCapacity);

            // Add random variation each time for real-time effect (±3%)
            $variation = 1 + (mt_rand(-30, 30) / 1000);
            $simulatedMonthProduction *= $variation;

            // Use simulated data
            $monthProduction = $simulatedMonthProduction;

            $monthlyData[] = round($monthProduction, 2);
        }

        // Get today's total for stats - always generate fresh data
        $todayProduction = Production::whereIn('solar_system_id', $systemIds)
            ->whereDate('production_date', today())
            ->sum('energy_produced_kwh');

        // Generate today's production with real-time fluctuation
        $simulatedTodayProduction = $this->generateSimulatedDailyProduction(now(), $totalCapacity);
        $variation = 1 + (mt_rand(-50, 50) / 1000);
        $simulatedTodayProduction *= $variation;
        $todayProduction = $simulatedTodayProduction;

        // Current hour production with real-time variation
        $currentProduction = ($totalCapacity / 12) * (0.7 + (rand(0, 60) / 100)) * (0.8 + (rand(0, 40) / 100));
        if ($currentHour < 6 || $currentHour > 18) {
            $currentProduction = 0;
        }

        // Try to get from database, but use simulated if not available
        $dbProduction = 0;
        if ($systemIds->count() > 0) {
            $dbProduction = Production::whereIn('solar_system_id', $systemIds)
                ->whereDate('production_date', today())
                ->where('production_time', 'like', sprintf('%02d:%%', $currentHour))
                ->sum('energy_produced_kwh');
        }

        if ($dbProduction > 0) {
            $currentProduction = $dbProduction;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'labels' => $weeklyLabels,
                'production' => $weeklyData,
                'monthly_labels' => $monthlyLabels,
                'monthly_production' => $monthlyData,
                'current_hour' => $currentHour,
                'current_production' => round($currentProduction, 2),
                'total_today' => round($todayProduction, 2),
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Generate simulated hourly production based on solar curve
     */
    private function generateSimulatedHourlyProduction(int $hour, float $totalCapacity): float
    {
        if ($totalCapacity == 0) {
            return 0;
        }

        // No production at night (before 6am or after 8pm)
        if ($hour < 6 || $hour > 20) {
            return 0;
        }

        // Calculate solar curve - peaks at noon (12:00)
        // Using a sine curve approximation for realistic solar production
        $normalizedHour = ($hour - 6) / 14; // Normalize to 0-1 range for 6am-8pm

        // Bell curve using sine - peaks at noon
        $curve = sin($normalizedHour * M_PI);

        // Apply efficiency factor (typically 70-85% of theoretical max)
        $efficiency = 0.7 + (mt_rand(30, 130) / 1000); // 70-83% efficiency with variation

        // Weather simulation - add some randomness
        $weatherFactor = mt_rand(70, 100) / 100; // 70-100% weather efficiency

        // Calculate kWh for this hour
        $production = $totalCapacity * $curve * $efficiency * $weatherFactor;

        // Add micro-fluctuations (±5%) for realism
        $fluctuation = 1 + (mt_rand(-50, 50) / 1000);
        $production *= $fluctuation;

        return max(0, round($production, 2));
    }

    /**
     * Generate simulated daily production for a given date
     */
    private function generateSimulatedDailyProduction(Carbon $date, float $totalCapacity): float
    {
        if ($totalCapacity == 0) {
            return 0;
        }

        // Month-based seasonal factor (higher in summer months)
        $month = $date->month;
        $seasonalFactor = match ($month) {
            1 => 0.6,   // January - lowest
            2 => 0.7,
            3 => 0.8,
            4 => 0.9,
            5 => 1.0,
            6 => 1.1,   // June - peak summer
            7 => 1.1,
            8 => 1.0,
            9 => 0.9,
            10 => 0.8,
            11 => 0.7,
            12 => 0.6,  // December - lowest
            default => 0.8,
        };

        // Weather factor - randomly determine weather for the day
        $weatherRand = mt_rand(1, 100);
        $weatherFactor = match (true) {
            $weatherRand <= 50 => 1.0,    // Sunny - 50%
            $weatherRand <= 75 => 0.75,   // Partly cloudy - 25%
            $weatherRand <= 90 => 0.45,  // Cloudy - 15%
            default => 0.20,              // Rainy - 10%
        };

        // Base daily production (assuming ~10 hours of sunlight)
        $baseProduction = $totalCapacity * 10 * 0.8; // 80% average efficiency

        // Apply factors
        $production = $baseProduction * $seasonalFactor * $weatherFactor;

        // Add daily fluctuation (±10%)
        $fluctuation = 1 + (mt_rand(-100, 100) / 1000);
        $production *= $fluctuation;

        return max(0, round($production, 2));
    }

    /**
     * Generate simulated monthly production for a given month
     */
    private function generateSimulatedMonthlyProduction(int $month, float $totalCapacity): float
    {
        if ($totalCapacity == 0) {
            return 0;
        }

        // Month-based seasonal factor (higher in summer months)
        $seasonalFactor = match ($month) {
            1 => 0.6,   // January - lowest
            2 => 0.7,
            3 => 0.8,
            4 => 0.9,
            5 => 1.0,
            6 => 1.1,   // June - peak summer
            7 => 1.1,
            8 => 1.0,
            9 => 0.9,
            10 => 0.8,
            11 => 0.7,
            12 => 0.6,  // December - lowest
            default => 0.8,
        };

        // Number of days in each month for 2026
        $daysInMonth = match ($month) {
            1 => 31, 2 => 28, 3 => 31, 4 => 30, 5 => 31, 6 => 30,
            7 => 31, 8 => 31, 9 => 30, 10 => 31, 11 => 30, 12 => 31,
            default => 30,
        };

        // Base monthly production
        $baseProduction = $totalCapacity * 10 * 0.8 * $daysInMonth; // 10 hours/day, 80% efficiency

        // Apply seasonal factor
        $production = $baseProduction * $seasonalFactor;

        // Add monthly fluctuation (±5%)
        $fluctuation = 1 + (mt_rand(-50, 50) / 1000);
        $production *= $fluctuation;

        return max(0, round($production, 2));
    }

    /**
     * Get real-time weather data affecting solar production
     */
    public function realtimeWeather()
    {
        $user = Auth::user();
        $systems = $user->solarSystems;

        // Get weather conditions from production records
        $weatherData = Production::whereIn('solar_system_id', $systems->pluck('id'))
            ->whereDate('production_date', today())
            ->selectRaw('weather_condition, AVG(irradiance_wm2) as avg_irradiance, AVG(temperature_celsius) as avg_temp, COUNT(*) as count')
            ->groupBy('weather_condition')
            ->get();

        // Get current weather based on latest production record
        $latestProduction = Production::whereIn('solar_system_id', $systems->pluck('id'))
            ->whereDate('production_date', today())
            ->latest('production_time')
            ->first();

        $currentWeather = $latestProduction ? [
            'condition' => $latestProduction->weather_condition ?? 'sunny',
            'irradiance' => round($latestProduction->irradiance_wm2 ?? 800, 2),
            'temperature' => round($latestProduction->temperature_celsius ?? 25, 2),
        ] : [
            'condition' => 'sunny',
            'irradiance' => 800,
            'temperature' => 25,
        ];

        // Weather impact on production
        $weatherImpact = [
            'sunny' => ['icon' => 'sun', 'color' => 'warning', 'impact' => 'Optimal', 'efficiency' => 100],
            'partly_cloudy' => ['icon' => 'cloud-sun', 'color' => 'info', 'impact' => 'Good', 'efficiency' => 75],
            'cloudy' => ['icon' => 'cloud', 'color' => 'secondary', 'impact' => 'Reduced', 'efficiency' => 45],
            'rainy' => ['icon' => 'cloud-rain', 'color' => 'primary', 'impact' => 'Low', 'efficiency' => 20],
        ];

        $currentImpact = $weatherImpact[$currentWeather['condition']] ?? $weatherImpact['sunny'];

        return response()->json([
            'success' => true,
            'data' => [
                'current' => array_merge($currentWeather, $currentImpact),
                'history' => $weatherData,
                'forecast' => $this->generateWeatherForecast(),
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Generate weather forecast for next hours
     */
    private function generateWeatherForecast(): array
    {
        $forecast = [];
        $conditions = ['sunny', 'partly_cloudy', 'cloudy', 'rainy'];
        $currentHour = now()->hour;

        for ($i = 0; $i < 6; $i++) {
            $hour = ($currentHour + $i) % 24;
            $forecast[] = [
                'hour' => sprintf('%02d:00', $hour),
                'condition' => $conditions[array_rand($conditions)],
                'temperature' => rand(20, 35),
                'irradiance' => rand(200, 1000),
            ];
        }

        return $forecast;
    }

    /**
     * Get current system status
     */
    public function systemStatus()
    {
        $user = Auth::user();
        $systems = $user->solarSystems;

        $status = [
            'total_systems' => $systems->count(),
            'active_systems' => $systems->where('status', 'active')->count(),
            'total_panels' => $systems->sum(fn ($s) => $s->panels->count()),
            'active_panels' => $systems->sum(fn ($s) => $s->activePanelsCount()),
            'today_production' => $systems->sum(fn ($s) => $s->todayProduction()),
            'active_alerts' => \App\Models\Alert::whereIn('solar_system_id', $systems->pluck('id'))
                ->where('status', 'active')
                ->count(),
            'efficiency' => $systems->count() > 0
                ? round($systems->avg(fn ($s) => $s->calculateEfficiency()), 2)
                : 0,
            'timestamp' => now()->toIso8601String(),
        ];

        return response()->json([
            'success' => true,
            'data' => $status,
        ]);
    }

    /**
     * Simulate real-time data update (for demo purposes)
     */
    public function simulateRealtimeData()
    {
        $user = Auth::user();
        $systems = $user->solarSystems;

        // Determine weather condition based on random factor
        $weatherConditions = ['sunny', 'sunny', 'sunny', 'partly_cloudy', 'partly_cloudy', 'cloudy', 'rainy'];
        $weatherCondition = $weatherConditions[array_rand($weatherConditions)];

        // Weather impact on production (0.0 to 1.0)
        $weatherImpact = [
            'sunny' => 1.0,
            'partly_cloudy' => 0.75,
            'cloudy' => 0.45,
            'rainy' => 0.20,
        ];

        $impact = $weatherImpact[$weatherCondition];

        // Current hour for simulation
        $currentHour = now()->hour;

        foreach ($systems as $system) {
            $totalCapacity = $system->total_capacity_kw;

            // Generate simulated hourly data for all hours with fluctuations
            for ($hour = 0; $hour < 24; $hour++) {
                // Generate realistic hourly production with variation
                $hourlyProduction = $this->generateSimulatedHourlyProduction($hour, $totalCapacity);

                // Add additional fluctuation based on current time (recent hours more variable)
                if (abs($hour - $currentHour) <= 2) {
                    // More fluctuation for current and nearby hours
                    $fluctuation = 0.8 + (mt_rand(0, 400) / 1000); // 0.8 - 1.2
                } else {
                    $fluctuation = 0.9 + (mt_rand(0, 200) / 1000); // 0.9 - 1.1
                }

                $hourlyProduction *= $fluctuation;

                Production::updateOrCreate(
                    [
                        'solar_system_id' => $system->id,
                        'production_date' => today(),
                        'production_time' => sprintf('%02d:00:00', $hour),
                    ],
                    [
                        'energy_produced_kwh' => max(0, $hourlyProduction),
                        'energy_consumed_kwh' => 0,
                        'peak_power_kw' => max(0, $hourlyProduction * 1.2),
                        'average_power_kw' => max(0, $hourlyProduction * 0.8),
                        'weather_condition' => $weatherCondition,
                        'irradiance_wm2' => match ($weatherCondition) {
                            'sunny' => rand(800, 1000),
                            'partly_cloudy' => rand(500, 800),
                            'cloudy' => rand(200, 500),
                            'rainy' => rand(0, 200),
                            default => 800,
                        },
                        'temperature_celsius' => rand(20, 40),
                    ]
                );
            }

            // Update panel readings for current hour
            foreach ($system->panels as $panel) {
                $baseOutput = ($panel->capacity_watts / 1000) * 0.8;

                if ($currentHour >= 10 && $currentHour <= 15) {
                    $timeMultiplier = 0.9 + (rand(0, 20) / 100);
                } elseif ($currentHour >= 6 && $currentHour <= 18) {
                    $timeMultiplier = 0.4 + (rand(0, 40) / 100);
                } else {
                    $timeMultiplier = 0;
                }

                $powerOutput = $baseOutput * $timeMultiplier * $impact * (0.95 + (rand(0, 10) / 100));
                $voltage = 30 + (rand(0, 100) / 10);
                $amperage = $powerOutput > 0 ? ($powerOutput * 1000) / $voltage : 0;

                $panel->update([
                    'current_power_output' => max(0, $powerOutput),
                    'current_voltage' => $voltage,
                    'current_amperage' => $amperage,
                    'last_reading_at' => now(),
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Real-time data updated',
            'weather' => $weatherCondition,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
