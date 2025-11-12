<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user can register via API.
     */
    public function test_user_can_register_via_api(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'API User',
            'email' => 'api@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'user',
            'token',
            'message',
        ]);
        $this->assertDatabaseHas('users', ['email' => 'api@example.com']);
    }

    /**
     * Test user can login via API.
     */
    public function test_user_can_login_via_api(): void
    {
        $user = User::factory()->create([
            'email' => 'api@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'api@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'user',
            'token',
            'message',
        ]);
    }

    /**
     * Test authenticated user can get their tasks.
     */
    public function test_authenticated_user_can_get_tasks(): void
    {
        $user = User::factory()->create();
        Task::factory()->count(5)->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/tasks');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'status', 'priority'],
            ],
        ]);
    }

    /**
     * Test authenticated user can create task via API.
     */
    public function test_authenticated_user_can_create_task(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/tasks', [
            'title' => 'API Task',
            'description' => 'Created via API',
            'status' => 'pending',
            'priority' => 'high',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => ['id', 'title', 'status', 'priority'],
        ]);
        $this->assertDatabaseHas('tasks', [
            'title' => 'API Task',
            'user_id' => $user->id,
        ]);
    }

    /**
     * Test unauthenticated user cannot access tasks.
     */
    public function test_unauthenticated_user_cannot_access_tasks(): void
    {
        $response = $this->getJson('/api/tasks');

        $response->assertStatus(401);
    }

    /**
     * Test user can get task statistics.
     */
    public function test_user_can_get_task_statistics(): void
    {
        $user = User::factory()->create();
        Task::factory()->count(3)->create(['user_id' => $user->id, 'status' => 'pending']);
        Task::factory()->count(2)->create(['user_id' => $user->id, 'status' => 'completed']);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/tasks/statistics');

        $response->assertStatus(200);
        $response->assertJson([
            'total' => 5,
            'pending' => 3,
            'completed' => 2,
        ]);
    }
}

