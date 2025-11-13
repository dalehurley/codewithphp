---
title: "08: Composer & Dependencies"
description: "Master Composer, PHP's dependency manager similar to Maven/Gradle"
series: "php-for-java-developers"
chapter: 8
order: 8
difficulty: "Intermediate"
prerequisites:
  - "/series/php-for-java-developers/chapters/07-error-handling"
---

![Composer Hero](/images/php-for-java-developers/chapter-08-composer-hero-full.webp)

# Chapter 8: Composer & Dependencies

<Badge type="warning">Intermediate</Badge> <Badge type="info">75-90 min</Badge>

## Overview

Composer is PHP's dependency manager—think Maven or Gradle for Java. It handles package installation, autoloading, version management, and dependency resolution. Modern PHP development is unthinkable without Composer. In this chapter, you'll learn everything you need to master dependency management in PHP.

## Prerequisites

::: info Time Estimate
⏱️ **75-90 minutes** to complete this chapter
:::

**What you need:**
- Completed [Chapter 7: Error Handling](/series/php-for-java-developers/chapters/07-error-handling)
- Understanding of Java dependency management (Maven/Gradle)
- Command line familiarity

## What You'll Build

In this chapter, you'll:
- Set up a complete Composer project from scratch
- Manage dependencies with version constraints
- Create publishable packages
- Configure advanced autoloading
- Build reproducible deployments

## Learning Objectives

By the end of this chapter, you'll be able to:

- **Install and configure** Composer
- **Manage dependencies** with composer.json
- **Understand version constraints** and semantic versioning
- **Use autoloading** (PSR-4, classmap, files)
- **Work with lock files** for reproducible builds
- **Create and publish** packages
- **Compare Composer** to Maven/Gradle
- **Use common packages** from the PHP ecosystem

---

## Section 1: Introduction to Composer

### Goal

Understand what Composer is and how it compares to Java build tools.

### What is Composer?

Composer is two things:

1. **Dependency Manager**: Downloads and manages third-party libraries
2. **Autoloader Generator**: Creates optimized class autoloading

::: code-group

```json [composer.json (PHP)]
{
    "name": "mycompany/myapp",
    "require": {
        "php": ">=8.3",
        "monolog/monolog": "^3.0",
        "guzzlehttp/guzzle": "^7.8"
    },
    "autoload": {
        "psr-4": {
            "App\\": "src/"
        }
    }
}
```

```xml [pom.xml (Maven)]
<project>
    <groupId>com.mycompany</groupId>
    <artifactId>myapp</artifactId>
    <version>1.0.0</version>

    <properties>
        <maven.compiler.source>17</maven.compiler.source>
    </properties>

    <dependencies>
        <dependency>
            <groupId>org.apache.logging.log4j</groupId>
            <artifactId>log4j-core</artifactId>
            <version>2.20.0</version>
        </dependency>
        <dependency>
            <groupId>com.squareup.okhttp3</groupId>
            <artifactId>okhttp</artifactId>
            <version>4.12.0</version>
        </dependency>
    </dependencies>
</project>
```

```gradle [build.gradle (Gradle)]
plugins {
    id 'java'
}

group = 'com.mycompany'
version = '1.0.0'

java {
    sourceCompatibility = JavaVersion.VERSION_17
}

dependencies {
    implementation 'org.apache.logging.log4j:log4j-core:2.20.0'
    implementation 'com.squareup.okhttp3:okhttp:4.12.0'
}
```

:::

### Composer vs Maven/Gradle

| Feature | Composer | Maven | Gradle |
|---------|----------|-------|--------|
| **Configuration** | composer.json | pom.xml | build.gradle |
| **Lock file** | composer.lock | N/A (uses ranges) | gradle.lock |
| **Repository** | Packagist.org | Maven Central | Maven Central + more |
| **Scope** | Dependencies + Autoloading | Full build lifecycle | Full build lifecycle |
| **Speed** | Fast | Slow | Fast |
| **Build tasks** | Limited (scripts only) | Extensive (phases) | Extensive (tasks) |

::: tip Key Difference
Composer focuses on **dependency management and autoloading**, not full build lifecycle. For build tasks, PHP developers use separate tools (PHPUnit, PHPStan, etc.) or build scripts.
:::

---

## Section 2: Installing and Configuring Composer

### Goal

Install Composer and set up your first project.

