<?php
declare(strict_types=1);

echo "=== Common Recursion Patterns ===\n\n";

// Pattern 1: Linear recursion (one recursive call)
function linearExample(int $n): int {
    if ($n === 0) return 0;
    return $n + linearExample($n - 1);
}

// Pattern 2: Binary recursion (two recursive calls)
function binaryExample(int $n): int {
    if ($n <= 1) return $n;
    return binaryExample($n - 1) + binaryExample($n - 2);
}

// Pattern 3: Multiple recursion (many calls)
function gridPaths(int $x, int $y): int {
    if ($x === 0 && $y === 0) return 1;
    $paths = 0;
    if ($x > 0) $paths += gridPaths($x - 1, $y);
    if ($y > 0) $paths += gridPaths($x, $y - 1);
    return $paths;
}

// Pattern 4: Mutual recursion
function isEvenMutual(int $n): bool {
    if ($n === 0) return true;
    return isOddMutual($n - 1);
}

function isOddMutual(int $n): bool {
    if ($n === 0) return false;
    return isEvenMutual($n - 1);
}

echo "Linear (sum 1-10): " . linearExample(10) . "\n";
echo "Binary (fib 10): " . binaryExample(10) . "\n";
echo "Multiple (3x3 grid paths): " . gridPaths(3, 3) . "\n";
echo "Mutual (is 7 even?): " . (isEvenMutual(7) ? "Yes" : "No") . "\n";

echo "\n✅ Complete!\n";
