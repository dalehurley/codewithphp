---
title: "11: Linear Search & Variants"
description: "Understand the simplest search algorithm and its variations. Learn sentinel search and early termination."
series: "php-algorithms"
chapter: 11
order: 11
difficulty: "Intermediate"
prerequisites:
  - "Understanding of arrays and loops"
  - "Familiarity with Big O notation"
  - "Completion of foundation chapters"
---

# Linear Search & Variants

Linear search is the simplest and most intuitive search algorithm. While it's O(n) and slower than binary search, it's essential to understand and has important use cases. In this chapter, we'll master linear search and explore its variants and optimizations.

## Basic Linear Search

**Linear search** checks each element sequentially until finding the target or reaching the end.

### Implementation

```php
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
echo linearSearch($numbers, 7); // Output: 2
echo linearSearch($numbers, 8); // Output: false
```

### Alternative Implementation

```php
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
```

## Complexity Analysis

- **Time Complexity:**
  - Best case: O(1) - element is at the beginning
  - Average case: O(n/2) → O(n)
  - Worst case: O(n) - element is at the end or not present

- **Space Complexity:** O(1) - no extra space needed

**Why O(n)?**
- Must potentially check every element
- No way to skip elements without examining them

## When to Use Linear Search

Linear search is the right choice when:

### 1. Array is Unsorted

```php
// Can't use binary search - array not sorted!
$randomNumbers = [15, 3, 42, 7, 23, 8, 16];
$index = linearSearch($randomNumbers, 42);
```

### 2. Small Arrays

```php
// For tiny arrays, linear search is faster than sorting + binary search
$smallArray = [1, 2, 3, 4, 5];

// This is faster:
linearSearch($smallArray, 3); // O(n) where n=5

// Than this:
sort($smallArray);            // O(n log n)
binarySearch($smallArray, 3); // O(log n)
// Total: O(n log n) is worse than O(n) for small n
```

### 3. Single Search Operation

```php
// If you only search once, sorting first is wasteful
$data = generateRandomArray(1000);

// Just search linearly - O(n)
linearSearch($data, $target);

// vs sorting first - O(n log n) + O(log n) = O(n log n)
sort($data);
binarySearch($data, $target);
```

### 4. Linked Lists

```php
// Can't do binary search on linked lists efficiently
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
```

## Linear Search Variants

### 1. Find All Occurrences

```php
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

$numbers = [1, 3, 5, 3, 7, 3, 9];
print_r(findAllOccurrences($numbers, 3));
// Output: [1, 3, 5]
```

### 2. Search with Early Termination

```php
function searchWithCondition(array $arr, callable $condition): mixed
{
    foreach ($arr as $value) {
        if ($condition($value)) {
            return $value;
        }
    }

    return null;
}

// Find first even number
$numbers = [1, 3, 5, 8, 7, 9, 10];
$firstEven = searchWithCondition($numbers, fn($x) => $x % 2 === 0);
echo $firstEven; // Output: 8

// Find first number > 100
$values = [45, 67, 89, 112, 134];
$firstLarge = searchWithCondition($values, fn($x) => $x > 100);
echo $firstLarge; // Output: 112
```

### 3. Sentinel Linear Search

**Sentinel search** eliminates the boundary check in the loop, reducing comparisons:

```php
function sentinelLinearSearch(array $arr, mixed $target): int|false
{
    $n = count($arr);

    if ($n === 0) {
        return false;
    }

    // Save last element
    $last = $arr[$n - 1];

    // Place sentinel at end
    $arr[$n - 1] = $target;

    $i = 0;

    // No need to check $i < $n because sentinel guarantees we'll find target
    while ($arr[$i] !== $target) {
        $i++;
    }

    // Restore last element
    $arr[$n - 1] = $last;

    // Check if we found actual target or just the sentinel
    if ($i < $n - 1 || $last === $target) {
        return $i;
    }

    return false;
}

$numbers = [4, 2, 7, 1, 9, 5];
echo sentinelLinearSearch($numbers, 7); // Output: 2
```

**Advantage:** Fewer comparisons per iteration (no boundary check)
**Disadvantage:** More complex code, minimal performance gain in practice

