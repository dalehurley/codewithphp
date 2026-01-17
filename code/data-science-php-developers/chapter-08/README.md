# Chapter 08: Machine Learning Explained - Code Examples

This directory contains all the code examples from Chapter 08: Machine Learning Explained for PHP Developers.

## Overview

This chapter provides educational implementations of fundamental machine learning algorithms in PHP. These implementations are designed for learning ML concepts, not production use.

## Setup

### Requirements

- **PHP 8.4+**
- **Composer**

### Installation

```bash
cd code/data-science-php-developers/chapter-08
composer install
```

### Running Examples

```bash
# Decision framework for when to use ML
php examples/when-to-use-ml.php

# Supervised learning (KNN classification + Linear regression)
php examples/supervised-learning.php

# Unsupervised learning (K-Means clustering)
php examples/unsupervised-learning.php

# Feature engineering toolkit
php examples/feature-engineering.php
```

### Running Tests

```bash
# Run all tests
./vendor/bin/phpunit tests

# Run specific test class
./vendor/bin/phpunit tests/SimpleClassifierTest.php

# Run with coverage (if xdebug enabled)
./vendor/bin/phpunit --coverage-html coverage tests
```

## Classes

### SimpleClassifier

K-Nearest Neighbors (KNN) classifier for classification tasks.

**Features:**
- Training with labeled data
- Prediction with configurable K
- Probability estimates
- Evaluation metrics (accuracy, precision, recall, F1-score)
- Confusion matrix generation
- Model persistence (save/load)

**Usage:**

```php
use DataScience\ML\SimpleClassifier;

$classifier = new SimpleClassifier();

// Train
$trainingData = [
    [1.4, 0.2], [4.7, 1.4], [6.0, 2.5]
];
$trainingLabels = ['setosa', 'versicolor', 'virginica'];

$classifier->train($trainingData, $trainingLabels);

// Predict
$prediction = $classifier->predict([1.5, 0.3], k: 3);
echo "Predicted: {$prediction}\n";

// Evaluate
$testData = [[1.3, 0.2], [4.6, 1.3]];
$testLabels = ['setosa', 'versicolor'];

$evaluation = $classifier->evaluate($testData, $testLabels);
echo "Accuracy: " . ($evaluation['accuracy'] * 100) . "%\n";

// Detailed metrics
$detailed = $classifier->evaluateDetailed($testData, $testLabels);
print_r($detailed['per_class']);

// Save model
$classifier->save('model.json');

// Load model
$loadedClassifier = SimpleClassifier::load('model.json');
```

### SimpleRegressor

Linear regression for predicting continuous values.

**Features:**
- Least squares fitting
- Single and batch predictions
- Multiple evaluation metrics (R², MAE, RMSE, MAPE)
- Model parameters extraction
- Model persistence (save/load)

**Usage:**

```php
use DataScience\ML\SimpleRegressor;

$regressor = new SimpleRegressor();

// Train
$x = [1000, 1500, 2000, 2500, 3000];
$y = [150, 200, 250, 300, 350];

$regressor->train($x, $y);

// Get parameters
$params = $regressor->getParameters();
echo "Equation: {$params['equation']}\n";

// Predict
$prediction = $regressor->predict(1750);
echo "Predicted price: \${$prediction}k\n";

// Batch predictions
$predictions = $regressor->predictBatch([1200, 2200, 3800]);

// Evaluate
$rSquared = $regressor->rSquared($x, $y);
echo "R² Score: {$rSquared}\n";

$mae = $regressor->meanAbsoluteError($x, $y);
echo "MAE: {$mae}\n";

$rmse = $regressor->rootMeanSquaredError($x, $y);
echo "RMSE: {$rmse}\n";

// Save model
$regressor->save('regressor.json');
```

### SimpleClusterer

K-Means clustering for unsupervised learning.

**Features:**
- K-Means algorithm with configurable K
- Cluster assignment for new data
- Quality metrics (inertia, silhouette score)
- Centroid extraction
- Empty cluster handling
- Model persistence (save/load)

**Usage:**

```php
use DataScience\ML\SimpleClusterer;

$clusterer = new SimpleClusterer();

// Fit clustering
$data = [
    [100, 2], [150, 3], [120, 2],     // Low spenders
    [500, 8], [600, 10], [550, 9],     // Medium spenders
    [1200, 20], [1100, 18], [1300, 22] // High spenders
];

$clusterer->fit($data, k: 3, maxIterations: 100);

// Get cluster assignments
$labels = $clusterer->getLabels();
print_r($labels);

// Get centroids
$centroids = $clusterer->getCentroids();
print_r($centroids);

// Predict cluster for new data
$newCustomers = [[130, 3], [580, 9], [1150, 19]];
$predictions = $clusterer->predict($newCustomers);

// Evaluate clustering quality
$inertia = $clusterer->inertia($data);
echo "Inertia: {$inertia}\n";

$silhouette = $clusterer->silhouetteScore($data);
echo "Silhouette Score: {$silhouette}\n";

// Save model
$clusterer->save('clusterer.json');
```

