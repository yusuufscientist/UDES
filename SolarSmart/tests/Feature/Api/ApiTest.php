<?php

namespace Tests\Feature\Api;

use App\Models\SolarSystem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected SolarSystem $system;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->system = SolarSystem::factory()->create(['user_id' => $this->user->id]);
    }

    /** @test */
    public function api_requires_authentication()
    {
        $response = $this->getJson('/api/solar-systems');

        $response->assertStatus(401);
    }

    /** @test */
    public function authenticated_user_can_list_solar_systems_via_api()
    {
        $this->actingAs($this->user, 'web');

        $response = $this->getJson('/api/solar-systems');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'name', 'location', 'total_capacity_kw', 'status'],
                ],
            ]);
    }

    /** @test */
    public function authenticated_user_can_create_solar_system_via_api()
    {
        $this->actingAs($this->user, 'web');

        $response = $this->postJson('/api/solar-systems', [
            'name' => 'Test System',
            'location' => 'Test Location',
            'total_capacity_kw' => 6.0,
            'installation_date' => '2024-01-01',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Solar system created successfully',
            ]);
    }

    /** @test */
    public function api_validates_solar_system_creation()
    {
        $this->actingAs($this->user, 'web');

        $response = $this->postJson('/api/solar-systems', [
            'name' => '',
            'location' => '',
            'total_capacity_kw' => -1,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'location', 'total_capacity_kw']);
    }

    /** @test */
    public function authenticated_user_can_view_solar_system_via_api()
    {
        $this->actingAs($this->user, 'web');

        $response = $this->getJson("/api/solar-systems/{$this->system->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $this->system->id,
                    'name' => $this->system->name,
                ],
            ]);
    }

    /** @test */
    public function user_cannot_view_others_solar_system_via_api()
    {
        $otherUser = User::factory()->create();
        $otherSystem = SolarSystem::factory()->create(['user_id' => $otherUser->id]);

        $this->actingAs($this->user, 'web');

        $response = $this->getJson("/api/solar-systems/{$otherSystem->id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function authenticated_user_can_update_solar_system_via_api()
    {
        $this->actingAs($this->user, 'web');

        $response = $this->putJson("/api/solar-systems/{$this->system->id}", [
            'name' => 'Updated System',
            'location' => $this->system->location,
            'total_capacity_kw' => $this->system->total_capacity_kw,
            'status' => 'active',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Solar system updated successfully',
            ]);
    }

    /** @test */
    public function authenticated_user_can_delete_solar_system_via_api()
    {
        $this->actingAs($this->user, 'web');

        $response = $this->deleteJson("/api/solar-systems/{$this->system->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Solar system deleted successfully',
            ]);
    }

    /** @test */
    public function authenticated_user_can_get_production_summary_via_api()
    {
        $this->actingAs($this->user, 'web');

        $response = $this->getJson("/api/solar-systems/{$this->system->id}/production-summary");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    }

    /** @test */
    public function authenticated_user_can_list_productions_via_api()
    {
        $this->actingAs($this->user, 'web');

        $response = $this->getJson("/api/solar-systems/{$this->system->id}/productions");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => ['id', 'energy_produced_kwh', 'production_date', 'production_time'],
                    ],
                ],
            ]);
    }

    /** @test */
    public function authenticated_user_can_create_production_via_api()
    {
        $this->actingAs($this->user, 'web');

        $response = $this->postJson("/api/solar-systems/{$this->system->id}/productions", [
            'production_date' => '2024-01-01',
            'production_time' => '12:00:00',
            'energy_produced_kwh' => 25.5,
            'energy_consumed_kwh' => 5.0,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Production record created successfully',
            ]);
    }

    /** @test */
    public function api_returns_chart_data()
    {
        $this->actingAs($this->user, 'web');

        $response = $this->getJson("/api/solar-systems/{$this->system->id}/productions/chart/data?period=week");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'labels',
                    'production',
                ],
            ]);
    }
}
