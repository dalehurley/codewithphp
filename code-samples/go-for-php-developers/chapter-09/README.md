# Chapter 09: Packages & Modules

Master Go's package system and module management. Learn how to organize code, manage dependencies, and create reusable packages - Go's approach to what PHP does with Composer and namespaces.

## Overview

Go's package system is simpler than PHP's autoloading and namespaces. Every directory is a package, imports are explicit, and Go modules replace Composer. Understanding packages is crucial for organizing larger applications.

## Files in This Chapter

### 1. `01-package-basics.go`
**Topics**: Package declaration, main vs library packages, package naming

### 2. `02-imports.go`
**Topics**: Import statements, import aliases, dot imports, blank imports

### 3. `03-exported-unexported.go`
**Topics**: Public/private via capitalization, package visibility

### 4. `04-internal-packages.go`
**Topics**: Internal packages, restricting access across packages

### 5. `05-init-functions.go`
**Topics**: init() functions, package initialization order

### 6. `06-go-modules.go`
**Topics**: go.mod, go.sum, dependency management, versioning

## Quick Reference

### Package Declaration

**PHP**:
```php
<?php
namespace App\Services\User;

use App\Database\Connection;
use App\Models\User as UserModel;

class UserService {
    private Connection $db;

    public function __construct(Connection $db) {
        $this->db = $db;
    }
}
```

**Go**:
```go
package user

import (
    "database/sql"
    "myapp/models"
)

type Service struct {
    db *sql.DB
}

func NewService(db *sql.DB) *Service {
    return &Service{db: db}
}
```

### Importing

**PHP**:
```php
// Composer autoload
require __DIR__ . '/vendor/autoload.php';

// Use statements
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Symfony\Component\HttpFoundation\Request;

// Aliasing
use Some\Long\Namespace\ClassName as ShortName;
```

**Go**:
```go
import (
    "fmt"                           // Standard library
    "github.com/user/repo"          // External package
    "myapp/internal/database"       // Internal package

    log "github.com/sirupsen/logrus" // Aliased import
    . "fmt"                          // Dot import (avoid!)
    _ "github.com/go-sql-driver/mysql"  // Blank import
)
```

## Key Concepts

### 1. Package Declaration

```go
// Every Go file starts with package declaration
package main  // Executable package

package user  // Library package

package utils  // Library package

// Package name should match directory name
// Directory: myapp/services/user/
// Package: package user
```

### 2. main Package

```go
// main package creates executable
package main

import "fmt"

func main() {
    fmt.Println("Hello, World!")
}

// Only main package can have main() function
// go build creates executable from main package
```

### 3. Library Packages

```go
// mathutil/math.go
package mathutil

// Exported (public) - starts with capital
func Add(a, b int) int {
    return a + b
}

// Unexported (private) - starts with lowercase
func subtract(a, b int) int {
    return a - b
}

// Usage from another package
import "myapp/mathutil"

sum := mathutil.Add(5, 3)      // ✅ Works
diff := mathutil.subtract(5, 3) // ❌ Won't compile!
```

### 4. Import Statements

```go
// Single import
import "fmt"

// Multiple imports
import (
    "fmt"
    "os"
    "strings"
)

// Import with alias
import (
    log "github.com/sirupsen/logrus"
    stdlog "log"  // Disambiguate
)

// Dot import (import into current namespace)
import . "fmt"

Println("No fmt. prefix")  // Not recommended!

// Blank import (for side effects only)
import _ "github.com/go-sql-driver/mysql"
```

### 5. Exported vs Unexported

```go
package user

// Exported (public)
type User struct {
    ID   int     // Exported field
    Name string  // Exported field
    age  int     // Unexported field
}

// Exported function
func NewUser(name string, age int) *User {
    return &User{Name: name, age: age}
}

// Exported method
func (u *User) GetAge() int {
    return u.age
}

// Unexported function
func validateAge(age int) bool {
    return age >= 0 && age < 150
}

// From another package:
import "myapp/user"

u := user.NewUser("Alice", 30)  // ✅ Works
fmt.Println(u.Name)              // ✅ Works
fmt.Println(u.age)               // ❌ Won't compile!
user.validateAge(30)             // ❌ Won't compile!
```

### 6. Package Initialization

```go
package database

import "database/sql"

var db *sql.DB

// init runs automatically before main()
func init() {
    var err error
    db, err = sql.Open("mysql", "user:pass@/dbname")
    if err != nil {
        panic(err)
    }
}

// Multiple init functions execute in order
func init() {
    fmt.Println("First init")
}

func init() {
    fmt.Println("Second init")
}
```

## Go Modules

### 1. Creating a Module

```bash
# Initialize new module
go mod init github.com/username/myapp

# Creates go.mod file
```

### 2. go.mod File

```go
module github.com/username/myapp

go 1.21

require (
    github.com/gorilla/mux v1.8.0
    github.com/lib/pq v1.10.9
)

require (
    github.com/gorilla/websocket v1.5.0 // indirect
)

replace github.com/old/package => github.com/new/package v1.0.0
```

