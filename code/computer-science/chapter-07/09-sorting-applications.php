<?php

declare(strict_types=1);

/**
 * Sorting Applications - Real-World Use Cases
 *
 * Demonstrates:
 * - Practical sorting applications
 * - Multi-key sorting
 * - Top-k elements
 * - Finding median
 * - Interval scheduling
 * - Database query optimization
 * - Duplicate detection
 */

echo "=== Sorting Applications: Real-World Use Cases ===\n\n";

// Application 1: Multi-Key Sorting (Database-style)
echo "1. Multi-Key Sorting (Database Records)\n";
echo "=========================================\n";

class Employee
{
    public function __construct(
        public string $department,
        public string $name,
        public int $salary
    ) {}

    public function __toString(): string
    {
        return sprintf("%-12s %-15s $%,d", $this->department, $this->name, $this->salary);
    }
}

$employees = [
    new Employee('Engineering', 'Alice', 95000),
    new Employee('Sales', 'Bob', 75000),
    new Employee('Engineering', 'Charlie', 105000),
    new Employee('Sales', 'Diana', 80000),
    new Employee('Engineering', 'Eve', 95000),
    new Employee('Marketing', 'Frank', 70000),
];

echo "Original:\n";
foreach ($employees as $emp) {
    echo "  $emp\n";
}
echo "\n";

// Sort by department, then salary (desc), then name
usort($employees, function($a, $b) {
    if ($a->department !== $b->department) {
        return $a->department <=> $b->department;
    }
    if ($a->salary !== $b->salary) {
        return $b->salary <=> $a->salary; // Descending
    }
    return $a->name <=> $b->name;
});

echo "Sorted by: Department → Salary (desc) → Name\n";
foreach ($employees as $emp) {
    echo "  $emp\n";
}
echo "\n";

// Application 2: Top K Elements
echo "2. Finding Top K Elements\n";
echo "==========================\n";

$scores = [85, 92, 78, 95, 88, 76, 94, 89, 91, 87];
$k = 3;

echo "Scores: [" . implode(', ', $scores) . "]\n";
echo "Find top $k scores\n\n";

// Method 1: Full sort (inefficient)
$sorted = $scores;
rsort($sorted);
$topK = array_slice($sorted, 0, $k);

echo "Top $k scores: [" . implode(', ', $topK) . "]\n\n";

// Method 2: Partial sort (more efficient)
function topKElements(array $arr, int $k): array
{
    // Build max heap and extract k times
    $n = count($arr);

    // Build max heap
    for ($i = (int)($n / 2) - 1; $i >= 0; $i--) {
        heapify($arr, $n, $i);
    }

    $result = [];

    // Extract top k
    for ($i = 0; $i < $k; $i++) {
        $result[] = $arr[0];
        [$arr[0], $arr[$n - 1 - $i]] = [$arr[$n - 1 - $i], $arr[0]];
        heapify($arr, $n - 1 - $i, 0);
    }

    return $result;
}

function heapify(array &$arr, int $n, int $i): void
{
    $largest = $i;
    $left = 2 * $i + 1;
    $right = 2 * $i + 2;

    if ($left < $n && $arr[$left] > $arr[$largest]) $largest = $left;
    if ($right < $n && $arr[$right] > $arr[$largest]) $largest = $right;

    if ($largest !== $i) {
        [$arr[$i], $arr[$largest]] = [$arr[$largest], $arr[$i]];
        heapify($arr, $n, $largest);
    }
}

$topKHeap = topKElements($scores, $k);
echo "Using heap (more efficient): [" . implode(', ', $topKHeap) . "]\n";
echo "Time: O(n + k log n) vs O(n log n) for full sort\n\n";

// Application 3: Finding Median
echo "3. Finding Median\n";
echo "==================\n";

function findMedian(array $arr): float
{
    sort($arr);
    $n = count($arr);

    if ($n % 2 === 1) {
        return $arr[(int)($n / 2)];
    } else {
        return ($arr[$n / 2 - 1] + $arr[$n / 2]) / 2;
    }
}

