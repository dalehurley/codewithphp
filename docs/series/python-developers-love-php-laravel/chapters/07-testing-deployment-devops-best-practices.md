---
title: "07: Testing, Deployment, DevOps: Best Practices You Know + Laravel Workflow"
description: "Compare pytest vs PHPUnit, CI/CD workflows, Docker, Laravel Forge/Vapor, and queues vs Celery—professional development practices."
series: "python-developers-love-php-laravel"
chapter: 7
order: 7
difficulty: "Intermediate"
prerequisites:
  - "/series/python-developers-love-php-laravel/chapters/06-building-rest-apis-integrations-python-to-laravel"
---

![Testing, Deployment, DevOps](/images/python-developers-love-php-laravel/chapter-07-testing-deployment-devops-best-practices-hero-full.webp)

# Chapter 07: Testing, Deployment, DevOps: Best Practices You Know + Laravel Workflow

## Overview

In Chapter 06, you mastered building REST APIs in Laravel—routes, controllers, resources, validation, authentication, and external integrations. You now understand how to create production-ready APIs. But building an API is only half the battle. Professional development requires testing your code, automating deployments, containerizing applications, and managing background jobs. This chapter is where your Python DevOps knowledge becomes your greatest asset.

If you've worked with pytest, GitHub Actions, Docker, Celery, or deployment platforms like Heroku or Railway, you already understand the fundamentals: write tests to catch bugs, automate CI/CD pipelines, containerize for consistency, queue jobs for async processing, and deploy to production. Laravel's testing, deployment, and DevOps tooling follows the same principles with PHP syntax. The concepts are identical: pytest becomes PHPUnit, Celery becomes Laravel Queues, GitHub Actions workflows work the same way, and Docker is Docker. The only difference is syntax: `@pytest.fixture` becomes `setUp()`, and `@celery.task` becomes `implements ShouldQueue`.

This chapter is a **comprehensive guide to professional development practices in Laravel**. We'll compare every major DevOps tool to Python equivalents, showing you Python code you know, then demonstrating the Laravel equivalent. You'll master testing with PHPUnit (comparing to pytest), CI/CD workflows with GitHub Actions, Docker containerization, deployment platforms (Laravel Forge/Vapor vs Heroku/Railway), and background jobs with Laravel Queues (comparing to Celery). By the end, you'll see that Laravel's DevOps tooling isn't fundamentally different—it's the same professional practices with PHP syntax and Laravel's delightful developer experience.

## Prerequisites

Before starting this chapter, you should have:

- Completion of [Chapter 06](/series/python-developers-love-php-laravel/chapters/06-building-rest-apis-integrations-python-to-laravel) or equivalent understanding of Laravel APIs
- Laravel 11.x installed (or ability to follow along with code examples)
- Experience with pytest or unittest (Python testing)
- Familiarity with GitHub Actions or similar CI/CD platforms
- Basic understanding of Docker and containerization
- Experience with background job queues (Celery preferred for comparison)
- Knowledge of deployment platforms (Heroku, Railway, or similar)
- **Estimated Time**: ~135 minutes

**Verify your setup:**

```bash
# Check PHP version (should show PHP 8.4+)
php --version

# Check Composer is installed
composer --version

# If you have Laravel installed, verify it works
php artisan --version

# Expected output: Laravel Framework 11.x.x (or similar)

# Check if PHPUnit is available (comes with Laravel)
php artisan test --version

# Check Docker (optional, for Step 4)
docker --version
```

## What You'll Build

By the end of this chapter, you will have:

- Side-by-side comparison examples (pytest/Celery/GitHub Actions → PHPUnit/Laravel Queues/GitHub Actions) for testing, CI/CD, Docker, deployment, and queues
- Understanding of PHPUnit vs pytest, including test structure, assertions, fixtures, and mocking
- Knowledge of Laravel's testing helpers (HTTP testing, database transactions, factories)
- Mastery of GitHub Actions workflows for Laravel (comparing to Python workflows)
- Ability to containerize Laravel applications with Docker (comparing to Python Dockerfiles)
- Understanding of Laravel Forge and Vapor deployment (comparing to Heroku/Railway)
- Working knowledge of Laravel Queues vs Celery, including job creation, queue drivers, and scheduling
- Confidence in professional development practices equivalent to your Python DevOps knowledge

## Quick Start

Want to see how pytest maps to PHPUnit right away? Here's a side-by-side comparison:

**pytest (Python):**

```python
# filename: test_user.py
import pytest

def test_user_creation():
    user = User(name="John Doe", email="john@example.com")
    assert user.name == "John Doe"
    assert user.email == "john@example.com"

def test_user_email_validation():
    with pytest.raises(ValueError):
        User(email="invalid-email")
```

**PHPUnit (Laravel):**

```php
<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use InvalidArgumentException;

class UserTest extends TestCase
{
    public function test_user_creation(): void
    {
        $user = new User([
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);

        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
    }

    public function test_user_email_validation(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new User(['email' => 'invalid-email']);
    }
}
```

See the pattern? Same concepts—test methods, assertions, exceptions—just different syntax! This chapter will show you how every Python testing and DevOps tool translates to Laravel.

## Objectives

