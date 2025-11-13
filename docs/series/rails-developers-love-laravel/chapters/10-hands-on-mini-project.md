---
title: "Hands-On Mini Project: Build a Task Manager"
description: Build a complete task management application with Laravel, applying everything you've learned in this series.
series: rails-developers-love-laravel
chapter: 10
difficulty: Intermediate
tags: ["laravel", "project", "tutorial", "hands-on", "practice"]
---

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/rails-developers-love-laravel/">Rails to Laravel</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 10</span>
</div>

![Hands-On Mini Project](/images/rails-developers-love-laravel/chapter-10-hands-on-mini-project-hero-full.webp)

# Chapter 10: Hands-On Mini Project <span class="difficulty-badge difficulty-intermediate">Intermediate</span>

## Overview

Theory is great, but nothing beats building a real application. In this final chapter, you'll build a complete task management application with Laravel, applying everything you've learned throughout this series.

We'll create a full-featured app with authentication, REST API, testing, and deployment—just like you would in Rails, but with Laravel.

## What You'll Build

**TaskMaster** — A task management application with:

- ✅ User authentication (Laravel Sanctum)
- ✅ CRUD operations for tasks
- ✅ Task categories and tags
- ✅ Task assignment to users
- ✅ Search and filtering
- ✅ REST API endpoints
- ✅ Comprehensive tests
- ✅ API documentation
- ✅ Ready for deployment

**Tech Stack:**
- Laravel 11
- PHP 8.4
- MySQL/SQLite
- Laravel Sanctum (API auth)
- PHPUnit/Pest (testing)

**Estimated Time:** 3-4 hours

## Prerequisites

```bash
# Ensure you have:
- PHP 8.4+
- Composer
- MySQL or SQLite
- Git

# Check versions
php --version
composer --version
```

## Step 1: Create the Project

### Rails Equivalent

```bash
rails new taskmaster --database=postgresql
cd taskmaster
```

### Laravel

```bash
# Create new Laravel project
composer create-project laravel/laravel taskmaster
cd taskmaster

# Or using Laravel installer
laravel new taskmaster
```

### Initial Setup

```bash
# Configure database
cp .env.example .env
php artisan key:generate

# Edit .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=taskmaster
DB_USERNAME=root
DB_PASSWORD=

# Or use SQLite for simplicity
DB_CONNECTION=sqlite
# DB_DATABASE=/absolute/path/to/database.sqlite

# Create SQLite database
touch database/database.sqlite

# Test the setup
php artisan serve
# Visit http://localhost:8000
```

## Step 2: Install Dependencies

```bash
# Install Sanctum for API authentication
composer require laravel/sanctum

# Install Pest for testing (optional but recommended)
composer require pestphp/pest --dev --with-all-dependencies
php artisan pest:install

# Install Laravel IDE Helper (development)
composer require --dev barryvdh/laravel-ide-helper
```

## Step 3: Database Design

### Schema Overview

```
users
- id
- name
- email
- password
- timestamps

tasks
- id
- user_id (owner)
- title
- description
- status (pending/in_progress/completed)
- priority (low/medium/high)
- due_date
- completed_at
- timestamps

categories
- id
- name
- color
- timestamps

task_category (pivot)
- task_id
- category_id

tags
- id
- name
- timestamps

task_tag (pivot)
- task_id
- tag_id
```

### Create Migrations

```bash
# Create migrations
php artisan make:migration create_tasks_table
php artisan make:migration create_categories_table
php artisan make:migration create_tags_table
php artisan make:migration create_task_category_table
php artisan make:migration create_task_tag_table
```

### Tasks Migration

```php
<?php
// database/migrations/xxxx_create_tasks_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed'])
                ->default('pending');
            $table->enum('priority', ['low', 'medium', 'high'])
                ->default('medium');
            $table->date('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('status');
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
```

### Categories Migration

```php
<?php
// database/migrations/xxxx_create_categories_table.php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('color', 7)->default('#3B82F6'); // Hex color
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
```

### Tags Migration

```php
<?php
// database/migrations/xxxx_create_tags_table.php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tags');
    }
};
```

### Pivot Tables

```php
<?php
// database/migrations/xxxx_create_task_category_table.php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_category', function (Blueprint $table) {
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->primary(['task_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_category');
    }
};

// database/migrations/xxxx_create_task_tag_table.php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_tag', function (Blueprint $table) {
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->foreignId('tag_id')->constrained()->onDelete('cascade');
            $table->primary(['task_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_tag');
    }
};
```

### Run Migrations

```bash
php artisan migrate
```

## Step 4: Create Models

### Task Model

```php
<?php
// app/Models/Task.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'completed_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeHighPriority($query)
    {
        return $query->where('priority', 'high');
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->whereNot('status', 'completed');
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%");
        });
    }

    // Helper methods
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function isOverdue(): bool
    {
        return $this->due_date &&
               $this->due_date->isPast() &&
               $this->status !== 'completed';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
```