### 3. Adding Dependencies

```bash
# Add dependency (auto-updates go.mod)
go get github.com/gorilla/mux

# Specific version
go get github.com/gorilla/mux@v1.8.0

# Latest version
go get -u github.com/gorilla/mux

# Download all dependencies
go mod download

# Clean up unused dependencies
go mod tidy
```

### 4. go.sum File

```
github.com/gorilla/mux v1.8.0 h1:i40aqfkR1h2SlN9hojwV5ZA91wcXFOvkdNIeFDP5koI=
github.com/gorilla/mux v1.8.0/go.mod h1:DVbg23sWSpFRCP0SfiEN6jmj59UnW/n46BH5rLB71So=
```

## Common Patterns

### 1. Package Organization

```
myapp/
├── go.mod
├── go.sum
├── main.go
├── config/
│   └── config.go
├── models/
│   ├── user.go
│   └── post.go
├── services/
│   ├── user/
│   │   ├── service.go
│   │   └── repository.go
│   └── auth/
│       └── service.go
├── handlers/
│   ├── user.go
│   └── post.go
└── internal/
    └── database/
        └── connection.go
```

### 2. Internal Packages

```go
// internal packages can only be imported by nearby code
myapp/
├── internal/
│   └── helpers/
│       └── util.go    // package helpers
└── services/
    └── user/
        └── service.go

// ✅ Can import from services/user/
import "myapp/internal/helpers"

// ❌ Can't import from outside myapp/
import "github.com/someone/theirapp/internal/helpers"
```

### 3. Package-Level Variables

```go
package config

import "time"

var (
    // Exported
    AppName    = "MyApp"
    Version    = "1.0.0"
    Timeout    = 30 * time.Second

    // Unexported
    secretKey  = "secret"
    debugMode  = false
)

// Getters for unexported
func SecretKey() string {
    return secretKey
}

func IsDebug() bool {
    return debugMode
}
```

### 4. Constructor Pattern

```go
package database

import "database/sql"

type DB struct {
    conn *sql.DB
}

// Constructor (exported)
func New(dsn string) (*DB, error) {
    conn, err := sql.Open("mysql", dsn)
    if err != nil {
        return nil, err
    }
    return &DB{conn: conn}, nil
}

// Alternative names
func NewDB(dsn string) (*DB, error) { }
func NewConnection(dsn string) (*DB, error) { }

// From other packages
import "myapp/database"

db, err := database.New(dsn)
```

### 5. Option Pattern for Packages

```go
package server

type Server struct {
    host    string
    port    int
    timeout time.Duration
}

type Option func(*Server)

func WithHost(host string) Option {
    return func(s *Server) {
        s.host = host
    }
}

func WithPort(port int) Option {
    return func(s *Server) {
        s.port = port
    }
}

func New(opts ...Option) *Server {
    s := &Server{
        host:    "localhost",
        port:    8080,
        timeout: 30 * time.Second,
    }
    for _, opt := range opts {
        opt(s)
    }
    return s
}

// Usage
import "myapp/server"

srv := server.New(
    server.WithHost("0.0.0.0"),
    server.WithPort(3000),
)
```

### 6. Circular Import Prevention

```go
// ❌ Circular import (won't compile)
// package a imports package b
// package b imports package a

// ✅ Solution 1: Extract common interface
// common/interfaces.go
package common

type UserStore interface {
    GetUser(id int) (*User, error)
}

// services/user/user.go
package user
import "myapp/common"

type Service struct {
    store common.UserStore
}

// ✅ Solution 2: Use dependency injection
// Pass dependencies as parameters instead of importing
```

## Best Practices

### 1. One Package Per Directory

```
// ✅ Good
myapp/
├── user/
│   ├── user.go      (package user)
│   └── service.go   (package user)
└── post/
    └── post.go      (package post)

// ❌ Bad - multiple packages in one directory
myapp/
├── user.go          (package user)
└── service.go       (package service)  // ❌
```

### 2. Keep Packages Focused

```go
// ✅ Good - focused package
package validator

func ValidateEmail(email string) bool { }
func ValidateURL(url string) bool { }
func ValidatePhone(phone string) bool { }

// ❌ Bad - too many responsibilities
package utils

func ValidateEmail(email string) bool { }
func FormatDate(t time.Time) string { }
func ParseJSON(data []byte) (map[string]interface{}, error) { }
func HashPassword(password string) string { }
```

### 3. Descriptive Package Names

```go
// ✅ Good names
package user
package database
package validator
package http

// ❌ Bad names
package utils
package helpers
package common
package misc
```

### 4. Avoid Dot Imports

```go
// ❌ Bad - pollutes namespace
import . "fmt"

Println("Hello")  // Where does this come from?

// ✅ Good - explicit
import "fmt"

fmt.Println("Hello")  // Clear origin
```

### 5. Group Related Imports

```go
import (
    // Standard library
    "fmt"
    "net/http"
    "time"

    // External dependencies
    "github.com/gorilla/mux"
    "github.com/lib/pq"

    // Internal packages
    "myapp/config"
    "myapp/models"
)
```