- Map pytest test structure and assertions to PHPUnit, understanding Laravel's testing conventions
- Understand Laravel's testing helpers (HTTP testing, database transactions, factories) vs pytest fixtures and Django factories
- Master mocking and faking in Laravel (comparing to pytest mocks and Python's unittest.mock)
- Create GitHub Actions workflows for Laravel (comparing to Python CI/CD pipelines)
- Containerize Laravel applications with Docker (comparing to Python Dockerfile patterns)
- Deploy Laravel applications using Forge and Vapor (comparing to Heroku/Railway deployment)
- Implement background jobs with Laravel Queues (comparing to Celery tasks and scheduling)
- Recognize that professional development practices are universal—only syntax differs between Python and PHP

## Step 1: Testing Fundamentals (~25 min)

### Goal

Understand PHPUnit vs pytest, comparing test structure, assertions, fixtures, and Laravel's HTTP testing capabilities.

### Actions

1. **pytest Test Example** (Python):

The complete pytest example is available in [`pytest-test-example.py`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-07/testing/pytest-test-example.py):

```python
# filename: test_user.py
import pytest
from datetime import datetime

class User:
    def __init__(self, name, email):
        self.name = name
        self.email = email
        self.created_at = datetime.now()

def test_user_creation():
    user = User("John Doe", "john@example.com")
    assert user.name == "John Doe"
    assert user.email == "john@example.com"
    assert user.created_at is not None

def test_user_email_validation():
    with pytest.raises(ValueError, match="Invalid email"):
        if "@" not in "invalid-email":
            raise ValueError("Invalid email")

def test_user_list():
    users = [
        User("John", "john@example.com"),
        User("Jane", "jane@example.com")
    ]
    assert len(users) == 2
    assert users[0].name == "John"
```

2. **PHPUnit Unit Test** (Laravel):

The complete PHPUnit example is available in [`phpunit-test-example.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-07/testing/phpunit-test-example.php):

```php
<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use InvalidArgumentException;

class UserTest extends TestCase
{
    public function test_user_creation(): void
    {
        $user = new User([
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);

        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertNotNull($user->created_at);
    }

    public function test_user_email_validation(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email');

        if (!str_contains('invalid-email', '@')) {
            throw new InvalidArgumentException('Invalid email');
        }
    }

    public function test_user_list(): void
    {
        $users = [
            new User(['name' => 'John', 'email' => 'john@example.com']),
            new User(['name' => 'Jane', 'email' => 'jane@example.com'])
        ];

        $this->assertCount(2, $users);
        $this->assertEquals('John', $users[0]->name);
    }
}
```

3. **Laravel Feature Test (HTTP Testing)** (PHP/Laravel):

The complete Laravel feature test example is available in [`laravel-feature-test.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-07/testing/laravel-feature-test.php):

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_users(): void
    {
        User::factory()->count(3)->create();

        $response = $this->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'email']
                ]
            ])
            ->assertJsonCount(3, 'data');
    }

    public function test_can_create_user(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123'
        ];

        $response = $this->postJson('/api/users', $userData);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'name' => 'John Doe',
                    'email' => 'john@example.com'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com'
        ]);
    }

    public function test_can_update_user(): void
    {
        $user = User::factory()->create();

        $response = $this->putJson("/api/users/{$user->id}", [
            'name' => 'Updated Name'
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name'
        ]);
    }

    public function test_can_delete_user(): void
    {
        $user = User::factory()->create();

        $response = $this->deleteJson("/api/users/{$user->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('users', [
            'id' => $user->id
        ]);
    }
}
```

### Expected Result

You can see the patterns are similar:

- **pytest**: `def test_name()` → **PHPUnit**: `public function test_name(): void`
- **pytest**: `assert value == expected` → **PHPUnit**: `$this->assertEquals($expected, $value)`
- **pytest**: `with pytest.raises()` → **PHPUnit**: `$this->expectException()`
- **pytest**: Manual HTTP testing → **Laravel**: Built-in HTTP testing with `getJson()`, `postJson()`, etc.

### Why It Works

Both testing frameworks follow similar principles:

- **pytest**: Uses Python's assert statement with introspection for detailed failure messages
- **PHPUnit**: Uses assertion methods (`assertEquals`, `assertTrue`, etc.) for explicit comparisons
- **Laravel Feature Tests**: Extend PHPUnit with HTTP testing helpers (`getJson`, `postJson`) and database assertions (`assertDatabaseHas`)

Laravel's `RefreshDatabase` trait automatically migrates and rolls back your database for each test, ensuring test isolation. The `getJson()` and `postJson()` methods automatically set JSON headers and parse JSON responses, making API testing straightforward.

::: tip Running Tests
Run tests with `php artisan test` (Laravel's wrapper) or `vendor/bin/phpunit`. Laravel's test command provides better output and automatically loads environment variables from `.env.testing`.
:::

### Comparison Table

| Feature            | pytest                     | PHPUnit                                  | Laravel Feature Tests                   |
| ------------------ | -------------------------- | ---------------------------------------- | --------------------------------------- |
| **Test Method**    | `def test_name()`          | `public function test_name(): void`      | Same as PHPUnit                         |
| **Assertion**      | `assert value == expected` | `$this->assertEquals($expected, $value)` | Same as PHPUnit                         |
| **Exception Test** | `with pytest.raises()`     | `$this->expectException()`               | Same as PHPUnit                         |
| **HTTP Testing**   | Manual with `requests`     | Manual with Guzzle                       | `$this->getJson()`, `$this->postJson()` |
| **Database**       | Manual setup/teardown      | Manual or `setUp()`                      | `RefreshDatabase` trait                 |
| **Fixtures**       | `@pytest.fixture`          | `setUp()` method                         | `setUp()` or factories                  |

### Troubleshooting

- **"Test not found"** — Make sure test methods start with `test_` or use `@test` annotation. PHPUnit discovers tests by naming convention.
- **"Database connection error"** — Ensure `.env.testing` exists with test database configuration. Laravel uses a separate database for testing.
- **"Class not found"** — Run `composer dump-autoload` to regenerate autoloader. Laravel's test classes must be in `Tests\` namespace.
- **"HTTP test failing"** — Make sure routes exist and middleware is configured correctly. Use `php artisan route:list` to verify routes.

### Laravel HTTP Testing Helpers

Laravel provides many HTTP testing helpers:

```php
// GET request
$response = $this->get('/api/users');
$response->assertStatus(200);

// POST request with JSON
$response = $this->postJson('/api/users', ['name' => 'John']);
$response->assertStatus(201);

// PUT request
$response = $this->putJson('/api/users/1', ['name' => 'Updated']);
$response->assertStatus(200);

// DELETE request
$response = $this->deleteJson('/api/users/1');
$response->assertStatus(204);

// Assert JSON structure
$response->assertJsonStructure(['data' => ['id', 'name']]);

// Assert JSON content
$response->assertJson(['name' => 'John']);

// Assert database state
$this->assertDatabaseHas('users', ['email' => 'john@example.com']);
$this->assertDatabaseMissing('users', ['email' => 'deleted@example.com']);
```

**Compare to Flask/Django testing:**

```python
# Flask
def test_get_users(client):
    response = client.get('/api/users')
    assert response.status_code == 200
    assert len(response.json) == 3

# Django REST
def test_get_users(api_client):
    response = api_client.get('/api/users/')
    assert response.status_code == 200
    assert len(response.data) == 3
