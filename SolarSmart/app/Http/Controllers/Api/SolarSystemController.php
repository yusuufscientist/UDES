<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SolarSystem;
use App\Services\ProductionCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SolarSystemController extends Controller
{
    protected $calculator;

    public function __construct(ProductionCalculator $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * Display a listing of solar systems
     */
    public function index()
    {
        $systems = Auth::user()->solarSystems()->withCount(['panels', 'alerts'])->get();

        return response()->json([
            'success' => true,
            'data' => $systems,
        ]);
    }

    /**
     * Store a newly created solar system
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'total_capacity_kw' => ['required', 'numeric', 'min:0.1'],
            'installation_date' => ['required', 'date', 'before_or_equal:today'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['user_id'] = Auth::id();
        $validated['status'] = 'active';

        $system = SolarSystem::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Solar system created successfully',
            'data' => $system,
        ], 201);
    }

    /**
     * Display the specified solar system
     */
    public function show(SolarSystem $solarSystem)
    {
        $this->authorize('view', $solarSystem);

        $solarSystem->load(['panels', 'alerts' => function ($query) {
            $query->where('status', 'active');
        }]);

        // Add calculated statistics
        $solarSystem->stats = [
            'today_production' => $solarSystem->todayProduction(),
            'month_production' => $solarSystem->monthProduction(),
            'efficiency' => $this->calculator->calculateSystemEfficiency($solarSystem),
            'active_panels' => $solarSystem->activePanelsCount(),
            'active_alerts' => $solarSystem->activeAlertsCount(),
        ];

        return response()->json([
            'success' => true,
            'data' => $solarSystem,
        ]);
    }

    /**
     * Update the specified solar system
     */
    public function update(Request $request, SolarSystem $solarSystem)
    {
        $this->authorize('update', $solarSystem);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'total_capacity_kw' => ['required', 'numeric', 'min:0.1'],
            'status' => ['required', 'in:active,inactive,maintenance'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $solarSystem->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Solar system updated successfully',
            'data' => $solarSystem,
        ]);
    }

    /**
     * Remove the specified solar system
     */
    public function destroy(SolarSystem $solarSystem)
    {
        $this->authorize('delete', $solarSystem);

        $solarSystem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Solar system deleted successfully',
        ]);
    }

    /**
     * Get production summary for a solar system
     */
    public function productionSummary(SolarSystem $solarSystem, Request $request)
    {
        $this->authorize('view', $solarSystem);

        $startDate = $request->get('start_date')
            ? \Carbon\Carbon::parse($request->get('start_date'))
            : now()->subDays(30);

        $endDate = $request->get('end_date')
            ? \Carbon\Carbon::parse($request->get('end_date'))
            : now();

        $summary = $this->calculator->getProductionSummary($solarSystem, $startDate, $endDate);

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }

    /**
     * Get production trend for a solar system
     */
    public function productionTrend(SolarSystem $solarSystem, Request $request)
    {
        $this->authorize('view', $solarSystem);

        $period = $request->get('period', 'day');
        $trend = $this->calculator->getProductionTrend($solarSystem, $period);

        return response()->json([
            'success' => true,
            'data' => $trend,
        ]);
    }
}
