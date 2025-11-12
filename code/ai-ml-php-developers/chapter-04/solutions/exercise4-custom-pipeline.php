<?php

declare(strict_types=1);

/**
 * Chapter 04: Exercise 4 Solution
 * Build a Custom Preprocessing Pipeline
 * 
 * Goal: Create an end-to-end preprocessing pipeline for predicting total_orders
 */

/**
 * Simple preprocessing pipeline for regression task
 */
class RegressionPreprocessor
{
    private array $features = [];
    private array $target = [];
    private array $featureNames = [];

    /**
     * Load CSV data
     */
    public function loadData(string $csvPath, string $targetColumn): self
    {
        $file = fopen($csvPath, 'r');
        if ($file === false) {
            throw new RuntimeException("Could not open file: $csvPath");
        }

        $headers = fgetcsv($file, 0, ',', '"', '\\');
        $data = [];
        while (($row = fgetcsv($file, 0, ',', '"', '\\')) !== false) {
            $data[] = array_combine($headers, $row);
        }
        fclose($file);

        // Separate features and target
        foreach ($data as $row) {
            // Extract target
            $this->target[] = isset($row[$targetColumn]) ? (float)$row[$targetColumn] : 0;

            // Remove target and ID columns from features
            unset($row[$targetColumn], $row['customer_id']);

            $this->features[] = $row;
        }

        return $this;
    }

    /**
     * Impute missing numeric values with mean
     */
    public function imputeNumeric(string $column): self
    {
        $values = array_filter(
            array_column($this->features, $column),
            fn($v) => $v !== null && $v !== '' && is_numeric($v)
        );

        if (empty($values)) {
            return $this;
        }

        $mean = array_sum(array_map('floatval', $values)) / count($values);

        for ($i = 0; $i < count($this->features); $i++) {
            if (!isset($this->features[$i][$column]) || $this->features[$i][$column] === '' || $this->features[$i][$column] === null) {
                $this->features[$i][$column] = $mean;
            } else {
                $this->features[$i][$column] = (float)$this->features[$i][$column];
            }
        }

        return $this;
    }

    /**
     * Min-max normalize numeric column
     */
    public function normalizeColumn(string $column): self
    {
        $values = array_column($this->features, $column);
        $min = min($values);
        $max = max($values);

        if ($max === $min) {
            return $this;
        }

        for ($i = 0; $i < count($this->features); $i++) {
            $this->features[$i][$column] = ($this->features[$i][$column] - $min) / ($max - $min);
        }

        return $this;
    }

    /**
     * One-hot encode categorical column
     */
    public function oneHotEncode(string $column): self
    {
        $uniqueValues = array_unique(array_column($this->features, $column));
        sort($uniqueValues);

        for ($i = 0; $i < count($this->features); $i++) {
            $originalValue = $this->features[$i][$column];

            foreach ($uniqueValues as $value) {
                $colName = $column . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', (string)$value);
                $this->features[$i][$colName] = ($originalValue === $value) ? 1 : 0;
            }

            // Remove original column
            unset($this->features[$i][$column]);
        }

        return $this;
    }

    /**
     * Remove non-numeric columns
     */
    public function removeNonNumeric(): self
    {
        $numericData = [];

        foreach ($this->features as $row) {
            $numericRow = [];
            foreach ($row as $key => $value) {
                if (is_numeric($value) || $value === 0 || $value === 1) {
                    $numericRow[$key] = (float)$value;
                }
            }
            $numericData[] = $numericRow;
        }

        $this->features = $numericData;
        return $this;
    }

    /**
     * Get processed features
     */
    public function getFeatures(): array
    {
        return $this->features;
    }

    /**
     * Get target values
     */
    public function getTarget(): array
    {
        return $this->target;
    }

    /**
     * Train/test split
     * 
     * @param float $testSize Proportion for test set (0.0 to 1.0)
     * @return array ['train_features', 'train_target', 'test_features', 'test_target']
     */
    public function trainTestSplit(float $testSize = 0.2): array
    {
        $totalSize = count($this->features);
        $testCount = (int)ceil($totalSize * $testSize);
        $trainCount = $totalSize - $testCount;

        // Shuffle indices
        $indices = range(0, $totalSize - 1);
        shuffle($indices);

        $trainIndices = array_slice($indices, 0, $trainCount);
        $testIndices = array_slice($indices, $trainCount);

        return [
            'train_features' => array_map(fn($i) => $this->features[$i], $trainIndices),
            'train_target' => array_map(fn($i) => $this->target[$i], $trainIndices),
            'test_features' => array_map(fn($i) => $this->features[$i], $testIndices),
            'test_target' => array_map(fn($i) => $this->target[$i], $testIndices),
            'train_size' => $trainCount,
            'test_size' => $testCount
        ];
    }

