---
title: "20: Laravel Fundamentals"
description: "Master Laravel's core features: Eloquent ORM, Blade templates, Artisan CLI, routing, and dependency injection"
sidebar:
  label: "20: Laravel Fundamentals"
  order: 20
  badge:
    text: Intermediate
    variant: caution
---

![Laravel Fundamentals](/images/php-for-java-developers/chapter-20-laravel-fundamentals-hero-full.webp)

# Chapter 20: Laravel Fundamentals

<Badge type="warning">Intermediate</Badge> <Badge type="info">90-120 min</Badge>

## Overview

Laravel is PHP's most popular framework, often compared to Spring Boot for rapid application development. If you're coming from Java and Spring Boot, you'll find Laravel's conventions, dependency injection, and ORM patterns familiar yet refreshingly expressive. Laravel emphasizes "convention over configuration" and provides elegant solutions to common web development challenges.

In this chapter, you'll explore Laravel's core features: the Eloquent ORM (comparable to JPA/Hibernate), Blade templating (similar to Thymeleaf), Artisan CLI (like Spring Boot CLI), routing, middleware, and the service container. By the end, you'll understand how Laravel's architecture compares to Spring Boot and be ready to build modern PHP applications.

**What You'll Learn:**

- Laravel project structure and conventions
- Routing system (comparable to Spring MVC routing)
- Eloquent ORM for database operations (like JPA/Hibernate)
- Blade templating engine (similar to Thymeleaf)
- Artisan CLI commands (like Spring Boot CLI)
- Middleware and request lifecycle (like Spring interceptors)
- Service container and dependency injection (like Spring IoC)
- Form validation and CSRF protection
- Database migrations (like Flyway/Liquibase)

## Prerequisites

::: info Time Estimate
⏱️ **90-120 minutes** to complete this chapter
:::

Before starting this chapter, you should have:

- Completed [Chapter 19: Framework Comparison](/series/php-for-java-developers/chapters/19-framework-comparison)
- Understanding of MVC architecture (from Java/Spring Boot experience)
- Familiarity with dependency injection concepts
- Basic knowledge of RESTful routing
- PHP 8.4+ and Composer installed
- Experience with ORM concepts (JPA/Hibernate helpful but not required)

**Verify your setup:**

```bash
# Check PHP version
php --version  # Should be 8.4+

# Check Composer
composer --version

# Verify Laravel can be installed
composer create-project laravel/laravel test-app --prefer-dist
cd test-app && rm -rf . && cd .. && rmdir test-app
```

## What You'll Build

By the end of this chapter, you will have created:

- A Laravel project with proper directory structure
- A User model using Eloquent ORM with relationships
- RESTful routes for a blog API
- Blade templates with layouts and components
- Middleware for authentication
- Form validation and CSRF protection
- Database migrations for schema management
- A working blog application demonstrating Laravel's core features

## Learning Objectives

By the end of this chapter, you will be able to:

1. **Create Laravel projects** and understand the directory structure
2. **Define routes** using Laravel's routing system (web and API)
3. **Work with Eloquent ORM** to perform database operations
4. **Create Blade templates** with layouts, components, and directives
5. **Use Artisan CLI** to generate code and manage the application
6. **Implement middleware** for cross-cutting concerns
7. **Utilize dependency injection** through Laravel's service container
8. **Handle form validation** and CSRF protection
9. **Manage database schema** using migrations
10. **Manipulate data** using Laravel Collections (similar to Java Streams)
11. **Process background jobs** using Laravel's queue system
12. **Implement event-driven architecture** with Events and Listeners
13. **Authorize actions** using Policies and Gates
14. **Compare Laravel patterns** to Spring Boot equivalents

---

## Section 1: Laravel Project Setup

### Goal

Set up a new Laravel project and understand its structure compared to Spring Boot.

### Actions

1. **Create a new Laravel project**:

```bash
# Create new Laravel project (like Spring Initializr)
composer create-project laravel/laravel blog-app

# Navigate to project
cd blog-app

# Start development server (like Spring Boot's embedded Tomcat)
php artisan serve
```

2. **Explore the directory structure**:

Laravel's structure is similar to Spring Boot's conventions:

::: code-group

```php [Laravel Structure]
blog-app/
├── app/                    # Application code (like src/main/java)
│   ├── Http/
│   │   ├── Controllers/    # Controllers (like @RestController)
│   │   └── Middleware/     # Middleware (like @Component interceptors)
│   ├── Models/             # Eloquent models (like JPA entities)
│   └── Providers/          # Service providers (like @Configuration)
├── config/                 # Configuration files (like application.properties)
├── database/
│   ├── migrations/         # Schema migrations (like Flyway scripts)
│   └── seeders/           # Database seeders
├── public/                 # Web root (like src/main/resources/static)
├── resources/
│   └── views/             # Blade templates (like templates/)
├── routes/                 # Route definitions (like @RequestMapping)
│   ├── web.php            # Web routes
│   └── api.php            # API routes
└── storage/               # Logs, cache, uploads
```

```java [Spring Boot Structure]
src/
├── main/
│   ├── java/
│   │   └── com/example/
│   │       ├── controller/    # @RestController classes
│   │       ├── service/       # @Service classes
│   │       ├── repository/    # JPA repositories
│   │       └── model/         # JPA entities
│   └── resources/
│       ├── templates/         # Thymeleaf templates
│       ├── static/           # Static files
│       └── application.properties
└── test/
```

:::

### Expected Result

You should see Laravel's welcome page at `http://localhost:8000` with the Laravel logo and links to documentation.

### Why It Works

