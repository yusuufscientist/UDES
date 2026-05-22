<?php

namespace App\Services;

use App\Models\Panel;
use App\Models\Production;
use App\Models\SolarSystem;
use Carbon\Carbon;

class ProductionCalculator
{
    /**
     * Calculate expected daily production for a solar system
     * Based on capacity and average peak sun hours
     */
    public function calculateExpectedDailyProduction(float $capacityKw, float $peakSunHours = 5): float
    {
        return $capacityKw * $peakSunHours;
    }

    /**
     * Calculate expected daily production for a panel
     */
    public function calculateExpectedPanelProduction(float $capacityWatts, float $peakSunHours = 5): float
    {
        return ($capacityWatts / 1000) * $peakSunHours;
    }

    /**
     * Calculate efficiency percentage
     */
    public function calculateEfficiency(float $actualProduction, float $expectedProduction): float
    {
        if ($expectedProduction <= 0) {
            return 0;
        }

        $efficiency = ($actualProduction / $expectedProduction) * 100;

        return min($efficiency, 100); // Cap at 100%
    }

    /**
     * Calculate system efficiency for a specific date
     */
    public function calculateSystemEfficiency(SolarSystem $system, ?Carbon $date = null): float
    {
        $date = $date ?? Carbon::today();

        $actualProduction = Production::where('solar_system_id', $system->id)
            ->whereDate('production_date', $date)
            ->sum('energy_produced_kwh');

        $expectedProduction = $this->calculateExpectedDailyProduction($system->total_capacity_kw);

        return $this->calculateEfficiency($actualProduction, $expectedProduction);
    }

    /**
     * Calculate panel efficiency for a specific date
     */
    public function calculatePanelEfficiency(Panel $panel, ?Carbon $date = null): float
    {
        $date = $date ?? Carbon::today();

        $actualProduction = Production::where('panel_id', $panel->id)
            ->whereDate('production_date', $date)
            ->sum('energy_produced_kwh');

        $expectedProduction = $this->calculateExpectedPanelProduction($panel->capacity_watts);

        return $this->calculateEfficiency($actualProduction, $expectedProduction);
    }

    /**
     * Get production trend (comparing current period to previous)
     */
    public function getProductionTrend(SolarSystem $system, string $period = 'day'): array
    {
        return match ($period) {
            'day' => $this->getDailyTrend($system),
            'week' => $this->getWeeklyTrend($system),
            'month' => $this->getMonthlyTrend($system),
            default => $this->getDailyTrend($system),
        };
    }

    /**
     * Get daily production trend
     */
    private function getDailyTrend(SolarSystem $system): array
    {
        $today = Production::where('solar_system_id', $system->id)
            ->whereDate('production_date', Carbon::today())
            ->sum('energy_produced_kwh');

        $yesterday = Production::where('solar_system_id', $system->id)
            ->whereDate('production_date', Carbon::yesterday())
            ->sum('energy_produced_kwh');

        $change = $yesterday > 0 ? (($today - $yesterday) / $yesterday) * 100 : 0;

        return [
            'current' => $today,
            'previous' => $yesterday,
            'change_percentage' => round($change, 2),
            'trend' => $change >= 0 ? 'up' : 'down',
        ];
    }

    /**
     * Get weekly production trend
     */
    private function getWeeklyTrend(SolarSystem $system): array
    {
        $thisWeek = Production::where('solar_system_id', $system->id)
            ->whereBetween('production_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->sum('energy_produced_kwh');

        $lastWeek = Production::where('solar_system_id', $system->id)
            ->whereBetween('production_date', [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()])
            ->sum('energy_produced_kwh');

        $change = $lastWeek > 0 ? (($thisWeek - $lastWeek) / $lastWeek) * 100 : 0;

        return [
            'current' => $thisWeek,
            'previous' => $lastWeek,
            'change_percentage' => round($change, 2),
            'trend' => $change >= 0 ? 'up' : 'down',
        ];
    }

    /**
     * Get monthly production trend
     */
    private function getMonthlyTrend(SolarSystem $system): array
    {
        $thisMonth = Production::where('solar_system_id', $system->id)
            ->whereMonth('production_date', Carbon::now()->month)
            ->whereYear('production_date', Carbon::now()->year)
            ->sum('energy_produced_kwh');

        $lastMonth = Production::where('solar_system_id', $system->id)
            ->whereMonth('production_date', Carbon::now()->subMonth()->month)
            ->whereYear('production_date', Carbon::now()->subMonth()->year)
            ->sum('energy_produced_kwh');

        $change = $lastMonth > 0 ? (($thisMonth - $lastMonth) / $lastMonth) * 100 : 0;

        return [
            'current' => $thisMonth,
            'previous' => $lastMonth,
            'change_percentage' => round($change, 2),
            'trend' => $change >= 0 ? 'up' : 'down',
        ];
    }

    /**
     * Calculate financial savings based on production
     */
    public function calculateSavings(float $energyProducedKwh, float $electricityRate = 0.12): float
    {
        return $energyProducedKwh * $electricityRate;
    }

    /**
     * Calculate CO2 offset (kg)
     * Average: 0.4 kg CO2 per kWh
     */
    public function calculateCO2Offset(float $energyProducedKwh): float
    {
        return $energyProducedKwh * 0.4;
    }

    /**
     * Get production summary for a date range
     */
    public function getProductionSummary(SolarSystem $system, Carbon $startDate, Carbon $endDate): array
    {
        $productions = Production::where('solar_system_id', $system->id)
            ->whereBetween('production_date', [$startDate, $endDate])
            ->get();

        $totalProduced = $productions->sum('energy_produced_kwh');
        $totalConsumed = $productions->sum('energy_consumed_kwh');
        $netEnergy = $totalProduced - $totalConsumed;

        $days = $startDate->diffInDays($endDate) + 1;
        $expectedProduction = $this->calculateExpectedDailyProduction($system->total_capacity_kw) * $days;

        return [
            'total_produced_kwh' => round($totalProduced, 2),
            'total_consumed_kwh' => round($totalConsumed, 2),
            'net_energy_kwh' => round($netEnergy, 2),
            'expected_production_kwh' => round($expectedProduction, 2),
            'efficiency_percentage' => round($this->calculateEfficiency($totalProduced, $expectedProduction), 2),
            'average_daily_production' => round($totalProduced / $days, 2),
            'peak_production_day' => $productions->max('energy_produced_kwh') ?? 0,
            'savings_estimate' => round($this->calculateSavings($totalProduced), 2),
            'co2_offset_kg' => round($this->calculateCO2Offset($totalProduced), 2),
        ];
    }

    /**
     * Check if production is within normal range
     */
    public function isProductionNormal(float $actualProduction, float $expectedProduction, float $tolerance = 0.2): bool
    {
        $lowerBound = $expectedProduction * (1 - $tolerance);

        return $actualProduction >= $lowerBound;
    }

    /**
     * Detect underperforming panels
     */
    public function detectUnderperformingPanels(SolarSystem $system, float $threshold = 0.5): array
    {
        $underperforming = [];

        foreach ($system->panels as $panel) {
            $efficiency = $this->calculatePanelEfficiency($panel);

            if ($efficiency < $threshold * 100) {
                $underperforming[] = [
                    'panel' => $panel,
                    'efficiency' => $efficiency,
                ];
            }
        }

        return $underperforming;
    }
}
