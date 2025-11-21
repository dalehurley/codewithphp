<?php

declare(strict_types=1);

/**
 * Fibonacci with Dynamic Programming
 *
 * Demonstrates memoization (top-down) and tabulation (bottom-up)
 * approaches to optimize recursive algorithms.
 *
 * Time Complexity: O(n)
 * Space Complexity: O(n) or O(1) optimized
 *
 * PHP Version: 8.0+
 */

class FibonacciDP
{
    private array $memo = [];

    /**
     * Memoization approach (top-down)
     *
     * @param int $n Fibonacci number to calculate
     * @return int Result
     */
    public function fibMemo(int $n): int
    {
        if ($n <= 1) {
            return $n;
        }

        if (isset($this->memo[$n])) {
            return $this->memo[$n];
        }

        $this->memo[$n] = $this->fibMemo($n - 1) + $this->fibMemo($n - 2);
        return $this->memo[$n];
    }

    /**
     * Tabulation approach (bottom-up)
     *
     * @param int $n Fibonacci number to calculate
     * @return int Result
     */
    public function fibTab(int $n): int
    {
        if ($n <= 1) {
            return $n;
        }

        $dp = [0, 1];

        for ($i = 2; $i <= $n; $i++) {
            $dp[$i] = $dp[$i - 1] + $dp[$i - 2];
        }

        return $dp[$n];
    }

    /**
     * Space-optimized tabulation
     *
     * @param int $n Fibonacci number to calculate
     * @return int Result
     */
    public function fibOptimized(int $n): int
    {
        if ($n <= 1) {
            return $n;
        }

        $prev2 = 0;
        $prev1 = 1;

        for ($i = 2; $i <= $n; $i++) {
            $current = $prev1 + $prev2;
            $prev2 = $prev1;
            $prev1 = $current;
        }

        return $prev1;
    }
}

// ============================================================================
// Example Usage
// ============================================================================

echo "=== Fibonacci with Dynamic Programming ===\n\n";

$fib = new FibonacciDP();

$n = 10;

echo "Computing Fibonacci($n) using different approaches:\n\n";

echo "1. Memoization (Top-Down):\n";
$result = $fib->fibMemo($n);
echo "   Result: $result\n\n";

echo "2. Tabulation (Bottom-Up):\n";
$result = $fib->fibTab($n);
echo "   Result: $result\n\n";

echo "3. Space-Optimized:\n";
$result = $fib->fibOptimized($n);
echo "   Result: $result\n\n";

echo "First 15 Fibonacci numbers:\n";
for ($i = 0; $i < 15; $i++) {
    echo "F($i) = " . $fib->fibOptimized($i) . "\n";
}

echo "\n=== Done ===\n";
