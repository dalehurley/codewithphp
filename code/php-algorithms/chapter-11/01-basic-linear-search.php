<?php

declare(strict_types=1);

/**
 * Chapter 11: Linear Search & Variants
 * Part 1: Basic Linear Search Implementations
 * 
 * Demonstrates fundamental linear search algorithms:
 * - Basic linear search (foreach)
 * - Loop-based linear search
 * - Recursive linear search
 * - Find all occurrences
 * - Search with early termination
 */

echo "=== Basic Linear Search Implementations ===\n\n";

// ============================================================================
// 1. Basic Linear Search (foreach)
// ============================================================================

echo "1. Basic Linear Search (foreach)\n";
echo str_repeat("-", 50) . "\n";

function linearSearch(array $arr, mixed $target): int|false
{
    foreach ($arr as $index => $value) {
        if ($value === $target) {
            return $index;
        }
    }
    return false;
}

$numbers = [4, 2, 7, 1, 9, 5];
echo "Array: " . json_encode($numbers) . "\n";
echo "Search for 7: " . (linearSearch($numbers, 7) !== false ? "Found at index " . linearSearch($numbers, 7) : "Not found") . "\n";
echo "Search for 8: " . (linearSearch($numbers, 8) !== false ? "Found at index " . linearSearch($numbers, 8) : "Not found") . "\n\n";

// ============================================================================
// 2. Loop-based Linear Search
// ============================================================================

echo "2. Loop-based Linear Search\n";
echo str_repeat("-", 50) . "\n";

function linearSearchLoop(array $arr, mixed $target): int|false
{
    $n = count($arr);
    
    for ($i = 0; $i < $n; $i++) {
        if ($arr[$i] === $target) {
            return $i;
        }
    }
    
    return false;
}

$fruits = ['apple', 'banana', 'cherry', 'date', 'elderberry'];
echo "Array: " . json_encode($fruits) . "\n";
echo "Search for 'cherry': " . (linearSearchLoop($fruits, 'cherry') !== false ? "Found at index " . linearSearchLoop($fruits, 'cherry') : "Not found") . "\n";
echo "Search for 'grape': " . (linearSearchLoop($fruits, 'grape') !== false ? "Found at index " . linearSearchLoop($fruits, 'grape') : "Not found") . "\n\n";

// ============================================================================
// 3. Recursive Linear Search
// ============================================================================

echo "3. Recursive Linear Search\n";
echo str_repeat("-", 50) . "\n";

function linearSearchRecursive(array $arr, mixed $target, int $index = 0): int|false
{
    // Base case: reached end of array
    if ($index >= count($arr)) {
        return false;
    }
    
    // Found target
    if ($arr[$index] === $target) {
        return $index;
    }
    
    // Recursive case: search rest of array
    return linearSearchRecursive($arr, $target, $index + 1);
}

$numbers2 = [10, 20, 30, 40, 50];
echo "Array: " . json_encode($numbers2) . "\n";
echo "Search for 30: " . (linearSearchRecursive($numbers2, 30) !== false ? "Found at index " . linearSearchRecursive($numbers2, 30) : "Not found") . "\n";
echo "Search for 35: " . (linearSearchRecursive($numbers2, 35) !== false ? "Found at index " . linearSearchRecursive($numbers2, 35) : "Not found") . "\n\n";

// ============================================================================
// 4. Find All Occurrences
// ============================================================================

echo "4. Find All Occurrences\n";
echo str_repeat("-", 50) . "\n";

function findAllOccurrences(array $arr, mixed $target): array
{
    $indices = [];
    
    foreach ($arr as $index => $value) {
        if ($value === $target) {
            $indices[] = $index;
        }
    }
    
    return $indices;
}

$numbers3 = [1, 3, 5, 3, 7, 3, 9];
echo "Array: " . json_encode($numbers3) . "\n";
$found = findAllOccurrences($numbers3, 3);
echo "All occurrences of 3: " . json_encode($found) . " (found " . count($found) . " times)\n";

$words = ['hello', 'world', 'hello', 'PHP', 'hello'];
echo "Array: " . json_encode($words) . "\n";
$foundWords = findAllOccurrences($words, 'hello');
echo "All occurrences of 'hello': " . json_encode($foundWords) . " (found " . count($foundWords) . " times)\n\n";

// ============================================================================
// 5. Count Occurrences
// ============================================================================

echo "5. Count Occurrences\n";
echo str_repeat("-", 50) . "\n";

function countOccurrences(array $arr, mixed $target): int
{
    $count = 0;
    
    foreach ($arr as $value) {
        if ($value === $target) {
            $count++;
        }
    }
    
    return $count;
}

$data = [1, 2, 3, 2, 4, 2, 5, 2];
echo "Array: " . json_encode($data) . "\n";
echo "Count of 2: " . countOccurrences($data, 2) . "\n";
echo "Count of 5: " . countOccurrences($data, 5) . "\n";
echo "Count of 9: " . countOccurrences($data, 9) . "\n\n";

// ============================================================================
// 6. Search with Early Termination (First Match)
// ============================================================================

echo "6. Search with Early Termination\n";
echo str_repeat("-", 50) . "\n";

