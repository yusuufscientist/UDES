<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;

class WeatherController extends Controller
{
    /**
     * Display the weather dashboard.
     */
    public function index()
    {
        $weatherData = $this->getWeatherData();

        return view('weather.index', compact('weatherData'));
    }

    /**
     * Get real-time weather data via API.
     */
    public function getWeather(Request $request)
    {
        $weatherData = $this->getWeatherData();

        return response()->json([
            'success' => true,
            'data' => $weatherData,
        ]);
    }

    /**
     * Get simulated weather data for the dashboard.
     * In production, this would connect to a weather API like OpenWeatherMap.
     */
    private function getWeatherData()
    {
        $hour = Carbon::now()->hour;

        // Simulate realistic weather conditions based on time of day
        $baseTemp = 25; // Base temperature
        $tempVariation = sin(($hour - 6) * pi() / 12) * 10; // Temperature curve throughout the day

        // Calculate solar irradiance based on time (peak at noon)
        $solarIrradiance = max(0, sin(($hour - 6) * pi() / 12) * 1000); // W/m²

        // Determine weather condition based on simulated data
        $conditions = $this->getCurrentConditions($hour);

        // Calculate cloud cover impact on solar production
        $cloudImpact = $this->calculateCloudImpact($conditions['cloud_cover']);

        // Calculate solar production impact
        $productionImpact = $this->calculateProductionImpact($solarIrradiance, $conditions['cloud_cover'], $conditions['humidity']);

        return [
            'temperature' => round($baseTemp + $tempVariation + rand(-2, 2), 1),
            'feels_like' => round($baseTemp + $tempVariation + rand(-3, 3), 1),
            'humidity' => $conditions['humidity'],
            'wind_speed' => round(rand(5, 25) + ($hour > 12 ? $hour - 12 : 0), 1),
            'wind_direction' => $this->getWindDirection(),
            'pressure' => rand(1010, 1025),
            'uv_index' => $this->getUVIndex($hour, $conditions['cloud_cover']),
            'visibility' => $this->getVisibility($conditions['cloud_cover']),
            'cloud_cover' => $conditions['cloud_cover'],
            'condition' => $conditions['condition'],
            'condition_icon' => $conditions['icon'],
            'solar_irradiance' => round($solarIrradiance * (1 - $conditions['cloud_cover'] / 100), 0),
            'production_impact' => $productionImpact,
            'is_day' => $hour >= 6 && $hour <= 18,
            'sunrise' => '06:15',
            'sunset' => '18:45',
            'last_updated' => Carbon::now()->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get current weather conditions.
     */
    private function getCurrentConditions($hour)
    {
        // Simulate different conditions
        $conditionsList = [
            ['condition' => 'Clear', 'icon' => 'bi-sun-fill', 'cloud_cover' => 10, 'humidity' => 45],
            ['condition' => 'Partly Cloudy', 'icon' => 'bi-cloud-sun-fill', 'cloud_cover' => 35, 'humidity' => 55],
            ['condition' => 'Cloudy', 'icon' => 'bi-cloud-fill', 'cloud_cover' => 70, 'humidity' => 65],
            ['condition' => 'Overcast', 'icon' => 'bi-cloud-fill', 'cloud_cover' => 90, 'humidity' => 75],
            ['condition' => 'Light Rain', 'icon' => 'bi-cloud-rain-fill', 'cloud_cover' => 85, 'humidity' => 85],
        ];

        // Use hour to deterministically select a condition (for demo purposes)
        $index = $hour % count($conditionsList);

        // Add some randomness
        if (rand(0, 10) > 7) {
            $index = rand(0, count($conditionsList) - 1);
        }

        return $conditionsList[$index];
    }

    /**
     * Calculate cloud impact on solar production.
     */
    private function calculateCloudImpact($cloudCover)
    {
        // More clouds = less production
        return round(100 - ($cloudCover * 0.9), 0);
    }

    /**
     * Calculate solar production impact based on weather conditions.
     */
    private function calculateProductionImpact($irradiance, $cloudCover, $humidity)
    {
        // Base efficiency
        $efficiency = 0.85;

        // Cloud cover reduces efficiency
        $efficiency *= (1 - ($cloudCover / 100) * 0.7);

        // High humidity slightly reduces efficiency
        if ($humidity > 70) {
            $efficiency *= 0.95;
        }

        // Calculate expected production (kWh for current hour)
        // Assuming 5kW system
        $expectedProduction = ($irradiance / 1000) * 5 * $efficiency;

        return [
            'efficiency' => round($efficiency * 100, 0),
            'expected_kwh' => round($expectedProduction, 2),
            'status' => $efficiency > 0.7 ? 'optimal' : ($efficiency > 0.4 ? 'moderate' : 'low'),
            'message' => $this->getProductionMessage($efficiency),
        ];
    }

    /**
     * Get production status message.
     */
    private function getProductionMessage($efficiency)
    {
        if ($efficiency > 0.75) {
            return 'Excellent conditions for solar production!';
        } elseif ($efficiency > 0.5) {
            return 'Good conditions for solar production.';
        } elseif ($efficiency > 0.25) {
            return 'Moderate conditions - production reduced due to clouds.';
        } else {
            return 'Low production - consider battery backup.';
        }
    }

    /**
     * Get random wind direction.
     */
    private function getWindDirection()
    {
        $directions = ['N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW'];

        return $directions[array_rand($directions)];
    }

    /**
     * Calculate UV index based on time and cloud cover.
     */
    private function getUVIndex($hour, $cloudCover)
    {
        // Peak UV at noon
        $baseUV = max(0, 11 - abs($hour - 12));

        // Reduce based on cloud cover
        $uv = round($baseUV * (1 - $cloudCover / 150));

        return $uv;
    }

    /**
     * Calculate visibility based on conditions.
     */
    private function getVisibility($cloudCover)
    {
        if ($cloudCover < 30) {
            return 'Excellent';
        } elseif ($cloudCover < 60) {
            return 'Good';
        } elseif ($cloudCover < 85) {
            return 'Moderate';
        } else {
            return 'Poor';
        }
    }
}