    /**
     * Save features and target to separate files
     */
    public function save(string $featuresPath, string $targetPath): void
    {
        // Ensure directory exists
        $dir = dirname($featuresPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($featuresPath, json_encode($this->features, JSON_PRETTY_PRINT));
        file_put_contents($targetPath, json_encode($this->target, JSON_PRETTY_PRINT));
    }

    /**
     * Display summary
     */
    public function summary(): void
    {
        $sampleRow = $this->features[0] ?? [];
        $featureCount = count($sampleRow);
        $rowCount = count($this->features);

        echo "Pipeline Summary:\n";
        echo "  - Rows: $rowCount\n";
        echo "  - Features: $featureCount\n";
        echo "  - Feature names: " . implode(', ', array_keys($sampleRow)) . "\n";
        echo "  - Target values: " . count($this->target) . "\n";
        echo "  - All features numeric: " . (count(array_filter(array_values($sampleRow), 'is_numeric')) === count($sampleRow) ? "Yes" : "No") . "\n";
    }
}

// Build the pipeline
echo "→ Building custom preprocessing pipeline for regression task\n";
echo "→ Target variable: total_orders\n\n";

$preprocessor = new RegressionPreprocessor();

// Step 1: Load data
echo "Step 1: Loading data...\n";
$preprocessor->loadData(__DIR__ . '/../data/customers.csv', 'total_orders');
echo "  ✓ Data loaded\n\n";

// Step 2: Handle missing values
echo "Step 2: Handling missing values...\n";
$preprocessor
    ->imputeNumeric('age')
    ->imputeNumeric('avg_order_value');
echo "  ✓ Imputed: age, avg_order_value\n\n";

// Step 3: Normalize numeric features
echo "Step 3: Normalizing numeric features...\n";
$preprocessor
    ->normalizeColumn('age')
    ->normalizeColumn('avg_order_value');
echo "  ✓ Normalized: age, avg_order_value\n\n";

// Step 4: One-hot encode categorical features
echo "Step 4: One-hot encoding categorical features...\n";
$preprocessor
    ->oneHotEncode('gender')
    ->oneHotEncode('country');
echo "  ✓ Encoded: gender, country\n\n";

// Step 5: Remove remaining non-numeric columns
echo "Step 5: Removing non-numeric columns...\n";
$preprocessor->removeNonNumeric();
echo "  ✓ Non-numeric columns removed\n\n";

// Display summary
echo str_repeat("=", 60) . "\n";
$preprocessor->summary();
echo str_repeat("=", 60) . "\n\n";

// Step 6: Train/Test Split
echo "Step 6: Splitting into train and test sets...\n";
$split = $preprocessor->trainTestSplit(testSize: 0.2);

echo "  ✓ Training set: {$split['train_size']} samples (" .
    round($split['train_size'] / ($split['train_size'] + $split['test_size']) * 100, 1) . "%)\n";
echo "  ✓ Test set:     {$split['test_size']} samples (" .
    round($split['test_size'] / ($split['train_size'] + $split['test_size']) * 100, 1) . "%)\n\n";

// Save train and test sets separately
$processedDir = dirname(__DIR__) . '/processed';

// Ensure directory exists
if (!is_dir($processedDir)) {
    mkdir($processedDir, 0755, true);
}

file_put_contents(
    $processedDir . '/exercise4_train_features.json',
    json_encode($split['train_features'], JSON_PRETTY_PRINT)
);
file_put_contents(
    $processedDir . '/exercise4_train_target.json',
    json_encode($split['train_target'], JSON_PRETTY_PRINT)
);
file_put_contents(
    $processedDir . '/exercise4_test_features.json',
    json_encode($split['test_features'], JSON_PRETTY_PRINT)
);
file_put_contents(
    $processedDir . '/exercise4_test_target.json',
    json_encode($split['test_target'], JSON_PRETTY_PRINT)
);

echo "✓ Training data saved:\n";
echo "  - processed/exercise4_train_features.json\n";
echo "  - processed/exercise4_train_target.json\n";
echo "✓ Test data saved:\n";
echo "  - processed/exercise4_test_features.json\n";
echo "  - processed/exercise4_test_target.json\n";

// Display sample
echo "\nSample from TRAINING set (first 2 rows):\n";
for ($i = 0; $i < min(2, count($split['train_features'])); $i++) {
    echo "\nRow $i:\n";
    $featureSubset = array_slice($split['train_features'][$i], 0, 5);
    echo "  Features: " . json_encode($featureSubset) . " ... (" . count($split['train_features'][$i]) . " total)\n";
    echo "  Target (total_orders): " . $split['train_target'][$i] . "\n";
}

echo "\nSample from TEST set (first 2 rows):\n";
for ($i = 0; $i < min(2, count($split['test_features'])); $i++) {
    echo "\nRow $i:\n";
    $featureSubset = array_slice($split['test_features'][$i], 0, 5);
    echo "  Features: " . json_encode($featureSubset) . " ... (" . count($split['test_features'][$i]) . " total)\n";
    echo "  Target (total_orders): " . $split['test_target'][$i] . "\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "✓ Exercise 4 complete!\n";
echo str_repeat("=", 60) . "\n";
echo "\n✓ Data is now ready for machine learning in Chapter 05!\n";
echo "✓ No data leakage: preprocessing done before split\n";
