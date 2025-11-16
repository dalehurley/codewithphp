<?php

declare(strict_types=1);

/**
 * Chapter 11: Linear Search & Variants
 * Part 2: Search Variants and Optimizations
 * 
 * Demonstrates advanced search variants:
 * - Sentinel search (eliminates boundary checks)
 * - Bidirectional search (searches from both ends)
 * - Jump search (O(√n) for sorted arrays)
 * - Interpolation search (O(log log n) for uniform data)
 * - Move-to-front optimization
 */

echo "=== Search Variants and Optimizations ===\n\n";

// ============================================================================
// 1. Sentinel Search (Eliminates Boundary Check)
// ============================================================================

echo "1. Sentinel Search\n";
echo str_repeat("-", 50) . "\n";

function sentinelSearch(array $arr, mixed $target): int|false
{
    $n = count($arr);
    if ($n === 0) return false;
    
    // Store last element and replace with target
    $last = $arr[$n - 1];
    $arr[$n - 1] = $target;
    
    $i = 0;
    // No boundary check needed - guaranteed to find target
    while ($arr[$i] !== $target) {
        $i++;
    }
    
    // Restore last element
    $arr[$n - 1] = $last;
    
    // Check if we found target before sentinel
    if ($i < $n - 1 || $arr[$n - 1] === $target) {
        return $i;
    }
    
    return false;
}

$numbers = [4, 2, 7, 1, 9, 5];
echo "Array: " . json_encode($numbers) . "\n";
echo "Sentinel search for 7: " . (sentinelSearch($numbers, 7) !== false ? "Found at index " . sentinelSearch($numbers, 7) : "Not found") . "\n";
echo "Sentinel search for 8: " . (sentinelSearch($numbers, 8) !== false ? "Found at index " . sentinelSearch($numbers, 8) : "Not found") . "\n";
echo "\nBenefit: Eliminates boundary check in loop (faster for large arrays)\n\n";

// ============================================================================
// 2. Bidirectional Search (Search from Both Ends)
// ============================================================================

echo "2. Bidirectional Search\n";
echo str_repeat("-", 50) . "\n";

function bidirectionalSearch(array $arr, mixed $target): int|false
{
    $left = 0;
    $right = count($arr) - 1;
    
    while ($left <= $right) {
        // Check left end
        if ($arr[$left] === $target) {
            return $left;
        }
        
        // Check right end
        if ($arr[$right] === $target) {
            return $right;
        }
        
        $left++;
        $right--;
    }
    
    return false;
}

$numbers2 = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
echo "Array: " . json_encode($numbers2) . "\n";
echo "Bidirectional search for 8: " . (bidirectionalSearch($numbers2, 8) !== false ? "Found at index " . bidirectionalSearch($numbers2, 8) : "Not found") . "\n";
echo "Bidirectional search for 11: " . (bidirectionalSearch($numbers2, 11) !== false ? "Found at index " . bidirectionalSearch($numbers2, 11) : "Not found") . "\n";
echo "\nBenefit: Potentially ~2x faster (checks both ends simultaneously)\n\n";

// ============================================================================
// 3. Jump Search (O(√n) for Sorted Arrays)
// ============================================================================

echo "3. Jump Search\n";
echo str_repeat("-", 50) . "\n";

function jumpSearch(array $arr, mixed $target): int|false
{
    $n = count($arr);
    $jump = (int) sqrt($n);
    $prev = 0;
    
    // Find the block where element is present
    while ($arr[min($jump, $n) - 1] < $target) {
        $prev = $jump;
        $jump += (int) sqrt($n);
        
        // If we've gone past the array
        if ($prev >= $n) {
            return false;
        }
    }
    
    // Linear search in the identified block
    while ($arr[$prev] < $target) {
        $prev++;
        
        // If we reached next block or end
        if ($prev === min($jump, $n)) {
            return false;
        }
    }
    
    // Check if element found
    if ($arr[$prev] === $target) {
        return $prev;
    }
    
    return false;
}