### Installation

::: code-group

```bash [Linux/macOS]
# Download installer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"

# Verify installer (optional but recommended)
php -r "if (hash_file('sha384', 'composer-setup.php') === file_get_contents('https://composer.github.io/installer.sig')) { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"

# Install globally
php composer-setup.php --install-dir=/usr/local/bin --filename=composer

# Cleanup
php -r "unlink('composer-setup.php');"

# Verify installation
composer --version
```

```bash [Windows]
# Download and run Composer-Setup.exe from https://getcomposer.org/download/

# Or via PowerShell:
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php --install-dir=bin --filename=composer.bat
php -r "unlink('composer-setup.php');"

# Verify
composer --version
```

```bash [Docker]
# Use official Composer image
docker run --rm -v $(pwd):/app composer:latest install

# Or add to Dockerfile
FROM php:8.3-cli
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
```

:::

### Creating Your First Project

```bash
# Create a new directory
mkdir my-php-app && cd my-php-app

# Initialize composer.json interactively
composer init

# Or create manually
cat > composer.json <<'EOF'
{
    "name": "mycompany/my-app",
    "description": "My PHP Application",
    "type": "project",
    "require": {
        "php": ">=8.3"
    },
    "autoload": {
        "psr-4": {
            "App\\": "src/"
        }
    }
}
EOF

# Install dependencies (creates vendor/ directory)
composer install
```

### Directory Structure After Installation

```
my-php-app/
├── composer.json       # Dependencies and configuration
├── composer.lock       # Locked versions (commit this!)
├── vendor/             # Downloaded packages (don't commit)
│   ├── autoload.php   # Include this in your application
│   ├── composer/      # Composer-generated files
│   └── ...            # Installed packages
└── src/               # Your application code
    └── ...
```

::: warning Important
- **DO commit**: composer.json, composer.lock
- **DON'T commit**: vendor/ directory
- Add `vendor/` to `.gitignore`
:::

---

## Section 3: composer.json Deep Dive

### Goal

Master the composer.json configuration file.

### Complete composer.json Example

```json
{
    "name": "mycompany/my-app",
    "description": "A sample PHP application",
    "type": "project",
    "license": "MIT",
    "authors": [
        {
            "name": "John Doe",
            "email": "john@example.com",
            "homepage": "https://example.com",
            "role": "Developer"
        }
    ],
    "require": {
        "php": ">=8.3",
        "ext-pdo": "*",
        "ext-json": "*",
        "monolog/monolog": "^3.0",
        "guzzlehttp/guzzle": "^7.8"
    },
    "require-dev": {
        "phpunit/phpunit": "^10.5",
        "phpstan/phpstan": "^1.10"
    },
    "autoload": {
        "psr-4": {
            "App\\": "src/",
            "Database\\": "database/"
        },
        "classmap": ["legacy/"],
        "files": ["helpers/functions.php"]
    },
    "autoload-dev": {
        "psr-4": {
            "Tests\\": "tests/"
        }
    },
    "scripts": {
        "test": "phpunit",
        "analyse": "phpstan analyse src",
        "post-install-cmd": [
            "@php artisan optimize"
        ]
    },
    "config": {
        "optimize-autoloader": true,
        "preferred-install": "dist",
        "sort-packages": true,
        "allow-plugins": {
            "php-http/discovery": true
        }
    },
    "minimum-stability": "stable",
    "prefer-stable": true
}
```

### Key Sections Explained

**1. Package Information**

```json
{
    "name": "vendor/package",  // Required for libraries, optional for projects
    "description": "...",       // Short description
    "type": "project",          // project, library, metapackage, etc.
    "license": "MIT"            // SPDX license identifier
}
```

**2. Dependencies**

```json
{
    "require": {
        "php": ">=8.3",               // PHP version constraint
        "ext-pdo": "*",               // PHP extension requirement
        "monolog/monolog": "^3.0"     // Package with version constraint
    },
    "require-dev": {
        "phpunit/phpunit": "^10.5"    // Development-only dependencies
    }
}
```

**3. Autoloading**

```json
{
    "autoload": {
        "psr-4": {
            "App\\": "src/"           // PSR-4 namespace mapping
        },
        "classmap": ["legacy/"],      // Scan directories for classes
        "files": ["helpers.php"]      // Always include these files
    }
}
```

**4. Scripts**

