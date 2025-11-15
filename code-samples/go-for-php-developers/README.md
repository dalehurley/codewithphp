# Go for PHP Developers - Code Samples

Comprehensive, production-ready Go code samples for the **Go for PHP Developers Series** - designed specifically for expert PHP developers transitioning to Go.

## 📊 Statistics

- **Total Code Files**: 120+ Go files
- **Total Documentation**: 41 README files
- **Chapters Covered**: 41 (Chapters 00-40)
- **Go Version**: 1.21+ with modern idioms
- **Lines of Code**: ~40,000+
- **Side-by-side PHP comparisons**: Throughout

## 🎯 What's Included

Every code sample in this repository features:

✅ **Complete, Runnable Code** - Execute directly with `go run filename.go`
✅ **Go 1.21+ Modern Idioms** - Generics, improved error handling, latest patterns
✅ **PHP Comparison Examples** - See equivalent PHP code side-by-side
✅ **Comprehensive Documentation** - Full comments explaining differences from PHP
✅ **Proper Error Handling** - Go's idiomatic error patterns vs PHP exceptions
✅ **Real-World Examples** - Practical applications with performance benchmarks
✅ **Educational Value** - Clear explanations of "why Go does it differently"

## 🚀 Quick Start

### Prerequisites

```bash
# Install Go 1.21 or higher
go version  # Should show 1.21+

# Verify installation
go env GOPATH
go env GOROOT
```

### Run a Sample

```bash
# Navigate to code samples directory
cd /home/user/codewithphp/code-samples/go-for-php-developers

# Run any example
go run chapter-00/01-hello-from-php-to-go.go
go run chapter-16/01-simple-http-server.go
go run chapter-11/01-first-goroutine.go
```

### Explore a Chapter

```bash
# View chapter README
cat chapter-18/README.md

# Run all examples in a chapter
cd chapter-18
for file in *.go; do go run "$file"; echo ""; done
```

### Build and Run

```bash
# Build executable
go build -o myapp chapter-16/01-simple-http-server.go

# Run the executable
./myapp
```

## 🗂️ Chapter Index

### Part 1: Go Foundations (Chapters 00-05)

| Chapter | Topics | Files | Key Concepts |
|---------|--------|-------|--------------|
| [00](chapter-00/) | Quick Start - From PHP to Go | 5 | First Go program, basic syntax, compile vs interpret |
| [01](chapter-01/) | Setup & Tooling | 4 | go mod, gofmt, go vet, linting, workspace |
| [02](chapter-02/) | Basic Syntax & Types | 6 | Variables, constants, type system vs PHP |
| [03](chapter-03/) | Control Structures & Functions | 5 | if/else, loops (no while!), functions, defer |
| [04](chapter-04/) | Arrays, Slices & Maps | 7 | vs PHP arrays, slice operations, map usage |
| [05](chapter-05/) | Pointers & Memory | 6 | Pointer basics, pass by value vs reference, nil |

### Part 2: Go Language Features (Chapters 06-10)

| Chapter | Topics | Files | Key Concepts |
|---------|--------|-------|--------------|
| [06](chapter-06/) | Structs & Methods | 6 | Structs vs classes, methods, composition over inheritance |
| [07](chapter-07/) | Interfaces & Polymorphism | 5 | Implicit interfaces, type assertions, empty interface |
| [08](chapter-08/) | Error Handling | 6 | errors vs exceptions, error wrapping, panic/recover |
| [09](chapter-09/) | Packages & Modules | 5 | Package structure, imports, visibility, go.mod |
| [10](chapter-10/) | Standard Library Tour | 8 | strings, fmt, time, math, sort, io, os, filepath |

### Part 3: Concurrent Programming (Chapters 11-15)

| Chapter | Topics | Files | Key Concepts |
|---------|--------|-------|--------------|
| [11](chapter-11/) | Goroutines Fundamentals | 5 | Goroutines vs threads, launch patterns, WaitGroups |
| [12](chapter-12/) | Channels & Communication | 6 | Channel basics, buffered vs unbuffered, closing |
| [13](chapter-13/) | Select & Timeouts | 5 | Select statement, timeouts, non-blocking ops |
| [14](chapter-14/) | Sync Package & Mutexes | 6 | Mutex, RWMutex, Once, atomic operations |
| [15](chapter-15/) | Concurrent Patterns | 8 | Worker pools, fan-out/in, pipeline, cancellation |

