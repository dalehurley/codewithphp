---
title: "01: Mapping Concepts: Rails vs Laravel"
description: "See how every Rails concept you know maps directly to Laravel-routing, models, controllers, migrations, and more"
series: rails-developers-love-laravel
chapter: 1
order: 1
difficulty: "Intermediate"
prerequisites:
  - "/series/rails-developers-love-laravel/chapters/00-introduction-why-look-at-laravel"
---

![Mapping Concepts: Rails vs Laravel](/images/rails-developers-love-laravel/chapter-01-mapping-concepts-rails-vs-laravel-hero-full.webp)

# 01: Mapping Concepts: Rails vs Laravel

## Overview

If you've worked with Ruby on Rails, you already understand web frameworks—you just need to see how those concepts translate to Laravel. Laravel's creator, Taylor Otwell, openly acknowledges Rails as a major inspiration. The result? Two frameworks that share remarkably similar philosophies and patterns.

This chapter shows you Rails code you know, then demonstrates the Laravel equivalent. You'll discover that Laravel isn't fundamentally different from Rails—it's the same concepts with different syntax and conventions.

By the end of this chapter, you'll have a comprehensive mental map that lets you confidently translate your Rails knowledge into Laravel development.

## Prerequisites

Before starting this chapter, you should have:

- Completion of [Chapter 00: Introduction](/series/rails-developers-love-laravel/chapters/00-introduction-why-look-at-laravel) or equivalent understanding
- Basic familiarity with Ruby on Rails (routing, models, controllers, migrations)
- PHP 8.4+ installed (for running Laravel examples)
- **Estimated Time**: ~45-60 minutes

**Verify your setup:**

```bash
php --version  # Should show PHP 8.4 or higher
```

## What You'll Build

By the end of this chapter, you will have:

- A comprehensive mental map of Rails-to-Laravel concept translations
- Understanding of how routing, models, controllers, and views compare
- Knowledge of migration syntax differences
- Familiarity with Laravel's command-line tools (artisan)
- A reference guide for common Rails patterns in Laravel

## Objectives

- Map Rails routing patterns to Laravel routing syntax
- Compare ActiveRecord with Eloquent ORM
- Understand controller differences between Rails and Laravel
- Translate ERB templates to Blade syntax
- Learn migration command and syntax differences
- Compare command-line tools: rails vs artisan
- Understand validation and authentication approaches

## Quick Start

If you want to see the big picture first, here's a side-by-side comparison of a simple Rails and Laravel feature:

**Rails Route + Controller:**

```ruby
# config/routes.rb
resources :posts

# app/controllers/posts_controller.rb
def index
  @posts = Post.where(published: true).order(created_at: :desc)
end
```

**Laravel Route + Controller:**

```php
# routes/web.php
Route::resource('posts', PostController::class);

# app/Http/Controllers/PostController.php
public function index()
{
    $posts = Post::where('published', true)->orderBy('created_at', 'desc')->get();
    return view('posts.index', compact('posts'));
}
```

Notice the similarities? The patterns are nearly identical—just different syntax. Let's dive into the details.

## The Shared Philosophy

Both Rails and Laravel embrace:

- **Convention over configuration** - Sensible defaults that "just work"
- **MVC architecture** - Separation of concerns
- **Active Record pattern** - Models that map to database tables
- **RESTful routing** - Resource-oriented URLs
- **Developer happiness** - Focus on productivity and joy
- **Batteries included** - Full-stack frameworks with everything you need

::: tip
As a Rails developer, you're already 70% of the way to understanding Laravel. The remaining 30% is mostly syntax and PHP-specific patterns.
:::

## Quick Reference: Rails to Laravel

Here's a high-level comparison to orient you:

| Rails Concept       | Laravel Equivalent     | Notes                                  |
| ------------------- | ---------------------- | -------------------------------------- |
| `rails new app`     | `laravel new app`      | Create new application                 |
| `rails server`      | `php artisan serve`    | Start development server               |
| `rails console`     | `php artisan tinker`   | Interactive REPL                       |
| `rails generate`    | `php artisan make:`    | Code generation                        |
| ActiveRecord        | Eloquent ORM           | Nearly identical Active Record pattern |
| `routes.rb`         | `routes/web.php`       | Route definitions                      |
| Controllers         | Controllers            | Same concept, different namespace      |
| ERB templates       | Blade templates        | Similar syntax, different directives   |
| `db/migrate/`       | `database/migrations/` | Migration files                        |
| `rake db:migrate`   | `artisan migrate`      | Run migrations                         |
| `has_many`          | `hasMany()`            | Relationships (camelCase in Laravel)   |
| `validates`         | Validation rules       | Different syntax, same concept         |
| Devise              | Breeze/Jetstream       | Authentication scaffolding             |
| RSpec/Minitest      | PHPUnit/Pest           | Testing frameworks                     |
| Bundler (`Gemfile`) | Composer               | Dependency management                  |

