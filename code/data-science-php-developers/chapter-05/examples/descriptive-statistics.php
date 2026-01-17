<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use DataScience\Analysis\StatisticalAnalyzer;

// Load sample sales data
$data = [
    ['product' => 'Widget A', 'price' => 29.99, 'quantity' => 150, 'revenue' => 4498.50, 'category' => 'Electronics'],
    ['product' => 'Widget B', 'price' => 49.99, 'quantity' => 89, 'revenue' => 4449.11, 'category' => 'Electronics'],
    ['product' => 'Gadget X', 'price' => 19.99, 'quantity' => 200, 'revenue' => 3998.00, 'category' => 'Home'],
    ['product' => 'Gadget Y', 'price' => 39.99, 'quantity' => 120, 'revenue' => 4798.80, 'category' => 'Home'],
    ['product' => 'Tool A', 'price' => 99.99, 'quantity' => 45, 'revenue' => 4499.55, 'category' => 'Tools'],
    ['product' => 'Tool B', 'price' => 79.99, 'quantity' => 67, 'revenue' => 5359.33, 'category' => 'Tools'],
    ['product' => 'Device 1', 'price' => 149.99, 'quantity' => 30, 'revenue' => 4499.70, 'category' => 'Electronics'],
    ['product' => 'Device 2', 'price' => 199.99, 'quantity' => 22, 'revenue' => 4399.78, 'category' => 'Electronics'],
];

$analyzer = new StatisticalAnalyzer();

echo "=== Exploratory Data Analysis ===\n\n";

// 1. Dataset overview
echo "Dataset Overview:\n";
echo "  Total products: " . count($data) . "\n";
echo "  Categories: " . count(array_unique(array_column($data, 'category'))) . "\n\n";

// 2. Analyze price column
echo "=== Price Analysis ===\n";
$priceStats = $analyzer->analyzeColumn($data, 'price');

echo "Central Tendency:\n";
echo "  Mean: $" . number_format($priceStats['mean'], 2) . "\n";
echo "  Median: $" . number_format($priceStats['median'], 2) . "\n";
echo "  Mode: $" . number_format($priceStats['mode'], 2) . "\n\n";

echo "Spread:\n";
echo "  Min: $" . number_format($priceStats['min'], 2) . "\n";
echo "  Max: $" . number_format($priceStats['max'], 2) . "\n";
echo "  Range: $" . number_format($priceStats['range'], 2) . "\n";
echo "  Std Dev: $" . number_format($priceStats['std_dev'], 2) . "\n";
echo "  IQR: $" . number_format($priceStats['iqr'], 2) . "\n\n";

echo "Shape:\n";
echo "  Skewness: " . round($priceStats['skewness'], 3) . "\n";
echo "  Kurtosis: " . round($priceStats['kurtosis'], 3) . "\n\n";

// Interpret skewness
if (abs($priceStats['skewness']) < 0.5) {
    echo "  → Distribution is approximately symmetric\n";
} elseif ($priceStats['skewness'] > 0) {
    echo "  → Distribution is right-skewed (tail extends right)\n";
} else {
    echo "  → Distribution is left-skewed (tail extends left)\n";
}
echo "\n";

// 3. Five-number summary
echo "=== Five-Number Summary (Price) ===\n";
$fiveNum = $analyzer->fiveNumberSummary($data, 'price');
foreach ($fiveNum as $stat => $value) {
    echo "  " . ucfirst($stat) . ": $" . number_format($value, 2) . "\n";
}
echo "\n";

// 4. Frequency distribution
echo "=== Price Distribution (Bins) ===\n";
$distribution = $analyzer->frequencyDistribution($data, 'price', bins: 4);
foreach ($distribution as $i => $bin) {
    $bar = str_repeat('█', $bin['count']);
    echo sprintf(
        "  $%6.2f - $%6.2f: %s (%d)\n",
        $bin['lower'],
        $bin['upper'],
        $bar,
        $bin['count']
    );
}
echo "\n";

// 5. Categorical frequency
echo "=== Category Distribution ===\n";
$catFreq = $analyzer->categoricalFrequency($data, 'category');
foreach ($catFreq as $item) {
    $bar = str_repeat('█', (int)($item['percentage'] / 5));
    echo sprintf(
        "  %-15s: %s %d (%s%%)\n",
        $item['value'],
        $bar,
        $item['count'],
        $item['percentage']
    );
}
echo "\n";

// 6. Analyze all numeric columns
echo "=== Complete Dataset Statistics ===\n";
$allStats = $analyzer->analyzeDataset($data);

foreach ($allStats as $column => $stats) {
    echo ucfirst($column) . ":\n";
    echo "  Mean: " . number_format($stats['mean'], 2) . "\n";
    echo "  Median: " . number_format($stats['median'], 2) . "\n";
    echo "  Std Dev: " . number_format($stats['std_dev'], 2) . "\n";
    echo "  Range: [" . number_format($stats['min'], 2) . " - " . 
         number_format($stats['max'], 2) . "]\n\n";
}

echo "✓ Descriptive statistics analysis complete!\n";


