<?php

# filename: examples/lazy-pipeline.php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use DataScience\Pipeline\LazyPipeline;
use DataScience\Streaming\StreamingCSVReader;
use DataScience\Memory\MemoryMonitor;

$monitor = new MemoryMonitor();
$reader = new StreamingCSVReader();
$filename = __DIR__ . '/../data/large_dataset.csv';

if (!file_exists($filename)) {
    echo "Error: Please run generate-large-csv.php first to create sample data.\n";
    exit(1);
}

echo "=== Lazy Data Pipeline ===\n\n";

// 1. Filter, transform, and limit
echo "1. Find top 5 high earners in Engineering:\n";

$topEarners = LazyPipeline::from($reader->readFile($filename))
    ->filter(fn($row) => $row['department'] === 'Engineering')
    ->filter(fn($row) => $row['salary'] > 100000)
    ->map(fn($row) => [
        'name' => $row['name'],
        'salary' => $row['salary'],
        'bonus' => $row['salary'] * 0.15,
    ])
    ->take(5)
    ->toArray();

foreach ($topEarners as $employee) {
    echo "   {$employee['name']}: \${$employee['salary']} (Bonus: \${$employee['bonus']})\n";
}

$monitor->report("   ");
echo "\n";

// 2. Count by department
echo "2. Count employees by department:\n";

$counts = LazyPipeline::from($reader->readFile($filename))
    ->reduce(function($acc, $row) {
        $dept = $row['department'];
        $acc[$dept] = ($acc[$dept] ?? 0) + 1;
        return $acc;
    }, []);

foreach ($counts as $dept => $count) {
    echo "   {$dept}: " . number_format($count) . "\n";
}

$monitor->report("   ");
echo "\n";

// 3. Calculate average salary by age group
echo "3. Average salary by age group:\n";

$ageGroups = LazyPipeline::from($reader->readFile($filename))
    ->map(function($row) {
        $ageGroup = floor($row['age'] / 10) * 10;
        return [
            'age_group' => "{$ageGroup}s",
            'salary' => $row['salary'],
        ];
    })
    ->reduce(function($acc, $row) {
        $group = $row['age_group'];
        
        if (!isset($acc[$group])) {
            $acc[$group] = ['sum' => 0, 'count' => 0];
        }
        
        $acc[$group]['sum'] += $row['salary'];
        $acc[$group]['count']++;
        
        return $acc;
    }, []);

foreach ($ageGroups as $group => $data) {
    $avg = $data['sum'] / $data['count'];
    echo "   {$group}: $" . number_format($avg, 2) . " ({$data['count']} employees)\n";
}

$monitor->report("   ");
echo "\n";

// 4. Complex pipeline: filter, transform, aggregate
echo "4. Senior employees (age > 50) with high salaries:\n";

$seniorStats = LazyPipeline::from($reader->readFile($filename))
    ->filter(fn($row) => $row['age'] > 50)
    ->filter(fn($row) => $row['salary'] > 80000)
    ->reduce(function($acc, $row) {
        $acc['count']++;
        $acc['total_salary'] += $row['salary'];
        $acc['max_salary'] = max($acc['max_salary'], $row['salary']);
        $acc['min_salary'] = min($acc['min_salary'], $row['salary']);
        return $acc;
    }, [
        'count' => 0,
        'total_salary' => 0,
        'max_salary' => 0,
        'min_salary' => PHP_INT_MAX,
    ]);

echo "   Count: " . number_format($seniorStats['count']) . "\n";
echo "   Avg Salary: $" . number_format($seniorStats['total_salary'] / $seniorStats['count'], 2) . "\n";
echo "   Range: $" . number_format($seniorStats['min_salary']) . " - $" . number_format($seniorStats['max_salary']) . "\n";

$monitor->report("   ");
echo "\n";

echo "✓ All pipeline operations completed with minimal memory!\n";
$monitor->report("Final");
