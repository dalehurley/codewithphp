# Chapter 14: Database & ORMs - TypeORM meets Eloquent

Code examples for Laravel Eloquent ORM and database operations.

## Prerequisites

- PHP 8.1+
- Composer
- MySQL/PostgreSQL/SQLite

## Setup

Create a Laravel project to run these examples:

```bash
composer create-project laravel/laravel eloquent-demo
cd eloquent-demo
php artisan serve
```

## Examples

### 1. Model Definitions (`01-models.php`)
Eloquent model examples with relationships.

### 2. Migrations (`02-migrations.php`)
Database migrations and schema building.

### 3. Basic CRUD Operations (`03-crud.php`)
Create, Read, Update, Delete with Eloquent.

### 4. Query Builder (`04-queries.php`)
Advanced querying with Eloquent.

### 5. Relationships (`05-relationships.php`)
One-to-many, many-to-many, polymorphic relationships.

### 6. Eager Loading (`06-eager-loading.php`)
Avoid N+1 queries with eager loading.

### 7. Accessors & Mutators (`07-accessors-mutators.php`)
Transform data on retrieval and storage.

### 8. Scopes (`08-scopes.php`)
Reusable query logic with scopes.

## Key Concepts

### Eloquent vs TypeORM

| Feature | TypeORM | Eloquent |
|---------|---------|----------|
| Pattern | Data Mapper | Active Record |
| Syntax | Verbose | Concise |
| Relations | Decorators | Methods |
| Queries | QueryBuilder | Fluent methods |

### Active Record Pattern

```php
// TypeORM (Data Mapper)
$user = await userRepository.findOne({where: {id: 1}});
await userRepository.save($user);

// Eloquent (Active Record)
$user = User::find(1);
$user->save();
```

## Quick Reference

### Basic Queries

```php
// Find by ID
$user = User::find(1);

// Find or fail (throws 404)
$user = User::findOrFail(1);

// Get all
$users = User::all();

// Where clause
$users = User::where('active', true)->get();

// Create
$user = User::create([
    'name' => 'Alice',
    'email' => 'alice@example.com',
]);

// Update
$user->update(['name' => 'Alice Updated']);

// Delete
$user->delete();
```

### Relationships

```php
// One-to-Many
class User extends Model {
    public function posts() {
        return $this->hasMany(Post::class);
    }
}

$user->posts; // Get all posts

// Many-to-Many
class User extends Model {
    public function roles() {
        return $this->belongsToMany(Role::class);
    }
}

$user->roles; // Get all roles
```

### Eager Loading

```php
// N+1 problem
$users = User::all();
foreach ($users as $user) {
    echo $user->posts; // Queries in loop!
}

// Solution: Eager load
$users = User::with('posts')->get();
foreach ($users as $user) {
    echo $user->posts; // No additional queries
}
```

## Artisan Commands

```bash
# Create model with migration
php artisan make:model Post -m

# Create migration
php artisan make:migration create_posts_table

# Run migrations
php artisan migrate

# Rollback
php artisan migrate:rollback

# Fresh (drop all & re-run)
php artisan migrate:fresh

# Seed database
php artisan db:seed

# Create seeder
php artisan make:seeder UserSeeder

# Create factory
php artisan make:factory PostFactory
```

## Resources

- [Eloquent Documentation](https://laravel.com/docs/eloquent)
- [Migrations](https://laravel.com/docs/migrations)
- [Seeding](https://laravel.com/docs/seeding)
