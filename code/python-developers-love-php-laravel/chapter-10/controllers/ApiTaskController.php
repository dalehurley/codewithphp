<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TaskController extends Controller
{
    /**
     * Display a listing of the user's tasks.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = $request->user()->tasks()->latest();

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->has('priority') && $request->priority !== '') {
            $query->where('priority', $request->priority);
        }

        // Search
        if ($request->has('search') && $request->search !== '') {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        $tasks = $query->paginate(10);

        return TaskResource::collection($tasks);
    }

    /**
     * Store a newly created task.
     */
    public function store(StoreTaskRequest $request): TaskResource
    {
        $task = $request->user()->tasks()->create($request->validated());

        return new TaskResource($task);
    }

    /**
     * Display the specified task.
     */
    public function show(Task $task): TaskResource
    {
        // Ensure user can only view their own tasks
        if ($task->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        return new TaskResource($task);
    }

    /**
     * Update the specified task.
     */
    public function update(UpdateTaskRequest $request, Task $task): TaskResource
    {
        // Ensure user can only update their own tasks
        if ($task->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $task->update($request->validated());

        return new TaskResource($task);
    }

    /**
     * Remove the specified task.
     */
    public function destroy(Task $task): JsonResponse
    {
        // Ensure user can only delete their own tasks
        if ($task->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $task->delete();

        return response()->json([
            'message' => 'Task deleted successfully',
        ], 204);
    }

    /**
     * Toggle task completion status.
     */
    public function toggleComplete(Task $task): TaskResource
    {
        if ($task->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $task->update([
            'status' => $task->status === 'completed' ? 'pending' : 'completed',
        ]);

        return new TaskResource($task);
    }

    /**
     * Get task statistics for authenticated user.
     */
    public function statistics(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'total' => $user->tasks()->count(),
            'pending' => $user->tasks()->where('status', 'pending')->count(),
            'in_progress' => $user->tasks()->where('status', 'in_progress')->count(),
            'completed' => $user->tasks()->where('status', 'completed')->count(),
            'high_priority' => $user->tasks()->where('priority', 'high')->count(),
            'overdue' => $user->tasks()
                ->where('due_date', '<', now())
                ->where('status', '!=', 'completed')
                ->count(),
        ]);
    }
}

