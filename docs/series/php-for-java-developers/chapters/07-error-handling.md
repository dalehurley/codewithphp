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

## What You'll Build

In this chapter, you'll create:
- Custom exception classes (UserNotFoundException, ValidationException, DatabaseException)
- A Result object pattern implementation for expected failures
- An API error handling system with global exception handler
- Structured logging with context-rich exceptions
- Exception handling tests using PHPUnit

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
# filename: exception-basic.php
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

**Expected Result:**

```
Error: Division by zero
Cleanup code runs regardless
```

**Why It Works:**

PHP's exception handling mirrors Java's try-catch-finally structure. When `divide()` throws an `InvalidArgumentException`, execution jumps to the matching catch block. The finally block always executes, regardless of whether an exception was thrown or caught. This ensures cleanup code runs even if errors occur.

### Exception Methods

```php
# filename: exception-methods.php
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

**Expected Result:**

```
Message: Something went wrong
Code: 500
File: /path/to/exception-methods.php
Line: 120
Trace:
#0 /path/to/exception-methods.php(120): Exception->__construct('Something went wrong', 500)
...
```

**Why It Works:**

PHP exceptions provide several methods to inspect error details. `getMessage()` returns the exception message, `getCode()` returns the error code, `getFile()` and `getLine()` show where the exception was thrown, and `getTraceAsString()` provides a formatted stack trace. These methods help debug issues by providing context about where and why exceptions occurred.

---

## Section 2: Exception Hierarchy

### Goal

Understand PHP's exception hierarchy and how it compares to Java.

### Built-in Exception Classes

```php
# filename: exception-hierarchy.php
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

**Expected Result:**

```
Out of bounds: Index out of bounds
```

**Why It Works:**

PHP's exception hierarchy allows catching specific exception types. When multiple catch blocks are present, PHP matches exceptions from most specific to least specific. Here, `OutOfBoundsException` is caught first because it's the exact type thrown. If it weren't present, the `RuntimeException` catch would handle it (since `OutOfBoundsException` extends `RuntimeException`), and if that weren't present, the generic `Exception` catch would handle it.

### Multiple Catch Blocks

```php
# filename: multiple-catch.php
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

**Expected Result:**

```
Input error: Bad argument
```

**Why It Works:**

PHP 7.1+ allows catching multiple exception types in a single catch block using the pipe (`|`) operator. This reduces code duplication when the same handling logic applies to multiple exception types. The catch block matches if the thrown exception is an instance of any of the specified types.

---

## Section 3: Custom Exceptions

### Goal

Create custom exception classes for specific error conditions.

### Custom Exception Classes

::: code-group

```php [PHP Custom Exceptions]
# filename: custom-exceptions.php
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

**Expected Result:**

```
Not found: User with ID 1001 not found
```

**Why It Works:**

Custom exceptions allow you to create domain-specific error types with additional context. `UserNotFoundException` automatically includes the user ID in its message, while `ValidationException` stores validation errors in an array accessible via `getErrors()`. This makes error handling more expressive and provides richer error information to callers.

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
# filename: error-to-exception.php
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

**Expected Result:**

```
Type error: Argument #1 ($value) must be of type int, string given
Division error: Division by zero
Something went wrong: [error message]
```

**Why It Works:**

PHP 7+ converts most errors to exceptions. `TypeError` is thrown when type declarations are violated, `DivisionByZeroError` is thrown for division by zero operations. The `Throwable` interface is the base for both `Exception` and `Error`, allowing you to catch both types in a single catch block. This unified error handling makes PHP's error system more consistent and easier to work with.

### Error Levels (Legacy)

```php
# filename: legacy-error-handling.php
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

**Expected Result:**

```
File error: file_get_contents(/nonexistent/file): Failed to open stream: No such file or directory
```

**Why It Works:**

Legacy PHP error handling used error levels (E_ERROR, E_WARNING, etc.) instead of exceptions. Modern PHP code should use exceptions, but you can convert legacy errors to exceptions using `set_error_handler()`. This allows you to handle all errors uniformly using try-catch blocks, making error handling more consistent across your application.

---

## Section 5: Finally Block

### Goal

Master the finally block for cleanup code.

### Finally Block Behavior

```php
# filename: finally-block.php
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

**Expected Result:**

```
Error: Cannot open file
File closed
```

**Why It Works:**

The finally block always executes, even when exceptions are thrown. This makes it perfect for cleanup operations like closing file handles, database connections, or releasing resources. Even though the file couldn't be opened and an exception was thrown, the finally block ensures the file handle is checked and closed if it was opened, preventing resource leaks.

### Finally with Return

