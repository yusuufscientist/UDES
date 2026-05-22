<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_user()
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'role' => 'user',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'role' => 'user',
        ]);

        $this->assertEquals('John Doe', $user->name);
    }

    /** @test */
    public function it_can_check_if_user_is_admin()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($user->isAdmin());
    }

    /** @test */
    public function it_can_check_if_user_is_technician()
    {
        $technician = User::factory()->create(['role' => 'technician']);
        $user = User::factory()->create(['role' => 'user']);

        $this->assertTrue($technician->isTechnician());
        $this->assertFalse($user->isTechnician());
    }

    /** @test */
    public function it_can_check_if_user_is_regular_user()
    {
        $user = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->assertTrue($user->isUser());
        $this->assertFalse($admin->isUser());
    }

    /** @test */
    public function it_has_solar_systems_relationship()
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $user->solarSystems());
    }

    /** @test */
    public function it_has_interventions_relationship()
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $user->interventions());
    }

    /** @test */
    public function it_casts_is_active_to_boolean()
    {
        $user = User::factory()->create(['is_active' => true]);

        $this->assertIsBool($user->is_active);
        $this->assertTrue($user->is_active);
    }

    /** @test */
    public function it_hashes_password_automatically()
    {
        $user = User::factory()->create([
            'password' => 'password123',
        ]);

        $this->assertNotEquals('password123', $user->password);
        $this->assertTrue(password_verify('password123', $user->password));
    }
}
