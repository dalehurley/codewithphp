<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use DataScience\Analysis\DataProfiler;

// Load sample customer data
$data = [
    ['id' => 1, 'age' => 35, 'income' => 50000, 'purchases' => 12, 'satisfaction' => 4.5, 'segment' => 'Premium'],
    ['id' => 2, 'age' => 28, 'income' => 35000, 'purchases' => 5, 'satisfaction' => 3.8, 'segment' => 'Standard'],
    ['id' => 3, 'age' => 42, 'income' => 75000, 'purchases' => 18, 'satisfaction' => 4.8, 'segment' => 'Premium'],
    ['id' => 4, 'age' => 31, 'income' => 45000, 'purchases' => 8, 'satisfaction' => 4.2, 'segment' => 'Standard'],
    ['id' => 5, 'age' => 55, 'income' => 95000, 'purchases' => 25, 'satisfaction' => 4.9, 'segment' => 'Premium'],
    ['id' => 6, 'age' => 23, 'income' => 28000, 'purchases' => 3, 'satisfaction' => 3.5, 'segment' => 'Basic'],
    ['id' => 7, 'age' => 38, 'income' => 62000, 'purchases' => 15, 'satisfaction' => 4.6, 'segment' => 'Premium'],
    ['id' => 8, 'age' => 29, 'income' => null, 'purchases' => 6, 'satisfaction' => 3.9, 'segment' => 'Standard'],
];

$profiler = new DataProfiler();

echo "=== Complete Exploratory Data Analysis ===\n\n";

// Generate profile
$profile = $profiler->profileDataset($data);

// Print formatted report
$profiler->printProfile($profile);

// Additional analysis
echo "=== Correlation Analysis ===\n";
if (!empty($profile['correlations'])) {
    foreach ($profile['correlations'] as $corr) {
        echo sprintf(
            "  %s <-> %s: %+.3f (%s)\n",
            $corr['variable1'],
            $corr['variable2'],
            $corr['correlation'],
            $corr['strength']
        );
    }
} else {
    echo "  No strong correlations found\n";
}

echo "\n✓ Complete EDA finished!\n";