```php
# filename: finally-return.php
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

**Expected Result:**

```
Finally block executed
```

**Why It Works:**

The finally block executes before the return statement completes. This ensures cleanup code runs even when a function returns early. The return value is preserved, but the finally block's code executes first. This behavior is consistent with Java's finally block and ensures predictable cleanup regardless of how the function exits.

---

## Section 6: Exception Best Practices

### Goal

Learn exception handling best practices.

### Do's and Don'ts

```php
# filename: exception-best-practices.php
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
# filename: exception-chaining.php
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
# filename: api-error-handling.php
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

## Section 8: Result Objects & Railway Oriented Programming

### Goal

Learn modern alternatives to exceptions for expected failures.

### The Problem with Exceptions for Control Flow

```php
# filename: exception-control-flow-problem.php
<?php

declare(strict_types=1);

// ❌ Using exceptions for expected failures (expensive)
class UserService
{
    public function authenticate(string $email, string $password): User
    {
        $user = $this->findByEmail($email);

        if ($user === null) {
            throw new UserNotFoundException();  // Expected case!
        }

        if (!$this->verifyPassword($user, $password)) {
            throw new InvalidPasswordException();  // Expected case!
        }

        return $user;
    }
}

// Caller must catch exceptions for normal flow
try {
    $user = $userService->authenticate($email, $password);
    // Success path
} catch (UserNotFoundException $e) {
    // Expected failure - wrong email
} catch (InvalidPasswordException $e) {
    // Expected failure - wrong password
}
```

### Result Object Pattern

::: code-group

```php [Result Class]
# filename: result-class.php
<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Result object for operations that can fail
 *
 * @template T
 */
final class Result
{
    private function __construct(
        private readonly bool $success,
        private readonly mixed $value,
        private readonly ?string $error
    ) {}

    /**
     * @template U
     * @param U $value
     * @return Result<U>
     */
    public static function ok(mixed $value): self
    {
        return new self(true, $value, null);
    }

    /**
     * @template U
     * @param string $error
     * @return Result<U>
     */
    public static function fail(string $error): self
    {
        return new self(false, null, $error);
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function isFailure(): bool
    {
        return !$this->success;
    }

    /**
     * @return T
     */
    public function getValue(): mixed
    {
        if ($this->isFailure()) {
            throw new \LogicException("Cannot get value from failed result");
        }
        return $this->value;
    }

    public function getError(): string
    {
        if ($this->isSuccess()) {
            throw new \LogicException("Cannot get error from successful result");
        }
        return $this->error;
    }

    /**
     * Get value or default if failed
     * @return T
     */
    public function getOrDefault(mixed $default): mixed
    {
        return $this->isSuccess() ? $this->value : $default;
    }

    /**
     * Map the success value to another value
     * @template U
     * @param callable(T): U $mapper
     * @return Result<U>
     */
    public function map(callable $mapper): self
    {
        if ($this->isFailure()) {
            return self::fail($this->error);
        }

        return self::ok($mapper($this->value));
    }

    /**
     * Chain operations that return Results
     * @template U
     * @param callable(T): Result<U> $mapper
     * @return Result<U>
     */
    public function flatMap(callable $mapper): self
    {
        if ($this->isFailure()) {
            return self::fail($this->error);
        }

        return $mapper($this->value);
    }
}
```

```php [Using Result Objects]
# filename: result-usage.php
<?php

declare(strict_types=1);

use App\Support\Result;

class UserService
{
    /**
     * @return Result<User>
     */
    public function authenticate(string $email, string $password): Result
    {
        $user = $this->findByEmail($email);

        if ($user === null) {
            return Result::fail("User not found");
        }

        if (!$this->verifyPassword($user, $password)) {
            return Result::fail("Invalid password");
        }

        return Result::ok($user);
    }

    /**
     * @return Result<User>
     */
    public function register(array $data): Result
    {
        // Validate
        if (empty($data['email'])) {
            return Result::fail("Email is required");
        }

        if ($this->emailExists($data['email'])) {
            return Result::fail("Email already registered");
        }

        $user = new User($data);
        $this->repository->save($user);

        return Result::ok($user);
    }
}

// Usage: Explicit success/failure handling
$result = $userService->authenticate($email, $password);

if ($result->isSuccess()) {
    $user = $result->getValue();
    session_start();
    $_SESSION['user_id'] = $user->id;
    redirect('/dashboard');
} else {
    $error = $result->getError();
    showError($error);
}

// Chaining operations
$result = $userService->register($data)
    ->map(fn(User $user) => $this->sendWelcomeEmail($user))
    ->map(fn(User $user) => $this->createDefaultSettings($user))
    ->flatMap(fn(User $user) => $this->authenticate($user->email, $data['password']));

if ($result->isSuccess()) {
    echo "Registration complete!\n";
}
```

