# TaskMaster: Complete Laravel Application

This is a complete, production-ready task management application built with Laravel 12 and PHP 8.4.

## Features

- ✅ User authentication with Laravel Sanctum
- ✅ CRUD operations for tasks
- ✅ Task categories and tags (many-to-many relationships)
- ✅ Task filtering and search
- ✅ REST API endpoints
- ✅ Comprehensive test coverage (Pest)
- ✅ Database factories and seeders
- ✅ Authorization policies
- ✅ Type-safe code throughout

## Tech Stack

- **Framework**: Laravel 12
- **PHP Version**: 8.4+
- **Database**: SQLite (development) / MySQL (production)
- **Testing**: Pest
- **Authentication**: Laravel Sanctum
- **ORM**: Eloquent

## Project Structure

```
taskmaster/
├── app/
│   ├── Models/
│   │   ├── User.php
│   │   ├── Task.php
│   │   ├── Category.php
│   │   └── Tag.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       ├── AuthController.php
│   │   │       ├── TaskController.php
│   │   │       ├── CategoryController.php
│   │   │       └── TagController.php
│   │   ├── Requests/
│   │   │   ├── StoreTaskRequest.php
│   │   │   ├── UpdateTaskRequest.php
│   │   │   ├── StoreCategoryRequest.php
│   │   │   └── StoreTagRequest.php
│   │   └── Resources/
│   │       ├── TaskResource.php
│   │       ├── CategoryResource.php
│   │       ├── TagResource.php
│   │       └── UserResource.php
│   └── Policies/
│       └── TaskPolicy.php
├── database/
│   ├── migrations/
│   ├── factories/
│   │   ├── TaskFactory.php
│   │   ├── CategoryFactory.php
│   │   └── TagFactory.php
│   └── seeders/
│       └── DatabaseSeeder.php
├── routes/
│   └── api.php
├── tests/
│   └── Feature/
│       ├── TaskTest.php
│       ├── AuthTest.php
│       ├── CategoryTest.php
│       └── TagTest.php
└── README.md
```

## Quick Start

### 1. Installation

```bash
# Create new Laravel project
composer create-project laravel/laravel taskmaster
cd taskmaster

# Install dependencies
composer require laravel/sanctum
composer require --dev pestphp/pest
```

### 2. Configuration

```bash
# Generate app key
php artisan key:generate

# Create database
touch database/database.sqlite

# Copy environment
cp .env.example .env

# Update .env
DB_CONNECTION=sqlite
DB_DATABASE=/path/to/database.sqlite
```

### 3. Setup

```bash
# Run migrations
php artisan migrate

# Seed database (optional)
php artisan db:seed

# Start development server
php artisan serve
```

## API Endpoints

### Authentication

```bash
POST   /api/register      # Create new user
POST   /api/login         # Login user
POST   /api/logout        # Logout user (protected)
GET    /api/user          # Get current user (protected)
```

### Tasks

```bash
GET    /api/tasks         # List user's tasks (protected)
POST   /api/tasks         # Create task (protected)
GET    /api/tasks/{id}    # Get task (protected)
PUT    /api/tasks/{id}    # Update task (protected)
DELETE /api/tasks/{id}    # Delete task (protected)
POST   /api/tasks/{id}/complete  # Mark as complete (protected)
```

### Categories

```bash
GET    /api/categories    # List categories
POST   /api/categories    # Create category (protected)
GET    /api/categories/{id}       # Get category
PUT    /api/categories/{id}       # Update category (protected)
DELETE /api/categories/{id}       # Delete category (protected)
```

### Tags

```bash
GET    /api/tags          # List tags
POST   /api/tags          # Create tag (protected)
GET    /api/tags/{id}     # Get tag
PUT    /api/tags/{id}     # Update tag (protected)
DELETE /api/tags/{id}     # Delete tag (protected)
```

## Usage Examples

### Register

