<?php

declare(strict_types=1);

/**
 * Laravel model factories example comparing to Django factories.
 * 
 * Factory file: database/factories/UserFactory.php
 * Usage shown in tests below.
 */

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
        ]);
    }
}

// Usage in tests
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_user(): void
    {
        $user = User::factory()->create();
        
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => $user->email
        ]);
    }

    public function test_can_create_multiple_users(): void
    {
        User::factory()->count(5)->create();
        
        $this->assertDatabaseCount('users', 5);
    }

    public function test_can_create_unverified_user(): void
    {
        $user = User::factory()->unverified()->create();
        
        $this->assertNull($user->email_verified_at);
    }

    public function test_can_create_admin_user(): void
    {
        $admin = User::factory()->admin()->create();
        
        $this->assertEquals('admin', $admin->role);
    }
}

