---
title: "05: Working with Data: Eloquent ORM & Database Workflow"
description: "Master Laravel's Eloquent ORM from an ActiveRecord perspective-relationships, scopes, eager loading, and database patterns you already know."
series: rails-developers-love-laravel
chapter: 5
order: 5
difficulty: "Intermediate"
prerequisites:
  - "/series/rails-developers-love-laravel/chapters/04-php-syntax-for-rails-devs"
tags: ["eloquent", "activerecord", "orm", "database", "laravel", "rails"]
---

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/#choose-your-learning-path">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/rails-developers-love-laravel/">Rails to Laravel</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 05</span>
</div>

![Working with Data: Eloquent ORM & Database Workflow](/images/rails-developers-love-laravel/chapter-05-eloquent-orm-hero-full.webp)

# 05: Working with Data: Eloquent ORM & Database Workflow <span class="difficulty-badge difficulty-intermediate">Intermediate</span>

## Overview

If you've worked with ActiveRecord, you already understand the Active Record pattern: models that map to database tables, relationships that connect data, migrations that version your schema, and query builders that let you chain methods. Eloquent ORM-Laravel's database abstraction layer-follows the exact same principles with PHP syntax.

This chapter shows you ActiveRecord code you know, then demonstrates the Eloquent equivalent. You'll discover that working with databases in Laravel feels remarkably similar to Rails.

## Prerequisites

Before starting this chapter, you should have:

- Completion of [Chapter 04: PHP Syntax & Language Differences for Rails Devs](/series/rails-developers-love-laravel/chapters/04-php-syntax-for-rails-devs) or equivalent understanding
- Familiarity with ActiveRecord in Rails (models, relationships, migrations, queries)
- Basic understanding of database concepts (tables, relationships, indexes)
- PHP 8.4+ installed (optional for this chapter, but recommended)
- **Estimated Time**: ~60-75 minutes

**Verify your setup (optional):**

```bash
# Check PHP version if you have it installed
php --version
```

## What You'll Build

By the end of this chapter, you will understand:

- How to define Eloquent models with relationships (equivalent to ActiveRecord models)
- How to query databases using Eloquent (equivalent to ActiveRecord queries)
- How to use scopes, eager loading, and model events
- How to write migrations and seeders
- How to work with factories for testing
- How to implement advanced patterns like soft deletes and transactions

You'll be able to translate any ActiveRecord pattern you know into its Eloquent equivalent.

## What You'll Learn

