---
title: "18: Tasks Module - CRUD & Task Scheduling"
description: "Build task management UI with CRUD operations and implement Laravel scheduler for automated email reminders"
series: "build-crm-laravel-12"
chapter: 18
order: 18
difficulty: "Intermediate"
prerequisites:
  - "/series/build-crm-laravel-12/chapters/17-tasks-module-database-model"
estimatedTime: "PT110M"
teaches:
  - 'Build a TaskController with full CRUD operations and quick-complete endpoint'
  - 'Create a Task policy enforcing team-based authorization on all actions'
  - 'Implement FormRequest validation with polymorphic relationship rules'
  - 'Design task management views in React with filters and quick actions'
  - 'Build quick-add task components for inline task creation'
---
![Tasks Module - CRUD & Task Scheduling](/images/build-crm-laravel-12/chapter-18-tasks-module-crud-task-scheduling-hero-full.webp)

# Chapter 18: Tasks Module - CRUD & Task Scheduling

## Overview

Your task data model is ready—now it's time to build the interface that makes it usable. The Task Management UI is where productivity happens: creating follow-up calls, setting due dates, marking tasks complete, and viewing what needs attention today. But great task management isn't just about manual tracking—it's about automation that keeps nothing falling through the cracks.

In this chapter, you'll build a complete Task management system with Create, Read, Update, and Delete operations, plus Laravel's powerful Task Scheduler to send automated email reminders for overdue tasks. You'll implement filters showing "My Tasks," "Overdue," "Due Today," and "Completed," create quick-add task forms embedded in contact/company/deal views, and set up cron jobs running daily to notify users about pending work.

By the end of this chapter, your sales team will be able to:

- **View all tasks** in a filterable list with status, priority, and due date indicators
- **Create tasks** linked to contacts, companies, or deals with one click
- **Quick-add tasks** directly from entity detail pages without leaving context
- **Mark tasks complete** with a single click, automatically updating timestamps
- **Receive daily email reminders** for overdue and due-today tasks automatically
- **Filter tasks** by status, priority, assignee, and entity type
- **Sort tasks** by due date, priority, or creation date

This chapter combines CRUD patterns you've mastered with Laravel's Task Scheduler—a powerful cron alternative built into the framework. You're building operational excellence into your CRM.

## Prerequisites

Before starting this chapter, you should have:

- ✅ Completed [Chapter 17](/series/build-crm-laravel-12/chapters/17-tasks-module-database-model) with Task model and relationships configured
- ✅ Completed [Chapter 14](/series/build-crm-laravel-12/chapters/14-companies-module-crud-operations) to understand CRUD patterns
- ✅ Completed [Chapter 09](/series/build-crm-laravel-12/chapters/09-authorization-access-control) to understand authorization
- ✅ Laravel Sail running with all containers active
- ✅ Database migrations applied with tasks table populated
- ✅ Understanding of Laravel controllers, policies, and Artisan commands
- ✅ Basic knowledge of cron jobs and scheduled tasks

**Estimated Time**: ~110 minutes (includes controller, policy, routes, views, quick-add forms, scheduler setup, and email reminders)

**Verify your setup:**

```bash
# Navigate to your project
cd crm-app

# Verify Sail is running
sail ps

# Verify Task model works
sail artisan tinker
$task = App\Models\Task::first();
echo $task->title;
$task->taskable;  # Should return related entity
exit
```

## What You'll Build

By the end of this chapter, you will have:

**Backend Controllers & Security**:

- ✅ **TaskController** with resourceful CRUD methods
- ✅ **TaskPolicy** authorizing all actions at the team level
- ✅ **TaskRequest FormRequest** validating task data with team-specific rules
- ✅ **Quick-complete endpoint** marking tasks done with one request
- ✅ **Team-scoped queries** ensuring users only see their team's tasks
- ✅ **Eager loading** preventing N+1 queries on all task views

**Frontend Views (React/Inertia)**:

- ✅ **Tasks Index** listing all tasks with filters and search
- ✅ **Task Create/Edit** form with entity selection and date pickers
- ✅ **Task Show** displaying complete details with related entity link
- ✅ **Quick-add task component** embedded in Contact/Company/Deal views
- ✅ **Task list component** showing tasks for an entity on detail pages
- ✅ **Status toggle** allowing one-click completion
- ✅ **Priority badges** with color coding (low=gray, normal=blue, high=orange, urgent=red)

**Laravel Task Scheduler**:

- ✅ **SendTaskReminders** command checking for overdue/due-today tasks
- ✅ **TaskReminder notification** sending formatted emails to assignees
- ✅ **Scheduled task** running daily at 8 AM checking all teams
- ✅ **Cron setup** triggering Laravel scheduler every minute
- ✅ **Email templates** with task details and CRM links

**Integration**:

- ✅ **Resourceful routes** for tasks using `Route::resource()`
- ✅ **Quick-complete route** for AJAX status updates
- ✅ **Authorization checks** on every action ensuring team isolation
- ✅ **Task counters** on entity pages showing pending task counts
- ✅ **Complete working task system** with automation ready for production

## Quick Start

Want to see it working in 5 minutes? Here's the end result:

```bash
# After completing this chapter:

# 1. View all tasks
open http://localhost/tasks

# 2. Create a task for a deal
# Click "New Task" → Select deal → Fill form → Save

# 3. Mark task complete
# Click checkbox → Task marked complete, timestamp updated

# 4. View tasks on deal page
open http://localhost/deals/1
# Expected: Tasks tab showing all related tasks

# 5. Test automated reminders (manually)
sail artisan task:send-reminders
# Expected: Email sent to users with overdue/due-today tasks

# 6. Schedule runs daily at 8 AM automatically
```

## Objectives

By completing this chapter, you will:

- **Build a TaskController** with full CRUD operations and quick-complete endpoint
- **Create a Task policy** enforcing team-based authorization on all actions
- **Implement FormRequest validation** with polymorphic relationship rules
- **Design task management views** in React with filters and quick actions
- **Build quick-add task components** for inline task creation
- **Create an Artisan command** checking for tasks needing reminders
- **Configure Laravel Task Scheduler** running reminders daily automatically
- **Send email notifications** with task details and CRM deep links
- **Master Laravel's scheduler** understanding cron setup and testing strategies

## Step 1: Generate Task Controller and Policy (~12 min)

### Goal

Create the Laravel controller handling all task operations and the policy enforcing team-level authorization.

### Actions

1. **Generate controller and policy**:

```bash
# Generate resourceful controller
sail artisan make:controller TaskController --resource

# Generate policy
sail artisan make:policy TaskPolicy --model=Task
```

2. **Build TaskController** (`app/Http/Controllers/TaskController.php`):

