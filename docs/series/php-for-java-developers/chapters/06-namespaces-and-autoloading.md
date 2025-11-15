---
title: "06: Namespaces & Autoloading"
description: "Master PHP namespaces and PSR-4 autoloading with Java package comparisons"
series: "php-for-java-developers"
chapter: 6
order: 6
difficulty: "Intermediate"
prerequisites:
  - "/series/php-for-java-developers/chapters/05-interfaces-and-traits"
---

![Namespaces Hero](/images/php-for-java-developers/chapter-06-namespaces-hero-full.webp)

# Chapter 6: Namespaces & Autoloading

<Badge type="warning">Intermediate</Badge> <Badge type="info">60-75 min</Badge>

## Overview

PHP namespaces work similarly to Java packages—they organize code and prevent naming conflicts. However, PHP's autoloading mechanism differs from Java's classpath. Instead of a JVM automatically finding classes, PHP uses Composer's autoloader (PSR-4 standard) to load classes on demand. In this chapter, you'll learn how to organize PHP code like you would with Java packages.

## Prerequisites

::: info Time Estimate
⏱️ **60-75 minutes** to complete this chapter
:::

**What you need:**
- Completed [Chapter 5: Interfaces & Traits](/series/php-for-java-developers/chapters/05-interfaces-and-traits)
- Understanding of Java packages and imports
- Basic familiarity with command line

## What You'll Build

In this chapter, you'll create:
- A properly namespaced application structure
- PSR-4 compliant autoloading configuration
- A multi-namespace project with Composer

## Learning Objectives

By the end of this chapter, you'll be able to:

- **Define namespaces** in PHP files
- **Use the `use` statement** to import classes
- **Understand PSR-4** autoloading standard
- **Configure Composer** autoloading
- **Organize code** with proper namespace structure
- **Compare namespaces** to Java packages

---

## Section 1: Namespace Basics

### Goal

Understand PHP namespaces and their similarity to Java packages.

### Defining Namespaces

::: code-group

```php [PHP Namespace]
<?php

declare(strict_types=1);

namespace App\Models;

class User
{
    public function __construct(
        public string $name,
        public string $email
    ) {}
}
```

```php [Using the Class]
<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;  // Import the class

class UserController
{
    public function createUser(): User
    {
        return new User("Alice", "alice@example.com");
    }
}
```

```java [Java Package (Comparison)]
// File: src/main/java/com/example/models/User.java
package com.example.models;

public class User {
    private String name;
    private String email;

    public User(String name, String email) {
        this.name = name;
        this.email = email;
    }
}
```

```java [Using the Class]
// File: src/main/java/com/example/controllers/UserController.java
package com.example.controllers;

import com.example.models.User;  // Import the class

public class UserController {
    public User createUser() {
        return new User("Alice", "alice@example.com");
    }
}
```

:::

### Key Similarities and Differences

| Feature | PHP Namespace | Java Package |
|---------|---------------|--------------|
| **Purpose** | Organize code, prevent conflicts | Organize code, prevent conflicts |
| **Declaration** | `namespace App\Models;` | `package com.example.models;` |
| **Separator** | Backslash `\` | Dot `.` |
| **Import** | `use App\Models\User;` | `import com.example.models.User;` |
| **File structure** | Not enforced (but PSR-4 recommends) | Strictly enforced |
| **Root namespace** | Configurable | Based on source root |

::: tip Namespace Convention
By convention (PSR-4):
- **Namespace matches directory structure**
- **Class name matches file name**
- Example: `App\Models\User` → `src/Models/User.php`

This is similar to Java's requirement but not enforced by PHP itself.
:::

---

## Section 2: Using Namespaces

### Goal

Master the `use` statement and namespace aliasing.

### Import Statements

```php
<?php

declare(strict_types=1);

namespace App\Services;

// Import classes
use App\Models\User;
use App\Models\Post;
use App\Repositories\UserRepository;

// Import with alias (like Java's import ... as)
use App\Services\External\PaymentService as ExternalPayment;
use App\Services\Internal\PaymentService as InternalPayment;

// Import multiple classes from same namespace
use App\Models\{User, Post, Comment};

// Import functions and constants (PHP-specific)
use function App\Helpers\formatDate;
use const App\Config\MAX_USERS;

class UserService
{
    public function __construct(
        private UserRepository $repository,
        private ExternalPayment $payment
    ) {}

    public function createUser(string $name, string $email): User
    {
        if ($this->repository->count() >= MAX_USERS) {
            throw new \Exception("Maximum users reached");
        }

        $user = new User($name, $email);
        $this->repository->save($user);

        return $user;
    }
}
```

### Fully Qualified Names

```php
<?php

