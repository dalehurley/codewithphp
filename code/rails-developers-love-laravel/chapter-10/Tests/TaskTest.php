<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Task;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_can_list_user_tasks(): void
    {
        Task::factory()->count(3)->create(['user_id' => $this->user->id]);
        Task::factory()->count(2)->create(); // Other users' tasks

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/tasks');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_can_create_task(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/tasks', [
                'title' => 'Test Task',
                'description' => 'Test Description',
                'priority' => 'high',
            ]);

        $response->assertCreated()
            ->assertJson([
                'title' => 'Test Task',
                'priority' => 'high',
            ]);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Test Task',
        ]);
    }

    public function test_can_update_task(): void
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/tasks/{$task->id}", [
                'title' => 'Updated Title',
            ]);

        $response->assertOk()
            ->assertJson(['title' => 'Updated Title']);
    }

    public function test_can_delete_task(): void
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/tasks/{$task->id}");

        $response->assertNoContent();
        $this->assertNull(Task::find($task->id));
    }

    public function test_cannot_access_other_users_tasks(): void
    {
        $otherUser = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/tasks/{$task->id}");

        $response->assertForbidden();
    }

    public function test_can_mark_task_as_completed(): void
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/tasks/{$task->id}/complete");

        $response->assertOk();

        $task->refresh();
        $this->assertEquals('completed', $task->status);
        $this->assertNotNull($task->completed_at);
    }

    public function test_can_filter_tasks_by_status(): void
    {
        Task::factory()->completed()->count(2)->create(['user_id' => $this->user->id]);
        Task::factory()->count(3)->create(['user_id' => $this->user->id, 'status' => 'pending']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/tasks?status=completed');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_can_search_tasks(): void
    {
        Task::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Laravel development',
        ]);
        Task::factory()->count(2)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/tasks?search=Laravel');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_requires_authentication(): void
    {
        $response = $this->getJson('/api/tasks');

        $response->assertUnauthorized();
    }

    public function test_validates_task_creation(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/tasks', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['title']);
    }
}







