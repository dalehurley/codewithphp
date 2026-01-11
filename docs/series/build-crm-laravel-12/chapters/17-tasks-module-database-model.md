---
title: "17: Tasks Module - Database & Model"
description: "Design task system with polymorphic relationships allowing tasks to belong to contacts, companies, or deals"
series: "build-crm-laravel-12"
chapter: 17
order: 17
difficulty: "Intermediate"
prerequisites:
  - "/series/build-crm-laravel-12/chapters/16-deals-module-crud-pipeline-interface"
estimatedTime: "PT75M"
teaches:
  - 'Design a polymorphic task system allowing tasks to belong to multiple entity types'
  - 'Create a tasks migration with all necessary columns, indexes, and foreign keys'
  - 'Build a Task model with polymorphic relationships and query scopes'
  - 'Add inverse relationships to Contact, Company, and Deal models'
  - 'Implement task enums for type, priority, and status with proper validation'
---
![Tasks Module - Database & Model](/images/build-crm-laravel-12/chapter-17-tasks-module-database-model-hero-full.webp)

# Chapter 17: Tasks Module - Database & Model

## Overview

Your CRM tracks contacts, companies, and deals—but without tasks, nothing gets done. The Tasks Module is the operational engine of daily work: follow-up calls, send proposals, schedule meetings, close deals. Every successful salesperson lives in their task list, and every high-performing team tracks activity metrics through task completion rates.

In this chapter, you'll design a flexible task system using Laravel's polymorphic relationships, allowing a single task to belong to a contact, company, or deal. You'll create a normalized database schema supporting task types (Call, Email, Meeting, To-Do), priority levels, due dates, and completion tracking. You'll learn how to structure data for complex queries like "Show me all overdue tasks for my team" or "What tasks are associated with this $50K deal?"

By the end of this chapter, you'll have:

- **A production-ready tasks table** with polymorphic relationships, priority levels, and status tracking
- **Task model with relationships** to contacts, companies, deals, and users
- **Scoped queries** for filtering tasks by status, priority, date ranges, and assignee
- **Database architecture** supporting advanced features like recurring tasks and reminders
- **Foundation for Chapter 18** where you'll build the task management UI and Laravel scheduler integration

This chapter focuses on data modeling and schema design. You're building a system that will power daily workflows for sales teams managing hundreds of tasks simultaneously.

## Prerequisites

Before starting this chapter, you should have:

- ✅ Completed [Chapter 16](/series/build-crm-laravel-12/chapters/16-deals-module-crud-pipeline-interface) with Deals CRUD operational
- ✅ Completed [Chapter 11-14](/series/build-crm-laravel-12/chapters/11-contacts-module-database-model) with Contacts, Companies, and relationships
- ✅ Laravel Sail running with all containers active
- ✅ Database migrations applied with contacts, companies, and deals tables populated
- ✅ Understanding of Laravel migrations, Eloquent relationships, and polymorphic relationships
- ✅ Basic knowledge of task management systems (to-do lists, due dates, priorities)

**Estimated Time**: ~75 minutes (includes schema design, migrations, model configuration, polymorphic relationships, and testing)

**Verify your setup:**

```bash
# Navigate to your project
cd crm-app

# Verify Sail is running
sail ps  # Should show: laravel.test, mysql, redis all "Up"

# Verify existing models work
sail artisan tinker
$contact = App\Models\Contact::first();
$company = App\Models\Company::first();
$deal = App\Models\Deal::first();
echo $contact->full_name;
echo $company->name;
echo $deal->title;
exit
```

## What You'll Build

By the end of this chapter, you will have:

**Database Schema**:

- ✅ **tasks table** with polymorphic relationship columns (taskable_type, taskable_id)
- ✅ **Task types** (Call, Email, Meeting, To-Do) stored as enum or reference table
- ✅ **Priority levels** (Low, Normal, High, Urgent) with numeric values for sorting
- ✅ **Status tracking** (Open, In Progress, Completed, Cancelled) with timestamps
- ✅ **Due date** and **reminder** columns for scheduling integration
- ✅ **Soft deletes** for task recovery and audit trails
- ✅ **Indexes optimized** for common queries (by status, due date, assignee)

**Eloquent Models**:

- ✅ **Task model** with team scoping, polymorphic relationships, and computed properties
- ✅ **Polymorphic relationships** to Contact, Company, and Deal models
- ✅ **Inverse relationships** on Contact, Company, Deal (e.g., `$deal->tasks`)
- ✅ **Scopes** for filtering overdue tasks, pending tasks, and completed tasks
- ✅ **Accessors** for human-readable priority/status labels
- ✅ **Casts** for date fields and status enums (PHP 8.4 enums if using)