## Step 1: Understanding Routing (~10 min)

### Goal

Learn how Rails routing patterns translate directly to Laravel's routing syntax.

### Actions

1. **Compare basic routing syntax**

In Rails, you define routes in `config/routes.rb`:

```ruby
# config/routes.rb
Rails.application.routes.draw do
  # Simple route
  get 'welcome', to: 'pages#welcome'

  # RESTful resource routes
  resources :posts

  # Nested resources
  resources :posts do
    resources :comments
  end

  # Root route
  root 'pages#home'
end
```

In Laravel, routes are defined in `routes/web.php`:

```php
# filename: routes/web.php
<?php

use App\Http\Controllers\PageController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;

// Simple route
Route::get('/welcome', [PageController::class, 'welcome']);

// RESTful resource routes
Route::resource('posts', PostController::class);

// Nested resources
Route::resource('posts.comments', CommentController::class);

// Root route
Route::get('/', [PageController::class, 'home']);
```

2. **Understand route helpers**

**Rails:**

```ruby
post_path(@post)           # => /posts/1
posts_path                 # => /posts
edit_post_path(@post)      # => /posts/1/edit
```

**Laravel:**

```php
# filename: example-route-helpers.php
<?php

declare(strict_types=1);

// Generate URL from named route
route('posts.show', $post);   // => /posts/1
route('posts.index');         // => /posts
route('posts.edit', $post);   // => /posts/1/edit
```

### Expected Result

You should understand that:

- Rails uses symbol-based routing (`'controller#action'`)
- Laravel uses class-based routing (`[Controller::class, 'method']`)
- Both support RESTful resource routing
- Route helpers work similarly but use different syntax

### Why It Works

Both frameworks follow RESTful conventions, automatically generating standard CRUD routes. Laravel's `Route::resource()` creates the same seven routes as Rails' `resources`, following identical HTTP verb and URL patterns.

::: tip Laravel Route Naming
Laravel automatically names resource routes using the pattern `resource.action`. For example, `Route::resource('posts', PostController::class)` creates named routes like `posts.index`, `posts.show`, `posts.store`, etc.
:::

### Troubleshooting

- **Error: "Target class does not exist"** — Ensure you've imported the controller class with `use App\Http\Controllers\ControllerName;` at the top of your routes file.

- **Routes not working** — Run `php artisan route:list` to see all registered routes (equivalent to `rails routes` in Rails).

## Step 2: Comparing Models and ORM (~15 min)

### Goal

Understand how ActiveRecord and Eloquent provide nearly identical functionality with different syntax.

### Actions

1. **Compare model relationships**

**Rails Model:**

```ruby
# app/models/post.rb
class Post < ApplicationRecord
  # Relationships
  belongs_to :user
  has_many :comments, dependent: :destroy
  has_many :tags, through: :post_tags

  # Scopes
  scope :published, -> { where(published: true) }
  scope :recent, -> { order(created_at: :desc).limit(10) }

  # Callbacks
  before_save :generate_slug

  private

  def generate_slug
    self.slug = title.parameterize
  end
end
```

**Laravel Model:**

```php
# filename: app/Models/Post.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = ['title', 'body', 'slug', 'published', 'user_id'];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'post_tags');
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('published', true);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc')->limit(10);
    }

    // Model events
    protected static function booted()
    {
        static::saving(function ($post) {
            $post->slug = \Str::slug($post->title);
        });
    }
}
```

2. **Compare query building**

**Rails:**

```ruby
# Find by ID
post = Post.find(1)

# Where clauses
posts = Post.where(published: true)
            .order('created_at DESC')
            .limit(10)

# Eager loading
posts = Post.includes(:comments, :user).all
```

**Laravel:**