```

Laravel's HTTP testing helpers provide a fluent, expressive API similar to Flask's test client but with more built-in assertions.

## Step 2: Test Coverage & Mocking (~20 min)

### Goal

Compare pytest fixtures and mocks to PHPUnit mocks and Laravel's testing features, including factories and fakes.

### Actions

1. **pytest Fixtures and Mocks** (Python):

The complete pytest fixtures and mocks example is available in [`pytest-fixtures-mocks.py`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-07/testing/pytest-fixtures-mocks.py):

```python
# filename: test_user_service.py
import pytest
from unittest.mock import Mock, patch
from datetime import datetime

class UserService:
    def __init__(self, email_service):
        self.email_service = email_service

    def create_user(self, name, email):
        # Simulate user creation
        user = {'id': 1, 'name': name, 'email': email}
        self.email_service.send_welcome_email(email)
        return user

@pytest.fixture
def email_service():
    return Mock()

@pytest.fixture
def user_service(email_service):
    return UserService(email_service)

def test_create_user_sends_email(user_service, email_service):
    user = user_service.create_user("John", "john@example.com")

    assert user['name'] == "John"
    email_service.send_welcome_email.assert_called_once_with("john@example.com")

@patch('requests.get')
def test_external_api_call(mock_get):
    mock_get.return_value.json.return_value = {'status': 'ok'}

    import requests
    response = requests.get('https://api.example.com/data')

    assert response.json()['status'] == 'ok'
    mock_get.assert_called_once_with('https://api.example.com/data')
```

2. **PHPUnit Mocks and Laravel Fakes** (PHP/Laravel):

The complete PHPUnit mocks and fakes example is available in [`phpunit-mocks-fakes.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-07/testing/phpunit-mocks-fakes.php):

```php
<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\UserService;
use App\Services\EmailService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Mockery;

class UserServiceTest extends TestCase
{
    public function test_create_user_sends_email(): void
    {
        // Create mock
        $emailService = Mockery::mock(EmailService::class);
        $emailService->shouldReceive('sendWelcomeEmail')
            ->once()
            ->with('john@example.com');

        // Inject mock
        $userService = new UserService($emailService);

        // Test
        $user = $userService->createUser('John', 'john@example.com');

        $this->assertEquals('John', $user->name);
        Mockery::close();
    }

    public function test_external_api_call_with_fake(): void
    {
        // Fake HTTP client
        Http::fake([
            'api.example.com/*' => Http::response(['status' => 'ok'], 200)
        ]);

        $response = Http::get('https://api.example.com/data');

        $this->assertEquals('ok', $response->json()['status']);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.example.com/data';
        });
    }

    public function test_mail_fake(): void
    {
        Mail::fake();

        // Code that sends email
        Mail::to('user@example.com')->send(new \App\Mail\WelcomeEmail());

        Mail::assertSent(\App\Mail\WelcomeEmail::class, function ($mail) {
            return $mail->hasTo('user@example.com');
        });
    }
}
```

3. **Laravel Model Factories** (PHP/Laravel):

