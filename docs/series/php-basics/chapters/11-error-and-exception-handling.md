---
title: "11: Error and Exception Handling"
description: "Learn how to write robust, resilient applications by gracefully handling errors and managing exceptional situations with the try-catch block."
series: "php-basics"
chapter: 11
order: 11
difficulty: "Intermediate"
prerequisites:
  - "/series/php-basics/chapters/10-oop-traits-and-namespaces"
---

# Chapter 11: Error and Exception Handling

## Overview

No matter how carefully we code, things can and will go wrong. A user might provide invalid input, a file we need to read might not exist, or a database connection could fail. If we don't plan for these situations, our scripts will crash and show ugly error messages to the user.

**Error handling** is the process of anticipating and managing these problems. In modern PHP, the primary way we do this is with **exceptions**. An exception is an object that is "thrown" when an error occurs, interrupting the normal flow of the program and allowing us to "catch" it and handle the problem gracefully.

By the end of this chapter, you'll build a robust bank account system that validates withdrawals, handles errors gracefully, and provides clear feedback when operations fail—all without crashing your application.

## Prerequisites

- PHP 8.4 installed and accessible from your terminal (PHP 7.1+ for multi-catch syntax)
- Completion of previous chapters, especially Chapter 10 (OOP, Traits, and Namespaces)
- A text editor and command line
- **Estimated time**: 35–40 minutes

## What You'll Build

- A division function that safely handles zero division
- Exception handling with `try`, `catch`, and `finally` blocks
- A custom `InsufficientFundsException` class
- A `BankAccount` class that throws and handles domain-specific exceptions
- Working examples demonstrating graceful error recovery

## Objectives

- Understand the difference between traditional errors and modern exceptions.
- Use a `try...catch` block to handle exceptions and prevent application crashes.
- "Throw" your own exceptions when something goes wrong in your code.
- Use the `finally` block to run cleanup code, regardless of whether an exception occurred.
- Create custom exception classes for more specific error handling.

## Step 1: The Problem: A Fatal Error (~3 min)

Let's look at what happens when an operation fails without any error handling.

### Goal

Demonstrate how unhandled errors crash your application and prevent subsequent code from running.

### Actions

1.  **Create a File**:
    Create a new file `exceptions.php` in your working directory.

2.  **Write Code that Fails**:
    Let's write a function that divides two numbers. Division by zero is a mathematical impossibility and will cause a fatal error.

```php
<?php
declare(strict_types=1);

function divide(int $numerator, int $denominator): float
{
    return $numerator / $denominator;
}

echo divide(10, 2); // This works
echo PHP_EOL;
echo divide(5, 0);  // This will crash
echo "This line will never be reached." . PHP_EOL;
```

3.  **Run the Script**:

```bash
# Run the failing script
php exceptions.php
```

### Expected Result

```
5

Fatal error: Uncaught DivisionByZeroError: Division by zero in exceptions.php:6
Stack trace:
#0 exceptions.php(12): divide(5, 0)
#1 {main}
  thrown in exceptions.php on line 6
```

The script prints `5` (from the first division), then crashes immediately when it tries to divide by zero. The final `echo` statement never executes.

### Why It Works (or Doesn't)

PHP 8.0+ automatically throws a `DivisionByZeroError` when you divide by zero. Without a `try...catch` block, this error is **uncaught**, causing the script to terminate abruptly. The user sees a technical stack trace instead of a helpful message.

### Troubleshooting

**Problem**: Script doesn't crash and shows `INF` or a warning instead.  
**Solution**: Ensure you're using PHP 8.0 or later. Earlier versions had different behavior for division by zero.

## Step 2: Throwing and Catching Exceptions (~5 min)

Instead of letting the script crash, we can detect the problem and **throw** an exception. Then, we can wrap the "risky" code that might cause the problem in a **`try`** block and handle the potential error in a **`catch`** block.

### Goal

Gracefully handle errors by catching exceptions and allowing the application to continue running.

### Actions