```php
<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Contact;
use App\Models\Company;
use App\Models\Deal;
use App\Models\User;
use App\Http\Requests\TaskRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TaskController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Task::class, 'task');
    }

    public function index(Request $request)
    {
        $query = Task::query()
            ->with(['taskable', 'assignedTo', 'createdBy'])
            ->where('team_id', $request->user()->currentTeam->id);

        // Filter by assignee
        if ($request->input('my_tasks')) {
            $query->where('assigned_to', $request->user()->id);
        }

        // Filter by status
        if ($status = $request->input('status')) {
            if ($status === 'pending') {
                $query->pending();
            } elseif ($status === 'overdue') {
                $query->overdue();
            } elseif ($status === 'due_today') {
                $query->dueToday();
            } else {
                $query->where('status', $status);
            }
        }

        // Filter by priority
        if ($priority = $request->input('priority')) {
            $query->where('priority', $priority);
        }

        // Filter by type
        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        // Search
        if ($search = $request->input('search')) {
            $query->where('title', 'like', "%{$search}%");
        }

        // Sort
        $sortField = $request->input('sort', 'due_date');
        $sortDirection = $request->input('direction', 'asc');
        
        if ($sortField === 'priority') {
            // Sort by priority value (urgent=4, high=3, normal=2, low=1)
            $query->orderByRaw("
                CASE priority
                    WHEN 'urgent' THEN 4
                    WHEN 'high' THEN 3
                    WHEN 'normal' THEN 2
                    WHEN 'low' THEN 1
                END {$sortDirection}
            ");
        } else {
            $query->orderBy($sortField, $sortDirection);
        }

        $tasks = $query->paginate(25)->withQueryString();

        // Summary stats
        $stats = [
            'overdue' => Task::where('team_id', $request->user()->currentTeam->id)->overdue()->count(),
            'due_today' => Task::where('team_id', $request->user()->currentTeam->id)->dueToday()->count(),
            'pending' => Task::where('team_id', $request->user()->currentTeam->id)->pending()->count(),
            'my_tasks' => Task::where('team_id', $request->user()->currentTeam->id)
                ->where('assigned_to', $request->user()->id)
                ->pending()
                ->count(),
        ];

        return Inertia::render('Tasks/Index', [
            'tasks' => $tasks,
            'filters' => $request->only(['my_tasks', 'status', 'priority', 'type', 'search', 'sort', 'direction']),
            'stats' => $stats,
        ]);
    }

    public function create(Request $request)
    {
        return Inertia::render('Tasks/Create', [
            'contacts' => Contact::where('team_id', $request->user()->currentTeam->id)
                ->orderBy('first_name')
                ->get(['id', 'first_name', 'last_name']),
            'companies' => Company::where('team_id', $request->user()->currentTeam->id)
                ->orderBy('name')
                ->get(['id', 'name']),
            'deals' => Deal::where('team_id', $request->user()->currentTeam->id)
                ->orderBy('title')
                ->get(['id', 'title']),
            'users' => User::where('current_team_id', $request->user()->currentTeam->id)
                ->get(['id', 'name']),
            'preselected' => $request->only(['taskable_type', 'taskable_id']),
        ]);
    }

    public function store(TaskRequest $request)
    {
        $task = Task::create([
            ...$request->validated(),
            'team_id' => $request->user()->currentTeam->id,
            'created_by' => $request->user()->id,
            'assigned_to' => $request->input('assigned_to', $request->user()->id),
        ]);

        return redirect()
            ->route('tasks.show', $task)
            ->with('success', 'Task created successfully.');
    }

    public function show(Task $task)
    {
        $task->load(['taskable', 'assignedTo', 'createdBy']);

        return Inertia::render('Tasks/Show', [
            'task' => $task,
        ]);
    }

    public function edit(Task $task)
    {
        return Inertia::render('Tasks/Edit', [
            'task' => $task->load('taskable', 'assignedTo'),
            'contacts' => Contact::where('team_id', auth()->user()->currentTeam->id)
                ->orderBy('first_name')
                ->get(['id', 'first_name', 'last_name']),
            'companies' => Company::where('team_id', auth()->user()->currentTeam->id)
                ->orderBy('name')
                ->get(['id', 'name']),
            'deals' => Deal::where('team_id', auth()->user()->currentTeam->id)
                ->orderBy('title')
                ->get(['id', 'title']),
            'users' => User::where('current_team_id', auth()->user()->currentTeam->id)
                ->get(['id', 'name']),
        ]);
    }

    public function update(TaskRequest $request, Task $task)
    {
        $task->update($request->validated());

        return redirect()
            ->route('tasks.show', $task)
            ->with('success', 'Task updated successfully.');
    }

    public function destroy(Task $task)
    {
        $task->delete();

        return redirect()
            ->route('tasks.index')
            ->with('success', 'Task deleted successfully.');
    }

    /**
     * Toggle task completion status
     */
    public function toggleComplete(Task $task)
    {
        $this->authorize('update', $task);

        if ($task->status === 'completed') {
            $task->update([
                'status' => 'open',
                'completed_at' => null,
            ]);
            $message = 'Task reopened.';
        } else {
            $task->markAsCompleted();
            $message = 'Task marked as complete.';
        }

        return back()->with('success', $message);
    }
}
```

3. **Build TaskPolicy** (`app/Policies/TaskPolicy.php`):

```php
<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view their team's tasks
    }

    public function view(User $user, Task $task): bool
    {
        return $user->currentTeam->id === $task->team_id;
    }

    public function create(User $user): bool
    {
        return true; // All team members can create tasks
    }

    public function update(User $user, Task $task): bool
    {
        return $user->currentTeam->id === $task->team_id;
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->currentTeam->id === $task->team_id;
    }

    public function restore(User $user, Task $task): bool
    {
        return $user->currentTeam->id === $task->team_id;
    }

    public function forceDelete(User $user, Task $task): bool
    {
        return $user->currentTeam->id === $task->team_id;
    }
}
```

4. **Create TaskRequest** for validation:

```bash
sail artisan make:request TaskRequest
```

**Build TaskRequest** (`app/Http/Requests/TaskRequest.php`):

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Policy handles authorization
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => ['required', Rule::in(['call', 'email', 'meeting', 'todo'])],
            'priority' => ['required', Rule::in(['low', 'normal', 'high', 'urgent'])],
            'status' => ['required', Rule::in(['open', 'in_progress', 'completed', 'cancelled'])],
            'taskable_type' => [
                'required',
                Rule::in(['App\\Models\\Contact', 'App\\Models\\Company', 'App\\Models\\Deal']),
            ],
            'taskable_id' => 'required|integer',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date|after_or_equal:today',
            'reminder_at' => 'nullable|date|before:due_date',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Please enter a task title.',
            'taskable_type.required' => 'Please select what this task is related to.',
            'taskable_id.required' => 'Please select a specific contact, company, or deal.',
            'due_date.after_or_equal' => 'The due date must be today or in the future.',
            'reminder_at.before' => 'The reminder must be before the due date.',
        ];
    }

    /**
     * Get validated data with proper model namespace
     */
    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        // Ensure taskable_type has proper namespace
        if (isset($data['taskable_type'])) {
            $data['taskable_type'] = str_replace('/', '\\', $data['taskable_type']);
        }

        return $data;
    }
}
```

### Expected Result

```bash
# Test controller and policy
sail artisan tinker
$user = User::first();
$task = Task::first();
Gate::forUser($user)->authorize('view', $task);
# Expected: No exception if same team
```

### Why It Works

The **TaskController** uses scopes like `pending()`, `overdue()`, and `dueToday()` defined in Chapter 17, making filter logic clean and reusable. The `toggleComplete()` method provides a dedicated endpoint for quick status changes without requiring all task fields.

**Priority sorting** uses a `CASE` statement converting text priorities to numeric values for database-level sorting, which is much faster than loading all tasks and sorting in PHP.

**Summary stats** provide dashboard metrics shown at the top of the tasks index, helping users see at a glance what needs attention.

### Troubleshooting

- **Policy not enforced** — Run `sail artisan optimize:clear` to clear cached policies
- **Validation fails on taskable_type** — Ensure value includes namespace: `App\Models\Contact`
- **Stats queries slow** — Add indexes to tasks table on `(team_id, status)` and `(team_id, due_date)`

## Step 2: Register Routes (~5 min)

### Goal

Define routes for tasks including the quick-complete toggle endpoint.

### Actions

1. **Add routes** to `routes/web.php`:

```php
use App\Http\Controllers\TaskController;