**Data Architecture**:

- ✅ **Normalized schema** preventing data duplication
- ✅ **Referential integrity** enforced via foreign keys
- ✅ **Team isolation** ensuring tasks are scoped to teams
- ✅ **Polymorphic constraints** allowing tasks to belong to multiple entity types
- ✅ **Task factory** generating realistic test data
- ✅ **Seeder** creating sample tasks for development

**Foundation for Chapter 18**:

- ✅ **Schema ready** for task CRUD operations and UI
- ✅ **Relationships configured** for eager loading and N+1 prevention
- ✅ **Scopes prepared** for filtering and search functionality
- ✅ **Computed properties** for displaying task status and priority

## Quick Start

Want to understand the end result? Here's what the Task model will support:

```php
// After completing this chapter:

// Create a task linked to a deal
$task = Task::create([
    'title' => 'Follow up on proposal',
    'description' => 'Call to discuss pricing and timeline',
    'type' => 'call',
    'priority' => 'high',
    'status' => 'open',
    'due_date' => now()->addDays(2),
    'taskable_type' => Deal::class,
    'taskable_id' => $deal->id,
    'assigned_to' => $user->id,
    'team_id' => $team->id,
]);

// Get all tasks for a deal
$deal->tasks;  // Polymorphic relationship

// Query overdue tasks
Task::overdue()->get();

// Filter by priority
Task::where('priority', 'urgent')->get();

// Get tasks assigned to current user
Task::forUser($user)->pending()->get();
```

## Objectives

By completing this chapter, you will:

- **Design a polymorphic task system** allowing tasks to belong to multiple entity types
- **Create a tasks migration** with all necessary columns, indexes, and foreign keys
- **Build a Task model** with polymorphic relationships and query scopes
- **Add inverse relationships** to Contact, Company, and Deal models
- **Implement task enums** for type, priority, and status with proper validation
- **Create a task factory** generating realistic test data for development
- **Master polymorphic relationships** understanding when and how to use them effectively

## Step 1: Design Task Schema (~15 min)

### Goal

Plan the task table structure supporting polymorphic relationships, priorities, types, and status tracking.

### Actions

1. **Identify required fields**:

**Core Task Fields**:
- `id` — Primary key
- `title` — Task name (required, max 255 chars)
- `description` — Detailed task notes (nullable, text)
- `type` — Task category (call, email, meeting, todo)
- `priority` — Urgency level (low, normal, high, urgent)
- `status` — Current state (open, in_progress, completed, cancelled)
- `due_date` — When task should be completed (nullable, datetime)
- `completed_at` — When task was marked done (nullable, datetime)

**Polymorphic Relationship**:
- `taskable_type` — Model class (Contact, Company, Deal)
- `taskable_id` — Model ID (polymorphic foreign key)

**Assignment & Ownership**:
- `team_id` — Team owning the task (foreign key to teams)
- `assigned_to` — User assigned to task (foreign key to users, nullable)
- `created_by` — User who created task (foreign key to users)

**Reminders & Scheduling**:
- `reminder_at` — When to send reminder (nullable, datetime)
- `reminder_sent` — Whether reminder was sent (boolean, default false)

**Timestamps & Soft Deletes**:
- `created_at`, `updated_at` — Automatic timestamps
- `deleted_at` — Soft delete support

2. **Define relationships**:

```
Task Model Relationships:
- belongsTo: team (Team)
- belongsTo: assignedTo (User)
- belongsTo: createdBy (User)
- morphTo: taskable (Contact, Company, or Deal)

Inverse Relationships:
- Contact hasMany tasks (polymorphic)
- Company hasMany tasks (polymorphic)
- Deal hasMany tasks (polymorphic)
- User hasMany tasks (as assigned_to)
- User hasMany tasks (as created_by)
```

3. **Plan indexes**:

```sql
-- Performance indexes
INDEX on (team_id, status, due_date)  -- Filter team tasks by status and due date
INDEX on (assigned_to, status)         -- User's pending tasks
INDEX on (taskable_type, taskable_id)  -- Polymorphic lookups
INDEX on (due_date, status)            -- Overdue task queries
INDEX on (deleted_at)                  -- Soft delete queries
```

### Expected Result