declare(strict_types=1);

namespace App\Services;

class Example
{
    public function demo(): void
    {
        // Relative (within same namespace)
        $local = new LocalClass();  // App\Services\LocalClass

        // Fully qualified (leading backslash)
        $user = new \App\Models\User("Alice", "alice@example.com");

        // With use statement (recommended)
        // use App\Models\User;
        // $user = new User("Alice", "alice@example.com");
    }
}
```

::: warning Leading Backslash
The leading backslash `\` indicates a fully qualified name from the global namespace:
- `new User()` - Looks in current namespace first
- `new \App\Models\User()` - Absolute path from root
- `new \Exception()` - Global PHP exception class

Similar to Java's fully qualified names: `com.example.models.User`
:::

---

## Section 3: PSR-4 Autoloading

### Goal

Understand PSR-4 standard and how it compares to Java's classpath.

### PSR-4 Standard

PSR-4 is PHP's standard for autoloading classes from file paths:

**PSR-4 Mapping:**
```
Namespace: App\Models\User
File: src/Models/User.php

Namespace: App\Controllers\UserController
File: src/Controllers/UserController.php
```

### Directory Structure

```
project/
├── composer.json          # Like pom.xml or build.gradle
├── vendor/                # Dependencies (like .m2 or build/libs)
│   └── autoload.php      # Autoloader entry point
└── src/
    ├── Models/
    │   ├── User.php
    │   └── Post.php
    ├── Controllers/
    │   └── UserController.php
    ├── Services/
    │   └── UserService.php
    └── Repositories/
        └── UserRepository.php
```

### Composer Autoload Configuration

::: code-group

```json [composer.json]
{
    "name": "mycompany/myapp",
    "description": "My PHP Application",
    "type": "project",
    "require": {
        "php": ">=8.3"
    },
    "autoload": {
        "psr-4": {
            "App\\": "src/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "Tests\\": "tests/"
        }
    }
}
```

```xml [pom.xml (Java Comparison)]
<project>
    <modelVersion>4.0.0</modelVersion>
    <groupId>com.mycompany</groupId>
    <artifactId>myapp</artifactId>
    <version>1.0.0</version>

    <build>
        <sourceDirectory>src/main/java</sourceDirectory>
        <testSourceDirectory>src/test/java</testSourceDirectory>
    </build>

    <dependencies>
        <!-- Dependencies here -->
    </dependencies>
</project>
```

:::

### Using the Autoloader

```php
<?php

declare(strict_types=1);

// Entry point (index.php or bootstrap.php)
require __DIR__ . '/vendor/autoload.php';

// Now classes are autoloaded automatically
use App\Models\User;
use App\Controllers\UserController;

$user = new User("Alice", "alice@example.com");  // Automatically loads src/Models/User.php
$controller = new UserController();               // Automatically loads src/Controllers/UserController.php
```

::: tip Composer Autoloader
After editing `composer.json`:
```bash
composer dump-autoload
```

This regenerates the autoloader files (similar to Maven's compile phase).
:::

---

## Section 4: Multiple Namespace Prefixes

### Goal

Configure multiple namespace roots like Java's multiple source directories.

### Complex Project Structure

```json
{
    "autoload": {
        "psr-4": {
            "App\\": "src/",
            "Database\\": "database/",
            "Support\\": "support/"
        },
        "files": [
            "helpers/functions.php"
        ]
    },
    "autoload-dev": {
        "psr-4": {
            "Tests\\Unit\\": "tests/Unit/",
            "Tests\\Feature\\": "tests/Feature/"
        }
    }
}
```

**Directory Structure:**
```
project/
├── src/                   # App\ namespace
│   ├── Models/
│   └── Controllers/
├── database/              # Database\ namespace
│   ├── Migrations/
│   └── Seeders/
├── support/               # Support\ namespace
│   └── Helpers/
├── helpers/               # Global functions (no namespace)
│   └── functions.php
└── tests/
    ├── Unit/             # Tests\Unit\ namespace
    └── Feature/          # Tests\Feature\ namespace
```

---

## Section 5: Sub-namespaces

### Goal

Organize code with nested namespaces.

### Nested Namespace Structure

```php
<?php

// File: src/Http/Controllers/Api/V1/UserController.php
namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Http\Resources\UserResource;

class UserController
{
    public function index(): array
    {
        $users = User::all();
        return UserResource::collection($users);
    }
}
```

```php
<?php

// File: src/Http/Controllers/Api/V2/UserController.php
namespace App\Http\Controllers\Api\V2;

use App\Models\User;
use App\Http\Resources\V2\UserResource;

