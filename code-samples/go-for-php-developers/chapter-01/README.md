# Chapter 01: Setup & Tooling

Learn how to set up a professional Go development environment and master the essential Go tools that make development efficient and enjoyable.

## Overview

Go comes with an excellent set of built-in tools that handle everything from code formatting to dependency management. Coming from PHP, you'll find Go's tooling to be more opinionated but also more consistent.

## Files in This Chapter

### 1. `01-installation-and-setup.md`
**Purpose**: Step-by-step guide to installing Go on different platforms
**Topics**:
- Installing Go on Mac, Linux, Windows
- Setting up GOPATH and GOROOT
- Configuring environment variables
- Verifying installation
- Updating Go versions

### 2. `02-go-modules.go`
**Purpose**: Understanding Go's dependency management system
**Key Concepts**:
- go.mod and go.sum files (like composer.json)
- Creating a new module
- Adding dependencies
- Updating dependencies
- Vendoring
- Semantic versioning

**Run it**:
```bash
go run 02-go-modules.go
```

### 3. `03-essential-commands.go`
**Purpose**: Master the Go command-line tools
**Key Concepts**:
- `go run` - compile and run
- `go build` - compile to binary
- `go test` - run tests
- `go fmt` - format code
- `go vet` - check for suspicious code
- `go mod` - manage dependencies
- `go install` - install binaries
- `go get` - add dependencies

**Run it**:
```bash
go run 03-essential-commands.go
```

### 4. `04-code-formatting.go`
**Purpose**: Automatic code formatting with gofmt and goimports
**Key Concepts**:
- gofmt - official formatter
- goimports - add/remove imports automatically
- No style debates (one true way)
- Editor integration
- Pre-commit hooks

**Run it**:
```bash
# Format this file
gofmt -w 04-code-formatting.go

# Or with imports
goimports -w 04-code-formatting.go
```

## Quick Reference: PHP Tools vs Go Tools

### Dependency Management

**PHP (Composer)**:
```bash
composer init
composer require package/name
composer install
composer update
```

**Go (Go Modules)**:
```bash
go mod init module-name
go get package-url
go mod download
go get -u all
```

### Code Formatting

**PHP (PHP-CS-Fixer, PSR-12)**:
```bash
php-cs-fixer fix src/
# Multiple formatters, different standards
```

**Go (gofmt - built-in)**:
```bash
gofmt -w .
# One formatter, one standard, no configuration
```

### Static Analysis

**PHP (PHPStan, Psalm)**:
```bash
phpstan analyze src/
psalm
```

**Go (go vet - built-in)**:
```bash
go vet ./...
staticcheck ./...
golangci-lint run
```

### Running Code

**PHP**:
```bash
php script.php
# Needs PHP runtime
```

**Go**:
```bash
go run main.go        # Quick run
go build -o app main.go  # Build binary
./app                 # Run binary (no Go needed!)
```

## Essential Commands Reference

### Project Initialization
```bash
# Create new module (like composer init)
go mod init github.com/username/project

# Initialize with current directory name
go mod init $(basename $(pwd))
```

### Dependency Management
```bash
# Add dependency (automatically updates go.mod)
go get github.com/gin-gonic/gin

# Add specific version
go get github.com/gin-gonic/gin@v1.9.0

# Update all dependencies
go get -u ./...

# Remove unused dependencies
go mod tidy

# Download dependencies without building
go mod download

# Create vendor directory
go mod vendor
```

### Building and Running
```bash
# Run without building binary
go run main.go

# Build binary (current platform)
go build -o myapp

# Build for Linux (from Mac/Windows)
GOOS=linux GOARCH=amd64 go build -o myapp-linux

# Build for Windows (from Mac/Linux)
GOOS=windows GOARCH=amd64 go build -o myapp.exe

# Build with optimizations (smaller binary)
go build -ldflags="-s -w" -o myapp
```

### Code Quality
```bash
# Format all code (like php-cs-fixer)
gofmt -w .

# Format with import management
goimports -w .

# Check for issues (like phpstan)
go vet ./...

# Run tests
go test ./...

# Run tests with coverage
go test -cover ./...

# Generate coverage report
go test -coverprofile=coverage.out ./...
go tool cover -html=coverage.out
```

