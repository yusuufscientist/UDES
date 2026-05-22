<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\SolarSystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlertController extends Controller
{
    /**
     * Display a listing of alerts
     */
    public function index()
    {
        $user = Auth::user();
        $systemIds = $user->solarSystems()->pluck('id');

        $alerts = Alert::whereIn('solar_system_id', $systemIds)
            ->with(['solarSystem', 'panel'])
            ->orderBy('triggered_at', 'desc')
            ->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $alerts,
        ]);
    }

    /**
     * Store a newly created alert
     */
    public function store(Request $request, SolarSystem $solarSystem)
    {
        $this->authorize('update', $solarSystem);

        $validated = $request->validate([
            'panel_id' => ['nullable', 'exists:panels,id'],
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:1000'],
            'type' => ['required', 'in:low_production,panel_fault,maintenance_due,system_offline,high_consumption,weather_warning'],
            'severity' => ['required', 'in:low,medium,high,critical'],
        ]);

        $validated['solar_system_id'] = $solarSystem->id;
        $validated['status'] = 'active';
        $validated['triggered_at'] = now();

        $alert = Alert::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Alert created successfully',
            'data' => $alert,
        ], 201);
    }

    /**
     * Display the specified alert
     */
    public function show(Alert $alert)
    {
        $this->authorize('view', $alert->solarSystem);

        $alert->load(['solarSystem', 'panel', 'acknowledgedBy']);

        return response()->json([
            'success' => true,
            'data' => $alert,
        ]);
    }

    /**
     * Update the specified alert
     */
    public function update(Request $request, Alert $alert)
    {
        $this->authorize('view', $alert->solarSystem);

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'message' => ['sometimes', 'string', 'max:1000'],
            'severity' => ['sometimes', 'in:low,medium,high,critical'],
            'status' => ['sometimes', 'in:active,acknowledged,resolved'],
            'resolution_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if (isset($validated['status'])) {
            if ($validated['status'] === 'acknowledged') {
                $validated['acknowledged_at'] = now();
                $validated['acknowledged_by'] = Auth::id();
            } elseif ($validated['status'] === 'resolved') {
                $validated['resolved_at'] = now();
            }
        }

        $alert->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Alert updated successfully',
            'data' => $alert,
        ]);
    }

    /**
     * Remove the specified alert
     */
    public function destroy(Alert $alert)
    {
        $this->authorize('view', $alert->solarSystem);

        $alert->delete();

        return response()->json([
            'success' => true,
            'message' => 'Alert deleted successfully',
        ]);
    }

    /**
     * Acknowledge an alert
     */
    public function acknowledge(Alert $alert)
    {
        $this->authorize('view', $alert->solarSystem);

        $alert->acknowledge(Auth::id());

        return response()->json([
            'success' => true,
            'message' => 'Alert acknowledged successfully',
            'data' => $alert,
        ]);
    }

    /**
     * Resolve an alert
     */
    public function resolve(Request $request, Alert $alert)
    {
        $this->authorize('view', $alert->solarSystem);

        $validated = $request->validate([
            'resolution_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $alert->resolve($validated['resolution_notes'] ?? null);

        return response()->json([
            'success' => true,
            'message' => 'Alert resolved successfully',
            'data' => $alert,
        ]);
    }

    /**
     * Get active alerts count
     */
    public function activeCount()
    {
        $user = Auth::user();
        $systemIds = $user->solarSystems()->pluck('id');

        $count = Alert::whereIn('solar_system_id', $systemIds)
            ->where('status', 'active')
            ->count();

        $criticalCount = Alert::whereIn('solar_system_id', $systemIds)
            ->where('status', 'active')
            ->where('severity', 'critical')
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_active' => $count,
                'critical' => $criticalCount,
            ],
        ]);
    }

    /**
     * Get alerts summary
     */
    public function summary()
    {
        $user = Auth::user();
        $systemIds = $user->solarSystems()->pluck('id');

        $summary = [
            'total' => Alert::whereIn('solar_system_id', $systemIds)->count(),
            'active' => Alert::whereIn('solar_system_id', $systemIds)->where('status', 'active')->count(),
            'acknowledged' => Alert::whereIn('solar_system_id', $systemIds)->where('status', 'acknowledged')->count(),
            'resolved' => Alert::whereIn('solar_system_id', $systemIds)->where('status', 'resolved')->count(),
            'by_severity' => [
                'critical' => Alert::whereIn('solar_system_id', $systemIds)->where('severity', 'critical')->where('status', 'active')->count(),
                'high' => Alert::whereIn('solar_system_id', $systemIds)->where('severity', 'high')->where('status', 'active')->count(),
                'medium' => Alert::whereIn('solar_system_id', $systemIds)->where('severity', 'medium')->where('status', 'active')->count(),
                'low' => Alert::whereIn('solar_system_id', $systemIds)->where('severity', 'low')->where('status', 'active')->count(),
            ],
            'by_type' => Alert::whereIn('solar_system_id', $systemIds)
                ->where('status', 'active')
                ->selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type'),
        ];

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }
}
