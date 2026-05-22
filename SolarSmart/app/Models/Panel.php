<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Panel extends Model
{
    /** @use HasFactory<\Database\Factories\PanelFactory> */
    use HasFactory;

    protected $fillable = [
        'solar_system_id',
        'serial_number',
        'model',
        'manufacturer',
        'capacity_watts',
        'efficiency_rating',
        'installation_date',
        'status',
        'current_voltage',
        'current_amperage',
        'current_power_output',
        'last_reading_at',
        'notes',
    ];

    protected $casts = [
        'capacity_watts' => 'decimal:2',
        'efficiency_rating' => 'decimal:2',
        'current_voltage' => 'decimal:2',
        'current_amperage' => 'decimal:2',
        'current_power_output' => 'decimal:2',
        'installation_date' => 'date',
        'last_reading_at' => 'datetime',
    ];

    /**
     * Get the solar system that owns this panel
     */
    public function solarSystem()
    {
        return $this->belongsTo(SolarSystem::class);
    }

    /**
     * Get production records for this panel
     */
    public function productions()
    {
        return $this->hasMany(Production::class);
    }

    /**
     * Get alerts for this panel
     */
    public function alerts()
    {
        return $this->hasMany(Alert::class);
    }

    /**
     * Get interventions for this panel
     */
    public function interventions()
    {
        return $this->hasMany(Intervention::class);
    }

    /**
     * Check if panel is producing normally
     */
    public function isProducingNormally(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $expectedOutput = $this->capacity_watts * 0.8; // 80% of capacity is considered normal

        return $this->current_power_output >= $expectedOutput;
    }

    /**
     * Get today's production for this panel
     */
    public function todayProduction()
    {
        return $this->productions()
            ->whereDate('production_date', today())
            ->sum('energy_produced_kwh');
    }

    /**
     * Calculate panel efficiency percentage
     */
    public function calculateEfficiency(): float
    {
        $todayProduction = $this->todayProduction();
        $expectedProduction = ($this->capacity_watts / 1000) * 5; // 5 peak sun hours

        return $expectedProduction > 0 ? ($todayProduction / $expectedProduction) * 100 : 0;
    }
}
