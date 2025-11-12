<?php

declare(strict_types=1);

/**
 * Chapter 04: Data Collection and Preprocessing
 * Example 10: Train/Test Split
 * 
 * Demonstrates: Proper data splitting, preventing data leakage, stratified splits
 */

/**
 * Split data into training and testing sets
 * 
 * @param array $data Full dataset
 * @param float $testSize Proportion for test set (0.0 to 1.0)
 * @param bool $shuffle Whether to shuffle before splitting
 * @param int|null $randomSeed Optional seed for reproducibility
 * @return array ['train' => [...], 'test' => [...]]
 */
function trainTestSplit(
    array $data,
    float $testSize = 0.2,
    bool $shuffle = true,
    ?int $randomSeed = null
): array {
    if ($testSize <= 0 || $testSize >= 1) {
        throw new InvalidArgumentException("Test size must be between 0 and 1");
    }

    if (empty($data)) {
        throw new InvalidArgumentException("Dataset cannot be empty");
    }

    // Create copy to avoid modifying original
    $dataCopy = $data;

    if ($shuffle) {
        if ($randomSeed !== null) {
            mt_srand($randomSeed);
        }
        shuffle($dataCopy);
    }

    $totalSize = count($dataCopy);
    $testCount = (int)ceil($totalSize * $testSize);
    $trainCount = $totalSize - $testCount;

    return [
        'train' => array_slice($dataCopy, 0, $trainCount),
        'test' => array_slice($dataCopy, $trainCount),
        'train_size' => $trainCount,
        'test_size' => $testCount
    ];
}

/**
 * Three-way split for train/validation/test
 * 
 * @param array $data Full dataset
 * @param float $validSize Proportion for validation set
 * @param float $testSize Proportion for test set
 * @param int|null $randomSeed Optional seed for reproducibility
 * @return array ['train' => [...], 'validation' => [...], 'test' => [...]]
 */
function trainValidationTestSplit(
    array $data,
    float $validSize = 0.15,
    float $testSize = 0.15,
    ?int $randomSeed = null
): array {
    if ($validSize + $testSize >= 1) {
        throw new InvalidArgumentException("Validation + test size must be less than 1");
    }

    if (empty($data)) {
        throw new InvalidArgumentException("Dataset cannot be empty");
    }

    // Shuffle data
    $dataCopy = $data;
    if ($randomSeed !== null) {
        mt_srand($randomSeed);
    }
    shuffle($dataCopy);

    $totalSize = count($dataCopy);
    $testCount = (int)ceil($totalSize * $testSize);
    $validCount = (int)ceil($totalSize * $validSize);
    $trainCount = $totalSize - $testCount - $validCount;

    return [
        'train' => array_slice($dataCopy, 0, $trainCount),
        'validation' => array_slice($dataCopy, $trainCount, $validCount),
        'test' => array_slice($dataCopy, $trainCount + $validCount),
        'train_size' => $trainCount,
        'validation_size' => $validCount,
        'test_size' => $testCount
    ];
}

/**
 * Stratified split - maintains class distribution in splits
 * Useful for imbalanced classification problems
 * 
 * @param array $data Dataset
 * @param string $targetColumn Column name containing class labels
 * @param float $testSize Test set proportion
 * @return array ['train' => [...], 'test' => [...]]
 */