**Task schema planning complete** with:
- ✅ All required fields identified
- ✅ Polymorphic relationship structure defined
- ✅ Relationships mapped to all related models
- ✅ Indexes planned for common queries

### Why It Works

**Polymorphic relationships** use `taskable_type` (stores model class name) and `taskable_id` (stores model's primary key) to reference different entity types with a single relationship. This prevents creating separate tables for contact_tasks, company_tasks, deal_tasks—keeping the schema normalized.

**Status and priority** as string enums (or enum columns in MySQL 8.0+) provide type safety and validation while remaining human-readable. They're indexed separately for fast filtering.

### Troubleshooting

- **Why not separate tables?** — Polymorphic relationships reduce duplication when task structure is identical across entities
- **What about task subtasks?** — Can be added later with self-referential parent_id column
- **Why nullable assigned_to?** — Tasks can exist without assignment initially (created for planning)

## Step 2: Create Tasks Migration (~20 min)

### Goal

Build the Laravel migration defining the tasks table with all columns, indexes, and constraints.

### Actions

1. **Generate migration**:

```bash
sail artisan make:migration create_tasks_table
```

2. **Build tasks migration** (`database/migrations/YYYY_MM_DD_HHMMSS_create_tasks_table.php`):

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            
            // Core fields
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['call', 'email', 'meeting', 'todo'])->default('todo');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->enum('status', ['open', 'in_progress', 'completed', 'cancelled'])->default('open');
            
            // Polymorphic relationship (taskable can be Contact, Company, or Deal)
            $table->morphs('taskable');  // Creates taskable_type and taskable_id
            
            // Team and assignment
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            
            // Dates
            $table->dateTime('due_date')->nullable();
            $table->dateTime('completed_at')->nullable();
            
            // Reminders (for Chapter 18)
            $table->dateTime('reminder_at')->nullable();
            $table->boolean('reminder_sent')->default(false);
            
            // Timestamps and soft deletes
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['team_id', 'status', 'due_date']);
            $table->index(['assigned_to', 'status']);
            $table->index('due_date');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
```

3. **Run migration**:

```bash
sail artisan migrate

# Expected output:
# Migrating: YYYY_MM_DD_HHMMSS_create_tasks_table
# Migrated:  YYYY_MM_DD_HHMMSS_create_tasks_table (XXms)
```

4. **Verify schema**:

```bash
sail artisan tinker
Schema::hasTable('tasks');  # Should return true
Schema::hasColumn('tasks', 'taskable_type');  # Should return true
Schema::hasColumn('tasks', 'reminder_at');  # Should return true
exit
```

### Expected Result

```bash
# Check database directly
sail mysql

USE crm_app;
DESCRIBE tasks;

