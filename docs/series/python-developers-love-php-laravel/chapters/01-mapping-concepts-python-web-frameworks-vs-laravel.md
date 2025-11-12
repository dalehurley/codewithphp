---
title: "01: Mapping Concepts: Python Web Frameworks vs Laravel"
description: "See how Django and Flask concepts map directly to Laravel—routing, views, templates, MVC patterns, and ORM comparisons."
series: "python-developers-love-php-laravel"
chapter: 1
order: 1
difficulty: "Intermediate"
prerequisites:
  - "/series/python-developers-love-php-laravel/chapters/00-introduction-why-look-at-php-laravel"
---

![Mapping Concepts: Python Web Frameworks vs Laravel](/images/python-developers-love-php-laravel/chapter-01-mapping-concepts-python-web-frameworks-vs-laravel-hero-full.webp)

# Chapter 01: Mapping Concepts: Python Web Frameworks vs Laravel

## Overview

Welcome to the first hands-on chapter! If you've worked with Django or Flask, you already understand web frameworks—you just need to see how those concepts translate to Laravel. This chapter is all about mapping: we'll show you Python code you know, then demonstrate the Laravel equivalent.

By the end of this chapter, you'll see that Laravel isn't fundamentally different from Django or Flask—it's the same concepts with different syntax and conventions. You'll understand routing, views, templates, MVC patterns, and ORM comparisons. Most importantly, you'll recognize that your Python knowledge accelerates your Laravel learning.

## Prerequisites

Before starting this chapter, you should have:

