<?php
declare(strict_types=1);

echo "=== Tail Recursion ===\n\n";

// Not tail recursive - work after recursive call
function factorialNormal(int $n): int {
    if ($n <= 1) return 1;
    return $n * factorialNormal($n - 1); // Multiplication after call
}

// Tail recursive - no work after recursive call
function factorialTail(int $n, int $acc = 1): int {
    if ($n <= 1) return $acc;
    return factorialTail($n - 1, $n * $acc); // Last operation is recursive call
}

// Sum with tail recursion
function sumTail(int $n, int $acc = 0): int {
    if ($n === 0) return $acc;
    return sumTail($n - 1, $acc + $n);
}

echo "Normal factorial(5): " . factorialNormal(5) . "\n";
echo "Tail factorial(5): " . factorialTail(5) . "\n";
echo "Tail sum(100): " . sumTail(100) . "\n\n";

echo "Tail recursion: Last operation is the recursive call\n";
echo "Benefit: Some compilers optimize to iteration (not PHP)\n";

echo "\n✅ Complete!\n";