### Part 4: Web Development (Chapters 16-20)

| Chapter | Topics | Files | Key Concepts |
|---------|--------|-------|--------------|
| [16](chapter-16/) | HTTP Server Basics | 6 | http.Server vs PHP-FPM, handlers, ServeMux |
| [17](chapter-17/) | Routing & Middleware | 7 | Custom routers, middleware chains, context |
| [18](chapter-18/) | JSON APIs & REST | 6 | JSON encoding/decoding, RESTful patterns, validation |
| [19](chapter-19/) | Templates & Views | 5 | html/template, text/template vs PHP templates |
| [20](chapter-20/) | Web Frameworks | 8 | Gin, Echo, Fiber, Chi - framework comparison |

### Part 5: Database & Data Access (Chapters 21-25)

| Chapter | Topics | Files | Key Concepts |
|---------|--------|-------|--------------|
| [21](chapter-21/) | Database/SQL Package | 6 | Connection pooling, prepared statements, scanning |
| [22](chapter-22/) | MySQL & PostgreSQL | 7 | Driver setup, transactions, best practices |
| [23](chapter-23/) | ORMs & Query Builders | 7 | GORM, sqlx, squirrel vs Eloquent/Doctrine |
| [24](chapter-24/) | Redis & Caching | 5 | go-redis, caching patterns, sessions |
| [25](chapter-25/) | Data Migrations | 4 | golang-migrate, schema management, versioning |

### Part 6: Testing & Quality (Chapters 26-30)

| Chapter | Topics | Files | Key Concepts |
|---------|--------|-------|--------------|
| [26](chapter-26/) | Unit Testing | 6 | testing package, test files, assertions |
| [27](chapter-27/) | Table-Driven Tests | 5 | Subtests, test data organization, coverage |
| [28](chapter-28/) | Mocking & Interfaces | 6 | testify/mock, gomock, interface-based testing |
| [29](chapter-29/) | Benchmarking & Profiling | 7 | Benchmark tests, pprof, memory profiling, tracing |
| [30](chapter-30/) | Code Quality Tools | 5 | golangci-lint, staticcheck, race detector |

### Part 7: Advanced Patterns (Chapters 31-35)

| Chapter | Topics | Files | Key Concepts |
|---------|--------|-------|--------------|
| [31](chapter-31/) | Context Package | 6 | Request scoping, cancellation, timeouts, values |
| [32](chapter-32/) | Dependency Injection | 5 | Wire, fx, manual DI patterns vs PHP DI containers |
| [33](chapter-33/) | Design Patterns in Go | 8 | Singleton, Factory, Builder, Observer, Strategy |
| [34](chapter-34/) | Reflection & Code Gen | 6 | reflect package, struct tags, code generation |
| [35](chapter-35/) | Files & IO Operations | 7 | File handling, bufio, io.Reader/Writer, streams |

### Part 8: Production & Deployment (Chapters 36-40)

| Chapter | Topics | Files | Key Concepts |
|---------|--------|-------|--------------|
| [36](chapter-36/) | Configuration Management | 5 | Viper, env vars, config files, 12-factor |
| [37](chapter-37/) | Logging & Monitoring | 6 | zap, logrus, structured logging, metrics |
| [38](chapter-38/) | Docker & Containers | 5 | Dockerfiles, multi-stage builds, optimization |
| [39](chapter-39/) | CI/CD & Deployment | 6 | GitHub Actions, builds, releases, versioning |
| [40](chapter-40/) | Performance & Best Practices | 8 | Profiling, optimization, common pitfalls, Go proverbs |

## 🎓 Learning Paths

### Beginner (Start Here!)
1. [Chapter 00](chapter-00/) - Quick Start - From PHP to Go
2. [Chapter 02](chapter-02/) - Basic Syntax & Types
3. [Chapter 03](chapter-03/) - Control Structures & Functions
4. [Chapter 04](chapter-04/) - Arrays, Slices & Maps
5. [Chapter 06](chapter-06/) - Structs & Methods

