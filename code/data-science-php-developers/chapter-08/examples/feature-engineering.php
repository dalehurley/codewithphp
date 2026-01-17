<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use DataScience\ML\FeatureEngineer;

$engineer = new FeatureEngineer();

echo "=== Feature Engineering Examples ===\n\n";

// 1. Normalization
echo "1. Min-Max Scaling:\n";
$prices = [100, 200, 150, 300, 250];
$normalized = $engineer->minMaxScale($prices);

echo "   Original: " . implode(', ', $prices) . "\n";
echo "   Normalized: " . implode(', ', array_map(fn($x) => round($x, 2), $normalized)) . "\n";
echo "   → All values scaled to [0, 1] range\n\n";

// 2. Standardization
echo "2. Standardization (Z-score):\n";
$scores = [85, 92, 78, 95, 88];
$standardized = $engineer->standardize($scores);

echo "   Original: " . implode(', ', $scores) . "\n";
echo "   Standardized: " . implode(', ', array_map(fn($x) => round($x, 2), $standardized)) . "\n";
echo "   → Mean = 0, Std Dev = 1\n\n";

// 3. Polynomial features
echo "3. Polynomial Features:\n";
$x = [1, 2, 3];
$poly = $engineer->polynomialFeatures($x, degree: 3);

echo "   Original: " . implode(', ', $x) . "\n";
echo "   Polynomial (degree 3): " . implode(', ', $poly) . "\n";
echo "   → Creates x, x², x³\n\n";

// 4. Binning
echo "4. Binning Continuous Features:\n";
$ages = [18, 25, 35, 42, 55, 68, 75];
$bins = [30, 50, 70]; // young, middle, senior, elderly
$binned = $engineer->binFeature($ages, $bins);

echo "   Ages: " . implode(', ', $ages) . "\n";
echo "   Bins: [0-30, 31-50, 51-70, 70+]\n";
echo "   Binned: " . implode(', ', $binned) . "\n";
echo "   → Converts continuous to categorical\n\n";

// 5. One-hot encoding
echo "5. One-Hot Encoding:\n";
$colors = ['red', 'blue', 'red', 'green', 'blue'];
$result = $engineer->oneHotEncode($colors);
$encoded = $result['encoded'];

echo "   Colors: " . implode(', ', $colors) . "\n";
echo "   One-hot encoded:\n";
foreach ($encoded as $i => $vector) {
    echo "     {$colors[$i]}: [" . implode(', ', $vector) . "]\n";
}
echo "   → Each category becomes a binary vector\n\n";

// 6. Date features
echo "6. Date-Based Features:\n";
$date = '2026-01-12';
$dateFeats = $engineer->dateFeatures($date);

echo "   Date: {$date}\n";
echo "   Extracted features:\n";
foreach ($dateFeats as $feature => $value) {
    echo "     {$feature}: {$value}\n";
}
echo "   → Captures temporal patterns\n\n";

// 7. Text features
echo "7. Text Features:\n";
$text = "Machine Learning is AMAZING! It has 123 applications.";
$textFeats = $engineer->textFeatures($text);

echo "   Text: \"{$text}\"\n";
echo "   Extracted features:\n";
foreach ($textFeats as $feature => $value) {
    echo "     {$feature}: {$value}\n";
}
echo "   → Captures text characteristics\n\n";

echo "✓ Feature engineering examples complete!\n";