- Model definitions and conventions
- Eloquent relationships vs ActiveRecord associations
- Dependent options and cascading deletes
- Query building and scopes
- Global scopes (default_scope equivalent)
- Eager loading (N+1 prevention)
- Touch associations
- Migrations and schema building
- Model events (like ActiveRecord callbacks)
- Accessors, mutators, and casts
- Enum attributes and casting
- Model validation (with Laravel's preferred approach)
- Database transactions
- Advanced patterns

## 📦 Code Samples

Eloquent ORM patterns and examples:

- **[Eloquent ORM Examples](https://github.com/dalehurley/codewithphp/blob/main/code/rails-developers-love-laravel/chapter-05/EloquentExamples.php)** — Query patterns, scopes, relationships, and best practices

Production-ready project with complete Eloquent usage:

- **[TaskMaster Application](https://github.com/dalehurley/codewithphp/tree/main/code/rails-developers-love-laravel/chapter-10)** — Full Laravel app with models, migrations, relationships, factories, and tests

Access all code samples:

```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/rails-developers-love-laravel
```

## Quick ORM Comparison

| Rails (ActiveRecord)           | Laravel (Eloquent)                  |
| ------------------------------ | ----------------------------------- |
| `rails g model User`           | `artisan make:model User -m`        |
| `belongs_to :user`             | `belongsTo(User::class)`            |
| `has_many :posts`              | `hasMany(Post::class)`              |
| `has_and_belongs_to_many`      | `belongsToMany()`                   |
| `scope :active`                | `scopeActive($query)`               |
| `User.where(...)`              | `User::where(...)->get()`           |
| `includes(:posts)`             | `with('posts')`                     |
| `before_save :callback`        | `static::saving(fn($model) => ...)` |
| `validates :name`              | Form Request validation             |
| `.pluck(:name)`                | `->pluck('name')`                   |

## 1. Model Basics

### Rails Model

```ruby
# app/models/user.rb
class User < ApplicationRecord
  # Table name automatically inferred: users
  # Primary key automatically: id
  # Timestamps automatically: created_at, updated_at
end

# Usage
user = User.new(name: 'John', email: 'john@example.com')
user.save

user = User.create(name: 'John', email: 'john@example.com')
user = User.find(1)
user.update(name: 'Jane')
user.destroy
```

### Laravel Model

```php
<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    // Table name automatically inferred: users
    // Primary key automatically: id
    // Timestamps automatically: created_at, updated_at

    // Mass assignment protection
    protected $fillable = ['name', 'email'];
    // Or specify guarded (opposite)
    // protected $guarded = [];
}

// Usage
$user = new User(['name' => 'John', 'email' => 'john@example.com']);
$user->save();

$user = User::create(['name' => 'John', 'email' => 'john@example.com']);
$user = User::find(1);
$user->update(['name' => 'Jane']);
$user->delete();
```

::: tip Mass Assignment Protection
Laravel requires you to explicitly define `$fillable` (whitelist) or `$guarded` (blacklist) for mass assignment. Rails uses strong parameters in controllers instead.
:::

### Custom Table Names and Keys

**Rails:**
```ruby
class User < ApplicationRecord
  self.table_name = 'custom_users'
  self.primary_key = 'user_id'
end
```

**Laravel:**
```php
<?php
class User extends Model
{
    protected $table = 'custom_users';
    protected $primaryKey = 'user_id';
    public $incrementing = false;  // If not auto-increment
    protected $keyType = 'string';  // If UUID
}
```

## 2. Relationships

Eloquent relationships are nearly identical to ActiveRecord associations.

### One-to-Many

**Rails:**
```ruby
# app/models/user.rb
class User < ApplicationRecord
  has_many :posts, dependent: :destroy
end

# app/models/post.rb
class Post < ApplicationRecord
  belongs_to :user
end

# Usage
user = User.first
user.posts                   # => [Post, Post, ...]
user.posts.create(title: 'Hello')

post = Post.first
post.user                    # => User
```

**Laravel:**
```php
<?php
// app/Models/User.php
class User extends Model
{
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}

// app/Models/Post.php
class Post extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

// Usage
$user = User::first();
$user->posts;                // => Collection of Post models
$user->posts()->create(['title' => 'Hello']);

$post = Post::first();
$post->user;                 // => User
```

::: tip Dependent Options and Cascades
In Rails, you use `dependent: :destroy` on the relationship. In Laravel, cascading deletes are typically handled at the **database level** in migrations using `cascadeOnDelete()`:

```php
// Migration
$table->foreignId('user_id')
    ->constrained()
    ->cascadeOnDelete();  // Equivalent to dependent: :destroy
```

For model-level cleanup (like `dependent: :delete_all` or `dependent: :nullify`), use model events in Laravel:

```php
// In User model
protected static function booted()
{
    static::deleting(function ($user) {
        // Option 1: Delete all posts (delete_all)
        $user->posts()->delete();
        
        // Option 2: Nullify foreign keys (nullify)
        // $user->posts()->update(['user_id' => null]);
    });
}
```
:::

### One-to-One

**Rails:**
```ruby
class User < ApplicationRecord
  has_one :profile
end

class Profile < ApplicationRecord
  belongs_to :user
end
```

**Laravel:**
```php
<?php
class User extends Model
{
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }
}

class Profile extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

### Many-to-Many

**Rails:**
```ruby
class Post < ApplicationRecord
  has_and_belongs_to_many :tags
  # Or with through:
  # has_many :post_tags
  # has_many :tags, through: :post_tags
end

class Tag < ApplicationRecord
  has_and_belongs_to_many :posts
end

# Usage
post.tags << Tag.first
post.tags.attach(tag_id)
```

**Laravel:**
```php
<?php
class Post extends Model
{
    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }
}

class Tag extends Model
{
    public function posts()
    {
        return $this->belongsToMany(Post::class);
    }
}

// Usage
$post->tags()->attach($tagId);
$post->tags()->detach($tagId);
$post->tags()->sync([1, 2, 3]);
```

### Pivot Table Data

**Rails:**
```ruby
class Post < ApplicationRecord
  has_many :post_tags
  has_many :tags, through: :post_tags
end

class PostTag < ApplicationRecord
  belongs_to :post
  belongs_to :tag
end

# Access pivot data
post.post_tags.first.created_at
```

**Laravel:**
```php
<?php
class Post extends Model
{
    public function tags()
    {
        return $this->belongsToMany(Tag::class)
            ->withPivot('created_at', 'updated_at')
            ->withTimestamps();
    }
}

// Access pivot data
$post->tags->first()->pivot->created_at;
```

### Polymorphic Relationships

**Rails:**
```ruby
class Comment < ApplicationRecord
  belongs_to :commentable, polymorphic: true
end

class Post < ApplicationRecord
  has_many :comments, as: :commentable
end

class Video < ApplicationRecord
  has_many :comments, as: :commentable
end
```

**Laravel:**
```php
<?php
class Comment extends Model
{
    public function commentable()
    {
        return $this->morphTo();
    }
}

class Post extends Model
{
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}

class Video extends Model
{
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
```

### Touch Associations

**Rails:**
```ruby
class Post < ApplicationRecord
  belongs_to :user, touch: true
  # Updates user.updated_at when post is saved
end
```

**Laravel:**
```php
<?php
class Post extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class)->withDefault();
    }
    
    // Manual touch in model events
    protected static function booted()
    {
        static::saved(function ($post) {
            $post->user->touch();  // Update user's updated_at
        });
    }
}

