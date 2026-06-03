<?php

namespace App\Http\Controllers;

use App\Models\Production;
use App\Models\SolarSystem;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ProductionController extends Controller
{
    /**
     * Display production records for a solar system
     */
    public function index(SolarSystem $solarSystem)
    {
        $productions = $solarSystem->productions()
            ->with('panel')
            ->latest()
            ->paginate(30);

        $stats = [
            'total_production' => $solarSystem->productions()->sum('energy_produced_kwh'),
            'avg_daily' => $solarSystem->productions()->avg('energy_produced_kwh'),
            'max_daily' => $solarSystem->productions()->max('energy_produced_kwh'),
            'total_consumption' => $solarSystem->productions()->sum('energy_consumed_kwh'),
        ];

        return view('productions.index', compact('solarSystem', 'productions', 'stats'));
    }

    public function create(SolarSystem $solarSystem)
    {
        $panels = $solarSystem->panels()->where('status', 'active')->get();

        return view('productions.create', compact('solarSystem', 'panels'));
    }

    public function store(Request $request, SolarSystem $solarSystem)
    {

        $validated = $request->validate([
            'panel_id' => ['nullable', 'exists:panels,id'],
            'production_date' => ['required', 'date', 'before_or_equal:today'],
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

        // Calculate efficiency if not provided
        if (! isset($validated['efficiency_percentage'])) {
            $validated['efficiency_percentage'] = $this->calculateEfficiency(
                $validated['energy_produced_kwh'],
                $solarSystem->total_capacity_kw
            );
        }

        $production = Production::create($validated);

        return redirect()->route('solar-systems.productions.index', $solarSystem)
            ->with('success', 'Production record added successfully.');
    }

    /**
     * Display the specified production record
     */
    public function show(SolarSystem $solarSystem, Production $production)
    {
        return view('productions.show', compact('solarSystem', 'production'));
    }

    public function edit(SolarSystem $solarSystem, Production $production)
    {
        $panels = $solarSystem->panels()->where('status', 'active')->get();

        return view('productions.edit', compact('solarSystem', 'production', 'panels'));
    }

    public function update(Request $request, SolarSystem $solarSystem, Production $production)
    {

        $validated = $request->validate([
            'panel_id' => ['nullable', 'exists:panels,id'],
            'production_date' => ['required', 'date', 'before_or_equal:today'],
            'production_time' => ['nullable'],
            'energy_produced_kwh' => ['required', 'numeric', 'min:0'],
            'energy_consumed_kwh' => ['nullable', 'numeric', 'min:0'],
            'peak_power_kw' => ['nullable', 'numeric', 'min:0'],
            'average_power_kw' => ['nullable', 'numeric', 'min:0'],
            'irradiance_wm2' => ['nullable', 'numeric', 'min:0'],
            'temperature_celsius' => ['nullable', 'numeric'],
            'weather_condition' => ['nullable', 'in:sunny,cloudy,rainy,partly_cloudy'],
        ]);

        $production->update($validated);

        return redirect()->route('solar-systems.productions.show', [$solarSystem, $production])
            ->with('success', 'Production record updated successfully.');
    }

    /**
     * Remove the specified production record
     */
    public function destroy(SolarSystem $solarSystem, Production $production)
    {
        $production->delete();

        return redirect()->route('solar-systems.productions.index', $solarSystem)
            ->with('success', 'Production record deleted successfully.');
    }

    public function chartData(SolarSystem $solarSystem, Request $request)
    {
        $period = $request->get('period', 'week'); // week, month, year

        $data = match ($period) {
            'week' => $this->getWeeklyData($solarSystem),
            'month' => $this->getMonthlyData($solarSystem),
            'year' => $this->getYearlyData($solarSystem),
            default => $this->getWeeklyData($solarSystem),
        };

        return response()->json($data);
    }

    /**
     * Get weekly production data
     */
    private function getWeeklyData(SolarSystem $solarSystem): array
    {
        $labels = [];
        $production = [];
        $consumption = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $labels[] = $date->format('D');

            $dailyData = Production::where('solar_system_id', $solarSystem->id)
                ->whereDate('production_date', $date)
                ->selectRaw('SUM(energy_produced_kwh) as produced, SUM(energy_consumed_kwh) as consumed')
                ->first();

            $production[] = round($dailyData->produced ?? 0, 2);
            $consumption[] = round($dailyData->consumed ?? 0, 2);
        }

        return compact('labels', 'production', 'consumption');
    }

    /**
     * Get monthly production data
     */
    private function getMonthlyData(SolarSystem $solarSystem): array
    {
        $labels = [];
        $production = [];
        $consumption = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $labels[] = $date->format('M d');

            $dailyData = Production::where('solar_system_id', $solarSystem->id)
                ->whereDate('production_date', $date)
                ->selectRaw('SUM(energy_produced_kwh) as produced, SUM(energy_consumed_kwh) as consumed')
                ->first();

            $production[] = round($dailyData->produced ?? 0, 2);
            $consumption[] = round($dailyData->consumed ?? 0, 2);
        }

        return compact('labels', 'production', 'consumption');
    }

    /**
     * Get yearly production data
     */
    private function getYearlyData(SolarSystem $solarSystem): array
    {
        $labels = [];
        $production = [];
        $consumption = [];

        for ($i = 1; $i <= 12; $i++) {
            $labels[] = Carbon::create()->month($i)->format('M');

            $monthlyData = Production::where('solar_system_id', $solarSystem->id)
                ->whereYear('production_date', now()->year)
                ->whereMonth('production_date', $i)
                ->selectRaw('SUM(energy_produced_kwh) as produced, SUM(energy_consumed_kwh) as consumed')
                ->first();

            $production[] = round($monthlyData->produced ?? 0, 2);
            $consumption[] = round($monthlyData->consumed ?? 0, 2);
        }

        return compact('labels', 'production', 'consumption');
    }

    /**
     * Calculate efficiency percentage
     */
    private function calculateEfficiency(float $energyProduced, float $capacityKw): float
    {
        $expectedProduction = $capacityKw * 5; // 5 peak sun hours

        return $expectedProduction > 0 ? ($energyProduced / $expectedProduction) * 100 : 0;
    }
}
