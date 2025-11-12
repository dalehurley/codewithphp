<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user can view their tasks.
     */
    public function test_user_can_view_tasks_index(): void
    {
        $user = User::factory()->create();
        $tasks = Task::factory()->count(5)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/tasks');

        $response->assertStatus(200);
        $response->assertViewIs('tasks.index');
        $response->assertViewHas('tasks');
    }

    /**
     * Test user can create a task.
     */
    public function test_user_can_create_task(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/tasks', [
            'title' => 'Test Task',
            'description' => 'Test Description',
            'status' => 'pending',
            'priority' => 'medium',
        ]);

        $response->assertRedirect('/tasks');
        $this->assertDatabaseHas('tasks', [
            'title' => 'Test Task',
            'user_id' => $user->id,
        ]);
    }

    /**
     * Test user can update their task.
     */
    public function test_user_can_update_task(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->put("/tasks/{$task->id}", [
            'title' => 'Updated Task',
            'status' => 'completed',
            'priority' => 'high',
        ]);

        $response->assertRedirect('/tasks');
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated Task',
            'status' => 'completed',
        ]);
    }

    /**
     * Test user can delete their task.
     */
    public function test_user_can_delete_task(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete("/tasks/{$task->id}");

        $response->assertRedirect('/tasks');
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    /**
     * Test user cannot view other users' tasks.
     */
    public function test_user_cannot_view_other_users_tasks(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user2->id]);

        $response = $this->actingAs($user1)->get("/tasks/{$task->id}");

        $response->assertStatus(403);
    }
}

