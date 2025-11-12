---
title: "05: Working with Data: Eloquent ORM & Database Workflow"
description: "Master Eloquent ORM by comparing to Django ORM and SQLAlchemy—migrations, relationships, query builder, and data modeling."
series: "python-developers-love-php-laravel"
chapter: 5
order: 5
difficulty: "Intermediate"
prerequisites:
  - "/series/python-developers-love-php-laravel/chapters/04-php-syntax-language-differences-for-python-devs"
---

![Working with Data: Eloquent ORM](/images/python-developers-love-php-laravel/chapter-05-working-with-data-eloquent-orm-database-workflow-hero-full.webp)

# Chapter 05: Working with Data: Eloquent ORM & Database Workflow

## Overview

In Chapter 04, you learned PHP syntax differences—variable prefixes, OOP conventions, type hints, and namespaces. You now understand how PHP code looks and feels. But syntax is only part of the story. When building web applications, you spend most of your time working with data: querying databases, defining relationships, managing schema changes, and optimizing queries. This chapter is where your Python ORM knowledge becomes your greatest asset.

If you've worked with Django ORM or SQLAlchemy, you already understand the Active Record pattern, migrations, relationships, and query builders. Eloquent ORM—Laravel's database abstraction layer—follows the same principles with PHP syntax. The concepts are identical: models represent tables, relationships connect data, migrations version your schema, and query builders let you chain methods. The only difference is syntax: `Post.objects.filter()` becomes `Post::where()->get()`, and `ForeignKey()` becomes `belongsTo()`.

This chapter is a **deep dive into Eloquent ORM**. We'll compare every major feature to Django ORM and SQLAlchemy, showing you Python code you know, then demonstrating the Laravel equivalent. You'll master model definitions, migrations, relationships (one-to-one, one-to-many, many-to-many, polymorphic), query building, eager loading, scopes, accessors, mutators, and model events. By the end, you'll see that Eloquent isn't fundamentally different—it's Django ORM with PHP syntax and Laravel's delightful developer experience.

## Prerequisites

Before starting this chapter, you should have:

- Completion of [Chapter 04](/series/python-developers-love-php-laravel/chapters/04-php-syntax-language-differences-for-python-devs) or equivalent understanding of PHP syntax
- Laravel 11.x installed (or ability to follow along with code examples)
- Experience with Django ORM or SQLAlchemy (for comparisons)
- Basic understanding of database relationships (foreign keys, joins)
- Familiarity with migrations (Django migrations preferred)
- SQLite, MySQL, or PostgreSQL database available
- **Estimated Time**: ~175 minutes

**Verify your setup:**

```bash
# Check PHP version (should show PHP 8.4+)
php --version

# Check Composer is installed
composer --version

# If you have Laravel installed, verify it works
php artisan --version

# Expected output: Laravel Framework 11.x.x (or similar)

# Check database connection (if Laravel is configured)
php artisan db:show
```

## What You'll Build

By the end of this chapter, you will have:

- Side-by-side comparison examples (Django ORM/SQLAlchemy → Eloquent) for models, migrations, relationships, and queries
- Understanding of Eloquent model definitions vs Django models and SQLAlchemy classes
- Knowledge of Laravel migrations vs Django migrations workflow
- Mastery of Eloquent relationships (hasOne, belongsTo, hasMany, belongsToMany, morphTo)
- Ability to write complex queries using Eloquent's query builder
- Understanding of eager loading vs lazy loading and N+1 query prevention
- Working code examples demonstrating scopes, accessors, mutators, and model events
- Knowledge of soft deletes, Collections, database transactions, and raw queries
- Confidence in Eloquent ORM equivalent to your Python ORM knowledge

## Quick Start

Want to see how Django ORM maps to Eloquent right away? Here's a side-by-side comparison:

**Django ORM (Python):**

```python
# models.py
class Post(models.Model):
    title = models.CharField(max_length=200)
    content = models.TextField()
    author = models.ForeignKey('User', on_delete=models.CASCADE)
    published_at = models.DateTimeField(null=True, blank=True)

# Query
posts = Post.objects.filter(author_id=1).order_by('-published_at')
```

**SQLAlchemy (Python):**

```python
# models.py
class Post(Base):
    __tablename__ = 'posts'
    id = Column(Integer, primary_key=True)
    title = Column(String(200))
    author_id = Column(Integer, ForeignKey('users.id'))
    author = relationship('User', backref='posts')

# Query
posts = session.query(Post).filter(Post.author_id == 1).order_by(Post.published_at.desc()).all()
```

**Eloquent ORM (PHP/Laravel):**

```php
// app/Models/Post.php
class Post extends Model
{
    protected $fillable = ['title', 'content', 'author_id'];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

// Query
$posts = Post::where('author_id', 1)->orderBy('published_at', 'desc')->get();
```

See the pattern? Same concepts—models, relationships, queries—just different syntax! This chapter will show you how every Django ORM and SQLAlchemy feature translates to Eloquent.

## Objectives

- Understand Eloquent model definitions and compare to Django models and SQLAlchemy classes
- Master Laravel migrations vs Django migrations workflow and schema management
- Map Django ORM relationships (ForeignKey, ManyToMany) to Eloquent relationships (belongsTo, hasMany, belongsToMany)
- Translate Django ORM queries (`filter()`, `exclude()`, `__gte`) to Eloquent query builder methods
- Understand eager loading (`with()`) vs Django's `select_related()` and `prefetch_related()`
- Learn Eloquent-specific features: scopes, accessors, mutators, and model events
- Master soft deletes, Collections, transactions, and raw queries
- Recognize that ORM concepts are universal—only syntax differs between Python and PHP

## Step 1: Model Definitions (~15 min)

### Goal

Understand how Eloquent models are defined and compare them to Django models and SQLAlchemy classes.

### Actions

1. **Django ORM Model** (Python):

The complete Django model example is available in [`django-orm-model.py`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-05/django-orm-model.py):

```python
# filename: models.py
from django.db import models

class Post(models.Model):
    title = models.CharField(max_length=200)
    content = models.TextField()
    published_at = models.DateTimeField(auto_now_add=True)
    author = models.ForeignKey('User', on_delete=models.CASCADE)

    class Meta:
        ordering = ['-published_at']
        db_table = 'posts'

    def __str__(self):
        return self.title
```

2. **SQLAlchemy Model** (Python):

The complete SQLAlchemy model example is available in [`sqlalchemy-model.py`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-05/sqlalchemy-model.py):

```python
# filename: models.py
from sqlalchemy import Column, Integer, String, Text, DateTime, ForeignKey
from sqlalchemy.orm import relationship
from sqlalchemy.ext.declarative import declarative_base

Base = declarative_base()

class Post(Base):
    __tablename__ = 'posts'

    id = Column(Integer, primary_key=True)
    title = Column(String(200))
    content = Column(Text)
    published_at = Column(DateTime)
    author_id = Column(Integer, ForeignKey('users.id'))

    author = relationship('User', backref='posts')
```

3. **Eloquent Model** (PHP/Laravel):