$sortedNumbers = [1, 3, 5, 7, 9, 11, 13, 15, 17, 19, 21, 23, 25];
echo "Array (sorted): " . json_encode($sortedNumbers) . "\n";
echo "Jump search for 13: " . (jumpSearch($sortedNumbers, 13) !== false ? "Found at index " . jumpSearch($sortedNumbers, 13) : "Not found") . "\n";
echo "Jump search for 14: " . (jumpSearch($sortedNumbers, 14) !== false ? "Found at index " . jumpSearch($sortedNumbers, 14) : "Not found") . "\n";
echo "\nComplexity: O(√n) - jumps by √n blocks\n";
echo "Best for: Sorted arrays with expensive comparisons\n\n";

// ============================================================================
// 4. Interpolation Search (O(log log n) for Uniform Data)
// ============================================================================

echo "4. Interpolation Search\n";
echo str_repeat("-", 50) . "\n";

function interpolationSearch(array $arr, int $target): int|false
{
    $low = 0;
    $high = count($arr) - 1;
    
    while ($low <= $high && $target >= $arr[$low] && $target <= $arr[$high]) {
        // Avoid division by zero
        if ($arr[$high] === $arr[$low]) {
            return $arr[$low] === $target ? $low : false;
        }
        
        // Calculate position using interpolation formula
        $pos = $low + (int) (
            (($high - $low) / ($arr[$high] - $arr[$low])) * 
            ($target - $arr[$low])
        );
        
        // Target found
        if ($arr[$pos] === $target) {
            return $pos;
        }
        
        // Target is in upper part
        if ($arr[$pos] < $target) {
            $low = $pos + 1;
        } 
        // Target is in lower part
        else {
            $high = $pos - 1;
        }
    }
    
    return false;
}

$uniformData = [10, 20, 30, 40, 50, 60, 70, 80, 90, 100];
echo "Array (uniformly distributed): " . json_encode($uniformData) . "\n";
echo "Interpolation search for 70: " . (interpolationSearch($uniformData, 70) !== false ? "Found at index " . interpolationSearch($uniformData, 70) : "Not found") . "\n";
echo "Interpolation search for 75: " . (interpolationSearch($uniformData, 75) !== false ? "Found at index " . interpolationSearch($uniformData, 75) : "Not found") . "\n";
echo "\nComplexity: O(log log n) for uniform data\n";
echo "Best for: Sorted, uniformly distributed numeric data\n\n";

// ============================================================================
// 5. Move-to-Front Optimization
// ============================================================================

echo "5. Move-to-Front Optimization\n";
echo str_repeat("-", 50) . "\n";

class MoveToFrontSearch
{
    private array $data;
    
    public function __construct(array $data)
    {
        $this->data = $data;
    }
    
    public function search(mixed $target): int|false
    {
        foreach ($this->data as $index => $value) {
            if ($value === $target) {
                // Move found element to front
                if ($index > 0) {
                    array_splice($this->data, $index, 1);
                    array_unshift($this->data, $value);
                }
                return 0; // Always returns 0 after move
            }
        }
        return false;
    }
    
    public function getData(): array
    {
        return $this->data;
    }
}

$mtf = new MoveToFrontSearch(['apple', 'banana', 'cherry', 'date', 'elderberry']);
echo "Initial array: " . json_encode($mtf->getData()) . "\n";

$mtf->search('cherry');
echo "After searching 'cherry': " . json_encode($mtf->getData()) . "\n";

$mtf->search('date');
echo "After searching 'date': " . json_encode($mtf->getData()) . "\n";

echo "\nBenefit: Frequently accessed items move to front (better for repeated searches)\n\n";

// ============================================================================
// 6. Transpose Search (Swap with Previous)
// ============================================================================

echo "6. Transpose Search\n";
echo str_repeat("-", 50) . "\n";

class TransposeSearch
{
    private array $data;
    
    public function __construct(array $data)
    {
        $this->data = array_values($data); // Reset keys
    }
    
