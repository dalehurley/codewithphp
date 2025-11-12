<?php

/**
 * Laravel Factory Examples
 * 
 * This file demonstrates Laravel factories.
 * Compare to Django factories (factory_boy).
 * 
 * To create a factory:
 * php artisan make:factory UserFactory
 * 
 * To use in tests:
 * User::factory()->create();
 * User::factory()->count(10)->create();
 */

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Example: Basic Factory
 * 
 * Django equivalent: myapp/factories.py (using factory_boy)
 */
class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     * 
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => bcrypt('password'), // Default password
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the user's email should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user is an admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_admin' => true,
        ]);
    }
}

/**
 * Example: Factory with Relationships
 */
use App\Models\Post;

class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(), // Automatically create user
            'title' => fake()->sentence(),
            'content' => fake()->paragraphs(3, true),
            'published' => fake()->boolean(),
        ];
    }

    /**
     * Indicate that the post is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'published' => true,
        ]);
    }

    /**
     * Indicate that the post is unpublished.
     */
    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => [
            'published' => false,
        ]);
    }
}

/**
 * Example: Using Factories in Tests
 * 
 * In your test file:
 */
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Post;

class FactoryUsageExample extends TestCase
{
    public function test_factory_creates_user(): void
    {
        // Create a single user
        $user = User::factory()->create();
        
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
        ]);
    }

    public function test_factory_creates_multiple_users(): void
    {
        // Create 10 users
        $users = User::factory()->count(10)->create();
        
        $this->assertCount(10, $users);
    }

    public function test_factory_with_custom_attributes(): void
    {
        // Create user with specific attributes
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
        
        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
    }

    public function test_factory_with_states(): void
    {
        // Create unverified user
        $unverifiedUser = User::factory()->unverified()->create();
        $this->assertNull($unverifiedUser->email_verified_at);

        // Create admin user
        $adminUser = User::factory()->admin()->create();
        $this->assertTrue($adminUser->is_admin);
    }

    public function test_factory_with_relationships(): void
    {
        // Create post with user (user is created automatically)
        $post = Post::factory()->create();
        
        $this->assertNotNull($post->user);
        $this->assertInstanceOf(User::class, $post->user);
    }

    public function test_factory_with_existing_relationship(): void
    {
        // Create post with existing user
        $user = User::factory()->create();
        $post = Post::factory()->create([
            'user_id' => $user->id,
        ]);
        
        $this->assertEquals($user->id, $post->user_id);
    }

    public function test_factory_with_multiple_relationships(): void
    {
        // Create user with 5 posts
        $user = User::factory()
            ->has(Post::factory()->count(5))
            ->create();
        
        $this->assertCount(5, $user->posts);
    }
}