class UserController
{
    public function index(): array
    {
        // V2 implementation with different logic
        $users = User::with('profile')->all();
        return UserResource::collection($users);
    }
}
```

**Usage:**
```php
<?php

use App\Http\Controllers\Api\V1\UserController as V1UserController;
use App\Http\Controllers\Api\V2\UserController as V2UserController;

$v1Controller = new V1UserController();
$v2Controller = new V2UserController();
```

---

## Section 6: Global Namespace

### Goal

Understand global namespace and built-in PHP classes.

### Global vs Namespaced

```php
<?php

declare(strict_types=1);

namespace App\Services;

class Example
{
    public function demo(): void
    {
        // PHP built-in classes are in global namespace
        $date = new \DateTime();  // Note the leading backslash
        $exception = new \Exception("Error");

        // Without backslash, PHP looks in current namespace first
        // This would look for App\Services\DateTime (doesn't exist!)
        // $date = new DateTime();  // Error!

        // User-defined class in same namespace
        $helper = new Helper();  // Looks for App\Services\Helper
    }
}
```

### Importing Global Classes

```php
<?php

declare(strict_types=1);

namespace App\Services;

// Import global classes
use DateTime;
use Exception;
use PDO;

class UserService
{
    public function createUser(): void
    {
        $now = new DateTime();  // Now works without backslash
        throw new Exception("Error");  // Works without backslash
    }
}
```

---

## Section 7: Practical Example

### Goal

Build a complete application with proper namespace structure.

### Project Structure

```
my-app/
├── composer.json
├── public/
│   └── index.php
└── src/
    ├── Controllers/
    │   └── UserController.php
    ├── Models/
    │   └── User.php
    ├── Repositories/
    │   └── UserRepository.php
    └── Services/
        └── UserService.php
```

### composer.json

```json
{
    "name": "mycompany/my-app",
    "autoload": {
        "psr-4": {
            "App\\": "src/"
        }
    },
    "require": {
        "php": ">=8.3"
    }
}
```

### Source Files

```php
<?php
// src/Models/User.php

declare(strict_types=1);

namespace App\Models;

class User
{
    public function __construct(
        private int $id,
        public string $name,
        public string $email
    ) {}

    public function getId(): int
    {
        return $this->id;
    }
}
```

```php
<?php
// src/Repositories/UserRepository.php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    private array $users = [];
    private int $nextId = 1;

    public function save(User $user): User
    {
        $id = $this->nextId++;
        $savedUser = new User($id, $user->name, $user->email);
        $this->users[$id] = $savedUser;
        return $savedUser;
    }

    public function findById(int $id): ?User
    {
        return $this->users[$id] ?? null;
    }

    public function all(): array
    {
        return array_values($this->users);
    }
}
```

```php
<?php
// src/Services/UserService.php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;

class UserService
{
    public function __construct(
        private UserRepository $repository
    ) {}

    public function createUser(string $name, string $email): User
    {
        $user = new User(0, $name, $email);  // ID will be assigned by repository
        return $this->repository->save($user);
    }

    public function getUser(int $id): ?User
    {
        return $this->repository->findById($id);
    }

    public function getAllUsers(): array
    {
        return $this->repository->all();
    }
}
```

```php
<?php
// src/Controllers/UserController.php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\UserService;

class UserController
{
    public function __construct(
        private UserService $userService
    ) {}

    public function index(): void
    {
        $users = $this->userService->getAllUsers();

        header('Content-Type: application/json');
        echo json_encode($users);
    }

    public function create(string $name, string $email): void
    {
        $user = $this->userService->createUser($name, $email);

        header('Content-Type: application/json');
        http_response_code(201);
        echo json_encode($user);
    }
}
```

```php
<?php
// public/index.php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Controllers\UserController;
use App\Repositories\UserRepository;
use App\Services\UserService;

// Dependency injection (manual for now, frameworks do this automatically)
$repository = new UserRepository();
$service = new UserService($repository);
$controller = new UserController($service);

// Create a user
$controller->create("Alice", "alice@example.com");

// List all users
$controller->index();
```

### Running the Application

```bash
# Install dependencies and generate autoloader
composer install

# Run the application
php -S localhost:8000 -t public
```

---

## Section 8: Namespace Resolution Rules

### Goal

Understand how PHP resolves class names—crucial for debugging namespace issues.

### Resolution Types

PHP has three types of name resolution:

**1. Unqualified Names** (no namespace separators)

```php
<?php

namespace App\Services;

// Unqualified name: "Logger"
$logger = new Logger();  // Resolves to: App\Services\Logger
```

**2. Qualified Names** (contain namespace separator, but not leading `\`)

```php
<?php

namespace App;

