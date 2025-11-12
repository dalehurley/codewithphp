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
  - "Node.js 20+ and npm installed"
  - "Basic PHP knowledge (variables, functions, classes)"
  - "Command line familiarity"
  - "Git (optional but recommended)"
---

![Installing Laravel](/images/laravel-for-humans/chapter-01-installing-laravel-hero-full.webp)

# Chapter 01: Installing Laravel and Your First Application

## Overview

Welcome to your Laravel journey! In this chapter, you'll install Laravel, explore the project structure that powers millions of applications worldwide, and create your first routes. By the end, you'll have a working Laravel application and understand the foundation upon which you'll build your SaaS.

Laravel is more than just a framework—it's a complete ecosystem designed for developer happiness. Its elegant syntax, powerful features, and extensive tooling make building web applications a joy rather than a chore.

### Why Laravel Over Raw PHP?

If you've completed our PHP Basics series, you built applications from scratch and understand how routing, databases, and templates work. Laravel automates and enhances all of this:

| **Task** | **Raw PHP** | **Laravel** |
|----------|-------------|-------------|
| **Routing** | Manual parsing of `$_SERVER['REQUEST_URI']` | `Route::get('/users', ...)` |
| **Database** | PDO with manual connection management | Eloquent ORM with migrations |
| **Templates** | PHP mixed with HTML (`<?php echo $var ?>`) | Blade templates (`{{ $var }}`) with layouts |
| **Security** | Manual SQL injection, XSS, CSRF protection | Built-in protection for all common vulnerabilities |
| **Authentication** | Write from scratch (100+ lines) | `php artisan make:auth` (one command) |
| **Validation** | Manual `if` checks for every field | `$request->validate(['email' => 'required\|email'])` |
| **Testing** | Setup PHPUnit manually | Integrated testing with factories and seeders |
| **Deployment** | Manual server setup, git hooks | Laravel Forge or Envoyer (one-click deploys) |

Laravel doesn't replace your PHP knowledge—it **amplifies** it. Everything you learned in PHP Basics is still happening under the hood, but Laravel handles the repetitive parts so you can focus on building features.

## Prerequisites

Before starting this chapter, you should have:

- **PHP 8.2+** installed and accessible via `php --version`
- **Composer** installed (PHP's dependency manager)
- **Node.js 20+** and **npm** (for Vite asset compilation)
- **A code editor** (VS Code with Laravel extensions or PhpStorm recommended)
- **Basic PHP knowledge** from our PHP Basics series or equivalent
- **Terminal/command line access**
- **SQLite, MySQL, or PostgreSQL** (SQLite is easiest for development)
- **Git** (optional but strongly recommended for version control)

**Estimated Time**: ~40 minutes (including optional sections)

::: tip Why Node.js for a PHP Framework?
Laravel 12 uses **Vite** for compiling JavaScript and CSS assets. While you won't need Node.js for basic backend development, you'll need it when working with frontend assets later in the series. Install it now to avoid issues later.

Check your Node.js version: `node --version` (should be 20.x or higher)
:::

## What You'll Build

By the end of this chapter, you will have:

- A fresh Laravel 12 installation (released February 2025)
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

**Missing Node.js errors**
- Install Node.js 20+ from [nodejs.org](https://nodejs.org/)
- Run `npm install` in your project directory
- Vite errors won't affect backend work but will be needed for frontend assets

## Step 1.5: Alternative Installation Methods (~Optional, 5 min)

### Goal

Learn about faster, more convenient ways to install and manage Laravel projects for local development.

### Option 1: Laravel Installer (Faster for Multiple Projects)

If you plan to create multiple Laravel projects, the global Laravel installer is more convenient:

```bash
# Install Laravel installer globally (one-time setup)
composer global require laravel/installer

# Ensure Composer's global bin is in your PATH
# Add to ~/.bashrc, ~/.zshrc, or equivalent:
# export PATH="$HOME/.composer/vendor/bin:$PATH"

# Create new projects quickly
laravel new taskflow

# With options
laravel new taskflow --git --branch=main --jet --stack=livewire
```

**Benefits**:
- Faster than `composer create-project`
- Remembers your preferences
- Can scaffold with starter kits in one command
- Interactive prompts for configuration

**When to use**: If you create Laravel projects frequently

### Option 2: Laravel Herd (Recommended for Mac/Windows)

**Laravel Herd** is a native Laravel development environment that provides PHP, Nginx, and database management with zero configuration.

```bash
# Download from https://herd.laravel.com

# After installation, create projects instantly:
herd create taskflow

# Or use with existing projects:
cd existing-project
herd link
```

**Benefits**:
- No manual PHP/Nginx setup required
- Automatic `.test` domain (taskflow.test)
- PHP version switching per project
- Database management built-in
- Much faster than Docker solutions

**Platforms**: macOS (free) and Windows (paid)

**When to use**: Primary local development environment

### Option 3: Laravel Sail (Docker-Based)

For consistent environments across teams or if you need specific services:

```bash
# Create project with Sail
curl -s "https://laravel.build/taskflow" | bash

# Start containers
cd taskflow
./vendor/bin/sail up

# Access at http://localhost
```

**Benefits**:
- Consistent environment across team members
- Includes Redis, Meilisearch, Selenium, etc.
- No local PHP/database installation needed
- Production-like environment

**When to use**: Team projects or when you need Docker services

### Which Method Should You Use?

| **Method** | **Best For** | **Speed** | **Complexity** |
|------------|--------------|-----------|----------------|
| **Composer** (Step 1) | Learning, one-off projects | Medium | Low |
| **Laravel Installer** | Frequent Laravel projects | Fast | Low |
| **Herd** | Daily local development | Fastest | Very Low |
| **Sail** | Team projects, Docker fans | Slow | Medium |

For this series, **we'll use the Composer method** shown in Step 1 as it works everywhere and teaches you the fundamentals. Feel free to use Herd or the installer for future projects!

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
- Compiled assets (CSS, JS) go here after build

**`tests/` - Automated testing**
- `Feature/` - Test full features (HTTP requests, database, etc.)
- `Unit/` - Test individual classes and methods
- Laravel includes PHPUnit/Pest for testing
- We'll write tests starting in Chapter 10

**`storage/` - Application runtime files**
- `app/` - Application-generated files (user uploads)
- `logs/` - Application logs (laravel.log)
- `framework/` - Framework cache, sessions, views cache

**`.env` - Environment variables**
- Database credentials, API keys, app settings
- **Never commit this file** (contains secrets)
- Use `.env.example` as a template for team members

**`.gitignore` - Ignored files**
- Specifies files Git should ignore
- Includes `vendor/`, `node_modules/`, `.env`, and other generated files
- Laravel's default .gitignore is already well-configured

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

## Step 3.5: Understanding Facades (~3 min)

### Goal

Understand what `Route::get()` actually is and how Laravel's Facades work.

### What Are Facades?

You've been using `Route::get()` without thinking about it, but what is `Route`? It's a **Facade** — Laravel's elegant way to access services from the service container with a simple, memorable syntax.

### How Facades Work

```php
use Illuminate\Support\Facades\Route;

Route::get('/hello', ...);  // Facade (clean, simple)
```

Behind the scenes, this is equivalent to:

```php
app('router')->get('/hello', ...);  // Service container (verbose)
```

Facades provide a "static" interface to classes in the service container, making your code:
- **More readable** - `Route::get()` vs `app('router')->get()`
- **Easier to test** - Facades can be mocked
- **Auto-completed** - IDEs understand facades
- **Documented** - Type hints work perfectly

### Common Facades You'll Use

| **Facade** | **Service** | **Common Use** |
|------------|-------------|----------------|
| `Route` | Router | Define routes |
| `DB` | Database | Query database |
| `Auth` | Authentication | Check logged-in users |
| `Cache` | Cache | Store temporary data |
| `Storage` | Filesystem | File operations |
| `Mail` | Mailer | Send emails |
| `Log` | Logger | Write to logs |

### Example: Different Ways to Access Services

```php
// Via Facade (recommended for most cases)
use Illuminate\Support\Facades\Cache;
Cache::put('key', 'value', 3600);

// Via Helper Function (convenient for quick access)
cache()->put('key', 'value', 3600);

// Via Dependency Injection (best for testability in classes)
public function __construct(protected Cache $cache) {}
$this->cache->put('key', 'value', 3600);

// Via Service Container (rarely needed)
app('cache')->put('key', 'value', 3600);
```

### Why This Matters

Facades make Laravel code elegant and expressive without sacrificing testability or performance. When you see `Route::get()`, `DB::table()`, or `Cache::remember()`, you now know you're using facades to access powerful services.

::: tip Not "Real" Static Methods
Despite looking like static method calls, Facades use PHP's `__callStatic()` magic method to proxy calls to actual service instances. This is why they're testable and mockable, unlike true static methods.
:::

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

## Step 4.5: Laravel Helper Functions (~3 min)

### Goal

Understand the powerful helper functions Laravel provides for common tasks.

### What Are Helper Functions?

Laravel includes dozens of global PHP functions to make common operations cleaner. You've already used some without realizing it:

```php
now()->toISOString()  // now() helper
config('app.name')    // config() helper
route('home')         // route() helper
```

### Commonly Used Helpers

#### **Time & Dates**
```php
now()                          // Current Carbon instance
today()                        // Today at midnight
now()->addDays(7)              // 7 days from now
now()->format('Y-m-d')         // 2025-02-24
```

#### **Configuration**
```php
config('app.name')             // Get config value
config('app.debug', false)     // With default value
env('DB_HOST', '127.0.0.1')    // Get env variable
```

#### **URLs & Routes**
```php
route('home')                  // Generate named route URL
route('user', ['id' => 1])     // /user/1
url('/about')                  // http://localhost:8000/about
asset('css/app.css')           // http://localhost:8000/css/app.css
```

#### **Arrays & Collections**
```php
collect([1, 2, 3])             // Create collection
collect($users)->pluck('name') // Extract field from all items
collect($numbers)->sum()       // Sum all values
```

#### **Strings**
```php
str('hello')->upper()          // 'HELLO'
str('hello_world')->camel()    // 'helloWorld'
str()->random(10)              // Random string
```

#### **Paths**
```php
app_path()                     // /path/to/taskflow/app
base_path()                    // /path/to/taskflow
storage_path()                 // /path/to/taskflow/storage
public_path()                  // /path/to/taskflow/public
```

#### **Debugging**
```php
dd($variable)                  // Dump and die
dump($variable)                // Dump without stopping
logger('Debug message')        // Write to logs
```

#### **Responses**
```php
response()->json(['data' => 'value'])
redirect('/dashboard')
back()                         // Go back to previous page
abort(404)                     // Throw 404 error
```

### Example: Refactoring with Helpers

**Before:**
```php
$timestamp = (new DateTime())->format('Y-m-d H:i:s');
$appName = $_ENV['APP_NAME'] ?? 'Laravel';
$users = [];
foreach ($data as $item) {
    $users[] = $item['name'];
}
```

**After:**
```php
$timestamp = now()->format('Y-m-d H:i:s');
$appName = config('app.name');
$users = collect($data)->pluck('name');
```

### Why This Matters

Helpers make your code:
- **More readable** - Clear intent
- **Less verbose** - Fewer lines
- **More testable** - Mockable and predictable
- **Consistent** - Same patterns throughout Laravel apps

::: tip Complete List
See all helpers in the [Laravel documentation](https://laravel.com/docs/12.x/helpers).
:::

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

## Step 5.5: Artisan Deep Dive (~5 min)

### Goal

Master Laravel's powerful command-line tool that will become your best friend as a Laravel developer.

### What is Artisan?

`php artisan` is Laravel's command-line interface (CLI). You've already used it (`php artisan serve`, `php artisan make:controller`), but it can do much more.

### Discovering Commands

```bash
# List all available commands
php artisan list

# Get help for a specific command
php artisan help make:controller

# Search for commands
php artisan list --raw | grep make
```

### Essential Artisan Commands

#### **Generators (make:)** - Create new files

```bash
php artisan make:controller TaskController      # Controller
php artisan make:model Task                     # Model
php artisan make:model Task -m                  # Model + Migration
php artisan make:model Task -mcr                # Model + Migration + Controller + Resource
php artisan make:migration create_tasks_table   # Database migration
php artisan make:seeder TaskSeeder              # Database seeder
php artisan make:factory TaskFactory            # Model factory
php artisan make:middleware EnsureAdmin         # HTTP middleware
php artisan make:request StoreTaskRequest       # Form request
php artisan make:policy TaskPolicy              # Authorization policy
php artisan make:command SendEmails             # Custom Artisan command
php artisan make:test TaskTest                  # Feature test
php artisan make:test TaskTest --unit           # Unit test
```

#### **Database Commands**

```bash
php artisan migrate                             # Run migrations
php artisan migrate:fresh                       # Drop all tables and re-migrate
php artisan migrate:fresh --seed                # Fresh + run seeders
php artisan migrate:rollback                    # Undo last migration
php artisan migrate:status                      # Show migration status
php artisan db:seed                             # Run database seeders
php artisan db:show                             # Show database info
```

#### **Caching & Optimization**

```bash
php artisan cache:clear                         # Clear application cache
php artisan config:clear                        # Clear config cache
php artisan config:cache                        # Cache config files
php artisan route:clear                         # Clear route cache
php artisan route:cache                         # Cache routes
php artisan view:clear                          # Clear compiled views
php artisan optimize                            # Cache config, routes, etc.
php artisan optimize:clear                      # Clear all caches
```

#### **Development Helpers**

```bash
php artisan serve                               # Start dev server
php artisan serve --port=8001                   # Custom port
php artisan tinker                              # Interactive REPL
php artisan route:list                          # List all routes
php artisan event:list                          # List all events
php artisan schedule:list                       # List scheduled tasks
```

#### **Queue & Jobs**

```bash
php artisan queue:work                          # Process queue jobs
php artisan queue:listen                        # Listen for queue jobs
php artisan queue:failed                        # List failed jobs
php artisan queue:retry                         # Retry failed job
```

### Interactive Tinker REPL

Tinker lets you interact with your application in real-time:

```bash
php artisan tinker

# Inside Tinker:
>>> $user = User::find(1);
>>> $user->name;
=> "John Doe"
>>> User::count();
=> 42
>>> config('app.name');
=> "TaskFlow"
```

Exit with `exit` or `Ctrl+C`.

### Creating Custom Commands

You can create your own Artisan commands:

```bash
php artisan make:command SendDailyReport
```

This creates `app/Console/Commands/SendDailyReport.php`:

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendDailyReport extends Command
{
    protected $signature = 'report:send';
    protected $description = 'Send daily report emails';

    public function handle()
    {
        $this->info('Sending daily report...');
        // Your logic here
        $this->info('Report sent successfully!');
    }
}
```

Run it with: `php artisan report:send`

### Why Artisan Matters

Artisan makes you dramatically more productive by:
- **Generating boilerplate code** in seconds
- **Managing database schema** with precision
- **Automating repetitive tasks** with custom commands
- **Debugging in real-time** with tinker
- **Optimizing for production** with caching commands

::: tip Pro Tip
Add aliases to your shell for common commands:
```bash
alias pa='php artisan'
alias pam='php artisan make:'
alias pams='php artisan migrate:fresh --seed'
```

Then use: `pa serve`, `pam controller TaskController`, etc.
:::

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

## Step 8: Version Control Setup (~Optional, 5 min)

### Goal

Initialize Git for your Laravel project and make your first commit.

### Why Use Version Control?

Version control lets you:
- **Track changes** - See what changed, when, and why
- **Undo mistakes** - Revert to previous working versions
- **Collaborate** - Work with others without conflicts
- **Deploy confidently** - Deploy known-good versions to production

Laravel projects come with a `.gitignore` file already configured, so you're ready to go!

### Actions

1. **Initialize Git** (if not already done):

```bash
cd taskflow

# Initialize repository
git init

# Check status
git status
```

You'll see many untracked files.

2. **Inspect the `.gitignore` file**:

```bash
cat .gitignore
```

Laravel's `.gitignore` already excludes:
- `/vendor/` - Composer dependencies (regenerated via `composer install`)
- `/node_modules/` - npm dependencies (regenerated via `npm install`)
- `.env` - Environment secrets (never commit!)
- `/storage/*.key` - Encryption keys
- `/public/hot` - Vite dev server files
- `/public/storage` - Symlinked storage
- `Homestead.yaml`, `.phpunit.result.cache`, etc.

::: tip Why Not Commit vendor/ and node_modules/?
These directories contain thousands of files that can be regenerated from `composer.json` and `package.json`. Committing them:
- Bloats your repository (100+ MB)
- Causes merge conflicts
- Makes diffs unreadable

Instead, teammates run `composer install` and `npm install` to get the same dependencies.
:::

3. **Make your first commit**:

```bash
# Add all files (respecting .gitignore)
git add .

# Verify what will be committed
git status

# Create initial commit
git commit -m "Initial Laravel 12 project setup"
```

4. **Verify your commit**:

```bash
# View commit history
git log

# See files in the commit
git show --name-only
```

### Setting Up Remote Repository (Optional)

To push to GitHub/GitLab/Bitbucket:

```bash
# Create repository on GitHub first, then:
git remote add origin https://github.com/your-username/taskflow.git
git branch -M main
git push -u origin main
```

### Good Commit Practices

**DO:**
- Make small, focused commits
- Write clear commit messages
- Commit working code
- Commit often (every feature/fix)

**Example commit messages:**
```
git commit -m "Add user authentication routes"
git commit -m "Fix task creation validation bug"
git commit -m "Refactor ProjectController for readability"
```

**DON'T:**
- Commit `.env` files
- Make huge commits with unrelated changes
- Write vague messages like "fix stuff" or "wip"
- Commit broken/untested code

### Expected Result

You have:
- A Git repository tracking your Laravel project
- Understanding of what should/shouldn't be committed
- Your first commit representing a clean Laravel installation

As you complete each chapter, commit your progress:
```bash
git add .
git commit -m "Complete Chapter 01: Installing Laravel"
```

## Common Pitfalls (~3 min)

### Goal

Learn the most common beginner mistakes and how to avoid them.

Before jumping into the exercises, let's cover mistakes that trip up new Laravel developers:

### 1. ⚠️ Forgetting to Import Classes

**Problem:**
```php
Route::get('/test', [HomeController::class, 'index']);
// Error: Class "HomeController" not found
```

**Solution:**
```php
use App\Http\Controllers\HomeController;  // Add this at the top!

Route::get('/test', [HomeController::class, 'index']);
```

**Why:** PHP namespaces require explicit imports. Laravel uses PSR-4 autoloading.

### 2. ⚠️ Returning Views That Don't Exist

**Problem:**
```php
return view('dashboard');  // Error: View [dashboard] not found
```

**Solution:**
```php
// Create the file first:
// resources/views/dashboard.blade.php

return view('dashboard');  // Now it works!
```

**Why:** Laravel looks for `{name}.blade.php` in `resources/views/`.

### 3. ⚠️ Using `.env` Directly Instead of config()

**Problem:**
```php
$name = $_ENV['APP_NAME'];  // Works locally, fails in production
```

**Solution:**
```php
$name = config('app.name');  // Correct way
```

**Why:** In production, config is cached. `$_ENV` won't have values, but `config()` will.

### 4. ⚠️ File Permission Errors

**Problem:**
```
The stream or file "/path/to/storage/logs/laravel.log" could not be opened
```

**Solution:**
```bash
chmod -R 775 storage bootstrap/cache
```

**Why:** Laravel needs write access to `storage/` and `bootstrap/cache/`.

### 5. ⚠️ Missing APP_KEY

**Problem:**
```
No application encryption key has been specified.
```

**Solution:**
```bash
php artisan key:generate
```

**Why:** Laravel requires an encryption key for security. This command generates one and saves it to `.env`.

### 6. ⚠️ Typos in Route Names/Methods

**Problem:**
```php
Route::get('/users', [UserController::class, 'indx']);  // Typo!
// Error: Method indx does not exist
```

**Solution:**
```php
Route::get('/users', [UserController::class, 'index']);  // Correct
```

**Why:** PHP is case-sensitive. Double-check method names.

### 7. ⚠️ Caching Issues During Development

**Problem:**
Changes to routes/config aren't showing up.

**Solution:**
```bash
php artisan optimize:clear  # Clear all caches
```

**Why:** Laravel caches routes and config for performance. Caching should only be used in production, but sometimes caches persist.

### 8. ⚠️ Editing Files in vendor/

**Problem:**
Making changes in `vendor/` directory.

**Solution:**
Never edit `vendor/`. Changes will be lost when you run `composer update`.

**Why:** `vendor/` is regenerated from `composer.json`. Extend or override classes instead.

### 9. ⚠️ Not Reading Error Messages

**Problem:**
Seeing an error and immediately searching Google.

**Solution:**
**Read the error message first!** Laravel errors are extremely helpful:
- They tell you exactly what's wrong
- They show the file and line number
- They often suggest how to fix it

**Example:**
```
Target class [TaskControler] does not exist.
Did you mean App\Http\Controllers\TaskController?
```

Laravel even suggests the fix!

### 10. ⚠️ Skipping the Documentation

**Problem:**
Trying to guess how Laravel features work.

**Solution:**
Laravel has the best documentation of any PHP framework: [laravel.com/docs](https://laravel.com/docs/12.x)

When stuck:
1. Read the error message
2. Check the docs
3. Search Laravel News/Stack Overflow
4. Ask in Laravel Discord

::: tip Success Mindset
Laravel is designed to be developer-friendly. If something feels overly complicated, you're probably doing it the hard way. There's usually a simpler Laravel way—check the docs!
:::

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

- ✓ Installed Laravel 12 using Composer
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

- [Laravel Installation](https://laravel.com/docs/12.x/installation) - Official installation guide
- [Directory Structure](https://laravel.com/docs/12.x/structure) - Detailed explanation of each folder
- [Routing](https://laravel.com/docs/12.x/routing) - Complete routing documentation
- [Controllers](https://laravel.com/docs/12.x/controllers) - Controller best practices
- [Configuration](https://laravel.com/docs/12.x/configuration) - Environment and config management
- [Artisan Console](https://laravel.com/docs/12.x/artisan) - All Artisan commands
- [Request Lifecycle](https://laravel.com/docs/12.x/lifecycle) - Deep dive into Laravel's request handling

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