```json
{
    "scripts": {
        "test": "phpunit",            // Custom command
        "post-install-cmd": [         // Hook after composer install
            "@php artisan optimize"
        ]
    }
}
```

---

## Section 4: Managing Dependencies

### Goal

Learn to add, update, and remove packages.

### Adding Dependencies

```bash
# Add production dependency
composer require monolog/monolog

# Add with specific version
composer require monolog/monolog:^3.0

# Add multiple packages
composer require guzzlehttp/guzzle symfony/http-foundation

# Add development dependency
composer require --dev phpunit/phpunit

# Add with constraint
composer require "symfony/console:^6.4"
```

### Updating Dependencies

```bash
# Update all packages
composer update

# Update specific package
composer update monolog/monolog

# Update multiple packages
composer update monolog/monolog guzzlehttp/guzzle

# Update without updating dependencies
composer update --no-dependencies monolog/monolog

# Dry run (see what would be updated)
composer update --dry-run

# Update with platform requirements
composer update --with-all-dependencies
```

### Removing Dependencies

```bash
# Remove package
composer remove monolog/monolog

# Remove dev package
composer remove --dev phpunit/phpunit

# Remove multiple packages
composer remove package1 package2
```

### Searching and Inspecting

```bash
# Search for packages
composer search monolog

# Show package information
composer show monolog/monolog

# Show all installed packages
composer show

# Show installed packages tree
composer show --tree

# Show outdated packages
composer outdated

# Show why package is installed
composer depends monolog/monolog

# Show what packages depend on this
composer prohibits php:8.4
```

---

## Section 5: Version Constraints & Semantic Versioning

### Goal

Master version constraints for reliable dependency management.

### Semantic Versioning (SemVer)

PHP packages follow semantic versioning: `MAJOR.MINOR.PATCH`

- **MAJOR**: Breaking changes (incompatible API changes)
- **MINOR**: New features (backward-compatible)
- **PATCH**: Bug fixes (backward-compatible)

Example: `3.5.2`
- Major: 3
- Minor: 5
- Patch: 2

### Version Constraint Operators

::: code-group

```json [Exact Version]
{
    "require": {
        "vendor/package": "1.2.3"  // Only 1.2.3 (not recommended)
    }
}
```

```json [Caret (^) - Recommended]
{
    "require": {
        // Allows: 1.2.3, 1.2.4, 1.3.0, 1.9.9
        // Blocks: 2.0.0 (breaking changes)
        "vendor/package": "^1.2.3"
    }
}
// ^ allows changes that don't modify left-most non-zero digit
```

```json [Tilde (~)]
{
    "require": {
        // Allows: 1.2.3, 1.2.4, 1.2.99
        // Blocks: 1.3.0 (new features)
        "vendor/package": "~1.2.3"
    }
}
// ~ allows last digit to increment
```

```json [Wildcard (*)]
{
    "require": {
        // Allows: 1.2.0, 1.2.1, 1.2.99
        // Blocks: 1.3.0
        "vendor/package": "1.2.*"
    }
}
```

```json [Range]
{
    "require": {
        // Allows versions between 1.2 and 2.0 (exclusive)
        "vendor/package": ">=1.2 <2.0"
    }
}
```

```json [Union]
{
    "require": {
        // Allows either range
        "vendor/package": "^1.2 || ^2.0"
    }
}
```

:::

### Version Constraint Comparison

| Constraint | `1.2.3` | `1.2.4` | `1.3.0` | `2.0.0` | Use Case |
|-----------|---------|---------|---------|---------|----------|
| `1.2.3` | ✅ | ❌ | ❌ | ❌ | Exact version (avoid) |
| `^1.2.3` | ✅ | ✅ | ✅ | ❌ | **Recommended** (safe updates) |
| `~1.2.3` | ✅ | ✅ | ❌ | ❌ | Conservative (patch only) |
| `1.2.*` | ✅ | ✅ | ❌ | ❌ | Wildcard (patch only) |
| `>=1.2.3` | ✅ | ✅ | ✅ | ✅ | Range (risky) |

### Java Comparison

::: code-group

```json [Composer Constraints]
{
    "require": {
        "monolog/monolog": "^3.0"
    }
}
```

```xml [Maven Version Ranges]
<dependency>
    <groupId>org.apache.logging.log4j</groupId>
    <artifactId>log4j-core</artifactId>
    <version>[2.0,3.0)</version>
</dependency>
```