### FeatureEngineer

Comprehensive toolkit for feature engineering and data transformation.

**Available Methods:**

**Scaling:**
- `minMaxScale(array $features)` - Normalize to [0, 1]
- `standardize(array $features)` - Z-score normalization (mean=0, std=1)

**Feature Creation:**
- `polynomialFeatures(array $x, int $degree)` - Create polynomial features
- `interactionFeatures(array $f1, array $f2)` - Create interaction terms
- `lagFeatures(array $values, int $lag)` - Create lagged features for time series
- `rollingWindow(array $values, int $window, string $function)` - Rolling statistics

**Encoding:**
- `oneHotEncode(array $categories)` - One-hot encode categorical variables
- `targetEncode(array $categories, array $targets)` - Mean encoding
- `binFeature(array $values, array $bins)` - Bin continuous features

**Feature Extraction:**
- `dateFeatures(string $date)` - Extract temporal features
- `textFeatures(string $text)` - Extract text statistics

**Usage:**

```php
use DataScience\ML\FeatureEngineer;

$engineer = new FeatureEngineer();

// Normalize features
$prices = [100, 200, 150, 300, 250];
$normalized = $engineer->minMaxScale($prices);

// Standardize
$scores = [85, 92, 78, 95, 88];
$standardized = $engineer->standardize($scores);

// One-hot encoding
$colors = ['red', 'blue', 'red', 'green'];
$result = $engineer->oneHotEncode($colors);
$encoded = $result['encoded'];
$categories = $result['categories'];

// Date features
$dateFeatures = $engineer->dateFeatures('2026-01-17');
// Returns: year, month, day, day_of_week, quarter, is_weekend, etc.

// Text features
$textFeatures = $engineer->textFeatures("Machine Learning");
// Returns: word_count, char_count, avg_word_length, uppercase_ratio, etc.

// Polynomial features
$x = [1, 2, 3];
$poly = $engineer->polynomialFeatures($x, degree: 3);

// Rolling window
$timeSeries = [1, 2, 3, 4, 5];
$movingAvg = $engineer->rollingWindow($timeSeries, window: 3, function: 'mean');
```

### CrossValidator

Cross-validation for model evaluation and selection.

**Available Methods:**
- `kFold()` - Standard K-fold cross-validation
- `stratifiedKFold()` - Stratified K-fold (maintains class distribution)
- `leaveOneOut()` - Leave-one-out cross-validation
- `timeSeriesSplit()` - Walk-forward validation for time series

**Usage:**

```php
use DataScience\ML\CrossValidator;
use DataScience\ML\SimpleClassifier;

$validator = new CrossValidator();
$classifier = new SimpleClassifier();

$data = [[1, 2], [3, 4], [5, 6], [7, 8], [9, 10]];
$labels = ['A', 'A', 'B', 'B', 'B'];

// K-fold cross-validation
$results = $validator->kFold($classifier, $data, $labels, k: 5);

echo "Mean Score: {$results['mean_score']}\n";
echo "Std Dev: {$results['std_dev']}\n";
echo "Min Score: {$results['min_score']}\n";
echo "Max Score: {$results['max_score']}\n";

// Stratified K-fold (for imbalanced datasets)
$results = $validator->stratifiedKFold($classifier, $data, $labels, k: 3);
```

## Expected Outputs

### when-to-use-ml.php

```
=== Spam Detection ===
Score: 10
Recommendation: Strong candidate for ML
  ✓ Data contains learnable patterns
  ✓ Rules are too complex to code manually
  ✓ Sufficient data for training
  ✓ System needs to adapt to changing patterns

=== Tax Calculation ===
Score: -7
Recommendation: ML probably not the best approach
  ✗ Simple rules—traditional code is better
  ✗ ML is probabilistic—can't guarantee 100% accuracy
  ✗ Insufficient data for training
  ⚠ ML models can be hard to explain (use interpretable models)
```

### supervised-learning.php

```
=== Supervised Learning Examples ===

1. Classification (K-Nearest Neighbors):
   Classifying iris flowers based on petal measurements

   Test 1: [1.5, 0.3]
     Prediction: setosa
     Confidence:
       setosa: ████████████████████ 100.0%

   Model Accuracy: 100.0%
   Correct: 3 / 3

2. Regression (Linear Regression):
   Predicting house prices based on size

   Model: y = 0.1000x + 50.0000
   Slope: $100.00 per sqft
   Intercept: $50,000.00

   Predictions:
     1200 sqft → $170.0k
     2200 sqft → $270.0k
     3800 sqft → $430.0k

   Model Quality:
     R² Score: 1.0000 (1.0 = perfect fit)
     Mean Absolute Error: $0.00k

✓ Supervised learning examples complete!
```

