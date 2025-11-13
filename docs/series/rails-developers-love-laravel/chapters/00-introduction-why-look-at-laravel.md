---
title: "Introduction: Why Look at Laravel"
description: Discover why Laravel deserves your attention as a Rails developer, and how your Rails knowledge translates to Laravel.
series: rails-developers-love-laravel
chapter: 0
difficulty: Beginner
tags: ["laravel", "rails", "introduction", "comparison", "philosophy"]
---

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/#choose-your-learning-path">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/rails-developers-love-laravel/">Rails to Laravel</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 00</span>
</div>

![Introduction: Why Look at Laravel](/images/rails-developers-love-laravel/chapter-00-introduction-why-look-at-laravel-hero-full.webp)

# Chapter 00: Introduction: Why Look at Laravel <span class="difficulty-badge difficulty-beginner">Beginner</span>

## Overview

If you're a Ruby on Rails developer, you might wonder why Laravel deserves your attention. After all, Rails is mature, beloved, and has powered countless successful applications. Why learn another framework?

The answer: Laravel isn't just "PHP's answer to Rails"—it's a framework that learned from Rails, refined many of its patterns, and added its own innovations. As a Rails developer, you'll find Laravel remarkably familiar yet refreshingly different.

This chapter explores why Laravel is worth your time and how your Rails experience gives you a significant head start.

## What You'll Learn

- Why Laravel appeals to Rails developers
- Laravel's philosophy and how it aligns with Rails
- The Rails patterns you'll recognize immediately
- Laravel's unique innovations and improvements
- When it makes sense to consider Laravel
- What this series will teach you

## The Laravel Origin Story

In 2011, Taylor Otwell created Laravel because he felt PHP frameworks lacked the elegance and developer happiness of Rails. He wasn't trying to copy Rails—he was bringing Rails' philosophy to PHP.

Taylor has openly acknowledged Rails as a major influence:

> "Laravel was heavily inspired by Ruby on Rails. I wanted to bring that same joy of development to PHP." — Taylor Otwell

The result? Laravel became PHP's most popular framework, powering millions of applications and introducing an entire generation of PHP developers to Rails-like patterns.

## Why Rails Developers Love Laravel

### 1. Familiar Philosophy

If you love Rails' philosophy, you'll feel at home with Laravel:

**Rails Philosophy:**
- Convention over configuration
- Don't repeat yourself (DRY)
- Developer happiness
- Beautiful, expressive code
- Rapid application development

**Laravel Philosophy:**
- Convention over configuration ✅
- Don't repeat yourself (DRY) ✅
- Developer happiness ✅
- Beautiful, expressive code ✅
- Rapid application development ✅

It's the same mindset with PHP syntax.

### 2. Recognizable Patterns

**Routing:**
```ruby
# Rails
Rails.application.routes.draw do
  resources :posts
  root 'pages#home'
end
```

```php
<?php
// Laravel
Route::resource('posts', PostController::class);
Route::get('/', [PageController::class, 'home']);
```

**Models:**
```ruby
# Rails - ActiveRecord
class Post < ApplicationRecord
  belongs_to :user
  has_many :comments
  scope :published, -> { where(published: true) }
end
```

```php
<?php
// Laravel - Eloquent
class Post extends Model
{
    public function user() {
        return $this->belongsTo(User::class);
    }

    public function comments() {
        return $this->hasMany(Comment::class);
    }

    public function scopePublished($query) {
        return $query->where('published', true);
    }
}
```

The concepts are identical—just different syntax.

### 3. Developer Happiness

Both frameworks prioritize joy:

**Rails:**
- Generators for rapid scaffolding
- Intuitive APIs
- Convention over configuration
- Excellent documentation

**Laravel:**
- Artisan generators
- Intuitive, readable APIs
- Sensible defaults
- Outstanding documentation and community resources

### 4. "Magic That Makes Sense"

Both frameworks use "magic" (conventions, metaprogramming, facades) but only when it improves developer experience:

**Rails:**
```ruby
# Route helper methods
post_path(@post)  # Automatically generated

# Dynamic finders
User.find_by_email(email)  # Method doesn't exist in code

# Association methods
user.posts  # Automatically created from has_many
```

**Laravel:**
```php
<?php
// Route helper functions
route('posts.show', $post)  // Named route helpers

// Dynamic properties
$user->posts  // Automatically available from relationship

// Facades (static-like syntax)
Cache::put('key', 'value');
Mail::to($user)->send(new Welcome());
```

Both use abstraction to make code readable and maintainable.

## What Laravel Learned From Rails

### 1. Migrations

Rails pioneered version-controlled database schemas. Laravel adopted and refined this:

**Rails:**
```ruby
class CreatePosts < ActiveRecord::Migration[7.0]
  def change
    create_table :posts do |t|
      t.string :title
      t.text :body
      t.timestamps
    end
  end
end
```

