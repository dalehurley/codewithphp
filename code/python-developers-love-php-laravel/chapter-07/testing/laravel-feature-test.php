<?php

declare(strict_types=1);

/**
 * Laravel feature test example for HTTP API testing.
 * 
 * Run with: php artisan test tests/Feature/UserApiTest.php
 */

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_users(): void
    {
        User::factory()->count(3)->create();
        
        $response = $this->getJson('/api/users');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'email']
                ]
            ])
            ->assertJsonCount(3, 'data');
    }

    public function test_can_create_user(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123'
        ];
        
        $response = $this->postJson('/api/users', $userData);
        
        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'name' => 'John Doe',
                    'email' => 'john@example.com'
                ]
            ]);
        
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com'
        ]);
    }

    public function test_can_update_user(): void
    {
        $user = User::factory()->create();
        
        $response = $this->putJson("/api/users/{$user->id}", [
            'name' => 'Updated Name'
        ]);
        
        $response->assertStatus(200);
        
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name'
        ]);
    }

    public function test_can_delete_user(): void
    {
        $user = User::factory()->create();
        
        $response = $this->deleteJson("/api/users/{$user->id}");
        
        $response->assertStatus(204);
        
        $this->assertDatabaseMissing('users', [
            'id' => $user->id
        ]);
    }
}

