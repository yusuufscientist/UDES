<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Production;
use App\Models\SolarSystem;
use Illuminate\Http\Request;

class ProductionController extends Controller
{
    /**
     * Display a listing of production records
     */
    public function index(SolarSystem $solarSystem)
    {
        $this->authorize('view', $solarSystem);

        $productions = $solarSystem->productions()
            ->with('panel')
            ->latest()
            ->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $productions,
        ]);
    }

    /**
     * Store a newly created production record
     */
    public function store(Request $request, SolarSystem $solarSystem)
    {
        $this->authorize('update', $solarSystem);

        $validated = $request->validate([
            'panel_id' => ['nullable', 'exists:panels,id'],
            'production_date' => ['required', 'date'],
            'production_time' => ['nullable'],
            'energy_produced_kwh' => ['required', 'numeric', 'min:0'],
            'energy_consumed_kwh' => ['nullable', 'numeric', 'min:0'],
            'peak_power_kw' => ['nullable', 'numeric', 'min:0'],
            'average_power_kw' => ['nullable', 'numeric', 'min:0'],
            'irradiance_wm2' => ['nullable', 'numeric', 'min:0'],
            'temperature_celsius' => ['nullable', 'numeric'],
            'weather_condition' => ['nullable', 'in:sunny,cloudy,rainy,partly_cloudy'],
        ]);

        $validated['solar_system_id'] = $solarSystem->id;

        $production = Production::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Production record created successfully',
            'data' => $production,
        ], 201);
    }

    /**
     * Display the specified production record
     */
    public function show(SolarSystem $solarSystem, Production $production)
    {
        $this->authorize('view', $solarSystem);

        $production->load('panel');

        return response()->json([
            'success' => true,
            'data' => $production,
        ]);
    }

    /**
     * Update the specified production record
     */
    public function update(Request $request, SolarSystem $solarSystem, Production $production)
    {
        $this->authorize('update', $solarSystem);

        $validated = $request->validate([
            'panel_id' => ['nullable', 'exists:panels,id'],
            'production_date' => ['required', 'date'],
            'energy_produced_kwh' => ['required', 'numeric', 'min:0'],
            'energy_consumed_kwh' => ['nullable', 'numeric', 'min:0'],
            'peak_power_kw' => ['nullable', 'numeric', 'min:0'],
            'average_power_kw' => ['nullable', 'numeric', 'min:0'],
            'irradiance_wm2' => ['nullable', 'numeric', 'min:0'],
            'temperature_celsius' => ['nullable', 'numeric'],
            'weather_condition' => ['nullable', 'in:sunny,cloudy,rainy,partly_cloudy'],
        ]);

        $production->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Production record updated successfully',
            'data' => $production,
        ]);
    }

    /**
     * Remove the specified production record
     */
    public function destroy(SolarSystem $solarSystem, Production $production)
    {
        $this->authorize('update', $solarSystem);

        $production->delete();

        return response()->json([
            'success' => true,
            'message' => 'Production record deleted successfully',
        ]);
    }

    /**
     * Get production statistics
     */
    public function statistics(SolarSystem $solarSystem, Request $request)
    {
        $this->authorize('view', $solarSystem);

        $period = $request->get('period', 'month'); // day, week, month, year

        $query = $solarSystem->productions();

        switch ($period) {
            case 'day':
                $query->whereDate('production_date', today());
                break;
            case 'week':
                $query->whereBetween('production_date', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth('production_date', now()->month)
                    ->whereYear('production_date', now()->year);
                break;
            case 'year':
                $query->whereYear('production_date', now()->year);
                break;
        }

        $stats = [
            'total_produced' => $query->sum('energy_produced_kwh'),
            'total_consumed' => $query->sum('energy_consumed_kwh'),
            'average_production' => $query->avg('energy_produced_kwh'),
            'peak_production' => $query->max('energy_produced_kwh'),
            'record_count' => $query->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get chart data for production
     */
    public function chartData(SolarSystem $solarSystem, Request $request)
    {
        $this->authorize('view', $solarSystem);

        $period = $request->get('period', 'week');
        $labels = [];
        $production = [];
        $consumption = [];

        switch ($period) {
            case 'week':
                for ($i = 6; $i >= 0; $i--) {
                    $date = now()->subDays($i);
                    $labels[] = $date->format('D');

                    $dailyData = Production::where('solar_system_id', $solarSystem->id)
                        ->whereDate('production_date', $date)
                        ->selectRaw('SUM(energy_produced_kwh) as produced, SUM(energy_consumed_kwh) as consumed')
                        ->first();

                    $production[] = round($dailyData->produced ?? 0, 2);
                    $consumption[] = round($dailyData->consumed ?? 0, 2);
                }
                break;

            case 'month':
                for ($i = 29; $i >= 0; $i--) {
                    $date = now()->subDays($i);
                    $labels[] = $date->format('M d');

                    $dailyData = Production::where('solar_system_id', $solarSystem->id)
                        ->whereDate('production_date', $date)
                        ->selectRaw('SUM(energy_produced_kwh) as produced, SUM(energy_consumed_kwh) as consumed')
                        ->first();

                    $production[] = round($dailyData->produced ?? 0, 2);
                    $consumption[] = round($dailyData->consumed ?? 0, 2);
                }
                break;

            case 'year':
                for ($i = 1; $i <= 12; $i++) {
                    $labels[] = \Carbon\Carbon::create()->month($i)->format('M');

                    $monthlyData = Production::where('solar_system_id', $solarSystem->id)
                        ->whereYear('production_date', now()->year)
                        ->whereMonth('production_date', $i)
                        ->selectRaw('SUM(energy_produced_kwh) as produced, SUM(energy_consumed_kwh) as consumed')
                        ->first();

                    $production[] = round($monthlyData->produced ?? 0, 2);
                    $consumption[] = round($monthlyData->consumed ?? 0, 2);
                }
                break;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'labels' => $labels,
                'production' => $production,
                'consumption' => $consumption,
            ],
        ]);
    }
}