```gradle [Gradle Dynamic Versions]
dependencies {
    implementation 'org.apache.logging.log4j:log4j-core:2.+'
}
```

:::

::: tip Best Practice
- **Production**: Use `^` (caret) for safe automatic updates
- **Library**: Use `^` for maximum compatibility
- **Development**: Use `*` or `^` for latest features
- **Avoid**: Exact versions unless you have a specific reason
:::

---

## Section 6: Lock Files & Reproducible Builds

### Goal

Understand composer.lock and ensure reproducible deployments.

### What is composer.lock?

When you run `composer install`, Composer:
1. Reads `composer.json` for dependencies
2. Resolves exact versions satisfying constraints
3. Writes exact versions to `composer.lock`
4. Downloads and installs packages

### composer.lock Example

```json
{
    "_readme": [
        "This file locks the dependencies to known versions",
        "Always commit this file to version control"
    ],
    "content-hash": "a5c8d5f3d8b4e6f1a2c3d4e5f6a7b8c9",
    "packages": [
        {
            "name": "monolog/monolog",
            "version": "3.5.0",
            "source": {
                "type": "git",
                "url": "https://github.com/Seldaek/monolog.git",
                "reference": "abc123..."
            },
            "require": {
                "php": ">=8.1"
            },
            "time": "2024-01-15T10:30:45+00:00"
        }
    ],
    "packages-dev": [],
    "aliases": [],
    "minimum-stability": "stable",
    "stability-flags": [],
    "prefer-stable": true,
    "prefer-lowest": false,
    "platform": {
        "php": ">=8.3"
    }
}
```

### Install vs Update

```bash
# composer install
# - Reads composer.lock (if exists)
# - Installs exact versions from lock file
# - Creates lock file if missing
# Use: CI/CD, production deployment, new developer setup

composer install

# composer update
# - Ignores composer.lock
# - Resolves latest versions from composer.json constraints
# - Updates composer.lock with new versions
# Use: When you want to update dependencies

composer update
```

### Workflow

::: code-group

```bash [Developer Machine]
# Add new dependency
composer require guzzlehttp/guzzle

# This updates both:
# - composer.json (adds package)
# - composer.lock (locks version)

# Commit both files
git add composer.json composer.lock
git commit -m "Add Guzzle HTTP client"
git push
```

```bash [CI/CD Pipeline]
# Pull latest code (includes composer.lock)
git pull

# Install exact versions from lock file
composer install --no-dev --optimize-autoloader

# Run tests, build, deploy...
```

```bash [Another Developer]
# Pull latest code
git pull

# Install dependencies (uses composer.lock)
composer install

# Now has exact same package versions!
```

:::

### Best Practices

::: tip Lock File Guidelines

**DO:**
- ✅ Commit `composer.lock` to version control
- ✅ Run `composer install` in CI/CD
- ✅ Run `composer install` when pulling code
- ✅ Run `composer update` deliberately
- ✅ Review lock file changes in PRs

**DON'T:**
- ❌ Add `composer.lock` to `.gitignore` (for applications)
- ❌ Run `composer update` in production
- ❌ Manually edit `composer.lock`
- ❌ Ignore lock file conflicts
:::

### Java Comparison

| Tool | PHP | Java (Maven) | Java (Gradle) |
|------|-----|--------------|---------------|
| **Manifest** | composer.json | pom.xml | build.gradle |
| **Lock file** | composer.lock | N/A | gradle.lockfile |
| **Install** | `composer install` | `mvn install` | `gradle build` |
| **Update** | `composer update` | `mvn versions:use-latest-versions` | `gradle dependencies --write-locks` |

---

## Section 7: Autoloading Strategies

### Goal

Master all autoloading strategies in Composer.

### PSR-4 Autoloading (Recommended)

```json
{
    "autoload": {
        "psr-4": {
            "App\\": "src/",
            "App\\Controllers\\": "app/Controllers/",
            "Domain\\": "src/Domain/"
        }
    }
}
```

**Mapping:**
- `App\Models\User` → `src/Models/User.php`
- `App\Controllers\UserController` → `app/Controllers/UserController.php`
- `Domain\User\Entity` → `src/Domain/User/Entity.php`

### Classmap Autoloading

For legacy code or non-PSR-4 structure:

```json
{
    "autoload": {
        "classmap": [
            "legacy/",
            "lib/OldClasses",
            "database/seeds",
            "database/factories"
        ]
    }
}
```

After changing classmap, regenerate:
```bash
composer dump-autoload
```

### Files Autoloading

For helper functions and constants:

```json
{
    "autoload": {
        "files": [
            "helpers/functions.php",
            "config/constants.php"
        ]
    }
}
```

**helpers/functions.php:**
```php
<?php

declare(strict_types=1);

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        return $_ENV[$key] ?? $default;
    }
}

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        // Load configuration
        return $default;
    }
}
```

### Combined Example

```json
{
    "autoload": {
        "psr-4": {
            "App\\": "src/",
            "Database\\": "database/"
        },
        "classmap": [
            "legacy/"
        ],
        "files": [
            "helpers/functions.php"
        ]
    },
    "autoload-dev": {
        "psr-4": {
            "Tests\\": "tests/"
        }
    }
}
```

### Optimization

```bash
# Development: Fast, allows file changes
composer dump-autoload

# Production: Optimized classmap
composer dump-autoload --optimize

# Production: Authoritative (fastest)
composer dump-autoload --classmap-authoritative

# Production: With APCu caching
composer dump-autoload --apcu
```

---

## Section 8: Scripts and Hooks

### Goal

Automate tasks with Composer scripts.

### Defining Scripts

```json
{
    "scripts": {
        "test": "phpunit",
        "test:unit": "phpunit --testsuite=unit",
        "test:integration": "phpunit --testsuite=integration",
        "analyse": "phpstan analyse src",
        "format": "php-cs-fixer fix",
        "check": [
            "@test",
            "@analyse"
        ]
    }
}
```

**Running scripts:**
```bash
composer test
composer analyse
composer check  # Runs multiple commands
```

### Event Hooks

Composer provides hooks for various events:

```json
{
    "scripts": {
        "pre-install-cmd": [
            "echo 'Before composer install'"
        ],
        "post-install-cmd": [
            "@php artisan cache:clear",
            "@php artisan config:cache"
        ],
        "pre-update-cmd": [
            "echo 'Before composer update'"
        ],
        "post-update-cmd": [
            "@php artisan migrate"
        ],
        "post-autoload-dump": [
            "@php artisan package:discover"
        ],
        "post-root-package-install": [
            "@php -r \"file_exists('.env') || copy('.env.example', '.env');\""
        ],
        "post-create-project-cmd": [
            "@php artisan key:generate"
        ]
    }
}
```

### Advanced Script Features

```json
{
    "scripts": {
        "build": [
            "Composer\\Config::disableProcessTimeout",  // Disable timeout
            "@php build.php"
        ],
        "deploy": {
            "script": "deploy.sh",
            "timeout": 600  // 10 minutes
        }
    },
    "scripts-descriptions": {
        "test": "Run the full test suite",
        "build": "Build production assets",
        "deploy": "Deploy to production"
    }
}
```

**Script variables:**
```bash
# Access composer variables in scripts
composer run-script --list  # List all scripts
```

---

## Section 9: Private Packages & Repositories

### Goal

Learn to use private packages and custom repositories.

### Private Repository Configuration

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/mycompany/private-package.git"
        },
        {
            "type": "composer",
            "url": "https://repo.example.com"
        },
        {
            "type": "path",
            "url": "../packages/my-local-package"
        }
    ],
    "require": {
        "mycompany/private-package": "^1.0"
    }
}
```

### Repository Types

**1. VCS Repository (Git)**

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "git@github.com:mycompany/private-lib.git"
        }
    ]
}
```

Requires authentication:
```bash
# GitHub token
composer config github-oauth.github.com YOUR_TOKEN

# GitLab token
composer config gitlab-oauth.gitlab.com YOUR_TOKEN
```

**2. Private Packagist**

```json
{
    "repositories": [
        {
            "type": "composer",
            "url": "https://repo.packagist.com/mycompany/"
        }
    ],
    "config": {
        "http-basic": {
            "repo.packagist.com": {
                "username": "token",
                "password": "YOUR_PACKAGIST_TOKEN"
            }
        }
    }
}
```

