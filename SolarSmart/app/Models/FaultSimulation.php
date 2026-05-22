<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FaultSimulation extends Model
{
    use HasFactory;

    protected $fillable = [
        'panel_id',
        'solar_system_id',
        'fault_type',
        'fault_description',
        'severity',
        'previous_status',
        'created_by',
        'generated_alert_id',
        'generated_intervention_id',
        'resolved',
        'resolved_at',
        'triggered_at',
    ];

    protected $casts = [
        'resolved' => 'boolean',
        'resolved_at' => 'datetime',
        'triggered_at' => 'datetime',
    ];

    public function panel(): BelongsTo
    {
        return $this->belongsTo(Panel::class);
    }

    public function solarSystem(): BelongsTo
    {
        return $this->belongsTo(SolarSystem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function generatedAlert(): BelongsTo
    {
        return $this->belongsTo(Alert::class, 'generated_alert_id');
    }

    public function generatedIntervention(): BelongsTo
    {
        return $this->belongsTo(Intervention::class, 'generated_intervention_id');
    }

    public function scopeActive($query)
    {
        return $query->where('resolved', false);
    }

    public function scopeResolved($query)
    {
        return $query->where('resolved', true);
    }

    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }

    public function scopeHigh($query)
    {
        return $query->where('severity', 'high');
    }

    public function markResolved(): void
    {
        $this->update([
            'resolved' => true,
            'resolved_at' => now(),
        ]);
    }

    public function faultTypeLabel(): string
    {
        return match ($this->fault_type) {
            'inverter_failure' => 'Inverter Failure',
            'panel_crack' => 'Panel Crack',
            'wiring_fault' => 'Wiring Fault',
            'sensor_malfunction' => 'Sensor Malfunction',
            'hot_spot' => 'Hot Spot Detection',
            'delamination' => 'Delamination',
            'connection_failure' => 'Connection Failure',
            'soiling_severe' => 'Severe Soiling',
            'shading_issue' => 'Shading Issue',
            'ground_fault' => 'Ground Fault',
            default => ucfirst(str_replace('_', ' ', $this->fault_type)),
        };
    }

    public function severityColor(): string
    {
        return match ($this->severity) {
            'critical' => 'danger',
            'high' => 'warning',
            'medium' => 'info',
            'low' => 'success',
            default => 'secondary',
        };
    }

    public function statusBadge(): string
    {
        return $this->resolved ? 'success' : 'danger';
    }
}
