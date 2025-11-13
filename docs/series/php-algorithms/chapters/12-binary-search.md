---
title: "12: Binary Search"
description: "Master the efficient divide-and-conquer search algorithm. Implement iterative and recursive versions."
series: "php-algorithms"
chapter: 12
order: 12
difficulty: "Intermediate"
prerequisites:
  - "Understanding of Big O notation"
  - "Familiarity with recursion"
  - "Understanding of sorted arrays"
---

# Binary Search

Binary search is one of the most important algorithms every developer should know. It's a fast, elegant algorithm that searches sorted data by repeatedly dividing the search space in half. In this chapter, we'll master binary search and its many variations.

## The Problem with Linear Search

First, let's see why we need binary search:

```php
// Linear search: O(n)
function linearSearch(array $arr, int $target): int|false
{
    foreach ($arr as $index => $value) {
        if ($value === $target) {
            return $index;
        }
    }
    return false;
}

// For 1,000,000 elements, might check 500,000 on average!
```

Linear search is simple but slow for large datasets. Binary search solves this.

## How Binary Search Works

**Key insight:** If the array is sorted, we can eliminate half the elements with each comparison!

**Algorithm:**
1. Start with the entire array
2. Check the middle element
3. If it's the target, done!
4. If target is smaller, search left half
5. If target is larger, search right half
6. Repeat until found or no elements left

**Example:** Find `37` in `[1, 3, 5, 7, 11, 13, 17, 19, 23, 29, 31, 37, 41, 43, 47]`

```
[1, 3, 5, 7, 11, 13, 17, 19, 23, 29, 31, 37, 41, 43, 47]
                      ↑
                     mid=19, target=37, go right

                        [23, 29, 31, 37, 41, 43, 47]
                              ↑
                            mid=37, FOUND!
```

Only 2 comparisons instead of potentially 15!

## Iterative Implementation

```php
function binarySearch(array $arr, int $target): int|false
{
    $left = 0;
    $right = count($arr) - 1;

    while ($left <= $right) {
        // Calculate middle index
        $mid = (int)(($left + $right) / 2);

        if ($arr[$mid] === $target) {
            return $mid; // Found!
        } elseif ($arr[$mid] < $target) {
            $left = $mid + 1; // Search right half
        } else {
            $right = $mid - 1; // Search left half
        }
    }

    return false; // Not found
}

$numbers = [1, 3, 5, 7, 9, 11, 13, 15, 17, 19];
echo binarySearch($numbers, 13); // Output: 6
echo binarySearch($numbers, 8);  // Output: false
```

### Why Use (int)(($left + $right) / 2)?

```php
// Potential integer overflow for very large arrays
$mid = ($left + $right) / 2; // Could overflow if left + right > PHP_INT_MAX

// Better: avoid overflow
$mid = $left + (int)(($right - $left) / 2);

// Or in PHP (which handles big integers):
$mid = (int)(($left + $right) / 2); // Usually fine
```

## Recursive Implementation

```php
function binarySearchRecursive(
    array $arr,
    int $target,
    int $left = 0,
    int $right = null
): int|false {
    if ($right === null) {
        $right = count($arr) - 1;
    }

    // Base case: search space exhausted
    if ($left > $right) {
        return false;
    }

    $mid = $left + (int)(($right - $left) / 2);

    if ($arr[$mid] === $target) {
        return $mid;
    } elseif ($arr[$mid] < $target) {
        // Recursive case: search right half
        return binarySearchRecursive($arr, $target, $mid + 1, $right);
    } else {
        // Recursive case: search left half
        return binarySearchRecursive($arr, $target, $left, $mid - 1);
    }
}
```

**Note:** Iterative is generally preferred in PHP due to:
- No recursion overhead
- No stack space concerns
- Slightly faster

## Complexity Analysis

- **Time Complexity:** O(log n)
  - Each iteration halves the search space
  - log₂(1,000,000) ≈ 20 comparisons
  - Compare to linear search's 500,000!

