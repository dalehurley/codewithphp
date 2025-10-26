<?php

declare(strict_types=1);

/**
 * Chapter 04: Data Collection and Preprocessing
 * Example 11: Save/Load Pipeline Parameters
 * 
 * Demonstrates: Parameter persistence for production deployment, applying transforms to new data
 */

require_once __DIR__ . '/09-preprocessing-pipeline.php';

echo "=" . str_repeat("=", 69) . "\n";
echo "Saving and Loading Preprocessing Parameters\n";
echo "=" . str_repeat("=", 69) . "\n\n";

echo "→ Scenario: Training phase\n";
echo "  1. Train pipeline on customer data\n";
echo "  2. Save preprocessing parameters\n";
echo "  3. Later, apply same transforms to new data\n\n";

// Phase 1: Train Pipeline on Training Data
echo str_repeat("-", 70) . "\n";
echo "Phase 1: Training Pipeline on Dataset\n";
echo str_repeat("-", 70) . "\n";

$trainingPipeline = new PreprocessingPipeline();

echo "→ Loading training data...\n";
$trainingPipeline
    ->load(__DIR__ . '/data/customers.csv', 'csv')
    ->handleMissing('age', 'mean')
    ->normalize('age', 'minmax')
    ->normalize('total_orders', 'zscore')
    ->encode('gender', 'label');

echo "✓ Training pipeline complete\n";
echo $trainingPipeline->summary();

// Save the parameters
$paramsPath = __DIR__ . '/processed/pipeline_parameters.json';
$trainingPipeline->saveParameters($paramsPath);
echo "\n✓ Parameters saved to: pipeline_parameters.json\n";

// Show what was saved
$params = $trainingPipeline->getParameters();
echo "\nSaved Parameters:\n";
foreach ($params as $key => $value) {
    echo "  - $key: " . json_encode($value) . "\n";
}

// Phase 2: Simulate New Data Arriving
echo "\n" . str_repeat("-", 70) . "\n";
echo "Phase 2: New Data Arrives (Production)\n";
echo str_repeat("-", 70) . "\n";

// Simulate new customer data (would normally come from API, database, etc.)
$newCustomers = [
    [
        'customer_id' => '101',
        'first_name' => 'New',
        'last_name' => 'Customer',
        'email' => 'new@example.com',
        'age' => '35',
        'gender' => 'Female',
        'city' => 'Miami',
        'country' => 'USA',
        'total_orders' => '15',
        'avg_order_value' => '95.50',
        'account_created' => '2024-01-15',
        'has_subscription' => '1',
        'is_active' => '1'
    ],
    [
        'customer_id' => '102',
        'first_name' => 'Another',
        'last_name' => 'User',
        'email' => 'another@example.com',
        'age' => '42',
        'gender' => 'Male',
        'city' => 'Boston',
        'country' => 'USA',
        'total_orders' => '25',
        'avg_order_value' => '125.00',
        'account_created' => '2024-01-20',
        'has_subscription' => '0',
        'is_active' => '1'
    ]
];

echo "\n→ New customers arrived:\n";
foreach ($newCustomers as $customer) {
    echo "  - {$customer['first_name']} {$customer['last_name']} (Age: {$customer['age']}, Gender: {$customer['gender']})\n";
}

// Phase 3: Apply Saved Parameters to New Data
echo "\n" . str_repeat("-", 70) . "\n";
echo "Phase 3: Applying Saved Parameters to New Data\n";
echo str_repeat("-", 70) . "\n";

// Load the saved parameters
$productionPipeline = new PreprocessingPipeline();
$productionPipeline->loadParameters($paramsPath);

echo "→ Loaded parameters from training phase\n";
$loadedParams = $productionPipeline->getParameters();

// Show we're using the SAME parameters as training
echo "\nApplying transformations with saved parameters:\n";

// Manually apply the saved transformations to demonstrate
foreach ($newCustomers as &$customer) {
    // Apply min-max normalization with saved min/max
    if (isset($loadedParams['minmax_age'])) {
        $min = $loadedParams['minmax_age']['min'];
        $max = $loadedParams['minmax_age']['max'];
        $customer['age_normalized'] = ((float)$customer['age'] - $min) / ($max - $min);
        echo "  - Age normalized using min=$min, max=$max\n";
    }

    // Apply z-score with saved mean/std
    if (isset($loadedParams['zscore_total_orders'])) {
        $mean = $loadedParams['zscore_total_orders']['mean'];
        $std = $loadedParams['zscore_total_orders']['std'];
        $customer['total_orders_standardized'] = ((float)$customer['total_orders'] - $mean) / $std;
        echo "  - Orders standardized using mean=$mean, std=" . round($std, 2) . "\n";
    }

    // Apply label encoding with saved mapping
    if (isset($loadedParams['label_gender'])) {
        $mapping = $loadedParams['label_gender']['mapping'];
        $customer['gender_encoded'] = $mapping[$customer['gender']] ?? -1; // -1 for unknown
        echo "  - Gender encoded using saved mapping\n";
    }
}
unset($customer);

echo "\n✓ New data preprocessed with consistent parameters!\n";

// Show results
echo "\nProcessed New Customers:\n";
foreach ($newCustomers as $customer) {
    echo "\n{$customer['first_name']} {$customer['last_name']}:\n";
    echo "  Original Age: {$customer['age']} → Normalized: " . round($customer['age_normalized'], 4) . "\n";
    echo "  Original Orders: {$customer['total_orders']} → Standardized: " . round($customer['total_orders_standardized'], 4) . "\n";
    echo "  Original Gender: {$customer['gender']} → Encoded: {$customer['gender_encoded']}\n";
}

// Key Takeaways
echo "\n" . str_repeat("=", 70) . "\n";
echo "Why This Matters for Production\n";
echo str_repeat("=", 70) . "\n\n";

echo "✓ Consistency: New data transformed exactly like training data\n";
echo "✓ No Data Leakage: Test/production data doesn't influence parameters\n";
echo "✓ Reproducibility: Same parameters = same results every time\n";
echo "✓ Versioning: Can track which parameter version was used\n";
echo "✓ Debugging: Easy to inspect and validate transformations\n";

echo "\n" . str_repeat("=", 70) . "\n";
echo "Production Checklist\n";
echo str_repeat("=", 70) . "\n\n";

echo "[ ] Save parameters after training pipeline\n";
echo "[ ] Version your parameter files (e.g., pipeline_v1.0.json)\n";
echo "[ ] Load parameters before processing new data\n";
echo "[ ] Handle unknown categories (new values not seen in training)\n";
echo "[ ] Monitor for data drift (distributions changing over time)\n";
echo "[ ] Document what each parameter file contains\n";

echo "\n✓ Pipeline parameter persistence complete!\n";
