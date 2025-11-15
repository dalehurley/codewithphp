# Go for PHP Developers: Chapters 16-20 Summary

## Part 4: Web Development

Build production-ready web applications with Go's built-in HTTP server and popular frameworks. Learn how Go replaces the entire PHP-FPM + Apache/Nginx stack.

## Overview

**Total Chapters**: 5 (Chapters 16-20)
**Code Files**: 25+ Go files
**Learning Time**: 2-3 weeks
**Prerequisite**: Parts 1-3 (Chapters 00-15)
**Difficulty**: Intermediate

## The Big Picture

**Traditional PHP Stack**:
```
Request → Nginx/Apache → PHP-FPM → Your Code → Response
```
- Separate web server needed
- Configuration files
- Process management
- Multiple moving parts

**Go Stack**:
```
Request → Your Code (with built-in HTTP server) → Response
```
- No web server needed!
- Single compiled binary
- Self-contained
- Deploy anywhere

## What's Covered

### Chapter 16: HTTP Server Basics
**Goal**: Replace Apache/Nginx + PHP-FPM with Go

**Code Files Created**:
- `01-simple-http-server.go` - Basic HTTP server
- `02-handlers-and-routing.go` - Request routing
- `03-request-response.go` - Forms, JSON, files

**Your First Web Server**:

```go
package main

import (
    "fmt"
    "net/http"
)

func main() {
    http.HandleFunc("/", func(w http.ResponseWriter, r *http.Request) {
        fmt.Fprintf(w, "Hello from Go!")
    })

    http.ListenAndServe(":8080", nil)
}
```

**vs PHP**:
```php
<?php
// Needs Apache/Nginx + PHP-FPM
echo "Hello from PHP!";
```

**Key Differences**:

| Aspect | PHP | Go |
|--------|-----|-----|
| Web Server | Apache/Nginx required | Built-in |
| Process Model | Process per request | Goroutine per request |
| Startup Time | ~50ms per request | ~5ms total |
| Configuration | httpd.conf, .htaccess | Pure code |
| Deployment | Upload files + configure | Single binary |
| Concurrency | Limited | 10,000+ concurrent |
| Memory | ~30MB baseline | ~5MB baseline |

**Handler Functions**:

```go
// Handler signature
func handler(w http.ResponseWriter, r *http.Request)

// Write response
fmt.Fprintf(w, "Response")

// Set headers
w.Header().Set("Content-Type", "application/json")

// Set status
w.WriteHeader(http.StatusNotFound)

// Read request
method := r.Method
path := r.URL.Path
query := r.URL.Query().Get("name")
```

**Routing**:

```go
// Basic routing
http.HandleFunc("/", homeHandler)
http.HandleFunc("/users", usersHandler)
http.HandleFunc("/api/posts", postsHandler)

// Start server
http.ListenAndServe(":8080", nil)
```

**vs Laravel Routes**:
```php
Route::get('/', [HomeController::class, 'index']);
Route::get('/users', [UserController::class, 'index']);
Route::get('/api/posts', [PostController::class, 'index']);
```

---

### Chapter 17: Routing & Middleware
**Goal**: Build middleware chains like Laravel

**Code Files Created**:
- `01-custom-router.go` - Advanced routing with parameters
- `02-middleware-chain.go` - Middleware stack
- `03-context-usage.go` - Request context

**Middleware Pattern**:

```go
// Middleware type
type Middleware func(http.HandlerFunc) http.HandlerFunc

// Logging middleware
func LoggingMiddleware(next http.HandlerFunc) http.HandlerFunc {
    return func(w http.ResponseWriter, r *http.Request) {
        log.Printf("%s %s", r.Method, r.URL.Path)
        next(w, r)
    }
}

// Auth middleware
func AuthMiddleware(next http.HandlerFunc) http.HandlerFunc {
    return func(w http.ResponseWriter, r *http.Request) {
        token := r.Header.Get("Authorization")
        if token == "" {
            http.Error(w, "Unauthorized", 401)
            return
        }
        next(w, r)
    }
}

// Chain middleware
handler := LoggingMiddleware(AuthMiddleware(apiHandler))
http.HandleFunc("/api/data", handler)
```