The complete Laravel factories example is available in [`laravel-factories.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-07/testing/laravel-factories.php):

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
        ]);
    }
}

// Usage in tests
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_user(): void
    {
        $user = User::factory()->create();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => $user->email
        ]);
    }

    public function test_can_create_multiple_users(): void
    {
        User::factory()->count(5)->create();

        $this->assertDatabaseCount('users', 5);
    }

    public function test_can_create_unverified_user(): void
    {
        $user = User::factory()->unverified()->create();

        $this->assertNull($user->email_verified_at);
    }

    public function test_can_create_admin_user(): void
    {
        $admin = User::factory()->admin()->create();

        $this->assertEquals('admin', $admin->role);
    }
}
```

### Expected Result

You can see the patterns are similar:

- **pytest**: `@pytest.fixture` → **PHPUnit**: `setUp()` method or dependency injection
- **pytest**: `Mock()` from unittest.mock → **PHPUnit**: `Mockery::mock()` or Laravel fakes
- **pytest**: `@patch` decorator → **Laravel**: `Http::fake()`, `Mail::fake()`, etc.
- **Django**: Factory classes → **Laravel**: Model factories with states

### Why It Works

All testing frameworks provide ways to isolate dependencies:

- **pytest Fixtures**: Reusable setup code that runs before tests, similar to PHPUnit's `setUp()`
- **Python Mocks**: Replace dependencies with controllable objects
- **Laravel Fakes**: Replace facades (Mail, Http, Queue) with test doubles that record interactions
- **Laravel Factories**: Generate test data with realistic defaults, similar to Django factories or Factory Boy

Laravel's fakes are particularly powerful because they work with facades—you can fake Mail, Http, Queue, Storage, and more without dependency injection. Factories provide a fluent API for creating test data with relationships and states.

::: tip Laravel Fakes
Laravel provides fakes for Mail, Http, Queue, Storage, Notification, and more. Use `Mail::fake()` to prevent emails from being sent during tests, `Http::fake()` to mock external API calls, and `Queue::fake()` to test queued jobs synchronously.
:::

### Comparison Table

| Feature           | pytest                      | PHPUnit             | Laravel               |
| ----------------- | --------------------------- | ------------------- | --------------------- |
| **Fixtures**      | `@pytest.fixture`           | `setUp()` method    | `setUp()` or traits   |
| **Mocks**         | `Mock()` from unittest.mock | `Mockery::mock()`   | `Mockery` or fakes    |
| **Patching**      | `@patch` decorator          | Manual injection    | Facade fakes          |
| **Test Data**     | Manual or factories         | Manual or factories | Model factories       |
| **States**        | Factory traits              | Manual              | Factory states        |
| **Relationships** | Factory subfactories        | Manual              | Factory relationships |

### Troubleshooting

- **"Mock not working"** — Make sure you're using `Mockery::close()` in `tearDown()` or use `$this->afterApplicationCreated()` callback. Mockery needs cleanup.
- **"Fake not working"** — Ensure you call `Mail::fake()` or `Http::fake()` before the code that uses them. Fakes must be set up before execution.
- **"Factory not found"** — Run `php artisan make:factory ModelFactory` to create factories. Laravel auto-discovers factories in `Database\Factories` namespace.
- **"State not applying"** — Make sure state methods return `$this->state()` with a closure. States are applied when creating models.

### Laravel Factory Relationships

Factories can create related models:

```php
// UserFactory.php
public function definition(): array
{
    return [
        'name' => fake()->name(),
        'email' => fake()->unique()->safeEmail(),
        'company_id' => Company::factory(), // Creates related company
    ];
}

// Usage
$user = User::factory()
    ->has(Post::factory()->count(3)) // Create 3 posts
    ->create();
```

**Compare to Django factories:**

```python
# Django factory
class UserFactory(factory.django.DjangoModelFactory):
    class Meta:
        model = User

    name = factory.Faker('name')
    company = factory.SubFactory(CompanyFactory)

# Usage
user = UserFactory.create()
user_with_posts = UserFactory.create(posts=PostFactory.create_batch(3))
```

Laravel factories provide a similar fluent API for creating test data with relationships, making it easy to set up complex test scenarios.

## Step 3: CI/CD Workflows (~25 min)

### Goal

Compare GitHub Actions workflows for Python vs Laravel, understanding how to automate testing, linting, and deployment.

### Actions

1. **Python GitHub Actions Workflow**:

The complete Python CI workflow example is available in [`python-ci.yml`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-07/cicd/python-ci.yml):

```yaml
# filename: .github/workflows/python-ci.yml
name: Python CI

on:
  push:
    branches: [main, develop]
  pull_request:
    branches: [main]

jobs:
  test:
    runs-on: ubuntu-latest

    strategy:
      matrix:
        python-version: ["3.10", "3.11", "3.12"]

    steps:
      - uses: actions/checkout@v4

      - name: Set up Python ${{ matrix.python-version }}
        uses: actions/setup-python@v5
        with:
          python-version: ${{ matrix.python-version }}

      - name: Install dependencies
        run: |
          python -m pip install --upgrade pip
          pip install -r requirements.txt
          pip install pytest pytest-cov

      - name: Run tests
        run: |
          pytest --cov=./ --cov-report=xml

      - name: Upload coverage
        uses: codecov/codecov-action@v3
        with:
          file: ./coverage.xml

      - name: Lint with flake8
        run: |
          pip install flake8
          flake8 . --count --select=E9,F63,F7,F82 --show-source --statistics

      - name: Format check with black
        run: |
          pip install black
          black --check .
```

2. **Laravel GitHub Actions Workflow**:

The complete Laravel CI workflow example is available in [`laravel-ci.yml`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-07/cicd/laravel-ci.yml):

```yaml
# filename: .github/workflows/laravel-ci.yml
name: Laravel CI

on:
  push:
    branches: [main, develop]
  pull_request:
    branches: [main]

jobs:
  tests:
    runs-on: ubuntu-latest

    strategy:
      matrix:
        php-version: ["8.2", "8.3", "8.4"]

    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: password
          MYSQL_DATABASE: testing
        ports:
          - 3306:3306
        options: --health-cmd="mysqladmin ping" --health-interval=10s --health-timeout=5s --health-retries=3

    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php-version }}
          extensions: mbstring, xml, mysql, pdo_mysql
          coverage: xdebug

      - name: Copy .env
        run: php -r "file_exists('.env') || copy('.env.example', '.env');"

      - name: Install Dependencies
        run: composer install -q --no-ansi --no-interaction --no-scripts --no-progress --prefer-dist

      - name: Generate key
        run: php artisan key:generate

      - name: Directory Permissions
        run: chmod -R 755 storage bootstrap/cache

      - name: Create Database
        run: |
          mkdir -p database
          touch database/database.sqlite

      - name: Execute tests (Unit and Feature) with PHPUnit
        env:
          DB_CONNECTION: sqlite
          DB_DATABASE: database/database.sqlite
        run: vendor/bin/phpunit

      - name: Run Pint (Code Style)
        run: |
          composer require --dev laravel/pint
          ./vendor/bin/pint --test

      - name: Run Psalm (Static Analysis)
        run: |
          composer require --dev vimeo/psalm
          ./vendor/bin/psalm --no-cache
```

### Expected Result

You can see the patterns are similar:

- **Python**: `setup-python@v5` → **PHP**: `setup-php@v2`
- **Python**: `pytest` → **PHP**: `phpunit`
- **Python**: `flake8` / `black` → **PHP**: `pint` / `psalm`
- **Python**: `pip install -r requirements.txt` → **PHP**: `composer install`
- Both use matrix strategies for multiple versions

### Why It Works

Both workflows follow the same CI/CD principles:

- **Checkout code**: Get the latest code from the repository
- **Setup environment**: Install runtime (Python/PHP) and dependencies
- **Run tests**: Execute test suite with coverage
- **Lint/Format**: Check code style and quality
- **Deploy** (optional): Deploy to staging/production on success

Laravel's workflow includes database setup (SQLite for testing) and key generation, which are Laravel-specific requirements. Both workflows use matrix strategies to test against multiple versions, ensuring compatibility.

::: tip GitHub Actions Secrets
Store sensitive values (API keys, database passwords) in GitHub Secrets. Access them in workflows with `${{ "{{" }} secrets.SECRET_NAME }}`. Never commit secrets to your repository.
:::

### Comparison Table

| Feature                | Python CI                         | Laravel CI                      |
| ---------------------- | --------------------------------- | ------------------------------- |
| **Setup Action**       | `setup-python@v5`                 | `setup-php@v2`                  |
| **Dependency Manager** | `pip install -r requirements.txt` | `composer install`              |
| **Test Runner**        | `pytest`                          | `phpunit` or `php artisan test` |
| **Linter**             | `flake8`                          | `pint` (Laravel Pint)           |
| **Formatter**          | `black`                           | `pint` (also formats)           |
| **Static Analysis**    | `mypy`                            | `psalm` or `phpstan`            |
| **Database**           | Manual setup                      | SQLite or MySQL service         |
| **Matrix Strategy**    | Python versions                   | PHP versions                    |

### Troubleshooting

- **"PHP version not found"** — Use `shivammathur/setup-php@v2` which supports many PHP versions. Check available versions in the action's documentation.
- **"Database connection failed"** — Ensure database service is healthy before running tests. Use health checks in service configuration.
- **"Composer install failed"** — Check `composer.json` syntax. Use `composer validate` locally before pushing.
- **"Tests failing in CI but passing locally"** — Ensure `.env` is properly configured. Use `.env.example` as a template and set test-specific values.

### Deployment Step (Optional)

Add deployment to your workflow:

```yaml
deploy:
  needs: tests
  runs-on: ubuntu-latest
  if: github.ref == 'refs/heads/main'

  steps:
    - uses: actions/checkout@v4

    - name: Deploy to production
      run: |
        # Your deployment commands
        echo "Deploying to production..."
```

**Compare to Python deployment:**

```yaml
# Python deployment (e.g., Heroku)
deploy:
  needs: test
  runs-on: ubuntu-latest
  steps:
    - uses: actions/checkout@v4
    - uses: akhileshns/heroku-deploy@v3.12.12
      with:
        heroku_api_key: ${{secrets.HEROKU_API_KEY}}
        heroku_app_name: "your-app-name"
        heroku_email: "your-email@example.com"
```

Both workflows can deploy to various platforms (Heroku, Railway, Laravel Forge, etc.) after successful tests.

## Step 4: Docker & Containerization (~20 min)

### Goal

Compare Docker setups for Python apps vs Laravel apps, understanding containerization patterns and docker-compose configurations.

### Actions

1. **Python Dockerfile** (Flask/Django):

The complete Python Dockerfile example is available in [`Dockerfile.python`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-07/docker/Dockerfile.python):

```dockerfile
# filename: Dockerfile
FROM python:3.11-slim

WORKDIR /app

# Install system dependencies
RUN apt-get update && apt-get install -y \
    gcc \
    postgresql-client \
    && rm -rf /var/lib/apt/lists/*

# Copy requirements
COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

# Copy application code
COPY . .

# Expose port
EXPOSE 8000

# Run application
CMD ["gunicorn", "app:app", "--bind", "0.0.0.0:8000"]
```

2. **Laravel Dockerfile**:

The complete Laravel Dockerfile example is available in [`Dockerfile.laravel`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-07/docker/Dockerfile.laravel):

```dockerfile
# filename: Dockerfile
FROM php:8.4-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy application files
COPY . /var/www

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Expose port
EXPOSE 8000

# Start PHP-FPM
CMD php artisan serve --host=0.0.0.0 --port=8000
```

3. **Python docker-compose**:

The complete Python docker-compose example is available in [`docker-compose.python.yml`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-07/docker/docker-compose.python.yml):

```yaml
# filename: docker-compose.yml
version: "3.8"

services:
  web:
    build: .
    ports:
      - "8000:8000"
    environment:
      - DATABASE_URL=postgresql://user:password@db:5432/mydb
    depends_on:
      - db

  db:
    image: postgres:15
    environment:
      - POSTGRES_USER=user
      - POSTGRES_PASSWORD=password
      - POSTGRES_DB=mydb
    volumes:
      - postgres_data:/var/lib/postgresql/data

volumes:
  postgres_data:
```

4. **Laravel docker-compose**:

The complete Laravel docker-compose example is available in [`docker-compose.laravel.yml`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-07/docker/docker-compose.laravel.yml):

```yaml
# filename: docker-compose.yml
version: "3.8"

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    ports:
      - "8000:8000"
    environment:
      - APP_ENV=local
      - DB_CONNECTION=mysql
      - DB_HOST=db
      - DB_DATABASE=laravel
      - DB_USERNAME=root
      - DB_PASSWORD=password
    volumes:
      - ./:/var/www
    depends_on:
      - db

  db:
    image: mysql:8.0
    environment:
      - MYSQL_DATABASE=laravel
      - MYSQL_ROOT_PASSWORD=password
    volumes:
      - mysql_data:/var/lib/mysql

  redis:
    image: redis:alpine
    ports:
      - "6379:6379"

volumes:
  mysql_data:
```

### Expected Result

You can see the patterns are similar:

- **Python**: `FROM python:3.11-slim` → **PHP**: `FROM php:8.4-fpm`
- **Python**: `pip install -r requirements.txt` → **PHP**: `composer install`
- **Python**: `gunicorn` → **PHP**: `php artisan serve` or PHP-FPM
- Both use docker-compose for multi-container setups
- Both include database services (PostgreSQL/MySQL)

### Why It Works

Both Dockerfiles follow similar patterns:

- **Base image**: Official runtime image (Python/PHP)
- **System dependencies**: Install required system packages
- **Application dependencies**: Install language-specific packages (pip/composer)
- **Copy code**: Add application files to container
- **Expose port**: Make application accessible
- **Run command**: Start the application server

Laravel's Dockerfile includes PHP extensions (pdo_mysql, mbstring, etc.) that are commonly needed. Both can use multi-stage builds for optimization (not shown here but recommended for production).

::: tip Multi-Stage Builds
Use multi-stage builds to reduce image size. Build dependencies in one stage, copy only necessary files to a smaller runtime image in the final stage.
:::

### Comparison Table

| Feature                | Python Dockerfile      | Laravel Dockerfile                    |
| ---------------------- | ---------------------- | ------------------------------------- |
| **Base Image**         | `python:3.11-slim`     | `php:8.4-fpm`                         |
| **Dependency Manager** | `pip`                  | `composer`                            |
| **Dependency File**    | `requirements.txt`     | `composer.json`                       |
| **Web Server**         | `gunicorn` / `uvicorn` | `php artisan serve` / PHP-FPM + Nginx |
| **Extensions**         | Python packages        | PHP extensions (pdo_mysql, etc.)      |
| **Database**           | PostgreSQL/MySQL       | MySQL/PostgreSQL                      |
| **Compose Services**   | web + db               | app + db + redis (optional)           |

### Troubleshooting

- **"Composer not found"** — Ensure Composer is installed in the Dockerfile. Use `COPY --from=composer:latest` for multi-stage builds.
- **"Permission denied"** — Set proper permissions for storage directories: `chown -R www-data:www-data storage bootstrap/cache`
- **"Database connection failed"** — Ensure database service is running and environment variables match docker-compose configuration.
- **"Port already in use"** — Change port mapping in docker-compose: `"8001:8000"` instead of `"8000:8000"`

## Step 5: Deployment Platforms (~20 min)

### Goal

Compare deployment options (Heroku/Railway vs Laravel Forge/Vapor), understanding platform-specific configurations and workflows.

### Actions

1. **Python Deployment (Heroku/Railway)**:

The complete Python Procfile example is available in [`Procfile.python`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-07/deployment/Procfile.python):

```bash
# filename: Procfile
web: gunicorn app:app --bind 0.0.0.0:$PORT

# Or for Django
# web: gunicorn myproject.wsgi:application --bind 0.0.0.0:$PORT

# Or for Railway
# CMD: gunicorn app:app --bind 0.0.0.0:$PORT
```

**Heroku/Railway Configuration:**

```python
# runtime.txt (Heroku)
python-3.11.5

# requirements.txt
Flask==2.3.0
gunicorn==21.2.0
psycopg2-binary==2.9.9

# Environment variables (set in platform dashboard)
DATABASE_URL=postgresql://...
SECRET_KEY=your-secret-key
```

2. **Laravel Forge Deployment**:

Laravel Forge is a server management platform that automates Laravel deployments:

**Forge Setup:**

1. Connect your Git repository (GitHub, GitLab, Bitbucket)
2. Select server (DigitalOcean, AWS, Linode, etc.)
3. Configure deployment script:

```bash
# Forge deployment script (auto-generated)
cd /home/forge/default
git pull origin main
$FORGE_COMPOSER install --no-interaction --prefer-dist --optimize-autoloader --no-dev
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
```

**Environment Variables:**

Set in Forge dashboard:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `DB_CONNECTION=mysql`
- `DB_HOST=127.0.0.1`
- `DB_DATABASE=forge`
- `DB_USERNAME=forge`
- `DB_PASSWORD=...`

3. **Laravel Vapor (Serverless)**:

The complete Laravel Vapor config example is available in [`vapor.yml`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-07/deployment/vapor.yml):

```yaml
# filename: vapor.yml
id: 12345
name: my-laravel-app

environments:
  production:
    domain: myapp.com
    memory: 1024
    cli-memory: 512
    runtime: php-8.4
    build:
      - "composer install --no-dev --optimize-autoloader"
      - "php artisan config:cache"
      - "php artisan route:cache"
      - "php artisan view:cache"
    deploy:
      - "php artisan migrate --force"
```

**Vapor Deployment:**

```bash
# Install Vapor CLI
composer require laravel/vapor-cli --global

# Login
vapor login

# Deploy
vapor deploy production
```

### Expected Result

You can see the deployment patterns:

- **Heroku/Railway**: Simple Procfile/CMD → **Laravel Forge**: Git-based deployment with scripts
- **Heroku/Railway**: Platform-managed → **Laravel Forge**: Server management platform
- **Heroku/Railway**: Traditional hosting → **Laravel Vapor**: Serverless (AWS Lambda)

### Why It Works

All platforms automate deployment:

- **Heroku/Railway**: Detect runtime, install dependencies, run Procfile command
- **Laravel Forge**: Git push triggers deployment script (composer install, migrations, cache)
- **Laravel Vapor**: Serverless deployment to AWS Lambda with automatic scaling

Laravel Forge provides a GUI for server management (SSL, queues, cron jobs) while Vapor offers serverless scaling. Both are Laravel-specific, while Heroku/Railway are language-agnostic.

::: tip Deployment Best Practices

- Always run migrations in deployment scripts
- Cache configuration, routes, and views for production
- Use environment variables for secrets
- Set up queue workers for background jobs
- Configure SSL certificates
- Set up monitoring and logging
  :::

### Comparison Table

| Feature               | Heroku/Railway | Laravel Forge       | Laravel Vapor          |
| --------------------- | -------------- | ------------------- | ---------------------- |
| **Deployment Method** | Git push       | Git push            | `vapor deploy`         |
| **Configuration**     | Procfile/CMD   | Deployment script   | `vapor.yml`            |
| **Scaling**           | Manual dynos   | Manual servers      | Automatic (serverless) |
| **Database**          | Add-on         | Self-managed        | RDS/DynamoDB           |
| **Queue**             | Worker dynos   | Queue workers       | SQS + Lambda           |
| **Cost**              | Pay per dyno   | Pay per server      | Pay per request        |
| **Best For**          | Simple apps    | Traditional hosting | High-scale APIs        |

### Troubleshooting

- **"Deployment failed"** — Check deployment logs in platform dashboard. Common issues: missing dependencies, migration failures, permission errors.
- **"Environment variables not set"** — Ensure variables are set in platform dashboard and match your `.env` file.
- **"Database connection failed"** — Verify database credentials and ensure database is accessible from application server.
- **"Queue not processing"** — Ensure queue workers are running. In Forge, configure queue workers in dashboard. In Vapor, queues use SQS automatically.

## Step 6: Background Jobs & Queues (~25 min)

### Goal

Compare Celery vs Laravel Queues, understanding job creation, queue drivers, and scheduling.

### Actions

1. **Celery Task** (Python):

The complete Celery task example is available in [`celery-task.py`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-07/queues/celery-task.py):

```python
# filename: tasks.py
from celery import Celery
import requests

app = Celery('tasks', broker='redis://localhost:6379/0')

@app.task
def send_email(to, subject, body):
    """Send email asynchronously"""
    # Simulate email sending
    print(f"Sending email to {to}: {subject}")
    return f"Email sent to {to}"

@app.task(bind=True, max_retries=3)
def process_payment(self, user_id, amount):
    """Process payment with retry logic"""
    try:
        # Simulate payment processing
        if amount < 0:
            raise ValueError("Invalid amount")
        print(f"Processing payment for user {user_id}: ${amount}")
        return f"Payment processed: ${amount}"
    except Exception as exc:
        # Retry on failure
        raise self.retry(exc=exc, countdown=60)

# Usage
from tasks import send_email, process_payment

# Dispatch task
send_email.delay("user@example.com", "Welcome", "Welcome to our app!")
process_payment.delay(user_id=1, amount=100.00)
```

2. **Celery Beat (Scheduling)**:

The complete Celery Beat example is available in [`celery-beat.py`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-07/queues/celery-beat.py):

```python
# filename: celery_beat.py
from celery import Celery
from celery.schedules import crontab

app = Celery('tasks', broker='redis://localhost:6379/0')

app.conf.beat_schedule = {
    'send-daily-report': {
        'task': 'tasks.send_daily_report',
        'schedule': crontab(hour=9, minute=0),  # 9 AM daily
    },
    'cleanup-old-data': {
        'task': 'tasks.cleanup_old_data',
        'schedule': crontab(hour=2, minute=0, day_of_week=1),  # 2 AM Monday
    },
}

@app.task
def send_daily_report():
    print("Sending daily report...")

@app.task
def cleanup_old_data():
    print("Cleaning up old data...")
```

3. **Laravel Job**:

The complete Laravel job example is available in [`laravel-job.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-07/queues/laravel-job.php):

```php
<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $to,
        public string $subject,
        public string $body
    ) {}

    public function handle(): void
    {
        // Send email
        Mail::to($this->to)->send(new \App\Mail\WelcomeMail($this->subject, $this->body));
    }

    public function failed(\Throwable $exception): void
    {
        // Handle job failure
        logger()->error('Email job failed', [
            'to' => $this->to,
            'error' => $exception->getMessage()
        ]);
    }
}

