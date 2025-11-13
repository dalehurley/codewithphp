---
title: "Mapping Concepts: Rails vs Laravel"
description: See how every Rails concept you know maps directly to Laravel—routing, models, controllers, migrations, and more.
series: rails-developers-love-laravel
chapter: 1
difficulty: Intermediate
tags: ["laravel", "rails", "comparison", "concepts", "activerecord", "eloquent"]
---

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/#choose-your-learning-path">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/rails-developers-love-laravel/">Rails to Laravel</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 01</span>
</div>

![Mapping Concepts: Rails vs Laravel](/images/rails-developers-love-laravel/chapter-01-mapping-concepts-rails-vs-laravel-hero-full.webp)

# Chapter 01: Mapping Concepts: Rails vs Laravel <span class="difficulty-badge difficulty-intermediate">Intermediate</span>

## Overview

If you've worked with Ruby on Rails, you already understand web frameworks—you just need to see how those concepts translate to Laravel. Laravel's creator, Taylor Otwell, openly acknowledges Rails as a major inspiration. The result? Two frameworks that share remarkably similar philosophies and patterns.

This chapter shows you Rails code you know, then demonstrates the Laravel equivalent. You'll discover that Laravel isn't fundamentally different from Rails—it's the same concepts with different syntax and conventions.

## What You'll Learn

- How Rails routing maps to Laravel routing
- ActiveRecord vs Eloquent ORM comparisons
- Rails controllers vs Laravel controllers
- ERB vs Blade templating
- Migrations in both frameworks
- Command-line tools: `rails` vs `artisan`
- Validation, authentication, and common patterns

## The Shared Philosophy

Both Rails and Laravel embrace:

- **Convention over configuration** — Sensible defaults that "just work"
- **MVC architecture** — Separation of concerns
- **Active Record pattern** — Models that map to database tables
- **RESTful routing** — Resource-oriented URLs
- **Developer happiness** — Focus on productivity and joy
- **Batteries included** — Full-stack frameworks with everything you need

::: tip
As a Rails developer, you're already 70% of the way to understanding Laravel. The remaining 30% is mostly syntax and PHP-specific patterns.
:::

## Quick Reference: Rails to Laravel

Here's a high-level comparison to orient you:

| Rails Concept          | Laravel Equivalent     | Notes                                     |
| ---------------------- | ---------------------- | ----------------------------------------- |
| `rails new app`        | `laravel new app`      | Create new application                    |
| `rails server`         | `php artisan serve`    | Start development server                  |
| `rails console`        | `php artisan tinker`   | Interactive REPL                          |
| `rails generate`       | `php artisan make:`    | Code generation                           |
| ActiveRecord           | Eloquent ORM           | Nearly identical Active Record pattern    |
| `routes.rb`            | `routes/web.php`       | Route definitions                         |
| Controllers            | Controllers            | Same concept, different namespace         |
| ERB templates          | Blade templates        | Similar syntax, different directives      |
| `db/migrate/`          | `database/migrations/` | Migration files                           |
| `rake db:migrate`      | `artisan migrate`      | Run migrations                            |
| `has_many`             | `hasMany()`            | Relationships (camelCase in Laravel)      |
| `validates`            | Validation rules       | Different syntax, same concept            |
| Devise                 | Breeze/Jetstream       | Authentication scaffolding                |
| RSpec/Minitest         | PHPUnit/Pest           | Testing frameworks                        |
| Bundler (`Gemfile`)    | Composer               | Dependency management                     |
| `config/application.rb`| `config/app.php`       | Application configuration                 |

## 1. Routing

### Rails Routing

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

  # Custom routes with constraints
  get 'posts/:id', to: 'posts#show', constraints: { id: /\d+/ }

  # Root route
  root 'pages#home'
end
```

### Laravel Routing

In Laravel, routes are defined in `routes/web.php` (for web routes) or `routes/api.php` (for API routes):

```php
<?php
// routes/web.php

use App\Http\Controllers\PageController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;

// Simple route
Route::get('/welcome', [PageController::class, 'welcome']);

// RESTful resource routes
Route::resource('posts', PostController::class);

// Nested resources
Route::resource('posts.comments', CommentController::class);

// Custom routes with constraints
Route::get('/posts/{id}', [PostController::class, 'show'])
    ->where('id', '[0-9]+');