// Or use $touches property (shorthand)
class Post extends Model
{
    protected $touches = ['user'];  // Auto-touches user when post is saved
}
```

::: tip Touch on Update
Both frameworks allow you to automatically update a parent model's `updated_at` timestamp when a child model is saved. Laravel's `$touches` property is cleaner than manual events.
:::

## 3. Querying

### Basic Queries

**Rails:**
```ruby
# Find by ID
user = User.find(1)
user = User.find_by(email: 'john@example.com')

# Where clauses
users = User.where(active: true)
users = User.where('age > ?', 18)
users = User.where(active: true).where('age > ?', 18)

# Ordering
users = User.order(created_at: :desc)
users = User.order('name ASC, created_at DESC')

# Limiting
users = User.limit(10)
users = User.limit(10).offset(20)

# Selecting specific columns
users = User.select(:id, :name, :email)

# First, last, all
user = User.first
user = User.last
users = User.all
```

**Laravel:**
```php
<?php
// Find by ID
$user = User::find(1);
$user = User::where('email', 'john@example.com')->first();
// Or shorthand
$user = User::firstWhere('email', 'john@example.com');

// Where clauses
$users = User::where('active', true)->get();
$users = User::where('age', '>', 18)->get();
$users = User::where('active', true)->where('age', '>', 18)->get();

// Ordering
$users = User::orderBy('created_at', 'desc')->get();
$users = User::orderBy('name')->orderBy('created_at', 'desc')->get();

// Limiting
$users = User::limit(10)->get();
$users = User::skip(20)->take(10)->get();

// Selecting specific columns
$users = User::select('id', 'name', 'email')->get();

// First, all
$user = User::first();
$user = User::latest()->first();  // Latest by created_at
$users = User::all();
```

::: tip Explicit .get()
Unlike Rails where queries execute automatically, Laravel requires explicit `->get()`, `->first()`, or `->all()` to execute queries. This makes it clearer when database calls happen.
:::

### Advanced Queries

**Rails:**
```ruby
# OR conditions
users = User.where(role: 'admin').or(User.where(role: 'moderator'))

# IN queries
users = User.where(id: [1, 2, 3])

# LIKE queries
users = User.where('name LIKE ?', '%john%')

# Joins
users = User.joins(:posts).where(posts: { published: true })

# Count, sum, average
count = User.count
total = Order.sum(:amount)
avg = Order.average(:amount)

# Distinct
users = User.select(:email).distinct

# Exists
exists = User.where(email: 'test@example.com').exists?

# Raw SQL
users = User.find_by_sql('SELECT * FROM users WHERE ...')
```

**Laravel:**
```php
<?php
// OR conditions
$users = User::where('role', 'admin')
    ->orWhere('role', 'moderator')
    ->get();

// IN queries
$users = User::whereIn('id', [1, 2, 3])->get();

// LIKE queries
$users = User::where('name', 'LIKE', '%john%')->get();

// Joins
$users = User::join('posts', 'users.id', '=', 'posts.user_id')
    ->where('posts.published', true)
    ->get();

// Count, sum, average
$count = User::count();
$total = Order::sum('amount');
$avg = Order::avg('amount');

// Distinct
$users = User::select('email')->distinct()->get();