- Completion of [Chapter 00](/series/python-developers-love-php-laravel/chapters/00-introduction-why-look-at-php-laravel) or equivalent understanding
- Experience with Django or Flask (routing, views, templates)
- Basic understanding of MVC/MVT patterns
- Familiarity with ORMs (Django ORM or SQLAlchemy)
- PHP 8.4+ installed
- Composer installed (PHP's package manager)
- **Estimated Time**: ~60 minutes

**Verify your setup:**

```bash
# Check PHP version (should show PHP 8.4+)
php --version

# Check Composer is installed
composer --version

# Optional: If you want to run Laravel examples, ensure Laravel is installed
# We'll show Laravel code examples, but you can follow along without a full Laravel installation
```

## What You'll Build

By the end of this chapter, you will have:

- Side-by-side comparison examples (Python → Laravel) for routing, views, templates, and middleware
- Understanding of how Django's "batteries included" philosophy compares to Laravel
- Knowledge of MVC vs MVT pattern differences
- Comparison of Django ORM vs Eloquent ORM
- Understanding of URL generation, Request/Response objects, and middleware patterns
- Working Laravel examples you can run immediately
- A mental map connecting Python concepts to Laravel equivalents

## Quick Start

Want to see how Python frameworks map to Laravel right away? Here's a side-by-side comparison of a simple route:

**Flask (Python):**

```python
@app.route('/user/<int:user_id>')
def user_profile(user_id):
    return f'User {user_id}'
```

**Django (Python):**

```python
# urls.py
path('user/<int:user_id>/', views.user_profile, name='user_profile')

# views.py
def user_profile(request, user_id):
    return HttpResponse(f'User {user_id}')
```

**Laravel (PHP):**

```php
Route::get('/user/{user_id}', function (int $user_id): string {
    return "User {$user_id}";
})->where('user_id', '[0-9]+');
```

See the pattern? Same concept—URL pattern, parameter extraction, handler function—just different syntax! This chapter will show you how all the concepts you know from Django/Flask translate directly to Laravel.

## Objectives

- Map Django/Flask routing patterns to Laravel routes
- Understand URL generation and Request/Response object comparisons
- Compare Django templates with Laravel Blade templates
- Understand MVC (Laravel) vs MVT (Django) pattern differences
- Compare Django ORM with Eloquent ORM syntax and features
- Map middleware patterns from Django/Flask to Laravel
- Recognize that framework concepts are universal, only syntax differs
- Build confidence that your Python knowledge transfers to Laravel

## Step 1: Framework Philosophy Comparison (~5 min)

### Goal

Understand how Django's "batteries included" and Flask's "microframework" philosophies compare to Laravel's approach.

### Actions

1. **Django's Philosophy**: "Batteries included"

   - Everything you need is built-in: admin panel, ORM, authentication, forms
   - Opinionated: Django decides how things should work
   - Less flexibility, more convention

2. **Flask's Philosophy**: "Microframework"

   - Minimal core, add what you need
   - Flexible: you decide how to structure things
   - More choices, more decisions to make

3. **Laravel's Philosophy**: "Expressive, elegant syntax"
   - More like Django: comprehensive feature set built-in
   - Convention over configuration (like Django)
   - But more flexible than Django in some areas
   - Excellent developer experience and tooling

### Comparison Table

| Feature             | Django             | Flask                | Laravel              |
| ------------------- | ------------------ | -------------------- | -------------------- |
| **Philosophy**      | Batteries included | Microframework       | Expressive, elegant  |
| **Built-in ORM**    | ✅ Django ORM      | ❌ (use SQLAlchemy)  | ✅ Eloquent ORM      |
| **Admin Panel**     | ✅ Excellent       | ❌ (use Flask-Admin) | ❌ (use Nova/Tinker) |
| **Authentication**  | ✅ Built-in        | ❌ (use Flask-Login) | ✅ Built-in          |
| **Migrations**      | ✅ Built-in        | ❌ (use Alembic)     | ✅ Built-in          |
| **CLI Tool**        | `manage.py`        | ❌                   | `php artisan`        |
| **Template Engine** | Django Templates   | Jinja2               | Blade                |
| **Flexibility**     | Lower              | Higher               | Medium-High          |

### Expected Result

After reviewing the comparison, you should understand:

- **Django** and **Laravel** are similar: both are "batteries included" frameworks with built-in ORM, authentication, and migrations
- **Flask** is more minimal: you add what you need, giving more flexibility but requiring more decisions
- **Laravel** sits between them: comprehensive like Django but more flexible, with excellent developer tooling
- All three frameworks solve the same problems—the difference is in philosophy and default choices

### Why It Works

Laravel sits between Django and Flask: it has Django's comprehensive feature set but Flask's flexibility. If you like Django's "everything included" approach, Laravel will feel familiar. If you prefer Flask's flexibility, Laravel offers more choices than Django while still providing built-in features.

### Troubleshooting

- **"Laravel seems too opinionated"** — It is, but less than Django. You can override conventions when needed. Flask is more flexible, but you'll write more boilerplate.
- **"Does Laravel have an admin panel like Django?"** — Not built-in, but Laravel Nova is excellent (paid) and there are free alternatives like Filament.
- **"Which framework should I choose?"** — If you like Django's "everything included" approach, Laravel will feel familiar. If you prefer Flask's minimalism, Laravel offers more flexibility than Django while still providing built-in features.

## Step 2: Routing Comparison (~10 min)

### Goal

See how Python routing (Django URLs, Flask routes) maps to Laravel routes.

### Actions

1. **Flask Routing** (Python):

The complete Flask routing example is available in [`flask-routing.py`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-01/flask-routing.py):

```python
@app.route('/')
def index():
    return 'Hello, World!'

@app.route('/user/<int:user_id>')
def user_profile(user_id):
    return f'User {user_id}'

@app.route('/post/<slug>', methods=['GET', 'POST'])
def post_detail(slug):
    if request.method == 'POST':
        # Handle POST
        pass
    return f'Post: {slug}'
```

2. **Django Routing** (Python):

Django routing examples are available in [`django-routing-urls.py`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-01/django-routing-urls.py) and [`django-routing-views.py`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-01/django-routing-views.py):

```python
# urls.py
urlpatterns = [
    path('', views.index, name='index'),
    path('user/<int:user_id>/', views.user_profile, name='user_profile'),
    path('post/<slug:slug>/', views.post_detail, name='post_detail'),
]

# views.py
def index(request):
    return HttpResponse('Hello, World!')
```

3. **Laravel Routing** (PHP):

The complete Laravel routing example with PHP 8.4 syntax is available in [`laravel-routing.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-01/laravel-routing.php):

```php
Route::get('/', function (): string {
    return 'Hello, World!';
});

Route::get('/user/{user_id}', function (int $user_id): string {
    return "User {$user_id}";
})->where('user_id', '[0-9]+');
```

### Expected Result

You can see the patterns are identical:

- **Flask**: `@app.route('/path')` → **Laravel**: `Route::get('/path')`
- **Django**: `path('pattern', view)` → **Laravel**: `Route::get('pattern', closure)`
- **Route parameters**: All three use `{param}` or `<param>` syntax
- **HTTP methods**: All support GET, POST, PUT, DELETE, etc.

### Why It Works

Routing is routing. The concepts are universal: define a URL pattern, map it to a handler (function/closure), extract parameters. Laravel's syntax is slightly different, but the logic is identical to Flask/Django.

### URL Generation / Reverse URLs

All frameworks provide ways to generate URLs from route names, avoiding hardcoded URLs:

**Django:**

URL generation examples are available in [`url-generation-django.py`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-01/url-generation-django.py):

```python
# In Python code
from django.urls import reverse
url = reverse('user_profile', args=[123])  # Returns: '/user/123/'

# In templates
{% url 'user_profile' user_id=123 %}
```

**Flask:**

URL generation examples are available in [`url-generation-flask.py`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-01/url-generation-flask.py):

```python
# In Python code
from flask import url_for
url = url_for('user_profile', user_id=123)  # Returns: '/user/123'

# In templates
# Use: {{ url_for('user_profile', user_id=123) }}
```

**Laravel:**

URL generation examples are available in [`url-generation-laravel.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-01/url-generation-laravel.php):

```php
// In PHP code
$url = route('user_profile', ['user_id' => 123]);  // Returns: '/user/123'

// In Blade templates
// Use: {{ route('user_profile', ['user_id' => 123]) }}
```

**Pattern**: All three use a function (`reverse()` / `url_for()` / `route()`) that takes a route name and parameters, returning the URL string.

### Request and Response Objects

All frameworks provide objects to access HTTP request data and create responses:

**Django:**

Request/Response examples are available in [`request-response-django.py`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-01/request-response-django.py):

```python
def my_view(request):
    # Access request data
    method = request.method
    user_id = request.GET.get('id')
    data = request.POST.get('data')

    # Create response
    return HttpResponse('Hello', status=200)
    # Or JSON
    return JsonResponse({'key': 'value'})
```

**Flask:**

Request/Response examples are available in [`request-response-flask.py`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-01/request-response-flask.py):

```python
@app.route('/example')
def example():
    # Access request data
    method = request.method
    user_id = request.args.get('id')
    data = request.form.get('data')

    # Create response
    return 'Hello', 200
    # Or JSON
    return jsonify({'key': 'value'})
```

**Laravel:**

Request/Response examples with PHP 8.4 syntax are available in [`request-response-laravel.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-01/request-response-laravel.php):

```php
Route::get('/example', function (Request $request): Response {
    // Access request data
    $method = $request->method();
    $userId = $request->query('id');
    $data = $request->input('data');

    // Create response
    return response('Hello', 200);
    // Or JSON
    return response()->json(['key' => 'value']);
});
```

**Pattern**: All three provide request objects with methods to access query parameters, form data, headers, and response objects to return data with status codes.

### Troubleshooting

- **"Laravel uses closures, not functions"** — You can use controllers (like Django views) too. Closures are convenient for simple routes, controllers for complex logic.
- **"How do I name routes like Django's `name='index'`?"** — Use `->name('index')` in Laravel: `Route::get('/', ...)->name('index')`.
- **"Route parameters aren't working"** — Ensure you're using `{param}` syntax in Laravel (not `<param>`). Also check route constraints with `->where()` if you need type validation like Django's `int:` converter.
- **"How do I generate URLs in templates?"** — Use <code v-pre>{{ route('route_name', ['param' => value]) }}</code> in Blade, similar to Django's `{% url %}` or Flask's `url_for()`.

## Step 3: Views and Templates Comparison (~10 min)

### Goal

Compare Django templates and Flask's Jinja2 with Laravel's Blade templating engine.

### Actions

1. **Django Template** (Python):

Django template examples are available in [`django-template-view.py`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-01/django-template-view.py) and [`django-template.html`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-01/django-template.html):

```python
# views.py
def post_list(request):
    posts = Post.objects.all()
    return render(request, 'blog/post_list.html', {'posts': posts})
```

::: v-pre

```html
<!-- templates/blog/post_list.html -->
<h1>Blog Posts</h1>
{% for post in posts %}
<article>
  <h2>{{ post.title }}</h2>
  <p>{{ post.content|truncatewords:30 }}</p>
  <p>Published: {{ post.published_at|date:"F j, Y" }}</p>
</article>
{% endfor %}
```

:::

2. **Flask Template** (Python):

Flask template examples are available in [`flask-template-view.py`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-01/flask-template-view.py) and [`flask-template.html`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-01/flask-template.html):

```python
# app.py
@app.route('/posts')
def post_list():
    posts = Post.query.all()
    return render_template('post_list.html', posts=posts)
```

3. **Laravel Blade Template** (PHP):

Laravel Blade examples are available in [`laravel-blade-route.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-01/laravel-blade-route.php) and [`laravel-blade-template.blade.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-01/laravel-blade-template.blade.php):

```php
// routes/web.php
Route::get('/posts', function (): \Illuminate\View\View {
    $posts = Post::all();
    return view('blog.post-list', ['posts' => $posts]);
});
```

::: v-pre

```blade
{{-- resources/views/blog/post-list.blade.php --}}
<h1>Blog Posts</h1>
@foreach($posts as $post)
    <article>
        <h2>{{ $post->title }}</h2>
        <p>{{ Str::limit($post->content, 30) }}</p>
        <p>Published: {{ $post->published_at->format('F j, Y') }}</p>
    </article>
@endforeach
```

:::

### Comparison Table

| Feature          | Django Templates                        | Jinja2 (Flask)                          | Blade (Laravel)                               |
| ---------------- | --------------------------------------- | --------------------------------------- | --------------------------------------------- |
| **Syntax**       | `{% tag %}`                             | `{% tag %}`                             | `@directive`                                  |
| **Variables**    | `&#123;&#123; var &#125;&#125;`         | `&#123;&#123; var &#125;&#125;`         | `&#123;&#123; $var &#125;&#125;`              |
| **Loops**        | `{% for %}`                             | `{% for %}`                             | `@foreach`                                    |
| **Conditionals** | `{% if %}`                              | `{% if %}`                              | `@if`                                         |
| **Filters**      | `&#123;&#123; var\|filter &#125;&#125;` | `&#123;&#123; var\|filter &#125;&#125;` | `&#123;&#123; Str::method($var) &#125;&#125;` |
| **Inheritance**  | `{% extends %}`                         | `{% extends %}`                         | `@extends`                                    |
| **Includes**     | `{% include %}`                         | `{% include %}`                         | `@include`                                    |

### Why It Works

Blade is very similar to Django templates and Jinja2. The main differences:

- **Variables**: Blade uses `$var` (PHP convention) vs `var` (Python)
- **Directives**: Blade uses `@` prefix (`@foreach`) vs `{% %}` (Django/Jinja2)
- **Filters**: Blade uses PHP methods (`Str::limit()`) vs template filters (`|truncate`)

The concepts are identical: loops, conditionals, inheritance, includes.

### Troubleshooting

- **"Why does Blade use `$` for variables?"** — PHP convention. In PHP, all variables start with `$`. Blade follows PHP syntax.
- **"Can I use Python-style filters?"** — Not directly, but Laravel's `Str`, `Carbon`, and other helper classes provide similar functionality.
- **"Template inheritance seems different"** — Blade uses `@extends` and `@section` instead of Django's `{% extends %}` and `{% block %}`, but the concept is identical. Both allow you to define a base layout and override sections.

## Step 4: MVC vs MVT Patterns (~5 min)

### Goal

Understand the difference between Laravel's MVC and Django's MVT patterns.

### Actions

1. **Django's MVT (Model-View-Template)**:

   - **Model**: Data layer (database, ORM)
   - **View**: Logic layer (handles requests, processes data)
   - **Template**: Presentation layer (HTML rendering)

2. **Laravel's MVC (Model-View-Controller)**:
   - **Model**: Data layer (database, Eloquent ORM)
   - **View**: Presentation layer (Blade templates)
   - **Controller**: Logic layer (handles requests, processes data)

### Comparison

| Layer            | Django          | Laravel            |
| ---------------- | --------------- | ------------------ |
| **Data**         | Model           | Model              |
| **Logic**        | View (function) | Controller (class) |
| **Presentation** | Template        | View (Blade)       |

**Key Difference**: Django's "View" is Laravel's "Controller". Django's "Template" is Laravel's "View". The concepts are the same, just different naming.

### Why It Works

Both patterns separate concerns:

- **Models** handle data (same in both)
- **Controllers/Views** handle business logic (same concept, different name)
- **Templates/Views** handle presentation (same concept, different name)

If you understand Django's MVT, you understand Laravel's MVC. It's just terminology.

### Troubleshooting

- **"Which is better: MVC or MVT?"** — Neither. They're the same pattern with different names. Django calls the logic layer "View", Laravel calls it "Controller". Both separate concerns identically.
- **"I'm confused by the terminology"** — Remember: Django's "View" = Laravel's "Controller" (both handle logic), and Django's "Template" = Laravel's "View" (both handle presentation). The Model is the same in both.
- **"Does this mean I need to relearn everything?"** — No! The concepts are identical. You're just learning new names for things you already understand. Your Python knowledge transfers directly.

## Step 5: ORM Comparison (~15 min)

### Goal

Compare Django ORM and SQLAlchemy with Laravel's Eloquent ORM.

### Actions

1. **Django ORM** (Python):

Django ORM examples are available in [`django-orm-model.py`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-01/django-orm-model.py) and [`django-orm-queries.py`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-01/django-orm-queries.py):

```python
# models.py
class Post(models.Model):
    title = models.CharField(max_length=200)
    content = models.TextField()
    published_at = models.DateTimeField(auto_now_add=True)
    author = models.ForeignKey('User', on_delete=models.CASCADE)

    class Meta:
        ordering = ['-published_at']

# Query examples
posts = Post.objects.all()
post = Post.objects.get(id=1)
recent_posts = Post.objects.filter(published_at__gte=timezone.now() - timedelta(days=7))
```

2. **SQLAlchemy** (Python, used with Flask):

SQLAlchemy examples are available in [`sqlalchemy-model.py`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-01/sqlalchemy-model.py) and [`sqlalchemy-queries.py`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-01/sqlalchemy-queries.py):

```python
# models.py
class Post(db.Model):
    __tablename__ = 'posts'
    id = Column(Integer, primary_key=True)
    title = Column(String(200))
    author_id = Column(Integer, ForeignKey('users.id'))
    author = relationship('User', backref='posts')

# Query examples
posts = Post.query.all()
post = Post.query.get(1)
recent_posts = Post.query.filter(Post.published_at >= datetime.now() - timedelta(days=7)).all()
```

3. **Eloquent ORM** (PHP/Laravel):

Eloquent examples with PHP 8.4 syntax are available in [`eloquent-model.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-01/eloquent-model.php) and [`eloquent-queries.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-01/eloquent-queries.php):

```php
// app/Models/Post.php
class Post extends Model
{
    protected $fillable = ['title', 'content', 'author_id'];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

// Query examples
$posts = Post::all();
$post = Post::find(1);
$recentPosts = Post::where('published_at', '>=', now()->subDays(7))->get();
```

### Comparison Table

| Feature           | Django ORM                               | SQLAlchemy                       | Eloquent                   |
| ----------------- | ---------------------------------------- | -------------------------------- | -------------------------- |
| **Syntax**        | `Model.objects.filter()`                 | `Model.query.filter()`           | `Model::where()->get()`    |
| **Relationships** | `ForeignKey()`, `ManyToMany()`           | `relationship()`                 | `belongsTo()`, `hasMany()` |
| **Migrations**    | Built-in                                 | Alembic                          | Built-in                   |
| **Query Builder** | `filter()`, `exclude()`                  | `filter()`, `filter_by()`        | `where()`, `orWhere()`     |
| **Chaining**      | ✅ Yes                                   | ✅ Yes                           | ✅ Yes                     |
| **Lazy Loading**  | ✅ Yes                                   | ✅ Yes                           | ✅ Yes                     |
| **Eager Loading** | `select_related()`, `prefetch_related()` | `joinedload()`, `subqueryload()` | `with()`                   |

### Why It Works

All three ORMs follow the same Active Record pattern:

- **Models** represent database tables
- **Relationships** define table connections
- **Query builders** allow method chaining
- **Migrations** manage schema changes

Eloquent's syntax is slightly different (PHP vs Python), but the concepts are identical. If you know Django ORM, Eloquent will feel familiar.

### Troubleshooting

- **"Eloquent uses `::` instead of `.`"** — PHP uses `::` for static methods, `.` for instance methods. `Post::all()` is equivalent to `Post.objects.all()`.
- **"How do I do `filter(field__gte=value)`?"** — Use `where('field', '>=', $value)` in Eloquent. The `__gte` syntax is Django-specific.
- **"Eager loading seems different"** — Django uses `select_related()` and `prefetch_related()`, while Eloquent uses `with()`. Both solve the N+1 query problem, but Eloquent's `with()` is simpler: `Post::with('author', 'comments')->get()`.

## Step 6: Middleware Comparison (~10 min)

### Goal

Understand how Django middleware, Flask decorators, and Laravel middleware handle request processing and cross-cutting concerns.

### Actions

1. **Django Middleware** (Python):

Django middleware examples are available in [`django-middleware.py`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-01/django-middleware.py):

```python
# middleware.py
class LoggingMiddleware:
    def __init__(self, get_response):
        self.get_response = get_response

    def __call__(self, request):
        # Before view
        print(f"Request: {request.method} {request.path}")
        response = self.get_response(request)
        # After view
        print(f"Response: {response.status_code}")
        return response
```

2. **Flask Middleware** (Python):

Flask middleware examples are available in [`flask-middleware.py`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-01/flask-middleware.py):

```python
# Using decorators (most common)
@app.before_request
def log_request():
    print(f"Request: {request.method} {request.path}")

@app.after_request
def log_response(response):
    print(f"Response: {response.status_code}")
    return response
```

3. **Laravel Middleware** (PHP):

Laravel middleware examples with PHP 8.4 syntax are available in [`laravel-middleware.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-01/laravel-middleware.php):

```php
// app/Http/Middleware/LoggingMiddleware.php
class LoggingMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Before request
        \Log::info("Request: {$request->method()} {$request->path()}");
        $response = $next($request);
        // After request
        \Log::info("Response: {$response->getStatusCode()}");
        return $response;
    }
}
```

### Comparison Table

| Feature            | Django                  | Flask                                | Laravel               |
| ------------------ | ----------------------- | ------------------------------------ | --------------------- |
| **Type**           | Class-based             | Decorators or WSGI classes           | Class-based           |
| **Method**         | `__call__()`            | `@before_request` / `@after_request` | `handle()`            |
| **Request Access** | `request` parameter     | `request` global                     | `Request $request`    |
| **Next Handler**   | `get_response(request)` | Return from decorator                | `$next($request)`     |
| **Registration**   | `MIDDLEWARE` setting    | Decorators or `wsgi_app`             | `Kernel.php` or route |
| **Order Matters**  | ✅ Yes (top to bottom)  | ✅ Yes (decorator order)             | ✅ Yes (array order)  |

### Why It Works

All three frameworks use middleware to process requests before they reach your views/controllers:

- **Django**: Middleware classes wrap the request/response cycle
- **Flask**: Decorators or WSGI middleware classes intercept requests
- **Laravel**: Middleware classes with `handle()` method process requests

The pattern is identical: intercept request → process → call next handler → process response → return. Middleware handles cross-cutting concerns like authentication, logging, CORS, and CSRF protection.

### Common Use Cases

All frameworks use middleware for:

- **Authentication**: Check if user is logged in
- **Logging**: Log requests and responses
- **CORS**: Handle cross-origin requests
- **CSRF Protection**: Validate CSRF tokens
- **Rate Limiting**: Limit requests per IP/user
- **Request Transformation**: Modify request data before it reaches controllers

### Troubleshooting

- **"Middleware not running"** — Check registration order. In Django, middleware runs top-to-bottom. In Laravel, check `app/Http/Kernel.php`. In Flask, ensure decorators are applied correctly.
- **"How do I skip middleware for certain routes?"** — Django: Use `@csrf_exempt`. Flask: Don't apply decorator. Laravel: Exclude from middleware in route definition or use route groups.
- **"Middleware vs decorators?"** — Flask decorators are simpler for route-specific logic. Laravel middleware is more powerful and reusable. Django middleware runs globally unless excluded.

## Exercises

Test your understanding by converting Python web framework patterns to Laravel equivalents:

### Exercise 1: Route Mapping (~10 min)

**Goal**: Convert Flask and Django routes to Laravel routes, reinforcing the routing pattern mapping.

**Requirements:**

1. **Convert this Flask route to Laravel:**

   ```python
   @app.route('/api/users/<int:user_id>/posts', methods=['GET', 'POST'])
   def user_posts(user_id):
       if request.method == 'POST':
           # Create new post
           pass
       return f'Posts for user {user_id}'
   ```

2. **Convert this Django URL pattern to Laravel:**

   ```python
   # urls.py
   path('articles/<slug:slug>/comments/', views.article_comments, name='article_comments')

   # views.py
   def article_comments(request, slug):
       return HttpResponse(f'Comments for {slug}')
   ```

3. **Add route naming** to both Laravel routes using `->name()` method

**Validation**: Your Laravel routes should:

- Accept the same URL patterns and parameters
- Handle the same HTTP methods
- Have named routes for easy reference
- Use proper type hints for parameters

**Reference**: See [`laravel-routing.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-01/laravel-routing.php) for examples.

