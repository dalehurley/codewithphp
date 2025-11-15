<?php

declare(strict_types=1);

/**
 * Searching in 2D Matrices
 *
 * Demonstrates:
 * - Search in row-wise and column-wise sorted matrix
 * - Search in fully sorted matrix (treat as 1D)
 * - Staircase search algorithm
 * - Binary search on 2D matrix
 * - Search in Young tableau
 * - Practical applications
 */

echo "=== Searching in 2D Matrices ===\n\n";

/**
 * Search in row-wise and column-wise sorted matrix (Staircase algorithm)
 */
function searchMatrix2D(array $matrix, int $target): ?array
{
    if (empty($matrix) || empty($matrix[0])) {
        return null;
    }

    $rows = count($matrix);
    $cols = count($matrix[0]);

    // Start from top-right corner
    $row = 0;
    $col = $cols - 1;

    while ($row < $rows && $col >= 0) {
        if ($matrix[$row][$col] === $target) {
            return [$row, $col];
        }

        if ($matrix[$row][$col] > $target) {
            // Move left
            $col--;
        } else {
            // Move down
            $row++;
        }
    }

    return null;
}

/**
 * Search in fully sorted matrix (treat as 1D array)
 */
function searchSortedMatrix(array $matrix, int $target): ?array
{
    if (empty($matrix) || empty($matrix[0])) {
        return null;
    }

    $rows = count($matrix);
    $cols = count($matrix[0]);

    $left = 0;
    $right = $rows * $cols - 1;

    while ($left <= $right) {
        $mid = $left + (int)(($right - $left) / 2);

        // Convert 1D index to 2D coordinates
        $row = (int)($mid / $cols);
        $col = $mid % $cols;

        if ($matrix[$row][$col] === $target) {
            return [$row, $col];
        }

        if ($matrix[$row][$col] < $target) {
            $left = $mid + 1;
        } else {
            $right = $mid - 1;
        }
    }

    return null;
}

/**
 * Count elements smaller than target
 */
function countSmaller(array $matrix, int $target): int
{
    if (empty($matrix) || empty($matrix[0])) {
        return 0;
    }

    $rows = count($matrix);
    $cols = count($matrix[0]);
    $count = 0;

    // Start from bottom-left
    $row = $rows - 1;
    $col = 0;

    while ($row >= 0 && $col < $cols) {
        if ($matrix[$row][$col] < $target) {
            // All elements in this column up to this row are smaller
            $count += $row + 1;
            $col++;
        } else {
            $row--;
        }
    }

    return $count;
}

/**
 * Find kth smallest element in row-wise and column-wise sorted matrix
 */
function kthSmallest(array $matrix, int $k): int
{
    $rows = count($matrix);
    $cols = count($matrix[0]);

    $low = $matrix[0][0];
    $high = $matrix[$rows - 1][$cols - 1];

    while ($low < $high) {
        $mid = $low + (int)(($high - $low) / 2);
        $count = countSmaller($matrix, $mid + 1);

        if ($count < $k) {
            $low = $mid + 1;
        } else {
            $high = $mid;
        }
    }

    return $low;
}

/**
 * Search in matrix with each row sorted
 */
function searchMatrixRowSorted(array $matrix, int $target): ?array
{
    foreach ($matrix as $rowIdx => $row) {
        // Binary search in each row
        $left = 0;
        $right = count($row) - 1;

        while ($left <= $right) {
            $mid = $left + (int)(($right - $left) / 2);

            if ($row[$mid] === $target) {
                return [$rowIdx, $mid];
            }

            if ($row[$mid] < $target) {
                $left = $mid + 1;
            } else {
                $right = $mid - 1;
            }
        }
    }

    return null;
}

// Test 1: Search in Row-wise and Column-wise Sorted Matrix
echo "1. Row-wise and Column-wise Sorted Matrix\n";
echo "===========================================\n";

$matrix = [
    [10, 20, 30, 40],
    [15, 25, 35, 45],
    [27, 29, 37, 48],
    [32, 33, 39, 50],
];

echo "Matrix (each row and column sorted):\n";
foreach ($matrix as $row) {
    echo "  [" . implode(', ', $row) . "]\n";
}
echo "\n";

$searches = [29, 50, 10, 99];

foreach ($searches as $target) {
    $result = searchMatrix2D($matrix, $target);

    if ($result !== null) {
        echo "Search for $target: Found at position [{$result[0]}, {$result[1]}]\n";
    } else {
        echo "Search for $target: Not found\n";
    }
}

echo "\n";

// Test 2: Staircase Algorithm Visualization
echo "2. Staircase Algorithm Step-by-Step\n";
echo "=====================================\n";

$matrix = [
    [1, 4, 7, 11],
    [2, 5, 8, 12],
    [3, 6, 9, 16],
    [10, 13, 14, 17],
];

$target = 5;

echo "Matrix:\n";
foreach ($matrix as $row) {
    echo "  [" . implode(', ', $row) . "]\n";
}
echo "\nTarget: $target\n";
echo "Starting from top-right corner\n\n";