// Exists
$exists = User::where('email', 'test@example.com')->exists();

// Raw SQL
$users = DB::select('SELECT * FROM users WHERE ...');
```

## 4. Scopes

Scopes let you reuse query logic, just like Rails.

**Rails:**
```ruby
class Post < ApplicationRecord
  scope :published, -> { where(published: true) }
  scope :recent, -> { order(created_at: :desc).limit(10) }
  scope :by_author, ->(author_id) { where(author_id: author_id) }

  # Can also use class methods
  def self.featured
    where(featured: true).order(views: :desc)
  end
end

# Usage
Post.published
Post.recent
Post.by_author(user.id)
Post.published.recent
```

**Laravel:**
```php
<?php
class Post extends Model
{
    public function scopePublished($query)
    {
        return $query->where('published', true);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc')->limit(10);
    }

    public function scopeByAuthor($query, $authorId)
    {
        return $query->where('author_id', $authorId);
    }

    // Can also use static methods
    public static function featured()
    {
        return static::where('featured', true)->orderBy('views', 'desc')->get();
    }
}

// Usage (Laravel automatically removes 'scope' prefix)
Post::published()->get();
Post::recent()->get();
Post::byAuthor($user->id)->get();
Post::published()->recent()->get();
```

### Global Scopes (Default Scopes)

**Rails:**
```ruby
class Post < ApplicationRecord
  default_scope { where(published: true) }
  # Or unscoped to bypass
  # Post.unscoped.all
end

# Usage
Post.all  # Automatically includes where(published: true)
```

**Laravel:**
```php
<?php
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected static function booted()
    {
        static::addGlobalScope('published', function (Builder $builder) {
            $builder->where('published', true);
        });
    }
}

// Usage
Post::all();  // Automatically includes where('published', true)

// Bypass global scope
Post::withoutGlobalScope('published')->get();
Post::withoutGlobalScopes()->get();  // Remove all global scopes
```

::: tip Global Scopes
Both Rails `default_scope` and Laravel global scopes apply automatically to all queries. Use sparingly and document them well, as they can make debugging harder when queries behave unexpectedly.
:::

## 5. Eager Loading (N+1 Prevention)

Both frameworks provide eager loading to prevent N+1 queries.

**Rails:**
```ruby
# N+1 problem
posts = Post.all
posts.each do |post|
  puts post.user.name  # Query for each post!
end

# Solution: eager loading
posts = Post.includes(:user)
posts.each do |post|
  puts post.user.name  # No additional queries!
end

# Multiple relations
posts = Post.includes(:user, :comments, :tags)

# Nested relations
posts = Post.includes(comments: :user)
```

**Laravel:**
```php
<?php
// N+1 problem
$posts = Post::all();
foreach ($posts as $post) {
    echo $post->user->name;  // Query for each post!
}

// Solution: eager loading
$posts = Post::with('user')->get();
foreach ($posts as $post) {
    echo $post->user->name;  // No additional queries!
}

// Multiple relations
$posts = Post::with(['user', 'comments', 'tags'])->get();

// Nested relations
$posts = Post::with('comments.user')->get();

// Conditional eager loading
$posts = Post::with(['comments' => function ($query) {
    $query->where('approved', true);
}])->get();
```

## 6. Model Events (Callbacks)

**Rails:**
```ruby
class Post < ApplicationRecord
  before_save :generate_slug
  after_create :notify_followers
  before_destroy :cleanup_files

  private

  def generate_slug
    self.slug = title.parameterize
  end

  def notify_followers
    # Send notifications
  end

  def cleanup_files
    # Delete associated files
  end
end
```

**Laravel:**
```php
<?php
class Post extends Model
{
    protected static function booted()
    {
        static::saving(function ($post) {
            $post->slug = \Str::slug($post->title);
        });

        static::created(function ($post) {
            // Send notifications
        });

        static::deleting(function ($post) {
            // Cleanup files
        });
    }
}

// Or use observers for complex logic
// php artisan make:observer PostObserver --model=Post
```

Available events:
- `creating`, `created`
- `updating`, `updated`
- `saving`, `saved` (fires on both create and update)
- `deleting`, `deleted`
- `restoring`, `restored` (soft deletes)

## 7. Accessors and Mutators

**Rails:**
```ruby
class User < ApplicationRecord
  def full_name
    "#{first_name} #{last_name}"
  end

  def full_name=(value)
    parts = value.split(' ')
    self.first_name = parts[0]
    self.last_name = parts[1]
  end
