---
title: "00: Introduction to Machine Learning"
description: "Understand what machine learning is, explore supervised vs unsupervised learning, and build your first ML model in PHP"
series: "php-ml"
chapter: 0
order: 0
difficulty: "Advanced"
prerequisites:
  - "Solid understanding of PHP 8.4+"
  - "Familiarity with arrays and object-oriented programming"
  - "Basic understanding of statistics (mean, variance)"
---

![00: Introduction to Machine Learning](/images/php-ml/chapter-00-introduction-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/php-ml">Machine Learning with PHP-ML</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 00</span>
</div>

# Chapter 00: Introduction to Machine Learning

## Overview

Welcome to **Machine Learning with PHP-ML**! This chapter introduces machine learning fundamentals and shows you how to apply ML concepts in PHP. You'll learn what machine learning is, explore different types of learning (supervised, unsupervised, reinforcement), and build your first ML model.

Understanding ML helps you build intelligent PHP applications that learn from data and make predictions. Whether you're building recommendation systems, spam filters, or predictive analytics, ML thinking is an essential skill for modern developers.

By the end of this chapter, you'll have a solid foundation in ML concepts and be ready to dive deep into specific algorithms and techniques.

## Prerequisites

Before starting this chapter, you should have:

- PHP 8.4+ installed and confirmed working with `php --version`
- Understanding of PHP arrays, loops, and functions
- Familiarity with object-oriented programming in PHP
- Basic statistics knowledge (mean, variance, distribution)
- Composer installed for package management

**Estimated Time**: ~90 minutes

**Verify your setup:**

```bash
# Check PHP version
php --version

# Check Composer
composer --version
```

## What You'll Build

By the end of this chapter, you will have:

- Understanding of machine learning fundamentals and terminology
- Knowledge of supervised vs unsupervised learning paradigms
- Working examples of classification, regression, and clustering
- Your first machine learning model implemented in PHP
- A systematic framework for approaching ML problems
- Experience with practical ML use cases in web development

## Objectives

- Understand what machine learning is and how it differs from traditional programming
- Learn the three main types of ML: supervised, unsupervised, and reinforcement learning
- Explore classification, regression, and clustering problem types
- Build your first ML classifier using simple algorithms
- Develop intuition for when to use machine learning vs traditional approaches
- Set up a mental framework for ML problem-solving

## Step 1: Understanding What Machine Learning Is (~15 min)

### Goal

Learn what machine learning is and how it differs from traditional programming.

### Actions

1. **Traditional Programming vs Machine Learning**:

Traditional programming:
```php
// Traditional: You write explicit rules
function categorizeEmail(string $email): string
{
    $spamWords = ['winner', 'free', 'click here', 'congratulations'];

    foreach ($spamWords as $word) {
        if (stripos($email, $word) !== false) {
            return 'spam';
        }
    }

    return 'not_spam';
}

// Problem: What if spam evolves? You must manually update rules.
```

Machine learning:
```php
// Machine Learning: The algorithm learns patterns from examples
// You provide examples (training data), and it discovers patterns

$trainingEmails = [
    ['text' => 'You won a free prize!', 'label' => 'spam'],
    ['text' => 'Meeting at 3pm tomorrow', 'label' => 'not_spam'],
    ['text' => 'Click here for amazing deals', 'label' => 'spam'],
    // ... thousands more examples
];

// Algorithm learns: "emails with 'won', 'free', 'click' are likely spam"
// It generalizes to NEW emails it has never seen before
```

2. **Core ML Concept**: Machine learning finds patterns in data automatically, rather than requiring you to specify every rule explicitly.

3. **When to Use ML**:
   - Pattern recognition (spam detection, image recognition)
   - Prediction (stock prices, customer churn, demand forecasting)
   - Personalization (recommendations, content ranking)
   - Discovery (customer segmentation, anomaly detection)

### Expected Result

You'll understand that ML is about:
- **Learning from data** instead of explicit programming
- **Generalizing** from examples to new situations
- **Discovering patterns** that may not be obvious to humans

### Why It Works

ML algorithms use statistical methods to find correlations and patterns in training data, then apply those patterns to new data. This approach works when:
- You have lots of examples (data)
- Patterns exist but are complex or hard to code manually
- Requirements change frequently (spam tactics evolve)

### Troubleshooting

- **"When should I NOT use ML?"** — Use traditional code when rules are simple, data is scarce, or you need 100% explainable logic
- **"How much data do I need?"** — Depends on complexity; start with hundreds of examples for simple problems, thousands+ for complex ones
- **"Can ML learn anything from any data?"** — No; ML requires patterns to exist and sufficient relevant features

## Step 2: Understanding Supervised Learning (~15 min)

### Goal

Learn supervised learning where models learn from labeled examples.

### Actions

1. **Supervised Learning Concept**: You provide input-output pairs (labels), and the algorithm learns the mapping.

```php
// Classification Example: Predict category
$trainingData = [
    // [feature1, feature2, feature3] => label
    [1, 0, 1] => 'A',  // Pattern for category A
    [1, 0, 0] => 'A',
    [0, 1, 1] => 'B',  // Pattern for category B
    [0, 1, 0] => 'B',
];

// After training, predict new examples:
predict([1, 0, 1]);  // Likely 'A' (similar to training examples)
predict([0, 1, 1]);  // Likely 'B'
```

2. **Two Types of Supervised Learning**:

**Classification** (discrete categories):
```php
// Examples:
// Email → spam or not_spam
// Image → cat, dog, or bird
// Transaction → fraudulent or legitimate
// Customer → will_churn or will_stay
```

**Regression** (continuous values):
```php
// Examples:
// House features → price ($250,000)
// Student hours studied → exam score (85.5)
// Website traffic → revenue ($12,450.23)
// Temperature + humidity → ice cream sales (342 units)
```

3. **Simple Classification Example**:

```php
# filename: simple-classifier.php
<?php

declare(strict_types=1);

// Simple rule-based classifier as ML introduction
class SimpleClassifier
{
    private array $trainingData = [];

    public function train(array $features, string $label): void
    {
        $this->trainingData[] = [
            'features' => $features,
            'label' => $label
        ];
    }

    public function predict(array $features): string
    {
        // Find most similar training example (simplified k-NN)
        $bestMatch = null;
        $bestDistance = PHP_FLOAT_MAX;

        foreach ($this->trainingData as $example) {
            $distance = $this->euclideanDistance($features, $example['features']);

            if ($distance < $bestDistance) {
                $bestDistance = $distance;
                $bestMatch = $example['label'];
            }
        }

        return $bestMatch ?? 'unknown';
    }

    private function euclideanDistance(array $a, array $b): float
    {
        $sum = 0.0;
        for ($i = 0; $i < count($a); $i++) {
            $sum += ($a[$i] - $b[$i]) ** 2;
        }
        return sqrt($sum);
    }
}

// Example: Classify fruits based on [weight_grams, sweetness_scale]
$classifier = new SimpleClassifier();

// Training
$classifier->train([150, 8], 'apple');
$classifier->train([160, 9], 'apple');
$classifier->train([140, 7], 'apple');
$classifier->train([300, 6], 'orange');
$classifier->train([320, 7], 'orange');
$classifier->train([280, 5], 'orange');

// Prediction
echo $classifier->predict([155, 8]) . "\n";  // apple
echo $classifier->predict([310, 6]) . "\n";  // orange
echo $classifier->predict([200, 7]) . "\n";  // ? (between apple and orange)
```

### Expected Result

```
apple
orange
apple
```

### Why It Works

The classifier finds the training example most similar to the input (using Euclidean distance) and returns its label. This is a simplified version of k-Nearest Neighbors (k-NN) algorithm.

### Troubleshooting

- **All predictions return same label** — Check that training data has diverse examples for each class
- **Predictions seem random** — Features may not be discriminative; try different features
- **Distance calculation error** — Ensure feature arrays have the same length

## Step 3: Understanding Unsupervised Learning (~15 min)

### Goal

Learn unsupervised learning where models find patterns without labels.

### Actions

1. **Unsupervised Learning Concept**: No labels provided; algorithm discovers structure in data.

```php
// No labels! Just raw data
$customers = [
    [25, 50000],  // [age, income]
    [27, 52000],
    [45, 90000],
    [48, 95000],
    [65, 40000],
    [67, 38000],
];

// Algorithm discovers: "There seem to be 3 groups here"
// Group 1: Young, moderate income
// Group 2: Middle-aged, high income
// Group 3: Seniors, moderate income
```

2. **Common Unsupervised Tasks**:

**Clustering** (grouping similar items):
```php
// Customer segmentation
// Document organization
// Anomaly detection
// Image compression
```

**Dimensionality Reduction** (simplify complex data):
```php
// Reduce 100 features to 10 key features
// Visualize high-dimensional data in 2D/3D
// Remove noise and redundancy
```

**Association Rule Learning** (discover relationships):
```php
// Market basket analysis: "Customers who buy X also buy Y"
// Product recommendations
// Pattern discovery
```

3. **Simple Clustering Example**:

```php
# filename: simple-clustering.php
<?php

declare(strict_types=1);

// Simple k-Means clustering (2 clusters)
class SimpleClustering
{
    public function cluster(array $points, int $k = 2): array
    {
        // Step 1: Initialize random centroids
        $centroids = array_slice($points, 0, $k);

        $iterations = 10;
        for ($iter = 0; $iter < $iterations; $iter++) {
            // Step 2: Assign each point to nearest centroid
            $clusters = array_fill(0, $k, []);

            foreach ($points as $point) {
                $nearestCluster = $this->findNearestCentroid($point, $centroids);
                $clusters[$nearestCluster][] = $point;
            }

            // Step 3: Update centroids
            for ($i = 0; $i < $k; $i++) {
                if (empty($clusters[$i])) continue;
                $centroids[$i] = $this->calculateMean($clusters[$i]);
            }
        }

        return $clusters;
    }

    private function findNearestCentroid(array $point, array $centroids): int
    {
        $nearest = 0;
        $minDistance = PHP_FLOAT_MAX;

        foreach ($centroids as $index => $centroid) {
            $distance = sqrt(
                ($point[0] - $centroid[0]) ** 2 +
                ($point[1] - $centroid[1]) ** 2
            );

            if ($distance < $minDistance) {
                $minDistance = $distance;
                $nearest = $index;
            }
        }

        return $nearest;
    }

    private function calculateMean(array $points): array
    {
        $sum = [0, 0];
        foreach ($points as $point) {
            $sum[0] += $point[0];
            $sum[1] += $point[1];
        }
        return [
            $sum[0] / count($points),
            $sum[1] / count($points)
        ];
    }
}

// Example: Cluster customers by [age, income/1000]
$customers = [
    [25, 50], [27, 52], [24, 48],  // Young
    [45, 90], [48, 95], [50, 88],  // Middle-aged
];

$clustering = new SimpleClustering();
$groups = $clustering->cluster($customers, 2);

echo "Cluster 1:\n";
print_r($groups[0]);
echo "\nCluster 2:\n";
print_r($groups[1]);
```

### Expected Result

```
Cluster 1:
Array (
    [0] => Array([25, 50])
    [1] => Array([27, 52])
    [2] => Array([24, 48])
)

Cluster 2:
Array (
    [0] => Array([45, 90])
    [1] => Array([48, 95])
    [2] => Array([50, 88])
)
```

### Why It Works

k-Means clustering:
1. Starts with random cluster centers (centroids)
2. Assigns each point to the nearest centroid
3. Moves centroids to the average position of assigned points
4. Repeats until clusters stabilize

### Troubleshooting

- **All points in one cluster** — Try different initial centroids or increase k value
- **Empty clusters** — Reduce k or provide more diverse data
- **Different results each run** — k-Means uses random initialization; run multiple times and pick best result

## Step 4: Real-World ML Use Cases in PHP (~15 min)

### Goal

Explore practical ML applications for PHP developers.

### Actions

1. **Common PHP ML Applications**:

**E-commerce**:
```php
// Product recommendations
// Customer segmentation
// Demand forecasting
// Price optimization
// Churn prediction
```

**Content Management**:
```php
// Spam detection (comments, contact forms)
// Content categorization
// Tag suggestions
// Similar article recommendations
// Sentiment analysis
```

**User Experience**:
```php
// Personalized content
// A/B test optimization
// Search ranking
// Fraud detection
// Anomaly detection in logs
```

**Business Intelligence**:
```php
// Customer lifetime value prediction
// Sales forecasting
// Trend analysis
// Market basket analysis
// User behavior clustering
```

2. **Practical Example: Spam Detection**:

```php
# filename: spam-detector-example.php
<?php

declare(strict_types=1);

class SpamDetector
{
    private array $spamWords = [];
    private array $hamWords = [];

    public function train(array $messages, array $labels): void
    {
        foreach ($messages as $index => $message) {
            $words = $this->extractWords($message);

            if ($labels[$index] === 'spam') {
                foreach ($words as $word) {
                    $this->spamWords[$word] = ($this->spamWords[$word] ?? 0) + 1;
                }
            } else {
                foreach ($words as $word) {
                    $this->hamWords[$word] = ($this->hamWords[$word] ?? 0) + 1;
                }
            }
        }
    }

    public function predict(string $message): string
    {
        $words = $this->extractWords($message);
        $spamScore = 0;
        $hamScore = 0;

        foreach ($words as $word) {
            $spamScore += $this->spamWords[$word] ?? 0;
            $hamScore += $this->hamWords[$word] ?? 0;
        }

        return $spamScore > $hamScore ? 'spam' : 'ham';
    }

    private function extractWords(string $text): array
    {
        $words = preg_split('/\s+/', strtolower($text));
        return array_filter($words, fn($w) => strlen($w) > 2);
    }
}

// Training
$messages = [
    'Free prize winner click now',
    'Meeting at 3pm tomorrow',
    'Congratulations you won lottery',
    'Project deadline is Friday',
    'Amazing discount click here',
    'Lunch plans for next week',
];

$labels = ['spam', 'ham', 'spam', 'ham', 'spam', 'ham'];

$detector = new SpamDetector();
$detector->train($messages, $labels);

// Prediction
echo $detector->predict('You are a winner claim prize') . "\n";  // spam
echo $detector->predict('Schedule meeting for tomorrow') . "\n";  // ham
```

### Expected Result

```
spam
ham
```

### Why It Works

This is a simplified Naive Bayes classifier that:
1. Counts word frequencies in spam vs ham messages
2. Scores new messages based on accumulated word evidence
3. Predicts the label with higher score

### Troubleshooting

- **Everything classified as spam** — Need more balanced training data (equal spam and ham examples)
- **Poor accuracy** — Need more training examples and better features
- **Common words dominate** — Consider removing stop words (the, and, is, etc.)

## Step 5: Setting Up Your ML Development Environment (~15 min)

### Goal

Prepare your environment for machine learning with PHP-ML.

### Actions

1. **Install PHP-ML using Composer**:

```bash
# Create a new project directory
mkdir php-ml-learning
cd php-ml-learning

# Initialize composer
composer init --no-interaction

# Install PHP-ML
composer require php-ai/php-ml
```

2. **Verify Installation**:

```php
# filename: test-php-ml.php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Phpml\Classification\KNearestNeighbors;

$samples = [[1, 3], [1, 4], [2, 4], [3, 1], [4, 1], [4, 2]];
$labels = ['a', 'a', 'a', 'b', 'b', 'b'];

$classifier = new KNearestNeighbors();
$classifier->train($samples, $labels);

echo $classifier->predict([3, 2]) . "\n";  // Should output: b

echo "PHP-ML is working!\n";
```

3. **Run the test**:

```bash
php test-php-ml.php
```

### Expected Result

```
b
PHP-ML is working!
```

### Why It Works

PHP-ML provides ML algorithms as PHP classes that you can use via Composer autoloading. The k-Nearest Neighbors classifier learned from 6 training examples and correctly predicted a new point.

### Troubleshooting

- **Composer not found** — Install Composer from [getcomposer.org](https://getcomposer.org/)
- **PHP version error** — PHP-ML requires PHP 7.2+; upgrade if needed
- **Autoload error** — Ensure you're in the project directory with vendor/autoload.php
- **Class not found** — Check that composer require completed successfully

## Step 6: Understanding the ML Workflow (~15 min)

### Goal

Learn the systematic approach to solving ML problems.

### Actions

1. **The ML Pipeline**:

```php
// Step 1: Collect and prepare data
$data = loadData();
$features = extractFeatures($data);
$labels = extractLabels($data);

// Step 2: Split into training and testing sets
[$trainX, $testX, $trainY, $testY] = trainTestSplit($features, $labels, 0.8);

// Step 3: Choose and train a model
$model = new SomeMLAlgorithm();
$model->train($trainX, $trainY);

// Step 4: Evaluate performance
$predictions = $model->predict($testX);
$accuracy = calculateAccuracy($predictions, $testY);

// Step 5: Iterate and improve
if ($accuracy < 0.85) {
    // Try different algorithm, features, or hyperparameters
}

// Step 6: Deploy to production
saveModel($model, 'production-model.pkl');
```

2. **Systematic ML Problem-Solving Framework**:

**Define the Problem**:
- What are you trying to predict?
- What type of problem is it? (classification, regression, clustering)
- What does success look like?

**Gather Data**:
- What data is available?
- Is it enough? (typically need hundreds to thousands of examples)
- Is it representative of real-world scenarios?

**Prepare Data**:
- Clean data (handle missing values, outliers)
- Extract features (what information predicts the outcome?)
- Normalize/standardize (make features comparable)

**Choose Algorithm**:
- Classification → k-NN, Decision Tree, SVM, Naive Bayes
- Regression → Linear Regression, SVR
- Clustering → k-Means, DBSCAN

**Train and Evaluate**:
- Split data (80% train, 20% test)
- Train model on training set
- Evaluate on test set (never seen during training!)
- Measure accuracy, precision, recall

**Iterate and Improve**:
- Try different algorithms
- Tune hyperparameters
- Engineer better features
- Collect more data

### Expected Result

You'll have a mental framework for approaching any ML problem systematically.

### Why It Works

Following a structured process:
- Prevents common mistakes (like testing on training data)
- Ensures reproducible results
- Makes it easier to identify and fix problems
- Guides you toward continuous improvement

### Troubleshooting

- **"I don't have enough data"** — Start with simple algorithms that work with less data (k-NN, Naive Bayes)
- **"How do I know which algorithm to use?"** — Start with k-NN as baseline, then experiment
- **"Model performs well on training but poorly on test"** — Overfitting; try simpler models or more data

## Exercises

### Exercise 1: Build a Temperature Classifier

Create a classifier that predicts if you should wear a jacket based on temperature and wind speed.

```php
# filename: exercise-01-jacket-classifier.php
<?php

declare(strict_types=1);

// Training data: [temperature_celsius, wind_speed_kmh] => wear_jacket
$training = [
    [[5, 20], 'yes'],   // Cold and windy
    [[10, 15], 'yes'],  // Cool and breezy
    [[15, 5], 'no'],    // Mild and calm
    [[20, 10], 'no'],   // Warm and light wind
    [[25, 5], 'no'],    // Hot and calm
    [[8, 25], 'yes'],   // Cool and very windy
];

// Your task: Implement a classifier that predicts:
// predict([12, 18]) => ?
// predict([22, 8]) => ?
```

<details>
<summary>Solution</summary>

```php
<?php

class JacketClassifier
{
    private array $training = [];

    public function train(array $samples, array $labels): void
    {
        foreach ($samples as $index => $sample) {
            $this->training[] = [
                'features' => $sample,
                'label' => $labels[$index]
            ];
        }
    }

    public function predict(array $features): string
    {
        // Find nearest neighbor
        $minDistance = PHP_FLOAT_MAX;
        $bestLabel = 'no';

        foreach ($this->training as $example) {
            $distance = sqrt(
                ($features[0] - $example['features'][0]) ** 2 +
                ($features[1] - $example['features'][1]) ** 2
            );

            if ($distance < $minDistance) {
                $minDistance = $distance;
                $bestLabel = $example['label'];
            }
        }

        return $bestLabel;
    }
}

$samples = [[5, 20], [10, 15], [15, 5], [20, 10], [25, 5], [8, 25]];
$labels = ['yes', 'yes', 'no', 'no', 'no', 'yes'];

$classifier = new JacketClassifier();
$classifier->train($samples, $labels);

echo "12°C, 18 km/h wind: " . $classifier->predict([12, 18]) . "\n";  // yes
echo "22°C, 8 km/h wind: " . $classifier->predict([22, 8]) . "\n";    // no
```
</details>

### Exercise 2: Customer Segmentation

Group customers into 2 segments based on purchase frequency and average order value.

```php
# filename: exercise-02-customer-segments.php
<?php

$customers = [
    ['name' => 'Alice', 'purchases_per_month' => 1, 'avg_order' => 50],
    ['name' => 'Bob', 'purchases_per_month' => 10, 'avg_order' => 200],
    ['name' => 'Charlie', 'purchases_per_month' => 2, 'avg_order' => 45],
    ['name' => 'David', 'purchases_per_month' => 12, 'avg_order' => 180],
    ['name' => 'Eve', 'purchases_per_month' => 1, 'avg_order' => 30],
    ['name' => 'Frank', 'purchases_per_month' => 8, 'avg_order' => 150],
];

// Your task: Implement clustering to find 2 customer segments
// Expected: High-value (frequent, high spending) vs Low-value customers
```

<details>
<summary>Hint</summary>
Use the SimpleClustering class from Step 3, but work with customer data instead of raw points.
</details>

## Wrap-up

Congratulations! You've completed the introduction to machine learning. Here's what you've accomplished:

- ✓ **Learned what ML is** and how it differs from traditional programming
- ✓ **Explored supervised learning** (classification and regression)
- ✓ **Understood unsupervised learning** (clustering)
- ✓ **Saw real-world ML applications** for PHP developers
- ✓ **Set up PHP-ML** and verified installation
- ✓ **Developed ML problem-solving framework** for systematic approach
- ✓ **Built simple classifiers and clustering** from scratch

### Key Concepts Learned

- **Machine learning** finds patterns in data automatically
- **Supervised learning** uses labeled examples (classification, regression)
- **Unsupervised learning** discovers structure without labels (clustering)
- **ML workflow** follows a systematic process from data to deployment
- **PHP-ML** enables ML in PHP without switching to Python

### What's Next

In the next chapter, we'll dive deep into **Setting Up PHP-ML and Your First Model**. You'll learn to:

- Install and configure PHP-ML properly
- Load and prepare datasets
- Train your first production-ready classifier
- Evaluate model performance with metrics
- Save and load trained models

## Further Reading

- [PHP-ML Documentation](https://php-ml.readthedocs.io/) — Official library documentation
- [Machine Learning Glossary](https://developers.google.com/machine-learning/glossary) — Google's ML terminology guide
- [StatQuest YouTube](https://www.youtube.com/user/joshstarmer) — Visual ML concept explanations
- [Introduction to Statistical Learning](https://www.statlearning.com/) — Free textbook (with R examples)

## Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 00 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/php-ml/chapter-00)**

<ChapterCheckbox
  seriesId="php-ml"
  chapterId="00"
  label="You've mastered the fundamentals of machine learning!"
/>

---

Ready to build your first real ML model? Continue to [Chapter 01: Setting Up PHP-ML and Your First Model](/series/php-ml/chapters/01-setting-up-php-ml).
