<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\SolarSystem;
use App\Models\User;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    private function getCurrentUser()
    {
        if (auth()->check()) {
            return auth()->user();
        }

        $user = User::where('email', 'fcyusuuf@gmail.com')->first();
        if (!$user) {
            $user = User::first();
        }
        if (!$user) {
            $user = User::create([
                'name' => 'Default User',
                'email' => 'default@example.com',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'is_active' => true,
            ]);
        }
        return $user;
    }

    public function index()
    {
        $user = $this->getCurrentUser();
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
        $alerts = $solarSystem->alerts()
            ->with(['panel', 'acknowledgedBy'])
            ->orderBy('triggered_at', 'desc')
            ->paginate(20);

        return view('alerts.system', compact('solarSystem', 'alerts'));
    }

    public function show(Alert $alert)
    {
        $alert->load(['solarSystem', 'panel', 'acknowledgedBy']);

        return view('alerts.show', compact('alert'));
    }

    public function acknowledge(Alert $alert)
    {
        $alert->acknowledge($this->getCurrentUser()->id);

        return redirect()->back()
            ->with('success', 'Alert acknowledged successfully.');
    }

    public function resolve(Request $request, Alert $alert)
    {
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
        $user = $this->getCurrentUser();
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
        $user = $this->getCurrentUser();
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
