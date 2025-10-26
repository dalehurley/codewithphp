<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Datasets\Unlabeled;
use Rubix\ML\Transformers\NumericStringConverter;
use Rubix\ML\Transformers\MissingDataImputer;
use Rubix\ML\Transformers\OneHotEncoder;
use Rubix\ML\Transformers\MinMaxNormalizer;
use Rubix\ML\Transformers\ZScaleStandardizer;
use Rubix\ML\Transformers\VarianceThresholdFilter;
use Rubix\ML\Pipeline;
use Rubix\ML\Classifiers\RandomForest;

echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║         Loading Real Data with Rubix ML                 ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

// ============================================================
// STEP 1: Load Data from CSV
// ============================================================

echo "STEP 1: Loading Data from CSV File\n";
echo "------------------------------------------------------------\n";

// Create sample CSV for demonstration
$csvData = <<<CSV
sepal_length,sepal_width,petal_length,petal_width,species
5.1,3.5,1.4,0.2,Iris-setosa
4.9,3.0,1.4,0.2,Iris-setosa
7.0,3.2,4.7,1.4,Iris-versicolor
6.4,3.2,4.5,1.5,Iris-versicolor
6.3,3.3,6.0,2.5,Iris-virginica
5.8,2.7,5.1,1.9,Iris-virginica
CSV;

if (!is_dir(__DIR__ . '/data')) {
    mkdir(__DIR__ . '/data', 0755, true);
}
file_put_contents(__DIR__ . '/data/iris_sample.csv', $csvData);

// Load CSV with Rubix ML
$dataset = Labeled::fromCSV(__DIR__ . '/data/iris_sample.csv', hasHeader: true);

echo "✓ CSV loaded successfully\n";
echo "  Samples: " . $dataset->numSamples() . "\n";
echo "  Features: " . $dataset->numFeatures() . "\n";
echo "  Labels: " . implode(', ', array_unique($dataset->labels())) . "\n\n";

// Display first few samples
echo "First 3 samples:\n";
for ($i = 0; $i < min(3, $dataset->numSamples()); $i++) {
    $sample = $dataset->sample($i);
    $label = $dataset->label($i);
    echo "  Sample " . ($i + 1) . ": [" . implode(', ', $sample) . "] → {$label}\n";
}
echo "\n";

// ============================================================
// STEP 2: Demonstrate Individual Transformers
// ============================================================

echo "STEP 2: Transformer Showcase\n";
echo "------------------------------------------------------------\n\n";

// Prepare sample data with issues to demonstrate transformers
$messyData = new Labeled(
    [
        ['5.1', '3.5', 1.4, 0.2, 'red'],      // Mix of strings and numbers
        [4.9, null, 1.4, 0.2, 'blue'],        // Missing value
        ['7.0', '3.2', 4.7, 1.4, 'red'],
        [6.4, 3.2, null, 1.5, 'green'],       // Missing value
    ],
    ['setosa', 'setosa', 'versicolor', 'versicolor']
);

echo "Original messy data:\n";
echo "  Sample types: " . gettype($messyData->sample(0)[0]) . " (should be float)\n";
echo "  Has nulls: Yes (sample 2 and 4)\n";
echo "  Has categorical: Yes (color column)\n\n";

// Transformer 1: NumericStringConverter
echo "1. NumericStringConverter\n";
echo "   Purpose: Convert string numbers to actual floats\n";
$converter = new NumericStringConverter();
$converter->fit($messyData);
$messyData->apply($converter);
echo "   ✓ Strings converted: '5.1' → 5.1\n\n";

// Transformer 2: MissingDataImputer
echo "2. MissingDataImputer\n";
echo "   Purpose: Fill missing values with mean/median/mode\n";
$imputer = new MissingDataImputer('mean');  // Options: 'mean', 'median', 'mode'
$imputer->fit($messyData);
$messyData->apply($imputer);
echo "   ✓ Null values filled with column means\n\n";

// Transformer 3: OneHotEncoder
echo "3. OneHotEncoder\n";
echo "   Purpose: Convert categorical values to binary columns\n";
echo "   Before: ['red', 'blue', 'green']\n";
echo "   After: [[1,0,0], [0,1,0], [0,0,1]]\n";
$encoder = new OneHotEncoder();
$encoder->fit($messyData);
$messyData->apply($encoder);
echo "   ✓ Categorical column expanded to " . ($messyData->numFeatures() - 4) . " binary columns\n\n";

// Transformer 4: MinMaxNormalizer
echo "4. MinMaxNormalizer\n";
echo "   Purpose: Scale features to [0, 1] range\n";
echo "   Formula: (x - min) / (max - min)\n";
$minmax = new MinMaxNormalizer();
$minmax->fit($messyData);
$messyData->apply($minmax);
echo "   ✓ All features now in [0.0, 1.0] range\n\n";

// Transformer 5: ZScaleStandardizer (alternative to MinMax)
echo "5. ZScaleStandardizer (Alternative Normalization)\n";
echo "   Purpose: Standardize features (mean=0, std=1)\n";
echo "   Formula: (x - μ) / σ\n";
echo "   Use when: Features have different scales, outliers present\n";
echo "   ✓ Would center features around 0 with unit variance\n\n";

// Transformer 6: VarianceThresholdFilter
echo "6. VarianceThresholdFilter\n";
echo "   Purpose: Remove low-variance (nearly constant) features\n";
echo "   Use when: Dataset has useless features that don't vary\n";
echo "   ✓ Automatically removes features with variance < threshold\n\n";

