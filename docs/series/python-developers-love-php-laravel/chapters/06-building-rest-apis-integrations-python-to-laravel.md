---
title: "06: Building REST APIs & Integrations: From Python Flask/Django to Laravel"
description: "Build REST APIs in Laravel comparing to Flask-RESTful and Django REST framework—routes, controllers, resources, authentication."
series: "python-developers-love-php-laravel"
chapter: 6
order: 6
difficulty: "Intermediate"
prerequisites:
  - "/series/python-developers-love-php-laravel/chapters/05-working-with-data-eloquent-orm-database-workflow"
---

![Building REST APIs](/images/python-developers-love-php-laravel/chapter-06-building-rest-apis-integrations-python-to-laravel-hero-full.webp)

# Chapter 06: Building REST APIs & Integrations: From Python Flask/Django to Laravel

## Overview

In Chapter 05, you mastered Eloquent ORM—working with models, relationships, migrations, and queries. You now understand how to interact with databases in Laravel. But modern web applications don't just store data—they expose it through APIs, integrate with external services, and communicate with frontend applications, mobile apps, and other backends. This chapter is where your Python API development experience becomes your greatest asset.

If you've built REST APIs with Flask-RESTful or Django REST Framework, you already understand the fundamentals: routes map to endpoints, controllers handle requests, serializers format responses, and authentication protects resources. Laravel's API development follows the same principles with PHP syntax. The concepts are identical: define routes, create controllers, validate requests, format responses, and secure endpoints. The only difference is syntax: `@app.route('/api/users')` becomes `Route::get('/api/users')`, and Django REST serializers become Laravel API Resources.

This chapter is a **comprehensive guide to building REST APIs in Laravel**. We'll compare every major feature to Flask-RESTful and Django REST Framework, showing you Python code you know, then demonstrating the Laravel equivalent. You'll master API routes, controllers, resources (response formatting), request validation, authentication (API tokens, Sanctum), pagination, filtering, external API integrations, and API versioning. By the end, you'll see that Laravel API development isn't fundamentally different—it's Flask/Django REST with PHP syntax and Laravel's delightful developer experience.

## Prerequisites

Before starting this chapter, you should have:

- Completion of [Chapter 05](/series/python-developers-love-php-laravel/chapters/05-working-with-data-eloquent-orm-database-workflow) or equivalent understanding of Eloquent ORM
- Laravel 11.x installed (or ability to follow along with code examples)
- Experience with Flask-RESTful or Django REST Framework (for comparisons)
- Understanding of REST principles (HTTP methods, status codes, JSON)
- Basic knowledge of API authentication (tokens, OAuth)
- Familiarity with HTTP clients (Python `requests` library preferred)
- **Estimated Time**: ~120 minutes

**Verify your setup:**

```bash
# Check PHP version (should show PHP 8.4+)
php --version

# Check Composer is installed
composer --version

# If you have Laravel installed, verify it works
php artisan --version

# Expected output: Laravel Framework 11.x.x (or similar)

# Check if Laravel Sanctum is available (for API authentication)
composer show laravel/sanctum 2>/dev/null || echo "Sanctum will be installed in this chapter"
```

## What You'll Build

By the end of this chapter, you will have:

- Side-by-side comparison examples (Flask-RESTful/Django REST → Laravel) for API routes, controllers, and resources
- Understanding of Laravel API routes vs Flask-RESTful resources and Django REST viewsets
- Knowledge of Laravel API Resources vs Django REST serializers and Flask response formatting
- Mastery of Laravel Form Requests vs Django form validation and Flask request validation
- Ability to implement API authentication using Laravel Sanctum (comparing to Flask-JWT and Django REST tokens)
- Understanding of Laravel HTTP Client (Guzzle) vs Python `requests` library for external integrations
- Working code examples demonstrating pagination, filtering, versioning, and error handling
- Confidence in building REST APIs in Laravel equivalent to your Python API development knowledge

## Quick Start

Want to see how Flask-RESTful maps to Laravel right away? Here's a side-by-side comparison:

**Flask-RESTful (Python):**

```python
# filename: app.py
from flask import Flask
from flask_restful import Resource, Api

app = Flask(__name__)
api = Api(app)

class UserResource(Resource):
    def get(self, user_id):
        return {'id': user_id, 'name': 'John Doe'}

api.add_resource(UserResource, '/api/users/<int:user_id>')
```

**Django REST Framework (Python):**

```python
# filename: views.py
from rest_framework.viewsets import ViewSet
from rest_framework.response import Response

class UserViewSet(ViewSet):
    def retrieve(self, request, pk=None):
        return Response({'id': pk, 'name': 'John Doe'})

# urls.py
from django.urls import path, include
from rest_framework.routers import DefaultRouter

router = DefaultRouter()
router.register(r'users', UserViewSet, basename='user')
urlpatterns = [path('api/', include(router.urls))]
```

**Laravel (PHP):**

```php
// routes/api.php
use App\Http\Controllers\Api\UserController;

Route::get('/users/{user_id}', [UserController::class, 'show']);

// app/Http/Controllers/Api/UserController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function show(int $user_id): JsonResponse
    {
        return response()->json([
            'id' => $user_id,
            'name' => 'John Doe'
        ]);
    }
}
```

See the pattern? Same concepts—routes, controllers, JSON responses—just different syntax! This chapter will show you how every Flask-RESTful and Django REST Framework feature translates to Laravel.

## Objectives

- Map Flask-RESTful Resource classes and Django REST viewsets to Laravel API controllers
- Understand Laravel API Resources vs Django REST serializers and Flask response formatting
- Master Laravel Form Requests vs Django form validation and Flask request validation
- Implement query parameter filtering and sorting in Laravel APIs (comparing to Flask `request.args` and Django REST `query_params`)
- Handle file uploads in API endpoints using Laravel's Storage facade (comparing to Flask and Django REST file handling)
- Configure CORS for APIs consumed by frontend applications (comparing to Flask-CORS and Django CORS headers)
- Implement API authentication using Laravel Sanctum (comparing to Flask-JWT and Django REST tokens)
- Build external API integrations using Laravel HTTP Client (comparing to Python `requests` library)
- Understand pagination, filtering, versioning, and error handling in Laravel APIs
- Recognize that REST API patterns are universal—only syntax differs between Python and PHP

## Step 1: API Routes & Controllers (~20 min)

### Goal

Understand how to define API routes and create controllers in Laravel, comparing Flask-RESTful Resource classes and Django REST viewsets to Laravel's approach.

### Actions

1. **Flask-RESTful API Route** (Python):

