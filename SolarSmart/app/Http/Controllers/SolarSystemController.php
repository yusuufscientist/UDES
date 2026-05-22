<?php

namespace App\Http\Controllers;

use App\Models\SolarSystem;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SolarSystemController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of solar systems
     */
    public function index()
    {
        $solarSystems = Auth::user()
            ->solarSystems()
            ->withCount(['panels', 'alerts'])
            ->get();

        return view('solar-systems.index', compact('solarSystems'));
    }

    /**
     * Show the form for creating a new solar system
     */
    public function create()
    {
        return view('solar-systems.create');
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

        $solarSystem = SolarSystem::create($validated);

        return redirect()
            ->route('solar-systems.show', $solarSystem)
            ->with('success', 'Solar system created successfully.');
    }

    /**
     * Display the specified solar system
     */
    public function show(SolarSystem $solarSystem)
    {
        $this->authorize('view', $solarSystem);

        $solarSystem->load([
            'panels',
            'productions' => function ($query) {
                $query->latest()->take(30);
            },
            'alerts' => function ($query) {
                $query->where('status', 'active')->latest();
            },
        ]);

        // Calculate statistics
        $stats = [
            'today_production' => $solarSystem->todayProduction(),
            'month_production' => $solarSystem->monthProduction(),
            'active_panels' => $solarSystem->activePanelsCount(),
            'total_panels' => $solarSystem->panels->count(),
            'efficiency' => $solarSystem->calculateEfficiency(),
            'active_alerts' => $solarSystem->activeAlertsCount(),
        ];

        return view('solar-systems.show', compact('solarSystem', 'stats'));
    }

    /**
     * Show the form for editing the specified solar system
     */
    public function edit(SolarSystem $solarSystem)
    {
        $this->authorize('update', $solarSystem);

        return view('solar-systems.edit', compact('solarSystem'));
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

        return redirect()
            ->route('solar-systems.show', $solarSystem)
            ->with('success', 'Solar system updated successfully.');
    }

    /**
     * Remove the specified solar system
     */
    public function destroy(SolarSystem $solarSystem)
    {
        $this->authorize('delete', $solarSystem);

        $solarSystem->delete();

        return redirect()
            ->route('solar-systems.index')
            ->with('success', 'Solar system deleted successfully.');
    }
}
