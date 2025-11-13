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

---

## Wrap-up Checklist

Before moving to the next chapter, ensure you can:

- [ ] Define namespaces in PHP files
- [ ] Use the `use` statement to import classes
- [ ] Configure PSR-4 autoloading in composer.json
- [ ] Organize code with proper directory structure
- [ ] Handle namespace conflicts with aliases
- [ ] Access global namespace with leading backslash
- [ ] Understand the similarity to Java packages
- [ ] Use Composer's autoloader

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