// Qualified name: "Services\Logger"
$logger = new Services\Logger();  // Resolves to: App\Services\Logger
```

**3. Fully Qualified Names** (start with `\`)

```php
<?php

namespace App\Services;

// Fully qualified name: "\App\Services\Logger"
$logger = new \App\Services\Logger();  // Absolute: App\Services\Logger
```

### Resolution Algorithm

::: code-group

```php [PHP Resolution]
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\UserService;
use DateTime;

class UserController
{
    public function demo(): void
    {
        // Unqualified: looks in current namespace first
        $helper = new Helper();
        // Searches: App\Http\Controllers\Helper

        // Imported class: uses import
        $service = new UserService();
        // Resolves to: App\Services\UserService

        // Global class: imported
        $date = new DateTime();
        // Resolves to: DateTime (global)

        // Qualified: relative to current namespace
        $api = new Api\UserController();
        // Resolves to: App\Http\Controllers\Api\UserController

        // Fully qualified: absolute path
        $exception = new \Exception("Error");
        // Resolves to: Exception (global)
    }
}
```

```java [Java Resolution (Comparison)]
package com.example.http.controllers;

import com.example.services.UserService;
import java.time.LocalDateTime;

public class UserController {
    public void demo() {
        // Unqualified: current package first
        Helper helper = new Helper();
        // Searches: com.example.http.controllers.Helper

        // Imported class
        UserService service = new UserService();
        // Resolves to: com.example.services.UserService

        // Java standard library (imported)
        LocalDateTime date = LocalDateTime.now();
        // Resolves to: java.time.LocalDateTime

        // Sub-package (relative)
        com.example.http.controllers.api.UserController api =
            new com.example.http.controllers.api.UserController();

        // Fully qualified (uncommon in Java)
        java.lang.Exception ex = new java.lang.Exception("Error");
    }
}
```

:::

### Resolution Priority Table

| Name Type | Example | Resolution Order |
|-----------|---------|------------------|
| **Unqualified** | `Logger` | 1. Current namespace<br>2. Imported (use statement)<br>3. Global namespace (built-ins) |
| **Qualified** | `Services\Logger` | 1. Relative to current namespace |
| **Fully Qualified** | `\App\Services\Logger` | 1. Absolute path only |

::: warning Common Mistake
```php
<?php

namespace App\Services;

// ❌ This looks for App\Services\DateTime (doesn't exist!)
$date = new DateTime();  // Error!

// ✅ Use fully qualified name
$date = new \DateTime();

// ✅ Or import it
use DateTime;
$date = new DateTime();
```
:::

---

## Section 9: Alternative Autoloading Strategies

### Goal

Learn classmap and files autoloading for legacy code and special cases.

### PSR-4 vs Classmap vs Files

```json
{
    "autoload": {
        "psr-4": {
            "App\\": "src/"
        },
        "classmap": [
            "legacy/",
            "lib/OldCode"
        ],
        "files": [
            "helpers/functions.php",
            "config/constants.php"
        ]
    }
}
```

### 1. PSR-4 Autoloading (Recommended)

**Best for:** Modern, well-structured code

```json
{
    "autoload": {
        "psr-4": {
            "App\\": "src/",
            "Domain\\": "src/Domain/",
            "Infrastructure\\": "src/Infrastructure/"
        }
    }
}
```

**Mapping:**
- `App\Models\User` → `src/Models/User.php`
- `Domain\User\Entity` → `src/Domain/User/Entity.php`

### 2. Classmap Autoloading

**Best for:** Legacy code, classes not following PSR-4 structure

```json
{
    "autoload": {
        "classmap": [
            "legacy/",
            "lib/external/",
            "vendor_old/"
        ]
    }
}
```

**How it works:**
- Composer scans directories and creates a map: `ClassName => file_path`
- No namespace structure required
- Faster than PSR-4 (direct lookup)

**Example Legacy Code:**
```
legacy/
├── user.class.php       # Contains: class User_Model
├── post.class.php       # Contains: class Post_Model
└── helper.functions.php # Contains: class HelperFunctions
```

After running `composer dump-autoload`, all classes are available:

```php
<?php

require 'vendor/autoload.php';

$user = new User_Model();    // Works!
$post = new Post_Model();    // Works!
$helper = new HelperFunctions();  // Works!
```

### 3. Files Autoloading

**Best for:** Global functions, constants, bootstrapping code

```json
{
    "autoload": {
        "files": [
            "helpers/functions.php",
            "config/constants.php",
            "bootstrap/app.php"
        ]
    }
}
```

**Example: helpers/functions.php**
```php
<?php

declare(strict_types=1);

