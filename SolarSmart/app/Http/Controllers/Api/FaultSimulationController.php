<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FaultSimulation;
use App\Models\Panel;
use App\Models\SolarSystem;
use App\Services\FaultSimulationService;
use Illuminate\Http\Request;

class FaultSimulationController extends Controller
{
    public function __construct(
        protected FaultSimulationService $service
    ) {}

    public function index(Request $request)
    {
        $query = FaultSimulation::with(['panel', 'generatedAlert', 'generatedIntervention', 'solarSystem']);

        if ($request->has('system_id')) {
            $query->where('solar_system_id', $request->system_id);
        }

        if ($request->has('resolved')) {
            $query->where('resolved', (bool) $request->resolved);
        }

        if ($request->has('severity')) {
            $query->where('severity', $request->severity);
        }

        $simulations = $query->latest('triggered_at')->paginate(20);

        return response()->json($simulations);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'panel_id' => 'required|exists:panels,id',
            'fault_type' => 'required|in:inverter_failure,panel_crack,wiring_fault,sensor_malfunction,hot_spot,delamination,connection_failure,soiling_severe,shading_issue,ground_fault',
        ]);

        try {
            $panel = Panel::with('solarSystem')->findOrFail($validated['panel_id']);

            if (! $panel->solarSystem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Panel does not belong to a solar system',
                ], 422);
            }

            $faultSimulation = $this->service->triggerFault($panel, $validated['fault_type'], auth()->id() ?? null);

            return response()->json([
                'success' => true,
                'message' => "Fault simulation triggered for panel {$panel->serial_number}",
                'data' => [
                    'id' => $faultSimulation->id,
                    'panel' => [
                        'id' => $panel->id,
                        'serial_number' => $panel->serial_number,
                        'model' => $panel->model,
                        'status' => 'inactive',
                    ],
                    'fault' => [
                        'type' => $faultSimulation->fault_type,
                        'label' => $faultSimulation->faultTypeLabel(),
                        'description' => $faultSimulation->fault_description,
                        'severity' => $faultSimulation->severity,
                    ],
                    'alert_id' => $faultSimulation->generated_alert_id,
                    'intervention_id' => $faultSimulation->generated_intervention_id,
                    'triggered_at' => $faultSimulation->triggered_at,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(FaultSimulation $faultSimulation)
    {
        $faultSimulation->load(['panel', 'solarSystem', 'generatedAlert', 'generatedIntervention', 'creator']);

        return response()->json([
            'success' => true,
            'data' => $faultSimulation,
        ]);
    }

    public function resolve(FaultSimulation $faultSimulation, Request $request)
    {
        $notes = $request->input('notes');
        $this->service->resolveFault($faultSimulation, $notes);

        return response()->json([
            'success' => true,
            'message' => 'Fault simulation resolved successfully',
            'data' => [
                'id' => $faultSimulation->id,
                'resolved' => true,
                'resolved_at' => $faultSimulation->resolved_at,
                'panel_status' => $faultSimulation->panel->status,
            ],
        ]);
    }

    public function statistics($systemId)
    {
        $system = SolarSystem::findOrFail($systemId);
        $stats = $this->service->getFaultStatistics($systemId);

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    public function faultTypes()
    {
        $types = $this->service->getAvailableFaultTypes();

        return response()->json([
            'success' => true,
            'data' => $types,
        ]);
    }
}