### 6. Use internal/ for Private Packages

```go
// Packages in internal/ can't be imported by external projects
myapp/
├── internal/
│   ├── auth/       // Only myapp can import
│   └── crypto/     // Only myapp can import
└── public/
    └── api/        // Anyone can import
```

## Common Mistakes

### 1. Circular Imports

```go
// package a
import "myapp/b"

// package b
import "myapp/a"  // ❌ Circular import!

// ✅ Solution: Extract interface to third package
```

### 2. Package Name Doesn't Match Directory

```go
// Directory: myapp/services/user/
package userservice  // ❌ Should be "user"

// ✅ Correct
package user
```

### 3. Importing Unused Packages

```go
import (
    "fmt"
    "os"   // ❌ Unused import (won't compile)
)

// ✅ Use blank import if needed for side effects
import _ "os"
```

### 4. Stuttering Package Names

```go
// package user
type UserService struct {}  // ❌ Redundant

// Usage
user.UserService{}  // user.User...

// ✅ Better
type Service struct {}

// Usage
user.Service{}  // Clear and concise
```

### 5. Too Many Small Packages

```go
// ❌ Over-engineered
myapp/
├── add/
│   └── add.go
├── subtract/
│   └── subtract.go
└── multiply/
    └── multiply.go

// ✅ Group related functionality
myapp/
└── math/
    ├── arithmetic.go
    └── geometry.go
```

## Module Management

### 1. Version Selection

```bash
# Get specific version
go get github.com/gorilla/mux@v1.8.0

# Get latest
go get github.com/gorilla/mux@latest

# Get specific commit
go get github.com/gorilla/mux@abc123

# Get specific branch
go get github.com/gorilla/mux@master
```

### 2. Replace Directive

```go
// In go.mod
replace github.com/old/repo => github.com/new/repo v1.0.0

// Replace with local path (for development)
replace github.com/myorg/mylib => ../mylib
```

### 3. Vendor Directory

```bash
# Create vendor directory
go mod vendor

# Build using vendor
go build -mod=vendor

# Structure
myapp/
├── go.mod
├── go.sum
└── vendor/
    ├── modules.txt
    └── github.com/
        └── gorilla/
            └── mux/
```

### 4. Module Commands

```bash
# Initialize module
go mod init github.com/user/repo

# Add missing dependencies
go mod tidy

# Download dependencies
go mod download

# Verify dependencies
go mod verify

# List modules
go list -m all

# Show why a package is needed
go mod why github.com/pkg/errors

# Show module graph
go mod graph
```

## Package Documentation

### 1. Package-Level Comments

```go
// Package user provides user management functionality.
//
// This package includes user creation, authentication,
// and profile management.
//
// Example:
//
//     srv := user.NewService(db)
//     user, err := srv.Create("alice@example.com")
//
package user

import "database/sql"
```

### 2. Generating Documentation

```bash
# View package docs
go doc user

# View specific function
go doc user.NewService

# Start documentation server
godoc -http=:6060

# View at http://localhost:6060/pkg/
```

## Comparison with PHP

| Feature | PHP | Go |
|---------|-----|-----|
| Package/Namespace | `namespace App\Services` | `package services` |
| Autoloading | Composer autoload | Built-in imports |
| Dependencies | composer.json | go.mod |
| Lock file | composer.lock | go.sum |
| Install deps | `composer install` | `go mod download` |
| Add dependency | `composer require` | `go get` |
| Update deps | `composer update` | `go get -u` |
| Visibility | public/private/protected | Capitalization |
| Use statement | `use App\Models\User` | `import "app/models"` |
| Aliasing | `use X as Y` | `import y "x"` |
| Initialization | Constructors | init() functions |

## Advanced Patterns

### 1. Plugin Architecture

```go
// Define interface in main app
package plugin

type Plugin interface {
    Name() string
    Execute() error
}

// Plugins implement interface
package myplugin

import "mainapp/plugin"

type MyPlugin struct{}

func (p *MyPlugin) Name() string {
    return "MyPlugin"
}

func (p *MyPlugin) Execute() error {
    return nil
}

// Register plugin
func init() {
    plugin.Register(&MyPlugin{})
}
```

### 2. Build Tags

```go
// +build linux

package myapp

// This file only compiles on Linux

// +build !windows

// This file compiles on all platforms except Windows
```

### 3. Multiple Modules in One Repo

```
repo/
├── go.mod            (root module)
├── moduleA/
│   ├── go.mod        (separate module)
│   └── a.go
└── moduleB/
    ├── go.mod        (separate module)
    └── b.go
```

## Next Steps

- **Chapter 10**: Standard Library Tour - Exploring important stdlib packages
- **Chapter 16**: HTTP Server Basics - Building web applications with packages
- **Chapter 32**: Dependency Injection - Advanced package patterns

---

**Key Takeaway**: Go's package system is simple but powerful. One package per directory, exported via capitalization, and modules for dependency management. Think of packages as your building blocks and modules as your dependency manager - simpler than PHP's namespaces and Composer, but just as effective.
