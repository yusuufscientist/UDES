<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Intervention extends Model
{
    /** @use HasFactory<\Database\Factories\InterventionFactory> */
    use HasFactory;

    protected $fillable = [
        'solar_system_id',
        'panel_id',
        'technician_id',
        'alert_id',
        'type',
        'description',
        'scheduled_date',
        'completed_date',
        'status',
        'cost',
        'parts_replaced',
        'notes',
        'duration_minutes',
        'priority',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'completed_date' => 'date',
        'cost' => 'decimal:2',
        'duration_minutes' => 'integer',
        'technician_id' => 'integer',
    ];

    /**
     * Get the solar system for this intervention
     */
    public function solarSystem()
    {
        return $this->belongsTo(SolarSystem::class);
    }

    /**
     * Get the panel for this intervention (if applicable)
     */
    public function panel()
    {
        return $this->belongsTo(Panel::class);
    }

    /**
     * Get the technician assigned to this intervention
     */
    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    /**
     * Get the related alert
     */
    public function alert()
    {
        return $this->belongsTo(Alert::class);
    }

    /**
     * Scope for scheduled interventions
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    /**
     * Scope for in progress interventions
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope for completed interventions
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for urgent priority
     */
    public function scopeUrgent($query)
    {
        return $query->where('priority', 'urgent');
    }

    /**
     * Scope for today's interventions
     */
    public function scopeToday($query)
    {
        return $query->whereDate('scheduled_date', today());
    }

    /**
     * Scope for upcoming interventions
     */
    public function scopeUpcoming($query)
    {
        return $query->whereDate('scheduled_date', '>=', today())
            ->whereIn('status', ['scheduled', 'in_progress']);
    }

    /**
     * Mark intervention as in progress
     */
    public function start(): void
    {
        $this->update(['status' => 'in_progress']);
    }

    /**
     * Mark intervention as completed
     */
    public function complete(?string $notes = null, ?int $durationMinutes = null): void
    {
        $this->update([
            'status' => 'completed',
            'completed_date' => today(),
            'notes' => $notes,
            'duration_minutes' => $durationMinutes,
        ]);

        // If there's an associated alert, resolve it
        if ($this->alert) {
            $this->alert->resolve($notes);
        }
    }

    /**
     * Get type label
     */
    public function typeLabel(): string
    {
        return match ($this->type) {
            'routine_maintenance' => 'Routine Maintenance',
            'repair' => 'Repair',
            'inspection' => 'Inspection',
            'cleaning' => 'Cleaning',
            'emergency_repair' => 'Emergency Repair',
            default => 'Unknown',
        };
    }

    /**
     * Get priority color class
     */
    public function priorityColor(): string
    {
        return match ($this->priority) {
            'urgent' => 'danger',
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
            'scheduled' => 'info',
            'in_progress' => 'warning',
            'completed' => 'success',
            'cancelled' => 'secondary',
            default => 'secondary',
        };
    }

    /**
     * Check if intervention is overdue
     */
    public function isOverdue(): bool
    {
        return $this->scheduled_date < today() && in_array($this->status, ['scheduled']);
    }
}
