package main

import (
	"fmt"
	"os"
	"runtime"
)

/*
ESSENTIAL GO COMMANDS

This file demonstrates the most important Go CLI commands
that every PHP developer needs to know when transitioning to Go.

PHP equivalent tools are shown for comparison.
*/

func main() {
	fmt.Println("=== Essential Go Commands for PHP Developers ===\n")

	// Show current Go environment
	showEnvironment()

	// Command reference
	showCommandReference()

	// Workflow examples
	showWorkflowExamples()

	// Build options
	showBuildOptions()

	// Testing commands
	showTestingCommands()
}

func showEnvironment() {
	fmt.Println("=== Your Go Environment ===")
	fmt.Printf("Go Version: %s\n", runtime.Version())
	fmt.Printf("OS/Arch: %s/%s\n", runtime.GOOS, runtime.GOARCH)
	fmt.Printf("GOROOT: %s\n", runtime.GOROOT())

	gopath := os.Getenv("GOPATH")
	if gopath == "" {
		gopath = "not set (using default)"
	}
	fmt.Printf("GOPATH: %s\n", gopath)
	fmt.Println()
}

func showCommandReference() {
	fmt.Println("=== Command Reference (PHP → Go) ===\n")

	commands := []struct {
		category string
		phpCmd   string
		goCmd    string
		purpose  string
	}{
		// Running code
		{"Run", "php script.php", "go run main.go", "Execute without building"},
		{"Run", "php script.php", "go run .", "Run package in current dir"},

		// Building
		{"Build", "n/a", "go build", "Compile to binary (current platform)"},
		{"Build", "n/a", "go build -o myapp", "Compile with custom name"},
		{"Build", "n/a", "GOOS=linux go build", "Cross-compile for Linux"},

		// Dependencies
		{"Deps", "composer install", "go mod download", "Download dependencies"},
		{"Deps", "composer require pkg", "go get package", "Add dependency"},
		{"Deps", "composer update", "go get -u ./...", "Update all deps"},
		{"Deps", "composer dump-autoload", "go mod tidy", "Clean up deps"},

		// Code Quality
		{"Format", "php-cs-fixer fix", "gofmt -w .", "Format code"},
		{"Format", "n/a", "goimports -w .", "Format + fix imports"},
		{"Lint", "phpstan analyze", "go vet ./...", "Static analysis"},
		{"Lint", "psalm", "golangci-lint run", "Comprehensive linting"},

		// Testing
		{"Test", "phpunit", "go test ./...", "Run tests"},
		{"Test", "phpunit --coverage", "go test -cover ./...", "Test with coverage"},
		{"Test", "n/a", "go test -bench=.", "Run benchmarks"},

		// Documentation
		{"Docs", "phpdoc", "go doc package", "View package docs"},
		{"Docs", "n/a", "godoc -http=:6060", "Local doc server"},

		// Installation
		{"Install", "composer global require", "go install package@latest", "Install binary"},
	}

	currentCategory := ""
	for _, cmd := range commands {
		if cmd.category != currentCategory {
			fmt.Printf("\n%s Commands:\n", cmd.category)
			currentCategory = cmd.category
		}
		fmt.Printf("  %-25s → %-30s # %s\n", cmd.phpCmd, cmd.goCmd, cmd.purpose)
	}
	fmt.Println()
}

func showWorkflowExamples() {
	fmt.Println("=== Common Workflows ===\n")

	workflows := []struct {
		name  string
		steps []string
	}{
		{
			name: "Starting a New Project",
			steps: []string{
				"mkdir myproject && cd myproject",
				"go mod init github.com/username/myproject",
				"touch main.go",
				"# Write code...",
				"go run .",
			},
		},
		{
			name: "Adding a Dependency",
			steps: []string{
				"# Import package in code first",
				"go mod tidy  # Downloads and adds to go.mod",
				"# Or explicitly:",
				"go get github.com/gin-gonic/gin",
			},
		},
		{
			name: "Before Committing",
			steps: []string{
				"gofmt -w .          # Format code",
				"go vet ./...        # Check for issues",
				"go test ./...       # Run tests",
				"go mod tidy         # Clean dependencies",
				"git add .",
				"git commit -m 'message'",
			},
		},
		{
			name: "Building for Production",
			steps: []string{
				"# Build optimized binary",
				"go build -ldflags='-s -w' -o app",
				"# Or for Linux (from Mac/Windows)",
				"GOOS=linux GOARCH=amd64 go build -o app-linux",
				"# Test the binary",
				"./app",
			},
		},
		{
			name: "Running Tests",
			steps: []string{
				"go test ./...                    # All tests",
				"go test -v ./...                 # Verbose",
				"go test -cover ./...             # With coverage",
				"go test -race ./...              # Race detection",
				"go test -bench=. ./...           # Benchmarks",
			},
		},
	}

	for _, wf := range workflows {
		fmt.Printf("%s:\n", wf.name)
		for _, step := range wf.steps {
			fmt.Printf("  %s\n", step)
		}
		fmt.Println()
	}
}