The complete Flask-RESTful example is available in [`flask-restful-api.py`](https://github.com/dalehurley/codewithphp/blob/main/code/python-developers-love-php-laravel/chapter-06/flask-restful-api.py):

```python
# filename: app.py
from flask import Flask, request, jsonify
from flask_restful import Resource, Api

app = Flask(__name__)
api = Api(app)

class UserResource(Resource):
    def get(self, user_id):
        # GET /api/users/1
        return {'id': user_id, 'name': 'John Doe', 'email': 'john@example.com'}, 200
    
    def put(self, user_id):
        # PUT /api/users/1
        data = request.get_json()
        return {'id': user_id, **data}, 200
    
    def delete(self, user_id):
        # DELETE /api/users/1
        return {'message': 'User deleted'}, 204

class UserListResource(Resource):
    def get(self):
        # GET /api/users
        return {'users': [
            {'id': 1, 'name': 'John Doe'},
            {'id': 2, 'name': 'Jane Smith'}
        ]}, 200
    
    def post(self):
        # POST /api/users
        data = request.get_json()
        return {'id': 3, **data}, 201

api.add_resource(UserListResource, '/api/users')
api.add_resource(UserResource, '/api/users/<int:user_id>')
```

2. **Django REST Framework ViewSet** (Python):

The complete Django REST example is available in [`django-rest-api.py`](https://github.com/dalehurley/codewithphp/blob/main/code/python-developers-love-php-laravel/chapter-06/django-rest-api.py):

```python
# filename: views.py
from rest_framework import viewsets
from rest_framework.decorators import action
from rest_framework.response import Response
from rest_framework import status

class UserViewSet(viewsets.ViewSet):
    def list(self, request):
        # GET /api/users/
        return Response([
            {'id': 1, 'name': 'John Doe'},
            {'id': 2, 'name': 'Jane Smith'}
        ])
    
    def retrieve(self, request, pk=None):
        # GET /api/users/1/
        return Response({'id': pk, 'name': 'John Doe', 'email': 'john@example.com'})
    
    def create(self, request):
        # POST /api/users/
        data = request.data
        return Response({'id': 3, **data}, status=status.HTTP_201_CREATED)
    
    def update(self, request, pk=None):
        # PUT /api/users/1/
        data = request.data
        return Response({'id': pk, **data})
    
    def destroy(self, request, pk=None):
        # DELETE /api/users/1/
        return Response(status=status.HTTP_204_NO_CONTENT)

# urls.py
from django.urls import path, include
from rest_framework.routers import DefaultRouter
from .views import UserViewSet

router = DefaultRouter()
router.register(r'users', UserViewSet, basename='user')
urlpatterns = [path('api/', include(router.urls))]
```

3. **Laravel API Routes & Controller** (PHP/Laravel):

The complete Laravel API example is available in [`laravel-api-routes.php`](https://github.com/dalehurley/codewithphp/blob/main/code/python-developers-love-php-laravel/chapter-06/laravel-api-routes.php) and [`laravel-api-controller.php`](https://github.com/dalehurley/codewithphp/blob/main/code/python-developers-love-php-laravel/chapter-06/laravel-api-controller.php):

```php
<?php

declare(strict_types=1);

// routes/api.php
use App\Http\Controllers\Api\UserController;

// Individual routes
Route::get('/users', [UserController::class, 'index']);
Route::get('/users/{user_id}', [UserController::class, 'show']);
Route::post('/users', [UserController::class, 'store']);
Route::put('/users/{user_id}', [UserController::class, 'update']);
Route::delete('/users/{user_id}', [UserController::class, 'destroy']);

// Or use resource routes (generates all CRUD routes)
Route::apiResource('users', UserController::class);
```

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     * GET /api/users
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'users' => [
                ['id' => 1, 'name' => 'John Doe'],
                ['id' => 2, 'name' => 'Jane Smith']
            ]
        ]);
    }

    /**
     * Display the specified user.
     * GET /api/users/{user_id}
     */
    public function show(int $user_id): JsonResponse
    {
        return response()->json([
            'id' => $user_id,
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);
    }

    /**
     * Store a newly created user.
     * POST /api/users
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->all();
        
        return response()->json([
            'id' => 3,
            ...$data
        ], 201);
    }

    /**
     * Update the specified user.
     * PUT /api/users/{user_id}
     */
    public function update(Request $request, int $user_id): JsonResponse
    {
        $data = $request->all();
        
        return response()->json([
            'id' => $user_id,
            ...$data
        ]);
    }

    /**
     * Remove the specified user.
     * DELETE /api/users/{user_id}
     */
    public function destroy(int $user_id): JsonResponse
    {
        return response()->json([], 204);
    }
}
```

### Expected Result

You can see the patterns are similar:

- **Flask-RESTful**: `Resource` class with HTTP methods → **Laravel**: `Controller` class with methods
- **Django REST**: `ViewSet` with action methods → **Laravel**: `Controller` with RESTful methods
- **Flask**: `api.add_resource()` → **Laravel**: `Route::apiResource()`
- **Django**: Router registration → **Laravel**: Route definition in `routes/api.php`

### Why It Works

All three frameworks follow RESTful conventions:

- **Flask-RESTful**: Resource classes encapsulate HTTP methods (`get`, `post`, `put`, `delete`)
- **Django REST**: ViewSets provide action methods (`list`, `retrieve`, `create`, `update`, `destroy`)
- **Laravel**: Controllers have standard RESTful methods (`index`, `show`, `store`, `update`, `destroy`)

Laravel's `Route::apiResource()` automatically generates all RESTful routes, similar to Django REST's router registration. The controller methods follow the same naming conventions, making it easy to translate between frameworks.

::: tip API Routes vs Web Routes
Laravel separates API routes (`routes/api.php`) from web routes (`routes/web.php`). API routes are automatically prefixed with `/api` and don't include CSRF protection (since APIs use token authentication). Web routes include CSRF protection and session middleware.
:::

### Comparison Table

| Feature | Flask-RESTful | Django REST Framework | Laravel |
|---------|---------------|----------------------|---------|
| **Route Definition** | `api.add_resource()` | Router registration | `Route::apiResource()` |
| **Controller Base** | `Resource` class | `ViewSet` class | `Controller` class |
| **List Endpoint** | `get()` in ListResource | `list()` method | `index()` method |
| **Detail Endpoint** | `get(id)` in Resource | `retrieve()` method | `show()` method |
| **Create Endpoint** | `post()` in ListResource | `create()` method | `store()` method |
| **Update Endpoint** | `put(id)` in Resource | `update()` method | `update()` method |
| **Delete Endpoint** | `delete(id)` in Resource | `destroy()` method | `destroy()` method |
| **Request Data** | `request.get_json()` | `request.data` | `$request->all()` |
| **JSON Response** | Return dict | `Response(data)` | `response()->json()` |

### Troubleshooting

- **"Routes not found"** — Make sure you're accessing `/api/users` (with `/api` prefix) for API routes. Laravel automatically prefixes `routes/api.php` routes with `/api`.
- **"Method not allowed"** — Check that you're using the correct HTTP method. `Route::apiResource()` creates routes for GET (index/show), POST (store), PUT/PATCH (update), and DELETE (destroy).
- **"Controller not found"** — Ensure your controller namespace matches: `App\Http\Controllers\Api\UserController`. Run `php artisan route:list` to see all registered routes.
- **"CSRF token mismatch"** — API routes don't require CSRF tokens. If you're getting this error, you might be hitting a web route instead of an API route. Check your route file (`api.php` vs `web.php`).

### Pagination in API Responses

When returning lists of resources, pagination is essential for performance. Here's how to add pagination:

**Laravel Pagination:**

```php
// In controller
public function index(): JsonResponse
{
    $users = User::paginate(15); // 15 items per page
    
    return response()->json($users);
}

// Response format:
// {
//   "data": [...],
//   "current_page": 1,
//   "per_page": 15,
//   "total": 100,
//   "last_page": 7,
//   "from": 1,
//   "to": 15
// }
```

**Compare to Django REST:**

```python
# Django REST pagination
from rest_framework.pagination import PageNumberPagination