**Laravel:**
```php
<?php
return new class extends Migration
{
    public function up() {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->timestamps();
        });
    }
};
```

### 2. Eloquent ORM (from ActiveRecord)

Laravel's Eloquent is clearly inspired by ActiveRecord:

```ruby
# Rails
user = User.find(1)
posts = Post.where(published: true).order(created_at: :desc).limit(10)
user.posts.create(title: "Hello")
```

```php
<?php
// Laravel
$user = User::find(1);
$posts = Post::where('published', true)->orderBy('created_at', 'desc')->limit(10)->get();
$user->posts()->create(['title' => 'Hello']);
```

The patterns are nearly identical.

### 3. Resourceful Routing

Rails' RESTful routing conventions directly influenced Laravel:

**Rails:**
```ruby
resources :posts
```

Generates:
- `GET /posts` → index
- `GET /posts/:id` → show
- `GET /posts/new` → new
- `POST /posts` → create
- etc.

**Laravel:**
```php
<?php
Route::resource('posts', PostController::class);
```

Generates the same routes with PHP naming conventions.

### 4. Convention Over Configuration

Both frameworks reduce boilerplate through conventions:

**Model/Table Conventions:**
- `User` model → `users` table (both)
- `Post` model → `posts` table (both)
- Primary key: `id` (both)
- Timestamps: `created_at`, `updated_at` (both)

**File Structure:**
- `app/models/` → `app/Models/`
- `app/controllers/` → `app/Http/Controllers/`
- `db/migrations/` → `database/migrations/`

## Where Laravel Innovates

While inspired by Rails, Laravel has its own innovations:

### 1. Dependency Injection & Service Container

Laravel's service container is more powerful than Rails' approach:

```php
<?php
class PostController extends Controller
{
    // Dependencies automatically injected
    public function __construct(
        private PostRepository $posts,
        private Cache $cache
    ) {}

    public function index(Request $request)
    {
        // Even request is injected!
        return $this->posts->getPublished();
    }
}
```

### 2. Facades

Laravel's facades provide a clean static-like API:

```php
<?php
// Clean, readable syntax
Cache::put('key', 'value', 3600);
DB::table('users')->get();
Mail::to($user)->send(new Welcome());

// Behind the scenes, these resolve from the container
```

Rails doesn't have an equivalent pattern.

### 3. Collections

Laravel's Collection class is more powerful than ActiveRecord::Relation:

```php
<?php
$users = User::all();

$result = $users
    ->filter(fn($u) => $u->active)
    ->map(fn($u) => $u->name)
    ->sort()
    ->values()
    ->take(10);

// Chaining feels like Ruby enumerables
```

### 4. Built-in Task Scheduling

No cron configuration needed:

```php
<?php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->command('emails:send')
        ->dailyAt('09:00');

    $schedule->job(new ProcessPodcast)
        ->hourly();
}
```

Rails requires gems like `whenever` for this.

### 5. Queues Built-in

Background jobs work out of the box:

```php
<?php
// Queue a job
ProcessPodcast::dispatch($podcast);

// Queue a notification
$user->notify(new InvoicePaid($invoice));

// Start worker
php artisan queue:work
```

No Sidekiq/Redis configuration required (though you can use Redis).

## When Should You Consider Laravel?

You might want to explore Laravel if:

### 1. Your Team Knows PHP
If your team is already proficient in PHP, Laravel lets you use that expertise while getting Rails-like productivity.

### 2. Hosting Constraints
PHP hosting is ubiquitous and often cheaper. Shared hosting supports PHP widely, while Ruby hosting is less common.

### 3. Integration Requirements
Need to integrate with WordPress, Drupal, or legacy PHP systems? Laravel makes this straightforward.

### 4. Performance at Scale
Modern PHP (8.4+) with JIT compilation is faster than Ruby in most benchmarks. For high-traffic applications, this matters.

### 5. Deployment Simplicity
PHP deployment is simpler:
- No application server (Puma/Unicorn) needed
- Works with Apache/Nginx directly
- Fewer moving parts

### 6. You're Curious!
Sometimes the best reason is curiosity. Learning Laravel makes you a better developer by exposing you to different approaches.

## When to Stick with Rails

Don't abandon Rails if:

- **Your team is productive** — Team expertise matters more than framework choice
- **Your apps work well** — If it ain't broke, don't fix it
- **You use Ruby's ecosystem** — Rails' gem ecosystem is mature and comprehensive
- **You prefer Ruby's syntax** — Language preference is valid
- **Your infrastructure is Rails-optimized** — Migration costs can be high

**This series isn't about replacing Rails—it's about expanding your toolkit.**

## What You'll Build in This Series

By the end of this series, you'll:

1. **Understand Laravel's architecture** — See how Rails concepts map to Laravel
2. **Write modern PHP** — Learn PHP 8.4 syntax and features
3. **Build Laravel applications** — Create controllers, models, views
4. **Master Eloquent ORM** — Rails' ActiveRecord meets Laravel
5. **Create REST APIs** — Build APIs with Laravel's tools
6. **Test and deploy** — Laravel's testing and deployment workflow
7. **Make informed decisions** — Know when to use Laravel vs Rails

## How This Series Works

Each chapter follows a **Rails-first approach**:

1. **Show Rails code you know**
2. **Demonstrate Laravel equivalent**
3. **Explain the differences**
4. **Provide practical examples**
5. **Offer practice exercises**

This approach leverages your Rails knowledge instead of starting from scratch.

## Your Rails Knowledge is an Asset

As a Rails developer, you already understand:

✅ **MVC architecture** — Same in Laravel
✅ **Active Record pattern** — Eloquent works identically
✅ **RESTful routing** — Laravel uses same conventions
✅ **Migrations** — Nearly identical syntax
✅ **Testing** — Similar philosophies
✅ **Background jobs** — Same concepts, different syntax

You're not learning a new framework—you're translating knowledge you already have.

## Prerequisites

Before continuing, you should be comfortable with:

- Ruby on Rails development (building apps, not just following tutorials)
- MVC architecture and RESTful APIs
- ActiveRecord and database migrations
- Git and command-line tools

You don't need:
- PHP experience (we'll teach you)
- Laravel knowledge (that's what this series is for)
- To abandon Rails (you can use both!)

## What You'll Need

**Software:**
- PHP 8.4+ (we'll help you install it)
- Composer (PHP's Bundler equivalent)
- A text editor (VS Code, PhpStorm, or your favorite)
- Terminal access

**Time Commitment:**
- **Total**: 15-20 hours for all chapters
- **Per chapter**: 1-2 hours
- **Hands-on project**: 3-4 hours

## Series Structure

This series has 11 chapters:

**Part 1: Foundations (Chapters 00-01)**
- 00: Introduction (you are here)
- 01: Mapping Rails concepts to Laravel

**Part 2: PHP & Laravel Basics (Chapters 02-04)**
- 02: Modern PHP features
- 03: Laravel's developer experience
- 04: PHP syntax for Rails developers

**Part 3: Building Applications (Chapters 05-07)**
- 05: Eloquent ORM and databases
- 06: Building REST APIs
- 07: Testing and deployment

**Part 4: Ecosystem & Decision Making (Chapters 08-09)**
- 08: Laravel's ecosystem and packages
- 09: When to use Laravel vs Rails

**Part 5: Practice (Chapter 10)**
- 10: Hands-on mini project

## A Note on PHP's Evolution

If your last experience with PHP was years ago, prepare to be surprised. Modern PHP (8.0+) is dramatically different:

**Old PHP (5.x):**
```php
<?php
function getUser($id) {
    $user = User::find($id);
    if ($user == null) {
        return null;
    }
    return $user->name;
}
```

**Modern PHP (8.4):**
```php
<?php
function getUser(int $id): ?string
{
    return User::find($id)?->name;
}
```

PHP now has:
- Type declarations
- Null-safe operators
- JIT compilation
- Property hooks
- Enums
- Attributes
- Match expressions
- And more!

It's not your grandmother's PHP.

## Why This Series Exists

As a Rails developer, you have valuable skills. This series helps you:

1. **Expand your toolkit** — More frameworks = more opportunities
2. **Make informed decisions** — Understand when to use each framework
3. **Increase your value** — Polyglot developers command higher salaries
4. **Stay curious** — Learning new frameworks makes you a better developer
5. **Bridge communities** — Rails and Laravel can learn from each other

## Let's Get Started

Ready to explore Laravel with your Rails knowledge as a foundation?

In the next chapter, we'll map every Rails concept you know to its Laravel equivalent. You'll see that Laravel isn't foreign—it's familiar with a PHP accent.

---

::: tip Continue Learning
Move on to [Chapter 01: Mapping Concepts: Rails vs Laravel](/series/rails-developers-love-laravel/chapters/01-mapping-concepts-rails-vs-laravel) to see comprehensive side-by-side comparisons.
:::

<ProgressTracker seriesId="rails-developers-love-laravel" :totalChapters="11" title="Your Progress" />

<style>
.comparison-box {
  background: #f0fdfa;
  border-left: 4px solid #0d9488;
  padding: 1rem;
  margin: 1rem 0;
  border-radius: 4px;
}

.innovation-box {
  background: #fef2f2;
  border-left: 4px solid #dc2626;
  padding: 1rem;
  margin: 1rem 0;
  border-radius: 4px;
}
</style>
