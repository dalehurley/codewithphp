# Chapter 13: Laravel Foundations

Code examples for Laravel framework fundamentals.

## Prerequisites

- PHP 8.1+
- Composer
- Laravel Installer (optional)

## Setup

### Option 1: Create New Laravel Project

```bash
composer create-project laravel/laravel my-laravel-app
cd my-laravel-app
php artisan serve
```

### Option 2: Use These Examples

The examples in this folder demonstrate Laravel concepts without requiring a full Laravel installation.

## Examples

### 1. Laravel Project Setup (`01-setup-guide.md`)
Step-by-step guide to setting up a Laravel project.

### 2. Routing Examples (`02-routes.php`)
Demonstrates various Laravel routing patterns.

### 3. Controller Examples (`03-controllers/`)
RESTful controller implementation.

### 4. Dependency Injection (`04-dependency-injection.php`)
Service container and DI examples.

### 5. Middleware (`05-middleware.php`)
Request/response middleware examples.

### 6. Form Request Validation (`06-form-requests.php`)
Clean validation with Form Requests.

### 7. Service Provider (`07-service-provider.php`)
Custom service provider example.

### 8. Artisan Commands

```bash
# Create controller
php artisan make:controller UserController

# Create model
php artisan make:model User

# Create migration
php artisan make:migration create_users_table

# Create middleware
php artisan make:middleware LogRequests

# Create form request
php artisan make:request StoreUserRequest

# Run migrations
php artisan migrate

# Run tests
php artisan test
```

## Key Laravel Concepts

### Routing
```php
// routes/api.php
Route::get('/users', [UserController::class, 'index']);
Route::apiResource('users', UserController::class);
```

### Controllers
```php
class UserController extends Controller {
    public function index() {
        return User::all();
    }
}
```

### Dependency Injection
```php
class UserController extends Controller {
    public function __construct(private UserService $userService) {}

    public function index() {
        return $this->userService->getAll();
    }
}
```

### Validation
```php
public function store(StoreUserRequest $request) {
    $validated = $request->validated();
    // Data is already validated!
}
```

### Middleware
```php
Route::get('/admin', [AdminController::class, 'index'])
    ->middleware('auth');
```

## Quick Reference

### Directory Structure
```
app/
├── Http/
│   ├── Controllers/    # Controllers
│   ├── Middleware/     # Middleware
│   └── Requests/       # Form requests
├── Models/             # Eloquent models
└── Services/           # Business logic

routes/
├── web.php            # Web routes
└── api.php            # API routes

database/
├── migrations/        # Database migrations
└── seeders/           # Database seeders
```

### Common Artisan Commands

```bash
# Development server
php artisan serve

# List all routes
php artisan route:list

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Database
php artisan migrate
php artisan migrate:fresh
php artisan db:seed

# Testing
php artisan test
php artisan test --filter UserTest
```

## Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Laracasts](https://laracasts.com/) - Video tutorials
- [Laravel News](https://laravel-news.com/)
- [Laravel Daily](https://laraveldaily.com/)

## Learning Path

1. Start with routing and controllers
2. Learn dependency injection
3. Master Eloquent ORM (Chapter 14)
4. Build full-stack apps with Inertia (Chapter 15)