### Exercise 2: Template Conversion (~15 min)

**Goal**: Convert Django and Jinja2 templates to Blade templates, understanding template syntax differences.

**Requirements:**

1. **Convert this Django template to Blade:**

   ::: v-pre

   ```html
   <h1>{{ article.title }}</h1>
   {% if article.published %}
   <p>{{ article.content|truncatewords:50 }}</p>
   <p>Published: {{ article.published_at|date:"M d, Y" }}</p>
   {% else %}
   <p>This article is not yet published.</p>
   {% endif %} {% for comment in article.comments.all %}
   <div class="comment">
     <strong>{{ comment.author }}</strong>: {{ comment.text }}
   </div>
   {% endfor %}
   ```

   :::

2. **Convert this Jinja2 template to Blade:**

   ::: v-pre

   ```html
   <h1>{{ post.title|title }}</h1>
   <p>{{ post.content|truncate(100) }}</p>
   {% if post.tags %}
   <div class="tags">
     {% for tag in post.tags %}
     <span class="tag">{{ tag.name }}</span>
     {% endfor %}
   </div>
   {% endif %}
   ```

   :::

**Validation**: Your Blade templates should:

- Use <code v-pre>{{ $variable }}</code> syntax for variables
- Use `@if`, `@foreach`, `@endif`, `@endforeach` directives
- Convert filters to PHP methods (e.g., `Str::limit()`, `Str::title()`)
- Maintain the same logic flow and output structure

