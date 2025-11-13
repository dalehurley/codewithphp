---
title: "13: Laravel Foundations - The PHP Framework"
description: "Discover Laravel—PHP's most elegant framework. Compare it to Nest.js/Express and learn routing, controllers, dependency injection, and the Laravel way."
series: "php-typescript-developers"
chapter: 13
order: 13
difficulty: "Advanced"
prerequisites:
  - "/series/php-typescript-developers/chapters/12-rest-apis"
---

# Laravel Foundations: The PHP Framework

## Overview

Laravel is to PHP what Nest.js is to TypeScript—a full-featured framework with elegant syntax, powerful features, and exceptional developer experience. If you've worked with Nest.js, Express, or Rails, Laravel will feel familiar yet distinctively refined.

## Learning Objectives

By the end of this chapter, you'll be able to:

- ✅ Set up a Laravel project
- ✅ Understand Laravel's MVC architecture
- ✅ Create routes and controllers
- ✅ Use dependency injection (Laravel's IoC container)
- ✅ Work with Eloquent ORM
- ✅ Handle requests and responses
- ✅ Use middleware and service providers
- ✅ Apply Laravel best practices

## Framework Comparison

| Laravel | Nest.js | Express | Purpose |
|---------|---------|---------|---------|
| **Full-stack** | ✅ | ❌ | Complete framework vs library |
| **ORM** | Eloquent | TypeORM | Database abstraction |
| **DI Container** | ✅ | ✅ | ❌ | Dependency injection |
| **CLI** | Artisan | Nest CLI | N/A | Code generation |
| **Template Engine** | Blade | EJS/Pug | EJS/Pug | Server-side rendering |
| **Auth System** | Built-in | Passport | Passport | Authentication |

**Laravel ≈ Nest.js** (both are opinionated, full-featured frameworks)

## Installation

### Nest.js Setup

```bash
npm install -g @nestjs/cli
nest new my-app
cd my-app
npm run start:dev
```

### Laravel Setup

```bash
# Install Laravel via Composer
composer create-project laravel/laravel my-app
cd my-app

# Start development server
php artisan serve
# Visit: http://localhost:8000
```

**Directory Structure:**
```
my-app/
├── app/            # Application code
│   ├── Http/
│   │   └── Controllers/
│   └── Models/
├── routes/         # Route definitions
│   ├── web.php     # Web routes
│   └── api.php     # API routes
├── resources/      # Views, assets
│   └── views/
├── database/       # Migrations, seeders
├── public/         # Web root
└── artisan         # CLI tool
```

## Routing

### Nest.js Routes

```typescript
// users.controller.ts
import { Controller, Get, Post, Body, Param } from '@nestjs/common';

@Controller('users')
export class UsersController {
  @Get()
  findAll() {
    return ['Alice', 'Bob'];
  }

  @Get(':id')
  findOne(@Param('id') id: string) {
    return { id, name: 'Alice' };
  }

  @Post()
  create(@Body() createUserDto: CreateUserDto) {
    return { id: 1, ...createUserDto };
  }
}
```

### Laravel Routes

```php
<?php
// routes/api.php
use App\Http\Controllers\UserController;

// GET /api/users
Route::get('/users', [UserController::class, 'index']);

// GET /api/users/{id}
Route::get('/users/{id}', [UserController::class, 'show']);

// POST /api/users
Route::post('/users', [UserController::class, 'store']);

// Or use resource routes (RESTful)
Route::apiResource('users', UserController::class);
```

```php
<?php
// app/Http/Controllers/UserController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller {
    public function index() {
        return response()->json(['Alice', 'Bob']);
    }

    public function show(string $id) {
        return response()->json(['id' => $id, 'name' => 'Alice']);
    }

    public function store(Request $request) {
        return response()->json([
            'id' => 1,
            ...$request->all()
        ], 201);
    }
}
```

## Dependency Injection

### Nest.js DI

```typescript
// users.service.ts
@Injectable()
export class UsersService {
  findAll() {
    return ['Alice', 'Bob'];
  }
}

// users.controller.ts
@Controller('users')
export class UsersController {
  constructor(private usersService: UsersService) {}

  @Get()
  findAll() {
    return this.usersService.findAll();
  }
}
```

### Laravel DI

```php
<?php
// app/Services/UserService.php
namespace App\Services;

class UserService {
    public function getAll(): array {
        return ['Alice', 'Bob'];
    }
}

// app/Http/Controllers/UserController.php
class UserController extends Controller {
    public function __construct(
        private UserService $userService
    ) {}

    public function index() {
        return response()->json($this->userService->getAll());
    }
}
```

**Laravel automatically resolves** dependencies via type hints!

## Request Validation

### Nest.js with class-validator

```typescript
import { IsString, IsEmail, MinLength } from 'class-validator';

export class CreateUserDto {
  @IsString()
  @MinLength(3)
  name: string;

  @IsEmail()
  email: string;
}

@Post()
create(@Body() createUserDto: CreateUserDto) {
  // Validated automatically
  return this.usersService.create(createUserDto);
}
```

### Laravel Form Requests

```php
<?php
// app/Http/Requests/StoreUserRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest {
    public function rules(): array {
        return [
            'name' => ['required', 'string', 'min:3'],
            'email' => ['required', 'email', 'unique:users'],
        ];
    }
}

// Controller
class UserController extends Controller {
    public function store(StoreUserRequest $request) {
        // $request is automatically validated!
        $validated = $request->validated();
        // Create user...
    }
}
```

## Middleware

### Nest.js Middleware

```typescript
@Injectable()
export class LoggerMiddleware implements NestMiddleware {
  use(req: Request, res: Response, next: NextFunction) {
    console.log(`${req.method} ${req.url}`);
    next();
  }
}
```

### Laravel Middleware

```php
<?php
// app/Http/Middleware/LogRequests.php
namespace App\Http\Middleware;

use Closure;

class LogRequests {
    public function handle($request, Closure $next) {
        \Log::info($request->method() . ' ' . $request->path());
        return $next($request);
    }
}

// Apply to route
Route::get('/users', [UserController::class, 'index'])
    ->middleware(LogRequests::class);

// Or globally in app/Http/Kernel.php
```

## Eloquent ORM

Laravel's Eloquent ORM is more elegant than TypeORM:

### TypeORM

```typescript
@Entity()
export class User {
  @PrimaryGeneratedColumn()
  id: number;

  @Column()
  name: string;

  @Column()
  email: string;

  @OneToMany(() => Post, post => post.user)
  posts: Post[];
}

// Usage
const user = await userRepository.findOne({ where: { id: 1 } });
const users = await userRepository.find();
```

### Laravel Eloquent

```php
<?php
// app/Models/User.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model {
    protected $fillable = ['name', 'email'];

    public function posts() {
        return $this->hasMany(Post::class);
    }
}

// Usage (so much cleaner!)
$user = User::find(1);
$users = User::all();
$user->posts; // Automatic eager loading
```

**More details in Chapter 14!**

## Artisan CLI

Laravel's Artisan is like Nest CLI:

### Nest CLI

```bash
nest generate controller users
nest generate service users
nest generate module users
```

### Artisan

```bash
php artisan make:controller UserController
php artisan make:model User
php artisan make:migration create_users_table
php artisan make:request StoreUserRequest
php artisan make:middleware LogRequests
```

**List all commands:**
```bash
php artisan list
```

## Environment Configuration

### Nest.js (.env)

```env
DATABASE_URL=postgresql://localhost/mydb
JWT_SECRET=secret
```

```typescript
// app.module.ts
ConfigModule.forRoot({
  envFilePath: '.env',
});

// Usage
constructor(private configService: ConfigService) {}

const dbUrl = this.configService.get('DATABASE_URL');
```

### Laravel (.env)

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=mydb
JWT_SECRET=secret
```

```php
<?php
// Usage (anywhere)
$dbHost = env('DB_HOST');
$secret = config('app.jwt_secret');
```

## Complete CRUD Example

### Create Controller

```bash
php artisan make:controller Api/UserController --api
```

```php
<?php
namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserController extends Controller {
    // GET /api/users
    public function index() {
        return User::all();
    }

    // GET /api/users/{id}
    public function show(User $user) {
        return $user; // Route model binding!
    }

    // POST /api/users
    public function store(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
        ]);

        $user = User::create($validated);
        return response()->json($user, 201);
    }

    // PUT /api/users/{id}
    public function update(Request $request, User $user) {
        $validated = $request->validate([
            'name' => 'sometimes|string',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
        ]);

        $user->update($validated);
        return $user;
    }

    // DELETE /api/users/{id}
    public function destroy(User $user) {
        $user->delete();
        return response()->noContent();
    }
}
```

### Routes

```php
<?php
// routes/api.php
Route::apiResource('users', UserController::class);