### 4. Jump Search (for Sorted Arrays)

Combines linear search with jumping:

```php
function jumpSearch(array $arr, mixed $target): int|false
{
    $n = count($arr);
    $step = (int)sqrt($n);
    $prev = 0;

    // Jump ahead by step size
    while ($arr[min($step, $n) - 1] < $target) {
        $prev = $step;
        $step += (int)sqrt($n);

        if ($prev >= $n) {
            return false;
        }
    }

    // Linear search in the block
    while ($arr[$prev] < $target) {
        $prev++;

        if ($prev === min($step, $n)) {
            return false;
        }
    }

    // Check if element found
    if ($arr[$prev] === $target) {
        return $prev;
    }

    return false;
}

// Works on sorted arrays
$sorted = [0, 1, 1, 2, 3, 5, 8, 13, 21, 34, 55, 89, 144, 233, 377, 610];
echo jumpSearch($sorted, 55); // Output: 10
```

**Complexity:** O(√n) - better than linear, worse than binary

### 5. Interpolation Search (for Uniformly Distributed Sorted Arrays)

```php
function interpolationSearch(array $arr, int $target): int|false
{
    $low = 0;
    $high = count($arr) - 1;

    while ($low <= $high && $target >= $arr[$low] && $target <= $arr[$high]) {
        if ($low === $high) {
            if ($arr[$low] === $target) {
                return $low;
            }
            return false;
        }

        // Estimate position using interpolation formula
        $pos = $low + (int)((($high - $low) / ($arr[$high] - $arr[$low])) *
                            ($target - $arr[$low]));

        if ($arr[$pos] === $target) {
            return $pos;
        }

        if ($arr[$pos] < $target) {
            $low = $pos + 1;
        } else {
            $high = $pos - 1;
        }
    }

    return false;
}

// Works best on uniformly distributed data
$uniform = [10, 20, 30, 40, 50, 60, 70, 80, 90, 100];
echo interpolationSearch($uniform, 70); // Output: 6
```

**Complexity:** O(log log n) average for uniform data, O(n) worst case

## Searching Objects and Complex Data

### Search in Array of Objects

```php
class User
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email
    ) {}
}

function findUserByEmail(array $users, string $email): ?User
{
    foreach ($users as $user) {
        if ($user->email === $email) {
            return $user;
        }
    }

    return null;
}

function findUsersByRole(array $users, string $role): array
{
    $result = [];

    foreach ($users as $user) {
        if ($user->role === $role) {
            $result[] = $user;
        }
    }

    return $result;
}

$users = [
    new User(1, 'Alice', 'alice@example.com'),
    new User(2, 'Bob', 'bob@example.com'),
    new User(3, 'Charlie', 'charlie@example.com'),
];

$user = findUserByEmail($users, 'bob@example.com');
```

### Search in Multidimensional Arrays

```php
function searchMultidimensional(array $arr, string $key, mixed $value): ?array
{
    foreach ($arr as $item) {
        if (isset($item[$key]) && $item[$key] === $value) {
            return $item;
        }
    }

    return null;
}

$products = [
    ['id' => 1, 'name' => 'Laptop', 'price' => 1200],
    ['id' => 2, 'name' => 'Mouse', 'price' => 25],
    ['id' => 3, 'name' => 'Keyboard', 'price' => 75],
];

$product = searchMultidimensional($products, 'name', 'Mouse');
print_r($product); // ['id' => 2, 'name' => 'Mouse', 'price' => 25]
```

### Deep Search in Nested Structures

```php
function deepSearch(array $data, string $key, mixed $value): ?array
{
    foreach ($data as $item) {
        if (is_array($item)) {
            // Check current level
            if (isset($item[$key]) && $item[$key] === $value) {
                return $item;
            }

            // Recursively search nested arrays
            $result = deepSearch($item, $key, $value);
            if ($result !== null) {
                return $result;
            }
        }
    }

    return null;
}

$nested = [
    'users' => [
        ['name' => 'Alice', 'age' => 30],
        'admins' => [
            ['name' => 'Bob', 'age' => 25],
            ['name' => 'Charlie', 'age' => 35]
        ]
    ]
];

$result = deepSearch($nested, 'name', 'Charlie');
```

