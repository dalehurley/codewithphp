---
title: "07: Error Handling"
description: "Master PHP exceptions and error handling with Java comparisons"
series: "php-for-java-developers"
chapter: 7
order: 7
difficulty: "Intermediate"
prerequisites:
  - "/series/php-for-java-developers/chapters/06-namespaces-and-autoloading"
---

![Error Handling Hero](/images/php-for-java-developers/chapter-07-error-handling-hero-full.webp)

# Chapter 7: Error Handling

<Badge type="warning">Intermediate</Badge> <Badge type="info">60-75 min</Badge>

## Overview

PHP's exception handling works nearly identically to Java's—you use try-catch-finally blocks, throw exceptions, and can create custom exception classes. However, PHP historically had both errors and exceptions, though modern PHP (7+) converts most errors to exceptions. In this chapter, you'll learn PHP's exception system and best practices coming from Java.

## Prerequisites

::: info Time Estimate
⏱️ **60-75 minutes** to complete this chapter
:::

**What you need:**
- Completed [Chapter 6: Namespaces & Autoloading](/series/php-for-java-developers/chapters/06-namespaces-and-autoloading)
- Understanding of Java exception handling
- Familiarity with try-catch-finally blocks

## Learning Objectives

By the end of this chapter, you'll be able to:

- **Use try-catch-finally** blocks in PHP
- **Throw and catch exceptions**
- **Create custom exception classes**
- **Understand PHP's exception hierarchy**
- **Handle multiple exception types**
- **Use modern error handling** best practices

---

## Section 1: Basic Exception Handling

### Goal

Master try-catch-finally blocks in PHP.

### Try-Catch-Finally

::: code-group

```php [PHP Exceptions]
<?php

declare(strict_types=1);

function divide(int $a, int $b): float
{
    if ($b === 0) {
        throw new InvalidArgumentException("Division by zero");
    }
    return $a / $b;
}

try {
    $result = divide(10, 0);
    echo "Result: $result\n";
} catch (InvalidArgumentException $e) {
    echo "Error: {$e->getMessage()}\n";
} finally {
    echo "Cleanup code runs regardless\n";
}
```

```java [Java Exceptions]
public class Example {
    public static double divide(int a, int b) {
        if (b == 0) {
            throw new IllegalArgumentException("Division by zero");
        }
        return (double) a / b;
    }

    public static void main(String[] args) {
        try {
            double result = divide(10, 0);
            System.out.println("Result: " + result);
        } catch (IllegalArgumentException e) {
            System.out.println("Error: " + e.getMessage());
        } finally {
            System.out.println("Cleanup code runs regardless");
        }
    }
}
```

:::

### Exception Methods

```php
<?php

declare(strict_types=1);

try {
    throw new Exception("Something went wrong", 500);
} catch (Exception $e) {
    echo "Message: " . $e->getMessage() . "\n";     // "Something went wrong"
    echo "Code: " . $e->getCode() . "\n";           // 500
    echo "File: " . $e->getFile() . "\n";           // Current file path
    echo "Line: " . $e->getLine() . "\n";           // Line number
    echo "Trace:\n" . $e->getTraceAsString() . "\n"; // Stack trace
}
```

---

## Section 2: Exception Hierarchy

### Goal

Understand PHP's exception hierarchy and how it compares to Java.

### Built-in Exception Classes

```php
<?php

declare(strict_types=1);

// Exception hierarchy in PHP
// Throwable (interface)
//   ├── Exception
//   │   ├── LogicException
//   │   │   ├── BadFunctionCallException
//   │   │   ├── BadMethodCallException
//   │   │   ├── DomainException
//   │   │   ├── InvalidArgumentException
//   │   │   ├── LengthException
//   │   │   └── OutOfRangeException
//   │   └── RuntimeException
//   │       ├── OutOfBoundsException
//   │       ├── OverflowException
//   │       ├── RangeException
//   │       ├── UnderflowException
//   │       └── UnexpectedValueException
//   └── Error
//       ├── ArithmeticError
//       ├── AssertionError
//       ├── ParseError
//       └── TypeError

// Catching specific exceptions
try {
    $array = [1, 2, 3];
    if (!isset($array[5])) {
        throw new OutOfBoundsException("Index out of bounds");
    }
} catch (OutOfBoundsException $e) {
    echo "Out of bounds: {$e->getMessage()}\n";
} catch (RuntimeException $e) {
    echo "Runtime error: {$e->getMessage()}\n";
} catch (Exception $e) {
    echo "General error: {$e->getMessage()}\n";
}
```

### Multiple Catch Blocks

```php
<?php

declare(strict_types=1);

// PHP 7.1+: Multiple exception types in one catch
try {
    // Some operation
    throw new InvalidArgumentException("Bad argument");
} catch (InvalidArgumentException | DomainException $e) {
    echo "Input error: {$e->getMessage()}\n";
} catch (RuntimeException $e) {
    echo "Runtime error: {$e->getMessage()}\n";
}
```

---

## Section 3: Custom Exceptions

### Goal

Create custom exception classes for specific error conditions.

### Custom Exception Classes

::: code-group

