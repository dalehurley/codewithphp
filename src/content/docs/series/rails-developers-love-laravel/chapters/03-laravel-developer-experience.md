---
title: "03: Laravel's Developer Experience: Productivity & Tools"
description: "Explore Laravel's developer tools from a Rails perspective - Artisan CLI, Tinker, code generation, debugging, and productivity features you'll love."
sidebar:
  label: "03: Laravel's Developer Experience: Productivity & Tools"
  order: 3
  badge:
    text: Intermediate
    variant: caution
---


![Laravel's Developer Experience](/images/rails-developers-love-laravel/chapter-03-laravel-developer-experience-hero-full.webp)

# 03: Laravel's Developer Experience <span class="difficulty-badge difficulty-intermediate">Intermediate</span>

## Overview

Laravel's developer experience tools are where Laravel truly shines. If you've worked with Rails' command-line tools like `rails generate`, `rails console`, or Rake tasks, you'll find Laravel's Artisan CLI familiar yet more powerful in some areas. Laravel follows Rails' "convention over configuration" philosophy, meaning you spend less time configuring and more time building.

This chapter explores Laravel's productivity tools from a Rails developer's perspective, showing you how to be productive immediately.

## Prerequisites

Before starting this chapter, you should have:

- Completion of [Chapter 02: Modern PHP: What's Changed](/series/rails-developers-love-laravel/chapters/02-modern-php-whats-changed) or equivalent understanding of modern PHP features
- PHP 8.4+ installed and confirmed working with `php --version`
- Composer installed globally (for Laravel project creation)
- Basic familiarity with command-line tools
- **Estimated Time**: ~60-90 minutes

**Verify your setup:**

```bash
# Check PHP version
php --version

# Check Composer installation
composer --version

# Verify Laravel installer (optional but recommended)
composer global require laravel/installer
laravel --version
```

**Note:** This chapter is reference-heavy and focuses on command-line tools. You don't need a Laravel project running to follow along, but having one will help you practice the commands.

## What You'll Build

By the end of this chapter, you will have:

- A comprehensive command reference comparing Rails and Laravel tools
- Understanding of Artisan CLI and how it compares to Rails commands
- Knowledge of code generation patterns in Laravel
- Familiarity with Tinker (Laravel's interactive console)
- Understanding of Laravel's built-in queue and scheduling systems
- Setup instructions for debugging tools (Telescope, Debugbar, Ray)
- Custom Artisan command examples
- IDE configuration recommendations for Laravel development

## 📦 Code Samples

Complete Artisan command examples are available on GitHub:

- **[Custom Artisan Commands](https://github.com/dalehurley/codewithphp/blob/main/code/rails-developers-love-laravel/chapter-03/CustomCommand.php)** — SendDigest command, CreateUser interactive command, SetupEnvironment with choices

View all code samples:

```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/rails-developers-love-laravel/chapter-03
```

## Objectives

- **Understand** Artisan CLI and how it compares to Rails commands
- **Master** code generation patterns for models, controllers, and migrations
- **Use** Tinker (Laravel's REPL) effectively for interactive development
- **Configure** development servers and local environments (Valet, Sail)
- **Manage** databases with migration and seeding commands
- **Implement** queue workers and background job processing
- **Set up** task scheduling without manual cron configuration
- **Create** custom Artisan commands with rich interactions
- **Configure** IDE support and debugging tools (Telescope, Debugbar, Ray)

## Quick Command Reference

| Task | Rails | Laravel |
|------|-------|---------|
| **Create project** | `rails new app` | `laravel new app` |
| **Dev server** | `rails server` | `artisan serve` |
| **Console/REPL** | `rails console` | `artisan tinker` |
| **Generate model** | `rails g model User` | `artisan make:model User` |
| **Generate controller** | `rails g controller Users` | `artisan make:controller UserController` |
| **Generate migration** | `rails g migration AddX` | `artisan make:migration add_x` |
| **Run migrations** | `rails db:migrate` | `artisan migrate` |
| **Rollback** | `rails db:rollback` | `artisan migrate:rollback` |
| **Seed database** | `rails db:seed` | `artisan db:seed` |
| **List routes** | `rails routes` | `artisan route:list` |
| **Run tests** | `rails test` or `rspec` | `artisan test` |
| **Clear cache** | `rails tmp:clear` | `artisan cache:clear` |
| **Storage link** | N/A | `artisan storage:link` |
| **Generate view** | N/A | `artisan make:view posts.index` |
| **Generate component** | N/A | `artisan make:component Alert` |
| **Queue table** | N/A | `artisan queue:table` |

## 1. Artisan CLI

Laravel's Artisan is equivalent to Rails' command-line tool. Every command starts with `php artisan`.

### Getting Help

**Rails:**
```bash
rails --help
rails generate --help
rails db:migrate --help
```

**Laravel:**
```bash
php artisan
php artisan list
php artisan help migrate
php artisan migrate --help
```

### Common Artisan Commands

```bash
# See all available commands
php artisan list

# Get help on a command
php artisan help make:controller

# Run with verbose output
php artisan migrate -v

# Run in specific environment
php artisan migrate --env=production
```

## 2. Code Generation

### Models

**Rails:**
```bash
# Generate model
rails generate model User name:string email:string

# Generate model with migration
rails g model Post title:string body:text user:references
```

**Laravel:**
```bash
# Generate model only
php artisan make:model User

# Generate model with migration
php artisan make:model User -m

# Generate model with migration, factory, and seeder
php artisan make:model User -mfs

# Generate model with everything (migration, factory, seeder, controller, resource)
php artisan make:model User --all

# Shortcuts
php artisan make:model User -mcr  # Model, migration, controller (resource)
```

### Controllers

**Rails:**
```bash
# Basic controller
rails g controller Users

# Controller with actions
rails g controller Users index show create update destroy

# API controller
rails g controller api/Users
```

**Laravel:**
```bash
# Basic controller
php artisan make:controller UserController

# Resource controller (with CRUD methods)
php artisan make:controller UserController --resource

# API resource controller (without create/edit views)
php artisan make:controller UserController --api

# Invokable controller (single action)
php artisan make:controller ShowProfile --invokable

# Controller in subdirectory
php artisan make:controller Api/UserController
```

### Migrations

**Rails:**
```bash
# Create migration
rails g migration CreateUsers name:string email:string
rails g migration AddRoleToUsers role:string
```

**Laravel:**
```bash
# Create migration
php artisan make:migration create_users_table
php artisan make:migration add_role_to_users_table

# Laravel infers table name from migration name
# create_users_table → creates users table
# add_role_to_users_table → modifies users table
```

### Other Generators

**Rails:**
```bash
rails g scaffold Post title:string body:text
rails g mailer UserMailer
rails g job ProcessPodcast
```

**Laravel:**
```bash
# No built-in scaffold, but can generate components
php artisan make:controller PostController --resource
php artisan make:model Post -m

# Mail
php artisan make:mail WelcomeEmail

# Job
php artisan make:job ProcessPodcast

# Event
php artisan make:event UserRegistered

# Listener
php artisan make:listener SendWelcomeEmail --event=UserRegistered

# Request (form validation)
php artisan make:request StoreUserRequest

# Middleware
php artisan make:middleware CheckAge

# Seeder
php artisan make:seeder UserSeeder

# Factory
php artisan make:factory UserFactory

# Policy (authorization)
php artisan make:policy PostPolicy --model=Post

# Resource (API transformation)
php artisan make:resource UserResource

# Test
php artisan make:test UserTest
php artisan make:test UserTest --unit

# View (Blade template)
php artisan make:view posts.index

# Component (Blade component)
php artisan make:component Alert
php artisan make:component Alert --inline  # Inline component

# Notification
php artisan make:notification InvoicePaid

# Observer (model lifecycle hooks)
php artisan make:observer PostObserver --model=Post

# Service Provider
php artisan make:provider CustomServiceProvider

# Channel (broadcasting)
php artisan make:channel OrderChannel

# Exception
php artisan make:exception CustomException
```

## 3. Tinker (Interactive Console)

Laravel's Tinker is equivalent to `rails console`.

**Rails Console:**
```ruby
rails console

# In console
user = User.first
user.name = "Jane"
user.save

User.where(active: true).count
Post.create(title: "Hello", body: "World")
```

**Laravel Tinker:**
```bash
php artisan tinker

# In Tinker
>>> $user = User::first();
>>> $user->name = "Jane";
>>> $user->save();

>>> User::where('active', true)->count();
>>> Post::create(['title' => 'Hello', 'body' => 'World']);

# Tinker-specific helpers
>>> $this  // Show available bindings
>>> help()  // Show help
>>> clear  // Clear screen
```

::: tip Tinker Shortcuts
Tinker includes helpful shortcuts:
- `$this` shows available variables
- Tab completion for methods
- History navigation with arrow keys
- Multi-line input support
:::

## 4. Development Server

**Rails:**
```bash
# Start server (default port 3000)
rails server

# Custom port
rails server -p 4000

# Bind to all interfaces
rails server -b 0.0.0.0
```

**Laravel:**
```bash
# Start server (default port 8000)
php artisan serve

# Custom port
php artisan serve --port=8080

# Custom host
php artisan serve --host=0.0.0.0

# Run in background (Unix)
php artisan serve > /dev/null 2>&1 &
```

::: tip Hot Reloading with Laravel Mix/Vite
Laravel's asset compilation tools (Mix or Vite) support hot module replacement (HMR) for instant browser updates:

```bash
# With Vite (Laravel 9+)
npm run dev  # Starts Vite dev server with HMR

# With Laravel Mix (older projects)
npm run watch  # Watches for changes and recompiles
```

Unlike Rails' asset pipeline, Laravel's frontend tools provide true hot reloading—changes to CSS/JS update in the browser without a full page refresh.
:::

### Laravel Valet (macOS) vs Pow

If you use Pow or Puma-dev on Rails, Laravel Valet is the equivalent:

```bash
# Install Valet (macOS)
composer global require laravel/valet
valet install

# Park directory (serves all subdirectories)
cd ~/Sites
valet park

# Now myapp.test works automatically!

# Or link individual project
cd ~/Projects/myapp
valet link

# Secure with HTTPS
valet secure myapp
```

## 5. Database Management

### Running Migrations

**Rails:**
```bash
rails db:migrate
rails db:rollback
rails db:rollback STEP=3
rails db:migrate:status
rails db:reset
```

**Laravel:**
```bash
php artisan migrate
php artisan migrate:rollback
php artisan migrate:rollback --step=3
php artisan migrate:status

# Fresh (drop all tables and re-migrate)
php artisan migrate:fresh

# Fresh with seed
php artisan migrate:fresh --seed

# Reset (rollback all, then re-migrate)
php artisan migrate:reset

# Refresh (rollback and re-migrate)
php artisan migrate:refresh
```

### Database Seeding

**Rails:**
```bash
rails db:seed
rails db:seed:replant  # Clear and re-seed
```

**Laravel:**
```bash
php artisan db:seed

# Run specific seeder
php artisan db:seed --class=UserSeeder

# Fresh migrate with seed
php artisan migrate:fresh --seed
```

### Database Inspection

**Laravel:**
```bash
# Show database tables
php artisan db:show

# Show table schema
php artisan db:table users

# Monitor database queries
php artisan db:monitor --max=100
```

### Storage Management

**Laravel:**
```bash
# Create symbolic link for public storage
php artisan storage:link

# This links storage/app/public to public/storage
# Allows serving uploaded files via public URL
```

## 6. Queue Workers

Both frameworks support background jobs. Rails uses Sidekiq, Resque, or ActiveJob. Laravel has built-in queue support.

**Rails (ActiveJob + Sidekiq):**
```bash
# Start Sidekiq
bundle exec sidekiq

# Queue a job
UserMailer.welcome_email(@user).deliver_later
```

**Laravel:**
```bash
# Create queue table (first time setup)
php artisan queue:table
php artisan migrate

# Create notifications table (for database notifications)
php artisan notifications:table
php artisan migrate

# Start queue worker
php artisan queue:work

# Process specific queue
php artisan queue:work --queue=emails,default

# Process one job
php artisan queue:work --once

# Listen (restarts automatically)
php artisan queue:listen

# Restart all workers
php artisan queue:restart

# View failed jobs
php artisan queue:failed

# Retry failed job
php artisan queue:retry 1

# Retry all failed jobs
php artisan queue:retry all

# Flush failed jobs
php artisan queue:flush

# Queue a job
Mail::to($user)->queue(new WelcomeEmail());
```

::: tip Horizon for Queue Management
Laravel Horizon provides a beautiful dashboard for monitoring queues (like Sidekiq Web UI). Install with `composer require laravel/horizon`.
:::

## 7. Task Scheduling

**Rails (whenever gem or cron):**
```ruby
# config/schedule.rb
every 1.day, at: '4:30 am' do
  rake 'emails:send_digest'
end
```

**Laravel (built-in scheduler):**
```php
# filename: app/Console/Kernel.php
<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('emails:send-digest')
            ->daily()
            ->at('04:30');

        $schedule->call(function () {
            // Inline task
        })->everyMinute();

        // More examples
        $schedule->command('backup:run')->weekly();
        $schedule->job(new ProcessPodcast)->hourly();
        $schedule->exec('node script.js')->dailyAt('13:00');
    }
}
```

Then add one cron entry:
```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

Laravel handles all scheduling internally!

## 8. Cache Management

**Rails:**
```bash
rails tmp:cache:clear
Rails.cache.clear  # In console
```

**Laravel:**
```bash
# Clear application cache
php artisan cache:clear

# Clear route cache
php artisan route:clear

# Clear config cache
php artisan config:clear

# Clear view cache
php artisan view:clear

# Clear compiled class files
php artisan clear-compiled

# Clear everything
php artisan optimize:clear
```

## 9. Route Management

**Rails:**
```bash
# List all routes
rails routes

# Search routes
rails routes | grep users

# Routes for specific controller
rails routes -c Users
```

**Laravel:**
```bash
# List all routes
php artisan route:list

# Filter by name
php artisan route:list --name=user

# Filter by path
php artisan route:list --path=api

# Filter by method
php artisan route:list --method=GET

# Show middleware
php artisan route:list --except-vendor

# Compact output
php artisan route:list --compact

# Cache routes (production)
php artisan route:cache

# Clear route cache
php artisan route:clear
```

## 10. Custom Artisan Commands

### Creating Commands

**Rails (Rake task):**
```ruby
# lib/tasks/users.rake
namespace :users do
  desc "Send weekly digest emails"
  task send_digest: :environment do
    User.active.find_each do |user|
      UserMailer.weekly_digest(user).deliver_now
    end
  end
end

# Run: rails users:send_digest
```

**Laravel:**
```bash
# Generate command
php artisan make:command SendDigest
```

```php
# filename: app/Console/Commands/SendDigest.php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendDigest extends Command
{
    protected $signature = 'users:send-digest
                          {--queue : Queue the emails}';

    protected $description = 'Send weekly digest emails';

    public function handle()
    {
        $this->info('Sending digest emails...');

        $bar = $this->output->createProgressBar(100);

        User::active()->chunk(100, function ($users) use ($bar) {
            foreach ($users as $user) {
                Mail::to($user)->send(new WeeklyDigest($user));
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info('Emails sent successfully!');
    }
}

// Run: php artisan users:send-digest
// Run: php artisan users:send-digest --queue
```

### Command Features

Laravel commands support rich interactions:

```php
# filename: app/Console/Commands/ExampleCommand.php
<?php

// Ask for input
$name = $this->ask('What is your name?');

// Ask with default
$name = $this->ask('What is your name?', 'John');

// Secret input (password)
$password = $this->secret('Password?');

// Confirmation
if ($this->confirm('Do you wish to continue?')) {
    // Continue
}

// Choice
$role = $this->choice('Select role', ['admin', 'user'], 'user');

// Output styling
$this->info('Information message');
$this->error('Error message');
$this->warn('Warning message');
$this->line('Regular message');

// Tables
$this->table(
    ['Name', 'Email'],
    [['John', 'john@example.com']]
);

// Progress bar
$bar = $this->output->createProgressBar(100);
for ($i = 0; $i < 100; $i++) {
    $bar->advance();
}
$bar->finish();
```

## 11. Asset Compilation

**Rails (Webpacker/Sprockets):**
```bash
rails assets:precompile
rails webpacker:compile
```

**Laravel (Mix/Vite):**
```bash
# Development
npm run dev

# Watch for changes
npm run watch

# Production build
npm run build

# Laravel Mix (older projects)
npm run production
```

## 12. Optimization Commands

Laravel provides optimization commands for production:

```bash
# Optimize for production (cache config, routes, views)
php artisan optimize

# Clear all caches
php artisan optimize:clear

# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Cache events
php artisan event:cache
```

## 13. Package Development

**Laravel:**
```bash
# Create package boilerplate
composer require spatie/laravel-package-tools

# Publish package assets
php artisan vendor:publish --provider="VendorName\PackageName\ServiceProvider"

# Publish specific tag
php artisan vendor:publish --tag=config
php artisan vendor:publish --tag=migrations
```

## 14. IDE Support

### PHP Storm / VS Code

Both IDEs offer excellent Laravel support:

**PHP Storm:**
- Laravel Plugin (official)
- Laravel Idea (paid, highly recommended)

**VS Code Extensions:**
- Laravel Extension Pack
- Laravel Blade Snippets
- Laravel Artisan
- Laravel Goto View
- Laravel Extra Intellisense

### Laravel IDE Helper

Generate helper files for better IDE autocomplete:

```bash
composer require --dev barryvdh/laravel-ide-helper

# Generate helper
php artisan ide-helper:generate

# Generate model helpers
php artisan ide-helper:models

# Generate PhpStorm meta
php artisan ide-helper:meta
```

## 15. Debugging Tools

### Laravel Debugbar

Like Rails' debug toolbar:

```bash
composer require barryvdh/laravel-debugbar --dev
```

Shows:
- Database queries (with timing)
- Route information
- Views rendered
- Memory usage
- Request/response data

### Telescope

Laravel Telescope is like Rails' web console but more powerful:

```bash
composer require laravel/telescope
php artisan telescope:install
php artisan migrate
```

Visit `/telescope` to see:
- Requests
- Commands
- Schedules
- Jobs
- Databases queries
- Emails
- Notifications
- Cache operations
- Redis operations

### Ray

Debugging tool (like `binding.pry` or `debugger`):

```bash
composer require spatie/laravel-ray
```

```php
# filename: app/Http/Controllers/ExampleController.php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ExampleController extends Controller
{
    public function index(Request $request)
    {
        // Debug with Ray
        ray($user);
        ray($request->all());
        ray()->showQueries();
        
        return view('example');
    }
}
```

## 16. Environment Management

**Rails:**
```bash
export RAILS_ENV=production
rails console -e production
```

**Laravel:**
```bash
# Laravel uses .env file
php artisan config:cache

# Check current environment
php artisan env

# Run command in specific environment
php artisan migrate --env=production
```

## 16b. Logging Commands

**Rails:**
```bash
# View logs
tail -f log/development.log
tail -f log/production.log
```

**Laravel:**
```bash
# View logs (default location)
tail -f storage/logs/laravel.log

# Clear log (custom command or manually)
# Note: Laravel doesn't have built-in log:clear
# You can create a custom command or manually truncate the file
```

::: tip Log Management
Laravel's logging is configured in `config/logging.php` and supports multiple channels (single, daily, slack, etc.). Unlike Rails, Laravel doesn't have a built-in `log:clear` command, but you can create one or use `truncate`:

```bash
# Clear log manually
truncate -s 0 storage/logs/laravel.log
```
:::

## 17. Testing Commands

**Rails:**
```bash
rails test
rspec
rspec spec/models
```

**Laravel:**
```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter=UserTest

# Run parallel tests
php artisan test --parallel

# Stop on failure
php artisan test --stop-on-failure

# With coverage
php artisan test --coverage

# PHPUnit directly
./vendor/bin/phpunit

# Pest (modern alternative)
./vendor/bin/pest
```

## 18. Maintenance Mode

**Rails (custom implementation):**
```ruby
# Usually custom middleware
```

**Laravel (built-in):**
```bash
# Enable maintenance mode
php artisan down

# With secret bypass
php artisan down --secret="bypass-token"
# Visit: /bypass-token to access site

# With custom message
php artisan down --message="Upgrading database"

# With retry header
php artisan down --retry=60

# Disable maintenance mode
php artisan up
```

## 19. Application Information

```bash
# Show Laravel version
php artisan --version

# Show application info
php artisan about

# Show environment
php artisan env

# List installed packages
composer show

# Show package versions
php artisan package:discover
```

## 20. Productivity Packages

### Essential Laravel Packages (like Ruby gems)

```bash
# Debugbar (development)
composer require barryvdh/laravel-debugbar --dev

# IDE Helper
composer require --dev barryvdh/laravel-ide-helper

# Telescope (monitoring)
composer require laravel/telescope

# Horizon (queue dashboard)
composer require laravel/horizon

# Sanctum (API authentication)
composer require laravel/sanctum

# Breeze (authentication scaffolding)
composer require laravel/breeze --dev

# Jetstream (advanced auth with teams)
composer require laravel/jetstream

# Sail (Docker development environment)
composer require laravel/sail --dev
```

## 21. Laravel Sail (Docker)

Laravel Sail is like Rails with Docker:

```bash
# Install Sail
composer require laravel/sail --dev
php artisan sail:install

# Start containers
./vendor/bin/sail up

# Or add alias to ~/.bashrc
alias sail='./vendor/bin/sail'
sail up

# Run artisan commands
sail artisan migrate
sail artisan tinker

# Run Composer
sail composer install

# Run NPM
sail npm install
sail npm run dev

# Run tests
sail test

# Access MySQL
sail mysql

# Access Redis
sail redis
```

## Wrap-up

Congratulations! You've completed a comprehensive tour of Laravel's developer experience tools. Here's what you've accomplished:

✓ **Mastered Artisan CLI** - Understood how Laravel's command-line tool compares to Rails
✓ **Learned Code Generation** - Generated models, controllers, migrations, views, components, and more with Artisan
✓ **Explored Tinker** - Used Laravel's interactive console for development and debugging
✓ **Configured Development Environment** - Set up local servers with Valet or Sail
✓ **Managed Databases** - Ran migrations, seeders, and database inspection commands
✓ **Set Up Storage** - Created symbolic links for public file storage
✓ **Implemented Queues** - Set up background job processing with built-in queue workers
✓ **Configured Scheduling** - Created scheduled tasks without manual cron configuration
✓ **Created Custom Commands** - Built Artisan commands with rich interactions and progress bars
✓ **Set Up Debugging Tools** - Installed and configured Telescope, Debugbar, and Ray
✓ **Configured IDE Support** - Set up Laravel IDE Helper and extension recommendations
✓ **Learned Hot Reloading** - Understood how Laravel Mix/Vite provides HMR for frontend development

### Key Takeaways

1. **Familiar Yet Powerful** - Laravel's tools feel familiar to Rails developers but offer additional features
2. **Convention Over Configuration** - Less setup, more productivity
3. **Built-In Everything** - Queues, scheduling, and debugging tools are first-class citizens
4. **Rich Command Interface** - Artisan commands support progress bars, tables, and interactive prompts
5. **Excellent Tooling** - Telescope and Debugbar provide Rails-level debugging capabilities
6. **Docker Support** - Sail makes Docker development as easy as Rails with Docker
7. **IDE Integration** - Excellent support in PHPStorm and VS Code with proper extensions

### What's Next?

Now that you understand Laravel's developer tools, you'll learn PHP syntax differences in the next chapter. This will help you write Laravel code confidently when coming from Ruby.

## Practice Exercise

Try these commands to get familiar:

```bash
# 1. Create a new Laravel project
laravel new blog

# 2. Generate a Post model with migration
php artisan make:model Post -m

# 3. Open Tinker and create a post
php artisan tinker
>>> Post::create(['title' => 'First', 'body' => 'Hello'])

# 4. List all routes
php artisan route:list

# 5. Generate a controller
php artisan make:controller PostController --resource

# 6. Create a custom command
php artisan make:command GreetUser

# 7. Start the development server
php artisan serve
```

---

::: tip Continue Learning
Move on to [Chapter 04: PHP Syntax for Rails Devs](/series/rails-developers-love-laravel/chapters/04-php-syntax-for-rails-devs) to learn PHP syntax differences.
:::


## Further Reading

- [Laravel Artisan Documentation](https://laravel.com/docs/artisan) — Official documentation for Artisan commands and custom command creation
- [Laravel Tinker Documentation](https://laravel.com/docs/tinker) — Guide to using Laravel's interactive REPL
- [Laravel Telescope Documentation](https://laravel.com/docs/telescope) — Complete guide to Laravel's debugging and monitoring tool
- [Laravel Queue Documentation](https://laravel.com/docs/queues) — Comprehensive guide to queue workers and background jobs
- [Laravel Task Scheduling](https://laravel.com/docs/scheduling) — Official documentation for the task scheduler
- [Laravel Sail Documentation](https://laravel.com/docs/sail) — Docker development environment for Laravel
- [Laravel Valet Documentation](https://laravel.com/docs/valet) — macOS development environment setup
- [Laravel IDE Helper](https://github.com/barryvdh/laravel-ide-helper) — GitHub repository for IDE autocomplete generation
- [Laravel Debugbar](https://github.com/barryvdh/laravel-debugbar) — Debug toolbar for Laravel applications
- [Spatie Laravel Ray](https://spatie.be/docs/ray/laravel/getting-started) — Powerful debugging tool for Laravel


