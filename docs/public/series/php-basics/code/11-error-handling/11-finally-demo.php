<?php

declare(strict_types=1);

/**
 * Finally Block Demonstration
 * Shows how finally blocks run regardless of exception handling
 */

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