# Expected columns:
# id, title, description, type, priority, status,
# taskable_type, taskable_id,
# team_id, assigned_to, created_by,
# due_date, completed_at,
# reminder_at, reminder_sent,
# created_at, updated_at, deleted_at
```

### Why It Works

**`morphs('taskable')`** is Laravel's helper creating both `taskable_type` (string) and `taskable_id` (bigint unsigned) columns with a composite index automatically. This is the foundation of polymorphic relationships.

**Enum columns** enforce valid values at the database level, preventing invalid statuses or priorities. They're readable in raw SQL queries and provide automatic validation.

**Foreign key constraints** with `cascadeOnDelete()` and `nullOnDelete()` ensure referential integrity: when a team is deleted, all tasks are deleted; when a user is deleted, tasks assigned to them become unassigned (nulled).

**Composite indexes** like `(team_id, status, due_date)` optimize multi-column WHERE clauses, making queries like "team's pending tasks due this week" extremely fast.

### Troubleshooting

- **Error: "Enum type not supported"** — Ensure MySQL 8.0+ or switch to string columns with validation rules
- **Foreign key constraint fails** — Verify teams and users tables exist before running migration
- **Index too long** — MySQL has key length limits; use shorter strings or `->index(['col'], 'custom_name')`

## Step 3: Create Task Model (~15 min)

### Goal

Build the Task Eloquent model with polymorphic relationships, scopes, and accessors.

### Actions

1. **Generate model**:

```bash
sail artisan make:model Task
```

2. **Build Task model** (`app/Models/Task.php`):

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'type',
        'priority',
        'status',
        'taskable_type',
        'taskable_id',
        'team_id',
        'assigned_to',
        'created_by',
        'due_date',
        'completed_at',
        'reminder_at',
        'reminder_sent',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'completed_at' => 'datetime',
        'reminder_at' => 'datetime',
        'reminder_sent' => 'boolean',
    ];

    protected $appends = [
        'is_overdue',
        'priority_label',
        'status_label',
    ];

    /**
     * Polymorphic relationship: task belongs to Contact, Company, or Deal
     */
    public function taskable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Task belongs to a team
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Task is assigned to a user
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Task was created by a user
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope: Tasks for a specific team
     */
    public function scopeForTeam(Builder $query, int $teamId): Builder
    {
        return $query->where('team_id', $teamId);
    }

    /**
     * Scope: Tasks assigned to a user
     */
    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where('assigned_to', $user->id);
    }

    /**
     * Scope: Pending tasks (open or in_progress)
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->whereIn('status', ['open', 'in_progress']);
    }

    /**
     * Scope: Completed tasks
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope: Overdue tasks (past due_date and not completed)
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('due_date', '<', now())
            ->whereNotIn('status', ['completed', 'cancelled']);
    }

    /**
     * Scope: Tasks due today
     */
    public function scopeDueToday(Builder $query): Builder
    {
        return $query->whereDate('due_date', today())
            ->whereNotIn('status', ['completed', 'cancelled']);
    }

    /**
     * Scope: Tasks due this week
     */
    public function scopeDueThisWeek(Builder $query): Builder
    {
        return $query->whereBetween('due_date', [now()->startOfWeek(), now()->endOfWeek()])
            ->whereNotIn('status', ['completed', 'cancelled']);
    }

    /**
     * Scope: Tasks by priority
     */
    public function scopeByPriority(Builder $query, string $priority): Builder
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope: Tasks by type
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Accessor: Is task overdue?
     */
    public function getIsOverdueAttribute(): bool
    {
        if (!$this->due_date || in_array($this->status, ['completed', 'cancelled'])) {
            return false;
        }
        return $this->due_date->isPast();
    }

    /**
     * Accessor: Human-readable priority label
     */
    public function getPriorityLabelAttribute(): string
    {
        return match ($this->priority) {
            'low' => 'Low',
            'normal' => 'Normal',
            'high' => 'High',
            'urgent' => 'Urgent',
            default => 'Unknown',
        };
    }

    /**
     * Accessor: Human-readable status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'open' => 'Open',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => 'Unknown',
        };
    }

    /**
     * Mark task as completed
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark task as in progress
     */
    public function markAsInProgress(): void
    {
        $this->update([
            'status' => 'in_progress',
        ]);
    }

    /**
     * Cancel task
     */
    public function cancel(): void
    {
        $this->update([
            'status' => 'cancelled',
        ]);
    }

    /**
     * Get priority numeric value for sorting (higher = more urgent)
     */
    public function getPriorityValueAttribute(): int
    {
        return match ($this->priority) {
            'low' => 1,
            'normal' => 2,
            'high' => 3,
            'urgent' => 4,
            default => 0,
        };
    }
}
```

### Expected Result

```bash
# Test Task model
sail artisan tinker

$task = new App\Models\Task();
$task->title = "Test Task";
$task->type = "call";
$task->priority = "high";
$task->status = "open";
$task->team_id = 1;
$task->created_by = 1;
$task->save();

# Test relationships
$task->team;  # Should return Team
$task->createdBy;  # Should return User

# Test scopes
Task::pending()->count();  # Should return count of open/in_progress tasks
Task::overdue()->count();  # Should return count of overdue tasks

exit
```

### Why It Works

**`morphTo()`** defines the polymorphic relationship, allowing Eloquent to dynamically load the related model based on `taskable_type`. When you call `$task->taskable`, Laravel checks the type column and loads the appropriate model (Contact, Company, or Deal).

**Query scopes** like `pending()` and `overdue()` encapsulate complex WHERE clauses, making queries readable: `Task::overdue()->forUser($user)->get()` is self-documenting and reusable.

**Accessors** like `getIsOverdueAttribute()` compute values on-the-fly without storing them in the database. They're automatically included in JSON responses via `$appends`.

**Helper methods** like `markAsCompleted()` encapsulate business logic, ensuring status and timestamp changes always happen together.

### Troubleshooting

- **morphTo() returns null** — Verify `taskable_type` contains full class name including namespace (e.g., `App\Models\Contact`)
- **Scope not working** — Ensure method signature matches: `scopeName(Builder $query, ...params)`
- **Accessor not appearing** — Add accessor name to `$appends` array

## Step 4: Add Inverse Relationships to Related Models (~10 min)

### Goal

