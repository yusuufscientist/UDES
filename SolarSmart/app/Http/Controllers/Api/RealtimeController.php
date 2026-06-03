<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Production;
use App\Models\SolarSystem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class RealtimeController extends Controller
{
    protected function currentUser()
    {
        return Auth::user() ?? User::first();
    }

    protected function currentSystemIds()
    {
        $user = $this->currentUser();
        return $user ? $user->solarSystems()->pluck('id') : collect();
    }

    public function realtimeProduction()
    {
        $systemIds = $this->currentSystemIds();

        $productions = Production::whereIn('solar_system_id', $systemIds)
            ->whereDate('production_date', today())
            ->selectRaw('production_time, SUM(energy_produced_kwh) as energy')
            ->groupBy('production_time')
            ->orderBy('production_time')
            ->get()
            ->keyBy(fn ($item) => substr($item->production_time, 0, 2));

        $labels = [];
        $data = [];
        $totalCapacity = SolarSystem::whereIn('id', $systemIds)->sum('total_capacity_kw');

        for ($i = 0; $i < 24; $i++) {
            $labels[] = sprintf('%02d:00', $i);
            if ($productions->has($i)) {
                $data[] = round($productions->get($i)->energy ?? 0, 2);
            } else {
                $data[] = round($this->generateSimulatedHourlyProduction($i, $totalCapacity), 2);
            }
        }

        $currentHour = now()->hour;
        $currentProduction = 0;
        if ($totalCapacity > 0) {
            $currentProduction = $this->generateSimulatedHourlyProduction($currentHour, $totalCapacity);
        }

        $monthlyLabels = [];
        $monthlyData = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthlyLabels[] = Carbon::create()->month($i)->format('M');
            $monthlyData[] = round(0, 2);
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

    public function generateAndGetRealtimeData()
    {
        $user = $this->currentUser();
        $systems = $user ? SolarSystem::where('user_id', $user->id)->get() : collect();
        $totalCapacity = $systems->sum('total_capacity_kw') ?: 50;

        $weeklyLabels = [];
        $weeklyData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $weeklyLabels[] = $date->format('M d');
            $weeklyData[] = round($this->generateSimulatedDailyProduction($date, $totalCapacity), 2);
        }

        $monthlyLabels = [];
        $monthlyData = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthlyLabels[] = Carbon::create(2026, $month)->format('M');
            $monthlyData[] = round($this->generateSimulatedMonthlyProduction($month, $totalCapacity), 2);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'labels' => $weeklyLabels,
                'production' => $weeklyData,
                'monthly_labels' => $monthlyLabels,
                'monthly_production' => $monthlyData,
                'current_hour' => now()->hour,
                'current_production' => round(($totalCapacity / 12) * 0.8, 2),
                'total_today' => round($this->generateSimulatedDailyProduction(now(), $totalCapacity), 2),
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }

    private function generateSimulatedHourlyProduction(int $hour, float $totalCapacity): float
    {
        if ($totalCapacity == 0 || $hour < 6 || $hour > 20) {
            return 0;
        }
        $normalizedHour = ($hour - 6) / 14;
        $curve = sin($normalizedHour * M_PI);
        return max(0, round($totalCapacity * $curve * 0.75, 2));
    }

    private function generateSimulatedDailyProduction(Carbon $date, float $totalCapacity): float
    {
        if ($totalCapacity == 0) return 0;
        return round($totalCapacity * 8 * 0.8 * (0.8 + mt_rand(0, 40) / 100), 2);
    }

    private function generateSimulatedMonthlyProduction(int $month, float $totalCapacity): float
    {
        if ($totalCapacity == 0) return 0;
        return round($totalCapacity * 10 * 0.8 * 30, 2);
    }

    public function realtimeWeather()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'current' => [
                    'condition' => 'sunny',
                    'temperature' => 25,
                    'irradiance' => 800,
                ],
                'history' => [],
                'forecast' => [],
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }

    public function livePanelReadings()
    {
        $user = $this->currentUser();
        $systems = $user ? SolarSystem::where('user_id', $user->id)->get() : collect();
        
        return response()->json([
            'success' => true,
            'data' => [],
        ]);
    }

    public function systemStatus()
    {
        $user = $this->currentUser();
        $systems = $user ? SolarSystem::where('user_id', $user->id)->get() : collect();

        return response()->json([
            'success' => true,
            'data' => [
                'total_systems' => $systems->count(),
                'active_systems' => $systems->where('status', 'active')->count(),
                'active_alerts' => 0,
                'efficiency' => 0,
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }

    public function simulateRealtimeData()
    {
        $user = $this->currentUser();
        $systems = $user ? SolarSystem::where('user_id', $user->id)->get() : collect();

        return response()->json([
            'success' => true,
            'message' => 'Real-time data updated',
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}

