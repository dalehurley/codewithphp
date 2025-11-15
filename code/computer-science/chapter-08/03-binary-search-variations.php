<?php

declare(strict_types=1);

/**
 * Binary Search Variations
 *
 * Demonstrates:
 * - Find first occurrence
 * - Find last occurrence
 * - Find insertion position
 * - Count occurrences
 * - Find closest element
 * - Find peak element
 * - Search range boundaries
 */

echo "=== Binary Search Variations ===\n\n";

/**
 * Find first occurrence of target
 */
function findFirstOccurrence(array $arr, mixed $target): ?int
{
    $left = 0;
    $right = count($arr) - 1;
    $result = null;

    while ($left <= $right) {
        $mid = $left + (int)(($right - $left) / 2);

        if ($arr[$mid] === $target) {
            $result = $mid;
            $right = $mid - 1; // Continue searching left
        } elseif ($arr[$mid] < $target) {
            $left = $mid + 1;
        } else {
            $right = $mid - 1;
        }
    }

    return $result;
}

/**
 * Find last occurrence of target
 */
function findLastOccurrence(array $arr, mixed $target): ?int
{
    $left = 0;
    $right = count($arr) - 1;
    $result = null;

    while ($left <= $right) {
        $mid = $left + (int)(($right - $left) / 2);

        if ($arr[$mid] === $target) {
            $result = $mid;
            $left = $mid + 1; // Continue searching right
        } elseif ($arr[$mid] < $target) {
            $left = $mid + 1;
        } else {
            $right = $mid - 1;
        }
    }

    return $result;
}

/**
 * Count occurrences using binary search
 */
function countOccurrences(array $arr, mixed $target): int
{
    $first = findFirstOccurrence($arr, $target);

    if ($first === null) {
        return 0;
    }

    $last = findLastOccurrence($arr, $target);

    return $last - $first + 1;
}

/**
 * Find insertion position (lower bound)
 */
function findInsertionPosition(array $arr, mixed $target): int
{
    $left = 0;
    $right = count($arr);

    while ($left < $right) {
        $mid = $left + (int)(($right - $left) / 2);

        if ($arr[$mid] < $target) {
            $left = $mid + 1;
        } else {
            $right = $mid;
        }
    }

    return $left;
}

/**
 * Find closest element to target
 */
function findClosest(array $arr, mixed $target): int
{
    if (count($arr) === 0) {
        throw new Exception("Array is empty");
    }

    $left = 0;
    $right = count($arr) - 1;

    // Handle edge cases
    if ($target <= $arr[$left]) return $left;
    if ($target >= $arr[$right]) return $right;

    while ($left < $right) {
        $mid = $left + (int)(($right - $left) / 2);

        if ($arr[$mid] === $target) {
            return $mid;
        }

        if ($target < $arr[$mid]) {
            $right = $mid;
        } else {
            $left = $mid + 1;
        }
    }

    // left === right here, check left-1 vs left
    if ($left > 0) {
        $distLeft = abs($arr[$left - 1] - $target);
        $distRight = abs($arr[$left] - $target);

        return $distLeft <= $distRight ? $left - 1 : $left;
    }

    return $left;
}

/**
 * Find peak element (greater than neighbors)
 */
function findPeakElement(array $arr): int
{
    $left = 0;
    $right = count($arr) - 1;

    while ($left < $right) {
        $mid = $left + (int)(($right - $left) / 2);

        if ($arr[$mid] > $arr[$mid + 1]) {
            // Peak is on left side (or mid is peak)
            $right = $mid;
        } else {
            // Peak is on right side
            $left = $mid + 1;
        }
    }

    return $left;
}

/**
 * Search in range [minVal, maxVal]
 */
function searchRange(array $arr, mixed $target): array
{
    $first = findFirstOccurrence($arr, $target);
    $last = findLastOccurrence($arr, $target);

    return [$first, $last];
}

/**
 * Find floor (largest element <= target)
 */