Add polymorphic `hasMany` relationships to Contact, Company, and Deal models allowing access to their tasks.

### Actions

1. **Update Contact model** (`app/Models/Contact.php`):

```php
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Contact has many tasks (polymorphic)
 */
public function tasks(): MorphMany
{
    return $this->morphMany(Task::class, 'taskable');
}
```

2. **Update Company model** (`app/Models/Company.php`):

```php
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Company has many tasks (polymorphic)
 */
public function tasks(): MorphMany
{
    return $this->morphMany(Task::class, 'taskable');
}
```

3. **Update Deal model** (`app/Models/Deal.php`):

```php
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Deal has many tasks (polymorphic)
 */
public function tasks(): MorphMany
{
    return $this->morphMany(Task::class, 'taskable');
}
```

4. **Test inverse relationships**:

```bash
sail artisan tinker

# Create task for a deal
$deal = App\Models\Deal::first();
$task = $deal->tasks()->create([
    'title' => 'Follow up on proposal',
    'type' => 'call',
    'priority' => 'high',
    'status' => 'open',
    'team_id' => $deal->team_id,
    'created_by' => 1,
    'due_date' => now()->addDays(2),
]);

# Retrieve all tasks for the deal
$deal->tasks;  # Should return collection with the new task

# Same for contact
$contact = App\Models\Contact::first();
$contact->tasks()->create([
    'title' => 'Send follow-up email',
    'type' => 'email',
    'priority' => 'normal',
    'status' => 'open',
    'team_id' => $contact->team_id,
    'created_by' => 1,
]);

$contact->tasks;  # Should return collection with task

exit
```

### Expected Result

✅ **Bidirectional polymorphic relationships working**:
- `$task->taskable` returns the related Contact, Company, or Deal
- `$deal->tasks` returns all tasks linked to that deal
- `$contact->tasks` returns all tasks linked to that contact
- `$company->tasks` returns all tasks linked to that company

### Why It Works

**`morphMany()`** defines the inverse of `morphTo()`. When you call `$deal->tasks`, Laravel generates a query like:

```sql
SELECT * FROM tasks
WHERE taskable_type = 'App\\Models\\Deal'
AND taskable_id = ?
```

This allows navigation from either direction: task to parent entity or entity to tasks. The relationship is seamless and type-safe.

### Troubleshooting

- **tasks relationship returns empty** — Verify `taskable_type` exactly matches model's fully-qualified class name
- **Cannot create task via relationship** — Ensure parent model has `team_id` attribute for auto-filling
- **Type mismatch** — Check `taskable_id` column type matches parent model's primary key type (usually bigint)

## Step 5: Create Task Factory (~10 min)

### Goal

Build a factory generating realistic task test data for development and testing.

### Actions

1. **Generate factory**:

```bash
sail artisan make:factory TaskFactory
```

2. **Build TaskFactory** (`database/factories/TaskFactory.php`):

```php
<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use App\Models\Contact;
use App\Models\Company;
use App\Models\Deal;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        $dueDate = fake()->optional(0.8)->dateTimeBetween('now', '+30 days');
        $status = fake()->randomElement(['open', 'open', 'in_progress', 'completed', 'cancelled']);
        
        return [
            'title' => fake()->randomElement([
                'Follow up with client',
                'Send proposal',
                'Schedule demo',
                'Review contract',
                'Prepare presentation',
                'Send quote',
                'Call for feedback',
                'Email meeting notes',
                'Update CRM',
                'Schedule follow-up',
            ]),
            'description' => fake()->optional(0.6)->paragraph(),
            'type' => fake()->randomElement(['call', 'email', 'meeting', 'todo']),
            'priority' => fake()->randomElement(['low', 'normal', 'normal', 'high', 'urgent']),
            'status' => $status,
            'due_date' => $dueDate,
            'completed_at' => $status === 'completed' ? fake()->dateTimeBetween('-7 days', 'now') : null,
            'reminder_at' => $dueDate ? fake()->dateTimeBefore($dueDate, '-1 day') : null,
            'reminder_sent' => false,
        ];
    }

    /**
     * Task assigned to a contact
     */
    public function forContact(Contact $contact): static
    {
        return $this->state(fn (array $attributes) => [
            'taskable_type' => Contact::class,
            'taskable_id' => $contact->id,
            'team_id' => $contact->team_id,
        ]);
    }

    /**
     * Task assigned to a company
     */
    public function forCompany(Company $company): static
    {
        return $this->state(fn (array $attributes) => [
            'taskable_type' => Company::class,
            'taskable_id' => $company->id,
            'team_id' => $company->team_id,
        ]);
    }

    /**
     * Task assigned to a deal
     */
    public function forDeal(Deal $deal): static
    {
        return $this->state(fn (array $attributes) => [
            'taskable_type' => Deal::class,
            'taskable_id' => $deal->id,
            'team_id' => $deal->team_id,
        ]);
    }

    /**
     * Overdue task
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_date' => fake()->dateTimeBetween('-14 days', '-1 day'),
            'status' => fake()->randomElement(['open', 'in_progress']),
            'completed_at' => null,
        ]);
    }

    /**
     * Completed task
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'completed_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    /**
     * High priority task
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => fake()->randomElement(['high', 'urgent']),
        ]);
    }

    /**
     * Task due today
     */
    public function dueToday(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_date' => today()->setTime(17, 0),  // 5 PM today
            'status' => 'open',
        ]);
    }
}
```