**3. Local Path Repository (Monorepo)**

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "./packages/*",
            "options": {
                "symlink": true
            }
        }
    ],
    "require": {
        "mycompany/package-one": "^1.0",
        "mycompany/package-two": "^1.0"
    }
}
```

### Authentication

**auth.json (don't commit!):**
```json
{
    "http-basic": {
        "repo.example.com": {
            "username": "myuser",
            "password": "mypassword"
        }
    },
    "github-oauth": {
        "github.com": "ghp_xxxxxxxxxxxxxxxxxxxx"
    },
    "gitlab-oauth": {
        "gitlab.com": "glpat-xxxxxxxxxxxxxxxxxxxx"
    }
}
```

**Environment variables:**
```bash
export COMPOSER_AUTH='{"github-oauth":{"github.com":"TOKEN"}}'
composer install
```

---

## Section 10: Common Packages & Ecosystem

### Goal

Discover essential packages every PHP developer should know.

### Essential Packages by Category

**1. Logging**

```bash
composer require monolog/monolog
```

```php
<?php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$log = new Logger('app');
$log->pushHandler(new StreamHandler('app.log', Logger::INFO));

$log->info('User logged in', ['user_id' => 42]);
$log->error('Database connection failed', ['error' => $e->getMessage()]);
```

**2. HTTP Client**

```bash
composer require guzzlehttp/guzzle
```

```php
<?php

use GuzzleHttp\Client;

$client = new Client();
$response = $client->get('https://api.example.com/users');

$data = json_decode($response->getBody(), true);
```

**3. Testing**

```bash
composer require --dev phpunit/phpunit
composer require --dev mockery/mockery
```

**4. Static Analysis**

```bash
composer require --dev phpstan/phpstan
composer require --dev vimeo/psalm
```

**5. Code Quality**

```bash
composer require --dev friendsofphp/php-cs-fixer
composer require --dev squizlabs/php_codesniffer
```

**6. Date/Time**

```bash
composer require nesbot/carbon
```

```php
<?php

use Carbon\Carbon;

$now = Carbon::now();
$tomorrow = $now->addDay();
$formatted = $now->format('Y-m-d H:i:s');
$human = $now->diffForHumans();  // "2 minutes ago"
```

**7. Collections**

```bash
composer require illuminate/collections
```

```php
<?php

use Illuminate\Support\Collection;

$collection = collect([1, 2, 3, 4, 5])
    ->filter(fn($n) => $n > 2)
    ->map(fn($n) => $n * 2)
    ->sum();  // 24
```

**8. Validation**

```bash
composer require respect/validation
```

**9. Database (ORM)**

```bash
composer require illuminate/database  # Laravel Eloquent
# or
composer require doctrine/orm  # Doctrine
```

**10. Environment Variables**

```bash
composer require vlucas/phpdotenv
```

```php
<?php

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$dbHost = $_ENV['DB_HOST'];
```

### Framework Ecosystems

**Laravel:**
```bash
composer create-project laravel/laravel my-app
```

**Symfony:**
```bash
composer create-project symfony/skeleton my-app
```

**Slim (Microframework):**
```bash
composer require slim/slim slim/psr7
```

---

## Section 11: Practical Example - Complete Project Setup

### Goal

Build a complete project with dependencies, autoloading, and scripts.

### Project: User Management API

**1. Initialize project**

```bash
mkdir user-api && cd user-api
composer init --name="mycompany/user-api" --no-interaction
```

**2. Complete composer.json**

```json
{
    "name": "mycompany/user-api",
    "description": "User Management REST API",
    "type": "project",
    "license": "MIT",
    "require": {
        "php": ">=8.3",
        "ext-pdo": "*",
        "ext-json": "*",
        "monolog/monolog": "^3.0",
        "guzzlehttp/guzzle": "^7.8",
        "vlucas/phpdotenv": "^5.6",
        "respect/validation": "^2.3"
    },
    "require-dev": {
        "phpunit/phpunit": "^10.5",
        "phpstan/phpstan": "^1.10",
        "friendsofphp/php-cs-fixer": "^3.47"
    },
    "autoload": {
        "psr-4": {
            "App\\": "src/"
        },
        "files": [
            "helpers/functions.php"
        ]
    },
    "autoload-dev": {
        "psr-4": {
            "Tests\\": "tests/"
        }
    },
    "scripts": {
        "test": "phpunit",
        "analyse": "phpstan analyse src --level=9",
        "format": "php-cs-fixer fix",
        "check": [
            "@test",
            "@analyse"
        ],
        "serve": "php -S localhost:8000 -t public"
    },
    "config": {
        "optimize-autoloader": true,
        "preferred-install": "dist",
        "sort-packages": true
    },
    "minimum-stability": "stable",
    "prefer-stable": true
}
```

**3. Install dependencies**

```bash
composer install
```

**4. Directory structure**

```
user-api/
├── composer.json
├── composer.lock
├── vendor/
├── src/
│   ├── Controllers/
│   │   └── UserController.php
│   ├── Models/
│   │   └── User.php
│   ├── Services/
│   │   └── UserService.php
│   └── Database/
│       └── Connection.php
├── public/
│   └── index.php
├── helpers/
│   └── functions.php
├── tests/
│   └── UserServiceTest.php
├── .env.example
└── .gitignore
```

**5. Application code**

```php
<?php
// public/index.php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Controllers\UserController;
use App\Services\UserService;
use App\Database\Connection;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Load environment
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Setup logging
$log = new Logger('api');
$log->pushHandler(new StreamHandler('logs/app.log', Logger::INFO));