1.  **Update Your Script**:
    Replace the contents of `exceptions.php` with the following:

```php
<?php
declare(strict_types=1);

function divide(int $numerator, int $denominator): float
{
    if ($denominator === 0) {
        // Instead of causing a fatal error, we throw an exception object.
        // `Exception` is a built-in PHP class.
        throw new Exception("Cannot divide by zero!");
    }
    return $numerator / $denominator;
}

// We "try" the code that might throw an exception.
try {
    echo divide(10, 2) . PHP_EOL;
    echo divide(5, 0) . PHP_EOL; // This line will throw the exception
    echo "This line will never be reached." . PHP_EOL;
} catch (Exception $e) {
    // If an exception is thrown inside the `try` block,
    // execution jumps to this `catch` block.
    // The exception object is passed as the argument `$e`.
    echo "An error occurred: " . $e->getMessage() . PHP_EOL;
}

echo "The application continues to run." . PHP_EOL;
```

2.  **Run the Script**:

```bash
# Run the improved script
php exceptions.php
```

### Expected Result

```
5
An error occurred: Cannot divide by zero!
The application continues to run.
```

The script now handles the error gracefully, displays a user-friendly message, and continues executing.

### Why It Works

The `try` block wraps potentially dangerous code. When `throw new Exception()` is executed inside the `try` block, PHP immediately stops executing that block and jumps to the matching `catch` block. The exception object (stored in `$e`) contains the error message we provided. The `getMessage()` method retrieves it. After the `catch` block completes, normal execution resumes—the application doesn't crash.

### Troubleshooting

**Problem**: "Class 'Exception' not found" error.  
**Solution**: Ensure your PHP installation is correct. `Exception` is a built-in class available in all PHP versions.

**Problem**: The application still crashes.  
**Solution**: Make sure your `throw` statement is inside the `try` block scope. If you call `divide()` outside the `try` block, the exception won't be caught.

## Step 3: The `finally` Block (~4 min)

Sometimes, there's a piece of code that you **always** want to run, whether an exception was thrown or not. This is often used for cleanup tasks, like closing a database connection or a file handle. The `finally` block is perfect for this.

### Goal

Learn to use the `finally` block for cleanup code that must run regardless of success or failure.

### Actions

1.  **Create a New File**:
    Create `finally-demo.php`:

```php
<?php
declare(strict_types=1);

function divide(int $numerator, int $denominator): float
{
    if ($denominator === 0) {
        throw new Exception("Cannot divide by zero!");
    }
    return $numerator / $denominator;
}

try {
    echo "Trying to divide by 2..." . PHP_EOL;
    echo divide(10, 2) . PHP_EOL;

    echo "Trying to divide by 0..." . PHP_EOL;
    echo divide(5, 0) . PHP_EOL;
} catch (Exception $e) {
    echo "Caught exception: " . $e->getMessage() . PHP_EOL;
} finally {
    // This code runs whether an exception was caught or not.
    echo "This is the finally block. It always runs." . PHP_EOL;
}

echo "Script completed." . PHP_EOL;
```

2.  **Run the Script**:

```bash
# Run the finally demonstration
php finally-demo.php
```

### Expected Result

```
Trying to divide by 2...
5
Trying to divide by 0...
Caught exception: Cannot divide by zero!
This is the finally block. It always runs.
Script completed.
```

### Why It Works

The `finally` block executes **after** the `try` and `catch` blocks, regardless of whether an exception was thrown. This makes it ideal for cleanup operations like:

- Closing file handles
- Releasing database connections
- Logging operations
- Resetting state

Even if you add a `return` statement in the `try` or `catch` block, the `finally` block still runs before the function returns.

### Troubleshooting

**Problem**: The finally block doesn't run.  
**Solution**: Check that you've correctly structured your try-catch-finally blocks. The `finally` keyword must be at the same indentation level as `try` and `catch`.

::: tip
The `finally` block runs even if there's a `return` statement in the `try` or `catch` blocks, making it perfect for guaranteed cleanup operations.
:::

