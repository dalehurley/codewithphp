package main

import "fmt"

/*
GO MODULES - DEPENDENCY MANAGEMENT

PHP Equivalent:
- composer.json = go.mod
- composer.lock = go.sum
- vendor/ = downloaded to GOPATH/pkg/mod (cached globally)

Key Commands:
- composer init     → go mod init [module-name]
- composer require  → go get [package]
- composer install  → go mod download
- composer update   → go get -u [package]
- composer dump     → go mod tidy

Go modules is built into Go 1.11+, no separate tool needed!
*/

func main() {
	fmt.Println("=== Go Modules Introduction ===\n")

	// This file demonstrates module concepts
	// In a real project, you would:

	fmt.Println("Step 1: Initialize a module")
	fmt.Println("  $ go mod init github.com/username/projectname")
	fmt.Println("  Creates: go.mod file\n")

	fmt.Println("Step 2: Add dependencies")
	fmt.Println("  $ go get github.com/gin-gonic/gin")
	fmt.Println("  Updates: go.mod and go.sum\n")

	fmt.Println("Step 3: Import in code")
	fmt.Println(`  import "github.com/gin-gonic/gin"`)
	fmt.Println("  Go automatically downloads on build\n")

	fmt.Println("Step 4: Clean up")
	fmt.Println("  $ go mod tidy")
	fmt.Println("  Removes unused dependencies\n")

	// Example go.mod file
	fmt.Println("=== Example go.mod file ===")
	exampleGoMod := `
module github.com/username/myproject

go 1.21

require (
    github.com/gin-gonic/gin v1.9.1
    github.com/lib/pq v1.10.9
)

require (
    // Indirect dependencies auto-added
    github.com/gin-contrib/sse v0.1.0 // indirect
    github.com/go-playground/validator/v10 v10.14.0 // indirect
)
`
	fmt.Println(exampleGoMod)

	// Comparison with PHP
	fmt.Println("\n=== PHP composer.json equivalent ===")
	exampleComposer := `
{
    "name": "username/myproject",
    "require": {
        "php": "^8.1",
        "guzzlehttp/guzzle": "^7.0",
        "monolog/monolog": "^3.0"
    },
    "autoload": {
        "psr-4": {
            "App\\": "src/"
        }
    }
}
`
	fmt.Println(exampleComposer)

	// Key differences
	fmt.Println("\n=== Key Differences ===")
	differences := []struct {
		aspect   string
		php      string
		golang   string
	}{
		{"Package registry", "Packagist (central)", "Git repos (decentralized)"},
		{"Versioning", "Semantic versioning", "Semantic versioning + commit hash"},
		{"Lock file", "composer.lock", "go.sum"},
		{"Install location", "vendor/", "GOPATH/pkg/mod (global cache)"},
		{"Autoloading", "Configured in composer.json", "Automatic via imports"},
		{"Update one package", "composer update package/name", "go get -u package/name"},
		{"Update all", "composer update", "go get -u ./..."},
	}

	for _, d := range differences {
		fmt.Printf("%-20s | PHP: %-30s | Go: %s\n", d.aspect, d.php, d.golang)
	}

	// Common commands
	fmt.Println("\n=== Common Commands Comparison ===")
	commands := []struct {
		task    string
		php     string
		golang  string
	}{
		{"Initialize", "composer init", "go mod init module-name"},
		{"Add dependency", "composer require vendor/package", "go get github.com/user/package"},
		{"Install deps", "composer install", "go mod download"},
		{"Update deps", "composer update", "go get -u ./..."},
		{"Remove unused", "composer dump-autoload", "go mod tidy"},
		{"List deps", "composer show", "go list -m all"},
		{"Why is X needed?", "composer why vendor/package", "go mod why package"},
		{"Check updates", "composer outdated", "go list -u -m all"},
	}

	for _, c := range commands {
		fmt.Printf("%-20s | %-40s | %s\n", c.task, c.php, c.golang)
	}

	// Best practices
	fmt.Println("\n=== Best Practices ===")
	practices := []string{
		"✅ Always commit go.mod and go.sum",
		"✅ Run 'go mod tidy' before committing",
		"✅ Use semantic versioning for your modules",
		"✅ Vendor dependencies for production: go mod vendor",
		"✅ Use replace directive for local development",
		"✅ Keep dependencies minimal and updated",
		"✅ Review go.sum changes in PRs",
	}

	for _, p := range practices {
		fmt.Println(p)
	}

	fmt.Println("\n=== Try It Yourself ===")
	fmt.Println("1. Create a new directory: mkdir myapp && cd myapp")
	fmt.Println("2. Initialize module: go mod init myapp")
	fmt.Println("3. Create main.go with imports")
	fmt.Println("4. Run: go mod tidy")
	fmt.Println("5. Build: go build")
	fmt.Println("\nDependencies will be automatically downloaded on first build!")
}

/*
ADDITIONAL NOTES:

1. MODULE PATH:
   - Should be the repository path (e.g., github.com/user/repo)
   - Used for imports by other projects
   - Can be any unique identifier for internal projects

2. VERSION SELECTION:
   - go get package@v1.2.3  (specific version)
   - go get package@latest  (latest release)
   - go get package@commit  (specific commit)

3. REPLACE DIRECTIVE (for local development):
   replace github.com/user/package => ../local-package

4. VENDORING:
   - go mod vendor (creates vendor/ directory)
   - go build -mod=vendor (use vendor instead of downloading)

5. PRIVATE REPOSITORIES:
   - Set GOPRIVATE="github.com/yourcompany/*"
   - Configure git credentials

6. UPGRADE STRATEGIES:
   - go get -u package  (upgrade to latest minor/patch)
   - go get -u=patch package  (upgrade patch only)
   - go get package@v2.0.0  (upgrade to specific version)

For more: go help mod
*/