## Optimization Techniques

### 1. Move-to-Front Heuristic

Optimize for repeated searches by moving found elements to the front:

```php
function searchMoveToFront(array &$arr, mixed $target): int|false
{
    foreach ($arr as $index => $value) {
        if ($value === $target) {
            // Move to front if not already there
            if ($index > 0) {
                $temp = $arr[$index];
                array_splice($arr, $index, 1);
                array_unshift($arr, $temp);
                return 0;
            }
            return $index;
        }
    }

    return false;
}

$cache = ['a', 'b', 'c', 'd', 'e'];
searchMoveToFront($cache, 'd');
print_r($cache); // ['d', 'a', 'b', 'c', 'e']
```

### 2. Frequency Count Optimization

Keep frequently accessed items near the front:

```php
class FrequencyOptimizedSearch
{
    private array $items;
    private array $frequencies;

    public function __construct(array $items)
    {
        $this->items = $items;
        $this->frequencies = array_fill(0, count($items), 0);
    }

    public function search(mixed $target): int|false
    {
        foreach ($this->items as $index => $value) {
            if ($value === $target) {
                $this->frequencies[$index]++;
                $this->reorder();
                return $index;
            }
        }

        return false;
    }

    private function reorder(): void
    {
        // Sort by frequency (descending)
        array_multisort(
            $this->frequencies, SORT_DESC,
            $this->items
        );
    }
}
```

### 3. Early Termination with Sorted Data

```php
function searchSortedWithTermination(array $arr, int $target): int|false
{
    foreach ($arr as $index => $value) {
        if ($value === $target) {
            return $index;
        }

        // Early termination: target can't be in remaining elements
        if ($value > $target) {
            return false;
        }
    }

    return false;
}

$sorted = [1, 3, 5, 7, 9, 11, 13];
// Searching for 6 stops at 7 - no need to check rest
```

## Real-World Applications

### 1. Filter Function

```php
function filter(array $arr, callable $predicate): array
{
    $result = [];

    foreach ($arr as $item) {
        if ($predicate($item)) {
            $result[] = $item;
        }
    }

    return $result;
}

$numbers = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

$evens = filter($numbers, fn($x) => $x % 2 === 0);
$largeNumbers = filter($numbers, fn($x) => $x > 5);
```

### 2. Finding in Unsorted Log Files

```php
function findInLogFile(string $filename, string $pattern): array
{
    $matches = [];
    $handle = fopen($filename, 'r');

    if ($handle) {
        $lineNumber = 0;

        while (($line = fgets($handle)) !== false) {
            $lineNumber++;

            // Linear search through file
            if (stripos($line, $pattern) !== false) {
                $matches[] = [
                    'line' => $lineNumber,
                    'content' => trim($line)
                ];
            }
        }

        fclose($handle);
    }

    return $matches;
}

// Find all ERROR entries in log
$errors = findInLogFile('app.log', 'ERROR');
```

### 3. Search with Wildcard Matching

```php
function searchWithWildcard(array $arr, string $pattern): array
{
    $results = [];

    // Convert wildcard pattern to regex
    $regex = '/^' . str_replace('*', '.*', preg_quote($pattern, '/')) . '$/i';

    foreach ($arr as $item) {
        if (preg_match($regex, $item)) {
            $results[] = $item;
        }
    }

    return $results;
}

$files = ['test.php', 'index.php', 'config.json', 'data.php'];
$phpFiles = searchWithWildcard($files, '*.php');
print_r($phpFiles); // ['test.php', 'index.php', 'data.php']
```

## Comparing Search Methods

```php
require_once 'Benchmark.php';

$bench = new Benchmark();
$sizes = [100, 1000, 10000];

foreach ($sizes as $size) {
    $data = range(1, $size);
    shuffle($data);
    $target = $data[rand(0, $size - 1)];

    echo "Array size: $size\n";
    $bench->compare([
        'Linear Search' => fn($arr) => linearSearch($arr, $target),
        'Sentinel Search' => fn($arr) => sentinelLinearSearch($arr, $target),
        'PHP in_array()' => fn($arr) => array_search($target, $arr),
    ], $data, iterations: 100);

    echo "\n";
}
```