// Root route
Route::get('/', [PageController::class, 'home']);
```

### Key Differences

| Aspect              | Rails                          | Laravel                                |
| ------------------- | ------------------------------ | -------------------------------------- |
| **File location**   | `config/routes.rb`             | `routes/web.php` or `routes/api.php`   |
| **Syntax**          | Ruby DSL with symbols          | PHP fluent methods                     |
| **Controller ref**  | `'controller#action'` string   | `[ControllerClass::class, 'method']`   |
| **Resources**       | `resources :posts`             | `Route::resource('posts', ...)`        |
| **Named routes**    | `as: 'profile'`                | `->name('profile')`                    |
| **Constraints**     | `constraints: { id: /\d+/ }`   | `->where('id', '[0-9]+')`              |

### Route Helpers

**Rails:**
```ruby
# Generate URL from named route
post_path(@post)           # => /posts/1
posts_path                 # => /posts
edit_post_path(@post)      # => /posts/1/edit
```

**Laravel:**
```php
<?php
// Generate URL from named route
route('posts.show', $post)   // => /posts/1
route('posts.index')         // => /posts
route('posts.edit', $post)   // => /posts/1/edit
```

::: tip Laravel Route Naming
Laravel's `Route::resource()` automatically creates named routes following the pattern `resource.action` (e.g., `posts.show`, `posts.edit`).
:::

## 2. Models and ORM

### ActiveRecord vs Eloquent

Both frameworks use the Active Record pattern, where models map directly to database tables.

**Rails Model:**
```ruby
# app/models/post.rb
class Post < ApplicationRecord
  # Relationships
  belongs_to :user
  has_many :comments, dependent: :destroy
  has_many :tags, through: :post_tags

  # Validations
  validates :title, presence: true, length: { minimum: 5 }
  validates :body, presence: true
  validates :slug, uniqueness: true

  # Scopes
  scope :published, -> { where(published: true) }
  scope :recent, -> { order(created_at: :desc).limit(10) }

  # Callbacks
  before_save :generate_slug
  after_create :notify_followers

  private

  def generate_slug
    self.slug = title.parameterize
  end

  def notify_followers
    # Notification logic
  end
end
```

**Laravel Model:**
```php
<?php
// app/Models/Post.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    // Mass assignment protection
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

    // Model events (like callbacks)
    protected static function booted()
    {
        static::saving(function ($post) {
            $post->slug = \Str::slug($post->title);
        });

        static::created(function ($post) {
            // Notification logic
        });
    }
}
```

### Validation Comparison

**Rails (model-level):**
```ruby
class Post < ApplicationRecord
  validates :title, presence: true, length: { minimum: 5, maximum: 200 }
  validates :email, format: { with: URI::MailTo::EMAIL_REGEXP }
  validates :slug, uniqueness: true
end
```

**Laravel (request-level):**
```php
<?php
// app/Http/Requests/StorePostRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function rules()
    {
        return [
            'title' => 'required|min:5|max:200',
            'email' => 'required|email',
            'slug' => 'required|unique:posts,slug',
        ];
    }
}
```

::: tip Laravel Validation Philosophy
Unlike Rails (which validates at the model level), Laravel typically validates at the request/controller level using Form Request classes. This separates validation logic from your models.
:::

## 3. Controllers

### Rails Controller

```ruby
# app/controllers/posts_controller.rb
class PostsController < ApplicationController
  before_action :set_post, only: [:show, :edit, :update, :destroy]
  before_action :authenticate_user!, except: [:index, :show]

  # GET /posts
  def index
    @posts = Post.published.recent.page(params[:page])
  end

  # GET /posts/:id
  def show
  end

  # GET /posts/new
  def new
    @post = Post.new
  end

  # POST /posts
  def create
    @post = current_user.posts.build(post_params)

    if @post.save
      redirect_to @post, notice: 'Post created successfully.'
    else
      render :new, status: :unprocessable_entity
    end
  end

  # GET /posts/:id/edit
  def edit
  end

  # PATCH/PUT /posts/:id
  def update
    if @post.update(post_params)
      redirect_to @post, notice: 'Post updated successfully.'
    else
      render :edit, status: :unprocessable_entity
    end
  end

  # DELETE /posts/:id
  def destroy
    @post.destroy
    redirect_to posts_path, notice: 'Post deleted successfully.'
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

