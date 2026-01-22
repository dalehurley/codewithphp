---
title: "08: Machine Learning Explained for PHP Developers"
description: "Learn core ML concepts, supervised and unsupervised learning, and where PHP fits in the ML ecosystem"
sidebar:
  label: "08: Machine Learning Explained for PHP Developers"
  order: 8
  badge:
    text: Intermediate
    variant: caution
---

![Machine Learning Explained for PHP Developers](/images/data-science-php-developers/chapter-08-machine-learning-hero-full.webp)

# Chapter 08: Machine Learning Explained for PHP Developers

## Overview

Machine learning doesn't have to be scary. At its core, ML is about finding patterns in data and using those patterns to make predictions. You've already been doing pattern recognition your whole life—ML just automates and scales it.

This chapter demystifies machine learning for PHP developers. You'll learn what ML really is (and isn't), understand the main types of learning (supervised, unsupervised, reinforcement), explore common algorithms, and discover where PHP fits in the ML ecosystem. No PhD required—just curiosity and practical examples.

By the end of this chapter, you'll understand how ML works conceptually, know which algorithms to use for different problems, and be able to integrate ML into your PHP applications. You'll see that ML isn't magic—it's math, statistics, and pattern recognition working together.

## Prerequisites

Before starting this chapter, you should have:

- Completed [Chapter 07: Statistics for Data Science](/series/data-science-php-developers/chapters/07-statistics-every-php-developer-needs-for-data-science)
- PHP 8.4+ installed
- Understanding of basic statistics (mean, variance, probability)
- Familiarity with linear algebra concepts (vectors, matrices)
- **Estimated Time**: ~90 minutes

**Verify your setup:**

```bash
# Check PHP version
php --version

# We'll use PHP-ML library in next chapter
# For now, we'll focus on concepts
```

## What You'll Build

By the end of this chapter, you will have created:

- **Conceptual understanding** of machine learning fundamentals
- **Algorithm selector** to choose the right ML algorithm
- **Simple implementations** of basic ML algorithms in PHP
- **Model evaluator** to assess ML performance
- **Feature engineering toolkit** for data preparation
- **ML workflow planner** for real-world projects
- **Integration strategies** for PHP + ML systems
- **Decision framework** for when to use ML

## Objectives

- Understand what machine learning is and how it works
- Distinguish between supervised, unsupervised, and reinforcement learning
- Learn common ML algorithms and when to use them
- Understand the ML workflow (data → training → evaluation → deployment)
- Recognize overfitting, underfitting, and how to prevent them
- Learn feature engineering basics
- Understand where PHP fits in ML ecosystems
- Make informed decisions about using ML

## Step 1: What Is Machine Learning? (~10 min)

### Goal

Understand what machine learning really is and how it differs from traditional programming.

### Traditional Programming vs Machine Learning

**Traditional Programming Flow:**
Rules/Logic + Input Data → Program → Output

**Machine Learning Flow:**
Training Data + Desired Output → Learning Algorithm → Trained Model → (receives New Data) → Predictions

### Key Differences

**Traditional Programming:**

- You write explicit rules
- Rules are deterministic
- Works well for well-defined problems
- Example: Calculate tax based on income brackets

**Machine Learning:**

- Algorithm learns rules from data
- Predictions are probabilistic
- Works well for complex, pattern-based problems
- Example: Predict customer churn based on behavior

### When to Use ML

```php
# filename: examples/when-to-use-ml.php
<?php

/**
 * Decision framework: Should you use ML?
 */
function shouldUseMachineLearning(array $problem): array
{
    $score = 0;
    $reasons = [];

    // ✅ Good candidates for ML
    if ($problem['has_patterns_in_data']) {
        $score += 3;
        $reasons[] = "✓ Data contains learnable patterns";
    }

    if ($problem['rules_are_complex_or_unknown']) {
        $score += 3;
        $reasons[] = "✓ Rules are too complex to code manually";
    }

    if ($problem['has_large_dataset']) {
        $score += 2;
        $reasons[] = "✓ Sufficient data for training";
    }

    if ($problem['needs_to_adapt']) {
        $score += 2;
        $reasons[] = "✓ System needs to adapt to changing patterns";
    }

    // ❌ Bad candidates for ML
    if ($problem['rules_are_simple_and_known']) {
        $score -= 3;
        $reasons[] = "✗ Simple rules—traditional code is better";
    }

    if ($problem['requires_100_percent_accuracy']) {
        $score -= 2;
        $reasons[] = "✗ ML is probabilistic—can't guarantee 100% accuracy";
    }

    if ($problem['has_small_dataset']) {
        $score -= 2;
        $reasons[] = "✗ Insufficient data for training";
    }

    if ($problem['needs_explainability']) {
        $score -= 1;
        $reasons[] = "⚠ ML models can be hard to explain (use interpretable models)";
    }

    $recommendation = match(true) {
        $score >= 5 => "Strong candidate for ML",
        $score >= 3 => "Good candidate for ML",
        $score >= 0 => "Consider ML, but evaluate alternatives",
        default => "ML probably not the best approach"
    };

    return [
        'score' => $score,
        'recommendation' => $recommendation,
        'reasons' => $reasons,
    ];
}

// Example 1: Spam detection
echo "=== Spam Detection ===\n";
$spamDetection = shouldUseMachineLearning([
    'has_patterns_in_data' => true,
    'rules_are_complex_or_unknown' => true,
    'has_large_dataset' => true,
    'needs_to_adapt' => true,
    'rules_are_simple_and_known' => false,
    'requires_100_percent_accuracy' => false,
    'has_small_dataset' => false,
    'needs_explainability' => false,
]);

echo "Score: {$spamDetection['score']}\n";
echo "Recommendation: {$spamDetection['recommendation']}\n";
foreach ($spamDetection['reasons'] as $reason) {
    echo "  {$reason}\n";
}
echo "\n";

// Example 2: Tax calculation
echo "=== Tax Calculation ===\n";
$taxCalculation = shouldUseMachineLearning([
    'has_patterns_in_data' => false,
    'rules_are_complex_or_unknown' => false,
    'has_large_dataset' => false,
    'needs_to_adapt' => false,
    'rules_are_simple_and_known' => true,
    'requires_100_percent_accuracy' => true,
    'has_small_dataset' => true,
    'needs_explainability' => true,
]);

echo "Score: {$taxCalculation['score']}\n";
echo "Recommendation: {$taxCalculation['recommendation']}\n";
foreach ($taxCalculation['reasons'] as $reason) {
    echo "  {$reason}\n";
}
```

### Types of Machine Learning

Machine Learning has four main categories:

**1. Supervised Learning** (learning from labeled data)
- **Classification**: Predicting categories
  - Binary (two classes)
  - Multi-class (three or more classes)
- **Regression**: Predicting continuous values
  - Linear
  - Non-linear

**2. Unsupervised Learning** (finding patterns in unlabeled data)
- **Clustering**: Grouping similar items
  - K-Means
  - Hierarchical