```php
# filename: example-queries.php
<?php

declare(strict_types=1);

use App\Models\Post;

// Find by ID
$post = Post::find(1);

// Where clauses
$posts = Post::where('published', true)
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();

// Eager loading
$posts = Post::with('comments', 'user')->get();
```

### Expected Result

You should see that:

- Relationship methods use camelCase in Laravel (`hasMany` vs `has_many`)
- Scopes are methods prefixed with `scope` in Laravel
- Query building is nearly identical between frameworks
- Laravel requires explicit `->get()` to execute queries

### Why It Works

Both frameworks implement the Active Record pattern where models directly correspond to database tables. The query builders use method chaining to construct SQL, making the APIs feel nearly identical despite being different languages.

::: tip Mass Assignment
Laravel requires you to define fillable or guarded properties for mass assignment protection, whereas Rails uses strong parameters in controllers with `params.require().permit()`.
:::

## Step 3: Translating Controllers (~10 min)

### Goal

Learn how Rails controller actions map to Laravel controller methods.

### Actions

1. **Compare a typical RESTful controller**

**Rails:**

```ruby
# app/controllers/posts_controller.rb
class PostsController < ApplicationController
  before_action :authenticate_user!, except: [:index, :show]
  before_action :set_post, only: [:show, :edit, :update, :destroy]

  def index
    @posts = Post.published.recent.page(params[:page])
  end

  def show
  end

  def create
    @post = current_user.posts.build(post_params)

    if @post.save
      redirect_to @post, notice: 'Post created successfully.'
    else
      render :new, status: :unprocessable_entity
    end
  end

  private

  def set_post
    @post = Post.find(params[:id])
  end

  def post_params
    params.require(:post).permit(:title, :body, :published)
  end
end
```

**Laravel:**

```php
# filename: app/Http/Controllers/PostController.php
<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Http\Requests\StorePostRequest;

class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'show']);
    }

    public function index()
    {
        $posts = Post::published()->recent()->paginate(15);
        return view('posts.index', compact('posts'));
    }

    public function show(Post $post)
    {
        // Route model binding automatically finds the post
        return view('posts.show', compact('post'));
    }

    public function store(StorePostRequest $request)
    {
        $post = $request->user()->posts()->create($request->validated());

        return redirect()
            ->route('posts.show', $post)
            ->with('success', 'Post created successfully.');
    }
}
```

### Expected Result

Key differences you should notice:

- Laravel uses `middleware()` instead of `before_action`
- Laravel has route model binding (no need for `set_post`)
- Validation happens in Form Request classes, not controllers
- Flash messages use `->with()` instead of `notice:`

### Why It Works

Both frameworks follow the MVC pattern with controllers handling HTTP requests. Laravel's dependency injection automatically resolves method parameters, enabling features like route model binding where `Post $post` automatically queries the database.

::: tip Route Model Binding
In Laravel, type-hinting a model in your controller method (`Post $post`) automatically queries the database using the route parameter. This eliminates Rails' need for `before_action :set_post` filters. If the record isn't found, Laravel automatically returns a 404 response.
:::

### Troubleshooting

- **Error: "Too few arguments to function"** — Ensure your route parameter name matches the variable name in the controller method. For example, if your route is `posts/{post}`, your method parameter should be `Post $post`, not `Post $item`.

- **Error: "Class 'App\Http\Controllers\Post' not found"** — You're missing the `use App\Models\Post;` import statement at the top of your controller file.

- **Middleware not working** — Verify the middleware is registered in `app/Http/Kernel.php` and that you're using the correct middleware alias in your controller.

## Step 4: Converting Templates (~8 min)

### Goal

Translate ERB template syntax to Blade template syntax.

### Actions

1. **Compare template syntax**

**Rails (ERB):**

```erb
<!-- app/views/posts/index.html.erb -->
<h1>All Posts</h1>

<% if @posts.any? %>
  <ul>
    <% @posts.each do |post| %>
      <li>
        <%= link_to post.title, post_path(post) %>
        <% if post.published? %>
          <span class="badge">Published</span>
        <% end %>
      </li>
    <% end %>
  </ul>
<% else %>
  <p>No posts found.</p>
<% end %>
```

**Laravel (Blade):**