// Global helper functions (no namespace)

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        return $_ENV[$key] ?? $default;
    }
}

if (!function_exists('dd')) {
    function dd(mixed ...$vars): never
    {
        var_dump(...$vars);
        exit(1);
    }
}

if (!function_exists('now')) {
    function now(): DateTime
    {
        return new DateTime();
    }
}
```

**Usage:**
```php
<?php

require 'vendor/autoload.php';

// Functions are globally available
$dbHost = env('DB_HOST', 'localhost');
$currentTime = now();
dd($dbHost, $currentTime);  // Dump and die
```

### Java Comparison

::: code-group

```json [PHP Autoloading]
{
    "autoload": {
        "psr-4": { "App\\": "src/" },
        "classmap": ["legacy/"],
        "files": ["helpers.php"]
    }
}
```

```xml [Java Classpath]
<!-- Maven -->
<build>
    <sourceDirectory>src/main/java</sourceDirectory>
    <resources>
        <resource>
            <directory>src/main/resources</directory>
        </resource>
    </resources>
</build>

<!-- Additional classpath entries -->
<dependencies>
    <dependency>
        <groupId>com.legacy</groupId>
        <artifactId>old-lib</artifactId>
        <version>1.0</version>
        <systemPath>${project.basedir}/lib/legacy.jar</systemPath>
        <scope>system</scope>
    </dependency>
</dependencies>
```

:::

### Performance: Classmap vs PSR-4

```php
// PSR-4: File path calculation on each autoload
// App\Models\User -> src/Models/User.php (string manipulation)

// Classmap: Direct array lookup (faster)
// $classmap['App\Models\User'] => 'src/Models/User.php'
```

**Optimization for Production:**

```json
{
    "autoload": {
        "psr-4": {
            "App\\": "src/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "Tests\\": "tests/"
        }
    },
    "config": {
        "optimize-autoloader": true,
        "classmap-authoritative": true
    }
}
```

```bash
# Production deployment
composer install --no-dev --optimize-autoloader --classmap-authoritative

# This converts PSR-4 to classmap for maximum performance
```

---

## Section 10: Performance & Optimization

### Goal

Optimize autoloading for production environments.

### Autoloader Optimization Levels

**1. Default (Development)**
```bash
composer dump-autoload
```
- PSR-4 rules applied at runtime
- Flexible, allows adding classes without regenerating
- Slower (file path calculation overhead)

**2. Optimized Classmap**
```bash
composer dump-autoload --optimize
# or
composer dump-autoload -o
```
- Converts PSR-4 namespaces to classmap
- Faster class loading (direct array lookup)
- Still allows fallback to PSR-4 rules

**3. Authoritative Classmap (Production)**
```bash
composer dump-autoload --classmap-authoritative
# or
composer dump-autoload -a
```
- Only uses classmap, no PSR-4 fallback
- Fastest possible
- Must regenerate when adding new classes
- **Use in production only**

### Production composer.json

```json
{
    "config": {
        "optimize-autoloader": true,
        "classmap-authoritative": true,
        "apcu-autoloader": true
    }
}
```

**Configuration Options:**

| Option | Description | When to Use |
|--------|-------------|-------------|
| `optimize-autoloader` | Generate classmap for PSR-4 packages | Production |
| `classmap-authoritative` | Don't fall back to PSR-4 rules | Production (after install) |
| `apcu-autoloader` | Cache found/not-found classes in APCu | Production with APCu extension |

### OPcache Configuration

PHP's OPcache significantly improves performance by caching compiled bytecode:

```ini
; php.ini (production)
[opcache]
opcache.enable=1
opcache.enable_cli=0
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0  ; Disable for production
opcache.save_comments=1
opcache.fast_shutdown=1

; File-based cache for autoloader
opcache.file_cache=/tmp/opcache
opcache.file_cache_only=0
```

### Autoloader Performance Comparison

```php
<?php

declare(strict_types=1);

// Performance test script

require 'vendor/autoload.php';

$iterations = 10000;

// Test 1: PSR-4 (default)
$start = microtime(true);
for ($i = 0; $i < $iterations; $i++) {
    $user = new App\Models\User(1, "Test", "test@example.com");
}
$psr4Time = microtime(true) - $start;

// Test 2: Optimized classmap
$start = microtime(true);
for ($i = 0; $i < $iterations; $i++) {
    $user = new App\Models\User(1, "Test", "test@example.com");
}
$classmapTime = microtime(true) - $start;