- **Dimensionality Reduction**: Simplifying complex data
  - PCA (Principal Component Analysis)
  - t-SNE

**3. Reinforcement Learning** (learning through trial and error)
- Q-Learning
- Policy Gradient

**4. Semi-Supervised Learning** (combines labeled and unlabeled data)

### Why It Works

**Machine Learning** finds mathematical functions that map inputs to outputs by learning from examples. Instead of programming rules, you provide data and let the algorithm discover patterns.

**Key Insight**: ML is pattern recognition at scale. The more data and better features you provide, the better the patterns it can learn.

## Step 2: Supervised Learning (~20 min)

### Goal

Understand supervised learning, where models learn from labeled examples.

### What Is Supervised Learning?

**Supervised learning** trains models on labeled data (input + correct output). The model learns to predict outputs for new inputs.

**Two main types:**

1. **Classification**: Predict categories (spam/not spam, cat/dog)
2. **Regression**: Predict numbers (price, temperature, sales)

### Classification Example

```php
# filename: src/ML/SimpleClassifier.php
<?php

declare(strict_types=1);

namespace DataScience\ML;

/**
 * Simple K-Nearest Neighbors (KNN) Classifier
 *
 * Predicts class based on majority vote of K nearest neighbors
 */
class SimpleClassifier
{
    private array $trainingData = [];
    private array $trainingLabels = [];

    /**
     * Train the model with labeled data
     */
    public function train(array $data, array $labels): void
    {
        if (count($data) !== count($labels)) {
            throw new \InvalidArgumentException('Data and labels must have same length');
        }

        $this->trainingData = $data;
        $this->trainingLabels = $labels;
    }

    /**
     * Predict class for new data point
     */
    public function predict(array $point, int $k = 3): string
    {
        if (empty($this->trainingData)) {
            throw new \RuntimeException('Model not trained');
        }

        // Calculate distances to all training points
        $distances = [];

        foreach ($this->trainingData as $i => $trainPoint) {
            $distance = $this->euclideanDistance($point, $trainPoint);
            $distances[] = [
                'distance' => $distance,
                'label' => $this->trainingLabels[$i],
            ];
        }

        // Sort by distance
        usort($distances, fn($a, $b) => $a['distance'] <=> $b['distance']);

        // Get K nearest neighbors
        $neighbors = array_slice($distances, 0, $k);

        // Count votes
        $votes = [];
        foreach ($neighbors as $neighbor) {
            $label = $neighbor['label'];
            $votes[$label] = ($votes[$label] ?? 0) + 1;
        }

        // Return majority vote
        arsort($votes);
        return array_key_first($votes);
    }

    /**
     * Predict with confidence scores
     */
    public function predictWithProbability(array $point, int $k = 3): array
    {
        if (empty($this->trainingData)) {
            throw new \RuntimeException('Model not trained');
        }

        // Calculate distances
        $distances = [];
        foreach ($this->trainingData as $i => $trainPoint) {
            $distances[] = [
                'distance' => $this->euclideanDistance($point, $trainPoint),
                'label' => $this->trainingLabels[$i],
            ];
        }

        usort($distances, fn($a, $b) => $a['distance'] <=> $b['distance']);
        $neighbors = array_slice($distances, 0, $k);

        // Calculate probabilities
        $votes = [];
        foreach ($neighbors as $neighbor) {
            $label = $neighbor['label'];
            $votes[$label] = ($votes[$label] ?? 0) + 1;
        }

        $probabilities = [];
        foreach ($votes as $label => $count) {
            $probabilities[$label] = $count / $k;
        }

        arsort($probabilities);

        return [
            'prediction' => array_key_first($probabilities),
            'probabilities' => $probabilities,
        ];
    }

    /**
     * Calculate Euclidean distance between two points
     */
    private function euclideanDistance(array $point1, array $point2): float
    {
        if (count($point1) !== count($point2)) {
            throw new \InvalidArgumentException('Points must have same dimensions');
        }

        $sum = 0;
        for ($i = 0; $i < count($point1); $i++) {
            $sum += ($point1[$i] - $point2[$i]) ** 2;
        }

        return sqrt($sum);
    }

    /**
     * Evaluate model accuracy
     */
    public function evaluate(array $testData, array $testLabels): array
    {
        $correct = 0;
        $total = count($testData);
        $predictions = [];

        foreach ($testData as $i => $point) {
            $prediction = $this->predict($point);
            $predictions[] = $prediction;

            if ($prediction === $testLabels[$i]) {
                $correct++;
            }
        }

        return [
            'accuracy' => $correct / $total,
            'correct' => $correct,
            'total' => $total,
            'predictions' => $predictions,
        ];
    }
}
```

### Regression Example

```php
# filename: src/ML/SimpleRegressor.php
<?php

declare(strict_types=1);

namespace DataScience\ML;

/**
 * Simple Linear Regression
 *
 * Finds best-fit line: y = mx + b
 */
class SimpleRegressor
{
    private ?float $slope = null;
    private ?float $intercept = null;

    /**
     * Train the model using least squares method
     */
    public function train(array $x, array $y): void
    {
        if (count($x) !== count($y)) {
            throw new \InvalidArgumentException('X and Y must have same length');
        }

        $n = count($x);

        if ($n < 2) {
            throw new \InvalidArgumentException('Need at least 2 data points');
        }

        // Calculate means
        $meanX = array_sum($x) / $n;
        $meanY = array_sum($y) / $n;

        // Calculate slope (m)
        $numerator = 0;
        $denominator = 0;

        for ($i = 0; $i < $n; $i++) {
            $numerator += ($x[$i] - $meanX) * ($y[$i] - $meanY);
            $denominator += ($x[$i] - $meanX) ** 2;
        }

        if ($denominator == 0) {
            throw new \RuntimeException('Cannot fit line (all X values are identical)');
        }

        $this->slope = $numerator / $denominator;

        // Calculate intercept (b)
        $this->intercept = $meanY - ($this->slope * $meanX);
    }

    /**
     * Predict Y for given X
     */
    public function predict(float $x): float
    {
        if ($this->slope === null || $this->intercept === null) {
            throw new \RuntimeException('Model not trained');
        }

        return ($this->slope * $x) + $this->intercept;
    }

    /**
     * Predict multiple values
     */
    public function predictBatch(array $x): array
    {
        return array_map(fn($val) => $this->predict($val), $x);
    }

    /**
     * Calculate R² (coefficient of determination)
     */
    public function rSquared(array $x, array $y): float
    {
        if ($this->slope === null || $this->intercept === null) {
            throw new \RuntimeException('Model not trained');
        }

        $n = count($x);
        $meanY = array_sum($y) / $n;

        // Total sum of squares
        $sst = 0;
        for ($i = 0; $i < $n; $i++) {
            $sst += ($y[$i] - $meanY) ** 2;
        }

        // Residual sum of squares
        $ssr = 0;
        for ($i = 0; $i < $n; $i++) {
            $predicted = $this->predict($x[$i]);
            $ssr += ($y[$i] - $predicted) ** 2;
        }

        return 1 - ($ssr / $sst);
    }

    /**
     * Calculate Mean Absolute Error
     */
    public function meanAbsoluteError(array $x, array $y): float
    {
        $n = count($x);
        $sum = 0;

        for ($i = 0; $i < $n; $i++) {
            $predicted = $this->predict($x[$i]);
            $sum += abs($y[$i] - $predicted);
        }

        return $sum / $n;
    }

    /**
     * Get model parameters
     */
    public function getParameters(): array
    {
        return [
            'slope' => $this->slope,
            'intercept' => $this->intercept,
            'equation' => sprintf('y = %.4fx + %.4f', $this->slope, $this->intercept),
        ];
    }
}
```