$rows = count($matrix);
$cols = count($matrix[0]);
$row = 0;
$col = $cols - 1;
$step = 1;

while ($row < $rows && $col >= 0) {
    echo "Step $step: Position [$row, $col] = {$matrix[$row][$col]}\n";

    if ($matrix[$row][$col] === $target) {
        echo "  Found target!\n";
        break;
    }

    if ($matrix[$row][$col] > $target) {
        echo "  {$matrix[$row][$col]} > $target → Move left\n";
        $col--;
    } else {
        echo "  {$matrix[$row][$col]} < $target → Move down\n";
        $row++;
    }

    $step++;
    echo "\n";
}

echo "\n";

// Test 3: Fully Sorted Matrix (Binary Search)
echo "3. Fully Sorted Matrix (Treat as 1D Array)\n";
echo "============================================\n";

$sortedMatrix = [
    [1, 3, 5, 7],
    [10, 11, 16, 20],
    [23, 30, 34, 60],
];

echo "Matrix (fully sorted, first of next row > last of previous):\n";
foreach ($sortedMatrix as $row) {
    echo "  [" . implode(', ', $row) . "]\n";
}
echo "\n";

$searches = [3, 16, 60, 13];

foreach ($searches as $target) {
    $result = searchSortedMatrix($sortedMatrix, $target);

    if ($result !== null) {
        echo "Search for $target: Found at [{$result[0]}, {$result[1]}]\n";
    } else {
        echo "Search for $target: Not found\n";
    }
}

echo "\nTime: O(log(m*n)) - treat as single sorted array\n\n";

// Test 4: Count Elements Smaller Than Target
echo "4. Count Elements Smaller Than Target\n";
echo "=======================================\n";

$matrix = [
    [1, 3, 5],
    [2, 6, 9],
    [3, 6, 9],
];

echo "Matrix:\n";
foreach ($matrix as $row) {
    echo "  [" . implode(', ', $row) . "]\n";
}
echo "\n";

$targets = [4, 7, 10];

foreach ($targets as $target) {
    $count = countSmaller($matrix, $target);
    echo "Elements < $target: $count\n";
}

echo "\n";

// Test 5: Find Kth Smallest Element
echo "5. Find Kth Smallest Element\n";
echo "==============================\n";

$matrix = [
    [1, 5, 9],
    [10, 11, 13],
    [12, 13, 15],
];

echo "Matrix:\n";
foreach ($matrix as $row) {
    echo "  [" . implode(', ', $row) . "]\n";
}
echo "\nAll elements sorted: [1, 5, 9, 10, 11, 12, 13, 13, 15]\n\n";

for ($k = 1; $k <= 9; $k++) {
    $result = kthSmallest($matrix, $k);
    echo "{$k}th smallest: $result\n";
}

echo "\nTime: O(n * log(max-min)) using binary search on values\n\n";

// Test 6: Different Matrix Types
echo "6. Different Matrix Sorting Patterns\n";
echo "======================================\n";

echo "Type 1: Row-wise and column-wise sorted\n";
$type1 = [
    [1, 2, 3],
    [2, 3, 4],
    [3, 4, 5],
];
foreach ($type1 as $row) {
    echo "  [" . implode(', ', $row) . "]\n";
}
echo "  Use: Staircase search O(m+n)\n\n";

echo "Type 2: Fully sorted (1D-like)\n";
$type2 = [
    [1, 2, 3],
    [4, 5, 6],
    [7, 8, 9],
];
foreach ($type2 as $row) {
    echo "  [" . implode(', ', $row) . "]\n";
}
echo "  Use: Binary search O(log(m*n))\n\n";

echo "Type 3: Each row sorted independently\n";
$type3 = [
    [1, 3, 5],
    [2, 4, 6],
    [7, 9, 11],
];
foreach ($type3 as $row) {
    echo "  [" . implode(', ', $row) . "]\n";
}
echo "  Use: Binary search each row O(m*log n)\n\n";

// Test 7: Performance Comparison
echo "7. Performance Comparison\n";
echo "==========================\n";

$largeMatrix = [];
for ($i = 0; $i < 100; $i++) {
    $row = [];
    for ($j = 0; $j < 100; $j++) {
        $row[] = $i * 100 + $j;
    }
    $largeMatrix[] = $row;
}

$target = 5050;

// Staircase search
$start = microtime(true);
for ($i = 0; $i < 1000; $i++) {
    searchMatrix2D($largeMatrix, $target);
}
$staircaseTime = (microtime(true) - $start) * 1000;

// Binary search (treat as 1D)
$start = microtime(true);
for ($i = 0; $i < 1000; $i++) {
    searchSortedMatrix($largeMatrix, $target);
}
$binaryTime = (microtime(true) - $start) * 1000;

echo "Matrix: 100x100 (10,000 elements)\n";
echo "Target: $target\n\n";

echo "Staircase search (O(m+n)): " . number_format($staircaseTime, 3) . " ms\n";
echo "Binary search (O(log mn)): " . number_format($binaryTime, 3) . " ms\n\n";

echo "Binary search wins for fully sorted matrix!\n\n";

