---
title: "14: Database & ORMs - TypeORM meets Eloquent"
description: "Master database operations in PHP with Eloquent ORM. Compare TypeORM patterns to Laravel's elegant query builder and learn relationships, migrations, and best practices."
sidebar:
  label: "14: Database & ORMs - TypeORM meets Eloquent"
  order: 14
  badge:
    text: Advanced
    variant: danger
---

# Database & ORMs: TypeORM meets Eloquent

## Overview

If you've used TypeORM, Prisma, or Sequelize, you'll find Laravel's Eloquent ORM refreshingly elegant. Eloquent uses the Active Record pattern (like ActiveRecord in Rails) rather than Data Mapper (like TypeORM), resulting in cleaner, more intuitive code.

## Learning Objectives

By the end of this chapter, you'll be able to:

- ✅ Define models with Eloquent
- ✅ Write database migrations
- ✅ Query databases efficiently
- ✅ Define relationships (one-to-many, many-to-many)
- ✅ Use eager loading to avoid N+1 queries
- ✅ Seed databases with test data
- ✅ Apply database best practices

## Code Examples

📁 **[View Code Examples on GitHub](https://github.com/dalehurley/codewithphp/tree/main/code/php-typescript-developers/chapter-14)**

This chapter includes comprehensive Eloquent ORM examples:
- `01-eloquent-basics.php` - Complete guide to Eloquent (models, CRUD, relationships, scopes, accessors)

Setup a Laravel project to run these examples:
```bash
composer create-project laravel/laravel eloquent-demo
cd eloquent-demo

# Review the code examples
cat ../code/php-typescript-developers/chapter-14/01-eloquent-basics.php

# Create migrations and models as shown in examples
php artisan make:model User -m
php artisan migrate
```

## ORM Comparison

| Feature | TypeORM | Eloquent |
|---------|---------|----------|
| **Pattern** | Data Mapper | Active Record |
| **Syntax** | Verbose | Concise |
| **Query Builder** | `QueryBuilder` | Fluent methods |
| **Relationships** | Decorators | Methods |
| **Migrations** | CLI generated | Artisan generated |
| **Seeding** | Manual | Built-in |

## Model Definition

### TypeORM Entity

```typescript
import { Entity, PrimaryGeneratedColumn, Column } from 'typeorm';

@Entity()
export class User {
  @PrimaryGeneratedColumn()
  id: number;

  @Column()
  name: string;

  @Column({ unique: true })
  email: string;

  @Column({ default: true })
  isActive: boolean;

  @Column({ type: 'timestamp', default: () => 'CURRENT_TIMESTAMP' })
  createdAt: Date;
}
```

### Eloquent Model

```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model {
    protected $fillable = ['name', 'email', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
    ];
}
```

**Eloquent conventions:**
- Table name: `users` (pluralized, snake_case)
- Primary key: `id` (auto-increment)
- Timestamps: `created_at`, `updated_at` (automatic)

## Migrations

### TypeORM Migration

```typescript
import { MigrationInterface, QueryRunner, Table } from 'typeorm';

export class CreateUsersTable1234567890 implements MigrationInterface {
  public async up(queryRunner: QueryRunner): Promise<void> {
    await queryRunner.createTable(
      new Table({
        name: 'user',
        columns: [
          { name: 'id', type: 'int', isPrimary: true, isGenerated: true },
          { name: 'name', type: 'varchar' },
          { name: 'email', type: 'varchar', isUnique: true },
          { name: 'isActive', type: 'boolean', default: true },
        ],
      }),
    );
  }

  public async down(queryRunner: QueryRunner): Promise<void> {
    await queryRunner.dropTable('user');
  }
}
```

### Laravel Migration

```bash
php artisan make:migration create_users_table
```

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps(); // created_at, updated_at
        });
    }

    public function down(): void {
        Schema::dropIfExists('users');
    }
};
```

**Run migrations:**
```bash
php artisan migrate
php artisan migrate:rollback
php artisan migrate:fresh  # Drop all tables and re-run
```

## Basic CRUD Operations

### TypeORM

```typescript
// Create
const user = userRepository.create({
  name: 'Alice',
  email: 'alice@example.com',
});
await userRepository.save(user);

// Read
const users = await userRepository.find();
const user = await userRepository.findOne({ where: { id: 1 } });

// Update
user.name = 'Alice Updated';
await userRepository.save(user);

// Delete
await userRepository.remove(user);
```

### Eloquent

```php
<?php
// Create
$user = User::create([
    'name' => 'Alice',
    'email' => 'alice@example.com',
]);

// Read
$users = User::all();
$user = User::find(1);
$user = User::where('email', 'alice@example.com')->first();

// Update
$user->update(['name' => 'Alice Updated']);
// Or
$user->name = 'Alice Updated';
$user->save();

