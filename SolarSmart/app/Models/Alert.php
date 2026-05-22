<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    /** @use HasFactory<\Database\Factories\AlertFactory> */
    use HasFactory;

    protected $fillable = [
        'solar_system_id',
        'panel_id',
        'title',
        'message',
        'type',
        'severity',
        'status',
        'triggered_at',
        'acknowledged_at',
        'acknowledged_by',
        'resolved_at',
        'resolution_notes',
    ];

    protected $casts = [
        'triggered_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    /**
     * Get the solar system for this alert
     */
    public function solarSystem()
    {
        return $this->belongsTo(SolarSystem::class);
    }

    /**
     * Get the panel for this alert (if applicable)
     */
    public function panel()
    {
        return $this->belongsTo(Panel::class);
    }

    /**
     * Get the user who acknowledged this alert
     */
    public function acknowledgedBy()
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    /**
     * Scope for active alerts
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for critical alerts
     */
    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }

    /**
     * Scope for high severity alerts
     */
    public function scopeHigh($query)
    {
        return $query->where('severity', 'high');
    }

    /**
     * Acknowledge the alert
     */
    public function acknowledge(int $userId): void
    {
        $this->update([
            'status' => 'acknowledged',
            'acknowledged_at' => now(),
            'acknowledged_by' => $userId,
        ]);
    }

    /**
     * Resolve the alert
     */
    public function resolve(?string $notes = null): void
    {
        $this->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'resolution_notes' => $notes,
        ]);
    }

    /**
     * Get severity color class
     */
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

    /**
     * Get status badge class
     */
    public function statusBadge(): string
    {
        return match ($this->status) {
            'active' => 'danger',
            'acknowledged' => 'warning',
            'resolved' => 'success',
            default => 'secondary',
        };
    }

    /**
     * Check if alert is critical
     */
    public function isCritical(): bool
    {
        return $this->severity === 'critical';
    }

    /**
     * Get type label
     */
    public function typeLabel(): string
    {
        return match ($this->type) {
            'low_production' => 'Low Production',
            'panel_fault' => 'Panel Fault',
            'maintenance_due' => 'Maintenance Due',
            'system_offline' => 'System Offline',
            'high_consumption' => 'High Consumption',
            'weather_warning' => 'Weather Warning',
            default => 'Unknown',
        };
    }
}