function stratifiedTrainTestSplit(
    array $data,
    string $targetColumn,
    float $testSize = 0.2
): array {
    if (empty($data)) {
        throw new InvalidArgumentException("Dataset cannot be empty");
    }

    // Group data by class
    $classGroups = [];
    foreach ($data as $row) {
        $classLabel = $row[$targetColumn];
        $classGroups[$classLabel][] = $row;
    }

    $train = [];
    $test = [];

    // Split each class proportionally
    foreach ($classGroups as $classLabel => $classData) {
        shuffle($classData);
        $classTestSize = (int)ceil(count($classData) * $testSize);
        $classTrainSize = count($classData) - $classTestSize;

        $train = array_merge($train, array_slice($classData, 0, $classTrainSize));
        $test = array_merge($test, array_slice($classData, $classTrainSize));
    }

    // Shuffle final splits to mix classes
    shuffle($train);
    shuffle($test);

    return [
        'train' => $train,
        'test' => $test,
        'train_size' => count($train),
        'test_size' => count($test),
        'classes' => array_keys($classGroups)
    ];
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

// Example 1: Simple 80/20 split
echo str_repeat("=", 70) . "\n";
echo "Example 1: Simple 80/20 Train/Test Split\n";
echo str_repeat("=", 70) . "\n";

$split1 = trainTestSplit($customers, testSize: 0.2, randomSeed: 42);

echo "Split sizes:\n";
echo "  Training set:   {$split1['train_size']} samples (" . round($split1['train_size'] / count($customers) * 100, 1) . "%)\n";
echo "  Test set:       {$split1['test_size']} samples (" . round($split1['test_size'] / count($customers) * 100, 1) . "%)\n";

// Show sample from each set
echo "\nSample from training set:\n";
foreach (array_slice($split1['train'], 0, 2) as $customer) {
    echo "  - {$customer['first_name']} {$customer['last_name']} (Age: {$customer['age']})\n";
}

echo "\nSample from test set:\n";
foreach (array_slice($split1['test'], 0, 2) as $customer) {
    echo "  - {$customer['first_name']} {$customer['last_name']} (Age: {$customer['age']})\n";
}

// Example 2: Three-way split (70/15/15)
echo "\n" . str_repeat("=", 70) . "\n";
echo "Example 2: Three-Way Split (Train/Validation/Test)\n";
echo str_repeat("=", 70) . "\n";

$split2 = trainValidationTestSplit($customers, validSize: 0.15, testSize: 0.15, randomSeed: 42);

echo "Split sizes:\n";
echo "  Training set:   {$split2['train_size']} samples (" . round($split2['train_size'] / count($customers) * 100, 1) . "%)\n";
echo "  Validation set: {$split2['validation_size']} samples (" . round($split2['validation_size'] / count($customers) * 100, 1) . "%)\n";
echo "  Test set:       {$split2['test_size']} samples (" . round($split2['test_size'] / count($customers) * 100, 1) . "%)\n";

echo "\nWhy use validation set?\n";
echo "  - Training: Learn model parameters\n";
echo "  - Validation: Tune hyperparameters, select best model\n";
echo "  - Test: Final evaluation on completely unseen data\n";

// Example 3: Stratified split
echo "\n" . str_repeat("=", 70) . "\n";
echo "Example 3: Stratified Split (Maintains Class Distribution)\n";
echo str_repeat("=", 70) . "\n";

$split3 = stratifiedTrainTestSplit($customers, 'has_subscription', testSize: 0.2);

echo "Split sizes:\n";
echo "  Training set:   {$split3['train_size']} samples\n";
echo "  Test set:       {$split3['test_size']} samples\n";
echo "  Classes found:  " . implode(', ', $split3['classes']) . "\n";

// Check class distribution
$trainSubs = array_filter($split3['train'], fn($c) => $c['has_subscription'] === '1');
$testSubs = array_filter($split3['test'], fn($c) => $c['has_subscription'] === '1');

echo "\nClass distribution (has_subscription = 1):\n";
echo "  Training:  " . count($trainSubs) . " / {$split3['train_size']} = " .
    round(count($trainSubs) / $split3['train_size'] * 100, 1) . "%\n";
echo "  Test:      " . count($testSubs) . " / {$split3['test_size']} = " .
    round(count($testSubs) / $split3['test_size'] * 100, 1) . "%\n";
echo "  → Distributions are similar (stratification working!)\n";

// Data Leakage Prevention
echo "\n" . str_repeat("=", 70) . "\n";
echo "Preventing Data Leakage\n";
echo str_repeat("=", 70) . "\n";

echo "\n❌ WRONG: Don't do this\n";
echo "  1. Normalize entire dataset\n";
echo "  2. Then split into train/test\n";
echo "  → Test data \"leaks\" into training via normalization parameters!\n";

echo "\n✓ CORRECT: Do this\n";
echo "  1. Split data first\n";
echo "  2. Calculate normalization parameters from TRAINING data only\n";
echo "  3. Apply those same parameters to test data\n";
echo "  → Test data remains truly unseen\n";

echo "\n" . str_repeat("=", 70) . "\n";
echo "Key Takeaways\n";
echo str_repeat("=", 70) . "\n";

echo "\n1. Always split BEFORE preprocessing (except basic cleaning)\n";
echo "2. Use stratified split for classification with imbalanced classes\n";
echo "3. Use validation set when tuning hyperparameters\n";
echo "4. Set random seed for reproducibility\n";
echo "5. Never let test data influence training in any way\n";

echo "\n✓ Train/test splitting complete!\n";