```java [Java Comparison - Optional/Result]
import java.util.Optional;

public class UserService {
    // Java 8+ Optional for nullable values
    public Optional<User> findByEmail(String email) {
        User user = repository.findByEmail(email);
        return Optional.ofNullable(user);
    }

    // Custom Result type (similar pattern)
    public Result<User> authenticate(String email, String password) {
        return findByEmail(email)
            .map(user -> verifyPassword(user, password)
                ? Result.ok(user)
                : Result.fail("Invalid password"))
            .orElse(Result.fail("User not found"));
    }

    // Usage
    Result<User> result = userService.authenticate(email, password);

    if (result.isSuccess()) {
        User user = result.getValue();
        // Success handling
    } else {
        String error = result.getError();
        // Error handling
    }
}
```

:::

### When to Use Result vs Exception

| Scenario | Use Result Object | Use Exception |
|----------|------------------|---------------|
| **Expected failures** | ✅ Login failure, validation errors | ❌ Too expensive |
| **Business logic errors** | ✅ Insufficient funds, out of stock | ❌ Not exceptional |
| **Unexpected errors** | ❌ Use exceptions | ✅ Database connection failure |
| **Performance critical** | ✅ Faster (no stack trace) | ❌ Expensive to throw |
| **External I/O errors** | Maybe (depends) | ✅ File not found, network error |
| **Programming errors** | ❌ Use exceptions | ✅ Null pointer, type error |

::: tip Best Practice
- **Result objects**: For expected failures (validation, business rules)
- **Exceptions**: For unexpected errors (system failures, programming errors)
- **Null/Boolean**: For simple yes/no cases with no error context needed
:::

---

## Section 9: Structured Logging & Error Context

### Goal

Learn to log exceptions with rich context for debugging.

### Exception Context

```php
# filename: enriched-exception.php
<?php

declare(strict_types=1);

namespace App\Exceptions;

use Throwable;

class EnrichedException extends \Exception
{
    private array $context = [];

    public function __construct(
        string $message,
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);

        // Automatically capture context
        $this->context['timestamp'] = date('c');
        $this->context['memory_usage'] = memory_get_usage(true);
        $this->context['request_id'] = $_SERVER['HTTP_X_REQUEST_ID'] ?? uniqid();
    }

    public function setContext(array $context): self
    {
        $this->context = array_merge($this->context, $context);
        return $this;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function toArray(): array
    {
        return [
            'message' => $this->getMessage(),
            'code' => $this->getCode(),
            'file' => $this->getFile(),
            'line' => $this->getLine(),
            'context' => $this->context,
            'trace' => $this->getTraceAsString(),
            'previous' => $this->getPrevious()?->getMessage(),
        ];
    }
}

// Usage with context
class DatabaseException extends EnrichedException {}

try {
    $result = $pdo->query($sql);
} catch (PDOException $e) {
    throw (new DatabaseException("Query failed", 0, $e))
        ->setContext([
            'query' => $sql,
            'bindings' => $bindings,
            'connection' => $connectionName,
            'duration_ms' => $duration,
        ]);
}
```

### Structured Logging

::: code-group

```php [Logger Interface]
# filename: structured-logger.php
<?php

declare(strict_types=1);

namespace App\Logging;

interface Logger
{
    public function emergency(string $message, array $context = []): void;
    public function alert(string $message, array $context = []): void;
    public function critical(string $message, array $context = []): void;
    public function error(string $message, array $context = []): void;
    public function warning(string $message, array $context = []): void;
    public function notice(string $message, array $context = []): void;
    public function info(string $message, array $context = []): void;
    public function debug(string $message, array $context = []): void;
}

class JsonLogger implements Logger
{
    public function __construct(
        private string $logPath
    ) {}

    public function error(string $message, array $context = []): void
    {
        $this->log('ERROR', $message, $context);
    }

    private function log(string $level, string $message, array $context): void
    {
        $entry = [
            'timestamp' => date('c'),
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'request_id' => $_SERVER['HTTP_X_REQUEST_ID'] ?? null,
            'user_id' => $_SESSION['user_id'] ?? null,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'url' => $_SERVER['REQUEST_URI'] ?? null,
            'method' => $_SERVER['REQUEST_METHOD'] ?? null,
        ];

        // Write as JSON line
        file_put_contents(
            $this->logPath,
            json_encode($entry) . "\n",
            FILE_APPEND | LOCK_EX
        );
    }
}
```

