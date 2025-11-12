<?php

declare(strict_types=1);

/**
 * Error Handling and Exceptions - PHP vs Python
 * 
 * This file demonstrates PHP exception handling syntax compared to Python.
 * Key differences:
 * - PHP: catch (ExceptionType $e) vs Python: except ExceptionType as e
 * - PHP: throw new Exception() vs Python: raise Exception()
 * - PHP uses ->getMessage() method vs Python's string representation
 * - PHP distinguishes Error (fatal) from Exception (catchable)
 */

// ============================================================================
// 1. Basic Try/Catch/Finally
// ============================================================================

// Python:
// try:
//     result = 10 / 0
// except ZeroDivisionError as e:
//     print(f"Error: {e}")
// finally:
//     print("Cleanup")

// PHP:
try {
    $result = 10 / 0;
} catch (DivisionByZeroError $e) {
    echo "Error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Unexpected error: " . $e->getMessage() . "\n";
} finally {
    echo "Cleanup code\n";
}

// ============================================================================
// 2. Throwing Exceptions
// ============================================================================

// Python: raise ValueError("Cannot divide by zero")
// PHP:    throw new ValueError("Cannot divide by zero");

function divide(float $a, float $b): float
{
    if ($b == 0) {
        throw new ValueError("Cannot divide by zero");
    }
    return $a / $b;
}

try {
    $result = divide(10, 0);
} catch (ValueError $e) {
    echo "Caught: " . $e->getMessage() . "\n";
}

// ============================================================================
// 3. Custom Exceptions
// ============================================================================

// Python:
// class ValidationError(Exception):
//     pass

// PHP:
class ValidationError extends Exception
{
}

function validateEmail(string $email): void
{
    if (strpos($email, "@") === false) {
        throw new ValidationError("Invalid email format");
    }
}

try {
    validateEmail("invalid-email");
} catch (ValidationError $e) {
    echo "Validation error: " . $e->getMessage() . "\n";
}

// ============================================================================
// 4. Exception with Custom Properties
// ============================================================================

class CustomException extends Exception
{
    public function __construct(
        string $message,
        int $code = 0,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
    
    // Note: getCode() is final in Exception, so we can't override it
    // Use parent::getCode() to access the code
}

try {
    throw new CustomException("Something went wrong", 500);
} catch (CustomException $e) {
    echo "Error: {$e->getMessage()}, Code: {$e->getCode()}\n";
}

// ============================================================================
// 5. Error vs Exception (PHP 7+)
// ============================================================================

// Python treats all errors as exceptions
// PHP distinguishes Error (fatal) from Exception (catchable)

// PHP 7+ makes many fatal errors catchable as Error
try {
    undefinedFunction();  // Would cause fatal error
} catch (Error $e) {
    echo "Caught Error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Caught Exception: " . $e->getMessage() . "\n";
}

// ============================================================================
// 6. Exception Chaining
// ============================================================================

// Python: raise ValueError("outer") from TypeError("inner")
// PHP:    throw new ValueError("outer", 0, new TypeError("inner"));

try {
    try {
        throw new TypeError("Inner error");
    } catch (TypeError $e) {
        throw new ValueError("Outer error", 0, $e);
    }
} catch (ValueError $e) {
    echo "Caught: " . $e->getMessage() . "\n";
    if ($e->getPrevious() !== null) {
        echo "Previous: " . $e->getPrevious()->getMessage() . "\n";
    }
}

// ============================================================================
// 7. Multiple Exception Types
// ============================================================================

// Python:
// except (ValueError, TypeError) as e:

// PHP: Multiple catch blocks
function processValue(mixed $value): void
{
    if (is_string($value) && empty($value)) {
        throw new ValueError("Empty string not allowed");
    }
    if (!is_numeric($value)) {
        throw new TypeError("Value must be numeric");
    }
}

try {
    processValue("");
} catch (ValueError | TypeError $e) {
    echo "Caught: " . get_class($e) . " - " . $e->getMessage() . "\n";
}

// ============================================================================
// 8. Exception Information
// ============================================================================

// Python: e.args, e.__traceback__
// PHP:    $e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine(), $e->getTrace()

try {
    throw new Exception("Test exception", 42);
} catch (Exception $e) {
    echo "Message: " . $e->getMessage() . "\n";
    echo "Code: " . $e->getCode() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace:\n";
    print_r($e->getTrace());
}

// ============================================================================
// 9. Suppressing Errors
// ============================================================================

// Python: No direct equivalent (use try/except)
// PHP:    @ operator (not recommended, but exists)

// Not recommended, but possible:
// $result = @file_get_contents("nonexistent.txt");

// Better approach:
try {
    $result = file_get_contents("nonexistent.txt");
    if ($result === false) {
        throw new RuntimeException("File not found");
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