func showBuildOptions() {
	fmt.Println("=== Build Options ===\n")

	options := []struct {
		command     string
		description string
	}{
		{"go build", "Build binary for current platform"},
		{"go build -o myapp", "Custom binary name"},
		{"go build -v", "Verbose (show packages being built)"},
		{"go build -race", "Enable race detector"},
		{"go build -ldflags='-s -w'", "Strip debug info (smaller binary)"},
		{"go build -tags=prod", "Build with tags (conditional compilation)"},
		{"GOOS=linux GOARCH=amd64 go build", "Cross-compile for Linux"},
		{"GOOS=windows GOARCH=amd64 go build", "Cross-compile for Windows"},
		{"GOOS=darwin GOARCH=amd64 go build", "Cross-compile for macOS"},
		{"CGO_ENABLED=0 go build", "Disable CGO (fully static binary)"},
	}

	for _, opt := range options {
		fmt.Printf("%-45s # %s\n", opt.command, opt.description)
	}
	fmt.Println()

	// Platform targets
	fmt.Println("Common GOOS/GOARCH combinations:")
	targets := []struct {
		os   string
		arch string
		desc string
	}{
		{"linux", "amd64", "Linux 64-bit (most servers)"},
		{"linux", "arm64", "Linux ARM (Raspberry Pi, AWS Graviton)"},
		{"darwin", "amd64", "macOS Intel"},
		{"darwin", "arm64", "macOS Apple Silicon (M1/M2)"},
		{"windows", "amd64", "Windows 64-bit"},
		{"windows", "386", "Windows 32-bit"},
	}

	for _, t := range targets {
		fmt.Printf("  GOOS=%-7s GOARCH=%-6s # %s\n", t.os, t.arch, t.desc)
	}
	fmt.Println()
}

func showTestingCommands() {
	fmt.Println("=== Testing Commands ===\n")

	testCmds := []struct {
		command     string
		description string
	}{
		{"go test", "Run tests in current package"},
		{"go test ./...", "Run all tests recursively"},
		{"go test -v", "Verbose output"},
		{"go test -run TestName", "Run specific test"},
		{"go test -cover", "Show coverage percentage"},
		{"go test -coverprofile=coverage.out", "Generate coverage file"},
		{"go tool cover -html=coverage.out", "View coverage in browser"},
		{"go test -bench=.", "Run benchmarks"},
		{"go test -bench=. -benchmem", "Benchmarks with memory stats"},
		{"go test -race", "Run with race detector"},
		{"go test -timeout 30s", "Set test timeout"},
		{"go test -short", "Skip long-running tests"},
		{"go test -count=1", "Disable test caching"},
	}

	for _, cmd := range testCmds {
		fmt.Printf("%-45s # %s\n", cmd.command, cmd.description)
	}
	fmt.Println()
}

/*
QUICK REFERENCE CARD:

Daily Use:
  go run .           # Quick run during development
  go build           # Create binary
  go test ./...      # Run tests
  gofmt -w .         # Format code
  go mod tidy        # Clean dependencies

Debugging:
  go run -race .     # Check for race conditions
  go vet ./...       # Find suspicious code
  go test -v         # Verbose test output

Production:
  go build -ldflags='-s -w' -o app
  GOOS=linux go build
  go test -race ./...

Get Help:
  go help            # General help
  go help build      # Help for specific command
  go help packages   # Package patterns
  go doc fmt         # Package documentation

Environment:
  go env             # Show all Go environment variables
  go version         # Show Go version
  go env GOPATH      # Show GOPATH
  go env GOOS        # Show target OS

TIPS FOR PHP DEVELOPERS:

1. No separate tools needed:
   - Formatting: gofmt (built-in)
   - Testing: go test (built-in)
   - Docs: go doc (built-in)
   - Linting: go vet (built-in)

2. Everything is fast:
   - Compile time: Usually <1 second
   - Test execution: Usually <1 second
   - Format: Instant

3. Cross-compilation is trivial:
   - Just set GOOS and GOARCH
   - No Docker needed for different platforms

4. Binaries are self-contained:
   - No runtime dependencies
   - Just copy and run

5. The Go tool is your friend:
   - Learn it well
   - It does everything you need
   - Very few external tools required

For comprehensive list: go help
*/