3. **Test factory**:

```bash
sail artisan tinker

# Create random task
$task = Task::factory()->create([
    'team_id' => 1,
    'created_by' => 1,
    'assigned_to' => 1,
]);

# Create task for a deal
$deal = App\Models\Deal::first();
$task = Task::factory()->forDeal($deal)->create([
    'created_by' => 1,
    'assigned_to' => 1,
]);

# Create overdue task
$task = Task::factory()->overdue()->create([
    'team_id' => 1,
    'created_by' => 1,
    'assigned_to' => 1,
    'taskable_type' => Contact::class,
    'taskable_id' => 1,
]);

echo $task->is_overdue;  # Should return true

exit
```

### Expected Result

✅ **Factory generates realistic task data**:
- Varied task titles from common CRM activities
- Random but realistic status distributions (more open than completed)
- Due dates in the future (80% chance of having one)
- Completed tasks have completion timestamps
- State methods (`forDeal`, `overdue`, `highPriority`) customize generation

### Why It Works

**Factory states** like `forDeal()` and `overdue()` return `static` (the factory instance), allowing method chaining: `Task::factory()->forDeal($deal)->overdue()->create()` applies multiple states sequentially.

**Weighted randomness** (`fake()->randomElement(['open', 'open', 'in_progress', 'completed'])`) duplicates values to increase probability of certain outcomes, creating more realistic distributions.

### Troubleshooting

- **Missing taskable relationship** — Always provide `taskable_type` and `taskable_id` when creating tasks
- **Team ID required** — Tasks require team_id; use state methods or pass manually
- **Factory not found** — Run `composer dump-autoload` after creating factory

## Step 6: Create Task Seeder (~5 min)

### Goal

Seed the database with sample tasks for development and testing.

### Actions

1. **Generate seeder**:

```bash
sail artisan make:seeder TaskSeeder
```

2. **Build TaskSeeder** (`database/seeders/TaskSeeder.php`):

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Task;
use App\Models\Contact;
use App\Models\Company;
use App\Models\Deal;
use App\Models\User;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        // Get first team and user
        $teamId = 1;
        $userId = User::where('current_team_id', $teamId)->first()->id;

        // Get sample entities
        $contacts = Contact::where('team_id', $teamId)->limit(5)->get();
        $companies = Company::where('team_id', $teamId)->limit(5)->get();
        $deals = Deal::where('team_id', $teamId)->limit(5)->get();

        // Create tasks for contacts
        $contacts->each(function ($contact) use ($userId) {
            Task::factory()->count(2)->forContact($contact)->create([
                'created_by' => $userId,
                'assigned_to' => $userId,
            ]);
        });

        // Create tasks for companies
        $companies->each(function ($company) use ($userId) {
            Task::factory()->count(2)->forCompany($company)->create([
                'created_by' => $userId,
                'assigned_to' => $userId,
            ]);
        });

        // Create tasks for deals (more tasks per deal)
        $deals->each(function ($deal) use ($userId) {
            Task::factory()->count(3)->forDeal($deal)->create([
                'created_by' => $userId,
                'assigned_to' => $userId,
            ]);
        });

        // Create some overdue tasks
        Task::factory()->count(5)->overdue()->create([
            'team_id' => $teamId,
            'created_by' => $userId,
            'assigned_to' => $userId,
            'taskable_type' => Deal::class,
            'taskable_id' => $deals->random()->id,
        ]);

        // Create tasks due today
        Task::factory()->count(3)->dueToday()->create([
            'team_id' => $teamId,
            'created_by' => $userId,
            'assigned_to' => $userId,
            'taskable_type' => Contact::class,
            'taskable_id' => $contacts->random()->id,
        ]);
    }
}
```

3. **Run seeder**:

```bash
sail artisan db:seed --class=TaskSeeder

