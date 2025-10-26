<?php

declare(strict_types=1);

/**
 * Chapter 04: Data Collection and Preprocessing
 * Example 13: Outlier Detection
 * 
 * Demonstrates: Z-score method, IQR method, handling strategies
 */

/**
 * Detect outliers using Z-score method
 * Values beyond threshold standard deviations from mean are outliers
 * 
 * @param array $data Dataset
 * @param string $column Column to check for outliers
 * @param float $threshold Z-score threshold (typically 2.5 or 3)
 * @return array Outlier information indexed by row number
 */
function detectOutliersZScore(array $data, string $column, float $threshold = 3.0): array
{
    if (empty($data)) {
        return [];
    }

    $values = array_column($data, $column);
    $values = array_map('floatval', $values);

    $mean = array_sum($values) / count($values);
    $variance = array_sum(array_map(fn($v) => ($v - $mean) ** 2, $values)) / count($values);
    $stdDev = sqrt($variance);

    if ($stdDev == 0) {
        return []; // All values are the same
    }

    $outliers = [];
    foreach ($data as $idx => $row) {
        $value = (float)$row[$column];
        $zScore = abs(($value - $mean) / $stdDev);

        if ($zScore > $threshold) {
            $outliers[$idx] = [
                'row' => $row,
                'z_score' => round($zScore, 4),
                'value' => $value,
                'mean' => round($mean, 2),
                'std_dev' => round($stdDev, 2)
            ];
        }
    }

    return $outliers;
}

/**
 * Detect outliers using IQR (Interquartile Range) method
 * More robust to extreme values than Z-score
 * 
 * @param array $data Dataset
 * @param string $column Column to check for outliers
 * @param float $multiplier IQR multiplier (typically 1.5)
 * @return array Outlier information indexed by row number
 */
function detectOutliersIQR(array $data, string $column, float $multiplier = 1.5): array
{
    if (empty($data)) {
        return [];
    }

    $values = array_column($data, $column);
    $values = array_map('floatval', $values);
    sort($values);

    $count = count($values);
    $q1Index = (int)floor($count * 0.25);
    $q3Index = (int)floor($count * 0.75);

    $q1 = $values[$q1Index];
    $q3 = $values[$q3Index];
    $iqr = $q3 - $q1;

    $lowerBound = $q1 - ($multiplier * $iqr);
    $upperBound = $q3 + ($multiplier * $iqr);

    $outliers = [];
    foreach ($data as $idx => $row) {
        $value = (float)$row[$column];

        if ($value < $lowerBound || $value > $upperBound) {
            $outliers[$idx] = [
                'row' => $row,
                'value' => $value,
                'lower_bound' => round($lowerBound, 2),
                'upper_bound' => round($upperBound, 2),
                'q1' => round($q1, 2),
                'q3' => round($q3, 2),
                'iqr' => round($iqr, 2)
            ];
        }
    }

    return $outliers;
}

/**
 * Remove outliers from dataset
 * 
 * @param array $data Dataset
 * @param array $outlierIndices Array of outlier row indices
 * @return array Cleaned dataset
 */
function removeOutliers(array $data, array $outlierIndices): array
{
    return array_values(array_filter($data, function ($row, $idx) use ($outlierIndices) {
        return !isset($outlierIndices[$idx]);
    }, ARRAY_FILTER_USE_BOTH));
}

/**
 * Cap outliers to boundary values instead of removing them
 * 
 * @param array $data Dataset
 * @param string $column Column to cap
 * @param float $lowerBound Lower boundary
 * @param float $upperBound Upper boundary
 * @return array Dataset with capped values
 */
function capOutliers(array $data, string $column, float $lowerBound, float $upperBound): array
{
    return array_map(function ($row) use ($column, $lowerBound, $upperBound) {
        $value = (float)$row[$column];

        if ($value < $lowerBound) {
            $row[$column] = $lowerBound;
        } elseif ($value > $upperBound) {
            $row[$column] = $upperBound;
        }

        return $row;
    }, $data);
}

/**
 * Calculate statistics for a column
 * 
 * @param array $data Dataset
 * @param string $column Column name
 * @return array Statistics
 */
function calculateStats(array $data, string $column): array
{
    $values = array_map('floatval', array_column($data, $column));
    sort($values);

    $count = count($values);
    $mean = array_sum($values) / $count;
    $variance = array_sum(array_map(fn($v) => ($v - $mean) ** 2, $values)) / $count;

    return [
        'count' => $count,
        'mean' => round($mean, 2),
        'std' => round(sqrt($variance), 2),
        'min' => round(min($values), 2),
        'max' => round(max($values), 2),
        'q1' => round($values[(int)floor($count * 0.25)], 2),
        'median' => round($values[(int)floor($count * 0.5)], 2),
        'q3' => round($values[(int)floor($count * 0.75)], 2)
    ];
}

