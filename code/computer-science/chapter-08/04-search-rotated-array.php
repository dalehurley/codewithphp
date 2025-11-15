<?php

declare(strict_types=1);

/**
 * Search in Rotated Sorted Array
 *
 * Demonstrates:
 * - Search in rotated sorted array (no duplicates)
 * - Search in rotated sorted array (with duplicates)
 * - Find minimum in rotated sorted array
 * - Find rotation point/pivot
 * - Determine rotation count
 * - Real-world applications
 */

echo "=== Search in Rotated Sorted Array ===\n\n";

echo "Concept: Array sorted then rotated at unknown pivot\n";
echo "         Example: [1,2,3,4,5,6,7] → [4,5,6,7,1,2,3]\n";
echo "         Still O(log n) with modified binary search\n\n";

/**
 * Search in rotated sorted array (no duplicates)
 */
function searchRotated(array $arr, mixed $target): ?int
{
    $left = 0;
    $right = count($arr) - 1;

    while ($left <= $right) {
        $mid = $left + (int)(($right - $left) / 2);

        if ($arr[$mid] === $target) {
            return $mid;
        }

        // Determine which half is sorted
        if ($arr[$left] <= $arr[$mid]) {
            // Left half is sorted
            if ($arr[$left] <= $target && $target < $arr[$mid]) {
                // Target is in sorted left half
                $right = $mid - 1;
            } else {
                // Target is in right half
                $left = $mid + 1;
            }
        } else {
            // Right half is sorted
            if ($arr[$mid] < $target && $target <= $arr[$right]) {
                // Target is in sorted right half
                $left = $mid + 1;
            } else {
                // Target is in left half
                $right = $mid - 1;
            }
        }
    }

    return null;
}

/**
 * Find minimum element in rotated sorted array
 */
function findMin(array $arr): mixed
{
    $left = 0;
    $right = count($arr) - 1;

    while ($left < $right) {
        $mid = $left + (int)(($right - $left) / 2);

        if ($arr[$mid] > $arr[$right]) {
            // Minimum is in right half
            $left = $mid + 1;
        } else {
            // Minimum is in left half (or mid itself)
            $right = $mid;
        }
    }

    return $arr[$left];
}

/**
 * Find rotation count (index of minimum)
 */
function findRotationCount(array $arr): int
{
    $left = 0;
    $right = count($arr) - 1;

    while ($left < $right) {
        $mid = $left + (int)(($right - $left) / 2);

        if ($arr[$mid] > $arr[$right]) {
            $left = $mid + 1;
        } else {
            $right = $mid;
        }
    }

    return $left;
}

/**
 * Search in rotated sorted array with duplicates
 */
function searchRotatedWithDuplicates(array $arr, mixed $target): ?int
{
    $left = 0;
    $right = count($arr) - 1;

    while ($left <= $right) {
        $mid = $left + (int)(($right - $left) / 2);

        if ($arr[$mid] === $target) {
            return $mid;
        }

        // Handle duplicates
        if ($arr[$left] === $arr[$mid] && $arr[$mid] === $arr[$right]) {
            $left++;
            $right--;
            continue;
        }

        // Same logic as without duplicates
        if ($arr[$left] <= $arr[$mid]) {
            if ($arr[$left] <= $target && $target < $arr[$mid]) {
                $right = $mid - 1;
            } else {
                $left = $mid + 1;
            }
        } else {
            if ($arr[$mid] < $target && $target <= $arr[$right]) {
                $left = $mid + 1;
            } else {
                $right = $mid - 1;
            }
        }
    }

    return null;
}

/**
 * Find pivot point (rotation point)
 */
function findPivot(array $arr): int
{
    $left = 0;
    $right = count($arr) - 1;

    while ($left < $right) {
        $mid = $left + (int)(($right - $left) / 2);

        if ($arr[$mid] > $arr[$mid + 1]) {
            return $mid;
        }

        if ($arr[$mid] < $arr[$mid - 1]) {
            return $mid - 1;
        }

        if ($arr[$left] > $arr[$mid]) {
            $right = $mid - 1;
        } else {
            $left = $mid + 1;
        }
    }

    return $left;
}

// Test 1: Basic Search in Rotated Array
echo "1. Search in Rotated Sorted Array\n";
echo "===================================\n";

$rotated = [4, 5, 6, 7, 0, 1, 2];
echo "Rotated array: [" . implode(', ', $rotated) . "]\n";
echo "Original was: [0, 1, 2, 3, 4, 5, 6, 7]\n";
echo "Rotated at index 4\n\n";

$searches = [0, 3, 4, 7, 5];

foreach ($searches as $target) {
    $result = searchRotated($rotated, $target);

    if ($result !== null) {
        echo "Search for $target: Found at index $result\n";
    } else {
        echo "Search for $target: Not found\n";
    }
}
echo "\n";

// Test 2: Different Rotation Points
echo "2. Arrays Rotated at Different Points\n";
echo "=======================================\n";

