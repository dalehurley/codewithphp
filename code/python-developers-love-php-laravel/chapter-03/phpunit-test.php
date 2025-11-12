<?php

/**
 * PHPUnit Test Examples
 * 
 * This file demonstrates PHPUnit testing in Laravel.
 * Compare to pytest tests (pytest-test.py).
 * 
 * To run tests:
 * php artisan test
 * 
 * To run specific test:
 * php artisan test --filter test_user_creation
 * 
 * To run with coverage:
 * php artisan test --coverage
 */

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

/**
 * Example: Feature Test (HTTP/Integration)
 * 
 * Feature tests test HTTP endpoints and integration.
 * Django equivalent: Django TestCase with client
 */
class UserApiTest extends TestCase
{
    use RefreshDatabase; // Resets database after each test

    /**
     * Test creating a user via API
     * 
     * pytest equivalent: def test_user_creation(client):
     */
    public function test_user_can_be_created_via_api(): void
    {
        $response = $this->postJson('/api/users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(201)
                 ->assertJson([
                     'name' => 'John Doe',
                     'email' => 'john@example.com',
                 ]);

        // Assert database has the user
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
    }

    /**
     * Test retrieving a user
     */
    public function test_user_can_be_retrieved(): void
    {
        $user = User::factory()->create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);

        $response = $this->getJson("/api/users/{$user->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'id' => $user->id,
                     'name' => 'Jane Doe',
                     'email' => 'jane@example.com',
                 ]);
    }

    /**
     * Test updating a user
     */
    public function test_user_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this->putJson("/api/users/{$user->id}", [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'name' => 'Updated Name',
                     'email' => 'updated@example.com',
                 ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    /**
     * Test deleting a user
     */
    public function test_user_can_be_deleted(): void
    {
        $user = User::factory()->create();

        $response = $this->deleteJson("/api/users/{$user->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    /**
     * Test validation errors
     */
    public function test_user_creation_requires_valid_email(): void
    {
        $response = $this->postJson('/api/users', [
            'name' => 'John Doe',
            'email' => 'invalid-email', // Invalid email
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }
}

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Services\UserService;

/**
 * Example: Unit Test (Isolated)
 * 
 * Unit tests test isolated code without HTTP layer.
 * pytest equivalent: def test_user_service():
 */
class UserServiceTest extends TestCase
{
    /**
     * Test user service creates user
     */
    public function test_user_service_creates_user(): void
    {
        $service = new UserService();
        $user = $service->createUser('John Doe', 'john@example.com');

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
    }

    /**
     * Test user service finds user by email
     */
    public function test_user_service_finds_user_by_email(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        $service = new UserService();
        $user = $service->findByEmail('test@example.com');

        $this->assertNotNull($user);
        $this->assertEquals('test@example.com', $user->email);
    }
}

/**
 * Example: Assertions Comparison
 */
class AssertionsExample extends TestCase
{
    public function test_assertions(): void
    {
        $user = User::factory()->create();

        // Equality assertions
        $this->assertEquals('John Doe', $user->name);
        $this->assertNotEquals('Jane Doe', $user->name);

        // Null assertions
        $this->assertNull($user->deleted_at);
        $this->assertNotNull($user->id);

        // Boolean assertions
        $this->assertTrue($user->isActive());
        $this->assertFalse($user->isDeleted());

        // Type assertions
        $this->assertInstanceOf(User::class, $user);
        $this->assertIsString($user->name);
        $this->assertIsInt($user->id);

        // Array/Collection assertions
        $users = User::factory()->count(3)->create();
        $this->assertCount(3, $users);
        $this->assertContains($user, $users);

        // Database assertions
        $this->assertDatabaseHas('users', [
            'email' => $user->email,
        ]);
        $this->assertDatabaseMissing('users', [
            'email' => 'nonexistent@example.com',
        ]);
    }
}