function findFloor(array $arr, mixed $target): ?int
{
    $left = 0;
    $right = count($arr) - 1;
    $result = null;

    while ($left <= $right) {
        $mid = $left + (int)(($right - $left) / 2);

        if ($arr[$mid] === $target) {
            return $mid;
        }

        if ($arr[$mid] < $target) {
            $result = $mid;
            $left = $mid + 1;
        } else {
            $right = $mid - 1;
        }
    }

    return $result;
}

/**
 * Find ceiling (smallest element >= target)
 */
function findCeiling(array $arr, mixed $target): ?int
{
    $left = 0;
    $right = count($arr) - 1;
    $result = null;

    while ($left <= $right) {
        $mid = $left + (int)(($right - $left) / 2);

        if ($arr[$mid] === $target) {
            return $mid;
        }

        if ($arr[$mid] > $target) {
            $result = $mid;
            $right = $mid - 1;
        } else {
            $left = $mid + 1;
        }
    }

    return $result;
}

// Test 1: Find First and Last Occurrence
echo "1. Find First and Last Occurrence\n";
echo "===================================\n";

$numbers = [1, 2, 2, 2, 3, 4, 4, 4, 4, 5, 6];
echo "Array: [" . implode(', ', $numbers) . "]\n\n";

$searches = [2, 4, 5, 7];

foreach ($searches as $target) {
    $first = findFirstOccurrence($numbers, $target);
    $last = findLastOccurrence($numbers, $target);

    if ($first !== null) {
        echo "Target $target:\n";
        echo "  First occurrence: index $first\n";
        echo "  Last occurrence:  index $last\n";
        echo "  Count: " . ($last - $first + 1) . "\n";
    } else {
        echo "Target $target: Not found\n";
    }
    echo "\n";
}

// Test 2: Count Occurrences
echo "2. Count Occurrences (O(log n))\\n";
echo "================================\n";

$data = [1, 1, 1, 2, 2, 3, 3, 3, 3, 3, 4, 5, 5];
echo "Array: [" . implode(', ', $data) . "]\n\n";

echo "Element | Count\n";
echo "--------|------\n";

for ($i = 1; $i <= 5; $i++) {
    $count = countOccurrences($data, $i);
    echo sprintf("%7d | %5d\n", $i, $count);
}

echo "\nUsing binary search to count is O(log n)!\n";
echo "Linear count would be O(n)\n\n";

// Test 3: Find Insertion Position
echo "3. Find Insertion Position\n";
echo "===========================\n";

$sorted = [10, 20, 30, 40, 50, 60];
echo "Array: [" . implode(', ', $sorted) . "]\n\n";

$insertions = [5, 25, 35, 65, 40];

foreach ($insertions as $value) {
    $pos = findInsertionPosition($sorted, $value);
    echo "Insert $value at position $pos: [";

    $temp = $sorted;
    array_splice($temp, $pos, 0, $value);
    echo implode(', ', $temp) . "]\n";
}

echo "\n";

// Test 4: Find Closest Element
echo "4. Find Closest Element\n";
echo "========================\n";

$arr = [1, 3, 8, 10, 15];
echo "Array: [" . implode(', ', $arr) . "]\n\n";

$targets = [2, 5, 12, 17];

foreach ($targets as $target) {
    $closestIdx = findClosest($arr, $target);
    $closest = $arr[$closestIdx];
    $distance = abs($closest - $target);

    echo "Target $target: Closest is $closest at index $closestIdx (distance: $distance)\n";
}

echo "\n";

// Test 5: Find Peak Element
echo "5. Find Peak Element\n";
echo "=====================\n";

$peaks = [
    [1, 2, 3, 1],
    [1, 2, 1, 3, 5, 6, 4],
    [1, 3, 20, 4, 1, 0],
];