$data1 = [12, 3, 5, 7, 19];
$data2 = [12, 3, 5, 7, 19, 21];

echo "Dataset 1: [" . implode(', ', $data1) . "]\n";
echo "Median: " . findMedian($data1) . "\n\n";

echo "Dataset 2: [" . implode(', ', $data2) . "]\n";
echo "Median: " . findMedian($data2) . "\n\n";

// Application 4: Interval Scheduling
echo "4. Interval Scheduling (Greedy Algorithm)\n";
echo "===========================================\n";

class Interval
{
    public function __construct(
        public int $start,
        public int $end,
        public string $name
    ) {}

    public function __toString(): string
    {
        return "$this->name [{$this->start}-{$this->end}]";
    }
}

$intervals = [
    new Interval(1, 3, 'Task A'),
    new Interval(2, 5, 'Task B'),
    new Interval(3, 6, 'Task C'),
    new Interval(5, 7, 'Task D'),
    new Interval(6, 9, 'Task E'),
    new Interval(8, 10, 'Task F'),
];

echo "Available tasks:\n";
foreach ($intervals as $interval) {
    echo "  $interval\n";
}
echo "\n";

// Greedy: sort by end time, select non-overlapping
usort($intervals, fn($a, $b) => $a->end <=> $b->end);

$selected = [];
$lastEnd = -1;

foreach ($intervals as $interval) {
    if ($interval->start >= $lastEnd) {
        $selected[] = $interval;
        $lastEnd = $interval->end;
    }
}

echo "Maximum non-overlapping tasks (sorted by end time):\n";
foreach ($selected as $interval) {
    echo "  $interval\n";
}
echo "\nSelected " . count($selected) . " tasks (optimal)\n\n";

// Application 5: Duplicate Detection
echo "5. Duplicate Detection\n";
echo "=======================\n";

function findDuplicates(array $arr): array
{
    sort($arr);
    $duplicates = [];

    for ($i = 1; $i < count($arr); $i++) {
        if ($arr[$i] === $arr[$i - 1] && !in_array($arr[$i], $duplicates)) {
            $duplicates[] = $arr[$i];
        }
    }

    return $duplicates;
}

$numbers = [1, 3, 2, 4, 3, 5, 1, 6, 2];
echo "Array: [" . implode(', ', $numbers) . "]\n";

$dupes = findDuplicates($numbers);
echo "Duplicates: [" . implode(', ', $dupes) . "]\n";
echo "Time: O(n log n) via sorting vs O(n) with hash table\n\n";

// Application 6: Merge Sorted Lists (Like Database JOIN)
echo "6. Merge Sorted Lists (Database-style)\n";
echo "========================================\n";

$prices_2023 = [
    ['product' => 'A', 'price' => 10],
    ['product' => 'B', 'price' => 15],
    ['product' => 'C', 'price' => 20],
];

$prices_2024 = [
    ['product' => 'A', 'price' => 12],
    ['product' => 'B', 'price' => 14],
    ['product' => 'D', 'price' => 25],
];

echo "2023 Prices:\n";
foreach ($prices_2023 as $item) {
    echo "  {$item['product']}: \${$item['price']}\n";
}
echo "\n2024 Prices:\n";
foreach ($prices_2024 as $item) {
    echo "  {$item['product']}: \${$item['price']}\n";
}
echo "\n";

// Merge with price increase
$merged = [];
$i = $j = 0;

while ($i < count($prices_2023) && $j < count($prices_2024)) {
    $cmp = $prices_2023[$i]['product'] <=> $prices_2024[$j]['product'];

    if ($cmp < 0) {
        $merged[] = [
            'product' => $prices_2023[$i]['product'],
            'price_2023' => $prices_2023[$i]['price'],
            'price_2024' => null,
        ];
        $i++;
    } elseif ($cmp > 0) {
        $merged[] = [
            'product' => $prices_2024[$j]['product'],
            'price_2023' => null,
            'price_2024' => $prices_2024[$j]['price'],
        ];
        $j++;
    } else {
        $merged[] = [
            'product' => $prices_2023[$i]['product'],
            'price_2023' => $prices_2023[$i]['price'],
            'price_2024' => $prices_2024[$j]['price'],
        ];
        $i++;
        $j++;
    }
}

