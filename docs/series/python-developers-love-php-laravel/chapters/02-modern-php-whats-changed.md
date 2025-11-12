---
title: "02: Modern PHP: What's Changed"
description: "Discover PHP's evolution from legacy language to modern, typed, performant powerhouse—and why it matters for Python developers."
series: "python-developers-love-php-laravel"
chapter: 2
order: 2
difficulty: "Intermediate"
prerequisites:
  - "/series/python-developers-love-php-laravel/chapters/01-mapping-concepts-python-web-frameworks-vs-laravel"
---

![Modern PHP: What's Changed](/images/python-developers-love-php-laravel/chapter-02-modern-php-whats-changed-hero-full.webp)

# Chapter 02: Modern PHP: What's Changed

## Overview

In Chapter 01, you saw how Python web framework concepts map directly to Laravel—routing, templates, ORMs, and MVC patterns. But before diving deeper into Laravel's developer experience, it's crucial to understand the language itself. If you're coming from Python, you might have outdated perceptions about PHP based on PHP 5.x or horror stories from the early 2000s.

This chapter is about **PHP's evolution**. Modern PHP (versions 7.4+ and especially 8.0+) is fundamentally different from the PHP you might remember or have heard about. PHP 7.x introduced type declarations, massive performance improvements, and modern error handling. PHP 8.0+ added JIT compilation, union types, match expressions, and constructor property promotion. PHP 8.4 continues this evolution with property hooks, asymmetric visibility, and improved JIT performance.

By the end of this chapter, you'll understand that modern PHP has caught up to—and in some areas surpassed—Python's language features. You'll see side-by-side comparisons showing how PHP's type system, modern syntax, and performance characteristics compare to Python. Most importantly, you'll recognize that PHP deserves a second look, not based on outdated perceptions, but on its current reality.

## Prerequisites

Before starting this chapter, you should have:

- Completion of [Chapter 01](/series/python-developers-love-php-laravel/chapters/01-mapping-concepts-python-web-frameworks-vs-laravel) or equivalent understanding
- Python experience (for comparisons)
- PHP 8.4+ installed
- Basic familiarity with type hints in Python (PEP 484)
- **Estimated Time**: ~60 minutes

**Verify your setup:**

```bash
# Check PHP version (should show PHP 8.4+)
php --version

# Expected output: PHP 8.4.x (or higher)
```

## What You'll Build

By the end of this chapter, you will have:

- Understanding of PHP 7.x improvements (type declarations, performance, error handling)
- Knowledge of PHP 8.0+ modern features (JIT, union types, match expressions, property hooks)
- Side-by-side comparison examples (Python vs PHP) for type systems, modern syntax, and language features
- Understanding of PHP community evolution (PSR standards, Composer, modern tooling)
- Clear picture of perception vs reality: what's actually changed in PHP
- Working PHP 8.4 code examples demonstrating modern features
- Confidence that modern PHP is competitive with Python for web development

## Quick Start

Want to see modern PHP in action right away? Here's a quick comparison showing how PHP 8.4 compares to Python:

**Python (with type hints):**

```python
from typing import Union

def greet(name: str, age: Union[int, None] = None) -> str:
    if age is None:
        return f"Hello, {name}!"
    return f"Hello, {name}! You are {age} years old."

class User:
    def __init__(self, name: str, email: str):
        self.name = name
        self.email = email
```

**PHP 8.4 (modern syntax):**

```php
# filename: quickstart.php
<?php

declare(strict_types=1);

function greet(string $name, ?int $age = null): string
{
    if ($age === null) {
        return "Hello, {$name}!";
    }
    return "Hello, {$name}! You are {$age} years old.";
}

class User
{
    public function __construct(
        public string $name,
        public string $email
    ) {}
}
```

See the similarities? Modern PHP has type declarations, union types (using `?` for nullable), constructor property promotion, and strict typing—just like Python! This chapter will show you all the ways PHP has evolved.

## Objectives

- Understand PHP 7.x improvements and how they compare to Python features
- Learn PHP 8.0+ modern features (JIT, union types, match expressions, attributes, enums, property hooks)
- Compare PHP's type system with Python's type hints
- Understand PHP community evolution (PSR standards, Composer ecosystem)
- Address common misconceptions about PHP from a Python developer's perspective
- Recognize that modern PHP is competitive with Python for web development
- Build confidence that PHP deserves a second look based on current reality

## Step 1: PHP 7.x Revolution (~10 min)

### Goal

Understand the fundamental improvements PHP 7.x introduced that transformed PHP from a scripting language into a modern, typed, performant language.

### Actions

1. **Type Declarations (PHP 7.0+)**

   PHP 7.0 introduced type declarations for function parameters and return types, similar to Python's type hints (PEP 484):

   **PHP 5.x (old way):**

   ```php
   # filename: php5-no-types.php
   <?php
   function calculateTotal($items, $tax) {
       // No type safety - could receive anything
       return $items * (1 + $tax);
   }
   ```

   **PHP 7.4+ (modern way):**

   ```php
   # filename: php7-types.php
   <?php

   declare(strict_types=1);

   function calculateTotal(float $items, float $tax): float
   {
       return $items * (1 + $tax);
   }
   ```

   **Python equivalent:**

   ```python
   def calculate_total(items: float, tax: float) -> float:
       return items * (1 + tax)
   ```

   The complete type declaration examples are available in [`php7-types.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-02/php7-types.php).

2. **Return Type Declarations**

   PHP 7.0+ allows you to specify return types, just like Python:

   ```php
   # filename: return-types.php
   <?php

   declare(strict_types=1);

   function getUserName(int $userId): string
   {
       return "User {$userId}";
   }

   function getUserAge(int $userId): ?int  // Nullable return type
   {
       // May return null if user not found
       return null;
   }
   ```

   **Python equivalent:**

   ```python
   from typing import Optional

   def get_user_name(user_id: int) -> str:
       return f"User {user_id}"

   def get_user_age(user_id: int) -> Optional[int]:
       return None  # May return None if user not found
   ```

3. **Performance Improvements**

   PHP 7.0 was **2-3x faster** than PHP 5.x due to:
   - Improved engine architecture
   - Better memory management
   - Optimized opcode caching
   - Reduced memory usage

   This performance boost made PHP competitive with Python for web applications.

4. **Error Handling Evolution**

   PHP 7.0 replaced many fatal errors with exceptions:

   **PHP 5.x:**

   ```php
   # filename: php5-errors.php
   <?php
   // Fatal error: Call to undefined function
   $result = undefinedFunction();  // Script dies
   ```

   **PHP 7.0+:**

   ```php
   # filename: php7-exceptions.php
   <?php

   try {
       $result = undefinedFunction();
   } catch (Error $e) {
       // Catchable error - script continues
       echo "Error: " . $e->getMessage();
   }
   ```

5. **Null Coalescing Operator (`??`)**

   PHP 7.0 introduced the null coalescing operator, similar to Python's `or` operator:

   ```php
   # filename: null-coalescing.php
   <?php

   // PHP null coalescing
   $username = $_GET['username'] ?? 'guest';
   $email = $user->email ?? 'no-email@example.com';
   ```

   **Python equivalent:**

   ```python
   # Python's 'or' operator
   username = request.GET.get('username') or 'guest'
   email = user.email or 'no-email@example.com'
   ```

   The null coalescing examples are available in [`null-coalescing.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-02/null-coalescing.php).

### Expected Result

After completing this step, you'll understand that:

- PHP 7.x introduced type declarations similar to Python's type hints
- PHP 7.0 was 2-3x faster than PHP 5.x, making it competitive with Python
- PHP 7.0 replaced fatal errors with catchable exceptions
- The null coalescing operator (`??`) works similarly to Python's `or` operator
- Modern PHP code looks much more like Python with type safety

### Why It Works

PHP 7.x transformed PHP from a loosely-typed scripting language into a modern, typed language. The type declarations provide the same benefits as Python's type hints: better IDE support, static analysis, and runtime type checking (with `declare(strict_types=1)`). The performance improvements made PHP competitive with Python for web applications, and the improved error handling makes PHP code more robust.

### Troubleshooting

- **"PHP types are optional, Python types are too"** — Both languages have optional typing. PHP requires `declare(strict_types=1);` for strict type checking, similar to how Python's type hints are checked by tools like mypy, not at runtime by default.
- **"PHP's `??` is different from Python's `or`"** — PHP's `??` only checks for null/undefined, while Python's `or` checks for falsy values. PHP's `??` is more precise for null checking.
- **"Do I need `declare(strict_types=1)`?"** — For modern PHP code, yes! It enables strict type checking, similar to how Python's type hints work with mypy.

## Step 2: PHP 8.0+ Modern Features (~15 min)

### Goal

Learn the modern language features PHP 8.0+ introduced that bring it on par with (and in some areas surpass) Python's language features.

### Actions

1. **JIT Compilation (PHP 8.0+)**

   PHP 8.0 introduced Just-In-Time (JIT) compilation, which can make PHP **3-4x faster** than PHP 7.x for CPU-intensive workloads:

   ```php
   # filename: jit-performance.php
   <?php

   declare(strict_types=1);

   // JIT is enabled by default in PHP 8.0+
   // For CPU-intensive code, JIT provides significant speedups
   function calculateFibonacci(int $n): int
   {
       if ($n <= 1) {
           return $n;
       }
       return calculateFibonacci($n - 1) + calculateFibonacci($n - 2);
   }
   ```

   **Performance impact**: JIT compilation makes PHP competitive with compiled languages for certain workloads, often outperforming Python for web applications.

2. **Union Types (PHP 8.0+)**

   PHP 8.0 introduced union types, similar to Python's `Union` type:

   ```php
   # filename: union-types.php
   <?php

   declare(strict_types=1);

   function processId(string|int $id): string
   {
       return (string) $id;
   }

   function getValue(): string|int|null
   {
       // May return string, int, or null
       return 42;
   }
   ```

   **Python equivalent:**

   ```python
   from typing import Union

   def process_id(id: Union[str, int]) -> str:
       return str(id)

   def get_value() -> Union[str, int, None]:
       return 42
   ```

   The union types examples are available in [`union-types.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-02/union-types.php).

3. **Named Arguments (PHP 8.0+)**

   PHP 8.0 introduced named arguments, similar to Python's keyword arguments:

   ```php
   # filename: named-arguments.php
   <?php

   declare(strict_types=1);

   function createUser(
       string $name,
       string $email,
       int $age = 0,
       bool $active = true
   ): array {
       return [
           'name' => $name,
           'email' => $email,
           'age' => $age,
           'active' => $active,
       ];
   }

   // Using named arguments (PHP 8.0+)
   $user = createUser(
       name: 'John Doe',
       email: 'john@example.com',
       active: false
   );
   ```

   **Python equivalent:**

   ```python
   def create_user(
       name: str,
       email: str,
       age: int = 0,
       active: bool = True
   ) -> dict:
       return {
           'name': name,
           'email': email,
           'age': age,
           'active': active,
       }

   # Using keyword arguments
   user = create_user(
       name='John Doe',
       email='john@example.com',
       active=False
   )
   ```

   The named arguments examples are available in [`named-arguments.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-02/named-arguments.php).

4. **Match Expressions (PHP 8.0+)**

   PHP 8.0 introduced match expressions, similar to Python 3.10+'s `match` statement:

   ```php
   # filename: match-expressions.php
   <?php

   declare(strict_types=1);

   function getStatusMessage(string $status): string
   {
       return match ($status) {
           'pending' => 'Your request is pending review.',
           'approved' => 'Your request has been approved!',
           'rejected' => 'Your request has been rejected.',
           default => 'Unknown status.',
       };
   }
   ```

   **Python 3.10+ equivalent:**

   ```python
   def get_status_message(status: str) -> str:
       match status:
           case 'pending':
               return 'Your request is pending review.'
           case 'approved':
               return 'Your request has been approved!'
           case 'rejected':
               return 'Your request has been rejected.'
           case _:
               return 'Unknown status.'
   ```

   The match expressions examples are available in [`match-expressions.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-02/match-expressions.php).

5. **Constructor Property Promotion (PHP 8.0+)**

   PHP 8.0 introduced constructor property promotion, reducing boilerplate similar to Python dataclasses:

   ```php
   # filename: constructor-promotion.php
   <?php

   declare(strict_types=1);

   // PHP 8.0+ constructor property promotion
   class User
   {
       public function __construct(
           public string $name,
           public string $email,
           public int $age = 0
       ) {}
   }

   $user = new User('John Doe', 'john@example.com', 30);
   echo $user->name;  // 'John Doe'
   ```

   **Note**: PHP 8.2+ also introduced `readonly` properties, which can be combined with constructor property promotion:

   ```php
   class Order
   {
       public function __construct(
           public readonly string $id,
           public readonly float $total
       ) {}
   }
   ```

   Once set in the constructor, readonly properties cannot be modified, similar to Python's `@dataclass(frozen=True)`.

   **Python equivalent (dataclasses):**

   ```python
   from dataclasses import dataclass

   @dataclass
   class User:
       name: str
       email: str
       age: int = 0

   user = User('John Doe', 'john@example.com', 30)
   print(user.name)  # 'John Doe'
   ```

   The constructor property promotion examples are available in [`constructor-promotion.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-02/constructor-promotion.php).

6. **Attributes (PHP 8.0+)**

   PHP 8.0 introduced attributes (also called annotations), similar to Python decorators:

   ```php
   # filename: attributes.php
   <?php

   declare(strict_types=1);

   // Define an attribute (like a Python decorator class)
   #[\Attribute]
   class Route
   {
       public function __construct(
           public string $path,
           public string $method = 'GET'
       ) {}
   }

   // Use attributes (like Python decorators)
   #[Route('/api/users', 'GET')]
   class UserController
   {
       #[Route('/api/users/{id}', 'GET')]
       public function getUser(int $id): array
       {
           return ['id' => $id, 'name' => 'John Doe'];
       }
   }
   ```

   **Python equivalent (decorators):**

   ```python
   from functools import wraps

   def route(path: str, method: str = 'GET'):
       def decorator(func):
           func.route_path = path
           func.route_method = method
           return func
       return decorator

   @route('/api/users', 'GET')
   class UserController:
       @route('/api/users/{id}', 'GET')
       def get_user(self, user_id: int) -> dict:
           return {'id': user_id, 'name': 'John Doe'}
   ```

   Attributes are used extensively in Laravel for routing, validation, and more. The attributes examples are available in [`attributes.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-02/attributes.php).

7. **Enums (PHP 8.1+)**

   PHP 8.1 introduced enums, similar to Python's `Enum` class:

   ```php
   # filename: enums.php
   <?php

   declare(strict_types=1);

   // PHP 8.1+ enum
   enum Status: string
   {
       case PENDING = 'pending';
       case APPROVED = 'approved';
       case REJECTED = 'rejected';

       public function isPending(): bool
       {
           return $this === self::PENDING;
       }
   }

   function processStatus(Status $status): string
   {
       return match ($status) {
           Status::PENDING => 'Processing...',
           Status::APPROVED => 'Completed!',
           Status::REJECTED => 'Failed.',
       };
   }

   $status = Status::PENDING;
   echo processStatus($status);  // 'Processing...'
   ```

   **Python equivalent:**

   ```python
   from enum import Enum

   class Status(str, Enum):
       PENDING = 'pending'
       APPROVED = 'approved'
       REJECTED = 'rejected'

   def process_status(status: Status) -> str:
       match status:
           case Status.PENDING:
               return 'Processing...'
           case Status.APPROVED:
               return 'Completed!'
           case Status.REJECTED:
               return 'Failed.'

   status = Status.PENDING
   print(process_status(status))  # 'Processing...'
   ```

   Enums provide type safety and are commonly used in Laravel for status fields, permissions, and more. The enums examples are available in [`enums.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-02/enums.php).

### Expected Result

After completing this step, you'll understand that:

- PHP 8.0+ JIT compilation makes PHP competitive with compiled languages
- Union types (`string|int`) work similarly to Python's `Union[str, int]`
- Named arguments provide the same flexibility as Python keyword arguments
- Match expressions are similar to Python 3.10+'s `match` statement
- Constructor property promotion reduces boilerplate like Python dataclasses
- Attributes provide decorator-like functionality similar to Python decorators
- Enums provide type safety similar to Python's `Enum` class
- Modern PHP features are on par with Python's language features

### Why It Works

PHP 8.0+ brought PHP's language features to parity with modern Python. JIT compilation provides performance benefits for CPU-intensive workloads. Union types, named arguments, and match expressions provide the same expressiveness as Python. Constructor property promotion reduces boilerplate, making PHP classes as concise as Python dataclasses. Attributes provide decorator-like functionality for metadata, and enums provide type-safe constants similar to Python's Enum class.

### Troubleshooting

- **"JIT doesn't seem faster"** — JIT provides the most benefit for CPU-intensive code, not I/O-bound operations. For typical web applications, the performance improvement may be less noticeable, but it's still there.
- **"Union types vs nullable types"** — Use `?string` for `string|null`, and `string|int` for multiple types. PHP 8.0+ supports both patterns.
- **"Match vs switch"** — Match expressions are stricter (require exhaustive matching) and return values, making them safer than switch statements. Use match for value selection, switch for complex control flow.
- **"Attributes vs decorators"** — PHP attributes are similar to Python decorators but are metadata attached to classes, methods, and properties. They're used extensively in Laravel for routing, validation, and dependency injection.
- **"Enums vs constants"** — PHP enums provide type safety and methods, similar to Python's Enum class. Use enums instead of class constants when you need type safety and behavior.

## Step 3: PHP 8.4 Latest Features (~10 min)

### Goal

Learn the cutting-edge features PHP 8.4 introduced that demonstrate PHP's continued evolution and innovation.

### Actions

1. **Property Hooks (PHP 8.4)**

   PHP 8.4 introduced property hooks, providing custom getters and setters similar to Python's `@property` decorator, but more powerful:

   ```php
   # filename: property-hooks.php
   <?php

   declare(strict_types=1);

   class User
   {
       private string $email;

       // Property hook: custom getter
       public function getEmail(): string
       {
           return $this->email;
       }

       // Property hook: custom setter with validation
       public function setEmail(string $email): void
       {
           if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
               throw new InvalidArgumentException('Invalid email address');
           }
           $this->email = $email;
       }

       // Property hook: accessor (combines getter and setter)
       private string $_name = '';
       
       public string $name {
           get => strtoupper($this->_name);
           set => $this->_name = trim($value);
       }
   }
   ```

   **Python equivalent:**

   ```python
   class User:
       def __init__(self):
           self._email = None
           self._name = None

       @property
       def email(self):
           return self._email

       @email.setter
       def email(self, value):
           if '@' not in value:
               raise ValueError('Invalid email address')
           self._email = value

       @property
       def name(self):
           return self._name.upper() if self._name else ''

       @name.setter
       def name(self, value):
           self._name = value.strip()
   ```

   The property hooks examples are available in [`property-hooks.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-02/property-hooks.php).

2. **Asymmetric Visibility (PHP 8.4)**

   PHP 8.4 introduced asymmetric visibility, allowing public read access with private write access:

   ```php
   # filename: asymmetric-visibility.php
   <?php

   declare(strict_types=1);

   class User
   {
       // Public read, private write
       public string $name {
           get;
           private set;
       }

       public function __construct(string $name)
       {
           $this->name = $name;  // Can set in constructor
       }

       public function updateName(string $newName): void
       {
           $this->name = $newName;  // Can set in class methods
       }
   }

   $user = new User('John Doe');
   echo $user->name;  // ✅ Can read
   // $user->name = 'Jane';  // ❌ Error: Cannot access private property
   ```

   **Python equivalent (using `@property`):**

   ```python
   class User:
       def __init__(self, name: str):
           self._name = name

       @property
       def name(self) -> str:
           return self._name

       # No setter = read-only from outside
   ```

   The asymmetric visibility examples are available in [`asymmetric-visibility.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-02/asymmetric-visibility.php).

3. **Typed Class Constants (PHP 8.4)**

   PHP 8.4 allows type declarations for class constants:

   ```php
   # filename: typed-constants.php
   <?php

   declare(strict_types=1);

   class Status
   {
       public const string PENDING = 'pending';
       public const string APPROVED = 'approved';
       public const string REJECTED = 'rejected';
       public const int MAX_RETRIES = 3;
   }
   ```

   **Python equivalent:**

   ```python
   class Status:
       PENDING: str = 'pending'
       APPROVED: str = 'approved'
       REJECTED: str = 'rejected'
       MAX_RETRIES: int = 3
   ```

   The typed constants examples are available in [`typed-constants.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-02/typed-constants.php).

### Expected Result

After completing this step, you'll understand that:

- Property hooks provide custom getters/setters similar to Python's `@property`
- Asymmetric visibility allows public read with private write, similar to Python's read-only properties
- Typed class constants provide type safety for constants
- PHP 8.4 features demonstrate PHP's continued innovation
- Modern PHP features are competitive with (and in some areas surpass) Python's features

### Why It Works

PHP 8.4's property hooks and asymmetric visibility provide more powerful property access control than Python's `@property` decorator. Property hooks allow custom logic in getters and setters, while asymmetric visibility provides fine-grained access control. These features demonstrate PHP's commitment to modern language design and developer experience.

### Troubleshooting

- **"Property hooks seem complex"** — They're optional! Use them when you need custom getter/setter logic. For simple properties, use regular public properties or constructor property promotion.
- **"Asymmetric visibility vs `@property`"** — PHP's asymmetric visibility is more explicit and type-safe than Python's `@property` without a setter. Both achieve similar goals with different syntax.
- **"Do I need PHP 8.4?"** — For this series, yes! We're teaching modern PHP. If you're working with older PHP versions, these features won't be available, but the concepts still apply.

## Step 4: Community Evolution (~10 min)

### Goal

Understand how PHP's community and ecosystem have evolved to support modern, professional development practices.

### Actions

1. **PSR Standards (PHP-FIG)**

   The PHP Framework Interop Group (PHP-FIG) created PSR standards, similar to Python's PEP standards:

   | Standard | Purpose | Python Equivalent |
   |----------|---------|-------------------|
   | PSR-1 | Basic coding standard | PEP 8 (style guide) |
   | PSR-12 | Extended coding style | PEP 8 (detailed) |
   | PSR-4 | Autoloading standard | Python import system |
   | PSR-7 | HTTP message interfaces | Python WSGI/ASGI |

   **PSR-12 compliant code example:**

   ```php
   # filename: psr12-example.php
   <?php

   declare(strict_types=1);

   namespace App\Services;

   use App\Models\User;

   class UserService
   {
       public function __construct(
           private UserRepository $repository
       ) {}

       public function getUserById(int $id): ?User
       {
           return $this->repository->find($id);
       }
   }
   ```

   **Python PEP 8 equivalent:**

   ```python
   from app.models import User
   from app.repositories import UserRepository

   class UserService:
       def __init__(self, repository: UserRepository):
           self.repository = repository

       def get_user_by_id(self, user_id: int) -> User | None:
           return self.repository.find(user_id)
   ```

   The PSR-12 example is available in [`psr12-example.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-02/psr12-example.php).

2. **Composer Ecosystem**

   Composer is PHP's dependency manager, similar to Python's pip:

   **Composer (`composer.json`):**

   ```json
   {
       "require": {
           "laravel/framework": "^11.0",
           "phpunit/phpunit": "^11.0"
       },
       "autoload": {
           "psr-4": {
               "App\\": "src/"
           }
       }
   }
   ```

   **Python (`requirements.txt` or `pyproject.toml`):**

   ```toml
   [project]
   dependencies = [
       "django>=5.0",
       "pytest>=8.0"
   ]
   ```

   Both provide dependency management, version locking, and autoloading/import systems.

3. **Modern Tooling**

   PHP has modern development tools comparable to Python's ecosystem:

   | Tool | Purpose | Python Equivalent |
   |------|---------|------------------|
   | PHPUnit | Testing framework | pytest, unittest |
   | PHPStan | Static analysis | mypy, pylint |
   | Psalm | Static analysis | mypy, pylint |
   | PHP CS Fixer | Code formatter | black, autopep8 |
   | PHP_CodeSniffer | Linter | flake8, pylint |

   **PHPUnit test example:**

   ```php
   # filename: phpunit-test.php
   <?php

   declare(strict_types=1);

   use PHPUnit\Framework\TestCase;

   class UserServiceTest extends TestCase
   {
       public function testGetUserById(): void
       {
           $service = new UserService($this->createMock(UserRepository::class));
           $user = $service->getUserById(1);
           $this->assertInstanceOf(User::class, $user);
       }
   }
   ```

   **Python pytest equivalent:**

   ```python
   import pytest
   from app.services import UserService
   from app.repositories import UserRepository

   def test_get_user_by_id():
       service = UserService(UserRepository())
       user = service.get_user_by_id(1)
       assert isinstance(user, User)
   ```

### Expected Result

After completing this step, you'll understand that:

- PSR standards provide coding conventions similar to Python's PEP 8
- Composer provides dependency management similar to pip
- PHP has modern tooling (PHPUnit, PHPStan, Psalm) comparable to Python's tools
- PHP's ecosystem supports professional development practices
- Modern PHP code follows standards and best practices, just like Python

### Why It Works

PHP's community evolution mirrors Python's. PSR standards provide coding conventions (like PEP 8), Composer provides dependency management (like pip), and modern tooling supports professional development practices. The PHP ecosystem has matured to support the same level of professionalism as Python's ecosystem.

### Troubleshooting

- **"PSR vs PEP 8"** — Both provide coding standards. PSR-12 is more prescriptive than PEP 8, but both achieve the same goal: consistent, readable code.
- **"Composer vs pip"** — Composer uses `composer.json` (like `pyproject.toml`), while pip uses `requirements.txt`. Both manage dependencies effectively.
- **"PHP tooling vs Python tooling"** — Both ecosystems have excellent tools. PHPUnit is comparable to pytest, PHPStan/Psalm are comparable to mypy, and PHP CS Fixer is comparable to black.

## Step 5: Perception vs Reality (~10 min)

### Goal

Address common misconceptions Python developers have about PHP and provide an honest comparison based on current reality.

### Actions

1. **"PHP is Slow"**

   **Reality**: PHP 8+ with JIT is often faster than Python for web applications:

   - PHP 8.0+ JIT compilation provides significant performance improvements
   - PHP's request-based model (shared-nothing architecture) is efficient for web apps
   - Python's GIL (Global Interpreter Lock) can limit performance for web applications
   - Benchmarks show PHP 8+ often outperforms Python for typical web workloads

   **When Python is faster**: Data science, ML, scientific computing (NumPy, Pandas, TensorFlow)

   **When PHP is faster**: Web applications, API endpoints, request handling

2. **"PHP is Loosely Typed"**

   **Reality**: Modern PHP supports strict typing:

   ```php
   # filename: strict-typing.php
   <?php

   declare(strict_types=1);  // Enables strict type checking

   function add(int $a, int $b): int
   {
       return $a + $b;
   }

   // add('1', '2');  // ❌ Type error with strict_types
   add(1, 2);  // ✅ Works correctly
   ```

   **Python comparison**: Python's type hints are also optional and checked by tools (mypy), not at runtime by default. Both languages support gradual typing.

3. **"PHP Code is Messy"**

   **Reality**: Modern PHP with PSR standards produces clean, maintainable code:

   - PSR-12 provides coding standards (like PEP 8)
   - Modern frameworks (Laravel, Symfony) enforce best practices
   - Static analysis tools (PHPStan, Psalm) catch errors early
   - Professional PHP code is as clean as professional Python code

4. **"PHP is Only for WordPress"**

   **Reality**: PHP powers major applications:

   - **Facebook** (originally PHP, now Hack/PHP)
   - **Wikipedia** (MediaWiki)
   - **Slack** (uses PHP for some services)
   - **Etsy** (PHP backend)
   - **Laravel/Symfony** applications power thousands of businesses

5. **"Python Has Better Libraries"**

   **Reality**: It depends on the domain:

   - **Data Science/ML**: Python wins (NumPy, Pandas, scikit-learn, TensorFlow)
   - **Web Development**: PHP/Laravel has excellent packages (authentication, payments, queues)
   - **Scientific Computing**: Python wins (SciPy, Matplotlib)
   - **Rapid Web Development**: PHP/Laravel often faster to develop

### Expected Result

After completing this step, you'll understand that:

- PHP 8+ performance is competitive with (often faster than) Python for web applications
- Modern PHP supports strict typing, similar to Python's type hints
- Professional PHP code follows standards and is as clean as Python code
- PHP powers major applications beyond WordPress
- Python excels at data science/ML, PHP excels at web development
- Neither language is universally better—they excel in different domains

### Why It Works

Perceptions about PHP are often based on PHP 5.x or outdated information. Modern PHP (7.4+ and especially 8.0+) is a different language. PHP 8+ performance, type system, and code quality are competitive with Python. The key is understanding when each language makes sense: Python for data science/ML, PHP for web development.

### Troubleshooting

- **"But I've seen terrible PHP code"** — You've probably seen terrible Python code too. Modern PHP with PSR standards and Laravel conventions produces clean, maintainable code, just like modern Python with PEP 8 and Django/Flask conventions.
- **"Python is more readable"** — Modern PHP with PSR-12 is highly readable. The `$` prefix actually helps distinguish variables from functions/classes, which some developers find helpful.
- **"Should I switch from Python to PHP?"** — No! Use both. Many successful teams use Python for data/ML and PHP/Laravel for web applications. This series helps you understand when each makes sense.

## Exercises

Test your understanding of modern PHP features by completing these exercises:

### Exercise 1: Convert Python Type Hints to PHP Type Declarations (~10 min)

**Goal**: Practice converting Python type hints to PHP 8.4 type declarations.

**Requirements:**

1. Convert this Python function to PHP 8.4:

   ```python
   from typing import Union, Optional, List

   def process_data(
       data: Union[str, int],
       items: Optional[List[str]] = None
   ) -> str:
       if items is None:
           items = []
       return f"Processed: {data}, Items: {len(items)}"
   ```

2. Use `declare(strict_types=1);`
3. Use PHP 8.4 union types and nullable types
4. Ensure the function works identically to the Python version

**Validation**: Test your implementation:

```php
// Test code
$result1 = processData('test', ['item1', 'item2']);
echo $result1;  // Expected: "Processed: test, Items: 2"

$result2 = processData(42);
echo $result2;  // Expected: "Processed: 42, Items: 0"
```

**Reference**: See [`union-types.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-02/union-types.php) for examples.

### Exercise 2: Rewrite PHP 5-Style Code Using PHP 8.4 Features (~15 min)

**Goal**: Modernize legacy PHP code using PHP 8.4 features.

**Requirements:**

1. Rewrite this PHP 5-style code using PHP 8.4 features:

   ```php
   <?php
   class User {
       private $name;
       private $email;
       private $age;

       public function __construct($name, $email, $age) {
           $this->name = $name;
           $this->email = $email;
           $this->age = $age;
       }

       public function getName() {
           return $this->name;
       }

       public function getEmail() {
           return $this->email;
       }

       public function getStatus() {
           if ($this->age < 18) {
               return 'minor';
           } elseif ($this->age < 65) {
               return 'adult';
           } else {
               return 'senior';
           }
       }
   }
   ```

2. Use constructor property promotion
3. Use match expressions for `getStatus()`
4. Add type declarations
5. Use `declare(strict_types=1);`

**Validation**: Your modernized code should:

- Have less boilerplate (constructor property promotion)
- Use match expressions instead of if/elseif
- Have full type safety
- Work identically to the original

**Reference**: See [`constructor-promotion.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-02/constructor-promotion.php) and [`match-expressions.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-02/match-expressions.php) for examples.

### Exercise 3: Compare Python and PHP Performance Characteristics (~10 min)

**Goal**: Understand when PHP vs Python performance matters.

**Requirements:**

1. Research and document:
   - When PHP 8+ is typically faster than Python
   - When Python is typically faster than PHP
   - Why PHP's request-based model is efficient for web apps
   - Why Python's GIL can limit web application performance

2. Create a comparison table showing:
   - Use case (web API, data processing, ML, etc.)
   - Which language typically performs better
   - Why (architecture, tooling, etc.)

**Validation**: Your comparison should:

- Be based on current benchmarks (PHP 8.4 vs Python 3.12+)
- Acknowledge that both languages are fast enough for most use cases
- Explain the architectural differences (request-based vs long-running processes)
- Help readers make informed decisions

**Reference**: See performance comparison resources in Further Reading.

## Wrap-up

Congratulations! You've completed the modern PHP chapter. You now understand:

- ✓ PHP 7.x improvements (type declarations, performance, error handling)
- ✓ PHP 8.0+ modern features (JIT, union types, match expressions, attributes, enums, property hooks)
- ✓ PHP 8.4 latest features (property hooks, asymmetric visibility, typed constants)
- ✓ PHP community evolution (PSR standards, Composer, modern tooling)
- ✓ Perception vs reality: what's actually changed in PHP
- ✓ How PHP compares to Python for web development
- ✓ That modern PHP is competitive with Python for web applications

### What You've Achieved

You've built a comprehensive understanding of PHP's evolution from PHP 5.x to modern PHP 8.4. You can see that PHP has caught up to—and in some areas surpassed—Python's language features. You understand that modern PHP is a typed, performant, professional language that deserves a second look based on current reality, not outdated perceptions.

### Next Steps

In **Chapter 03**, we'll explore Laravel's developer experience. You'll learn:

- Artisan CLI (Laravel's command-line tool)
- Migrations and database workflow
- Testing with PHPUnit
- Laravel conventions and best practices
- How Laravel's developer experience compares to Django/Flask

Your understanding of modern PHP will help you appreciate Laravel's design decisions and developer-friendly features.

## Code Examples

All code examples from this chapter are available in the [`code/chapter-02/`](https://github.com/dalehurley/codewithphp/tree/main/docs/series/python-developers-love-php-laravel/code/chapter-02) directory:

- **Type Declarations**: [`php7-types.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-02/php7-types.php) — PHP 7.x type hints
- **Null Coalescing**: [`null-coalescing.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-02/null-coalescing.php) — PHP 7.0+ null coalescing operator
- **Strict Typing**: [`strict-typing.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-02/strict-typing.php) — PHP 7.0+ strict type checking
- **Union Types**: [`union-types.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-02/union-types.php) — PHP 8.0+ union types
- **Named Arguments**: [`named-arguments.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-02/named-arguments.php) — PHP 8.0+ named arguments
- **Match Expressions**: [`match-expressions.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-02/match-expressions.php) — PHP 8.0+ match vs switch
- **Constructor Promotion**: [`constructor-promotion.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-02/constructor-promotion.php) — PHP 8.0+ constructor property promotion
- **Attributes**: [`attributes.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-02/attributes.php) — PHP 8.0+ attributes (similar to Python decorators)
- **Enums**: [`enums.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-02/enums.php) — PHP 8.1+ enums (similar to Python Enum class)
- **Property Hooks**: [`property-hooks.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-02/property-hooks.php) — PHP 8.4 property hooks
- **Asymmetric Visibility**: [`asymmetric-visibility.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-02/asymmetric-visibility.php) — PHP 8.4 asymmetric visibility
- **Typed Constants**: [`typed-constants.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-02/typed-constants.php) — PHP 8.4 typed class constants
- **PSR-12 Example**: [`psr12-example.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-02/psr12-example.php) — Modern PHP code structure

See the [README.md](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-02/README.md) for detailed instructions on running each example.

## Knowledge Check

Test your understanding of modern PHP features and how they compare to Python:

<Quiz
  title="Chapter 02 Quiz: Modern PHP Features"
  :questions="[
    {
      question: 'Which PHP version introduced type declarations for function parameters and return types?',
      options: [
        { text: 'PHP 5.x', correct: false, explanation: 'PHP 5.x did not have type declarations. Type declarations were introduced in PHP 7.0.' },
        { text: 'PHP 7.0', correct: true, explanation: 'Correct! PHP 7.0 (2015) introduced type declarations for function parameters and return types, similar to Python\'s type hints (PEP 484).' },
        { text: 'PHP 8.0', correct: false, explanation: 'PHP 8.0 added union types, named arguments, and match expressions, but type declarations came in PHP 7.0.' },
        { text: 'PHP 8.4', correct: false, explanation: 'PHP 8.4 added property hooks and asymmetric visibility, but type declarations were introduced much earlier in PHP 7.0.' }
      ]
    },
    {
      question: 'What does PHP\'s null coalescing operator (??) do, and how does it compare to Python?',
      options: [
        { text: 'It checks for null/undefined values, similar to Python\'s `or` operator but more precise', correct: true, explanation: 'Correct! PHP\'s `??` only checks for null/undefined, while Python\'s `or` checks for falsy values. PHP\'s `??` is more precise for null checking.' },
        { text: 'It performs null coalescing exactly like Python\'s `or` operator', correct: false, explanation: 'Not quite. PHP\'s `??` only checks for null/undefined, while Python\'s `or` checks for any falsy value (None, 0, empty string, etc.).' },
        { text: 'It doesn\'t exist in PHP', correct: false, explanation: 'PHP\'s null coalescing operator (`??`) was introduced in PHP 7.0 and is commonly used for default value assignment.' },
        { text: 'It only works with arrays', correct: false, explanation: 'The null coalescing operator works with any value that might be null or undefined, not just arrays.' }
      ]
    },
    {
      question: 'Which PHP 8.0+ feature is similar to Python 3.10+\'s `match` statement?',
      options: [
        { text: 'Switch statements', correct: false, explanation: 'Switch statements exist in both languages, but PHP 8.0+ introduced match expressions which are more similar to Python\'s match statement.' },
        { text: 'Match expressions', correct: true, explanation: 'Correct! PHP 8.0+ match expressions are similar to Python 3.10+\'s `match` statement. Both return values and require exhaustive matching.' },
        { text: 'Union types', correct: false, explanation: 'Union types allow multiple types for a parameter, but they\'re not related to Python\'s match statement.' },
        { text: 'Property hooks', correct: false, explanation: 'Property hooks (PHP 8.4) are similar to Python\'s `@property` decorator, not the match statement.' }
      ]
    },
    {
      question: 'What is the main performance advantage of PHP 8.0+ over PHP 7.x?',
      options: [
        { text: 'Better memory management', correct: false, explanation: 'Memory management improvements came in PHP 7.0, not PHP 8.0+.' },
        { text: 'JIT compilation', correct: true, explanation: 'Correct! PHP 8.0 introduced JIT (Just-In-Time) compilation, which can make PHP 3-4x faster than PHP 7.x for CPU-intensive workloads.' },
        { text: 'Type declarations', correct: false, explanation: 'Type declarations were introduced in PHP 7.0, not PHP 8.0+.' },
        { text: 'Better error handling', correct: false, explanation: 'Improved error handling (exceptions instead of fatal errors) came in PHP 7.0, not PHP 8.0+.' }
      ]
    },
    {
      question: 'How do PHP\'s PSR standards compare to Python\'s PEP standards?',
      options: [
        { text: 'They serve different purposes and cannot be compared', correct: false, explanation: 'Both PSR and PEP standards provide coding conventions and best practices for their respective languages.' },
        { text: 'PSR standards provide coding conventions similar to Python\'s PEP 8', correct: true, explanation: 'Correct! PSR standards (like PSR-1 and PSR-12) provide coding conventions and style guides similar to Python\'s PEP 8. Both aim to create consistent, readable code.' },
        { text: 'PEP standards are more comprehensive than PSR standards', correct: false, explanation: 'Both standards are comprehensive. PSR covers coding style (PSR-1, PSR-12), autoloading (PSR-4), and HTTP interfaces (PSR-7), similar to how PEP covers various aspects of Python.' },
        { text: 'PSR standards only apply to Laravel', correct: false, explanation: 'PSR standards are framework-agnostic and apply to all PHP code, not just Laravel. They\'re created by the PHP Framework Interop Group (PHP-FIG).' }
      ]
    },
    {
      question: 'Which PHP 8.4 feature provides custom getters and setters similar to Python\'s `@property` decorator?',
      options: [
        { text: 'Asymmetric visibility', correct: false, explanation: 'Asymmetric visibility allows public read/private write, but doesn\'t provide custom getter/setter logic like property hooks.' },
        { text: 'Property hooks', correct: true, explanation: 'Correct! PHP 8.4 property hooks provide custom getters and setters similar to Python\'s `@property` decorator, but with more powerful features.' },
        { text: 'Typed constants', correct: false, explanation: 'Typed constants provide type safety for class constants, but don\'t provide getter/setter functionality.' },
        { text: 'Constructor property promotion', correct: false, explanation: 'Constructor property promotion reduces boilerplate in constructors, but doesn\'t provide custom getters/setters.' }
      ]
    },
    {
      question: 'What PHP 8.0+ feature is similar to Python decorators and is used extensively in Laravel for routing and validation?',
      options: [
        { text: 'Enums', correct: false, explanation: 'Enums provide type-safe constants, but they\'re not similar to Python decorators.' },
        { text: 'Attributes', correct: true, explanation: 'Correct! PHP 8.0+ attributes are similar to Python decorators. They provide metadata that can be read via reflection and are used extensively in Laravel for routing, validation, and dependency injection.' },
        { text: 'Property hooks', correct: false, explanation: 'Property hooks provide custom getters/setters, but they\'re not similar to Python decorators.' },
        { text: 'Match expressions', correct: false, explanation: 'Match expressions are similar to Python\'s match statement, not decorators.' }
      ]
    },
    {
      question: 'Which PHP version introduced enums, and how do they compare to Python\'s Enum class?',
      options: [
        { text: 'PHP 8.0 introduced enums, which are identical to Python enums', correct: false, explanation: 'PHP 8.1 introduced enums, not PHP 8.0. While similar, they have some differences from Python enums.' },
        { text: 'PHP 8.1 introduced enums, which provide type safety similar to Python\'s Enum class', correct: true, explanation: 'Correct! PHP 8.1 introduced enums that provide type-safe constants with methods, similar to Python\'s Enum class. They\'re commonly used in Laravel for status fields, permissions, and configuration values.' },
        { text: 'PHP 8.4 introduced enums as part of the property hooks feature', correct: false, explanation: 'Enums were introduced in PHP 8.1, not PHP 8.4, and they\'re not related to property hooks.' },
        { text: 'PHP doesn\'t have enums, only class constants', correct: false, explanation: 'PHP 8.1+ does have enums, which provide more type safety than class constants.' }
      ]
    }
  ]"
/>

## Further Reading

To deepen your understanding:

- [PHP 8.4 Release Notes](https://www.php.net/releases/8.4/en.php) — What's new in PHP 8.4
- [PHP 8.0 New Features](https://www.php.net/manual/en/migration80.new-features.php) — PHP 8.0+ improvements
- [PSR Standards](https://www.php-fig.org/psr/) — PHP Framework Interop Group standards
- [PHP: The Right Way](https://phptherightway.com/) — Modern PHP best practices
- [Composer Documentation](https://getcomposer.org/doc/) — PHP dependency management
- [PHP Performance](https://www.php.net/manual/en/features.gc.performance-considerations.php) — PHP performance considerations
- [Python vs PHP Performance](https://www.google.com/search?q=php+8+vs+python+performance+benchmark) — Performance comparison resources

---

::: tip Ready to Explore Laravel's Developer Experience?
Head to [Chapter 03: Laravel's Developer Experience: Productivity, Conventions and Tools](/series/python-developers-love-php-laravel/chapters/03-laravel-developer-experience-productivity-tools) to learn about Artisan CLI, migrations, and Laravel conventions!
:::