### Category Model

```php
<?php
// app/Models/Category.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'color'];

    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class);
    }
}
```

### Tag Model

```php
<?php
// app/Models/Tag.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class);
    }
}
```

### Update User Model

```php
<?php
// app/Models/User.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
```

## Step 5: Create Factories

```bash
# Generate factories
php artisan make:factory TaskFactory
php artisan make:factory CategoryFactory
php artisan make:factory TagFactory
```

### Task Factory

```php
<?php
// database/factories/TaskFactory.php
namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    public function definition(): array
    {
        $status = fake()->randomElement(['pending', 'in_progress', 'completed']);

        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'status' => $status,
            'priority' => fake()->randomElement(['low', 'medium', 'high']),
            'due_date' => fake()->optional()->dateTimeBetween('now', '+30 days'),
            'completed_at' => $status === 'completed' ? now() : null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'due_date' => now()->subDays(5),
            'completed_at' => null,
        ]);
    }
}
```

### Category & Tag Factories

```php
<?php
// database/factories/CategoryFactory.php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'color' => fake()->hexColor(),
        ];
    }
}

// database/factories/TagFactory.php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TagFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
        ];
    }
}
```

## Step 6: Create API Controllers

```bash
# Create controllers
php artisan make:controller Api/AuthController
php artisan make:controller Api/TaskController --api
php artisan make:controller Api/CategoryController --api
php artisan make:controller Api/TagController --api
```

### Auth Controller

```php
<?php
// app/Http/Controllers/Api/AuthController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }
}
```

### Task Controller

```php
<?php
// app/Http/Controllers/Api/TaskController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $request->user()->tasks()->with(['categories', 'tags']);

        // Filtering
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->has('overdue')) {
            $query->overdue();
        }

        if ($request->has('search')) {
            $query->search($request->search);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        $tasks = $query->paginate($request->get('per_page', 15));

        return response()->json($tasks);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:pending,in_progress,completed',
            'priority' => 'nullable|in:low,medium,high',
            'due_date' => 'nullable|date',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:tags,id',
        ]);

        $task = $request->user()->tasks()->create($validated);

        if (isset($validated['category_ids'])) {
            $task->categories()->sync($validated['category_ids']);
        }

        if (isset($validated['tag_ids'])) {
            $task->tags()->sync($validated['tag_ids']);
        }

        $task->load(['categories', 'tags']);

        return response()->json($task, 201);
    }

    public function show(Request $request, Task $task): JsonResponse
    {
        $this->authorize('view', $task);

        $task->load(['categories', 'tags', 'user']);

        return response()->json($task);
    }

    public function update(Request $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:pending,in_progress,completed',
            'priority' => 'sometimes|in:low,medium,high',
            'due_date' => 'nullable|date',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:tags,id',
        ]);

        $task->update($validated);

        if (isset($validated['category_ids'])) {
            $task->categories()->sync($validated['category_ids']);
        }

        if (isset($validated['tag_ids'])) {
            $task->tags()->sync($validated['tag_ids']);
        }

        if (isset($validated['status']) && $validated['status'] === 'completed') {
            $task->markAsCompleted();
        }

        $task->load(['categories', 'tags']);

        return response()->json($task);
    }

    public function destroy(Task $task): JsonResponse
    {
        $this->authorize('delete', $task);

        $task->delete();

        return response()->json(null, 204);
    }

    public function complete(Request $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $task->markAsCompleted();

        return response()->json($task);
    }
}
```

## Step 7: Create Authorization Policy

```bash
php artisan make:policy TaskPolicy --model=Task
```

```php
<?php
// app/Policies/TaskPolicy.php
namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function view(User $user, Task $task): bool
    {
        return $user->id === $task->user_id;
    }

    public function update(User $user, Task $task): bool
    {
        return $user->id === $task->user_id;
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->id === $task->user_id;
    }
}
```

## Step 8: Define API Routes

```php
<?php
// routes/api.php
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\TagController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Tasks
    Route::apiResource('tasks', TaskController::class);
    Route::post('tasks/{task}/complete', [TaskController::class, 'complete']);

    // Categories
    Route::apiResource('categories', CategoryController::class);

    // Tags
    Route::apiResource('tags', TagController::class);
});
```

## Step 9: Write Tests

### Feature Tests with Pest

