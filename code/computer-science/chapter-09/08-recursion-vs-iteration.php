<?php
declare(strict_types=1);

echo "=== Recursion vs Iteration ===\n\n";

// Recursive factorial
function factorialRecursive(int $n): int {
    if ($n <= 1) return 1;
    return $n * factorialRecursive($n - 1);
}

// Iterative factorial
function factorialIterative(int $n): int {
    $result = 1;
    for ($i = 2; $i <= $n; $i++) {
        $result *= $i;
    }
    return $result;
}

// Performance comparison
$n = 20;

$start = microtime(true);
for ($i = 0; $i < 10000; $i++) {
    factorialRecursive($n);
}
$recursiveTime = (microtime(true) - $start) * 1000;

$start = microtime(true);
for ($i = 0; $i < 10000; $i++) {
    factorialIterative($n);
}
$iterativeTime = (microtime(true) - $start) * 1000;

echo "Factorial($n) - 10000 iterations:\n";
echo "  Recursive: " . number_format($recursiveTime, 2) . " ms\n";
echo "  Iterative: " . number_format($iterativeTime, 2) . " ms\n";
echo "  Speedup: " . number_format($recursiveTime / $iterativeTime, 1) . "x faster with iteration\n\n";

echo "When to use Recursion:\n";
echo "  ✓ Trees/graphs\n";
echo "  ✓ Divide and conquer\n";
echo "  ✓ Backtracking\n";
echo "  ✓ Cleaner code\n\n";

echo "When to use Iteration:\n";
echo "  ✓ Performance critical\n";
echo "  ✓ Simple loops\n";
echo "  ✓ Avoid stack overflow\n";

echo "\n✅ Complete!\n";