- **Space Complexity:**
  - Iterative: O(1) - no extra space
  - Recursive: O(log n) - call stack depth

**Why log n?**
```
n elements → n/2 → n/4 → n/8 → ... → 1
How many halvings? log₂(n)
```

## Visualizing Binary Search

```php
function binarySearchVisualized(array $arr, int $target): int|false
{
    $left = 0;
    $right = count($arr) - 1;
    $step = 1;

    while ($left <= $right) {
        $mid = (int)(($left + $right) / 2);

        // Visual representation
        echo "Step $step:\n";
        echo "  Search space: [" . implode(', ', array_slice($arr, $left, $right - $left + 1)) . "]\n";
        echo "  Checking index $mid: {$arr[$mid]}\n";

        if ($arr[$mid] === $target) {
            echo "  ✓ Found at index $mid!\n";
            return $mid;
        } elseif ($arr[$mid] < $target) {
            echo "  → Target is greater, search right half\n\n";
            $left = $mid + 1;
        } else {
            echo "  ← Target is smaller, search left half\n\n";
            $right = $mid - 1;
        }

        $step++;
    }

    echo "Not found\n";
    return false;
}

$numbers = [1, 3, 5, 7, 9, 11, 13, 15, 17, 19];
binarySearchVisualized($numbers, 13);
```

**Output:**
```
Step 1:
  Search space: [1, 3, 5, 7, 9, 11, 13, 15, 17, 19]
  Checking index 4: 9
  → Target is greater, search right half

Step 2:
  Search space: [11, 13, 15, 17, 19]
  Checking index 7: 15
  ← Target is smaller, search left half

Step 3:
  Search space: [11, 13]
  Checking index 6: 13
  ✓ Found at index 6!
```

## Binary Search Variants

### 1. Find First Occurrence

```php
function findFirst(array $arr, int $target): int|false
{
    $left = 0;
    $right = count($arr) - 1;
    $result = false;

    while ($left <= $right) {
        $mid = (int)(($left + $right) / 2);

        if ($arr[$mid] === $target) {
            $result = $mid;
            $right = $mid - 1; // Continue searching left for first occurrence
        } elseif ($arr[$mid] < $target) {
            $left = $mid + 1;
        } else {
            $right = $mid - 1;
        }
    }

    return $result;
}

$numbers = [1, 2, 2, 2, 3, 4, 5];
echo findFirst($numbers, 2); // Output: 1 (first occurrence)
```

### 2. Find Last Occurrence

```php
function findLast(array $arr, int $target): int|false
{
    $left = 0;
    $right = count($arr) - 1;
    $result = false;

    while ($left <= $right) {
        $mid = (int)(($left + $right) / 2);

        if ($arr[$mid] === $target) {
            $result = $mid;
            $left = $mid + 1; // Continue searching right for last occurrence
        } elseif ($arr[$mid] < $target) {
            $left = $mid + 1;
        } else {
            $right = $mid - 1;
        }
    }

    return $result;
}

$numbers = [1, 2, 2, 2, 3, 4, 5];
echo findLast($numbers, 2); // Output: 3 (last occurrence)
```

### 3. Find Insertion Point

Find where to insert a value to maintain sorted order:

```php
function findInsertPosition(array $arr, int $target): int
{
    $left = 0;
    $right = count($arr) - 1;

    while ($left <= $right) {
        $mid = (int)(($left + $right) / 2);

        if ($arr[$mid] < $target) {
            $left = $mid + 1;
        } else {
            $right = $mid - 1;
        }
    }

    return $left; // Insertion position
}

$numbers = [1, 3, 5, 7, 9];
echo findInsertPosition($numbers, 6); // Output: 3
// Insert 6 at index 3: [1, 3, 5, 6, 7, 9]
```

### 4. Count Occurrences

```php
function countOccurrences(array $arr, int $target): int
{
    $first = findFirst($arr, $target);

    if ($first === false) {
        return 0;
    }

    $last = findLast($arr, $target);
    return $last - $first + 1;
}

$numbers = [1, 2, 2, 2, 3, 4, 5];
echo countOccurrences($numbers, 2); // Output: 3
```