## Step 4: Creating Custom Exceptions (~6 min)

PHP has many built-in exception types (`DivisionByZeroError`, `TypeError`, etc.), but it's a great practice to create your own for your application's specific logic. This allows you to catch different types of errors separately and handle them appropriately.

### Goal

Build a bank account system with custom exceptions for domain-specific errors.

### Actions

A custom exception is simply a class that `extends` the base `Exception` class.

1.  **Create a New File**:
    Create `bank-account.php`:

```php
<?php
declare(strict_types=1);

// A custom exception class for insufficient funds
class InsufficientFundsException extends Exception {}

class BankAccount
{
    // PHP 8.0+ constructor property promotion
    public function __construct(private float $balance) {}

    public function withdraw(float $amount): void
    {
        if ($amount <= 0) {
            throw new Exception("Withdrawal amount must be positive.");
        }

        if ($amount > $this->balance) {
            // Throw our specific exception type
            throw new InsufficientFundsException(
                "Cannot withdraw $$amount. Insufficient funds."
            );
        }

        $this->balance -= $amount;
    }

    public function getBalance(): float
    {
        return $this->balance;
    }
}

// Demonstration
$account = new BankAccount(100);

try {
    $account->withdraw(50);
    echo "Withdrawal successful. New balance: $" . $account->getBalance() . PHP_EOL;

    $account->withdraw(75); // This will throw InsufficientFundsException
    echo "This line won't execute." . PHP_EOL;

} catch (InsufficientFundsException $e) {
    // We can specifically catch *our* custom exception
    echo "Transaction failed: " . $e->getMessage() . PHP_EOL;
    echo "Current balance remains: $" . $account->getBalance() . PHP_EOL;
} catch (Exception $e) {
    // A general catch block for any other type of exception
    echo "A general error occurred: " . $e->getMessage() . PHP_EOL;
}

echo "Application continues running normally." . PHP_EOL;
```

2.  **Run the Script**:

```bash
# Run the bank account demonstration
php bank-account.php
```

### Expected Result

```
Withdrawal successful. New balance: $50
Transaction failed: Cannot withdraw $75. Insufficient funds.
Current balance remains: $50
Application continues running normally.
```

### Why It Works

By creating a custom `InsufficientFundsException` class, you can:

1. **Catch specific errors**: The first `catch` block only handles insufficient funds errors, allowing you to provide specific feedback.
2. **Separate concerns**: Different exception types can be handled differently—maybe insufficient funds gets logged differently than invalid input.
3. **Improve readability**: `InsufficientFundsException` is far more descriptive than a generic `Exception`.
4. **Add custom behavior**: You can add properties and methods to your custom exceptions (e.g., store the attempted amount and current balance).

The order of `catch` blocks matters: PHP checks them top-to-bottom and executes the first match. Always catch more specific exceptions before generic ones.

### Troubleshooting

**Problem**: The generic `Exception` catch block is triggered instead of the custom one.  
**Solution**: Ensure the custom exception `extends Exception` and that you're throwing the correct exception type. Also, check that the more specific `catch` block comes before the generic one.

**Problem**: "Constructor property promotion" syntax error.  
**Solution**: Constructor property promotion (`private float $balance` in the constructor) requires PHP 8.0+. Verify your version with `php --version`.

::: tip
You can extend your custom exceptions with additional methods and properties. For example, `InsufficientFundsException` could have a `getAttemptedAmount()` method to provide more context for error handling.
:::

## Step 5: Built-in SPL Exceptions (~5 min)

PHP provides a rich set of built-in exception types through the **Standard PHP Library (SPL)**. These exceptions are more semantically meaningful than the generic `Exception` class and are widely used in professional PHP code.

### Goal

Learn when to use SPL exceptions and refactor code to use more specific exception types.

### Actions

1.  **Create a New File**:
    Create `spl-exceptions.php`:

```php
<?php
declare(strict_types=1);

/**
 * Demonstrates proper use of SPL exception types
 */

class Calculator
{
    public function divide(int $numerator, int $denominator): float
    {
        if ($denominator === 0) {
            // InvalidArgumentException is more specific than Exception
            throw new InvalidArgumentException(
                "Denominator cannot be zero."
            );
        }

        return $numerator / $denominator;
    }

    public function getFactorial(int $number): int
    {
        if ($number < 0) {
            throw new InvalidArgumentException(
                "Factorial is not defined for negative numbers."
            );
        }

        if ($number > 20) {
            throw new OutOfRangeException(
                "Number too large. Maximum supported value is 20."
            );
        }

        $result = 1;
        for ($i = 2; $i <= $number; $i++) {
            $result *= $i;
        }

        return $result;
    }
}

$calc = new Calculator();

// Example 1: Catching InvalidArgumentException
try {
    echo "5 / 2 = " . $calc->divide(5, 2) . PHP_EOL;
    echo "5 / 0 = " . $calc->divide(5, 0) . PHP_EOL;
} catch (InvalidArgumentException $e) {
    echo "Invalid argument: " . $e->getMessage() . PHP_EOL;
}

// Example 2: Catching multiple exception types (PHP 7.1+)
try {
    echo "Factorial of 5: " . $calc->getFactorial(5) . PHP_EOL;
    echo "Factorial of 25: " . $calc->getFactorial(25) . PHP_EOL;
} catch (InvalidArgumentException | OutOfRangeException $e) {
    // Both exception types handled here
    echo "Error: " . $e->getMessage() . PHP_EOL;
}

// Example 3: Exception object methods
try {
    $calc->divide(10, 0);
} catch (InvalidArgumentException $e) {
    echo "Exception Details:" . PHP_EOL;
    echo "  Message: " . $e->getMessage() . PHP_EOL;
    echo "  Code: " . $e->getCode() . PHP_EOL;
    echo "  File: " . $e->getFile() . PHP_EOL;
    echo "  Line: " . $e->getLine() . PHP_EOL;
}
```

2.  **Run the Script**:

```bash
# Run the SPL exceptions demonstration
php spl-exceptions.php
```

### Expected Result

```
5 / 2 = 2.5
Invalid argument: Denominator cannot be zero.
Factorial of 5: 120
Error: Number too large. Maximum supported value is 20.
Exception Details:
  Message: Denominator cannot be zero.
  Code: 0
  File: /path/to/spl-exceptions.php
  Line: 15
```

### Why It Works

**Common SPL Exception Types:**

- **`InvalidArgumentException`**: When a function receives an argument of incorrect type or invalid value
- **`OutOfRangeException`**: When a value is not within an expected range
- **`OutOfBoundsException`**: For array/collection access beyond valid bounds
- **`RuntimeException`**: For errors that can only be detected at runtime
- **`LogicException`**: For errors in program logic (should never happen in production)
- **`UnexpectedValueException`**: When a function returns an unexpected value type

**Multi-catch syntax** (PHP 7.1+) lets you handle multiple exception types in one catch block using the pipe (`|`) operator.

**Exception Object Methods:**

- `getMessage()`: The error message
- `getCode()`: Numeric error code (default 0)
- `getFile()`: File where exception was thrown
- `getLine()`: Line number where exception was thrown
- `getTrace()`: Array representation of stack trace
- `getTraceAsString()`: String representation of stack trace

### Troubleshooting

**Problem**: "Class 'InvalidArgumentException' not found" error.  
**Solution**: SPL exceptions are built-in to PHP 5.1+. If you see this error, check your PHP installation.

**Problem**: "Syntax error" on the multi-catch line.  
**Solution**: Multi-catch syntax requires PHP 7.1 or later. Update your PHP version or use separate catch blocks.

::: tip
Always use the most specific exception type that matches your use case. `InvalidArgumentException` is clearer than generic `Exception`, making debugging easier and code more maintainable.
:::

## Step 6: Exception Chaining and Error Types (~4 min)