**Reference**: See [`laravel-blade-template.blade.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-01/laravel-blade-template.blade.php) for examples.

### Exercise 3: ORM Query Translation (~15 min)

**Goal**: Translate Django ORM and SQLAlchemy queries to Eloquent, reinforcing ORM pattern mapping.

**Requirements:**

1. **Convert these Django ORM queries to Eloquent:**

   ```python
   # Get posts published in the last 30 days
   recent = Post.objects.filter(
       published_at__gte=timezone.now() - timedelta(days=30)
   ).exclude(status='draft').order_by('-published_at')

   # Get posts with author and comments (eager loading)
   posts = Post.objects.select_related('author').prefetch_related('comments').all()

   # Get posts by multiple authors
   authors = [1, 2, 3]
   posts = Post.objects.filter(author_id__in=authors)
   ```

2. **Convert these SQLAlchemy queries to Eloquent:**

   ```python
   # Get posts with pagination
   page = request.args.get('page', 1, type=int)
   posts = Post.query.order_by(Post.published_at.desc()).paginate(page=page, per_page=10)

   # Get posts with search
   search_term = request.args.get('q', '')
   posts = Post.query.filter(Post.title.contains(search_term)).all()

   # Get posts with relationship
   posts = Post.query.options(joinedload(Post.author)).all()
   ```

**Validation**: Your Eloquent queries should:

- Use `where()`, `orWhere()`, `whereIn()` methods appropriately
- Use `with()` for eager loading relationships
- Use `orderBy()` for sorting
- Use `paginate()` for pagination (Laravel has built-in pagination)
- Maintain the same filtering logic

**Reference**: See [`eloquent-queries.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-01/eloquent-queries.php) for examples.

