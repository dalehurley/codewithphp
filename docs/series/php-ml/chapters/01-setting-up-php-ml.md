---
title: "01: Setting Up PHP-ML and Your First Model"
description: "Install and configure PHP-ML, load datasets, train your first classifier, and evaluate model performance"
series: "php-ml"
chapter: 1
order: 1
difficulty: "Advanced"
prerequisites:
  - "Completion of Chapter 00"
  - "Composer installed"
  - "PHP 8.4+ environment"
---

![01: Setting Up PHP-ML and Your First Model](/images/php-ml/chapter-01-setup-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/php-ml">Machine Learning with PHP-ML</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 01</span>
</div>

# Chapter 01: Setting Up PHP-ML and Your First Model

## Overview

This chapter guides you through installing PHP-ML, understanding its architecture, and building your first complete machine learning pipeline. You'll learn dataset management, model training, prediction, and evaluation—the core workflow you'll use throughout this series.

**Estimated time:** 120 minutes

By the end of this chapter, you will:

- Install and configure PHP-ML with Composer
- Understand PHP-ML's class structure and organization
- Load and prepare datasets using PHP-ML utilities
- Train your first k-NN classifier on real data
- Make predictions and evaluate model accuracy
- Save and load trained models for reuse
- Build a complete spam detection system

## Prerequisites

- ✓ Completion of Chapter 00 (~90 mins if not done)
- ✓ Composer installed and working
- ✓ PHP 8.4+ with CLI access
- ✓ Text editor or IDE
- ✓ Command line proficiency

## Learning Path

### Foundation Topics
- PHP-ML installation and configuration
- Dataset handling (CSV, arrays)
- Training and prediction workflow
- Model evaluation basics

### Practical Skills
- Build end-to-end ML pipeline
- Implement spam classifier
- Evaluate model performance
- Persist models to disk

### Advanced Concepts
- Cross-validation
- Hyperparameter tuning
- Model comparison strategies

## Step 1: Installing PHP-ML (~15 min)

### Goal
Install PHP-ML and verify the installation with a working example.

### Actions

1. **Create Project Directory**:

```bash
mkdir php-ml-projects
cd php-ml-projects
```

2. **Initialize Composer**:

```bash
composer init --no-interaction --name="yourname/php-ml-projects"
```

3. **Install PHP-ML**:

```bash
composer require php-ai/php-ml
```

4. **Verify Installation**:

```php
# filename: verify-installation.php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Phpml\Classification\KNearestNeighbors;
use Phpml\Math\Distance\Euclidean;

echo "PHP-ML Version: " . Phpml\Phpml::VERSION . "\n";

// Quick test
$classifier = new KNearestNeighbors();
$samples = [[1], [2], [3], [6], [7], [8]];
$labels = ['a', 'a', 'a', 'b', 'b', 'b'];

$classifier->train($samples, $labels);
echo "Prediction for [2]: " . $classifier->predict([2]) . "\n";
echo "Prediction for [7]: " . $classifier->predict([7]) . "\n";

echo "\n✓ PHP-ML is installed and working!\n";
```

### Expected Result

```
PHP-ML Version: 0.10.0
Prediction for [2]: a
Prediction for [7]: b

✓ PHP-ML is installed and working!
```

### Key Concepts

- **PHP-ML namespace**: All classes are under `Phpml\`
- **Autoloading**: Composer handles class loading automatically
- **Version checking**: Useful for debugging compatibility issues

### Troubleshooting

- **"Class 'Phpml\Classification\KNearestNeighbors' not found"** — Run `composer dump-autoload`
- **"Your requirements could not be resolved"** — Check PHP version (need 7.2+)
- **Memory errors** — Increase PHP memory limit in php.ini: `memory_limit = 256M`

## Step 2: Understanding PHP-ML Architecture (~20 min)

### Goal
Learn PHP-ML's class organization and where to find algorithms.

### Actions

1. **PHP-ML Structure Overview**:

```
Phpml\
├── Classification\           # Supervised learning for categories
│   ├── KNearestNeighbors    # k-NN classifier
│   ├── NaiveBayes           # Probabilistic classifier
│   ├── SVC                  # Support Vector Classification
│   ├── DecisionTree         # Tree-based classifier
│   └── Ensemble\            # Ensemble methods
│       ├── RandomForest
│       └── AdaBoost
│
├── Regression\              # Supervised learning for continuous values
│   ├── LeastSquares         # Linear regression
│   ├── SVR                  # Support Vector Regression
│   └── DecisionTree         # Tree-based regression
│
├── Clustering\              # Unsupervised learning
│   ├── KMeans               # k-Means clustering
│   ├── DBSCAN               # Density-based clustering
│   └── FuzzyCMeans          # Fuzzy clustering
│
├── Preprocessing\           # Data preparation
│   ├── Normalizer           # Scale features to [0,1]
│   ├── Imputer              # Fill missing values
│   ├── LabelEncoder         # Encode text labels to numbers
│   └── OneHotEncoder        # One-hot encoding
│
├── FeatureExtraction\       # Extract features from text
│   ├── TokenCountVectorizer
│   └── TfidfTransformer
│
├── FeatureSelection\        # Select important features
│   ├── VarianceThreshold
│   └── SelectKBest
│
├── Metric\                  # Evaluation metrics
│   ├── Accuracy
│   ├── ConfusionMatrix
│   └── ClassificationReport
│
├── CrossValidation\         # Model validation
│   ├── RandomSplit
│   └── StratifiedRandomSplit
│
└── ModelManager             # Save/load trained models
```

2. **Common Usage Patterns**:

```php
<?php

use Phpml\Classification\KNearestNeighbors;
use Phpml\Preprocessing\Normalizer;
use Phpml\Metric\Accuracy;
use Phpml\CrossValidation\StratifiedRandomSplit;

// Pattern 1: Create classifier
$classifier = new KNearestNeighbors($k = 3);

// Pattern 2: Preprocess data
$normalizer = new Normalizer();
$normalizer->transform($samples);

// Pattern 3: Evaluate accuracy
$score = Accuracy::score($actualLabels, $predictedLabels);

// Pattern 4: Split data
$dataset = new StratifiedRandomSplit($samples, $labels, $testSize = 0.3);
```

### Expected Result

You'll understand:
- Where to find specific algorithms
- How classes are organized by functionality
- Common import patterns and workflows

### Troubleshooting

- **"Which classifier should I use?"** — Start with KNearestNeighbors for simplicity
- **"Do I need to use Preprocessing?"** — Yes, for best results; normalize features
- **"What's the difference between Classification and Regression?"** — Classification for categories (spam/not spam), Regression for numbers (house prices)

## Step 3: Loading and Preparing Datasets (~25 min)

### Goal
Master dataset loading from CSV files and PHP arrays.

### Actions

1. **Loading from PHP Arrays**:

```php
# filename: load-array-dataset.php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Phpml\Dataset\ArrayDataset;

// Method 1: Simple arrays
$samples = [
    [5.1, 3.5, 1.4, 0.2],
    [4.9, 3.0, 1.4, 0.2],
    [7.0, 3.2, 4.7, 1.4],
    [6.4, 3.2, 4.5, 1.5],
];

$labels = ['setosa', 'setosa', 'versicolor', 'versicolor'];

$dataset = new ArrayDataset($samples, $labels);

echo "Dataset size: " . count($dataset->getSamples()) . "\n";
echo "Features: " . count($dataset->getSamples()[0]) . "\n";
echo "Labels: " . implode(', ', array_unique($labels)) . "\n";
```

2. **Loading from CSV**:

```php
# filename: load-csv-dataset.php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Phpml\Dataset\CsvDataset;

// Create sample CSV file first
$csvData = "5.1,3.5,1.4,0.2,setosa
4.9,3.0,1.4,0.2,setosa
7.0,3.2,4.7,1.4,versicolor
6.4,3.2,4.5,1.5,versicolor";

file_put_contents('iris.csv', $csvData);

// Load CSV: last column is label, comma delimiter, no headers
$dataset = new CsvDataset('iris.csv', 4, true);

$samples = $dataset->getSamples();
$labels = $dataset->getTargets();

echo "Loaded " . count($samples) . " samples\n";
echo "Sample 1: " . implode(', ', $samples[0]) . " => {$labels[0]}\n";

// Cleanup
unlink('iris.csv');
```

3. **Data Preprocessing**:

```php
# filename: preprocess-data.php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Phpml\Preprocessing\Normalizer;
use Phpml\Preprocessing\Imputer;
use Phpml\Preprocessing\Imputer\Strategy\MeanStrategy;

// Sample data with different scales
$samples = [
    [100, 0.5],    // [income in thousands, satisfaction 0-1]
    [150, 0.8],
    [200, 0.9],
];

echo "Before normalization:\n";
print_r($samples);

// Normalize to [0, 1] range
$normalizer = new Normalizer();
$normalizer->transform($samples);

echo "\nAfter normalization:\n";
print_r($samples);

// Handling missing values
$samplesWithMissing = [
    [1, 2, 3],
    [4, null, 6],  // Missing value
    [7, 8, 9],
];

$imputer = new Imputer(null, new MeanStrategy());
$imputer->fit($samplesWithMissing);
$imputer->transform($samplesWithMissing);

echo "\nAfter imputation (mean strategy):\n";
print_r($samplesWithMissing);
```

### Expected Result

You'll know how to:
- Load data from arrays and CSV files
- Normalize features to similar scales
- Handle missing values with imputation
- Prepare data for training

### Troubleshooting

- **"CsvDataset error: file not found"** — Ensure CSV file exists and path is correct
- **"Wrong number of features"** — Check CSV column count matches expected features
- **"Normalizer doesn't change data"** — Some data may already be normalized; check original values

## Step 4: Training Your First Classifier (~30 min)

### Goal
Build and train a complete classification model.

### Actions

1. **Basic k-NN Classifier**:

```php
# filename: first-classifier.php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Phpml\Classification\KNearestNeighbors;

// Student exam data: [hours_studied, practice_tests_taken] => pass/fail
$samples = [
    [2, 1], [3, 1], [3, 2], [4, 2], [4, 3],  // Failed
    [6, 4], [7, 5], [8, 6], [9, 7], [10, 8], // Passed
];

$labels = [
    'fail', 'fail', 'fail', 'fail', 'fail',
    'pass', 'pass', 'pass', 'pass', 'pass'
];

// Create and train classifier (k=3 nearest neighbors)
$classifier = new KNearestNeighbors($k = 3);
$classifier->train($samples, $labels);

// Make predictions
$testCases = [
    [5, 3],   // Borderline case
    [8, 7],   // Strong pass
    [2, 1],   // Likely fail
];

echo "Predictions:\n";
foreach ($testCases as $test) {
    $prediction = $classifier->predict($test);
    echo sprintf(
        "  Hours: %d, Tests: %d => %s\n",
        $test[0],
        $test[1],
        $prediction
    );
}
```

2. **Multi-class Classification**:

```php
# filename: multi-class-classifier.php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Phpml\Classification\KNearestNeighbors;

// Iris flower classification
// Features: [sepal_length, sepal_width, petal_length, petal_width]
$samples = [
    [5.1, 3.5, 1.4, 0.2], [4.9, 3.0, 1.4, 0.2],  // Setosa
    [7.0, 3.2, 4.7, 1.4], [6.4, 3.2, 4.5, 1.5],  // Versicolor
    [6.3, 3.3, 6.0, 2.5], [5.8, 2.7, 5.1, 1.9],  // Virginica
];

$labels = [
    'setosa', 'setosa',
    'versicolor', 'versicolor',
    'virginica', 'virginica'
];

$classifier = new KNearestNeighbors($k = 3);
$classifier->train($samples, $labels);

// Test new flowers
$newFlowers = [
    [5.0, 3.4, 1.5, 0.2],  // Looks like setosa
    [6.5, 3.0, 4.5, 1.5],  // Looks like versicolor
    [6.0, 2.8, 5.5, 2.1],  // Looks like virginica
];

echo "Flower predictions:\n";
foreach ($newFlowers as $flower) {
    echo sprintf(
        "  [%.1f, %.1f, %.1f, %.1f] => %s\n",
        $flower[0], $flower[1], $flower[2], $flower[3],
        $classifier->predict($flower)
    );
}
```

### Expected Result

```
Predictions:
  Hours: 5, Tests: 3 => pass
  Hours: 8, Tests: 7 => pass
  Hours: 2, Tests: 1 => fail

Flower predictions:
  [5.0, 3.4, 1.5, 0.2] => setosa
  [6.5, 3.0, 4.5, 1.5] => versicolor
  [6.0, 2.8, 5.5, 2.1] => virginica
```

### Key Concepts

- **k parameter**: Number of nearest neighbors to consider (odd numbers avoid ties)
- **Feature scaling**: Normalize features so no single feature dominates distance calculations
- **Multi-class**: PHP-ML handles 2+ classes automatically

### Troubleshooting

- **All predictions return same label** — Features may need normalization
- **Inconsistent predictions** — Try increasing k value (e.g., k=5 or k=7)
- **Training error** — Ensure samples and labels arrays have same length

## Step 5: Evaluating Model Performance (~20 min)

### Goal
Measure classifier accuracy and understand model quality.

### Actions

1. **Train/Test Split and Accuracy**:

```php
# filename: evaluate-model.php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Phpml\Classification\KNearestNeighbors;
use Phpml\CrossValidation\StratifiedRandomSplit;
use Phpml\Metric\Accuracy;

// Full dataset
$samples = [
    [2, 1], [3, 1], [3, 2], [4, 2], [4, 3], [5, 3],  // Fail
    [6, 4], [7, 5], [8, 6], [9, 7], [10, 8], [11, 9], // Pass
];

$labels = [
    'fail', 'fail', 'fail', 'fail', 'fail', 'fail',
    'pass', 'pass', 'pass', 'pass', 'pass', 'pass'
];

// Split: 70% training, 30% testing
$dataset = new StratifiedRandomSplit($samples, $labels, $testSize = 0.3);

// Train on training set
$classifier = new KNearestNeighbors($k = 3);
$classifier->train(
    $dataset->getTrainSamples(),
    $dataset->getTrainLabels()
);

// Predict on test set
$predictions = $classifier->predict($dataset->getTestSamples());

// Calculate accuracy
$accuracy = Accuracy::score(
    $dataset->getTestLabels(),
    $predictions
);

echo "Training samples: " . count($dataset->getTrainSamples()) . "\n";
echo "Test samples: " . count($dataset->getTestSamples()) . "\n";
echo "Accuracy: " . round($accuracy * 100, 2) . "%\n";

// Show predictions vs actual
echo "\nPredictions vs Actual:\n";
foreach ($dataset->getTestSamples() as $index => $sample) {
    echo sprintf(
        "  Sample %d: predicted=%s, actual=%s %s\n",
        $index,
        $predictions[$index],
        $dataset->getTestLabels()[$index],
        $predictions[$index] === $dataset->getTestLabels()[$index] ? '✓' : '✗'
    );
}
```

2. **Confusion Matrix**:

```php
# filename: confusion-matrix.php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Phpml\Metric\ConfusionMatrix;
use Phpml\Metric\ClassificationReport;

$actualLabels = ['cat', 'dog', 'cat', 'cat', 'dog', 'dog', 'cat', 'dog'];
$predictedLabels = ['cat', 'dog', 'dog', 'cat', 'dog', 'cat', 'cat', 'dog'];

// Confusion matrix shows prediction distribution
$matrix = ConfusionMatrix::compute($actualLabels, $predictedLabels, ['cat', 'dog']);

echo "Confusion Matrix:\n";
echo "                Predicted\n";
echo "              cat    dog\n";
echo "Actual cat  {$matrix[0][0]}      {$matrix[0][1]}\n";
echo "       dog  {$matrix[1][0]}      {$matrix[1][1]}\n\n";

// Classification report (precision, recall, F1-score)
$report = new ClassificationReport($actualLabels, $predictedLabels);

echo "Classification Report:\n";
echo $report;
```

### Expected Result

```
Training samples: 8
Test samples: 4
Accuracy: 75.00%

Predictions vs Actual:
  Sample 0: predicted=fail, actual=fail ✓
  Sample 1: predicted=pass, actual=pass ✓
  Sample 2: predicted=pass, actual=fail ✗
  Sample 3: predicted=pass, actual=pass ✓

Confusion Matrix:
                Predicted
              cat    dog
Actual cat  3      1
       dog  1      3

Classification Report:
              precision    recall  f1-score   support
         cat       0.75      0.75      0.75         4
         dog       0.75      0.75      0.75         4
   micro avg       0.75      0.75      0.75         8
   macro avg       0.75      0.75      0.75         8
weighted avg       0.75      0.75      0.75         8
```

### Key Concepts

- **Train/Test Split**: Always evaluate on data the model hasn't seen
- **Accuracy**: Percentage of correct predictions (good baseline metric)
- **Confusion Matrix**: Shows where model makes mistakes (which classes confused)
- **Precision/Recall**: Important for imbalanced datasets

### Troubleshooting

- **Accuracy = 100%** — Possible overfitting; need more diverse test data
- **Accuracy varies wildly** — Random split variation; use cross-validation
- **Low accuracy (<60%)** — Need more data, better features, or different algorithm

## Step 6: Saving and Loading Models (~10 min)

### Goal
Persist trained models to disk for reuse.

### Actions

```php
# filename: save-load-model.php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Phpml\Classification\KNearestNeighbors;
use Phpml\ModelManager;

// Train a model
$samples = [[1], [2], [3], [6], [7], [8]];
$labels = ['a', 'a', 'a', 'b', 'b', 'b'];

$classifier = new KNearestNeighbors();
$classifier->train($samples, $labels);

echo "Original model prediction for [2]: " . $classifier->predict([2]) . "\n";

// Save model to file
$modelManager = new ModelManager();
$modelManager->saveToFile($classifier, 'trained-model.phpml');

echo "Model saved to trained-model.phpml\n\n";

// Later... load the model
$loadedClassifier = $modelManager->restoreFromFile('trained-model.phpml');

echo "Loaded model prediction for [2]: " . $loadedClassifier->predict([2]) . "\n";
echo "Loaded model prediction for [7]: " . $loadedClassifier->predict([7]) . "\n";

// Cleanup
unlink('trained-model.phpml');
echo "\nModel file cleaned up.\n";
```

### Expected Result

```
Original model prediction for [2]: a
Model saved to trained-model.phpml

Loaded model prediction for [2]: a
Loaded model prediction for [7]: b

Model file cleaned up.
```

### Key Concepts

- **Model persistence**: Save trained models to avoid retraining
- **Production deployment**: Load saved models in web applications
- **Version control**: Track model versions alongside code

### Troubleshooting

- **"Permission denied" when saving** — Check write permissions in directory
- **Model file size large** — Normal for complex models; consider compression
- **Loaded model different predictions** — Ensure PHP-ML version matches

## Practical Project: Spam Email Detector

Build a complete spam detection system:

```php
# filename: spam-detector-complete.php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Phpml\Classification\NaiveBayes;
use Phpml\FeatureExtraction\TokenCountVectorizer;
use Phpml\Tokenization\WordTokenizer;
use Phpml\CrossValidation\StratifiedRandomSplit;
use Phpml\Metric\Accuracy;

// Sample email dataset
$emails = [
    'Free money click here now',
    'Meeting agenda for tomorrow',
    'You won the lottery click now',
    'Project deadline reminder',
    'Congratulations you are a winner',
    'Team lunch plans Friday',
    'Amazing prize waiting for you',
    'Code review feedback',
];

$labels = ['spam', 'ham', 'spam', 'ham', 'spam', 'ham', 'spam', 'ham'];

// Extract features from text
$vectorizer = new TokenCountVectorizer(new WordTokenizer());
$vectorizer->fit($emails);
$vectorizer->transform($emails);

$samples = $vectorizer->getVocabulary();  // Simplified for example

// For actual implementation, use proper feature extraction
// Here we'll use a simpler approach

$samples = array_map(function($email) {
    return [
        substr_count(strtolower($email), 'free'),
        substr_count(strtolower($email), 'click'),
        substr_count(strtolower($email), 'winner'),
        str_word_count($email),
    ];
}, $emails);

// Split and train
$dataset = new StratifiedRandomSplit($samples, $labels, 0.3);

$classifier = new NaiveBayes();
$classifier->train(
    $dataset->getTrainSamples(),
    $dataset->getTrainLabels()
);

// Evaluate
$predictions = $classifier->predict($dataset->getTestSamples());
$accuracy = Accuracy::score($dataset->getTestLabels(), $predictions);

echo "Spam Detector Accuracy: " . round($accuracy * 100, 2) . "%\n\n";

// Test new emails
$newEmails = [
    'Click here for free prize money',
    'Quarterly report due next week',
];

foreach ($newEmails as $email) {
    $features = [
        substr_count(strtolower($email), 'free'),
        substr_count(strtolower($email), 'click'),
        substr_count(strtolower($email), 'winner'),
        str_word_count($email),
    ];

    $prediction = $classifier->predict($features);
    echo "'{$email}' => {$prediction}\n";
}
```

## Exercises

### Exercise 1: Iris Flower Classifier

Build a complete Iris classifier with evaluation.

### Exercise 2: Customer Churn Predictor

Predict customer churn based on usage patterns.

### Exercise 3: Wine Quality Classifier

Classify wine quality using multiple features.

## Wrap-up

Congratulations! You've mastered PHP-ML fundamentals:

- ✓ Installed and configured PHP-ML
- ✓ Loaded datasets from arrays and CSV
- ✓ Trained classification models
- ✓ Evaluated model performance
- ✓ Saved and loaded models
- ✓ Built a complete spam detector

### What's Next

[Chapter 02: Data Preprocessing and Feature Engineering](/series/php-ml/chapters/02-data-preprocessing)

<ChapterCheckbox
  seriesId="php-ml"
  chapterId="01"
  label="You've mastered PHP-ML setup and your first model!"
/>
