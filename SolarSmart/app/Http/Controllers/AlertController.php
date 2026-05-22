<?php

namespace App\Http\Controllers;

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
            ->with(['solarSystem', 'panel', 'acknowledgedBy'])
            ->orderBy('triggered_at', 'desc')
            ->paginate(20);

        // Statistics
        $stats = [
            'total' => Alert::whereIn('solar_system_id', $systemIds)->count(),
            'active' => Alert::whereIn('solar_system_id', $systemIds)->where('status', 'active')->count(),
            'critical' => Alert::whereIn('solar_system_id', $systemIds)->where('severity', 'critical')->where('status', 'active')->count(),
            'resolved' => Alert::whereIn('solar_system_id', $systemIds)->where('status', 'resolved')->count(),
        ];

        return view('alerts.index', compact('alerts', 'stats'));
    }

    /**
     * Display alerts for a specific solar system
     */
    public function systemAlerts(SolarSystem $solarSystem)
    {
        $this->authorize('view', $solarSystem);

        $alerts = $solarSystem->alerts()
            ->with(['panel', 'acknowledgedBy'])
            ->orderBy('triggered_at', 'desc')
            ->paginate(20);

        return view('alerts.system', compact('solarSystem', 'alerts'));
    }

    /**
     * Display the specified alert
     */
    public function show(Alert $alert)
    {
        $this->authorize('view', $alert->solarSystem);

        $alert->load(['solarSystem', 'panel', 'acknowledgedBy']);

        return view('alerts.show', compact('alert'));
    }

    /**
     * Acknowledge an alert
     */
    public function acknowledge(Alert $alert)
    {
        $this->authorize('view', $alert->solarSystem);

        $alert->acknowledge(Auth::id());

        return redirect()->back()
            ->with('success', 'Alert acknowledged successfully.');
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

        return redirect()->back()
            ->with('success', 'Alert resolved successfully.');
    }

    /**
     * Resolve an alert (PUT method)
     */
    public function putResolve(Request $request, Alert $alert)
    {
        $this->authorize('view', $alert->solarSystem);

        $validated = $request->validate([
            'resolution_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $alert->resolve($validated['resolution_notes'] ?? null);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Alert resolved successfully.']);
        }

        return redirect()->back()
            ->with('success', 'Alert resolved successfully.');
    }

    /**
     * Create a new alert (for system use)
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

        return redirect()->route('alerts.show', $alert)
            ->with('success', 'Alert created successfully.');
    }

    /**
     * Get active alerts count (for AJAX updates)
     */
    public function activeCount()
    {
        $user = Auth::user();
        $systemIds = $user->solarSystems()->pluck('id');

        $count = Alert::whereIn('solar_system_id', $systemIds)
            ->where('status', 'active')
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Get recent alerts (for AJAX updates)
     */
    public function recent()
    {
        $user = Auth::user();
        $systemIds = $user->solarSystems()->pluck('id');

        $alerts = Alert::whereIn('solar_system_id', $systemIds)
            ->where('status', 'active')
            ->with(['solarSystem'])
            ->orderBy('triggered_at', 'desc')
            ->take(5)
            ->get();

        return response()->json($alerts);
    }
}