**Bonus Challenge**: Create a complete Laravel route that combines all three concepts—a route that queries posts, renders a Blade template, and handles both GET and POST requests.

## Wrap-up

Congratulations! You've completed the concept mapping chapter. You now understand:

- ✓ How Django's "batteries included" compares to Laravel's comprehensive features
- ✓ How Flask/Django routing maps to Laravel routes
- ✓ How Django templates and Jinja2 compare to Blade
- ✓ The difference between MVC (Laravel) and MVT (Django) patterns
- ✓ How Django ORM and SQLAlchemy compare to Eloquent ORM
- ✓ How middleware patterns map from Django/Flask to Laravel
- ✓ How to generate URLs and work with Request/Response objects
- ✓ That framework concepts are universal—only syntax differs

### What You've Achieved

You've built a mental map connecting Python web framework concepts to Laravel. You can see that Laravel isn't fundamentally different—it's the same patterns with PHP syntax. Your Python knowledge is accelerating your Laravel learning.

### Next Steps

In **Chapter 02**, we'll dive deeper into modern PHP itself. You'll learn:

- What's changed in PHP 7/8+ (typed properties, JIT, performance)
- PHP community evolution and modern tooling
- Perception vs reality: why PHP deserves a second look
- Why modern PHP matters for Python developers