foreach ($peaks as $i => $arr) {
    echo "Array " . ($i + 1) . ": [" . implode(', ', $arr) . "]\n";

    $peakIdx = findPeakElement($arr);
    echo "  Peak element: {$arr[$peakIdx]} at index $peakIdx\n\n";
}

echo "Note: If multiple peaks exist, any one is valid!\n\n";

// Test 6: Search Range
echo "6. Search Range (Find All Indices)\n";
echo "====================================\n";

$numbers = [5, 7, 7, 8, 8, 8, 10];
echo "Array: [" . implode(', ', $numbers) . "]\n\n";

$target = 8;
[$first, $last] = searchRange($numbers, $target);

if ($first !== null) {
    echo "Target $target found in range: [$first, $last]\n";
    echo "Indices: ";
    for ($i = $first; $i <= $last; $i++) {
        echo "$i ";
    }
    echo "\n\n";
} else {
    echo "Target $target not found\n\n";
}

$target = 6;
[$first, $last] = searchRange($numbers, $target);
echo "Target $target: [" . ($first ?? 'null') . ", " . ($last ?? 'null') . "] (not found)\n\n";

// Test 7: Floor and Ceiling
echo "7. Floor and Ceiling\n";
echo "=====================\n";

$arr = [1, 2, 8, 10, 10, 12, 19];
echo "Array: [" . implode(', ', $arr) . "]\n\n";

echo "Target | Floor | Ceiling\n";
echo "-------|-------|--------\n";

$targets = [0, 1, 5, 10, 15, 20];

foreach ($targets as $target) {
    $floorIdx = findFloor($arr, $target);
    $ceilingIdx = findCeiling($arr, $target);

    $floor = $floorIdx !== null ? $arr[$floorIdx] : 'none';
    $ceiling = $ceilingIdx !== null ? $arr[$ceilingIdx] : 'none';

    echo sprintf("%6d | %5s | %7s\n", $target, $floor, $ceiling);
}

echo "\n";
echo "Floor: Largest element ≤ target\n";
echo "Ceiling: Smallest element ≥ target\n\n";

// Test 8: Practical Application - Version Search
echo "8. Practical Application: Version Search\n";
echo "==========================================\n";

class Version
{
    public function __construct(
        public string $version,
        public bool $hasBug
    ) {}
}

$versions = [
    new Version('1.0.0', false),
    new Version('1.0.1', false),
    new Version('1.1.0', false),
    new Version('1.2.0', true),  // Bug introduced here
    new Version('1.2.1', true),
    new Version('1.3.0', true),
    new Version('2.0.0', true),
];

echo "Find first version with bug:\n\n";

$left = 0;
$right = count($versions) - 1;
$firstBug = null;

while ($left <= $right) {
    $mid = $left + (int)(($right - $left) / 2);

    if ($versions[$mid]->hasBug) {
        $firstBug = $mid;
        $right = $mid - 1; // Look for earlier bug
    } else {
        $left = $mid + 1;
    }
}

if ($firstBug !== null) {
    echo "First buggy version: {$versions[$firstBug]->version} (index $firstBug)\n";
    echo "Checked ~" . (int)log(count($versions), 2) . " versions (binary search)\n";
    echo "Linear search would check ~" . ($firstBug + 1) . " versions\n\n";
}

// Test 9: Search in Duplicates
echo "9. Handling Arrays with Many Duplicates\n";
echo "=========================================\n";

$duplicates = array_merge(
    array_fill(0, 100, 1),
    array_fill(0, 200, 2),
    array_fill(0, 150, 3),
    array_fill(0, 50, 4)
);

echo "Array with 500 elements (many duplicates):\n";
echo "  1 appears 100 times\n";
echo "  2 appears 200 times\n";
echo "  3 appears 150 times\n";
echo "  4 appears 50 times\n\n";

$target = 2;
$start = microtime(true);
$count = countOccurrences($duplicates, $target);
$binaryTime = (microtime(true) - $start) * 1000000;