```php [PHP Custom Exceptions]
<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class UserNotFoundException extends Exception
{
    public function __construct(int $userId)
    {
        parent::__construct("User with ID {$userId} not found", 404);
    }
}

class ValidationException extends Exception
{
    public function __construct(
        string $message,
        private array $errors = []
    ) {
        parent::__construct($message, 422);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}

class DatabaseException extends Exception
{
    public function __construct(
        string $message,
        private string $query = ''
    ) {
        parent::__construct($message, 500);
    }

    public function getQuery(): string
    {
        return $this->query;
    }
}

// Usage
function findUser(int $id): array
{
    // Simulate database query
    if ($id <= 0) {
        throw new ValidationException(
            "Invalid user ID",
            ['id' => 'Must be positive']
        );
    }

    if ($id > 1000) {
        throw new UserNotFoundException($id);
    }

    return ['id' => $id, 'name' => 'User ' . $id];
}

try {
    $user = findUser(1001);
} catch (UserNotFoundException $e) {
    echo "Not found: {$e->getMessage()}\n";
} catch (ValidationException $e) {
    echo "Validation failed: {$e->getMessage()}\n";
    print_r($e->getErrors());
}
```

```java [Java Custom Exceptions]
// UserNotFoundException.java
package com.example.exceptions;

public class UserNotFoundException extends Exception {
    private int userId;

    public UserNotFoundException(int userId) {
        super("User with ID " + userId + " not found");
        this.userId = userId;
    }

    public int getUserId() {
        return userId;
    }
}

// ValidationException.java
package com.example.exceptions;

import java.util.Map;

public class ValidationException extends Exception {
    private Map<String, String> errors;

    public ValidationException(String message, Map<String, String> errors) {
        super(message);
        this.errors = errors;
    }

    public Map<String, String> getErrors() {
        return errors;
    }
}

// Usage
public User findUser(int id) throws UserNotFoundException, ValidationException {
    if (id <= 0) {
        throw new ValidationException(
            "Invalid user ID",
            Map.of("id", "Must be positive")
        );
    }

    if (id > 1000) {
        throw new UserNotFoundException(id);
    }

    return new User(id, "User " + id);
}
```

:::

---

## Section 4: Error vs Exception

### Goal

Understand the difference between PHP errors and exceptions.

### PHP 7+ Error Handling

```php
<?php

declare(strict_types=1);

// PHP 7+ converts most errors to exceptions

// TypeError (PHP 7+)
try {
    function requiresInt(int $value): void {
        echo "Value: $value\n";
    }

    requiresInt("string");  // TypeError thrown
} catch (TypeError $e) {
    echo "Type error: {$e->getMessage()}\n";
}

// DivisionByZeroError (PHP 7+)
try {
    $result = 10 % 0;  // DivisionByZeroError
} catch (DivisionByZeroError $e) {
    echo "Division error: {$e->getMessage()}\n";
}

// Catching both Error and Exception
try {
    // Some code
} catch (Throwable $e) {  // Catches both Error and Exception
    echo "Something went wrong: {$e->getMessage()}\n";
}
```

### Error Levels (Legacy)

```php
<?php

// Old PHP error levels (avoid in modern code)
// E_ERROR, E_WARNING, E_NOTICE, E_DEPRECATED

// Set error reporting (use exceptions instead)
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Custom error handler (converts errors to exceptions)
set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// Now warnings become exceptions
try {
    $file = file_get_contents('/nonexistent/file');
} catch (ErrorException $e) {
    echo "File error: {$e->getMessage()}\n";
}
```

---

## Section 5: Finally Block

### Goal

Master the finally block for cleanup code.

### Finally Block Behavior

```php
<?php

declare(strict_types=1);

function processFile(string $filename): void
{
    $handle = null;

    try {
        $handle = fopen($filename, 'r');

        if ($handle === false) {
            throw new RuntimeException("Cannot open file");
        }

        // Process file
        $content = fread($handle, 1024);

        if ($content === false) {
            throw new RuntimeException("Cannot read file");
        }

        echo "Content: $content\n";

    } catch (RuntimeException $e) {
        echo "Error: {$e->getMessage()}\n";

    } finally {
        // Cleanup: Always runs
        if ($handle !== null && is_resource($handle)) {
            fclose($handle);
            echo "File closed\n";
        }
    }
}

// Finally runs even if exception is thrown
processFile('/nonexistent.txt');
```

### Finally with Return

```php
<?php

declare(strict_types=1);

function testFinally(): string
{
    try {
        return "try block";
    } finally {
        // Finally runs BEFORE the return
        echo "Finally block executed\n";
    }
}

$result = testFinally();
// Output:
// Finally block executed
// Then returns "try block"
```

---

## Section 6: Exception Best Practices

### Goal

Learn exception handling best practices.

### Do's and Don'ts