Route::middleware(['auth', 'verified'])->group(function () {
    // Task quick-complete toggle
    Route::patch('/tasks/{task}/toggle-complete', [TaskController::class, 'toggleComplete'])
        ->name('tasks.toggle-complete');
    
    // Tasks CRUD
    Route::resource('tasks', TaskController::class);
});
```

2. **Test routes**:

```bash
sail artisan route:list --name=tasks

# Expected output:
# GET    /tasks ......................... tasks.index
# POST   /tasks ......................... tasks.store
# GET    /tasks/create .................. tasks.create
# GET    /tasks/{task} .................. tasks.show
# PUT    /tasks/{task} .................. tasks.update
# DELETE /tasks/{task} .................. tasks.destroy
# GET    /tasks/{task}/edit ............. tasks.edit
# PATCH  /tasks/{task}/toggle-complete .. tasks.toggle-complete
```

### Expected Result

Routes registered and ready to receive requests.

### Why It Works

The **toggle-complete route** comes before the resource routes to prevent Laravel from interpreting "toggle-complete" as a task ID. Route ordering matters—specific routes before wildcard routes.

### Troubleshooting

- **Route not found** — Run `sail artisan route:clear` and verify route name
- **toggle-complete treated as ID** — Move custom route before `Route::resource()`

## Step 3: Build Tasks Index View (~20 min)

### Goal

Create a React/Inertia view listing all tasks with filters, search, and quick actions.

### Actions

1. **Create Tasks Index** (`resources/js/Pages/Tasks/Index.tsx`):

Due to length, I'll provide the key structure. The full implementation would be similar to Deals Index but with task-specific filters.

```tsx
import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Select } from '@/Components/ui/select';
import { Badge } from '@/Components/ui/badge';
import { Checkbox } from '@/Components/ui/checkbox';
import { formatDate } from '@/lib/utils';

interface Task {
  id: number;
  title: string;
  type: string;
  priority: string;
  status: string;
  due_date: string | null;
  is_overdue: boolean;
  taskable: {
    id: number;
    name?: string;  // Company
    title?: string;  // Deal
    first_name?: string;  // Contact
    last_name?: string;
  };
  taskable_type: string;
  assigned_to: { id: number; name: string } | null;
}

interface Props {
  tasks: {
    data: Task[];
    links: any[];
    meta: any;
  };
  filters: {
    my_tasks?: boolean;
    status?: string;
    priority?: string;
    type?: string;
    search?: string;
    sort?: string;
    direction?: string;
  };
  stats: {
    overdue: number;
    due_today: number;
    pending: number;
    my_tasks: number;
  };
}

