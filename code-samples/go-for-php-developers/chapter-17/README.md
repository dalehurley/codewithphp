# Chapter 17: Routing & Middleware

Master advanced routing techniques and middleware patterns. Learn how to build clean, composable HTTP request pipelines - the foundation of modern Go web applications.

## Overview

While Go's standard library provides basic routing, real applications need URL parameters, middleware chains, and request/response processing. This chapter covers routing patterns and middleware - from building your own to using popular routers like gorilla/mux and chi.

## Files in This Chapter

### 1. `01-url-parameters.go`
**Topics**: Extracting path parameters, dynamic routes

### 2. `02-middleware-basics.go`
**Topics**: Middleware pattern, wrapping handlers, chains

### 3. `03-common-middleware.go`
**Topics**: Logging, auth, CORS, recovery middleware

### 4. `04-gorilla-mux.go`
**Topics**: gorilla/mux router, advanced routing patterns

### 5. `05-chi-router.go`
**Topics**: chi router, middleware mounting, route groups

### 6. `06-custom-router.go`
**Topics**: Building a simple router from scratch

## Quick Reference

### Routing

**PHP**:
```php
// Laravel/Symfony style
Route::get('/users/{id}', function($id) {
    return "User: " . $id;
});

// Middleware
Route::middleware(['auth'])->group(function() {
    Route::get('/dashboard', 'DashboardController');
});
```

**Go with gorilla/mux**:
```go
import "github.com/gorilla/mux"

r := mux.NewRouter()

r.HandleFunc("/users/{id}", func(w http.ResponseWriter, r *http.Request) {
    vars := mux.Vars(r)
    id := vars["id"]
    fmt.Fprintf(w, "User: %s", id)
})

// Middleware
r.Use(authMiddleware)
r.HandleFunc("/dashboard", dashboardHandler)
```

## Key Concepts

### 1. Middleware Pattern

```go
// Middleware signature
type Middleware func(http.Handler) http.Handler

// Logging middleware
func loggingMiddleware(next http.Handler) http.Handler {
    return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
        start := time.Now()

        next.ServeHTTP(w, r)

        log.Printf("%s %s %v", r.Method, r.URL.Path, time.Since(start))
    })
}

// Usage
mux := http.NewServeMux()
mux.HandleFunc("/", homeHandler)

handler := loggingMiddleware(mux)
http.ListenAndServe(":8080", handler)
```

### 2. Middleware Chaining

```go
func chain(h http.Handler, middlewares ...Middleware) http.Handler {
    for i := len(middlewares) - 1; i >= 0; i-- {
        h = middlewares[i](h)
    }
    return h
}

// Usage
handler := chain(
    mux,
    loggingMiddleware,
    authMiddleware,
    corsMiddleware,
)
```

### 3. Recovery Middleware

```go
func recoveryMiddleware(next http.Handler) http.Handler {
    return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
        defer func() {
            if err := recover(); err != nil {
                log.Printf("Panic: %v", err)
                http.Error(w, "Internal server error", http.StatusInternalServerError)
            }
        }()

        next.ServeHTTP(w, r)
    })
}
```

### 4. Authentication Middleware

```go
func authMiddleware(next http.Handler) http.Handler {
    return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
        token := r.Header.Get("Authorization")

        if token == "" {
            http.Error(w, "Unauthorized", http.StatusUnauthorized)
            return
        }

        // Validate token...
        if !isValidToken(token) {
            http.Error(w, "Invalid token", http.StatusUnauthorized)
            return
        }

        next.ServeHTTP(w, r)
    })
}
```

### 5. CORS Middleware

```go
func corsMiddleware(next http.Handler) http.Handler {
    return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
        w.Header().Set("Access-Control-Allow-Origin", "*")
        w.Header().Set("Access-Control-Allow-Methods", "GET, POST, PUT, DELETE")
        w.Header().Set("Access-Control-Allow-Headers", "Content-Type, Authorization")

        if r.Method == http.MethodOptions {
            w.WriteHeader(http.StatusOK)
            return
        }

        next.ServeHTTP(w, r)
    })
}
```