```php [Exception Handler with Logging]
# filename: exception-handler-logging.php
<?php

declare(strict_types=1);

use App\Logging\Logger;

class ExceptionHandler
{
    public function __construct(
        private Logger $logger,
        private bool $debug = false
    ) {}

    public function handle(\Throwable $e): void
    {
        // Log with full context
        $this->logger->error($e->getMessage(), [
            'exception' => get_class($e),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $this->formatTrace($e),
            'previous' => $this->getPreviousChain($e),
            'context' => method_exists($e, 'getContext')
                ? $e->getContext()
                : [],
        ]);

        // Return appropriate response
        if ($this->debug) {
            $this->renderDebugError($e);
        } else {
            $this->renderProductionError($e);
        }
    }

    private function formatTrace(\Throwable $e): array
    {
        return array_map(function($trace) {
            return [
                'file' => $trace['file'] ?? 'unknown',
                'line' => $trace['line'] ?? 0,
                'function' => $trace['function'] ?? 'unknown',
                'class' => $trace['class'] ?? null,
            ];
        }, $e->getTrace());
    }

    private function getPreviousChain(\Throwable $e): array
    {
        $chain = [];
        $current = $e->getPrevious();

        while ($current !== null) {
            $chain[] = [
                'exception' => get_class($current),
                'message' => $current->getMessage(),
                'code' => $current->getCode(),
            ];
            $current = $current->getPrevious();
        }

        return $chain;
    }

    private function renderProductionError(\Throwable $e): void
    {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'Internal server error',
            'reference' => $_SERVER['HTTP_X_REQUEST_ID'] ?? uniqid(),
        ]);
    }

    private function renderDebugError(\Throwable $e): void
    {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => $e->getMessage(),
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTrace(),
        ], JSON_PRETTY_PRINT);
    }
}

// Register handler
$logger = new JsonLogger(__DIR__ . '/logs/app.log');
$handler = new ExceptionHandler($logger, debug: true);

set_exception_handler([$handler, 'handle']);
```

:::

### Log Aggregation & Search

```php
<?php

// Structured logs can be easily searched and analyzed

// Example log entry:
// {"timestamp":"2025-01-15T10:30:45+00:00","level":"ERROR","message":"Database connection failed","context":{"host":"localhost","port":3306,"database":"myapp","duration_ms":5000},"request_id":"abc123","user_id":42}

// Search logs with jq:
// cat logs/app.log | jq 'select(.level == "ERROR")'
// cat logs/app.log | jq 'select(.context.database == "myapp")'
// cat logs/app.log | jq 'select(.user_id == 42)'

// Or use log aggregation services:
// - ELK Stack (Elasticsearch, Logstash, Kibana)
// - Splunk
// - Datadog
// - New Relic
```

---

## Section 10: Performance Considerations

### Goal

Understand the performance impact of exceptions and when to avoid them.

### Exception Cost

```php
# filename: exception-performance-benchmark.php
<?php

declare(strict_types=1);

// Benchmark: Exception vs Return Value

function testExceptions(int $iterations): float
{
    $start = microtime(true);

    for ($i = 0; $i < $iterations; $i++) {
        try {
            if ($i % 2 === 0) {
                throw new Exception("Error");
            }
        } catch (Exception $e) {
            // Handle
        }
    }

    return microtime(true) - $start;
}

function testReturnValues(int $iterations): float
{
    $start = microtime(true);

    for ($i = 0; $i < $iterations; $i++) {
        $result = ($i % 2 === 0) ? null : $i;
        if ($result === null) {
            // Handle
        }
    }

    return microtime(true) - $start;
}

$iterations = 10000;

$exceptionTime = testExceptions($iterations);
$returnTime = testReturnValues($iterations);

echo "Exceptions: {$exceptionTime}s\n";
echo "Return values: {$returnTime}s\n";
echo "Exceptions are " . round($exceptionTime / $returnTime, 2) . "x slower\n";

// Typical results:
// Exceptions: 0.25s
// Return values: 0.001s
// Exceptions are 250x slower
```

### Best Practices for Performance

::: warning Performance Guidelines

**1. Don't use exceptions for control flow**
```php
// ❌ BAD: Exception in loop
for ($i = 0; $i < 1000000; $i++) {
    try {
        $result = divide($a, $b);
    } catch (DivisionByZeroException $e) {
        $result = 0;
    }
}

// ✅ GOOD: Check before operation
for ($i = 0; $i < 1000000; $i++) {
    $result = ($b !== 0) ? $a / $b : 0;
}
```

**2. Use early returns instead of exceptions for validation**
```php
// ❌ BAD: Throw exception for expected validation
function processUser(array $data): User
{
    if (empty($data['email'])) {
        throw new ValidationException("Email required");
    }
    // ... more validation
}

// ✅ GOOD: Return Result object
function processUser(array $data): Result
{
    if (empty($data['email'])) {
        return Result::fail("Email required");
    }
    // ... more validation
    return Result::ok($user);
}
```

