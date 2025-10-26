<?php

declare(strict_types=1);

/**
 * Example 3: Feature Extraction and Engineering
 * 
 * Demonstrates how to transform raw data (text, dates, etc.) into
 * numeric features that machine learning algorithms can understand.
 */

echo "=== Feature Extraction and Engineering ===\n\n";

// Example 1: Text to Features
echo "Example 1: Extracting Features from Text\n";
echo str_repeat('-', 50) . "\n";

$emails = [
    "Get FREE money NOW! Click here!!!",
    "Meeting scheduled for tomorrow at 3pm",
    "URGENT: You won a prize! Act now!!",
    "Please review the attached document",
];

/**
 * Extract features from email text
 */
function extractEmailFeatures(string $email): array
{
    $lower = strtolower($email);

    return [
        'has_free' => str_contains($lower, 'free') ? 1 : 0,
        'has_money' => str_contains($lower, 'money') ? 1 : 0,
        'has_urgent' => str_contains($lower, 'urgent') ? 1 : 0,
        'has_click' => str_contains($lower, 'click') ? 1 : 0,
        'has_won' => str_contains($lower, 'won') ? 1 : 0,
        'exclamation_count' => substr_count($email, '!'),
        'capital_ratio' => calculateCapitalRatio($email),
        'word_count' => str_word_count($email),
    ];
}

/**
 * Calculate ratio of capital letters
 */
function calculateCapitalRatio(string $text): float
{
    $letters = preg_replace('/[^A-Za-z]/', '', $text);
    if (strlen($letters) === 0) {
        return 0.0;
    }

    $capitals = preg_replace('/[^A-Z]/', '', $text);
    return strlen($capitals) / strlen($letters);
}

foreach ($emails as $index => $email) {
    echo "\nEmail " . ($index + 1) . ": \"$email\"\n";
    $features = extractEmailFeatures($email);

    echo "  Features extracted:\n";
    foreach ($features as $name => $value) {
        $formatted = is_float($value) ? number_format($value, 2) : $value;
        echo "    - {$name}: {$formatted}\n";
    }
}

// Example 2: Numeric Feature Engineering
echo "\n\n" . str_repeat('=', 50) . "\n";
echo "Example 2: Engineering Features from Numeric Data\n";
echo str_repeat('-', 50) . "\n";

$customers = [
    ['age' => 25, 'income' => 35000, 'purchases' => 5],
    ['age' => 45, 'income' => 85000, 'purchases' => 15],
    ['age' => 35, 'income' => 60000, 'purchases' => 8],
];

/**
 * Engineer additional features from raw data
 */
function engineerCustomerFeatures(array $customer): array
{
    return [
        // Original features
        'age' => $customer['age'],
        'income' => $customer['income'],
        'purchases' => $customer['purchases'],

        // Engineered features
        'age_group' => getAgeGroup($customer['age']),
        'income_per_purchase' => $customer['income'] / max($customer['purchases'], 1),
        'purchase_frequency' => $customer['purchases'] / 12, // per month
        'is_high_value' => ($customer['income'] > 70000 && $customer['purchases'] > 10) ? 1 : 0,
    ];
}

function getAgeGroup(int $age): int
{
    if ($age < 30) return 1;      // Young
    if ($age < 50) return 2;      // Middle-aged
    return 3;                     // Senior
}

echo "\nCustomer Feature Engineering:\n";
foreach ($customers as $index => $customer) {
    echo "\nCustomer " . ($index + 1) . ":\n";
    echo "  Raw data: Age={$customer['age']}, Income=\${$customer['income']}, Purchases={$customer['purchases']}\n";

    $engineered = engineerCustomerFeatures($customer);
    echo "  Engineered features:\n";
    echo "    - age_group: {$engineered['age_group']}\n";
    echo "    - income_per_purchase: $" . number_format($engineered['income_per_purchase'], 2) . "\n";
    echo "    - purchase_frequency: " . number_format($engineered['purchase_frequency'], 2) . "/month\n";
    echo "    - is_high_value: {$engineered['is_high_value']}\n";
}

// Example 3: Feature Normalization
echo "\n\n" . str_repeat('=', 50) . "\n";
echo "Example 3: Normalizing Features to [0, 1] Range\n";
echo str_repeat('-', 50) . "\n";

$rawFeatures = [
    [25, 35000, 5],
    [45, 85000, 15],
    [35, 60000, 8],
    [55, 120000, 20],
];

echo "\nRaw features (Age, Income, Purchases):\n";
foreach ($rawFeatures as $index => $features) {
    echo "  Customer " . ($index + 1) . ": [" . implode(', ', $features) . "]\n";
}

/**
 * Normalize features using min-max scaling
 * Formula: x_scaled = (x - min) / (max - min)
 */
function normalizeFeatures(array $data): array
{
    $numFeatures = count($data[0]);
    $normalized = [];

    // Calculate min and max for each feature
    for ($featureIndex = 0; $featureIndex < $numFeatures; $featureIndex++) {
        $column = array_column($data, $featureIndex);
        $min = min($column);
        $max = max($column);
        $range = $max - $min;

        echo "\nFeature {$featureIndex}: min={$min}, max={$max}, range={$range}\n";

        // Normalize each value
        foreach ($data as $sampleIndex => $sample) {
            $value = $sample[$featureIndex];
            $normalizedValue = $range > 0 ? ($value - $min) / $range : 0.5;
            $normalized[$sampleIndex][$featureIndex] = $normalizedValue;
        }
    }

    return $normalized;
}

$normalizedFeatures = normalizeFeatures($rawFeatures);

echo "\nNormalized features (all in [0, 1] range):\n";
foreach ($normalizedFeatures as $index => $features) {
    $formatted = array_map(fn($f) => number_format($f, 3), $features);
    echo "  Customer " . ($index + 1) . ": [" . implode(', ', $formatted) . "]\n";
}

echo "\n" . str_repeat('=', 50) . "\n";
echo "Why Feature Engineering Matters:\n";
echo str_repeat('=', 50) . "\n";
echo "1. ML algorithms need numeric features (not text or dates)\n";
echo "2. Features on different scales (age vs income) need normalization\n";
echo "3. New derived features can capture complex relationships\n";
echo "4. Good features = better model performance\n\n";

echo "Key Techniques:\n";
echo "- Extraction: Raw data → numeric values\n";
echo "- Engineering: Create new features from existing ones\n";
echo "- Normalization: Scale features to similar ranges\n";
echo "- Encoding: Convert categories to numbers\n";