```php
<?php
// tests/Feature/TaskTest.php
use App\Models\User;
use App\Models\Task;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('can list user tasks', function () {
    Task::factory()->count(3)->create(['user_id' => $this->user->id]);
    Task::factory()->count(2)->create(); // Other users' tasks

    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/tasks');

    $response->assertOk()
        ->assertJsonCount(3, 'data');
});

it('can create a task', function () {
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

    expect(Task::where('title', 'Test Task'))->toExist();
});

it('can update a task', function () {
    $task = Task::factory()->create(['user_id' => $this->user->id]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->putJson("/api/tasks/{$task->id}", [
            'title' => 'Updated Title',
        ]);

    $response->assertOk()
        ->assertJson(['title' => 'Updated Title']);
});

it('can delete a task', function () {
    $task = Task::factory()->create(['user_id' => $this->user->id]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->deleteJson("/api/tasks/{$task->id}");

    $response->assertNoContent();

    expect(Task::find($task->id))->toBeNull();
});

it('cannot access other users tasks', function () {
    $otherUser = User::factory()->create();
    $task = Task::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson("/api/tasks/{$task->id}");

    $response->assertForbidden();
});

it('can mark task as completed', function () {
    $task = Task::factory()->create([
        'user_id' => $this->user->id,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson("/api/tasks/{$task->id}/complete");

    $response->assertOk();

    $task->refresh();
    expect($task->status)->toBe('completed')
        ->and($task->completed_at)->not->toBeNull();
});

it('can filter tasks by status', function () {
    Task::factory()->completed()->count(2)->create(['user_id' => $this->user->id]);
    Task::factory()->count(3)->create(['user_id' => $this->user->id, 'status' => 'pending']);

    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/tasks?status=completed');

    $response->assertOk()
        ->assertJsonCount(2, 'data');
});
```

### Run Tests

```bash
# Run all tests
php artisan test

# Or with Pest
./vendor/bin/pest

# With coverage
./vendor/bin/pest --coverage
```

## Step 10: Seed Database

```php
<?php
// database/seeders/DatabaseSeeder.php
namespace Database\Seeders;

use App\Models\User;
use App\Models\Task;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        // Create regular users
        $users = User::factory()->count(5)->create();

        // Create categories
        $categories = Category::factory()->count(5)->create([
            ['name' => 'Work', 'color' => '#3B82F6'],
            ['name' => 'Personal', 'color' => '#10B981'],
            ['name' => 'Shopping', 'color' => '#F59E0B'],
            ['name' => 'Health', 'color' => '#EF4444'],
            ['name' => 'Learning', 'color' => '#8B5CF6'],
        ]);

        // Create tags
        $tags = Tag::factory()->count(10)->create();

        // Create tasks for each user
        $users->push($admin)->each(function ($user) use ($categories, $tags) {
            Task::factory()->count(10)->create([
                'user_id' => $user->id,
            ])->each(function ($task) use ($categories, $tags) {
                // Attach random categories
                $task->categories()->attach(
                    $categories->random(rand(1, 3))->pluck('id')
                );

                // Attach random tags
                $task->tags()->attach(
                    $tags->random(rand(1, 4))->pluck('id')
                );
            });
        });
    }
}
```

```bash
# Seed database
php artisan db:seed
```

## Step 11: Test the API

### Using cURL

```bash
# Register
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'

# Login
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'

# Save the token from response

# List tasks
curl -X GET http://localhost:8000/api/tasks \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"

# Create task
curl -X POST http://localhost:8000/api/tasks \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "My First Task",
    "description": "This is a test task",
    "priority": "high",
    "due_date": "2025-12-31"
  }'
```

### Using HTTPie (Better)

```bash
# Install HTTPie
pip install httpie

# Register
http POST localhost:8000/api/register \
  name="John Doe" \
  email="john@example.com" \
  password="password123" \
  password_confirmation="password123"

# Login and save token
http POST localhost:8000/api/login \
  email="john@example.com" \
  password="password123" \
  | jq -r '.token' > token.txt

# List tasks
http GET localhost:8000/api/tasks \
  "Authorization: Bearer $(cat token.txt)"
```

## Step 12: Add API Documentation

```bash
composer require knuckleswtf/scribe
php artisan vendor:publish --tag=scribe-config
php artisan scribe:generate
```

Visit: http://localhost:8000/docs

## Step 13: Deploy to Production

### Option 1: Laravel Forge (Easiest)

1. Sign up at forge.laravel.com ($12/month)
2. Connect your server (DigitalOcean, AWS, etc.)
3. Create site
4. Deploy from GitHub
5. Done!

### Option 2: Manual Deployment

```bash
# On server
git clone your-repo.git
cd your-repo
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
```

### Nginx Configuration