/**
 * Create a simple text-based box plot
 * 
 * @param array $stats Statistics array
 * @return string ASCII box plot
 */
function textBoxPlot(array $stats): string
{
    $width = 50;
    $range = $stats['max'] - $stats['min'];

    if ($range == 0) {
        return "All values are identical: {$stats['min']}";
    }

    $scale = $width / $range;

    $positions = [
        'min' => 0,
        'q1' => (int)(($stats['q1'] - $stats['min']) * $scale),
        'median' => (int)(($stats['median'] - $stats['min']) * $scale),
        'q3' => (int)(($stats['q3'] - $stats['min']) * $scale),
        'max' => $width
    ];

    // Build plot using array
    $plotArray = array_fill(0, $width + 1, ' ');

    // Whiskers (left and right ends)
    $plotArray[$positions['min']] = '|';
    $plotArray[$positions['max']] = '|';

    // Box (from Q1 to Q3)
    for ($i = $positions['q1']; $i <= $positions['q3']; $i++) {
        $plotArray[$i] = '#';
    }

    // Median line
    $plotArray[$positions['median']] = '|';

    $plot = implode('', $plotArray);
    $plot .= "\n";

    // Add labels
    $plot .= sprintf(
        "%-8s%s%-8s%s%-8s%s%-8s%s%-8s\n",
        $stats['min'],
        str_repeat(' ', max(0, $positions['q1'] - 8)),
        $stats['q1'],
        str_repeat(' ', max(0, $positions['median'] - $positions['q1'] - 8)),
        $stats['median'],
        str_repeat(' ', max(0, $positions['q3'] - $positions['median'] - 8)),
        $stats['q3'],
        str_repeat(' ', max(0, $positions['max'] - $positions['q3'] - 8)),
        $stats['max']
    );

    return $plot;
}

// Load customer data
echo "→ Loading customer data...\n";
$file = fopen(__DIR__ . '/data/customers.csv', 'r');
if ($file === false) {
    echo "Error: Could not open customers.csv\n";
    exit(1);
}

$headers = fgetcsv($file, 0, ',', '"', '\\');
$customers = [];
while (($row = fgetcsv($file, 0, ',', '"', '\\')) !== false) {
    $customers[] = array_combine($headers, $row);
}
fclose($file);

echo "✓ Loaded " . count($customers) . " customers\n\n";

echo str_repeat("=", 70) . "\n";
echo "Outlier Detection Examples\n";
echo str_repeat("=", 70) . "\n\n";

// Analyze avg_order_value column
$column = 'avg_order_value';

echo "Analyzing column: $column\n";
echo str_repeat("-", 70) . "\n\n";

// Calculate basic statistics
$stats = calculateStats($customers, $column);

echo "Basic Statistics:\n";
foreach ($stats as $key => $value) {
    $label = ucfirst(str_replace('_', ' ', $key));
    echo sprintf("  %-10s: %s\n", $label, $value);
}

echo "\nBox Plot Visualization:\n";
echo textBoxPlot($stats);
echo "\n\n";

// Method 1: Z-Score Detection
echo str_repeat("=", 70) . "\n";
echo "Method 1: Z-Score Outlier Detection\n";
echo str_repeat("=", 70) . "\n\n";

$zScoreOutliers = detectOutliersZScore($customers, $column, threshold: 2.5);

echo "Found " . count($zScoreOutliers) . " outliers using Z-score method (threshold=2.5)\n\n";

if (!empty($zScoreOutliers)) {
    echo "Outliers detected:\n";
    foreach (array_slice($zScoreOutliers, 0, 5, true) as $idx => $outlier) {
        echo sprintf(
            "  Row %d: %s %s - $%s (Z-score: %s)\n",
            $idx,
            $outlier['row']['first_name'],
            $outlier['row']['last_name'],
            number_format($outlier['value'], 2),
            $outlier['z_score']
        );
    }

    if (count($zScoreOutliers) > 5) {
        echo "  ... and " . (count($zScoreOutliers) - 5) . " more\n";
    }
}

// Method 2: IQR Detection
echo "\n" . str_repeat("=", 70) . "\n";
echo "Method 2: IQR Outlier Detection\n";
echo str_repeat("=", 70) . "\n\n";

$iqrOutliers = detectOutliersIQR($customers, $column, multiplier: 1.5);

echo "Found " . count($iqrOutliers) . " outliers using IQR method (multiplier=1.5)\n\n";