class UserViewSet(viewsets.ModelViewSet):
    pagination_class = PageNumberPagination
    
    def list(self, request):
        queryset = User.objects.all()
        page = self.paginate_queryset(queryset)
        serializer = UserSerializer(page, many=True)
        return self.get_paginated_response(serializer.data)
```

Laravel's `paginate()` method automatically handles query parameters (`?page=2`) and returns paginated results with metadata, similar to Django REST's pagination classes.

::: tip Rate Limiting
Laravel includes built-in rate limiting for API routes. Add `->middleware('throttle:60,1')` to limit requests to 60 per minute:

```php
Route::middleware('throttle:60,1')->group(function () {
    Route::apiResource('users', UserController::class);
});
```

This is similar to Flask's `@limiter.limit()` or Django REST's throttling classes.
:::

### Query Parameter Filtering & Sorting

Most APIs need to support filtering and sorting via query parameters. Here's how to handle this in Laravel:

**Flask Query Parameters (Python):**

```python
# Flask
from flask import request

@app.route('/api/users')
def get_users():
    status = request.args.get('status')
    sort = request.args.get('sort', 'created_at')
    order = request.args.get('order', 'desc')
    
    query = User.query
    if status:
        query = query.filter_by(status=status)
    
    if order == 'desc':
        query = query.order_by(getattr(User, sort).desc())
    else:
        query = query.order_by(getattr(User, sort).asc())
    
    return jsonify([u.to_dict() for u in query.all()])
```

**Django REST Query Parameters (Python):**

```python
# Django REST Framework
class UserViewSet(viewsets.ModelViewSet):
    def list(self, request):
        queryset = User.objects.all()
        
        # Filtering
        status = request.query_params.get('status')
        if status:
            queryset = queryset.filter(status=status)
        
        # Sorting
        sort = request.query_params.get('sort', 'created_at')
        order = request.query_params.get('order', 'desc')
        if order == 'desc':
            queryset = queryset.order_by(f'-{sort}')
        else:
            queryset = queryset.order_by(sort)
        
        serializer = UserSerializer(queryset, many=True)
        return Response(serializer.data)
```

**Laravel Query Parameters (PHP):**

The complete Laravel query filtering example is available in [`laravel-query-filtering.php`](https://github.com/dalehurley/codewithphp/blob/main/code/python-developers-love-php-laravel/chapter-06/laravel-query-filtering.php):

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::query();
        
        // Filtering: ?status=active
        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        }
        
        // Filtering: ?author_id=1
        if ($request->has('author_id')) {
            $query->where('author_id', $request->query('author_id'));
        }
        
        // Sorting: ?sort=created_at&order=desc
        $sort = $request->query('sort', 'created_at');
        $order = $request->query('order', 'desc');
        
        $query->orderBy($sort, $order);
        
        // Pagination with filters
        $users = $query->paginate(15);
        
        return response()->json($users);
    }
}
```

**Advanced Filtering with Validation:**

```php
// Validate query parameters using Form Request
class IndexUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'string', 'in:active,inactive,pending'],
            'author_id' => ['sometimes', 'integer', 'exists:users,id'],
            'sort' => ['sometimes', 'string', 'in:created_at,updated_at,name'],
            'order' => ['sometimes', 'string', 'in:asc,desc'],
        ];
    }
}

// In controller
public function index(IndexUserRequest $request): JsonResponse
{
    $query = User::query();
    
    // All parameters are validated
    if ($request->filled('status')) {
        $query->where('status', $request->validated()['status']);
    }
    
    $sort = $request->validated()['sort'] ?? 'created_at';
    $order = $request->validated()['order'] ?? 'desc';
    
    $users = $query->orderBy($sort, $order)->paginate(15);
    
    return response()->json($users);
}
```

**Pattern Comparison:**

- **Flask**: `request.args.get('key')` → **Laravel**: `$request->query('key')` or `$request->get('key')`
- **Django REST**: `request.query_params.get('key')` → **Laravel**: `$request->query('key')`
- **Flask**: Manual query building → **Laravel**: Eloquent query builder with conditional methods
- **Django REST**: `queryset.filter()` → **Laravel**: `$query->where()`

Laravel's query builder provides a fluent interface for building dynamic queries based on request parameters, similar to Django's QuerySet methods but with PHP syntax.

::: tip Query Parameter Validation
Always validate query parameters using Form Requests or manual validation. This prevents SQL injection and ensures data integrity. Use `$request->filled('key')` to check if a parameter exists and is not empty.
:::

## Step 2: API Resources & Transformers (~15 min)

### Goal

Understand how to format API responses using Laravel API Resources, comparing Django REST serializers and Flask response formatting to Laravel's approach.

### Actions

1. **Django REST Framework Serializer** (Python):

