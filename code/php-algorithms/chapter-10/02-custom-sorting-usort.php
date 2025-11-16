<?php

declare(strict_types=1);

/**
 * Chapter 10: Custom Sorting with usort(), uasort(), and uksort()
 * 
 * Demonstrates custom comparison functions for complex sorting requirements:
 * - usort() - User-defined sort (resets keys)
 * - uasort() - User-defined sort (maintains keys)
 * - uksort() - User-defined sort by keys
 * - Spaceship operator (<=>)
 * - Multi-level sorting
 * - Sorting objects and complex data structures
 */

echo "=== Custom Sorting Functions Demo ===\n\n";

// ============================================================================
// 1. Basic usort() with Spaceship Operator
// ============================================================================

echo "1. Basic usort() - Simple number sorting\n";
echo str_repeat("-", 50) . "\n";

$numbers = [3, 1, 4, 1, 5, 9, 2, 6];
echo "Original: " . json_encode($numbers) . "\n";

usort($numbers, function($a, $b) {
    return $a <=> $b; // Spaceship operator
});

echo "Ascending: " . json_encode($numbers) . "\n";

usort($numbers, function($a, $b) {
    return $b <=> $a; // Flip for descending
});

echo "Descending: " . json_encode($numbers) . "\n\n";

// ============================================================================
// 2. Arrow Functions (PHP 7.4+)
// ============================================================================

echo "2. Arrow Functions - Cleaner syntax\n";
echo str_repeat("-", 50) . "\n";

$values = [5, 2, 8, 1, 9];
echo "Original: " . json_encode($values) . "\n";

usort($values, fn($a, $b) => $a <=> $b);
echo "Sorted: " . json_encode($values) . "\n\n";

// ============================================================================
// 3. Sorting Objects by Property
// ============================================================================

echo "3. Sorting Objects by Property\n";
echo str_repeat("-", 50) . "\n";

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
    new Student('Diana', 92, 20),
];

echo "Original:\n";
foreach ($students as $s) {
    echo "  {$s->name}: Grade {$s->grade}, Age {$s->age}\n";
}

// Sort by grade (descending)
usort($students, fn($a, $b) => $b->grade <=> $a->grade);

echo "\nSorted by grade (descending):\n";
foreach ($students as $s) {
    echo "  {$s->name}: Grade {$s->grade}, Age {$s->age}\n";
}
echo "\n";

// ============================================================================
// 4. Multi-Level Sorting
// ============================================================================

echo "4. Multi-Level Sorting\n";
echo str_repeat("-", 50) . "\n";

$students2 = [
    new Student('Alice', 85, 20),
    new Student('Bob', 92, 19),
    new Student('Charlie', 85, 21),
    new Student('Diana', 92, 20),
];

// Sort by: grade (desc), then age (asc), then name (asc)
usort($students2, function($a, $b) {
    // Primary: grade (descending)
    $gradeCompare = $b->grade <=> $a->grade;
    if ($gradeCompare !== 0) {
        return $gradeCompare;
    }
    
    // Secondary: age (ascending)
    $ageCompare = $a->age <=> $b->age;
    if ($ageCompare !== 0) {
        return $ageCompare;
    }
    
    // Tertiary: name (ascending)
    return $a->name <=> $b->name;
});

echo "Sorted by: grade (desc) → age (asc) → name (asc)\n";
foreach ($students2 as $s) {
    echo "  {$s->name}: Grade {$s->grade}, Age {$s->age}\n";
}
echo "\n";

// ============================================================================
// 5. uasort() - Maintain Keys
// ============================================================================

echo "5. uasort() - Custom sort maintaining keys\n";
echo str_repeat("-", 50) . "\n";

$products = [
    'laptop' => ['price' => 1200, 'rating' => 4.5],
    'phone' => ['price' => 800, 'rating' => 4.8],
    'tablet' => ['price' => 500, 'rating' => 4.2],
    'monitor' => ['price' => 400, 'rating' => 4.6],
];

echo "Original:\n";
foreach ($products as $name => $details) {
    echo "  {$name}: \${$details['price']}, rating {$details['rating']}\n";
}

// Sort by rating (descending), maintain product names as keys
uasort($products, function($a, $b) {
    return $b['rating'] <=> $a['rating'];
});

echo "\nSorted by rating (keys maintained):\n";
foreach ($products as $name => $details) {
    echo "  {$name}: \${$details['price']}, rating {$details['rating']}\n";
}
echo "\n";

// ============================================================================
// 6. uksort() - Sort by Keys with Custom Logic
// ============================================================================

echo "6. uksort() - Custom sort by keys\n";
echo str_repeat("-", 50) . "\n";

$data = [
    'item_3' => 'Third',
    'item_1' => 'First',
    'item_10' => 'Tenth',
    'item_2' => 'Second',
    'item_20' => 'Twentieth',
];

echo "Original:\n";
print_r($data);

// Natural sort by keys
uksort($data, function($a, $b) {
    return strnatcmp($a, $b);
});

echo "After uksort with natural comparison:\n";
print_r($data);

// ============================================================================
// 7. Sorting with Null Values
// ============================================================================

echo "7. Sorting with Null Values\n";
echo str_repeat("-", 50) . "\n";

$values = [5, null, 3, null, 1, 4, null];
echo "Original: " . json_encode($values) . "\n";

usort($values, function($a, $b) {
    // Move nulls to the end
    if ($a === null && $b === null) return 0;
    if ($a === null) return 1;  // a goes after b
    if ($b === null) return -1; // a goes before b
    
    return $a <=> $b;
});

echo "Sorted (nulls last): " . json_encode($values) . "\n\n";

