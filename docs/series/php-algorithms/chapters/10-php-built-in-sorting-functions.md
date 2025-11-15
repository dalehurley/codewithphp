---
title: "10: PHP's Built-in Sorting Functions"
description: "Explore PHP's sort(), usort(), and array sorting functions. Understand their implementations and best practices."
series: "php-algorithms"
chapter: 10
order: 10
difficulty: "Intermediate"
prerequisites:
  - "Understanding of sorting algorithms"
  - "Completion of Chapters 05-09"
  - "Familiarity with PHP arrays"
---
![10: PHP's Built-in Sorting Functions](/images/php-algorithms/chapter-10-php-builtin-sorting-hero-full.webp)


<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/php-algorithms">PHP Algorithms</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 10</span>
</div>

# PHP's Built-in Sorting Functions <span class="difficulty-badge difficulty-intermediate">Intermediate</span>

PHP provides powerful built-in sorting functions that are highly optimized. In this chapter, we'll explore all of PHP's sorting functions, learn when to use each one, and master custom comparators for complex sorting requirements.

## What You'll Learn

**Estimated time:** 55 minutes

By the end of this chapter, you will:

- Master all PHP sorting functions: sort(), rsort(), asort(), ksort(), usort(), and more
- Understand which underlying algorithms PHP uses (typically Quick Sort with optimizations)
- Learn custom comparison functions with usort(), uasort(), and uksort()
- Discover performance characteristics and when to use built-in vs custom implementations
- Apply sorting to real-world PHP scenarios including objects, multidimensional arrays, and databases

## Prerequisites

Before starting this chapter, ensure you have:

- ✓ Understanding of sorting algorithms *(review Chapters 05-09 if needed)*
- ✓ Completion of Chapters 05-09 *(305 mins if not done)*
- ✓ Familiarity with PHP arrays *(10 mins review if needed)*

## Quick Checklist

Complete these hands-on tasks as you work through the chapter:

- [ ] Test all basic PHP sorting functions (sort, rsort, asort, arsort, ksort, krsort)
- [ ] Implement custom comparison functions with usort() for complex sorting rules
- [ ] Sort objects by properties using usort() and arrow functions
- [ ] Sort multidimensional arrays with array_multisort()
- [ ] Benchmark PHP's built-in sort() vs your custom implementations
- [ ] Use array_column() with sorting for database-like operations
- [ ] (Optional) Explore natsort() and natcasesort() for natural ordering

## Overview of PHP Sorting Functions

PHP has 15+ sorting functions! They differ in:
- What they sort (values, keys, or both)
- How they maintain keys
- Sort order (ascending, descending, natural, user-defined)

### Quick Reference Table

| Function | Sorts By | Maintains Keys | Order | Complexity | Use When |
|----------|----------|----------------|-------|------------|----------|
| `sort()` | Value | ❌ No | Ascending | O(n log n) | Indexed arrays |
| `rsort()` | Value | ❌ No | Descending | O(n log n) | Indexed arrays (reverse) |
| `asort()` | Value | ✅ Yes | Ascending | O(n log n) | Associative arrays |
| `arsort()` | Value | ✅ Yes | Descending | O(n log n) | Associative arrays (reverse) |
| `ksort()` | Key | ✅ Yes | Ascending | O(n log n) | Sort by keys |
| `krsort()` | Key | ✅ Yes | Descending | O(n log n) | Sort by keys (reverse) |
| `usort()` | Value | ❌ No | User-defined | O(n log n) | Custom comparisons |
| `uasort()` | Value | ✅ Yes | User-defined | O(n log n) | Custom + keep keys |
| `uksort()` | Key | ✅ Yes | User-defined | O(n log n) | Custom key comparison |
| `natsort()` | Value | ✅ Yes | Natural | O(n log n) | Human-friendly numbers |
| `natcasesort()` | Value | ✅ Yes | Natural (case-ins) | O(n log n) | Natural + case-insensitive |
| `array_multisort()` | Multiple | Optional | Multiple | O(n log n) | Multi-column sorting |

### Visual Decision Tree

```
What do you need to sort?
│
├─ By VALUES
│  ├─ Need to keep array keys?
│  │  ├─ YES (associative array)
│  │  │  ├─ Ascending → asort()
│  │  │  ├─ Descending → arsort()
│  │  │  ├─ Natural order → natsort()
│  │  │  └─ Custom logic → uasort()
│  │  │
│  │  └─ NO (indexed array)
│  │     ├─ Ascending → sort()
│  │     ├─ Descending → rsort()
│  │     └─ Custom logic → usort()
│  │
├─ By KEYS
│  ├─ Ascending → ksort()
│  ├─ Descending → krsort()
│  └─ Custom logic → uksort()
│
└─ By MULTIPLE COLUMNS
   └─ Use array_multisort()
```

### Performance Comparison

**Test: Sorting 10,000 elements**

| Function | Time | Notes |
|----------|------|-------|
| `sort()` | 3.5ms | Fastest for values |
| `asort()` | 4.2ms | Slight overhead for key preservation |
| `ksort()` | 3.8ms | Similar to sort() |
| `usort()` | 15ms | Custom function overhead |
| `uasort()` | 16ms | Custom + key preservation |
| `natsort()` | 12ms | Natural comparison slower |
| `array_multisort()` | 8ms | Multi-column (2 columns) |

**Key Insights:**
- Built-in functions are **highly optimized**
- Custom comparators (`usort`) are **3-4x slower**
- Natural sorting has **moderate overhead**
- Pre-calculate expensive values before `usort()`

## Basic Sorting Functions

### sort() - Sort by Value, Reset Keys

```php
$numbers = [3, 1, 4, 1, 5, 9, 2, 6];
sort($numbers);
print_r($numbers);
// [1, 1, 2, 3, 4, 5, 6, 9]

$fruits = ['orange', 'apple', 'banana'];
sort($fruits);
print_r($fruits);
// ['apple', 'banana', 'orange']
```

**Key points:**
- Sorts in ascending order
- Resets array keys to 0, 1, 2...
- Works with numbers and strings
- **Modifies array in place** (returns bool, not sorted array)

### rsort() - Reverse Sort

```php
$numbers = [3, 1, 4, 1, 5, 9];
rsort($numbers);
print_r($numbers);
// [9, 5, 4, 3, 1, 1]
```

### asort() - Sort by Value, Maintain Keys

```php
$ages = [
    'Alice' => 30,
    'Bob' => 25,
    'Charlie' => 35
];

asort($ages);
print_r($ages);
// [
//     'Bob' => 25,
//     'Alice' => 30,
//     'Charlie' => 35
// ]
```

**Use when:** You need to preserve key-value associations.

### arsort() - Reverse Sort, Maintain Keys

```php
$scores = [
    'Player1' => 100,
    'Player2' => 150,
    'Player3' => 75
];

arsort($scores);
print_r($scores);
// [
//     'Player2' => 150,
//     'Player1' => 100,
//     'Player3' => 75
// ]
```

## Sorting by Keys

### ksort() - Sort by Key, Ascending

```php
$data = [
    'z' => 1,
    'a' => 2,
    'm' => 3
];

ksort($data);
print_r($data);
// [
//     'a' => 2,
//     'm' => 3,
//     'z' => 1
// ]
```

### krsort() - Sort by Key, Descending

```php
$months = [
    'March' => 3,
    'January' => 1,
    'February' => 2
];

krsort($months);
print_r($months);
// [
//     'March' => 3,
//     'February' => 2,
//     'January' => 1
// ]
```

## Sort Flags

Many sorting functions accept flags to control comparison behavior:

```php
// Default comparison
sort($arr);

// Compare as numbers
sort($arr, SORT_NUMERIC);

// Compare as strings
sort($arr, SORT_STRING);

// Natural order
sort($arr, SORT_NATURAL);

// Case-insensitive string comparison
sort($arr, SORT_STRING | SORT_FLAG_CASE);
```

### Examples with Flags

```php
// Numeric strings
$numbers = ['10', '2', '1', '20'];

sort($numbers);
print_r($numbers); // ['1', '10', '2', '20'] - string comparison

sort($numbers, SORT_NUMERIC);
print_r($numbers); // ['1', '2', '10', '20'] - numeric comparison

// Case sensitivity
$words = ['Banana', 'apple', 'Cherry'];

sort($words);
print_r($words); // ['Banana', 'Cherry', 'apple'] - case-sensitive

sort($words, SORT_STRING | SORT_FLAG_CASE);
print_r($words); // ['apple', 'Banana', 'Cherry'] - case-insensitive
```

## Custom Sorting with usort()

### Basic usort()

```php
$numbers = [3, 1, 4, 1, 5, 9, 2, 6];

usort($numbers, function($a, $b) {
    return $a <=> $b; // Spaceship operator (PHP 7+)
});

print_r($numbers); // [1, 1, 2, 3, 4, 5, 6, 9]
```

**Comparison function rules:**
- Return **< 0** if $a should come before $b
- Return **0** if $a and $b are equal
- Return **> 0** if $a should come after $b

### The Spaceship Operator (<=>)

```php
// These are equivalent:
function compare1($a, $b) {
    if ($a < $b) return -1;
    if ($a > $b) return 1;
    return 0;
}

function compare2($a, $b) {
    return $a <=> $b; // Much cleaner!
}

// For descending order:
function compareDesc($a, $b) {
    return $b <=> $a; // Flip the order
}
```

### Sorting Objects

```php
class Student
{
    public function __construct(
        public string $name,
        public int $grade,
        public int $age
    ) {}
}

$students = [
    new Student('Alice', 85, 20),
    new Student('Bob', 92, 19),
    new Student('Charlie', 85, 21),
];

// Sort by grade (descending)
usort($students, fn($a, $b) => $b->grade <=> $a->grade);

// Sort by grade (desc), then age (asc) for ties
usort($students, function($a, $b) {
    $gradeCompare = $b->grade <=> $a->grade;
    if ($gradeCompare !== 0) {
        return $gradeCompare;
    }
    return $a->age <=> $b->age;
});

foreach ($students as $student) {
    echo "{$student->name}: Grade {$student->grade}, Age {$student->age}\n";
}
```

### uasort() - Custom Sort, Maintain Keys

```php
$products = [
    'laptop' => ['price' => 1200, 'rating' => 4.5],
    'phone' => ['price' => 800, 'rating' => 4.8],
    'tablet' => ['price' => 500, 'rating' => 4.2],
];

// Sort by rating, keep keys
uasort($products, function($a, $b) {
    return $b['rating'] <=> $a['rating'];
});

print_r($products);
// [
//     'phone' => ['price' => 800, 'rating' => 4.8],
//     'laptop' => ['price' => 1200, 'rating' => 4.5],
//     'tablet' => ['price' => 500, 'rating' => 4.2],
// ]
```

### uksort() - Custom Sort by Keys

```php
$data = [
    'item_3' => 'Third',
    'item_1' => 'First',
    'item_10' => 'Tenth',
    'item_2' => 'Second',
];

// Natural sort by keys
uksort($data, function($a, $b) {
    return strnatcmp($a, $b);
});

print_r($data);
// [
//     'item_1' => 'First',
//     'item_2' => 'Second',
//     'item_3' => 'Third',
//     'item_10' => 'Tenth',
// ]
```

## Natural Sorting

### natsort() - Natural Order

Natural sorting sorts strings with numbers the way humans expect:

```php
$files = ['file1.txt', 'file10.txt', 'file2.txt', 'file20.txt'];

// Regular sort
sort($files);
print_r($files);
// ['file1.txt', 'file10.txt', 'file2.txt', 'file20.txt'] ❌

// Natural sort
natsort($files);
print_r($files);
// ['file1.txt', 'file2.txt', 'file10.txt', 'file20.txt'] ✓
```

### natcasesort() - Case-Insensitive Natural Order

```php
$files = ['File1.txt', 'file10.txt', 'File2.txt'];

natcasesort($files);
print_r($files);
// ['File1.txt', 'File2.txt', 'file10.txt']
```

## array_multisort() - Sort Multiple Arrays

Sort multiple arrays simultaneously, or one array by multiple columns:

```php
// Sort one array by multiple criteria
$data = [
    ['name' => 'Alice', 'age' => 30, 'salary' => 50000],
    ['name' => 'Bob', 'age' => 25, 'salary' => 60000],
    ['name' => 'Charlie', 'age' => 30, 'salary' => 55000],
];

// Extract columns
$ages = array_column($data, 'age');
$salaries = array_column($data, 'salary');

// Sort by age (asc), then salary (desc)
array_multisort(
    $ages, SORT_ASC,
    $salaries, SORT_DESC,
    $data
);

print_r($data);
// [
//     ['name' => 'Bob', 'age' => 25, 'salary' => 60000],
//     ['name' => 'Charlie', 'age' => 30, 'salary' => 55000],
//     ['name' => 'Alice', 'age' => 30, 'salary' => 50000],
// ]
```

### Sorting Two Related Arrays

```php
$names = ['Alice', 'Bob', 'Charlie'];
$scores = [85, 92, 78];

// Sort both arrays by scores (descending)
array_multisort($scores, SORT_DESC, $names);

print_r($names);  // ['Bob', 'Alice', 'Charlie']
print_r($scores); // [92, 85, 78]
```

## Advanced Sorting Techniques

### Multi-Level Sorting

```php
class Product
{
    public function __construct(
        public string $category,
        public string $name,
        public float $price
    ) {}
}

$products = [
    new Product('Electronics', 'Laptop', 1200),
    new Product('Books', 'PHP Guide', 40),
    new Product('Electronics', 'Mouse', 25),
    new Product('Books', 'Algorithms', 50),
];

// Sort by: category (asc), then price (desc), then name (asc)
usort($products, function($a, $b) {
    // Compare category
    $catCompare = $a->category <=> $b->category;
    if ($catCompare !== 0) return $catCompare;

    // Compare price (descending)
    $priceCompare = $b->price <=> $a->price;
    if ($priceCompare !== 0) return $priceCompare;

    // Compare name
    return $a->name <=> $b->name;
});
```

### Sorting with Null Values

```php
$data = [5, null, 3, null, 1, 4];

usort($data, function($a, $b) {
    // Nulls at the end
    if ($a === null && $b === null) return 0;
    if ($a === null) return 1;
    if ($b === null) return -1;

    return $a <=> $b;
});

print_r($data); // [1, 3, 4, 5, null, null]
```

### Case-Insensitive Sorting

```php
$words = ['Banana', 'apple', 'Cherry', 'date'];

usort($words, function($a, $b) {
    return strcasecmp($a, $b);
});

print_r($words); // ['apple', 'Banana', 'Cherry', 'date']
```

### Locale-Aware Sorting

```php
$names = ['Ömer', 'Alice', 'Åsa', 'Bob'];

// Set locale
setlocale(LC_COLLATE, 'sv_SE.UTF-8');

usort($names, function($a, $b) {
    return strcoll($a, $b); // Locale-aware comparison
});
```

## Performance Considerations

### Comparison Function Overhead

```php
// Inefficient: complex calculation in comparison
usort($items, function($a, $b) {
    $scoreA = $this->calculateComplexScore($a); // Called many times!
    $scoreB = $this->calculateComplexScore($b);
    return $scoreB <=> $scoreA;
});

// Better: pre-calculate scores
$scores = array_map(fn($item) => $this->calculateComplexScore($item), $items);
array_multisort($scores, SORT_DESC, $items);
```

### Choose the Right Function

```php
// Overkill for simple sorting
usort($numbers, fn($a, $b) => $a <=> $b);

// Better: use built-in
sort($numbers);

// Much faster for simple cases!
```

## Real-World Examples

### Sorting Search Results

```php
class SearchResult
{
    public function __construct(
        public string $title,
        public float $relevanceScore,
        public int $views,
        public \DateTime $publishedAt
    ) {}
}

function sortSearchResults(array $results): array
{
    usort($results, function($a, $b) {
        // Primary: relevance score (desc)
        $scoreCompare = $b->relevanceScore <=> $a->relevanceScore;
        if ($scoreCompare !== 0) return $scoreCompare;

        // Secondary: views (desc)
        $viewsCompare = $b->views <=> $a->views;
        if ($viewsCompare !== 0) return $viewsCompare;

        // Tertiary: publish date (newest first)
        return $b->publishedAt <=> $a->publishedAt;
    });

    return $results;
}
```

### Sorting E-commerce Products

```php
function sortProducts(array $products, string $sortBy): array
{
    $comparators = [
        'price_asc' => fn($a, $b) => $a['price'] <=> $b['price'],
        'price_desc' => fn($a, $b) => $b['price'] <=> $a['price'],
        'rating' => fn($a, $b) => $b['rating'] <=> $a['rating'],
        'popularity' => fn($a, $b) => $b['sales'] <=> $a['sales'],
        'newest' => fn($a, $b) => $b['created_at'] <=> $a['created_at'],
    ];

    if (isset($comparators[$sortBy])) {
        usort($products, $comparators[$sortBy]);
    }

    return $products;
}
```

### Sorting File Sizes

```php
function sortByFileSize(array $files): array
{
    uasort($files, function($a, $b) {
        $sizeA = filesize($a);
        $sizeB = filesize($b);
        return $sizeB <=> $sizeA; // Largest first
    });

    return $files;
}
```

## Best Practices

### 1. Choose the Right Function

```php
// ✅ Good: Use built-in for simple sorting
sort($numbers);

// ❌ Bad: Unnecessary complexity
usort($numbers, fn($a, $b) => $a <=> $b);
```

### 2. Pre-Calculate Expensive Values

```php
// ❌ Bad: Expensive calculation in comparison
usort($items, function($a, $b) {
    $scoreA = $this->calculateComplexScore($a); // Called n log n times!
    $scoreB = $this->calculateComplexScore($b);
    return $scoreB <=> $scoreA;
});

// ✅ Good: Calculate once
$scores = array_map(fn($item) => $this->calculateComplexScore($item), $items);
array_multisort($scores, SORT_DESC, $items);

// Performance: O(n) + O(n log n) vs O(n² log n)!
```

### 3. Preserve Keys When Needed

```php
// Associative array - preserve keys
$ages = ['Alice' => 30, 'Bob' => 25, 'Charlie' => 35];
asort($ages); // ✅ Keys preserved

// Indexed array - reset keys OK
$numbers = [3, 1, 4, 1, 5];
sort($numbers); // ✅ Keys reset to 0,1,2...
```

### 4. Use Natural Sorting for Human-Friendly Data

```php
$files = ['file1.txt', 'file10.txt', 'file2.txt'];

// ❌ Bad: String sort gives wrong order
sort($files); // ['file1.txt', 'file10.txt', 'file2.txt']

// ✅ Good: Natural sort
natsort($files); // ['file1.txt', 'file2.txt', 'file10.txt']
```

### 5. Leverage Spaceship Operator

```php
// Old way (verbose)
usort($items, function($a, $b) {
    if ($a < $b) return -1;
    if ($a > $b) return 1;
    return 0;
});

// ✅ Modern way (clean)
usort($items, fn($a, $b) => $a <=> $b);
```

## Common Pitfalls and Solutions

### Pitfall 1: Forgetting In-Place Modification

```php
// ❌ Wrong: sort() returns bool, not sorted array
$sorted = sort($numbers);
print_r($sorted); // bool(true) - not what you want!

// ✅ Correct: sort() modifies in place
sort($numbers);
$sorted = $numbers; // Copy if needed
```

### Pitfall 2: Using usort() When Built-in Works

```php
// ❌ Inefficient: 3-4x slower than built-in
usort($numbers, fn($a, $b) => $a <=> $b);

// ✅ Better: Use optimized built-in
sort($numbers);

// Performance impact on 10,000 elements:
// usort(): 15ms
// sort(): 3.5ms (4x faster!)
```

### Pitfall 3: Expensive Comparisons

```php
class Product {
    public function getScore() {
        return $this->calculateComplexScore(); // Expensive!
    }
}

// ❌ Bad: O(n² log n) - score calculated millions of times!
usort($products, fn($a, $b) => $b->getScore() <=> $a->getScore());

// ✅ Good: O(n) pre-calculation + O(n log n) sort
$scores = array_map(fn($p) => $p->getScore(), $products);
array_multisort($scores, SORT_DESC, $products);
```

### Pitfall 4: Unstable Sorting

```php
// PHP's sort() is NOT guaranteed to be stable
$data = [
    ['value' => 5, 'id' => 'a'],
    ['value' => 3, 'id' => 'b'],
    ['value' => 5, 'id' => 'c'],
];

usort($data, fn($a, $b) => $a['value'] <=> $b['value']);
// Order of 'a' and 'c' is unpredictable!

// ✅ Solution: Include secondary sort for stability
usort($data, function($a, $b) {
    $cmp = $a['value'] <=> $b['value'];
    return $cmp !== 0 ? $cmp : $a['id'] <=> $b['id'];
});

// Or use array_multisort for guaranteed stability
$values = array_column($data, 'value');
$ids = array_column($data, 'id');
array_multisort($values, SORT_ASC, $ids, SORT_ASC, $data);
```

### Pitfall 5: Sorting References

```php
// ❌ Problem: Sorting array of objects by reference
$objects = [&$obj1, &$obj2, &$obj3];
sort($objects); // May cause unexpected behavior

// ✅ Solution: Sort array of values, not references
$objects = [$obj1, $obj2, $obj3]; // No references
sort($objects);
```

### Pitfall 6: Locale-Dependent Sorting

```php
$names = ['Åsa', 'Alice', 'Ömer', 'Bob'];

// ❌ Problem: Wrong order for non-English names
sort($names); // ASCII order, not linguistic order

// ✅ Solution: Use locale-aware comparison
setlocale(LC_COLLATE, 'sv_SE.UTF-8');
usort($names, fn($a, $b) => strcoll($a, $b));
// Correct Swedish alphabetical order
```

### Pitfall 7: Numeric String Sorting

```php
$versions = ['1.10', '1.2', '1.1'];

// ❌ Problem: String sort gives wrong order
sort($versions); // ['1.1', '1.10', '1.2'] ✗

// ✅ Solution 1: Use SORT_NUMERIC flag
sort($versions, SORT_NUMERIC); // ['1.1', '1.2', '1.10'] ✓

// ✅ Solution 2: Use version_compare()
usort($versions, fn($a, $b) => version_compare($a, $b));
```

## Practice Exercises

### Exercise 1: Multi-Field Sort

Create a function that sorts an array of records by multiple fields with configurable order:

```php
function multiSort(array $data, array $sortFields): array
{
    // $sortFields = [
    //     ['field' => 'category', 'order' => 'asc'],
    //     ['field' => 'price', 'order' => 'desc'],
    // ]
    // Your code here
}
```

### Exercise 2: Priority-Based Sort

Sort tasks by priority, but group "urgent" tasks first regardless of priority number:

```php
function sortTasks(array $tasks): array
{
    // Sort: urgent first, then by priority, then by created date
    // Your code here
}
```

### Exercise 3: Version Number Sort

Sort an array of version strings correctly:

```php
function sortVersions(array $versions): array
{
    // ['1.10.0', '1.2.0', '1.2.1'] → ['1.2.0', '1.2.1', '1.10.0']
    // Your code here
}
```

## Key Takeaways

- **Use built-in functions** when possible—they're optimized
- **sort()** resets keys, **asort()** maintains keys
- **usort()** for custom comparisons with comparison function
- **Spaceship operator (<=>)** simplifies comparisons
- **array_multisort()** for multi-column sorting
- **natsort()** for natural (human-friendly) sorting
- **Sort flags** control comparison behavior
- **Pre-calculate** expensive values before sorting
- Sorting is **in-place**—functions modify the array

<ChapterCheckbox 
  seriesId="php-algorithms"
  chapterId="10"
  label="Mastered PHP's built-in sorting functions!"
/>

## What's Next

Congratulations! You've completed the sorting algorithms section. In the next chapter, we'll move on to **Searching Algorithms**, starting with **Linear Search & Variants**.

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 10 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/php-algorithms/chapter-10)**

Files included:
- `01-basic-sorting-functions.php` - Demonstrates sort(), rsort(), asort(), arsort(), ksort(), krsort(), and natsort()
- `02-custom-sorting-usort.php` - Custom comparators with usort(), uasort(), uksort() and best practices
- `03-advanced-sorting-techniques.php` - Advanced topics: Intl Collator, SplFixedArray, shuffle(), mixed types, performance optimization
- `README.md` - Complete documentation and usage guide

Clone the repository to run the examples locally:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/php-algorithms/chapter-10
php 01-basic-sorting-functions.php
```

---

Continue to [Chapter 11: Linear Search & Variants](/series/php-algorithms/chapters/11-linear-search-variants).
