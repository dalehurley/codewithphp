# Chapter 17: Building a Basic HTTP Router - Code Examples

Complete router implementation demonstrating the front controller pattern essential for modern PHP applications.

## Files Overview

### Core Router

1. **`Router.php`** - Basic router class with GET/POST support and parameter matching
2. **`index.php`** - Front controller demo with sample routes
3. **`advanced-router.php`** - Extended router with middleware, named routes, JSON responses
4. **`controllers-example.php`** - Router with controller classes (MVC pattern)
5. **`.htaccess`** - Apache rewrite rules (directs all requests to index.php)

## Quick Start

### Using PHP Built-in Server

```bash
# Start server
php -S localhost:8000

# Visit in browser:
# http://localhost:8000/
# http://localhost:8000/posts
# http://localhost:8000/posts/123
# http://localhost:8000/users/john/profile
```

### Using Apache

1. Place files in Apache document root
2. Ensure `.htaccess` is present
3. Ensure `mod_rewrite` is enabled
4. Visit `http://localhost/`

## Router Features

### Basic Router (`Router.php`)

**Features:**

- GET and POST routes
- URL parameter extraction (`/posts/{id}`)
- Pattern matching with regex
- 404 handling

**Usage:**

```php
$router = new Router();

// Simple route
$router->get('/about', function () {
    echo "About page";
});

// Route with parameter
$router->get('/posts/{id}', function (string $id) {
    echo "Post ID: $id";
});

$router->dispatch();
```

### Advanced Router (`advanced-router.php`)

**Additional Features:**

- Middleware support (global + route-specific)
- Named routes with URL generation
- PUT and DELETE methods
- JSON responses
- Redirect helper

**Usage:**

```php
$router = new AdvancedRouter();

// Global middleware
$router->addMiddleware(function () {
    // Check authentication
    return true;
});

// Named route with middleware
$router->get('/admin', function () {
    echo "Admin panel";
}, 'admin.dashboard')
->middleware(function () {
    // Check if user is admin
    return true;
});

// Generate URL
$url = $router->url('admin.dashboard');

// JSON response
$router->get('/api/users', function () use ($router) {
    $router->json(['users' => [...]]);
});
```

### Controllers Pattern (`controllers-example.php`)

**Organize routes into classes:**

```php
class PostController {
    public function index() { }    // GET /posts
    public function show($id) { }  // GET /posts/{id}
    public function create() { }   // GET /posts/create
    public function store() { }    // POST /posts
}

$controller = new PostController();
$router->get('/posts', [$controller, 'index']);
$router->get('/posts/{id}', [$controller, 'show']);
```

## URL Parameter Extraction

The router extracts parameters from URLs:

```php
// Define route
$router->get('/users/{username}/posts/{id}', function ($username, $id) {
    echo "User: $username, Post: $id";
});

// Match URLs:
// /users/john/posts/123 → username='john', id='123'
// /users/alice/posts/456 → username='alice', id='456'
```

## Front Controller Pattern

**What is it?**
A single entry point (index.php) handles all HTTP requests.

**Benefits:**

- Centralized request handling
- Clean URLs without `.php` extensions
- Easy to add global middleware
- Consistent error handling

**How it works:**

1. Web server redirects all requests to `index.php` (.htaccess)
2. Router parses the request URI
3. Matches URI against defined routes
4. Executes the appropriate handler
5. Returns response

## RESTful Routing

Standard conventions for CRUD operations:

```php
GET    /posts           → index()   // List all
GET    /posts/create    → create()  // Show form
POST   /posts           → store()   // Save new
GET    /posts/{id}      → show()    // Display one
GET    /posts/{id}/edit → edit()    // Show edit form
PUT    /posts/{id}      → update()  // Update
DELETE /posts/{id}      → destroy() // Delete
```

## Middleware

Middleware runs before route handlers:

```php
// Global middleware (all routes)
$router->addMiddleware(function () {
    // Check CSRF token
    // Log request
    // Check authentication
    return true; // continue
});

// Route middleware
$router->get('/admin', function () {
    // ...
})->middleware(function () {
    if (!isAdmin()) {
        redirect('/login');
        return false; // stop execution
    }
    return true;
});
```

## Common Patterns

### API Endpoints

```php
$router->get('/api/posts', function () use ($router) {
    $posts = Post::all();
    $router->json($posts);
});

$router->post('/api/posts', function () use ($router) {
    $data = json_decode(file_get_contents('php://input'), true);
    // Validate and save
    $router->json(['success' => true], 201);
});
```

### Form Handling

```php
$router->get('/posts/create', function () {
    // Show form
});

$router->post('/posts', function () {
    // Validate $_POST
    // Save to database
    // Redirect
});
```

### 404 Custom Handler

```php
// In Router class, modify send404():
private function send404(): void {
    http_response_code(404);
    require 'views/404.php';
}
```

## Web Server Configuration

### Apache (.htaccess)

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

### Nginx

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### PHP Built-in Server

```bash
php -S localhost:8000
# Automatically routes all requests
```

## Security Considerations

✓ **Always sanitize output**: Use `htmlspecialchars()`
✓ **Validate parameters**: Check types and ranges
✓ **Use CSRF tokens**: For POST/PUT/DELETE
✓ **Rate limit**: Prevent abuse
✓ **HTTPS**: In production
✓ **Input validation**: Never trust user input

## Common Mistakes to Avoid

❌ **Forgetting .htaccess**: Routes won't work
❌ **No 404 handling**: Leads to confusing errors
❌ **Hardcoding URLs**: Use named routes or URL helpers
❌ **No parameter validation**: Security risk
❌ **Complex regex**: Keep patterns simple

## Testing Routes

```bash
# Test with curl
curl http://localhost:8000/
curl http://localhost:8000/posts/123
curl -X POST http://localhost:8000/posts -d "title=Test"

# Test 404
curl http://localhost:8000/nonexistent
```

## Next Steps

After mastering the router:

1. **Chapter 18**: Structure your application with MVC
2. **Chapter 19**: Build the complete blog project
3. Add more features:
   - Route groups
   - Subdomain routing
   - Route caching
   - Dependency injection

## Related Chapter

[Chapter 17: Building a Basic HTTP Router](../../chapters/17-building-a-basic-http-router.md)

## Further Reading

- [Front Controller Pattern](https://en.wikipedia.org/wiki/Front_controller)
- [RESTful API Design](https://restfulapi.net/)
- [PHP Routing Libraries](https://github.com/nikic/FastRoute)