# Expected output:
# Seeding: Database\Seeders\TaskSeeder
# Seeded:  Database\Seeders\TaskSeeder (XXms)
```

4. **Verify seeded data**:

```bash
sail artisan tinker

Task::count();  # Should return ~40+ tasks
Task::overdue()->count();  # Should return ~5
Task::dueToday()->count();  # Should return ~3

# Check polymorphic relationships
$deal = Deal::first();
$deal->tasks->count();  # Should be 3+

exit
```

### Expected Result

✅ **Database seeded with realistic task data**:
- ~10 tasks for contacts (2 each for 5 contacts)
- ~10 tasks for companies (2 each for 5 companies)
- ~15 tasks for deals (3 each for 5 deals)
- 5 overdue tasks
- 3 tasks due today
- All tasks properly linked via polymorphic relationships

### Why It Works

The **seeder uses the factory** to generate consistent, realistic data following the patterns defined in TaskFactory. Using `each()` on collections creates tasks for every entity efficiently.

**Mixing factory states** (`overdue()`, `dueToday()`) with explicit attributes ensures specific test scenarios exist in the development database.

### Troubleshooting

- **No entities found** — Run Contact, Company, Deal seeders first
- **Seeder fails** — Check that team_id, created_by, assigned_to reference existing records
- **Duplicate data** — Truncate tasks table before reseeding: `Task::truncate();`

## Step 7: Test Task Relationships and Scopes (~10 min)

### Goal

Verify all task relationships, scopes, and queries work correctly.

### Actions

1. **Test polymorphic relationships**:

```bash
sail artisan tinker

# Test task -> entity relationship
$task = Task::first();
$task->taskable;  # Should return Contact, Company, or Deal
$task->taskable_type;  # Should be full class name

# Test entity -> tasks relationship
$deal = Deal::first();
$deal->tasks;  # Should return collection of tasks
$deal->tasks()->count();  # Should return count

# Create task via relationship
$newTask = $deal->tasks()->create([
    'title' => 'Relationship test task',
    'type' => 'todo',
    'priority' => 'normal',
    'status' => 'open',
    'team_id' => $deal->team_id,
    'created_by' => 1,
]);

$deal->tasks()->count();  # Should increase by 1
```

2. **Test scopes**:

```bash
# Still in tinker...

# Pending tasks
Task::pending()->count();

# Overdue tasks
Task::overdue()->get();

# Due today
Task::dueToday()->count();

# Tasks for specific user
$user = User::first();
Task::forUser($user)->count();

# Combined scopes
Task::pending()->overdue()->forUser($user)->get();

# High priority pending tasks
Task::pending()->byPriority('high')->get();
```

3. **Test accessors**:

```bash
# Still in tinker...

$task = Task::overdue()->first();
$task->is_overdue;  # Should be true
$task->priority_label;  # Should be human-readable (e.g., "High")
$task->status_label;  # Should be human-readable (e.g., "Open")

# Test helper methods
$task->markAsCompleted();
$task->status;  # Should be "completed"
$task->completed_at;  # Should be current timestamp
```

4. **Test eager loading**:

```bash
# Still in tinker...

# Without eager loading (N+1 problem)
$tasks = Task::limit(10)->get();
$tasks->each(fn($task) => $task->taskable->id);  # Triggers 10 queries

# With eager loading (efficient)
$tasks = Task::with('taskable')->limit(10)->get();
$tasks->each(fn($task) => $task->taskable->id);  # Only 2 queries