## Binary Search on Answer Space

Sometimes we binary search on possible answers rather than array indices:

### Square Root (Integer)

```php
function sqrtInteger(int $x): int
{
    if ($x < 2) return $x;

    $left = 1;
    $right = $x;

    while ($left <= $right) {
        $mid = $left + (int)(($right - $left) / 2);
        $square = $mid * $mid;

        if ($square === $x) {
            return $mid;
        } elseif ($square < $x) {
            $left = $mid + 1;
        } else {
            $right = $mid - 1;
        }
    }

    return $right; // Return floor(sqrt(x))
}

echo sqrtInteger(16); // 4
echo sqrtInteger(20); // 4 (floor of 4.47)
```

### Find Peak Element

```php
function findPeakElement(array $nums): int
{
    $left = 0;
    $right = count($nums) - 1;

    while ($left < $right) {
        $mid = (int)(($left + $right) / 2);

        if ($nums[$mid] > $nums[$mid + 1]) {
            // Peak is on left side (including mid)
            $right = $mid;
        } else {
            // Peak is on right side
            $left = $mid + 1;
        }
    }

    return $left;
}

// Array with peak: [1, 2, 3, 1]
// Peak is at index 2 (value 3)
```

## Common Mistakes & Edge Cases

### Mistake 1: Infinite Loop

```php
// Wrong: infinite loop when target not found
while ($left < $right) { // Should be $left <= $right
    $mid = (int)(($left + $right) / 2);
    if ($arr[$mid] === $target) return $mid;
    elseif ($arr[$mid] < $target) $left = $mid; // Should be $mid + 1
    else $right = $mid; // Should be $mid - 1
}
```

### Mistake 2: Off-by-One Errors

```php
// Test edge cases:
$arr = [1];
binarySearch($arr, 1);  // Should find at index 0
binarySearch($arr, 2);  // Should return false

$arr = [1, 2];
binarySearch($arr, 1);  // Should find at index 0
binarySearch($arr, 2);  // Should find at index 1
```

### Mistake 3: Unsorted Array

```php
// Binary search ONLY works on sorted arrays!
$unsorted = [5, 2, 8, 1, 9];
binarySearch($unsorted, 8); // Wrong result! Must sort first!

sort($unsorted);
binarySearch($unsorted, 8); // Now correct
```

## Practical Applications

### 1. Autocomplete Search

```php
function autocomplete(array $sortedWords, string $prefix): array
{
    // Find first word with prefix
    $left = 0;
    $right = count($sortedWords) - 1;
    $start = -1;

    while ($left <= $right) {
        $mid = (int)(($left + $right) / 2);

        if (str_starts_with($sortedWords[$mid], $prefix)) {
            $start = $mid;
            $right = $mid - 1; // Find earliest match
        } elseif ($sortedWords[$mid] < $prefix) {
            $left = $mid + 1;
        } else {
            $right = $mid - 1;
        }
    }

    if ($start === -1) return [];

    // Collect all words with prefix
    $results = [];
    for ($i = $start; $i < count($sortedWords); $i++) {
        if (str_starts_with($sortedWords[$i], $prefix)) {
            $results[] = $sortedWords[$i];
        } else {
            break;
        }
    }

    return $results;
}

$words = ['apple', 'application', 'apply', 'banana', 'band'];
print_r(autocomplete($words, 'app'));
// Output: ['apple', 'application', 'apply']
```

### 2. Date Range Search

```php
class Event
{
    public function __construct(
        public string $name,
        public int $timestamp
    ) {}
}

function findEventsInRange(array $events, int $start, int $end): array
{
    // Find first event >= start
    $left = 0;
    $right = count($events) - 1;
    $startIdx = count($events);

    while ($left <= $right) {
        $mid = (int)(($left + $right) / 2);
        if ($events[$mid]->timestamp >= $start) {
            $startIdx = $mid;
            $right = $mid - 1;
        } else {
            $left = $mid + 1;
        }
    }

    // Collect events until end
    $result = [];
    for ($i = $startIdx; $i < count($events) && $events[$i]->timestamp <= $end; $i++) {
        $result[] = $events[$i];
    }

    return $result;
}
```

