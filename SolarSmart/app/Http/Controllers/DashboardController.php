<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Production;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the main dashboard
     */
    public function index()
    {
        // Use a default user if not authenticated
        $user = Auth::check() ? Auth::user() : \App\Models\User::where('email', 'fcyusuuf@gmail.com')->first();
        
        if (!$user) {
            $user = \App\Models\User::first();
        }

        // Get user's solar systems
        $solarSystems = $user->solarSystems()->with(['panels', 'productions', 'alerts'])->get();
        $solarSystem = $solarSystems->first();

        // Calculate summary statistics
        $totalSystems = $solarSystems->count();
        $totalPanels = $solarSystems->sum(fn ($system) => $system->panels->count());
        $activePanels = $solarSystems->sum(fn ($system) => $system->activePanelsCount());
        $totalCapacity = $solarSystems->sum('total_capacity_kw');

        // Get today's production
        $todayProduction = $solarSystems->sum(fn ($system) => $system->todayProduction());

        // Get this month's production
        $monthProduction = $solarSystems->sum(fn ($system) => $system->monthProduction());

        // Get active alerts
        $activeAlerts = Alert::whereIn('solar_system_id', $solarSystems->pluck('id'))
            ->where('status', 'active')
            ->with(['solarSystem', 'panel'])
            ->orderBy('triggered_at', 'desc')
            ->take(5)
            ->get();

        // Get production data for charts (last 7 days)
        $productionChartData = $this->getProductionChartData($solarSystems);

        // Get monthly production data
        $monthlyChartData = $this->getMonthlyChartData($solarSystems);

        // Get weather data
        $weatherData = $this->getWeatherData();

        // Get productions for the table
        $productions = Production::whereIn('solar_system_id', $solarSystems->pluck('id'))
            ->with('solarSystem')
            ->orderBy('production_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dashboard.index', compact(
            'solarSystems',
            'solarSystem',
            'totalSystems',
            'totalPanels',
            'activePanels',
            'totalCapacity',
            'todayProduction',
            'monthProduction',
            'activeAlerts',
            'productionChartData',
            'monthlyChartData',
            'weatherData',
            'productions'
        ));
    }

    /**
     * Get production data for the last 7 days
     */
    private function getProductionChartData($solarSystems)
    {
        $systemIds = $solarSystems->pluck('id');
        $dates = [];
        $productions = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dates[] = $date->format('M d');

            $dailyProduction = Production::whereIn('solar_system_id', $systemIds)
                ->whereDate('production_date', $date)
                ->sum('energy_produced_kwh');

            $productions[] = round($dailyProduction, 2);
        }

        return [
            'labels' => $dates,
            'data' => $productions,
        ];
    }

    /**
     * Get monthly production data for the current year
     */
    private function getMonthlyChartData($solarSystems)
    {
        $systemIds = $solarSystems->pluck('id');
        $months = [];
        $productions = [];

        for ($i = 1; $i <= 12; $i++) {
            $months[] = Carbon::create()->month($i)->format('M');

            $monthlyProduction = Production::whereIn('solar_system_id', $systemIds)
                ->whereYear('production_date', now()->year)
                ->whereMonth('production_date', $i)
                ->sum('energy_produced_kwh');

            $productions[] = round($monthlyProduction, 2);
        }

        return [
            'labels' => $months,
            'data' => $productions,
        ];
    }

    /**
     * Get weather data for dashboard
     */
    private function getWeatherData()
    {
        $hour = Carbon::now()->hour;

        // Simulate realistic weather conditions based on time of day
        $baseTemp = 25;
        $tempVariation = sin(($hour - 6) * pi() / 12) * 10;
        $solarIrradiance = max(0, sin(($hour - 6) * pi() / 12) * 1000);

        // Determine weather condition
        $conditionsList = [
            ['condition' => 'Clear', 'icon' => 'bi-sun-fill', 'cloud_cover' => 10, 'humidity' => 45],
            ['condition' => 'Partly Cloudy', 'icon' => 'bi-cloud-sun-fill', 'cloud_cover' => 35, 'humidity' => 55],
            ['condition' => 'Cloudy', 'icon' => 'bi-cloud-fill', 'cloud_cover' => 70, 'humidity' => 65],
            ['condition' => 'Overcast', 'icon' => 'bi-cloud-fill', 'cloud_cover' => 90, 'humidity' => 75],
            ['condition' => 'Light Rain', 'icon' => 'bi-cloud-rain-fill', 'cloud_cover' => 85, 'humidity' => 85],
        ];

        $index = $hour % count($conditionsList);
        if (rand(0, 10) > 7) {
            $index = rand(0, count($conditionsList) - 1);
        }
        $conditions = $conditionsList[$index];

        // Calculate production impact
        $efficiency = 0.85 * (1 - ($conditions['cloud_cover'] / 100) * 0.7);
        if ($conditions['humidity'] > 70) {
            $efficiency *= 0.95;
        }

        return [
            'temperature' => round($baseTemp + $tempVariation + rand(-2, 2), 1),
            'feels_like' => round($baseTemp + $tempVariation + rand(-3, 3), 1),
            'humidity' => $conditions['humidity'],
            'wind_speed' => round(rand(5, 25) + ($hour > 12 ? $hour - 12 : 0), 1),
            'uv_index' => max(0, 11 - abs($hour - 12)) * (1 - $conditions['cloud_cover'] / 150),
            'cloud_cover' => $conditions['cloud_cover'],
            'condition' => $conditions['condition'],
            'condition_icon' => $conditions['icon'],
            'solar_irradiance' => round($solarIrradiance * (1 - $conditions['cloud_cover'] / 100), 0),
            'production_impact' => [
                'efficiency' => round($efficiency * 100, 0),
            ],
        ];
    }
}
