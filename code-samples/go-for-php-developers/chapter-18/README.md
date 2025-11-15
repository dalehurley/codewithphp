# Chapter 18: JSON APIs & REST

Build production-ready REST APIs in Go. Learn JSON handling, status codes, error responses, and best practices for API design - from CRUD to versioning.

## Overview

Go excels at building JSON APIs. The encoding/json package handles serialization, http package provides excellent HTTP support, and Go's type system ensures API consistency. This chapter covers building RESTful APIs that PHP developers would typically create with Laravel or Symfony.

## Files in This Chapter

### 1. `01-json-basics.go`
Topics: Marshal/Unmarshal, struct tags, nested JSON

### 2. `02-rest-crud.go`
Topics: CRUD operations, REST conventions, HTTP methods

### 3. `03-error-handling.go`
Topics: Error responses, status codes, API errors

### 4. `04-validation.go`
Topics: Input validation, error aggregation, validator library

### 5. `05-pagination.go`
Topics: Cursor/offset pagination, response metadata

### 6. `06-versioning.go`
Topics: API versioning strategies, URL vs header versioning

## Quick Reference

**PHP (Laravel)**:
```php
// routes/api.php
Route::get('/users/{id}', function($id) {
    $user = User::find($id);
    return response()->json($user);
});

Route::post('/users', function(Request $request) {
    $validated = $request->validate([
        'name' => 'required|string',
        'email' => 'required|email|unique:users',
    ]);

    $user = User::create($validated);
    return response()->json($user, 201);
});
```

**Go**:
```go
type User struct {
    ID    int    `json:"id"`
    Name  string `json:"name"`
    Email string `json:"email"`
}

r.HandleFunc("/users/{id}", func(w http.ResponseWriter, r *http.Request) {
    user := getUserByID(vars["id"])
    json.NewEncoder(w).Encode(user)
}).Methods("GET")

r.HandleFunc("/users", func(w http.ResponseWriter, r *http.Request) {
    var user User
    json.NewDecoder(r.Body).Decode(&user)

    if err := validate(user); err != nil {
        respondError(w, err, http.StatusBadRequest)
        return
    }

    created := createUser(user)
    w.WriteHeader(http.StatusCreated)
    json.NewEncoder(w).Encode(created)
}).Methods("POST")
```

## Common Patterns

### 1. Standard API Response

```go
type APIResponse struct {
    Success bool        `json:"success"`
    Data    interface{} `json:"data,omitempty"`
    Error   string      `json:"error,omitempty"`
}

func respondJSON(w http.ResponseWriter, data interface{}, status int) {
    w.Header().Set("Content-Type", "application/json")
    w.WriteHeader(status)
    json.NewEncoder(w).Encode(APIResponse{
        Success: status < 400,
        Data:    data,
    })
}

func respondError(w http.ResponseWriter, message string, status int) {
    w.Header().Set("Content-Type", "application/json")
    w.WriteHeader(status)
    json.NewEncoder(w).Encode(APIResponse{
        Success: false,
        Error:   message,
    })
}
```

### 2. CRUD Operations

```go
// GET /api/users
func listUsers(w http.ResponseWriter, r *http.Request) {
    users := db.GetAllUsers()
    respondJSON(w, users, http.StatusOK)
}

// GET /api/users/{id}
func getUser(w http.ResponseWriter, r *http.Request) {
    id := mux.Vars(r)["id"]
    user, err := db.GetUser(id)
    if err != nil {
        respondError(w, "User not found", http.StatusNotFound)
        return
    }
    respondJSON(w, user, http.StatusOK)
}

// POST /api/users
func createUser(w http.ResponseWriter, r *http.Request) {
    var user User
    if err := json.NewDecoder(r.Body).Decode(&user); err != nil {
        respondError(w, "Invalid JSON", http.StatusBadRequest)
        return
    }

    created, err := db.CreateUser(user)
    if err != nil {
        respondError(w, err.Error(), http.StatusInternalServerError)
        return
    }

    respondJSON(w, created, http.StatusCreated)
}

// PUT /api/users/{id}
func updateUser(w http.ResponseWriter, r *http.Request) {
    id := mux.Vars(r)["id"]
    var user User

    if err := json.NewDecoder(r.Body).Decode(&user); err != nil {
        respondError(w, "Invalid JSON", http.StatusBadRequest)
        return
    }

    updated, err := db.UpdateUser(id, user)
    if err != nil {
        respondError(w, err.Error(), http.StatusInternalServerError)
        return
    }

    respondJSON(w, updated, http.StatusOK)
}

// DELETE /api/users/{id}
func deleteUser(w http.ResponseWriter, r *http.Request) {
    id := mux.Vars(r)["id"]

    if err := db.DeleteUser(id); err != nil {
        respondError(w, err.Error(), http.StatusInternalServerError)
        return
    }

    w.WriteHeader(http.StatusNoContent)
}
```

### 3. Pagination

```go
type PaginatedResponse struct {
    Data       interface{} `json:"data"`
    Page       int         `json:"page"`
    PerPage    int         `json:"per_page"`
    TotalPages int         `json:"total_pages"`
    TotalItems int         `json:"total_items"`
}

func listUsers(w http.ResponseWriter, r *http.Request) {
    page := getQueryInt(r, "page", 1)
    perPage := getQueryInt(r, "per_page", 20)

    users, total := db.GetUsersPaginated(page, perPage)

    response := PaginatedResponse{
        Data:       users,
        Page:       page,
        PerPage:    perPage,
        TotalItems: total,
        TotalPages: (total + perPage - 1) / perPage,
    }

    respondJSON(w, response, http.StatusOK)
}
```

### 4. Validation

```go
import "github.com/go-playground/validator/v10"

type CreateUserRequest struct {
    Name  string `json:"name" validate:"required,min=2,max=50"`
    Email string `json:"email" validate:"required,email"`
    Age   int    `json:"age" validate:"gte=0,lte=120"`
}

var validate = validator.New()

func createUser(w http.ResponseWriter, r *http.Request) {
    var req CreateUserRequest

    if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
        respondError(w, "Invalid JSON", http.StatusBadRequest)
        return
    }

    if err := validate.Struct(req); err != nil {
        respondError(w, err.Error(), http.StatusBadRequest)
        return
    }

    // Create user...
}
```

### 5. API Versioning

```go
// URL versioning
r.PathPrefix("/api/v1").Subrouter()
r.PathPrefix("/api/v2").Subrouter()

// Header versioning
func versionMiddleware(version string) Middleware {
    return func(next http.Handler) http.Handler {
        return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
            apiVersion := r.Header.Get("API-Version")
            if apiVersion != version {
                respondError(w, "Invalid API version", http.StatusBadRequest)
                return
            }
            next.ServeHTTP(w, r)
        })
    }
}
```

## Best Practices

- Use struct tags for JSON mapping
- Return proper HTTP status codes
- Implement consistent error responses
- Validate all input data
- Use pagination for list endpoints
- Version your API from the start
- Include request IDs for tracing
- Implement rate limiting
- Use HTTPS in production

## Next Steps

- Chapter 19: Templates & Views
- Chapter 21: Database/SQL Package
- Chapter 24: Redis & Caching

---

**Key Takeaway**: Go's JSON handling and HTTP support make it excellent for building APIs. Use struct tags for serialization, return proper status codes, and implement consistent patterns for errors and responses.