The complete Eloquent model example is available in [`eloquent-model.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-05/eloquent-model.php):

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    /**
     * The table associated with the model.
     * Eloquent automatically pluralizes: Post -> posts
     */
    protected $table = 'posts';

    /**
     * The attributes that are mass assignable.
     * Similar to Django's form fields or SQLAlchemy's bulk operations.
     */
    protected $fillable = ['title', 'content', 'author_id'];

    /**
     * The attributes that should be cast.
     * Similar to Django's field types or SQLAlchemy's column types.
     */
    protected $casts = [
        'published_at' => 'datetime',
    ];

    /**
     * Get the author that owns the post.
     * Equivalent to Django's ForeignKey or SQLAlchemy's relationship.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The "booted" method adds global scopes.
     * Similar to Django's Meta.ordering.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('ordered', function ($query): void {
            $query->orderBy('published_at', 'desc');
        });
    }
}
```

### Expected Result

You can see the patterns are similar:

- **Django**: `class Post(models.Model)` → **Eloquent**: `class Post extends Model`
- **Django**: `models.CharField(max_length=200)` → **Eloquent**: `protected $fillable` + migration
- **Django**: `ForeignKey('User')` → **Eloquent**: `belongsTo(User::class)`
- **Django**: `Meta.ordering` → **Eloquent**: Global scope in `booted()`

### Why It Works

All three ORMs follow the Active Record pattern: models represent database tables, and instances represent rows. The main differences:

- **Django**: Field definitions in the model class (declarative)
- **SQLAlchemy**: Column definitions with explicit table name
- **Eloquent**: Mass assignment protection (`$fillable`) + migrations for schema

Eloquent uses migrations (like Django) to define the actual database schema, while the model defines relationships and behavior. This separation is cleaner than Django's approach where models define both schema and behavior.

::: tip Model vs Migration Separation
In Laravel, models define **behavior** (relationships, scopes, accessors) while migrations define **structure** (columns, indexes, foreign keys). This separation makes it easier to version control schema changes and keeps models focused on business logic.
:::

### Comparison Table

| Feature             | Django ORM                         | SQLAlchemy                                   | Eloquent                                               |
| ------------------- | ---------------------------------- | -------------------------------------------- | ------------------------------------------------------ |
| **Base Class**      | `models.Model`                     | `Base` (declarative)                         | `Model`                                                |
| **Table Name**      | Auto-pluralized or `Meta.db_table` | `__tablename__`                              | Auto-pluralized or `$table`                            |
| **Primary Key**     | Auto `id` field                    | Explicit `Column(Integer, primary_key=True)` | Auto `id` (or `$primaryKey`)                           |
| **Timestamps**      | `auto_now_add`, `auto_now`         | Manual `Column(DateTime)`                    | `$timestamps = true` (adds `created_at`, `updated_at`) |
| **Mass Assignment** | Form validation                    | Manual assignment                            | `$fillable` or `$guarded`                              |
| **Type Casting**    | Field types                        | Column types                                 | `$casts` array                                         |

### Troubleshooting

- **"Eloquent uses `$fillable`, but Django doesn't have this"** — Django uses form validation for mass assignment protection. Eloquent's `$fillable` is more explicit and prevents mass assignment vulnerabilities. Use `$fillable` for allowed fields or `$guarded` for protected fields.
- **"How do I set default values?"** — In Eloquent, set defaults in migrations (`$table->string('status')->default('draft')`) or in the model constructor. Django uses `default=` in field definitions.
- **"Table name isn't pluralizing correctly"** — Eloquent auto-pluralizes: `Post` → `posts`, `User` → `users`. Override with `protected $table = 'custom_name'`. Django uses the model name as-is unless `Meta.db_table` is set.
- **"Where do I define indexes?"** — In Laravel migrations (like Django migrations), not in the model. Use `$table->index('column')` in migrations.

## Step 2: Migrations Deep Dive (~20 min)

### Goal

Master Laravel migrations and compare them to Django migrations, understanding schema management, foreign keys, indexes, and rollback strategies.

### Actions

1. **Django Migration** (Python):

The complete Django migration example is available in [`django-migration-detailed.py`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-05/django-migration-detailed.py):

```python
# filename: migrations/0001_initial.py
from django.db import migrations, models
import django.db.models.deletion

class Migration(migrations.Migration):
    initial = True

    dependencies = []

    operations = [
        migrations.CreateModel(
            name='User',
            fields=[
                ('id', models.BigAutoField(primary_key=True, serialize=False)),
                ('name', models.CharField(max_length=255)),
                ('email', models.EmailField(max_length=255, unique=True)),
                ('created_at', models.DateTimeField(auto_now_add=True)),
                ('updated_at', models.DateTimeField(auto_now=True)),
            ],
        ),
        migrations.CreateModel(
            name='Post',
            fields=[
                ('id', models.BigAutoField(primary_key=True, serialize=False)),
                ('title', models.CharField(max_length=200)),
                ('content', models.TextField()),
                ('published_at', models.DateTimeField(null=True, blank=True)),
                ('created_at', models.DateTimeField(auto_now_add=True)),
                ('updated_at', models.DateTimeField(auto_now=True)),
                ('author', models.ForeignKey(
                    on_delete=django.db.models.deletion.CASCADE,
                    to='myapp.user'
                )),
            ],
            options={
                'indexes': [
                    models.Index(fields=['author'], name='post_author_idx'),
                    models.Index(fields=['published_at'], name='post_published_idx'),
                ],
            },
        ),
    ]
```

2. **Laravel Migration** (PHP):

The complete Laravel migration example is available in [`laravel-migration-detailed.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-05/laravel-migration-detailed.php):

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create users table migration
 *
 * To create: php artisan make:migration create_users_table
 * To run: php artisan migrate
 * To rollback: php artisan migrate:rollback
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id(); // Big integer, auto-increment, primary key
            $table->string('name'); // VARCHAR(255)
            $table->string('email')->unique(); // VARCHAR(255) with unique index
            $table->timestamps(); // created_at and updated_at timestamps
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

/**
 * Create posts table with foreign key and indexes
 *
 * To create: php artisan make:migration create_posts_table
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('author_id')->constrained('users')->onDelete('cascade');
            // Equivalent to:
            // $table->unsignedBigInteger('author_id');
            // $table->foreign('author_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('title', 200);
            $table->text('content');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            // Add indexes
            $table->index('author_id', 'post_author_idx');
            $table->index('published_at', 'post_published_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
```

### Expected Result

Both migrations create the same database structure:

- **Users table**: `id`, `name`, `email` (unique), `created_at`, `updated_at`
- **Posts table**: `id`, `author_id` (foreign key), `title`, `content`, `published_at`, `created_at`, `updated_at`
- **Indexes**: On `author_id` and `published_at` columns

### Why It Works

Both Django and Laravel migrations follow the same principles:

- **Version control**: Migrations are versioned files that track schema changes
- **Up/Down**: Both support forward (`up()`) and backward (`down()`) migrations
- **Dependencies**: Migrations can depend on previous migrations
- **Rollback**: Both allow rolling back to previous schema states

**Key Differences:**

- **Django**: Migrations are auto-generated from model changes (`python manage.py makemigrations`)
- **Laravel**: Migrations are manually created (`php artisan make:migration`) but more flexible
- **Django**: Uses `operations` array with model definitions
- **Laravel**: Uses schema builder with fluent methods (`$table->string()`, `$table->foreignId()`)

Laravel's schema builder is more explicit and gives you fine-grained control over column types, indexes, and constraints.

::: info Migration Best Practices

- Always test migrations on a copy of production data
- Keep migrations small and focused (one change per migration when possible)
- Never edit existing migrations that have been run in production—create new migrations instead
- Use descriptive migration names: `add_phone_to_users_table` not `update_users`
  :::

### Schema Builder Methods Comparison

| Django Field                       | Laravel Schema Builder                        | Notes                       |
| ---------------------------------- | --------------------------------------------- | --------------------------- |
| `CharField(max_length=255)`        | `$table->string('column')`                    | VARCHAR(255)                |
| `CharField(max_length=100)`        | `$table->string('column', 100)`               | VARCHAR(100)                |
| `TextField()`                      | `$table->text('column')`                      | TEXT                        |
| `IntegerField()`                   | `$table->integer('column')`                   | INTEGER                     |
| `BigIntegerField()`                | `$table->bigInteger('column')`                | BIGINT                      |
| `BooleanField()`                   | `$table->boolean('column')`                   | BOOLEAN/TINYINT             |
| `DateTimeField()`                  | `$table->timestamp('column')`                 | TIMESTAMP                   |
| `DateTimeField(auto_now_add=True)` | `$table->timestamps()`                        | created_at, updated_at      |
| `EmailField()`                     | `$table->string('email')`                     | VARCHAR (validate in model) |
| `ForeignKey('User')`               | `$table->foreignId('user_id')->constrained()` | Foreign key with constraint |

### Troubleshooting

- **"How do I modify an existing column?"** — Create a new migration: `php artisan make:migration modify_posts_table`. Use `$table->string('title', 500)->change()` to modify, or `$table->renameColumn('old', 'new')` to rename.
- **"Foreign key constraint fails"** — Ensure the referenced table exists. Run migrations in order, or use `Schema::disableForeignKeyConstraints()` temporarily.
- **"Migration rollback isn't working"** — Check that `down()` method properly reverses `up()`. Laravel tracks migration batches—use `php artisan migrate:rollback --step=1` to rollback one batch.
- **"How do I add a column to an existing table?"** — Create migration: `php artisan make:migration add_status_to_posts_table --table=posts`. In `up()`, use `Schema::table('posts', function ($table) { $table->string('status'); })`.

## Step 3: Relationships (~25 min)

### Goal

Master Eloquent relationships and compare them to Django ORM and SQLAlchemy relationships, covering one-to-one, one-to-many, many-to-many, and polymorphic relationships.

### Actions

1. **Django ORM Relationships** (Python):

The complete Django relationships example is available in [`django-relationships.py`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-05/django-relationships.py):

```python
# filename: models.py
from django.db import models

class User(models.Model):
    name = models.CharField(max_length=255)
    email = models.EmailField(unique=True)

class Post(models.Model):
    title = models.CharField(max_length=200)
    author = models.ForeignKey('User', on_delete=models.CASCADE, related_name='posts')
    # One-to-many: User has many Posts

class Profile(models.Model):
    user = models.OneToOneField('User', on_delete=models.CASCADE, related_name='profile')
    bio = models.TextField()
    # One-to-one: User has one Profile

class Tag(models.Model):
    name = models.CharField(max_length=50)

class PostTag(models.Model):
    post = models.ForeignKey('Post', on_delete=models.CASCADE)
    tag = models.ForeignKey('Tag', on_delete=models.CASCADE)
    # Many-to-many through intermediate model

# Or using ManyToManyField
class Post(models.Model):
    tags = models.ManyToManyField('Tag', related_name='posts')
    # Many-to-many: Post has many Tags, Tag has many Posts
```

2. **SQLAlchemy Relationships** (Python):

The complete SQLAlchemy relationships example is available in [`sqlalchemy-relationships.py`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-05/sqlalchemy-relationships.py):

```python
# filename: models.py
from sqlalchemy import Column, Integer, String, Text, ForeignKey
from sqlalchemy.orm import relationship
from sqlalchemy.ext.declarative import declarative_base

Base = declarative_base()

class User(Base):
    __tablename__ = 'users'
    id = Column(Integer, primary_key=True)
    name = Column(String(255))

    # One-to-many: User has many Posts
    posts = relationship('Post', backref='author')

    # One-to-one: User has one Profile
    profile = relationship('Profile', back_populates='user', uselist=False)

class Post(Base):
    __tablename__ = 'posts'
    id = Column(Integer, primary_key=True)
    title = Column(String(200))
    author_id = Column(Integer, ForeignKey('users.id'))

    # Many-to-many: Post has many Tags
    tags = relationship('Tag', secondary='post_tags', back_populates='posts')

class Profile(Base):
    __tablename__ = 'profiles'
    id = Column(Integer, primary_key=True)
    user_id = Column(Integer, ForeignKey('users.id'), unique=True)
    bio = Column(Text)
    user = relationship('User', back_populates='profile')

class Tag(Base):
    __tablename__ = 'tags'
    id = Column(Integer, primary_key=True)
    name = Column(String(50))
    posts = relationship('Post', secondary='post_tags', back_populates='tags')
```

3. **Eloquent Relationships** (PHP/Laravel):

The complete Eloquent relationships example is available in [`eloquent-relationships.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-05/eloquent-relationships.php):

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Model
{
    protected $fillable = ['name', 'email'];

    /**
     * One-to-many: User has many Posts
     * Equivalent to Django's ForeignKey with related_name='posts'
     * or SQLAlchemy's relationship('Post', backref='author')
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'author_id');
    }

    /**
     * One-to-one: User has one Profile
     * Equivalent to Django's OneToOneField or SQLAlchemy's uselist=False
     */
    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }
}

class Post extends Model
{
    protected $fillable = ['title', 'content', 'author_id'];

    /**
     * Many-to-one: Post belongs to User (author)
     * Equivalent to Django's ForeignKey or SQLAlchemy's ForeignKey column
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Many-to-many: Post has many Tags
     * Equivalent to Django's ManyToManyField or SQLAlchemy's secondary relationship
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'post_tag', 'post_id', 'tag_id');
        // Or use convention: $this->belongsToMany(Tag::class)
        // Laravel auto-detects: post_tag table, post_id, tag_id
    }
}

class Profile extends Model
{
    protected $fillable = ['bio', 'user_id'];

    /**
     * One-to-one inverse: Profile belongs to User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

class Tag extends Model
{
    protected $fillable = ['name'];

    /**
     * Many-to-many inverse: Tag has many Posts
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class);
    }
}
```

### Expected Result

All three ORMs define the same relationships:

- **One-to-many**: User → Posts (one user has many posts)
- **One-to-one**: User → Profile (one user has one profile)
- **Many-to-many**: Post ↔ Tags (posts have many tags, tags have many posts)

### Why It Works

Relationships in all three ORMs follow the same database principles:

- **One-to-many**: Foreign key on the "many" side (`posts.author_id` → `users.id`)
- **One-to-one**: Foreign key with unique constraint (`profiles.user_id` → `users.id`, unique)
- **Many-to-many**: Pivot table (`post_tag` with `post_id` and `tag_id`)

**Key Differences:**

- **Django**: Uses `ForeignKey()`, `OneToOneField()`, `ManyToManyField()` in model fields
- **SQLAlchemy**: Uses `relationship()` with `backref` or `back_populates`
- **Eloquent**: Uses relationship methods (`hasMany()`, `belongsTo()`, `belongsToMany()`)

Eloquent's relationship methods are more explicit—you define relationships as methods, making them easier to understand and customize.

::: tip Relationship Naming Conventions
Eloquent automatically infers foreign key names from relationship method names. For `belongsTo(User::class)`, it looks for `user_id`. Override with the second parameter: `belongsTo(User::class, 'author_id')`. The same applies to `hasMany()` and other relationships.
:::

### Relationship Methods Comparison

| Relationship Type                | Django                  | SQLAlchemy                      | Eloquent                   |
| -------------------------------- | ----------------------- | ------------------------------- | -------------------------- |
| **One-to-many** (parent → child) | `ForeignKey()` on child | `relationship()` on parent      | `hasMany()` on parent      |
| **Many-to-one** (child → parent) | `ForeignKey()` on child | `ForeignKey()` column           | `belongsTo()` on child     |
| **One-to-one**                   | `OneToOneField()`       | `relationship(uselist=False)`   | `hasOne()` / `belongsTo()` |
| **Many-to-many**                 | `ManyToManyField()`     | `relationship(secondary=table)` | `belongsToMany()`          |

### Using Relationships

**Django:**

```python
# Get user's posts
user = User.objects.get(id=1)
posts = user.posts.all()  # Uses related_name

# Get post's author
post = Post.objects.get(id=1)
author = post.author  # Direct access

# Many-to-many
post.tags.add(tag)
post.tags.remove(tag)
```

**SQLAlchemy:**

```python
# Get user's posts
user = session.query(User).get(1)
posts = user.posts  # Direct access via relationship

# Get post's author
post = session.query(Post).get(1)
author = post.author  # Direct access via backref

# Many-to-many
post.tags.append(tag)
session.commit()
```

**Eloquent:**

```php
// Get user's posts
$user = User::find(1);
$posts = $user->posts; // Direct access via relationship method

// Get post's author
$post = Post::find(1);
$author = $post->author; // Direct access via relationship method

// Many-to-many
$post->tags()->attach($tagId);
$post->tags()->detach($tagId);
$post->tags()->sync([$tagId1, $tagId2]); // Sync all tags
```

### Polymorphic Relationships

Eloquent supports polymorphic relationships (like Django's `GenericForeignKey`), allowing a model to belong to multiple other models on a single association:

**Eloquent:**

```php
class Post extends Model
{
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}

class Video extends Model
{
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}

class Comment extends Model
{
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }
}
```

This allows `Post` and `Video` to both have comments without separate `post_comments` and `video_comments` tables. The `comments` table has `commentable_type` (Post/Video) and `commentable_id` columns.

::: info When to Use Polymorphic Relationships
Use polymorphic relationships when multiple models need the same relationship type. Common examples: comments (posts, videos, articles), tags (various content types), or activity logs (various model types). Avoid overusing them—sometimes separate tables are clearer.
:::

### Troubleshooting

- **"Relationship method returns null"** — Ensure foreign key column exists and has correct name. Eloquent convention: `user_id` for `belongsTo(User::class)`. Override with second parameter: `belongsTo(User::class, 'author_id')`.
- **"Many-to-many pivot table name"** — Eloquent auto-generates: `post_tag` from `Post` and `Tag` (alphabetical). Override with second parameter: `belongsToMany(Tag::class, 'custom_pivot_table')`.
- **"How do I add extra columns to pivot table?"** — Use `withPivot()`: `belongsToMany(Tag::class)->withPivot('created_at')`. Access via `$post->tags[0]->pivot->created_at`.
- **"One-to-one relationship not working"** — Ensure foreign key has unique constraint in migration: `$table->foreignId('user_id')->unique()->constrained()`.

## Step 4: Query Builder & Filtering (~20 min)

### Goal

Master Eloquent's query builder and compare it to Django ORM and SQLAlchemy query methods, covering filtering, chaining, aggregations, and ordering.

### Actions

1. **Django ORM Queries** (Python):

The complete Django query examples are available in [`django-queries.py`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-05/django-queries.py):

```python
# filename: queries.py
from django.db.models import Q, Count, Avg
from .models import Post, User

# Basic queries
posts = Post.objects.all()  # Get all
post = Post.objects.get(id=1)  # Get one (raises if not found)
post = Post.objects.filter(id=1).first()  # Get one (returns None if not found)

# Filtering
recent_posts = Post.objects.filter(published_at__gte=timezone.now() - timedelta(days=7))
draft_posts = Post.objects.exclude(published_at__isnull=False)
user_posts = Post.objects.filter(author_id=1)

# Complex filtering
posts = Post.objects.filter(
    Q(title__icontains='django') | Q(content__icontains='django'),
    published_at__isnull=False
)

# Ordering and limiting
posts = Post.objects.order_by('-published_at')[:10]  # Latest 10

# Aggregations
post_count = Post.objects.count()
avg_posts = User.objects.annotate(post_count=Count('posts')).aggregate(Avg('post_count'))

# Chaining
posts = Post.objects.filter(author_id=1).order_by('-published_at').exclude(title='')
```

2. **SQLAlchemy Queries** (Python):

The complete SQLAlchemy query examples are available in [`sqlalchemy-queries.py`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-05/sqlalchemy-queries.py):

```python
# filename: queries.py
from sqlalchemy import and_, or_, func
from datetime import datetime, timedelta
from .models import Post, User

# Basic queries
posts = session.query(Post).all()  # Get all
post = session.query(Post).get(1)  # Get by primary key
post = session.query(Post).filter(Post.id == 1).first()  # Get one

# Filtering
recent_posts = session.query(Post).filter(
    Post.published_at >= datetime.now() - timedelta(days=7)
).all()
draft_posts = session.query(Post).filter(Post.published_at == None).all()
user_posts = session.query(Post).filter(Post.author_id == 1).all()

# Complex filtering
posts = session.query(Post).filter(
    or_(
        Post.title.contains('django'),
        Post.content.contains('django')
    ),
    Post.published_at != None
).all()

# Ordering and limiting
posts = session.query(Post).order_by(Post.published_at.desc()).limit(10).all()

# Aggregations
post_count = session.query(Post).count()
avg_posts = session.query(func.avg(func.count(Post.id))).join(User).group_by(User.id).scalar()

# Chaining
posts = session.query(Post).filter(Post.author_id == 1).order_by(Post.published_at.desc()).filter(Post.title != '').all()
```

3. **Eloquent Queries** (PHP/Laravel):

The complete Eloquent query examples are available in [`eloquent-queries.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-05/eloquent-queries.php):

```php
<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\DB;

// Basic queries
$posts = Post::all(); // Get all
$post = Post::find(1); // Get by primary key (returns null if not found)
$post = Post::where('id', 1)->first(); // Get one (returns null if not found)

// Filtering
$recentPosts = Post::where('published_at', '>=', now()->subDays(7))->get();
$draftPosts = Post::whereNull('published_at')->get();
$userPosts = Post::where('author_id', 1)->get();

// Complex filtering
$posts = Post::where(function ($query): void {
    $query->where('title', 'like', '%django%')
          ->orWhere('content', 'like', '%django%');
})->whereNotNull('published_at')->get();

// Ordering and limiting
$posts = Post::orderBy('published_at', 'desc')->limit(10)->get();

// Aggregations
$postCount = Post::count();
$avgPosts = User::withCount('posts')->get()->avg('posts_count');

// Chaining
$posts = Post::where('author_id', 1)
    ->orderBy('published_at', 'desc')
    ->where('title', '!=', '')
    ->get();
```

### Expected Result

All three ORMs produce similar SQL queries:

```sql
-- Example: Get recent posts by author
SELECT * FROM posts
WHERE author_id = 1
  AND published_at >= '2024-01-01'
ORDER BY published_at DESC
LIMIT 10;
```

### Why It Works

All three ORMs use method chaining to build queries:

- **Django**: `Post.objects.filter().order_by().exclude()`
- **SQLAlchemy**: `session.query(Post).filter().order_by().filter()`
- **Eloquent**: `Post::where()->orderBy()->where()->get()`

The key difference is execution:

- **Django**: Queries are lazy—executed when iterated or evaluated
- **SQLAlchemy**: Queries are lazy—executed with `.all()`, `.first()`, etc.
- **Eloquent**: Queries are lazy—executed with `.get()`, `.first()`, `.count()`, etc.

::: tip Query Execution Methods
Eloquent queries are lazy until you call an execution method. Common execution methods: `get()` (collection), `first()` (single model), `count()` (integer), `exists()` (boolean), `pluck()` (array of values), `value()` (single value). Always end your query chain with an execution method.
:::

### Query Method Comparison

| Django ORM                       | SQLAlchemy                             | Eloquent                           | Notes                 |
| -------------------------------- | -------------------------------------- | ---------------------------------- | --------------------- |
| `filter(field=value)`            | `filter(Model.field == value)`         | `where('field', value)`            | Equality              |
| `filter(field__gte=value)`       | `filter(Model.field >= value)`         | `where('field', '>=', value)`      | Greater than or equal |
| `filter(field__contains='text')` | `filter(Model.field.contains('text'))` | `where('field', 'like', '%text%')` | Contains              |
| `exclude(field=value)`           | `filter(Model.field != value)`         | `where('field', '!=', value)`      | Not equal             |
| `filter(field__isnull=True)`     | `filter(Model.field == None)`          | `whereNull('field')`               | Is null               |
| `order_by('-field')`             | `order_by(Model.field.desc())`         | `orderBy('field', 'desc')`         | Order descending      |
| `[:10]`                          | `.limit(10)`                           | `limit(10)`                        | Limit results         |
| `count()`                        | `.count()`                             | `count()`                          | Count rows            |

### Advanced Query Examples

**Django:**

```python
# Aggregations with annotations
users = User.objects.annotate(
    post_count=Count('posts'),
    latest_post=Max('posts__published_at')
).filter(post_count__gt=0)

# Subqueries
recent_authors = User.objects.filter(
    id__in=Post.objects.filter(
        published_at__gte=timezone.now() - timedelta(days=7)
    ).values_list('author_id', flat=True)
)
```

**SQLAlchemy:**

```python
# Aggregations with func
users = session.query(
    User,
    func.count(Post.id).label('post_count'),
    func.max(Post.published_at).label('latest_post')
).join(Post).group_by(User.id).having(func.count(Post.id) > 0).all()

# Subqueries
recent_authors = session.query(User).filter(
    User.id.in_(
        session.query(Post.author_id).filter(
            Post.published_at >= datetime.now() - timedelta(days=7)
        )
    )
).all()
```

**Eloquent:**

```php
// Aggregations with withCount
$users = User::withCount('posts')
    ->withMax('posts', 'published_at')
    ->having('posts_count', '>', 0)
    ->get();

// Subqueries
$recentAuthors = User::whereIn('id', function ($query): void {
    $query->select('author_id')
          ->from('posts')
          ->where('published_at', '>=', now()->subDays(7));
})->get();
```

### Troubleshooting

- **"Query returns a Builder instance, not results"** — You forgot to call `.get()`, `.first()`, or `.count()`. Eloquent queries are lazy—add an execution method at the end.
- **"Django's `__gte` doesn't exist in Eloquent"** — Use `where('field', '>=', value)` instead. Eloquent uses operators as strings: `=`, `!=`, `>`, `>=`, `<`, `<=`, `like`, `in`, `not in`.
- **"How do I do `OR` conditions?"** — Use closures: `where(function ($q) { $q->where(...)->orWhere(...); })`. Or use `orWhere()` for simple cases: `where('a', 1)->orWhere('b', 2)`.
- **"Query is too slow"** — Use eager loading (Step 5) to prevent N+1 queries. Add indexes in migrations. Use `select()` to limit columns: `Post::select('id', 'title')->get()`.

## Step 5: Eager Loading & Performance (~15 min)

### Goal

Understand eager loading in Eloquent and compare it to Django's `select_related()` and `prefetch_related()`, learning how to prevent N+1 query problems.

### Actions

1. **The N+1 Problem**

When accessing relationships, ORMs can generate many queries:

**Django (N+1 problem):**

```python
# This generates 1 query for posts + N queries for authors (one per post)
posts = Post.objects.all()
for post in posts:
    print(post.author.name)  # Query executed here for each post!
```

**Eloquent (N+1 problem):**

```php
// This generates 1 query for posts + N queries for authors (one per post)
$posts = Post::all();
foreach ($posts as $post) {
    echo $post->author->name; // Query executed here for each post!
}
```

2. **Django Eager Loading** (Python):

```python
# select_related() for ForeignKey/OneToOne (JOIN)
posts = Post.objects.select_related('author').all()
# Generates: SELECT * FROM posts JOIN users ON posts.author_id = users.id

# prefetch_related() for ManyToMany/Reverse ForeignKey (separate query)
posts = Post.objects.prefetch_related('tags').all()
# Generates:
# SELECT * FROM posts
# SELECT * FROM post_tag WHERE post_id IN (...)
# SELECT * FROM tags WHERE id IN (...)
```

3. **Eloquent Eager Loading** (PHP):

The complete eager loading example is available in [`eloquent-eager-loading.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-05/eloquent-eager-loading.php):

```php
<?php

declare(strict_types=1);

namespace App\Examples;

use App\Models\Post;

// with() for all relationship types (separate queries, like prefetch_related)
$posts = Post::with('author', 'tags')->get();
// Generates:
// SELECT * FROM posts
// SELECT * FROM users WHERE id IN (...)
// SELECT * FROM post_tag WHERE post_id IN (...)
// SELECT * FROM tags WHERE id IN (...)

// Nested eager loading
$posts = Post::with('author.profile', 'tags')->get();
// Loads: Post → Author → Profile, and Post → Tags

// Lazy eager loading (load after initial query)
$posts = Post::all();
$posts->load('author'); // Load authors for already-fetched posts

// Constraining eager loads
$posts = Post::with(['author' => function ($query): void {
    $query->select('id', 'name'); // Only load specific columns
}])->get();

// Eager loading with conditions
$posts = Post::with(['tags' => function ($query): void {
    $query->where('name', '!=', 'draft');
}])->get();
```

### Expected Result

Eager loading reduces queries:

- **Without eager loading**: 1 query for posts + N queries for authors = N+1 queries
- **With eager loading**: 1 query for posts + 1 query for all authors = 2 queries

::: warning The N+1 Query Problem
The N+1 problem is one of the most common performance issues in ORMs. If you have 100 posts and access `$post->author` for each, you'll generate 101 queries (1 for posts + 100 for authors). Always use `with()` when you know you'll access relationships in loops.
:::

### Why It Works

Eager loading fetches related data in advance:

- **Django `select_related()`**: Uses SQL JOIN for ForeignKey/OneToOne (single query)
- **Django `prefetch_related()`**: Uses separate queries for ManyToMany/Reverse FK (2-3 queries)
- **Eloquent `with()`**: Always uses separate queries (like `prefetch_related`)

**Key Difference:**

- **Django**: `select_related()` uses JOINs (faster for small datasets)
- **Eloquent**: `with()` always uses separate queries (more consistent, better for large datasets)

Eloquent doesn't have a direct equivalent to `select_related()` because separate queries often perform better with proper indexing and are easier to optimize.

### Eager Loading Comparison

| Django                                              | Eloquent                 | Use Case                                               |
| --------------------------------------------------- | ------------------------ | ------------------------------------------------------ |
| `select_related('author')`                          | `with('author')`         | ForeignKey/OneToOne (but Eloquent uses separate query) |
| `prefetch_related('tags')`                          | `with('tags')`           | ManyToMany/Reverse ForeignKey                          |
| `select_related('author').prefetch_related('tags')` | `with('author', 'tags')` | Multiple relationships                                 |
| `prefetch_related('tags__category')`                | `with('tags.category')`  | Nested relationships                                   |

### Performance Tips

**Django:**

```python
# Only load what you need
posts = Post.objects.select_related('author').only('title', 'author__name').all()

# Use prefetch_related with Prefetch for filtering
from django.db.models import Prefetch
posts = Post.objects.prefetch_related(
    Prefetch('tags', queryset=Tag.objects.filter(active=True))
).all()
```

**Eloquent:**

```php
// Only load specific columns
$posts = Post::with('author:id,name')->select('id', 'title', 'author_id')->get();

// Constrain eager loads
$posts = Post::with(['tags' => function ($query): void {
    $query->where('active', true);
}])->get();

// Count relationships without loading
$posts = Post::withCount('tags')->get();
// Access via: $post->tags_count
```

### Troubleshooting

- **"Still seeing N+1 queries"** — Ensure `with()` is called before `get()`. Check that relationships are defined correctly. Use Laravel Debugbar or `DB::enableQueryLog()` to see actual queries.
- **"Eager loading loads too much data"** — Use `select()` to limit columns: `with('author:id,name')`. Or use `with()` callbacks to constrain: `with(['author' => fn($q) => $q->select('id', 'name')])`.
- **"How do I eager load conditionally?"** — Use `when()`: `Post::when($loadAuthor, fn($q) => $q->with('author'))->get()`. Or use `load()` after fetching: `$posts->load('author')`.
- **"Performance is still slow"** — Add database indexes on foreign keys. Use `select()` to limit columns. Consider pagination: `Post::with('author')->paginate(20)`.

## Step 6: Mass Assignment & Protection (~10 min)

### Goal

Understand Eloquent's mass assignment protection and compare it to Django's form validation and SQLAlchemy's manual assignment.

### Actions

1. **Django Mass Assignment** (Python):

Django uses forms for mass assignment protection:

```python
# models.py
class Post(models.Model):
    title = models.CharField(max_length=200)
    content = models.TextField()
    author_id = models.IntegerField()  # Can be set via form

# forms.py
class PostForm(forms.ModelForm):
    class Meta:
        model = Post
        fields = ['title', 'content']  # Only these fields can be mass-assigned
        # author_id is excluded, must be set manually

# views.py
form = PostForm(request.POST)
if form.is_valid():
    post = form.save(commit=False)
    post.author_id = request.user.id  # Set manually
    post.save()
```

2. **SQLAlchemy Mass Assignment** (Python):

SQLAlchemy requires manual assignment:

```python
# Manual assignment (no built-in protection)
post = Post(
    title=request.form['title'],
    content=request.form['content'],
    author_id=request.user.id
)
session.add(post)
session.commit()
```

3. **Eloquent Mass Assignment** (PHP):

The complete mass assignment example is available in [`eloquent-model.php`](../code/chapter-05/eloquent-model.php) (see Step 1):

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    /**
     * Mass assignable fields
     * Similar to Django's form fields or SQLAlchemy's allowed attributes
     */
    protected $fillable = ['title', 'content', 'author_id'];

    /**
     * OR: Guarded fields (opposite of fillable)
     * Everything except 'id' and 'timestamps' is fillable
     */
    // protected $guarded = ['id', 'created_at', 'updated_at'];
}

// Usage
$post = Post::create([
    'title' => 'My Post',
    'content' => 'Content here',
    'author_id' => 1,
    // 'admin_only_field' => 'value' // Would be ignored if not in $fillable
]);

// Or with fill()
$post = new Post();
$post->fill([
    'title' => 'My Post',
    'content' => 'Content here',
]);
$post->author_id = 1; // Set manually
$post->save();
```

### Expected Result

Mass assignment protection prevents unauthorized field updates:

- **Allowed**: Fields in `$fillable` can be mass-assigned
- **Blocked**: Fields not in `$fillable` are ignored during mass assignment
- **Manual**: You can still set protected fields directly: `$post->admin_field = value`

### Why It Works

Mass assignment protection prevents security vulnerabilities:

**The Problem:**

```php
// Without protection, user could send: { "title": "Post", "is_admin": true }
Post::create($request->all()); // is_admin would be set!
```

**The Solution:**

```php
// With $fillable, only allowed fields are set
protected $fillable = ['title', 'content']; // is_admin is not fillable
Post::create($request->all()); // is_admin is ignored
```

**Comparison:**

- **Django**: Uses form validation (`ModelForm` with `fields` or `exclude`)
- **SQLAlchemy**: No built-in protection (manual validation required)
- **Eloquent**: Uses `$fillable` or `$guarded` (explicit and simple)

Eloquent's approach is more explicit—you define allowed fields in the model, not in forms.

### Fillable vs Guarded

**Fillable (whitelist approach):**

```php
protected $fillable = ['title', 'content', 'author_id'];
// Only these fields can be mass-assigned
```

**Guarded (blacklist approach):**

```php
protected $guarded = ['id', 'is_admin', 'created_at'];
// Everything except these fields can be mass-assigned
```

**Best Practice:** Use `$fillable` for explicit control. Use `$guarded = []` to allow all fields (not recommended for production).

::: warning Mass Assignment Security
Never use `$guarded = []` in production. Always explicitly define `$fillable` to prevent unauthorized field updates. This is especially important for fields like `is_admin`, `role`, or `balance` that should never be mass-assigned from user input.
:::

### Troubleshooting

- **"Mass assignment exception"** — Field is not in `$fillable`. Add it to `$fillable` or set it manually: `$post->field = value` before `save()`.
- **"Field is being ignored"** — Check `$fillable` array. Ensure field name matches exactly (case-sensitive). Use `$post->fill()` to see which fields are set.
- **"How do I allow all fields?"** — Use `protected $guarded = []`. Not recommended—explicit `$fillable` is safer.
- **"Can I use both `$fillable` and `$guarded`?"** — No, use one or the other. `$fillable` takes precedence if both are defined.

## Step 7: Scopes, Accessors & Mutators (~15 min)

### Goal

Learn Eloquent's scopes, accessors, and mutators, comparing them to Django's model methods and SQLAlchemy's properties.

### Actions

1. **Django Model Methods** (Python):

```python
# models.py
class Post(models.Model):
    title = models.CharField(max_length=200)
    content = models.TextField()
    published_at = models.DateTimeField(null=True, blank=True)

    class Meta:
        ordering = ['-published_at']  # Global ordering

    def is_published(self):
        return self.published_at is not None

    @property
    def excerpt(self):
        return self.content[:100] + '...' if len(self.content) > 100 else self.content

    # Custom queryset methods (Django 1.7+)
    @classmethod
    def published(cls):
        return cls.objects.filter(published_at__isnull=False)
```

2. **SQLAlchemy Properties** (Python):

```python
# models.py
class Post(Base):
    __tablename__ = 'posts'
    title = Column(String(200))
    content = Column(Text)
    published_at = Column(DateTime, nullable=True)

    @property
    def is_published(self):
        return self.published_at is not None

    @property
    def excerpt(self):
        return self.content[:100] + '...' if len(self.content) > 100 else self.content
```

3. **Eloquent Scopes, Accessors & Mutators** (PHP):

The complete scopes, accessors, and mutators example is available in [`eloquent-scopes.php`](../code/chapter-05/eloquent-scopes.php) and [`eloquent-accessors-mutators.php`](../code/chapter-05/eloquent-accessors-mutators.php):

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Post extends Model
{
    protected $fillable = ['title', 'content', 'published_at'];

    /**
     * Global Scope: Applied to all queries
     * Equivalent to Django's Meta.ordering
     */
    protected static function booted(): void
    {
        static::addGlobalScope('ordered', function (Builder $query): void {
            $query->orderBy('published_at', 'desc');
        });
    }

    /**
     * Local Scope: Reusable query constraint
     * Equivalent to Django's @classmethod queryset methods
     * Usage: Post::published()->get()
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at');
    }

    /**
     * Local Scope with parameters
     * Usage: Post::byAuthor(1)->get()
     */
    public function scopeByAuthor(Builder $query, int $authorId): Builder
    {
        return $query->where('author_id', $authorId);
    }

    /**
     * Accessor: Computed attribute
     * Equivalent to Django's @property or SQLAlchemy's @property
     * Usage: $post->excerpt (automatically calls getExcerptAttribute)
     */
    public function getExcerptAttribute(): string
    {
        $content = $this->attributes['content'] ?? '';
        return strlen($content) > 100
            ? substr($content, 0, 100) . '...'
            : $content;
    }

    /**
     * Accessor: Boolean check
     * Usage: $post->is_published (automatically calls getIsPublishedAttribute)
     */
    public function getIsPublishedAttribute(): bool
    {
        return $this->published_at !== null;
    }

    /**
     * Mutator: Transform value before saving
     * Equivalent to Django's save() override or SQLAlchemy's setter
     * Usage: $post->title = 'My Title' (automatically calls setTitleAttribute)
     */
    public function setTitleAttribute(string $value): void
    {
        $this->attributes['title'] = ucfirst(trim($value));
    }

    /**
     * Mutator: Transform JSON
     * Usage: $post->metadata = ['key' => 'value'] (automatically JSON encodes)
     */
    public function setMetadataAttribute(array $value): void
    {
        $this->attributes['metadata'] = json_encode($value);
    }

    /**
     * Accessor: Transform JSON back
     * Usage: $post->metadata (automatically JSON decodes)
     */
    public function getMetadataAttribute(?string $value): ?array
    {
        return $value ? json_decode($value, true) : null;
    }
}
```

### Expected Result

Scopes, accessors, and mutators provide reusable query logic and computed attributes:

- **Scopes**: `Post::published()->get()` returns only published posts
- **Accessors**: `$post->excerpt` returns computed excerpt without storing it
- **Mutators**: `$post->title = 'value'` automatically transforms the value before saving

### Why It Works

**Scopes:**

- **Global scopes**: Applied to all queries (like Django's `Meta.ordering`)
- **Local scopes**: Reusable query constraints (like Django's queryset methods)
- **Usage**: `Post::published()->byAuthor(1)->get()`

**Accessors:**

- Automatically called when accessing attributes: `$post->excerpt`
- Naming convention: `get{AttributeName}Attribute()`
- Useful for computed values, formatting, or transformations

**Mutators:**

- Automatically called when setting attributes: `$post->title = 'value'`
- Naming convention: `set{AttributeName}Attribute()`
- Useful for validation, transformation, or encoding before saving

### Comparison Table

| Feature                  | Django                  | SQLAlchemy    | Eloquent     |
| ------------------------ | ----------------------- | ------------- | ------------ |
| **Global ordering**      | `Meta.ordering`         | Query default | Global scope |
| **Reusable queries**     | `@classmethod` queryset | Query methods | Local scope  |
| **Computed attributes**  | `@property`             | `@property`   | Accessor     |
| **Value transformation** | `save()` override       | Setter method | Mutator      |

### Using Scopes

**Django:**

```python
# Define
class Post(models.Model):
    @classmethod
    def published(cls):
        return cls.objects.filter(published_at__isnull=False)

# Use
posts = Post.published().filter(author_id=1)
```

**Eloquent:**

```php
// Define
public function scopePublished(Builder $query): Builder
{
    return $query->whereNotNull('published_at');
}

// Use
$posts = Post::published()->where('author_id', 1)->get();
```

### Using Accessors

**Django:**

```python
@property
def excerpt(self):
    return self.content[:100] + '...'

# Use
print(post.excerpt)
```

**Eloquent:**

```php
public function getExcerptAttribute(): string
{
    return substr($this->content, 0, 100) . '...';
}

// Use
echo $post->excerpt;
```

### Using Mutators

**Django:**

```python
def save(self, *args, **kwargs):
    self.title = self.title.capitalize()
    super().save(*args, **kwargs)

# Use
post.title = 'my title'
post.save()  # Saved as 'My title'
```

**Eloquent:**

```php
public function setTitleAttribute(string $value): void
{
    $this->attributes['title'] = ucfirst($value);
}

// Use
$post->title = 'my title';
$post->save(); // Saved as 'My title'
```

### Troubleshooting

- **"Scope method not found"** — Ensure method name starts with `scope`: `scopePublished`, not `published`. Call with `Post::published()`, not `Post::scopePublished()`.
- **"Accessor not working"** — Check naming: `getExcerptAttribute()` for `$post->excerpt`. Ensure attribute name matches (camelCase in method, snake_case in access).
- **"Mutator not being called"** — Check naming: `setTitleAttribute()` for `$post->title = value`. Mutators are called on `save()`, not on assignment (though they transform the value).
- **"How do I disable a global scope?"** — Use `withoutGlobalScope()`: `Post::withoutGlobalScope('ordered')->get()`. Or `withoutGlobalScopes()` to disable all.

## Step 8: Model Events & Observers (~10 min)

### Goal

Understand Eloquent model events and observers, comparing them to Django signals and SQLAlchemy events.

### Actions

1. **Django Signals** (Python):

```python
# signals.py
from django.db.models.signals import post_save, pre_delete
from django.dispatch import receiver
from .models import Post

@receiver(post_save, sender=Post)
def post_created(sender, instance, created, **kwargs):
    if created:
        print(f"New post created: {instance.title}")

@receiver(pre_delete, sender=Post)
def post_deleting(sender, instance, **kwargs):
    print(f"Post being deleted: {instance.title}")
```

2. **SQLAlchemy Events** (Python):

```python
# models.py
from sqlalchemy import event
from sqlalchemy.orm import Session

@event.listens_for(Post, 'after_insert')
def post_created(mapper, connection, target):
    print(f"New post created: {target.title}")

@event.listens_for(Post, 'before_delete')
def post_deleting(mapper, connection, target):
    print(f"Post being deleted: {target.title}")
```

3. **Eloquent Model Events** (PHP):

The complete model events example is available in [`eloquent-events.php`](../code/chapter-05/eloquent-events.php):

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = ['title', 'content', 'published_at'];

    /**
     * Model lifecycle events (hooks)
     * Equivalent to Django signals or SQLAlchemy events
     */

    protected static function booted(): void
    {
        // Equivalent to Django's post_save signal
        static::created(function (Post $post): void {
            // Fired after a new model is saved
            echo "New post created: {$post->title}\n";
        });

        // Equivalent to Django's pre_save signal
        static::creating(function (Post $post): void {
            // Fired before a new model is saved
            $post->slug = \Str::slug($post->title);
        });

        // Equivalent to Django's post_save signal (for updates)
        static::updated(function (Post $post): void {
            // Fired after an existing model is updated
            echo "Post updated: {$post->title}\n";
        });

        // Equivalent to Django's pre_delete signal
        static::deleting(function (Post $post): void {
            // Fired before a model is deleted
            echo "Post being deleted: {$post->title}\n";
        });
    }
}
```

4. **Eloquent Observers** (PHP):

For complex event logic, use observers (similar to Django's signal handlers):

```php
<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Post;

class PostObserver
{
    /**
     * Handle the Post "created" event.
     * Equivalent to Django's post_save signal with created=True
     */
    public function created(Post $post): void
    {
        // Send notification, update cache, etc.
        \Log::info("Post created: {$post->title}");
    }

    /**
     * Handle the Post "updated" event.
     */
    public function updated(Post $post): void
    {
        // Clear cache, update search index, etc.
        \Log::info("Post updated: {$post->title}");
    }

    /**
     * Handle the Post "deleting" event.
     * Equivalent to Django's pre_delete signal
     */
    public function deleting(Post $post): void
    {
        // Clean up related data, etc.
        \Log::info("Post being deleted: {$post->title}");
    }
}

// Register in AppServiceProvider
use App\Models\Post;
use App\Observers\PostObserver;

public function boot(): void
{
    Post::observe(PostObserver::class);
}
```

### Expected Result

Model events fire at specific points in the model lifecycle:

- **`creating`**: Before a new model is saved
- **`created`**: After a new model is saved
- **`updating`**: Before an existing model is updated
- **`updated`**: After an existing model is updated
- **`deleting`**: Before a model is deleted
- **`deleted`**: After a model is deleted

### Why It Works

Model events allow you to hook into the model lifecycle:

- **Django**: Uses signals (`post_save`, `pre_delete`) with `@receiver` decorator
- **SQLAlchemy**: Uses event listeners (`after_insert`, `before_delete`)
- **Eloquent**: Uses model events (`created`, `updated`, `deleting`) or observers

**Use Cases:**

- **Creating**: Generate slugs, set default values, send notifications
- **Updating**: Clear cache, update search index, log changes
- **Deleting**: Clean up related data, archive records, send notifications

### Event Comparison

| Django Signal | SQLAlchemy Event                  | Eloquent Event          | Fires When    |
| ------------- | --------------------------------- | ----------------------- | ------------- |
| `pre_save`    | `before_insert` / `before_update` | `creating` / `updating` | Before save   |
| `post_save`   | `after_insert` / `after_update`   | `created` / `updated`   | After save    |
| `pre_delete`  | `before_delete`                   | `deleting`              | Before delete |
| `post_delete` | `after_delete`                    | `deleted`               | After delete  |

### Troubleshooting

- **"Event not firing"** — Ensure event is registered in `booted()` method or observer is registered in `AppServiceProvider`. Check that you're using the correct event name (`created`, not `create`).
- **"Observer not working"** — Ensure observer is registered: `Post::observe(PostObserver::class)` in `AppServiceProvider::boot()`. Check that observer methods match event names (`created()`, `updated()`, etc.).
- **"How do I prevent save/delete in an event?"** — Return `false` in `creating`, `updating`, or `deleting` events: `static::creating(fn($post) => false)`. This cancels the operation.
- **"Event fires multiple times"** — Check if observer is registered multiple times. Use `booted()` in the model for one-time registration, or register observer once in `AppServiceProvider`.

## Step 9: Soft Deletes (~10 min)

### Goal

Learn Eloquent's soft delete feature, which allows you to "delete" records without actually removing them from the database, comparing it to Django's approach.

### Actions

1. **Django Soft Deletes** (Python):

Django doesn't have built-in soft deletes. You need to use packages like `django-model-utils` or implement manually:

```python
# Manual implementation
class Post(models.Model):
    title = models.CharField(max_length=200)
    deleted_at = models.DateTimeField(null=True, blank=True)

    def delete(self):
        self.deleted_at = timezone.now()
        self.save()

    def restore(self):
        self.deleted_at = None
        self.save()

    class Meta:
        # Filter out deleted records by default
        pass

# Query only non-deleted
posts = Post.objects.filter(deleted_at__isnull=True)
```

2. **Eloquent Soft Deletes** (PHP/Laravel):

Eloquent has built-in soft delete support:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes; // Adds soft delete functionality

    protected $fillable = ['title', 'content'];

    // Eloquent automatically adds deleted_at column
    // No need to define it in migration (but you can)
}
```

**Migration:**

```php
// Migration automatically handles deleted_at
// Or explicitly add:
Schema::table('posts', function (Blueprint $table): void {
    $table->softDeletes(); // Adds deleted_at timestamp column
});
```

### Expected Result

Soft deletes allow you to "delete" records without removing them:

- **Normal delete**: `$post->delete()` sets `deleted_at` timestamp
- **Force delete**: `$post->forceDelete()` actually removes the record
- **Restore**: `$post->restore()` clears `deleted_at` timestamp
- **Queries**: Automatically exclude soft-deleted records

### Why It Works

Soft deletes are useful for:

- **Audit trails**: Keep records for compliance
- **Recovery**: Restore accidentally deleted records
- **Relationships**: Maintain referential integrity
- **Analytics**: Track deleted items

**Key Differences:**

- **Django**: Requires packages or manual implementation
- **Eloquent**: Built-in with `SoftDeletes` trait

### Using Soft Deletes

**Eloquent:**

```php
// Soft delete (sets deleted_at)
$post->delete();

// Force delete (actually removes)
$post->forceDelete();

// Restore soft-deleted record
$post->restore();

// Query only non-deleted (default)
$posts = Post::all(); // Excludes soft-deleted

// Include soft-deleted
$posts = Post::withTrashed()->get();

// Only soft-deleted
$posts = Post::onlyTrashed()->get();

// Check if soft-deleted
if ($post->trashed()) {
    // Record is soft-deleted
}
```

### Comparison Table

| Feature               | Django (Manual)     | Eloquent           |
| --------------------- | ------------------- | ------------------ |
| **Built-in**          | ❌ Requires package | ✅ Built-in        |
| **Column**            | Manual `deleted_at` | Auto `deleted_at`  |
| **Default filtering** | Manual filter       | Automatic          |
| **Restore**           | Manual method       | `restore()` method |
| **Force delete**      | Manual `delete()`   | `forceDelete()`    |

### Troubleshooting

- **"Soft delete not working"** — Ensure model uses `SoftDeletes` trait and migration has `$table->softDeletes()` or `deleted_at` column.
- **"Queries include deleted records"** — Use `withTrashed()` to include soft-deleted, or check that `SoftDeletes` trait is properly imported.
- **"Can't restore record"** — Ensure record was soft-deleted (has `deleted_at`), not force-deleted. Use `onlyTrashed()` to find soft-deleted records.
- **"Relationships include deleted"** — Soft-deleted parent models still appear in relationships. Use `withTrashed()` on relationship queries if needed.

## Step 10: Eloquent Collections (~10 min)

### Goal

Understand Eloquent Collections vs arrays, and learn Collection methods that Python developers need to know.

### Actions

1. **Django QuerySets** (Python):

Django returns QuerySets (lazy iterables):

```python
# QuerySet (lazy)
posts = Post.objects.all()

# Convert to list
post_list = list(posts)

# Iterate (executes query)
for post in posts:
    print(post.title)

# List comprehension
titles = [post.title for post in posts]
```

2. **SQLAlchemy Results** (Python):

SQLAlchemy returns lists:

```python
# List of objects
posts = session.query(Post).all()

# List comprehension
titles = [post.title for post in posts]

# Filter list
published = [p for p in posts if p.published_at]
```

3. **Eloquent Collections** (PHP/Laravel):

Eloquent returns Collection objects (not arrays):

```php
<?php

declare(strict_types=1);

namespace App\Examples;

use App\Models\Post;

// Collection (not array)
$posts = Post::all(); // Returns Illuminate\Support\Collection

// Convert to array
$postArray = $posts->toArray();

// Collection methods (similar to Python list methods)
$titles = $posts->pluck('title'); // Get array of titles
$published = $posts->filter(fn($post) => $post->published_at !== null);
$mapped = $posts->map(fn($post) => $post->title);
```

### Expected Result

Collections provide powerful methods for manipulating data:

- **`pluck()`**: Extract single column (like list comprehension)
- **`map()`**: Transform each item (like `map()` in Python)
- **`filter()`**: Filter items (like list comprehension with condition)
- **`reduce()`**: Reduce to single value (like `reduce()` in Python)
- **`groupBy()`**: Group by key (like `itertools.groupby()`)

### Why It Works

Eloquent Collections are inspired by Laravel's Collection class, which provides a fluent interface for working with arrays of data. They're more powerful than plain arrays:

- **Method chaining**: `$posts->filter()->map()->pluck()`
- **Lazy evaluation**: Some methods are lazy (like `lazy()`)
- **Type safety**: Collections maintain type information
- **Helper methods**: Many convenient methods built-in

**Key Difference:**

- **Django**: Returns QuerySets (lazy) or lists (eager)
- **SQLAlchemy**: Returns lists
- **Eloquent**: Returns Collections (eager, but with lazy methods available)

### Collection Methods Comparison

| Python List/Dict                    | Django QuerySet                                   | Eloquent Collection                       |
| ----------------------------------- | ------------------------------------------------- | ----------------------------------------- |
| `[x.title for x in items]`          | `items.values_list('title', flat=True)`           | `$items->pluck('title')`                  |
| `[x for x in items if x.published]` | `items.filter(published=True)`                    | `$items->filter(fn($x) => $x->published)` |
| `map(fn, items)`                    | `[fn(x) for x in items]`                          | `$items->map(fn($x) => fn($x))`           |
| `sum(x.count for x in items)`       | `items.aggregate(Sum('count'))`                   | `$items->sum('count')`                    |
| `groupby(items, key)`               | `items.values('key').annotate(count=Count('id'))` | `$items->groupBy('key')`                  |

### Common Collection Methods

**Eloquent:**

```php
// Extract values
$titles = $posts->pluck('title'); // ['Title 1', 'Title 2']
$titles = $posts->pluck('title', 'id'); // [1 => 'Title 1', 2 => 'Title 2']

// Transform
$titles = $posts->map(fn($post) => strtoupper($post->title));

// Filter
$published = $posts->filter(fn($post) => $post->published_at !== null);

// Reduce
$total = $posts->reduce(fn($carry, $post) => $carry + $post->views, 0);

// Group
$byAuthor = $posts->groupBy('author_id');

// Sort
$sorted = $posts->sortBy('published_at');
$sorted = $posts->sortByDesc('published_at');

// Take/Skip
$first5 = $posts->take(5);
$skip5 = $posts->skip(5);

// Chunk (for processing large collections)
$posts->chunk(100, function ($chunk): void {
    foreach ($chunk as $post) {
        // Process 100 at a time
    }
});

// Convert to array
$array = $posts->toArray();

// Convert to JSON
$json = $posts->toJson();
```

### Troubleshooting

- **"Collection method not found"** — Ensure you're calling methods on a Collection, not an array. Use `collect()` to convert array to Collection: `collect($array)->map(...)`.
- **"Can't iterate Collection"** — Collections are iterable. Use `foreach` or convert to array: `$collection->toArray()`.
- **"Performance issues with large collections"** — Use `chunk()` or `lazy()` for large datasets. Consider using query builder methods instead of Collection methods when possible.
- **"Type errors with Collection methods"** — Collection methods return new Collections. Chain them: `$posts->filter()->map()->pluck()`.

## Step 11: Database Transactions & Raw Queries (~15 min)

### Goal

Master database transactions and raw SQL queries in Eloquent, comparing to Django and SQLAlchemy approaches.

### Actions

1. **Django Transactions** (Python):

```python
from django.db import transaction

# Context manager
with transaction.atomic():
    post = Post.objects.create(title='Post')
    Comment.objects.create(post=post, content='Comment')
    # If any operation fails, all are rolled back

# Decorator
@transaction.atomic
def create_post_with_comment():
    post = Post.objects.create(title='Post')
    Comment.objects.create(post=post, content='Comment')
```

2. **SQLAlchemy Transactions** (Python):

```python
from sqlalchemy import create_engine
from sqlalchemy.orm import sessionmaker

# Context manager
with session.begin():
    post = Post(title='Post')
    session.add(post)
    comment = Comment(post=post, content='Comment')
    session.add(comment)
    # Commit happens automatically on exit

# Manual
session.begin()
try:
    post = Post(title='Post')
    session.add(post)
    session.commit()
except:
    session.rollback()
    raise
```

3. **Eloquent Transactions** (PHP/Laravel):

```php
<?php

declare(strict_types=1);

namespace App\Examples;

use App\Models\Post;
use App\Models\Comment;
use Illuminate\Support\Facades\DB;

// Closure-based (recommended)
DB::transaction(function (): void {
    $post = Post::create(['title' => 'Post']);
    Comment::create(['post_id' => $post->id, 'content' => 'Comment']);
    // If any operation fails, all are rolled back automatically
});

// Manual transaction
DB::beginTransaction();
try {
    $post = Post::create(['title' => 'Post']);
    Comment::create(['post_id' => $post->id, 'content' => 'Comment']);
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    throw $e;
}
```

### Expected Result

Transactions ensure data integrity:

- **All operations succeed**: Changes are committed
- **Any operation fails**: All changes are rolled back
- **Consistency**: Database remains in valid state

### Why It Works

Transactions provide ACID guarantees:

- **Atomicity**: All operations succeed or all fail
- **Consistency**: Database remains consistent
- **Isolation**: Concurrent transactions don't interfere
- **Durability**: Committed changes persist

**Key Differences:**

- **Django**: Uses `transaction.atomic()` context manager
- **SQLAlchemy**: Uses `session.begin()` or context manager
- **Eloquent**: Uses `DB::transaction()` closure or manual methods

### Raw Queries

Sometimes you need raw SQL:

**Django:**

```python
from django.db import connection

# Raw SQL
with connection.cursor() as cursor:
    cursor.execute("SELECT * FROM posts WHERE id = %s", [1])
    row = cursor.fetchone()

# Raw queryset
posts = Post.objects.raw("SELECT * FROM posts WHERE published_at IS NOT NULL")
```

**SQLAlchemy:**

```python
from sqlalchemy import text

# Raw SQL
result = session.execute(text("SELECT * FROM posts WHERE id = :id"), {"id": 1})
row = result.fetchone()
```

**Eloquent:**

```php
// Raw queries
$posts = DB::select("SELECT * FROM posts WHERE published_at IS NOT NULL");
$post = DB::selectOne("SELECT * FROM posts WHERE id = ?", [1]);

// Raw in query builder
$posts = Post::whereRaw("published_at IS NOT NULL")->get();
$posts = Post::selectRaw("*, YEAR(published_at) as year")->get();

// Raw expressions
$posts = Post::where('id', DB::raw('(SELECT MAX(id) FROM posts)'))->get();
```

### Transaction Comparison

| Feature                 | Django                 | SQLAlchemy        | Eloquent            |
| ----------------------- | ---------------------- | ----------------- | ------------------- |
| **Context manager**     | `transaction.atomic()` | `session.begin()` | `DB::transaction()` |
| **Auto rollback**       | ✅ Yes                 | ✅ Yes            | ✅ Yes              |
| **Nested transactions** | ✅ Yes (savepoints)    | ✅ Yes            | ✅ Yes              |
| **Manual control**      | ✅ Yes                 | ✅ Yes            | ✅ Yes              |

### Raw Query Comparison

| Feature               | Django                | SQLAlchemy                | Eloquent                    |
| --------------------- | --------------------- | ------------------------- | --------------------------- |
| **Raw SQL**           | `connection.cursor()` | `session.execute(text())` | `DB::select()`              |
| **Raw in builder**    | `raw()`               | `text()`                  | `whereRaw()`, `selectRaw()` |
| **Parameter binding** | `%s` placeholders     | `:param` or `?`           | `?` placeholders            |

### Best Practices

**Transactions:**

```php
// ✅ Good: Use closure-based transactions
DB::transaction(function (): void {
    // Multiple operations
});

// ✅ Good: Handle exceptions
try {
    DB::transaction(function (): void {
        // Operations
    });
} catch (\Exception $e) {
    // Handle error
}

// ❌ Bad: Don't forget to commit/rollback manually
DB::beginTransaction();
// ... operations ...
// Missing DB::commit() or DB::rollBack()
```

**Raw Queries:**

```php
// ✅ Good: Use parameter binding (prevents SQL injection)
DB::select("SELECT * FROM posts WHERE id = ?", [$id]);

// ✅ Good: Use query builder when possible
Post::where('published_at', '>=', now())->get();

// ❌ Bad: Don't concatenate user input into SQL
DB::select("SELECT * FROM posts WHERE id = " . $id); // SQL injection risk!
```

### Troubleshooting

- **"Transaction not rolling back"** — Ensure you're using `DB::transaction()` closure or properly calling `DB::rollBack()` in catch block. Check that database driver supports transactions.
- **"Raw query returns stdClass"** — Raw queries return `stdClass` objects, not Eloquent models. Use `Post::fromQuery()` to convert to models, or use query builder methods when possible.
- **"SQL injection vulnerability"** — Always use parameter binding: `DB::select("SELECT * FROM posts WHERE id = ?", [$id])`. Never concatenate user input into SQL strings.
- **"Transaction deadlock"** — Ensure transactions are short-lived. Consider using database-level locking (`lockForUpdate()`) or retry logic for concurrent operations.

## Exercises

### Exercise 1: Blog Model with Relationships

**Goal**: Create a complete blog system with User, Post, Comment, and Tag models, demonstrating all relationship types.

Create models for a blog system:

- **User model**: `id`, `name`, `email`
- **Post model**: `id`, `title`, `content`, `author_id` (ForeignKey to User), `published_at`
- **Comment model**: `id`, `content`, `post_id` (ForeignKey to Post), `user_id` (ForeignKey to User), `created_at`
- **Tag model**: `id`, `name`
- **PostTag pivot table**: `post_id`, `tag_id`

**Requirements:**

1. Define all models in Django ORM, SQLAlchemy, and Eloquent
2. Set up relationships:
   - User has many Posts (one-to-many)
   - Post belongs to User (many-to-one)
   - Post has many Comments (one-to-many)
   - Comment belongs to Post and User (many-to-one)
   - Post has many Tags, Tag has many Posts (many-to-many)
3. Create migrations for all tables
4. Write queries to:
   - Get all posts by a user
   - Get all comments for a post
   - Get all tags for a post
   - Get all posts with a specific tag

**Validation**: Test your models:

```php
// Eloquent example
$user = User::create(['name' => 'John', 'email' => 'john@example.com']);
$post = Post::create(['title' => 'My Post', 'content' => 'Content', 'author_id' => $user->id]);
$tag = Tag::create(['name' => 'PHP']);
$post->tags()->attach($tag->id);

// Verify relationships
assert($user->posts->count() === 1);
assert($post->author->id === $user->id);
assert($post->tags->count() === 1);
```

Expected output: All relationships work correctly, queries return expected results.

### Exercise 2: Complex Queries with Eager Loading

**Goal**: Write complex queries demonstrating filtering, ordering, aggregations, and eager loading.

Create queries for a blog system:

1. **Get published posts from the last 7 days**, ordered by published date, with author and tags eager loaded
2. **Get users with post counts**, only showing users who have at least 3 posts
3. **Get posts with comment counts**, ordered by comment count descending
4. **Get tags with post counts**, only showing tags used in at least 5 posts

**Requirements:**

- Use eager loading to prevent N+1 queries
- Write equivalent queries in Django ORM, SQLAlchemy, and Eloquent
- Optimize queries (use `select()` to limit columns where appropriate)
- Add appropriate indexes in migrations

**Validation**: Test your queries:

```php
// Eloquent example
$posts = Post::where('published_at', '>=', now()->subDays(7))
    ->whereNotNull('published_at')
    ->with('author:id,name', 'tags:id,name')
    ->orderBy('published_at', 'desc')
    ->get();

// Verify eager loading (should be 3 queries: posts, authors, tags)
\DB::enableQueryLog();
$posts = Post::with('author', 'tags')->get();
foreach ($posts as $post) {
    $post->author->name; // Should not trigger additional queries
}
$queries = \DB::getQueryLog();
assert(count($queries) <= 3); // Posts + Authors + Tags
```

Expected output: Queries execute efficiently with eager loading, no N+1 problems.

### Exercise 3: Scopes, Accessors, and Mutators

**Goal**: Implement reusable query scopes and computed attributes.

Add the following to your Post model:

1. **Scopes**:

   - `published()`: Get only published posts
   - `byAuthor($authorId)`: Get posts by a specific author
   - `recent($days = 7)`: Get posts from the last N days
   - `popular()`: Get posts with at least 10 comments

2. **Accessors**:

   - `excerpt`: Return first 150 characters of content with "..."
   - `reading_time`: Calculate estimated reading time (assume 200 words per minute)
   - `is_published`: Boolean check if post is published

3. **Mutators**:
   - `title`: Automatically capitalize first letter and trim whitespace
   - `slug`: Automatically generate slug from title (if slug is not provided)

**Requirements:**

- Write equivalent implementations in Django (using `@classmethod` and `@property`) and Eloquent
- Use scopes in queries: `Post::published()->byAuthor(1)->recent(30)->get()`
- Access computed attributes: `$post->excerpt`, `$post->reading_time`

**Validation**: Test your implementations:

```php
// Eloquent example
$post = Post::create([
    'title' => '  my post title  ', // Should be transformed to "My post title"
    'content' => str_repeat('word ', 500), // 500 words
]);

assert($post->title === 'My post title'); // Mutator applied
assert(strlen($post->excerpt) <= 153); // 150 + "..."
assert($post->reading_time === 3); // 500 words / 200 words per minute = 2.5, rounded to 3

// Test scopes
$publishedPosts = Post::published()->get();
assert($publishedPosts->every(fn($p) => $p->published_at !== null));
```

Expected output: Scopes filter correctly, accessors return computed values, mutators transform values before saving.

## Wrap-up

Congratulations! You've completed a comprehensive deep dive into Eloquent ORM. Let's review what you've accomplished:

- **Model Definitions**: You understand how Eloquent models compare to Django models and SQLAlchemy classes, including mass assignment protection (`$fillable`), type casting, and table naming conventions.

- **Migrations**: You've mastered Laravel migrations vs Django migrations, including schema builder methods, foreign keys, indexes, and rollback strategies.

- **Relationships**: You can map Django ORM relationships (`ForeignKey`, `ManyToMany`) to Eloquent relationships (`belongsTo`, `hasMany`, `belongsToMany`), including one-to-one, one-to-many, many-to-many, and polymorphic relationships.

- **Query Builder**: You can translate Django ORM queries (`filter()`, `exclude()`, `__gte`) to Eloquent query builder methods (`where()`, `orderBy()`, `limit()`), including complex filtering, aggregations, and chaining.

- **Eager Loading**: You understand how to prevent N+1 queries using `with()`, comparing it to Django's `select_related()` and `prefetch_related()`.

- **Mass Assignment**: You've learned Eloquent's `$fillable` and `$guarded` protection, comparing it to Django's form validation.

- **Scopes, Accessors & Mutators**: You can create reusable query scopes, computed attributes (accessors), and value transformers (mutators), comparing them to Django's model methods and properties.

- **Model Events**: You understand Eloquent model events and observers, comparing them to Django signals and SQLAlchemy events.

- **Soft Deletes**: You've learned Eloquent's built-in soft delete feature, comparing it to Django's approach (which requires packages).

- **Eloquent Collections**: You understand that Eloquent returns Collection objects (not arrays) and know how to use Collection methods like `pluck()`, `map()`, `filter()`, and `groupBy()`.

- **Database Transactions & Raw Queries**: You can use transactions for data integrity and write raw SQL queries when needed, comparing to Django and SQLAlchemy approaches.

### Key Takeaways

1. **ORM Concepts Are Universal**: The Active Record pattern, relationships, migrations, and query builders work the same way across Django, SQLAlchemy, and Eloquent. Only syntax differs.

2. **Eloquent's Developer Experience**: Eloquent's explicit relationship methods, schema builder, and event system provide a clean, intuitive API that feels familiar to Python developers.

3. **Performance Matters**: Eager loading (`with()`) is essential for preventing N+1 queries. Always eager load relationships when you know you'll access them.

4. **Security First**: Mass assignment protection (`$fillable`) prevents unauthorized field updates. Always define `$fillable` explicitly.

5. **Code Reusability**: Scopes, accessors, and mutators help you write DRY (Don't Repeat Yourself) code and keep business logic in models where it belongs.

### What's Next?

In [Chapter 06](/series/python-developers-love-php-laravel/chapters/06-building-rest-apis-integrations-python-to-laravel), you'll learn how to build REST APIs in Laravel, comparing Flask-RESTful and Django REST Framework approaches. You'll master API routing, authentication, validation, and external integrations—bringing together everything you've learned about Laravel so far.

<ChapterCheckbox seriesId="python-developers-love-php-laravel" chapterId="05-working-with-data-eloquent-orm-database-workflow" />

## Further Reading

- [Laravel Eloquent Documentation](https://laravel.com/docs/eloquent) — Comprehensive guide to Eloquent ORM
- [Laravel Migrations Documentation](https://laravel.com/docs/migrations) — Complete migration reference
- [Laravel Relationships Documentation](https://laravel.com/docs/eloquent-relationships) — All relationship types explained
- [Django ORM Documentation](https://docs.djangoproject.com/en/stable/topics/db/) — Reference for comparison
- [SQLAlchemy Documentation](https://docs.sqlalchemy.org/) — Reference for comparison
- [Eloquent Performance Tips](https://laravel.com/docs/eloquent#query-optimization) — Optimizing Eloquent queries
- [Laravel Query Builder](https://laravel.com/docs/queries) — Advanced query building techniques
