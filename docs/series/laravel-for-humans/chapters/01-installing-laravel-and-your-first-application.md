---
title: "01: Installing Laravel and Your First Application"
description: 'Install Laravel, explore the project structure, and create your first routes to understand the framework fundamentals.'
series: "laravel-for-humans"
chapter: 1
order: 1
difficulty: "Intermediate"
prerequisites:
  - "PHP 8.2+ installed"
  - "Composer installed"
  - "Basic PHP knowledge (variables, functions, classes)"
  - "Command line familiarity"
---

![Installing Laravel](/images/laravel-for-humans/chapter-01-installing-laravel-hero-full.webp)

# Chapter 01: Installing Laravel and Your First Application

## Overview

Welcome to your Laravel journey! In this chapter, you'll install Laravel, explore the project structure that powers millions of applications worldwide, and create your first routes. By the end, you'll have a working Laravel application and understand the foundation upon which you'll build your SaaS.

Laravel is more than just a framework—it's a complete ecosystem designed for developer happiness. Its elegant syntax, powerful features, and extensive tooling make building web applications a joy rather than a chore.

## Prerequisites

Before starting this chapter, you should have:

- **PHP 8.2+** installed and accessible via `php --version`
- **Composer** installed (PHP's dependency manager)
- **A code editor** (VS Code with Laravel extensions or PhpStorm recommended)
- **Basic PHP knowledge** from our PHP Basics series or equivalent
- **Terminal/command line access**
- **SQLite, MySQL, or PostgreSQL** (SQLite is easiest for development)

**Estimated Time**: ~30 minutes

## What You'll Build

By the end of this chapter, you will have:

- A fresh Laravel 11 installation
- Understanding of the Laravel project structure
- Your first working routes (web and API)
- A basic controller
- Knowledge of the request/response lifecycle
- Confidence to explore Laravel's features

## Objectives

- Install Laravel using Composer
- Understand the purpose of each directory in a Laravel project
- Create and test routes using closures and controllers
- Understand the request/response cycle
- Use Laravel's development server
- Navigate Laravel's excellent documentation

## Step 1: Installing Laravel (~5 min)

### Goal

Install Laravel and create your first project named "TaskFlow" (the SaaS we'll build throughout this series).

### Actions

1. **Verify PHP and Composer are installed**:

```bash
# Check PHP version (need 8.2+)
php --version

# Check Composer
composer --version
```

Expected output:
```
PHP 8.3.x (cli) ...
Composer version 2.x.x ...
```

2. **Create a new Laravel project**:

```bash
# Navigate to your development directory
cd ~/Code  # or wherever you keep projects

# Create Laravel project
composer create-project laravel/laravel taskflow

# Navigate into the project
cd taskflow
```

This will take 1-2 minutes as Composer downloads Laravel and all its dependencies.

3. **Start the development server**:

```bash
php artisan serve
```

You should see:
```
INFO  Server running on [http://127.0.0.1:8000].

Press Ctrl+C to stop the server
```

4. **Visit your application**:

Open your browser to `http://localhost:8000`

You should see Laravel's beautiful welcome page!

### Expected Result

You now have a fully functional Laravel application running locally. The welcome page confirms that:
- Laravel is installed correctly
- The development server is working
- Your PHP version is compatible
- All dependencies are loaded

### Why It Works

- `composer create-project` uses Laravel's application template
- Laravel's installer sets up the directory structure, dependencies, and configuration
- `php artisan serve` starts PHP's built-in development server on port 8000
- The welcome route is defined by default in `routes/web.php`

### Troubleshooting

**Error: "PHP version required 8.2.0+"**
- Update PHP to 8.2 or higher
- On macOS: Use Homebrew (`brew install php@8.3`)
- On Ubuntu/Debian: `sudo apt install php8.3`
- On Windows: Download from php.net

**Error: "Port 8000 already in use"**
- Use a different port: `php artisan serve --port=8001`
- Or kill the process using port 8000

**Composer is very slow**
- Enable parallel downloads: `composer global require hirak/prestissimo`
- Or use `composer install --prefer-dist`

**Blank page instead of welcome**
- Check file permissions: `chmod -R 775 storage bootstrap/cache`
- Ensure `.env` file exists (copy from `.env.example` if missing)

## Step 2: Exploring the Laravel Structure (~5 min)

### Goal

Understand what each directory in your Laravel project does and where you'll be working most often.

### The Laravel Directory Structure

When you open your `taskflow` project, you'll see this structure:

```
taskflow/
├── app/                # Application core (models, controllers, services)
│   ├── Http/
│   │   ├── Controllers/    # Your route handlers
│   │   └── Middleware/     # Request filtering
│   ├── Models/            # Eloquent models (database)
│   └── Providers/         # Service container configuration
├── bootstrap/         # Framework initialization
├── config/            # All configuration files
├── database/
│   ├── migrations/        # Database schema versions
│   └── seeders/          # Test data
├── public/            # Web root (index.php, assets)
├── resources/
│   ├── views/            # Blade templates
│   ├── js/               # JavaScript
│   └── css/              # Stylesheets
├── routes/
│   ├── web.php           # Web routes (sessions, CSRF)
│   ├── api.php           # API routes (stateless)
│   └── console.php       # Artisan commands
├── storage/           # Logs, cache, uploads
├── tests/             # Automated tests
├── vendor/            # Composer dependencies (don't edit)
├── .env               # Environment configuration
├── artisan            # CLI tool
├── composer.json      # PHP dependencies
└── package.json       # JavaScript dependencies
```

### Key Directories Explained

**`app/` - Your application code lives here**
- `Http/Controllers/` - Handle incoming requests
- `Models/` - Database models using Eloquent ORM
- `Providers/` - Bootstrap services

**`routes/` - Define URL endpoints**
- `web.php` - Routes for web browsers (with sessions)
- `api.php` - Routes for APIs (stateless, no CSRF)

**`resources/views/` - HTML templates**
- Uses Blade templating engine
- Mix PHP and HTML elegantly

**`database/migrations/` - Database version control**
- Track schema changes over time
- Rollback and replay changes

**`config/` - Application configuration**
- All settings in organized files
- Override with `.env` for environment-specific values

**`public/` - Web server document root**
- Only directory exposed to the web
- Entry point is `index.php`

### Expected Result

You understand where to find and create:
- Routes (endpoints)
- Controllers (logic)
- Views (templates)
- Models (database)
- Configuration

### Why This Structure Matters

Laravel's structure follows proven architectural patterns:
- **MVC (Model-View-Controller)** - Separation of concerns
- **Convention over configuration** - Predictable locations
- **Namespacing** - PSR-4 autoloading from `app/` namespace

This structure will feel natural after a few chapters and makes team collaboration seamless.

## Step 3: Understanding Routes (~5 min)

### Goal

Learn how Laravel maps URLs to code by creating your first custom routes.

### Actions

1. **Open `routes/web.php`**:

```php
<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
```

This is the default route showing the welcome page.

2. **Add your first custom route** at the bottom of `routes/web.php`:

```php
Route::get('/hello', function () {
    return 'Hello from TaskFlow!';
});
```

3. **Visit the route**: Navigate to `http://localhost:8000/hello`

You should see: **Hello from TaskFlow!**

4. **Add a route with parameters**:

```php
Route::get('/greet/{name}', function (string $name) {
    return "Welcome to TaskFlow, {$name}!";
});
```

5. **Test it**: Visit `http://localhost:8000/greet/Alice`

You should see: **Welcome to TaskFlow, Alice!**

6. **Return JSON (API-style)**:

```php
Route::get('/api/status', function () {
    return response()->json([
        'status' => 'online',
        'version' => '1.0.0',
        'message' => 'TaskFlow API is running'
    ]);
});
```

Visit: `http://localhost:8000/api/status`

You'll see formatted JSON:
```json
{
  "status": "online",
  "version": "1.0.0",
  "message": "TaskFlow API is running"
}
```

### Expected Result

You've created three different types of routes:
1. Simple text response
2. Route with dynamic parameters
3. JSON API response

All without writing any controller code yet!

### Why It Works

- `Route::get()` registers a GET request handler
- First argument is the URL pattern
- Second argument is a closure (anonymous function) or controller reference
- Laravel automatically:
  - Matches URLs
  - Extracts parameters
  - Handles request/response conversion
  - Sets appropriate headers (Content-Type: application/json for JSON)

### Route Methods

Laravel supports all HTTP verbs:

```php
Route::get('/resource', ...);      // Retrieve
Route::post('/resource', ...);     // Create
Route::put('/resource/{id}', ...); // Update (full)
Route::patch('/resource/{id}', ...); // Update (partial)
Route::delete('/resource/{id}', ...); // Delete
```

You can also match multiple verbs:

```php
Route::match(['get', 'post'], '/form', ...);
Route::any('/anything', ...); // All verbs
```

### Troubleshooting

**404 Not Found**
- Check the URL matches exactly (case-sensitive)
- Ensure the route is defined before visiting
- Check for typos in route definition

**Unexpected output**
- Clear route cache: `php artisan route:clear`
- Restart the dev server (Ctrl+C, then `php artisan serve`)

## Step 4: Creating Your First Controller (~5 min)

### Goal

Move route logic into a controller class for better organization and reusability.

### Actions

1. **Generate a controller using Artisan**:

```bash
php artisan make:controller WelcomeController
```

Output: `Controller created successfully.`

This creates: `app/Http/Controllers/WelcomeController.php`

2. **Open the controller and add methods**:

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    /**
     * Show the welcome message
     */
    public function index()
    {
        return view('welcome');
    }

    /**
     * Greet a user by name
     */
    public function greet(string $name)
    {
        return "Hello, {$name}! Welcome to TaskFlow.";
    }

    /**
     * Return API status
     */
    public function status()
    {
        return response()->json([
            'app' => 'TaskFlow',
            'status' => 'operational',
            'timestamp' => now()->toISOString(),
        ]);
    }
}
```

3. **Update `routes/web.php` to use the controller**:

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WelcomeController;

// Controller-based routes
Route::get('/', [WelcomeController::class, 'index']);
Route::get('/greet/{name}', [WelcomeController::class, 'greet']);
Route::get('/api/status', [WelcomeController::class, 'status']);
```

4. **Test your routes**: Visit the same URLs as before. They work identically but now use controller methods!

### Expected Result

Your routes now point to controller methods instead of closures. The behavior is identical, but the code is:
- More organized
- Easier to test
- Reusable across multiple routes
- Following Laravel conventions

### Why Use Controllers?

**Closures (anonymous functions) are great for:**
- Simple, one-line responses
- Quick prototyping
- Testing ideas

**Controllers are better for:**
- Complex logic
- Reusable code
- Organized applications
- Team collaboration
- Automated testing

As your application grows, controllers keep code maintainable.

### Controller Best Practices

1. **One controller per resource** (e.g., `TaskController`, `ProjectController`)
2. **Use resource controllers** for CRUD operations (we'll cover this in Chapter 10)
3. **Keep controllers thin** - delegate complex logic to services or actions
4. **Return views or responses** - never echo directly

### Troubleshooting

**Error: "Target class [WelcomeController] does not exist"**
- Import the controller: `use App\Http\Controllers\WelcomeController;`
- Check the namespace in the controller file
- Run `composer dump-autoload`

**Method not found**
- Ensure method is public (not private or protected)
- Check spelling matches route definition exactly
- Clear route cache: `php artisan route:clear`

## Step 5: Viewing All Routes (~3 min)

### Goal

Use Laravel's built-in tools to see all registered routes in your application.

### Actions

1. **List all routes**:

```bash
php artisan route:list
```

You'll see a table showing:
```
GET|HEAD  / ..................... WelcomeController@index
GET|HEAD  api/status ............. WelcomeController@status
GET|HEAD  greet/{name} ........... WelcomeController@greet
```

2. **Filter routes**:

```bash
# Show only GET routes
php artisan route:list --method=GET

# Search by path
php artisan route:list --path=api

# Search by name (we'll add names in Chapter 2)
php artisan route:list --name=status
```

3. **More details**:

```bash
# Show route middleware
php artisan route:list --columns=method,uri,name,middleware
```

### Expected Result

You can see every route in your application, including:
- HTTP method
- URI pattern
- Controller and method
- Middleware applied
- Route name (if named)

This is invaluable for debugging and understanding your application's structure.

### Why This Matters

As your application grows to hundreds of routes, `route:list` becomes essential for:
- Finding existing routes
- Debugging 404 errors
- Understanding middleware flow
- Documentation
- Team onboarding

## Step 6: Understanding the Request Lifecycle (~3 min)

### Goal

Understand what happens when a user visits your Laravel application.

### The Request Flow

```
1. User visits http://localhost:8000/greet/Alice
                     ↓
2. Web server (Apache/Nginx/PHP built-in) receives request
                     ↓
3. Routes to public/index.php (Laravel's entry point)
                     ↓
4. Loads Composer autoloader and Laravel bootstrap
                     ↓
5. Creates Application instance (service container)
                     ↓
6. Runs through HTTP kernel middleware
                     ↓
7. Router matches /greet/{name} pattern
                     ↓
8. Resolves WelcomeController from container
                     ↓
9. Calls greet('Alice') method
                     ↓
10. Controller returns response
                     ↓
11. Middleware processes response
                     ↓
12. Sends HTTP response to browser
```

### Key Concepts

**1. Entry Point**
- All requests go through `public/index.php`
- Never directly access other PHP files
- Web server configured to route everything to `index.php`

**2. Service Container**
- Manages class dependencies
- Auto-resolves constructor parameters
- Binds interfaces to implementations

**3. Middleware**
- Filters requests before they reach routes
- Examples: authentication, CSRF protection, logging
- Can also modify responses

**4. Router**
- Matches URL to route definition
- Extracts parameters
- Calls appropriate controller/closure

**5. Response**
- Can be string, view, JSON, redirect, file download, etc.
- Automatically converted to HTTP response

### Expected Result

You understand that Laravel is doing a lot of work behind the scenes:
- Routing
- Dependency injection
- Middleware processing
- Response formatting

This "magic" is actually well-organized, predictable code you can dive into when needed.

## Step 7: Environment Configuration (~4 min)

### Goal

Understand how Laravel uses environment variables for configuration.

### Actions

1. **Open `.env` file** in your project root:

```ini
APP_NAME=Laravel
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_TIMEZONE=UTC
APP_URL=http://localhost

DB_CONNECTION=sqlite
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=laravel
# DB_USERNAME=root
# DB_PASSWORD=
```

2. **Update for TaskFlow**:

```ini
APP_NAME=TaskFlow
APP_ENV=local
APP_KEY=base64:... # Don't change this!
APP_DEBUG=true
APP_TIMEZONE=UTC
APP_URL=http://localhost:8000
```

3. **Access config values in code**:

Add this route to `routes/web.php`:

```php
Route::get('/info', function () {
    return [
        'app' => config('app.name'),
        'environment' => config('app.env'),
        'debug' => config('app.debug'),
        'timezone' => config('app.timezone'),
    ];
});
```

Visit: `http://localhost:8000/info`

You'll see your configuration values as JSON.

### Expected Result

You understand that:
- `.env` stores environment-specific settings
- Config files in `config/` reference `.env` values
- Never commit `.env` to version control (it's in `.gitignore`)
- Use `.env.example` as a template for others

### Important Environment Variables

**APP_KEY** - Encryption key (generated during install)
- Never share this
- Regenerate if compromised: `php artisan key:generate`

**APP_DEBUG** - Show detailed errors
- `true` in development
- `false` in production (security risk!)

**APP_ENV** - Environment name
- `local` for development
- `production` for live site
- `testing` for automated tests

**DB_*** - Database credentials
- We'll configure this in Chapter 6

### Configuration Best Practices

1. **Never hardcode secrets** - Use `.env`
2. **Different values per environment** - Local vs production
3. **Use config() helper** - Don't read `.env` directly in code
4. **Cache config in production**: `php artisan config:cache`

## Exercises

Test your understanding with these hands-on challenges:

1. **Create a Personal Info Route**

Create a route `/about` that returns JSON with:
- Your name
- Your favorite programming language
- Years of coding experience
- Whether you're excited to learn Laravel

**Hint**: Use `response()->json([...])`

2. **Build a Calculator Controller**

- Create `CalculatorController`
- Add a method that handles `/calculate/{num1}/{num2}/{operation}`
- Support operations: add, subtract, multiply, divide
- Return the result as JSON
- Handle division by zero

**Example**: `/calculate/10/5/multiply` returns `{"result": 50}`

3. **Named Routes**

Laravel lets you name routes for easy reference:

```php
Route::get('/dashboard', function () {
    return 'Dashboard';
})->name('dashboard');

// Generate URL: route('dashboard') returns '/dashboard'
```

**Challenge**:
- Name all your existing routes
- Create a route `/routes` that returns all route URLs using the `route()` helper
- Try: `route('greet', ['name' => 'John'])` for routes with parameters

4. **Route Groups**

Create a group of routes under `/admin` prefix:

```php
Route::prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return 'Admin Dashboard';
    });

    Route::get('/users', function () {
        return 'User Management';
    });
});
```

**Challenge**: Create an `/admin` group with routes for dashboard, users, and settings.

5. **View Routes**

Instead of returning strings, return actual views:

```php
Route::get('/', function () {
    return view('welcome'); // Looks for resources/views/welcome.blade.php
});
```

**Challenge**:
- Create a new view in `resources/views/hello.blade.php`
- Make it display HTML with your name
- Create a route that returns this view

## Wrap-up

Congratulations! You've successfully installed Laravel and created your first application. Here's what you've accomplished:

- ✓ Installed Laravel 11 using Composer
- ✓ Understood the Laravel directory structure
- ✓ Created routes using closures and controllers
- ✓ Learned the request/response lifecycle
- ✓ Explored environment configuration
- ✓ Used Artisan commands to manage your application
- ✓ Built confidence to explore Laravel further

### What's Next

In the next chapter, **Routing and Controllers: The Heart of Laravel**, you'll dive deeper into:
- Route parameters and constraints
- Route groups and prefixes
- Middleware and route protection
- Resource controllers for CRUD operations
- Form handling and CSRF protection

### Quick Recap

**Key Commands:**

```bash
# Create new Laravel project
composer create-project laravel/laravel project-name

# Start development server
php artisan serve

# Generate controller
php artisan make:controller ControllerName

# List all routes
php artisan route:list

# Clear route cache
php artisan route:clear

# Generate application key
php artisan key:generate
```

**Basic Route Patterns:**

```php
// Simple route
Route::get('/path', function () {
    return 'response';
});

// Route with parameters
Route::get('/path/{id}', function ($id) {
    return "ID: {$id}";
});

// Controller route
Route::get('/path', [ControllerClass::class, 'method']);

// Named route
Route::get('/path', ...)->name('route.name');

// JSON response
return response()->json(['key' => 'value']);
```

::: info Code Examples
Complete, runnable examples from this chapter are available in:

- [`routes/web.php`](/code/laravel-for-humans/chapter-01/routes/web.php) - All route examples
- [`WelcomeController.php`](/code/laravel-for-humans/chapter-01/app/Http/Controllers/WelcomeController.php) - Controller examples
- [`solutions/`](/code/laravel-for-humans/chapter-01/solutions/) - Solutions to chapter exercises
:::

## Further Reading

To deepen your understanding of the topics covered in this chapter:

- [Laravel Installation](https://laravel.com/docs/11.x/installation) - Official installation guide
- [Directory Structure](https://laravel.com/docs/11.x/structure) - Detailed explanation of each folder
- [Routing](https://laravel.com/docs/11.x/routing) - Complete routing documentation
- [Controllers](https://laravel.com/docs/11.x/controllers) - Controller best practices
- [Configuration](https://laravel.com/docs/11.x/configuration) - Environment and config management
- [Artisan Console](https://laravel.com/docs/11.x/artisan) - All Artisan commands
- [Request Lifecycle](https://laravel.com/docs/11.x/lifecycle) - Deep dive into Laravel's request handling

## Knowledge Check

Test your understanding of Laravel basics:

<Quiz
title="Chapter 01 Quiz: Installing Laravel"
:questions="[
{
  question: 'What is the correct command to create a new Laravel project?',
  options: [
    { text: 'composer create-project laravel/laravel project-name', correct: true, explanation: 'This is the official way to create a new Laravel project via Composer.' },
    { text: 'php artisan new project-name', correct: false, explanation: 'Artisan is for managing existing Laravel projects, not creating new ones.' },
    { text: 'laravel install project-name', correct: false, explanation: 'While the Laravel installer exists, the Composer method is more common and reliable.' },
    { text: 'npm create laravel project-name', correct: false, explanation: 'npm is for JavaScript packages. Laravel uses Composer for PHP.' }
  ]
},
{
  question: 'Which directory contains your application controllers?',
  options: [
    { text: 'app/Http/Controllers/', correct: true, explanation: 'Controllers live in app/Http/Controllers/ following Laravel conventions.' },
    { text: 'app/Controllers/', correct: false, explanation: 'Controllers are in the Http subdirectory, not directly in app/.' },
    { text: 'resources/controllers/', correct: false, explanation: 'resources/ is for views and frontend assets, not controllers.' },
    { text: 'routes/controllers/', correct: false, explanation: 'routes/ contains route definitions, not controllers.' }
  ]
},
{
  question: 'What file should NEVER be committed to version control?',
  options: [
    { text: '.env', correct: true, explanation: '.env contains sensitive credentials and is environment-specific. It should never be committed.' },
    { text: '.env.example', correct: false, explanation: '.env.example is a template without secrets and should be committed.' },
    { text: 'composer.json', correct: false, explanation: 'composer.json defines dependencies and should be committed.' },
    { text: 'routes/web.php', correct: false, explanation: 'Route files are part of your application and should be committed.' }
  ]
},
{
  question: 'How do you generate a new controller using Artisan?',
  options: [
    { text: 'php artisan make:controller ControllerName', correct: true, explanation: 'The make:controller command generates a new controller class.' },
    { text: 'php artisan create:controller ControllerName', correct: false, explanation: 'Laravel uses make: prefix for generators, not create:.' },
    { text: 'php artisan generate controller ControllerName', correct: false, explanation: 'The syntax is make:controller, not generate controller.' },
    { text: 'composer make controller ControllerName', correct: false, explanation: 'Artisan (php artisan) is used for Laravel commands, not Composer.' }
  ]
},
{
  question: 'What is the purpose of public/index.php?',
  options: [
    { text: 'It is the entry point for all HTTP requests to your Laravel application', correct: true, explanation: 'index.php bootstraps Laravel and handles all incoming requests.' },
    { text: 'It displays the homepage of your application', correct: false, explanation: 'index.php is the entry point, but routes/controllers determine what displays.' },
    { text: 'It contains your application configuration', correct: false, explanation: 'Configuration is in config/ and .env, not index.php.' },
    { text: 'It lists all available routes', correct: false, explanation: 'Routes are defined in routes/, not index.php.' }
  ]
}
]"
/>