The complete Django REST serializer example is available in [`django-serializer.py`](https://github.com/dalehurley/codewithphp/blob/main/code/python-developers-love-php-laravel/chapter-06/django-serializer.py):

```python
# filename: serializers.py
from rest_framework import serializers

class UserSerializer(serializers.Serializer):
    id = serializers.IntegerField()
    name = serializers.CharField()
    email = serializers.EmailField()
    created_at = serializers.DateTimeField()
    
    # Computed field
    profile_url = serializers.SerializerMethodField()
    
    def get_profile_url(self, obj):
        return f"/users/{obj['id']}"

# Usage in view
class UserViewSet(viewsets.ViewSet):
    def retrieve(self, request, pk=None):
        user_data = {
            'id': 1,
            'name': 'John Doe',
            'email': 'john@example.com',
            'created_at': '2024-01-01T00:00:00Z'
        }
        serializer = UserSerializer(user_data)
        return Response(serializer.data)
```

2. **Flask Response Formatting** (Python):

```python
# filename: app.py
from flask import Flask, jsonify
from datetime import datetime

def format_user(user):
    return {
        'id': user['id'],
        'name': user['name'],
        'email': user['email'],
        'created_at': user['created_at'].isoformat(),
        'profile_url': f"/users/{user['id']}"
    }

@app.route('/api/users/<int:user_id>')
def get_user(user_id):
    user = {
        'id': user_id,
        'name': 'John Doe',
        'email': 'john@example.com',
        'created_at': datetime.now()
    }
    return jsonify(format_user(user))
```

3. **Laravel API Resource** (PHP/Laravel):

The complete Laravel API Resource example is available in [`laravel-api-resource.php`](https://github.com/dalehurley/codewithphp/blob/main/code/python-developers-love-php-laravel/chapter-06/laravel-api-resource.php):

```php
<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'created_at' => $this->created_at->toIso8601String(),
            'profile_url' => "/users/{$this->id}",
        ];
    }
}

// Usage in controller
namespace App\Http\Controllers\Api;

use App\Http\Resources\UserResource;
use App\Models\User;

class UserController extends Controller
{
    public function show(int $user_id): JsonResponse
    {
        $user = User::findOrFail($user_id);
        return new UserResource($user);
    }
    
    public function index(): JsonResponse
    {
        $users = User::all();
        return UserResource::collection($users);
    }
}
```

### Expected Result

You can see the patterns are similar:

- **Django REST**: `Serializer` class with fields → **Laravel**: `JsonResource` class with `toArray()` method
- **Django REST**: `SerializerMethodField()` → **Laravel**: Computed properties in `toArray()`
- **Flask**: Manual formatting function → **Laravel**: Resource class with transformation logic
- **Django REST**: `serializer.data` → **Laravel**: `new UserResource($model)`

### Why It Works

All three approaches transform model data into API responses:

- **Django REST**: Serializers define fields and transformation logic, returning a dictionary
- **Flask**: Manual formatting functions transform data before returning JSON
- **Laravel**: API Resources encapsulate transformation logic in `toArray()`, automatically converting models to arrays

Laravel's API Resources provide a clean, reusable way to format responses, similar to Django REST serializers. They automatically handle relationships, collections, and conditional fields.

::: tip Resource Collections
Use `UserResource::collection($users)` to transform multiple models. Laravel automatically wraps collections in a `data` key, similar to Django REST's pagination format.
:::

### Comparison Table

| Feature | Django REST Framework | Flask | Laravel |
|---------|----------------------|-------|---------|
| **Transformation Class** | `Serializer` | Manual function | `JsonResource` |
| **Field Definition** | `serializers.Field()` | Manual dict | Array in `toArray()` |
| **Computed Fields** | `SerializerMethodField()` | Function logic | Computed in `toArray()` |
| **Single Resource** | `Serializer(data).data` | `format_user(user)` | `new UserResource($model)` |
| **Collection** | `Serializer(data, many=True)` | List comprehension | `UserResource::collection()` |
| **Nested Resources** | Nested serializer | Nested function | `new RelatedResource()` |

### Troubleshooting

- **"Property does not exist"** — Make sure the property exists on the model. API Resources access model properties directly (`$this->name`), not array keys (`$this['name']`).
- **"Collection not formatted correctly"** — Use `UserResource::collection($users)` instead of mapping manually. Laravel handles the collection wrapper automatically.
- **"Date formatting issues"** — Use `$this->created_at->toIso8601String()` or `$this->created_at->format('Y-m-d\TH:i:s\Z')` for ISO 8601 format. Laravel's Carbon provides many formatting methods.

### Conditional Fields in Resources

You can include fields conditionally based on the request:

```php
public function toArray(Request $request): array
{
    return [
        'id' => $this->id,
        'name' => $this->name,
        'email' => $this->email,
        // Only include admin fields if user is admin
        'admin_data' => $this->when($request->user()?->isAdmin(), [
            'internal_notes' => $this->internal_notes,
        ]),
    ];
}
```

This is similar to Django REST's `SerializerMethodField()` with conditional logic or Flask's conditional dictionary keys.

## Step 3: Request Validation (~15 min)

### Goal

Master request validation in Laravel using Form Requests, comparing Django form validation and Flask request validation to Laravel's approach.

### Actions

1. **Flask Request Validation** (Python):

The complete Flask validation example is available in [`flask-validation.py`](https://github.com/dalehurley/codewithphp/blob/main/code/python-developers-love-php-laravel/chapter-06/flask-validation.py):

```python
# filename: app.py
from flask import Flask, request, jsonify
from marshmallow import Schema, fields, ValidationError

class UserSchema(Schema):
    name = fields.Str(required=True, validate=fields.Length(min=2, max=100))
    email = fields.Email(required=True)
    age = fields.Int(required=False, validate=fields.Range(min=0, max=150))

@app.route('/api/users', methods=['POST'])
def create_user():
    try:
        data = UserSchema().load(request.get_json())
        # Process valid data
        return jsonify({'id': 1, **data}), 201
    except ValidationError as err:
        return jsonify({'errors': err.messages}), 400
```

2. **Django Form Validation** (Python):

```python
# filename: forms.py
from django import forms

class UserForm(forms.Form):
    name = forms.CharField(max_length=100, min_length=2, required=True)
    email = forms.EmailField(required=True)
    age = forms.IntegerField(required=False, min_value=0, max_value=150)

# In view
def create_user(request):
    form = UserForm(request.data)
    if form.is_valid():
        # Process valid data
        return Response({'id': 1, **form.cleaned_data}, status=201)
    return Response({'errors': form.errors}, status=400)
```

3. **Laravel Form Request** (PHP/Laravel):

The complete Laravel Form Request example is available in [`laravel-form-request.php`](https://github.com/dalehurley/codewithphp/blob/main/code/python-developers-love-php-laravel/chapter-06/laravel-form-request.php):

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Add authorization logic here
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'email'],
            'age' => ['nullable', 'integer', 'min:0', 'max:150'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'email.email' => 'The email must be a valid email address.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'errors' => $validator->errors()
            ], 422)
        );
    }
}

// Usage in controller
namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreUserRequest;

class UserController extends Controller
{
    public function store(StoreUserRequest $request): JsonResponse
    {
        // Validation passed automatically
        $validated = $request->validated();
        
        return response()->json([
            'id' => 1,
            ...$validated
        ], 201);
    }
}
```

### Expected Result

You can see the patterns are similar:

- **Flask**: Schema validation with Marshmallow → **Laravel**: Form Request with rules
- **Django**: Form class with field definitions → **Laravel**: Form Request with rules array
- **Flask**: `ValidationError` exception → **Laravel**: Automatic validation with `failedValidation()`
- **Django**: `form.is_valid()` → **Laravel**: Automatic validation before controller method

### Why It Works

All three approaches validate request data before processing:

- **Flask**: Manual validation using schemas (Marshmallow) or validators, returning error responses
- **Django**: Form classes define validation rules, checking `is_valid()` before processing
- **Laravel**: Form Requests automatically validate before the controller method executes, throwing exceptions on failure

Laravel's Form Requests provide a clean separation of validation logic from controllers, similar to Django forms. They automatically return JSON error responses for API requests.

::: tip Validation Rules
Laravel provides many built-in validation rules: `required`, `email`, `min`, `max`, `integer`, `string`, `array`, `exists`, `unique`, etc. See the [Laravel Validation documentation](https://laravel.com/docs/validation) for the complete list.
:::

### Comparison Table

| Feature | Flask (Marshmallow) | Django Forms | Laravel Form Requests |
|---------|---------------------|--------------|---------------------|
| **Validation Class** | `Schema` | `Form` | `FormRequest` |
| **Field Rules** | `fields.Str(required=True)` | `forms.CharField(required=True)` | `'name' => ['required', 'string']` |
| **Validation Check** | `Schema().load()` | `form.is_valid()` | Automatic (before controller) |
| **Valid Data** | Returned dict | `form.cleaned_data` | `$request->validated()` |
| **Error Format** | `err.messages` | `form.errors` | `$validator->errors()` |
| **Error Response** | Manual `jsonify()` | Manual `Response()` | Automatic JSON (API) |

### Troubleshooting

- **"Validation not running"** — Make sure you type-hint the Form Request in your controller method: `public function store(StoreUserRequest $request)`. Laravel automatically validates before the method executes.
- **"422 Unprocessable Entity"** — This is Laravel's standard validation error status code. Check `$validator->errors()` in your error response to see validation messages.
- **"Custom error messages not showing"** — Override the `messages()` method in your Form Request. Laravel uses these custom messages instead of default ones.
- **"Authorization check failing"** — Override the `authorize()` method in your Form Request. Return `true` to allow all requests, or add authorization logic.

### File Uploads in API Endpoints

APIs often need to handle file uploads. Here's how to handle multipart/form-data requests in Laravel:

**Flask File Upload (Python):**

```python
# Flask
from flask import request
from werkzeug.utils import secure_filename
import os

