<?php

namespace Tests\Feature;

use App\Models\SolarSystem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SolarSystemManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function authenticated_user_can_view_solar_systems_list()
    {
        SolarSystem::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->get('/solar-systems');

        $response->assertStatus(200);
        $response->assertViewIs('solar-systems.index');
    }

    /** @test */
    public function guest_cannot_view_solar_systems()
    {
        $response = $this->get('/solar-systems');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function user_can_create_solar_system()
    {
        $response = $this->actingAs($this->user)->post('/solar-systems', [
            'name' => 'Home Solar System',
            'location' => '123 Main St',
            'total_capacity_kw' => 5.5,
            'installation_date' => '2024-01-15',
            'description' => 'Main home solar installation',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('solar_systems', [
            'name' => 'Home Solar System',
            'user_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function solar_system_requires_name()
    {
        $response = $this->actingAs($this->user)->post('/solar-systems', [
            'name' => '',
            'location' => '123 Main St',
            'total_capacity_kw' => 5.5,
            'installation_date' => '2024-01-15',
        ]);

        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function solar_system_requires_location()
    {
        $response = $this->actingAs($this->user)->post('/solar-systems', [
            'name' => 'Home Solar',
            'location' => '',
            'total_capacity_kw' => 5.5,
            'installation_date' => '2024-01-15',
        ]);

        $response->assertSessionHasErrors('location');
    }

    /** @test */
    public function solar_system_requires_positive_capacity()
    {
        $response = $this->actingAs($this->user)->post('/solar-systems', [
            'name' => 'Home Solar',
            'location' => '123 Main St',
            'total_capacity_kw' => 0,
            'installation_date' => '2024-01-15',
        ]);

        $response->assertSessionHasErrors('total_capacity_kw');
    }

    /** @test */
    public function solar_system_requires_valid_installation_date()
    {
        $response = $this->actingAs($this->user)->post('/solar-systems', [
            'name' => 'Home Solar',
            'location' => '123 Main St',
            'total_capacity_kw' => 5.5,
            'installation_date' => 'invalid-date',
        ]);

        $response->assertSessionHasErrors('installation_date');
    }

    /** @test */
    public function installation_date_cannot_be_in_future()
    {
        $response = $this->actingAs($this->user)->post('/solar-systems', [
            'name' => 'Home Solar',
            'location' => '123 Main St',
            'total_capacity_kw' => 5.5,
            'installation_date' => now()->addDay()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('installation_date');
    }

    /** @test */
    public function user_can_view_own_solar_system()
    {
        $system = SolarSystem::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->get("/solar-systems/{$system->id}");

        $response->assertStatus(200);
        $response->assertViewIs('solar-systems.show');
        $response->assertSee($system->name);
    }

    /** @test */
    public function user_cannot_view_others_solar_system()
    {
        $otherUser = User::factory()->create();
        $system = SolarSystem::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)->get("/solar-systems/{$system->id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function user_can_update_own_solar_system()
    {
        $system = SolarSystem::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->put("/solar-systems/{$system->id}", [
            'name' => 'Updated Name',
            'location' => $system->location,
            'total_capacity_kw' => $system->total_capacity_kw,
            'status' => 'active',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('solar_systems', [
            'id' => $system->id,
            'name' => 'Updated Name',
        ]);
    }

    /** @test */
    public function user_cannot_update_others_solar_system()
    {
        $otherUser = User::factory()->create();
        $system = SolarSystem::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)->put("/solar-systems/{$system->id}", [
            'name' => 'Updated Name',
            'location' => $system->location,
            'total_capacity_kw' => $system->total_capacity_kw,
            'status' => 'active',
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function user_can_delete_own_solar_system()
    {
        $system = SolarSystem::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->delete("/solar-systems/{$system->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('solar_systems', [
            'id' => $system->id,
        ]);
    }

    /** @test */
    public function user_cannot_delete_others_solar_system()
    {
        $otherUser = User::factory()->create();
        $system = SolarSystem::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)->delete("/solar-systems/{$system->id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_view_any_solar_system()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $system = SolarSystem::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($admin)->get("/solar-systems/{$system->id}");

        $response->assertStatus(200);
    }

    /** @test */
    public function solar_system_latitude_must_be_valid()
    {
        $response = $this->actingAs($this->user)->post('/solar-systems', [
            'name' => 'Home Solar',
            'location' => '123 Main St',
            'total_capacity_kw' => 5.5,
            'installation_date' => '2024-01-15',
            'latitude' => 100, // Invalid: must be between -90 and 90
        ]);

        $response->assertSessionHasErrors('latitude');
    }

    /** @test */
    public function solar_system_longitude_must_be_valid()
    {
        $response = $this->actingAs($this->user)->post('/solar-systems', [
            'name' => 'Home Solar',
            'location' => '123 Main St',
            'total_capacity_kw' => 5.5,
            'installation_date' => '2024-01-15',
            'longitude' => 200, // Invalid: must be between -180 and 180
        ]);

        $response->assertSessionHasErrors('longitude');
    }
}
