<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\Panel;
use App\Models\SolarSystem;
use Carbon\Carbon;

class AlertService
{
    /**
     * Create a low production alert
     */
    public function createLowProductionAlert(SolarSystem $system, ?Panel $panel = null, float $efficiency = 0): Alert
    {
        $title = $panel ? "Low Production: Panel {$panel->serial_number}" : "Low Production: {$system->name}";
        $message = $panel
            ? "Panel {$panel->serial_number} is operating at {$efficiency}% efficiency, which is below the expected threshold."
            : "System {$system->name} is producing below expected levels with {$efficiency}% efficiency.";

        return $this->createAlert($system, $panel, [
            'title' => $title,
            'message' => $message,
            'type' => 'low_production',
            'severity' => $efficiency < 30 ? 'critical' : 'high',
        ]);
    }

    /**
     * Create a panel fault alert
     */
    public function createPanelFaultAlert(Panel $panel, string $faultDescription): Alert
    {
        return $this->createAlert($panel->solarSystem, $panel, [
            'title' => "Panel Fault: {$panel->serial_number}",
            'message' => "Panel {$panel->serial_number} has reported a fault: {$faultDescription}",
            'type' => 'panel_fault',
            'severity' => 'critical',
        ]);
    }

    /**
     * Create a maintenance due alert
     */
    public function createMaintenanceDueAlert(SolarSystem $system, ?Panel $panel = null): Alert
    {
        $title = $panel ? "Maintenance Due: Panel {$panel->serial_number}" : "Maintenance Due: {$system->name}";
        $message = $panel
            ? "Panel {$panel->serial_number} is due for routine maintenance."
            : "Solar system {$system->name} is due for routine maintenance.";

        return $this->createAlert($system, $panel, [
            'title' => $title,
            'message' => $message,
            'type' => 'maintenance_due',
            'severity' => 'medium',
        ]);
    }

    /**
     * Create a system offline alert
     */
    public function createSystemOfflineAlert(SolarSystem $system): Alert
    {
        return $this->createAlert($system, null, [
            'title' => "System Offline: {$system->name}",
            'message' => "Solar system {$system->name} appears to be offline and not reporting data.",
            'type' => 'system_offline',
            'severity' => 'critical',
        ]);
    }

    /**
     * Create a high consumption alert
     */
    public function createHighConsumptionAlert(SolarSystem $system, float $consumptionRatio): Alert
    {
        return $this->createAlert($system, null, [
            'title' => "High Consumption: {$system->name}",
            'message' => "Energy consumption is at {$consumptionRatio}% of production, which is unusually high.",
            'type' => 'high_consumption',
            'severity' => $consumptionRatio > 90 ? 'high' : 'medium',
        ]);
    }

    /**
     * Create a weather warning alert
     */
    public function createWeatherWarningAlert(SolarSystem $system, string $weatherCondition): Alert
    {
        return $this->createAlert($system, null, [
            'title' => "Weather Warning: {$system->name}",
            'message' => "Severe weather condition ({$weatherCondition}) may affect solar production.",
            'type' => 'weather_warning',
            'severity' => 'low',
        ]);
    }

    /**
     * Check if an alert already exists
     */
    public function alertExists(SolarSystem $system, string $type, ?Panel $panel = null): bool
    {
        $query = Alert::where('solar_system_id', $system->id)
            ->where('type', $type)
            ->where('status', 'active');

        if ($panel) {
            $query->where('panel_id', $panel->id);
        }

        return $query->exists();
    }

    /**
     * Resolve duplicate alerts
     */
    public function resolveDuplicateAlerts(SolarSystem $system, string $type, ?Panel $panel = null): void
    {
        $query = Alert::where('solar_system_id', $system->id)
            ->where('type', $type)
            ->where('status', 'active');

        if ($panel) {
            $query->where('panel_id', $panel->id);
        }

        // Keep only the most recent alert
        $alerts = $query->orderBy('triggered_at', 'desc')->get();

        if ($alerts->count() > 1) {
            $alerts->skip(1)->each(function ($alert) {
                $alert->resolve('Duplicate alert resolved automatically');
            });
        }
    }

    /**
     * Get active alerts summary
     */
    public function getActiveAlertsSummary(SolarSystem $system): array
    {
        $alerts = Alert::where('solar_system_id', $system->id)
            ->where('status', 'active')
            ->get();

        return [
            'total' => $alerts->count(),
            'critical' => $alerts->where('severity', 'critical')->count(),
            'high' => $alerts->where('severity', 'high')->count(),
            'medium' => $alerts->where('severity', 'medium')->count(),
            'low' => $alerts->where('severity', 'low')->count(),
            'by_type' => $alerts->groupBy('type')->map->count()->toArray(),
        ];
    }

    /**
     * Auto-resolve stale alerts
     */
    public function autoResolveStaleAlerts(int $days = 7): int
    {
        $staleAlerts = Alert::where('status', 'active')
            ->where('triggered_at', '<', Carbon::now()->subDays($days))
            ->get();

        $count = 0;
        foreach ($staleAlerts as $alert) {
            $alert->resolve('Auto-resolved after '.$days.' days');
            $count++;
        }

        return $count;
    }

    /**
     * Create a generic alert
     */
    private function createAlert(SolarSystem $system, ?Panel $panel, array $data): Alert
    {
        // Check for existing active alert of same type
        if ($this->alertExists($system, $data['type'], $panel)) {
            // Update existing alert timestamp
            $existingAlert = Alert::where('solar_system_id', $system->id)
                ->where('type', $data['type'])
                ->where('status', 'active')
                ->when($panel, function ($query) use ($panel) {
                    return $query->where('panel_id', $panel->id);
                })
                ->first();

            $existingAlert->update(['triggered_at' => now()]);

            return $existingAlert;
        }

        return Alert::create([
            'solar_system_id' => $system->id,
            'panel_id' => $panel?->id,
            'title' => $data['title'],
            'message' => $data['message'],
            'type' => $data['type'],
            'severity' => $data['severity'],
            'status' => 'active',
            'triggered_at' => now(),
        ]);
    }
}
