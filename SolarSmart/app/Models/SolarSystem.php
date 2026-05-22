<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolarSystem extends Model
{
    /** @use HasFactory<\Database\Factories\SolarSystemFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'location',
        'latitude',
        'longitude',
        'total_capacity_kw',
        'installation_date',
        'status',
        'description',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'total_capacity_kw' => 'decimal:2',
        'installation_date' => 'date',
    ];

    /**
     * Get the user that owns this solar system
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get panels for this solar system
     */
    public function panels()
    {
        return $this->hasMany(Panel::class);
    }

    /**
     * Get production records for this solar system
     */
    public function productions()
    {
        return $this->hasMany(Production::class);
    }

    /**
     * Get alerts for this solar system
     */
    public function alerts()
    {
        return $this->hasMany(Alert::class);
    }

    /**
     * Get interventions for this solar system
     */
    public function interventions()
    {
        return $this->hasMany(Intervention::class);
    }

    /**
     * Get active panels count
     */
    public function activePanelsCount(): int
    {
        return $this->panels()->where('status', 'active')->count();
    }

    /**
     * Get total energy produced today
     */
    public function todayProduction()
    {
        return $this->productions()
            ->whereDate('production_date', today())
            ->sum('energy_produced_kwh');
    }

    /**
     * Get total energy produced this month
     */
    public function monthProduction()
    {
        return $this->productions()
            ->whereMonth('production_date', now()->month)
            ->whereYear('production_date', now()->year)
            ->sum('energy_produced_kwh');
    }

    /**
     * Get current active alerts count
     */
    public function activeAlertsCount(): int
    {
        return $this->alerts()->where('status', 'active')->count();
    }

    /**
     * Calculate system efficiency
     */
    public function calculateEfficiency(): float
    {
        $todayProduction = $this->todayProduction();
        $expectedProduction = $this->total_capacity_kw * 5; // Assuming 5 peak sun hours

        return $expectedProduction > 0 ? ($todayProduction / $expectedProduction) * 100 : 0;
    }
}