```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

### Login

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

### Create Task

```bash
curl -X POST http://localhost:8000/api/tasks \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "My First Task",
    "description": "This is a test task",
    "priority": "high",
    "due_date": "2025-12-31",
    "category_ids": [1, 2],
    "tag_ids": [1, 3]
  }'
```

### List Tasks with Filtering

```bash
# All tasks
curl http://localhost:8000/api/tasks \
  -H "Authorization: Bearer YOUR_TOKEN"

# Filter by status
curl "http://localhost:8000/api/tasks?status=completed" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Filter by priority
curl "http://localhost:8000/api/tasks?priority=high" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Search
curl "http://localhost:8000/api/tasks?search=laravel" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Combine filters
curl "http://localhost:8000/api/tasks?status=pending&priority=high&search=bug" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## Testing

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/TaskTest.php

# Run with coverage
./vendor/bin/pest --coverage

# Run tests matching a pattern
./vendor/bin/pest --filter=test_can_create_task
```

## Database Schema

### Users Table
- id (PRIMARY)
- name
- email (UNIQUE)
- password (hashed)
- email_verified_at
- timestamps

### Tasks Table
- id (PRIMARY)
- user_id (FOREIGN)
- title
- description
- status (enum: pending, in_progress, completed)
- priority (enum: low, medium, high)
- due_date
- completed_at
- timestamps

### Categories Table
- id (PRIMARY)
- name (UNIQUE)
- color (hex color)
- timestamps

### Tags Table
- id (PRIMARY)
- name (UNIQUE)
- timestamps

### Pivot Tables
- task_category (task_id, category_id)
- task_tag (task_id, tag_id)

## Key Concepts Demonstrated

1. **Eloquent Relationships** - One-to-many and many-to-many relationships
2. **Query Scopes** - Reusable query logic (published, pending, overdue, search)
3. **Authorization Policies** - Control what users can do with tasks
4. **API Resources** - Transform models into JSON responses
5. **Form Requests** - Centralized validation logic
6. **Factories** - Generate test data
7. **Seeders** - Populate database
8. **Testing** - Comprehensive API tests
9. **Type Safety** - Return types and parameter hints throughout
10. **REST Best Practices** - Proper HTTP methods and status codes

## Best Practices Applied

- ✅ All code uses strict type declarations
- ✅ Type hints on all functions and methods
- ✅ Request validation with Form Requests
- ✅ Response transformation with API Resources
- ✅ Authorization with Policies
- ✅ Query optimization with eager loading
- ✅ Comprehensive tests with Pest
- ✅ Clean, readable code with PHPStan

## Troubleshooting

### Sanctum 401 Unauthorized
```bash
# Make sure Sanctum is installed and migrated
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

### Database errors
```bash
# Reset database
php artisan migrate:fresh --seed

# Or with specific environment
php artisan migrate:fresh --seed --env=testing
```

### Tests failing
```bash
# Make sure RefreshDatabase is used in tests
# Run tests with verbose output
php artisan test --verbose
```

## Extending the Application

### Add Task Comments
1. Create Comment model and migration
2. Add hasMany relationship to Task
3. Create CommentController
4. Add API routes for comments
5. Create CommentResource
6. Write tests

### Add Notifications
1. Create PostCreated notification
2. Send notification when task is created
3. Allow users to mark notifications as read

### Add Task Attachments
1. Create Attachment model
2. Store files on disk or S3
3. Add file upload endpoint
4. Return file URLs in TaskResource

### Add Bulk Operations
1. Create bulk update endpoint
2. Handle multiple task IDs
3. Update multiple records efficiently
4. Write tests for bulk operations

## Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Sanctum API Authentication](https://laravel.com/docs/sanctum)
- [Eloquent ORM](https://laravel.com/docs/eloquent)
- [API Resources](https://laravel.com/docs/eloquent-resources)
- [Testing](https://laravel.com/docs/testing)
- [Pest PHP](https://pestphp.com)

## License

This project is open source and available under the MIT License.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

---

**Created as part of the "Rails Developers Love Laravel" tutorial series**