**3. Catch exceptions at the right level**
```php
// ❌ BAD: Catch-and-rethrow in every method
function a() {
    try {
        b();
    } catch (Exception $e) {
        throw $e; // Unnecessary
    }
}

function b() {
    try {
        c();
    } catch (Exception $e) {
        throw $e; // Unnecessary
    }
}

// ✅ GOOD: Let exceptions bubble up
function a() {
    b(); // Exception propagates naturally
}

function b() {
    c(); // Exception propagates naturally
}

// Only catch at boundary
try {
    a();
} catch (Exception $e) {
    handleError($e);
}
```

:::

### When Exceptions Are OK

```php
# filename: when-exceptions-ok.php
<?php

declare(strict_types=1);

// ✅ Exceptions are appropriate for:

// 1. Truly exceptional conditions
try {
    $pdo = new PDO($dsn, $user, $pass);
} catch (PDOException $e) {
    // Database unavailable is exceptional
    die("Database connection failed");
}

// 2. External I/O failures
try {
    $content = file_get_contents($url);
} catch (Exception $e) {
    // Network/file errors are exceptional
    logError($e);
}

// 3. Programming errors
function divide(int $a, int $b): float
{
    if ($b === 0) {
        // Programming error - should never happen
        throw new LogicException("Division by zero");
    }
    return $a / $b;
}

// 4. Transaction failures
try {
    $pdo->beginTransaction();
    $pdo->exec($sql1);
    $pdo->exec($sql2);
    $pdo->commit();
} catch (PDOException $e) {
    $pdo->rollBack();
    throw $e;
}
```

---

## Section 11: Testing Exception Handling

### Goal

Learn to test exception scenarios effectively.

### Testing Exceptions

::: code-group

```php [PHPUnit Tests]
# filename: UserServiceTest.php
<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class UserServiceTest extends TestCase
{
    private UserService $service;

    protected function setUp(): void
    {
        $this->service = new UserService();
    }

    /**
     * Test that exception is thrown
     */
    public function testFindUserThrowsExceptionForInvalidId(): void
    {
        // Expect specific exception
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("User ID must be positive");
        $this->expectExceptionCode(400);

        $this->service->findUser(-1);
    }

    /**
     * Test exception message contains specific text
     */
    public function testDatabaseExceptionContainsQuery(): void
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessageMatches('/SELECT.*users/');

        $this->service->executeQuery("SELECT * FROM users WHERE invalid");
    }

    /**
     * Test exception with try-catch (more flexible)
     */
    public function testValidationExceptionIncludesErrors(): void
    {
        try {
            $this->service->createUser(['name' => '']); // Missing email
            $this->fail('Expected ValidationException was not thrown');
        } catch (ValidationException $e) {
            // Assert exception properties
            $this->assertEquals('Validation failed', $e->getMessage());
            $this->assertArrayHasKey('email', $e->getErrors());
            $this->assertEquals('Email is required', $e->getErrors()['email']);
        }
    }

    /**
     * Test no exception is thrown
     */
    public function testSuccessfulOperationDoesNotThrow(): void
    {
        // This test passes if no exception is thrown
        $user = $this->service->findUser(1);
        $this->assertInstanceOf(User::class, $user);
    }

    /**
     * Test exception chaining
     */
    public function testExceptionChainPreservesCause(): void
    {
        try {
            $this->service->complexOperation();
            $this->fail('Expected exception');
        } catch (ServiceException $e) {
            // Check outer exception
            $this->assertEquals('Service unavailable', $e->getMessage());

            // Check inner exception
            $previous = $e->getPrevious();
            $this->assertInstanceOf(DatabaseException::class, $previous);
            $this->assertEquals('Connection failed', $previous->getMessage());
        }
    }
}
```

```php [Testing Result Objects]
# filename: UserServiceWithResultTest.php
<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Support\Result;

class UserServiceWithResultTest extends TestCase
{
    private UserService $service;

    protected function setUp(): void
    {
        $this->service = new UserService();
    }

    public function testAuthenticateReturnsSuccessForValidCredentials(): void
    {
        $result = $this->service->authenticate('user@example.com', 'password');

        $this->assertTrue($result->isSuccess());
        $this->assertFalse($result->isFailure());
        $this->assertInstanceOf(User::class, $result->getValue());
    }

    public function testAuthenticateReturnsFailureForInvalidEmail(): void
    {
        $result = $this->service->authenticate('invalid@example.com', 'password');

        $this->assertFalse($result->isSuccess());
        $this->assertTrue($result->isFailure());
        $this->assertEquals('User not found', $result->getError());
    }

    public function testAuthenticateReturnsFailureForInvalidPassword(): void
    {
        $result = $this->service->authenticate('user@example.com', 'wrong');

        $this->assertTrue($result->isFailure());
        $this->assertEquals('Invalid password', $result->getError());
    }

    public function testResultChaining(): void
    {
        $result = $this->service->register([
            'email' => 'new@example.com',
            'password' => 'password123'
        ])
        ->map(fn($user) => $user->id)
        ->map(fn($id) => $id * 2);

        $this->assertTrue($result->isSuccess());
        $this->assertIsInt($result->getValue());
    }
}
```