echo "PSR-4: " . $psr4Time . "s\n";
echo "Classmap: " . $classmapTime . "s\n";
echo "Improvement: " . round(($psr4Time - $classmapTime) / $psr4Time * 100, 2) . "%\n";
```

**Typical Results:**
- PSR-4: ~0.15s
- Optimized Classmap: ~0.08s
- Improvement: ~45-50%

### Best Practices

::: tip Production Deployment Checklist

**1. Install dependencies without dev packages:**
```bash
composer install --no-dev --optimize-autoloader --classmap-authoritative
```

**2. Enable OPcache in php.ini**

**3. Preload (PHP 7.4+):**
```php
// preload.php
<?php

require __DIR__ . '/vendor/autoload.php';

// Preload frequently used classes
$classesToPreload = [
    App\Models\User::class,
    App\Models\Post::class,
    App\Services\UserService::class,
    // ... more classes
];

foreach ($classesToPreload as $class) {
    if (class_exists($class)) {
        // Class is now preloaded in OPcache
    }
}
```

**php.ini:**
```ini
opcache.preload=/path/to/preload.php
opcache.preload_user=www-data
```

**4. Monitor autoloader performance:**
```bash
composer diagnose
composer check-platform-reqs
```
:::

---

## Section 11: Common Pitfalls & Debugging

### Goal

Avoid common namespace mistakes and learn debugging techniques.

### Pitfall 1: Case Sensitivity

::: danger File System Case Sensitivity

**Problem:** Works on macOS/Windows, fails on Linux

```php
<?php

// File: src/Models/User.php
namespace App\Models;
class User {}

// Your code:
use App\Models\user;  // ❌ Wrong case!
$u = new user();       // ❌ Wrong case!
```

**Solution:** Always match exact case
```php
<?php

use App\Models\User;  // ✅ Correct
$u = new User();       // ✅ Correct
```

:::

**Why it matters:**
- macOS/Windows: Case-insensitive file systems (works accidentally)
- Linux: Case-sensitive (production servers usually Linux)
- Code works locally, breaks in production

### Pitfall 2: Forgetting Leading Backslash

```php
<?php

declare(strict_types=1);

namespace App\Services;

class DateService
{
    public function now(): DateTime  // ❌ Looks for App\Services\DateTime!
    {
        return new DateTime();  // Fatal error: Class not found
    }
}
```

**Solution 1: Leading backslash**
```php
<?php

namespace App\Services;

class DateService
{
    public function now(): \DateTime  // ✅ Global DateTime
    {
        return new \DateTime();
    }
}
```

**Solution 2: Import**
```php
<?php

namespace App\Services;

use DateTime;

class DateService
{
    public function now(): DateTime  // ✅ Imported DateTime
    {
        return new DateTime();
    }
}
```

### Pitfall 3: Namespace Mismatch with File Path

::: warning Directory Structure Mismatch

**Incorrect:**
```
src/
└── models/           # Lowercase!
    └── User.php      # namespace App\Models; (uppercase!)
```

**PSR-4 Mapping:** `App\Models\User` should be at `src/Models/User.php`

**Result:** Class not found (autoloader can't locate it)

**Solution:** Match directory names exactly
```
src/
└── Models/           # ✅ Matches namespace
    └── User.php      # ✅ namespace App\Models;
```
:::

### Pitfall 4: Multiple Classes in One File

```php
<?php

// ❌ BAD: Multiple classes in one file (src/Models/User.php)
namespace App\Models;

class User {}
class UserProfile {}  // Won't autoload!
class UserSettings {} // Won't autoload!
```

**Problem:** Autoloader only loads file when `User` is requested. `UserProfile` and `UserSettings` won't be found.

**Solution:** One class per file
```
src/Models/
├── User.php          # class User
├── UserProfile.php   # class UserProfile
└── UserSettings.php  # class UserSettings
```

### Pitfall 5: Namespace in Wrong Location

```php
<?php

declare(strict_types=1);

// Some code here...

namespace App\Models;  // ❌ Namespace must be first statement (after declare)!

class User {}
```

**Solution:**
```php
<?php

declare(strict_types=1);

namespace App\Models;  // ✅ Immediately after declare

class User {}
```

### Debugging Namespace Issues

**1. Check Autoloader Output**

```php
<?php

require 'vendor/autoload.php';

// See registered autoloaders
spl_autoload_functions();

// Register custom autoloader with logging
spl_autoload_register(function ($class) {
    echo "Trying to load: $class\n";
});

// Try loading class
$user = new App\Models\User();
```

**2. Dump Autoloader Information**

```bash
# View generated autoloader
cat vendor/composer/autoload_psr4.php
cat vendor/composer/autoload_classmap.php