end

# Usage
user.full_name = 'John Doe'
puts user.full_name  # => "John Doe"
```

**Laravel (Traditional):**
```php
<?php
class User extends Model
{
    // Accessor (get)
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    // Mutator (set)
    public function setFullNameAttribute($value)
    {
        $parts = explode(' ', $value);
        $this->attributes['first_name'] = $parts[0];
        $this->attributes['last_name'] = $parts[1] ?? '';
    }
}

// Usage
$user->full_name = 'John Doe';
echo $user->full_name;  // => "John Doe"
```

**Laravel 8.4+ (Property Hooks):**
```php
<?php
class User extends Model
{
    public string $full_name {
        get => "{$this->first_name} {$this->last_name}";

        set(string $value) {
            [$this->first_name, $this->last_name] = explode(' ', $value);
        }
    }
}
```

## 8. Attribute Casting

**Rails:**
```ruby
class Post < ApplicationRecord
  # Rails infers types from database schema
  # For custom casting:
  attribute :metadata, :json
  attribute :published_at, :datetime
end
```

**Laravel:**
```php
<?php
class Post extends Model
{
    protected $casts = [
        'published_at' => 'datetime',
        'metadata' => 'array',
        'is_published' => 'boolean',
        'views' => 'integer',
        'rating' => 'decimal:2',
    ];
}

// Usage
$post->metadata = ['key' => 'value'];  // Automatically JSON-encoded
$post->save();

$data = $post->metadata;  // Automatically decoded to array
```

### Enums

**Rails:**
```ruby
class Post < ApplicationRecord
  enum status: { draft: 0, published: 1, archived: 2 }
end

# Usage
post.status = 'published'
post.published?  # => true
Post.published   # => [Post, Post, ...]
```

**Laravel (8.75+):**
```php
<?php
use App\Enums\PostStatus;

class Post extends Model
{
    protected $casts = [
        'status' => PostStatus::class,
    ];
}

// Define enum class (PHP 8.1+)
enum PostStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';
}

// Usage
$post->status = PostStatus::PUBLISHED;
$post->status === PostStatus::PUBLISHED;  // => true
Post::where('status', PostStatus::PUBLISHED)->get();
```

::: tip Enums in Laravel
Laravel 8.75+ introduced native enum support. Before that, you'd use string/integer values with constants or a package. PHP 8.1+ native enums are type-safe and work seamlessly with Eloquent.
:::

### Model Validation

**Rails:**
```ruby
class Post < ApplicationRecord
  validates :title, presence: true, length: { minimum: 5 }
  validates :slug, uniqueness: true
  validates :published_at, presence: true, if: :published?
end
```

**Laravel:**
```php
<?php
use Illuminate\Validation\ValidationException;

class Post extends Model
{
    protected static function booted()
    {
        static::saving(function ($post) {
            $validator = \Validator::make($post->toArray(), [
                'title' => 'required|min:5',
                'slug' => 'unique:posts,slug,' . $post->id,
                'published_at' => 'required_if:published,true',
            ]);
            
            if ($validator->fails()) {
                throw ValidationException::withMessages($validator->errors()->toArray());
            }
        });
    }
}
```

::: warning Laravel Validation Philosophy
While Laravel **can** validate at the model level (like Rails), Laravel's preferred approach is **Form Request validation** at the controller level. This separates validation logic from models and makes it reusable. Model-level validation is useful for data integrity, but most validation should happen in Form Request classes. See [Chapter 06: Building REST APIs](/series/rails-developers-love-laravel/chapters/06-building-rest-apis) for Form Request examples.
:::

## 9. Soft Deletes

**Rails:**
```ruby
# Requires paranoia gem or custom implementation
gem 'paranoia'

class Post < ApplicationRecord
  acts_as_paranoid
end

post.destroy           # Soft delete
post.really_destroy!   # Hard delete
Post.with_deleted      # Include soft-deleted
```

**Laravel:**
```php
<?php
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes;
}

// Migration must have deleted_at column
// $table->softDeletes();

$post->delete();                  // Soft delete
$post->forceDelete();             // Hard delete
Post::withTrashed()->get();       // Include soft-deleted
Post::onlyTrashed()->get();       // Only soft-deleted
$post->restore();                 // Restore soft-deleted
```

## 10. Transactions

**Rails:**
```ruby
ActiveRecord::Base.transaction do
  user.save!
  post.save!
  # All or nothing
