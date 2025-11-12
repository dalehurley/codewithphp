<?php

declare(strict_types=1);

/**
 * Chapter 04: Data Collection and Preprocessing
 * Example 12: Feature Engineering Basics
 * 
 * Demonstrates: Creating derived features, binning, interactions, ratios
 */

/**
 * Bin a continuous variable into categorical ranges
 * 
 * @param array $data Dataset
 * @param string $column Column to bin
 * @param array $bins Bin edges (e.g., [0, 30, 45, 60, 100])
 * @param array $labels Labels for each bin
 * @return array Data with new binned column
 */
function binFeature(array $data, string $column, array $bins, array $labels): array
{
    if (count($bins) !== count($labels) + 1) {
        throw new InvalidArgumentException("Bins array must have exactly one more element than labels");
    }

    return array_map(function ($row) use ($column, $bins, $labels) {
        $value = (float)$row[$column];
        $binLabel = 'Unknown';

        for ($i = 0; $i < count($bins) - 1; $i++) {
            if ($value >= $bins[$i] && $value < $bins[$i + 1]) {
                $binLabel = $labels[$i];
                break;
            }
        }

        // Handle edge case: value equals max bin
        if ($value == $bins[count($bins) - 1]) {
            $binLabel = $labels[count($labels) - 1];
        }

        $row[$column . '_group'] = $binLabel;
        return $row;
    }, $data);
}

/**
 * Create interaction feature (product of two columns)
 * Captures non-linear relationships between features
 * 
 * @param array $data Dataset
 * @param string $col1 First column
 * @param string $col2 Second column
 * @param string $newName New feature name
 * @return array Data with new interaction column
 */
function createInteraction(array $data, string $col1, string $col2, string $newName): array
{
    return array_map(function ($row) use ($col1, $col2, $newName) {
        $val1 = is_numeric($row[$col1]) ? (float)$row[$col1] : 0;
        $val2 = is_numeric($row[$col2]) ? (float)$row[$col2] : 0;
        $row[$newName] = $val1 * $val2;
        return $row;
    }, $data);
}

/**
 * Create ratio feature (division of two columns)
 * Useful for creating relative measures
 * 
 * @param array $data Dataset
 * @param string $numerator Numerator column
 * @param string $denominator Denominator column
 * @param string $newName New feature name
 * @return array Data with new ratio column
 */
function createRatio(array $data, string $numerator, string $denominator, string $newName): array
{
    return array_map(function ($row) use ($numerator, $denominator, $newName) {
        $num = is_numeric($row[$numerator]) ? (float)$row[$numerator] : 0;
        $denom = is_numeric($row[$denominator]) ? (float)$row[$denominator] : 1;

        // Avoid division by zero
        $row[$newName] = $denom != 0 ? $num / $denom : 0;
        return $row;
    }, $data);
}

/**
 * Create polynomial features
 * Captures non-linear patterns
 * 
 * @param array $data Dataset
 * @param string $column Column to transform
 * @param int $degree Polynomial degree
 * @return array Data with polynomial features
 */
function createPolynomial(array $data, string $column, int $degree = 2): array
{
    return array_map(function ($row) use ($column, $degree) {
        $value = is_numeric($row[$column]) ? (float)$row[$column] : 0;

        for ($i = 2; $i <= $degree; $i++) {
            $row[$column . "_power$i"] = pow($value, $i);
        }

        return $row;
    }, $data);
}

/**
 * Create time-based features from date strings
 * 
 * @param array $data Dataset
 * @param string $dateColumn Column containing date strings
 * @return array Data with extracted time features
 */