function findFirstMatch(array $arr, callable $condition): mixed
{
    foreach ($arr as $value) {
        if ($condition($value)) {
            return $value; // Early termination
        }
    }
    return null;
}

$numbers4 = [1, 3, 5, 8, 10, 12, 15];
echo "Array: " . json_encode($numbers4) . "\n";

$firstEven = findFirstMatch($numbers4, fn($x) => $x % 2 === 0);
echo "First even number: " . ($firstEven ?? 'None') . "\n";

$firstGreaterThan10 = findFirstMatch($numbers4, fn($x) => $x > 10);
echo "First number > 10: " . ($firstGreaterThan10 ?? 'None') . "\n\n";

// ============================================================================
// 7. Complexity Analysis Demo
// ============================================================================

echo "7. Complexity Analysis\n";
echo str_repeat("-", 50) . "\n";

function linearSearchWithStats(array $arr, mixed $target): array
{
    $comparisons = 0;
    
    foreach ($arr as $index => $value) {
        $comparisons++;
        if ($value === $target) {
            return ['found' => true, 'index' => $index, 'comparisons' => $comparisons];
        }
    }
    
    return ['found' => false, 'index' => -1, 'comparisons' => $comparisons];
}

$testArray = range(1, 10);
echo "Array size: " . count($testArray) . "\n\n";

// Best case: element at beginning
$result = linearSearchWithStats($testArray, 1);
echo "Best case (search for 1): {$result['comparisons']} comparisons\n";

// Average case: element in middle
$result = linearSearchWithStats($testArray, 5);
echo "Average case (search for 5): {$result['comparisons']} comparisons\n";

// Worst case: element at end
$result = linearSearchWithStats($testArray, 10);
echo "Worst case (search for 10): {$result['comparisons']} comparisons\n";

// Element not found
$result = linearSearchWithStats($testArray, 99);
echo "Not found (search for 99): {$result['comparisons']} comparisons\n\n";

// ============================================================================
// 8. Linked List Search
// ============================================================================

echo "8. Linked List Search\n";
echo str_repeat("-", 50) . "\n";

class Node
{
    public function __construct(
        public mixed $data,
        public ?Node $next = null
    ) {}
}

function searchLinkedList(?Node $head, mixed $target): ?Node
{
    $current = $head;
    
    while ($current !== null) {
        if ($current->data === $target) {
            return $current;
        }
        $current = $current->next;
    }
    
    return null;
}

// Create linked list: 1 -> 2 -> 3 -> 4 -> 5
$head = new Node(1);
$head->next = new Node(2);
$head->next->next = new Node(3);
$head->next->next->next = new Node(4);
$head->next->next->next->next = new Node(5);

echo "Linked list: 1 -> 2 -> 3 -> 4 -> 5\n";
$foundNode = searchLinkedList($head, 3);
echo "Search for 3: " . ($foundNode !== null ? "Found (data: {$foundNode->data})" : "Not found") . "\n";

$foundNode = searchLinkedList($head, 10);
echo "Search for 10: " . ($foundNode !== null ? "Found (data: {$foundNode->data})" : "Not found") . "\n\n";

// ============================================================================
// 9. Performance Test
// ============================================================================

echo "9. Performance Test\n";
echo str_repeat("-", 50) . "\n";

$sizes = [100, 1000, 10000];

foreach ($sizes as $size) {
    $testData = range(1, $size);
    $target = $size; // Worst case: search for last element
    
    $start = microtime(true);
    linearSearch($testData, $target);
    $time = (microtime(true) - $start) * 1000;
    
    echo sprintf("Array size %d: %.4f ms\n", $size, $time);
}

echo "\nAs array size increases 10x, time increases ~10x (O(n))\n\n";

// ============================================================================
// 10. Comparison with PHP Built-in Functions
// ============================================================================

echo "10. Comparison with PHP Built-in Functions\n";
echo str_repeat("-", 50) . "\n";

$testArray2 = ['apple', 'banana', 'cherry', 'date'];
echo "Array: " . json_encode($testArray2) . "\n\n";

// Our implementation
$result = linearSearch($testArray2, 'cherry');
echo "linearSearch('cherry'): " . ($result !== false ? "index $result" : "not found") . "\n";

// PHP's array_search (similar to linear search)
$result = array_search('cherry', $testArray2);
echo "array_search('cherry'): " . ($result !== false ? "index $result" : "not found") . "\n";

// PHP's in_array (returns bool)
$result = in_array('cherry', $testArray2);
echo "in_array('cherry'): " . ($result ? "true" : "false") . "\n\n";

echo "Note: PHP's array_search() and in_array() use linear search internally!\n";

// ============================================================================
// Summary
// ============================================================================

echo "\n" . str_repeat("=", 50) . "\n";
echo "Summary:\n";
echo "- Linear search: O(n) time complexity\n";
echo "- Best case: O(1) (first element)\n";
echo "- Worst case: O(n) (last element or not found)\n";
echo "- Works on unsorted arrays\n";
echo "- No preprocessing required\n";
echo "- Simple to implement and understand\n";
echo "- Useful for small arrays and single searches\n";
echo str_repeat("=", 50) . "\n";