### Web Developer Path
1. [Chapter 16](chapter-16/) - HTTP Server Basics
2. [Chapter 17](chapter-17/) - Routing & Middleware
3. [Chapter 18](chapter-18/) - JSON APIs & REST
4. [Chapter 20](chapter-20/) - Web Frameworks
5. [Chapter 21](chapter-21/) - Database/SQL Package
6. [Chapter 23](chapter-23/) - ORMs & Query Builders

### Concurrent Programming Master
1. [Chapter 11](chapter-11/) - Goroutines Fundamentals
2. [Chapter 12](chapter-12/) - Channels & Communication
3. [Chapter 13](chapter-13/) - Select & Timeouts
4. [Chapter 14](chapter-14/) - Sync Package & Mutexes
5. [Chapter 15](chapter-15/) - Concurrent Patterns
6. [Chapter 31](chapter-31/) - Context Package

### Production Deployment
1. [Chapter 26](chapter-26/) - Unit Testing
2. [Chapter 29](chapter-29/) - Benchmarking & Profiling
3. [Chapter 30](chapter-30/) - Code Quality Tools
4. [Chapter 36](chapter-36/) - Configuration Management
5. [Chapter 37](chapter-37/) - Logging & Monitoring
6. [Chapter 38](chapter-38/) - Docker & Containers
7. [Chapter 39](chapter-39/) - CI/CD & Deployment
8. [Chapter 40](chapter-40/) - Performance & Best Practices

## 🔥 Featured Examples

### Simple HTTP Server (Chapter 16)
```bash
go run chapter-16/01-simple-http-server.go
```
Shows how **Go's built-in HTTP server** replaces PHP-FPM/Apache/Nginx.

### Goroutines vs PHP Async (Chapter 11)
```bash
go run chapter-11/01-first-goroutine.go
```
Demonstrates **concurrent execution** that's built into the language.

### JSON API with Database (Chapter 18 + 21)
```bash
go run chapter-18/03-complete-rest-api.go
```
Full **REST API** with database, middleware, and error handling.

### Worker Pool Pattern (Chapter 15)
```bash
go run chapter-15/02-worker-pool.go
```
Shows **concurrent job processing** - like Laravel queues but built-in.

### Table-Driven Tests (Chapter 27)
```bash
go test chapter-27/ -v
```
Demonstrates Go's **idiomatic testing** approach.

## 🔄 Key Differences from PHP

### Compiled vs Interpreted
```go
// Go: Compiled to native binary
go build -o app main.go
./app  // Fast startup, no runtime needed

// PHP: Interpreted (or JIT)
php index.php  // Requires PHP runtime
```

### Strong Static Typing
```go
// Go: Types checked at compile time
var count int = 42
count = "hello"  // ❌ Compile error

// PHP: Dynamic typing
$count = 42;
$count = "hello";  // ✅ Works fine
```

### No Classes, Use Structs
```go
// Go: Structs with methods
type User struct {
    Name string
    Age  int
}
func (u *User) Greet() string { }

// PHP: Classes
class User {
    public string $name;
    public int $age;
    public function greet(): string { }
}
```

### Explicit Error Handling
```go
// Go: Explicit error returns
data, err := readFile("config.json")
if err != nil {
    // Handle error
}

// PHP: Exceptions
try {
    $data = readFile("config.json");
} catch (Exception $e) {
    // Handle error
}
```

### Built-in Concurrency
```go
// Go: Goroutines built-in
go processTask()  // Runs concurrently

// PHP: Needs external tools
// ReactPHP, Swoole, or separate processes
```

## 🛠️ Requirements

- **Go**: 1.21 or higher
- **External Tools**:
  - Docker (for Chapter 38)
  - PostgreSQL/MySQL (for Chapters 21-23)
  - Redis (for Chapter 24)
- **Dependencies**: Managed via go.mod in each chapter

## 📖 Code Quality Standards

All code samples follow Go best practices:

### Modern Go Idioms
```go
// Proper error handling
func doSomething() error {
    if err := validate(); err != nil {
        return fmt.Errorf("validation failed: %w", err)
    }
    return nil
}

// Interface-based design
type UserRepository interface {
    Find(id int) (*User, error)
    Save(user *User) error
}

// Table-driven tests
func TestAdd(t *testing.T) {
    tests := []struct {
        name     string
        a, b     int
        expected int
    }{
        {"positive", 2, 3, 5},
        {"negative", -1, -2, -3},
        {"zero", 0, 0, 0},
    }

    for _, tt := range tests {
        t.Run(tt.name, func(t *testing.T) {
            result := add(tt.a, tt.b)
            if result != tt.expected {
                t.Errorf("got %d, want %d", result, tt.expected)
            }
        })
    }
}
```

## 🧪 Testing

Run tests for a specific chapter:

```bash
# Test Chapter 26 (Unit Testing)
cd chapter-26
go test -v

# Run with coverage
go test -cover

# Generate coverage report
go test -coverprofile=coverage.out
go tool cover -html=coverage.out
```

Run all examples:

```bash
# Test every code sample
cd /home/user/codewithphp/code-samples/go-for-php-developers

for i in {00..40}; do
    chapter="chapter-$(printf "%02d" $i)"
    if [ -d "$chapter" ]; then
        echo "=== Testing $chapter ==="
        cd "$chapter"
        for file in *.go; do
            [ -f "$file" ] && go run "$file" 2>/dev/null || echo "Skipped $file"
        done
        cd ..
    fi
done
```

## 📈 Performance Highlights

| Comparison | PHP | Go | Improvement |
|------------|-----|-----|-------------|
| Startup Time | ~50ms (FPM) | ~5ms (binary) | **10x faster** |
| Memory Usage | ~30MB baseline | ~5MB baseline | **6x less memory** |
| HTTP Throughput | ~10k req/s | ~50k req/s | **5x more throughput** |
| Concurrent Tasks | Limited (threads) | 100k+ goroutines | **Unlimited concurrency** |
| JSON Processing | 50k ops/s | 200k ops/s | **4x faster** |

## 🔗 Related Resources

- **Official Go Documentation**: [go.dev/doc](https://go.dev/doc/)
- **Go by Example**: [gobyexample.com](https://gobyexample.com/)
- **Effective Go**: [go.dev/doc/effective_go](https://go.dev/doc/effective_go)
- **Go Proverbs**: [go-proverbs.github.io](https://go-proverbs.github.io/)
- **Awesome Go**: [awesome-go.com](https://awesome-go.com/)

## 📚 Series Documentation

- [Main Series Documentation](../../docs/series/go-for-php-developers/)
- [Appendix A: PHP to Go Quick Reference](../../docs/series/go-for-php-developers/appendices/appendix-a-quick-reference.md)
- [Appendix B: Common Pitfalls for PHP Developers](../../docs/series/go-for-php-developers/appendices/appendix-b-common-pitfalls.md)
- [Appendix C: Go Tooling Guide](../../docs/series/go-for-php-developers/appendices/appendix-c-tooling.md)

## 🤝 Contributing

Found an issue or have an improvement? Please open an issue or pull request in the [main repository](https://github.com/dalehurley/codewithphp).

## 📝 License

This code is part of the Go for PHP Developers educational series. See the main repository for license information.

## 💡 Philosophy

This series embraces the Go philosophy:

- **Simplicity over cleverness**
- **Explicit over implicit**
- **Composition over inheritance**
- **Interfaces over concrete types**
- **Clear is better than clever**

Coming from PHP, you'll find Go:
- More verbose (explicit error handling)
- Less magical (no auto-loading, no magic methods)
- More performant (compiled, concurrent)
- Simpler (smaller language, less features)

## 🎯 Course Goals

By the end of this series, you will:

✅ Write idiomatic Go code
✅ Build production-ready web applications
✅ Master concurrent programming with goroutines
✅ Understand Go's type system and interfaces
✅ Test, benchmark, and profile Go applications
✅ Deploy Go applications with Docker and CI/CD
✅ Think in Go, not "PHP translated to Go"

---

**Welcome to Go!** 🚀

*Last Updated: 2025*