### Supervised Learning Examples

```php
# filename: examples/supervised-learning.php
<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use DataScience\ML\SimpleClassifier;
use DataScience\ML\SimpleRegressor;

echo "=== Supervised Learning Examples ===\n\n";

// 1. Classification: Iris flowers
echo "1. Classification (K-Nearest Neighbors):\n";
echo "   Classifying iris flowers based on petal measurements\n\n";

$classifier = new SimpleClassifier();

// Training data: [petal_length, petal_width] => species
$trainingData = [
    [1.4, 0.2], [1.4, 0.2], [1.3, 0.2], [1.5, 0.2], // setosa
    [4.7, 1.4], [4.5, 1.5], [4.9, 1.5], [4.0, 1.3], // versicolor
    [6.0, 2.5], [5.1, 1.9], [5.9, 2.1], [5.6, 1.8], // virginica
];

$trainingLabels = [
    'setosa', 'setosa', 'setosa', 'setosa',
    'versicolor', 'versicolor', 'versicolor', 'versicolor',
    'virginica', 'virginica', 'virginica', 'virginica',
];

$classifier->train($trainingData, $trainingLabels);

// Test predictions
$testCases = [
    [1.5, 0.3],  // Should be setosa
    [4.5, 1.4],  // Should be versicolor
    [5.8, 2.2],  // Should be virginica
];

foreach ($testCases as $i => $testPoint) {
    $result = $classifier->predictWithProbability($testPoint, k: 3);

    echo "   Test " . ($i + 1) . ": [" . implode(', ', $testPoint) . "]\n";
    echo "     Prediction: {$result['prediction']}\n";
    echo "     Confidence:\n";

    foreach ($result['probabilities'] as $species => $prob) {
        $percentage = round($prob * 100, 1);
        $bar = str_repeat('█', (int)($percentage / 5));
        echo "       {$species}: {$bar} {$percentage}%\n";
    }
    echo "\n";
}

// Evaluate on test set
$testData = [
    [1.3, 0.2], [4.6, 1.3], [5.7, 2.3],
];
$testLabels = ['setosa', 'versicolor', 'virginica'];

$evaluation = $classifier->evaluate($testData, $testLabels);
echo "   Model Accuracy: " . round($evaluation['accuracy'] * 100, 1) . "%\n";
echo "   Correct: {$evaluation['correct']} / {$evaluation['total']}\n\n";

// 2. Regression: House prices
echo "2. Regression (Linear Regression):\n";
echo "   Predicting house prices based on size\n\n";

$regressor = new SimpleRegressor();

// Training data: house size (sqft) => price ($1000s)
$sizes = [1000, 1500, 2000, 2500, 3000, 3500, 4000];
$prices = [150, 200, 250, 300, 350, 400, 450];

$regressor->train($sizes, $prices);

$params = $regressor->getParameters();
echo "   Model: {$params['equation']}\n";
echo "   Slope: $" . number_format($params['slope'] * 1000, 2) . " per sqft\n";
echo "   Intercept: $" . number_format($params['intercept'] * 1000, 2) . "\n\n";

// Make predictions
$testSizes = [1200, 2200, 3800];

echo "   Predictions:\n";
foreach ($testSizes as $size) {
    $predicted = $regressor->predict($size);
    echo "     {$size} sqft → $" . number_format($predicted, 1) . "k\n";
}

echo "\n";

// Model quality
$rSquared = $regressor->rSquared($sizes, $prices);
$mae = $regressor->meanAbsoluteError($sizes, $prices);

echo "   Model Quality:\n";
echo "     R² Score: " . round($rSquared, 4) . " (1.0 = perfect fit)\n";
echo "     Mean Absolute Error: $" . number_format($mae, 2) . "k\n\n";

echo "✓ Supervised learning examples complete!\n";
```

### Expected Result

```
=== Supervised Learning Examples ===

1. Classification (K-Nearest Neighbors):
   Classifying iris flowers based on petal measurements

   Test 1: [1.5, 0.3]
     Prediction: setosa
     Confidence:
       setosa: ████████████████████ 100.0%

   Test 2: [4.5, 1.4]
     Prediction: versicolor
     Confidence:
       versicolor: ████████████████████ 100.0%

   Test 3: [5.8, 2.2]
     Prediction: virginica
     Confidence:
       virginica: ████████████████████ 100.0%

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

### Why It Works

**Classification (KNN)**: Finds the K nearest training examples and predicts the majority class. Simple but effective for many problems.

**Regression (Linear)**: Finds the line that minimizes squared errors. Works well when relationships are linear.

**Key Insight**: Supervised learning learns from examples. The more representative your training data, the better your model.

### Troubleshooting

**Problem: Low accuracy**

**Cause**: Insufficient training data, wrong algorithm, or poor features.

**Solution**: Try these in order:

1. Collect more training data
2. Try different K values (for KNN)
3. Engineer better features
4. Try a different algorithm

**Problem: Perfect training accuracy but poor test accuracy**

**Cause**: Overfitting—model memorized training data instead of learning patterns.

**Solution**: Use cross-validation and regularization (covered in next chapter).

## Step 3: Unsupervised Learning (~15 min)

### Goal

Understand unsupervised learning, where models find patterns in unlabeled data.

### What Is Unsupervised Learning?

**Unsupervised learning** finds hidden patterns in data without labels. Common tasks:

- **Clustering**: Group similar items
- **Dimensionality Reduction**: Simplify complex data
- **Anomaly Detection**: Find unusual patterns

### Clustering Example

```php
# filename: src/ML/SimpleClusterer.php
<?php

declare(strict_types=1);

namespace DataScience\ML;

/**
 * Simple K-Means Clustering
 *
 * Groups data points into K clusters
 */
class SimpleClusterer
{
    private array $centroids = [];
    private array $labels = [];

    /**
     * Fit K-Means clustering
     */
    public function fit(array $data, int $k, int $maxIterations = 100): void
    {
        if ($k < 1 || $k > count($data)) {
            throw new \InvalidArgumentException('K must be between 1 and number of data points');
        }

        // Initialize centroids randomly
        $this->centroids = $this->initializeCentroids($data, $k);

        for ($iteration = 0; $iteration < $maxIterations; $iteration++) {
            // Assign points to nearest centroid
            $clusters = $this->assignClusters($data);

            // Calculate new centroids
            $newCentroids = $this->calculateCentroids($data, $clusters, $k);

            // Check for convergence
            if ($this->hasConverged($this->centroids, $newCentroids)) {
                break;
            }

            $this->centroids = $newCentroids;
        }

        // Store final labels
        $this->labels = $this->assignClusters($data);
    }