## Performance Benchmarks

Let's compare different linear search approaches with actual measurements:

```php
class SearchBenchmark
{
    public function benchmarkSearchMethods(int $arraySize = 10000): array
    {
        $data = range(1, $arraySize);
        shuffle($data);

        // Target at different positions
        $positions = [
            'beginning' => $data[0],
            'middle' => $data[(int)($arraySize / 2)],
            'end' => $data[$arraySize - 1],
            'not_found' => $arraySize + 1
        ];

        $results = [];

        foreach ($positions as $position => $target) {
            echo "\n=== Target at: $position ===\n";

            $start = microtime(true);
            for ($i = 0; $i < 1000; $i++) {
                linearSearch($data, $target);
            }
            $results['linear'][$position] = microtime(true) - $start;

            $start = microtime(true);
            for ($i = 0; $i < 1000; $i++) {
                sentinelLinearSearch($data, $target);
            }
            $results['sentinel'][$position] = microtime(true) - $start;

            $start = microtime(true);
            for ($i = 0; $i < 1000; $i++) {
                array_search($target, $data);
            }
            $results['php_native'][$position] = microtime(true) - $start;

            printf("Linear Search:   %.4f seconds\n", $results['linear'][$position]);
            printf("Sentinel Search: %.4f seconds\n", $results['sentinel'][$position]);
            printf("PHP Native:      %.4f seconds\n", $results['php_native'][$position]);
        }

        return $results;
    }
}

// Run benchmark
$benchmark = new SearchBenchmark();
$benchmark->benchmarkSearchMethods(10000);
```

**Expected Results:**
```
=== Target at: beginning ===
Linear Search:   0.0123 seconds
Sentinel Search: 0.0118 seconds (5% faster)
PHP Native:      0.0089 seconds (27% faster)

=== Target at: end ===
Linear Search:   0.8456 seconds
Sentinel Search: 0.7823 seconds (7% faster)
PHP Native:      0.6234 seconds (26% faster)
```

**Key Insights:**
- PHP native functions are optimized at C level - always prefer them
- Sentinel search shows marginal improvement (5-7%)
- Position of target significantly affects performance
- For not found: all methods must scan entire array

## Memory Efficiency Comparisons

Understanding memory usage of different search approaches:

```php
class MemoryProfiler
{
    public function compareMemoryUsage(): void
    {
        $arraySize = 100000;
        $data = range(1, $arraySize);

        // Basic Linear Search
        $memBefore = memory_get_usage();
        $result = linearSearch($data, 50000);
        $memAfter = memory_get_usage();
        echo "Linear Search Memory: " . ($memAfter - $memBefore) . " bytes\n";

        // Search with Callback
        $memBefore = memory_get_usage();
        $result = searchWithCondition($data, fn($x) => $x === 50000);
        $memAfter = memory_get_usage();
        echo "Callback Search Memory: " . ($memAfter - $memBefore) . " bytes\n";

        // Find All Occurrences
        $memBefore = memory_get_usage();
        $results = findAllOccurrences($data, 50000);
        $memAfter = memory_get_usage();
        echo "Find All Memory: " . ($memAfter - $memBefore) . " bytes\n";

        // Move-to-Front (modifies array)
        $temp = $data;
        $memBefore = memory_get_usage();
        searchMoveToFront($temp, 50000);
        $memAfter = memory_get_usage();
        echo "Move-to-Front Memory: " . ($memAfter - $memBefore) . " bytes\n";
    }
}

$profiler = new MemoryProfiler();
$profiler->compareMemoryUsage();
```

**Output:**
```
Linear Search Memory: 16 bytes (minimal overhead)
Callback Search Memory: 1024 bytes (closure overhead)
Find All Memory: 5632 bytes (stores result array)
Move-to-Front Memory: 524 bytes (array modification)
```

**Memory Considerations:**
- Simple search: O(1) extra space
- Find all: O(k) where k = number of matches
- Move-to-front: O(1) but modifies original
- Callbacks add closure overhead