@app.route('/api/users/<int:user_id>/avatar', methods=['POST'])
def upload_avatar(user_id):
    if 'file' not in request.files:
        return jsonify({'error': 'No file provided'}), 400
    
    file = request.files['file']
    if file.filename == '':
        return jsonify({'error': 'No file selected'}), 400
    
    if file and allowed_file(file.filename):
        filename = secure_filename(file.filename)
        file.save(os.path.join('uploads', filename))
        return jsonify({'url': f'/uploads/{filename}'}), 201
```

**Django REST File Upload (Python):**

```python
# Django REST Framework
from rest_framework.parsers import MultiPartParser, FormParser
from rest_framework.decorators import parser_classes

class UserViewSet(viewsets.ModelViewSet):
    @parser_classes([MultiPartParser, FormParser])
    def update(self, request, pk=None):
        user = self.get_object()
        if 'avatar' in request.FILES:
            user.avatar = request.FILES['avatar']
            user.save()
        return Response({'avatar_url': user.avatar.url})
```

**Laravel File Upload (PHP):**

The complete Laravel file upload example is available in [`laravel-file-upload.php`](https://github.com/dalehurley/codewithphp/blob/main/code/python-developers-love-php-laravel/chapter-06/laravel-file-upload.php):

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadAvatarRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Upload user avatar.
     * POST /api/users/{user_id}/avatar
     */
    public function uploadAvatar(UploadAvatarRequest $request, int $user_id): JsonResponse
    {
        $user = User::findOrFail($user_id);
        
        // Store file and get path
        $path = $request->file('avatar')->store('avatars', 'public');
        
        // Update user record
        $user->avatar_path = $path;
        $user->save();
        
        // Return public URL
        return response()->json([
            'avatar_url' => Storage::url($path)
        ], 201);
    }
}
```

**File Upload Validation:**

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadAvatarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'avatar' => [
                'required',
                'file',
                'image',
                'mimes:jpeg,png,jpg,gif',
                'max:2048', // 2MB
                'dimensions:min_width=100,min_height=100',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'avatar.required' => 'Please select an image file.',
            'avatar.image' => 'The file must be an image.',
            'avatar.mimes' => 'The image must be a jpeg, png, jpg, or gif.',
            'avatar.max' => 'The image must not be larger than 2MB.',
            'avatar.dimensions' => 'The image must be at least 100x100 pixels.',
        ];
    }
}
```

**File Storage Configuration:**

```php
// config/filesystems.php
'disks' => [
    'public' => [
        'driver' => 'local',
        'root' => storage_path('app/public'),
        'url' => env('APP_URL').'/storage',
        'visibility' => 'public',
    ],
    
    's3' => [
        'driver' => 's3',
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION'),
        'bucket' => env('AWS_BUCKET'),
    ],
],

// Store to S3
$path = $request->file('avatar')->store('avatars', 's3');
$url = Storage::disk('s3')->url($path);
```

**Pattern Comparison:**

- **Flask**: `request.files['key']` → **Laravel**: `$request->file('key')`
- **Django REST**: `request.FILES['key']` → **Laravel**: `$request->file('key')`
- **Flask**: `file.save(path)` → **Laravel**: `$file->store('path', 'disk')`
- **Django REST**: `user.avatar = request.FILES['avatar']` → **Laravel**: `Storage::store()` then update model

Laravel's file storage system provides a unified API for local and cloud storage (S3, etc.), similar to Django's storage backends but with a simpler interface.

::: tip File Storage
Laravel's Storage facade abstracts file storage, allowing you to switch between local storage and cloud storage (S3, etc.) without changing your code. Use `Storage::disk('s3')->store()` for cloud storage or `Storage::disk('public')->store()` for local storage.
:::

## Step 4: Authentication & Authorization (~25 min)

### Goal

Implement API authentication in Laravel using Sanctum, comparing Flask-JWT and Django REST authentication to Laravel's token-based authentication.

### Actions

1. **Flask-JWT Authentication** (Python):

```python
# filename: app.py
from flask import Flask, request, jsonify
from flask_jwt_extended import JWTManager, jwt_required, create_access_token, get_jwt_identity

app = Flask(__name__)
app.config['JWT_SECRET_KEY'] = 'your-secret-key'
jwt = JWTManager(app)

@app.route('/api/login', methods=['POST'])
def login():
    data = request.get_json()
    # Validate credentials
    if data['email'] == 'user@example.com' and data['password'] == 'password':
        token = create_access_token(identity=data['email'])
        return jsonify({'token': token}), 200
    return jsonify({'error': 'Invalid credentials'}), 401

@app.route('/api/protected', methods=['GET'])
@jwt_required()
def protected():
    current_user = get_jwt_identity()
    return jsonify({'user': current_user}), 200
```

2. **Django REST Framework Authentication** (Python):

The complete Django REST auth example is available in [`django-rest-auth.py`](https://github.com/dalehurley/codewithphp/blob/main/code/python-developers-love-php-laravel/chapter-06/django-rest-auth.py):

```python
# filename: views.py
from rest_framework.authtoken.models import Token
from rest_framework.decorators import api_view, permission_classes
from rest_framework.permissions import IsAuthenticated
from rest_framework.response import Response
from rest_framework import status

@api_view(['POST'])
def login(request):
    # Validate credentials
    if request.data['email'] == 'user@example.com':
        user = User.objects.get(email=request.data['email'])
        token, created = Token.objects.get_or_create(user=user)
        return Response({'token': token.key}, status=status.HTTP_200_OK)
    return Response({'error': 'Invalid credentials'}, status=status.HTTP_401_UNAUTHORIZED)

@api_view(['GET'])
@permission_classes([IsAuthenticated])
def protected(request):
    return Response({'user': request.user.email})
```

3. **Laravel Sanctum Authentication** (PHP/Laravel):

The complete Laravel Sanctum example is available in [`laravel-api-auth.php`](https://github.com/dalehurley/codewithphp/blob/main/code/python-developers-love-php-laravel/chapter-06/laravel-api-auth.php):

```php
<?php

declare(strict_types=1);

// First, install Sanctum
// composer require laravel/sanctum
// php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
// php artisan migrate

// routes/api.php
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;

Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{user_id}', [UserController::class, 'show']);
});

// app/Http/Controllers/Api/AuthController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = Auth::user();
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}