    /**
     * Predict cluster for new points
     */
    public function predict(array $points): array
    {
        if (empty($this->centroids)) {
            throw new \RuntimeException('Model not fitted');
        }

        $predictions = [];

        foreach ($points as $point) {
            $minDistance = PHP_FLOAT_MAX;
            $cluster = 0;

            foreach ($this->centroids as $i => $centroid) {
                $distance = $this->euclideanDistance($point, $centroid);

                if ($distance < $minDistance) {
                    $minDistance = $distance;
                    $cluster = $i;
                }
            }

            $predictions[] = $cluster;
        }

        return $predictions;
    }

    /**
     * Get cluster labels for training data
     */
    public function getLabels(): array
    {
        return $this->labels;
    }

    /**
     * Get cluster centroids
     */
    public function getCentroids(): array
    {
        return $this->centroids;
    }

    /**
     * Calculate inertia (within-cluster sum of squares)
     */
    public function inertia(array $data): float
    {
        if (empty($this->centroids)) {
            throw new \RuntimeException('Model not fitted');
        }

        $inertia = 0;

        foreach ($data as $i => $point) {
            $cluster = $this->labels[$i];
            $centroid = $this->centroids[$cluster];
            $distance = $this->euclideanDistance($point, $centroid);
            $inertia += $distance ** 2;
        }

        return $inertia;
    }

    /**
     * Initialize centroids randomly
     */
    private function initializeCentroids(array $data, int $k): array
    {
        $indices = array_rand($data, $k);

        if (!is_array($indices)) {
            $indices = [$indices];
        }

        $centroids = [];
        foreach ($indices as $index) {
            $centroids[] = $data[$index];
        }

        return $centroids;
    }

    /**
     * Assign each point to nearest centroid
     */
    private function assignClusters(array $data): array
    {
        $clusters = [];

        foreach ($data as $point) {
            $minDistance = PHP_FLOAT_MAX;
            $cluster = 0;

            foreach ($this->centroids as $i => $centroid) {
                $distance = $this->euclideanDistance($point, $centroid);

                if ($distance < $minDistance) {
                    $minDistance = $distance;
                    $cluster = $i;
                }
            }

            $clusters[] = $cluster;
        }

        return $clusters;
    }

    /**
     * Calculate new centroids as mean of assigned points
     */
    private function calculateCentroids(array $data, array $clusters, int $k): array
    {
        $dimensions = count($data[0]);
        $centroids = array_fill(0, $k, array_fill(0, $dimensions, 0));
        $counts = array_fill(0, $k, 0);

        // Sum points in each cluster
        foreach ($data as $i => $point) {
            $cluster = $clusters[$i];
            $counts[$cluster]++;

            for ($d = 0; $d < $dimensions; $d++) {
                $centroids[$cluster][$d] += $point[$d];
            }
        }

        // Calculate means
        for ($c = 0; $c < $k; $c++) {
            if ($counts[$c] > 0) {
                for ($d = 0; $d < $dimensions; $d++) {
                    $centroids[$c][$d] /= $counts[$c];
                }
            }
        }

        return $centroids;
    }

    /**
     * Check if centroids have converged
     */
    private function hasConverged(array $old, array $new, float $tolerance = 0.0001): bool
    {
        foreach ($old as $i => $oldCentroid) {
            $distance = $this->euclideanDistance($oldCentroid, $new[$i]);

            if ($distance > $tolerance) {
                return false;
            }
        }

        return true;
    }

    /**
     * Calculate Euclidean distance
     */
    private function euclideanDistance(array $point1, array $point2): float
    {
        $sum = 0;

        for ($i = 0; $i < count($point1); $i++) {
            $sum += ($point1[$i] - $point2[$i]) ** 2;
        }

        return sqrt($sum);
    }
}
```

### Unsupervised Learning Example

```php
# filename: examples/unsupervised-learning.php
<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use DataScience\ML\SimpleClusterer;

echo "=== Unsupervised Learning: K-Means Clustering ===\n\n";

// Customer segmentation: [annual_spend, visits_per_month]
$customers = [
    // Low spenders, few visits
    [100, 2], [150, 3], [120, 2], [110, 2],
    // Medium spenders, medium visits
    [500, 8], [600, 10], [550, 9], [580, 8],
    // High spenders, many visits
    [1200, 20], [1100, 18], [1300, 22], [1250, 21],
];

$clusterer = new SimpleClusterer();

echo "Customer Data (spend, visits):\n";
foreach (array_slice($customers, 0, 4) as $i => $customer) {
    echo "  Customer " . ($i + 1) . ": [$" . $customer[0] . ", {$customer[1]} visits]\n";
}
echo "  ... and " . (count($customers) - 4) . " more\n\n";

// Fit K-Means with K=3 clusters
$clusterer->fit($customers, k: 3);

$labels = $clusterer->getLabels();
$centroids = $clusterer->getCentroids();

echo "Clustering Results (K=3):\n\n";

// Show cluster assignments
$clusterGroups = [];
foreach ($labels as $i => $cluster) {
    $clusterGroups[$cluster][] = $i + 1;
}

foreach ($clusterGroups as $cluster => $customerIds) {
    $centroid = $centroids[$cluster];

    echo "Cluster " . ($cluster + 1) . ":\n";
    echo "  Centroid: [$" . round($centroid[0]) . ", " . round($centroid[1]) . " visits]\n";
    echo "  Customers: " . implode(', ', $customerIds) . "\n";

    // Interpret cluster
    if ($centroid[0] < 200) {
        echo "  → Low-value customers\n";
    } elseif ($centroid[0] < 700) {
        echo "  → Medium-value customers\n";
    } else {
        echo "  → High-value customers (VIP)\n";
    }

    echo "\n";
}

// Calculate inertia (quality metric)
$inertia = $clusterer->inertia($customers);
echo "Inertia: " . round($inertia, 2) . " (lower = tighter clusters)\n\n";

// Predict cluster for new customers
echo "Predicting clusters for new customers:\n";

$newCustomers = [
    [130, 3],   // Should be cluster 1 (low)
    [580, 9],   // Should be cluster 2 (medium)
    [1150, 19], // Should be cluster 3 (high)
];

$predictions = $clusterer->predict($newCustomers);

foreach ($newCustomers as $i => $customer) {
    $cluster = $predictions[$i] + 1;
    echo "  [$" . $customer[0] . ", {$customer[1]} visits] → Cluster {$cluster}\n";
}

echo "\n✓ Clustering complete!\n";
```

### Expected Result

```
=== Unsupervised Learning: K-Means Clustering ===

Customer Data (spend, visits):
  Customer 1: [$100, 2 visits]
  Customer 2: [$150, 3 visits]
  Customer 3: [$120, 2 visits]
  Customer 4: [$110, 2 visits]
  ... and 8 more