When catching and re-throwing exceptions, you can preserve the original exception context using **exception chaining**. Additionally, PHP 7+ distinguishes between `Error` and `Exception` classes.

### Goal

Understand exception chaining for debugging and the difference between Error and Exception.

### Actions

1.  **Create a New File**:
    Create `exception-chaining.php`:

```php
<?php
declare(strict_types=1);

/**
 * Demonstrates exception chaining and Error vs Exception
 */

class UserService
{
    public function loadUser(int $userId): array
    {
        try {
            // Simulate a database error
            throw new RuntimeException("Database connection failed");
        } catch (RuntimeException $e) {
            // Re-throw with more context, preserving original exception
            throw new Exception(
                "Failed to load user with ID: $userId",
                0,
                $e  // The previous exception is preserved
            );
        }
    }
}

// Example 1: Exception chaining
echo "=== Exception Chaining ===" . PHP_EOL;
try {
    $service = new UserService();
    $user = $service->loadUser(123);
} catch (Exception $e) {
    echo "Current exception: " . $e->getMessage() . PHP_EOL;

    // Access the previous exception in the chain
    if ($previous = $e->getPrevious()) {
        echo "Original cause: " . $previous->getMessage() . PHP_EOL;
    }
}

// Example 2: Error vs Exception (PHP 7+)
echo PHP_EOL . "=== Error vs Exception ===" . PHP_EOL;

// This will trigger a TypeError (Error, not Exception)
try {
    function requireString(string $value): void {
        echo "Received: $value" . PHP_EOL;
    }

    // Uncommenting this line would cause a TypeError in strict mode
    // requireString(123); // Type error: expected string, got int

    // Instead, demonstrate catching both Error and Exception
    throw new TypeError("Expected string, got integer");

} catch (TypeError $e) {
    echo "Type error caught: " . $e->getMessage() . PHP_EOL;
} catch (Throwable $e) {
    // Throwable is the parent interface for both Error and Exception
    echo "Caught throwable: " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL . "Script completed successfully." . PHP_EOL;
```

2.  **Run the Script**:

```bash
# Run the exception chaining demonstration
php exception-chaining.php
```

### Expected Result

```
=== Exception Chaining ===
Current exception: Failed to load user with ID: 123
Original cause: Database connection failed

=== Error vs Exception ===
Type error caught: Expected string, got integer

Script completed successfully.
```

### Why It Works

**Exception Chaining** preserves the original error context when wrapping exceptions in higher-level exceptions. The third parameter of the `Exception` constructor accepts a previous exception, creating a chain you can traverse with `getPrevious()`.

**Error vs Exception (PHP 7+):**

- `Error` class: For internal PHP errors (type errors, parse errors, etc.)
- `Exception` class: For application-level exceptions
- Both implement `Throwable` interface (the root of all throwable objects)
- You can catch both with `catch (Throwable $e)`

**Common Error types:**

- `TypeError`: Type declaration violations
- `ParseError`: Syntax errors in eval() or require
- `ArgumentCountError`: Wrong number of arguments to a function
- `DivisionByZeroError`: Division or modulo by zero
- `ArithmeticError`: Math operation errors

::: warning
Never catch `Throwable` or `Error` unless you have a specific reason (like logging). These represent serious problems that usually shouldn't be recovered from. Stick to catching `Exception` and its subclasses for application logic.
:::

### Troubleshooting

**Problem**: `getPrevious()` returns null.  
**Solution**: Ensure you're passing the previous exception as the third parameter when throwing: `throw new Exception($message, $code, $previousException)`.

**Problem**: "Class 'TypeError' not found" error.  
**Solution**: `TypeError` was introduced in PHP 7.0. Ensure you're using PHP 7.0 or later.

## Best Practices

Before moving on, here are some key principles for professional exception handling:

### When to Use Exceptions

✅ **Use exceptions for:**

