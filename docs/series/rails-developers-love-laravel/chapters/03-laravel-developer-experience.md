---
title: "Laravel's Developer Experience: Productivity & Tools"
description: Explore Laravel's developer tools from a Rails perspective—Artisan CLI, Tinker, code generation, debugging, and productivity features you'll love.
series: rails-developers-love-laravel
chapter: 3
difficulty: Intermediate
tags: ["laravel", "artisan", "cli", "tools", "developer-experience", "productivity"]
---

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/#choose-your-learning-path">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/rails-developers-love-laravel/">Rails to Laravel</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 03</span>
</div>

![Laravel's Developer Experience](/images/rails-developers-love-laravel/chapter-03-laravel-developer-experience-hero-full.webp)

# Chapter 03: Laravel's Developer Experience <span class="difficulty-badge difficulty-intermediate">Intermediate</span>

## Overview

Laravel's developer experience tools are where Laravel truly shines. If you've worked with Rails' command-line tools like `rails generate`, `rails console`, or Rake tasks, you'll find Laravel's Artisan CLI familiar yet more powerful in some areas. Laravel follows Rails' "convention over configuration" philosophy, meaning you spend less time configuring and more time building.

This chapter explores Laravel's productivity tools from a Rails developer's perspective, showing you how to be productive immediately.

## What You'll Learn

- Artisan CLI vs Rails commands
- Code generation (controllers, models, migrations)
- Tinker (Laravel's REPL) vs Rails console
- Development server and hot reloading
- Database management commands
- Queue workers and background jobs
- Task scheduling (cron alternatives)
- Package development
- Custom Artisan commands
- IDE support and debugging tools

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
<?php
// app/Console/Kernel.php

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
<?php
// app/Console/Commands/SendDigest.php

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
<?php
// In your code
ray($user);
ray($request->all());
ray()->showQueries();
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

## Summary

Laravel's developer experience parallels Rails in many ways:

✅ **Artisan CLI** — Equivalent to `rails` command
✅ **Tinker** — Interactive console like `rails console`
✅ **Code generators** — Comprehensive generation commands
✅ **Built-in scheduling** — No cron configuration needed
✅ **Queue workers** — Built-in background job processing
✅ **Telescope** — Advanced debugging and monitoring
✅ **Sail** — Docker development environment

Key differences:
- All commands start with `php artisan`
- More granular cache clearing
- Built-in queue and schedule management
- Separate commands for different cache types

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

<ProgressTracker seriesId="rails-developers-love-laravel" :totalChapters="11" title="Your Progress" />