## Common Issues and Solutions

### "Model not trained" error

**Problem:** Calling `predict()` before `train()`.

**Solution:** Always train the model before making predictions:

```php
$classifier->train($trainingData, $trainingLabels);
$prediction = $classifier->predict($testPoint);
```

### Dimension mismatch errors

**Problem:** Input features have different dimensions than training data.

**Solution:** Ensure all data points have the same number of features:

```php
// ❌ Wrong - inconsistent dimensions
$trainingData = [[1, 2], [3, 4, 5]];

// ✅ Correct - all have 2 features
$trainingData = [[1, 2], [3, 4]];
```

### Empty cluster warnings (K-Means)

**Problem:** K is too large relative to data points, causing empty clusters.

**Solution:** Reduce K or provide more data points:

```php
// If you have 12 data points, K=3 or K=4 is reasonable
// K=10 might create empty clusters
$clusterer->fit($data, k: 3); // Better choice
```

### K parameter out of bounds

**Problem:** K is less than 1 or greater than the number of training samples.

**Solution:** Choose K between 1 and the number of training points:

```php
$trainingSize = count($trainingData);
$k = min(5, $trainingSize); // Safe K selection
$prediction = $classifier->predict($point, k: $k);
```

### Poor model performance

**Problem:** Low accuracy or poor predictions.

**Solutions:**
1. **More training data** - ML models need sufficient examples
2. **Feature engineering** - Normalize/standardize features
3. **Try different K values** - For KNN, experiment with K
4. **Check for overfitting** - Use cross-validation

```php
// Use cross-validation to check for overfitting
$validator = new CrossValidator();
$results = $validator->kFold($classifier, $data, $labels, k: 5);

if ($results['std_dev'] > 0.2) {
    echo "High variance - possible overfitting\n";
}
```

### Negative R² values

**Problem:** Linear regression R² is negative.

**Solution:** The model fits worse than a horizontal line. Try:
1. Check for non-linear relationships (use polynomial features)
2. Remove outliers
3. Verify data quality

```php
$engineer = new FeatureEngineer();

// Try polynomial features for non-linear relationships
$xPoly = $engineer->polynomialFeatures($x, degree: 2);
```

## Performance Considerations

These are **educational implementations** optimized for:
- **Code clarity** and learning
- **Conceptual understanding**
- **Moderate datasets** (<1000 samples)

For production use:
- Use **PHP-ML** library (Chapter 09)
- Train models in **Python** (scikit-learn) and serve via API
- Consider **performance optimization** for large datasets

## Testing

All classes have comprehensive unit tests. Run the test suite:

```bash
./vendor/bin/phpunit tests
```

Expected results:
- **SimpleClassifierTest**: 11 tests
- **SimpleRegressorTest**: 12 tests
- **SimpleClustererTest**: 11 tests
- **FeatureEngineerTest**: 16 tests
- **MLWorkflowIntegrationTest**: 8 tests

**Total: ~58 tests** covering:
- Core functionality
- Error handling
- Edge cases
- Model persistence
- Integration workflows

## Architecture

```
chapter-08/
├── src/ML/              # Core ML classes
│   ├── SimpleClassifier.php
│   ├── SimpleRegressor.php
│   ├── SimpleClusterer.php
│   ├── FeatureEngineer.php
│   └── CrossValidator.php
├── examples/            # Runnable examples
│   ├── when-to-use-ml.php
│   ├── supervised-learning.php
│   ├── unsupervised-learning.php
│   └── feature-engineering.php
├── tests/               # PHPUnit tests
│   ├── SimpleClassifierTest.php
│   ├── SimpleRegressorTest.php
│   ├── SimpleClustererTest.php
│   ├── FeatureEngineerTest.php
│   └── MLWorkflowIntegrationTest.php
├── composer.json
└── README.md
```

## Next Steps

After completing this chapter:
- **Chapter 09**: Using ML Models in PHP Applications (PHP-ML library, Python integration)
- **Chapter 10**: Data Visualization and Reporting
- **Chapter 11**: Building a Real-World Data Science Project

## Educational Notes

**What You Learned:**
- Core ML concepts (supervised vs unsupervised)
- Classification with KNN
- Regression with linear models
- Clustering with K-Means
- Feature engineering techniques
- Model evaluation metrics
- Cross-validation strategies

**Production Considerations:**
- These implementations are for learning, not production
- Real-world ML uses optimized libraries (scikit-learn, TensorFlow)
- PHP excels at serving predictions, not training models
- See Chapter 09 for production-ready ML in PHP

## License

This code is part of the "Data Science for PHP Developers" tutorial series.

## Support

For questions about this code:
- Review the chapter text
- Check the test files for usage examples
- See Chapter 09 for production implementations
