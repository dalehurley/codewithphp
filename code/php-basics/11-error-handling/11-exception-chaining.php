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
    function requireString(string $value): void
    {
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