### 6. gorilla/mux Router

```go
import "github.com/gorilla/mux"

r := mux.NewRouter()

// Path parameters
r.HandleFunc("/users/{id:[0-9]+}", getUserHandler)

// Query parameters
r.HandleFunc("/search", searchHandler).Queries("q", "{query}")

// Methods
r.HandleFunc("/api/users", createUserHandler).Methods("POST")
r.HandleFunc("/api/users/{id}", getUserHandler).Methods("GET")

// Subrouters
api := r.PathPrefix("/api").Subrouter()
api.Use(authMiddleware)
api.HandleFunc("/users", usersHandler)

http.ListenAndServe(":8080", r)
```

## Common Patterns

### 1. Request Context

```go
type contextKey string

const userKey contextKey = "user"

func authMiddleware(next http.Handler) http.Handler {
    return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
        user := getUserFromToken(r)

        // Store in context
        ctx := context.WithValue(r.Context(), userKey, user)
        r = r.WithContext(ctx)

        next.ServeHTTP(w, r)
    })
}

func handler(w http.ResponseWriter, r *http.Request) {
    user := r.Context().Value(userKey).(*User)
    fmt.Fprintf(w, "Hello, %s", user.Name)
}
```

### 2. Route Groups

```go
// Using chi router
r := chi.NewRouter()

// Public routes
r.Group(func(r chi.Router) {
    r.Get("/", homeHandler)
    r.Get("/login", loginHandler)
})

// Protected routes
r.Group(func(r chi.Router) {
    r.Use(authMiddleware)
    r.Get("/dashboard", dashboardHandler)
    r.Get("/profile", profileHandler)
})
```

### 3. Request Validation Middleware

```go
func validateJSONMiddleware(next http.Handler) http.Handler {
    return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
        if r.Header.Get("Content-Type") != "application/json" {
            http.Error(w, "Content-Type must be application/json", http.StatusBadRequest)
            return
        }

        next.ServeHTTP(w, r)
    })
}
```

### 4. Rate Limiting Middleware

```go
import "golang.org/x/time/rate"

func rateLimitMiddleware(limiter *rate.Limiter) Middleware {
    return func(next http.Handler) http.Handler {
        return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
            if !limiter.Allow() {
                http.Error(w, "Too many requests", http.StatusTooManyRequests)
                return
            }

            next.ServeHTTP(w, r)
        })
    }
}

// Usage
limiter := rate.NewLimiter(10, 20)  // 10 req/sec, burst 20
handler := rateLimitMiddleware(limiter)(mux)
```

## Best Practices

### 1. Use Popular Routers for Complex Apps

```go
// gorilla/mux
import "github.com/gorilla/mux"

// chi (lightweight, composable)
import "github.com/go-chi/chi/v5"

// gin (fastest, but more opinionated)
import "github.com/gin-gonic/gin"
```

### 2. Order Matters in Middleware

```go
// ✅ Correct order
handler := chain(
    mux,
    recoveryMiddleware,  // First: catch panics
    loggingMiddleware,   // Second: log requests
    authMiddleware,      // Third: authenticate
)

// ❌ Wrong order
handler := chain(
    mux,
    authMiddleware,      // Panic might bypass this
    loggingMiddleware,   // Logs after auth failure
    recoveryMiddleware,  // Recovery last won't help
)
```

### 3. Use Context for Request-Scoped Data

```go
// ✅ Use context
ctx := context.WithValue(r.Context(), userKey, user)
r = r.WithContext(ctx)

// ❌ Don't use global variables
var currentUser *User  // Race conditions!
```

## Next Steps

- **Chapter 18**: JSON APIs & REST - Building RESTful services
- **Chapter 19**: Templates & Views - Server-side rendering
- **Chapter 32**: Dependency Injection - Advanced patterns

---

**Key Takeaway**: Middleware is the foundation of request processing in Go web apps. Master the middleware pattern and use popular routers like chi or gorilla/mux for production applications. Unlike PHP frameworks that hide complexity, Go's middleware pattern is explicit and composable.