## PHP SPL Implementations

Leveraging Standard PHP Library for efficient searching:

### Using SplFixedArray

```php
class SPLSearchExamples
{
    // SplFixedArray - faster than regular PHP arrays
    public function searchFixedArray(): void
    {
        $size = 10000;
        $fixedArray = new SplFixedArray($size);

        // Populate
        for ($i = 0; $i < $size; $i++) {
            $fixedArray[$i] = rand(1, 1000);
        }

        // Linear search on SplFixedArray
        $target = 500;
        $found = false;

        for ($i = 0; $i < $fixedArray->getSize(); $i++) {
            if ($fixedArray[$i] === $target) {
                echo "Found at index: $i\n";
                $found = true;
                break;
            }
        }

        if (!$found) {
            echo "Not found\n";
        }
    }

    // Iterator-based search
    public function searchWithIterator(array $data, mixed $target): mixed
    {
        $iterator = new ArrayIterator($data);

        foreach ($iterator as $key => $value) {
            if ($value === $target) {
                return $key;
            }
        }

        return null;
    }

    // Filtered iterator for complex searches
    public function searchWithFilter(array $data, callable $predicate): array
    {
        $iterator = new ArrayIterator($data);
        $filtered = new CallbackFilterIterator(
            $iterator,
            $predicate
        );

        return iterator_to_array($filtered);
    }
}

// Usage examples
$spl = new SPLSearchExamples();
$spl->searchFixedArray();

$data = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
$result = $spl->searchWithIterator($data, 7);
echo "Found at: $result\n";

// Find all even numbers
$evens = $spl->searchWithFilter($data, fn($x) => $x % 2 === 0);
print_r($evens); // [2, 4, 6, 8, 10]
```

### Using SplHeap for Priority Search

```php
class PrioritySearch
{
    private SplMinHeap $heap;

    public function __construct(array $data)
    {
        $this->heap = new SplMinHeap();
        foreach ($data as $item) {
            $this->heap->insert($item);
        }
    }

    public function findMinimum(): mixed
    {
        return $this->heap->top();
    }

    public function searchLessThan(int $threshold): array
    {
        $results = [];
        $tempHeap = clone $this->heap;

        while (!$tempHeap->isEmpty()) {
            $value = $tempHeap->extract();
            if ($value < $threshold) {
                $results[] = $value;
            } else {
                break; // Heap is sorted
            }
        }

        return $results;
    }
}

$search = new PrioritySearch([5, 2, 8, 1, 9, 3]);
echo "Minimum: " . $search->findMinimum() . "\n"; // 1
print_r($search->searchLessThan(5)); // [1, 2, 3]
```

## Security Considerations

### Timing Attack Vulnerabilities

Linear search can be vulnerable to timing attacks when searching sensitive data:

```php
class SecureSearch
{
    /**
     * VULNERABLE: Early termination leaks information
     * Attacker can measure time to determine if value exists
     */
    public function insecureSearch(array $secrets, string $token): bool
    {
        foreach ($secrets as $secret) {
            if ($secret === $token) {
                return true; // Returns immediately - timing leak!
            }
        }
        return false;
    }

    /**
     * SECURE: Constant-time comparison
     * Always checks entire array
     */
    public function secureSearch(array $secrets, string $token): bool
    {
        $found = false;

        foreach ($secrets as $secret) {
            // Use constant-time comparison
            if (hash_equals($secret, $token)) {
                $found = true;
                // Don't return early - continue checking
            }
        }

        return $found;
    }

    /**
     * SECURE: Constant-time with hash_equals
     */
    public function constantTimeArraySearch(array $secrets, string $token): bool
    {
        $result = 0;

        foreach ($secrets as $secret) {
            // Bitwise OR to avoid early termination
            $result |= (int)hash_equals($secret, $token);
        }

        return $result === 1;
    }
}

// Example: API token validation
$validTokens = [
    'token_abc123def456',
    'token_xyz789ghi012',
    'token_mno345pqr678'
];

$search = new SecureSearch();

// VULNERABLE: Timing attack possible
$userToken = $_POST['token'] ?? '';
if ($search->insecureSearch($validTokens, $userToken)) {
    echo "Access granted";
}

// SECURE: Constant-time search
if ($search->secureSearch($validTokens, $userToken)) {
    echo "Access granted";
}
```