exit
```

### Expected Result

✅ **All task functionality working**:
- Polymorphic relationships navigate both directions
- Scopes filter tasks correctly
- Accessors compute values accurately
- Helper methods update status and timestamps
- Eager loading prevents N+1 queries

### Why It Works

**Polymorphic relationships** store the model class name, allowing Eloquent to dynamically load the correct related model. This flexibility enables a single task table to reference multiple entity types without duplication.

**Query scopes** encapsulate complex WHERE clauses, making code self-documenting and reusable. They're chainable, allowing combinations like `pending()->overdue()->highPriority()`.

**Eager loading** with `with('taskable')` tells Eloquent to load all related models in a second query instead of one query per task, dramatically improving performance.

### Troubleshooting

- **taskable returns null** — Verify `taskable_type` contains full namespace (e.g., `App\Models\Deal`)
- **Scope not working** — Check method signature: `scopeName(Builder $query)`
- **N+1 queries persist** — Use Laravel Debugbar or Telescope to verify eager loading is applied

## Exercises

### Exercise 1: Add Task Comments

**Goal**: Learn to extend models with related data

Add a task_comments table allowing team members to comment on tasks.

Requirements:

- Create `task_comments` migration with `task_id`, `user_id`, `comment` (text), `timestamps`
- Create `TaskComment` model with relationships to Task and User
- Add inverse relationship: `Task hasMany TaskComment`
- Create factory and seeder for comments
- Test creating comments via relationship: `$task->comments()->create([...])`

**Validation**: `$task->comments()->count()` should return number of comments

### Exercise 2: Implement Task Tags

**Goal**: Practice many-to-many polymorphic relationships

Add tagging system allowing tasks to have multiple tags.

Requirements:

- Create `tags` table with `id`, `name`, `team_id`, `timestamps`
- Create `taggables` pivot table with `tag_id`, `taggable_type`, `taggable_id`
- Add `morphToMany` relationship on Task model
- Add scope: `Task::withTag('follow-up')` filtering by tag
- Allow same tag system for Contacts, Companies, Deals

**Validation**: `$task->tags()->attach($tag)` and `$task->tags` should work

### Exercise 3: Add Recurring Tasks

**Goal**: Understand advanced date handling and task automation

Add support for recurring tasks (daily, weekly, monthly).

Requirements:

- Add `is_recurring` (boolean) and `recurrence_pattern` (string) columns to tasks
- Create `TaskRecurrence` model handling patterns (e.g., "daily", "weekly", "monthly")
- Implement `Task::createNextRecurrence()` method generating next task instance
- Add Artisan command checking for completed recurring tasks and creating next occurrence
- Test: Mark recurring task complete → next instance created automatically

**Validation**: Completing a weekly task should create next task 7 days later with same details

## Wrap-up

Excellent work! You've designed a professional task management system with polymorphic relationships allowing tasks to belong to multiple entity types. Your CRM now has:

✅ **Polymorphic task system** linking tasks to contacts, companies, and deals  
✅ **Task model with rich relationships** to teams, users, and entities  
✅ **Query scopes** for filtering by status, priority, due date, and user  
✅ **Accessors** computing overdue status and human-readable labels  
✅ **Helper methods** marking tasks complete and transitioning status  
✅ **Factory and seeder** generating realistic test data  
✅ **Database schema** optimized with indexes for common queries  
✅ **Bidirectional relationships** navigating from tasks to entities and vice versa

You've mastered polymorphic relationships, one of Laravel's most powerful features for flexible data modeling. Tasks are now the operational backbone of your CRM, ready to drive daily sales workflows.

**What's next?** In [Chapter 18](/series/build-crm-laravel-12/chapters/18-tasks-module-crud-task-scheduling), you'll build the task management UI with CRUD operations, implement Laravel's Task Scheduler to send automated reminders for overdue tasks, and integrate tasks into the contacts, companies, and deals views.

<ChapterCheckbox 
  seriesId="build-crm-laravel-12"
  chapterId="17"
  label="Completed: 17: Tasks Module - Database & Model"
/>

## Further Reading

- [Laravel Polymorphic Relationships](https://laravel.com/docs/12.x/eloquent-relationships#polymorphic-relationships) — Official docs on morphTo/morphMany
- [Laravel Query Scopes](https://laravel.com/docs/12.x/eloquent#query-scopes) — Local and global scopes documentation
- [Laravel Accessors & Mutators](https://laravel.com/docs/12.x/eloquent-mutators) — Computed properties and attribute casting
- [Laravel Database Indexes](https://laravel.com/docs/12.x/migrations#indexes) — Index types and optimization strategies
- [Polymorphic Relationships in Practice](https://laracasts.com/series/eloquent-techniques/episodes/4) — Video tutorial by Jeffrey Way
- [Laravel Collections](https://laravel.com/docs/12.x/collections) — Methods like each(), map(), filter()
- [Enum Columns in MySQL](https://dev.mysql.com/doc/refman/8.0/en/enum.html) — Database-level enums vs PHP enums

**Code Samples**: View complete implementations in [`/code/build-crm-laravel-12/chapter-17/`](https://github.com/dalehurley/codewithphp/tree/main/code/build-crm-laravel-12/chapter-17)