## Code Examples

All code examples from this chapter are available in the [`code/chapter-01/`](https://github.com/dalehurley/codewithphp/tree/main/docs/series/python-developers-love-php-laravel/code/chapter-01) directory:

- **Routing**: Flask, Django, and Laravel routing examples
- **URL Generation**: Django `reverse()`, Flask `url_for()`, Laravel `route()` examples
- **Request/Response**: Django, Flask, and Laravel request/response object examples
- **Templates**: Django templates, Jinja2, and Blade examples
- **ORM**: Django ORM, SQLAlchemy, and Eloquent examples
- **Middleware**: Django middleware, Flask decorators, and Laravel middleware examples

See the [README.md](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-01/README.md) for detailed instructions on running each example.

## Further Reading

To deepen your understanding:

- [Laravel Routing Documentation](https://laravel.com/docs/routing) — Complete routing guide
- [Blade Templates Documentation](https://laravel.com/docs/blade) — Blade templating engine
- [Eloquent ORM Documentation](https://laravel.com/docs/eloquent) — Eloquent ORM reference
- [Django vs Laravel Comparison](https://www.google.com/search?q=django+vs+laravel+comparison) — Broader framework comparisons
- [Flask vs Laravel Comparison](https://www.google.com/search?q=flask+vs+laravel+comparison) — Microframework comparisons

---

::: tip Ready to Learn About Modern PHP?
Head to [Chapter 02: Modern PHP: What's Changed](/series/python-developers-love-php-laravel/chapters/02-modern-php-whats-changed) to discover PHP's evolution!
:::