**vs Laravel Middleware**:
```php
Route::get('/api/data', [ApiController::class, 'data'])
    ->middleware(['auth', 'log']);
```

**Common Middleware**:

1. **Logging**:
```go
func Logger(next http.Handler) http.Handler {
    return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
        start := time.Now()
        next.ServeHTTP(w, r)
        log.Printf("%s %s %v", r.Method, r.URL.Path, time.Since(start))
    })
}
```

2. **Authentication**:
```go
func RequireAuth(next http.Handler) http.Handler {
    return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
        user, err := authenticate(r)
        if err != nil {
            http.Error(w, "Unauthorized", 401)
            return
        }
        ctx := context.WithValue(r.Context(), "user", user)
        next.ServeHTTP(w, r.WithContext(ctx))
    })
}
```

3. **CORS**:
```go
func CORS(next http.Handler) http.Handler {
    return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
        w.Header().Set("Access-Control-Allow-Origin", "*")
        w.Header().Set("Access-Control-Allow-Methods", "GET, POST, PUT, DELETE")
        if r.Method == "OPTIONS" {
            return
        }
        next.ServeHTTP(w, r)
    })
}
```

4. **Rate Limiting**:
```go
func RateLimit(limit int) func(http.Handler) http.Handler {
    limiter := rate.NewLimiter(rate.Limit(limit), limit)
    return func(next http.Handler) http.Handler {
        return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
            if !limiter.Allow() {
                http.Error(w, "Rate limit exceeded", 429)
                return
            }
            next.ServeHTTP(w, r)
        })
    }
}
```

**Context for Request Data**:

```go
// Set value in context
ctx := context.WithValue(r.Context(), "userID", 123)
r = r.WithContext(ctx)

// Get value from context
userID := r.Context().Value("userID").(int)

// Timeout
ctx, cancel := context.WithTimeout(r.Context(), 5*time.Second)
defer cancel()
```

---

### Chapter 18: JSON APIs & REST
**Goal**: Build RESTful APIs with JSON

**Code Files Created**:
- `01-json-encoding.go` - JSON marshaling/unmarshaling
- `02-rest-api-complete.go` - Full CRUD API
- `03-validation.go` - Input validation

**JSON Encoding**:

```go
type User struct {
    ID    int    `json:"id"`
    Name  string `json:"name"`
    Email string `json:"email"`
}

// Encode (like json_encode)
user := User{ID: 1, Name: "Alice", Email: "alice@example.com"}
jsonData, err := json.Marshal(user)

// Decode (like json_decode)
var user User
err := json.Unmarshal(jsonData, &user)
```

**vs PHP**:
```php
$user = ['id' => 1, 'name' => 'Alice', 'email' => 'alice@example.com'];

// Encode
$json = json_encode($user);

// Decode
$user = json_decode($json, true);
```

**JSON HTTP Response**:

```go
func jsonResponse(w http.ResponseWriter, data interface{}, status int) {
    w.Header().Set("Content-Type", "application/json")
    w.WriteHeader(status)
    json.NewEncoder(w).Encode(data)
}

// Usage
func getUser(w http.ResponseWriter, r *http.Request) {
    user := User{ID: 1, Name: "Alice"}
    jsonResponse(w, user, http.StatusOK)
}
```

**Full REST API**:

```go
// GET /users
func listUsers(w http.ResponseWriter, r *http.Request) {
    users := []User{...}
    jsonResponse(w, users, 200)
}

// GET /users/:id
func getUser(w http.ResponseWriter, r *http.Request) {
    id := extractID(r)
    user, err := findUser(id)
    if err != nil {
        jsonError(w, "User not found", 404)
        return
    }
    jsonResponse(w, user, 200)
}

// POST /users
func createUser(w http.ResponseWriter, r *http.Request) {
    var user User
    if err := json.NewDecoder(r.Body).Decode(&user); err != nil {
        jsonError(w, "Invalid JSON", 400)
        return
    }

    if err := validateUser(&user); err != nil {
        jsonError(w, err.Error(), 422)
        return
    }

    // Save to database
    createdUser, _ := saveUser(&user)
    jsonResponse(w, createdUser, 201)
}

// PUT /users/:id
func updateUser(w http.ResponseWriter, r *http.Request) {
    // Similar to create
}

// DELETE /users/:id
func deleteUser(w http.ResponseWriter, r *http.Request) {
    id := extractID(r)
    if err := deleteUserByID(id); err != nil {
        jsonError(w, "Not found", 404)
        return
    }
    w.WriteHeader(204)  // No Content
}
```

**vs Laravel API**:
```php
Route::apiResource('users', UserController::class);

class UserController extends Controller {
    public function index() {
        return User::all();
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
        ]);

        return User::create($validated);
    }
}
```

**Validation**:

```go
type UserValidator struct {
    Name  string `validate:"required,min=2,max=50"`
    Email string `validate:"required,email"`
    Age   int    `validate:"gte=0,lte=150"`
}

func validateUser(user *User) error {
    validate := validator.New()
    if err := validate.Struct(user); err != nil {
        return fmt.Errorf("validation failed: %w", err)
    }
    return nil
}
```

---

### Chapter 19: Templates & Views
**Goal**: Server-side rendering with templates

**Templates**:

```go
// Define template
tmpl := template.Must(template.ParseFiles("view.html"))

// Render
func renderView(w http.ResponseWriter, r *http.Request) {
    data := map[string]interface{}{
        "Title": "My Page",
        "User":  user,
    }
    tmpl.Execute(w, data)
}
```

**Template Syntax**:
```html
<h1>{{.Title}}</h1>
<p>Welcome, {{.User.Name}}</p>

{{range .Items}}
    <li>{{.}}</li>
{{end}}

{{if .IsLoggedIn}}
    <p>You are logged in</p>
{{else}}
    <p>Please log in</p>
{{end}}
```

**vs PHP/Blade**:
```php
@extends('layouts.app')

@section('content')
    <h1>{{ $title }}</h1>
    <p>Welcome, {{ $user->name }}</p>

    @foreach($items as $item)
        <li>{{ $item }}</li>
    @endforeach

    @if($isLoggedIn)
        <p>You are logged in</p>
    @else
        <p>Please log in</p>
    @endif
@endsection
```

---

### Chapter 20: Web Frameworks
**Goal**: Choose the right framework

**Popular Frameworks**:

1. **Gin** (Most popular):
```go
r := gin.Default()
r.GET("/users/:id", func(c *gin.Context) {
    id := c.Param("id")
    c.JSON(200, gin.H{"id": id})
})
r.Run(":8080")
```

2. **Echo**:
```go
e := echo.New()
e.GET("/users/:id", func(c echo.Context) error {
    id := c.Param("id")
    return c.JSON(200, map[string]string{"id": id})
})
e.Start(":8080")
```

3. **Fiber** (Express.js-like):
```go
app := fiber.New()
app.Get("/users/:id", func(c *fiber.Ctx) error {
    id := c.Params("id")
    return c.JSON(fiber.Map{"id": id})
})
app.Listen(":8080")
```

**Framework Comparison**:

| Framework | Style | Performance | Learning Curve |
|-----------|-------|-------------|----------------|
| **Standard Library** | Explicit | Fast | Low |
| **Gin** | Laravel-like | Fastest | Low |
| **Echo** | Clean | Fast | Low |
| **Fiber** | Express.js-like | Very Fast | Medium |
| **Chi** | Minimalist | Fast | Low |

**When to Use Each**:

- **Standard Library**: Simple APIs, learning, full control
- **Gin**: Production apps, need speed, Laravel background
- **Echo**: Clean code preference, good docs
- **Fiber**: Coming from Node.js, need WebSockets
- **Chi**: Minimalist, idiomatic Go

---

## Complete Example: REST API with Database

