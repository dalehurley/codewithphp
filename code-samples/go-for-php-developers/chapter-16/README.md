# Chapter 16: HTTP Server Basics

Build your first HTTP server in Go. Discover why Go's built-in net/http package is production-ready - no Apache, Nginx, or PHP-FPM needed.

## Overview

Unlike PHP which requires a web server (Apache, Nginx) and PHP-FPM, Go includes a production-ready HTTP server in its standard library. You can build, deploy, and scale web applications using just Go - no external web server needed.

## Files in This Chapter

### 1. `01-hello-server.go`
**Topics**: Basic HTTP server, http.ListenAndServe, simple handlers

### 2. `02-handlers.go`
**Topics**: http.Handler interface, http.HandlerFunc, handler functions

### 3. `03-routing.go`
**Topics**: http.ServeMux, pattern matching, route parameters

### 4. `04-request-response.go`
**Topics**: Reading requests, writing responses, headers, status codes

### 5. `05-static-files.go`
**Topics**: File server, serving static assets, http.FileServer

### 6. `06-server-config.go`
**Topics**: http.Server configuration, timeouts, TLS/HTTPS

## Quick Reference

### Basic Server

**PHP**:
```php
// index.php (requires Apache/Nginx + PHP-FPM)
<?php
echo "Hello, World!";
```

**Go**:
```go
// Complete standalone server
package main

import (
    "fmt"
    "net/http"
)

func main() {
    http.HandleFunc("/", func(w http.ResponseWriter, r *http.Request) {
        fmt.Fprintf(w, "Hello, World!")
    })

    http.ListenAndServe(":8080", nil)
}

// Run: go run main.go
// No web server needed!
```

### Routing

**PHP**:
```php
// router.php or .htaccess
$uri = $_SERVER['REQUEST_URI'];

if ($uri === '/') {
    echo "Home";
} elseif ($uri === '/about') {
    echo "About";
} elseif (preg_match('/^\/user\/(\d+)$/', $uri, $matches)) {
    echo "User " . $matches[1];
}
```

**Go**:
```go
mux := http.NewServeMux()

mux.HandleFunc("/", homeHandler)
mux.HandleFunc("/about", aboutHandler)
mux.HandleFunc("/user/", userHandler)  // Matches /user/*

http.ListenAndServe(":8080", mux)
```

## Key Concepts

### 1. Hello World Server

```go
package main

import (
    "fmt"
    "net/http"
    "log"
)

func main() {
    http.HandleFunc("/", func(w http.ResponseWriter, r *http.Request) {
        fmt.Fprintf(w, "Hello, World!")
    })

    log.Println("Server starting on :8080")
    log.Fatal(http.ListenAndServe(":8080", nil))
}
```

### 2. Handler Interface

```go
// http.Handler interface
type Handler interface {
    ServeHTTP(ResponseWriter, *Request)
}

// Custom handler (struct)
type HelloHandler struct{}

func (h *HelloHandler) ServeHTTP(w http.ResponseWriter, r *http.Request) {
    fmt.Fprintf(w, "Hello from struct handler!")
}

// Usage
http.Handle("/", &HelloHandler{})
```

### 3. Handler Functions

```go
// Function as handler
func homeHandler(w http.ResponseWriter, r *http.Request) {
    fmt.Fprintf(w, "Welcome home!")
}

// Register with HandleFunc
http.HandleFunc("/", homeHandler)

// Or inline
http.HandleFunc("/about", func(w http.ResponseWriter, r *http.Request) {
    fmt.Fprintf(w, "About page")
})
```

### 4. Request Object

```go
func handler(w http.ResponseWriter, r *http.Request) {
    // Method
    method := r.Method  // GET, POST, etc.

    // URL
    path := r.URL.Path
    query := r.URL.Query()  // Query parameters

    // Headers
    userAgent := r.Header.Get("User-Agent")

    // Body
    body, err := io.ReadAll(r.Body)
    defer r.Body.Close()

    // Form data
    r.ParseForm()
    username := r.Form.Get("username")
}
```

### 5. Response Writer

```go
func handler(w http.ResponseWriter, r *http.Request) {
    // Set header
    w.Header().Set("Content-Type", "application/json")

    // Set status code
    w.WriteHeader(http.StatusOK)  // 200

    // Write response
    fmt.Fprintf(w, `{"message": "Hello"}`)

    // Or
    w.Write([]byte(`{"message": "Hello"}`))
}
```

### 6. ServeMux (Router)

```go
mux := http.NewServeMux()

// Routes
mux.HandleFunc("/", homeHandler)
mux.HandleFunc("/api/users", usersHandler)
mux.HandleFunc("/api/posts", postsHandler)

// Start server with custom mux
http.ListenAndServe(":8080", mux)
```

## Common Patterns

### 1. RESTful API Handlers

```go
func usersHandler(w http.ResponseWriter, r *http.Request) {
    switch r.Method {
    case http.MethodGet:
        getUsers(w, r)
    case http.MethodPost:
        createUser(w, r)
    default:
        http.Error(w, "Method not allowed", http.StatusMethodNotAllowed)
    }
}

func getUsers(w http.ResponseWriter, r *http.Request) {
    users := []User{{ID: 1, Name: "Alice"}, {ID: 2, Name: "Bob"}}

    w.Header().Set("Content-Type", "application/json")
    json.NewEncoder(w).Encode(users)
}

func createUser(w http.ResponseWriter, r *http.Request) {
    var user User

    if err := json.NewDecoder(r.Body).Decode(&user); err != nil {
        http.Error(w, err.Error(), http.StatusBadRequest)
        return
    }

    // Save user...

    w.WriteHeader(http.StatusCreated)
    json.NewEncoder(w).Encode(user)
}
```

### 2. Static File Server

