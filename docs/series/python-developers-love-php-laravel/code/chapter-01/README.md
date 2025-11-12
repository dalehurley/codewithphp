# Chapter 01: Mapping Concepts - Code Examples

This directory contains working code examples demonstrating how Python web framework concepts (Django and Flask) map directly to Laravel. These examples show side-by-side comparisons of routing, templates, and ORM patterns.

## Quick Start

See the mapping concept in action with a simple route comparison:

**Flask:**
```python
@app.route('/user/<int:user_id>')
def user_profile(user_id):
    return f'User {user_id}'
```

**Django:**
```python
# urls.py
path('user/<int:user_id>/', views.user_profile, name='user_profile')

# views.py  
def user_profile(request, user_id):
    return HttpResponse(f'User {user_id}')
```

**Laravel:**
```php
Route::get('/user/{user_id}', function (int $user_id): string {
    return "User {$user_id}";
})->where('user_id', '[0-9]+');
```

**Expected Output:** All three return `"User 123"` when accessing `/user/123`

The pattern is identical—URL pattern, parameter extraction, handler function. Only the syntax differs! Explore the files below to see how templates and ORM queries follow the same pattern.

## 📁 Files Overview

### Routing Examples

#### Flask Routing
- **`flask-routing.py`** - Demonstrates Flask route decorators and parameter handling
  - Run: `flask run` (requires Flask: `pip install flask`)
  - Shows: Route decorators, URL parameters, HTTP methods

#### Django Routing
- **`django-routing-urls.py`** - Django URL configuration
- **`django-routing-views.py`** - Django view functions
  - Place in Django app's `urls.py` and `views.py`
  - Shows: URL patterns, view functions, named routes

#### Laravel Routing
- **`laravel-routing.php`** - Laravel route definitions
  - Place in `routes/web.php` in a Laravel application
  - Run: `php artisan serve`
  - Shows: Route closures, type-hinted parameters, route constraints

### Template Examples

#### Django Templates
- **`django-template-view.py`** - Django view rendering template
- **`django-template.html`** - Django template file
  - Place template in `templates/blog/post_list.html`
  - Shows: Template rendering, context passing, template tags

#### Flask/Jinja2 Templates
- **`flask-template-view.py`** - Flask view rendering template
- **`flask-template.html`** - Jinja2 template file
  - Place template in `templates/post_list.html`
  - Run: `flask run`
  - Shows: Jinja2 syntax, template filters, context variables

#### Laravel Blade Templates
- **`laravel-blade-route.php`** - Laravel route rendering Blade
- **`laravel-blade-template.blade.php`** - Blade template file
  - Place template in `resources/views/blog/post-list.blade.php`
  - Run: `php artisan serve`
  - Shows: Blade directives, PHP variable syntax, helper methods

### URL Generation Examples

#### Django URL Generation
- **`url-generation-django.py`** - Django `reverse()` function examples
  - Shows: Generating URLs in code and templates
  - Place in Django views or use in templates with `{% url %}`

#### Flask URL Generation
- **`url-generation-flask.py`** - Flask `url_for()` function examples
  - Shows: Generating URLs in code and templates
  - Use `url_for()` in routes or Jinja2 templates

#### Laravel URL Generation
- **`url-generation-laravel.php`** - Laravel `route()` helper examples
  - Place routes in `routes/web.php`
  - Shows: Generating URLs in code and Blade templates with `route()`

### Request/Response Examples

#### Django Request/Response
- **`request-response-django.py`** - Django `HttpRequest` and `HttpResponse` examples
  - Shows: Accessing request data, creating responses, JSON responses
  - Place in Django views

#### Flask Request/Response
- **`request-response-flask.py`** - Flask `request` object and response creation
  - Run: `flask run`
  - Shows: Accessing query params, form data, creating responses

#### Laravel Request/Response
- **`request-response-laravel.php`** - Laravel `Request` and `Response` objects
  - Place routes in `routes/web.php`
  - Shows: Type-hinted request objects, response helpers, JSON responses

### ORM Examples

#### Django ORM
- **`django-orm-model.py`** - Django model definition
- **`django-orm-queries.py`** - Django ORM query examples
  - Place model in Django app's `models.py`
  - Shows: Model fields, relationships, query methods, eager loading

#### SQLAlchemy (Flask)
- **`sqlalchemy-model.py`** - SQLAlchemy model definition
- **`sqlalchemy-queries.py`** - SQLAlchemy query examples
  - Used with Flask applications
  - Shows: Column definitions, relationships, query builder, eager loading

#### Eloquent ORM (Laravel)
- **`eloquent-model.php`** - Eloquent model definition
- **`eloquent-queries.php`** - Eloquent query examples
  - Place model in `app/Models/Post.php`
  - Run queries in controllers or tinker: `php artisan tinker`
  - Shows: Model properties, relationships, query builder, eager loading

### Middleware Examples

#### Django Middleware
- **`django-middleware.py`** - Django middleware class example
  - Place in Django app's `middleware.py`
  - Register in `settings.py` `MIDDLEWARE` setting
  - Shows: Class-based middleware with `__call__()` method

#### Flask Middleware
- **`flask-middleware.py`** - Flask middleware using decorators
  - Run: `flask run`
  - Shows: `@before_request` and `@after_request` decorators
  - Alternative WSGI middleware class example included