// app/Http/Controllers/Api/UserController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user()->email
        ]);
    }
}
```

### Expected Result

You can see the patterns are similar:

- **Flask-JWT**: `@jwt_required()` decorator → **Laravel**: `auth:sanctum` middleware
- **Django REST**: `@permission_classes([IsAuthenticated])` → **Laravel**: `auth:sanctum` middleware
- **Flask-JWT**: `create_access_token()` → **Laravel**: `$user->createToken()`
- **Django REST**: `Token.objects.create()` → **Laravel**: `createToken()` method

### Why It Works

All three approaches use token-based authentication:

- **Flask-JWT**: JWT tokens stored client-side, validated on each request using decorators
- **Django REST**: Database tokens stored in `authtoken_token` table, validated using permissions
- **Laravel Sanctum**: Database tokens stored in `personal_access_tokens` table, validated using middleware

Laravel Sanctum provides a simple, secure way to authenticate API requests. Tokens are stored in the database (like Django REST) but with a cleaner API (like Flask-JWT). The `auth:sanctum` middleware automatically validates tokens and sets the authenticated user.

::: tip Token Storage
Sanctum tokens are stored in the `personal_access_tokens` table. Each token is hashed before storage, and the plain-text token is only shown once during creation. Store it securely in your frontend application.
:::

### Comparison Table

| Feature | Flask-JWT | Django REST | Laravel Sanctum |
|---------|-----------|-------------|-----------------|
| **Token Type** | JWT (stateless) | Database token | Database token |
| **Token Creation** | `create_access_token()` | `Token.objects.create()` | `$user->createToken()` |
| **Protection** | `@jwt_required()` | `@permission_classes([IsAuthenticated])` | `auth:sanctum` middleware |
| **Current User** | `get_jwt_identity()` | `request.user` | `$request->user()` |
| **Token Storage** | Client-side (localStorage) | Database | Database (hashed) |
| **Token Expiration** | Configurable | Manual | Configurable |

### Troubleshooting

- **"Unauthenticated" error** — Make sure you're sending the token in the `Authorization` header: `Authorization: Bearer {token}`. Sanctum expects the `Bearer` prefix.
- **"Token not found"** — Check that the token exists in the `personal_access_tokens` table. Expired or deleted tokens won't authenticate.
- **"Middleware not applied"** — Add `auth:sanctum` middleware to your routes or controller constructor. API routes need explicit middleware application.
- **"Token creation failing"** — Ensure the `personal_access_tokens` table exists. Run `php artisan migrate` after installing Sanctum.

### CORS Configuration for APIs

When building APIs consumed by frontend applications, CORS (Cross-Origin Resource Sharing) configuration is essential. Here's how to configure CORS in Laravel:

**Flask-CORS (Python):**

```python
# Flask with flask-cors
from flask import Flask
from flask_cors import CORS

app = Flask(__name__)
CORS(app, resources={
    r"/api/*": {
        "origins": ["http://localhost:3000", "https://example.com"],
        "methods": ["GET", "POST", "PUT", "DELETE"],
        "allow_headers": ["Content-Type", "Authorization"]
    }
})
```

**Django CORS Headers (Python):**

```python
# settings.py
INSTALLED_APPS = [
    'corsheaders',
    # ...
]

MIDDLEWARE = [
    'corsheaders.middleware.CorsMiddleware',
    # ...
]

CORS_ALLOWED_ORIGINS = [
    "http://localhost:3000",
    "https://example.com",
]

CORS_ALLOW_METHODS = [
    'DELETE',
    'GET',
    'OPTIONS',
    'PATCH',
    'POST',
    'PUT',
]

CORS_ALLOW_HEADERS = [
    'accept',
    'accept-encoding',
    'authorization',
    'content-type',
]
```

**Laravel CORS Configuration (PHP):**

The complete Laravel CORS configuration example is available in [`laravel-cors-config.php`](https://github.com/dalehurley/codewithphp/blob/main/code/python-developers-love-php-laravel/chapter-06/laravel-cors-config.php):

Laravel includes CORS middleware by default. Configure it in `config/cors.php`:

```php
<?php

declare(strict_types=1);

// config/cors.php
return [
    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    */
    
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    
    'allowed_methods' => ['*'],
    
    'allowed_origins' => [
        'http://localhost:3000',
        'https://example.com',
    ],
    
    'allowed_origins_patterns' => [],
    
    'allowed_headers' => ['*'],
    
    'exposed_headers' => [],
    
    'max_age' => 0,
    
    'supports_credentials' => true,
];
```

**Environment-Based Configuration:**

```php
// config/cors.php
'allowed_origins' => env('CORS_ALLOWED_ORIGINS', 'http://localhost:3000')
    ? explode(',', env('CORS_ALLOWED_ORIGINS'))
    : [],
```

**Apply CORS Middleware:**

```php
// bootstrap/app.php (Laravel 11)
->withMiddleware(function (Middleware $middleware): void {
    $middleware->api(prepend: [
        \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    ]);
    
    $middleware->alias([
        'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
    ]);
    
    // CORS is handled automatically for API routes
})

// Or in app/Http/Kernel.php (Laravel 10 and earlier)
protected $middlewareGroups = [
    'api' => [
        \Illuminate\Http\Middleware\HandleCors::class,
        // ...
    ],
];
```

**Pattern Comparison:**

- **Flask-CORS**: `CORS(app, resources={...})` → **Laravel**: `config/cors.php` configuration
- **Django CORS**: `CORS_ALLOWED_ORIGINS` in settings → **Laravel**: `allowed_origins` in config
- **Flask-CORS**: Decorator-based → **Laravel**: Middleware-based (automatic for API routes)
- **Django CORS**: Middleware in `MIDDLEWARE` → **Laravel**: Automatic for `api/*` routes

Laravel's CORS configuration is centralized in `config/cors.php`, making it easier to manage than Flask's decorator-based approach or Django's settings-based configuration.

::: tip CORS for Development
For development, you can allow all origins temporarily: `'allowed_origins' => ['*']`. Never use this in production! Always specify exact origins in production.
:::

**Common CORS Issues:**

- **"CORS policy blocked"** — Check that your frontend origin is in `allowed_origins`. Use browser DevTools Network tab to see the exact error.
- **"Preflight request failed"** — Ensure `OPTIONS` method is allowed in `allowed_methods`. Laravel handles this automatically.
- **"Credentials not sent"** — Set `supports_credentials` to `true` and ensure frontend sends `credentials: 'include'` in fetch requests.
- **"Authorization header blocked"** — Add `Authorization` to `allowed_headers` or use `['*']` to allow all headers.

## Step 5: External API Integrations (~20 min)

### Goal

Learn how to make HTTP requests to external APIs using Laravel's HTTP Client, comparing Python's `requests` library to Laravel's Guzzle-based HTTP client.

### Actions

1. **Python `requests` Library** (Python):

The complete Python requests example is available in [`python-requests-example.py`](https://github.com/dalehurley/codewithphp/blob/main/code/python-developers-love-php-laravel/chapter-06/python-requests-example.py):

```python
# filename: external_api.py
import requests
from requests.exceptions import RequestException, HTTPError

def fetch_user_data(user_id):
    try:
        response = requests.get(
            f'https://api.example.com/users/{user_id}',
            headers={'Authorization': 'Bearer token123'},
            timeout=10
        )
        response.raise_for_status()  # Raises HTTPError for bad status codes
        return response.json()
    except HTTPError as e:
        print(f'HTTP error: {e}')
        return None
    except RequestException as e:
        print(f'Request error: {e}')
        return None

def create_user(user_data):
    response = requests.post(
        'https://api.example.com/users',
        json=user_data,
        headers={'Authorization': 'Bearer token123', 'Content-Type': 'application/json'},
        timeout=10
    )
    return response.json(), response.status_code
```

2. **Laravel HTTP Client** (PHP/Laravel):

The complete Laravel HTTP client example is available in [`laravel-http-client.php`](https://github.com/dalehurley/codewithphp/blob/main/code/python-developers-love-php-laravel/chapter-06/laravel-http-client.php):

```php
<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;

class ExternalApiService
{
    /**
     * Fetch user data from external API.
     */
    public function fetchUserData(int $userId): ?array
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => 'Bearer token123',
                ])
                ->get("https://api.example.com/users/{$userId}");

            $response->throw(); // Throws exception for bad status codes

            return $response->json();
        } catch (RequestException $e) {
            // Log error or handle gracefully
            logger()->error('External API error', [
                'url' => $e->request->url(),
                'status' => $e->response->status(),
            ]);
            return null;
        }
    }

    /**
     * Create user in external API.
     */
    public function createUser(array $userData): array
    {
        $response = Http::timeout(10)
            ->withHeaders([
                'Authorization' => 'Bearer token123',
                'Content-Type' => 'application/json',
            ])
            ->post('https://api.example.com/users', $userData);

        return [
            'data' => $response->json(),
            'status' => $response->status(),
        ];
    }

    /**
     * Make request with retry logic.
     */
    public function fetchWithRetry(string $url, int $maxRetries = 3): ?array
    {
        return Http::retry($maxRetries, 1000)
            ->get($url)
            ->json();
    }
}
```

### Expected Result

You can see the patterns are similar:

- **Python**: `requests.get()` → **Laravel**: `Http::get()`
- **Python**: `response.json()` → **Laravel**: `$response->json()`
- **Python**: `response.raise_for_status()` → **Laravel**: `$response->throw()`
- **Python**: `requests.exceptions.RequestException` → **Laravel**: `RequestException`

### Why It Works

Both libraries provide a simple interface for making HTTP requests:

- **Python `requests`**: Synchronous HTTP library with intuitive API, exception handling, and JSON support
- **Laravel HTTP Client**: Wrapper around Guzzle HTTP client, providing fluent API, automatic JSON handling, and Laravel integration

Laravel's HTTP Client provides a clean, fluent interface similar to Python's `requests`. It automatically handles JSON encoding/decoding, provides retry logic, and integrates seamlessly with Laravel's logging and error handling.

::: tip HTTP Client Features
Laravel's HTTP Client provides many features: retries, timeouts, authentication, file uploads, and async requests. See the [Laravel HTTP Client documentation](https://laravel.com/docs/http-client) for the complete API.
:::

### Comparison Table

| Feature | Python `requests` | Laravel HTTP Client |
|---------|------------------|---------------------|
| **GET Request** | `requests.get(url)` | `Http::get($url)` |
| **POST Request** | `requests.post(url, json=data)` | `Http::post($url, $data)` |
| **Headers** | `headers={'Key': 'value'}` | `->withHeaders(['Key' => 'value'])` |
| **Timeout** | `timeout=10` | `->timeout(10)` |
| **JSON Response** | `response.json()` | `$response->json()` |
| **Status Check** | `response.raise_for_status()` | `$response->throw()` |
| **Exception** | `RequestException` | `RequestException` |
| **Retry Logic** | Manual with loop | `->retry($times, $delay)` |

### Troubleshooting

- **"Connection timeout"** — Increase the timeout value: `Http::timeout(30)->get($url)`. Default is 30 seconds, but some APIs are slower.
- **"SSL certificate error"** — Use `Http::withoutVerifying()->get($url)` for development only. Never disable SSL verification in production.
- **"JSON decode error"** — Check that the API returns valid JSON. Use `$response->body()` to see the raw response.
- **"Rate limiting"** — Implement retry logic with exponential backoff: `Http::retry(3, 1000)->get($url)`.

### Error Handling Best Practices

When building APIs, consistent error handling is crucial:

**Laravel API Error Responses:**

```php
// In controller
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function show(int $user_id): JsonResponse
    {
        try {
            $user = User::findOrFail($user_id);
            return new UserResource($user);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'User not found',
                'message' => "No user found with ID {$user_id}"
            ], 404);
        }
    }
}