```blade
{{-- resources/views/posts/index.blade.php --}}
<h1>All Posts</h1>

@if($posts->count() > 0)
  <ul>
    @foreach($posts as $post)
      <li>
        <a href="{{ route('posts.show', $post) }}">{{ $post->title }}</a>
        @if($post->published)
          <span class="badge">Published</span>
        @endif
      </li>
    @endforeach
  </ul>
@else
  <p>No posts found.</p>
@endif
```

2. **Compare common directives**

| Feature              | Rails (ERB)                  | Laravel (Blade)            |
| -------------------- | ---------------------------- | -------------------------- |
| **Output (escaped)** | Uses equals syntax           | Uses double-brace syntax   |
| **Conditionals**     | Tag-based if/else/end        | `@if` / `@else` / `@endif` |
| **Loops**            | Block iteration with `.each` | `@foreach` / `@endforeach` |
| **Comments**         | Hash-prefixed tags           | Double-dash curly syntax   |
| **Partials**         | `render` helper              | `@include` directive       |

**Key Similarities:**

- Both escape output by default for XSS protection
- Both support layouts and content sections
- Both compile templates for performance
- Both provide raw/unescaped output options

### Expected Result

You should understand that:

- Blade uses at-sign directives (like `@if`, `@foreach`) instead of ERB tag syntax
- Both frameworks escape output by default for security
- Blade syntax is cleaner and more readable
- Variable access uses dollar-sign prefix in Blade

### Why It Works

Both templating engines compile to native code (Ruby/PHP) for performance. They provide the same security features (auto-escaping), layout inheritance, and partials—just with different syntax.

## Step 5: Understanding Migrations (~7 min)

### Goal

Learn how Rails migrations translate to Laravel migrations.

### Actions

1. **Compare migration syntax**

**Rails:**

```ruby
# db/migrate/20240101000000_create_posts.rb
class CreatePosts < ActiveRecord::Migration[7.0]
  def change
    create_table :posts do |t|
      t.string :title, null: false
      t.text :body
      t.string :slug, index: { unique: true }
      t.boolean :published, default: false
      t.references :user, null: false, foreign_key: true

      t.timestamps
    end
  end
end
```

**Laravel:**

```php
# filename: database/migrations/2024_01_01_000000_create_posts_table.php
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
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('posts');
    }
};
```

2. **Compare migration commands**

| Task                 | Rails                       | Laravel                             |
| -------------------- | --------------------------- | ----------------------------------- |
| **Create migration** | `rails g migration AddXToY` | `artisan make:migration add_x_to_y` |
| **Run migrations**   | `rake db:migrate`           | `artisan migrate`                   |
| **Rollback**         | `rake db:rollback`          | `artisan migrate:rollback`          |
| **Reset database**   | `rake db:reset`             | `artisan migrate:fresh`             |

### Expected Result

You should see that:

- Laravel uses `up()` and `down()` methods explicitly
- Column modifiers chain in Laravel (`->nullable()->default()`)
- Foreign keys are more explicit in Laravel
- Commands are nearly identical

### Why It Works

Both frameworks provide a DSL for database schema management, allowing version control of database structure. The migration files track changes over time and can be rolled back, making database evolution safe and repeatable.

## Step 6: Validation Approaches (~5 min)

### Goal

Understand the key difference between Rails' model-level validation and Laravel's request-level validation.

### Actions

1. **Compare validation philosophies**

**Rails (Model Validation):**

```ruby
# app/models/post.rb
class Post < ApplicationRecord
  validates :title, presence: true, length: { minimum: 5, maximum: 200 }
  validates :email, format: { with: URI::MailTo::EMAIL_REGEXP }
  validates :slug, uniqueness: true
  validates :category, inclusion: { in: %w[tech business lifestyle] }
end
```

**Laravel (Request Validation):**

```php
# filename: app/Http/Requests/StorePostRequest.php
<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // or check user permissions
    }

    public function rules(): array
    {
        return [
            'title' => 'required|min:5|max:200',
            'email' => 'required|email',
            'slug' => 'required|unique:posts,slug',
            'category' => 'in:tech,business,lifestyle',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Please provide a post title',
            'title.min' => 'Title must be at least 5 characters',
        ];
    }
}
```

### Expected Result

You should understand that:

- Rails validates at the model level before saving to database
- Laravel validates at the HTTP request level before reaching controllers
- Laravel's approach separates validation logic from models
- Both provide similar validation rules with different syntax

### Why It Works

