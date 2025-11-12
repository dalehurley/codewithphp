# Chapter 06: Building REST APIs & Integrations — Code Examples

Complete, working examples for Chapter 06 demonstrating REST API development in Laravel, comparing Flask-RESTful and Django REST Framework patterns.

## Files

### Python Examples (For Comparison)

#### `flask-restful-api.py`

Flask-RESTful API example showing Resource classes:

- `UserResource` for individual user operations (GET, PUT, DELETE)
- `UserListResource` for collection operations (GET, POST)
- Route registration with `api.add_resource()`

**Run:**

```bash
pip install flask flask-restful
python flask-restful-api.py
```

#### `django-rest-api.py`

Django REST Framework ViewSet example:

- `UserViewSet` with standard REST actions (list, retrieve, create, update, destroy)
- Router registration pattern
- Response formatting

**Run:**

```bash
# Requires Django and djangorestframework
python manage.py runserver
```

#### `django-serializer.py`

Django REST Framework serializer example:

- `UserSerializer` for response transformation
- Computed fields using `SerializerMethodField()`
- Usage in viewsets

#### `flask-validation.py`

Flask request validation using Marshmallow:

- `UserSchema` for request validation
- Error handling with `ValidationError`
- Compare to Laravel Form Requests

**Run:**

```bash
pip install flask marshmallow
python flask-validation.py
```

#### `django-rest-auth.py`

Django REST Framework authentication:

- Token-based authentication
- Login endpoint that creates tokens
- Protected endpoints with `@permission_classes([IsAuthenticated])`

#### `python-requests-example.py`

Python `requests` library for external APIs:

- GET and POST requests
- Error handling (HTTPError, RequestException)
- Retry logic implementation
- Compare to Laravel HTTP Client

**Run:**

```bash
pip install requests
python python-requests-example.py
```

### Laravel Examples

#### `laravel-api-routes.php`

Laravel API route definitions:

- Individual route definitions
- `Route::apiResource()` for automatic RESTful routes
- Route prefixing and grouping

**Location:** `routes/api.php`

#### `laravel-api-controller.php`

Laravel API controller example:

- `UserController` with standard REST methods (index, show, store, update, destroy)
- JSON response formatting
- Request handling

**Location:** `app/Http/Controllers/Api/UserController.php`

#### `laravel-api-resource.php`

Laravel API Resource for response formatting:

- `UserResource` extending `JsonResource`
- `toArray()` method for transformation
- Computed fields
- Collection usage with `UserResource::collection()`

**Location:** `app/Http/Resources/UserResource.php`

#### `laravel-form-request.php`

Laravel Form Request for validation:

- `StoreUserRequest` extending `FormRequest`
- Validation rules in `rules()` method
- Custom error messages
- Automatic JSON error responses

**Location:** `app/Http/Requests/StoreUserRequest.php`

#### `laravel-api-auth.php`

Laravel Sanctum authentication:

- `AuthController` with login/logout endpoints
- Token creation with `createToken()`
- Protected routes with `auth:sanctum` middleware
- Compare to Flask-JWT and Django REST tokens

**Setup:**