    public function search(mixed $target): int|false
    {
        foreach ($this->data as $index => $value) {
            if ($value === $target) {
                // Swap with previous element (if not first)
                if ($index > 0) {
                    $temp = $this->data[$index - 1];
                    $this->data[$index - 1] = $this->data[$index];
                    $this->data[$index] = $temp;
                    return $index - 1;
                }
                return $index;
            }
        }
        return false;
    }
    
    public function getData(): array
    {
        return $this->data;
    }
}

$ts = new TransposeSearch(['A', 'B', 'C', 'D', 'E']);
echo "Initial array: " . json_encode($ts->getData()) . "\n";

$ts->search('D');
echo "After searching 'D': " . json_encode($ts->getData()) . " (swapped with 'C')\n";

$ts->search('D');
echo "After searching 'D' again: " . json_encode($ts->getData()) . " (swapped with 'B')\n";

echo "\nBenefit: Gradually moves frequently accessed items toward front\n\n";

// ============================================================================
// 7. Performance Comparison
// ============================================================================

echo "7. Performance Comparison\n";
echo str_repeat("-", 50) . "\n";

$testSize = 1000;
$sortedData = range(1, $testSize);
$target = 750;

// Linear search
$start = microtime(true);
$found = array_search($target, $sortedData);
$linearTime = (microtime(true) - $start) * 1000;

// Jump search
$start = microtime(true);
$found = jumpSearch($sortedData, $target);
$jumpTime = (microtime(true) - $start) * 1000;

// Interpolation search
$start = microtime(true);
$found = interpolationSearch($sortedData, $target);
$interpolationTime = (microtime(true) - $start) * 1000;

echo "Searching for {$target} in array of size {$testSize}:\n";
echo sprintf("Linear search:        %.4f ms (O(n))\n", $linearTime);
echo sprintf("Jump search:          %.4f ms (O(√n))\n", $jumpTime);
echo sprintf("Interpolation search: %.4f ms (O(log log n))\n", $interpolationTime);
echo "\nNote: Results vary based on data distribution and target position\n\n";

// ============================================================================
// 8. When to Use Each Variant
// ============================================================================

echo "8. When to Use Each Variant\n";
echo str_repeat("-", 50) . "\n";

echo "Linear Search:\n";
echo "  ✓ Unsorted data\n";
echo "  ✓ Small arrays (< 100 elements)\n";
echo "  ✓ Single search operation\n";
echo "  ✓ Linked lists\n\n";

echo "Sentinel Search:\n";
echo "  ✓ Large arrays with many searches\n";
echo "  ✓ When eliminating boundary check matters\n";
echo "  ✓ Unsorted data\n\n";

echo "Bidirectional Search:\n";
echo "  ✓ When target might be near either end\n";
echo "  ✓ Unsorted data\n";
echo "  ✓ Want ~2x speedup\n\n";

echo "Jump Search:\n";
echo "  ✓ Sorted data\n";
echo "  ✓ Expensive comparisons\n";
echo "  ✓ O(√n) better than O(n)\n\n";

echo "Interpolation Search:\n";
echo "  ✓ Sorted, uniformly distributed data\n";
echo "  ✓ Numeric data\n";
echo "  ✓ O(log log n) extremely fast\n\n";

echo "Move-to-Front:\n";
echo "  ✓ Repeated searches on same data\n";
echo "  ✓ Some items accessed more frequently\n";
echo "  ✓ Self-organizing list\n\n";

// ============================================================================
// Summary
// ============================================================================

echo str_repeat("=", 50) . "\n";
echo "Summary:\n";
echo "- Sentinel: Eliminates boundary check\n";
echo "- Bidirectional: Checks both ends\n";
echo "- Jump: O(√n) for sorted arrays\n";
echo "- Interpolation: O(log log n) for uniform data\n";
echo "- Move-to-front: Optimizes repeated searches\n";
echo "- Choose based on data characteristics\n";
echo str_repeat("=", 50) . "\n";


