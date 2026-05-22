<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Production extends Model
{
    /** @use HasFactory<\Database\Factories\ProductionFactory> */
    use HasFactory;

    protected $fillable = [
        'solar_system_id',
        'panel_id',
        'production_date',
        'production_time',
        'energy_produced_kwh',
        'energy_consumed_kwh',
        'peak_power_kw',
        'average_power_kw',
        'irradiance_wm2',
        'temperature_celsius',
        'efficiency_percentage',
        'weather_condition',
    ];

    protected $casts = [
        'production_date' => 'date',
        'energy_produced_kwh' => 'decimal:4',
        'energy_consumed_kwh' => 'decimal:4',
        'peak_power_kw' => 'decimal:4',
        'average_power_kw' => 'decimal:4',
        'irradiance_wm2' => 'decimal:2',
        'temperature_celsius' => 'decimal:2',
        'efficiency_percentage' => 'decimal:2',
    ];

    /**
     * Get the solar system for this production record
     */
    public function solarSystem()
    {
        return $this->belongsTo(SolarSystem::class);
    }

    /**
     * Get the panel for this production record (if applicable)
     */
    public function panel()
    {
        return $this->belongsTo(Panel::class);
    }

    /**
     * Calculate net energy (produced - consumed)
     */
    public function netEnergy(): float
    {
        return $this->energy_produced_kwh - $this->energy_consumed_kwh;
    }

    /**
     * Check if this was a high production day
     */
    public function isHighProduction(): bool
    {
        $systemCapacity = $this->solarSystem->total_capacity_kw;
        $expectedDaily = $systemCapacity * 5; // 5 peak sun hours

        return $this->energy_produced_kwh >= ($expectedDaily * 0.9);
    }

    /**
     * Get production efficiency rating
     */
    public function getEfficiencyRating(): string
    {
        if ($this->efficiency_percentage >= 90) {
            return 'excellent';
        } elseif ($this->efficiency_percentage >= 75) {
            return 'good';
        } elseif ($this->efficiency_percentage >= 50) {
            return 'fair';
        } else {
            return 'poor';
        }
    }

    /**
     * Scope for daily production
     */
    public function scopeDaily($query, $date = null)
    {
        $date = $date ?? today();

        return $query->whereDate('production_date', $date);
    }

    /**
     * Scope for monthly production
     */
    public function scopeMonthly($query, $year = null, $month = null)
    {
        $year = $year ?? now()->year;
        $month = $month ?? now()->month;

        return $query->whereYear('production_date', $year)
            ->whereMonth('production_date', $month);
    }

    /**
     * Scope for yearly production
     */
    public function scopeYearly($query, $year = null)
    {
        $year = $year ?? now()->year;

        return $query->whereYear('production_date', $year);
    }
}