```go
func main() {
    // Serve files from ./static directory
    fs := http.FileServer(http.Dir("./static"))
    http.Handle("/static/", http.StripPrefix("/static/", fs))

    // API routes
    http.HandleFunc("/api/users", usersHandler)

    http.ListenAndServe(":8080", nil)
}

// Access: http://localhost:8080/static/style.css
```

### 3. Query Parameters

```go
func searchHandler(w http.ResponseWriter, r *http.Request) {
    query := r.URL.Query()

    q := query.Get("q")           // Single value
    filters := query["filter"]    // Multiple values

    fmt.Fprintf(w, "Search: %s, Filters: %v", q, filters)
}

// /search?q=golang&filter=new&filter=popular
```

### 4. Form Handling

```go
func formHandler(w http.ResponseWriter, r *http.Request) {
    if r.Method == http.MethodGet {
        // Show form
        html := `
            <form method="POST">
                <input name="username" />
                <input name="email" />
                <button>Submit</button>
            </form>
        `
        fmt.Fprintf(w, html)
        return
    }

    // Parse form
    if err := r.ParseForm(); err != nil {
        http.Error(w, "Bad request", http.StatusBadRequest)
        return
    }

    username := r.FormValue("username")
    email := r.FormValue("email")

    fmt.Fprintf(w, "Received: %s, %s", username, email)
}
```

### 5. JSON API

```go
type User struct {
    ID    int    `json:"id"`
    Name  string `json:"name"`
    Email string `json:"email"`
}

func getUserHandler(w http.ResponseWriter, r *http.Request) {
    user := User{
        ID:    1,
        Name:  "Alice",
        Email: "alice@example.com",
    }

    w.Header().Set("Content-Type", "application/json")
    json.NewEncoder(w).Encode(user)
}

func createUserHandler(w http.ResponseWriter, r *http.Request) {
    var user User

    if err := json.NewDecoder(r.Body).Decode(&user); err != nil {
        http.Error(w, err.Error(), http.StatusBadRequest)
        return
    }

    // Validate and save...

    w.WriteHeader(http.StatusCreated)
    json.NewEncoder(w).Encode(user)
}
```

### 6. Server Configuration

```go
func main() {
    mux := http.NewServeMux()
    mux.HandleFunc("/", homeHandler)

    // Configure server
    server := &http.Server{
        Addr:         ":8080",
        Handler:      mux,
        ReadTimeout:  10 * time.Second,
        WriteTimeout: 10 * time.Second,
        IdleTimeout:  60 * time.Second,
        MaxHeaderBytes: 1 << 20,  // 1 MB
    }

    log.Fatal(server.ListenAndServe())
}
```

## Best Practices

### 1. Always Set Timeouts

```go
server := &http.Server{
    Addr:         ":8080",
    Handler:      mux,
    ReadTimeout:  5 * time.Second,
    WriteTimeout: 10 * time.Second,
    IdleTimeout:  120 * time.Second,
}
```

### 2. Use Custom ServeMux

```go
// ❌ Using default mux (global state)
http.HandleFunc("/", handler)

// ✅ Custom mux (better testability)
mux := http.NewServeMux()
mux.HandleFunc("/", handler)
http.ListenAndServe(":8080", mux)
```

### 3. Handle Errors Properly

```go
func handler(w http.ResponseWriter, r *http.Request) {
    data, err := fetchData()
    if err != nil {
        http.Error(w, "Internal server error", http.StatusInternalServerError)
        log.Printf("Error: %v", err)
        return
    }

    json.NewEncoder(w).Encode(data)
}
```

### 4. Set Content-Type

```go
// JSON
w.Header().Set("Content-Type", "application/json")

// HTML
w.Header().Set("Content-Type", "text/html; charset=utf-8")

// Plain text
w.Header().Set("Content-Type", "text/plain")
```

## Common Mistakes

### 1. Writing Header After Body

```go
// ❌ Wrong order
w.Write([]byte("Hello"))
w.WriteHeader(http.StatusOK)  // Too late!

// ✅ Correct order
w.WriteHeader(http.StatusOK)
w.Write([]byte("Hello"))

// ✅ Or just write (defaults to 200)
w.Write([]byte("Hello"))
```

### 2. Not Checking HTTP Method

```go
// ❌ Accepts any method
func handler(w http.ResponseWriter, r *http.Request) {
    // Process...
}

// ✅ Check method
func handler(w http.ResponseWriter, r *http.Request) {
    if r.Method != http.MethodPost {
        http.Error(w, "Method not allowed", http.StatusMethodNotAllowed)
        return
    }
    // Process...
}
```

### 3. Not Closing Request Body

```go
// ❌ Body not closed
body, _ := io.ReadAll(r.Body)

// ✅ Always close
defer r.Body.Close()
body, err := io.ReadAll(r.Body)
```

## Comparison with PHP

| Feature | PHP | Go |
|---------|-----|-----|
| Web Server | Apache/Nginx required | Built-in |
| Process Model | FPM (process per request) | Goroutines |
| Concurrency | Limited (processes) | Excellent (goroutines) |
| Static Files | Web server handles | Go can handle |
| Routing | .htaccess or framework | ServeMux or libraries |
| Performance | Moderate | High |
| Memory | ~8-32 MB per process | ~2 KB per request |

## Next Steps

- **Chapter 17**: Routing & Middleware - Advanced routing and request processing
- **Chapter 18**: JSON APIs & REST - Building RESTful APIs
- **Chapter 19**: Templates & Views - HTML templating

---

**Key Takeaway**: Go's net/http package is production-ready out of the box. No need for Apache, Nginx, or PHP-FPM - just compile and run. Go's goroutine-based concurrency handles thousands of concurrent requests efficiently, making it perfect for building high-performance web services.