### Laravel Controller

```php
<?php
// app/Http/Controllers/PostController.php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'show']);
    }

    // GET /posts
    public function index()
    {
        $posts = Post::published()->recent()->paginate(15);

        return view('posts.index', compact('posts'));
    }

    // GET /posts/{post}
    public function show(Post $post)
    {
        // Route model binding automatically finds the post
        return view('posts.show', compact('post'));
    }

    // GET /posts/create
    public function create()
    {
        return view('posts.create');
    }

    // POST /posts
    public function store(StorePostRequest $request)
    {
        $post = $request->user()->posts()->create($request->validated());

        return redirect()
            ->route('posts.show', $post)
            ->with('success', 'Post created successfully.');
    }

    // GET /posts/{post}/edit
    public function edit(Post $post)
    {
        return view('posts.edit', compact('post'));
    }

    // PUT/PATCH /posts/{post}
    public function update(UpdatePostRequest $request, Post $post)
    {
        $post->update($request->validated());

        return redirect()
            ->route('posts.show', $post)
            ->with('success', 'Post updated successfully.');
    }

    // DELETE /posts/{post}
    public function destroy(Post $post)
    {
        $post->delete();

        return redirect()
            ->route('posts.index')
            ->with('success', 'Post deleted successfully.');
    }
}
```

### Key Differences

| Aspect                 | Rails                                | Laravel                                     |
| ---------------------- | ------------------------------------ | ------------------------------------------- |
| **Filters/Middleware** | `before_action`                      | `middleware()` in constructor               |
| **Parameter handling** | `params[:id]`                        | Route model binding or `$request` injection |
| **Strong parameters**  | `params.require(...).permit(...)`    | Form Request validation                     |
| **Flash messages**     | `notice:` or `flash[:notice]`        | `->with('success', ...)`                    |
| **Redirects**          | `redirect_to @post`                  | `redirect()->route('posts.show', $post)`    |
| **View rendering**     | `render :show` (implicit)            | `return view('posts.show', ...)`            |

::: tip Route Model Binding
Laravel's route model binding (`Post $post` parameter) automatically queries the database and injects the model—no need for `Post::find($id)` like Rails' `@post = Post.find(params[:id])`.
:::

## 4. Views and Templates

### ERB Templates (Rails)

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
        <small>by <%= post.user.name %></small>
      </li>
    <% end %>
  </ul>

  <%= paginate @posts %>
<% else %>
  <p>No posts found.</p>
<% end %>

<%= link_to 'New Post', new_post_path, class: 'btn btn-primary' %>
```

### Blade Templates (Laravel)

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
        <small>by {{ $post->user->name }}</small>
      </li>
    @endforeach
  </ul>

  {{ $posts->links() }}
@else
  <p>No posts found.</p>
@endif

<a href="{{ route('posts.create') }}" class="btn btn-primary">New Post</a>
```

### Template Syntax Comparison

| Purpose              | Rails (ERB)                    | Laravel (Blade)                     |
| -------------------- | ------------------------------ | ----------------------------------- |
| **Execute code**     | `<% code %>`                   | (PHP code directly)                 |
| **Output escaped**   | `<%= value %>`                 | `{{ $value }}`                      |
| **Output unescaped** | `<%== html %>`                 | `{!! $html !!}`                     |
| **If statement**     | `<% if condition %>`           | `@if($condition)`                   |
| **End if**           | `<% end %>`                    | `@endif`                            |
| **Each/foreach**     | `<% @items.each do \|item\| %>`| `@foreach($items as $item)`         |
| **Comments**         | `<%# comment %>`               | `{{-- comment --}}`                 |
| **Include partial**  | `<%= render 'header' %>`       | `@include('partials.header')`       |
| **Yield section**    | `<%= yield :content %>`        | `@yield('content')`                 |

::: tip Blade Directives
Blade uses `@` directives (`@if`, `@foreach`, `@include`) which are cleaner than ERB's `<% %>` tags. Both escape output by default for XSS protection.
:::

## 5. Migrations

### Rails Migration

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

    add_index :posts, :published
  end