Laravel uses Composer (PHP's package manager) to create projects, similar to how Spring Initializr generates Spring Boot projects. The `php artisan serve` command starts PHP's built-in development server, comparable to Spring Boot's embedded Tomcat server. The directory structure follows MVC conventions, making it familiar to Spring Boot developers.

### Troubleshooting

- **Error: "composer: command not found"** — Install Composer from [getcomposer.org](https://getcomposer.org/)
- **Error: "PHP version must be 8.2+"** — Update PHP to 8.4+ using your system's package manager
- **Port 8000 already in use** — Use `php artisan serve --port=8001` to use a different port

---

## Section 2: Routing System

### Goal

Understand Laravel's routing system and compare it to Spring MVC routing.

### Actions

1. **Define web routes** in `routes/web.php`:

```php
# filename: routes/web.php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;

// Simple route (like @GetMapping("/"))
Route::get('/', function () {
    return view('welcome');
});

// Route to controller (like @GetMapping("/posts"))
Route::get('/posts', [PostController::class, 'index']);

// Route with parameter (like @GetMapping("/posts/{id}"))
Route::get('/posts/{id}', [PostController::class, 'show']);

// RESTful resource routes (like @RestController)
Route::resource('posts', PostController::class);
// Creates: GET /posts, POST /posts, GET /posts/{id}, etc.
```

2. **Define API routes** in `routes/api.php`:

```php
# filename: routes/api.php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PostController;

// API routes are prefixed with /api automatically
Route::get('/posts', [PostController::class, 'index']);
Route::post('/posts', [PostController::class, 'store']);
Route::get('/posts/{id}', [PostController::class, 'show']);
Route::put('/posts/{id}', [PostController::class, 'update']);
Route::delete('/posts/{id}', [PostController::class, 'destroy']);
```

### Comparison with Spring Boot

::: code-group

```php [Laravel Routing]
<?php
// routes/web.php

// Simple route
Route::get('/hello', function () {
    return 'Hello World';
});

// Controller route
Route::get('/users', [UserController::class, 'index']);

// Route with parameter
Route::get('/users/{id}', [UserController::class, 'show']);

// Route with constraints
Route::get('/users/{id}', [UserController::class, 'show'])
    ->where('id', '[0-9]+');

// Named routes
Route::get('/users/{id}', [UserController::class, 'show'])
    ->name('users.show');
// Usage: route('users.show', ['id' => 1])
```

```java [Spring Boot Routing]
// @RestController
@GetMapping("/hello")
public String hello() {
    return "Hello World";
}

// Controller method
@GetMapping("/users")
public List<User> index() {
    return userService.findAll();
}

// Path variable
@GetMapping("/users/{id}")
public User show(@PathVariable Long id) {
    return userService.findById(id);
}

// Path variable with regex
@GetMapping("/users/{id:\\d+}")
public User show(@PathVariable Long id) {
    return userService.findById(id);
}
```

:::

### Expected Result

Routes are accessible at:
- `http://localhost:8000/` — Welcome page
- `http://localhost:8000/posts` — Posts index (once controller is created)

### Why It Works

Laravel's routing system maps HTTP requests to controller methods, similar to Spring MVC's `@RequestMapping` annotations. The `Route::resource()` method automatically creates RESTful routes, comparable to Spring Data REST. Routes are defined in separate files (`web.php` for web routes, `api.php` for API routes), keeping concerns separated.

### Troubleshooting

- **404 Not Found** — Ensure routes are defined in the correct file (`web.php` or `api.php`)
- **Route not found** — Run `php artisan route:list` to see all registered routes
- **Method not allowed** — Check that the HTTP method matches (GET, POST, etc.)

---

## Section 3: Eloquent ORM

### Goal

Master Laravel's Eloquent ORM, comparable to JPA/Hibernate in Spring Boot.

### Actions

1. **Create a Model** using Artisan:

```bash
# Generate model with migration (like JPA entity)
php artisan make:model Post -m
```

2. **Define the Model** in `app/Models/Post.php`:

```php
# filename: app/Models/Post.php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    // Mass assignment protection (like @Column)
    protected $fillable = [
        'title',
        'content',
        'user_id',
        'published_at',
    ];

    // Dates automatically cast to Carbon instances
    protected $dates = [
        'published_at',
    ];

    // Relationship: Post belongs to User (like @ManyToOne)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Accessor (like getter method)
    public function getExcerptAttribute(): string
    {
        return substr($this->content, 0, 100) . '...';
    }

    // Scope (like custom query method)
    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }
}
```

3. **Use Eloquent for queries**:

```php
# filename: app/Http/Controllers/PostController.php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    // Get all posts (like findAll())
    public function index()
    {
        $posts = Post::all();
        // Or with conditions
        $posts = Post::where('published_at', '!=', null)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('posts.index', ['posts' => $posts]);
    }

    // Get single post (like findById())
    public function show(string $id)
    {
        $post = Post::findOrFail($id);
        // Or with relationships
        $post = Post::with('user')->findOrFail($id);
        
        return view('posts.show', ['post' => $post]);
    }

    // Create post (like save())
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $post = Post::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('posts.show', $post);
    }

    // Update post
    public function update(Request $request, string $id)
    {
        $post = Post::findOrFail($id);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $post->update($validated);

        return redirect()->route('posts.show', $post);
    }

    // Delete post (like delete())
    public function destroy(string $id)
    {
        $post = Post::findOrFail($id);
        $post->delete();

        return redirect()->route('posts.index');
    }
}
```

### Comparison with JPA/Hibernate

::: code-group

```php [Laravel Eloquent]
<?php
// Model definition
class Post extends Model
{
    protected $fillable = ['title', 'content'];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

// Query operations
$posts = Post::all();                    // findAll()
$post = Post::find(1);                   // findById(1)
$post = Post::where('title', 'LIKE', '%PHP%')->get();
$post = Post::create(['title' => 'New']); // save()
$post->update(['title' => 'Updated']);    // save()
$post->delete();                          // delete()

// Relationships
$post->user;                              // Lazy load
$post->load('user');                      // Eager load
Post::with('user')->get();                // Eager load all
```

```java [Spring Boot JPA]
// Entity definition
@Entity
public class Post {
    @Id
    @GeneratedValue
    private Long id;
    
    @ManyToOne
    private User user;
}

// Repository operations
@Repository
public interface PostRepository extends JpaRepository<Post, Long> {
    List<Post> findByTitleContaining(String title);
}

// Usage
postRepository.findAll();                 // findAll()
postRepository.findById(1L);             // findById(1)
postRepository.findByTitleContaining("PHP");
postRepository.save(post);               // save()
postRepository.delete(post);              // delete()

// Relationships
post.getUser();                           // Lazy load
// Eager loading via @EntityGraph or JOIN FETCH
```

:::

### Expected Result

You can perform CRUD operations on posts using Eloquent's expressive syntax, similar to JPA repositories but with more fluent query building.

### Why It Works

Eloquent provides an ActiveRecord implementation where models represent database tables. Relationships are defined as methods, and queries use a fluent interface. This is similar to JPA's entity relationships but with a more expressive query syntax. Eloquent automatically handles table names, primary keys, and timestamps based on conventions.

### Troubleshooting

- **Error: "Class 'App\Models\Post' not found"** — Run `composer dump-autoload` to regenerate autoload files
- **Mass assignment error** — Add fields to `$fillable` array in the model
- **Relationship not loading** — Use `with()` for eager loading: `Post::with('user')->get()`

---

## Section 4: Blade Templating

### Goal

Create Blade templates with layouts and components, similar to Thymeleaf in Spring Boot.

### Actions

1. **Create a layout** in `resources/views/layouts/app.blade.php`:

```php
# filename: resources/views/layouts/app.blade.php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Laravel Blog')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('posts.index') }}">Blog</a>
            <div class="navbar-nav">
                <a class="nav-link" href="{{ route('posts.index') }}">Posts</a>
                <a class="nav-link" href="{{ route('posts.create') }}">New Post</a>
            </div>
        </div>
    </nav>

    <main class="container mt-4">
        @yield('content')
    </main>

    <footer class="mt-5 py-4 bg-light">
        <div class="container text-center">
            <p>&copy; {{ date('Y') }} Laravel Blog</p>
        </div>
    </footer>
</body>
</html>
```

2. **Create a view** that extends the layout:

```php
# filename: resources/views/posts/index.blade.php
@extends('layouts.app')

@section('title', 'All Posts')

@section('content')
    <h1>Blog Posts</h1>

    @if($posts->isEmpty())
        <p>No posts yet.</p>
    @else
        <div class="row">
            @foreach($posts as $post)
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">{{ $post->title }}</h5>
                            <p class="card-text">{{ $post->excerpt }}</p>
                            <a href="{{ route('posts.show', $post) }}" class="btn btn-primary">
                                Read More
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
```

3. **Create a component** for reusable UI elements:

```bash
# Generate component
php artisan make:component PostCard
```

```php
# filename: app/View/Components/PostCard.php
<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Models\Post;
use Illuminate\View\Component;

class PostCard extends Component
{
    public function __construct(
        public Post $post
    ) {}

    public function render()
    {
        return view('components.post-card');
    }
}
```

```php
# filename: resources/views/components/post-card.blade.php
<div class="card mb-3">
    <div class="card-body">
        <h5 class="card-title">{{ $post->title }}</h5>
        <p class="card-text">{{ $post->excerpt }}</p>
        <small class="text-muted">
            By {{ $post->user->name }} on {{ $post->created_at->format('M d, Y') }}
        </small>
        <a href="{{ route('posts.show', $post) }}" class="btn btn-sm btn-primary">
            Read More
        </a>
    </div>
</div>
```

4. **Use the component** in views:

```php
# filename: resources/views/posts/index.blade.php
@extends('layouts.app')

@section('content')
    <h1>Blog Posts</h1>
    
    @foreach($posts as $post)
        <x-post-card :post="$post" />
    @endforeach
@endsection
```

### Comparison with Thymeleaf

::: code-group

```php [Laravel Blade]
{{-- Layout inheritance --}}
@extends('layouts.app')

@section('title', 'Posts')

@section('content')
    <h1>{{ $title }}</h1>
    
    @if($posts->count() > 0)
        @foreach($posts as $post)
            <div>
                <h2>{{ $post->title }}</h2>
                <p>{{ $post->content }}</p>
            </div>
    @endforeach
    @else
        <p>No posts found.</p>
    @endif
    
    {{-- Component usage --}}
    <x-post-card :post="$post" />
@endsection

{{-- Directives --}}
@auth
    <p>Welcome, {{ auth()->user()->name }}</p>
@endauth

@csrf  {{-- CSRF token --}}
```

```html [Spring Boot Thymeleaf]
<!-- Layout inheritance -->
<!DOCTYPE html>
<html xmlns:th="http://www.thymeleaf.org"
      th:replace="~{layouts/app :: layout(~{::title}, ~{::content})}">
<head>
    <title th:fragment="title">Posts</title>
</head>
<body>
    <div th:fragment="content">
        <h1 th:text="${title}">Title</h1>
        
        <div th:if="${posts.size() > 0}">
            <div th:each="post : ${posts}">
                <h2 th:text="${post.title}">Title</h2>
                <p th:text="${post.content}">Content</p>
            </div>
        </div>
        <p th:unless="${posts.size() > 0}">No posts found.</p>
        
        <!-- Component usage -->
        <div th:replace="~{components/post-card :: card(${post})}"></div>
    </div>
</body>
</html>

<!-- Security -->
<input type="hidden" th:name="${_csrf.parameterName}" th:value="${_csrf.token}"/>
```

:::

### Expected Result

Views render with proper layouts, components, and Blade directives, providing a clean separation of presentation logic.

### Why It Works

Blade templates compile to optimized PHP code, similar to how Thymeleaf processes templates. The `@extends` directive provides layout inheritance, `@section` defines content blocks, and components enable reusable UI elements. Blade automatically escapes output to prevent XSS attacks, and directives like `@auth` and `@csrf` provide convenient security features.

### Troubleshooting

- **View not found** — Ensure the view file exists in `resources/views/` with correct path
- **Variable undefined** — Pass variables from controller: `return view('posts.index', ['posts' => $posts])`
- **Component not found** — Run `php artisan view:clear` to clear compiled views cache

---

## Section 5: Artisan CLI

### Goal

Master Laravel's Artisan CLI, comparable to Spring Boot CLI and Maven/Gradle commands.

### Actions

1. **Common Artisan commands**:

```bash
# Create models, controllers, migrations (like generating code)
php artisan make:model Post -m -c
# -m creates migration, -c creates controller

php artisan make:controller PostController --resource
# Creates resource controller with CRUD methods

php artisan make:migration create_posts_table
# Create migration file

php artisan make:middleware AuthMiddleware
# Create middleware class

# Database operations (like Flyway migrations)
php artisan migrate              # Run migrations
php artisan migrate:rollback     # Rollback last migration
php artisan migrate:refresh      # Rollback and re-run
php artisan migrate:fresh        # Drop all tables and re-run

# Database seeding (like data initialization)
php artisan db:seed
php artisan make:seeder PostSeeder

# Cache and optimization
php artisan cache:clear         # Clear cache
php artisan config:clear         # Clear config cache
php artisan route:clear          # Clear route cache
php artisan view:clear           # Clear view cache
php artisan optimize             # Cache routes, config, views

# List routes (like Spring Boot actuator)
php artisan route:list

# Interactive shell (like Spring Boot shell)
php artisan tinker
# Then: Post::all(), User::create(['name' => 'John']), etc.

# Generate key for encryption
php artisan key:generate

# Create user (if using Laravel Breeze/Jetstream)
php artisan make:auth
```

### Comparison with Spring Boot Tools

::: code-group

```bash [Laravel Artisan]
# Code generation
php artisan make:model Post -m -c
php artisan make:controller PostController
php artisan make:migration create_posts_table
php artisan make:middleware AuthMiddleware

# Database
php artisan migrate
php artisan migrate:rollback
php artisan db:seed

# Optimization
php artisan optimize
php artisan route:cache
php artisan config:cache

# Utilities
php artisan route:list
php artisan tinker
php artisan serve
```

```bash [Spring Boot / Maven]
# Code generation (Spring Initializr or IDE)
# Or manually create classes

# Database (Flyway)
mvn flyway:migrate
mvn flyway:repair

# Build and run
mvn clean install
mvn spring-boot:run

# Actuator endpoints
curl http://localhost:8080/actuator/health
curl http://localhost:8080/actuator/mappings

# Spring Boot CLI (if installed)
spring run app.groovy
```

:::

### Expected Result

You can generate code, manage databases, and optimize your application using Artisan commands, streamlining development workflow.

### Why It Works

Artisan provides a command-line interface for common Laravel tasks, similar to Maven/Gradle commands in Java. The `make:` commands generate boilerplate code following Laravel conventions, while migration commands manage database schema versioning. Tinker provides an interactive REPL for testing code, comparable to Spring Boot's shell or Java's jshell.

### Troubleshooting

- **Command not found** — Ensure you're in the Laravel project root directory
- **Migration fails** — Check database connection in `.env` file
- **Tinker errors** — Ensure database is set up and migrations are run

---

## Section 6: Middleware

### Goal

Implement middleware for cross-cutting concerns, similar to Spring interceptors.

### Actions

1. **Create middleware**:

```bash
php artisan make:middleware EnsureUserIsAdmin
```

2. **Implement middleware** in `app/Http/Middleware/EnsureUserIsAdmin.php`:

```php
# filename: app/Http/Middleware/EnsureUserIsAdmin.php
<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->is_admin) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
```

3. **Register middleware** in `app/Http/Kernel.php`:

```php
# filename: app/Http/Kernel.php
protected $middlewareAliases = [
    // ... existing middleware
    'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
];
```

4. **Apply middleware** to routes:

```php
# filename: routes/web.php
<?php

use Illuminate\Support\Facades\Route;

// Apply to single route
Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])
    ->middleware('admin');

// Apply to route group
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/users', [AdminController::class, 'users']);
    Route::get('/admin/settings', [AdminController::class, 'settings']);
});

// Apply globally (in Kernel.php)
protected $middleware = [
    \App\Http\Middleware\EnsureUserIsAdmin::class,
];
```

### Comparison with Spring Interceptors

::: code-group

```php [Laravel Middleware]
<?php
// Middleware class
class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->user()->is_admin) {
            abort(403);
        }
        
        return $next($request);
    }
}

// Apply to routes
Route::get('/admin', [AdminController::class, 'index'])
    ->middleware('admin');

// Apply to group
Route::middleware(['auth', 'admin'])->group(function () {
    // Routes here
});
```

```java [Spring Boot Interceptor]
// Interceptor class
@Component
public class AdminInterceptor implements HandlerInterceptor {
    @Override
    public boolean preHandle(HttpServletRequest request, 
                            HttpServletResponse response, 
                            Object handler) {
        User user = getCurrentUser();
        if (user == null || !user.isAdmin()) {
            response.setStatus(403);
            return false;
        }
        return true;
    }
}

// Register interceptor
@Configuration
public class WebConfig implements WebMvcConfigurer {
    @Autowired
    private AdminInterceptor adminInterceptor;
    
    @Override
    public void addInterceptors(InterceptorRegistry registry) {
        registry.addInterceptor(adminInterceptor)
                .addPathPatterns("/admin/**");
    }
}
```

:::

### Expected Result

Middleware intercepts requests before they reach controllers, allowing you to perform authentication, authorization, logging, and other cross-cutting concerns.

### Why It Works

Middleware provides a mechanism to filter HTTP requests entering your application, similar to Spring's interceptors or servlet filters. Middleware can modify requests, perform authentication checks, log activity, or abort requests. Laravel includes built-in middleware for CSRF protection, authentication, and rate limiting, and you can create custom middleware for application-specific needs.

### Troubleshooting

- **Middleware not executing** — Ensure middleware is registered in `Kernel.php` and applied to routes
- **403 Forbidden** — Check authentication logic in middleware
- **Infinite redirect** — Avoid redirecting in middleware that's applied to the redirect target

---

## Section 7: Service Container and Dependency Injection

### Goal

Understand Laravel's service container and dependency injection, comparable to Spring's IoC container.

### Actions

1. **Type-hinted dependency injection** in controllers:

```php
# filename: app/Http/Controllers/PostController.php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\PostService;
use Illuminate\Http\Request;

class PostController extends Controller
{
    // Constructor injection (like @Autowired)
    public function __construct(
        private PostService $postService
    ) {}

    public function index()
    {
        // Use injected service
        $posts = $this->postService->getAllPublishedPosts();
        
        return view('posts.index', ['posts' => $posts]);
    }
}
```

2. **Create a service class**:

```php
# filename: app/Services/PostService.php
<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Post;
use Illuminate\Support\Collection;

class PostService
{
    public function getAllPublishedPosts(): Collection
    {
        return Post::whereNotNull('published_at')
            ->orderBy('published_at', 'desc')
            ->get();
    }

    public function createPost(array $data): Post
    {
        return Post::create($data);
    }
}
```

3. **Bind services** in service providers:

```php
# filename: app/Providers/AppServiceProvider.php
<?php

namespace App\Providers;

use App\Services\PostService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind interface to implementation (like @Qualifier)
        $this->app->bind(
            \App\Contracts\PostRepositoryInterface::class,
            \App\Repositories\PostRepository::class
        );

        // Singleton binding (like @Scope("singleton"))
        $this->app->singleton(PostService::class, function ($app) {
            return new PostService();
        });
    }
}
```

4. **Use method injection**:

```php
# filename: app/Http/Controllers/PostController.php
<?php

// Method injection (like @Autowired on method parameter)
public function store(
    Request $request,
    PostService $postService  // Injected automatically
) {
    $post = $postService->createPost($request->validated());
    return redirect()->route('posts.show', $post);
}
```

### Comparison with Spring IoC

::: code-group

```php [Laravel Service Container]
<?php
// Constructor injection
class PostController
{
    public function __construct(
        private PostService $postService
    ) {}
}

// Method injection
public function store(Request $request, PostService $service)
{
    // Use service
}

// Service provider binding
$this->app->bind(PostRepositoryInterface::class, PostRepository::class);
$this->app->singleton(CacheService::class);

// Resolve from container
$service = app(PostService::class);
```

```java [Spring IoC Container]
// Constructor injection
@RestController
public class PostController {
    private final PostService postService;
    
    public PostController(PostService postService) {
        this.postService = postService;
    }
}

// Field injection (not recommended)
@Autowired
private PostService postService;

// Method injection
@PostMapping
public ResponseEntity<Post> create(@RequestBody Post post, 
                                  PostService service) {
    // Use service
}

// Configuration
@Configuration
public class AppConfig {
    @Bean
    @Scope("singleton")
    public PostService postService() {
        return new PostService();
    }
}
```

:::

### Expected Result

Dependencies are automatically resolved and injected into controllers and services, promoting loose coupling and testability.

### Why It Works

Laravel's service container automatically resolves type-hinted dependencies by reflection, similar to Spring's dependency injection. When Laravel encounters a type-hinted parameter in a constructor or method, it attempts to resolve an instance from the container. You can bind interfaces to implementations, define singletons, and configure service resolution in service providers, providing the same flexibility as Spring's `@Bean` and `@Qualifier` annotations.

### Troubleshooting

- **Class not found** — Ensure the class is properly namespaced and autoloaded
- **Binding not working** — Check that service provider is registered in `config/app.php`
- **Circular dependency** — Refactor to avoid circular references between services

---

## Section 8: Form Validation and CSRF Protection

### Goal

Implement form validation and CSRF protection, similar to Spring's validation and security features.

### Actions

1. **Validate form requests**:

```php
# filename: app/Http/Controllers/PostController.php
<?php

public function store(Request $request)
{
    // Validation rules (like @Valid and @NotNull)
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'content' => 'required|string|min:10',
        'email' => 'required|email|unique:users,email',
        'published_at' => 'nullable|date',
    ]);

    // If validation fails, Laravel automatically redirects back
    // with errors. If it passes, $validated contains clean data.

    $post = Post::create($validated);
    return redirect()->route('posts.show', $post);
}
```

2. **Create form request classes** for complex validation:

```bash
php artisan make:request StorePostRequest
```

```php
# filename: app/Http/Requests/StorePostRequest.php
<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization logic (like @PreAuthorize)
        return $this->user()->can('create', Post::class);
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'content' => 'required|string|min:10',
            'published_at' => 'nullable|date|after:now',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'The post title is required.',
            'content.min' => 'The content must be at least 10 characters.',
        ];
    }
}
```

3. **Use form request** in controller:

```php
# filename: app/Http/Controllers/PostController.php
<?php

use App\Http\Requests\StorePostRequest;

public function store(StorePostRequest $request)
{
    // $request is already validated and authorized
    $post = Post::create($request->validated());
    return redirect()->route('posts.show', $post);
}
```

4. **Display validation errors** in Blade templates:

```php
# filename: resources/views/posts/create.blade.php
<form method="POST" action="{{ route('posts.store') }}">
    @csrf  {{-- CSRF token (like Spring's CSRF protection) --}}

    <div class="mb-3">
        <label for="title" class="form-label">Title</label>
        <input type="text" 
               class="form-control @error('title') is-invalid @enderror" 
               id="title" 
               name="title" 
               value="{{ old('title') }}">
        @error('title')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <button type="submit" class="btn btn-primary">Create Post</button>
</form>
```

### Comparison with Spring Validation

::: code-group

```php [Laravel Validation]
<?php
// Inline validation
$validated = $request->validate([
    'title' => 'required|string|max:255',
    'email' => 'required|email|unique:users',
]);

// Form request class
class StorePostRequest extends FormRequest
{
    public function rules(): array
    {
        return ['title' => 'required|max:255'];
    }
    
    public function authorize(): bool
    {
        return $this->user()->can('create', Post::class);
    }
}

// In controller
public function store(StorePostRequest $request)
{
    // Already validated
}

// CSRF protection
@csrf  // In Blade template
```

```java [Spring Validation]
// Entity validation
@Entity
public class Post {
    @NotNull
    @Size(max = 255)
    private String title;
    
    @Email
    @Column(unique = true)
    private String email;
}

// Controller validation
@PostMapping
public ResponseEntity<Post> create(
    @Valid @RequestBody Post post,
    BindingResult result
) {
    if (result.hasErrors()) {
        // Handle errors
    }
}

// Method security
@PreAuthorize("hasAuthority('CREATE_POST')")
public Post create(Post post) {
    // ...
}

// CSRF protection (enabled by default in Spring Security)
```

:::

### Expected Result

Forms are validated with clear error messages, and CSRF tokens protect against cross-site request forgery attacks.

### Why It Works

Laravel's validation system provides a fluent interface for defining rules, similar to Bean Validation annotations in Java. Form request classes encapsulate validation and authorization logic, comparable to DTOs with validation annotations. The `@csrf` directive automatically includes CSRF tokens in forms, and Laravel's middleware validates these tokens on submission, similar to Spring Security's CSRF protection.

### Troubleshooting

- **Validation not working** — Ensure `@csrf` token is included in forms
- **Errors not displaying** — Check that `@error` directive matches field name
- **Custom validation rules** — Create rule classes: `php artisan make:rule CustomRule`

---

## Section 9: Database Migrations

### Goal

Manage database schema using migrations, similar to Flyway or Liquibase in Java.

### Actions

1. **Create a migration**:

```bash
php artisan make:migration create_posts_table
```

2. **Define the migration** in `database/migrations/YYYY_MM_DD_HHMMSS_create_posts_table.php`:

```php
# filename: database/migrations/2024_01_15_100000_create_posts_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();  // Auto-incrementing primary key
            $table->string('title');
            $table->text('content');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();  // created_at, updated_at
            
            // Indexes
            $table->index('published_at');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
```

3. **Run migrations**:

```bash
# Run all pending migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Rollback all migrations
php artisan migrate:reset

# Rollback and re-run
php artisan migrate:refresh

# Drop all tables and re-run (development only)
php artisan migrate:fresh
```

4. **Create a seeder** for initial data:

```bash
php artisan make:seeder PostSeeder
```

```php
# filename: database/seeders/PostSeeder.php
<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();

        Post::factory()->count(10)->create([
            'user_id' => $user->id,
        ]);
    }
}
```

### Comparison with Flyway/Liquibase

::: code-group

```php [Laravel Migrations]
<?php
// Migration file
class CreatePostsTable extends Migration
{
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->foreignId('user_id')->constrained();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('posts');
    }
}

// Commands
php artisan migrate
php artisan migrate:rollback
php artisan migrate:refresh
```

```sql [Flyway Migration]
-- V1__Create_posts_table.sql
CREATE TABLE posts (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    user_id BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Rollback
-- V1_1__Drop_posts_table.sql
DROP TABLE IF EXISTS posts;
```

```java [Liquibase]
<!-- changelog.xml -->
<changeSet id="1" author="developer">
    <createTable tableName="posts">
        <column name="id" type="BIGINT" autoIncrement="true">
            <constraints primaryKey="true"/>
        </column>
        <column name="title" type="VARCHAR(255)"/>
        <column name="content" type="TEXT"/>
    </createTable>
</changeSet>
```

:::

### Expected Result

Database schema is version-controlled and can be applied or rolled back consistently across environments.

### Why It Works

Migrations provide version control for database schema, similar to Flyway or Liquibase in Java projects. Each migration file represents a change to the database structure, and Laravel tracks which migrations have been run. The `up()` method applies changes, while `down()` provides rollback capability. This ensures database consistency across development, staging, and production environments.

### Troubleshooting

- **Migration fails** — Check database connection in `.env` file
- **Table already exists** — Use `php artisan migrate:fresh` (drops all tables) or manually drop the table
- **Foreign key errors** — Ensure referenced tables exist and are created in correct order

---

## Section 10: Collections

### Goal

Master Laravel's Collection class, a powerful data manipulation tool unique to Laravel.

### Actions

1. **Understanding Collections**:

Collections are Laravel's wrapper around arrays, providing a fluent interface for data manipulation:

```php
# filename: app/Http/Controllers/PostController.php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Support\Collection;

class PostController extends Controller
{
    public function index()
    {
        // Eloquent returns Collection instances
        $posts = Post::all(); // Returns Collection
        
        // Transform data
        $titles = $posts->pluck('title');
        $grouped = $posts->groupBy('user_id');
        $filtered = $posts->filter(fn($post) => $post->published_at !== null);
        
        // Chain operations
        $recent = $posts
            ->where('published_at', '!=', null)
            ->sortByDesc('published_at')
            ->take(10)
            ->map(fn($post) => [
                'title' => $post->title,
                'author' => $post->user->name,
            ]);
        
        return response()->json($recent);
    }
}
```

2. **Common Collection Methods**:

```php
<?php

// Create collection from array
$collection = collect([1, 2, 3, 4, 5]);

// Filtering
$even = $collection->filter(fn($n) => $n % 2 === 0);
$greaterThan = $collection->where('value', '>', 3);

// Mapping
$doubled = $collection->map(fn($n) => $n * 2);
$plucked = collect($users)->pluck('email');

// Reducing
$sum = $collection->sum();
$avg = $collection->avg();
$max = $collection->max();

// Grouping
$grouped = collect($posts)->groupBy('category');
$keyed = collect($users)->keyBy('id');

// Sorting
$sorted = $collection->sort()->values();
$sortedDesc = $collection->sortDesc();

// Chunking
$chunks = $collection->chunk(100); // Process in batches

// Combining
$merged = $collection->merge([6, 7, 8]);
$unique = $collection->unique();
```

### Comparison with Java Streams

::: code-group

```php [Laravel Collections]
<?php
// Create collection
$users = collect([
    ['name' => 'John', 'age' => 25],
    ['name' => 'Jane', 'age' => 30],
]);

// Filter and map
$adults = $users
    ->filter(fn($user) => $user['age'] >= 18)
    ->map(fn($user) => $user['name'])
    ->values();

// Group by
$byAge = $users->groupBy(fn($user) => 
    $user['age'] >= 30 ? 'senior' : 'junior'
);

// Reduce
$totalAge = $users->sum('age');
```

```java [Java Streams]
// Create stream
List<User> users = Arrays.asList(
    new User("John", 25),
    new User("Jane", 30)
);

// Filter and map
List<String> adults = users.stream()
    .filter(user -> user.getAge() >= 18)
    .map(User::getName)
    .collect(Collectors.toList());

// Group by
Map<String, List<User>> byAge = users.stream()
    .collect(Collectors.groupingBy(user -> 
        user.getAge() >= 30 ? "senior" : "junior"
    ));

// Reduce
int totalAge = users.stream()
    .mapToInt(User::getAge)
    .sum();
```

:::

### Expected Result

Collections provide a fluent, chainable interface for manipulating data, similar to Java Streams but with more convenience methods.

### Why It Works

Laravel Collections wrap arrays and provide a consistent API for data manipulation. They're lazy-evaluated where possible and provide methods for filtering, mapping, reducing, grouping, and more. Collections are returned by Eloquent queries and can be created from any array, making data manipulation consistent throughout Laravel applications.

### Troubleshooting

- **Collection method not found** — Ensure you're using `collect()` helper or `Illuminate\Support\Collection`
- **Nested data access** — Use `pluck()` for nested values: `$users->pluck('profile.email')`
- **Performance with large datasets** — Use `lazy()` for memory-efficient iteration: `Post::lazy()->chunk(1000)`

---

## Section 11: Queues and Jobs

### Goal

Implement background job processing using Laravel's queue system, similar to Spring's `@Async` or message queues.

### Actions

1. **Create a Job**:

```bash
php artisan make:job SendWelcomeEmail
```

2. **Define the Job** in `app/Jobs/SendWelcomeEmail.php`:

```php
# filename: app/Jobs/SendWelcomeEmail.php
<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendWelcomeEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public User $user
    ) {}

    public function handle(): void
    {
        // Send welcome email
        Mail::to($this->user->email)->send(new WelcomeMail($this->user));
        
        // Or perform any time-consuming task
        // Process large file, generate report, etc.
    }
}
```

3. **Dispatch Jobs** from controllers:

```php
# filename: app/Http/Controllers/UserController.php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Jobs\SendWelcomeEmail;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function store(Request $request)
    {
        $user = User::create($request->validated());
        
        // Dispatch job to queue (runs in background)
        SendWelcomeEmail::dispatch($user);
        
        // Or dispatch with delay
        SendWelcomeEmail::dispatch($user)->delay(now()->addMinutes(5));
        
        // Or dispatch to specific queue
        SendWelcomeEmail::dispatch($user)->onQueue('emails');
        
        return response()->json($user, 201);
    }
}
```

4. **Configure Queue Driver** in `.env`:

```sh
QUEUE_CONNECTION=database  # or redis, sqs, beanstalkd
```

5. **Run Queue Worker**:

```bash
# Process jobs
php artisan queue:work

# Process specific queue
php artisan queue:work --queue=emails

# Process with timeout
php artisan queue:work --timeout=60
```

### Comparison with Spring Async

::: code-group

```php [Laravel Queues]
<?php
// Dispatch job
SendWelcomeEmail::dispatch($user);

// With delay
SendWelcomeEmail::dispatch($user)
    ->delay(now()->addMinutes(5));

// On specific queue
SendWelcomeEmail::dispatch($user)
    ->onQueue('high-priority');
```

```java [Spring @Async]
@Service
public class EmailService {
    
    @Async
    public CompletableFuture<Void> sendWelcomeEmail(User user) {
        // Send email
        return CompletableFuture.completedFuture(null);
    }
}

// Usage
@Autowired
private EmailService emailService;

public void createUser(User user) {
    userRepository.save(user);
    emailService.sendWelcomeEmail(user); // Runs async
}
```

:::

### Expected Result

Time-consuming tasks run in the background, keeping HTTP responses fast and improving user experience.

### Why It Works

Laravel's queue system allows you to defer time-consuming tasks (emails, file processing, API calls) to background workers. Jobs are serialized and stored in a queue (database, Redis, SQS), then processed by worker processes. This prevents blocking HTTP requests and allows horizontal scaling of workers.

### Troubleshooting

- **Jobs not processing** — Ensure queue worker is running: `php artisan queue:work`
- **Failed jobs** — Check `failed_jobs` table, retry with `php artisan queue:retry all`
- **Memory issues** — Use `--max-jobs` and `--max-time` flags: `php artisan queue:work --max-jobs=1000 --max-time=3600`

---

## Section 12: Events and Listeners

### Goal

Implement event-driven architecture using Laravel's event system, similar to Spring's event publishing.

### Actions

1. **Create an Event**:

```bash
php artisan make:event UserRegistered
```

2. **Define the Event** in `app/Events/UserRegistered.php`:

```php
# filename: app/Events/UserRegistered.php
<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserRegistered
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public User $user
    ) {}
}
```

3. **Create a Listener**:

```bash
php artisan make:listener SendWelcomeEmail --event=UserRegistered
```

4. **Define the Listener** in `app/Listeners/SendWelcomeEmail.php`:

```php
# filename: app/Listeners/SendWelcomeEmail.php
<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\UserRegistered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendWelcomeEmail implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(UserRegistered $event): void
    {
        // Send welcome email to $event->user
        Mail::to($event->user->email)->send(new WelcomeMail($event->user));
    }
}
```

5. **Register Event-Listener Mapping** in `app/Providers/EventServiceProvider.php`:

```php
# filename: app/Providers/EventServiceProvider.php
<?php

namespace App\Providers;

use App\Events\UserRegistered;
use App\Listeners\SendWelcomeEmail;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        UserRegistered::class => [
            SendWelcomeEmail::class,
            // Add more listeners here
        ],
    ];
}
```

6. **Dispatch Events** from controllers:

```php
# filename: app/Http/Controllers/UserController.php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Events\UserRegistered;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function store(Request $request)
    {
        $user = User::create($request->validated());
        
        // Dispatch event (triggers all listeners)
        event(new UserRegistered($user));
        
        // Or using helper
        UserRegistered::dispatch($user);
        
        return response()->json($user, 201);
    }
}
```

### Comparison with Spring Events

::: code-group

```php [Laravel Events]
<?php
// Define event
class UserRegistered
{
    public function __construct(public User $user) {}
}

// Create listener
class SendWelcomeEmail
{
    public function handle(UserRegistered $event) {
        // Handle event
    }
}

// Dispatch event
event(new UserRegistered($user));
```

```java [Spring Events]
// Define event
public class UserRegisteredEvent extends ApplicationEvent {
    private final User user;
    public UserRegisteredEvent(User user) {
        super(user);
        this.user = user;
    }
}

// Create listener
@Component
public class SendWelcomeEmailListener {
    @EventListener
    public void handleUserRegistered(UserRegisteredEvent event) {
        // Handle event
    }
}

// Publish event
@Autowired
private ApplicationEventPublisher publisher;

public void createUser(User user) {
    userRepository.save(user);
    publisher.publishEvent(new UserRegisteredEvent(user));
}
```

:::

### Expected Result

Events decouple components, allowing multiple listeners to respond to the same event without tight coupling.

### Why It Works

Laravel's event system implements the observer pattern, allowing you to decouple application components. When an event is dispatched, all registered listeners are notified. Listeners can be synchronous or queued (implementing `ShouldQueue`), providing flexibility for handling events. This pattern is essential for building maintainable, testable applications.

### Troubleshooting

- **Listener not firing** — Ensure event is registered in `EventServiceProvider::$listen`
- **Queued listener not processing** — Run queue worker: `php artisan queue:work`
- **Multiple listeners** — Add all listeners to the array in `EventServiceProvider`

---

## Section 13: Authorization (Policies and Gates)

### Goal

Implement fine-grained authorization using Laravel's Policies and Gates, similar to Spring Security's method security.

### Actions

1. **Create a Policy**:

```bash
php artisan make:policy PostPolicy --model=Post
```

2. **Define the Policy** in `app/Policies/PostPolicy.php`:

```php
# filename: app/Policies/PostPolicy.php
<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    /**
     * Determine if user can view any posts
     */
    public function viewAny(User $user): bool
    {
        return true; // Anyone can view posts
    }

    /**
     * Determine if user can view the post
     */
    public function view(User $user, Post $post): bool
    {
        return true; // Anyone can view a post
    }

    /**
     * Determine if user can create posts
     */
    public function create(User $user): bool
    {
        return $user->is_verified; // Only verified users
    }

    /**
     * Determine if user can update the post
     */
    public function update(User $user, Post $post): bool
    {
        // User owns the post OR is admin
        return $user->id === $post->user_id || $user->is_admin;
    }

    /**
     * Determine if user can delete the post
     */
    public function delete(User $user, Post $post): bool
    {
        return $user->id === $post->user_id || $user->is_admin;
    }
}
```

3. **Use Policies in Controllers**:

```php
# filename: app/Http/Controllers/PostController.php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function create()
    {
        // Check authorization
        $this->authorize('create', Post::class);
        
        return view('posts.create');
    }

    public function update(Request $request, Post $post)
    {
        // Check authorization (passes model instance)
        $this->authorize('update', $post);
        
        $post->update($request->validated());
        return redirect()->route('posts.show', $post);
    }

    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);
        
        $post->delete();
        return redirect()->route('posts.index');
    }
}
```

4. **Use Policies in Blade Templates**:

```php
# filename: resources/views/posts/show.blade.php
@can('update', $post)
    <a href="{{ route('posts.edit', $post) }}">Edit</a>
@endcan

@can('delete', $post)
    <form method="POST" action="{{ route('posts.destroy', $post) }}">
        @csrf
        @method('DELETE')
        <button type="submit">Delete</button>
    </form>
@endcan
```

5. **Define Gates** (for simple authorization):

```php
# filename: app/Providers/AuthServiceProvider.php
<?php

namespace App\Providers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Define gate
        Gate::define('update-post', function (User $user, Post $post) {
            return $user->id === $post->user_id || $user->is_admin;
        });

        // Define gate with class
        Gate::define('manage-users', function (User $user) {
            return $user->is_admin;
        });
    }
}
```

6. **Use Gates**:

```php
<?php

// In controller
if (Gate::allows('update-post', $post)) {
    // User can update
}

// Or using authorize helper
Gate::authorize('update-post', $post);

// In Blade
@can('update-post', $post)
    <!-- Show edit button -->
@endcan
```

### Comparison with Spring Security

::: code-group

```php [Laravel Policies]
<?php
// Policy class
class PostPolicy
{
    public function update(User $user, Post $post): bool
    {
        return $user->id === $post->user_id || $user->is_admin;
    }
}

// In controller
$this->authorize('update', $post);

// In Blade
@can('update', $post)
    <!-- Content -->
@endcan
```

```java [Spring Security]
// Method security
@PreAuthorize("hasRole('ADMIN') or #post.userId == authentication.principal.id")
public void updatePost(Post post) {
    // Update post
}

// Or programmatic
@Autowired
private SecurityContext securityContext;

public void updatePost(Post post) {
    User user = getCurrentUser();
    if (user.isAdmin() || post.getUserId().equals(user.getId())) {
        // Update post
    }
}
```

:::

### Expected Result

Authorization logic is centralized in policies, making it easy to test and maintain, while controllers stay clean.

### Why It Works

Laravel's authorization system provides two approaches: Policies (for model-based authorization) and Gates (for simple authorization checks). Policies organize authorization logic around models, while Gates provide simple closures for authorization. Both can be used in controllers, Blade templates, and middleware, providing consistent authorization throughout the application.

### Troubleshooting

- **Policy not found** — Ensure policy is registered in `AuthServiceProvider` or follows naming convention (`PostPolicy` for `Post` model)
- **Authorization always fails** — Check that user is authenticated: `auth()->check()`
- **Gate not working** — Ensure gate is defined in `AuthServiceProvider::boot()`

---

## Exercises

### Exercise 1: Create a Blog Application

**Goal**: Build a complete blog application using Laravel's core features.

**Requirements**:

1. Create a `Post` model with migration
2. Create a `PostController` with CRUD operations
3. Define RESTful routes for posts
4. Create Blade templates for listing, showing, creating, and editing posts
5. Implement form validation
6. Add a relationship between `Post` and `User` models

**Validation**: Test your application:

```bash
# Create a post via form
# View all posts
# Edit a post
# Delete a post
```

Expected behavior: All CRUD operations work correctly with proper validation and error handling.

### Exercise 2: Implement Authentication Middleware

**Goal**: Protect routes using middleware.

**Requirements**:

1. Create middleware that checks if user is authenticated
2. Apply middleware to post creation and editing routes
3. Redirect unauthenticated users to login page
4. Display user information in the layout when authenticated

**Validation**: 

- Unauthenticated users cannot access protected routes
- Authenticated users can create and edit posts
- User information displays in navigation

### Exercise 3: Build an API Endpoint

**Goal**: Create a RESTful API for posts.

**Requirements**:

1. Create an API controller in `app/Http/Controllers/Api/PostController.php`
2. Define API routes in `routes/api.php`
3. Return JSON responses
4. Implement API resource classes for consistent JSON structure
5. Add API authentication using Laravel Sanctum or API tokens

**Validation**: Test API endpoints:

```bash
# Get all posts
curl http://localhost:8000/api/posts

# Get single post
curl http://localhost:8000/api/posts/1

# Create post (with authentication)
curl -X POST http://localhost:8000/api/posts \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"title":"New Post","content":"Content here"}'
```

---

## Wrap-up

Congratulations! You've mastered Laravel's core fundamentals. Here's what you've accomplished:

✓ **Set up Laravel projects** and understood the directory structure  
✓ **Defined routes** for web and API applications  
✓ **Worked with Eloquent ORM** for database operations  
✓ **Created Blade templates** with layouts and components  
✓ **Used Artisan CLI** to generate code and manage the application  
✓ **Implemented middleware** for cross-cutting concerns  
✓ **Utilized dependency injection** through the service container  
✓ **Handled form validation** and CSRF protection  
✓ **Managed database schema** using migrations  
✓ **Manipulated data** using Laravel Collections  
✓ **Processed background jobs** using queues and jobs  
✓ **Implemented event-driven architecture** with events and listeners  
✓ **Authorized actions** using policies and gates  
✓ **Compared Laravel patterns** to Spring Boot equivalents  

### Key Takeaways

- **Laravel's conventions** reduce configuration compared to Spring Boot
- **Eloquent ORM** provides a more expressive query syntax than JPA
- **Blade templates** offer similar functionality to Thymeleaf with PHP syntax
- **Artisan CLI** streamlines development workflow like Maven/Gradle
- **Middleware** provides the same functionality as Spring interceptors
- **Service container** offers dependency injection similar to Spring IoC

### Next Steps

In the next chapter, you'll explore **Symfony Components**, another major PHP framework that takes a more modular, enterprise-focused approach. You'll learn how Symfony's component-based architecture compares to Laravel's full-stack framework and when to choose each.


---

## Further Reading

- [Laravel Documentation](https://laravel.com/docs) — Official Laravel documentation
- [Laravel Eloquent ORM](https://laravel.com/docs/eloquent) — Deep dive into Eloquent
- [Laravel Routing](https://laravel.com/docs/routing) — Complete routing guide
- [Laravel Blade Templates](https://laravel.com/docs/blade) — Blade templating documentation
- [Laravel Service Container](https://laravel.com/docs/container) — Dependency injection guide
- [Spring Boot vs Laravel](https://www.baeldung.com/spring-boot-vs-laravel) — Framework comparison article
- [PSR Standards](https://www.php-fig.org/psr/) — PHP Framework Interop Group standards

---

<div style="display: flex; justify-content: space-between;">
  <div><strong>Previous:</strong> <a href="/series/php-for-java-developers/chapters/19-framework-comparison">← Chapter 19</a></div>
  <div><strong>Next:</strong> <a href="/series/php-for-java-developers/chapters/21-symfony-components">Chapter 21 →</a></div>
</div>