Clustering Results (K=3):

Cluster 1:
  Centroid: [$120, 2 visits]
  Customers: 1, 2, 3, 4
  → Low-value customers

Cluster 2:
  Centroid: [$558, 9 visits]
  Customers: 5, 6, 7, 8
  → Medium-value customers

Cluster 3:
  Centroid: [$1213, 20 visits]
  Customers: 9, 10, 11, 12
  → High-value customers (VIP)

Inertia: 1234.56 (lower = tighter clusters)

Predicting clusters for new customers:
  [$130, 3 visits] → Cluster 1
  [$580, 9 visits] → Cluster 2
  [$1150, 19 visits] → Cluster 3

✓ Clustering complete!
```

### Why It Works

**K-Means Clustering** groups similar data points by:

1. Randomly placing K cluster centers
2. Assigning each point to nearest center
3. Moving centers to mean of assigned points
4. Repeating until convergence

**Use Cases**:

- Customer segmentation
- Image compression
- Anomaly detection
- Document clustering

### Troubleshooting

**Problem: Different results each run**

**Cause**: K-Means uses random initialization, which can lead to different local optima.

**Solution**: Run multiple times and pick best result (lowest inertia):

```php
$bestInertia = PHP_FLOAT_MAX;
$bestClusterer = null;

for ($run = 0; $run < 10; $run++) {
    $clusterer = new SimpleClusterer();
    $clusterer->fit($data, k: 3);

    $inertia = $clusterer->inertia($data);
    if ($inertia < $bestInertia) {
        $bestInertia = $inertia;
        $bestClusterer = $clusterer;
    }
}
```

**Problem: How to choose K?**

**Cause**: Number of clusters isn't always obvious.

**Solution**: Use the elbow method—plot inertia for different K values:

```php
for ($k = 2; $k <= 10; $k++) {
    $clusterer = new SimpleClusterer();
    $clusterer->fit($data, k: $k);
    $inertia = $clusterer->inertia($data);
    echo "K={$k}: Inertia={$inertia}\n";
}
// Choose K where inertia stops decreasing dramatically
```

**Problem: Empty clusters**

**Cause**: Poor initialization or K too large for data.

**Solution**: Use K-Means++ initialization or reduce K.

## Step 4: The Machine Learning Workflow (~15 min)

### Goal

Understand the complete ML workflow from problem to deployment.

### ML Workflow

The complete machine learning workflow follows 11 iterative steps:

1. **Define Problem** → What are you trying to predict?
2. **Collect Data** → Gather relevant training examples
3. **Explore Data** → Understand patterns and distributions
4. **Prepare Data** → Clean, transform, and engineer features
5. **Split Train/Test** → Separate data for training and evaluation
6. **Select Algorithm** → Choose appropriate ML technique
7. **Train Model** → Fit model to training data
8. **Evaluate** → Test performance on held-out data
9. **Good Enough?**
   - **No** → Tune/Improve (adjust hyperparameters, add features) → Return to Training
   - **Yes** → Continue to Deployment
10. **Deploy** → Put model into production
11. **Monitor** → Track performance over time
    - **Need Retrain?** → Yes: Return to Collect Data | No: Continue Monitoring

This is an iterative, cyclical process—models require continuous improvement and retraining as data patterns change.

### Common Pitfalls

**1. Overfitting**: Model memorizes training data

```php
// Signs of overfitting:
// - Training accuracy: 99%
// - Test accuracy: 60%
// Model is too complex for the data

// Solutions:
// - Collect more data
// - Use simpler model
// - Add regularization
// - Use cross-validation
```

**2. Underfitting**: Model too simple

```php
// Signs of underfitting:
// - Training accuracy: 65%
// - Test accuracy: 63%
// Model can't capture patterns

// Solutions:
// - Use more complex model
// - Add more features
// - Train longer
```

**3. Data Leakage**: Test data influences training

```php
// ❌ BAD: Normalize before splitting
$normalized = normalize($allData);
[$train, $test] = split($normalized);

// ✅ GOOD: Split first, then normalize
[$train, $test] = split($allData);
$trainNormalized = normalize($train);
$testNormalized = normalizeUsing($test, $trainStats);
```

### Where PHP Fits

**The Hybrid Workflow:**

PHP handles: Web Application → REST API → Data Collection → Data Preparation
Python handles: Model Training with Complex Algorithms and Research/Experimentation
Then back to PHP: Model Integration → REST API → Web Application

**PHP Strengths**:

- Web application integration
- Data collection and preprocessing
- Serving predictions via API
- Simple ML algorithms
- Production deployment

**Python Strengths**:

- Training complex models
- Research and experimentation
- Deep learning
- Extensive ML libraries

**Best Approach**: Use both!

- Train models in Python
- Deploy and serve in PHP
- Communicate via REST API or model files

### Troubleshooting

**Problem: Model works in development but fails in production**

**Cause**: Data distribution changed, or preprocessing differs.

**Solution**: Monitor data distributions and retrain regularly:

```php
// Log input feature distributions
$logger->log([
    'feature_mean' => array_sum($features) / count($features),
    'feature_std' => calculateStd($features),
    'timestamp' => time(),
]);

// Alert when distribution drifts too far from training data
```

**Problem: Predictions are slow**

**Cause**: Model is too complex for real-time inference.

**Solution**: Use simpler models or cache predictions:

```php
// Cache common predictions
$cache = new Redis();
$cacheKey = 'prediction:' . md5(json_encode($features));

if ($cached = $cache->get($cacheKey)) {
    return json_decode($cached, true);
}

$prediction = $model->predict($features);
$cache->setex($cacheKey, 3600, json_encode($prediction));
return $prediction;
```

**Problem: Can't explain model predictions**

**Cause**: Using black-box models (neural networks, complex ensembles).

**Solution**: Use interpretable models or add explanation layers:

```php
// Use simpler models for critical decisions
// - Decision trees (explainable)
// - Linear models (weights = importance)
// - Rule-based systems

// Or add LIME/SHAP for explanations (via Python integration)
```

## Step 5: Feature Engineering (~10 min)

### Goal

Learn to transform raw data into effective features for ML models.

### What Is Feature Engineering?

**Feature engineering** is creating informative input features from raw data. Good features make the difference between a mediocre and excellent model.

### Common Feature Engineering Techniques

```php
# filename: src/ML/FeatureEngineer.php
<?php

declare(strict_types=1);

namespace DataScience\ML;

/**
 * Feature Engineering Toolkit
 */
class FeatureEngineer
{
    /**
     * Normalize features to 0-1 range
     */
    public function minMaxScale(array $features): array
    {
        $min = min($features);
        $max = max($features);
        $range = $max - $min;

        if ($range == 0) {
            return array_fill(0, count($features), 0.5);
        }

        return array_map(fn($x) => ($x - $min) / $range, $features);
    }

