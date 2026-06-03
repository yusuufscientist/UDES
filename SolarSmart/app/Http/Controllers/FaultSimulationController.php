<?php

namespace App\Http\Controllers;

use App\Models\FaultSimulation;
use App\Models\Panel;
use App\Models\SolarSystem;
use App\Models\User;
use App\Services\FaultSimulationService;
use Illuminate\Http\Request;

class FaultSimulationController extends Controller
{
    public function __construct(
        protected FaultSimulationService $service
    ) {}

    private function currentUserId()
    {
        if (auth()->check()) {
            return auth()->id();
        }

        return User::where('email', 'fcyusuuf@gmail.com')->value('id') ?? User::first()?->id;
    }

    public function index()
    {
        $systems = SolarSystem::with(['panels' => function ($query) {
            $query->where('status', 'active');
        }])
            ->latest()
            ->get();

        $simulations = FaultSimulation::with(['panel', 'generatedAlert', 'generatedIntervention', 'creator'])
            ->latest('triggered_at')
            ->paginate(20);

        $stats = [];
        foreach ($systems as $system) {
            $stats[$system->id] = $this->service->getFaultStatistics($system->id);
        }

        $faultTypes = $this->service->getAvailableFaultTypes();

        return view('fault-simulations.index', compact('systems', 'simulations', 'stats', 'faultTypes'));
    }

    public function create(SolarSystem $solarSystem)
    {
        $activePanels = $solarSystem->panels()->where('status', 'active')->get();
        $faultTypes = $this->service->getAvailableFaultTypes();

        return view('fault-simulations.create', compact('solarSystem', 'activePanels', 'faultTypes'));
    }

    public function store(Request $request, SolarSystem $solarSystem)
    {

        $validated = $request->validate([
            'panel_id' => 'required|exists:panels,id',
            'fault_type' => 'required|in:inverter_failure,panel_crack,wiring_fault,sensor_malfunction,hot_spot,delamination,connection_failure,soiling_severe,shading_issue,ground_fault',
        ]);

        $panel = Panel::findOrFail($validated['panel_id']);

        if ($panel->solar_system_id !== $solarSystem->id) {
            abort(403, 'Panel does not belong to this solar system');
        }

        $faultSimulation = $this->service->triggerFault($panel, $validated['fault_type'], auth()->id());

        return redirect()
            ->route('solar-systems.interventions.index', $solarSystem->id)
            ->with('success', "Fault simulation triggered successfully for panel {$panel->serial_number}. Alert and intervention have been created automatically.");
    }

    public function quickTrigger(Request $request)
    {
        $validated = $request->validate([
            'panel_id' => 'required|exists:panels,id',
            'fault_type' => 'required|in:inverter_failure,panel_crack,wiring_fault,sensor_malfunction,hot_spot,delamination,connection_failure,soiling_severe,shading_issue,ground_fault',
        ]);

        try {
            $panel = Panel::with('solarSystem')->findOrFail($validated['panel_id']);

            $faultSimulation = $this->service->triggerFault($panel, $validated['fault_type'], $this->currentUserId());

            return response()->json([
                'success' => true,
                'message' => "Fault triggered: {$faultSimulation->faultTypeLabel()}",
                'panel' => [
                    'id' => $panel->id,
                    'serial_number' => $panel->serial_number,
                    'status' => 'inactive',
                ],
                'fault' => [
                    'id' => $faultSimulation->id,
                    'type' => $faultSimulation->fault_type,
                    'label' => $faultSimulation->faultTypeLabel(),
                    'severity' => $faultSimulation->severity,
                ],
                'alert_id' => $faultSimulation->generated_alert_id,
                'intervention_id' => $faultSimulation->generated_intervention_id,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function resolve(FaultSimulation $faultSimulation)
    {
        $this->service->resolveFault($faultSimulation);

        return back()->with('success', "Fault simulation for panel {$faultSimulation->panel->serial_number} has been resolved.");
    }

    public function show(FaultSimulation $faultSimulation)
    {
        $faultSimulation->load(['panel', 'solarSystem', 'generatedAlert', 'generatedIntervention', 'creator']);

        return view('fault-simulations.show', compact('faultSimulation'));
    }
}
