<?php

namespace App\Http\Controllers;

use App\Models\Panel;
use App\Models\SolarSystem;
use Illuminate\Http\Request;

class PanelController extends Controller
{
    /**
     * Display a listing of panels for a solar system
     */
    public function index(SolarSystem $solarSystem)
    {
        $this->authorize('view', $solarSystem);

        $panels = $solarSystem->panels()->withCount('alerts')->get();

        return view('panels.index', compact('solarSystem', 'panels'));
    }

    /**
     * Show the form for creating a new panel
     */
    public function create(SolarSystem $solarSystem)
    {
        $this->authorize('update', $solarSystem);

        return view('panels.create', compact('solarSystem'));
    }

    /**
     * Store a newly created panel
     */
    public function store(Request $request, SolarSystem $solarSystem)
    {
        $this->authorize('update', $solarSystem);

        $validated = $request->validate([
            'serial_number' => ['required', 'string', 'max:255', 'unique:panels'],
            'model' => ['required', 'string', 'max:255'],
            'manufacturer' => ['required', 'string', 'max:255'],
            'capacity_watts' => ['required', 'numeric', 'min:1'],
            'efficiency_rating' => ['nullable', 'numeric', 'between:0,100'],
            'installation_date' => ['required', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['solar_system_id'] = $solarSystem->id;
        $validated['status'] = 'active';

        $panel = Panel::create($validated);

        return redirect()->route('solar-systems.panels.index', $solarSystem)
            ->with('success', 'Panel added successfully.');
    }

    /**
     * Display the specified panel
     */
    public function show(SolarSystem $solarSystem, Panel $panel)
    {
        $this->authorize('view', $solarSystem);

        $panel->load(['productions' => function ($query) {
            $query->latest()->take(30);
        }, 'alerts' => function ($query) {
            $query->latest()->take(10);
        }]);

        // Calculate panel statistics
        $stats = [
            'today_production' => $panel->todayProduction(),
            'efficiency' => $panel->calculateEfficiency(),
            'is_producing_normally' => $panel->isProducingNormally(),
        ];

        return view('panels.show', compact('solarSystem', 'panel', 'stats'));
    }

    /**
     * Show the form for editing the specified panel
     */
    public function edit(SolarSystem $solarSystem, Panel $panel)
    {
        $this->authorize('update', $solarSystem);

        return view('panels.edit', compact('solarSystem', 'panel'));
    }

    /**
     * Update the specified panel
     */
    public function update(Request $request, SolarSystem $solarSystem, Panel $panel)
    {
        $this->authorize('update', $solarSystem);

        $validated = $request->validate([
            'serial_number' => ['required', 'string', 'max:255', 'unique:panels,serial_number,'.$panel->id],
            'model' => ['required', 'string', 'max:255'],
            'manufacturer' => ['required', 'string', 'max:255'],
            'capacity_watts' => ['required', 'numeric', 'min:1'],
            'efficiency_rating' => ['nullable', 'numeric', 'between:0,100'],
            'status' => ['required', 'in:active,inactive,faulty,maintenance'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $panel->update($validated);

        return redirect()->route('solar-systems.panels.show', [$solarSystem, $panel])
            ->with('success', 'Panel updated successfully.');
    }

    /**
     * Remove the specified panel
     */
    public function destroy(SolarSystem $solarSystem, Panel $panel)
    {
        $this->authorize('update', $solarSystem);

        $panel->delete();

        return redirect()->route('solar-systems.panels.index', $solarSystem)
            ->with('success', 'Panel removed successfully.');
    }

    /**
     * Update panel readings (for real-time monitoring)
     */
    public function updateReadings(Request $request, SolarSystem $solarSystem, Panel $panel)
    {
        $this->authorize('update', $solarSystem);

        $validated = $request->validate([
            'current_voltage' => ['required', 'numeric', 'min:0'],
            'current_amperage' => ['required', 'numeric', 'min:0'],
            'current_power_output' => ['required', 'numeric', 'min:0'],
        ]);

        $validated['last_reading_at'] = now();

        $panel->update($validated);

        // Check for low production and create alert if necessary
        if (! $panel->isProducingNormally()) {
            $this->createLowProductionAlert($panel);
        }

        return response()->json(['success' => true, 'panel' => $panel]);
    }

    /**
     * Create low production alert
     */
    private function createLowProductionAlert(Panel $panel): void
    {
        // Check if there's already an active alert for this panel
        $existingAlert = $panel->alerts()
            ->where('type', 'low_production')
            ->where('status', 'active')
            ->first();

        if (! $existingAlert) {
            $panel->alerts()->create([
                'solar_system_id' => $panel->solar_system_id,
                'title' => 'Low Production Alert',
                'message' => "Panel {$panel->serial_number} is producing below expected levels.",
                'type' => 'low_production',
                'severity' => 'high',
                'status' => 'active',
                'triggered_at' => now(),
            ]);
        }
    }
}