```java [Java JUnit Comparison]
import org.junit.jupiter.api.Test;
import static org.junit.jupiter.api.Assertions.*;

class UserServiceTest {
    private UserService service = new UserService();

    @Test
    void testFindUserThrowsExceptionForInvalidId() {
        // JUnit 5 - assertThrows
        InvalidArgumentException exception = assertThrows(
            InvalidArgumentException.class,
            () -> service.findUser(-1)
        );

        assertEquals("User ID must be positive", exception.getMessage());
    }

    @Test
    void testValidationExceptionIncludesErrors() {
        ValidationException exception = assertThrows(
            ValidationException.class,
            () -> service.createUser(Map.of("name", ""))
        );

        assertTrue(exception.getErrors().containsKey("email"));
        assertEquals("Email is required", exception.getErrors().get("email"));
    }

    @Test
    void testSuccessfulOperationDoesNotThrow() {
        // No exception expected
        assertDoesNotThrow(() -> {
            User user = service.findUser(1);
            assertNotNull(user);
        });
    }
}
```

:::

### Mock Exceptions in Tests

```php
# filename: PaymentServiceTest.php
<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class PaymentServiceTest extends TestCase
{
    public function testHandlesGatewayFailureGracefully(): void
    {
        // Create mock that throws exception
        $gateway = $this->createMock(PaymentGateway::class);
        $gateway->method('charge')
            ->willThrowException(new GatewayException('Connection timeout'));

        $service = new PaymentService($gateway);

        // Expect service to handle exception
        $result = $service->processPayment(100.00);

        $this->assertFalse($result->isSuccess());
        $this->assertStringContainsString('timeout', $result->getError());
    }

    public function testRetriesOnTransientFailure(): void
    {
        $gateway = $this->createMock(PaymentGateway::class);

        // Throw exception first 2 times, then succeed
        $gateway->method('charge')
            ->will($this->onConsecutiveCalls(
                $this->throwException(new TransientException('Timeout')),
                $this->throwException(new TransientException('Timeout')),
                $this->returnValue(['transaction_id' => '12345'])
            ));

        $service = new PaymentService($gateway, maxRetries: 3);
        $result = $service->processPayment(100.00);

        $this->assertTrue($result->isSuccess());
    }
}
```

---

## Section 12: PHP 8+ Error Handling Features

### Goal

Leverage modern PHP 8+ features for better error handling.

### Never Return Type

```php
# filename: never-type.php
<?php

declare(strict_types=1);

// PHP 8.1+: never type indicates function never returns normally
function abort(string $message, int $code = 500): never
{
    http_response_code($code);
    echo json_encode(['error' => $message]);
    exit();
}

function failFast(string $error): never
{
    throw new \RuntimeException($error);
}

// Type system knows these never return
function processRequest(): void
{
    if (!authenticated()) {
        abort('Unauthorized', 401);  // Compiler knows this exits
        // No code here is reachable
    }

    if (!validated()) {
        failFast('Invalid input');  // Compiler knows this throws
        // No code here is reachable
    }

    // Continue normal flow
    echo "Processing...\n";
}
```

### Union Types for Error Handling

```php
# filename: union-types-error-handling.php
<?php

declare(strict_types=1);

// PHP 8.0+: Union types for nullable or error results

class UserService
{
    /**
     * Returns User or null (using union type)
     */
    public function findUser(int $id): User|null
    {
        return $this->repository->find($id);
    }

    /**
     * Returns User or error message
     */
    public function authenticate(string $email, string $password): User|string
    {
        $user = $this->findByEmail($email);

        if ($user === null) {
            return "User not found";
        }

        if (!$this->verifyPassword($user, $password)) {
            return "Invalid password";
        }

        return $user;
    }
}

// Usage with pattern matching
$result = $userService->authenticate($email, $password);

match (true) {
    $result instanceof User => redirectToDashboard($result),
    is_string($result) => showError($result),
};

// Or with type checking
if ($result instanceof User) {
    // Success path
    $_SESSION['user'] = $result;
} else {
    // Error path
    $errorMessage = $result;
    showError($errorMessage);
}
```

### Intersection Types

```php
# filename: intersection-types.php
<?php

declare(strict_types=1);

// PHP 8.1+: Intersection types

interface Loggable
{
    public function toLog(): array;
}

interface Notifiable
{
    public function getNotificationChannels(): array;
}

// Exception that is both Loggable and Notifiable
function handleSpecialException(Loggable&Notifiable $exception): void
{
    // Can call methods from both interfaces
    $logData = $exception->toLog();
    $channels = $exception->getNotificationChannels();

    // Log and notify
    logger()->error($logData);
    notifier()->send($channels, $exception);
}

class CriticalException extends \Exception implements Loggable, Notifiable
{
    public function toLog(): array
    {
        return [
            'message' => $this->getMessage(),
            'severity' => 'critical',
        ];
    }

    public function getNotificationChannels(): array
    {
        return ['slack', 'email', 'pagerduty'];
    }
}
```