// Delete
$user->delete();
```

**So much cleaner!**

## Query Building

### TypeORM QueryBuilder

```typescript
const users = await userRepository
  .createQueryBuilder('user')
  .where('user.isActive = :active', { active: true })
  .andWhere('user.age > :age', { age: 18 })
  .orderBy('user.name', 'ASC')
  .take(10)
  .getMany();
```

### Eloquent Query Builder

```php
<?php
$users = User::where('is_active', true)
    ->where('age', '>', 18)
    ->orderBy('name')
    ->limit(10)
    ->get();

// Or more complex
$users = User::query()
    ->where(function ($query) {
        $query->where('role', 'admin')
            ->orWhere('role', 'moderator');
    })
    ->whereNotNull('email_verified_at')
    ->get();
```

## Relationships

### One-to-Many

**TypeORM:**
```typescript
// User.ts
@Entity()
export class User {
  @OneToMany(() => Post, post => post.user)
  posts: Post[];
}

// Post.ts
@Entity()
export class Post {
  @ManyToOne(() => User, user => user.posts)
  @JoinColumn()
  user: User;

  @Column()
  userId: number;
}

// Usage
const user = await userRepository.findOne({
  where: { id: 1 },
  relations: ['posts'],
});
```

**Eloquent:**
```php
<?php
// User.php
class User extends Model {
    public function posts() {
        return $this->hasMany(Post::class);
    }
}

// Post.php
class Post extends Model {
    public function user() {
        return $this->belongsTo(User::class);
    }
}

// Usage
$user = User::with('posts')->find(1);
foreach ($user->posts as $post) {
    echo $post->title;
}
```

### Many-to-Many

**TypeORM:**
```typescript
@Entity()
export class User {
  @ManyToMany(() => Role)
  @JoinTable()
  roles: Role[];
}

// Usage
const user = await userRepository.findOne({
  where: { id: 1 },
  relations: ['roles'],
});
```

**Eloquent:**
```php
<?php
class User extends Model {
    public function roles() {
        return $this->belongsToMany(Role::class);
    }
}

// Usage
$user = User::with('roles')->find(1);

// Attach/Detach
$user->roles()->attach($roleId);
$user->roles()->detach($roleId);
$user->roles()->sync([1, 2, 3]);
```

**Pivot table convention:** `role_user` (alphabetically ordered)

## Eager Loading (Avoiding N+1)

### TypeORM

```typescript
// ❌ N+1 Problem
const users = await userRepository.find();
for (const user of users) {
  // Each iteration makes a query!
  const posts = await user.posts;
}

// ✅ Eager Loading
const users = await userRepository.find({
  relations: ['posts'],
});
```

### Eloquent

```php
<?php
// ❌ N+1 Problem
$users = User::all();
foreach ($users as $user) {
    // Each iteration makes a query!
    echo $user->posts->count();
}

// ✅ Eager Loading
$users = User::with('posts')->get();
foreach ($users as $user) {
    echo $user->posts->count(); // No extra queries!
}

// Multiple relationships
$users = User::with(['posts', 'roles'])->get();

// Nested relationships
$users = User::with('posts.comments')->get();
```

## Database Seeding

### TypeORM (Manual)

```typescript
import { Factory, Seeder } from 'typeorm-seeding';

export default class CreateUsers implements Seeder {
  public async run(factory: Factory): Promise<void> {
    await factory(User)().createMany(10);
  }
}
```

### Laravel Factories & Seeders

**Create Factory:**
```bash
php artisan make:factory UserFactory
```

```php
<?php
// database/factories/UserFactory.php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory {
    public function definition(): array {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => bcrypt('password'),
        ];
    }
}
```

**Create Seeder:**
```bash
php artisan make:seeder UserSeeder
```

```php
<?php
// database/seeders/UserSeeder.php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder {
    public function run(): void {
        // Create 10 users
        User::factory()->count(10)->create();

        // Create specific user
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);
    }
}
```

**Run seeder:**
```bash
php artisan db:seed
php artisan db:seed --class=UserSeeder
```

## Advanced Queries

### Aggregates

**TypeORM:**
```typescript
const count = await userRepository.count();
const avg = await userRepository
  .createQueryBuilder()
  .select('AVG(age)', 'average')
  .getRawOne();
```

**Eloquent:**
```php
<?php
$count = User::count();
$avg = User::avg('age');
$max = User::max('age');
$sum = User::sum('balance');

// With conditions
$activeCount = User::where('is_active', true)->count();
```

### Raw Queries

**TypeORM:**
```typescript
const users = await userRepository.query(
  'SELECT * FROM users WHERE age > ?',
  [18]
);
```

**Eloquent:**
```php
<?php
use Illuminate\Support\Facades\DB;

$users = DB::select('SELECT * FROM users WHERE age > ?', [18]);

// Or with query builder
$users = DB::table('users')
    ->where('age', '>', 18)
    ->get();
```

## Transactions

### TypeORM

```typescript
await connection.transaction(async manager => {
  await manager.save(user);
  await manager.save(post);
});
```

### Eloquent

```php
<?php
use Illuminate\Support\Facades\DB;