end
```

**Laravel:**
```php
<?php
use Illuminate\Support\Facades\DB;

DB::transaction(function () use ($user, $post) {
    $user->save();
    $post->save();
    // All or nothing
});

// Or manual control
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

## 11. Pagination

**Rails:**
```ruby
# Using kaminari or will_paginate
@posts = Post.page(params[:page]).per(15)

# In view
<%= paginate @posts %>
```

**Laravel:**
```php
<?php
// Controller
$posts = Post::paginate(15);
// Or cursor pagination
$posts = Post::cursorPaginate(15);

return view('posts.index', compact('posts'));

// In Blade view
{{ $posts->links() }}

// API response
return response()->json($posts);
// Includes: data, current_page, last_page, per_page, total, etc.
```

## 12. Mass Assignment

**Rails:**
```ruby
# Controller - strong parameters
def user_params
  params.require(:user).permit(:name, :email, :role)
end

def create
  @user = User.create(user_params)
end
```

**Laravel:**
```php
<?php
// Model - fillable/guarded
class User extends Model
{
    protected $fillable = ['name', 'email', 'role'];
    // Or: protected $guarded = ['id', 'admin'];
}

// Controller
public function store(Request $request)
{
    $user = User::create($request->validated());
}
```

## 13. Query Performance

### Select Specific Columns

**Rails:**
```ruby
# Instead of SELECT *
users = User.select(:id, :name, :email)
```

**Laravel:**
```php
<?php
// Instead of SELECT *
$users = User::select(['id', 'name', 'email'])->get();
// Or
$users = User::get(['id', 'name', 'email']);
```

### Chunk Large Datasets

**Rails:**
```ruby
User.find_each(batch_size: 100) do |user|
  # Process user
end
```

**Laravel:**
```php
<?php
User::chunk(100, function ($users) {
    foreach ($users as $user) {
        // Process user
    }
});

// Or lazy collection (PHP 8+)
User::lazy()->each(function ($user) {
    // Process user
});
```

## 14. Database Migrations

### Creating Tables

**Rails:**
```ruby
class CreatePosts < ActiveRecord::Migration[7.0]
  def change
    create_table :posts do |t|
      t.string :title, null: false
      t.text :body
      t.string :slug, index: { unique: true }
      t.boolean :published, default: false
      t.integer :views, default: 0
      t.references :user, null: false, foreign_key: true

      t.timestamps
    end

    add_index :posts, :published
  end
end
```

**Laravel:**
```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('slug')->unique();
            $table->boolean('published')->default(false);
            $table->integer('views')->default(0);
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->timestamps();

            $table->index('published');
        });
    }

    public function down()
    {
        Schema::dropIfExists('posts');
    }
};
```

### Modifying Tables

**Rails:**
```ruby
class AddSlugToPosts < ActiveRecord::Migration[7.0]
  def change
    add_column :posts, :slug, :string
    add_index :posts, :slug, unique: true
  end
end
```

**Laravel:**
```php
<?php
return new class extends Migration
{
    public function up()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('slug')->after('title');
            $table->unique('slug');
        });
    }

    public function down()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
```

## 15. Seeders

**Rails:**
```ruby
# db/seeds.rb
User.create!(
  name: 'Admin',
  email: 'admin@example.com',
  role: 'admin'
)

10.times do |i|
  Post.create!(
    title: "Post #{i}",
    body: "Content for post #{i}",
    user: User.first
  )
end
```

**Laravel:**
```php
<?php
// database/seeders/DatabaseSeeder.php
namespace Database\Seeders;

use App\Models\User;
use App\Models\Post;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        for ($i = 0; $i < 10; $i++) {
            Post::create([
                'title' => "Post {$i}",
                'body' => "Content for post {$i}",
                'user_id' => User::first()->id,
            ]);
        }
    }
}

// Run: php artisan db:seed
```

## 16. Factories (Testing)

**Rails (FactoryBot):**
```ruby
# spec/factories/users.rb
FactoryBot.define do
  factory :user do
    name { "John Doe" }
    email { "john@example.com" }
    role { "user" }

    trait :admin do
      role { "admin" }
    end
  end
end

# Usage
user = create(:user)
admin = create(:user, :admin)
users = create_list(:user, 5)
```