// Test 8: Practical Application - Spreadsheet Search
echo "8. Practical Application: Spreadsheet Search\n";
echo "==============================================\n";

$salesData = [
    ['Date' => '2024-01', 'Sales' => 1000],
    ['Date' => '2024-02', 'Sales' => 1500],
    ['Date' => '2024-03', 'Sales' => 1200],
    ['Date' => '2024-04', 'Sales' => 1800],
    ['Date' => '2024-05', 'Sales' => 2000],
];

// Extract sales column for binary search
$sales = array_column($salesData, 'Sales');

echo "Sales data (sorted by date, sales growing):\n";
foreach ($salesData as $row) {
    echo "  {$row['Date']}: \${$row['Sales']}\n";
}
echo "\n";

$targetSales = 1800;

// Binary search in sales column
$left = 0;
$right = count($sales) - 1;

while ($left <= $right) {
    $mid = $left + (int)(($right - $left) / 2);

    if ($sales[$mid] === $targetSales) {
        echo "Found sales of \$$targetSales in {$salesData[$mid]['Date']}\n";
        break;
    }

    if ($sales[$mid] < $targetSales) {
        $left = $mid + 1;
    } else {
        $right = $mid - 1;
    }
}

echo "\n";

// Test 9: Search in Young Tableau
echo "9. Young Tableau (Special Matrix)\n";
echo "===================================\n";

echo "Young Tableau properties:\n";
echo "  • Each row sorted left to right\n";
echo "  • Each column sorted top to bottom\n";
echo "  • Can have ∞ (empty cells)\n\n";

$youngTableau = [
    [2, 3, 12, 14],
    [4, 7, 15, 20],
    [6, 10, 17, PHP_INT_MAX],
    [8, 11, PHP_INT_MAX, PHP_INT_MAX],
];

echo "Young Tableau (PHP_INT_MAX represents empty):\n";
foreach ($youngTableau as $row) {
    $display = array_map(fn($x) => $x === PHP_INT_MAX ? '∞' : $x, $row);
    echo "  [" . implode(', ', $display) . "]\n";
}
echo "\n";

$target = 7;
$result = searchMatrix2D($youngTableau, $target);

if ($result !== null) {
    echo "Search for $target: Found at [{$result[0]}, {$result[1]}]\n";
}

echo "\nYoung Tableau used in sorting and priority queues\n\n";

// Test 10: Matrix Search Problems Summary
echo "10. Matrix Search Problems Summary\n";
echo "====================================\n\n";

echo "Problem Types:\n\n";

echo "1. Row & Column Sorted Matrix\n";
echo "   Algorithm: Staircase search\n";
echo "   Time: O(m + n)\n";
echo "   Start: Top-right or bottom-left\n";
echo "   Example: searchMatrix2D()\n\n";

echo "2. Fully Sorted Matrix\n";
echo "   Algorithm: Binary search (treat as 1D)\n";
echo "   Time: O(log(m*n))\n";
echo "   Convert: idx → (row, col) = (idx/n, idx%n)\n";
echo "   Example: searchSortedMatrix()\n\n";

echo "3. Each Row Sorted\n";
echo "   Algorithm: Binary search each row\n";
echo "   Time: O(m * log n)\n";
echo "   Example: searchMatrixRowSorted()\n\n";

echo "4. Kth Smallest in Sorted Matrix\n";
echo "   Algorithm: Binary search on values + count\n";
echo "   Time: O(n * log(max-min))\n";
echo "   Example: kthSmallest()\n\n";

echo "Complexity Comparison:\n";
echo "  Method          | Time           | Space | Best For\n";
echo "  ----------------|----------------|-------|------------------\n";
echo "  Brute Force     | O(m*n)         | O(1)  | Unsorted\n";
echo "  Staircase       | O(m+n)         | O(1)  | Row & col sorted\n";
echo "  Binary 1D       | O(log(m*n))    | O(1)  | Fully sorted\n";
echo "  Binary per row  | O(m*log n)     | O(1)  | Each row sorted\n\n";

echo "Key Insights:\n";
echo "  • Matrix properties determine optimal algorithm\n";
echo "  • Staircase: Start corner, eliminate row OR column each step\n";
echo "  • Binary search: Convert 2D coordinates ↔ 1D index\n";
echo "  • Always check: sorted pattern, search frequency\n\n";

echo "Real-World Applications:\n";
echo "  • Database table scans (sorted columns)\n";
echo "  • Image processing (searching pixel values)\n";
echo "  • Game boards (pathfinding in grids)\n";
echo "  • Spreadsheet data lookup\n";
echo "  • Scientific data matrices\n\n";

echo "Interview Problems:\n";
echo "  ✓ Search a 2D Matrix\n";
echo "  ✓ Search a 2D Matrix II\n";
echo "  ✓ Kth Smallest Element in Sorted Matrix\n";
echo "  ✓ Count Negative Numbers in Sorted Matrix\n";
echo "  ✓ Lucky Numbers in a Matrix\n";

echo "\n✅ Complete! 2D matrix search demonstrated.\n";