### Match Expression for Error Handling

```php
# filename: match-expression-error-handling.php
<?php

declare(strict_types=1);

// PHP 8.0+: Match expression (better than switch for exceptions)

function handleError(\Throwable $e): never
{
    $response = match (true) {
        $e instanceof ValidationException => [
            'status' => 422,
            'message' => $e->getMessage(),
            'errors' => $e->getErrors(),
        ],
        $e instanceof NotFoundException => [
            'status' => 404,
            'message' => $e->getMessage(),
        ],
        $e instanceof UnauthorizedException => [
            'status' => 401,
            'message' => 'Unauthorized',
        ],
        $e instanceof \PDOException => [
            'status' => 500,
            'message' => 'Database error',
        ],
        default => [
            'status' => 500,
            'message' => 'Internal server error',
        ],
    };

    http_response_code($response['status']);
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

set_exception_handler('handleError');
```

---

## Exercises

### Exercise 1: Custom Exception Classes

Create a file called `custom-exceptions.php` and implement:

**Requirements:**
- Create `PaymentException` base class extending `Exception`
- Create `InsufficientFundsException` extending `PaymentException`
- Create `InvalidCardException` extending `PaymentException`
- Create `PaymentProcessor` class with `processPayment()` method
- Throw appropriate exceptions based on validation logic
- Include meaningful error messages and error codes

**Validation:** Test your implementation:

```php
$processor = new PaymentProcessor();

try {
    $processor->processPayment(-100, '1234');
} catch (InsufficientFundsException $e) {
    echo "Expected: {$e->getMessage()}\n";
}

try {
    $processor->processPayment(100, 'invalid');
} catch (InvalidCardException $e) {
    echo "Expected: {$e->getMessage()}\n";
}
```

### Exercise 2: Result Object Pattern

Implement a `Result` class and refactor a service to use it:

**Requirements:**
- Create a `Result` class with `ok()` and `fail()` static methods
- Implement `isSuccess()`, `isFailure()`, `getValue()`, `getError()` methods
- Create `UserService` with `authenticate()` method returning `Result<User>`
- Handle both success and failure cases without exceptions
- Chain operations using `map()` and `flatMap()` methods

**Validation:** Test your implementation:

```php
$service = new UserService();

$result = $service->authenticate('user@example.com', 'password');

if ($result->isSuccess()) {
    $user = $result->getValue();
    echo "Authenticated: {$user->email}\n";
} else {
    echo "Error: {$result->getError()}\n";
}
```

### Exercise 3: Global Exception Handler

Create a global exception handler for a web API:

**Requirements:**
- Create custom exception classes: `ValidationException`, `NotFoundException`, `UnauthorizedException`
- Implement `ApiExceptionHandler` class
- Use `match` expression to map exceptions to HTTP status codes
- Return JSON responses with error details
- Log exceptions with context (request ID, user ID, timestamp)
- Set up the handler using `set_exception_handler()`

**Validation:** Test your implementation:

```php
// Should return 422 JSON response
throw new ValidationException('Email is required', ['email' => 'Required']);

// Should return 404 JSON response
throw new NotFoundException('User not found');

// Should return 401 JSON response
throw new UnauthorizedException('Invalid token');
```

### Exercise 4: Exception Chaining

Implement exception chaining for a multi-layer application:

**Requirements:**
- Create `DatabaseException`, `ServiceException`, `ControllerException`
- Implement a `UserService` that catches `DatabaseException` and wraps it in `ServiceException`
- Implement a `UserController` that catches `ServiceException` and wraps it in `ControllerException`
- Preserve the exception chain using the `$previous` parameter
- Display the full exception chain when handling errors

**Validation:** Test your implementation:

```php
try {
    $controller->getUser(123);
} catch (ControllerException $e) {
    // Should show full chain: ControllerException -> ServiceException -> DatabaseException
    $current = $e;
    while ($current !== null) {
        echo get_class($current) . ": {$current->getMessage()}\n";
        $current = $current->getPrevious();
    }
}
```

### Exercise 5: Structured Logging

Create a structured logging system for exceptions:

**Requirements:**
- Create `StructuredLogger` class implementing PSR-3-like interface
- Log exceptions as JSON with context (timestamp, level, message, exception class, file, line, trace)
- Include request context (request ID, user ID, IP address, URL, method)
- Create `EnrichedException` class that captures context automatically
- Use the logger in exception handler

