---
title: "Building REST APIs: From Rails to Laravel"
description: Learn how to build robust REST APIs in Laravel using your Rails API knowledge as a foundation.
series: rails-developers-love-laravel
chapter: 6
difficulty: Intermediate
tags: ["laravel", "api", "rest", "sanctum", "resources", "authentication"]
---

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/#choose-your-learning-path">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/rails-developers-love-laravel/">Rails to Laravel</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 06</span>
</div>

![Building REST APIs](/images/rails-developers-love-laravel/chapter-06-building-rest-apis-hero-full.webp)

# Chapter 06: Building REST APIs: From Rails to Laravel <span class="difficulty-badge difficulty-intermediate">Intermediate</span>

## Overview

If you've built REST APIs in Rails, you'll find Laravel's API capabilities remarkably similar yet refreshingly elegant. Laravel provides excellent tools for API development, from resource transformations to authentication, all with a Rails-like developer experience.

This chapter explores how to translate your Rails API knowledge to Laravel, covering everything from basic JSON responses to full-featured API authentication with Laravel Sanctum.

## What You'll Learn

- API routing: Rails routes vs Laravel routes
- JSON responses and error handling
- Resource transformations (Serializers vs Resources)
- API authentication (Devise vs Sanctum)
- API versioning strategies
- Rate limiting and throttling
- Testing APIs
- CORS configuration
- Real-world API patterns

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
// app/Http/Controllers/Api/V1/PostController.php
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
// app/Http/Requests/StorePostRequest.php
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
// app/Http/Resources/PostResource.php
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

// app/Http/Controllers/Api/AuthController.php
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

1. **Similar Patterns** — API development in Laravel feels like Rails
2. **Better Type Safety** — Form requests and type hints catch errors early
3. **Sanctum is Elegant** — Token authentication is simpler than JWT setup
4. **Resources > Serializers** — More powerful and flexible transformations
5. **Built-in Features** — Rate limiting, CORS, pagination included
6. **Testing is Clean** — Fluent assertions make API tests readable
7. **Performance Tools** — Eager loading and caching built-in

## Practice Exercises

### Exercise 1: Build a Task API
Create a task management API with:
- User authentication (Sanctum)
- CRUD operations for tasks
- Filter by status (pending/completed)
- Mark tasks as complete
- Resource transformations

### Exercise 2: Add Rate Limiting
Add custom rate limits:
- 100 requests/hour for authenticated users
- 20 requests/hour for guests
- Different limits for different endpoints

### Exercise 3: API Versioning
Create V2 of your API with:
- Different response structure
- New fields
- Backward compatibility

## What's Next?

Now that you can build REST APIs in Laravel, the next chapter covers testing, deployment, and DevOps practices to ship your Laravel applications to production.

---

::: tip Continue Learning
Move on to [Chapter 07: Testing, Deployment, DevOps](/series/rails-developers-love-laravel/chapters/07-testing-deployment-devops) to learn how to test and deploy Laravel applications.
:::

<ProgressTracker seriesId="rails-developers-love-laravel" :totalChapters="11" title="Your Progress" />

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