echo "Merged Price Comparison:\n";
foreach ($merged as $item) {
    $p2023 = $item['price_2023'] ?? 'N/A';
    $p2024 = $item['price_2024'] ?? 'N/A';
    $change = '';

    if ($item['price_2023'] && $item['price_2024']) {
        $diff = $item['price_2024'] - $item['price_2023'];
        $change = $diff > 0 ? " (+\$$diff)" : " (\$$diff)";
    }

    echo "  {$item['product']}: 2023=\${$p2023}, 2024=\${$p2024}$change\n";
}
echo "\n";

// Application 7: Closest Pair Problem
echo "7. Closest Pair Problem\n";
echo "========================\n";

function closestPair(array $arr): array
{
    if (count($arr) < 2) return [];

    sort($arr);

    $minDiff = PHP_INT_MAX;
    $pair = [];

    for ($i = 1; $i < count($arr); $i++) {
        $diff = abs($arr[$i] - $arr[$i - 1]);

        if ($diff < $minDiff) {
            $minDiff = $diff;
            $pair = [$arr[$i - 1], $arr[$i]];
        }
    }

    return $pair;
}

$points = [5, 10, 2, 15, 3, 8];
echo "Points: [" . implode(', ', $points) . "]\n";

$closest = closestPair($points);
echo "Closest pair: [{$closest[0]}, {$closest[1]}]\n";
echo "Distance: " . abs($closest[1] - $closest[0]) . "\n";
echo "Time: O(n log n) for sorting + O(n) for finding pair\n\n";

// Application 8: Search Query Optimization
echo "8. Search Query Optimization\n";
echo "=============================\n";

$queries = ['apple', 'banana', 'apricot', 'application', 'apply', 'berry'];

echo "Search queries: [" . implode(', ', $queries) . "]\n";
echo "User types: 'app'\n\n";

// Sort for efficient prefix search
sort($queries);

echo "Sorted for binary search: [" . implode(', ', $queries) . "]\n\n";

$prefix = 'app';
$results = [];

// Find start position with binary search
foreach ($queries as $query) {
    if (str_starts_with($query, $prefix)) {
        $results[] = $query;
    }
}

echo "Autocomplete results:\n";
foreach ($results as $result) {
    echo "  - $result\n";
}
echo "\nSorting enables efficient autocomplete!\n\n";

echo "Key Insights:\n";
echo "==============\n\n";

echo "When Sorting is Essential:\n";
echo "  ✓ Database query optimization (ORDER BY)\n";
echo "  ✓ Finding duplicates or unique elements\n";
echo "  ✓ Finding median or percentiles\n";
echo "  ✓ Interval scheduling problems\n";
echo "  ✓ Merge operations (like database JOINs)\n";
echo "  ✓ Binary search enablement\n";
echo "  ✓ Closest pair problems\n";
echo "  ✓ Autocomplete and prefix search\n\n";

echo "Sorting Trade-offs:\n";
echo "  • One-time O(n log n) cost\n";
echo "  • Enables many O(log n) or O(n) operations\n";
echo "  • Worth it when data is queried multiple times\n";
echo "  • Not worth it for single linear scan\n\n";

echo "Real-World Examples:\n";
echo "  • E-commerce: Sort products by price/rating\n";
echo "  • Social media: Sort posts by timestamp\n";
echo "  • Search engines: Rank search results\n";
echo "  • Finance: Sort transactions by date\n";
echo "  • Scheduling: Allocate resources optimally\n";
echo "  • Data analysis: Find outliers, compute statistics\n";

echo "\n✅ Complete! Sorting applications demonstrated.\n";