// Usage
use App\Jobs\SendEmailJob;

// Dispatch job
SendEmailJob::dispatch('user@example.com', 'Welcome', 'Welcome to our app!');

// Dispatch with delay
SendEmailJob::dispatch('user@example.com', 'Welcome', 'Welcome!')
    ->delay(now()->addMinutes(5));

// Dispatch to specific queue
SendEmailJob::dispatch('user@example.com', 'Welcome', 'Welcome!')
    ->onQueue('emails');
```

4. **Laravel Job with Retry Logic**:

The complete Laravel job with retry logic example is available in [`laravel-job-retry.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-07/queues/laravel-job-retry.php):

```php
<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPaymentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3; // Max retries
    public $backoff = [60, 120, 300]; // Delay between retries (seconds)

    public function __construct(
        public int $userId,
        public float $amount
    ) {}

    public function handle(): void
    {
        if ($this->amount < 0) {
            throw new \InvalidArgumentException('Invalid amount');
        }

        // Process payment
        logger()->info('Processing payment', [
            'user_id' => $this->userId,
            'amount' => $this->amount
        ]);
    }
}
```

5. **Laravel Scheduled Tasks**:

The complete Laravel scheduled task example is available in [`laravel-scheduled-task.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-07/queues/laravel-scheduled-task.php):

```php
<?php

declare(strict_types=1);

// app/Console/Kernel.php
namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Run daily at 9 AM
        $schedule->call(function () {
            // Send daily report
            logger()->info('Sending daily report...');
        })->dailyAt('09:00');

        // Run weekly on Monday at 2 AM
        $schedule->call(function () {
            // Cleanup old data
            logger()->info('Cleaning up old data...');
        })->weeklyOn(1, '02:00'); // 1 = Monday

        // Run job on schedule
        $schedule->job(new \App\Jobs\SendDailyReportJob)
            ->dailyAt('09:00');
    }
}

