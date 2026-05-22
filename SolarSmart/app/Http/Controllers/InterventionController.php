<?php

namespace App\Http\Controllers;

use App\Models\Intervention;
use App\Models\SolarSystem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InterventionController extends Controller
{
    /**
     * Display a listing of interventions
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->isTechnician()) {
            $interventions = Intervention::where('technician_id', $user->id)
                ->with(['solarSystem', 'panel', 'alert'])
                ->orderBy('scheduled_date', 'asc')
                ->paginate(20);
        } else {
            $systemIds = $user->solarSystems()->pluck('id');
            $interventions = Intervention::whereIn('solar_system_id', $systemIds)
                ->with(['solarSystem', 'panel', 'technician', 'alert'])
                ->orderBy('scheduled_date', 'asc')
                ->paginate(20);
        }

        return view('interventions.index', compact('interventions'));
    }

    /**
     * Display interventions for a specific solar system
     */
    public function systemInterventions(SolarSystem $solarSystem)
    {
        $this->authorize('view', $solarSystem);

        $interventions = $solarSystem->interventions()
            ->with(['panel', 'technician', 'alert'])
            ->orderBy('scheduled_date', 'asc')
            ->paginate(20);

        return view('interventions.system', compact('solarSystem', 'interventions'));
    }

    /**
     * Show the form for creating a new intervention
     */
    public function create(SolarSystem $solarSystem)
    {
        $this->authorize('update', $solarSystem);

        $technicians = User::where('role', 'technician')->where('is_active', true)->get();
        $panels = $solarSystem->panels;

        return view('interventions.create', compact('solarSystem', 'technicians', 'panels'));
    }

    /**
     * Store a newly created intervention
     */
    public function store(Request $request, SolarSystem $solarSystem)
    {
        $this->authorize('update', $solarSystem);

        $validated = $request->validate([
            'panel_id' => ['nullable', 'exists:panels,id'],
            'technician_id' => ['required', 'exists:users,id'],
            'alert_id' => ['nullable', 'exists:alerts,id'],
            'type' => ['required', 'in:routine_maintenance,repair,inspection,cleaning,emergency_repair'],
            'description' => ['required', 'string', 'max:1000'],
            'scheduled_date' => ['required', 'date', 'after_or_equal:today'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        $validated['solar_system_id'] = $solarSystem->id;
        $validated['status'] = 'scheduled';

        $intervention = Intervention::create($validated);

        return redirect()->route('solar-systems.interventions.index', $solarSystem)
            ->with('success', 'Intervention scheduled successfully.');
    }

    /**
     * Display the specified intervention
     */
    public function show(Intervention $intervention)
    {
        $this->authorize('view', $intervention->solarSystem);

        $intervention->load(['solarSystem', 'panel', 'technician', 'alert']);

        return view('interventions.show', compact('intervention'));
    }

    /**
     * Show the form for editing the specified intervention
     */
    public function edit(Intervention $intervention)
    {
        $this->authorize('update', $intervention->solarSystem);

        $technicians = User::where('role', 'technician')->where('is_active', true)->get();
        $panels = $intervention->solarSystem->panels;

        return view('interventions.edit', compact('intervention', 'technicians', 'panels'));
    }

    /**
     * Update the specified intervention
     */
    public function update(Request $request, Intervention $intervention)
    {
        $this->authorize('update', $intervention->solarSystem);

        $validated = $request->validate([
            'panel_id' => ['nullable', 'exists:panels,id'],
            'technician_id' => ['required', 'exists:users,id'],
            'type' => ['required', 'in:routine_maintenance,repair,inspection,cleaning,emergency_repair'],
            'description' => ['required', 'string', 'max:1000'],
            'scheduled_date' => ['required', 'date'],
            'status' => ['required', 'in:scheduled,in_progress,completed,cancelled'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'parts_replaced' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'duration_minutes' => ['nullable', 'integer', 'min:0'],
        ]);

        if ($validated['status'] === 'completed' && $intervention->status !== 'completed') {
            $validated['completed_date'] = today();
        }

        $intervention->update($validated);

        // If intervention is completed and there's an associated alert, resolve it
        if ($validated['status'] === 'completed' && $intervention->alert) {
            $intervention->alert->resolve($validated['notes'] ?? null);
        }

        return redirect()->route('interventions.show', $intervention)
            ->with('success', 'Intervention updated successfully.');
    }

    /**
     * Start an intervention (for technicians or system owner)
     */
    public function start(Intervention $intervention)
    {
        // Allow technician, system owner, or admin
        $isOwner = $intervention->solarSystem->user_id === Auth::id();
        if (Auth::id() !== $intervention->technician_id && ! Auth::user()->isAdmin() && ! $isOwner) {
            abort(403, 'Unauthorized action.');
        }

        $intervention->start();

        return redirect()->back()
            ->with('success', 'Intervention started.');
    }

    /**
     * Complete an intervention (for technicians or system owner)
     */
    public function complete(Request $request, Intervention $intervention)
    {
        // Allow technician, system owner, or admin
        $isOwner = $intervention->solarSystem->user_id === Auth::id();
        if (Auth::id() !== $intervention->technician_id && ! Auth::user()->isAdmin() && ! $isOwner) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
            'parts_replaced' => ['nullable', 'string', 'max:1000'],
            'duration_minutes' => ['required', 'integer', 'min:0'],
            'cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        $intervention->complete($validated['notes'] ?? null, $validated['duration_minutes']);

        return redirect()->back()
            ->with('success', 'Intervention completed successfully.');
    }

    /**
     * Remove the specified intervention
     */
    public function destroy(Intervention $intervention)
    {
        $this->authorize('update', $intervention->solarSystem);

        $intervention->delete();

        return redirect()->route('interventions.index')
            ->with('success', 'Intervention cancelled successfully.');
    }

    /**
     * Display technician dashboard
     */
    public function technicianDashboard()
    {
        $user = Auth::user();

        if (! $user->isTechnician() && ! $user->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        $todayInterventions = Intervention::where('technician_id', $user->id)
            ->whereDate('scheduled_date', today())
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->with(['solarSystem', 'panel'])
            ->get();

        $upcomingInterventions = Intervention::where('technician_id', $user->id)
            ->upcoming()
            ->with(['solarSystem', 'panel'])
            ->take(10)
            ->get();

        $completedInterventions = Intervention::where('technician_id', $user->id)
            ->completed()
            ->count();

        return view('technician.dashboard', compact(
            'todayInterventions',
            'upcomingInterventions',
            'completedInterventions'
        ));
    }
}