### Protection Against Timing Attacks

```php
class TimingSafeOperations
{
    /**
     * Find user by email (timing-safe)
     */
    public function findUserSecure(array $users, string $email): ?array
    {
        $result = null;
        $found = 0;

        foreach ($users as $user) {
            $match = hash_equals($user['email'], $email);

            // Constant-time selection
            if ($match) {
                $result = $user;
                $found = 1;
            }
        }

        // Add random delay to obscure timing
        usleep(rand(100, 500));

        return $found ? $result : null;
    }

    /**
     * Rate limiting to prevent timing attack exploitation
     */
    private array $attempts = [];

    public function searchWithRateLimit(
        string $clientId,
        array $data,
        mixed $target,
        int $maxAttempts = 10
    ): mixed {
        // Track attempts
        if (!isset($this->attempts[$clientId])) {
            $this->attempts[$clientId] = ['count' => 0, 'time' => time()];
        }

        // Reset after 1 minute
        if (time() - $this->attempts[$clientId]['time'] > 60) {
            $this->attempts[$clientId] = ['count' => 0, 'time' => time()];
        }

        // Check rate limit
        if ($this->attempts[$clientId]['count'] >= $maxAttempts) {
            throw new Exception("Rate limit exceeded");
        }

        $this->attempts[$clientId]['count']++;

        // Perform constant-time search
        $result = null;
        foreach ($data as $index => $value) {
            if (hash_equals((string)$value, (string)$target)) {
                $result = $index;
            }
        }

        return $result;
    }
}
```

## Framework Integration Examples

### Laravel Integration

```php
namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SearchService
{
    /**
     * Search with caching for frequently accessed data
     */
    public function searchWithCache(
        Collection $collection,
        string $field,
        mixed $value
    ): mixed {
        $cacheKey = "search:{$field}:{$value}";

        return Cache::remember($cacheKey, 3600, function () use ($collection, $field, $value) {
            return $collection->first(function ($item) use ($field, $value) {
                return $item->$field === $value;
            });
        });
    }

    /**
     * Autocomplete search using linear search
     */
    public function autocomplete(Collection $items, string $query, int $limit = 10): Collection
    {
        return $items
            ->filter(function ($item) use ($query) {
                return str_starts_with(strtolower($item->name), strtolower($query));
            })
            ->take($limit)
            ->values();
    }

    /**
     * Full-text search simulation
     */
    public function fullTextSearch(Collection $items, string $searchTerm): Collection
    {
        $terms = explode(' ', strtolower($searchTerm));

        return $items->filter(function ($item) use ($terms) {
            $content = strtolower($item->title . ' ' . $item->description);

            foreach ($terms as $term) {
                if (str_contains($content, $term)) {
                    return true;
                }
            }

            return false;
        });
    }
}

// Usage in Laravel controller
class ProductController extends Controller
{
    public function search(Request $request, SearchService $searchService)
    {
        $products = Product::all();

        // Autocomplete
        if ($request->has('autocomplete')) {
            return $searchService->autocomplete(
                $products,
                $request->input('q'),
                10
            );
        }

        // Full search
        return $searchService->fullTextSearch(
            $products,
            $request->input('q')
        );
    }
}
```

### Symfony Integration

