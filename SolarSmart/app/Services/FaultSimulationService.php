<?php

namespace App\Services;

use App\Models\FaultSimulation;
use App\Models\Intervention;
use App\Models\Panel;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class FaultSimulationService
{
    public const FAULT_TYPES = [
        'inverter_failure' => [
            'label' => 'Inverter Failure',
            'description' => 'Inverter has stopped converting DC to AC power. System output dropped to zero.',
            'severity' => 'critical',
            'intervention_type' => 'emergency_repair',
        ],
        'panel_crack' => [
            'label' => 'Panel Crack',
            'description' => 'Physical crack detected on panel surface. Risk of moisture ingress and electrical failure.',
            'severity' => 'high',
            'intervention_type' => 'repair',
        ],
        'wiring_fault' => [
            'label' => 'Wiring Fault',
            'description' => 'Abnormal wiring resistance detected. Potential fire hazard and power loss.',
            'severity' => 'critical',
            'intervention_type' => 'emergency_repair',
        ],
        'sensor_malfunction' => [
            'label' => 'Sensor Malfunction',
            'description' => 'Temperature or irradiance sensor returning invalid readings. Monitoring accuracy compromised.',
            'severity' => 'medium',
            'intervention_type' => 'repair',
        ],
        'hot_spot' => [
            'label' => 'Hot Spot Detection',
            'description' => 'Thermal anomaly detected indicating hot spot. Risk of permanent cell damage and fire.',
            'severity' => 'high',
            'intervention_type' => 'repair',
        ],
        'delamination' => [
            'label' => 'Delamination',
            'description' => 'Panel layer separation detected. Moisture penetration risk and efficiency degradation.',
            'severity' => 'medium',
            'intervention_type' => 'inspection',
        ],
        'connection_failure' => [
            'label' => 'Connection Failure',
            'description' => 'Loose or corroded connector detected. Intermittent power output and potential arcing.',
            'severity' => 'high',
            'intervention_type' => 'repair',
        ],
        'soiling_severe' => [
            'label' => 'Severe Soiling',
            'description' => 'Heavy dust, bird droppings, or debris accumulation causing significant output reduction.',
            'severity' => 'low',
            'intervention_type' => 'cleaning',
        ],
        'shading_issue' => [
            'label' => 'Shading Issue',
            'description' => 'New obstruction causing partial shading. Output reduction exceeding acceptable threshold.',
            'severity' => 'low',
            'intervention_type' => 'inspection',
        ],
        'ground_fault' => [
            'label' => 'Ground Fault',
            'description' => 'Ground fault current detected. Safety hazard requiring immediate investigation.',
            'severity' => 'critical',
            'intervention_type' => 'emergency_repair',
        ],
    ];

    public function triggerFault(Panel $panel, string $faultType, ?int $createdBy = null): FaultSimulation
    {
        if (! isset(self::FAULT_TYPES[$faultType])) {
            throw new \InvalidArgumentException("Unknown fault type: {$faultType}");
        }

        $faultConfig = self::FAULT_TYPES[$faultType];

        return DB::transaction(function () use ($panel, $faultType, $faultConfig, $createdBy) {
            $previousStatus = $panel->status;

            $panel->update(['status' => 'inactive']);

            $faultSimulation = FaultSimulation::create([
                'panel_id' => $panel->id,
                'solar_system_id' => $panel->solar_system_id,
                'fault_type' => $faultType,
                'fault_description' => $faultConfig['description'],
                'severity' => $faultConfig['severity'],
                'previous_status' => $previousStatus,
                'created_by' => $createdBy,
                'triggered_at' => now(),
            ]);

            $alertService = app(AlertService::class);
            if ($panel->solarSystem) {
                $alert = $alertService->createPanelFaultAlert($panel, "{$faultConfig['label']}: {$faultConfig['description']}");
                $faultSimulation->update(['generated_alert_id' => $alert->id]);
            }

            $intervention = $this->createIntervention($panel, $faultType, $faultConfig, $faultSimulation->generated_alert_id ?? null);

            if ($intervention) {
                $faultSimulation->update(['generated_intervention_id' => $intervention->id]);
            }

            return $faultSimulation->fresh(['generatedAlert', 'generatedIntervention']);
        });
    }

    public function resolveFault(FaultSimulation $faultSimulation, ?string $notes = null): void
    {
        DB::transaction(function () use ($faultSimulation, $notes) {
            $faultSimulation->markResolved();

            $panel = $faultSimulation->panel;
            if ($panel->status === 'inactive') {
                $panel->update(['status' => 'active']);
            }

            if ($faultSimulation->generatedIntervention && $faultSimulation->generatedIntervention->status !== 'completed') {
                $completionNotes = $notes ? "Fault simulation resolved. {$notes}" : 'Fault simulation resolved.';
                $faultSimulation->generatedIntervention->complete($completionNotes, 0);
            }

            if ($faultSimulation->generatedAlert && $faultSimulation->generatedAlert->status !== 'resolved') {
                $resolutionNotes = $notes ? "Resolved via fault simulation. {$notes}" : 'Resolved via fault simulation.';
                $faultSimulation->generatedAlert->resolve($resolutionNotes);
            }
        });
    }

    public function getActiveFaultsForSystem(int $systemId)
    {
        return FaultSimulation::with(['panel', 'generatedAlert', 'generatedIntervention'])
            ->where('solar_system_id', $systemId)
            ->active()
            ->latest('triggered_at')
            ->get();
    }

    public function getFaultStatistics(int $systemId): array
    {
        $allFaults = FaultSimulation::where('solar_system_id', $systemId)->get();

        return [
            'total_simulations' => $allFaults->count(),
            'active_faults' => $allFaults->where('resolved', false)->count(),
            'resolved_faults' => $allFaults->where('resolved', true)->count(),
            'critical_count' => $allFaults->where('severity', 'critical')->count(),
            'high_count' => $allFaults->where('severity', 'high')->count(),
            'by_type' => $allFaults->groupBy('fault_type')->map->count()->toArray(),
        ];
    }

    public function getAvailableFaultTypes(): array
    {
        return collect(self::FAULT_TYPES)->map(function ($config, $key) {
            return [
                'key' => $key,
                'label' => $config['label'],
                'description' => $config['description'],
                'severity' => $config['severity'],
            ];
        })->values()->toArray();
    }

    protected function createIntervention(Panel $panel, string $faultType, array $faultConfig, ?int $alertId): ?Intervention
    {
        $technician = User::where('role', 'technician')
            ->where('is_active', true)
            ->inRandomOrder()
            ->first();

        if (! $technician) {
            $technician = User::where('is_active', true)->first();
        }

        if (! $technician) {
            $technician = User::first();
        }

        $userId = $technician?->id ?? null;

        $title = self::FAULT_TYPES[$faultType]['label'];

        return Intervention::create([
            'solar_system_id' => $panel->solar_system_id,
            'panel_id' => $panel->id,
            'technician_id' => $userId,
            'alert_id' => $alertId,
            'type' => $faultConfig['intervention_type'],
            'description' => "[AUTO-GENERATED] {$title} - Panel Serial: {$panel->serial_number}\n\n{$faultConfig['description']}\n\nThis intervention was automatically created by the Fault Simulation System.",
            'scheduled_date' => now()->addHours(2)->toDateString(),
            'priority' => in_array($faultConfig['severity'], ['critical', 'high']) ? 'urgent' : 'high',
            'status' => 'scheduled',
        ]);
    }
}
