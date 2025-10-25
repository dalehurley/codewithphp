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
