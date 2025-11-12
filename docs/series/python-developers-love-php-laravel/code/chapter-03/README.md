# Chapter 03: Laravel Developer Experience - Code Examples

This directory contains code examples demonstrating Laravel's developer experience tools compared to Python equivalents.

## Files Overview

### Artisan CLI Examples

- **`artisan-commands.php`** - Laravel Artisan command examples
  - Custom commands with arguments and options
  - Progress bars and interactive input
  - Command output formatting
  - Code generation examples (`make:*` commands)
  - Generated code examples (models, controllers, requests, middleware)

- **`django-manage.py`** - Django management command examples
  - Equivalent Django management commands
  - Comparison with Laravel Artisan commands
  - Manual file creation (Django doesn't have code generators)

### Migration Examples

- **`laravel-migration.php`** - Laravel migration examples
  - Creating tables
  - Adding/modifying columns
  - Foreign keys and indexes
  - Rollback methods

- **`django-migration.py`** - Django migration examples
  - Equivalent Django migrations
  - Comparison with Laravel migrations

### Testing Examples

- **`phpunit-test.php`** - PHPUnit test examples
  - Feature tests (HTTP/Integration)
  - Unit tests (isolated)
  - Assertions and database testing
  - Using factories in tests

- **`pytest-test.py`** - pytest test examples
  - Equivalent pytest tests
  - Comparison with PHPUnit

### Seeder and Factory Examples

- **`laravel-seeder.php`** - Laravel seeder examples
  - Basic seeders
  - Seeders with relationships
  - Conditional seeding
  - Progress bars

- **`django-fixture.json`** - Django fixture example
  - JSON fixture format
  - Comparison with Laravel seeders

- **`laravel-factory.php`** - Laravel factory examples
  - Basic factories
  - Factories with states
  - Factories with relationships
  - Using factories in tests

## Running the Examples

### Laravel Examples

These examples are designed to be used within a Laravel project. To use them:

1. **Artisan Commands:**
   ```bash
   # Copy command class to app/Console/Commands/
   # Register in app/Console/Kernel.php (if needed)
   php artisan users:list
   ```

2. **Migrations:**
   ```bash
   # Copy migration to database/migrations/
   # Run migration
   php artisan migrate
   ```

3. **Tests:**
   ```bash
   # Copy test files to tests/Feature/ or tests/Unit/
   # Run tests
   php artisan test
   ```

4. **Seeders:**
   ```bash
   # Copy seeder to database/seeders/
   # Run seeder
   php artisan db:seed --class=UserSeeder
   ```

5. **Factories:**
   ```bash
   # Copy factory to database/factories/
   # Use in tests or seeders
   User::factory()->create();
   ```

### Python Examples

These examples are designed to be used within a Django project. To use them:

1. **Management Commands:**
   ```bash
   # Copy command to myapp/management/commands/
   python manage.py list_users
   ```

2. **Migrations:**
   ```bash
   # Migrations are auto-generated
   python manage.py makemigrations
   python manage.py migrate
   ```

3. **Tests:**
   ```bash
   # Copy test files to tests/
   pytest
   # or
   python manage.py test
   ```

4. **Fixtures:**
   ```bash
   # Copy fixture to myapp/fixtures/
   python manage.py loaddata users.json
   ```

5. **Factories (factory_boy):**
   ```bash
   # Install factory_boy: pip install factory_boy
   # Copy factory to myapp/factories.py
   # Use in tests
   UserFactory.create()
   ```

## Prerequisites

### Laravel

- PHP 8.4+
- Laravel 11.x
- Composer
- Database (MySQL, PostgreSQL, or SQLite)

### Django

- Python 3.10+
- Django 5.x
- pip
- Database (SQLite, PostgreSQL, or MySQL)

## Key Comparisons

### Code Generation

| Feature | Laravel | Django |
|---------|---------|--------|
| Model generation | `php artisan make:model Post -m -f` | Manual creation in `models.py` |
| Controller generation | `php artisan make:controller PostController --resource` | Manual creation in `views.py` |
| Form validation | `php artisan make:request StorePostRequest` | Manual creation in `forms.py` |
| Middleware generation | `php artisan make:middleware CheckAge` | Manual creation |
| Code generators | Built-in `make:*` commands | No equivalent generators |

### Artisan vs manage.py

| Feature | Laravel Artisan | Django manage.py |
|---------|----------------|------------------|
| Command creation | `php artisan make:command` | Manual file creation |
| Help system | `php artisan help <command>` | `python manage.py <command> --help` |
| Output formatting | Built-in helpers (`$this->info()`) | Style helpers (`self.style.SUCCESS()`) |
| Progress bars | Built-in | Requires `tqdm` |
| Code generation | `make:*` commands | Manual file creation |
| Route listing | `php artisan route:list` | Requires `django-extensions` |
| Cache management | `cache:clear`, `config:clear` | `collectstatic` (static files only) |

### Migrations

| Feature | Laravel | Django |
|---------|---------|--------|
| Filename format | Timestamp (`2024_01_01_120000_description.php`) | Numbered (`0001_initial.py`) |
| Syntax | Fluent builder (`$table->string()`) | Model-based (`models.CharField()`) |
| Rollback | `php artisan migrate:rollback` | `python manage.py migrate app zero` |

### Testing

| Feature | PHPUnit | pytest |
|---------|---------|--------|
| Test structure | Class-based (`class UserTest`) | Function or class-based |
| Assertions | Method-based (`$this->assertEquals()`) | Function-based (`assert x == y`) |
| Fixtures | `RefreshDatabase` trait | `@pytest.mark.django_db` |
| Database helpers | `assertDatabaseHas()` | Manual queries |

### Seeders vs Fixtures

| Feature | Laravel Seeders | Django Fixtures |
|---------|----------------|----------------|
| Format | PHP classes | JSON/YAML files |
| Flexibility | High (programmatic) | Low (data files) |
| Relationships | Easy (Eloquent) | Requires FK references |
| Faker integration | Built-in (`fake()`) | Requires `factory_boy` |

## Notes

- These examples are simplified for educational purposes
- In production, add proper error handling and validation
- Laravel examples assume standard Laravel project structure
- Django examples assume standard Django project structure
- Some examples may require additional dependencies

## Further Reading

- [Laravel Artisan Documentation](https://laravel.com/docs/artisan)
- [Laravel Migrations Guide](https://laravel.com/docs/migrations)
- [PHPUnit Documentation](https://phpunit.de/)
- [Laravel Testing Guide](https://laravel.com/docs/testing)
- [Django Management Commands](https://docs.djangoproject.com/en/stable/howto/custom-management-commands/)
- [pytest Documentation](https://docs.pytest.org/)

