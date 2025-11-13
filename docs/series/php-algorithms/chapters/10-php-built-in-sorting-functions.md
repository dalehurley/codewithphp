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

# PHP's Built-in Sorting Functions

PHP provides powerful built-in sorting functions that are highly optimized. In this chapter, we'll explore all of PHP's sorting functions, learn when to use each one, and master custom comparators for complex sorting requirements.

## Overview of PHP Sorting Functions

PHP has 15+ sorting functions! They differ in:
- What they sort (values, keys, or both)
- How they maintain keys
- Sort order (ascending, descending, natural, user-defined)

### Quick Reference

| Function | Sorts By | Maintains Keys | Order |
|----------|----------|----------------|-------|
| `sort()` | Value | No | Ascending |
| `rsort()` | Value | No | Descending |
| `asort()` | Value | Yes | Ascending |
| `arsort()` | Value | Yes | Descending |
| `ksort()` | Key | Yes | Ascending |
| `krsort()` | Key | Yes | Descending |
| `usort()` | Value | No | User-defined |
| `uasort()` | Value | Yes | User-defined |
| `uksort()` | Key | Yes | User-defined |
| `natsort()` | Value | Yes | Natural |
| `natcasesort()` | Value | Yes | Natural (case-insensitive) |
| `array_multisort()` | Multiple | Optional | Multiple |

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

## Common Pitfalls

### Pitfall 1: Forgetting In-Place Modification

```php
// Wrong: sort() returns bool, not sorted array
$sorted = sort($numbers);

// Correct: sort() modifies in place
sort($numbers);
$sorted = $numbers;
```

### Pitfall 2: Using usort() When Built-in Works

```php
// Inefficient
usort($numbers, fn($a, $b) => $a <=> $b);

// Better
sort($numbers);
```

### Pitfall 3: Unstable Sorting

```php
// PHP's sort is not guaranteed to be stable
// Equal elements may change order

// For stable sort, use array_multisort with original indices
$data = [/* ... */];
$indices = array_keys($data);
array_multisort($data, SORT_ASC, $indices, SORT_ASC);
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

## What's Next

Congratulations! You've completed the sorting algorithms section. In the next chapter, we'll move on to **Searching Algorithms**, starting with **Linear Search & Variants**.

---

Continue to [Chapter 11: Linear Search & Variants](/series/php-algorithms/chapters/11-linear-search-variants).