// Add to crontab (run once):
// * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### Expected Result

You can see the patterns are similar:

- **Celery**: `@app.task` decorator → **Laravel**: `implements ShouldQueue`
- **Celery**: `task.delay()` → **Laravel**: `Job::dispatch()`
- **Celery**: `bind=True` for retries → **Laravel**: `$tries` and `$backoff` properties
- **Celery Beat**: `beat_schedule` → **Laravel**: `schedule()` method in Kernel

### Why It Works

Both queue systems follow similar principles:

- **Task/Job Definition**: Define work to be done asynchronously
- **Queue Driver**: Store jobs in Redis, database, or message queue (SQS, RabbitMQ)
- **Worker**: Process jobs from queue
- **Retry Logic**: Automatically retry failed jobs
- **Scheduling**: Run tasks on a schedule (cron-like)

Laravel's queue system is simpler to set up than Celery (no separate broker process needed for database driver) but Celery offers more advanced features (task routing, result backends, etc.). Laravel's scheduler uses a single cron entry that runs `schedule:run` every minute, which is simpler than Celery Beat's separate process.

::: tip Queue Drivers
Laravel supports multiple queue drivers: `sync` (for testing), `database`, `redis`, `sqs`, `beanstalkd`. Use `database` for simple setups, `redis` for better performance, and `sqs` for AWS serverless.
:::