// Or use Laravel's automatic exception handling
// app/Exceptions/Handler.php
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;

public function render($request, Throwable $exception)
{
    if ($request->expectsJson()) {
        if ($exception instanceof ModelNotFoundException) {
            return response()->json([
                'error' => 'Resource not found'
            ], 404);
        }
    }
    
    return parent::render($request, $exception);
}
```
<｜tool▁calls▁begin｜><｜tool▁call▁begin｜>
read_lints

**Compare to Flask/Django:**

```python
# Flask
@app.errorhandler(404)
def not_found(error):
    return jsonify({'error': 'Resource not found'}), 404

# Django REST
from rest_framework.exceptions import NotFound

class UserViewSet(viewsets.ViewSet):
    def retrieve(self, request, pk=None):
        try:
            user = User.objects.get(pk=pk)
        except User.DoesNotExist:
            raise NotFound('User not found')
```

Laravel's exception handling can be customized in the `Handler` class, similar to Flask's error handlers or Django REST's exception classes.

## Step 6: API Versioning & Documentation (~15 min)

### Goal

Understand API versioning strategies and documentation approaches in Laravel, comparing common Python API versioning patterns.

### Actions

1. **API Versioning in Routes** (Laravel):

The complete Laravel API versioning example is available in [`laravel-api-versioning.php`](https://github.com/dalehurley/codewithphp/blob/main/code/python-developers-love-php-laravel/chapter-06/laravel-api-versioning.php):

```php
<?php

declare(strict_types=1);

// routes/api.php

// URL-based versioning: /api/v1/users, /api/v2/users
Route::prefix('v1')->group(function () {
    Route::apiResource('users', App\Http\Controllers\Api\V1\UserController::class);
});

Route::prefix('v2')->group(function () {
    Route::apiResource('users', App\Http\Controllers\Api\V2\UserController::class);
});

// Header-based versioning: Accept: application/vnd.api+json;version=1
Route::middleware(['api.version'])->group(function () {
    Route::apiResource('users', App\Http\Controllers\Api\UserController::class);
});
```

2. **Version Middleware** (Laravel):

```php
<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiVersion
{
    public function handle(Request $request, Closure $next): mixed
    {
        $version = $request->header('Accept');
        
        // Parse version from header: application/vnd.api+json;version=1
        if (preg_match('/version=(\d+)/', $version, $matches)) {
            $request->route()->setParameter('version', $matches[1]);
        }

        return $next($request);
    }
}
```

3. **API Documentation** (Laravel with Swagger/OpenAPI):

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Info(
 *     title="User API",
 *     version="1.0.0"
 * )
 */
class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/users/{id}",
     *     summary="Get user by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User found"
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        // Controller logic
    }
}
```

### Expected Result

You can see the patterns:

- **URL Versioning**: `/api/v1/users` vs `/api/v2/users` — Clear and explicit
- **Header Versioning**: `Accept: application/vnd.api+json;version=1` — Keeps URLs clean
- **Documentation**: Swagger/OpenAPI annotations provide interactive API documentation

### Why It Works

API versioning allows you to evolve your API without breaking existing clients:

- **URL Versioning**: Most common approach, easy to understand and implement
- **Header Versioning**: Keeps URLs clean but requires client configuration
- **Documentation**: Swagger/OpenAPI provides interactive documentation that stays in sync with code

Laravel's route prefixing makes URL versioning straightforward. For header-based versioning, middleware can parse version headers and route to appropriate controllers.

::: tip Versioning Strategy
Choose URL versioning for public APIs (easier for clients) and header versioning for internal APIs (cleaner URLs). Most Laravel APIs use URL versioning: `/api/v1/`, `/api/v2/`.
:::

### Comparison Table

| Feature | Flask/Django | Laravel |
|---------|--------------|---------|
| **URL Versioning** | Manual route prefixes | `Route::prefix('v1')` |
| **Header Versioning** | Custom middleware | Custom middleware |
| **Documentation** | Swagger/Flask-RESTX | Swagger/L5-Swagger |
| **Version Detection** | Manual parsing | Middleware or route parameter |

