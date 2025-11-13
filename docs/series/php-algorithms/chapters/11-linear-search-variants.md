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

---

Continue to [Chapter 12: Binary Search](/series/php-algorithms/chapters/12-binary-search).
