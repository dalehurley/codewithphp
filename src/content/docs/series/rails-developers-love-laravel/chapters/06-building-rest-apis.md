---
title: "06: Building REST APIs: From Rails to Laravel"
description: "Build robust REST APIs in Laravel using your Rails API knowledge as a foundation"
sidebar:
  label: "06: Building REST APIs: From Rails to Laravel"
  order: 6
  badge:
    text: Intermediate
    variant: caution
---


![Building REST APIs](/images/rails-developers-love-laravel/chapter-06-building-rest-apis-hero-full.webp)

# 06: Building REST APIs: From Rails to Laravel <span class="difficulty-badge difficulty-intermediate">Intermediate</span>

## Overview

If you've built REST APIs in Rails, you'll find Laravel's API capabilities remarkably similar yet refreshingly elegant. Laravel provides excellent tools for API development, from resource transformations to authentication, all with a Rails-like developer experience.

This chapter explores how to translate your Rails API knowledge to Laravel, covering everything from basic JSON responses to full-featured API authentication with Laravel Sanctum.

## Prerequisites

Before starting this chapter, you should have:

- Completion of [Chapter 05: Working with Data: Eloquent ORM & Database Workflow](/series/rails-developers-love-laravel/chapters/05-eloquent-orm) or equivalent understanding
- Familiarity with building REST APIs in Rails (routes, controllers, serializers, authentication)
- Basic understanding of HTTP methods (GET, POST, PUT, DELETE) and status codes
- Laravel project set up (or ability to follow along with examples)
- **Estimated Time**: ~75-90 minutes

**Verify your setup:**

```bash
# Check if you have a Laravel project
php artisan --version

# Or create a new Laravel project if needed
composer create-project laravel/laravel my-api-project
```

## What You'll Build

By the end of this chapter, you will have:

- A complete understanding of Laravel API routing and how it compares to Rails
- Knowledge of Laravel Form Requests for API validation (equivalent to Rails strong parameters)
- Ability to transform API responses using Laravel Resources (equivalent to ActiveModel Serializers)
- A working authentication system using Laravel Sanctum (equivalent to Devise + JWT)
- Understanding of API error handling, rate limiting, and CORS configuration
- Skills to test APIs using Laravel's testing tools
- A complete blog API example with authentication, authorization, and resource transformations

You'll be able to build production-ready REST APIs in Laravel using the same patterns you know from Rails.

## Quick Start

Here's a complete API endpoint in 5 minutes:

```bash
# Create a new Laravel project (if needed)
composer create-project laravel/laravel quick-api
cd quick-api

# Create a model and migration
php artisan make:model Post -m

# Add routes
```

```php
<?php
// routes/api.php
use App\Http\Controllers\Api\PostController;

Route::apiResource('posts', PostController::class);
```

```php
<?php
# filename: app/Http/Controllers/Api/PostController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Post::all());
    }

    public function store(Request $request): JsonResponse
    {
        $post = Post::create($request->validate([
            'title' => 'required|string',
            'body' => 'required|string',
        ]));

        return response()->json($post, 201);
    }
}
```

```bash
# Test it
php artisan serve
curl http://localhost:8000/api/posts
```

That's it! You have a working API. Now let's dive deeper.

## What You'll Learn

- API routing: Rails routes vs Laravel routes
- JSON responses and error handling
- Resource transformations (Serializers vs Resources)
- API authentication (Devise vs Sanctum)
- API versioning strategies
- Rate limiting and throttling
- Testing APIs
- CORS configuration
- File uploads in APIs
- Consuming external APIs
- Webhook handling
- Real-world API patterns

## 📦 Code Samples

Complete API examples and documentation:

- **[Blog API Documentation](https://github.com/dalehurley/codewithphp/blob/main/code/rails-developers-love-laravel/chapter-06/complete-blog-api.md)** — Full API reference with endpoints, cURL examples, and error handling

Production-ready API with all patterns:

- **[TaskMaster API](https://github.com/dalehurley/codewithphp/tree/main/code/rails-developers-love-laravel/chapter-10)** — Complete REST API with authentication, authorization, resources, validation, and 16 test cases

Access code samples:

```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/rails-developers-love-laravel/chapter-06
```

## API Routing: RESTful Resources

### Rails API Routes

```ruby
# config/routes.rb
Rails.application.routes.draw do
  namespace :api do
    namespace :v1 do
      resources :posts do
        resources :comments
      end
      resources :users, only: [:index, :show]
    end
  end
end

# Generates routes like:
# GET    /api/v1/posts
# POST   /api/v1/posts
# GET    /api/v1/posts/:id
# PATCH  /api/v1/posts/:id
# DELETE /api/v1/posts/:id
```

### Laravel API Routes

```php
<?php
// routes/api.php (automatically prefixed with /api)
use App\Http\Controllers\Api\V1\PostController;
use App\Http\Controllers\Api\V1\CommentController;

Route::prefix('v1')->group(function () {
    // Resource routes
    Route::apiResource('posts', PostController::class);
    Route::apiResource('posts.comments', CommentController::class);
    Route::apiResource('users', UserController::class)
        ->only(['index', 'show']);
});

// Generates routes like:
// GET    /api/v1/posts
// POST   /api/v1/posts
// GET    /api/v1/posts/{post}
// PUT    /api/v1/posts/{post}
// DELETE /api/v1/posts/{post}
```

**Key Differences:**
- Laravel's `routes/api.php` is automatically prefixed with `/api`
- `apiResource` excludes `create` and `edit` routes (no forms needed)
- Route model binding is automatic
- Cleaner nested resource syntax

### Named API Routes

**Rails:**
```ruby
# config/routes.rb
namespace :api do
  namespace :v1 do
    get 'posts/trending', to: 'posts#trending', as: :trending_posts
    post 'posts/:id/publish', to: 'posts#publish', as: :publish_post
  end
end

# Usage in controller
redirect_to api_v1_trending_posts_path
```

**Laravel:**
```php
<?php
// routes/api.php
Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::get('posts/trending', [PostController::class, 'trending'])
        ->name('posts.trending');
    Route::post('posts/{post}/publish', [PostController::class, 'publish'])
        ->name('posts.publish');
});

// Usage in controller
return redirect()->route('api.v1.posts.trending');
```

Nearly identical concepts with slightly different syntax.

## Controllers: API Responses

### Rails API Controller

```ruby
# app/controllers/api/v1/posts_controller.rb
module Api
  module V1
    class PostsController < ApplicationController
      before_action :set_post, only: [:show, :update, :destroy]

      def index
        @posts = Post.all
        render json: @posts
      end

      def show
        render json: @post
      end

      def create
        @post = Post.new(post_params)

        if @post.save
          render json: @post, status: :created
        else
          render json: { errors: @post.errors }, status: :unprocessable_entity
        end
      end

      def update
        if @post.update(post_params)
          render json: @post
        else
          render json: { errors: @post.errors }, status: :unprocessable_entity
        end
      end

      def destroy
        @post.destroy
        head :no_content
      end

      private

      def set_post
        @post = Post.find(params[:id])
      end

      def post_params
        params.require(:post).permit(:title, :body, :published)
      end
    end
  end
end
```

### Laravel API Controller

```php
<?php
# filename: app/Http/Controllers/Api/V1/PostController.php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use Illuminate\Http\JsonResponse;

class PostController extends Controller
{
    public function index(): JsonResponse
    {
        $posts = Post::all();
        return response()->json($posts);
    }

    public function show(Post $post): JsonResponse
    {
        // Route model binding automatically loads $post
        return response()->json($post);
    }

    public function store(StorePostRequest $request): JsonResponse
    {
        $post = Post::create($request->validated());

        return response()->json($post, 201);
    }

    public function update(UpdatePostRequest $request, Post $post): JsonResponse
    {
        $post->update($request->validated());

        return response()->json($post);
    }

    public function destroy(Post $post): JsonResponse
    {
        $post->delete();

        return response()->json(null, 204);
    }
}
```

**Key Differences:**
1. **Route Model Binding**: Laravel automatically finds the model
2. **Form Requests**: Validation separated into dedicated classes
3. **Type Hints**: Return types make intent explicit
4. **No `set_post` needed**: Route model binding handles it

## Request Validation

### Rails Strong Parameters

```ruby
# app/controllers/api/v1/posts_controller.rb
def post_params
  params.require(:post).permit(:title, :body, :published)
end

# With custom validation
def create
  @post = Post.new(post_params)

  if @post.save
    render json: @post, status: :created
  else
    render json: { errors: @post.errors }, status: :unprocessable_entity
  end
end
```

### Laravel Form Requests

```php
<?php
# filename: app/Http/Requests/StorePostRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // or check permissions
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'published' => 'boolean',
            'tags' => 'array',
            'tags.*' => 'string|exists:tags,name',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'A post title is required',
            'body.required' => 'Post body cannot be empty',
        ];
    }
}

// Controller automatically validates
public function store(StorePostRequest $request): JsonResponse
{
    // If we reach here, validation passed
    $post = Post::create($request->validated());
    return response()->json($post, 201);
}
```

**Advantages of Laravel Form Requests:**
- ✅ Validation logic separated from controller
- ✅ Reusable across controllers
- ✅ Automatic error responses
- ✅ Authorization built-in
- ✅ Custom error messages
- ✅ Type-safe

### Validation Error Response

**Rails Error Response:**
```json
{
  "errors": {
    "title": ["can't be blank"],
    "body": ["can't be blank"]
  }
}
```

**Laravel Error Response:**
```json
{
  "message": "The title field is required. (and 1 more error)",
  "errors": {
    "title": [
      "The title field is required."
    ],
    "body": [
      "The body field is required."
    ]
  }
}
```

Both provide structured error responses automatically.

::: tip Pro Tip: Advanced Validation Rules
Laravel has powerful built-in validation rules:

```php
<?php
public function rules(): array
{
    return [
        // Conditional validation
        'discount' => 'required_if:promo_code,null',

        // Unique except current record (for updates)
        'email' => 'required|email|unique:users,email,' . $this->user->id,

        // Custom regex
        'username' => ['required', 'regex:/^[a-zA-Z0-9_]+$/'],

        // File validation
        'avatar' => 'required|image|max:2048|dimensions:min_width=100,min_height=100',

        // Date validation
        'start_date' => 'required|date|after:today',
        'end_date' => 'required|date|after:start_date',

        // Array validation
        'items' => 'required|array|min:1|max:10',
        'items.*.quantity' => 'required|integer|min:1',
        'items.*.price' => 'required|numeric|min:0',
    ];
}
```

**Custom validation messages per field:**
```php
<?php
public function messages(): array
{
    return [
        'items.*.quantity.required' => 'Each item must have a quantity',
        'items.*.quantity.min' => 'Item quantity must be at least 1',
    ];
}
```
:::

::: warning Security: Always Validate, Never Trust
**Common API security mistakes:**

```php
<?php
// ❌ DANGEROUS - Trusts all input
public function store(Request $request)
{
    User::create($request->all()); // Can set ANY field including admin flags!
}

// ✅ SAFE - Explicit validation
public function store(StoreUserRequest $request)
{
    User::create($request->validated()); // Only validated fields
}
```

**Mass assignment protection:**
```php
<?php
class User extends Model
{
    // Whitelist approach (recommended)
    protected $fillable = ['name', 'email', 'password'];

    // Or blacklist approach
    protected $guarded = ['id', 'is_admin', 'created_at', 'updated_at'];
}
```

**Always validate:**
- File uploads (type, size, dimensions)
- Foreign keys (use `exists:table,column`)
- Email addresses (use `email:rfc,dns` for strict validation)
- URLs (use `url` or `active_url`)
- Enum values (use `Rule::in()`)
:::

## Resource Transformations

### Rails: ActiveModel Serializers

```ruby
# app/serializers/post_serializer.rb
class PostSerializer < ActiveModel::Serializer
  attributes :id, :title, :body, :published_at, :created_at

  belongs_to :user
  has_many :comments

  def published_at
    object.published_at&.iso8601
  end
end

# app/serializers/user_serializer.rb
class UserSerializer < ActiveModel::Serializer
  attributes :id, :name, :email
end

# Controller
render json: @posts, each_serializer: PostSerializer
```

### Laravel: API Resources

```php
<?php
# filename: app/Http/Resources/PostResource.php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,
            'published_at' => $this->published_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),

            // Relationships
            'user' => new UserResource($this->whenLoaded('user')),
            'comments' => CommentResource::collection($this->whenLoaded('comments')),

            // Conditional attributes
            'draft_notes' => $this->when(!$this->published, $this->draft_notes),

            // Computed values
            'excerpt' => $this->excerpt(),
            'reading_time' => $this->readingTime(),
        ];
    }
}

// app/Http/Resources/UserResource.php
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
        ];
    }
}

// Controller
return PostResource::collection($posts);
return new PostResource($post);
```

**Laravel Resources Advantages:**
- ✅ More powerful conditional logic
- ✅ Nested resources with `whenLoaded`
- ✅ Pagination support built-in
- ✅ Type-safe with PHPStan
- ✅ Request context available

### Resource Collections

**Rails:**
```ruby
# Simple collection
render json: @posts, each_serializer: PostSerializer

# Paginated
render json: @posts.page(params[:page]), each_serializer: PostSerializer
```

**Laravel:**
```php
<?php
// Simple collection
return PostResource::collection($posts);

// Paginated (automatic links and meta)
return PostResource::collection(
    Post::paginate(15)
);

// Response structure:
{
    "data": [...],
    "links": {
        "first": "http://example.com/api/posts?page=1",
        "last": "http://example.com/api/posts?page=3",
        "prev": null,
        "next": "http://example.com/api/posts?page=2"
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "to": 15,
        "total": 50,
        "per_page": 15,
        "last_page": 4
    }
}
```

Laravel's pagination includes links and metadata automatically.

### Conditional Attributes

**Rails:**
```ruby
class PostSerializer < ActiveModel::Serializer
  attributes :id, :title, :body, :draft_notes

  def draft_notes
    object.draft_notes unless object.published?
  end

  def attributes(*args)
    hash = super
    hash.delete(:draft_notes) if object.published?
    hash
  end
end
```

**Laravel:**
```php
<?php
class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,

            // Only include if not published
            'draft_notes' => $this->when(!$this->published, $this->draft_notes),

            // Only for admins
            'internal_notes' => $this->when(
                $request->user()?->isAdmin(),
                $this->internal_notes
            ),

            // Merge additional data conditionally
            $this->mergeWhen($request->user()?->isAdmin(), [
                'created_by' => $this->creator_id,
                'ip_address' => $this->created_from_ip,
            ]),
        ];
    }
}
```

Laravel's `when()` and `mergeWhen()` make conditional attributes cleaner.

## API Authentication

### Rails: Devise + JWT

```ruby
# Gemfile
gem 'devise'
gem 'devise-jwt'

# app/controllers/api/v1/sessions_controller.rb
class Api::V1::SessionsController < Devise::SessionsController
  respond_to :json

  private

  def respond_with(resource, _opts = {})
    render json: {
      user: UserSerializer.new(resource),
      token: request.env['warden-jwt_auth.token']
    }
  end
end

# app/controllers/api/v1/base_controller.rb
class Api::V1::BaseController < ApplicationController
  before_action :authenticate_user!
end

# Usage
class Api::V1::PostsController < Api::V1::BaseController
  def index
    @posts = current_user.posts
    render json: @posts
  end
end
```

### Laravel: Sanctum (Token-Based)

```php
<?php
// Install: composer require laravel/sanctum

# filename: app/Http/Controllers/Api/AuthController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}

// routes/api.php
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    Route::apiResource('posts', PostController::class);
});

// Protected controller
class PostController extends Controller
{
    public function index(Request $request)
    {
        $posts = $request->user()->posts;
        return PostResource::collection($posts);
    }
}
```

**Sanctum Features:**
- ✅ Simple token-based authentication
- ✅ Token abilities (scopes)
- ✅ Multi-device support
- ✅ SPA authentication
- ✅ Mobile app support
- ✅ Built into Laravel

### Token Abilities (Scopes)

**Laravel Sanctum Abilities:**
```php
<?php
// Create token with specific abilities
$token = $user->createToken('mobile-app', [
    'post:read',
    'post:create',
    'comment:read',
])->plainTextToken;

// Check abilities in controller
public function store(Request $request)
{
    if (!$request->user()->tokenCan('post:create')) {
        abort(403, 'Insufficient permissions');
    }

    // Create post...
}

// Or use middleware
Route::middleware(['auth:sanctum', 'ability:post:create'])
    ->post('/posts', [PostController::class, 'store']);
```

### Multiple Tokens Per User

```php
<?php
// Create multiple tokens for different devices
$webToken = $user->createToken('web-app')->plainTextToken;
$mobileToken = $user->createToken('mobile-app')->plainTextToken;
$apiToken = $user->createToken('third-party-api')->plainTextToken;

// Revoke specific token
$request->user()->currentAccessToken()->delete();

// Revoke all tokens
$user->tokens()->delete();

// List user's tokens
$tokens = $user->tokens;
```

Rails requires more setup for this functionality.

::: tip Pro Tip: API Authentication Best Practices
**Token Expiration:**
```php
<?php
// Set token expiration in config/sanctum.php
'expiration' => 60, // minutes (null = never expires)

// Or per-token:
$token = $user->createToken('mobile-app', ['*'], now()->addDays(30));
```

**Rate Limiting Per User:**
```php
<?php
// Different limits based on user type
RateLimiter::for('api', function (Request $request) {
    return $request->user()?->isPremium()
        ? Limit::perMinute(1000)
        : Limit::perMinute(60);
});
```

**Token Naming Convention:**
```php
<?php
// Use descriptive names to track where tokens are used
$user->createToken('iPhone 14 Pro')->plainTextToken;
$user->createToken('Web Dashboard')->plainTextToken;
$user->createToken('Android App v2.1')->plainTextToken;

// Later, revoke specific device
$user->tokens()->where('name', 'iPhone 14 Pro')->delete();
```
:::

::: warning Security: API Authentication Pitfalls
**Common security mistakes:**

```php
<?php
// ❌ DON'T: Never send passwords in GET requests
Route::get('/login', function (Request $request) {
    // Passwords in URL = logged in server logs!
});

// ✅ DO: Always use POST for authentication
Route::post('/login', [AuthController::class, 'login']);

// ❌ DON'T: Return raw tokens in error messages
catch (Exception $e) {
    return response()->json(['error' => $e->getMessage()], 500);
}

// ✅ DO: Sanitize error messages
catch (Exception $e) {
    Log::error('Auth error', ['message' => $e->getMessage()]);
    return response()->json(['error' => 'Authentication failed'], 401);
}

// ❌ DON'T: Use the same secret for all environments
// .env
APP_KEY=same_key_everywhere

// ✅ DO: Different keys per environment
// production .env: APP_KEY=random_production_key
// staging .env: APP_KEY=random_staging_key
```

**Rate limiting is CRITICAL:**
```php
<?php
// Prevent brute force attacks
Route::middleware('throttle:5,1')->post('/login', [AuthController::class, 'login']);
// Only 5 login attempts per minute
```

**HTTPS in production:**
```php
<?php
// Force HTTPS in production
// app/Providers/AppServiceProvider.php
public function boot()
{
    if (app()->environment('production')) {
        URL::forceScheme('https');
    }
}
```
:::

## Error Handling

### Rails Error Responses

```ruby
# app/controllers/api/v1/base_controller.rb
class Api::V1::BaseController < ApplicationController
  rescue_from ActiveRecord::RecordNotFound, with: :not_found
  rescue_from ActiveRecord::RecordInvalid, with: :unprocessable_entity

  private

  def not_found(exception)
    render json: { error: exception.message }, status: :not_found
  end

  def unprocessable_entity(exception)
    render json: { errors: exception.record.errors }, status: :unprocessable_entity
  end
end
```

### Laravel Error Handling

```php
<?php
// app/Exceptions/Handler.php
namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    public function render($request, Throwable $exception)
    {
        if ($request->expectsJson()) {
            // Model not found
            if ($exception instanceof ModelNotFoundException) {
                return response()->json([
                    'message' => 'Resource not found',
                ], 404);
            }

            // Validation error
            if ($exception instanceof ValidationException) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $exception->errors(),
                ], 422);
            }

            // Generic error
            return response()->json([
                'message' => 'An error occurred',
                'error' => config('app.debug') ? $exception->getMessage() : null,
            ], 500);
        }

        return parent::render($request, $exception);
    }
}
```

Laravel's exception handler automatically formats errors for JSON requests.

### Custom API Exceptions

**Laravel:**
```php
<?php
// app/Exceptions/ApiException.php
namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class ApiException extends Exception
{
    public function __construct(
        public string $message,
        public int $statusCode = 400,
        public array $errors = []
    ) {
        parent::__construct($message);
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'message' => $this->message,
            'errors' => $this->errors,
        ], $this->statusCode);
    }
}

// Usage in controller
throw new ApiException('Post cannot be deleted', 403);

throw new ApiException(
    'Validation failed',
    422,
    ['title' => ['Title is required']]
);
```

## Rate Limiting

### Rails Rate Limiting

```ruby
# Gemfile
gem 'rack-attack'

# config/initializers/rack_attack.rb
Rack::Attack.throttle('api/ip', limit: 60, period: 1.minute) do |req|
  req.ip if req.path.start_with?('/api/')
end

Rack::Attack.throttle('api/user', limit: 100, period: 1.hour) do |req|
  req.env['warden'].user.id if req.env['warden'].user
end
```

### Laravel Rate Limiting

```php
<?php
// app/Providers/RouteServiceProvider.php
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});

// Custom rate limiter
RateLimiter::for('uploads', function (Request $request) {
    return $request->user()
        ? Limit::perMinute(100)->by($request->user()->id)
        : Limit::perMinute(10)->by($request->ip());
});

// routes/api.php
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::apiResource('posts', PostController::class);
});

Route::middleware(['auth:sanctum', 'throttle:uploads'])
    ->post('/upload', [UploadController::class, 'store']);
```

**Rate Limit Response:**
```json
{
    "message": "Too Many Attempts."
}
```

Headers included:
- `X-RateLimit-Limit: 60`
- `X-RateLimit-Remaining: 59`
- `Retry-After: 60`

## API Versioning

### Rails Versioning

```ruby
# config/routes.rb
namespace :api do
  namespace :v1 do
    resources :posts
  end

  namespace :v2 do
    resources :posts
  end
end

# app/controllers/api/v1/posts_controller.rb
module Api
  module V1
    class PostsController < ApplicationController
      # V1 implementation
    end
  end
end

# app/controllers/api/v2/posts_controller.rb
module Api
  module V2
    class PostsController < ApplicationController
      # V2 implementation with breaking changes
    end
  end
end
```

### Laravel Versioning

```php
<?php
// routes/api.php

// Version 1
Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::apiResource('posts', Api\V1\PostController::class);
});

// Version 2
Route::prefix('v2')->name('api.v2.')->group(function () {
    Route::apiResource('posts', Api\V2\PostController::class);
});

// Or use route files
// routes/api/v1.php
// routes/api/v2.php

// app/Http/Controllers/Api/V1/PostController.php
namespace App\Http\Controllers\Api\V1;

class PostController extends Controller
{
    // V1 implementation
}

// app/Http/Controllers/Api/V2/PostController.php
namespace App\Http\Controllers\Api\V2;

class PostController extends Controller
{
    // V2 implementation
}
```

Both frameworks handle versioning through namespacing.

## CORS Configuration

### Rails CORS

```ruby
# Gemfile
gem 'rack-cors'

# config/initializers/cors.rb
Rails.application.config.middleware.insert_before 0, Rack::Cors do
  allow do
    origins 'example.com', 'localhost:3000'
    resource '/api/*',
      headers: :any,
      methods: [:get, :post, :put, :patch, :delete, :options, :head]
  end
end
```

### Laravel CORS

```php
<?php
// config/cors.php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['https://example.com', 'http://localhost:3000'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];
```

Laravel includes CORS configuration out of the box.

## Testing APIs

### Rails API Tests

```ruby
# spec/requests/api/v1/posts_spec.rb
require 'rails_helper'

RSpec.describe 'Api::V1::Posts', type: :request do
  let(:user) { create(:user) }
  let(:headers) { { 'Authorization' => "Bearer #{user.token}" } }

  describe 'GET /api/v1/posts' do
    it 'returns all posts' do
      create_list(:post, 3)

      get '/api/v1/posts', headers: headers

      expect(response).to have_http_status(:ok)
      expect(JSON.parse(response.body).size).to eq(3)
    end
  end

  describe 'POST /api/v1/posts' do
    it 'creates a post' do
      post_params = { post: { title: 'Test', body: 'Content' } }

      post '/api/v1/posts', params: post_params, headers: headers

      expect(response).to have_http_status(:created)
      expect(JSON.parse(response.body)['title']).to eq('Test')
    end

    it 'returns validation errors' do
      post_params = { post: { title: '' } }

      post '/api/v1/posts', params: post_params, headers: headers

      expect(response).to have_http_status(:unprocessable_entity)
      expect(JSON.parse(response.body)['errors']).to be_present
    end
  end
end
```

### Laravel API Tests

```php
<?php
// tests/Feature/Api/PostTest.php
namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_posts(): void
    {
        $user = User::factory()->create();
        Post::factory()->count(3)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/posts');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_can_create_post(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/posts', [
                'title' => 'Test Post',
                'body' => 'Test content',
            ]);

        $response->assertCreated()
            ->assertJson([
                'title' => 'Test Post',
            ]);

        $this->assertDatabaseHas('posts', [
            'title' => 'Test Post',
        ]);
    }

    public function test_validation_fails_for_invalid_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/posts', [
                'title' => '',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'body']);
    }

    public function test_unauthorized_without_token(): void
    {
        $response = $this->getJson('/api/v1/posts');

        $response->assertUnauthorized();
    }
}
```

**Laravel Testing Advantages:**
- ✅ Fluent JSON assertions
- ✅ `actingAs()` for easy authentication
- ✅ Database assertions built-in
- ✅ JSON structure testing
- ✅ Type-safe

## Complete API Example

Let's build a complete blog API with authentication:

### Laravel Implementation

```php
<?php
// routes/api.php
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('posts', PostController::class);
    Route::post('posts/{post}/publish', [PostController::class, 'publish']);
    Route::apiResource('posts.comments', CommentController::class);
});

// app/Http/Controllers/Api/PostController.php
class PostController extends Controller
{
    public function index(Request $request)
    {
        $posts = Post::with(['user', 'tags'])
            ->when($request->published, fn($q) => $q->where('published', true))
            ->latest()
            ->paginate(15);

        return PostResource::collection($posts);
    }

    public function store(StorePostRequest $request)
    {
        $post = $request->user()->posts()->create($request->validated());

        return new PostResource($post);
    }

    public function show(Post $post)
    {
        $post->load(['user', 'comments.user', 'tags']);

        return new PostResource($post);
    }

    public function update(UpdatePostRequest $request, Post $post)
    {
        $this->authorize('update', $post);

        $post->update($request->validated());

        return new PostResource($post);
    }

    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);

        $post->delete();

        return response()->json(null, 204);
    }

    public function publish(Post $post)
    {
        $this->authorize('publish', $post);

        $post->update(['published' => true, 'published_at' => now()]);

        return new PostResource($post);
    }
}

// app/Http/Resources/PostResource.php
class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'body' => $this->body,
            'excerpt' => $this->excerpt,
            'published' => $this->published,
            'published_at' => $this->published_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),

            'user' => new UserResource($this->whenLoaded('user')),
            'comments' => CommentResource::collection($this->whenLoaded('comments')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),

            'links' => [
                'self' => route('api.posts.show', $this->id),
            ],
        ];
    }
}

// app/Policies/PostPolicy.php
class PostPolicy
{
    public function update(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }

    public function delete(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }

    public function publish(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }
}
```

This complete example shows:
- Authentication with Sanctum
- Resource controllers
- Authorization policies
- Resource transformations
- Pagination
- Relationship loading
- HATEOAS links

## Filtering, Searching, and Sorting

### Rails Filtering

```ruby
# app/controllers/api/v1/posts_controller.rb
def index
  @posts = Post.all
  @posts = @posts.where(published: true) if params[:published].present?
  @posts = @posts.where('title LIKE ?', "%#{params[:search]}%") if params[:search].present?
  @posts = @posts.order(params[:sort] || :created_at)
  
  render json: @posts
end
```

### Laravel Filtering

```php
<?php
// app/Http/Controllers/Api/V1/PostController.php
public function index(Request $request): JsonResponse
{
    $query = Post::query();

    // Filter by published status
    if ($request->has('published')) {
        $query->where('published', $request->boolean('published'));
    }

    // Search in title and body
    if ($request->has('search')) {
        $search = $request->get('search');
        $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('body', 'like', "%{$search}%");
        });
    }

    // Sort
    $sortBy = $request->get('sort_by', 'created_at');
    $sortOrder = $request->get('sort_order', 'desc');
    $query->orderBy($sortBy, $sortOrder);

    // Paginate
    $posts = $query->paginate($request->get('per_page', 15));

    return PostResource::collection($posts);
}
```

### Advanced Filtering with Scopes

```php
<?php
// app/Models/Post.php
class Post extends Model
{
    public function scopePublished($query)
    {
        return $query->where('published', true);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
              ->orWhere('body', 'like', "%{$term}%");
        });
    }

    public function scopeByTag($query, string $tag)
    {
        return $query->whereHas('tags', function ($q) use ($tag) {
            $q->where('name', $tag);
        });
    }
}

// Controller
public function index(Request $request): JsonResponse
{
    $posts = Post::query()
        ->when($request->published, fn($q) => $q->published())
        ->when($request->search, fn($q, $search) => $q->search($search))
        ->when($request->tag, fn($q, $tag) => $q->byTag($tag))
        ->latest()
        ->paginate(15);

    return PostResource::collection($posts);
}
```

### Query Parameter Validation

```php
<?php
// app/Http/Requests/IndexPostRequest.php
class IndexPostRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'published' => 'sometimes|boolean',
            'search' => 'sometimes|string|max:255',
            'tag' => 'sometimes|string|exists:tags,name',
            'sort_by' => 'sometimes|string|in:created_at,title,updated_at',
            'sort_order' => 'sometimes|string|in:asc,desc',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ];
    }
}

// Controller
public function index(IndexPostRequest $request): JsonResponse
{
    // All query parameters are validated
    $posts = Post::query()
        ->when($request->validated('published'), fn($q) => $q->published())
        ->when($request->validated('search'), fn($q, $search) => $q->search($search))
        ->orderBy($request->validated('sort_by', 'created_at'), $request->validated('sort_order', 'desc'))
        ->paginate($request->validated('per_page', 15));

    return PostResource::collection($posts);
}
```

**Usage:**
```bash
# Filter published posts
GET /api/posts?published=true

# Search
GET /api/posts?search=laravel

# Sort
GET /api/posts?sort_by=title&sort_order=asc

# Combine filters
GET /api/posts?published=true&search=api&sort_by=created_at&per_page=20
```

## API Response Standardization

### Consistent Response Format

**Rails:**
```ruby
# Custom response format
def index
  @posts = Post.all
  render json: {
    success: true,
    data: @posts,
    meta: {
      count: @posts.count
    }
  }
end
```

**Laravel:**
```php
<?php
// app/Http/Controllers/Api/V1/PostController.php
public function index(): JsonResponse
{
    $posts = Post::paginate(15);

    return response()->json([
        'success' => true,
        'data' => PostResource::collection($posts),
        'meta' => [
            'current_page' => $posts->currentPage(),
            'total' => $posts->total(),
        ],
    ]);
}
```

### Response Trait for Consistency

```php
<?php
// app/Traits/ApiResponse.php
namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function success($data, string $message = null, int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function error(string $message, int $code = 400, $errors = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $code);
    }
}

// Controller
class PostController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $posts = Post::paginate(15);
        return $this->success(PostResource::collection($posts));
    }

    public function store(StorePostRequest $request): JsonResponse
    {
        $post = Post::create($request->validated());
        return $this->success(new PostResource($post), 'Post created successfully', 201);
    }

    public function destroy(Post $post): JsonResponse
    {
        $post->delete();
        return $this->success(null, 'Post deleted successfully', 204);
    }
}
```

**Response format:**
```json
{
  "success": true,
  "message": "Post created successfully",
  "data": {
    "id": 1,
    "title": "My Post",
    ...
  }
}
```

## File Uploads in APIs

### Rails File Uploads

```ruby
# app/controllers/api/v1/posts_controller.rb
def create
  @post = Post.new(post_params)
  @post.image.attach(params[:post][:image]) if params[:post][:image]
  
  if @post.save
    render json: @post, status: :created
  else
    render json: { errors: @post.errors }, status: :unprocessable_entity
  end
end

private

def post_params
  params.require(:post).permit(:title, :body, :image)
end
```

### Laravel File Uploads

```php
<?php
# filename: app/Http/Requests/StorePostRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|mimes:pdf,doc,docx|max:5120',
        ];
    }
}

# filename: app/Http/Controllers/Api/V1/PostController.php
public function store(StorePostRequest $request): JsonResponse
{
    $post = Post::create($request->only(['title', 'body']));
    
    // Store single image
    if ($request->hasFile('image')) {
        $path = $request->file('image')->store('posts', 'public');
        $post->update(['image_path' => $path]);
    }
    
    // Store multiple attachments
    if ($request->hasFile('attachments')) {
        foreach ($request->file('attachments') as $file) {
            $path = $file->store('attachments', 'public');
            $post->attachments()->create(['path' => $path]);
        }
    }
    
    return response()->json(new PostResource($post), 201);
}
```

### File Upload with Storage

```php
<?php
# filename: app/Http/Controllers/Api/V1/PostController.php
use Illuminate\Support\Facades\Storage;

public function store(StorePostRequest $request): JsonResponse
{
    $post = Post::create($request->only(['title', 'body']));
    
    if ($request->hasFile('image')) {
        // Store in 'public' disk (accessible via URL)
        $path = $request->file('image')->store('posts', 'public');
        
        // Or store in S3
        // $path = $request->file('image')->store('posts', 's3');
        
        $post->update(['image_path' => $path]);
    }
    
    return response()->json([
        'post' => new PostResource($post),
        'image_url' => $post->image_path ? Storage::url($post->image_path) : null,
    ], 201);
}
```

### Resource with File URLs

```php
<?php
# filename: app/Http/Resources/PostResource.php
public function toArray(Request $request): array
{
    return [
        'id' => $this->id,
        'title' => $this->title,
        'body' => $this->body,
        'image_url' => $this->image_path 
            ? Storage::url($this->image_path) 
            : null,
        'attachments' => $this->attachments->map(function ($attachment) {
            return [
                'id' => $attachment->id,
                'name' => $attachment->name,
                'url' => Storage::url($attachment->path),
                'size' => Storage::size($attachment->path),
            ];
        }),
    ];
}
```

**Testing file uploads:**
```php
<?php
// tests/Feature/Api/PostTest.php
public function test_can_upload_image(): void
{
    $user = User::factory()->create();
    Storage::fake('public');
    
    $file = UploadedFile::fake()->image('post.jpg', 800, 600);
    
    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/posts', [
            'title' => 'Test Post',
            'body' => 'Content',
            'image' => $file,
        ]);
    
    $response->assertCreated();
    Storage::disk('public')->assertExists('posts/' . $file->hashName());
}
```

## Consuming External APIs

### Rails HTTP Client

```ruby
# Gemfile
gem 'faraday'
gem 'httparty'

# app/services/weather_service.rb
class WeatherService
  def self.get_forecast(city)
    response = HTTParty.get(
      "https://api.weather.com/v1/forecast",
      query: { city: city, api_key: ENV['WEATHER_API_KEY'] },
      headers: { 'Accept' => 'application/json' }
    )
    
    JSON.parse(response.body)
  end
end
```

### Laravel HTTP Client

```php
<?php
# filename: app/Services/WeatherService.php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class WeatherService
{
    public function getForecast(string $city): array
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
        ])->get('https://api.weather.com/v1/forecast', [
            'city' => $city,
            'api_key' => config('services.weather.api_key'),
        ]);
        
        if ($response->successful()) {
            return $response->json();
        }
        
        throw new \Exception('Weather API request failed');
    }
}
```

### Advanced HTTP Client Usage

```php
<?php
# filename: app/Services/PaymentService.php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function processPayment(array $data): array
    {
        $response = Http::timeout(30)
            ->retry(3, 100) // Retry 3 times with 100ms delay
            ->withToken(config('services.payment.api_key'))
            ->withHeaders([
                'X-Request-ID' => uniqid(),
            ])
            ->post('https://api.payment.com/charge', $data);
        
        if ($response->successful()) {
            return $response->json();
        }
        
        // Log error
        Log::error('Payment API failed', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);
        
        throw new \Exception('Payment processing failed');
    }
    
    public function getPaymentStatus(string $paymentId): array
    {
        return Http::withToken(config('services.payment.api_key'))
            ->get("https://api.payment.com/payments/{$paymentId}")
            ->throw() // Throw exception on HTTP error
            ->json();
    }
}
```

### HTTP Client Macros

```php
<?php
# filename: app/Providers/AppServiceProvider.php
use Illuminate\Support\Facades\Http;

public function boot(): void
{
    // Create reusable HTTP client macro
    Http::macro('github', function () {
        return Http::withHeaders([
            'Accept' => 'application/vnd.github.v3+json',
            'Authorization' => 'token ' . config('services.github.token'),
        ])->baseUrl('https://api.github.com');
    });
}

// Usage
$response = Http::github()->get('/user/repos');
```

### Handling Webhooks

```php
<?php
# filename: app/Http/Controllers/Api/WebhookController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handle(Request $request, string $provider): JsonResponse
    {
        // Verify webhook signature
        if (!$this->verifySignature($request, $provider)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }
        
        // Process webhook based on provider
        match ($provider) {
            'stripe' => $this->handleStripeWebhook($request),
            'github' => $this->handleGithubWebhook($request),
            default => throw new \Exception('Unknown webhook provider'),
        };
        
        return response()->json(['status' => 'processed'], 200);
    }
    
    private function handleStripeWebhook(Request $request): void
    {
        $event = $request->input('type');
        
        match ($event) {
            'payment_intent.succeeded' => $this->processPayment($request->input('data.object')),
            'customer.subscription.deleted' => $this->cancelSubscription($request->input('data.object')),
            default => Log::info("Unhandled Stripe event: {$event}"),
        };
    }
    
    private function verifySignature(Request $request, string $provider): bool
    {
        return match ($provider) {
            'stripe' => $this->verifyStripeSignature($request),
            'github' => $this->verifyGithubSignature($request),
            default => false,
        };
    }
}
```

**Routes:**
```php
<?php
// routes/api.php
Route::post('/webhooks/{provider}', [WebhookController::class, 'handle'])
    ->middleware('verify.webhook'); // Custom middleware for signature verification
```

## Performance Optimization

### Eager Loading (N+1 Prevention)

**Rails:**
```ruby
# Bad - N+1 queries
@posts = Post.all
@posts.each do |post|
  puts post.user.name  # Separate query for each post
end

# Good - eager loading
@posts = Post.includes(:user, :comments)
```

**Laravel:**
```php
<?php
// Bad - N+1 queries
$posts = Post::all();
foreach ($posts as $post) {
    echo $post->user->name;  // Separate query for each post
}

// Good - eager loading
$posts = Post::with(['user', 'comments'])->get();

// Even better - count relationships without loading
$posts = Post::withCount('comments')->get();

// Conditional loading
$posts = Post::query()
    ->when($request->include_user, fn($q) => $q->with('user'))
    ->when($request->include_comments, fn($q) => $q->with('comments'))
    ->get();
```

### API Response Caching

**Laravel:**
```php
<?php
public function index(Request $request)
{
    $cacheKey = "posts.index.{$request->page}.{$request->per_page}";

    $posts = Cache::remember($cacheKey, now()->addMinutes(10), function () {
        return Post::with(['user', 'tags'])
            ->latest()
            ->paginate(15);
    });

    return PostResource::collection($posts);
}
```

## Key Takeaways

1. **Similar Patterns** - API development in Laravel feels like Rails
2. **Better Type Safety** - Form requests and type hints catch errors early
3. **Sanctum is Elegant** - Token authentication is simpler than JWT setup
4. **Resources > Serializers** - More powerful and flexible transformations
5. **Built-in Features** - Rate limiting, CORS, pagination included
6. **Testing is Clean** - Fluent assertions make API tests readable
7. **Performance Tools** - Eager loading and caching built-in

## Practice Exercises

### Exercise 1: Build a Task API

**Goal**: Create a complete task management API with authentication and resource transformations.

Create a task management API with:

- User authentication using Laravel Sanctum
- CRUD operations for tasks (create, read, update, delete)
- Filter tasks by status (pending/completed)
- Mark tasks as complete with a custom endpoint
- Resource transformations using API Resources
- Form Request validation for all endpoints
- Authorization (users can only manage their own tasks)

**Validation**: Test your API with:

```bash
# Register a user
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Test User","email":"test@example.com","password":"password"}'

# Login and get token
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}'

# Create a task (use token from login)
curl -X POST http://localhost:8000/api/tasks \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"title":"Complete API exercise","description":"Build task API"}'

# List tasks
curl -X GET http://localhost:8000/api/tasks \
  -H "Authorization: Bearer YOUR_TOKEN"

# Mark task as complete
curl -X POST http://localhost:8000/api/tasks/1/complete \
  -H "Authorization: Bearer YOUR_TOKEN"
```

Expected output should include proper JSON responses with status codes and resource transformations.

### Exercise 2: Add Rate Limiting

**Goal**: Implement custom rate limiting for different user types and endpoints.

Add custom rate limits to your API:

- 100 requests/hour for authenticated users
- 20 requests/hour for guests (unauthenticated)
- 10 requests/hour for login endpoint (prevent brute force)
- 200 requests/hour for premium users (if you add a user type)

**Validation**: Test rate limiting:

```bash
# Make 21 requests as guest (should fail on 21st)
for i in {1..21}; do
  curl -X GET http://localhost:8000/api/tasks
done

# Check response headers for rate limit info
curl -I http://localhost:8000/api/tasks
# Should include: X-RateLimit-Limit, X-RateLimit-Remaining
```

### Exercise 3: API Versioning

**Goal**: Create a V2 version of your API with backward compatibility.

Create V2 of your API with:

- Different response structure (e.g., wrap data in `data` key for V2, direct array for V1)
- New fields added to resources
- Maintain V1 endpoints for backward compatibility
- Use route versioning: `/api/v1/tasks` and `/api/v2/tasks`

**Validation**: Both versions should work:

```bash
# V1 endpoint (original structure)
curl http://localhost:8000/api/v1/tasks

# V2 endpoint (new structure)
curl http://localhost:8000/api/v2/tasks
```

## Wrap-up

You've now mastered building REST APIs in Laravel and how they compare to Rails:

- ✓ **API Routing** - Laravel's `apiResource` routes work just like Rails `resources`, with automatic `/api` prefix
- ✓ **Controllers** - Similar structure to Rails, with route model binding eliminating the need for `set_post` callbacks
- ✓ **Request Validation** - Form Requests provide cleaner separation than Rails strong parameters
- ✓ **Resource Transformations** - Laravel Resources are more powerful than ActiveModel Serializers with better conditional logic
- ✓ **API Authentication** - Laravel Sanctum is simpler to set up than Devise + JWT, with built-in token management
- ✓ **Error Handling** - Centralized exception handling with automatic JSON formatting
- ✓ **Rate Limiting** - Built-in rate limiting with flexible configuration (no gem needed)
- ✓ **API Versioning** - Clean versioning through route prefixes and namespaces
- ✓ **CORS Configuration** - Built-in CORS support (no gem needed)
- ✓ **Testing** - Fluent JSON assertions make API testing more readable than Rails
- ✓ **Performance** - Eager loading and caching patterns identical to Rails
- ✓ **Filtering & Searching** - Query scopes and conditional filtering with clean syntax
- ✓ **Response Standardization** - Traits and helpers for consistent API response formats
- ✓ **File Uploads** - Handle single and multiple file uploads with validation and storage
- ✓ **External API Integration** - Laravel's HTTP client makes consuming APIs simple and elegant
- ✓ **Webhooks** - Process incoming webhooks with signature verification

The core API development patterns are nearly identical between Rails and Laravel. The main advantages of Laravel are built-in features (Sanctum, rate limiting, CORS) that require gems in Rails, and more powerful resource transformations.

You now have everything you need to build production-ready REST APIs in Laravel!

## Further Reading

- [Laravel API Resources](https://laravel.com/docs/eloquent-resources) — Official documentation for transforming API responses
- [Laravel Sanctum](https://laravel.com/docs/sanctum) — Complete guide to API authentication with Sanctum
- [Laravel Form Requests](https://laravel.com/docs/validation#form-request-validation) — Documentation for request validation
- [Laravel Rate Limiting](https://laravel.com/docs/routing#rate-limiting) — Guide to implementing rate limiting
- [Laravel API Testing](https://laravel.com/docs/http-tests) — Comprehensive guide to testing APIs
- [Laravel CORS Configuration](https://laravel.com/docs/sanctum#cors-and-cookies) — CORS setup for APIs
- [REST API Design Best Practices](https://restfulapi.net/) — General REST API design principles
- [JSON:API Specification](https://jsonapi.org/) — Standard for building APIs in JSON
- [Laravel API Versioning](https://laravel.com/docs/routing#route-groups) — Patterns for API versioning
- [Laravel Query Scopes](https://laravel.com/docs/eloquent#query-scopes) — Reusable query logic for filtering
- [Laravel API Documentation](https://laravel.com/docs/sanctum#api-token-authentication) — Documenting your APIs
- [Laravel Scribe](https://scribe.knuckles.wtf/laravel) — Automatic API documentation generator for Laravel
- [OpenAPI/Swagger](https://swagger.io/) — Industry standard for API documentation
- [Laravel HTTP Client](https://laravel.com/docs/http-client) — Making HTTP requests to external APIs
- [Laravel File Storage](https://laravel.com/docs/filesystem) — File uploads and storage management
- [Laravel File Validation](https://laravel.com/docs/validation#rule-file) — Validating file uploads

## What's Next?

Now that you can build REST APIs in Laravel, the next chapter covers testing, deployment, and DevOps practices to ship your Laravel applications to production.

---

::: tip Continue Learning
Move on to [Chapter 07: Testing, Deployment, DevOps](/series/rails-developers-love-laravel/chapters/07-testing-deployment-devops) to learn how to test and deploy Laravel applications.
:::


<style>
.code-comparison {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  margin: 1.5rem 0;
}

.api-box {
  background: #f0fdf4;
  border-left: 4px solid #10b981;
  padding: 1rem;
  margin: 1rem 0;
  border-radius: 4px;
}

.auth-box {
  background: #fef3c7;
  border-left: 4px solid #f59e0b;
  padding: 1rem;
  margin: 1rem 0;
  border-radius: 4px;
}
</style>