echo "Transformed dataset:\n";
echo "  Samples: " . $messyData->numSamples() . "\n";
echo "  Features: " . $messyData->numFeatures() . " (increased due to one-hot encoding)\n";
echo "  All numeric: Yes\n";
echo "  No missing values: Yes\n";
echo "  Normalized: Yes\n\n";

// ============================================================
// STEP 3: Production Pipeline with Chained Transformers
// ============================================================

echo "STEP 3: Complete Preprocessing Pipeline\n";
echo "------------------------------------------------------------\n";

// Load fresh data
$rawDataset = Labeled::fromCSV(__DIR__ . '/data/iris_sample.csv', hasHeader: true);

// Create pipeline with multiple transformers
$pipeline = new Pipeline([
    new NumericStringConverter(),      // 1. Fix type issues
    new MissingDataImputer('mean'),    // 2. Handle missing values
    new MinMaxNormalizer(),            // 3. Normalize to [0,1]
], new RandomForest(estimators: 10));

echo "Pipeline created with 3 transformers + Random Forest classifier\n\n";

echo "Transformer order (CRITICAL):\n";
echo "  1. NumericStringConverter    - Must be first (convert before math)\n";
echo "  2. MissingDataImputer        - Fill nulls before normalization\n";
echo "  3. MinMaxNormalizer          - Last preprocessing step\n";
echo "  4. RandomForest              - Final estimator\n\n";

echo "Why order matters:\n";
echo "  ✗ Normalize → Impute: NaN values break normalization\n";
echo "  ✗ Impute → Convert: Can't calculate mean of strings\n";
echo "  ✓ Convert → Impute → Normalize: Correct order\n\n";

// Train pipeline (all transformers auto-fit on training data)
[$training, $testing] = $rawDataset->randomize()->split(0.8);

$trainStart = microtime(true);
$pipeline->train($training);
$trainTime = (microtime(true) - $trainStart) * 1000;

echo "✓ Pipeline trained in " . number_format($trainTime, 2) . " ms\n";
echo "  - Transformers fitted to training data only\n";
echo "  - Test data will use same transformation parameters\n\n";

// Make predictions
$predictions = $pipeline->predict($testing);

echo "✓ Predictions made on test set\n";
echo "  Test samples: " . $testing->numSamples() . "\n";

// Calculate accuracy
$correct = 0;
foreach ($predictions as $i => $pred) {
    if ($pred === $testing->label($i)) {
        $correct++;
    }
}
$accuracy = ($correct / count($predictions)) * 100;

echo "  Accuracy: " . number_format($accuracy, 1) . "%\n\n";

// ============================================================
// STEP 4: Loading from Database
// ============================================================

echo "STEP 4: Loading Data from Database\n";
echo "------------------------------------------------------------\n";

echo "Example: MySQL/PostgreSQL Loading\n\n";

echo "```php\n";
echo "// Connect to database\n";
echo "\$pdo = new PDO(\n";
echo "    'mysql:host=localhost;dbname=ml_data',\n";
echo "    'username',\n";
echo "    'password',\n";
echo "    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]\n";
echo ");\n\n";

echo "// Query training data\n";
echo "\$stmt = \$pdo->query('\n";
echo "    SELECT feature1, feature2, feature3, label\n";
echo "    FROM training_data\n";
echo "    WHERE created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)\n";
echo "');\n\n";

echo "// Build dataset\n";
echo "\$samples = [];\n";
echo "\$labels = [];\n";
echo "while (\$row = \$stmt->fetch(PDO::FETCH_NUM)) {\n";
echo "    \$samples[] = array_slice(\$row, 0, -1);  // All but last column\n";
echo "    \$labels[] = \$row[count(\$row) - 1];     // Last column\n";
echo "}\n\n";

echo "\$dataset = new Labeled(\$samples, \$labels);\n";
echo "echo \"Loaded \" . count(\$samples) . \" samples from database\\n\";\n";
echo "```\n\n";

echo "Benefits:\n";
echo "  ✓ Load only recent data (avoid stale training data)\n";
echo "  ✓ Filter by conditions (WHERE clauses)\n";
echo "  ✓ Join multiple tables for features\n";
echo "  ✓ Stream large datasets (fetchAll vs fetch loop)\n\n";

// ============================================================
// STEP 5: Loading from JSON API
// ============================================================

echo "STEP 5: Loading Data from JSON API\n";
echo "------------------------------------------------------------\n";

echo "Example: Loading from REST API\n\n";

echo "```php\n";
echo "// Fetch data from API\n";
echo "\$response = file_get_contents('https://api.example.com/training-data');\n";
echo "\$data = json_decode(\$response, true);\n\n";

echo "// Extract features and labels\n";
echo "\$samples = [];\n";
echo "\$labels = [];\n";
echo "foreach (\$data['records'] as \$record) {\n";
echo "    \$samples[] = [\n";
echo "        \$record['age'],\n";
echo "        \$record['income'],\n";
echo "        \$record['score'],\n";
echo "    ];\n";
echo "    \$labels[] = \$record['category'];\n";
echo "}\n\n";

echo "\$dataset = new Labeled(\$samples, \$labels);\n";
echo "```\n\n";

echo "Use cases:\n";
echo "  - Load training data from microservices\n";
echo "  - Fetch labeled data from annotation services\n";
echo "  - Import datasets from cloud ML platforms\n\n";

echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║         ✓ Real Data Loading Complete!                  ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n";