```nginx
server {
    listen 80;
    server_name example.com;
    root /var/www/taskmaster/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## What You've Built

Congratulations! You've built a complete task management application with:

✅ **Authentication** — User registration and login with Sanctum
✅ **CRUD Operations** — Full task management
✅ **Relationships** — Categories and tags with many-to-many
✅ **Authorization** — Policy-based access control
✅ **Filtering** — Search, status, priority filters
✅ **Scopes** — Eloquent query scopes
✅ **Testing** — Comprehensive test coverage
✅ **API** — RESTful API with proper responses
✅ **Documentation** — Auto-generated API docs
✅ **Deployment Ready** — Production configuration

## Rails Comparison

| Feature | Rails Equivalent | Laravel |
|---------|-----------------|---------|
| **Setup** | `rails new` | `laravel new` |
| **Migration** | `rails g migration` | `php artisan make:migration` |
| **Model** | `rails g model` | `php artisan make:model` |
| **Controller** | `rails g controller` | `php artisan make:controller` |
| **Auth** | Devise gem | Laravel Sanctum (built-in) |
| **Testing** | RSpec | Pest/PHPUnit |
| **Factory** | FactoryBot | Laravel Factories (built-in) |
| **Routes** | routes.rb | routes/api.php |
| **Policy** | Pundit gem | Laravel Policies (built-in) |
| **Scopes** | ActiveRecord scopes | Eloquent scopes |

**Key Insight:** Laravel includes most features Rails requires gems for!

## Next Steps & Enhancements

### Ideas to Extend This Project

1. **Add Team Functionality**
   - Share tasks with team members
   - Assign tasks to others
   - Team permissions

2. **Add File Attachments**
   - Upload files to tasks
   - Store on S3
   - Preview images

3. **Add Notifications**
   - Email reminders for due tasks
   - Slack/Discord integration
   - Push notifications

4. **Add Activity Log**
   - Track all changes
   - See who did what
   - Audit trail

5. **Add Search**
   - Full-text search
   - Laravel Scout + Algolia
   - Advanced filtering

6. **Add Frontend**
   - Vue.js SPA
   - React with Inertia.js
   - Livewire (no JavaScript needed)

7. **Add Real-time Updates**
   - Laravel Echo
   - WebSocket broadcasting
   - Live task updates

8. **Add Reporting**
   - Task completion charts
   - Productivity metrics
   - Export to PDF

## Key Takeaways

1. **Laravel Feels Like Rails** — The patterns are nearly identical
2. **More Built-in Features** — Less gems/packages needed
3. **Type Safety Helps** — Catches errors early
4. **Testing is Similar** — Pest especially feels like RSpec
5. **Deployment is Simpler** — No application server needed
6. **Great Developer Experience** — Artisan, Tinker, Telescope
7. **You Can Be Productive** — Your Rails knowledge translates!

## Final Thoughts

You've completed the "Rails Developers Love Laravel" series! You now know:

- ✅ How Rails concepts map to Laravel
- ✅ Modern PHP features
- ✅ Laravel's developer experience
- ✅ Eloquent ORM
- ✅ Building REST APIs
- ✅ Testing and deployment
- ✅ Laravel's ecosystem
- ✅ When to choose Laravel vs Rails
- ✅ How to build complete applications

**You're now a polyglot developer!** Your Rails knowledge combined with Laravel skills makes you more valuable and versatile.

## Resources

### Continue Learning

- **[Laravel Bootcamp](https://bootcamp.laravel.com)** — Free official tutorial
- **[Laracasts](https://laracasts.com)** — Premium video courses
- **[Laravel News](https://laravel-news.com)** — Stay updated
- **[Laravel Daily](https://www.youtube.com/@LaravelDaily)** — YouTube channel
- **[Laravel Documentation](https://laravel.com/docs)** — Excellent docs

### Join the Community

- **[Laravel Discord](https://discord.gg/laravel)** — Real-time help
- **[Laravel.io](https://laravel.io)** — Forum discussions
- **[Twitter #Laravel](https://twitter.com/search?q=%23laravel)** — Community chat
- **[Reddit r/laravel](https://reddit.com/r/laravel)** — Reddit community

### Build More Projects

Practice by building:
- Blog with comments
- E-commerce store
- Social network
- Project management tool
- API-first SaaS

---

## Thank You!

Thank you for completing this series! You've proven that experienced developers can learn new frameworks quickly when concepts are properly mapped.

Whether you choose Laravel, Rails, or both for your projects, you're now equipped to make informed decisions and build great applications.

Happy coding! 🚀

---

<ProgressTracker seriesId="rails-developers-love-laravel" :totalChapters="11" title="Your Progress" />

<style>
.project-box {
  background: #f0fdf4;
  border-left: 4px solid #10b981;
  padding: 1rem;
  margin: 1rem 0;
  border-radius: 4px;
}

.code-step {
  background: #eff6ff;
  border-left: 4px solid #3b82f6;
  padding: 1rem;
  margin: 1rem 0;
  border-radius: 4px;
}

.success-box {
  background: #fef3c7;
  border-left: 4px solid #f59e0b;
  padding: 1rem;
  margin: 1rem 0;
  border-radius: 4px;
}
</style>