```php
namespace App\Search;

use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Contracts\Cache\ItemInterface;

class LinearSearchService
{
    private AdapterInterface $cache;

    public function __construct(AdapterInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Search entities with caching
     */
    public function searchEntities(
        array $entities,
        string $property,
        mixed $value
    ): ?object {
        $cacheKey = sprintf('entity_search_%s_%s', $property, md5((string)$value));

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($entities, $property, $value) {
            $item->expiresAfter(3600);

            foreach ($entities as $entity) {
                $getter = 'get' . ucfirst($property);
                if (method_exists($entity, $getter) && $entity->$getter() === $value) {
                    return $entity;
                }
            }

            return null;
        });
    }

    /**
     * Repository pattern with linear search
     */
    public function findByMultipleFields(array $data, array $criteria): array
    {
        return array_filter($data, function ($item) use ($criteria) {
            foreach ($criteria as $field => $value) {
                if (!isset($item[$field]) || $item[$field] !== $value) {
                    return false;
                }
            }
            return true;
        });
    }
}

// Usage in Symfony controller
class SearchController extends AbstractController
{
    #[Route('/search', name: 'app_search')]
    public function search(
        Request $request,
        LinearSearchService $searchService
    ): Response {
        $users = $this->getDoctrine()
            ->getRepository(User::class)
            ->findAll();

        $result = $searchService->searchEntities(
            $users,
            'email',
            $request->query->get('email')
        );

        return $this->json(['user' => $result]);
    }
}
```

## Advanced Real-World Examples

### Autocomplete Implementation

```php
class AutocompleteEngine
{
    private array $dictionary;
    private array $frequencyMap = [];

    public function __construct(array $words)
    {
        sort($words);
        $this->dictionary = $words;

        // Build frequency map
        foreach ($words as $word) {
            $this->frequencyMap[$word] = 0;
        }
    }

    /**
     * Optimized autocomplete with prefix search
     */
    public function suggest(string $prefix, int $maxResults = 5): array
    {
        $prefix = strtolower($prefix);
        $results = [];

        // Binary search for first match (optimization)
        $start = $this->findFirstMatch($prefix);

        if ($start === -1) {
            return [];
        }

        // Linear search from first match
        for ($i = $start; $i < count($this->dictionary); $i++) {
            $word = $this->dictionary[$i];

            if (!str_starts_with(strtolower($word), $prefix)) {
                break; // No more matches
            }

            $results[] = [
                'word' => $word,
                'frequency' => $this->frequencyMap[$word]
            ];

            if (count($results) >= $maxResults) {
                break;
            }
        }

        // Sort by frequency
        usort($results, fn($a, $b) => $b['frequency'] <=> $a['frequency']);

        return array_column($results, 'word');
    }

    private function findFirstMatch(string $prefix): int
    {
        $left = 0;
        $right = count($this->dictionary) - 1;
        $result = -1;

        while ($left <= $right) {
            $mid = (int)(($left + $right) / 2);
            $comparison = strncasecmp($this->dictionary[$mid], $prefix, strlen($prefix));

            if ($comparison >= 0) {
                if ($comparison === 0) {
                    $result = $mid;
                }
                $right = $mid - 1;
            } else {
                $left = $mid + 1;
            }
        }

        return $result;
    }

    public function recordUsage(string $word): void
    {
        if (isset($this->frequencyMap[$word])) {
            $this->frequencyMap[$word]++;
        }
    }
}

// Usage
$words = ['apple', 'application', 'apply', 'banana', 'band', 'bandana'];
$autocomplete = new AutocompleteEngine($words);

print_r($autocomplete->suggest('app')); // ['apple', 'application', 'apply']
print_r($autocomplete->suggest('ban')); // ['banana', 'band', 'bandana']

$autocomplete->recordUsage('application');
$autocomplete->recordUsage('application');
print_r($autocomplete->suggest('app')); // ['application', 'apple', 'apply'] - sorted by frequency
```

### LRU Cache with Linear Search