#### Laravel Middleware
- **`laravel-middleware.php`** - Laravel middleware class example
  - Place in `app/Http/Middleware/LoggingMiddleware.php`
  - Register in `bootstrap/app.php` or `app/Http/Kernel.php`
  - Shows: Class-based middleware with `handle()` method

## 🚀 Quick Start

### Prerequisites

**For Python Examples:**
- Python 3.8+
- Flask: `pip install flask`
- Django: `pip install django`

**For Laravel Examples:**
- PHP 8.4+
- Composer installed
- Laravel application set up

### Running Examples

**Flask Routing:**
```bash
# Install Flask
pip install flask

# Set FLASK_APP environment variable
export FLASK_APP=flask-routing.py

# Run Flask development server
flask run
```

**Django Routing:**
```bash
# These are code snippets to integrate into a Django project
# Place urls.py and views.py in your Django app
# Run: python manage.py runserver
```

**Laravel Routing:**
```bash
# Ensure you're in a Laravel project directory
# Copy laravel-routing.php content to routes/web.php
php artisan serve
# Visit http://localhost:8000
```

**Template Examples:**
- Django: Integrate into Django project structure
- Flask: Place templates in `templates/` directory
- Laravel: Place Blade templates in `resources/views/`

**ORM Examples:**
- Django: Run migrations: `python manage.py makemigrations && python manage.py migrate`
- SQLAlchemy: Set up database connection in Flask app
- Eloquent: Run migrations: `php artisan migrate`

## 📊 Comparison Patterns

### Routing Patterns

| Framework | Pattern | Example |
|-----------|---------|---------|
| Flask | `@app.route('/path')` | `@app.route('/user/<int:id>')` |
| Django | `path('pattern', view)` | `path('user/<int:user_id>/', views.profile)` |
| Laravel | `Route::get('/path', closure)` | `Route::get('/user/{id}', fn($id) => ...)` |

### URL Generation Patterns

| Framework | Function | Example |
|-----------|----------|---------|
| Flask | `url_for()` | `url_for('user_profile', user_id=123)` |
| Django | `reverse()` | `reverse('user_profile', args=[123])` |
| Laravel | `route()` | `route('user_profile', ['user_id' => 123])` |

### Middleware Patterns

| Framework | Type | Example |
|-----------|------|---------|
| Flask | Decorators | `@app.before_request` / `@app.after_request` |
| Django | Class-based | `class Middleware: def __call__(self, request)` |
| Laravel | Class-based | `class Middleware { public function handle() }` |

### Template Patterns

| Framework | Variables | Loops | Filters |
|-----------|-----------|-------|---------|
| Django | `{{ var }}` | `{% for %}` | `{{ var\|filter }}` |
| Jinja2 | `{{ var }}` | `{% for %}` | `{{ var\|filter }}` |
| Blade | `{{ $var }}` | `@foreach` | `{{ Str::method($var) }}` |

### ORM Patterns

| Framework | Get All | Filter | Relationships |
|-----------|---------|-------|--------------|
| Django | `Model.objects.all()` | `Model.objects.filter()` | `ForeignKey()`, `ManyToMany()` |
| SQLAlchemy | `Model.query.all()` | `Model.query.filter()` | `relationship()` |
| Eloquent | `Model::all()` | `Model::where()->get()` | `belongsTo()`, `hasMany()` |

## 🔍 Key Concepts Demonstrated

1. **Routing**: All frameworks use similar patterns—URL patterns mapped to handlers
2. **Templates**: Template engines share concepts (variables, loops, conditionals) with syntax differences
3. **ORM**: Active Record pattern is universal—models represent tables, relationships connect them
4. **MVC/MVT**: Same separation of concerns, different terminology

## 📝 Notes

- **Laravel examples** use PHP 8.4 syntax with `declare(strict_types=1)` and type declarations
- **Python examples** follow PEP 8 style guidelines
- All examples are **complete and runnable** within their respective framework contexts
- Some examples require **database setup** (ORM examples)
- Template examples require **framework installation** to render properly

## 🎯 Learning Objectives

After reviewing these examples, you should understand:

- How Flask/Django routing maps to Laravel routes
- How Django templates and Jinja2 compare to Blade syntax
- How Django ORM and SQLAlchemy queries translate to Eloquent
- That framework concepts are universal—only syntax differs

## 🔗 Related Chapter

[Chapter 01: Mapping Concepts](../../chapters/01-mapping-concepts-python-web-frameworks-vs-laravel.md)

## 💡 Tips

- **Compare side-by-side**: Open Python and PHP examples together to see the patterns
- **Run the examples**: Actually running the code helps cement understanding
- **Modify and experiment**: Change parameters, add routes, try different queries
- **Read the comments**: Each file includes explanatory comments

## 🐛 Troubleshooting

**Flask: "Module not found"**
- Ensure Flask is installed: `pip install flask`
- Check Python path and virtual environment

**Django: "No module named 'django'"**
- Install Django: `pip install django`
- Ensure you're in a Django project directory

**Laravel: "Class not found"**
- Run `composer install` to install dependencies
- Ensure you're in a Laravel project directory
- Check namespace declarations match Laravel conventions

**Templates not rendering:**
- Verify template file paths match framework conventions
- Check template directory configuration
- Ensure template engine is properly configured