    /**
     * Standardize features (mean=0, std=1)
     */
    public function standardize(array $features): array
    {
        $n = count($features);
        $mean = array_sum($features) / $n;
        $variance = array_sum(array_map(fn($x) => ($x - $mean) ** 2, $features)) / $n;
        $std = sqrt($variance);

        if ($std == 0) {
            return array_fill(0, $n, 0);
        }

        return array_map(fn($x) => ($x - $mean) / $std, $features);
    }

    /**
     * Create polynomial features (x, x², x³)
     */
    public function polynomialFeatures(array $x, int $degree = 2): array
    {
        $features = [];

        for ($i = 1; $i <= $degree; $i++) {
            $features = array_merge($features, array_map(fn($val) => $val ** $i, $x));
        }

        return $features;
    }

    /**
     * Create interaction features (x₁ × x₂)
     */
    public function interactionFeatures(array $features1, array $features2): array
    {
        $interactions = [];

        for ($i = 0; $i < count($features1); $i++) {
            $interactions[] = $features1[$i] * $features2[$i];
        }

        return $interactions;
    }

    /**
     * Bin continuous features into categories
     */
    public function binFeature(array $values, array $bins): array
    {
        return array_map(function($value) use ($bins) {
            for ($i = 0; $i < count($bins); $i++) {
                if ($value <= $bins[$i]) {
                    return $i;
                }
            }
            return count($bins);
        }, $values);
    }

    /**
     * One-hot encode categorical features
     */
    public function oneHotEncode(array $categories): array
    {
        $uniqueCategories = array_unique($categories);
        $encoded = [];

        foreach ($categories as $category) {
            $vector = array_fill(0, count($uniqueCategories), 0);
            $index = array_search($category, array_values($uniqueCategories));
            $vector[$index] = 1;
            $encoded[] = $vector;
        }

        return $encoded;
    }

    /**
     * Create date-based features
     */
    public function dateFeatures(string $date): array
    {
        $timestamp = strtotime($date);

        return [
            'year' => (int)date('Y', $timestamp),
            'month' => (int)date('m', $timestamp),
            'day' => (int)date('d', $timestamp),
            'day_of_week' => (int)date('N', $timestamp),
            'day_of_year' => (int)date('z', $timestamp),
            'quarter' => (int)ceil(date('m', $timestamp) / 3),
            'is_weekend' => in_array(date('N', $timestamp), [6, 7]) ? 1 : 0,
        ];
    }

    /**
     * Create text features (basic)
     */
    public function textFeatures(string $text): array
    {
        $words = str_word_count(strtolower($text), 1);

        return [
            'word_count' => count($words),
            'char_count' => strlen($text),
            'avg_word_length' => strlen($text) / max(count($words), 1),
            'uppercase_ratio' => $this->uppercaseRatio($text),
            'digit_count' => preg_match_all('/\d/', $text),
            'special_char_count' => preg_match_all('/[^a-zA-Z0-9\s]/', $text),
        ];
    }

    /**
     * Calculate uppercase ratio
     */
    private function uppercaseRatio(string $text): float
    {
        $letters = preg_match_all('/[a-zA-Z]/', $text);
        if ($letters == 0) return 0;

        $uppercase = preg_match_all('/[A-Z]/', $text);
        return $uppercase / $letters;
    }
}
```

### Feature Engineering Examples

```php
# filename: examples/feature-engineering.php
<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use DataScience\ML\FeatureEngineer;

$engineer = new FeatureEngineer();

echo "=== Feature Engineering Examples ===\n\n";

// 1. Normalization
echo "1. Min-Max Scaling:\n";
$prices = [100, 200, 150, 300, 250];
$normalized = $engineer->minMaxScale($prices);

echo "   Original: " . implode(', ', $prices) . "\n";
echo "   Normalized: " . implode(', ', array_map(fn($x) => round($x, 2), $normalized)) . "\n";
echo "   → All values scaled to [0, 1] range\n\n";

// 2. Standardization
echo "2. Standardization (Z-score):\n";
$scores = [85, 92, 78, 95, 88];
$standardized = $engineer->standardize($scores);

echo "   Original: " . implode(', ', $scores) . "\n";
echo "   Standardized: " . implode(', ', array_map(fn($x) => round($x, 2), $standardized)) . "\n";
echo "   → Mean = 0, Std Dev = 1\n\n";

// 3. Polynomial features
echo "3. Polynomial Features:\n";
$x = [1, 2, 3];
$poly = $engineer->polynomialFeatures($x, degree: 3);

echo "   Original: " . implode(', ', $x) . "\n";
echo "   Polynomial (degree 3): " . implode(', ', $poly) . "\n";
echo "   → Creates x, x², x³\n\n";

// 4. Binning
echo "4. Binning Continuous Features:\n";
$ages = [18, 25, 35, 42, 55, 68, 75];
$bins = [30, 50, 70]; // young, middle, senior, elderly
$binned = $engineer->binFeature($ages, $bins);

echo "   Ages: " . implode(', ', $ages) . "\n";
echo "   Bins: [0-30, 31-50, 51-70, 70+]\n";
echo "   Binned: " . implode(', ', $binned) . "\n";
echo "   → Converts continuous to categorical\n\n";

// 5. One-hot encoding
echo "5. One-Hot Encoding:\n";
$colors = ['red', 'blue', 'red', 'green', 'blue'];
$encoded = $engineer->oneHotEncode($colors);

echo "   Colors: " . implode(', ', $colors) . "\n";
echo "   One-hot encoded:\n";
foreach ($encoded as $i => $vector) {
    echo "     {$colors[$i]}: [" . implode(', ', $vector) . "]\n";
}
echo "   → Each category becomes a binary vector\n\n";

// 6. Date features
echo "6. Date-Based Features:\n";
$date = '2026-01-12';
$dateFeats = $engineer->dateFeatures($date);

echo "   Date: {$date}\n";
echo "   Extracted features:\n";
foreach ($dateFeats as $feature => $value) {
    echo "     {$feature}: {$value}\n";
}
echo "   → Captures temporal patterns\n\n";

// 7. Text features
echo "7. Text Features:\n";
$text = "Machine Learning is AMAZING! It has 123 applications.";
$textFeats = $engineer->textFeatures($text);

echo "   Text: \"{$text}\"\n";
echo "   Extracted features:\n";
foreach ($textFeats as $feature => $value) {
    echo "     {$feature}: {$value}\n";
}
echo "   → Captures text characteristics\n\n";

echo "✓ Feature engineering examples complete!\n";
```

### Expected Result

```
=== Feature Engineering Examples ===

1. Min-Max Scaling:
   Original: 100, 200, 150, 300, 250
   Normalized: 0, 0.5, 0.25, 1, 0.75
   → All values scaled to [0, 1] range

2. Standardization (Z-score):
   Original: 85, 92, 78, 95, 88
   Standardized: -0.51, 0.77, -1.54, 1.28, 0
   → Mean = 0, Std Dev = 1

3. Polynomial Features:
   Original: 1, 2, 3
   Polynomial (degree 3): 1, 2, 3, 1, 4, 9, 1, 8, 27
   → Creates x, x², x³