- Unexpected conditions (file not found, network timeout, invalid user input)
- Errors that prevent normal execution
- Validating preconditions (invalid arguments)
- Operations that fail in ways the caller should handle

❌ **Don't use exceptions for:**

- Normal control flow (use return values instead)
- Expected conditions (use conditional checks)
- Performance-critical loops (exceptions are expensive)
- Situations where a return value would be clearer

### Exception Naming Conventions

- Exception class names should end with `Exception`: `InvalidEmailException`, not `InvalidEmail`
- Be specific: `InsufficientFundsException` > `BankAccountException` > `Exception`
- Use SPL exceptions when they fit: `InvalidArgumentException` instead of custom `BadArgumentException`

### Exception Message Guidelines

```php
// ❌ Bad: Vague message
throw new Exception("Error");

// ✅ Good: Specific, actionable message
throw new InvalidArgumentException(
    "Email address must contain '@' symbol. Received: $email"
);
```

### Catching Exceptions

```php
// ❌ Bad: Catching and ignoring
try {
    riskyOperation();
} catch (Exception $e) {
    // Silent failure - never do this
}

// ✅ Good: Catch specific types, log, and handle appropriately
try {
    riskyOperation();
} catch (InvalidArgumentException $e) {
    // Log the error
    error_log("Invalid argument: " . $e->getMessage());
    // Inform the user
    echo "Please provide valid input.";
} catch (RuntimeException $e) {
    error_log("Runtime error: " . $e->getMessage());
    echo "An error occurred. Please try again later.";
}
```

### Re-throwing Exceptions

```php
// ✅ Good: Preserve context when re-throwing
catch (DatabaseException $e) {
    throw new UserNotFoundException(
        "Could not find user with ID: $userId",
        0,
        $e  // Preserve original exception
    );
}
```

## Code

All code examples from this chapter are available in the repository:

- `/series/php-basics/code/11-exceptions.php` – Basic exception handling
- `/series/php-basics/code/11-finally-demo.php` – Finally block demonstration
- `/series/php-basics/code/11-bank-account.php` – Custom exceptions
- `/series/php-basics/code/11-spl-exceptions.php` – SPL exception types and multi-catch
- `/series/php-basics/code/11-exception-chaining.php` – Exception chaining and Error vs Exception

## Exercises

1.  **Array Access with SPL Exceptions** (~10 min):

    - Create a `ProductCatalog` class with a private array of products (e.g., `['laptop' => 999, 'mouse' => 25]`).
    - Add a `getPrice(string $productName): float` method.
    - If the product doesn't exist, throw an `OutOfBoundsException` with a descriptive message.
    - If the product name is empty, throw an `InvalidArgumentException`.
    - In your main code, use multi-catch syntax to handle both exception types in a single catch block.
    - **Bonus**: Add a `setPrice(string $product, float $price)` method that throws `OutOfRangeException` if the price is negative or greater than 10,000.

2.  **Enhanced User Validation** (~15 min):

    - Create a custom `ValidationException` class that extends `InvalidArgumentException` (not `Exception`).
    - Create a `User` class with `setEmail(string $email): void` and `setAge(int $age): void` methods.
    - In `setEmail()`, validate the email contains '@' and '.', otherwise throw `ValidationException`.
    - In `setAge()`, throw `OutOfRangeException` if age is less than 13 or greater than 120.
    - Use constructor property promotion for storing the values.
    - In your main code, catch `ValidationException`, `OutOfRangeException`, and a generic `Exception` in separate catch blocks.
    - **Bonus**: Add exception chaining—catch any exceptions in a wrapper method and re-throw with additional context while preserving the original exception.

3.  **Exception Chaining with Data Processing** (~15 min):

    - Create a `DataProcessor` class with a `processFile(string $filename): array` method.
    - Inside `processFile()`, wrap file operations in a try-catch block.
    - If the file doesn't exist, throw `RuntimeException` with "File operation failed".
    - Catch that exception and re-throw a new `Exception` with message "Failed to process file: $filename", passing the original exception as the third parameter.
    - In your main code, catch the re-thrown exception and use `getPrevious()` to display both the current and original error messages.
    - Use `getFile()`, `getLine()`, and `getTraceAsString()` to display detailed debugging information.
    - **Bonus**: Add a `finally` block that logs "Cleanup completed" regardless of success or failure.

