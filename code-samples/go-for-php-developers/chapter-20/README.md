# Chapter 20: Web Frameworks

Explore popular Go web frameworks: Gin, Echo, Fiber, and Chi. Learn when to use the standard library vs frameworks, and how they compare to Laravel and Symfony.

## Overview

While Go's standard library is powerful, frameworks provide batteries-included development. Gin, Echo, and Fiber offer routing, middleware, validation, and more - similar to Laravel or Symfony but faster and more lightweight.

## Files in This Chapter

1. `01-gin-basics.go` - Gin framework, routing, middleware
2. `02-echo-framework.go` - Echo framework, handlers, groups
3. `03-fiber-framework.go` - Fiber (Express-like), performance
4. `04-chi-router.go` - Chi (minimal, composable)
5. `05-framework-comparison.go` - Comparing frameworks, benchmarks
6. `06-choosing-framework.go` - When to use std lib vs framework

## Quick Reference

**Standard Library**:
```go
mux := http.NewServeMux()
mux.HandleFunc("/users/{id}", getUserHandler)
http.ListenAndServe(":8080", mux)
```

**Gin Framework**:
```go
r := gin.Default()

r.GET("/users/:id", func(c *gin.Context) {
    id := c.Param("id")
    c.JSON(200, gin.H{"id": id})
})

r.Run(":8080")
```

**Echo Framework**:
```go
e := echo.New()

e.GET("/users/:id", func(c echo.Context) error {
    id := c.Param("id")
    return c.JSON(200, map[string]string{"id": id})
})

e.Start(":8080")
```

## Framework Comparison

### Gin (Most Popular)

```go
import "github.com/gin-gonic/gin"

r := gin.Default()  // With logging and recovery

// Routes
r.GET("/ping", func(c *gin.Context) {
    c.JSON(200, gin.H{"message": "pong"})
})

// Path parameters
r.GET("/users/:id", func(c *gin.Context) {
    id := c.Param("id")
    c.JSON(200, gin.H{"id": id})
})

// Query parameters
r.GET("/search", func(c *gin.Context) {
    query := c.Query("q")
    c.JSON(200, gin.H{"query": query})
})

// JSON binding
r.POST("/users", func(c *gin.Context) {
    var user User
    if err := c.ShouldBindJSON(&user); err != nil {
        c.JSON(400, gin.H{"error": err.Error()})
        return
    }
    c.JSON(201, user)
})

// Route groups
api := r.Group("/api")
{
    api.GET("/users", getUsers)
    api.POST("/users", createUser)
}

r.Run(":8080")
```

### Echo (Clean API)

```go
import "github.com/labstack/echo/v4"

e := echo.New()

// Middleware
e.Use(middleware.Logger())
e.Use(middleware.Recover())

// Routes
e.GET("/users/:id", func(c echo.Context) error {
    id := c.Param("id")
    user := getUser(id)
    return c.JSON(http.StatusOK, user)
})

// Binding
e.POST("/users", func(c echo.Context) error {
    user := new(User)
    if err := c.Bind(user); err != nil {
        return err
    }
    return c.JSON(http.StatusCreated, user)
})

// Groups
api := e.Group("/api")
api.Use(authMiddleware)
api.GET("/admin", adminHandler)

e.Start(":8080")
```

### Fiber (Express-like)

```go
import "github.com/gofiber/fiber/v2"

app := fiber.New()

// Routes
app.Get("/users/:id", func(c *fiber.Ctx) error {
    id := c.Params("id")
    return c.JSON(fiber.Map{"id": id})
})

// Body parser
app.Post("/users", func(c *fiber.Ctx) error {
    user := new(User)
    if err := c.BodyParser(user); err != nil {
        return err
    }
    return c.Status(201).JSON(user)
})

// Groups
api := app.Group("/api")
api.Get("/users", getUsers)

app.Listen(":8080")
```

## Common Patterns

### 1. Middleware in Gin

```go
// Custom middleware
func authMiddleware() gin.HandlerFunc {
    return func(c *gin.Context) {
        token := c.GetHeader("Authorization")

        if token == "" {
            c.AbortWithStatusJSON(401, gin.H{"error": "Unauthorized"})
            return
        }

        // Validate token
        user, err := validateToken(token)
        if err != nil {
            c.AbortWithStatusJSON(401, gin.H{"error": "Invalid token"})
            return
        }

        c.Set("user", user)
        c.Next()
    }
}

// Use middleware
r.Use(authMiddleware())
```

### 2. Validation in Echo

```go
import "github.com/go-playground/validator/v10"

type User struct {
    Name  string `json:"name" validate:"required,min=2"`
    Email string `json:"email" validate:"required,email"`
}

var validate = validator.New()

func createUser(c echo.Context) error {
    user := new(User)

    if err := c.Bind(user); err != nil {
        return err
    }

    if err := validate.Struct(user); err != nil {
        return c.JSON(400, map[string]string{"error": err.Error()})
    }

    // Create user...
    return c.JSON(201, user)
}
```

### 3. Error Handling

```go
// Gin
r.Use(gin.Recovery())

// Echo
e.Use(middleware.Recover())

// Custom error handler (Echo)
e.HTTPErrorHandler = func(err error, c echo.Context) {
    code := http.StatusInternalServerError
    message := "Internal server error"

    if he, ok := err.(*echo.HTTPError); ok {
        code = he.Code
        message = he.Message.(string)
    }

    c.JSON(code, map[string]string{"error": message})
}
```

## Performance Comparison

| Framework | Req/sec | Latency | Memory |
|-----------|---------|---------|--------|
| Fiber | ~600K | 0.1ms | Low |
| Gin | ~400K | 0.2ms | Medium |
| Echo | ~380K | 0.2ms | Medium |
| Chi | ~350K | 0.3ms | Low |
| Std Lib | ~320K | 0.3ms | Very Low |

## When to Use What

**Use Standard Library When**:
- Building microservices
- Need minimal dependencies
- Full control over routing
- Learning Go

**Use Gin When**:
- Building full-featured APIs
- Need validation, binding
- Want good documentation
- Community support important

**Use Echo When**:
- Clean, simple API preferred
- Building RESTful services
- Need good middleware system

**Use Fiber When**:
- Maximum performance needed
- Coming from Express.js
- Building real-time apps

**Use Chi When**:
- Want minimal framework
- Need composable routing
- Prefer standard library style

## Best Practices

- Start with standard library, add framework when needed
- Don't over-engineer for simple APIs
- Use middleware for cross-cutting concerns
- Implement proper error handling
- Add request validation
- Use struct tags for binding
- Benchmark for your use case

## Comparison with PHP

| Feature | Laravel/Symfony | Go Frameworks |
|---------|----------------|---------------|
| Routing | Powerful, file-based | Code-based |
| ORM | Eloquent, Doctrine | Manual or GORM |
| Validation | Built-in | External libraries |
| Middleware | Built-in | Built-in (similar) |
| Performance | Moderate | Very high |
| Learning curve | Higher | Lower |

## Next Steps

- Chapter 21: Database/SQL Package
- Chapter 22: MySQL & PostgreSQL
- Chapter 23: ORMs & Query Builders

---

**Key Takeaway**: Go frameworks are lighter and faster than PHP frameworks while providing similar features. Gin and Echo are production-ready, well-documented, and offer the right balance of features and performance.