DB::transaction(function () use ($user, $post) {
    $user->save();
    $post->save();
});

// Or manual
DB::beginTransaction();
try {
    $user->save();
    $post->save();
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    throw $e;
}
```

## Soft Deletes

### TypeORM

```typescript
@Entity()
export class User {
  @DeleteDateColumn()
  deletedAt?: Date;
}

await userRepository.softDelete(id);
await userRepository.restore(id);
```

### Eloquent

```php
<?php
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model {
    use SoftDeletes;
}

// Usage
$user->delete(); // Soft delete
$user->restore(); // Restore
$user->forceDelete(); // Permanent delete

// Query with trashed
User::withTrashed()->find(1);
User::onlyTrashed()->get();
```

**Migration:**
```php
$table->softDeletes(); // Adds deleted_at column
```

## Accessors & Mutators

### Eloquent (Attribute Casting)

```php
<?php
class User extends Model {
    // Accessor (get)
    public function getFullNameAttribute(): string {
        return "{$this->first_name} {$this->last_name}";
    }

    // Mutator (set)
    public function setEmailAttribute(string $value): void {
        $this->attributes['email'] = strtolower($value);
    }
}

// Usage
$user->full_name; // Calls accessor
$user->email = 'ALICE@EXAMPLE.COM'; // Calls mutator, stores lowercase
```

**Modern syntax (PHP 8.1+):**
```php
<?php
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Model {
    protected function fullName(): Attribute {
        return Attribute::make(
            get: fn() => "{$this->first_name} {$this->last_name}",
        );
    }

    protected function email(): Attribute {
        return Attribute::make(
            set: fn($value) => strtolower($value),
        );
    }
}
```

## Practical Example: Blog System

```php
<?php
// Models
class User extends Model {
    public function posts() {
        return $this->hasMany(Post::class);
    }

    public function comments() {
        return $this->hasMany(Comment::class);
    }
}

class Post extends Model {
    protected $fillable = ['title', 'body', 'user_id'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function comments() {
        return $this->hasMany(Comment::class);
    }

    public function tags() {
        return $this->belongsToMany(Tag::class);
    }
}

class Comment extends Model {
    public function post() {
        return $this->belongsTo(Post::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}

class Tag extends Model {
    public function posts() {
        return $this->belongsToMany(Post::class);
    }
}

// Queries
// Get user with posts and comments
$user = User::with(['posts.comments', 'comments'])->find(1);

// Get posts with user, comments, and tags
$posts = Post::with('user', 'comments.user', 'tags')->get();

// Create post with tags
$post = Post::create([
    'title' => 'My Post',
    'body' => 'Content here',
    'user_id' => 1,
]);
$post->tags()->attach([1, 2, 3]);

// Get posts by tag
$taggedPosts = Tag::find(1)->posts;
```

## Key Takeaways

1. **Eloquent is more concise** than TypeORM - fewer lines, clearer intent
2. **Active Record pattern** feels more natural than TypeORM's Data Mapper
3. **Migrations are cleaner** with Blueprint DSL - no verbose table definitions
4. **Relationships are simpler** to define - just method definitions, no decorators
5. **Factories & seeders** built-in - no external library needed
6. **Query builder** is intuitive and fluent - chains naturally like Eloquent
7. **Soft deletes** trivial to implement - one trait, one column
8. **Eager loading with `with()`** prevents N+1 queries automatically
9. **Convention over configuration** - table names, foreign keys inferred automatically
10. **Accessors and mutators** provide clean data transformation (getters/setters on steroids)
11. **Model events** (creating, created, updating, etc.) for lifecycle hooks
12. **Query scopes** allow reusable query logic - like TypeORM's query builder methods but cleaner

## Comparison Summary

| Feature | TypeORM | Eloquent |
|---------|---------|----------|
| **Model Definition** | Decorators | Class methods |
| **Queries** | `repository.find()` | `Model::get()` |
| **Relationships** | Decorators | Methods |
| **Eager Loading** | `relations: []` | `with()` |
| **Migrations** | Classes | Blueprint DSL |
| **Seeding** | External library | Built-in |
| **Soft Deletes** | `@DeleteDateColumn` | `SoftDeletes` trait |

## Next Steps

Now let's build a complete full-stack application with Laravel and Inertia.js!

**Next Chapter:** [15: Full-Stack: Inertia.js (React/Vue + Laravel)](/series/php-typescript-developers/chapters/15-full-stack-inertia)

## Resources

- [Eloquent Documentation](https://laravel.com/docs/eloquent)
- [Database Migrations](https://laravel.com/docs/migrations)
- [Database Seeding](https://laravel.com/docs/seeding)
- [Query Builder](https://laravel.com/docs/queries)

---

**Questions or feedback?** Open an issue on [GitHub](https://github.com/dalehurley/codewithphp/issues)