## Benchmarking Binary vs Linear Search

```php
require_once 'Benchmark.php';

$bench = new Benchmark();
$sizes = [1000, 10000, 100000, 1000000];

foreach ($sizes as $size) {
    $data = range(1, $size);
    $target = $size - 100; // Near the end

    echo "Array size: $size\n";
    $bench->compare([
        'Linear Search' => fn($arr) => linearSearch($arr, $target),
        'Binary Search' => fn($arr) => binarySearch($arr, $target),
    ], $data, iterations: 100);
    echo "\n";
}
```

## Detailed Performance Comparisons

Comprehensive benchmarking showing binary search advantages:

```php
class BinarySearchBenchmark
{
    public function comprehensiveBenchmark(): void
    {
        $sizes = [100, 1000, 10000, 100000, 1000000];

        echo "=== Binary Search vs Linear Search Performance ===\n\n";

        foreach ($sizes as $size) {
            $data = range(1, $size);
            $iterations = 10000;

            echo "Array Size: " . number_format($size) . "\n";
            echo str_repeat('-', 60) . "\n";

            // Test different target positions
            $positions = [
                'Beginning' => 1,
                'Middle' => (int)($size / 2),
                'End' => $size,
                'Not Found' => $size + 1
            ];

            foreach ($positions as $label => $target) {
                // Linear Search
                $start = microtime(true);
                for ($i = 0; $i < $iterations; $i++) {
                    linearSearch($data, $target);
                }
                $linearTime = microtime(true) - $start;

                // Binary Search
                $start = microtime(true);
                for ($i = 0; $i < $iterations; $i++) {
                    binarySearch($data, $target);
                }
                $binaryTime = microtime(true) - $start;

                // Calculate speedup
                $speedup = $linearTime / $binaryTime;

                printf("  %s:\n", $label);
                printf("    Linear: %.6f sec\n", $linearTime);
                printf("    Binary: %.6f sec\n", $binaryTime);
                printf("    Speedup: %.2fx faster\n\n", $speedup);
            }

            echo "\n";
        }
    }

    public function memoryComparison(): void
    {
        $size = 1000000;
        $data = range(1, $size);

        echo "=== Memory Usage Comparison ===\n\n";

        // Iterative Binary Search
        $memBefore = memory_get_usage();
        binarySearch($data, 500000);
        $memAfter = memory_get_usage();
        $iterativeMemory = $memAfter - $memBefore;

        // Recursive Binary Search
        $memBefore = memory_get_usage();
        binarySearchRecursive($data, 500000);
        $memAfter = memory_get_usage();
        $recursiveMemory = $memAfter - $memBefore;

        printf("Iterative Binary Search: %d bytes\n", $iterativeMemory);
        printf("Recursive Binary Search: %d bytes\n", $recursiveMemory);
        printf("Difference: %d bytes\n", abs($recursiveMemory - $iterativeMemory));
    }
}

$benchmark = new BinarySearchBenchmark();
$benchmark->comprehensiveBenchmark();
$benchmark->memoryComparison();
```

**Expected Output:**
```
=== Binary Search vs Linear Search Performance ===

Array Size: 1,000
------------------------------------------------------------
  Beginning:
    Linear: 0.001234 sec
    Binary: 0.002456 sec
    Speedup: 0.50x faster (Linear wins for small n)

  End:
    Linear: 0.125000 sec
    Binary: 0.002500 sec
    Speedup: 50.00x faster

Array Size: 1,000,000
------------------------------------------------------------
  End:
    Linear: 125.500000 sec
    Binary: 0.003500 sec
    Speedup: 35,857.14x faster!
```