// Equivalent to:
// GET    /api/users       -> index()
// POST   /api/users       -> store()
// GET    /api/users/{id}  -> show()
// PUT    /api/users/{id}  -> update()
// DELETE /api/users/{id}  -> destroy()
```

## Service Providers

Laravel's service providers are like Nest.js modules:

```php
<?php
// app/Providers/AppServiceProvider.php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider {
    public function register(): void {
        // Bind interfaces to implementations
        $this->app->bind(
            \App\Contracts\UserRepositoryInterface::class,
            \App\Repositories\UserRepository::class
        );
    }

    public function boot(): void {
        // Run code on application boot
    }
}
```

## Testing

Laravel includes PHPUnit with helpful assertions:

```php
<?php
// tests/Feature/UserApiTest.php
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

class UserApiTest extends TestCase {
    public function test_can_list_users(): void {
        User::factory()->count(3)->create();

        $response = $this->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_can_create_user(): void {
        $data = [
            'name' => 'Alice',
            'email' => 'alice@example.com',
        ];

        $response = $this->postJson('/api/users', $data);

        $response->assertStatus(201)
            ->assertJsonPath('name', 'Alice');

        $this->assertDatabaseHas('users', $data);
    }
}
```

**Run tests:**
```bash
php artisan test
```

## Key Takeaways

1. **Laravel ≈ Nest.js** - Both are full-featured, opinionated frameworks with rich ecosystems
2. **Routing** is clean and expressive with multiple definition styles
3. **Dependency injection** works automatically via type hints (no manual binding needed)
4. **Eloquent ORM** is incredibly elegant - Active Record pattern beats TypeORM's Data Mapper
5. **Artisan CLI** generates boilerplate (controllers, models, migrations, tests, etc.)
6. **Middleware** pipeline like Express/Nest for request/response modification
7. **Testing** is built-in and powerful with PHPUnit integration
8. **Service container** resolves dependencies automatically - just type-hint in constructors
9. **Form request validation** provides clean, reusable validation logic
10. **Route model binding** automatically injects models - no manual `findOrFail()`
11. **API resources** provide consistent JSON transformation layer
12. **Laravel ecosystem** rivals Node.js: Forge (deployment), Vapor (serverless), Nova (admin panel)

## Laravel vs Nest.js

| Feature | Laravel | Nest.js |
|---------|---------|---------|
| **Language** | PHP | TypeScript |
| **Philosophy** | Convention over configuration | TypeScript + Angular patterns |
| **Learning Curve** | Gentle | Moderate |
| **ORM** | Eloquent (Active Record) | TypeORM (Data Mapper) |
| **Templating** | Blade | Various (EJS, Handlebars) |
| **Real-time** | Laravel Echo + Pusher | Socket.io |
| **Queue System** | Built-in | Bull (separate) |
| **Admin Panel** | Nova (commercial) | Various |

## Next Steps

Now that you know Laravel basics, let's dive deeper into Eloquent ORM and databases.

**Next Chapter:** [14: Database & ORMs: TypeORM meets Eloquent](/series/php-typescript-developers/chapters/14-database-and-orms)

## Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Laracasts](https://laracasts.com/) - Video tutorials
- [Laravel News](https://laravel-news.com/)
- [Laravel Daily](https://laraveldaily.com/)

---

**Questions or feedback?** Open an issue on [GitHub](https://github.com/dalehurley/codewithphp/issues)
