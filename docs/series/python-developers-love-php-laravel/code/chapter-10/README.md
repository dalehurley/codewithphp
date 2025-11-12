# Chapter 10: Task Manager Project - Code Samples

This directory contains all code samples for the complete Task Manager application built in Chapter 10.

## Directory Structure

```
chapter-10/
├── README.md                    # This file
├── models/                      # Eloquent models
│   ├── User.php                 # User model with HasApiTokens trait
│   └── Task.php                 # Task model with relationships and scopes
├── migrations/                  # Database migrations
│   └── create_tasks_table.php   # Tasks table migration
├── controllers/                 # Application controllers
│   ├── TaskController.php       # Web controller (Blade views)
│   ├── ApiTaskController.php     # API controller (JSON responses)
│   └── ApiAuthController.php    # API authentication controller
├── requests/                     # Form Request classes
│   ├── StoreTaskRequest.php     # Validation for creating tasks
│   └── UpdateTaskRequest.php   # Validation for updating tasks
├── views/                       # Blade templates
│   ├── tasks/                   # Task views
│   │   ├── index.blade.php      # Task listing page
│   │   ├── create.blade.php    # Create task form
│   │   ├── edit.blade.php      # Edit task form
│   │   └── show.blade.php      # Task detail page
│   └── layouts/                 # Layout templates
│       └── app.blade.php        # Main layout (from Breeze)
├── api-resources/               # API response formatting
│   └── TaskResource.php        # Task API resource
├── routes/                      # Route definitions
│   ├── web.php                 # Web routes
│   └── api.php                 # API routes
├── tests/                       # Test files
│   ├── TaskTest.php            # Web route tests
│   └── ApiTaskApiTest.php      # API endpoint tests
└── deployment/                  # Deployment files
    ├── forge-setup.md          # Forge deployment guide
    └── environment-example.env # Production environment example
```

## Quick Start

1. **Create Laravel project**:
   ```bash
   laravel new task-manager
   cd task-manager
   ```

2. **Copy files to appropriate locations**:
   - Models → `app/Models/`
   - Migrations → `database/migrations/`
   - Controllers → `app/Http/Controllers/`
   - Views → `resources/views/`
   - Routes → `routes/`
   - Tests → `tests/Feature/`

3. **Install dependencies**:
   ```bash
   composer require laravel/breeze --dev
   php artisan breeze:install blade
   php artisan install:api
   npm install && npm run build
   ```

4. **Run migrations**:
   ```bash
   php artisan migrate
   php artisan db:seed --class=TaskSeeder
   ```

5. **Start development server**:
   ```bash
   php artisan serve
   ```

## Features

- **Full-stack application** with Blade views
- **REST API** with Sanctum authentication
- **User authentication** (web and API)
- **Form Requests** for clean validation separation
- **Rate limiting** on API routes (production best practice)
- **Task CRUD operations** (Create, Read, Update, Delete)
- **Advanced features**: filtering, search, pagination, task statistics
- **Comprehensive tests** for web and API
- **Production deployment** guide for Laravel Forge

## Notes

- All code samples are complete and runnable
- Models include relationships and query scopes
- Controllers include validation and authorization
- Views use Tailwind CSS (from Laravel Breeze)
- API uses Sanctum for token authentication
- Tests use PHPUnit with RefreshDatabase trait

## Related Chapters

- Chapter 05: Eloquent ORM and relationships
- Chapter 06: REST APIs and Sanctum authentication
- Chapter 07: Testing and deployment