**Performance Insights:**
- Binary search excels with large datasets
- For small arrays (< 100), linear search may be faster due to overhead
- Binary search performance independent of target position
- Iterative uses less memory than recursive

## Security Considerations

### Timing Attack Vulnerabilities in Binary Search

Binary search can leak information through timing, especially in security-sensitive contexts:

```php
class SecureBinarySearch
{
    /**
     * VULNERABLE: Standard binary search leaks information
     * Different paths take different times
     */
    public function insecureBinarySearch(array $secrets, string $target): bool
    {
        $left = 0;
        $right = count($secrets) - 1;

        while ($left <= $right) {
            $mid = (int)(($left + $right) / 2);

            if ($secrets[$mid] === $target) {
                return true; // Early return - timing leak!
            } elseif ($secrets[$mid] < $target) {
                $left = $mid + 1;
            } else {
                $right = $mid - 1;
            }
        }

        return false;
    }

    /**
     * SECURE: Constant-time binary search
     * Always performs same number of comparisons
     */
    public function constantTimeBinarySearch(array $secrets, string $target): bool
    {
        $left = 0;
        $right = count($secrets) - 1;
        $found = false;

        // Always perform log(n) iterations
        $maxIterations = (int)ceil(log(count($secrets), 2));

        for ($i = 0; $i < $maxIterations; $i++) {
            if ($left <= $right) {
                $mid = (int)(($left + $right) / 2);

                // Use constant-time comparison
                $comparison = strcmp($secrets[$mid], $target);

                // Update found flag without early return
                if ($comparison === 0) {
                    $found = true;
                }

                // Always update bounds (even if found)
                if ($comparison < 0) {
                    $left = $mid + 1;
                } else {
                    $right = $mid - 1;
                }
            }
        }

        return $found;
    }

    /**
     * SECURE: Using hash_equals for passwords/tokens
     */
    public function secureTokenSearch(array $validTokens, string $userToken): bool
    {
        sort($validTokens); // Ensure sorted
        $left = 0;
        $right = count($validTokens) - 1;
        $found = 0;

        $maxIterations = (int)ceil(log(count($validTokens) + 1, 2));

        for ($i = 0; $i < $maxIterations; $i++) {
            if ($left <= $right) {
                $mid = (int)(($left + $right) / 2);

                // Constant-time comparison
                if (hash_equals($validTokens[$mid], $userToken)) {
                    $found = 1;
                }

                // Use string comparison for bounds
                if (strcmp($validTokens[$mid], $userToken) < 0) {
                    $left = $mid + 1;
                } else {
                    $right = $mid - 1;
                }
            }
        }

        // Add random delay to further obscure timing
        usleep(rand(50, 150));

        return $found === 1;
    }
}

// Example: API key validation
$validKeys = [
    'key_1a2b3c4d',
    'key_2e3f4g5h',
    'key_3i4j5k6l',
    'key_4m5n6o7p'
];
sort($validKeys);

$search = new SecureBinarySearch();
$userKey = $_POST['api_key'] ?? '';

// VULNERABLE
if ($search->insecureBinarySearch($validKeys, $userKey)) {
    echo "Valid key";
}

// SECURE
if ($search->secureTokenSearch($validKeys, $userKey)) {
    echo "Valid key";
}
```

### Protection Strategies

