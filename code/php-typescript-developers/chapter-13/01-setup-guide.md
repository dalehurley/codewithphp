# Laravel Setup Guide for TypeScript Developers

Complete guide to setting up Laravel from scratch.

## Installation

### 1. Install Laravel

```bash
# Via Composer
composer create-project laravel/laravel my-app

# Or via Laravel installer
composer global require laravel/installer
laravel new my-app
```

### 2. Configure Environment

```bash
cd my-app
cp .env.example .env
php artisan key:generate
```

### 3. Configure Database

Edit `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=my_app
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Run Migrations

```bash
php artisan migrate
```

### 5. Start Development Server

```bash
php artisan serve
# Visit: http://localhost:8000
```

## Project Structure

```
my-app/
├── app/                    # Application code
│   ├── Http/
│   │   ├── Controllers/    # Controllers
│   │   ├── Middleware/     # Middleware
│   │   └── Requests/       # Form requests
│   ├── Models/             # Eloquent models
│   └── Providers/          # Service providers
├── bootstrap/              # Framework bootstrap
├── config/                 # Configuration files
├── database/
│   ├── migrations/         # Database migrations
│   ├── seeders/            # Database seeders
│   └── factories/          # Model factories
├── public/                 # Web root
│   └── index.php           # Entry point
├── resources/
│   ├── views/              # Blade templates
│   ├── js/                 # JavaScript
│   └── css/                # Stylesheets
├── routes/
│   ├── web.php             # Web routes
│   ├── api.php             # API routes
│   └── console.php         # Console routes
├── storage/                # Logs, cache, uploads
├── tests/                  # Tests
│   ├── Feature/            # Feature tests
│   └── Unit/               # Unit tests
├── vendor/                 # Composer dependencies
├── .env                    # Environment config
├── artisan                 # CLI tool
└── composer.json           # Dependencies
```

## Quick Start: Build a Simple API

### 1. Create a Model and Migration

```bash
php artisan make:model Task -m
```

This creates:
- `app/Models/Task.php` (model)
- `database/migrations/xxxx_create_tasks_table.php` (migration)

### 2. Define Migration

Edit `database/migrations/xxxx_create_tasks_table.php`:

```php
public function up(): void
{
    Schema::create('tasks', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->text('description')->nullable();
        $table->boolean('completed')->default(false);
        $table->timestamps();
    });
}
```

Run migration:
```bash
php artisan migrate
```

### 3. Create Controller

```bash
php artisan make:controller Api/TaskController --api
```

Edit `app/Http/Controllers/Api/TaskController.php`:

```php
<?php

namespace App\Http\Controllers\Api;

use App\Models\Task;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index()
    {
        return Task::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $task = Task::create($validated);

        return response()->json($task, 201);
    }

    public function show(Task $task)
    {
        return $task;
    }

    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'completed' => 'boolean',
        ]);

        $task->update($validated);

        return $task;
    }

    public function destroy(Task $task)
    {
        $task->delete();

        return response()->noContent();
    }
}
```

### 4. Define Routes

Edit `routes/api.php`:

```php
<?php

use App\Http\Controllers\Api\TaskController;

Route::apiResource('tasks', TaskController::class);
```

### 5. Update Model

Edit `app/Models/Task.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = ['title', 'description', 'completed'];

    protected $casts = [
        'completed' => 'boolean',
    ];
}
```

### 6. Test the API

```bash
# List tasks
curl http://localhost:8000/api/tasks

# Create task
curl -X POST http://localhost:8000/api/tasks \
  -H "Content-Type: application/json" \
  -d '{"title":"My first task","description":"Learn Laravel"}'

# Get specific task
curl http://localhost:8000/api/tasks/1

# Update task
curl -X PUT http://localhost:8000/api/tasks/1 \
  -H "Content-Type: application/json" \
  -d '{"completed":true}'

# Delete task
curl -X DELETE http://localhost:8000/api/tasks/1
```

## Comparison: Express.js vs Laravel

### Express.js
```javascript
// app.js
const express = require('express');
const app = express();

app.use(express.json());

app.get('/api/tasks', async (req, res) => {
  const tasks = await Task.findAll();
  res.json(tasks);
});

app.listen(3000);
```

### Laravel
```php
// routes/api.php
Route::apiResource('tasks', TaskController::class);

// app/Http/Controllers/Api/TaskController.php
public function index() {
    return Task::all();
}
```

**Laravel is more structured** with clear separation of concerns.

## Next Steps

1. Learn Eloquent relationships (Chapter 14)
2. Add authentication (Laravel Sanctum/Passport)
3. Build full-stack with Inertia.js (Chapter 15)
4. Deploy to production (Laravel Forge/Vapor)

## Resources

- [Official Laravel Docs](https://laravel.com/docs)
- [Laracasts Free Series](https://laracasts.com/series/laravel-8-from-scratch)
- [Laravel Bootcamp](https://bootcamp.laravel.com/)
