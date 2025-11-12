<?php

declare(strict_types=1);

/**
 * Chapter 04: Exercise 5 Solution
 * Outlier Detection and Handling
 * 
 * Goal: Detect and handle outliers in product pricing data
 */

require_once dirname(__DIR__) . '/13-outlier-detection.php';

echo "=" . str_repeat("=", 69) . "\n";
echo "Exercise 5: Outlier Detection and Handling\n";
echo "=" . str_repeat("=", 69) . "\n\n";

// Step 1: Load products from database
echo "Step 1: Loading products from database...\n";

$dbPath = dirname(__DIR__) . '/data/products.db';
if (!file_exists($dbPath)) {
    echo "Error: Database not found at $dbPath\n";
    exit(1);
}

try {
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("SELECT * FROM products");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "  ✓ Loaded " . count($products) . " products\n\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

// Step 2: Analyze price column
echo "Step 2: Analyzing price column...\n";
$stats = calculateStats($products, 'price');

echo "  Statistics:\n";
foreach ($stats as $key => $value) {
    $label = ucfirst(str_replace('_', ' ', $key));
    echo sprintf("    %-10s: $%s\n", $label, $value);
}
echo "\n";

// Step 3: Detect outliers using both methods
echo "Step 3: Detecting outliers...\n\n";

// Z-Score Method
echo "  Z-Score Method (threshold=2.5):\n";
$zScoreOutliers = detectOutliersZScore($products, 'price', threshold: 2.5);
echo "    Found " . count($zScoreOutliers) . " outliers\n";

if (!empty($zScoreOutliers)) {
    echo "    Outliers:\n";
    foreach ($zScoreOutliers as $idx => $outlier) {
        echo sprintf(
            "      - %s: $%s (Z-score: %s)\n",
            $outlier['row']['name'],
            number_format($outlier['value'], 2),
            $outlier['z_score']
        );
    }
}
echo "\n";

// IQR Method
echo "  IQR Method (multiplier=1.5):\n";
$iqrOutliers = detectOutliersIQR($products, 'price', multiplier: 1.5);
echo "    Found " . count($iqrOutliers) . " outliers\n";

if (!empty($iqrOutliers)) {
    echo "    Outliers:\n";
    foreach ($iqrOutliers as $idx => $outlier) {
        $direction = $outlier['value'] < $outlier['lower_bound'] ? 'LOW' : 'HIGH';
        echo sprintf(
            "      - %s: $%s [%s]\n",
            $outlier['row']['name'],
            number_format($outlier['value'], 2),
            $direction
        );
    }
}
echo "\n";

// Step 4: Compare methods
echo "Step 4: Comparing detection methods...\n";
echo "  Z-Score found: " . count($zScoreOutliers) . " outliers\n";
echo "  IQR found:     " . count($iqrOutliers) . " outliers\n";

if (count($zScoreOutliers) > count($iqrOutliers)) {
    echo "  → Z-Score detected more outliers (more sensitive)\n";
} elseif (count($iqrOutliers) > count($zScoreOutliers)) {
    echo "  → IQR detected more outliers (more robust to extremes)\n";
} else {
    echo "  → Both methods found the same number of outliers\n";
}
echo "\n";

// Step 5: Create cleaned datasets

// Strategy 1: Remove outliers
echo "Step 5: Creating cleaned datasets...\n\n";
echo "  Strategy 1: Remove Outliers\n";

// Use IQR outliers for removal (more conservative)
$removedData = removeOutliers($products, array_keys($iqrOutliers));
$removedStats = calculateStats($removedData, 'price');

echo "    Original size: " . count($products) . " products\n";
echo "    After removal:  " . count($removedData) . " products\n";
echo "    Removed:        " . (count($products) - count($removedData)) . " products\n";
echo "    Impact:\n";
echo "      - Original mean: $" . $stats['mean'] . "\n";
echo "      - New mean:      $" . $removedStats['mean'] . "\n";
echo "      - Original std:  $" . $stats['std'] . "\n";
echo "      - New std:       $" . $removedStats['std'] . "\n";
echo "\n";

// Strategy 2: Cap outliers (Winsorization)
echo "  Strategy 2: Cap Outliers (Winsorization)\n";

if (!empty($iqrOutliers)) {
    $firstOutlier = reset($iqrOutliers);
    $cappedData = capOutliers(
        $products,
        'price',
        $firstOutlier['lower_bound'],
        $firstOutlier['upper_bound']
    );
    $cappedStats = calculateStats($cappedData, 'price');

    echo "    Capping range: $" . number_format($firstOutlier['lower_bound'], 2) .
        " - $" . number_format($firstOutlier['upper_bound'], 2) . "\n";
    echo "    Data size:     " . count($cappedData) . " products (unchanged)\n";
    echo "    Impact:\n";
    echo "      - Original mean: $" . $stats['mean'] . "\n";
    echo "      - Capped mean:   $" . $cappedStats['mean'] . "\n";
    echo "      - Original std:  $" . $stats['std'] . "\n";
    echo "      - Capped std:    $" . $cappedStats['std'] . "\n";
} else {
    echo "    No outliers to cap (none detected by IQR method)\n";
}
echo "\n";

// Step 6: Report impact
echo "Step 6: Impact Analysis\n";
echo str_repeat("-", 70) . "\n";

echo sprintf(
    "%-25s %15s %15s %15s\n",
    "Metric",
    "Original",
    "Removed",
    "Capped"
);
echo str_repeat("-", 70) . "\n";

echo sprintf(
    "%-25s %15d %15d %15d\n",
    "Sample Size",
    count($products),
    count($removedData),
    count($cappedData ?? $products)
);

echo sprintf(
    "%-25s $%14s $%14s $%14s\n",
    "Mean Price",
    $stats['mean'],
    $removedStats['mean'],
    $cappedStats['mean'] ?? $stats['mean']
);

echo sprintf(
    "%-25s $%14s $%14s $%14s\n",
    "Std Deviation",
    $stats['std'],
    $removedStats['std'],
    $cappedStats['std'] ?? $stats['std']
);

echo sprintf(
    "%-25s $%14s $%14s $%14s\n",
    "Min Price",
    $stats['min'],
    $removedStats['min'],
    $cappedStats['min'] ?? $stats['min']
);

echo sprintf(
    "%-25s $%14s $%14s $%14s\n",
    "Max Price",
    $stats['max'],
    $removedStats['max'],
    $cappedStats['max'] ?? $stats['max']
);

echo str_repeat("-", 70) . "\n\n";

// Save cleaned datasets
$processedDir = dirname(__DIR__) . '/processed';
if (!is_dir($processedDir)) {
    mkdir($processedDir, 0755, true);
}

file_put_contents(
    $processedDir . '/exercise5_removed_outliers.json',
    json_encode($removedData, JSON_PRETTY_PRINT)
);

if (!empty($cappedData)) {
    file_put_contents(
        $processedDir . '/exercise5_capped_outliers.json',
        json_encode($cappedData, JSON_PRETTY_PRINT)
    );
}

echo "✓ Cleaned datasets saved:\n";
echo "  - processed/exercise5_removed_outliers.json\n";
if (!empty($cappedData)) {
    echo "  - processed/exercise5_capped_outliers.json\n";
}

// Recommendations
echo "\n" . str_repeat("=", 70) . "\n";
echo "Recommendations\n";
echo str_repeat("=", 70) . "\n\n";

if (count($iqrOutliers) === 0) {
    echo "✓ No significant outliers detected\n";
    echo "  → The price distribution appears normal\n";
    echo "  → Proceed with original data\n";
} else {
    echo "⚠ Outliers detected (" . count($iqrOutliers) . " products)\n\n";

    echo "Choose handling strategy based on context:\n\n";

    echo "Remove outliers if:\n";
    echo "  - They represent data entry errors\n";
    echo "  - Sample size remains sufficient (" . count($removedData) . " products)\n";
    echo "  - Not trying to detect rare expensive items\n\n";

    echo "Cap outliers if:\n";
    echo "  - They represent real but extreme values\n";
    echo "  - Need to preserve sample size\n";
    echo "  - Want to reduce impact without losing data\n\n";

    echo "Keep outliers if:\n";
    echo "  - They represent important business cases (luxury items)\n";
    echo "  - Using robust algorithms (tree-based models)\n";
    echo "  - Goal is to detect unusual patterns\n";
}

echo "\n" . str_repeat("=", 70) . "\n";
echo "✓ Exercise 5 complete!\n";
echo str_repeat("=", 70) . "\n";