### Comparison Table

| Feature             | Celery                     | Laravel Queues                       |
| ------------------- | -------------------------- | ------------------------------------ |
| **Task Definition** | `@app.task` decorator      | `implements ShouldQueue`             |
| **Dispatch**        | `task.delay()`             | `Job::dispatch()`                    |
| **Queue Driver**    | Redis, RabbitMQ, SQS       | Database, Redis, SQS, Beanstalkd     |
| **Retry Logic**     | `bind=True`, `max_retries` | `$tries`, `$backoff` properties      |
| **Scheduling**      | Celery Beat                | Laravel Scheduler                    |
| **Worker**          | `celery worker`            | `php artisan queue:work`             |
| **Monitoring**      | Flower, Celery monitoring  | Horizon (for Redis), Queue dashboard |
| **Result Backend**  | Redis, database, RPC       | Optional (not built-in)              |

### Troubleshooting

- **"Job not processing"** — Ensure queue worker is running: `php artisan queue:work`. Use supervisor or systemd to keep workers running.
- **"Job failing silently"** — Check `failed_jobs` table: `php artisan queue:failed`. Implement `failed()` method in job to handle failures.
- **"Scheduled task not running"** — Ensure cron is set up: `* * * * * cd /path && php artisan schedule:run`. Check `php artisan schedule:list` to see scheduled tasks.
- **"Redis connection failed"** — Verify Redis is running and `QUEUE_CONNECTION=redis` in `.env`. Test connection: `redis-cli ping`.

### Queue Configuration

```php
// config/queue.php
'connections' => [
    'database' => [
        'driver' => 'database',
        'table' => 'jobs',
        'queue' => 'default',
        'retry_after' => 90,
    ],

    'redis' => [
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => env('REDIS_QUEUE', 'default'),
        'retry_after' => 90,
        'block_for' => null,
    ],
],
```

**Compare to Celery configuration:**

```python
# celery.py
app = Celery('tasks')
app.conf.broker_url = 'redis://localhost:6379/0'
app.conf.result_backend = 'redis://localhost:6379/0'
app.conf.task_serializer = 'json'
app.conf.accept_content = ['json']
app.conf.task_default_queue = 'default'
```

Both systems provide flexible configuration for different queue backends and serialization formats.

## Exercises

Practice professional development practices with these exercises:

### Exercise 1: Write Tests for API Endpoints

**Goal**: Write feature tests for REST API endpoints with authentication

Create feature tests for the User API endpoints from Chapter 06:

**Requirements:**