export default function Index({ tasks, filters, stats }: Props) {
  const [search, setSearch] = useState(filters.search || '');

  const handleFilter = (key: string, value: string | boolean) => {
    router.get(route('tasks.index'), {
      ...filters,
      [key]: value,
    }, {
      preserveState: true,
      replace: true,
    });
  };

  const toggleComplete = (taskId: number) => {
    router.patch(route('tasks.toggle-complete', taskId), {}, {
      preserveState: true,
    });
  };

  const getPriorityColor = (priority: string) => {
    const colors = {
      low: 'bg-gray-200 text-gray-800',
      normal: 'bg-blue-200 text-blue-800',
      high: 'bg-orange-200 text-orange-800',
      urgent: 'bg-red-200 text-red-800',
    };
    return colors[priority] || 'bg-gray-200';
  };

  const getEntityName = (task: Task) => {
    if (task.taskable_type.includes('Contact')) {
      return `${task.taskable.first_name} ${task.taskable.last_name}`;
    } else if (task.taskable_type.includes('Company')) {
      return task.taskable.name;
    } else if (task.taskable_type.includes('Deal')) {
      return task.taskable.title;
    }
    return 'Unknown';
  };

  const getEntityRoute = (task: Task) => {
    if (task.taskable_type.includes('Contact')) {
      return route('contacts.show', task.taskable.id);
    } else if (task.taskable_type.includes('Company')) {
      return route('companies.show', task.taskable.id);
    } else if (task.taskable_type.includes('Deal')) {
      return route('deals.show', task.taskable.id);
    }
    return '#';
  };

  return (
    <AuthenticatedLayout
      header={
        <div className="flex justify-between items-center">
          <h2 className="font-semibold text-xl text-gray-800 leading-tight">Tasks</h2>
          <Link href={route('tasks.create')}>
            <Button>New Task</Button>
          </Link>
        </div>
      }
    >
      <Head title="Tasks" />

      <div className="py-12">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
          {/* Stats Cards */}
          <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <button
              onClick={() => handleFilter('status', 'overdue')}
              className="bg-white p-4 rounded-lg shadow hover:shadow-md transition-shadow text-left"
            >
              <div className="text-2xl font-bold text-red-600">{stats.overdue}</div>
              <div className="text-sm text-gray-600">Overdue</div>
            </button>
            <button
              onClick={() => handleFilter('status', 'due_today')}
              className="bg-white p-4 rounded-lg shadow hover:shadow-md transition-shadow text-left"
            >
              <div className="text-2xl font-bold text-orange-600">{stats.due_today}</div>
              <div className="text-sm text-gray-600">Due Today</div>
            </button>
            <button
              onClick={() => handleFilter('status', 'pending')}
              className="bg-white p-4 rounded-lg shadow hover:shadow-md transition-shadow text-left"
            >
              <div className="text-2xl font-bold text-blue-600">{stats.pending}</div>
              <div className="text-sm text-gray-600">Pending</div>
            </button>
            <button
              onClick={() => handleFilter('my_tasks', true)}
              className="bg-white p-4 rounded-lg shadow hover:shadow-md transition-shadow text-left"
            >
              <div className="text-2xl font-bold text-green-600">{stats.my_tasks}</div>
              <div className="text-sm text-gray-600">My Tasks</div>
            </button>
          </div>

          {/* Filters */}
          <div className="bg-white shadow-sm rounded-lg p-6 mb-6">
            <div className="grid grid-cols-1 md:grid-cols-5 gap-4">
              <Input
                placeholder="Search tasks..."
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                onKeyUp={(e) => e.key === 'Enter' && handleFilter('search', search)}
              />
              <Select
                value={filters.status || ''}
                onChange={(e) => handleFilter('status', e.target.value)}
              >
                <option value="">All Statuses</option>
                <option value="open">Open</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
                <option value="overdue">Overdue</option>
                <option value="due_today">Due Today</option>
              </Select>
              <Select
                value={filters.priority || ''}
                onChange={(e) => handleFilter('priority', e.target.value)}
              >
                <option value="">All Priorities</option>
                <option value="low">Low</option>
                <option value="normal">Normal</option>
                <option value="high">High</option>
                <option value="urgent">Urgent</option>
              </Select>
              <Select
                value={filters.type || ''}
                onChange={(e) => handleFilter('type', e.target.value)}
              >
                <option value="">All Types</option>
                <option value="call">Call</option>
                <option value="email">Email</option>
                <option value="meeting">Meeting</option>
                <option value="todo">To-Do</option>
              </Select>
              <Button variant="outline" onClick={() => router.get(route('tasks.index'))}>
                Clear Filters
              </Button>
            </div>
          </div>

          {/* Tasks Table */}
          <div className="bg-white shadow-sm rounded-lg overflow-hidden">
            <table className="min-w-full divide-y divide-gray-200">
              <thead className="bg-gray-50">
                <tr>
                  <th className="px-6 py-3 w-12"></th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Task
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Related To
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Priority
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Due Date
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Assigned To
                  </th>
                  <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200">
                {tasks.data.map((task) => (
                  <tr key={task.id} className="hover:bg-gray-50">
                    <td className="px-6 py-4 whitespace-nowrap">
                      <Checkbox
                        checked={task.status === 'completed'}
                        onCheckedChange={() => toggleComplete(task.id)}
                      />
                    </td>
                    <td className="px-6 py-4">
                      <Link
                        href={route('tasks.show', task.id)}
                        className="text-blue-600 hover:text-blue-900 font-medium"
                      >
                        {task.title}
                      </Link>
                      <div className="text-sm text-gray-500">{task.type}</div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <Link
                        href={getEntityRoute(task)}
                        className="text-gray-600 hover:text-gray-900"
                      >
                        {getEntityName(task)}
                      </Link>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <Badge className={getPriorityColor(task.priority)}>
                        {task.priority}
                      </Badge>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      {task.due_date ? (
                        <span className={task.is_overdue ? 'text-red-600 font-semibold' : ''}>
                          {formatDate(task.due_date)}
                        </span>
                      ) : (
                        <span className="text-gray-400">Not set</span>
                      )}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                      {task.assigned_to?.name || 'Unassigned'}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                      <Link
                        href={route('tasks.edit', task.id)}
                        className="text-indigo-600 hover:text-indigo-900"
                      >
                        Edit
                      </Link>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}
```

### Expected Result

```bash
# Visit tasks index
open http://localhost/tasks

# Expected:
# - Stats cards showing counts
# - Filters for status/priority/type
# - Table listing all tasks
# - Checkboxes toggling completion
# - Links to related entities
# - Color-coded priority badges
```

### Why It Works

**Stats cards** are clickable, applying filters when clicked—providing quick navigation to overdue or due-today tasks. The **checkbox** calls `toggleComplete()` using Inertia's router for optimistic UI updates.

**Priority colors** use Tailwind's color system with consistent semantic meaning: red=urgent, orange=high, blue=normal, gray=low.

### Troubleshooting

- **Checkbox not toggling** — Verify `tasks.toggle-complete` route exists and accepts PATCH method
- **Entity links broken** — Check polymorphic relationship includes full class namespace
- **TypeScript errors** — Install `@types/react` if missing

(Continuing with remaining steps in next message due to length...)

## Step 4: Build Task Create and Edit Forms (~18 min)

### Goal

Create forms for adding and editing tasks with polymorphic entity selection.

### Actions

1. **Create Task Create Form** (`resources/js/Pages/Tasks/Create.tsx`):

```tsx
import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Select } from '@/Components/ui/select';
import { Textarea } from '@/Components/ui/textarea';

interface Entity {
  id: number;
  name?: string;
  title?: string;
  first_name?: string;
  last_name?: string;
}

interface Props {
  contacts: Entity[];
  companies: Entity[];
  deals: Entity[];
  users: Array<{ id: number; name: string }>;
  preselected?: {
    taskable_type?: string;
    taskable_id?: number;
  };
}

export default function Create({ contacts, companies, deals, users, preselected }: Props) {
  const [entityType, setEntityType] = useState(preselected?.taskable_type || '');
  
  const { data, setData, post, processing, errors } = useForm({
    title: '',
    description: '',
    type: 'todo',
    priority: 'normal',
    status: 'open',
    taskable_type: preselected?.taskable_type || '',
    taskable_id: preselected?.taskable_id?.toString() || '',
    assigned_to: '',
    due_date: '',
    reminder_at: '',
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post(route('tasks.store'));
  };

  const getEntityOptions = () => {
    if (entityType === 'App\\Models\\Contact') {
      return contacts.map(c => ({
        id: c.id,
        label: `${c.first_name} ${c.last_name}`,
      }));
    } else if (entityType === 'App\\Models\\Company') {
      return companies.map(c => ({
        id: c.id,
        label: c.name!,
      }));
    } else if (entityType === 'App\\Models\\Deal') {
      return deals.map(d => ({
        id: d.id,
        label: d.title!,
      }));
    }
    return [];
  };

  return (
    <AuthenticatedLayout
      header={
        <h2 className="font-semibold text-xl text-gray-800 leading-tight">Create New Task</h2>
      }
    >
      <Head title="Create Task" />

      <div className="py-12">
        <div className="max-w-3xl mx-auto sm:px-6 lg:px-8">
          <div className="bg-white shadow-sm rounded-lg p-6">
            <form onSubmit={handleSubmit} className="space-y-6">
              {/* Title */}
              <div>
                <Label htmlFor="title">Task Title *</Label>
                <Input
                  id="title"
                  value={data.title}
                  onChange={(e) => setData('title', e.target.value)}
                  placeholder="e.g., Call to discuss pricing"
                  className={errors.title ? 'border-red-500' : ''}
                />
                {errors.title && (
                  <p className="text-sm text-red-600 mt-1">{errors.title}</p>
                )}
              </div>

              {/* Description */}
              <div>
                <Label htmlFor="description">Description</Label>
                <Textarea
                  id="description"
                  value={data.description}
                  onChange={(e) => setData('description', e.target.value)}
                  placeholder="Task details and notes..."
                  rows={3}
                />
              </div>

              {/* Type and Priority */}
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <Label htmlFor="type">Type *</Label>
                  <Select
                    id="type"
                    value={data.type}
                    onChange={(e) => setData('type', e.target.value)}
                  >
                    <option value="todo">To-Do</option>
                    <option value="call">Call</option>
                    <option value="email">Email</option>
                    <option value="meeting">Meeting</option>
                  </Select>
                </div>
                <div>
                  <Label htmlFor="priority">Priority *</Label>
                  <Select
                    id="priority"
                    value={data.priority}
                    onChange={(e) => setData('priority', e.target.value)}
                  >
                    <option value="low">Low</option>
                    <option value="normal">Normal</option>
                    <option value="high">High</option>
                    <option value="urgent">Urgent</option>
                  </Select>
                </div>
              </div>

              {/* Related Entity Type */}
              <div>
                <Label htmlFor="taskable_type">Related To *</Label>
                <Select
                  id="taskable_type"
                  value={entityType}
                  onChange={(e) => {
                    const value = e.target.value;
                    setEntityType(value);
                    setData('taskable_type', value);
                    setData('taskable_id', '');
                  }}
                  className={errors.taskable_type ? 'border-red-500' : ''}
                >
                  <option value="">Select type...</option>
                  <option value="App\\Models\\Contact">Contact</option>
                  <option value="App\\Models\\Company">Company</option>
                  <option value="App\\Models\\Deal">Deal</option>
                </Select>
                {errors.taskable_type && (
                  <p className="text-sm text-red-600 mt-1">{errors.taskable_type}</p>
                )}
              </div>

              {/* Specific Entity */}
              {entityType && (
                <div>
                  <Label htmlFor="taskable_id">
                    Select {entityType.includes('Contact') ? 'Contact' : entityType.includes('Company') ? 'Company' : 'Deal'} *
                  </Label>
                  <Select
                    id="taskable_id"
                    value={data.taskable_id}
                    onChange={(e) => setData('taskable_id', e.target.value)}
                    className={errors.taskable_id ? 'border-red-500' : ''}
                  >
                    <option value="">Choose...</option>
                    {getEntityOptions().map((entity) => (
                      <option key={entity.id} value={entity.id}>
                        {entity.label}
                      </option>
                    ))}
                  </Select>
                  {errors.taskable_id && (
                    <p className="text-sm text-red-600 mt-1">{errors.taskable_id}</p>
                  )}
                </div>
              )}

              {/* Assigned To */}
              <div>
                <Label htmlFor="assigned_to">Assign To</Label>
                <Select
                  id="assigned_to"
                  value={data.assigned_to}
                  onChange={(e) => setData('assigned_to', e.target.value)}
                >
                  <option value="">Unassigned</option>
                  {users.map((user) => (
                    <option key={user.id} value={user.id}>
                      {user.name}
                    </option>
                  ))}
                </Select>
              </div>

              {/* Due Date and Reminder */}
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <Label htmlFor="due_date">Due Date</Label>
                  <Input
                    id="due_date"
                    type="datetime-local"
                    value={data.due_date}
                    onChange={(e) => setData('due_date', e.target.value)}
                  />
                </div>
                <div>
                  <Label htmlFor="reminder_at">Reminder</Label>
                  <Input
                    id="reminder_at"
                    type="datetime-local"
                    value={data.reminder_at}
                    onChange={(e) => setData('reminder_at', e.target.value)}
                  />
                </div>
              </div>

              {/* Status */}
              <div>
                <Label htmlFor="status">Status *</Label>
                <Select
                  id="status"
                  value={data.status}
                  onChange={(e) => setData('status', e.target.value)}
                >
                  <option value="open">Open</option>
                  <option value="in_progress">In Progress</option>
                  <option value="completed">Completed</option>
                  <option value="cancelled">Cancelled</option>
                </Select>
              </div>

              {/* Actions */}
              <div className="flex items-center justify-end gap-4 pt-4">
                <Link href={route('tasks.index')}>
                  <Button type="button" variant="outline">Cancel</Button>
                </Link>
                <Button type="submit" disabled={processing}>
                  {processing ? 'Creating...' : 'Create Task'}
                </Button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}
```

2. **Create Task Edit Form** (similar structure, use `put()` method):

The Edit form would be nearly identical but pre-populate data and use `put()` instead of `post()`.

### Expected Result

```bash
# Visit create form
open http://localhost/tasks/create

# Expected:
# - All form fields present
# - Entity type dropdown working
# - Specific entity dropdown populates based on type
# - Form validates and creates task
```

### Why It Works

**Dynamic entity selection** uses state to show different dropdown options based on the selected entity type. When user selects "Contact," the second dropdown shows contacts; when "Company," it shows companies.

**Preselected values** allow linking from entity pages: "Create task for this deal" passes `taskable_type` and `taskable_id` as query params, auto-selecting the deal.

### Troubleshooting

- **Entity dropdown empty** — Verify controller passes contacts, companies, deals arrays
- **Validation fails on taskable_type** — Ensure value includes full namespace with double backslashes
- **Dates not working** — Use `datetime-local` input type for combined date+time picker

## Step 5: Build Task Show View (~12 min)

### Goal

Display complete task details with related entity link and completion status.

### Actions

1. **Create Task Show** (`resources/js/Pages/Tasks/Show.tsx`):

```tsx
import React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Button } from '@/Components/ui/button';
import { Card } from '@/Components/ui/card';
import { Badge } from '@/Components/ui/badge';
import { formatDate, formatDateTime } from '@/lib/utils';

interface Task {
  id: number;
  title: string;
  description: string | null;
  type: string;
  priority: string;
  status: string;
  due_date: string | null;
  completed_at: string | null;
  is_overdue: boolean;
  taskable: {
    id: number;
    name?: string;
    title?: string;
    first_name?: string;
    last_name?: string;
  };
  taskable_type: string;
  assigned_to: { id: number; name: string } | null;
  created_by: { id: number; name: string };
  created_at: string;
  updated_at: string;
}

interface Props {
  task: Task;
}

export default function Show({ task }: Props) {
  const getPriorityColor = (priority: string) => {
    const colors = {
      low: 'bg-gray-200 text-gray-800',
      normal: 'bg-blue-200 text-blue-800',
      high: 'bg-orange-200 text-orange-800',
      urgent: 'bg-red-200 text-red-800',
    };
    return colors[priority] || 'bg-gray-200';
  };

  const getStatusColor = (status: string) => {
    const colors = {
      open: 'bg-blue-100 text-blue-800',
      in_progress: 'bg-yellow-100 text-yellow-800',
      completed: 'bg-green-100 text-green-800',
      cancelled: 'bg-gray-100 text-gray-800',
    };
    return colors[status] || 'bg-gray-100';
  };

  const getEntityName = () => {
    if (task.taskable_type.includes('Contact')) {
      return `${task.taskable.first_name} ${task.taskable.last_name}`;
    } else if (task.taskable_type.includes('Company')) {
      return task.taskable.name;
    } else if (task.taskable_type.includes('Deal')) {
      return task.taskable.title;
    }
    return 'Unknown';
  };

  const getEntityRoute = () => {
    if (task.taskable_type.includes('Contact')) {
      return route('contacts.show', task.taskable.id);
    } else if (task.taskable_type.includes('Company')) {
      return route('companies.show', task.taskable.id);
    } else if (task.taskable_type.includes('Deal')) {
      return route('deals.show', task.taskable.id);
    }
    return '#';
  };

  const getEntityType = () => {
    if (task.taskable_type.includes('Contact')) return 'Contact';
    if (task.taskable_type.includes('Company')) return 'Company';
    if (task.taskable_type.includes('Deal')) return 'Deal';
    return 'Unknown';
  };

  const toggleComplete = () => {
    router.patch(route('tasks.toggle-complete', task.id), {}, {
      preserveState: false,
    });
  };

  return (
    <AuthenticatedLayout
      header={
        <div className="flex justify-between items-center">
          <h2 className="font-semibold text-xl text-gray-800 leading-tight">{task.title}</h2>
          <div className="flex gap-2">
            {task.status !== 'completed' && (
              <Button onClick={toggleComplete}>Mark Complete</Button>
            )}
            {task.status === 'completed' && (
              <Button variant="outline" onClick={toggleComplete}>Reopen Task</Button>
            )}
            <Link href={route('tasks.edit', task.id)}>
              <Button variant="outline">Edit</Button>
            </Link>
            <Link href={route('tasks.index')}>
              <Button variant="outline">Back to List</Button>
            </Link>
          </div>
        </div>
      }
    >
      <Head title={task.title} />

      <div className="py-12">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {/* Main Details */}
            <div className="lg:col-span-2 space-y-6">
              {/* Task Info Card */}
              <Card className="p-6">
                <h3 className="text-lg font-semibold mb-4">Task Details</h3>
                <dl className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <dt className="text-sm font-medium text-gray-500">Title</dt>
                    <dd className="mt-1 text-sm text-gray-900">{task.title}</dd>
                  </div>
                  <div>
                    <dt className="text-sm font-medium text-gray-500">Type</dt>
                    <dd className="mt-1 text-sm text-gray-900 capitalize">{task.type}</dd>
                  </div>
                  <div>
                    <dt className="text-sm font-medium text-gray-500">Priority</dt>
                    <dd className="mt-1">
                      <Badge className={getPriorityColor(task.priority)}>
                        {task.priority}
                      </Badge>
                    </dd>
                  </div>
                  <div>
                    <dt className="text-sm font-medium text-gray-500">Status</dt>
                    <dd className="mt-1">
                      <Badge className={getStatusColor(task.status)}>
                        {task.status.replace('_', ' ')}
                      </Badge>
                    </dd>
                  </div>
                  <div>
                    <dt className="text-sm font-medium text-gray-500">Related To</dt>
                    <dd className="mt-1 text-sm">
                      <Link
                        href={getEntityRoute()}
                        className="text-blue-600 hover:text-blue-900"
                      >
                        {getEntityType()}: {getEntityName()}
                      </Link>
                    </dd>
                  </div>
                  <div>
                    <dt className="text-sm font-medium text-gray-500">Assigned To</dt>
                    <dd className="mt-1 text-sm text-gray-900">
                      {task.assigned_to?.name || 'Unassigned'}
                    </dd>
                  </div>
                  <div>
                    <dt className="text-sm font-medium text-gray-500">Due Date</dt>
                    <dd className="mt-1 text-sm">
                      {task.due_date ? (
                        <span className={task.is_overdue ? 'text-red-600 font-semibold' : 'text-gray-900'}>
                          {formatDateTime(task.due_date)}
                          {task.is_overdue && ' (Overdue)'}
                        </span>
                      ) : (
                        <span className="text-gray-500">Not set</span>
                      )}
                    </dd>
                  </div>
                  <div>
                    <dt className="text-sm font-medium text-gray-500">Completed</dt>
                    <dd className="mt-1 text-sm text-gray-900">
                      {task.completed_at ? formatDateTime(task.completed_at) : 'Not completed'}
                    </dd>
                  </div>
                  <div>
                    <dt className="text-sm font-medium text-gray-500">Created By</dt>
                    <dd className="mt-1 text-sm text-gray-900">
                      {task.created_by.name}
                    </dd>
                  </div>
                </dl>

                {task.description && (
                  <div className="mt-6">
                    <dt className="text-sm font-medium text-gray-500 mb-2">Description</dt>
                    <dd className="text-sm text-gray-900 whitespace-pre-wrap">
                      {task.description}
                    </dd>
                  </div>
                )}
              </Card>
            </div>

            {/* Sidebar */}
            <div className="space-y-6">
              {/* Quick Stats */}
              <Card className="p-6">
                <h3 className="text-lg font-semibold mb-4">Timeline</h3>
                <dl className="space-y-3">
                  <div>
                    <dt className="text-sm text-gray-500">Created</dt>
                    <dd className="text-sm font-medium">
                      {formatDateTime(task.created_at)}
                    </dd>
                  </div>
                  <div>
                    <dt className="text-sm text-gray-500">Last Updated</dt>
                    <dd className="text-sm font-medium">
                      {formatDateTime(task.updated_at)}
                    </dd>
                  </div>
                  {task.due_date && (
                    <div>
                      <dt className="text-sm text-gray-500">Days Until Due</dt>
                      <dd className="text-sm font-medium">
                        {Math.ceil(
                          (new Date(task.due_date).getTime() - new Date().getTime()) /
                            (1000 * 60 * 60 * 24)
                        )}{' '}
                        days
                      </dd>
                    </div>
                  )}
                </dl>
              </Card>

              {/* Actions */}
              <Card className="p-6">
                <h3 className="text-lg font-semibold mb-4">Quick Actions</h3>
                <div className="space-y-2">
                  <Link href={getEntityRoute()} className="block">
                    <Button variant="outline" className="w-full">
                      View {getEntityType()}
                    </Button>
                  </Link>
                  <Link href={route('tasks.index')} className="block">
                    <Button variant="outline" className="w-full">
                      All Tasks
                    </Button>
                  </Link>
                </div>
              </Card>
            </div>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}
```

### Expected Result

```bash
# Visit task show page
open http://localhost/tasks/1

# Expected:
# - Complete task information
# - Priority and status badges
# - Link to related entity
# - Mark complete button
# - Timeline sidebar
# - Overdue indicator if applicable
```

### Why It Works

**Conditional rendering** shows "Mark Complete" or "Reopen Task" based on current status. The **getEntityName/Route** functions handle polymorphic relationship display dynamically.

**Days until due** calculation uses JavaScript Date math, providing quick insight into task urgency.

### Troubleshooting

- **Entity link broken** — Verify `taskable` relationship is eager loaded in controller
- **Toggle complete not working** — Check route exists and policy authorizes update
- **Badge colors wrong** — Verify Tailwind classes are included in build

## Step 6: Create Artisan Command for Task Reminders (~15 min)

### Goal

Build a command that finds overdue and due-today tasks, sending email reminders to assignees.

### Actions

1. **Generate command**:

```bash
sail artisan make:command SendTaskReminders
```

2. **Build SendTaskReminders command** (`app/Console/Commands/SendTaskReminders.php`):

```php
<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendTaskReminders extends Command
{
    protected $signature = 'task:send-reminders';
    protected $description = 'Send email reminders for overdue and due-today tasks';

    public function handle(): int
    {
        $this->info('Checking for tasks needing reminders...');

        // Get all users with pending tasks (overdue or due today)
        $usersWithTasks = User::whereHas('assignedTasks', function ($query) {
            $query->whereIn('status', ['open', 'in_progress'])
                ->where(function ($q) {
                    $q->where('due_date', '<', now())  // Overdue
                      ->orWhereDate('due_date', today());  // Due today
                });
        })->get();

        if ($usersWithTasks->isEmpty()) {
            $this->info('No tasks requiring reminders.');
            return Command::SUCCESS;
        }

        $totalReminders = 0;

        foreach ($usersWithTasks as $user) {
            // Get overdue tasks
            $overdueTasks = Task::where('assigned_to', $user->id)
                ->overdue()
                ->with(['taskable'])
                ->get();

            // Get tasks due today
            $dueTodayTasks = Task::where('assigned_to', $user->id)
                ->dueToday()
                ->with(['taskable'])
                ->get();

            if ($overdueTasks->isEmpty() && $dueTodayTasks->isEmpty()) {
                continue;
            }

            // Send notification
            $user->notify(new TaskReminderNotification($overdueTasks, $dueTodayTasks));

            $totalReminders++;

            $this->info("Sent reminder to {$user->name} ({$overdueTasks->count()} overdue, {$dueTodayTasks->count()} due today)");
        }

        $this->info("Sent {$totalReminders} reminder(s) total.");

        return Command::SUCCESS;
    }
}
```

3. **Add relationship to User model** (`app/Models/User.php`):

```php
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Tasks assigned to this user
 */
public function assignedTasks(): HasMany
{
    return $this->hasMany(Task::class, 'assigned_to');
}
```

4. **Create TaskReminderNotification**:

```bash
sail artisan make:notification TaskReminderNotification
```

**Build notification** (`app/Notifications/TaskReminderNotification.php`):

```php
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class TaskReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Collection $overdueTasks,
        public Collection $dueTodayTasks
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Task Reminders - ' . now()->format('M d, Y'))
            ->greeting('Hello ' . $notifiable->name . '!');

        if ($this->overdueTasks->isNotEmpty()) {
            $mail->line('You have **' . $this->overdueTasks->count() . '** overdue task(s):');
            
            foreach ($this->overdueTasks as $task) {
                $entityName = $this->getEntityName($task);
                $mail->line('- **' . $task->title . '** (' . $entityName . ') - Due: ' . $task->due_date->format('M d, Y'));
            }
        }

        if ($this->dueTodayTasks->isNotEmpty()) {
            if ($this->overdueTasks->isNotEmpty()) {
                $mail->line('');
            }
            
            $mail->line('You have **' . $this->dueTodayTasks->count() . '** task(s) due today:');
            
            foreach ($this->dueTodayTasks as $task) {
                $entityName = $this->getEntityName($task);
                $mail->line('- **' . $task->title . '** (' . $entityName . ')');
            }
        }

        $mail->action('View All Tasks', route('tasks.index'))
            ->line('Stay on top of your work!');

        return $mail;
    }

    protected function getEntityName($task): string
    {
        if ($task->taskable_type === 'App\\Models\\Contact') {
            return $task->taskable->first_name . ' ' . $task->taskable->last_name;
        } elseif ($task->taskable_type === 'App\\Models\\Company') {
            return $task->taskable->name;
        } elseif ($task->taskable_type === 'App\\Models\\Deal') {
            return $task->taskable->title;
        }
        return 'Unknown';
    }
}
```

5. **Test command manually**:

```bash
# Run command manually
sail artisan task:send-reminders

# Expected output:
# Checking for tasks needing reminders...
# Sent reminder to John Doe (2 overdue, 1 due today)
# Sent 1 reminder(s) total.

# Check Mailhog
open http://localhost:8025
# Expected: Email with task list
```

### Expected Result

✅ **Command sends emails** to users with overdue or due-today tasks
✅ **Email contains** list of tasks with entity names and due dates
✅ **Link to CRM** allows users to view all tasks
✅ **Queued notification** sends asynchronously if queue workers running

### Why It Works

The **command queries users with pending tasks** using `whereHas()`, efficiently finding only users who need reminders. The notification uses `ShouldQueue` interface, sending emails asynchronously if queues are configured.

**Collection-based emails** group tasks by status (overdue vs due today), providing clear organization in the reminder.

### Troubleshooting

- **No emails sent** — Verify tasks exist with due dates and assigned users
- **Mailhog not working** — Check `.env` has `MAIL_MAILER=smtp` and correct port (1025)
- **Queue not processing** — Run `sail artisan queue:work` or use `sync` driver for testing

## Step 7: Configure Laravel Task Scheduler (~8 min)

### Goal

Schedule the reminder command to run automatically every day at 8 AM.

### Actions

1. **Register scheduled task** in `app/Console/Kernel.php`:

```php
<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Send task reminders daily at 8 AM
        $schedule->command('task:send-reminders')
            ->dailyAt('08:00')
            ->timezone('America/New_York')  // Adjust to your timezone
            ->runInBackground()
            ->onOneServer();
        
        // Alternative: Run every hour during work hours
        // $schedule->command('task:send-reminders')
        //     ->hourly()
        //     ->between('8:00', '18:00')
        //     ->weekdays();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
```

2. **Set up cron job** (production):

```bash
# On production server, add this to crontab:
# Edit crontab
crontab -e

# Add this line (runs Laravel scheduler every minute)
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

3. **Test scheduler locally**:

```bash
# Run scheduler manually (simulates cron)
sail artisan schedule:run

# Expected: If time matches schedule, command runs

# See scheduled tasks
sail artisan schedule:list

# Expected output:
# 0 8 * * * task:send-reminders ............. Next Due: tomorrow at 8:00 AM
```

4. **Set up queue workers** (for async notifications):

```bash
# Run queue worker (keep terminal open)
sail artisan queue:work

# Or in background (production)
# sail artisan queue:work --daemon
```

### Expected Result

✅ **Scheduler configured** to run daily at 8 AM
✅ **Cron setup documented** for production deployment
✅ **Queue workers** process email notifications asynchronously
✅ **Timezone set** to match your business hours

### Why It Works

**Laravel's scheduler** provides a fluent API for cron-like scheduling without managing cron syntax. A single cron entry (`* * * * *`) runs `schedule:run` every minute, which checks registered tasks and executes those due.

**onOneServer()** ensures tasks run only once even with multiple servers (requires cache driver supporting locks, like Redis).

**runInBackground()** spawns a separate process, preventing long-running tasks from blocking the scheduler.

### Troubleshooting

- **Schedule not running** — Verify cron is active: `crontab -l` should show the entry
- **Command runs multiple times** — Use `onOneServer()` and ensure cache driver supports locks
- **Timezone issues** — Set `timezone()` explicitly or configure `app.timezone` in `config/app.php`

## Step 8: Add Task List to Entity Views (~12 min)

### Goal

Embed a task list component on Contact, Company, and Deal show pages.

### Actions

1. **Create TaskList component** (`resources/js/Components/TaskList.tsx`):

```tsx
import React from 'react';
import { Link, router } from '@inertiajs/react';
import { Card } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { Checkbox } from '@/Components/ui/checkbox';
import { formatDate } from '@/lib/utils';

interface Task {
  id: number;
  title: string;
  type: string;
  priority: string;
  status: string;
  due_date: string | null;
  is_overdue: boolean;
}

interface Props {
  tasks: Task[];
  entityType: string;
  entityId: number;
}

export default function TaskList({ tasks, entityType, entityId }: Props) {
  const toggleComplete = (taskId: number) => {
    router.patch(route('tasks.toggle-complete', taskId), {}, {
      preserveState: true,
    });
  };

  const getPriorityColor = (priority: string) => {
    const colors = {
      low: 'bg-gray-200 text-gray-800',
      normal: 'bg-blue-200 text-blue-800',
      high: 'bg-orange-200 text-orange-800',
      urgent: 'bg-red-200 text-red-800',
    };
    return colors[priority] || 'bg-gray-200';
  };

  return (
    <Card className="p-6">
      <div className="flex justify-between items-center mb-4">
        <h3 className="text-lg font-semibold">Tasks ({tasks.length})</h3>
        <Link
          href={route('tasks.create', {
            taskable_type: entityType,
            taskable_id: entityId,
          })}
        >
          <Button size="sm">Add Task</Button>
        </Link>
      </div>

      {tasks.length === 0 ? (
        <p className="text-gray-500 text-center py-4">No tasks yet.</p>
      ) : (
        <div className="space-y-3">
          {tasks.map((task) => (
            <div
              key={task.id}
              className="flex items-start gap-3 p-3 border rounded-lg hover:bg-gray-50"
            >
              <Checkbox
                checked={task.status === 'completed'}
                onCheckedChange={() => toggleComplete(task.id)}
                className="mt-1"
              />
              <div className="flex-1 min-w-0">
                <div className="flex items-center gap-2 mb-1">
                  <Link
                    href={route('tasks.show', task.id)}
                    className="font-medium text-blue-600 hover:text-blue-900"
                  >
                    {task.title}
                  </Link>
                  <Badge className={getPriorityColor(task.priority)} size="sm">
                    {task.priority}
                  </Badge>
                </div>
                <div className="flex items-center gap-4 text-sm text-gray-600">
                  <span className="capitalize">{task.type}</span>
                  {task.due_date && (
                    <span className={task.is_overdue ? 'text-red-600 font-semibold' : ''}>
                      Due: {formatDate(task.due_date)}
                    </span>
                  )}
                </div>
              </div>
            </div>
          ))}
        </div>
      )}
    </Card>
  );
}
```

2. **Update Deal show page** to include tasks:

In `app/Http/Controllers/DealController.php`, modify `show()` method:

```php
public function show(Deal $deal)
{
    $deal->load([
        'company',
        'primaryContact',
        'stage',
        'owner',
        'stageHistory.user',
        'stageHistory.stage',
        'tasks' => function ($query) {
            $query->with('assignedTo')->orderBy('due_date');
        },
    ]);

    return Inertia::render('Deals/Show', [
        'deal' => $deal,
    ]);
}
```

In `resources/js/Pages/Deals/Show.tsx`, add TaskList:

```tsx
import TaskList from '@/Components/TaskList';

// In the render, add below deal details:
<TaskList
  tasks={deal.tasks}
  entityType="App\\Models\\Deal"
  entityId={deal.id}
/>
```

3. **Repeat for Contact and Company show pages**.

### Expected Result

```bash
# Visit deal page
open http://localhost/deals/1

# Expected:
# - Tasks section showing all related tasks
# - "Add Task" button pre-linking to deal
# - Checkboxes marking tasks complete
# - Priority badges
# - Due date with overdue highlighting
```

### Why It Works

**TaskList component** is reusable across all entity types by accepting `entityType` and `entityId` props. The "Add Task" button pre-populates the create form, maintaining context for users.

**Eager loading tasks** in the controller prevents N+1 queries when displaying multiple tasks with their assignees.

### Troubleshooting

- **Tasks not showing** — Verify `tasks` relationship is loaded in controller
- **Add Task link broken** — Check query parameters match TaskController's `preselected` logic
- **Checkbox not working** — Ensure toggle-complete route is accessible

## Exercises

### Exercise 1: Add Task Filtering Widget

**Goal**: Practice building reusable filter components

Create a sidebar widget on the tasks index showing quick filter buttons.

Requirements:

- "My Tasks" button (assigned to current user)
- "High Priority" button (priority = high or urgent)
- "No Due Date" button (due_date is null)
- "This Week" button (due between now and end of week)
- Each button shows count and applies filter when clicked
- Active filter highlighted with different background color

**Validation**: Clicking "My Tasks" should filter list to only user's tasks and highlight button

### Exercise 2: Implement Task Comments

**Goal**: Learn to add supplemental features to existing modules

Add commenting functionality to tasks for team collaboration.

Requirements:

- Create `task_comments` table with `task_id`, `user_id`, `comment` (text), `timestamps`
- Create `TaskComment` model with relationships
- Add comments section to Task Show page below details
- Create comment form with textarea and submit button
- Display comments in reverse chronological order with author and timestamp
- Allow comment author to delete their own comments

**Validation**: Adding a comment should immediately appear in the list without page refresh

### Exercise 3: Task Templates

**Goal**: Understand data duplication patterns for efficiency

Create task templates for common workflows (e.g., "Onboard New Client").

Requirements:

- Create `task_templates` table with `name`, `description`, `type`, `priority`, `team_id`
- Create `TaskTemplate` model and factory
- Add "Use Template" dropdown on task create form
- Selecting template pre-fills all fields except entity and due date
- Create seeder with 5 common task templates
- Allow users to save current task as template

**Validation**: Selecting "Follow-up Call" template should populate title, type=call, priority=normal

## Wrap-up

Outstanding work! You've built a complete task management system with automation built in. Your CRM now has:

✅ **Full Task CRUD** with team-scoped authorization  
✅ **Filterable task list** with stats, search, and sorting  
✅ **Quick-complete toggles** for instant status updates  
✅ **Polymorphic relationships** allowing tasks linked to multiple entities  
✅ **Task list components** embedded in entity detail pages  
✅ **Automated email reminders** sent daily via Laravel Scheduler  
✅ **Queue-based notifications** for async email delivery  
✅ **Cron configuration** ready for production deployment  
✅ **Reusable components** for task display across the application

You've mastered Laravel's Task Scheduler, a production-ready alternative to cron that provides timezone support, task overlap prevention, and elegant scheduling syntax. The reminder system ensures nothing falls through the cracks.

**What's next?** In [Chapter 19](/series/build-crm-laravel-12/chapters/19-notifications-email-integration), you'll expand the notification system beyond task reminders, adding notifications for deal closures, team invitations, and customizable user preferences. You'll learn Laravel's comprehensive notification channels including database notifications for in-app alerts.

<ChapterCheckbox 
  seriesId="build-crm-laravel-12"
  chapterId="18"
  label="Completed: 18: Tasks Module - CRUD & Task Scheduling"
/>

## Further Reading

- [Laravel Task Scheduling](https://laravel.com/docs/12.x/scheduling) — Official scheduling documentation
- [Laravel Notifications](https://laravel.com/docs/12.x/notifications) — Notification channels and customization
- [Laravel Queues](https://laravel.com/docs/12.x/queues) — Async job processing fundamentals
- [Cron Expression Syntax](https://crontab.guru/) — Interactive cron schedule builder
- [Laravel Horizon](https://laravel.com/docs/12.x/horizon) — Queue monitoring (covered in Chapter 30)
- [Mailhog Documentation](https://github.com/mailhog/MailHog) — Email testing tool
- [Laravel Eloquent: Polymorphic Relationships](https://laravel.com/docs/12.x/eloquent-relationships#polymorphic-relationships) — Deep dive into morphTo/morphMany

**Code Samples**: View complete implementations in [`/code/build-crm-laravel-12/chapter-18/`](https://github.com/dalehurley/codewithphp/tree/main/code/build-crm-laravel-12/chapter-18)