// ============================================================================
// 8. Case-Insensitive String Sorting
// ============================================================================

echo "8. Case-Insensitive String Sorting\n";
echo str_repeat("-", 50) . "\n";

$words = ['Banana', 'apple', 'Cherry', 'date', 'Elderberry'];
echo "Original: " . json_encode($words) . "\n";

usort($words, function($a, $b) {
    return strcasecmp($a, $b); // Case-insensitive comparison
});

echo "Sorted (case-insensitive): " . json_encode($words) . "\n\n";

// ============================================================================
// 9. Sorting by String Length
// ============================================================================

echo "9. Sorting by String Length\n";
echo str_repeat("-", 50) . "\n";

$strings = ['a', 'abc', 'ab', 'abcd', 'abcde'];
echo "Original: " . json_encode($strings) . "\n";

usort($strings, function($a, $b) {
    $lenCompare = strlen($a) <=> strlen($b);
    if ($lenCompare !== 0) {
        return $lenCompare;
    }
    // If same length, sort alphabetically
    return $a <=> $b;
});

echo "Sorted by length, then alphabetically: " . json_encode($strings) . "\n\n";

// ============================================================================
// 10. Complex Multi-Field Sorting
// ============================================================================

echo "10. Complex Multi-Field Sorting\n";
echo str_repeat("-", 50) . "\n";

class Product
{
    public function __construct(
        public string $category,
        public string $name,
        public float $price
    ) {}
}

$products2 = [
    new Product('Electronics', 'Laptop', 1200),
    new Product('Books', 'PHP Guide', 40),
    new Product('Electronics', 'Mouse', 25),
    new Product('Books', 'Algorithms', 50),
    new Product('Electronics', 'Keyboard', 80),
];

echo "Original:\n";
foreach ($products2 as $p) {
    echo "  [{$p->category}] {$p->name}: \${$p->price}\n";
}

// Sort by: category (asc), then price (desc), then name (asc)
usort($products2, function($a, $b) {
    // Primary: category (ascending)
    $catCompare = $a->category <=> $b->category;
    if ($catCompare !== 0) return $catCompare;
    
    // Secondary: price (descending)
    $priceCompare = $b->price <=> $a->price;
    if ($priceCompare !== 0) return $priceCompare;
    
    // Tertiary: name (ascending)
    return $a->name <=> $b->name;
});

echo "\nSorted by: category (asc) → price (desc) → name (asc)\n";
foreach ($products2 as $p) {
    echo "  [{$p->category}] {$p->name}: \${$p->price}\n";
}
echo "\n";

// ============================================================================
// 11. Performance: usort() vs sort()
// ============================================================================

echo "11. Performance Comparison\n";
echo str_repeat("-", 50) . "\n";

$testSize = 10000;
$testData = range(1, $testSize);
shuffle($testData);

// Test built-in sort()
$data1 = $testData;
$start = microtime(true);
sort($data1);
$time1 = (microtime(true) - $start) * 1000;

// Test usort() with simple comparison
$data2 = $testData;
$start = microtime(true);
usort($data2, fn($a, $b) => $a <=> $b);
$time2 = (microtime(true) - $start) * 1000;

echo "Sorting {$testSize} elements:\n";
echo sprintf("  sort():  %.3f ms\n", $time1);
echo sprintf("  usort(): %.3f ms (%.1fx slower)\n", $time2, $time2 / $time1);
echo "\nKey Insight: Use built-in functions when possible!\n";
echo "usort() has overhead from calling PHP callback function.\n\n";

// ============================================================================
// 12. Real-World Example: E-commerce Product Sorting
// ============================================================================

echo "12. Real-World Example: E-commerce Product Sorting\n";
echo str_repeat("-", 50) . "\n";

function sortProducts(array $products, string $sortBy): array
{
    $comparators = [
        'price_asc' => fn($a, $b) => $a['price'] <=> $b['price'],
        'price_desc' => fn($a, $b) => $b['price'] <=> $a['price'],
        'rating' => fn($a, $b) => $b['rating'] <=> $a['rating'],
        'popularity' => fn($a, $b) => $b['sales'] <=> $a['sales'],
    ];
    
    if (isset($comparators[$sortBy])) {
        usort($products, $comparators[$sortBy]);
    }
    
    return $products;
}

$ecomProducts = [
    ['name' => 'Laptop', 'price' => 1200, 'rating' => 4.5, 'sales' => 150],
    ['name' => 'Phone', 'price' => 800, 'rating' => 4.8, 'sales' => 320],
    ['name' => 'Tablet', 'price' => 500, 'rating' => 4.2, 'sales' => 89],
    ['name' => 'Monitor', 'price' => 400, 'rating' => 4.6, 'sales' => 210],
];

$sorted = sortProducts($ecomProducts, 'rating');
echo "Sorted by rating:\n";
foreach ($sorted as $p) {
    echo "  {$p['name']}: \${$p['price']} (rating {$p['rating']}, {$p['sales']} sales)\n";
}

// ============================================================================
// Summary
// ============================================================================

echo "\n" . str_repeat("=", 50) . "\n";
echo "Summary:\n";
echo "- usort(): Custom sort, resets keys\n";
echo "- uasort(): Custom sort, maintains keys\n";
echo "- uksort(): Custom sort by keys\n";
echo "- Spaceship operator (<=>): Clean comparisons\n";
echo "- Arrow functions (fn): Concise syntax\n";
echo "- Multi-level sorting: Compare multiple fields\n";
echo "- Performance: Built-in functions are 3-4x faster\n";
echo str_repeat("=", 50) . "\n";