```php
class TimingAttackProtection
{
    /**
     * Add artificial delay to mask timing differences
     */
    public function searchWithJitter(array $data, mixed $target): bool
    {
        $result = binarySearch($data, $target);

        // Random delay: 10-50 microseconds
        usleep(rand(10, 50));

        return $result !== false;
    }

    /**
     * Rate limiting per IP/user
     */
    private array $searchCounts = [];

    public function searchWithRateLimit(
        string $clientId,
        array $data,
        mixed $target,
        int $maxSearches = 100,
        int $windowSeconds = 60
    ): mixed {
        $now = time();

        // Initialize or reset counter
        if (!isset($this->searchCounts[$clientId])) {
            $this->searchCounts[$clientId] = ['count' => 0, 'window_start' => $now];
        }

        $clientData = &$this->searchCounts[$clientId];

        // Reset if window expired
        if ($now - $clientData['window_start'] > $windowSeconds) {
            $clientData = ['count' => 0, 'window_start' => $now];
        }

        // Check rate limit
        if ($clientData['count'] >= $maxSearches) {
            throw new Exception("Rate limit exceeded. Try again later.");
        }

        $clientData['count']++;

        // Perform search
        return binarySearch($data, $target);
    }

    /**
     * Audit logging for sensitive searches
     */
    public function auditedSearch(
        string $userId,
        array $sensitiveData,
        mixed $target,
        string $purpose
    ): mixed {
        $startTime = microtime(true);
        $result = binarySearch($sensitiveData, $target);
        $duration = microtime(true) - $startTime;

        // Log the search
        $this->logSearch([
            'user_id' => $userId,
            'timestamp' => date('Y-m-d H:i:s'),
            'purpose' => $purpose,
            'found' => $result !== false,
            'duration' => $duration,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);

        return $result;
    }

    private function logSearch(array $data): void
    {
        // Log to file, database, or monitoring service
        error_log(json_encode($data), 3, '/var/log/sensitive_searches.log');
    }
}
```

## Advanced Binary Search Optimizations

### Interpolation-Enhanced Binary Search

```php
class OptimizedBinarySearch
{
    /**
     * Hybrid search: interpolation + binary
     * Best for uniformly distributed data
     */
    public function interpolationBinarySearch(array $arr, int $target): int|false
    {
        $left = 0;
        $right = count($arr) - 1;

        while ($left <= $right && $target >= $arr[$left] && $target <= $arr[$right]) {
            if ($left === $right) {
                return $arr[$left] === $target ? $left : false;
            }

            // Interpolation formula
            $pos = $left + (int)((
                ($right - $left) / ($arr[$right] - $arr[$left])
            ) * ($target - $arr[$left]));

            // Bounds check
            $pos = max($left, min($pos, $right));

            if ($arr[$pos] === $target) {
                return $pos;
            }

            // Fall back to binary search logic
            if ($arr[$pos] < $target) {
                $left = $pos + 1;
            } else {
                $right = $pos - 1;
            }
        }

        return false;
    }

    /**
     * Exponential search: good when target is near beginning
     */
    public function exponentialSearch(array $arr, int $target): int|false
    {
        $n = count($arr);

        // If target is at first position
        if ($arr[0] === $target) {
            return 0;
        }

        // Find range for binary search
        $i = 1;
        while ($i < $n && $arr[$i] <= $target) {
            $i *= 2;
        }

        // Binary search in found range
        return $this->binarySearchRange(
            $arr,
            $target,
            (int)($i / 2),
            min($i, $n - 1)
        );
    }

    private function binarySearchRange(
        array $arr,
        int $target,
        int $left,
        int $right
    ): int|false {
        while ($left <= $right) {
            $mid = $left + (int)(($right - $left) / 2);

            if ($arr[$mid] === $target) {
                return $mid;
            } elseif ($arr[$mid] < $target) {
                $left = $mid + 1;
            } else {
                $right = $mid - 1;
            }
        }

        return false;
    }
}

// Performance comparison
$arr = range(1, 1000000);
$search = new OptimizedBinarySearch();

// Target near beginning
$target = 100;
$start = microtime(true);
$result = $search->exponentialSearch($arr, $target);
echo "Exponential: " . (microtime(true) - $start) . "s\n";

$start = microtime(true);
$result = binarySearch($arr, $target);
echo "Binary: " . (microtime(true) - $start) . "s\n";
```

## Framework Integration Examples

### Laravel Integration

