<?php

declare(strict_types=1);

/**
 * Load and explore time series sales data.
 * Demonstrates proper CSV parsing, date handling, and summary statistics.
 */

// Load sales data from CSV
function loadSalesData(string $filepath): array
{
    if (!file_exists($filepath)) {
        throw new RuntimeException("Data file not found: $filepath");
    }

    $data = [];
    $handle = fopen($filepath, 'r');

    // Skip header row
    fgetcsv($handle);

    while (($row = fgetcsv($handle)) !== false) {
        [$month, $revenue] = $row;

        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            throw new RuntimeException("Invalid date format: $month");
        }

        $data[] = [
            'month' => $month,
            'revenue' => (float) $revenue,
            'timestamp' => strtotime($month . '-01'), // Convert to Unix timestamp
        ];
    }

    fclose($handle);

    return $data;
}

// Calculate summary statistics
function calculateStatistics(array $data): array
{
    $revenues = array_column($data, 'revenue');
    $n = count($revenues);

    if ($n === 0) {
        throw new RuntimeException("No data to analyze");
    }

    // Basic statistics
    $total = array_sum($revenues);
    $mean = $total / $n;

    sort($revenues);
    $median = $n % 2 === 0
        ? ($revenues[$n / 2 - 1] + $revenues[$n / 2]) / 2
        : $revenues[floor($n / 2)];

    $min = min($revenues);
    $max = max($revenues);

    // Standard deviation
    $variance = array_sum(array_map(
        fn($rev) => pow($rev - $mean, 2),
        $revenues
    )) / $n;
    $stdDev = sqrt($variance);

    // Simple trend (difference between last and first)
    $firstRevenue = $data[0]['revenue'];
    $lastRevenue = $data[$n - 1]['revenue'];
    $totalGrowth = $lastRevenue - $firstRevenue;
    $growthPercentage = ($totalGrowth / $firstRevenue) * 100;

    return [
        'count' => $n,
        'mean' => $mean,
        'median' => $median,
        'min' => $min,
        'max' => $max,
        'std_dev' => $stdDev,
        'first_month' => $data[0]['month'],
        'last_month' => $data[$n - 1]['month'],
        'first_revenue' => $firstRevenue,
        'last_revenue' => $lastRevenue,
        'total_growth' => $totalGrowth,
        'growth_percentage' => $growthPercentage,
    ];
}

// Main execution
echo "📊 Sales Data Exploration\n";
echo str_repeat('=', 60) . "\n\n";

try {
    // Load data
    $salesData = loadSalesData('sample-sales-data.csv');
    echo "✅ Loaded " . count($salesData) . " months of sales data\n\n";

    // Display first and last few records
    echo "First 3 months:\n";
    foreach (array_slice($salesData, 0, 3) as $record) {
        echo sprintf(
            "  %s: $%s\n",
            $record['month'],
            number_format($record['revenue'])
        );
    }

    echo "\nLast 3 months:\n";
    foreach (array_slice($salesData, -3) as $record) {
        echo sprintf(
            "  %s: $%s\n",
            $record['month'],
            number_format($record['revenue'])
        );
    }

    // Calculate and display statistics
    echo "\n" . str_repeat('-', 60) . "\n";
    echo "Summary Statistics:\n";
    echo str_repeat('-', 60) . "\n";

    $stats = calculateStatistics($salesData);

    echo sprintf(
        "Period: %s to %s (%d months)\n",
        $stats['first_month'],
        $stats['last_month'],
        $stats['count']
    );
    echo sprintf("Average Revenue: $%s\n", number_format($stats['mean'], 2));
    echo sprintf("Median Revenue: $%s\n", number_format($stats['median'], 2));
    echo sprintf("Min Revenue: $%s\n", number_format($stats['min']));
    echo sprintf("Max Revenue: $%s\n", number_format($stats['max']));
    echo sprintf("Std Deviation: $%s\n", number_format($stats['std_dev'], 2));

    echo "\n" . str_repeat('-', 60) . "\n";
    echo "Growth Analysis:\n";
    echo str_repeat('-', 60) . "\n";
    echo sprintf(
        "Starting Revenue (%s): $%s\n",
        $stats['first_month'],
        number_format($stats['first_revenue'])
    );
    echo sprintf(
        "Ending Revenue (%s): $%s\n",
        $stats['last_month'],
        number_format($stats['last_revenue'])
    );
    echo sprintf(
        "Total Growth: $%s (%.1f%%)\n",
        number_format($stats['total_growth']),
        $stats['growth_percentage']
    );

    echo "\n✅ Data loaded and analyzed successfully!\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