end
```

### Laravel Migration

```php
<?php
// database/migrations/2024_01_01_000000_create_posts_table.php

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

            $table->index('published');
        });
    }

    public function down()
    {
        Schema::dropIfExists('posts');
    }
};
```

### Migration Commands

| Task                      | Rails                          | Laravel                        |
| ------------------------- | ------------------------------ | ------------------------------ |
| **Create migration**      | `rails g migration AddXToY`    | `artisan make:migration add_x_to_y` |
| **Run migrations**        | `rake db:migrate`              | `artisan migrate`              |
| **Rollback**              | `rake db:rollback`             | `artisan migrate:rollback`     |
| **Reset database**        | `rake db:reset`                | `artisan migrate:fresh`        |
| **Seed database**         | `rake db:seed`                 | `artisan db:seed`              |
| **Check migration status**| `rake db:migrate:status`       | `artisan migrate:status`       |

## 6. Query Building

Both ActiveRecord and Eloquent provide fluent query builders:

### Rails (ActiveRecord)

```ruby
# Find by ID
post = Post.find(1)

# Find by attribute
post = Post.find_by(slug: 'hello-world')

# Where clauses
posts = Post.where(published: true)
posts = Post.where('created_at > ?', 1.week.ago)

# Chaining
posts = Post.where(published: true)
            .order(created_at: :desc)
            .limit(10)

# Joins
posts = Post.joins(:user).where(users: { active: true })

# Eager loading
posts = Post.includes(:comments, :user).all

# Pluck
titles = Post.pluck(:title)

# Count
count = Post.where(published: true).count

# First or create
post = Post.find_or_create_by(slug: 'hello') do |p|
  p.title = 'Hello World'
end
```

### Laravel (Eloquent)

```php
<?php
// Find by ID
$post = Post::find(1);

// Find by attribute
$post = Post::where('slug', 'hello-world')->first();
// Or using firstWhere
$post = Post::firstWhere('slug', 'hello-world');

// Where clauses
$posts = Post::where('published', true)->get();
$posts = Post::where('created_at', '>', now()->subWeek())->get();

// Chaining
$posts = Post::where('published', true)
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();

// Joins
$posts = Post::join('users', 'posts.user_id', '=', 'users.id')
    ->where('users.active', true)
    ->get();

// Eager loading
$posts = Post::with('comments', 'user')->get();

// Pluck
$titles = Post::pluck('title');

// Count
$count = Post::where('published', true)->count();

// First or create
$post = Post::firstOrCreate(
    ['slug' => 'hello'],
    ['title' => 'Hello World']
);
```

::: tip Query Similarities
The query interfaces are remarkably similar. The main difference is `.all` or implicit execution in Rails vs explicit `.get()` in Laravel.
:::

## 7. Command-Line Tools

### Rails CLI

```bash
# Generate scaffolding
rails generate scaffold Post title:string body:text
rails generate model Post title:string
rails generate controller Posts index show
rails generate migration AddPublishedToPosts published:boolean

# Database operations
rails db:create
rails db:migrate
rails db:seed
rails db:rollback

# Console
rails console
rails console --sandbox

# Server
rails server
rails server -p 4000

# Testing
rails test
rspec

# Routes
rails routes
rails routes -c PostsController
```

### Laravel Artisan

```bash
# Generate scaffolding (via packages)
php artisan make:model Post -mcr  # Model + Migration + Controller + Resource
php artisan make:model Post --all  # Everything
php artisan make:controller PostController --resource
php artisan make:migration add_published_to_posts

# Database operations
php artisan migrate
php artisan db:seed
php artisan migrate:rollback
php artisan migrate:fresh --seed

# Console (Tinker)
php artisan tinker

# Server
php artisan serve
php artisan serve --port=8080

# Testing
php artisan test
./vendor/bin/phpunit

# Routes
php artisan route:list
php artisan route:list --path=posts
```

## 8. Authentication

### Rails (Devise)

```ruby
# Gemfile
gem 'devise'

# Generate Devise setup
rails generate devise:install
rails generate devise User

# Controller filter
class PostsController < ApplicationController
  before_action :authenticate_user!

  def create
    @post = current_user.posts.build(post_params)
    @post.save
  end