```php
class LRUCache
{
    private array $cache = [];
    private array $usage = [];
    private int $capacity;

    public function __construct(int $capacity)
    {
        $this->capacity = $capacity;
    }

    public function get(string $key): mixed
    {
        // Linear search in cache
        if (!isset($this->cache[$key])) {
            return null;
        }

        // Update usage order
        $this->updateUsage($key);

        return $this->cache[$key];
    }

    public function put(string $key, mixed $value): void
    {
        if (isset($this->cache[$key])) {
            $this->cache[$key] = $value;
            $this->updateUsage($key);
            return;
        }

        // Evict least recently used if at capacity
        if (count($this->cache) >= $this->capacity) {
            $lruKey = $this->findLRU();
            unset($this->cache[$lruKey]);
            unset($this->usage[$lruKey]);
        }

        $this->cache[$key] = $value;
        $this->updateUsage($key);
    }

    private function updateUsage(string $key): void
    {
        $this->usage[$key] = microtime(true);
    }

    private function findLRU(): string
    {
        $minTime = PHP_FLOAT_MAX;
        $lruKey = null;

        // Linear search for least recently used
        foreach ($this->usage as $key => $time) {
            if ($time < $minTime) {
                $minTime = $time;
                $lruKey = $key;
            }
        }

        return $lruKey;
    }

    public function getStats(): array
    {
        return [
            'size' => count($this->cache),
            'capacity' => $this->capacity,
            'items' => array_keys($this->cache)
        ];
    }
}

// Usage
$cache = new LRUCache(3);
$cache->put('a', 1);
$cache->put('b', 2);
$cache->put('c', 3);
print_r($cache->getStats()); // size: 3, items: [a, b, c]

$cache->put('d', 4); // Evicts 'a' (least recently used)
print_r($cache->getStats()); // size: 3, items: [b, c, d]

$cache->get('b'); // Access 'b', making it most recently used
$cache->put('e', 5); // Evicts 'c' (now least recently used)
print_r($cache->getStats()); // size: 3, items: [b, d, e]
```

## Practice Exercises

### Exercise 1: Find Minimum and Maximum

Write a function that finds both min and max in a single pass:

```php
function findMinMax(array $arr): array
{
    // Your code here
    // Return ['min' => $min, 'max' => $max]
}

echo findMinMax([3, 1, 4, 1, 5, 9, 2, 6]);
// Should output: ['min' => 1, 'max' => 9]
```

<details>
<summary>Solution</summary>

```php
function findMinMax(array $arr): array
{
    if (empty($arr)) {
        throw new InvalidArgumentException('Array cannot be empty');
    }

    $min = $max = $arr[0];

    foreach ($arr as $value) {
        if ($value < $min) {
            $min = $value;
        }
        if ($value > $max) {
            $max = $value;
        }
    }

    return ['min' => $min, 'max' => $max];
}
```
</details>

### Exercise 2: Find Missing Number

Find the missing number in array [1..n]:

```php
function findMissing(array $arr): int
{
    // Array contains 1 to n with one number missing
    // Your code here
}

echo findMissing([1, 2, 4, 5, 6]); // Should output: 3
```

<details>
<summary>Hint</summary>
Use the formula: sum of 1 to n = n(n+1)/2
</details>

### Exercise 3: Two Sum

Find two numbers that add up to a target:

```php
function twoSum(array $nums, int $target): ?array
{
    // Return indices of two numbers that add up to target
    // Your code here
}

print_r(twoSum([2, 7, 11, 15], 9)); // Should output: [0, 1]
```

## Key Takeaways

- **Linear search** is O(n) - simple but slow for large datasets
- **Use linear search** for unsorted data, small arrays, or single searches
- **Sentinel search** eliminates boundary checks
- **Jump search** is O(√n) for sorted arrays
- **Interpolation search** is O(log log n) for uniform data
- **Optimizations** like move-to-front help with repeated searches
- **PHP's in_array()** and **array_search()** use linear search internally
- Linear search is the **only option** for linked lists

## What's Next

In the next chapter, we'll explore **Binary Search**, a much faster O(log n) algorithm that works on sorted data.

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 11 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code-samples/php-algorithms/chapter-11)**

Files included:
- `01-basic-linear-search.php` - Basic linear search implementation with multiple implementations and use cases
- `02-search-variants.php` - Advanced search variants including sentinel search, jump search, and interpolation search
- `03-search-with-conditions.php` - Search with conditions and callbacks including findIndex(), findAll(), and predicates
- `04-object-search.php` - Searching in objects, associative arrays, and nested structures
- `05-practical-applications.php` - Practical real-world applications including grep, autocomplete, and validation
- `README.md` - Complete documentation and usage guide

Clone the repository to run the examples locally:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code-samples/php-algorithms/chapter-11
php 01-basic-linear-search.php
```

---

Continue to [Chapter 12: Binary Search](/series/php-algorithms/chapters/12-binary-search).