function extractTimeFeatures(array $data, string $dateColumn): array
{
    return array_map(function ($row) use ($dateColumn) {
        $dateStr = $row[$dateColumn];
        $timestamp = strtotime($dateStr);

        if ($timestamp !== false) {
            $row[$dateColumn . '_year'] = (int)date('Y', $timestamp);
            $row[$dateColumn . '_month'] = (int)date('m', $timestamp);
            $row[$dateColumn . '_day'] = (int)date('d', $timestamp);
            $row[$dateColumn . '_weekday'] = (int)date('N', $timestamp); // 1=Monday, 7=Sunday

            // Calculate days since date
            $row[$dateColumn . '_days_ago'] = (int)((time() - $timestamp) / 86400);
        }

        return $row;
    }, $data);
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
echo "Feature Engineering Examples\n";
echo str_repeat("=", 70) . "\n\n";

// Example 1: Age Binning
echo "1. Age Binning: Group continuous ages into categories\n";
echo str_repeat("-", 70) . "\n";

$ageBins = [0, 30, 45, 60, 100];
$ageLabels = ['Young (0-29)', 'Middle (30-44)', 'Senior (45-59)', 'Elderly (60+)'];

$engineered = binFeature($customers, 'age', $ageBins, $ageLabels);

echo "Age groups created:\n";
$groupCounts = [];
foreach ($engineered as $customer) {
    $group = $customer['age_group'];
    $groupCounts[$group] = ($groupCounts[$group] ?? 0) + 1;
}

foreach ($groupCounts as $group => $count) {
    $pct = round($count / count($engineered) * 100, 1);
    echo "  - $group: $count customers ({$pct}%)\n";
}

echo "\nSample:\n";
foreach (array_slice($engineered, 0, 3) as $customer) {
    echo "  {$customer['first_name']}: Age {$customer['age']} → {$customer['age_group']}\n";
}

// Example 2: Interaction Features
echo "\n\n2. Interaction Feature: Subscription × Age\n";
echo str_repeat("-", 70) . "\n";
echo "Captures relationship: Do older subscribers behave differently?\n\n";

$engineered = createInteraction($engineered, 'has_subscription', 'age', 'sub_age_interaction');

echo "Sample interactions:\n";
foreach (array_slice($engineered, 0, 5) as $customer) {
    echo "  {$customer['first_name']}: Subscription({$customer['has_subscription']}) × Age({$customer['age']}) = {$customer['sub_age_interaction']}\n";
}

// Example 3: Ratio Features
echo "\n\n3. Ratio Feature: Average Order Value per Order\n";
echo str_repeat("-", 70) . "\n";
echo "Creates relative measure of customer spending behavior\n\n";

// First, calculate total order value (avg_order_value is per-order average)
$engineered = array_map(function ($row) {
    $row['total_order_value'] = (float)$row['avg_order_value'] * (float)$row['total_orders'];
    return $row;
}, $engineered);

$engineered = createRatio($engineered, 'total_order_value', 'total_orders', 'spending_per_order');

echo "Sample spending patterns:\n";
foreach (array_slice($engineered, 0, 5) as $customer) {
    echo sprintf(
        "  %s: $%s total / %s orders = $%.2f per order\n",
        $customer['first_name'],
        number_format($customer['total_order_value'], 2),
        $customer['total_orders'],
        $customer['spending_per_order']
    );
}

// Example 4: Polynomial Features
echo "\n\n4. Polynomial Features: Age Squared\n";
echo str_repeat("-", 70) . "\n";
echo "Captures non-linear age effects\n\n";

$engineered = createPolynomial($engineered, 'age', degree: 2);

echo "Sample polynomial features:\n";
foreach (array_slice($engineered, 0, 3) as $customer) {
    echo "  {$customer['first_name']}: Age={$customer['age']}, Age²={$customer['age_power2']}\n";
}

// Example 5: Time-based Features
echo "\n\n5. Time-based Features: Account Age\n";
echo str_repeat("-", 70) . "\n";
echo "Extract temporal patterns from dates\n\n";

$engineered = extractTimeFeatures($engineered, 'account_created');

echo "Sample time features:\n";
foreach (array_slice($engineered, 0, 3) as $customer) {
    echo "  {$customer['first_name']}: Created {$customer['account_created']}\n";
    echo "    → Year: {$customer['account_created_year']}, Month: {$customer['account_created_month']}\n";
    echo "    → Days ago: {$customer['account_created_days_ago']} days\n";
}

// Summary Statistics
echo "\n" . str_repeat("=", 70) . "\n";
echo "Feature Engineering Summary\n";
echo str_repeat("=", 70) . "\n\n";

$originalFeatures = count($customers[0]);
$newFeatures = count($engineered[0]);
$addedFeatures = $newFeatures - $originalFeatures;

echo "Original features:  $originalFeatures\n";
echo "Engineered features: $addedFeatures\n";
echo "Total features:      $newFeatures\n";

echo "\nNew features created:\n";
$newCols = array_diff(array_keys($engineered[0]), array_keys($customers[0]));
foreach ($newCols as $col) {
    echo "  - $col\n";
}

// Best Practices
echo "\n" . str_repeat("=", 70) . "\n";
echo "Feature Engineering Best Practices\n";
echo str_repeat("=", 70) . "\n\n";

echo "✓ Domain Knowledge: Use business understanding to create meaningful features\n";
echo "✓ Start Simple: Basic features (ratios, bins) often work well\n";
echo "✓ Test Impact: Measure if new features actually improve model performance\n";
echo "✓ Avoid Leakage: Don't use future information or target variable\n";
echo "✓ Document: Keep track of how each feature was created\n";
echo "✓ Consider Interpretability: Complex features may be harder to explain\n";

echo "\n" . str_repeat("=", 70) . "\n";
echo "When to Use Each Technique\n";
echo str_repeat("=", 70) . "\n\n";

echo "Binning:\n";
echo "  → When: Continuous variable has non-linear effects\n";
echo "  → Example: Age groups for different life stages\n\n";

echo "Interactions:\n";
echo "  → When: Two features combined create new meaning\n";
echo "  → Example: Premium product × high income = luxury segment\n\n";

echo "Ratios:\n";
echo "  → When: Relative measures matter more than absolutes\n";
echo "  → Example: Revenue per employee, clicks per impression\n\n";

echo "Polynomials:\n";
echo "  → When: Capturing curved/non-linear relationships\n";
echo "  → Example: Advertising returns diminish at high spend\n\n";

echo "Time Features:\n";
echo "  → When: Temporal patterns exist (seasonality, trends)\n";
echo "  → Example: Day of week affects sales, account age affects churn\n";

echo "\n✓ Feature engineering complete!\n";