4.  **Enhanced Bank Account** (~20 min):
    - Refactor the `BankAccount` class from Step 4 to use SPL exceptions:
      - Use `InvalidArgumentException` instead of generic `Exception` for negative amounts
      - Keep `InsufficientFundsException` as a custom exception
    - Add a `deposit(float $amount)` method that validates the amount is positive
    - Add a `transfer(BankAccount $to, float $amount)` method that:
      - Validates both accounts aren't the same (throw `LogicException`)
      - Calls `withdraw()` from the source account (may throw `InsufficientFundsException`)
      - Calls `deposit()` on the target account
      - If deposit fails, re-deposits to the source account (rollback)
    - Use exception chaining when re-throwing to preserve context
    - Demonstrate multi-catch syntax to handle different exception types
    - **Bonus**: Add a `locked` property and an `AccountLockedException` that's thrown if operations are attempted on a locked account.

## Wrap-up

Congratulations! You've just completed a comprehensive deep dive into error and exception handling in PHP. You now know how to build robust, production-ready applications that handle failures gracefully. You've learned:

**Core Exception Handling:**

- Use `try...catch` blocks to handle exceptions gracefully
- Throw exceptions when operations fail
- Use `finally` for guaranteed cleanup operations
- Create custom exception classes for domain-specific errors

**Professional Techniques:**

- Use SPL exception types (`InvalidArgumentException`, `OutOfRangeException`, etc.) for more semantic code
- Handle multiple exception types with multi-catch syntax (PHP 7.1+)
- Use exception chaining with `getPrevious()` to preserve error context
- Leverage exception object methods (`getFile()`, `getLine()`, `getTrace()`) for debugging

**Best Practices:**

- Choose specific exception types over generic `Exception`
- Write clear, actionable exception messages
- Never silently catch and ignore exceptions
- Understand when to use exceptions vs return values
- Distinguish between `Error` (PHP internal) and `Exception` (application-level)

Using these techniques, you control exactly what happens when things go wrong, preventing crashes and providing clear feedback to users. This is a critical skill for building production-ready applications that handle edge cases professionally.

### What You Accomplished

✅ Built a safe division function that handles edge cases  
✅ Created a bank account system with custom exceptions  
✅ Learned to use `finally` for cleanup operations  
✅ Mastered the exception handling flow in PHP  
✅ Used SPL exceptions for more semantic error handling  
✅ Applied multi-catch syntax to handle multiple exception types  
✅ Implemented exception chaining to preserve error context  
✅ Learned professional exception handling best practices

### Next Steps

In [Chapter 12: Dependency Management with Composer](/series/php-basics/chapters/12-dependency-management-with-composer), you'll learn how to use Composer, PHP's package manager, to integrate third-party libraries into your projects and manage dependencies professionally. Exception handling becomes even more important when working with external packages that may throw their own exception types.

## Further Reading

- [PHP Manual: Exceptions](https://www.php.net/manual/en/language.exceptions.php) – Official documentation on exception handling
- [PHP Manual: Predefined Exceptions](https://www.php.net/manual/en/reserved.exceptions.php) – Complete list of built-in exception types
- [PHP Manual: SPL Exceptions](https://www.php.net/manual/en/spl.exceptions.php) – Standard PHP Library exception types
- [PHP Manual: Errors in PHP 7](https://www.php.net/manual/en/language.errors.php7.php) – Understanding the Error hierarchy
- [PSR-3: Logger Interface](https://www.php-fig.org/psr/psr-3/) – Standard for logging exceptions in production
- [PHP 8.0: Throw Expression](https://www.php.net/manual/en/migration80.new-features.php#migration80.new-features.core.throw-expression) – Modern exception features