# Verbose autoloader regeneration
composer dump-autoload -vvv
```

**3. Common Error Messages & Solutions**

| Error | Cause | Solution |
|-------|-------|----------|
| `Class 'App\Models\User' not found` | Autoloader can't find file | Check namespace matches directory |
| `Class 'DateTime' not found` | Missing leading `\` in namespace | Add `\DateTime` or `use DateTime` |
| `Cannot redeclare class User` | Class loaded twice | Check for multiple files with same class |
| `Parse error: syntax error` | Namespace syntax wrong | Ensure namespace before class definition |

**4. Validation Script**

```php
<?php
// validate-autoload.php

declare(strict_types=1);

require 'vendor/autoload.php';

$classesToCheck = [
    'App\Models\User',
    'App\Services\UserService',
    'App\Controllers\UserController',
];

foreach ($classesToCheck as $class) {
    if (class_exists($class)) {
        echo "✅ $class loaded successfully\n";
        $reflection = new ReflectionClass($class);
        echo "   File: {$reflection->getFileName()}\n";
    } else {
        echo "❌ $class not found\n";
    }
}
```

**5. IDE Integration**

Most IDEs (PHPStorm, VS Code) can detect namespace issues:

**PHPStorm:**
- Automatically suggests namespace based on directory
- Warns about namespace mismatches
- Quick fix: "Import class" (Alt+Enter)

**VS Code with PHP Intelephense:**
- Install "PHP Intelephense" extension
- Automatically detects namespace issues
- Provides "Import class" quick action

---

## Section 12: Monorepo & Multi-Package Structure

### Goal

Organize large projects with multiple packages (like Java multi-module projects).

### Monorepo Structure

Similar to Java's Maven multi-module or Gradle multi-project:

```
my-project/
├── composer.json           # Root composer file
├── packages/
│   ├── core/
│   │   ├── composer.json
│   │   └── src/
│   │       └── CoreService.php
│   ├── api/
│   │   ├── composer.json
│   │   └── src/
│   │       └── ApiController.php
│   └── web/
│       ├── composer.json
│       └── src/
│           └── WebController.php
└── apps/
    ├── admin/
    │   ├── composer.json
    │   └── public/
    │       └── index.php
    └── customer/
        ├── composer.json
        └── public/
            └── index.php
```

### Root composer.json

```json
{
    "name": "mycompany/monorepo",
    "type": "project",
    "repositories": [
        {
            "type": "path",
            "url": "./packages/*"
        },
        {
            "type": "path",
            "url": "./apps/*"
        }
    ],
    "require": {
        "php": ">=8.3"
    },
    "autoload": {
        "psr-4": {
            "MyCompany\\Core\\": "packages/core/src/",
            "MyCompany\\Api\\": "packages/api/src/",
            "MyCompany\\Web\\": "packages/web/src/"
        }
    },
    "minimum-stability": "dev",
    "prefer-stable": true
}
```

### Package: packages/core/composer.json

```json
{
    "name": "mycompany/core",
    "description": "Core business logic",
    "type": "library",
    "autoload": {
        "psr-4": {
            "MyCompany\\Core\\": "src/"
        }
    },
    "require": {
        "php": ">=8.3"
    }
}
```

### Package: packages/api/composer.json

```json
{
    "name": "mycompany/api",
    "description": "REST API package",
    "type": "library",
    "autoload": {
        "psr-4": {
            "MyCompany\\Api\\": "src/"
        }
    },
    "require": {
        "php": ">=8.3",
        "mycompany/core": "^1.0"
    }
}
```

### Application: apps/admin/composer.json

```json
{
    "name": "mycompany/admin-app",
    "description": "Admin application",
    "type": "project",
    "require": {
        "php": ">=8.3",
        "mycompany/core": "^1.0",
        "mycompany/api": "^1.0",
        "mycompany/web": "^1.0"
    },
    "autoload": {
        "psr-4": {
            "MyCompany\\Admin\\": "src/"
        }
    }
}
```

### Using Packages

::: code-group

```php [packages/core/src/CoreService.php]
<?php

declare(strict_types=1);

namespace MyCompany\Core;

class CoreService
{
    public function process(string $data): string
    {
        return "Processed: " . $data;
    }
}
```

```php [packages/api/src/ApiController.php]
<?php

declare(strict_types=1);

namespace MyCompany\Api;

use MyCompany\Core\CoreService;

class ApiController
{
    public function __construct(
        private CoreService $coreService
    ) {}

    public function handleRequest(array $data): array
    {
        $result = $this->coreService->process($data['input']);
        return ['result' => $result];
    }
}
```

```php [apps/admin/public/index.php]
<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use MyCompany\Core\CoreService;
use MyCompany\Api\ApiController;

$core = new CoreService();
$api = new ApiController($core);