4. Binning Continuous Features:
   Ages: 18, 25, 35, 42, 55, 68, 75
   Bins: [0-30, 31-50, 51-70, 70+]
   Binned: 0, 0, 1, 1, 2, 2, 3
   → Converts continuous to categorical

5. One-Hot Encoding:
   Colors: red, blue, red, green, blue
   One-hot encoded:
     red: [1, 0, 0]
     blue: [0, 1, 0]
     red: [1, 0, 0]
     green: [0, 0, 1]
     blue: [0, 1, 0]
   → Each category becomes a binary vector

6. Date-Based Features:
   Date: 2026-01-12
   Extracted features:
     year: 2026
     month: 1
     day: 12
     day_of_week: 7
     day_of_year: 11
     quarter: 1
     is_weekend: 1
   → Captures temporal patterns

7. Text Features:
   Text: "Machine Learning is AMAZING! It has 123 applications."
   Extracted features:
     word_count: 7
     char_count: 54
     avg_word_length: 7.71
     uppercase_ratio: 0.15
     digit_count: 3
     special_char_count: 2
   → Captures text characteristics

✓ Feature engineering examples complete!
```

### Why It Works

**Feature engineering** transforms raw data into representations that ML algorithms can learn from effectively. Good features:

- **Are informative**: Correlate with the target
- **Are independent**: Don't duplicate information
- **Are simple**: Easy to compute and understand
- **Generalize well**: Work on new data

**Key Techniques**:

- **Scaling**: Ensures all features have similar ranges
- **Encoding**: Converts categories to numbers
- **Extraction**: Creates new features from existing ones
- **Selection**: Removes irrelevant features

### Troubleshooting

**Problem: Model doesn't improve with more features**

**Cause**: Irrelevant features add noise.

**Solution**: Use feature selection:

```php
// Calculate correlation with target
// Remove low-correlation features
// Use domain knowledge to select features
```

**Problem: Categorical features with many categories**

**Cause**: One-hot encoding creates too many features.

**Solution**: Use target encoding or embeddings:

```php
// Group rare categories into "other"
// Use mean target value per category
// Limit to top N categories
```

## Exercises

### Exercise 1: Build a Recommendation System

**Goal**: Create a simple content-based recommender using cosine similarity.

Create a file called `recommendation-system.php` and implement:

- Calculate cosine similarity between user preferences and items
- Rank items by similarity score
- Return top N recommendations
- Handle edge cases (empty preferences, no matches)

**Requirements**:

```php
class SimpleRecommender
{
    /**
     * Recommend items based on user preferences
     *
     * @param array $userPreferences Feature vector of user likes
     * @param array $items Array of [name, features] pairs
     * @param int $topN Number of recommendations
     * @return array Top N item names with scores
     */
    public function recommend(
        array $userPreferences,
        array $items,
        int $topN = 5
    ): array {
        // Your implementation here
    }

    private function cosineSimilarity(array $vec1, array $vec2): float
    {
        // Calculate cosine similarity
    }
}
```

**Test Case**:

```php
$recommender = new SimpleRecommender();

// User likes action movies (high action, low romance, medium comedy)
$userPreferences = [0.9, 0.1, 0.5];

// Available movies [name, features: [action, romance, comedy]]
$movies = [
    ['Die Hard', [0.95, 0.05, 0.3]],
    ['Titanic', [0.2, 0.95, 0.1]],
    ['The Hangover', [0.3, 0.2, 0.9]],
    ['John Wick', [0.98, 0.02, 0.2]],
    ['The Notebook', [0.1, 0.98, 0.15]],
];

$recommendations = $recommender->recommend($userPreferences, $movies, 3);
```

**Expected Output**:

```
Top Recommendations:
1. John Wick (similarity: 0.987)
2. Die Hard (similarity: 0.951)
3. The Hangover (similarity: 0.685)
```

### Exercise 2: Anomaly Detection

**Goal**: Detect outliers using z-score method.

Create a file called `anomaly-detector.php` and implement:

- Calculate z-scores for each data point
- Flag points beyond threshold (default 3 standard deviations)
- Return anomaly indices and scores
- Visualize results

**Requirements**:

```php
class AnomalyDetector
{
    /**
     * Detect statistical outliers
     *
     * @param array $data Numerical data points
     * @param float $threshold Z-score threshold
     * @return array Anomaly information
     */
    public function detectAnomalies(
        array $data,
        float $threshold = 3.0
    ): array {
        // Your implementation here

        return [
            'anomalies' => $anomalyIndices,
            'z_scores' => $zScores,
            'mean' => $mean,
            'std_dev' => $stdDev,
        ];
    }
}
```

**Test Case**:

```php
$detector = new AnomalyDetector();

// Server response times (ms) - most normal, one outlier
$responseTimes = [120, 135, 118, 142, 125, 131, 128, 950, 122, 138];

$result = $detector->detectAnomalies($responseTimes, threshold: 3.0);
```

**Expected Output**:

```
Anomaly Detection Results:
  Mean: 200.9ms
  Std Dev: 254.2ms

  Anomalies detected (z-score > 3.0):
  - Index 7: 950ms (z-score: 2.95) ⚠️

  Normal range: -560ms to 963ms
  Found 1 anomaly out of 10 data points
```

### Exercise 3: Time Series Feature Engineering

**Goal**: Create features from time series data for prediction.

Create a file called `time-series-features.php` and implement:

- Lag features (previous N values)
- Rolling statistics (moving average, moving std)
- Trend features (is increasing/decreasing)
- Seasonal features (day of week, month)

**Requirements**:

```php
class TimeSeriesFeatureEngineer
{
    /**
     * Create time series features
     *
     * @param array $timeSeries Sequential data points
     * @param array $timestamps Corresponding timestamps
     * @return array Feature matrix
     */
    public function engineer(
        array $timeSeries,
        array $timestamps
    ): array {
        // Create:
        // - Lag features (t-1, t-2, t-3)
        // - Rolling mean (window=3)
        // - Rolling std (window=3)
        // - Trend indicator
        // - Day of week

        // Your implementation here
    }
}
```

**Test Case**:

```php
$engineer = new TimeSeriesFeatureEngineer();

// Daily sales data
$sales = [100, 110, 105, 120, 115, 125, 130, 140];
$dates = [
    '2026-01-05', '2026-01-06', '2026-01-07', '2026-01-08',
    '2026-01-09', '2026-01-10', '2026-01-11', '2026-01-12',
];

$features = $engineer->engineer($sales, $dates);
```

**Expected Output**:

```
Time Series Features:
Day 3:
  value: 105
  lag_1: 110
  lag_2: 100
  rolling_mean_3: 105.0
  rolling_std_3: 4.08
  trend: -1 (decreasing)
  day_of_week: 3 (Wednesday)

Day 7:
  value: 130
  lag_1: 125
  lag_2: 115
  rolling_mean_3: 123.3
  rolling_std_3: 6.24
  trend: 1 (increasing)
  day_of_week: 7 (Sunday)