end
```

### Laravel (Breeze/Jetstream)

```bash
# Install Laravel Breeze (simpler)
composer require laravel/breeze --dev
php artisan breeze:install
npm install && npm run dev
php artisan migrate
```

```php
<?php
// Controller middleware
class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request)
    {
        $post = $request->user()->posts()->create($request->validated());

        return redirect()->route('posts.show', $post);
    }
}
```

## 9. Testing

### Rails (RSpec)

```ruby
# spec/models/post_spec.rb
RSpec.describe Post, type: :model do
  it { should belong_to(:user) }
  it { should have_many(:comments) }
  it { should validate_presence_of(:title) }

  describe '#published?' do
    it 'returns true for published posts' do
      post = create(:post, published: true)
      expect(post).to be_published
    end
  end
end

# spec/controllers/posts_controller_spec.rb
RSpec.describe PostsController, type: :controller do
  describe 'GET #index' do
    it 'returns successful response' do
      get :index
      expect(response).to be_successful
    end
  end
end
```

### Laravel (PHPUnit/Pest)

```php
<?php
// tests/Unit/PostTest.php
namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Post;

class PostTest extends TestCase
{
    public function test_post_belongs_to_user()
    {
        $post = Post::factory()->create();

        $this->assertInstanceOf(User::class, $post->user);
    }

    public function test_title_is_required()
    {
        $this->expectException(ValidationException::class);

        Post::create(['title' => '']);
    }
}

// tests/Feature/PostControllerTest.php
namespace Tests\Feature;

use Tests\TestCase;

class PostControllerTest extends TestCase
{
    public function test_index_displays_posts()
    {
        $response = $this->get('/posts');

        $response->assertStatus(200);
        $response->assertViewIs('posts.index');
    }
}
```

## 10. Key Architectural Differences

While Rails and Laravel are similar, here are some notable differences:

### 1. Service Container (Laravel) vs. Convention (Rails)

**Laravel** uses dependency injection heavily:
```php
<?php
class PostController extends Controller
{
    public function __construct(
        private PostService $postService,
        private Cache $cache
    ) {}

    public function index()
    {
        return $this->postService->getPublished();
    }
}
```

**Rails** relies more on convention and global helpers.

### 2. Facades (Laravel) vs. Class Methods (Rails)

**Laravel Facades** provide static-like interface:
```php
<?php
Cache::put('key', 'value', 3600);
DB::table('users')->get();
Mail::to($user)->send(new Welcome());
```

**Rails** uses class methods directly:
```ruby
Rails.cache.write('key', 'value', expires_in: 1.hour)
User.all
UserMailer.welcome_email(@user).deliver_now
```

### 3. Middleware (Laravel) vs. Filters (Rails)

Both handle cross-cutting concerns, but Laravel's middleware is more explicit:

```php
<?php
// app/Http/Middleware/CheckAge.php
class CheckAge
{
    public function handle($request, Closure $next)
    {
        if ($request->age <= 18) {
            return redirect('home');
        }

        return $next($request);
    }
}
```

## Summary

As a Rails developer, you'll find Laravel remarkably familiar:

✅ **Same MVC architecture** — Models, Views, Controllers work the same way
✅ **Similar ORM** — Eloquent is nearly identical to ActiveRecord
✅ **Comparable routing** — RESTful conventions, resource routes
✅ **Familiar commands** — `artisan` mirrors `rails` CLI
✅ **Convention over configuration** — Sensible defaults everywhere
✅ **Active community** — Large ecosystem like Rails

The main differences are syntax (PHP vs Ruby) and some architectural choices around dependency injection and service containers.

## Practice Exercise

Try recreating a simple Rails model in Laravel:

**Rails:**
```ruby
class Post < ApplicationRecord
  belongs_to :user
  has_many :comments
  validates :title, presence: true
  scope :published, -> { where(published: true) }
end
```

**Your Turn:** Create the equivalent Laravel model with the same relationships, validation (via Form Request), and scope.

## Next Steps

Now that you understand how Rails concepts map to Laravel, let's dive deeper into modern PHP features that make Laravel possible:

---

::: tip Continue Learning
Move on to [Chapter 02: Modern PHP: What's Changed](/series/rails-developers-love-laravel/chapters/02-modern-php-whats-changed) to learn about modern PHP 8.4 features.
:::

<ProgressTracker seriesId="rails-developers-love-laravel" :totalChapters="11" title="Your Progress" />

<style>
table {
  font-size: 0.9rem;
}

.comparison-highlight {
  background: #f0fdfa;
  border-left: 3px solid #0d9488;
  padding: 0.5rem;
  margin: 1rem 0;
}
</style>