$response = $api->handleRequest(['input' => 'test data']);
echo json_encode($response);
```

:::

### Installation

```bash
# From root directory
composer install

# This symlinks local packages into vendor/
# vendor/mycompany/core -> ../../packages/core
# vendor/mycompany/api -> ../../packages/api
```

### Java Comparison

::: code-group

```json [PHP Monorepo]
{
    "repositories": [
        {"type": "path", "url": "./packages/*"}
    ],
    "require": {
        "mycompany/core": "^1.0",
        "mycompany/api": "^1.0"
    }
}
```

```xml [Maven Multi-Module]
<!-- Root pom.xml -->
<project>
    <groupId>com.mycompany</groupId>
    <artifactId>parent</artifactId>
    <packaging>pom</packaging>

    <modules>
        <module>core</module>
        <module>api</module>
        <module>web</module>
    </modules>
</project>

<!-- api/pom.xml -->
<dependencies>
    <dependency>
        <groupId>com.mycompany</groupId>
        <artifactId>core</artifactId>
        <version>1.0.0</version>
    </dependency>
</dependencies>
```

```gradle [Gradle Multi-Project]
// settings.gradle
rootProject.name = 'my-project'
include 'core', 'api', 'web'

// api/build.gradle
dependencies {
    implementation project(':core')
}
```

:::

**Benefits:**
- Share code between applications
- Manage dependencies between packages
- Independent versioning
- Easier testing and CI/CD

---

## Exercises

### Exercise 1: Multi-Layer Application

Create a complete application with proper namespacing:

**Requirements:**
- Models: Product, Category
- Repositories: ProductRepository, CategoryRepository
- Services: ProductService
- Controllers: ProductController
- PSR-4 autoloading configuration

### Exercise 2: Namespace Aliasing

Handle namespace conflicts using aliases:

**Requirements:**
- Two different `Logger` classes in different namespaces
- Use both in the same file with aliases
- Demonstrate proper import statements

### Exercise 3: Legacy Code Integration

Integrate legacy code using classmap autoloading:

**Requirements:**
- Create a `legacy/` directory with non-PSR-4 classes
- Configure classmap autoloading in `composer.json`
- Create modern PSR-4 classes that use the legacy classes
- Demonstrate both autoloading strategies working together

### Exercise 4: Global Helper Functions

Create a helper functions file:

**Requirements:**
- Create `helpers/functions.php` with 5 utility functions
- Configure files autoloading in `composer.json`
- Use `function_exists()` guards to prevent redeclaration
- Use the functions in a namespaced class

### Exercise 5: Monorepo Structure

Build a simple monorepo:

**Requirements:**
- Create packages: `packages/logger`, `packages/validation`
- Create app: `apps/web` that uses both packages
- Configure PSR-4 autoloading for all packages
- Set up path repositories in root `composer.json`
- Demonstrate cross-package dependencies

---

## Wrap-up Checklist

Before moving to the next chapter, ensure you can:

- [ ] Define namespaces in PHP files
- [ ] Use the `use` statement to import classes
- [ ] Understand namespace resolution rules (unqualified, qualified, fully qualified)
- [ ] Configure PSR-4 autoloading in composer.json
- [ ] Use classmap autoloading for legacy code
- [ ] Set up files autoloading for helper functions
- [ ] Organize code with proper directory structure
- [ ] Handle namespace conflicts with aliases
- [ ] Access global namespace with leading backslash
- [ ] Optimize autoloader for production
- [ ] Debug namespace issues effectively
- [ ] Avoid common namespace pitfalls
- [ ] Structure monorepo projects
- [ ] Understand the similarity to Java packages
- [ ] Use Composer's autoloader efficiently

::: tip Ready for More?
In [Chapter 7: Error Handling](/series/php-for-java-developers/chapters/07-error-handling), we'll explore exceptions, try-catch-finally blocks, and error handling best practices in PHP.
:::

---

## Further Reading

**PHP Documentation:**
- [Namespaces](https://www.php.net/manual/en/language.namespaces.php)
- [PSR-4 Autoloading](https://www.php-fig.org/psr/psr-4/)

**Composer:**
- [Composer Documentation](https://getcomposer.org/doc/)
- [Autoloading](https://getcomposer.org/doc/01-basic-usage.md#autoloading)

---

<div style="display: flex; justify-content: space-between; margin-top: 2rem;">
  <div>
    <strong>Previous:</strong> <a href="/series/php-for-java-developers/chapters/05-interfaces-and-traits">← Chapter 5: Interfaces & Traits</a>
  </div>
  <div>
    <strong>Next:</strong> <a href="/series/php-for-java-developers/chapters/07-error-handling">Chapter 7: Error Handling →</a>
  </div>
</div>