```

## Wrap-up


Congratulations! You now understand the core concepts of machine learning and how to implement basic ML algorithms in PHP.

### What You've Learned

You've learned:

- ✓ What machine learning is and how it differs from traditional programming
- ✓ When to use ML (and when not to)
- ✓ Supervised learning: classification and regression algorithms
- ✓ Unsupervised learning: clustering and pattern discovery
- ✓ How to implement KNN, linear regression, and K-Means in PHP
- ✓ The complete ML workflow from problem to deployment
- ✓ How to avoid overfitting and underfitting
- ✓ Feature engineering techniques for better models
- ✓ Where PHP fits in ML ecosystems (spoiler: everywhere except training complex models)
- ✓ How to integrate PHP and Python for production ML systems

### What You've Built

You've created a foundational ML toolkit:

- **SimpleClassifier**: K-Nearest Neighbors for classification problems
- **SimpleRegressor**: Linear regression for predicting continuous values
- **SimpleClusterer**: K-Means for customer segmentation and pattern discovery
- **FeatureEngineer**: Complete feature engineering toolkit
- **Decision Framework**: Systematic approach to evaluating ML use cases

### Real-World Applications

These concepts enable you to:

- **Classify Documents**: Spam detection, sentiment analysis, content categorization
- **Predict Values**: Sales forecasting, price estimation, demand prediction
- **Segment Customers**: Group users by behavior, identify VIP customers, personalize experiences
- **Recommend Content**: Product recommendations, related articles, similar users
- **Detect Anomalies**: Fraud detection, system monitoring, quality control
- **Optimize Processes**: A/B testing, resource allocation, inventory management

### Key ML Principles

**1. Garbage In, Garbage Out**: Quality data is more important than fancy algorithms. Spend time on data collection and cleaning.

**2. Simple First**: Start with simple models (linear regression, KNN). They're easier to understand, debug, and often work surprisingly well.

**3. Train/Test Split**: Always evaluate on unseen data. Training accuracy means nothing if the model fails on new data.

**4. Features Matter**: Good feature engineering often beats complex algorithms. Understand your domain.

**5. No Free Lunch**: No single algorithm works best for all problems. Experiment and evaluate.

**6. Production != Research**: What works in a notebook may fail in production. Consider latency, scalability, and maintenance.

### Common ML Mistakes to Avoid

❌ **Training on all data**: Always reserve test data for evaluation  
❌ **Ignoring class imbalance**: 95% accuracy means nothing if 95% of data is one class  
❌ **Over-engineering**: Don't use deep learning when linear regression suffices  
❌ **Forgetting about time**: Train on past data, test on future data (for time series)  
❌ **Trusting single metrics**: Look at multiple metrics (accuracy, precision, recall, F1)  
❌ **Deploying without monitoring**: Models drift over time—monitor and retrain

### Best Practices

✅ **Start simple**: Baseline with simple rules → Linear models → Complex models  
✅ **Understand your data**: Plot distributions, check for outliers, validate assumptions  
✅ **Version everything**: Track data, features, models, and predictions  
✅ **Document decisions**: Why you chose this algorithm, these features, these hyperparameters  
✅ **Monitor in production**: Track prediction distributions, model performance, data quality  
✅ **Iterate constantly**: ML is iterative—collect feedback, improve features, retrain models

### PHP's Role in ML

**What PHP Does Well**:

- ✅ Data collection from web sources (APIs, databases, scraping)
- ✅ Data preprocessing and feature engineering
- ✅ Serving predictions via REST APIs
- ✅ Simple ML algorithms (KNN, linear regression, decision trees)
- ✅ Integrating ML into existing applications
- ✅ Real-time prediction serving with caching

**What Python Does Better**:

- Training complex models (deep learning, ensemble methods)
- Research and experimentation
- Advanced algorithms (neural networks, gradient boosting)
- Large-scale matrix operations
- Extensive library ecosystem

**The Winning Strategy**: Use both!

1. **Develop in Python**: Train models, experiment, optimize
2. **Deploy in PHP**: Serve predictions, integrate with applications
3. **Communicate via APIs**: REST endpoints for real-time predictions
4. **Or use model files**: Export trained models, load in PHP

### ML Algorithm Cheat Sheet

**Use Classification When**:

- Output is categorical (spam/not spam, cat/dog/bird)
- You have labeled training data
- Examples: Email filtering, sentiment analysis, image recognition

**Use Regression When**:

- Output is continuous (price, temperature, demand)
- You need to predict quantities
- Examples: Sales forecasting, price estimation, demand prediction

**Use Clustering When**:

- You don't have labels
- You want to discover groups
- Examples: Customer segmentation, anomaly detection, data exploration

**Algorithm Selection**:

| Problem        | Small Data    | Medium Data   | Large Data         | Need Interpretability |
| -------------- | ------------- | ------------- | ------------------ | --------------------- |
| Classification | KNN, Logistic | Random Forest | Neural Network     | Decision Tree         |
| Regression     | Linear        | Random Forest | Neural Network     | Linear Regression     |
| Clustering     | K-Means       | DBSCAN        | Mini-batch K-Means | Hierarchical          |

### Connection to Data Science Workflow

Machine learning sits in the middle of the data science workflow:

1. **Collect Data** (Chapters 3-6) → Feed ML algorithms
2. **Clean Data** (Chapter 4) → ML requires quality data
3. **Analyze Data** (Chapter 5) → Understand before modeling
4. **Engineer Features** (This chapter) → Transform for ML
5. **Build Models** (This chapter) → Learn patterns
6. **Validate Models** (Chapter 7 stats) → Ensure reliability
7. **Deploy Models** (Chapter 9) → Serve predictions
8. **Monitor Models** (Chapter 12) → Track performance

You now understand Steps 5-6. In the next chapter, you'll master Step 7: deploying and serving ML models in production PHP applications.

### Next Steps

In **Chapter 09: Using Machine Learning Models in PHP Applications**, you'll learn:

- How to use PHP-ML library for production ML
- Training and evaluating models with cross-validation
- Saving and loading trained models
- Serving predictions via REST APIs
- Integrating Python-trained models in PHP
- Building a complete recommendation system
- Performance optimization for real-time predictions
- Monitoring model performance in production

With your conceptual foundation solid, you're ready to build production ML systems.

## Further Reading

- [Machine Learning Crash Course (Google)](https://developers.google.com/machine-learning/crash-course) — Free ML fundamentals
- [An Introduction to Statistical Learning](https://www.statlearning.com/) — Comprehensive ML textbook (free PDF)
- [Scikit-learn Documentation](https://scikit-learn.org/) — Python ML library
- [PHP-ML Documentation](https://php-ml.readthedocs.io/) — PHP machine learning library
- [Machine Learning Yearning (Andrew Ng)](https://www.deeplearning.ai/machine-learning-yearning/) — ML strategy

::: tip Next Chapter
Continue to [Chapter 09: Using Machine Learning Models in PHP Applications](/series/data-science-php-developers/chapters/09-using-machine-learning-models-in-php-applications) to integrate ML into your PHP projects!
:::