$arrays = [
    [1, 2, 3, 4, 5, 6, 7],         // No rotation
    [2, 3, 4, 5, 6, 7, 1],         // Rotated by 1
    [4, 5, 6, 7, 1, 2, 3],         // Rotated by 3
    [7, 1, 2, 3, 4, 5, 6],         // Rotated by 6
];

$target = 5;

foreach ($arrays as $i => $arr) {
    echo "Array " . ($i + 1) . ": [" . implode(', ', $arr) . "]\n";
    $result = searchRotated($arr, $target);
    echo "  Search for $target: " . ($result !== null ? "Found at index $result" : "Not found") . "\n\n";
}

// Test 3: Find Minimum Element
echo "3. Find Minimum in Rotated Array\n";
echo "==================================\n";

$rotatedArrays = [
    [3, 4, 5, 1, 2],
    [4, 5, 6, 7, 0, 1, 2],
    [11, 13, 15, 17],
    [2, 1],
];

foreach ($rotatedArrays as $i => $arr) {
    echo "Array " . ($i + 1) . ": [" . implode(', ', $arr) . "]\n";
    $min = findMin($arr);
    echo "  Minimum: $min\n\n";
}

// Test 4: Find Rotation Count
echo "4. Find Rotation Count\n";
echo "=======================\n";

echo "Rotation count = index of minimum element\n\n";

$examples = [
    [15, 18, 2, 3, 6, 12],
    [7, 9, 11, 12, 5],
    [1, 2, 3, 4],
];

foreach ($examples as $i => $arr) {
    echo "Array " . ($i + 1) . ": [" . implode(', ', $arr) . "]\n";
    $rotationCount = findRotationCount($arr);
    echo "  Rotated $rotationCount times\n";
    echo "  Original array started at index $rotationCount\n\n";
}

// Test 5: Step-by-Step Visualization
echo "5. Step-by-Step Search Visualization\n";
echo "======================================\n";

$arr = [4, 5, 6, 7, 0, 1, 2];
$target = 0;

echo "Array: [" . implode(', ', $arr) . "]\n";
echo "Target: $target\n\n";

$left = 0;
$right = count($arr) - 1;
$step = 1;

while ($left <= $right) {
    $mid = $left + (int)(($right - $left) / 2);

    echo "Step $step:\n";
    echo "  Range: [$left..$right]\n";
    echo "  Mid: $mid (value: {$arr[$mid]})\n";

    if ($arr[$mid] === $target) {
        echo "  Result: FOUND at index $mid!\n";
        break;
    }

    if ($arr[$left] <= $arr[$mid]) {
        echo "  Left half [$left..$mid] is sorted\n";
        if ($arr[$left] <= $target && $target < $arr[$mid]) {
            echo "  Action: Target in sorted left half → search left\n";
            $right = $mid - 1;
        } else {
            echo "  Action: Target in right half → search right\n";
            $left = $mid + 1;
        }
    } else {
        echo "  Right half [$mid..$right] is sorted\n";
        if ($arr[$mid] < $target && $target <= $arr[$right]) {
            echo "  Action: Target in sorted right half → search right\n";
            $left = $mid + 1;
        } else {
            echo "  Action: Target in left half → search left\n";
            $right = $mid - 1;
        }
    }

    echo "\n";
    $step++;
}

echo "\n";

// Test 6: Search with Duplicates
echo "6. Search in Rotated Array with Duplicates\n";
echo "============================================\n";

$withDuplicates = [2, 5, 6, 0, 0, 1, 2];
echo "Array: [" . implode(', ', $withDuplicates) . "]\n\n";

$searches = [0, 3, 5];

foreach ($searches as $target) {
    $result = searchRotatedWithDuplicates($withDuplicates, $target);
    echo "Search for $target: " . ($result !== null ? "Found at index $result" : "Not found") . "\n";
}

echo "\nNote: Duplicates can degrade to O(n) worst case\n\n";

// Test 7: Find Pivot Point
echo "7. Find Pivot Point (Rotation Point)\n";
echo "======================================\n";

$arrays = [
    [3, 4, 5, 6, 7, 1, 2],
    [4, 5, 6, 1, 2, 3],
    [5, 1, 2, 3, 4],
];

foreach ($arrays as $i => $arr) {
    echo "Array " . ($i + 1) . ": [" . implode(', ', $arr) . "]\n";
    $pivot = findPivot($arr);
    echo "  Pivot at index $pivot (value: {$arr[$pivot]})\n";
    echo "  Array rotated after this element\n\n";
}

// Test 8: Performance Comparison
echo "8. Performance Comparison\n";
echo "==========================\n";

$size = 10000;
$original = range(1, $size);
$rotateBy = $size / 2;

// Create rotated array
$rotated = array_merge(
    array_slice($original, $rotateBy),
    array_slice($original, 0, $rotateBy)
);

$target = $size - 1; // Near end (worst case)