Laravel's request validation approach decouples validation from models, allowing different validation rules for the same model depending on the context (create vs update, public API vs admin panel). Rails keeps validation closer to the model for consistency.

::: tip Validation Benefits
Laravel Form Requests also handle authorization logic, allowing you to check if the user has permission to perform the action in the same class that validates the input.
:::

## Step 7: Command-Line Tools Comparison (~5 min)

### Goal

Map Rails CLI commands to Laravel Artisan equivalents.

### Actions

**Rails CLI:**

```bash
# Generate scaffolding
rails generate scaffold Post title:string body:text
rails generate model Post title:string
rails generate controller Posts index show

# Database operations
rails db:migrate
rails db:seed
rails db:rollback

# Console and server
rails console
rails server
```

**Laravel Artisan:**

```bash
# Generate scaffolding
php artisan make:model Post -mcr  # Model + Migration + Controller + Resource
php artisan make:controller PostController --resource
php artisan make:migration create_posts_table

# Database operations
php artisan migrate
php artisan db:seed
php artisan migrate:rollback

# Console and server
php artisan tinker
php artisan serve
```

### Expected Result

You should see that Artisan provides the same code generation and database management capabilities as Rails, just with `php artisan` instead of `rails`.

### Why It Works

Both frameworks embrace code generation to boost productivity. Artisan's `make:` commands parallel Rails' `generate` commands, creating boilerplate files that follow framework conventions.

## Wrap-up

You've completed a comprehensive mapping of Rails to Laravel concepts! Here's what you've accomplished:

✅ **Mapped routing patterns** - Translated Rails routes to Laravel syntax with route model binding  
✅ **Compared ORMs** - Understood ActiveRecord vs Eloquent similarities and query building  
✅ **Learned controller differences** - Saw how Rails controllers map to Laravel with dependency injection  
✅ **Translated templates** - Converted ERB to Blade syntax with cleaner directives  
✅ **Compared migrations** - Understood database schema differences and chaining modifiers  
✅ **Understood validation** - Compared model-level vs request-level validation approaches  
✅ **Learned command-line tools** - Mapped rails commands to artisan equivalents

As a Rails developer, you now understand that Laravel isn't fundamentally different—it's the same concepts with different syntax and some architectural improvements. The main differences are:

- **Syntax**: PHP vs Ruby, but patterns remain familiar
- **Architecture**: Laravel's service container and dependency injection
- **Validation**: Request-level in Laravel vs model-level in Rails
- **Routing**: Class-based controllers vs string-based in Rails
- **Model Binding**: Automatic in Laravel vs explicit `before_action` in Rails

You're now ready to dive deeper into modern PHP features that make Laravel possible, and see how PHP 8.4 brings many features Rails developers already know and love.

## Exercises

### Exercise 1: Translate a Rails Model to Laravel

**Goal**: Practice translating Rails ActiveRecord patterns to Laravel Eloquent

Create a Laravel model equivalent to this Rails model:

```ruby
class Post < ApplicationRecord
  belongs_to :user
  has_many :comments
  validates :title, presence: true
  scope :published, -> { where(published: true) }
end
```

Your Laravel model should include:

- The same relationships (belongsTo, hasMany)
- A published scope method
- Proper namespace and type hints

**Validation**: Test your implementation:

```php
$post = Post::factory()->create(['published' => true]);

// Test relationship
$this->assertInstanceOf(User::class, $post->user);

// Test scope
$published = Post::published()->get();
```

Expected result: A working Laravel model that mirrors the Rails model's functionality.

## Further Reading

- [Laravel Routing Documentation](https://laravel.com/docs/routing) - Official routing guide
- [Eloquent ORM Documentation](https://laravel.com/docs/eloquent) - Complete Eloquent reference
- [Laravel Migrations](https://laravel.com/docs/migrations) - Database migration guide
- [Blade Templates](https://laravel.com/docs/blade) - Blade templating documentation
- [Ruby on Rails Guides](https://guides.rubyonrails.org/) - Rails official documentation for comparison

<ChapterCheckbox 
  seriesId="rails-developers-love-laravel"
  chapterId="01"
  label="You've mapped Rails concepts to Laravel!"
/>

::: tip Continue Learning
Move on to [Chapter 02: Modern PHP: What's Changed](/series/rails-developers-love-laravel/chapters/02-modern-php-whats-changed) to learn about modern PHP 8.4 features.
:::