```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

**Location:** `app/Http/Controllers/Api/AuthController.php`

#### `laravel-http-client.php`

Laravel HTTP Client for external APIs:

- `ExternalApiService` using `Http` facade
- GET and POST requests
- Error handling with `RequestException`
- Retry logic with `retry()` method
- Compare to Python `requests` library

**Location:** `app/Services/ExternalApiService.php`

#### `laravel-api-versioning.php`

Laravel API versioning strategies:

- URL-based versioning with `Route::prefix('v1')`
- Header-based versioning with custom middleware
- Version detection and routing

**Location:** `routes/api.php` and `app/Http/Middleware/ApiVersion.php`

#### `laravel-query-filtering.php`

Laravel query parameter filtering and sorting:

- Handle query parameters with `$request->query()`
- Dynamic filtering (`?status=active&author_id=1`)
- Sorting (`?sort=created_at&order=desc`)
- Query parameter validation using Form Requests
- Compare to Flask's `request.args` and Django REST's `query_params`

**Location:** `app/Http/Controllers/Api/UserController.php` and `app/Http/Requests/IndexUserRequest.php`

#### `laravel-cors-config.php`

Laravel CORS configuration:

- Configure CORS in `config/cors.php`
- Environment-based configuration
- Allowed origins, methods, and headers
- Compare to Flask-CORS and Django CORS headers

**Location:** `config/cors.php`

#### `laravel-file-upload.php`

Laravel file upload handling:

- Handle multipart/form-data requests
- File validation and storage
- Local and S3 storage examples
- Multiple file uploads
- Compare to Flask's `request.files` and Django REST file handling

**Location:** `app/Http/Controllers/Api/UserController.php` and `app/Http/Requests/UploadAvatarRequest.php`

## Key Comparisons

### Routes & Controllers

| Flask-RESTful | Django REST | Laravel |
|---------------|-------------|---------|
| `Resource` class | `ViewSet` class | `Controller` class |
| `api.add_resource()` | Router registration | `Route::apiResource()` |
| HTTP methods in class | Action methods | RESTful methods |

### Response Formatting

| Django REST | Flask | Laravel |
|-------------|-------|---------|
| `Serializer` | Manual function | `JsonResource` |
| `SerializerMethodField()` | Function logic | Computed in `toArray()` |
| `serializer.data` | `format_user()` | `new UserResource()` |

### Validation

| Flask (Marshmallow) | Django Forms | Laravel Form Requests |
|---------------------|--------------|----------------------|
| `Schema` class | `Form` class | `FormRequest` class |
| `Schema().load()` | `form.is_valid()` | Automatic (type-hint) |
| `ValidationError` | `form.errors` | `$validator->errors()` |

### Authentication

| Flask-JWT | Django REST | Laravel Sanctum |
|-----------|-------------|-----------------|
| `@jwt_required()` | `@permission_classes([IsAuthenticated])` | `auth:sanctum` middleware |
| `create_access_token()` | `Token.objects.create()` | `$user->createToken()` |
| JWT (stateless) | Database token | Database token |

### External APIs

| Python `requests` | Laravel HTTP Client |
|-------------------|-------------------|
| `requests.get()` | `Http::get()` |
| `response.json()` | `$response->json()` |
| `response.raise_for_status()` | `$response->throw()` |
| Manual retry | `->retry($times, $delay)` |

### Query Parameters

| Flask | Django REST | Laravel |
|-------|-------------|---------|
| `request.args.get('key')` | `request.query_params.get('key')` | `$request->query('key')` |
| Manual filtering | `queryset.filter()` | `$query->where()` |
| Manual sorting | `queryset.order_by()` | `$query->orderBy()` |

### File Uploads

| Flask | Django REST | Laravel |
|-------|-------------|---------|
| `request.files['key']` | `request.FILES['key']` | `$request->file('key')` |
| `file.save(path)` | `user.avatar = file` | `$file->store('path', 'disk')` |
| Manual validation | Form field validation | Form Request validation |

### CORS Configuration

| Flask-CORS | Django CORS | Laravel |
|------------|-------------|---------|
| `CORS(app, resources={...})` | `CORS_ALLOWED_ORIGINS` in settings | `config/cors.php` |
| Decorator-based | Middleware-based | Middleware-based (automatic) |

## Running Examples

### Python Examples

```bash
# Install dependencies
pip install flask flask-restful marshmallow requests djangorestframework

# Run Flask examples
python flask-restful-api.py
python flask-validation.py

# Run requests example
python python-requests-example.py
```

### Laravel Examples

These are code snippets meant to be integrated into a Laravel application:

1. Copy route definitions to `routes/api.php`
2. Copy controllers to `app/Http/Controllers/Api/`
3. Copy resources to `app/Http/Resources/`
4. Copy form requests to `app/Http/Requests/`
5. Copy services to `app/Services/`

Then test with:

```bash
# Start Laravel server
php artisan serve

# Test endpoints
curl http://localhost:8000/api/users
curl -X POST http://localhost:8000/api/users \
  -H "Content-Type: application/json" \
  -d '{"name":"John Doe","email":"john@example.com"}'
```

## Common Tasks

### Create API Controller

```bash
php artisan make:controller Api/UserController --api
```

### Create API Resource

```bash
php artisan make:resource UserResource
```

### Create Form Request

```bash
php artisan make:request StoreUserRequest
```

### Install Sanctum

```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

## Tips

1. **API Routes**: Always use `routes/api.php` for API endpoints. They're automatically prefixed with `/api` and don't require CSRF protection.

2. **Resource Routes**: Use `Route::apiResource()` to generate all RESTful routes automatically. It creates: index, show, store, update, destroy.

3. **Form Requests**: Type-hint Form Requests in controller methods. Laravel automatically validates before the method executes.

4. **API Resources**: Use `UserResource::collection($users)` for multiple resources. Laravel handles the collection wrapper automatically.

5. **Sanctum Tokens**: Store tokens securely in your frontend. They're only shown once during creation.

6. **HTTP Client**: Use `Http::retry()` for automatic retry logic. Configure retry conditions for better error handling.

## Related Chapters

- [Chapter 05: Working with Data: Eloquent ORM & Database Workflow](/series/python-developers-love-php-laravel/chapters/05-working-with-data-eloquent-orm-database-workflow)
- [Chapter 07: Testing, Deployment, DevOps: Best Practices](/series/python-developers-love-php-laravel/chapters/07-testing-deployment-devops-best-practices)

## Further Reading

- [Laravel API Routes](https://laravel.com/docs/routing#api-routes)
- [Laravel API Resources](https://laravel.com/docs/eloquent-resources)
- [Laravel Form Requests](https://laravel.com/docs/validation#form-request-validation)
- [Laravel Sanctum](https://laravel.com/docs/sanctum)
- [Laravel HTTP Client](https://laravel.com/docs/http-client)

