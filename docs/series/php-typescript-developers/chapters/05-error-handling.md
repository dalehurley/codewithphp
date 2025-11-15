---
title: "05: Error Handling - Try/Catch & Type Safety"
description: "Master PHP's exception handling system from a TypeScript perspective. Learn custom exceptions, error types, and type-safe error handling patterns."
series: "php-typescript-developers"
chapter: 5
order: 5
difficulty: "Intermediate"
prerequisites:
  - "/series/php-typescript-developers/chapters/04-oop-classes-interfaces"
---

# Error Handling: Try/Catch & Type Safety

## Overview

Both TypeScript and PHP use try/catch for exception handling, but PHP has a richer exception hierarchy and more powerful error handling capabilities. This chapter shows you how to write robust, type-safe error handling in PHP.

## Learning Objectives

By the end of this chapter, you'll be able to:

- ✅ Use try/catch/finally blocks in PHP
- ✅ Understand PHP's Error and Exception hierarchy
- ✅ Create custom exception classes
- ✅ Handle multiple exception types
- ✅ Use type-safe error handling patterns
- ✅ Implement Result types for functional error handling
- ✅ Apply error handling best practices

## Code Examples

📁 **[View Code Examples on GitHub](https://github.com/dalehurley/codewithphp/tree/main/code/php-typescript-developers/chapter-05)**

This chapter includes error handling examples:
- Custom exception classes
- Exception chaining
- Result type pattern
- Validation examples

Run the examples:
```bash
cd code/php-typescript-developers/chapter-05
php custom-exceptions.php
php result-type.php
```

## Basic Try/Catch

### TypeScript

```typescript
try {
  throw new Error("Something went wrong");
} catch (error) {
  if (error instanceof Error) {
    console.error(error.message);
  }
} finally {
  console.log("Cleanup code");
}
```

### PHP

```php
<?php
declare(strict_types=1);

try {
    throw new Exception("Something went wrong");
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
} finally {
    echo "Cleanup code" . PHP_EOL;
}
```

**Key Similarities:**
- ✅ `try`, `catch`, `finally` work the same
- ✅ `finally` always executes
- ✅ Exceptions bubble up if not caught

**Key Differences:**
- PHP requires type hint for caught exception: `catch (Exception $e)`
- PHP has explicit exception variable in catch block

## Exception Hierarchy

### TypeScript (Simple)

```typescript
// Basic Error class
class CustomError extends Error {
  constructor(message: string) {
    super(message);
    this.name = "CustomError";
  }
}

try {
  throw new CustomError("Custom error");
} catch (error) {
  if (error instanceof CustomError) {
    console.error("Custom:", error.message);
  } else if (error instanceof Error) {
    console.error("Generic:", error.message);
  }
}
```

### PHP (Rich Hierarchy)

```php
<?php
declare(strict_types=1);

// PHP has two base classes:
// 1. Exception - for recoverable errors
// 2. Error - for serious errors (usually fatal)

// Custom exception
class CustomException extends Exception {}

try {
    throw new CustomException("Custom error");
} catch (CustomException $e) {
    echo "Custom: " . $e->getMessage() . PHP_EOL;
} catch (Exception $e) {
    echo "Generic: " . $e->getMessage() . PHP_EOL;
}
```

**PHP Exception Hierarchy:**

```
Throwable (interface)
├── Exception
│   ├── LogicException
│   │   ├── BadFunctionCallException
│   │   ├── BadMethodCallException
│   │   ├── DomainException
│   │   ├── InvalidArgumentException
│   │   ├── LengthException
│   │   └── OutOfRangeException
│   ├── RuntimeException
│   │   ├── OutOfBoundsException
│   │   ├── OverflowException
│   │   ├── RangeException
│   │   ├── UnderflowException
│   │   └── UnexpectedValueException
│   └── Other built-in exceptions...
└── Error
    ├── TypeError
    ├── ParseError
    ├── ArithmeticError
    │   └── DivisionByZeroError
    ├── AssertionError
    └── CompileError
```

## Exception vs Error

### Exception (Recoverable)

```php
<?php
declare(strict_types=1);

class UserNotFoundException extends Exception {}

function findUser(int $id): array {
    // Simulated database lookup
    if ($id !== 1) {
        throw new UserNotFoundException("User {$id} not found");
    }
    return ['id' => $id, 'name' => 'Alice'];
}

try {
    $user = findUser(999);
} catch (UserNotFoundException $e) {
    // Recoverable: show error to user, return default, etc.
    echo "Error: " . $e->getMessage() . PHP_EOL;
    $user = ['id' => 0, 'name' => 'Guest'];
}
```

### Error (Serious Issues)

```php
<?php
declare(strict_types=1);

function processData(string $data): void {
    // TypeError is an Error, not Exception
    $length = strlen($data);
}

try {
    processData(123); // Wrong type
} catch (TypeError $e) {
    // Serious issue: indicates a programming error
    echo "Type Error: " . $e->getMessage() . PHP_EOL;
}
```

**When to Use:**
- **Exception:** Business logic errors (user not found, validation failed)
- **Error:** Programming errors (type errors, parse errors, fatal issues)

## Multiple Catch Blocks

### TypeScript

```typescript
class ValidationError extends Error {}
class NetworkError extends Error {}

try {
  // Some operation
  throw new ValidationError("Invalid input");
} catch (error) {
  if (error instanceof ValidationError) {
    console.error("Validation:", error.message);
  } else if (error instanceof NetworkError) {
    console.error("Network:", error.message);
  } else {
    console.error("Unknown:", error);
  }
}
```

### PHP

```php
<?php
declare(strict_types=1);

class ValidationException extends Exception {}
class NetworkException extends Exception {}

try {
    // Some operation
    throw new ValidationException("Invalid input");
} catch (ValidationException $e) {
    echo "Validation: " . $e->getMessage() . PHP_EOL;
} catch (NetworkException $e) {
    echo "Network: " . $e->getMessage() . PHP_EOL;
} catch (Exception $e) {
    echo "Unknown: " . $e->getMessage() . PHP_EOL;
}
```

## Union Catch (PHP 8.0+)

```php
<?php
declare(strict_types=1);

class ValidationException extends Exception {}
class AuthenticationException extends Exception {}

try {
    throw new ValidationException("Invalid input");
} catch (ValidationException | AuthenticationException $e) {
    // Handle both exception types the same way
    echo "Client error: " . $e->getMessage() . PHP_EOL;
}
```

## Custom Exception Classes

### TypeScript

```typescript
class HttpException extends Error {
  constructor(
    public statusCode: number,
    message: string
  ) {
    super(message);
    this.name = "HttpException";
  }
}

class NotFoundException extends HttpException {
  constructor(message: string) {
    super(404, message);
  }
}

try {
  throw new NotFoundException("Resource not found");
} catch (error) {
  if (error instanceof HttpException) {
    console.error(`HTTP ${error.statusCode}: ${error.message}`);
  }
}
```

### PHP

```php
<?php
declare(strict_types=1);

class HttpException extends Exception {
    public function __construct(
        private int $statusCode,
        string $message = "",
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getStatusCode(): int {
        return $this->statusCode;
    }
}

class NotFoundException extends HttpException {
    public function __construct(string $message = "Resource not found") {
        parent::__construct(404, $message);
    }
}

try {
    throw new NotFoundException("User not found");
} catch (HttpException $e) {
    echo "HTTP {$e->getStatusCode()}: {$e->getMessage()}" . PHP_EOL;
}
```

## Exception Context and Stack Traces

### PHP Exception Methods

```php
<?php
declare(strict_types=1);

try {
    throw new Exception("Something went wrong", 500);
} catch (Exception $e) {
    echo "Message: " . $e->getMessage() . PHP_EOL;
    echo "Code: " . $e->getCode() . PHP_EOL;
    echo "File: " . $e->getFile() . PHP_EOL;
    echo "Line: " . $e->getLine() . PHP_EOL;
    echo "Trace: " . $e->getTraceAsString() . PHP_EOL;
}
```

**Available Methods:**
- `getMessage()`: Error message
- `getCode()`: Numeric error code
- `getFile()`: File where exception was thrown
- `getLine()`: Line number
- `getTrace()`: Stack trace as array
- `getTraceAsString()`: Stack trace as string
- `getPrevious()`: Previous exception (for chaining)

## Exception Chaining

### TypeScript

```typescript
class DatabaseError extends Error {}
class QueryError extends Error {}

try {
  try {
    throw new QueryError("Invalid SQL");
  } catch (error) {
    throw new DatabaseError("Database operation failed", { cause: error });
  }
} catch (error) {
  if (error instanceof DatabaseError && error.cause) {
    console.error("Database:", error.message);
    console.error("Cause:", (error.cause as Error).message);
  }
}
```

### PHP

```php
<?php
declare(strict_types=1);

class DatabaseException extends Exception {}
class QueryException extends Exception {}

try {
    try {
        throw new QueryException("Invalid SQL");
    } catch (QueryException $e) {
        // Chain exceptions with $previous parameter
        throw new DatabaseException("Database operation failed", 0, $e);
    }
} catch (DatabaseException $e) {
    echo "Database: " . $e->getMessage() . PHP_EOL;

    $previous = $e->getPrevious();
    if ($previous) {
        echo "Cause: " . $previous->getMessage() . PHP_EOL;
    }
}
```

## Result Type Pattern (Functional Error Handling)

For type-safe error handling without exceptions:

### TypeScript

```typescript
type Result<T, E = Error> =
  | { success: true; value: T }
  | { success: false; error: E };

function divide(a: number, b: number): Result<number> {
  if (b === 0) {
    return {
      success: false,
      error: new Error("Division by zero")
    };
  }
  return {
    success: true,
    value: a / b
  };
}

const result = divide(10, 2);
if (result.success) {
  console.log("Result:", result.value);
} else {
  console.error("Error:", result.error.message);
}
```

### PHP

```php
<?php
declare(strict_types=1);

/**
 * @template T
 * @template E of Exception
 */
class Result {
    private function __construct(
        private bool $success,
        private mixed $value,
        private ?Throwable $error
    ) {}

    /**
     * @template V
     * @param V $value
     * @return Result<V, never>
     */
    public static function ok(mixed $value): self {
        return new self(true, $value, null);
    }

    /**
     * @template Err of Throwable
     * @param Err $error
     * @return Result<never, Err>
     */
    public static function err(Throwable $error): self {
        return new self(false, null, $error);
    }

    public function isOk(): bool {
        return $this->success;
    }

    public function isErr(): bool {
        return !$this->success;
    }

    /**
     * @return T
     */
    public function unwrap(): mixed {
        if ($this->success) {
            return $this->value;
        }
        throw new RuntimeException("Called unwrap on error result");
    }

    /**
     * @return E
     */
    public function unwrapErr(): Throwable {
        if (!$this->success) {
            return $this->error;
        }
        throw new RuntimeException("Called unwrapErr on ok result");
    }
}

/**
 * @return Result<float, Exception>
 */
function divide(float $a, float $b): Result {
    if ($b === 0) {
        return Result::err(new Exception("Division by zero"));
    }
    return Result::ok($a / $b);
}

$result = divide(10, 2);
if ($result->isOk()) {
    echo "Result: " . $result->unwrap() . PHP_EOL;
} else {
    echo "Error: " . $result->unwrapErr()->getMessage() . PHP_EOL;
}
```

## Best Practices

### 1. Use Specific Exception Types

**❌ Bad:**
```php
<?php
throw new Exception("User not found");
throw new Exception("Invalid email");
throw new Exception("Network error");
```

**✅ Good:**
```php
<?php
throw new UserNotFoundException("User not found");
throw new ValidationException("Invalid email");
throw new NetworkException("Network error");
```

### 2. Don't Catch Everything

**❌ Bad:**
```php
<?php
try {
    // Some code
} catch (Throwable $e) {
    // Silently ignore - BAD!
}
```

**✅ Good:**
```php
<?php
try {
    // Some code
} catch (ValidationException $e) {
    // Handle specific error
    return response(['error' => $e->getMessage()], 400);
} catch (NotFoundException $e) {
    return response(['error' => $e->getMessage()], 404);
}
// Let other exceptions bubble up
```

### 3. Provide Context

**❌ Bad:**
```php
<?php
throw new Exception("Error");
```

**✅ Good:**
```php
<?php
throw new ValidationException(
    "Invalid email format: '{$email}'. Expected format: user@domain.com"
);
```

### 4. Use Finally for Cleanup

```php
<?php
$file = fopen('data.txt', 'r');
try {
    // Process file
    $content = fread($file, filesize('data.txt'));
} finally {
    // Always close file, even if exception occurs
    fclose($file);
}
```

## Practical Example: API Request Handler

### TypeScript

```typescript
class ApiError extends Error {
  constructor(
    public statusCode: number,
    message: string
  ) {
    super(message);
  }
}

async function fetchUser(id: number): Promise<User> {
  try {
    const response = await fetch(`/api/users/${id}`);

    if (!response.ok) {
      throw new ApiError(
        response.status,
        `Failed to fetch user: ${response.statusText}`
      );
    }

    return await response.json();
  } catch (error) {
    if (error instanceof ApiError) {
      // Handle API errors
      console.error(`API Error ${error.statusCode}: ${error.message}`);
      throw error;
    } else {
      // Handle network errors
      console.error("Network error:", error);
      throw new ApiError(500, "Network request failed");
    }
  }
}
```

### PHP

```php
<?php
declare(strict_types=1);

class ApiException extends Exception {
    public function __construct(
        private int $statusCode,
        string $message,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $statusCode, $previous);
    }

    public function getStatusCode(): int {
        return $this->statusCode;
    }
}

class NetworkException extends Exception {}

function fetchUser(int $id): array {
    try {
        $ch = curl_init("https://api.example.com/users/{$id}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($response === false) {
            throw new NetworkException(curl_error($ch));
        }

        curl_close($ch);

        if ($httpCode !== 200) {
            throw new ApiException(
                $httpCode,
                "Failed to fetch user: HTTP {$httpCode}"
            );
        }

        return json_decode($response, true);
    } catch (ApiException $e) {
        // Handle API errors
        error_log("API Error {$e->getStatusCode()}: {$e->getMessage()}");
        throw $e;
    } catch (NetworkException $e) {
        // Handle network errors
        error_log("Network error: {$e->getMessage()}");
        throw new ApiException(500, "Network request failed", $e);
    }
}
```

## Hands-On Exercise

### Task 1: Create a Validation System

Build a validation system with custom exceptions:

**Requirements:**
- `ValidationException` base class
- `InvalidEmailException`, `InvalidAgeException` subclasses
- `Validator` class with methods to validate email and age
- Throw appropriate exceptions with helpful messages

<details>
<summary>Solution</summary>

```php
<?php
declare(strict_types=1);

class ValidationException extends Exception {}
class InvalidEmailException extends ValidationException {}
class InvalidAgeException extends ValidationException {}

class Validator {
    public function validateEmail(string $email): void {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidEmailException(
                "Invalid email format: '{$email}'"
            );
        }
    }

    public function validateAge(int $age): void {
        if ($age < 0 || $age > 150) {
            throw new InvalidAgeException(
                "Age must be between 0 and 150, got: {$age}"
            );
        }
    }

    public function validateUser(array $data): array {
        $errors = [];

        try {
            $this->validateEmail($data['email'] ?? '');
        } catch (InvalidEmailException $e) {
            $errors['email'] = $e->getMessage();
        }

        try {
            $this->validateAge($data['age'] ?? 0);
        } catch (InvalidAgeException $e) {
            $errors['age'] = $e->getMessage();
        }

        if (!empty($errors)) {
            throw new ValidationException(json_encode($errors));
        }

        return $data;
    }
}

// Test
$validator = new Validator();

try {
    $validator->validateUser([
        'email' => 'invalid-email',
        'age' => 200
    ]);
} catch (ValidationException $e) {
    echo "Validation errors: " . $e->getMessage() . PHP_EOL;
}
```
</details>

### Task 2: Implement Retry Logic

Create a retry function that catches exceptions and retries:

<details>
<summary>Solution</summary>

```php
<?php
declare(strict_types=1);

class RetryException extends Exception {}

/**
 * @template T
 * @param callable(): T $operation
 * @return T
 */
function retry(callable $operation, int $maxAttempts = 3, int $delayMs = 100): mixed {
    $lastException = null;

    for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
        try {
            return $operation();
        } catch (Exception $e) {
            $lastException = $e;
            echo "Attempt {$attempt} failed: {$e->getMessage()}" . PHP_EOL;

            if ($attempt < $maxAttempts) {
                usleep($delayMs * 1000);
            }
        }
    }

    throw new RetryException(
        "Operation failed after {$maxAttempts} attempts",
        0,
        $lastException
    );
}

// Test
$attempts = 0;
try {
    $result = retry(function() use (&$attempts) {
        $attempts++;
        if ($attempts < 3) {
            throw new Exception("Temporary failure");
        }
        return "Success!";
    });

    echo "Result: {$result}" . PHP_EOL;
} catch (RetryException $e) {
    echo "All retries failed: " . $e->getMessage() . PHP_EOL;
}
```
</details>

## Key Takeaways

1. **Try/catch/finally** syntax is nearly identical between TS and PHP
2. **PHP has two base types**: `Exception` (recoverable) and `Error` (serious)
3. **Type hints required** in catch blocks: `catch (Exception $e)` - cannot catch without type
4. **Rich exception hierarchy** in PHP with specific exception types (InvalidArgumentException, RuntimeException, etc.)
5. **Union catch** (PHP 8.0+) handles multiple exception types: `catch (A | B $e)`
6. **Exception chaining** via `$previous` parameter preserves context across layers
7. **Result type pattern** provides functional error handling alternative to exceptions
8. **Custom exceptions** should extend appropriate base class and add domain-specific context
9. **`catch (Throwable $e)`** catches everything (both Exception and Error)
10. **Set exception/error handlers** globally with `set_exception_handler()` and `set_error_handler()`
11. **Never catch `Error`** in application code - indicates serious problems
12. **Always include exception message** when rethrowing for debugging context

## Comparison Table

| Feature | TypeScript | PHP |
|---------|------------|-----|
| **Try/Catch** | ✅ | ✅ |
| **Finally** | ✅ | ✅ |
| **Type Hint in Catch** | Optional | Required |
| **Exception Hierarchy** | Simple | Rich (Exception/Error) |
| **Multiple Catch** | Manual checks | Native |
| **Union Catch** | ❌ | ✅ (PHP 8.0+) |
| **Exception Chaining** | `cause` option | `$previous` param |
| **Stack Traces** | `stack` property | `getTrace()` method |

## Next Steps

Congratulations! You've completed **Phase 1: Foundations** of the series. You now understand PHP's type system, syntax, functions, OOP, and error handling.

**Next Chapter:** [06: Package Management: npm vs Composer](/series/php-typescript-developers/chapters/06-package-management)

**Phase 2 Preview:** Ecosystem (Package management, testing, code quality, build tools, debugging)

## Resources

- [PHP Exceptions Documentation](https://www.php.net/manual/en/language.exceptions.php)
- [SPL Exceptions](https://www.php.net/manual/en/spl.exceptions.php)
- [Error Handling Best Practices](https://www.php.net/manual/en/language.errors.php7.php)

---

**Questions or feedback?** Open an issue on [GitHub](https://github.com/dalehurley/codewithphp/issues)