### Troubleshooting

- **"Version not detected"** — Check that your middleware runs before routing. Middleware order matters in `bootstrap/app.php` or `app/Http/Kernel.php`.
- **"Route not found"** — Ensure versioned routes are defined correctly. Use `php artisan route:list` to see all registered routes.
- **"Documentation not generating"** — Install and configure Swagger package: `composer require darkaonline/l5-swagger`. Run `php artisan l5-swagger:generate` after adding annotations.

## Exercises

Practice building REST APIs with these exercises:

### Exercise 1: Build a Simple REST API

**Goal**: Create a complete REST API endpoint with validation and resources

Create a `PostController` with the following endpoints:

- `GET /api/posts` — List all posts
- `GET /api/posts/{id}` — Get a single post
- `POST /api/posts` — Create a new post (with validation)
- `PUT /api/posts/{id}` — Update a post
- `DELETE /api/posts/{id}` — Delete a post

**Requirements:**

- Use `Route::apiResource()` for routes
- Create a `PostResource` to format responses
- Create a `StorePostRequest` Form Request for validation
- Validate: `title` (required, min 3 chars), `content` (required), `author_id` (required, exists in users table)
- Return appropriate HTTP status codes (200, 201, 204, 404)
- Add pagination to the index endpoint (15 items per page)

**Validation**: Test your API:

```bash
# List posts
curl http://localhost:8000/api/posts

# Create post
curl -X POST http://localhost:8000/api/posts \
  -H "Content-Type: application/json" \
  -d '{"title":"My Post","content":"Post content","author_id":1}'

# Get single post
curl http://localhost:8000/api/posts/1

# Update post
curl -X PUT http://localhost:8000/api/posts/1 \
  -H "Content-Type: application/json" \
  -d '{"title":"Updated Post","content":"New content","author_id":1}'

# Delete post
curl -X DELETE http://localhost:8000/api/posts/1
```

Expected output: All endpoints return JSON with appropriate status codes.

### Exercise 2: Add API Authentication

**Goal**: Protect API endpoints with Sanctum authentication

**Requirements:**

- Install Laravel Sanctum: `composer require laravel/sanctum`
- Create login endpoint: `POST /api/login` (returns token)
- Create logout endpoint: `POST /api/logout` (requires authentication)
- Protect all post endpoints with `auth:sanctum` middleware
- Return user information in protected endpoints

**Validation**: Test authentication:

```bash
# Login
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password"}'

# Use token in protected endpoint
curl http://localhost:8000/api/posts \
  -H "Authorization: Bearer {your-token}"

# Logout
curl -X POST http://localhost:8000/api/logout \
  -H "Authorization: Bearer {your-token}"
```

Expected output: Unauthenticated requests return 401, authenticated requests return data.

### Exercise 3: External API Integration

**Goal**: Integrate with an external API (e.g., JSONPlaceholder)

**Requirements:**

- Create a service class `ExternalPostService`
- Fetch posts from `https://jsonplaceholder.typicode.com/posts`
- Cache the response for 1 hour
- Handle errors gracefully (timeout, connection errors)
- Return formatted data using a Resource class

**Validation**: Test integration:

```php
// In tinker or controller
$service = new ExternalPostService();
$posts = $service->fetchPosts();
// Should return array of posts from external API
```

Expected output: External API data is fetched, cached, and formatted correctly.

## Wrap-up

Congratulations! You've completed a comprehensive guide to building REST APIs in Laravel. Let's review what you've accomplished:

- **API Routes & Controllers**: You understand how Laravel API routes compare to Flask-RESTful resources and Django REST viewsets, including `Route::apiResource()` for automatic RESTful route generation.

- **API Resources**: You can format API responses using Laravel API Resources, comparing them to Django REST serializers and Flask response formatting functions.

- **Request Validation**: You've mastered Laravel Form Requests for validation, comparing them to Django forms and Flask validation schemas.

- **Authentication**: You can implement API authentication using Laravel Sanctum, comparing token-based authentication to Flask-JWT and Django REST tokens.

- **External Integrations**: You understand how to make HTTP requests using Laravel's HTTP Client, comparing it to Python's `requests` library.

- **API Versioning**: You know how to version APIs using URL prefixes or headers, and understand documentation approaches.

- **Query Parameter Filtering & Sorting**: You can handle dynamic filtering and sorting via query parameters, comparing Flask's `request.args` and Django REST's `query_params` to Laravel's `$request->query()`.

- **File Uploads**: You understand how to handle file uploads in API endpoints using Laravel's Storage facade, comparing to Flask and Django REST file handling.

- **CORS Configuration**: You can configure CORS for APIs consumed by frontend applications, comparing Flask-CORS and Django CORS headers to Laravel's CORS middleware.

- **Pagination & Error Handling**: You understand how to paginate API responses and handle errors consistently across your API.

### Key Takeaways

1. **REST Patterns Are Universal**: The concepts of routes, controllers, validation, and authentication work the same way across Flask, Django, and Laravel. Only syntax differs.

2. **Laravel's Developer Experience**: Laravel's `Route::apiResource()`, Form Requests, API Resources, and Sanctum provide a clean, intuitive API that feels familiar to Python developers.

3. **Security First**: Always validate requests, authenticate API endpoints, and handle errors gracefully. Laravel's Form Requests and Sanctum make this straightforward.

4. **Code Organization**: Separate concerns: routes in `routes/api.php`, controllers in `App\Http\Controllers\Api`, resources in `App\Http\Resources`, and requests in `App\Http\Requests`.

5. **External APIs**: Laravel's HTTP Client provides a fluent interface for external integrations, with built-in retry logic, timeouts, and error handling.

6. **Query Parameters**: Use `$request->query()` to access query parameters. Always validate query parameters to prevent SQL injection and ensure data integrity.

7. **File Storage**: Laravel's Storage facade abstracts file storage, allowing seamless switching between local and cloud storage (S3, etc.) without changing your code.

8. **CORS Configuration**: Configure CORS in `config/cors.php` for centralized management. Always specify exact origins in production, never use `['*']` in production.

### What's Next?

In [Chapter 07](/series/python-developers-love-php-laravel/chapters/07-testing-deployment-devops-best-practices), you'll learn about testing, deployment, and DevOps practices in Laravel, comparing pytest to PHPUnit, CI/CD workflows, and deployment strategies. You'll bring together everything you've learned to build and deploy production-ready applications.

<ChapterCheckbox seriesId="python-developers-love-php-laravel" chapterId="06-building-rest-apis-integrations-python-to-laravel" />

## Further Reading

- [Laravel API Documentation](https://laravel.com/docs/routing#api-routes) — Complete guide to API routing
- [Laravel API Resources](https://laravel.com/docs/eloquent-resources) — API response formatting
- [Laravel Form Requests](https://laravel.com/docs/validation#form-request-validation) — Request validation
- [Laravel Sanctum](https://laravel.com/docs/sanctum) — API authentication
- [Laravel HTTP Client](https://laravel.com/docs/http-client) — External API integrations
- [REST API Best Practices](https://restfulapi.net/) — REST API design guidelines
- [Flask-RESTful Documentation](https://flask-restful.readthedocs.io/) — Reference for comparison
- [Django REST Framework Documentation](https://www.django-rest-framework.org/) — Reference for comparison