// Binary search in rotated array
$start = microtime(true);
for ($i = 0; $i < 1000; $i++) {
    searchRotated($rotated, $target);
}
$binaryTime = (microtime(true) - $start) * 1000;

// Linear search
$start = microtime(true);
for ($i = 0; $i < 1000; $i++) {
    array_search($target, $rotated, true);
}
$linearTime = (microtime(true) - $start) * 1000;

echo "Array size: $size (rotated)\n";
echo "Binary search (rotated): " . number_format($binaryTime, 3) . " ms\n";
echo "Linear search:           " . number_format($linearTime, 3) . " ms\n";
echo "Speedup: " . number_format($linearTime / $binaryTime, 1) . "x\n\n";

echo "Binary search maintains O(log n) even when rotated!\n\n";

// Test 9: Practical Application - Circular Buffer
echo "9. Practical Application: Circular Buffer Search\n";
echo "==================================================\n";

class CircularBuffer
{
    private array $buffer;
    private int $start = 0;

    public function __construct(array $data)
    {
        $this->buffer = $data;
    }

    public function rotate(int $positions): void
    {
        $this->start = ($this->start + $positions) % count($this->buffer);
    }

    public function search(mixed $target): ?int
    {
        // Create logical view of rotated buffer
        $rotated = array_merge(
            array_slice($this->buffer, $this->start),
            array_slice($this->buffer, 0, $this->start)
        );

        return searchRotated($rotated, $target);
    }

    public function display(): string
    {
        $rotated = array_merge(
            array_slice($this->buffer, $this->start),
            array_slice($this->buffer, 0, $this->start)
        );

        return "[" . implode(', ', $rotated) . "]";
    }
}

$buffer = new CircularBuffer([10, 20, 30, 40, 50, 60, 70]);

echo "Initial buffer: " . $buffer->display() . "\n";

$buffer->rotate(3);
echo "After rotating 3 positions: " . $buffer->display() . "\n\n";

$target = 30;
$result = $buffer->search($target);
echo "Search for $target: " . ($result !== null ? "Found at logical index $result" : "Not found") . "\n\n";

// Test 10: Edge Cases
echo "10. Edge Cases\n";
echo "===============\n";

$edgeCases = [
    'Single element' => [1],
    'Two elements (no rotation)' => [1, 2],
    'Two elements (rotated)' => [2, 1],
    'All same elements' => [1, 1, 1, 1, 1],
    'Large rotation' => [6, 7, 1, 2, 3, 4, 5],
];

foreach ($edgeCases as $name => $arr) {
    echo "$name: [" . implode(', ', $arr) . "]\n";

    $target = $arr[0];
    $result = searchRotated($arr, $target);
    echo "  Search for $target: " . ($result !== null ? "Found at index $result" : "Not found") . "\n";

    $min = findMin($arr);
    echo "  Minimum: $min\n\n";
}

echo "Key Insights:\n";
echo "==============\n\n";

echo "Rotated Array Search Strategy:\n";
echo "  1. Find mid point\n";
echo "  2. Determine which half is sorted\n";
echo "  3. Check if target is in sorted half\n";
echo "  4. If yes → search sorted half\n";
echo "  5. If no → search other half\n\n";

echo "Time Complexity:\n";
echo "  • Search:            O(log n) - same as regular binary search\n";
echo "  • Find minimum:      O(log n)\n";
echo "  • Find pivot:        O(log n)\n";
echo "  • With duplicates:   O(n) worst case, O(log n) average\n\n";

echo "Key Observations:\n";
echo "  • At least one half is always sorted\n";
echo "  • Use sorted half to determine search direction\n";
echo "  • Pivot is where arr[i] > arr[i+1]\n";
echo "  • Minimum is at pivot + 1\n\n";

echo "Real-World Applications:\n";
echo "  • Circular buffers (ring buffers)\n";
echo "  • Log rotation systems\n";
echo "  • Scheduling with wrap-around\n";
echo "  • Cache systems with rotation\n";
echo "  • Time-based data with day boundaries\n\n";

echo "Common Pitfalls:\n";
echo "  ⚠ Forgetting to handle duplicates\n";
echo "  ⚠ Integer overflow in mid calculation (use left + (right-left)/2)\n";
echo "  ⚠ Wrong comparison operators (< vs <=)\n";
echo "  ⚠ Not handling single/two element arrays\n\n";

echo "Interview Variations:\n";
echo "  • Search in rotated sorted array\n";
echo "  • Find minimum in rotated sorted array\n";
echo "  • Find if array is rotated version of sorted array\n";
echo "  • Count rotations\n";
echo "  • Search in rotated array with duplicates\n\n";

echo "Why This Works:\n";
echo "  Original:  [1, 2, 3, 4, 5, 6, 7]\n";
echo "  Rotated:   [4, 5, 6, 7, 1, 2, 3]\n";
echo "             \\________/  \\______/\n";
echo "               sorted     sorted\n";
echo "  At any mid point, one half is guaranteed sorted!\n";

echo "\n✅ Complete! Rotated array search demonstrated.\n";