```php
namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BinarySearchService
{
    /**
     * Search sorted database results
     */
    public function searchSortedModels(
        Collection $models,
        string $field,
        mixed $value
    ): ?object {
        // Ensure collection is sorted
        $sorted = $models->sortBy($field)->values();

        $left = 0;
        $right = $sorted->count() - 1;

        while ($left <= $right) {
            $mid = (int)(($left + $right) / 2);
            $model = $sorted[$mid];

            if ($model->$field === $value) {
                return $model;
            } elseif ($model->$field < $value) {
                $left = $mid + 1;
            } else {
                $right = $mid - 1;
            }
        }

        return null;
    }

    /**
     * Paginated search with caching
     */
    public function searchWithPagination(
        string $modelClass,
        string $sortField,
        mixed $searchValue
    ): ?object {
        $cacheKey = sprintf(
            'binary_search:%s:%s:%s',
            $modelClass,
            $sortField,
            md5((string)$searchValue)
        );

        return Cache::remember($cacheKey, 3600, function () use (
            $modelClass,
            $sortField,
            $searchValue
        ) {
            $models = $modelClass::orderBy($sortField)->get();
            return $this->searchSortedModels($models, $sortField, $searchValue);
        });
    }

    /**
     * Range search: find all items between two values
     */
    public function searchRange(
        Collection $sorted,
        string $field,
        mixed $min,
        mixed $max
    ): Collection {
        // Find first element >= min
        $startIdx = $this->findInsertPosition($sorted, $field, $min);

        // Find last element <= max
        $endIdx = $this->findInsertPosition($sorted, $field, $max + 1) - 1;

        if ($startIdx > $endIdx) {
            return collect();
        }

        return $sorted->slice($startIdx, $endIdx - $startIdx + 1);
    }

    private function findInsertPosition(
        Collection $sorted,
        string $field,
        mixed $value
    ): int {
        $left = 0;
        $right = $sorted->count() - 1;

        while ($left <= $right) {
            $mid = (int)(($left + $right) / 2);

            if ($sorted[$mid]->$field < $value) {
                $left = $mid + 1;
            } else {
                $right = $mid - 1;
            }
        }

        return $left;
    }
}

// Usage in Laravel Controller
namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\BinarySearchService;

class ProductController extends Controller
{
    public function search(Request $request, BinarySearchService $searchService)
    {
        // Search by price
        if ($request->has('price')) {
            $products = Product::orderBy('price')->get();
            $product = $searchService->searchSortedModels(
                $products,
                'price',
                $request->input('price')
            );

            return response()->json(['product' => $product]);
        }

        // Range search
        if ($request->has('min_price') && $request->has('max_price')) {
            $products = Product::orderBy('price')->get();
            $results = $searchService->searchRange(
                $products,
                'price',
                $request->input('min_price'),
                $request->input('max_price')
            );

            return response()->json(['products' => $results]);
        }
    }
}
```

### Symfony Integration