**Laravel:**
```php
<?php
// database/factories/UserFactory.php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    public function definition()
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'role' => 'user',
        ];
    }

    public function admin()
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
        ]);
    }
}

// Usage in tests
$user = User::factory()->create();
$admin = User::factory()->admin()->create();
$users = User::factory()->count(5)->create();
```

## Wrap-up

You've now mastered Eloquent ORM and how it compares to ActiveRecord:

- ✓ **Model basics** - Eloquent models follow the same Active Record pattern as Rails
- ✓ **Relationships** - `hasMany`, `belongsTo`, `belongsToMany`, and polymorphic relationships work just like Rails associations
- ✓ **Dependent options** - Cascading deletes handled in migrations (`cascadeOnDelete()`) or model events
- ✓ **Query building** - Chainable queries with explicit `->get()` execution (more explicit than Rails)
- ✓ **Scopes** - Reusable query logic with `scope*` methods
- ✓ **Global scopes** - Apply default conditions automatically (equivalent to Rails `default_scope`)
- ✓ **Touch associations** - Use `$touches` property to update parent timestamps automatically
- ✓ **Eager loading** - Use `with()` to prevent N+1 queries (equivalent to `includes`)
- ✓ **Migrations** - Database schema version control with Laravel migrations
- ✓ **Model events** - Use `booted()` method for callbacks (equivalent to ActiveRecord callbacks)
- ✓ **Soft deletes** - Built-in support with `SoftDeletes` trait
- ✓ **Enums** - PHP 8.1+ native enums work seamlessly with Eloquent casting
- ✓ **Model validation** - Possible at model level, but Laravel prefers Form Request validation
- ✓ **Mass assignment** - Use `$fillable` or `$guarded` (Laravel) vs strong parameters (Rails controllers)
- ✓ **Factories** - Test data generation similar to FactoryBot
- ✓ **Advanced patterns** - Transactions, pagination, chunking, and performance optimization

The core patterns are identical between Eloquent and ActiveRecord. The main differences are PHP syntax and Laravel's explicit query execution (`->get()`, `->first()`) compared to Rails' implicit execution.

You now have the foundation to work with databases in Laravel using all the ActiveRecord patterns you already know!

## Practice Exercise

Convert this Rails model to Laravel:

```ruby
class Article < ApplicationRecord
  belongs_to :author, class_name: 'User'
  has_many :comments, dependent: :destroy
  has_and_belongs_to_many :categories

  scope :published, -> { where(published: true) }
  scope :recent, ->(limit = 10) { order(created_at: :desc).limit(limit) }

  validates :title, presence: true, length: { minimum: 5 }
  validates :slug, uniqueness: true

  before_save :generate_slug

  def read_time
    (body.split.size / 200.0).ceil
  end

  private

  def generate_slug
    self.slug ||= title.parameterize
  end
end
```

Create the equivalent Eloquent model with:
- Relationships
- Scopes
- Model events
- Accessor for `read_time`

---

## Further Reading

- [Laravel Eloquent Documentation](https://laravel.com/docs/eloquent) — Official documentation for Eloquent ORM, relationships, and query building
- [Laravel Database Migrations](https://laravel.com/docs/migrations) — Complete guide to database migrations and schema building
- [Laravel Database Factories](https://laravel.com/docs/database-testing#defining-model-factories) — Guide to creating test data with factories
- [Laravel Soft Deletes](https://laravel.com/docs/eloquent#soft-deleting) — Documentation for soft delete functionality
- [Laravel Query Builder](https://laravel.com/docs/queries) — Advanced query building techniques
- [Laravel Relationships](https://laravel.com/docs/eloquent-relationships) — Comprehensive guide to all relationship types
- [Laravel Model Events](https://laravel.com/docs/eloquent#events) — Complete reference for model lifecycle events
- [Laravel Database Transactions](https://laravel.com/docs/database#database-transactions) — Guide to using database transactions
- [Active Record Pattern](https://en.wikipedia.org/wiki/Active_record_pattern) — Wikipedia article explaining the Active Record pattern

::: tip Continue Learning
Move on to [Chapter 06: Building REST APIs: From Rails to Laravel](/series/rails-developers-love-laravel/chapters/06-building-rest-apis) to learn about API development.
:::

<ChapterCheckbox 
  seriesId="rails-developers-love-laravel"
  chapterId="05"
  label="Mastered Eloquent ORM from an ActiveRecord perspective!"
/>

<ProgressTracker seriesId="rails-developers-love-laravel" :totalChapters="11" title="Your Progress" />
