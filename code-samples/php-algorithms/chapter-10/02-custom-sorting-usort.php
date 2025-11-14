<?php

declare(strict_types=1);

/**
 * Custom Sorting with usort(), uasort(), uksort()
 * Demonstrates custom comparators for complex sorting requirements
 */

class Product
{
    public function __construct(
        public string $name,
        public float $price,
        public int $rating,
        public int $sales
    ) {}

    public function __toString(): string
    {
        return sprintf(
            "%s - $%.2f (★%d, %d sales)",
            $this->name,
            $this->price,
            $this->rating,
            $this->sales
        );
    }
}

echo "CUSTOM SORTING WITH usort()\n";
echo str_repeat('=', 70) . "\n\n";

// Example 1: Basic usort with spaceship operator
echo "Example 1: Basic Numeric Sorting\n";
echo str_repeat('-', 70) . "\n";
$numbers = [3, 1, 4, 1, 5, 9, 2, 6];
usort($numbers, fn($a, $b) => $a <=> $b);
echo "Ascending: " . json_encode($numbers) . "\n";

$numbers = [3, 1, 4, 1, 5, 9, 2, 6];
usort($numbers, fn($a, $b) => $b <=> $a);
echo "Descending: " . json_encode($numbers) . "\n\n";

// Example 2: Sorting objects by single property
echo "Example 2: Sort Products by Price\n";
echo str_repeat('-', 70) . "\n";
$products = [
    new Product('Laptop', 1200, 4, 100),
    new Product('Mouse', 25, 5, 500),
    new Product('Keyboard', 75, 4, 300),
];

echo "Before:\n";
foreach ($products as $p) {
    echo "  $p\n";
}

usort($products, fn($a, $b) => $a->price <=> $b->price);

echo "\nAfter (sorted by price):\n";
foreach ($products as $p) {
    echo "  $p\n";
}
echo "\n";

// Example 3: Multi-level sorting
echo "Example 3: Multi-Level Sorting (Rating desc, then Price asc)\n";
echo str_repeat('-', 70) . "\n";
$products = [
    new Product('Laptop', 1200, 4, 100),
    new Product('Mouse', 25, 5, 500),
    new Product('Keyboard', 75, 4, 300),
    new Product('Monitor', 350, 5, 200),
];

usort($products, function($a, $b) {
    // Primary: rating (descending)
    $ratingCmp = $b->rating <=> $a->rating;
    if ($ratingCmp !== 0) {
        return $ratingCmp;
    }

    // Secondary: price (ascending)
    return $a->price <=> $b->price;
});

echo "Sorted by: Rating↓, then Price↑\n";
foreach ($products as $p) {
    echo "  $p\n";
}
echo "\n";

// Example 4: uasort - maintain keys
echo "Example 4: uasort() - Maintain Array Keys\n";
echo str_repeat('-', 70) . "\n";
$scores = [
    'Alice' => 85,
    'Bob' => 92,
    'Charlie' => 78,
    'Dave' => 92,
];

uasort($scores, fn($a, $b) => $b <=> $a);

echo "Scores (high to low):\n";
foreach ($scores as $name => $score) {
    echo "  $name: $score\n";
}
echo "\n";

// Example 5: Complex sorting logic
echo "Example 5: Complex E-commerce Sorting\n";
echo str_repeat('-', 70) . "\n";
$products = [
    new Product('Item A', 100, 4, 1000),
    new Product('Item B', 150, 5, 500),
    new Product('Item C', 120, 4, 800),
    new Product('Item D', 90, 5, 1200),
];

// Sort by: Rating desc, Sales desc, Price asc
usort($products, function($a, $b) {
    $ratingCmp = $b->rating <=> $a->rating;
    if ($ratingCmp !== 0) return $ratingCmp;

    $salesCmp = $b->sales <=> $a->sales;
    if ($salesCmp !== 0) return $salesCmp;

    return $a->price <=> $b->price;
});

echo "Best products (Rating↓, Sales↓, Price↑):\n";
foreach ($products as $p) {
    echo "  $p\n";
}
echo "\n";

// Example 6: Null handling
echo "Example 6: Sorting with Null Values\n";
echo str_repeat('-', 70) . "\n";
$data = [5, null, 3, null, 1, 4];

usort($data, function($a, $b) {
    // Nulls at the end
    if ($a === null && $b === null) return 0;
    if ($a === null) return 1;
    if ($b === null) return -1;
    return $a <=> $b;
});

echo "Sorted with nulls at end: " . json_encode($data) . "\n\n";

// Example 7: Case-insensitive string sorting
echo "Example 7: Case-Insensitive String Sorting\n";
echo str_repeat('-', 70) . "\n";
$words = ['Banana', 'apple', 'Cherry', 'date'];

usort($words, fn($a, $b) => strcasecmp($a, $b));

echo "Case-insensitive: " . json_encode($words) . "\n\n";

// Example 8: Performance comparison
echo "Example 8: Performance - Pre-calculate vs Calculate in Comparator\n";
echo str_repeat('-', 70) . "\n";

$items = array_map(fn($i) => ['value' => $i], range(1, 1000));
shuffle($items);

// Bad: Calculate in comparator (called many times)
$test = $items;
$start = microtime(true);
usort($test, function($a, $b) {
    $scoreA = $a['value'] * 2 + 10; // Expensive calculation
    $scoreB = $b['value'] * 2 + 10;
    return $scoreA <=> $scoreB;
});
$slowTime = (microtime(true) - $start) * 1000;

// Good: Pre-calculate
$test = $items;
$scores = array_map(fn($item) => $item['value'] * 2 + 10, $test);
$start = microtime(true);
array_multisort($scores, SORT_ASC, $test);
$fastTime = (microtime(true) - $start) * 1000;

echo sprintf("Calculate in comparator: %.4f ms\n", $slowTime);
echo sprintf("Pre-calculate scores:    %.4f ms\n", $fastTime);
echo sprintf("Speed improvement:       %.1fx faster!\n\n", $slowTime / $fastTime);

echo "BEST PRACTICES:\n";
echo str_repeat('-', 70) . "\n";
echo "✓ Use spaceship operator (<=>) for clean comparisons\n";
echo "✓ Pre-calculate expensive values before sorting\n";
echo "✓ Use built-in functions when possible (faster than usort)\n";
echo "✓ Consider stability: PHP's usort is not guaranteed stable\n";
echo "✗ Avoid complex calculations in comparator function\n";