- Test `GET /api/users` — List all users (with authentication)
- Test `POST /api/users` — Create user (with validation)
- Test `PUT /api/users/{id}` — Update user (with authentication)
- Test `DELETE /api/users/{id}` — Delete user (with authentication)
- Use `RefreshDatabase` trait for database isolation
- Use factories to create test data
- Assert JSON structure and status codes
- Test authentication middleware (should return 401 for unauthenticated requests)

**Validation**: Run tests and verify coverage:

```bash
# Run tests
php artisan test

# Run with coverage (if configured)
php artisan test --coverage

# Expected: All tests pass with green output
```

Expected output: All tests pass, showing proper authentication, validation, and CRUD operations.

### Exercise 2: Set Up CI/CD Pipeline

**Goal**: Create GitHub Actions workflow for Laravel app

**Requirements:**

- Create `.github/workflows/laravel-ci.yml`
- Test against PHP 8.2, 8.3, and 8.4 (matrix strategy)
- Set up MySQL service for testing
- Run PHPUnit tests
- Run Laravel Pint (code style check)
- Run Psalm or PHPStan (static analysis)
- Only run on push to `main` and pull requests
- Add deployment step (optional, can be commented out)

**Validation**: Push to GitHub and verify workflow runs:

```bash
# Commit and push
git add .github/workflows/laravel-ci.yml
git commit -m "Add CI workflow"
git push origin main

# Check GitHub Actions tab
# Expected: Workflow runs successfully with green checkmarks
```

Expected output: GitHub Actions workflow runs successfully, showing all jobs passing (tests, linting, static analysis).

### Exercise 3: Create Background Job

**Goal**: Create a Laravel job that processes data asynchronously

**Requirements:**

- Create a job `ProcessUserDataJob` that:
  - Accepts a user ID
  - Fetches user data from database
  - Processes the data (e.g., generates report, sends notification)
  - Handles failures gracefully
- Implement retry logic (3 attempts with exponential backoff)
- Create a scheduled task that runs the job daily at 9 AM
- Test the job with `Queue::fake()` in a test

**Validation**: Test your implementation:

```php
// In tinker or test
use App\Jobs\ProcessUserDataJob;
use Illuminate\Support\Facades\Queue;

// Test job dispatch
Queue::fake();
ProcessUserDataJob::dispatch(1);
Queue::assertPushed(ProcessUserDataJob::class);

// Test scheduled task
php artisan schedule:list
// Should show your scheduled job

// Run queue worker (in separate terminal)
php artisan queue:work
```

Expected output: Job dispatches correctly, processes data, and scheduled task appears in schedule list.

## Wrap-up

Congratulations! You've completed a comprehensive guide to professional development practices in Laravel. Let's review what you've accomplished:

- **Testing Fundamentals**: You understand PHPUnit vs pytest, including test structure, assertions, fixtures, and Laravel's HTTP testing capabilities. You can write unit tests and feature tests with database isolation.

- **Test Coverage & Mocking**: You've mastered mocking and faking in Laravel, comparing them to pytest fixtures and Python mocks. You can use Laravel factories to create test data with relationships and states.

- **CI/CD Workflows**: You can create GitHub Actions workflows for Laravel, comparing them to Python CI/CD pipelines. You understand how to automate testing, linting, and deployment.

- **Docker & Containerization**: You can containerize Laravel applications with Docker, comparing Dockerfile patterns to Python applications. You understand docker-compose configurations for multi-container setups.

- **Deployment Platforms**: You understand Laravel Forge and Vapor deployment, comparing them to Heroku/Railway. You know when to use each platform and how to configure deployments.

- **Background Jobs & Queues**: You can implement background jobs with Laravel Queues, comparing them to Celery tasks. You understand queue drivers, retry logic, and scheduling.

### Key Takeaways

1. **Testing Patterns Are Universal**: The concepts of unit tests, feature tests, mocks, and fixtures work the same way across pytest and PHPUnit. Only syntax differs.

2. **Laravel's Developer Experience**: Laravel's testing helpers (`getJson`, `postJson`, `RefreshDatabase`), fakes (`Mail::fake`, `Http::fake`), and factories provide a clean, intuitive testing experience that feels familiar to Python developers.

3. **CI/CD Is Language-Agnostic**: GitHub Actions workflows work the same way for Python and PHP. The principles of checkout, setup, test, lint, and deploy are universal.

4. **Docker Patterns Are Similar**: Both Python and PHP Dockerfiles follow similar patterns: base image, dependencies, copy code, expose port, run command. The main differences are runtime-specific (pip vs composer, gunicorn vs PHP-FPM).

5. **Deployment Options**: Laravel Forge provides server management (similar to Heroku but more control), while Vapor offers serverless scaling (similar to AWS Lambda). Choose based on your needs: simple apps (Forge), high-scale APIs (Vapor).

6. **Queues Are Queues**: Celery and Laravel Queues follow the same principles: define tasks/jobs, dispatch to queue, process with workers, retry on failure. Laravel's queue system is simpler to set up, while Celery offers more advanced features.

7. **Professional Practices**: Testing, CI/CD, containerization, deployment, and queues are essential for professional development. Laravel provides excellent tooling for all of these, making it easy to adopt best practices.

### What's Next?

In [Chapter 08](/series/python-developers-love-php-laravel/chapters/08-ecosystem-community-packages-where-laravel-excels), you'll explore Laravel's ecosystem, community, and packages. You'll learn about Livewire, Inertia, Laravel Nova, and other tools that make Laravel development delightful. You'll also see where Laravel excels compared to Python frameworks.

## Further Reading

- [Laravel Testing Documentation](https://laravel.com/docs/testing) — Complete guide to testing in Laravel
- [PHPUnit Documentation](https://phpunit.de/documentation.html) — PHPUnit testing framework reference
- [Laravel Pint Documentation](https://laravel.com/docs/pint) — Laravel's code style fixer
- [Laravel Queues Documentation](https://laravel.com/docs/queues) — Complete guide to queues and jobs
- [Laravel Forge Documentation](https://forge.laravel.com/docs) — Server management platform
- [Laravel Vapor Documentation](https://docs.vapor.build/) — Serverless deployment platform
- [GitHub Actions Documentation](https://docs.github.com/en/actions) — CI/CD workflows
- [Docker Documentation](https://docs.docker.com/) — Containerization platform
- [pytest Documentation](https://docs.pytest.org/) — Reference for comparison
- [Celery Documentation](https://docs.celeryq.dev/) — Reference for comparison