if (!empty($iqrOutliers)) {
    echo "Outliers detected:\n";
    foreach (array_slice($iqrOutliers, 0, 5, true) as $idx => $outlier) {
        $direction = $outlier['value'] < $outlier['lower_bound'] ? 'LOW' : 'HIGH';
        echo sprintf(
            "  Row %d: %s %s - $%s [%s] (bounds: $%s - $%s)\n",
            $idx,
            $outlier['row']['first_name'],
            $outlier['row']['last_name'],
            number_format($outlier['value'], 2),
            $direction,
            number_format($outlier['lower_bound'], 2),
            number_format($outlier['upper_bound'], 2)
        );
    }

    if (count($iqrOutliers) > 5) {
        echo "  ... and " . (count($iqrOutliers) - 5) . " more\n";
    }
}

// Comparison
echo "\n" . str_repeat("=", 70) . "\n";
echo "Comparing Detection Methods\n";
echo str_repeat("=", 70) . "\n\n";

echo "Z-Score Method:\n";
echo "  - Outliers found: " . count($zScoreOutliers) . "\n";
echo "  - Best for: Normally distributed data\n";
echo "  - Sensitive to: Extreme outliers affecting mean/std\n\n";

echo "IQR Method:\n";
echo "  - Outliers found: " . count($iqrOutliers) . "\n";
echo "  - Best for: Skewed distributions\n";
echo "  - Robust to: Extreme values (uses quartiles)\n";

// Handling Strategies
echo "\n" . str_repeat("=", 70) . "\n";
echo "Outlier Handling Strategies\n";
echo str_repeat("=", 70) . "\n\n";

// Strategy 1: Remove outliers
$cleanedData = removeOutliers($customers, array_keys($iqrOutliers));
echo "Strategy 1: Remove Outliers\n";
echo "  Original size: " . count($customers) . " rows\n";
echo "  After removal:  " . count($cleanedData) . " rows\n";
echo "  Removed:        " . (count($customers) - count($cleanedData)) . " rows (" .
    round((count($customers) - count($cleanedData)) / count($customers) * 100, 1) . "%)\n";

$statsAfterRemoval = calculateStats($cleanedData, $column);
echo "  New mean: $" . $statsAfterRemoval['mean'] . " (was $" . $stats['mean'] . ")\n";
echo "  New std:  $" . $statsAfterRemoval['std'] . " (was $" . $stats['std'] . ")\n\n";

// Strategy 2: Cap outliers
if (!empty($iqrOutliers)) {
    $firstOutlier = reset($iqrOutliers);
    $cappedData = capOutliers($customers, $column, $firstOutlier['lower_bound'], $firstOutlier['upper_bound']);

    echo "Strategy 2: Cap Outliers (Winsorization)\n";
    echo "  Capping range: $" . number_format($firstOutlier['lower_bound'], 2) .
        " - $" . number_format($firstOutlier['upper_bound'], 2) . "\n";
    echo "  Data size: " . count($cappedData) . " rows (unchanged)\n";

    $statsAfterCapping = calculateStats($cappedData, $column);
    echo "  New mean: $" . $statsAfterCapping['mean'] . " (was $" . $stats['mean'] . ")\n";
    echo "  New std:  $" . $statsAfterCapping['std'] . " (was $" . $stats['std'] . ")\n";
}

// Decision Guide
echo "\n" . str_repeat("=", 70) . "\n";
echo "When to Remove vs. Keep Outliers\n";
echo str_repeat("=", 70) . "\n\n";

echo "✓ REMOVE outliers when:\n";
echo "  - Data entry errors (e.g., age = 999)\n";
echo "  - Measurement errors\n";
echo "  - Not relevant to problem (e.g., celebrity purchases in typical customer analysis)\n";
echo "  - Would distort model training\n\n";

echo "✓ KEEP outliers when:\n";
echo "  - They represent real, important behavior\n";
echo "  - You're specifically trying to detect them (fraud, anomalies)\n";
echo "  - Removing would bias your analysis\n";
echo "  - Use robust methods instead (IQR, median, trimmed mean)\n\n";

echo "✓ CAP outliers when:\n";
echo "  - Want to reduce impact without losing data\n";
echo "  - Outliers are real but extreme\n";
echo "  - Need to preserve sample size\n";

echo "\n" . str_repeat("=", 70) . "\n";
echo "Key Takeaways\n";
echo str_repeat("=", 70) . "\n\n";

echo "1. Always visualize data before deciding on outliers\n";
echo "2. IQR method is more robust for skewed distributions\n";
echo "3. Z-score works well for normally distributed data\n";
echo "4. Consider domain knowledge - not all outliers are errors\n";
echo "5. Document your outlier handling strategy\n";
echo "6. Different columns may need different approaches\n";

echo "\n✓ Outlier detection complete!\n";