```php
<?php

declare(strict_types=1);

// ✅ DO: Be specific with exceptions
class UserService
{
    public function findUser(int $id): User
    {
        if ($id <= 0) {
            throw new InvalidArgumentException("User ID must be positive");
        }

        $user = $this->repository->find($id);

        if ($user === null) {
            throw new UserNotFoundException($id);
        }

        return $user;
    }
}

// ✅ DO: Catch specific exceptions first
try {
    $user = $userService->findUser(-1);
} catch (InvalidArgumentException $e) {
    // Handle validation errors
    return $this->badRequest($e->getMessage());
} catch (UserNotFoundException $e) {
    // Handle not found
    return $this->notFound($e->getMessage());
} catch (Exception $e) {
    // Handle unexpected errors
    $this->log($e);
    return $this->serverError("Internal error");
}

// ❌ DON'T: Catch generic Exception first
try {
    $user = $userService->findUser($id);
} catch (Exception $e) {  // Too broad
    // This catches EVERYTHING
}

// ❌ DON'T: Swallow exceptions silently
try {
    riskyOperation();
} catch (Exception $e) {
    // Empty catch - bad practice!
}

// ✅ DO: Always log or handle
try {
    riskyOperation();
} catch (Exception $e) {
    $logger->error($e->getMessage(), ['exception' => $e]);
    throw $e;  // Re-throw if can't handle
}

// ✅ DO: Use meaningful exception messages
throw new InvalidArgumentException(
    "Email must be a valid format, got: {$email}"
);

// ❌ DON'T: Use generic messages
throw new Exception("Error");  // Not helpful!
```

### Exception Chaining

```php
<?php

declare(strict_types=1);

try {
    try {
        throw new DatabaseException("Connection failed");
    } catch (DatabaseException $e) {
        // Wrap in higher-level exception, preserving original
        throw new ServiceException(
            "User service unavailable",
            0,
            $e  // Previous exception
        );
    }
} catch (ServiceException $e) {
    echo "Service error: {$e->getMessage()}\n";
    echo "Caused by: {$e->getPrevious()->getMessage()}\n";

    // Full exception chain
    $current = $e;
    while ($current !== null) {
        echo "- {$current->getMessage()}\n";
        $current = $current->getPrevious();
    }
}
```

---

## Section 7: Practical Example - API Error Handling

### Goal

Build a robust error handling system for an API.

```php
<?php

declare(strict_types=1);

namespace App;

// Custom exceptions
class ApiException extends \Exception {}
class ValidationException extends ApiException {}
class NotFoundException extends ApiException {}
class UnauthorizedException extends ApiException {}

// API Response handler
class ApiResponse
{
    public static function success(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
    }

    public static function error(string $message, int $status = 500, array $details = []): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => [
                'message' => $message,
                'details' => $details
            ]
        ]);
    }
}

// Global exception handler
set_exception_handler(function(\Throwable $e) {
    // Log the exception
    error_log($e->getMessage());

    // Return appropriate response based on exception type
    match (true) {
        $e instanceof ValidationException => ApiResponse::error(
            $e->getMessage(),
            422,
            method_exists($e, 'getErrors') ? $e->getErrors() : []
        ),
        $e instanceof NotFoundException => ApiResponse::error(
            $e->getMessage(),
            404
        ),
        $e instanceof UnauthorizedException => ApiResponse::error(
            $e->getMessage(),
            401
        ),
        default => ApiResponse::error(
            'Internal server error',
            500
        )
    };
});

// Controller
class UserController
{
    public function __construct(
        private UserService $userService
    ) {}

    public function show(int $id): void
    {
        $user = $this->userService->findUser($id);
        ApiResponse::success($user);
    }

    public function store(array $data): void
    {
        // Validate
        if (empty($data['email'])) {
            throw new ValidationException(
                'Validation failed',
                ['email' => 'Email is required']
            );
        }

        $user = $this->userService->createUser($data);
        ApiResponse::success($user, 201);
    }
}
```

---

## Wrap-up Checklist

Before moving to the next chapter, ensure you can:

- [ ] Use try-catch-finally blocks
- [ ] Throw and catch exceptions
- [ ] Create custom exception classes
- [ ] Understand PHP's exception hierarchy
- [ ] Handle multiple exception types
- [ ] Use finally for cleanup code
- [ ] Follow exception handling best practices
- [ ] Build robust error handling systems

::: tip Completed Part 2!
Congratulations! You've completed Part 2: Object-Oriented PHP. In [Chapter 8: Composer & Dependencies](/series/php-for-java-developers/chapters/08-composer-and-dependencies), we'll begin Part 3: Modern PHP Development, learning about Composer (PHP's Maven/Gradle equivalent).
:::

---

## Further Reading

**PHP Documentation:**
- [Exceptions](https://www.php.net/manual/en/language.exceptions.php)
- [Error Handling](https://www.php.net/manual/en/book.errorfunc.php)
- [Throwable Interface](https://www.php.net/manual/en/class.throwable.php)

---

<div style="display: flex; justify-content: space-between; margin-top: 2rem;">
  <div>
    <strong>Previous:</strong> <a href="/series/php-for-java-developers/chapters/06-namespaces-and-autoloading">← Chapter 6: Namespaces & Autoloading</a>
  </div>
  <div>
    <strong>Next:</strong> <a href="/series/php-for-java-developers/chapters/08-composer-and-dependencies">Chapter 8: Composer & Dependencies →</a>
  </div>
</div>