**Validation:** Test your implementation:

```php
$logger = new StructuredLogger('logs/app.log');

try {
    throw new EnrichedException('Database query failed')
        ->setContext(['query' => 'SELECT * FROM users', 'duration_ms' => 500]);
} catch (EnrichedException $e) {
    $logger->error($e->getMessage(), $e->getContext());
}

// Check logs/app.log for JSON entry
```

---

## Troubleshooting

### Error: "Uncaught Exception: ..."

**Symptom:** Exception is thrown but not caught, causing a fatal error.

**Cause:** No try-catch block around code that throws exceptions, or exception type doesn't match catch block.

**Solution:**

```php
// ❌ BAD: No exception handling
function riskyOperation() {
    throw new Exception("Error");
}
riskyOperation(); // Fatal error!

// ✅ GOOD: Proper exception handling
try {
    riskyOperation();
} catch (Exception $e) {
    echo "Caught: {$e->getMessage()}\n";
}
```

### Error: "Catch block order matters"

**Symptom:** More specific exceptions are caught by generic catch blocks.

**Cause:** Catch blocks are evaluated in order, and generic exceptions catch specific ones.

**Solution:**

```php
// ❌ BAD: Generic catch first
try {
    throw new InvalidArgumentException("Bad input");
} catch (Exception $e) {  // Catches everything!
    echo "Generic error";
} catch (InvalidArgumentException $e) {  // Never reached
    echo "Invalid argument";
}

// ✅ GOOD: Specific catches first
try {
    throw new InvalidArgumentException("Bad input");
} catch (InvalidArgumentException $e) {  // Caught here
    echo "Invalid argument";
} catch (Exception $e) {  // Fallback for other exceptions
    echo "Generic error";
}
```

### Error: "Finally block not executing"

**Symptom:** Finally block code doesn't run.

**Cause:** Fatal errors or script termination (`exit()`, `die()`) bypass finally blocks.

**Solution:**

```php
// ❌ BAD: exit() prevents finally from running
try {
    exit("Script ends");
} finally {
    echo "This never runs";
}

// ✅ GOOD: Use return or throw instead
try {
    return "Script ends";
} finally {
    echo "This runs before return";
}
```

### Error: "Exception context lost"

**Symptom:** Exception thrown but context information is missing.

**Cause:** Not preserving exception chain or not adding context to exceptions.

**Solution:**

```php
// ❌ BAD: Context lost
try {
    throw new DatabaseException("Query failed");
} catch (DatabaseException $e) {
    throw new ServiceException("Service unavailable");  // Original exception lost
}

// ✅ GOOD: Preserve exception chain
try {
    throw new DatabaseException("Query failed");
} catch (DatabaseException $e) {
    throw new ServiceException("Service unavailable", 0, $e);  // Chain preserved
}
```

### Error: "Performance issues with exceptions"

**Symptom:** Application is slow, exceptions thrown frequently in loops.

**Cause:** Using exceptions for control flow in performance-critical code.

**Solution:**

```php
// ❌ BAD: Exception in loop (expensive)
for ($i = 0; $i < 1000000; $i++) {
    try {
        if ($i % 2 === 0) {
            throw new Exception("Even number");
        }
    } catch (Exception $e) {
        // Handle
    }
}

// ✅ GOOD: Check condition first
for ($i = 0; $i < 1000000; $i++) {
    if ($i % 2 === 0) {
        // Handle even number without exception
    }
}
```

### Error: "Exception message not helpful"

**Symptom:** Exception messages don't provide enough context for debugging.

**Cause:** Generic exception messages without context.

**Solution:**

```php
// ❌ BAD: Generic message
throw new Exception("Error");

// ✅ GOOD: Descriptive message with context
throw new ValidationException(
    "Email validation failed: '{$email}' is not a valid email address",
    ['email' => $email, 'field' => 'email', 'value' => $email]
);
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
- [ ] Implement Result objects for expected failures
- [ ] Add rich context to exceptions for debugging
- [ ] Set up structured logging
- [ ] Understand performance implications of exceptions
- [ ] Test exception scenarios with PHPUnit
- [ ] Use PHP 8+ error handling features (never type, union types, match)
- [ ] Follow exception handling best practices
- [ ] Build robust error handling systems

::: tip Completed Part 2!
Congratulations! You've completed Part 2: Object-Oriented PHP. In [Chapter 8: Composer & Dependencies](/series/php-for-java-developers/chapters/08-composer-and-dependencies), we'll begin Part 3: Modern PHP Development, learning about Composer (PHP's Maven/Gradle equivalent).
:::

<ChapterCheckbox 
  seriesId="php-for-java-developers"
  chapterId="07"
  label="Mastered PHP exception handling and error management!"
/>

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
