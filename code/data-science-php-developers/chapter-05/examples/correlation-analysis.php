<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use DataScience\Analysis\CorrelationAnalyzer;

// Sample data: advertising spend vs sales
$data = [
    ['tv_spend' => 230.1, 'radio_spend' => 37.8, 'newspaper_spend' => 69.2, 'sales' => 22.1],
    ['tv_spend' => 44.5, 'radio_spend' => 39.3, 'newspaper_spend' => 45.1, 'sales' => 10.4],
    ['tv_spend' => 17.2, 'radio_spend' => 45.9, 'newspaper_spend' => 69.3, 'sales' => 9.3],
    ['tv_spend' => 151.5, 'radio_spend' => 41.3, 'newspaper_spend' => 58.5, 'sales' => 18.5],
    ['tv_spend' => 180.8, 'radio_spend' => 10.8, 'newspaper_spend' => 58.4, 'sales' => 12.9],
    ['tv_spend' => 8.7, 'radio_spend' => 48.9, 'newspaper_spend' => 75.0, 'sales' => 7.2],
    ['tv_spend' => 57.5, 'radio_spend' => 32.8, 'newspaper_spend' => 23.5, 'sales' => 11.8],
    ['tv_spend' => 120.2, 'radio_spend' => 19.6, 'newspaper_spend' => 11.6, 'sales' => 13.2],
    ['tv_spend' => 8.6, 'radio_spend' => 2.1, 'newspaper_spend' => 1.0, 'sales' => 4.8],
    ['tv_spend' => 199.8, 'radio_spend' => 2.6, 'newspaper_spend' => 21.2, 'sales' => 10.6],
];

$analyzer = new CorrelationAnalyzer();

echo "=== Correlation Analysis ===\n\n";

// 1. Calculate individual correlations
echo "Individual Correlations with Sales:\n";

$columns = ['tv_spend', 'radio_spend', 'newspaper_spend'];
foreach ($columns as $column) {
    $corr = $analyzer->pearsonCorrelation($data, $column, 'sales');
    $strength = abs($corr) >= 0.7 ? 'Strong' : (abs($corr) >= 0.5 ? 'Moderate' : 'Weak');
    
    echo sprintf(
        "  %-20s: %+.3f (%s)\n",
        ucwords(str_replace('_', ' ', $column)),
        $corr,
        $strength
    );
}

echo "\n";

// 2. Correlation matrix
echo "=== Correlation Matrix ===\n";
$matrix = $analyzer->correlationMatrix($data);

// Print header
echo "                 ";
foreach (array_keys($matrix) as $col) {
    echo sprintf("%-12s", substr($col, 0, 10));
}
echo "\n";

// Print matrix
foreach ($matrix as $row => $values) {
    echo sprintf("%-15s", substr($row, 0, 13));
    
    foreach ($values as $value) {
        if ($value === null) {
            echo sprintf("%-12s", "N/A");
        } else {
            // Color code: strong correlation
            $display = sprintf("%+.2f", $value);
            if (abs($value) >= 0.7 && $value != 1.0) {
                $display .= " **";
            }
            echo sprintf("%-12s", $display);
        }
    }
    echo "\n";
}

echo "\n** = Strong correlation (|r| >= 0.7)\n\n";

// 3. Find strongest correlations
echo "=== Strongest Correlations ===\n";
$strong = $analyzer->strongestCorrelations($data, threshold: 0.5);

if (empty($strong)) {
    echo "No strong correlations found (threshold: 0.5)\n";
} else {
    foreach ($strong as $item) {
        echo sprintf(
            "%s <-> %s: %+.3f (%s, %s)\n",
            $item['variable1'],
            $item['variable2'],
            $item['correlation'],
            $item['strength'],
            $item['direction']
        );
    }
}

echo "\n";

// 4. Interpret findings
echo "=== Interpretation ===\n";

$tvCorr = $analyzer->pearsonCorrelation($data, 'tv_spend', 'sales');
$radioCorr = $analyzer->pearsonCorrelation($data, 'radio_spend', 'sales');
$newsCorr = $analyzer->pearsonCorrelation($data, 'newspaper_spend', 'sales');

echo "Key Findings:\n";

if (abs($tvCorr) > abs($radioCorr) && abs($tvCorr) > abs($newsCorr)) {
    echo "  • TV advertising has the strongest relationship with sales\n";
    echo "    (r = " . sprintf("%+.3f", $tvCorr) . ")\n";
}

if ($radioCorr > 0.5) {
    echo "  • Radio advertising shows moderate positive correlation\n";
    echo "    (r = " . sprintf("%+.3f", $radioCorr) . ")\n";
}

if (abs($newsCorr) < 0.3) {
    echo "  • Newspaper advertising shows weak correlation with sales\n";
    echo "    (r = " . sprintf("%+.3f", $newsCorr) . ")\n";
    echo "    → Consider reallocating budget from newspaper to TV/radio\n";
}

echo "\n✓ Correlation analysis complete!\n";