```php
namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Psr\Log\LoggerInterface;

class BinarySearchService
{
    private EntityManagerInterface $entityManager;
    private CacheInterface $cache;
    private LoggerInterface $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        CacheInterface $cache,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->cache = $cache;
        $this->logger = $logger;
    }

    /**
     * Search entities with binary search
     */
    public function searchEntity(
        string $entityClass,
        string $property,
        mixed $value
    ): ?object {
        $cacheKey = sprintf('entity_search_%s_%s_%s',
            $entityClass,
            $property,
            md5(serialize($value))
        );

        return $this->cache->get($cacheKey, function (ItemInterface $item) use (
            $entityClass,
            $property,
            $value
        ) {
            $item->expiresAfter(3600);

            // Get sorted entities
            $repository = $this->entityManager->getRepository($entityClass);
            $entities = $repository->findBy([], [$property => 'ASC']);

            return $this->binarySearchObjects($entities, $property, $value);
        });
    }

    private function binarySearchObjects(
        array $objects,
        string $property,
        mixed $value
    ): ?object {
        $left = 0;
        $right = count($objects) - 1;

        $getter = 'get' . ucfirst($property);

        while ($left <= $right) {
            $mid = (int)(($left + $right) / 2);
            $obj = $objects[$mid];

            if (!method_exists($obj, $getter)) {
                $this->logger->error("Getter {$getter} not found");
                return null;
            }

            $midValue = $obj->$getter();

            if ($midValue === $value) {
                return $obj;
            } elseif ($midValue < $value) {
                $left = $mid + 1;
            } else {
                $right = $mid - 1;
            }
        }

        return null;
    }

    /**
     * Version history search by timestamp
     */
    public function searchVersionByTimestamp(
        string $entityId,
        \DateTimeInterface $timestamp
    ): ?array {
        // Fetch all versions sorted by timestamp
        $versions = $this->entityManager
            ->createQuery('
                SELECT v FROM App\Entity\Version v
                WHERE v.entityId = :id
                ORDER BY v.createdAt ASC
            ')
            ->setParameter('id', $entityId)
            ->getResult();

        return $this->findVersionByTime($versions, $timestamp);
    }

    private function findVersionByTime(
        array $versions,
        \DateTimeInterface $target
    ): ?array {
        $left = 0;
        $right = count($versions) - 1;
        $result = null;

        while ($left <= $right) {
            $mid = (int)(($left + $right) / 2);
            $version = $versions[$mid];

            if ($version->getCreatedAt() <= $target) {
                $result = $version;
                $left = $mid + 1; // Look for later version
            } else {
                $right = $mid - 1;
            }
        }

        return $result ? [
            'version' => $result,
            'data' => $result->getData()
        ] : null;
    }
}

// Usage in Symfony Controller
namespace App\Controller;

use App\Service\BinarySearchService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    #[Route('/search/product/{price}', name: 'search_product')]
    public function searchByPrice(
        float $price,
        BinarySearchService $searchService
    ): Response {
        $product = $searchService->searchEntity(
            'App\\Entity\\Product',
            'price',
            $price
        );

        return $this->json(['product' => $product]);
    }

    #[Route('/history/{id}', name: 'version_history')]
    public function getVersionAtTime(
        string $id,
        Request $request,
        BinarySearchService $searchService
    ): Response {
        $timestamp = new \DateTime($request->query->get('timestamp'));

        $version = $searchService->searchVersionByTimestamp($id, $timestamp);

        return $this->json($version);
    }
}
```

## Practice Exercises

### Exercise 1: Rotated Sorted Array

Search in a sorted array that has been rotated:

```php
function searchRotated(array $nums, int $target): int|false
{
    // Your code here
}

$nums = [4, 5, 6, 7, 0, 1, 2]; // Rotated [0,1,2,3,4,5,6,7]
echo searchRotated($nums, 0); // Should output: 4
```

<details>
<summary>Hint</summary>
One half is always sorted. Check which half is sorted, then decide which side to search.
</details>

### Exercise 2: Find Minimum in Rotated Array

```php
function findMin(array $nums): int
{
    // Your code here
}

echo findMin([4, 5, 6, 7, 0, 1, 2]); // Should output: 0
```

### Exercise 3: Search 2D Matrix

Search in a matrix where each row is sorted and first element of each row is greater than last element of previous row:

```php
function searchMatrix(array $matrix, int $target): bool
{
    // Your code here
}

$matrix = [
    [1, 3, 5, 7],
    [10, 11, 16, 20],
    [23, 30, 34, 60]
];
echo searchMatrix($matrix, 3) ? 'Found' : 'Not found';
```

## Key Takeaways

- **Binary search** is O(log n) - dramatically faster than linear search for sorted data
- **Requirements:** Array must be sorted
- **Iterative** implementation preferred in PHP over recursive
- Many **variants** exist: first/last occurrence, insertion point, etc.
- Can search on **answer space**, not just array indices
- Watch for **off-by-one errors** and **infinite loops**
- **Edge cases:** Empty array, single element, duplicates

## What's Next

In the next chapter, we'll explore **Hash Tables & Hash Functions**, learning about O(1) lookups and collision handling strategies.

---

Continue to [Chapter 13: Hash Tables & Hash Functions](/series/php-algorithms/chapters/13-hash-tables-hash-functions).