// Database connection
$db = Connection::make([
    'host' => env('DB_HOST', 'localhost'),
    'database' => env('DB_NAME'),
    'username' => env('DB_USER'),
    'password' => env('DB_PASS'),
]);

// Services
$userService = new UserService($db, $log);
$controller = new UserController($userService);

// Route handling
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

match ([$method, $path]) {
    ['GET', '/users'] => $controller->index(),
    ['GET', '/users/{id}'] => $controller->show($_GET['id']),
    ['POST', '/users'] => $controller->store($_POST),
    default => http_response_code(404),
};
```

**6. Run the application**

```bash
composer serve
# Visit: http://localhost:8000
```

**7. Run quality checks**

```bash
# Run tests
composer test

# Static analysis
composer analyse

# Format code
composer format

# Run all checks
composer check
```

---

## Exercises

### Exercise 1: Create a Library Package

Create a reusable library package:

**Requirements:**
- Package name: `yourname/string-helper`
- PSR-4 autoloading
- Unit tests with PHPUnit
- Publish to Packagist.org

### Exercise 2: Monorepo Setup

Create a monorepo with multiple packages:

**Requirements:**
- Root composer.json with path repositories
- Three packages: core, api, web
- api and web depend on core
- Demonstrate cross-package usage

### Exercise 3: Custom Scripts

Set up a project with comprehensive scripts:

**Requirements:**
- test, test:unit, test:integration scripts
- analyse (PHPStan), format (CS Fixer) scripts
- pre/post install hooks
- Custom deployment script

---

## Wrap-up Checklist

Before moving to the next chapter, ensure you can:

- [ ] Install and configure Composer
- [ ] Create and configure composer.json
- [ ] Manage dependencies (add, update, remove)
- [ ] Understand version constraints and semantic versioning
- [ ] Use composer.lock for reproducible builds
- [ ] Configure PSR-4, classmap, and files autoloading
- [ ] Create and use scripts and hooks
- [ ] Set up private repositories
- [ ] Use common packages from the ecosystem
- [ ] Compare Composer to Maven/Gradle
- [ ] Build complete projects with Composer

::: tip Ready for More?
In [Chapter 9: Working with Arrays](/series/php-for-java-developers/chapters/09-working-with-arrays), we'll explore PHP's powerful array manipulation functions and how they compare to Java Streams.
:::

---

## Further Reading

**Official Documentation:**
- [Composer Documentation](https://getcomposer.org/doc/)
- [Packagist.org](https://packagist.org/) - Package repository
- [PSR-4 Autoloading](https://www.php-fig.org/psr/psr-4/)

**Tools:**
- [Private Packagist](https://packagist.com/) - Private package hosting
- [Composer Plugin API](https://getcomposer.org/doc/articles/plugins.md)

---

<div style="display: flex; justify-content: space-between; margin-top: 2rem;">
  <div>
    <strong>Previous:</strong> <a href="/series/php-for-java-developers/chapters/07-error-handling">← Chapter 7: Error Handling</a>
  </div>
  <div>
    <strong>Next:</strong> <a href="/series/php-for-java-developers/chapters/09-working-with-arrays">Chapter 9: Working with Arrays →</a>
  </div>
</div>