echo "Count of $target using binary search:\n";
echo "  Count: $count\n";
echo "  Time: " . number_format($binaryTime, 2) . " μs\n\n";

// Linear count for comparison
$start = microtime(true);
$linearCount = count(array_filter($duplicates, fn($x) => $x === $target));
$linearTime = (microtime(true) - $start) * 1000000;

echo "Count of $target using linear search:\n";
echo "  Count: $linearCount\n";
echo "  Time: " . number_format($linearTime, 2) . " μs\n\n";

// Test 10: Practical Application - Database Range Queries
echo "10. Database Range Queries\n";
echo "===========================\n";

class Record
{
    public function __construct(
        public int $id,
        public string $name
    ) {}
}

$database = [
    new Record(100, 'Alice'),
    new Record(105, 'Bob'),
    new Record(110, 'Charlie'),
    new Record(115, 'Diana'),
    new Record(120, 'Eve'),
    new Record(125, 'Frank'),
    new Record(130, 'Grace'),
];

// Extract IDs for binary search
$ids = array_map(fn($r) => $r->id, $database);

echo "Database (sorted by ID):\n";
foreach ($database as $record) {
    echo "  ID {$record->id}: {$record->name}\n";
}
echo "\n";

// Range query: Find all records with ID between 110 and 125
$minId = 110;
$maxId = 125;

echo "Query: Find records with ID between $minId and $maxId\n\n";

$startIdx = findInsertionPosition($ids, $minId);
$endIdx = findInsertionPosition($ids, $maxId + 1) - 1;

echo "Result indices: [$startIdx, $endIdx]\n";
echo "Matching records:\n";

for ($i = $startIdx; $i <= $endIdx && $i < count($database); $i++) {
    echo "  ID {$database[$i]->id}: {$database[$i]->name}\n";
}

echo "\nBinary search enables efficient range queries!\n";
echo "Time: O(log n) to find boundaries + O(k) to return results\n\n";

echo "Key Insights:\n";
echo "==============\n\n";

echo "Binary Search Variations:\n";
echo "  • First occurrence:     Find leftmost match\n";
echo "  • Last occurrence:      Find rightmost match\n";
echo "  • Count occurrences:    O(log n) instead of O(n)\n";
echo "  • Insertion position:   Where to insert to maintain sorted order\n";
echo "  • Closest element:      Find nearest value\n";
echo "  • Peak element:         Find local maximum\n";
echo "  • Floor/Ceiling:        Boundary searches\n\n";

echo "Common Patterns:\n";
echo "  1. Finding boundaries → Keep searching after finding match\n";
echo "  2. Insertion position → Use left < right (not <=)\n";
echo "  3. Closest element → Check neighbors after binary search\n";
echo "  4. Peak finding → Compare with neighbor, move toward larger\n\n";

echo "Real-World Applications:\n";
echo "  • Database range queries (SELECT WHERE id BETWEEN x AND y)\n";
echo "  • Version bisect (find first bad commit)\n";
echo "  • Finding duplicates count in O(log n)\n";
echo "  • Auto-complete with prefix matching\n";
echo "  • Finding closest color in sorted palette\n";
echo "  • Scheduling: find available time slots\n\n";

echo "Interview Problems Using These Patterns:\n";
echo "  ✓ Search in Rotated Sorted Array\n";
echo "  ✓ Find First and Last Position of Element\n";
echo "  ✓ Search Insert Position\n";
echo "  ✓ Find Peak Element\n";
echo "  ✓ Single Element in Sorted Array\n";
echo "  ✓ Find Minimum in Rotated Sorted Array\n\n";

echo "Template for Binary Search Variations:\n";
echo "  1. Identify what you're searching for\n";
echo "  2. Define the invariant (what stays true)\n";
echo "  3. Adjust left/right based on condition\n";
echo "  4. Handle edge cases (empty, single element)\n";
echo "  5. Return appropriate result\n";

echo "\n✅ Complete! Binary search variations demonstrated.\n";
