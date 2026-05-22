<?php

namespace Tests\Unit\Services;

use App\Models\Panel;
use App\Models\Production;
use App\Models\SolarSystem;
use App\Models\User;
use App\Services\ProductionCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionCalculatorTest extends TestCase
{
    use RefreshDatabase;

    protected ProductionCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new ProductionCalculator;
    }

    /** @test */
    public function it_calculates_expected_daily_production()
    {
        $capacityKw = 5.0;
        $peakSunHours = 5;

        $expected = $this->calculator->calculateExpectedDailyProduction($capacityKw, $peakSunHours);

        $this->assertEquals(25.0, $expected);
    }

    /** @test */
    public function it_calculates_expected_panel_production()
    {
        $capacityWatts = 300;
        $peakSunHours = 5;

        $expected = $this->calculator->calculateExpectedPanelProduction($capacityWatts, $peakSunHours);

        $this->assertEquals(1.5, $expected);
    }

    /** @test */
    public function it_calculates_efficiency_percentage()
    {
        $actual = 20;
        $expected = 25;

        $efficiency = $this->calculator->calculateEfficiency($actual, $expected);

        $this->assertEquals(80.0, $efficiency);
    }

    /** @test */
    public function it_returns_zero_efficiency_when_expected_is_zero()
    {
        $efficiency = $this->calculator->calculateEfficiency(10, 0);

        $this->assertEquals(0, $efficiency);
    }

    /** @test */
    public function it_caps_efficiency_at_100_percent()
    {
        $efficiency = $this->calculator->calculateEfficiency(30, 20);

        $this->assertEquals(100.0, $efficiency);
    }

    /** @test */
    public function it_calculates_system_efficiency()
    {
        $user = User::factory()->create();
        $system = SolarSystem::factory()->create([
            'user_id' => $user->id,
            'total_capacity_kw' => 5,
        ]);

        // Create production record for today
        Production::factory()->create([
            'solar_system_id' => $system->id,
            'production_date' => today(),
            'energy_produced_kwh' => 20,
        ]);

        $efficiency = $this->calculator->calculateSystemEfficiency($system);

        // Expected: 5 kW * 5 hours = 25 kWh
        // Actual: 20 kWh
        // Efficiency: (20/25) * 100 = 80%
        $this->assertEquals(80.0, $efficiency);
    }

    /** @test */
    public function it_calculates_daily_trend()
    {
        $user = User::factory()->create();
        $system = SolarSystem::factory()->create(['user_id' => $user->id]);

        Production::factory()->create([
            'solar_system_id' => $system->id,
            'production_date' => today(),
            'energy_produced_kwh' => 25,
        ]);

        Production::factory()->create([
            'solar_system_id' => $system->id,
            'production_date' => today()->subDay(),
            'energy_produced_kwh' => 20,
        ]);

        $trend = $this->calculator->getProductionTrend($system, 'day');

        $this->assertEquals(25, $trend['current']);
        $this->assertEquals(20, $trend['previous']);
        $this->assertEquals(25.0, $trend['change_percentage']);
        $this->assertEquals('up', $trend['trend']);
    }

    /** @test */
    public function it_calculates_financial_savings()
    {
        $energyProduced = 100; // kWh
        $electricityRate = 0.12; // $ per kWh

        $savings = $this->calculator->calculateSavings($energyProduced, $electricityRate);

        $this->assertEquals(12.0, $savings);
    }

    /** @test */
    public function it_calculates_co2_offset()
    {
        $energyProduced = 100; // kWh

        $co2Offset = $this->calculator->calculateCO2Offset($energyProduced);

        $this->assertEquals(40.0, $co2Offset);
    }

    /** @test */
    public function it_detects_production_as_normal()
    {
        $actual = 22;
        $expected = 25;

        $isNormal = $this->calculator->isProductionNormal($actual, $expected, 0.2);

        $this->assertTrue($isNormal);
    }

    /** @test */
    public function it_detects_production_as_abnormal()
    {
        $actual = 15;
        $expected = 25;

        $isNormal = $this->calculator->isProductionNormal($actual, $expected, 0.2);

        $this->assertFalse($isNormal);
    }

    /** @test */
    public function it_gets_production_summary()
    {
        $user = User::factory()->create();
        $system = SolarSystem::factory()->create([
            'user_id' => $user->id,
            'total_capacity_kw' => 5,
        ]);

        $startDate = today()->subDays(7);
        $endDate = today();

        // Create production records
        Production::factory()->count(7)->create([
            'solar_system_id' => $system->id,
            'energy_produced_kwh' => 20,
            'energy_consumed_kwh' => 5,
        ]);

        $summary = $this->calculator->getProductionSummary($system, $startDate, $endDate);

        $this->assertArrayHasKey('total_produced_kwh', $summary);
        $this->assertArrayHasKey('total_consumed_kwh', $summary);
        $this->assertArrayHasKey('net_energy_kwh', $summary);
        $this->assertArrayHasKey('efficiency_percentage', $summary);
        $this->assertArrayHasKey('savings_estimate', $summary);
        $this->assertArrayHasKey('co2_offset_kg', $summary);
    }

    /** @test */
    public function it_detects_underperforming_panels()
    {
        $user = User::factory()->create();
        $system = SolarSystem::factory()->create(['user_id' => $user->id]);

        $panel1 = Panel::factory()->create([
            'solar_system_id' => $system->id,
            'capacity_watts' => 300,
        ]);

        $panel2 = Panel::factory()->create([
            'solar_system_id' => $system->id,
            'capacity_watts' => 300,
        ]);

        // Panel 1 has low production (10% efficiency)
        Production::factory()->create([
            'solar_system_id' => $system->id,
            'panel_id' => $panel1->id,
            'production_date' => today(),
            'energy_produced_kwh' => 0.15, // Very low
        ]);

        // Panel 2 has good production
        Production::factory()->create([
            'solar_system_id' => $system->id,
            'panel_id' => $panel2->id,
            'production_date' => today(),
            'energy_produced_kwh' => 1.2,
        ]);

        $underperforming = $this->calculator->detectUnderperformingPanels($system, 0.5);

        $this->assertCount(1, $underperforming);
        $this->assertEquals($panel1->id, $underperforming[0]['panel']->id);
    }
}