```go
package main

import (
    "database/sql"
    "encoding/json"
    "log"
    "net/http"

    _ "github.com/lib/pq"
)

type User struct {
    ID    int    `json:"id"`
    Name  string `json:"name"`
    Email string `json:"email"`
}

var db *sql.DB

func main() {
    // Connect to database
    var err error
    db, err = sql.Open("postgres", "postgres://localhost/mydb")
    if err != nil {
        log.Fatal(err)
    }
    defer db.Close()

    // Routes
    http.HandleFunc("/users", listUsers)
    http.HandleFunc("/users/", userHandler)  // GET, PUT, DELETE with ID

    // Middleware
    handler := loggingMiddleware(http.DefaultServeMux)

    // Start server
    log.Println("Server starting on :8080")
    log.Fatal(http.ListenAndServe(":8080", handler))
}

func listUsers(w http.ResponseWriter, r *http.Request) {
    if r.Method != "GET" && r.Method != "POST" {
        http.Error(w, "Method not allowed", 405)
        return
    }

    if r.Method == "GET" {
        getUsers(w, r)
    } else {
        createUser(w, r)
    }
}

func getUsers(w http.ResponseWriter, r *http.Request) {
    rows, err := db.Query("SELECT id, name, email FROM users")
    if err != nil {
        http.Error(w, err.Error(), 500)
        return
    }
    defer rows.Close()

    var users []User
    for rows.Next() {
        var u User
        if err := rows.Scan(&u.ID, &u.Name, &u.Email); err != nil {
            continue
        }
        users = append(users, u)
    }

    w.Header().Set("Content-Type", "application/json")
    json.NewEncoder(w).Encode(users)
}

func loggingMiddleware(next http.Handler) http.Handler {
    return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
        log.Printf("%s %s", r.Method, r.URL.Path)
        next.ServeHTTP(w, r)
    })
}
```

---

## Best Practices

### 1. Structure Your Application
```
myapp/
├── cmd/
│   └── server/
│       └── main.go          # Entry point
├── internal/
│   ├── handlers/            # HTTP handlers
│   ├── models/              # Data models
│   ├── repository/          # Database layer
│   └── middleware/          # Middleware
└── go.mod
```

### 2. Separate Concerns
```go
// Handler
func GetUser(w http.ResponseWriter, r *http.Request) {
    id := extractID(r)
    user, err := repository.FindUser(id)
    // ...
}

// Repository
func FindUser(id int) (*User, error) {
    // Database logic
}
```

### 3. Use Proper Status Codes
```go
w.WriteHeader(http.StatusOK)           // 200
w.WriteHeader(http.StatusCreated)      // 201
w.WriteHeader(http.StatusNoContent)    // 204
w.WriteHeader(http.StatusBadRequest)   // 400
w.WriteHeader(http.StatusUnauthorized) // 401
w.WriteHeader(http.StatusNotFound)     // 404
w.WriteHeader(http.StatusInternalServerError) // 500
```

### 4. Handle Errors Consistently
```go
func jsonError(w http.ResponseWriter, message string, code int) {
    w.Header().Set("Content-Type", "application/json")
    w.WriteHeader(code)
    json.NewEncoder(w).Encode(map[string]string{
        "error": message,
    })
}
```

---

## Performance Tips

1. **Connection Pooling**: Use `http.Client` with configured transport
2. **Response Buffering**: Write to buffer before sending
3. **Compression**: Use gzip middleware for large responses
4. **Caching**: Implement proper cache headers
5. **Graceful Shutdown**: Handle SIGTERM properly

---

## What's Next

After mastering Part 4:

### Part 5: Database & Data Access (Ch 21-25)
- SQL database integration
- ORMs and query builders
- Redis caching
- Data migrations

---

**Key Takeaway**: Go's built-in HTTP server is production-ready and replaces the entire PHP-FPM + web server stack. Combined with goroutines, you get a high-performance, concurrent web server in a single binary.

---

*Continue to Part 5 to add database persistence to your web applications!*
