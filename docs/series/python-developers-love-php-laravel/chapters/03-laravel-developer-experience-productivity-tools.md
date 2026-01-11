---
title: "03: Laravel's Developer Experience: Productivity, Conventions and Tools"
description: "Master Laravel's Artisan CLI, migrations, testing, and conventions—comparing to Python's manage.py and pytest workflows."
series: "python-developers-love-php-laravel"
chapter: 3
order: 3
difficulty: "Intermediate"
prerequisites:
  - "/series/python-developers-love-php-laravel/chapters/02-modern-php-whats-changed"
estimatedTime: "PT75M"
teaches:
  - 'Master Artisan CLI commands and compare to Python equivalents (Django''s `manage.py`, Flask CLI)'
  - 'Understand Laravel migrations vs Django migrations workflow'
  - 'Learn PHPUnit testing vs pytest workflows'
  - 'Understand Laravel conventions vs Django/Flask conventions'
  - 'Use seeders and factories for test data management'
---
![Laravel's Developer Experience](/images/python-developers-love-php-laravel/chapter-03-laravel-developer-experience-productivity-tools-hero-full.webp)

# Chapter 03: Laravel's Developer Experience: Productivity, Conventions and Tools

## Overview

In Chapter 02, you learned about modern PHP's evolution—type declarations, JIT compilation, and language features that bring PHP on par with Python. Now it's time to explore Laravel's developer experience tools, which are where Laravel truly shines. If you've worked with Django's `manage.py` or Flask's CLI tools, you'll find Laravel's Artisan CLI familiar yet more powerful.

This chapter is about **productivity and conventions**. Laravel follows a "conventions over configuration" philosophy similar to Django, meaning you spend less time configuring and more time building. You'll learn how Laravel's command-line tools, migrations, testing framework, and naming conventions compare to Python equivalents—and where Laravel might surprise you with its developer-friendly features.

By the end of this chapter, you'll understand that Laravel's developer experience tools mirror Python's best practices while adding their own delightful touches. You'll master Artisan CLI (Laravel's `manage.py`), migrations (Laravel's version of Django migrations), PHPUnit testing (Laravel's pytest), and Laravel's conventions. Most importantly, you'll see how these tools accelerate development in ways that feel familiar yet refreshing.

## Prerequisites

Before starting this chapter, you should have:

- Completion of [Chapter 02](/series/python-developers-love-php-laravel/chapters/02-modern-php-whats-changed) or equivalent understanding of modern PHP
- Laravel 11.x installed (or ability to follow along with code examples)
- Python experience with Django's `manage.py` or Flask CLI
- Familiarity with pytest or unittest
- Basic understanding of database migrations (Django migrations preferred)
- **Estimated Time**: ~75 minutes

**Verify your setup:**

```bash
# Check PHP version (should show PHP 8.4+)
php --version

# Check Composer is installed
composer --version

# If you have Laravel installed, verify Artisan works
php artisan --version

# Expected output: Laravel Framework 11.x.x (or similar)
```

## What You'll Build

By the end of this chapter, you will have:

- Understanding of Artisan CLI commands and how they compare to Django's `manage.py`
- Knowledge of Laravel migrations workflow vs Django migrations
- PHPUnit test examples comparing to pytest
- Understanding of Laravel conventions vs Django/Flask conventions
- Working code examples demonstrating each tool
- Ability to create custom Artisan commands, migrations, seeders, and tests
- Confidence in Laravel's developer experience tools

## Quick Start

Want to see Laravel's developer tools in action right away? Here's a quick comparison:

**Django (Python):**

```bash
# Create a migration
python manage.py makemigrations

# Run migrations
python manage.py migrate

# Run tests
python manage.py test
```

**Laravel (PHP):**

```bash
# Create a migration
php artisan make:migration create_users_table

# Run migrations
php artisan migrate

# Run tests
php artisan test
```

See the pattern? Laravel's `php artisan` is Django's `python manage.py` equivalent. Both provide command-line access to framework functionality, but Laravel's Artisan offers more built-in commands and a more consistent interface. This chapter will show you all the ways Laravel's developer tools compare to Python equivalents.

## Objectives

- Master Artisan CLI commands and compare to Python equivalents (Django's `manage.py`, Flask CLI)
- Understand Laravel migrations vs Django migrations workflow
- Learn PHPUnit testing vs pytest workflows
- Understand Laravel conventions vs Django/Flask conventions
- Use seeders and factories for test data management
- Recognize Laravel's "conventions over configuration" philosophy
- Create custom Artisan commands, migrations, seeders, and tests
- Use Laravel's code generation commands (`make:*`) to boost productivity
- Manage cache and configuration with Artisan commands

## Step 1: Artisan CLI - Laravel's Command-Line Tool (~15 min)

### Goal

Understand Laravel's Artisan CLI and how it compares to Django's `manage.py` and Flask CLI tools.

### Actions

1. **Artisan vs manage.py - The Basics**

   Laravel's Artisan CLI is the equivalent of Django's `manage.py`. Both provide command-line access to framework functionality:

   **Django (Python):**

   ```bash
   # List all available commands
   python manage.py help

   # Run a specific command
   python manage.py runserver
   python manage.py createsuperuser
   python manage.py shell
   ```

   **Laravel (PHP):**

   ```bash
   # List all available commands
   php artisan list

   # Run a specific command
   php artisan serve
   php artisan make:user
   php artisan tinker
   ```

   The complete Artisan command examples are available in [`artisan-commands.php`](https://github.com/dalehurley/codewithphp/blob/main/code/python-developers-love-php-laravel/chapter-03/artisan-commands.php).

2. **Common Commands Comparison**

   Here's how common Django commands map to Laravel:

   | Django Command | Laravel Command | Purpose |
   |---------------|-----------------|---------|
   | `python manage.py runserver` | `php artisan serve` | Start development server |
   | `python manage.py shell` | `php artisan tinker` | Interactive shell |
   | `python manage.py makemigrations` | `php artisan make:migration` | Create migration |
   | `python manage.py migrate` | `php artisan migrate` | Run migrations |
   | `python manage.py test` | `php artisan test` | Run tests |
   | `python manage.py createsuperuser` | `php artisan make:user` | Create admin user |
   | `python manage.py collectstatic` | `php artisan storage:link` | Link storage |
   | `python manage.py showmigrations` | `php artisan migrate:status` | Show migration status |
   | `python manage.py dbshell` | `php artisan db` | Open database shell |
   | `python manage.py shell_plus` | `php artisan tinker` | Enhanced shell with models |

3. **Creating Custom Commands**

   Both frameworks allow you to create custom commands:

   **Django Management Command:**

   ```python
   # filename: management/commands/send_emails.py
   from django.core.management.base import BaseCommand
   from django.core.mail import send_mail

   class Command(BaseCommand):
       help = 'Send email notifications'

       def add_arguments(self, parser):
           parser.add_argument('--count', type=int, default=10)

       def handle(self, *args, **options):
           count = options['count']
           self.stdout.write(f'Sending {count} emails...')
           # Send emails logic
           self.stdout.write(self.style.SUCCESS('Emails sent!'))
   ```

   **Laravel Artisan Command:**

   ```php
   # filename: app/Console/Commands/SendEmails.php
   <?php

   namespace App\Console\Commands;

   use Illuminate\Console\Command;

   class SendEmails extends Command
   {
       protected $signature = 'emails:send {--count=10}';
       protected $description = 'Send email notifications';

       public function handle(): int
       {
           $count = (int) $this->option('count');
           $this->info("Sending {$count} emails...");
           // Send emails logic
           $this->info('Emails sent!');
           return Command::SUCCESS;
       }
   }
   ```

   **Creating the command:**

   ```bash
   # Django: Create file manually in management/commands/
   # Laravel: Use Artisan generator
   php artisan make:command SendEmails
   ```

   The custom command examples are available in [`artisan-commands.php`](https://github.com/dalehurley/codewithphp/blob/main/code/python-developers-love-php-laravel/chapter-03/artisan-commands.php) and [`django-manage.py`](https://github.com/dalehurley/codewithphp/blob/main/code/python-developers-love-php-laravel/chapter-03/django-manage.py).

4. **Artisan Tinker - Interactive Shell**

   Laravel's `tinker` is similar to Django's `shell` but more powerful:

   **Django Shell:**

   ```bash
   python manage.py shell
   >>> from myapp.models import User
   >>> User.objects.all()
   ```

   **Laravel Tinker:**

   ```bash
   php artisan tinker
   >>> User::all()
   >>> User::where('email', 'john@example.com')->first()
   >>> $user = User::factory()->create()
   ```

   Tinker provides full access to your Laravel application, including models, facades, and helpers. It's perfect for quick database queries and testing.

5. **Route Listing - Discover Your Routes**

   Laravel's `route:list` command shows all registered routes, similar to Django's URL pattern inspection:

   **Django (Python):**

   ```bash
   # Django doesn't have a built-in command, but you can:
   python manage.py show_urls  # Requires django-extensions
   # Or inspect urls.py manually
   ```

   **Laravel (PHP):**

   ```bash
   # List all routes
   php artisan route:list

   # Filter by method
   php artisan route:list --method=GET

   # Filter by name
   php artisan route:list --name=user

   # Show route details
   php artisan route:list --columns=method,uri,name,action
   ```

   This is invaluable for debugging routing issues and discovering available endpoints.

6. **Cache and Config Management**

   Laravel provides commands to manage cache and configuration:

   **Laravel (PHP):**

   ```bash
   # Clear various caches
   php artisan cache:clear        # Clear application cache
   php artisan config:clear       # Clear configuration cache
   php artisan route:clear        # Clear route cache
   php artisan view:clear         # Clear compiled view cache

   # Cache for production (optimization)
   php artisan config:cache       # Cache configuration files
   php artisan route:cache        # Cache routes
   php artisan view:cache         # Cache compiled views
   ```

   **Django (Python):**

   ```bash
   # Django's equivalent
   python manage.py collectstatic  # Collect static files
   python manage.py clearsessions   # Clear session data
   # Django doesn't cache config/routes by default
   ```

   ::: tip Production Optimization
   In production, use `php artisan config:cache` and `php artisan route:cache` to improve performance. These commands compile configuration and routes into optimized files. Remember to run `config:clear` and `route:clear` after making changes in development!
   :::

7. **Development Server Options**

   Laravel's development server offers more options than Django's:

   **Django (Python):**

   ```bash
   python manage.py runserver
   python manage.py runserver 8000
   python manage.py runserver 0.0.0.0:8000
   ```

   **Laravel (PHP):**

   ```bash
   php artisan serve
   php artisan serve --port=8000
   php artisan serve --host=0.0.0.0
   php artisan serve --port=8080 --host=127.0.0.1
   ```

   Both serve your application locally, but Laravel's server is built on PHP's built-in server, while Django uses a development server written in Python.

8. **Artisan's Built-in Helpers**

   Laravel's Artisan provides helpful output formatting:

   ```php
   # In an Artisan command
   $this->info('Success message');        // Green text
   $this->error('Error message');         // Red text
   $this->warn('Warning message');        // Yellow text
   $this->line('Plain message');          // Default text
   $this->table(['Name', 'Email'], $data); // Formatted table
   ```

   Django's `manage.py` uses similar helpers:

   ```python
   # In a Django management command
   self.stdout.write(self.style.SUCCESS('Success message'))
   self.stdout.write(self.style.ERROR('Error message'))
   self.stdout.write(self.style.WARNING('Warning message'))
   ```

### Expected Result

After completing this step, you'll understand that:

- Laravel's `php artisan` is equivalent to Django's `python manage.py`
- Artisan provides more built-in commands than Django's `manage.py`
- Both frameworks allow custom command creation
- Artisan's command syntax is more consistent and discoverable
- Laravel's `tinker` is similar to Django's `shell` but more powerful
- Artisan's `make:*` commands generate boilerplate code automatically
- Artisan provides helpful output formatting methods

### Why It Works

Laravel's Artisan CLI follows the same philosophy as Django's `manage.py`: provide a single entry point for all framework operations. Artisan is built on Symfony Console component, which provides a robust command-line interface. The `make:*` commands generate boilerplate code, reducing manual file creation. Artisan's help system (`php artisan help <command>`) provides inline documentation, making it easy to discover command options.

### Troubleshooting

- **"Command not found"** — Ensure you're in a Laravel project directory. Artisan commands only work within Laravel projects. Check with `php artisan list`.
- **"Artisan command doesn't exist"** — Some commands require specific packages. For example, `php artisan make:user` might require Laravel Breeze or Jetstream. Use `php artisan list` to see available commands.
- **"Permission denied"** — Ensure the `artisan` file is executable: `chmod +x artisan` (though this is rarely needed).
- **"Class not found"** — After creating a custom command, run `composer dump-autoload` to refresh the autoloader. Laravel should auto-discover commands in `app/Console/Commands/`.

## Step 2: Database Migrations - Version Control for Your Schema (~20 min)

### Goal

Master Laravel migrations and understand how they compare to Django migrations.

### Actions

1. **Creating Migrations**

   Both frameworks use migrations for database schema versioning:

   **Django (Python):**

   ```bash
   # Create a migration
   python manage.py makemigrations

   # Create a migration for a specific app
   python manage.py makemigrations myapp

   # Create an empty migration
   python manage.py makemigrations --empty myapp
   ```

   **Laravel (PHP):**

   ```bash
   # Create a migration
   php artisan make:migration create_users_table

   # Create a migration with specific name
   php artisan make:migration add_email_to_users_table --table=users

   # Create an empty migration
   php artisan make:migration custom_migration
   ```

2. **Migration File Structure**

   Here's how migration files compare:

   **Django Migration:**

   ```python
   # filename: myapp/migrations/0001_initial.py
   from django.db import migrations, models

   class Migration(migrations.Migration):
       dependencies = []
       operations = [
           migrations.CreateModel(
               name='User',
               fields=[
                   ('id', models.BigAutoField(primary_key=True)),
                   ('name', models.CharField(max_length=255)),
                   ('email', models.EmailField(max_length=255, unique=True)),
                   ('created_at', models.DateTimeField(auto_now_add=True)),
               ],
           ),
       ]
   ```

   **Laravel Migration:**

   ```php
   # filename: database/migrations/2024_01_01_000001_create_users_table.php
   <?php

   use Illuminate\Database\Migrations\Migration;
   use Illuminate\Database\Schema\Blueprint;
   use Illuminate\Support\Facades\Schema;

   return new class extends Migration
   {
       public function up(): void
       {
           Schema::create('users', function (Blueprint $table) {
               $table->id();
               $table->string('name');
               $table->string('email')->unique();
               $table->timestamp('created_at')->useCurrent();
           });
       }

       public function down(): void
       {
           Schema::dropIfExists('users');
       }
   };
   ```

   ::: tip Laravel Migration Naming
   Laravel migrations use timestamps in filenames (e.g., `2024_01_01_120000_create_users_table.php`). This ensures migrations run in chronological order, even if created on different machines. Django uses numbered migrations (`0001_initial.py`), which can cause conflicts in team environments.
   :::

   The migration examples are available in [`laravel-migration.php`](https://github.com/dalehurley/codewithphp/blob/main/code/python-developers-love-php-laravel/chapter-03/laravel-migration.php) and [`django-migration.py`](https://github.com/dalehurley/codewithphp/blob/main/code/python-developers-love-php-laravel/chapter-03/django-migration.py).

3. **Running Migrations**

   Both frameworks track which migrations have been run:

   **Django (Python):**

   ```bash
   # Run all pending migrations
   python manage.py migrate

   # Run migrations for specific app
   python manage.py migrate myapp

   # Show migration status
   python manage.py showmigrations
   ```

   **Laravel (PHP):**

   ```bash
   # Run all pending migrations
   php artisan migrate

   # Run migrations in specific path
   php artisan migrate --path=database/migrations/custom

   # Show migration status
   php artisan migrate:status
   ```

4. **Rolling Back Migrations**

   Both frameworks support rolling back migrations:

   **Django (Python):**

   ```bash
   # Rollback last migration
   python manage.py migrate myapp zero
   python manage.py migrate myapp 0001

   # Rollback to specific migration
   python manage.py migrate myapp 0002_previous_migration
   ```

   **Laravel (PHP):**

   ```bash
   # Rollback last batch of migrations
   php artisan migrate:rollback

   # Rollback specific number of steps
   php artisan migrate:rollback --step=3

   # Rollback all migrations
   php artisan migrate:reset

   # Rollback and re-run migrations
   php artisan migrate:refresh
   ```

5. **Migration Best Practices**

   Both frameworks follow similar best practices:

   - **Never edit existing migrations** — Create new migrations for changes
   - **Keep migrations small** — One logical change per migration
   - **Test migrations** — Always test rollbacks
   - **Use descriptive names** — `create_users_table` not `migration1`

### Expected Result

After completing this step, you'll understand that:

- Laravel migrations work similarly to Django migrations
- Both use timestamp-based versioning (Django uses numbers, Laravel uses timestamps)
- Both track which migrations have been applied
- Both support rolling back migrations
- Laravel's migration syntax is more fluent/expressive
- Laravel migrations are more explicit about up/down operations

### Why It Works

Migrations provide version control for database schemas, allowing teams to share schema changes and deploy consistently. Laravel's migration system is built on top of a database abstraction layer, making it database-agnostic. The `up()` method defines what happens when migrating forward, while `down()` defines the rollback behavior. Laravel tracks migrations in a `migrations` table, similar to Django's `django_migrations` table.

### Troubleshooting

- **"Migration already exists"** — Laravel uses timestamps in migration filenames. If you create a migration at the same second, you might get a conflict. Wait a second or manually adjust the timestamp.
- **"Migration not found"** — Ensure migrations are in `database/migrations/` directory. Check file naming: `YYYY_MM_DD_HHMMSS_description.php`.
- **"Can't rollback"** — Ensure the `down()` method is properly implemented. Laravel requires both `up()` and `down()` methods for rollbacks to work.
- **"Foreign key constraint fails"** — Roll back migrations in reverse order. Use `php artisan migrate:rollback --step=N` to rollback multiple migrations.
- **"Migration already exists"** — Laravel uses timestamps in filenames. If you create migrations at the same second, you might get conflicts. Wait a second or manually adjust the timestamp.
- **"Table already exists"** — Use `php artisan migrate:fresh` to drop all tables and re-run migrations (development only!). Or check `migrations` table to see which migrations have run.

## Step 3: Testing with PHPUnit - Laravel's Testing Framework (~20 min)

### Goal

Learn PHPUnit testing and understand how it compares to pytest and unittest.

### Actions

1. **Test Structure Comparison**

   Here's how test files compare:

   **pytest (Python):**

   ```python
   # filename: tests/test_user.py
   import pytest
   from django.contrib.auth import get_user_model

   User = get_user_model()

   def test_user_creation():
       user = User.objects.create(
           name='John Doe',
           email='john@example.com'
       )
       assert user.name == 'John Doe'
       assert user.email == 'john@example.com'

   def test_user_str():
       user = User(name='John Doe', email='john@example.com')
       assert str(user) == 'John Doe'
   ```

   **PHPUnit (Laravel):**

   ```php
   # filename: tests/Feature/UserTest.php
   <?php

   namespace Tests\Feature;

   use Tests\TestCase;
   use App\Models\User;

   class UserTest extends TestCase
   {
       public function test_user_creation(): void
       {
           $user = User::create([
               'name' => 'John Doe',
               'email' => 'john@example.com',
           ]);

           $this->assertEquals('John Doe', $user->name);
           $this->assertEquals('john@example.com', $user->email);
       }

       public function test_user_to_string(): void
       {
           $user = new User([
               'name' => 'John Doe',
               'email' => 'john@example.com',
           ]);

           $this->assertEquals('John Doe', (string) $user);
       }
   }
   ```

   The test examples are available in [`phpunit-test.php`](https://github.com/dalehurley/codewithphp/blob/main/code/python-developers-love-php-laravel/chapter-03/phpunit-test.php) and [`pytest-test.py`](https://github.com/dalehurley/codewithphp/blob/main/code/python-developers-love-php-laravel/chapter-03/pytest-test.py).

2. **Feature Tests vs Unit Tests**

   Laravel distinguishes between Feature tests (HTTP/Integration) and Unit tests (isolated):

   **Laravel Feature Test:**

   ```php
   # filename: tests/Feature/UserApiTest.php
   <?php

   namespace Tests\Feature;

   use Tests\TestCase;
   use Illuminate\Foundation\Testing\RefreshDatabase;
   use App\Models\User;

   class UserApiTest extends TestCase
   {
       use RefreshDatabase; // Resets database after each test

       public function test_user_can_be_created_via_api(): void
       {
           $response = $this->postJson('/api/users', [
               'name' => 'John Doe',
               'email' => 'john@example.com',
               'password' => 'password123',
           ]);

           $response->assertStatus(201)
                    ->assertJson([
                        'name' => 'John Doe',
                        'email' => 'john@example.com',
                    ]);

           // Assert database has the user
           $this->assertDatabaseHas('users', [
               'email' => 'john@example.com',
           ]);
       }
   }
   ```

   ::: tip Laravel Testing Helpers
   Laravel provides many testing helpers beyond PHPUnit's standard assertions. Use `$response->assertStatus()`, `$response->assertJson()`, `$this->assertDatabaseHas()`, and `RefreshDatabase` trait for comprehensive testing. These helpers make tests more readable and maintainable than raw PHPUnit assertions.
   :::

   **Laravel Unit Test:**

   ```php
   # filename: tests/Unit/UserServiceTest.php
   <?php

   namespace Tests\Unit;

   use Tests\TestCase;
   use App\Services\UserService;

   class UserServiceTest extends TestCase
   {
       public function test_user_service_creates_user(): void
       {
           $service = new UserService();
           $user = $service->createUser('John Doe', 'john@example.com');

           $this->assertEquals('John Doe', $user->name);
       }
   }
   ```

   **pytest equivalent:**

   ```python
   # Feature test (using Django test client)
   def test_user_can_be_created_via_api(client):
       response = client.post('/api/users/', {
           'name': 'John Doe',
           'email': 'john@example.com',
       })
       assert response.status_code == 201
       assert response.json()['name'] == 'John Doe'

   # Unit test (isolated)
   def test_user_service_creates_user():
       service = UserService()
       user = service.create_user('John Doe', 'john@example.com')
       assert user.name == 'John Doe'
   ```

3. **Assertions Comparison**

   Both frameworks provide similar assertion methods:

   | pytest | PHPUnit | Purpose |
   |--------|---------|---------|
   | `assert x == y` | `$this->assertEquals($x, $y)` | Equality assertion |
   | `assert x is None` | `$this->assertNull($x)` | Null assertion |
   | `assert x in list` | `$this->assertContains($x, $list)` | Contains assertion |
   | `assert response.status_code == 200` | `$response->assertStatus(200)` | HTTP status |
   | `assert 'text' in response.content` | `$response->assertSee('text')` | Content assertion |

4. **Running Tests**

   Both frameworks provide command-line test runners:

   **pytest (Python):**

   ```bash
   # Run all tests
   pytest

   # Run specific test file
   pytest tests/test_user.py

   # Run specific test
   pytest tests/test_user.py::test_user_creation

   # Run with coverage
   pytest --cov=app tests/
   ```

   **PHPUnit (Laravel):**

   ```bash
   # Run all tests
   php artisan test

   # Run specific test file
   php artisan test tests/Feature/UserTest.php

   # Run specific test
   php artisan test --filter test_user_creation

   # Run with coverage
   php artisan test --coverage
   ```

5. **Database Testing**

   Laravel provides database testing helpers:

   ```php
   # filename: tests/Feature/UserTest.php
   <?php

   namespace Tests\Feature;

   use Tests\TestCase;
   use Illuminate\Foundation\Testing\RefreshDatabase;
   use App\Models\User;

   class UserTest extends TestCase
   {
       use RefreshDatabase; // Resets database after each test

       public function test_user_can_be_created(): void
       {
           $user = User::factory()->create([
               'name' => 'John Doe',
           ]);

           $this->assertDatabaseHas('users', [
               'name' => 'John Doe',
           ]);
       }
   }
   ```

   **Django equivalent:**

   ```python
   # filename: tests/test_user.py
   from django.test import TestCase
   from django.contrib.auth import get_user_model

   User = get_user_model()

   class UserTest(TestCase):
       def test_user_can_be_created(self):
           user = User.objects.create(name='John Doe')
           self.assertTrue(User.objects.filter(name='John Doe').exists())
   ```

### Expected Result

After completing this step, you'll understand that:

- PHPUnit is Laravel's testing framework, equivalent to pytest/unittest
- Laravel distinguishes Feature tests (HTTP) from Unit tests (isolated)
- PHPUnit assertions are method-based (`$this->assertEquals()`), while pytest uses functions (`assert x == y`)
- Laravel provides database testing helpers (`RefreshDatabase`, `assertDatabaseHas`)
- Both frameworks support test discovery and filtering
- Laravel's `php artisan test` is more integrated than pytest's standalone runner

### Why It Works

PHPUnit is PHP's standard testing framework, similar to pytest for Python. Laravel extends PHPUnit with additional helpers for HTTP testing, database testing, and assertions. The `RefreshDatabase` trait automatically resets the database between tests, similar to Django's `TestCase`. Laravel's test runner (`php artisan test`) wraps PHPUnit with Laravel-specific features like automatic environment detection and better error reporting.

### Troubleshooting

- **"Test database not found"** — Laravel uses a separate test database. Ensure `.env.testing` exists or set `DB_DATABASE` in `phpunit.xml`. Laravel will create the database automatically if it doesn't exist.
- **"Migration errors in tests"** — Use `RefreshDatabase` trait to ensure migrations run. Check that `database/migrations` directory contains all migrations.
- **"Factory not found"** — Ensure factories are in `database/factories/` directory and properly namespaced. Use `php artisan make:factory ModelFactory` to create factories.
- **"Test takes too long"** — Use `RefreshDatabase` instead of `DatabaseMigrations` for faster tests. Consider using in-memory SQLite for unit tests.

## Step 4: Seeders and Factories - Test Data Management (~15 min)

### Goal

Understand Laravel seeders and factories, and how they compare to Django fixtures and Faker.

### Actions

1. **Creating Seeders**

   Seeders populate the database with initial or test data:

   **Django Fixture:**

   ```json
   # filename: myapp/fixtures/users.json
   [
       {
           "model": "auth.user",
           "pk": 1,
           "fields": {
               "name": "John Doe",
               "email": "john@example.com"
           }
       }
   ]
   ```

   **Laravel Seeder:**

   ```php
   # filename: database/seeders/UserSeeder.php
   <?php

   namespace Database\Seeders;

   use Illuminate\Database\Seeder;
   use App\Models\User;

   class UserSeeder extends Seeder
   {
       public function run(): void
       {
           User::create([
               'name' => 'John Doe',
               'email' => 'john@example.com',
           ]);
       }
   }
   ```

   **Creating seeders:**

   ```bash
   # Django: Create JSON file manually
   # Laravel: Use Artisan generator
   php artisan make:seeder UserSeeder
   ```

   The seeder examples are available in [`laravel-seeder.php`](https://github.com/dalehurley/codewithphp/blob/main/code/python-developers-love-php-laravel/chapter-03/laravel-seeder.php) and [`django-fixture.json`](https://github.com/dalehurley/codewithphp/blob/main/code/python-developers-love-php-laravel/chapter-03/django-fixture.json).

2. **Running Seeders**

   Both frameworks provide ways to load seed data:

   **Django (Python):**

   ```bash
   # Load fixture
   python manage.py loaddata users.json

   # Load all fixtures
   python manage.py loaddata *
   ```

   **Laravel (PHP):**

   ```bash
   # Run specific seeder
   php artisan db:seed --class=UserSeeder

   # Run all seeders
   php artisan db:seed

   # Migrate and seed
   php artisan migrate --seed
   ```

3. **Using Factories for Test Data**

   Factories generate test data programmatically:

   **Django Factory (using factory_boy):**

   ```python
   # filename: myapp/factories.py
   import factory
   from django.contrib.auth import get_user_model

   User = get_user_model()

   class UserFactory(factory.django.DjangoModelFactory):
       class Meta:
           model = User

       name = factory.Faker('name')
       email = factory.Faker('email')
   ```

   **Laravel Factory:**

   ```php
   # filename: database/factories/UserFactory.php
   <?php

   namespace Database\Factories;

   use App\Models\User;
   use Illuminate\Database\Eloquent\Factories\Factory;
   use Illuminate\Support\Str;

   class UserFactory extends Factory
   {
       protected $model = User::class;

       public function definition(): array
       {
           return [
               'name' => fake()->name(),
               'email' => fake()->unique()->safeEmail(),
               'email_verified_at' => now(),
               'password' => bcrypt('password'), // Default password
               'remember_token' => Str::random(10),
           ];
       }

       /**
        * Indicate that the user's email should be unverified.
        */
       public function unverified(): static
       {
           return $this->state(fn (array $attributes) => [
               'email_verified_at' => null,
           ]);
       }
   }
   ```

   ::: tip Factory States
   Laravel factories support "states" that modify the default attributes. Use `User::factory()->unverified()->create()` to create an unverified user. This is more flexible than Django's factory_boy, which requires separate factory classes for variations.
   :::

   **Using factories in tests:**

   **Django:**

   ```python
   # In a test
   user = UserFactory.create(name='John Doe')
   users = UserFactory.create_batch(10)
   ```

   **Laravel:**

   ```php
   // In a test
   $user = User::factory()->create(['name' => 'John Doe']);
   $users = User::factory()->count(10)->create();
   ```

   The factory examples are available in [`laravel-factory.php`](https://github.com/dalehurley/codewithphp/blob/main/code/python-developers-love-php-laravel/chapter-03/laravel-factory.php).

4. **DatabaseSeeder - Main Seeder**

   Laravel's `DatabaseSeeder` is the main entry point for seeding:

   ```php
   # filename: database/seeders/DatabaseSeeder.php
   <?php

   namespace Database\Seeders;

   use Illuminate\Database\Seeder;

   class DatabaseSeeder extends Seeder
   {
       public function run(): void
       {
           $this->call([
               UserSeeder::class,
               PostSeeder::class,
           ]);
       }
   }
   ```

   **Django equivalent (using management command):**

   ```python
   # filename: management/commands/seed_data.py
   from django.core.management.base import BaseCommand
   from myapp.factories import UserFactory, PostFactory

   class Command(BaseCommand):
       def handle(self, *args, **options):
           UserFactory.create_batch(10)
           PostFactory.create_batch(20)
   ```

### Expected Result

After completing this step, you'll understand that:

- Laravel seeders are programmatic (PHP code), while Django fixtures are data files (JSON/YAML)
- Laravel factories are built-in, while Django uses third-party `factory_boy`
- Both frameworks support generating test data programmatically
- Laravel's factory syntax is more fluent (`User::factory()->create()`)
- Seeders run via `php artisan db:seed`, similar to Django's `loaddata` command
- Laravel's `DatabaseSeeder` coordinates multiple seeders

### Why It Works

Seeders provide a way to populate databases with initial or test data. Laravel's seeders are PHP classes, making them more flexible than JSON fixtures. Factories generate test data programmatically using Faker, similar to Python's `factory_boy`. Laravel's factories are integrated into the framework, while Django requires `factory_boy` as a third-party package. The `DatabaseSeeder` class acts as the main entry point, allowing you to coordinate multiple seeders.

### Troubleshooting

- **"Seeder class not found"** — Ensure seeders are in `database/seeders/` directory and properly namespaced. Run `composer dump-autoload` to refresh autoloader.
- **"Factory not found"** — Ensure factories are in `database/factories/` directory. Use `php artisan make:factory UserFactory` to create factories. Run `composer dump-autoload` after creating factories.
- **"Duplicate entry error"** — Use `fake()->unique()` for unique fields, or clear database before seeding: `php artisan migrate:fresh --seed`.
- **"Faker not working"** — Laravel includes Faker by default. If you see errors, ensure you're using `fake()` helper or `$this->faker` in factories. In Laravel 11+, use `fake()` instead of `$this->faker`.

## Step 5: Laravel Conventions - "Conventions Over Configuration" (~10 min)

### Goal

Understand Laravel's naming conventions and how they compare to Django/Flask conventions.

### Actions

1. **File Naming Conventions**

   Laravel follows strict naming conventions:

   | Laravel | Django | Flask | Example |
   |---------|--------|-------|---------|
   | `User.php` (Model) | `models.py` (contains `User`) | `models.py` (contains `User`) | Model class |
   | `UserController.php` | `views.py` (contains `UserView`) | `views.py` (function-based) | Controller/View |
   | `2024_01_01_create_users.php` | `0001_initial.py` | N/A (no migrations) | Migration |
   | `UserSeeder.php` | `fixtures/users.json` | N/A | Seeder/Fixture |
   | `UserFactory.php` | `factories.py` (factory_boy) | N/A | Factory |

2. **Directory Structure Comparison**

   **Laravel Structure:**

   ```
   app/
   ├── Models/
   │   └── User.php
   ├── Http/
   │   ├── Controllers/
   │   │   └── UserController.php
   │   └── Middleware/
   ├── Console/
   │   └── Commands/
   database/
   ├── migrations/
   ├── seeders/
   └── factories/
   tests/
   ├── Feature/
   └── Unit/
   ```

   **Django Structure:**

   ```
   myapp/
   ├── models.py
   ├── views.py
   ├── urls.py
   ├── admin.py
   migrations/
   ├── 0001_initial.py
   tests/
   ├── test_models.py
   fixtures/
   └── users.json
   ```

3. **Naming Patterns**

   Laravel uses specific naming patterns:

   **Models:**

   ```php
   // Laravel: Singular, PascalCase
   class User extends Model {}
   // Table: 'users' (plural, snake_case)

   // Django: Singular, PascalCase
   class User(models.Model):
       class Meta:
           db_table = 'users'  # Optional, defaults to 'myapp_user'
   ```

   **Controllers:**

   ```php
   // Laravel: PascalCase with 'Controller' suffix
   class UserController extends Controller {}

   // Django: Function-based or class-based
   def user_list(request): pass
   # or
   class UserListView(ListView): pass
   ```

   **Migrations:**

   ```php
   // Laravel: Timestamp_description.php
   // 2024_01_01_120000_create_users_table.php

   // Django: Numbered_description.py
   // 0001_initial.py
   ```

4. **Convention Benefits**

   Laravel's conventions reduce configuration:

   **Laravel (Convention-based):**

   ```php
   // Model automatically knows table name is 'users'
   class User extends Model {}

   // Route automatically maps to UserController@index
   Route::get('/users', [UserController::class, 'index']);
   ```

   **Django (More explicit):**

   ```python
   # Model explicitly defines table name (optional)
   class User(models.Model):
       class Meta:
           db_table = 'users'

   # URL explicitly maps to view
   path('users/', views.user_list, name='user_list')
   ```

### Expected Result

After completing this step, you'll understand that:

- Laravel follows strict naming conventions (singular models, plural tables)
- Laravel's directory structure is more organized (separate folders for Models, Controllers)
- Django uses file-based organization (`models.py`, `views.py`)
- Laravel's conventions reduce boilerplate configuration
- Both frameworks follow "conventions over configuration" philosophy
- Laravel's conventions are more opinionated than Django's

### Why It Works

Laravel's conventions reduce cognitive load by providing predictable patterns. If you know a model is `User`, you know the table is `users`, the controller is `UserController`, and the migration follows a timestamp pattern. This reduces configuration and makes code more discoverable. Django also follows conventions but is less opinionated, allowing more flexibility at the cost of more configuration.

### Troubleshooting

- **"Model not found"** — Ensure models are in `app/Models/` directory and properly namespaced (`App\Models\User`). Use `composer dump-autoload` to refresh autoloader.
- **"Table not found"** — Laravel automatically pluralizes model names. If your table name differs, override `$table` property: `protected $table = 'custom_table_name';`
- **"Controller not found"** — Ensure controllers are in `app/Http/Controllers/` directory. Use `php artisan make:controller UserController` to create controllers. Run `composer dump-autoload` after creating controllers.
- **"Route not found"** — Check route naming. Laravel uses `Route::get('/users', [UserController::class, 'index'])` syntax. Ensure controller method exists. Use `php artisan route:list` to see all registered routes.
- **"Table name doesn't match"** — Laravel automatically pluralizes model names. If your table name differs, override `$table` property: `protected $table = 'custom_table_name';`

## Step 6: Code Generation - Laravel's `make:*` Commands (~10 min)

### Goal

Understand Laravel's code generation commands and how they compare to Django's manual file creation approach.

### Actions

1. **Code Generation Overview**

   Laravel's `make:*` commands generate boilerplate code automatically, saving significant time. Django doesn't have equivalent generators—you create files manually:

   **Django (Python) - Manual Creation:**

   ```bash
   # Django: Create files manually
   # 1. Create models.py file
   # 2. Create views.py file
   # 3. Create urls.py file
   # 4. Create forms.py file (if needed)
   # 5. Write all boilerplate code yourself
   ```

   **Laravel (PHP) - Code Generation:**

   ```bash
   # Laravel: Generate files with Artisan
   php artisan make:model Post
   php artisan make:controller PostController
   php artisan make:request StorePostRequest
   php artisan make:middleware CheckAge
   ```

2. **Model Generation**

   Laravel can generate models with migrations and factories:

   **Laravel:**

   ```bash
   # Generate model only
   php artisan make:model Post

   # Generate model with migration
   php artisan make:model Post -m

   # Generate model with migration and factory
   php artisan make:model Post -m -f

   # Generate model with migration, factory, seeder, and controller
   php artisan make:model Post -a
   ```

   **Django equivalent:**

   ```bash
   # Django: Create model manually in models.py
   # Then create migration
   python manage.py makemigrations
   ```

   The generated Laravel model includes proper namespacing, type hints, and follows conventions:

   ```php
   # Generated: app/Models/Post.php
   <?php

   namespace App\Models;

   use Illuminate\Database\Eloquent\Model;

   class Post extends Model
   {
       // Model code here
   }
   ```

3. **Controller Generation**

   Laravel can generate controllers with CRUD methods:

   **Laravel:**

   ```bash
   # Generate basic controller
   php artisan make:controller PostController

   # Generate resource controller (CRUD methods)
   php artisan make:controller PostController --resource

   # Generate API resource controller (no create/edit views)
   php artisan make:controller PostController --api
   ```

   **Django equivalent:**

   ```python
   # Django: Create views.py manually
   # Or use class-based views
   from django.views.generic import ListView, DetailView

   class PostListView(ListView):
       model = Post
   ```

   The `--resource` flag generates all CRUD methods: `index()`, `create()`, `store()`, `show()`, `edit()`, `update()`, `destroy()`.

4. **Form Request Validation**

   Laravel generates form request classes for validation:

   **Laravel:**

   ```bash
   php artisan make:request StorePostRequest
   ```

   **Generated file:**

   ```php
   # app/Http/Requests/StorePostRequest.php
   <?php

   namespace App\Http\Requests;

   use Illuminate\Foundation\Http\FormRequest;

   class StorePostRequest extends FormRequest
   {
       public function authorize(): bool
       {
           return true;
       }

       public function rules(): array
       {
           return [
               'title' => 'required|string|max:255',
               'content' => 'required|string',
           ];
       }
   }
   ```

   **Django equivalent:**

   ```python
   # Django: Create forms.py manually
   from django import forms

   class PostForm(forms.ModelForm):
       class Meta:
           model = Post
           fields = ['title', 'content']
   ```

5. **Middleware Generation**

   Laravel generates middleware classes:

   **Laravel:**

   ```bash
   php artisan make:middleware CheckAge
   ```

   **Generated file:**

   ```php
   # app/Http/Middleware/CheckAge.php
   <?php

   namespace App\Http\Middleware;

   use Closure;
   use Illuminate\Http\Request;

   class CheckAge
   {
       public function handle(Request $request, Closure $next): mixed
       {
           // Middleware logic here
           return $next($request);
       }
   }
   ```

   **Django equivalent:**

   ```python
   # Django: Create middleware manually
   class CheckAgeMiddleware:
       def __init__(self, get_response):
           self.get_response = get_response
   ```

6. **API Resource Generation**

   Laravel can generate API resources for transforming model data:

   **Laravel:**

   ```bash
   php artisan make:resource PostResource
   ```

   This generates a resource class for formatting API responses, something Django REST Framework handles differently.

### Expected Result

After completing this step, you'll understand that:

- Laravel's `make:*` commands generate boilerplate code automatically
- Django requires manual file creation—no equivalent generators
- Laravel's code generation follows conventions, reducing errors
- Flags like `-m`, `-f`, `--resource` create related files together
- Code generation is a major productivity advantage in Laravel

### Why It Works

Laravel's code generation commands follow the framework's conventions, ensuring consistency across projects. The `make:*` commands create properly namespaced, type-hinted code that follows PSR standards. This reduces boilerplate writing and prevents common mistakes. Django's approach requires more manual work but gives you complete control over file structure.

### Troubleshooting

- **"Command not found"** — Ensure you're using Laravel 11.x. Some `make:*` commands may vary by version. Use `php artisan list` to see available commands.
- **"Generated file has wrong namespace"** — Laravel auto-detects namespaces based on directory structure. Ensure you're running commands from the project root.
- **"Can't find generated file"** — Generated files go to standard Laravel directories: `app/Models/`, `app/Http/Controllers/`, etc. Check these directories after generation.

## Exercises

Test your understanding of Laravel's developer experience tools by completing these exercises:

### Exercise 1: Create a Custom Artisan Command (~15 min)

**Goal**: Practice creating custom Artisan commands and compare to Django management commands.

**Requirements:**

1. Create a custom Artisan command called `users:list` that displays all users in a table format
2. The command should accept an optional `--limit` argument (default: 10)
3. Display users in a formatted table with columns: ID, Name, Email, Created At
4. Compare your implementation to a Django management command that does the same

**Validation**: Test your implementation:

```bash
# Run the command
php artisan users:list

# Run with limit
php artisan users:list --limit=5

# Expected: Formatted table with user data
```

**Reference**: See [`artisan-commands.php`](https://github.com/dalehurley/codewithphp/blob/main/code/python-developers-love-php-laravel/chapter-03/artisan-commands.php) for examples.

### Exercise 2: Write a Migration and Seeder (~20 min)

**Goal**: Practice creating migrations and seeders, comparing to Django migrations and fixtures.

**Requirements:**

1. Create a migration for a `posts` table with fields:
   - `id` (primary key)
   - `title` (string, required)
   - `content` (text)
   - `user_id` (foreign key to users table)
   - `created_at` and `updated_at` (timestamps)

2. Create a `PostSeeder` that creates 10 posts with fake data
3. Update `DatabaseSeeder` to call `PostSeeder`
4. Run the migration and seeder
5. Compare your implementation to Django migrations and fixtures

**Validation**: Verify your implementation:

```bash
# Run migration
php artisan migrate

# Run seeder
php artisan db:seed --class=PostSeeder

# Check database
php artisan tinker
>>> Post::count() // Should return 10
```

**Reference**: See [`laravel-migration.php`](https://github.com/dalehurley/codewithphp/blob/main/code/python-developers-love-php-laravel/chapter-03/laravel-migration.php) and [`laravel-seeder.php`](https://github.com/dalehurley/codewithphp/blob/main/code/python-developers-love-php-laravel/chapter-03/laravel-seeder.php) for examples.

### Exercise 3: Write a PHPUnit Test (~15 min)

**Goal**: Practice writing PHPUnit tests and compare to pytest tests.

**Requirements:**

1. Create a `PostTest` feature test that:
   - Tests creating a post via API
   - Tests retrieving a post
   - Tests updating a post
   - Tests deleting a post

2. Use factories to create test data
3. Use `RefreshDatabase` trait for database isolation
4. Compare your implementation to pytest tests

**Validation**: Run your tests:

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter PostTest

# Expected: All tests pass
```

**Reference**: See [`phpunit-test.php`](https://github.com/dalehurley/codewithphp/blob/main/code/python-developers-love-php-laravel/chapter-03/phpunit-test.php) for examples.

### Exercise 4: Use Code Generation Commands (~10 min)

**Goal**: Practice using Laravel's `make:*` commands to generate boilerplate code.

**Requirements:**

1. Generate a `Post` model with migration and factory:
   ```bash
   php artisan make:model Post -m -f
   ```

2. Generate a resource controller for posts:
   ```bash
   php artisan make:controller PostController --resource
   ```

3. Generate a form request for storing posts:
   ```bash
   php artisan make:request StorePostRequest
   ```

4. Inspect the generated files and compare to Django's manual file creation approach

**Validation**: Verify the generated files:

```bash
# Check model was created
ls app/Models/Post.php

# Check migration was created
ls database/migrations/*_create_posts_table.php

# Check controller was created
ls app/Http/Controllers/PostController.php

# Check request was created
ls app/Http/Requests/StorePostRequest.php
```

**Reference**: See code generation examples in [`artisan-commands.php`](https://github.com/dalehurley/codewithphp/blob/main/code/python-developers-love-php-laravel/chapter-03/artisan-commands.php).

## Wrap-up

Congratulations! You've completed the Laravel developer experience chapter. You now understand:

- ✓ Artisan CLI commands and how they compare to Django's `manage.py`
- ✓ Laravel migrations vs Django migrations workflow
- ✓ PHPUnit testing vs pytest workflows
- ✓ Laravel seeders and factories vs Django fixtures and factory_boy
- ✓ Laravel conventions vs Django/Flask conventions
- ✓ Laravel's "conventions over configuration" philosophy
- ✓ How to create custom Artisan commands, migrations, seeders, and tests
- ✓ Laravel's code generation commands (`make:*`) and their productivity benefits
- ✓ Cache and configuration management with Artisan commands
- ✓ Route listing and development server options

### What You've Achieved

You've built a comprehensive understanding of Laravel's developer experience tools. You can see that Laravel's tools mirror Python's best practices while adding their own delightful touches. Artisan CLI provides more built-in commands than Django's `manage.py`. Laravel's code generation commands (`make:*`) are a major productivity advantage—Django has no equivalent generators, requiring manual file creation. Laravel migrations work similarly to Django migrations but with more expressive syntax. PHPUnit testing is equivalent to pytest, with Laravel-specific helpers for HTTP and database testing. Seeders and factories provide flexible test data management, and Laravel's conventions reduce configuration overhead. Cache and configuration management commands help optimize applications for production.

### Next Steps

In **Chapter 04**, we'll explore PHP syntax and language differences for Python developers. You'll learn:

- Variable prefixes (`$`) and how they compare to Python
- OOP differences between PHP and Python
- Type declarations and how they compare to Python type hints
- Namespaces vs Python modules
- Side-by-side syntax comparisons

Your understanding of Laravel's developer experience tools will help you appreciate PHP's syntax choices and how they contribute to Laravel's developer-friendly features. The conventions you've learned here (naming patterns, directory structure) will make PHP syntax feel more natural.

## Code Examples

All code examples from this chapter are available in the [`code/chapter-03/`](https://github.com/dalehurley/codewithphp/tree/main/docs/series/python-developers-love-php-laravel/code/chapter-03) directory:

- **Artisan Commands**: [`artisan-commands.php`](https://github.com/dalehurley/codewithphp/blob/main/code/python-developers-love-php-laravel/chapter-03/artisan-commands.php) — Custom Artisan command examples
- **Django manage.py**: [`django-manage.py`](https://github.com/dalehurley/codewithphp/blob/main/code/python-developers-love-php-laravel/chapter-03/django-manage.py) — Django management command comparison
- **Laravel Migration**: [`laravel-migration.php`](https://github.com/dalehurley/codewithphp/blob/main/code/python-developers-love-php-laravel/chapter-03/laravel-migration.php) — Laravel migration example
- **Django Migration**: [`django-migration.py`](https://github.com/dalehurley/codewithphp/blob/main/code/python-developers-love-php-laravel/chapter-03/django-migration.py) — Django migration comparison
- **PHPUnit Test**: [`phpunit-test.php`](https://github.com/dalehurley/codewithphp/blob/main/code/python-developers-love-php-laravel/chapter-03/phpunit-test.php) — PHPUnit test example
- **pytest Test**: [`pytest-test.py`](https://github.com/dalehurley/codewithphp/blob/main/code/python-developers-love-php-laravel/chapter-03/pytest-test.py) — pytest comparison example
- **Laravel Seeder**: [`laravel-seeder.php`](https://github.com/dalehurley/codewithphp/blob/main/code/python-developers-love-php-laravel/chapter-03/laravel-seeder.php) — Laravel seeder example
- **Django Fixture**: [`django-fixture.json`](https://github.com/dalehurley/codewithphp/blob/main/code/python-developers-love-php-laravel/chapter-03/django-fixture.json) — Django fixture comparison
- **Laravel Factory**: [`laravel-factory.php`](https://github.com/dalehurley/codewithphp/blob/main/code/python-developers-love-php-laravel/chapter-03/laravel-factory.php) — Laravel factory example

See the [README.md](https://github.com/dalehurley/codewithphp/blob/main/code/python-developers-love-php-laravel/chapter-03/README.md) for detailed instructions on running each example.

<ChapterCheckbox seriesId="python-developers-love-php-laravel" chapterId="03-laravel-developer-experience-productivity-tools" />

## Further Reading

To deepen your understanding:

- [Laravel Artisan Documentation](https://laravel.com/docs/artisan) — Complete Artisan CLI reference
- [Laravel Migrations Guide](https://laravel.com/docs/migrations) — Database migrations documentation
- [PHPUnit Documentation](https://phpunit.de/documentation.html) — PHPUnit testing framework
- [Laravel Testing Guide](https://laravel.com/docs/testing) — Laravel-specific testing features
- [Laravel Seeders Documentation](https://laravel.com/docs/seeders) — Database seeding guide
- [Laravel Factories Documentation](https://laravel.com/docs/eloquent-factories) — Model factories guide
- [Django Management Commands](https://docs.djangoproject.com/en/stable/howto/custom-management-commands/) — Django management commands reference
- [pytest Documentation](https://docs.pytest.org/) — pytest testing framework

---

::: tip Ready to Explore PHP Syntax?
Head to [Chapter 04: The PHP Syntax & Language Differences for Python Devs](/series/python-developers-love-php-laravel/chapters/04-php-syntax-language-differences-for-python-devs) to learn about PHP syntax and how it compares to Python!
:::
