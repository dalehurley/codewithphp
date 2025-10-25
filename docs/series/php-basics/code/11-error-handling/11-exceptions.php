<?php

declare(strict_types=1);

/**
 * Basic Exception Handling Example
 * Demonstrates try-catch blocks with a division function
 */

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