### Code Organization
```bash
# List all packages
go list ./...

# Show dependencies
go mod graph

# Why is package needed?
go mod why github.com/some/package

# Check for vulnerabilities
go run golang.org/x/vuln/cmd/govulncheck@latest ./...
```

## Go Workspace Structure

### Typical Project Layout
```
myproject/
├── go.mod              # Dependencies (like composer.json)
├── go.sum              # Lock file (like composer.lock)
├── main.go             # Entry point
├── cmd/                # Command-line apps
│   └── myapp/
│       └── main.go
├── internal/           # Private code (not importable)
│   ├── handlers/
│   └── models/
├── pkg/                # Public libraries
│   └── utils/
├── api/                # API definitions
├── web/                # Web assets
├── configs/            # Configuration files
├── scripts/            # Build/deploy scripts
├── test/               # Integration tests
└── docs/               # Documentation
```

### Comparison with PHP
```
PHP Project:
myproject/
├── composer.json       → go.mod
├── composer.lock       → go.sum
├── vendor/            → downloaded automatically
├── src/               → internal/ or pkg/
├── public/            → web/
├── config/            → configs/
├── tests/             → test/ or *_test.go files
└── index.php          → main.go
```

## Editor Setup

### VS Code (Recommended)
```json
{
  "go.useLanguageServer": true,
  "go.formatTool": "goimports",
  "editor.formatOnSave": true,
  "go.lintTool": "golangci-lint",
  "go.lintOnSave": "workspace"
}
```

**Extensions**:
- Go (official) - golang.go
- Go Test Explorer
- Go Template Support

### GoLand/IntelliJ IDEA
- Built-in Go support
- Excellent refactoring tools
- Integrated debugger

### Vim/Neovim
- vim-go plugin
- Built-in LSP support (Neovim)
- gopls language server

## Linting and Static Analysis

### golangci-lint (Recommended)
```bash
# Install
go install github.com/golangci/golangci-lint/cmd/golangci-lint@latest

# Run
golangci-lint run

# Run with all linters
golangci-lint run --enable-all

# Configuration file: .golangci.yml
```

### Other Tools
```bash
# Static analysis
go install honnef.co/go/tools/cmd/staticcheck@latest
staticcheck ./...

# Security check
go install github.com/securego/gosec/v2/cmd/gosec@latest
gosec ./...

# Detect ineffectual assignments
go install github.com/gordonklaus/ineffassign@latest
ineffassign ./...
```

## Common Issues for PHP Developers

### 1. GOPATH Confusion
**Problem**: Old tutorials mention GOPATH
**Solution**: Use Go modules (post Go 1.11), ignore GOPATH for new projects

### 2. Import Paths
**Problem**: Imports look like URLs
**Solution**: They are! Go fetches from git repositories
```go
import "github.com/gin-gonic/gin"  // Actually fetches from GitHub
```

### 3. No Central Package Repository
**PHP**: Packagist (central repository)
**Go**: Decentralized (direct from git repos)

### 4. Case Sensitivity in Exports
**Problem**: Capitalization matters
```go
type user struct { }  // unexported (private)
type User struct { }  // exported (public)
```

## Best Practices

### 1. Always Use go fmt
```bash
# Format before committing
go fmt ./...

# Or use goimports (includes go fmt)
goimports -w .
```

### 2. Run go vet Regularly
```bash
# Check for common mistakes
go vet ./...
```

### 3. Keep Dependencies Updated
```bash
# Update all dependencies
go get -u ./...

# Clean up
go mod tidy
```

### 4. Use go mod vendor for Docker
```bash
# Create vendor directory
go mod vendor

# Build using vendor
go build -mod=vendor
```

### 5. Version Your Builds
```bash
# Build with version info
go build -ldflags="-X main.Version=$(git describe --tags)"
```

## Next Steps

After mastering the tools:
1. **Chapter 02**: Dive deep into Go's syntax and type system
2. **Chapter 03**: Learn control structures and functions
3. **Chapter 04**: Master arrays, slices, and maps

## Resources

- [Go Modules Reference](https://go.dev/ref/mod)
- [Go Command Documentation](https://pkg.go.dev/cmd/go)
- [Effective Go](https://go.dev/doc/effective_go)
- [Go Tooling in Action](https://www.youtube.com/watch?v=uBjoTxosSys)

---

**Remember**: Go's tooling is opinionated and consistent. Embrace it! No more debates about code style or formatters.
