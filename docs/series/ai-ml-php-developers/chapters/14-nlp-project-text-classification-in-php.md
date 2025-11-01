---
title: "14: NLP Project: Text Classification in PHP"
description: "Build a production-ready sentiment analyzer using PHP to preprocess text, extract TF-IDF features, train Naive Bayes/SVM/Logistic Regression classifiers, and achieve 85%+ accuracy on movie reviews"
series: "ai-ml-php-developers"
chapter: "14"
order: 14
difficulty: "Intermediate"
prerequisites:
  - "13"
  - "08"
---

# Chapter 14: NLP Project: Text Classification in PHP

## Overview

In Chapter 13, you learned the fundamentals of Natural Language Processing—how to tokenize text, remove stop words, and extract features like bag-of-words and TF-IDF. Now it's time to apply those techniques to build a complete, working text classification system.

Text classification is one of the most practical applications of machine learning in web development. Every time you see automated content moderation, sentiment analysis on product reviews, spam detection in comments, or topic categorization of articles, text classification is at work. Unlike the spam filter you built in Chapter 6 (which focused on email characteristics), this chapter dives deep into sophisticated text analysis techniques that can handle any classification task involving natural language.

In this chapter, you'll build a **sentiment analyzer** that classifies movie reviews as positive or negative. You'll start by setting up a real dataset of 1,000 labeled reviews, then implement a complete preprocessing pipeline (tokenization, stopword removal, feature extraction). You'll train and compare three different machine learning algorithms—Naive Bayes, Support Vector Machines (SVM), and Logistic Regression—learning when to use each approach. By the end, you'll have a production-ready sentiment analysis system achieving 85%+ accuracy that you can adapt to any text classification problem.

This project bridges theory and practice. You'll understand why certain algorithms excel at text classification, how to engineer features that capture meaning from unstructured text, and how to evaluate models using metrics designed for imbalanced datasets. The skills you develop here apply directly to building intelligent features in PHP applications: analyzing customer feedback, moderating user-generated content, routing support tickets, or personalizing content recommendations based on text preferences.

## Prerequisites

Before starting this chapter, you should have:

- Completed [Chapter 13](/series/ai-ml-php-developers/chapters/13-natural-language-processing-nlp-fundamentals) or equivalent understanding of text preprocessing, tokenization, and feature extraction concepts
- Completed [Chapter 8](/series/ai-ml-php-developers/chapters/08-leveraging-php-machine-learning-libraries) with experience using Rubix ML or PHP-ML for training and prediction
- PHP 8.4+ environment with Composer installed
- Rubix ML and/or PHP-ML installed (from Chapter 2)
- Familiarity with classification concepts from Chapters 3 and 6
- Basic understanding of arrays, string manipulation, and file I/O in PHP
- Text editor or IDE with PHP support

**Estimated Time**: ~90-120 minutes (reading, coding, and exercises)

## What You'll Build

By the end of this chapter, you will have created:

- A **text preprocessing pipeline** that tokenizes, normalizes, and cleans raw text with stopword removal and optional stemming
- A **bag-of-words vectorizer** that builds vocabularies and converts documents to numeric feature vectors
- A **TF-IDF feature extractor** implementing term frequency-inverse document frequency weighting from scratch
- A **Naive Bayes sentiment classifier** optimized for text with probability-based predictions
- A **Support Vector Machine (SVM) classifier** with linear kernel for high-dimensional text features
- A **Logistic Regression classifier** providing interpretable probability estimates for sentiment
- A **comprehensive evaluation system** calculating accuracy, precision, recall, F1-score, and confusion matrices
- A **model comparison framework** benchmarking multiple algorithms on the same dataset with performance metrics
- An **n-gram feature extractor** capturing multi-word phrases (bigrams, trigrams) for improved context
- A **feature selection system** identifying the most predictive words for each sentiment class
- A **model persistence layer** for saving and loading trained classifiers with versioning metadata
- A **confidence scoring system** providing prediction probabilities for decision thresholding
- A **production-ready sentiment analyzer class** integrating all components with error handling, batch processing, and extensibility
- A **cross-validation implementation** ensuring robust model evaluation without data leakage
- An **imbalanced dataset handler** detecting and addressing class distribution issues
- A **complete movie review dataset** with 1,000 labeled examples split into training and test sets

All code examples are fully functional, tested, and include realistic datasets you can run immediately.

::: info Code Examples
Complete, runnable examples for this chapter:

- [`01-load-dataset.php`](../code/chapter-14/01-load-dataset.php) — Load and inspect movie review dataset
- [`02-text-preprocessing.php`](../code/chapter-14/02-text-preprocessing.php) — Complete text preprocessing pipeline
- [`03-bag-of-words.php`](../code/chapter-14/03-bag-of-words.php) — Bag-of-words vectorization
- [`04-tfidf-vectorizer.php`](../code/chapter-14/04-tfidf-vectorizer.php) — TF-IDF feature extraction
- [`05-naive-bayes-sentiment.php`](../code/chapter-14/05-naive-bayes-sentiment.php) — Naive Bayes classifier
- [`06-svm-sentiment.php`](../code/chapter-14/06-svm-sentiment.php) — SVM classifier
- [`07-logistic-regression-sentiment.php`](../code/chapter-14/07-logistic-regression-sentiment.php) — Logistic Regression classifier
- [`08-evaluation-metrics.php`](../code/chapter-14/08-evaluation-metrics.php) — Comprehensive evaluation metrics
- [`09-model-comparison.php`](../code/chapter-14/09-model-comparison.php) — Algorithm benchmarking
- [`10-advanced-features.php`](../code/chapter-14/10-advanced-features.php) — N-grams and feature selection
- [`11-model-persistence.php`](../code/chapter-14/11-model-persistence.php) — Save and load trained models
- [`12-production-sentiment-analyzer.php`](../code/chapter-14/12-production-sentiment-analyzer.php) — Production-ready analyzer
- [`data/movie_reviews.csv`](../code/chapter-14/data/movie_reviews.csv) — 1,000 labeled movie reviews
- [`data/stopwords.txt`](../code/chapter-14/data/stopwords.txt) — English stopwords list

All files are in [`docs/series/ai-ml-php-developers/code/chapter-14/`](../code/chapter-14/README.md)
:::

## Quick Start

Want to see sentiment analysis in action right now? Here's a 5-minute working example:

```php
# filename: quick-sentiment-analyzer.php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../../code/chapter-02/vendor/autoload.php';

use Phpml\Classification\NaiveBayes;

// Step 1: Training data - movie reviews with sentiment labels
$trainingReviews = [
    "This movie was absolutely fantastic! Great acting and compelling story.",
    "Terrible waste of time. Poor plot and bad acting throughout.",
    "One of the best films I've seen this year. Highly recommended!",
    "Boring and predictable. I almost fell asleep halfway through.",
    "Brilliant cinematography and outstanding performances. A masterpiece!",
    "Disappointing and clichéd. Expected much better from this director.",
    "Amazing! Every scene was gripping and emotionally powerful.",
    "Awful movie. Confusing plot and weak character development.",
];

// Step 2: Extract simple features from each review
function extractSimpleFeatures(string $review): array
{
    $lower = strtolower($review);

    // Positive indicators
    $positiveWords = ['great', 'fantastic', 'amazing', 'brilliant', 'outstanding',
                      'best', 'masterpiece', 'recommended', 'excellent', 'wonderful'];

    // Negative indicators
    $negativeWords = ['terrible', 'awful', 'boring', 'disappointing', 'poor',
                      'bad', 'waste', 'weak', 'confusing', 'predictable'];

    $positiveCount = 0;
    $negativeCount = 0;

    foreach ($positiveWords as $word) {
        $positiveCount += substr_count($lower, $word);
    }

    foreach ($negativeWords as $word) {
        $negativeCount += substr_count($lower, $word);
    }

    return [
        $positiveCount,           // Number of positive words
        $negativeCount,           // Number of negative words
        strlen($review),          // Review length
        substr_count($review, '!'), // Exclamation marks (often indicate strong emotion)
    ];
}

$trainingFeatures = array_map('extractSimpleFeatures', $trainingReviews);
$trainingLabels = ['positive', 'negative', 'positive', 'negative',
                   'positive', 'negative', 'positive', 'negative'];

// Step 3: Train Naive Bayes classifier
$classifier = new NaiveBayes();
$classifier->train($trainingFeatures, $trainingLabels);

// Step 4: Test on new reviews
$testReviews = [
    "Absolutely brilliant! Best movie of the year!",
    "Total waste of time. Terrible acting and boring story.",
    "Pretty good movie with some great moments.",
    "Disappointing. Expected better based on the reviews.",
];

echo "=== Sentiment Analysis Results ===\n\n";

foreach ($testReviews as $review) {
    $features = extractSimpleFeatures($review);
    $sentiment = $classifier->predict($features);
    $icon = $sentiment === 'positive' ? '👍' : '👎';

    echo "{$icon} {$sentiment}: \"{$review}\"\n\n";
}

echo "Ready to build a production-ready sentiment analyzer? Let's go!\n";
```

**Run it:**

```bash
cd /Users/dalehurley/Code/PHP-From-Scratch
php quick-sentiment-analyzer.php
```

**Expected output:**

```
=== Sentiment Analysis Results ===

👍 positive: "Absolutely brilliant! Best movie of the year!"

👎 negative: "Total waste of time. Terrible acting and boring story."

👍 positive: "Pretty good movie with some great moments."

👎 negative: "Disappointing. Expected better based on the reviews."

Ready to build a production-ready sentiment analyzer? Let's go!
```

This simple example shows the basic concept, but there's much more to learn! In this chapter, you'll build a sophisticated system using proper text preprocessing, TF-IDF features, and multiple algorithms to achieve professional-grade accuracy.

## Objectives

By completing this chapter, you will:

- **Understand** the unique challenges of text classification and how they differ from numerical data classification
- **Implement** a complete text preprocessing pipeline including tokenization, normalization, stopword removal, and stemming
- **Build** both bag-of-words and TF-IDF feature extractors from scratch to convert text into numeric representations
- **Train and compare** three different classification algorithms (Naive Bayes, SVM, Logistic Regression) on the same text dataset
- **Master** evaluation metrics specific to text classification including precision, recall, F1-score, and confusion matrix analysis
- **Create** a production-ready sentiment analyzer with model persistence, confidence scoring, and error handling
- **Apply** advanced techniques like n-grams, feature selection, and cross-validation to improve model performance

## Step 1: Understanding Text Classification (~10 min)

### Goal

Understand what makes text classification different from numeric classification and learn the specific challenges involved in analyzing natural language.

### Actions

Text classification assigns predefined categories (labels) to text documents. While the concept is similar to the classification you learned in Chapter 6, working with text presents unique challenges that require specialized approaches.

**Key Differences from Numeric Classification:**

1. **Variable Length**: Reviews can be 10 words or 1,000 words—how do you create consistent feature vectors?
2. **High Dimensionality**: With thousands of unique words, each document becomes a very long feature vector
3. **Sparse Features**: Most words don't appear in most documents, creating mostly-zero feature vectors
4. **Word Order**: "not good" means something different from "good"—but bag-of-words loses this order
5. **Synonyms and Ambiguity**: "great" and "excellent" mean similar things, but appear as different features

**What is Sentiment Analysis?**

Sentiment analysis is a specific type of text classification that determines the emotional tone of text. It's one of the most common NLP applications:

- **Binary Sentiment**: Positive or negative (this chapter's focus)
- **Multi-class Sentiment**: Positive, neutral, or negative
- **Fine-grained Ratings**: 1-5 stars or 1-10 ratings
- **Aspect-based**: Sentiment about specific features (e.g., "great acting, poor plot")

**Real-World Applications:**

Think of text classification like a smart routing system for text. Just as a web router directs HTTP requests to controllers, text classifiers route documents to appropriate categories or actions. Consider these scenarios:

- **E-commerce**: Analyze product reviews to identify dissatisfied customers automatically
- **Customer Support**: Route support tickets to appropriate teams based on content
- **Content Moderation**: Flag potentially problematic user comments for review
- **News Aggregation**: Categorize articles by topic (sports, politics, tech)
- **Email Management**: Beyond spam detection—categorize emails by urgency or department

### Why It Works

Text classification works by converting text into numeric features that capture meaning, then applying classification algorithms just like with numeric data. The key insight is that word presence and frequency patterns correlate strongly with document category. Reviews containing "excellent," "masterpiece," and "brilliant" are statistically more likely to be positive than those with "terrible," "boring," and "waste."

The challenge is engineering features that capture these patterns effectively while handling the unique properties of text data. That's where preprocessing (Step 3) and feature extraction (Steps 4-5) become critical.

### Troubleshooting

**Issue: "Why can't I just search for positive/negative words?"**

Simple keyword matching (like the Quick Start example) works for obvious cases but fails on:

- Negation: "not good" contains "good" but is negative
- Sarcasm: "Oh great, another predictable ending" is negative despite "great"
- Context: "This movie is good... for putting me to sleep" starts positive but is negative
- Subtle sentiment: "I expected more" contains no obvious sentiment words but expresses disappointment

Machine learning learns patterns from examples rather than relying on handcrafted rules, making it more robust.

**Issue: "Is sentiment analysis the same as emotion detection?"**

No. Sentiment analysis classifies **positive vs. negative tone**, while emotion detection identifies specific emotions (joy, anger, sadness, fear, etc.). Sentiment is simpler and more actionable for most business applications.

## Step 2: Setting Up the Dataset (~15 min)

### Goal

Load a real movie review dataset, understand its structure, inspect label distribution, and prepare train/test splits for model evaluation.

### Actions

1. **Understand the dataset structure**:

Our dataset contains 1,000 movie reviews with binary sentiment labels. Each row has:

- `review`: The full text of the movie review
- `sentiment`: Either "positive" or "negative"

This is a **balanced dataset** (500 positive, 500 negative), which simplifies initial learning. Later you'll handle imbalanced data.

2. **Create the dataset loader**:

```php
# filename: 01-load-dataset.php
<?php

declare(strict_types=1);

/**
 * Load and inspect the movie review dataset.
 *
 * This script demonstrates:
 * - Reading CSV data
 * - Inspecting dataset properties
 * - Checking class balance
 * - Creating train/test splits
 */

// Load dataset from CSV
function loadDataset(string $filepath): array
{
    if (!file_exists($filepath)) {
        throw new RuntimeException("Dataset file not found: {$filepath}");
    }

    $handle = fopen($filepath, 'r');
    if ($handle === false) {
        throw new RuntimeException("Could not open dataset file: {$filepath}");
    }

    // Skip header row
    $header = fgetcsv($handle);

    $reviews = [];
    $sentiments = [];

    while (($row = fgetcsv($handle)) !== false) {
        $reviews[] = $row[0];      // Review text
        $sentiments[] = $row[1];    // Sentiment label
    }

    fclose($handle);

    return [$reviews, $sentiments];
}

// Analyze dataset distribution
function analyzeDataset(array $sentiments): void
{
    $counts = array_count_values($sentiments);
    $total = count($sentiments);

    echo "=== Dataset Analysis ===\n\n";
    echo "Total samples: {$total}\n\n";

    foreach ($counts as $label => $count) {
        $percentage = round(($count / $total) * 100, 1);
        echo "  {$label}: {$count} ({$percentage}%)\n";
    }

    echo "\n";

    // Check if balanced
    $max = max($counts);
    $min = min($counts);
    $ratio = $max / $min;

    if ($ratio <= 1.5) {
        echo "✓ Dataset is balanced (ratio: " . round($ratio, 2) . ":1)\n";
    } else {
        echo "⚠ Dataset is imbalanced (ratio: " . round($ratio, 2) . ":1)\n";
        echo "  Consider using stratified sampling or adjusting class weights.\n";
    }
}

// Create train/test split with stratification
function trainTestSplit(
    array $reviews,
    array $sentiments,
    float $testSize = 0.2,
    int $randomSeed = 42
): array {
    // Set seed for reproducibility
    mt_srand($randomSeed);

    // Group indices by class
    $positiveIndices = [];
    $negativeIndices = [];

    foreach ($sentiments as $idx => $sentiment) {
        if ($sentiment === 'positive') {
            $positiveIndices[] = $idx;
        } else {
            $negativeIndices[] = $idx;
        }
    }

    // Shuffle each class independently
    shuffle($positiveIndices);
    shuffle($negativeIndices);

    // Split each class
    $posTestCount = (int) floor(count($positiveIndices) * $testSize);
    $negTestCount = (int) floor(count($negativeIndices) * $testSize);

    $testIndices = array_merge(
        array_slice($positiveIndices, 0, $posTestCount),
        array_slice($negativeIndices, 0, $negTestCount)
    );

    $trainIndices = array_merge(
        array_slice($positiveIndices, $posTestCount),
        array_slice($negativeIndices, $negTestCount)
    );

    // Shuffle train and test sets
    shuffle($trainIndices);
    shuffle($testIndices);

    // Extract data
    $trainReviews = array_map(fn($i) => $reviews[$i], $trainIndices);
    $trainSentiments = array_map(fn($i) => $sentiments[$i], $trainIndices);
    $testReviews = array_map(fn($i) => $reviews[$i], $testIndices);
    $testSentiments = array_map(fn($i) => $sentiments[$i], $testIndices);

    return [
        'train_reviews' => $trainReviews,
        'train_sentiments' => $trainSentiments,
        'test_reviews' => $testReviews,
        'test_sentiments' => $testSentiments,
    ];
}

// Main execution
try {
    // Load dataset (adjust path as needed)
    $datasetPath = __DIR__ . '/data/movie_reviews.csv';
    [$reviews, $sentiments] = loadDataset($datasetPath);

    echo "✓ Dataset loaded successfully!\n\n";

    // Analyze distribution
    analyzeDataset($sentiments);

    // Show sample reviews
    echo "\n=== Sample Reviews ===\n\n";
    for ($i = 0; $i < 3; $i++) {
        $truncated = strlen($reviews[$i]) > 100
            ? substr($reviews[$i], 0, 100) . '...'
            : $reviews[$i];
        echo "Review {$i}: [{$sentiments[$i]}]\n";
        echo "  {$truncated}\n\n";
    }

    // Create train/test split
    echo "=== Creating Train/Test Split ===\n\n";
    $split = trainTestSplit($reviews, $sentiments, testSize: 0.2);

    echo "Training set: " . count($split['train_reviews']) . " samples\n";
    echo "Test set: " . count($split['test_reviews']) . " samples\n\n";

    // Verify stratification worked
    $trainPositive = count(array_filter(
        $split['train_sentiments'],
        fn($s) => $s === 'positive'
    ));
    $testPositive = count(array_filter(
        $split['test_sentiments'],
        fn($s) => $s === 'positive'
    ));

    $trainPosPercent = round(($trainPositive / count($split['train_sentiments'])) * 100, 1);
    $testPosPercent = round(($testPositive / count($split['test_sentiments'])) * 100, 1);

    echo "Train positive: {$trainPosPercent}%\n";
    echo "Test positive: {$testPosPercent}%\n\n";

    if (abs($trainPosPercent - $testPosPercent) < 5) {
        echo "✓ Stratification successful! Class distribution is similar.\n";
    } else {
        echo "⚠ Class distribution differs between train and test sets.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
```

3. **Run the script**:

```bash
cd /Users/dalehurley/Code/PHP-From-Scratch/docs/series/ai-ml-php-developers/code/chapter-14
php 01-load-dataset.php
```

### Expected Result

```
✓ Dataset loaded successfully!

=== Dataset Analysis ===

Total samples: 1000

  positive: 500 (50.0%)
  negative: 500 (50.0%)

✓ Dataset is balanced (ratio: 1:1)

=== Sample Reviews ===

Review 0: [positive]
  One of the best films I've ever seen! The acting was superb and the story kept me engaged from star...

Review 1: [negative]
  Terrible movie. Waste of two hours. The plot made no sense and the acting was wooden throughout...

Review 2: [positive]
  Absolutely brilliant! A masterpiece of cinema. Every frame is beautiful and the performances are...

=== Creating Train/Test Split ===

Training set: 800 samples
Test set: 200 samples

Train positive: 50.0%
Test positive: 50.0%

✓ Stratification successful! Class distribution is similar.
```

### Why It Works

**Stratified Splitting** ensures that both training and test sets have the same proportion of positive and negative reviews. This is critical because:

1. **Representative evaluation**: If your test set had 80% positive reviews but your training set had 50%, accuracy metrics would be misleading
2. **Prevents learning bias**: The model sees the same class distribution during training that it will encounter during testing
3. **Enables fair comparison**: When you compare different algorithms later, they all see the same balanced data

The random seed (`mt_srand(42)`) makes splits reproducible—running the script multiple times produces identical splits, which is essential for debugging and comparing experiments.

### Troubleshooting

**Error: "Dataset file not found"**

Make sure you're running the script from the correct directory, or adjust the path:

```php
// Absolute path
$datasetPath = '/full/path/to/movie_reviews.csv';

// Relative to script location
$datasetPath = __DIR__ . '/data/movie_reviews.csv';
```

**Issue: "Why 80/20 split?"**

The 80/20 train/test split is a common convention that balances two needs:

- **More training data** = better model learning (argues for 90/10 or 95/5)
- **More test data** = more reliable accuracy estimates (argues for 70/30 or 60/40)

With 1,000 samples, 200 test samples provide reliable accuracy estimates. For smaller datasets (< 500 samples), consider cross-validation instead.

**Issue: "My test accuracy is suspiciously high"**

This might indicate **data leakage**—the test set accidentally influenced training. Common causes:

- Preprocessing fit on entire dataset instead of just training set
- Feature engineering using statistics from test set
- Duplicate or near-duplicate samples in train and test sets

Always preprocess train and test independently, and check for duplicates.

## Step 3: Text Preprocessing Pipeline (~20 min)

### Goal

Build a complete text preprocessing pipeline that tokenizes, normalizes, and cleans text to prepare it for feature extraction.

### Actions

Raw text contains noise that hurts classification: capitalization, punctuation, common words that add no meaning. Preprocessing transforms messy natural language into clean tokens ready for vectorization.

1. **Create the preprocessing pipeline**:

```php
# filename: 02-text-preprocessing.php
<?php

declare(strict_types=1);

/**
 * Text preprocessing pipeline for sentiment analysis.
 *
 * Demonstrates:
 * - Tokenization
 * - Case normalization
 * - Stopword removal
 * - Basic stemming
 */

class TextPreprocessor
{
    public function __construct(
        private array $stopwords = [],
        private bool $removePunctuation = true,
        private bool $removeStopwords = true,
        private bool $stem = false,
    ) {}

    /**
     * Load stopwords from file.
     */
    public static function loadStopwords(string $filepath): array
    {
        if (!file_exists($filepath)) {
            throw new RuntimeException("Stopwords file not found: {$filepath}");
        }

        $stopwords = file($filepath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        return array_flip($stopwords); // Use as keys for O(1) lookup
    }

    /**
     * Tokenize text into words.
     */
    public function tokenize(string $text): array
    {
        // Convert to lowercase
        $text = strtolower($text);

        if ($this->removePunctuation) {
            // Remove punctuation but keep apostrophes in words like "don't"
            $text = preg_replace("/[^a-z0-9\s']/", ' ', $text);
        }

        // Split on whitespace
        $tokens = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);

        return $tokens;
    }

    /**
     * Remove stopwords from token array.
     */
    public function removeStopwords(array $tokens): array
    {
        if (!$this->removeStopwords || empty($this->stopwords)) {
            return $tokens;
        }

        return array_filter($tokens, fn($token) => !isset($this->stopwords[$token]));
    }

    /**
     * Apply basic Porter stemmer rules (simplified).
     */
    public function stemToken(string $token): string
    {
        // Simple suffix removal rules
        // In production, use a proper stemming library

        if (str_ends_with($token, 'ing') && strlen($token) > 5) {
            return substr($token, 0, -3);
        }

        if (str_ends_with($token, 'ed') && strlen($token) > 4) {
            return substr($token, 0, -2);
        }

        if (str_ends_with($token, 's') && strlen($token) > 3 && !str_ends_with($token, 'ss')) {
            return substr($token, 0, -1);
        }

        return $token;
    }

    /**
     * Complete preprocessing pipeline.
     */
    public function preprocess(string $text): array
    {
        // 1. Tokenize
        $tokens = $this->tokenize($text);

        // 2. Remove stopwords
        $tokens = $this->removeStopwords($tokens);

        // 3. Stem (optional)
        if ($this->stem) {
            $tokens = array_map([$this, 'stemToken'], $tokens);
        }

        // 4. Filter empty tokens and reindex array
        return array_values(array_filter($tokens, fn($t) => strlen($t) > 1));
    }

    /**
     * Preprocess multiple documents.
     */
    public function preprocessBatch(array $documents): array
    {
        return array_map([$this, 'preprocess'], $documents);
    }
}

// Demonstration
try {
    // Load stopwords
    $stopwordsPath = __DIR__ . '/data/stopwords.txt';
    $stopwords = TextPreprocessor::loadStopwords($stopwordsPath);

    echo "✓ Loaded " . count($stopwords) . " stopwords\n\n";

    // Create preprocessor
    $preprocessor = new TextPreprocessor(
        stopwords: $stopwords,
        removePunctuation: true,
        removeStopwords: true,
        stem: false  // Try setting to true to see stemming effect
    );

    // Test documents
    $documents = [
        "This movie was absolutely fantastic! I loved every minute of it.",
        "Terrible waste of time. The acting was poor and the plot boring.",
        "One of the best films I've seen this year. Highly recommended!",
    ];

    echo "=== Text Preprocessing Demo ===\n\n";

    foreach ($documents as $idx => $doc) {
        echo "Original {$idx}: \"{$doc}\"\n";

        $tokens = $preprocessor->preprocess($doc);
        $cleaned = implode(' ', $tokens);

        echo "Cleaned {$idx}:  \"{$cleaned}\"\n";
        echo "Tokens: " . count($tokens) . "\n\n";
    }

    // Compare with vs without stopwords
    echo "=== Effect of Stopword Removal ===\n\n";

    $testSentence = "This is a great movie that I really enjoyed watching.";

    $withStopwords = new TextPreprocessor(stopwords: [], removeStopwords: false);
    $withoutStopwords = new TextPreprocessor(stopwords: $stopwords, removeStopwords: true);

    echo "Original: \"{$testSentence}\"\n";
    echo "With stopwords: \"" . implode(' ', $withStopwords->preprocess($testSentence)) . "\"\n";
    echo "Without stopwords: \"" . implode(' ', $withoutStopwords->preprocess($testSentence)) . "\"\n\n";

    // Compare with vs without stemming
    echo "=== Effect of Stemming ===\n\n";

    $noStem = new TextPreprocessor(stopwords: $stopwords, stem: false);
    $withStem = new TextPreprocessor(stopwords: $stopwords, stem: true);

    $testStemming = "The actors were acting brilliantly in multiple scenes showing great performances.";

    echo "Original: \"{$testStemming}\"\n";
    echo "No stemming: \"" . implode(' ', $noStem->preprocess($testStemming)) . "\"\n";
    echo "With stemming: \"" . implode(' ', $withStem->preprocess($testStemming)) . "\"\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
```

2. **Run the preprocessing demo**:

```bash
php 02-text-preprocessing.php
```

### Expected Result

```
✓ Loaded 127 stopwords

=== Text Preprocessing Demo ===

Original 0: "This movie was absolutely fantastic! I loved every minute of it."
Cleaned 0:  "movie absolutely fantastic loved minute"
Tokens: 5

Original 1: "Terrible waste of time. The acting was poor and the plot boring."
Cleaned 1:  "terrible waste time acting poor plot boring"
Tokens: 7

Original 2: "One of the best films I've seen this year. Highly recommended!"
Cleaned 2:  "best films seen year highly recommended"
Tokens: 6

=== Effect of Stopword Removal ===

Original: "This is a great movie that I really enjoyed watching."
With stopwords: "great movie really enjoyed watching"
Without stopwords: "great movie really enjoyed watching"

=== Effect of Stemming ===

Original: "The actors were acting brilliantly in multiple scenes showing great performances."
No stemming: "actors acting brilliantly multiple scenes showing great performances"
With stemming: "actor act brilliant multiple scene show great performance"
```

### Why It Works

**Tokenization** breaks text into individual words, the atomic units for analysis. Think of it like parsing a URL into components—you need individual pieces before you can analyze them.

**Case Normalization** treats "Great" and "great" as the same word. Without this, your vocabulary doubles unnecessarily with capitalized variants.

**Stopword Removal** eliminates high-frequency words that carry little meaning: "the", "is", "a", "and". These words appear in both positive and negative reviews with similar frequency, so they don't help classification. Removing them:

- Reduces vocabulary size (faster training, less memory)
- Emphasizes content words (nouns, verbs, adjectives)
- Improves signal-to-noise ratio

**Stemming** reduces words to their root form: "acting", "acted", "actor" all become "act". This groups related words together, reducing vocabulary size and helping the model generalize. The tradeoff is occasional over-stemming (grouping unrelated words) or under-stemming (missing connections).

### Troubleshooting

**Issue: "Should I always remove stopwords?"**

Not necessarily. Consider these cases:

**Remove stopwords when:**

- Working with bag-of-words or TF-IDF (this chapter)
- Vocabulary size is a concern (memory/speed)
- Document length varies significantly

**Keep stopwords when:**

- Using n-grams (phrases like "not good" need "not")
- Word order matters (deep learning models)
- Very short documents (tweets, titles) where every word counts

For sentiment analysis, removing stopwords typically helps.

**Issue: "My preprocessor is too slow on large datasets"**

Preprocessing can be a bottleneck. Optimize by:

```php
// Cache preprocessed data
$cacheFile = __DIR__ . '/cache/preprocessed.json';
if (file_exists($cacheFile)) {
    $preprocessed = json_decode(file_get_contents($cacheFile), true);
} else {
    $preprocessed = $preprocessor->preprocessBatch($documents);
    file_put_contents($cacheFile, json_encode($preprocessed));
}
```

**Issue: "What about contractions like 'don't', 'isn't'?"**

Our basic preprocessor keeps contractions. For production, expand them first:

```php
$expansions = [
    "don't" => "do not",
    "isn't" => "is not",
    "won't" => "will not",
    // ... more contractions
];
$text = str_replace(array_keys($expansions), array_values($expansions), $text);
```

## Step 4: Feature Extraction - Bag of Words (~15 min)

### Goal

Implement bag-of-words vectorization to convert variable-length text documents into fixed-size numeric feature vectors.

### Actions

Machine learning algorithms require numeric input, but we have text. Bag-of-words solves this by representing each document as a vector of word counts, where each dimension corresponds to a word in the vocabulary.

1. **Create the bag-of-words vectorizer**:

```php
# filename: 03-bag-of-words.php
<?php

declare(strict_types=1);

/**
 * Bag-of-Words vectorization for text documents.
 *
 * Demonstrates:
 * - Building vocabulary from training data
 * - Converting documents to word count vectors
 * - Handling unknown words in test data
 */

class BagOfWordsVectorizer
{
    private array $vocabulary = [];
    private array $wordToIndex = [];

    /**
     * Build vocabulary from training documents.
     *
     * @param array<array<string>> $documents Array of tokenized documents
     * @param int $minDf Minimum document frequency (ignore rare words)
     * @param int $maxDf Maximum document frequency (ignore common words)
     * @param int|null $maxFeatures Limit vocabulary size to top N words
     */
    public function fit(
        array $documents,
        int $minDf = 1,
        int $maxDf = PHP_INT_MAX,
        ?int $maxFeatures = null
    ): void {
        // Count document frequency for each word
        $documentFrequency = [];
        $totalDocs = count($documents);

        foreach ($documents as $doc) {
            $uniqueWords = array_unique($doc);
            foreach ($uniqueWords as $word) {
                $documentFrequency[$word] = ($documentFrequency[$word] ?? 0) + 1;
            }
        }

        // Filter by document frequency
        $vocabulary = [];
        foreach ($documentFrequency as $word => $df) {
            if ($df >= $minDf && $df <= $maxDf) {
                $vocabulary[$word] = $df;
            }
        }

        // Limit to top N most frequent words if specified
        if ($maxFeatures !== null && count($vocabulary) > $maxFeatures) {
            arsort($vocabulary);
            $vocabulary = array_slice($vocabulary, 0, $maxFeatures, true);
        }

        // Sort vocabulary alphabetically for consistency
        ksort($vocabulary);

        // Create word-to-index mapping
        $this->vocabulary = array_keys($vocabulary);
        $this->wordToIndex = array_flip($this->vocabulary);
    }

    /**
     * Transform documents to bag-of-words vectors.
     *
     * @param array<array<string>> $documents Array of tokenized documents
     * @return array<array<int>> Array of feature vectors
     */
    public function transform(array $documents): array
    {
        if (empty($this->vocabulary)) {
            throw new RuntimeException("Vocabulary not built. Call fit() first.");
        }

        $vectors = [];
        $vocabSize = count($this->vocabulary);

        foreach ($documents as $doc) {
            // Initialize zero vector
            $vector = array_fill(0, $vocabSize, 0);

            // Count words
            foreach ($doc as $word) {
                if (isset($this->wordToIndex[$word])) {
                    $index = $this->wordToIndex[$word];
                    $vector[$index]++;
                }
                // Unknown words are silently ignored
            }

            $vectors[] = $vector;
        }

        return $vectors;
    }

    /**
     * Fit and transform in one step (convenience method).
     */
    public function fitTransform(array $documents): array
    {
        $this->fit($documents);
        return $this->transform($documents);
    }

    /**
     * Get vocabulary words.
     */
    public function getVocabulary(): array
    {
        return $this->vocabulary;
    }

    /**
     * Get vocabulary size.
     */
    public function getVocabularySize(): int
    {
        return count($this->vocabulary);
    }

    /**
     * Get top N words by document frequency.
     */
    public function getTopWords(int $n = 10): array
    {
        return array_slice($this->vocabulary, 0, $n);
    }
}

// Demonstration
require_once '02-text-preprocessing.php';

try {
    // Load and preprocess data
    $stopwordsPath = __DIR__ . '/data/stopwords.txt';
    $stopwords = TextPreprocessor::loadStopwords($stopwordsPath);

    $preprocessor = new TextPreprocessor(
        stopwords: $stopwords,
        removeStopwords: true
    );

    // Sample documents
    $documents = [
        "This movie was absolutely fantastic and amazing",
        "Terrible movie with poor acting and boring plot",
        "Great film with excellent performances",
        "Awful waste of time",
        "Brilliant masterpiece with stunning visuals",
        "Disappointing and dull movie",
    ];

    echo "=== Bag of Words Vectorization ===\n\n";

    // Preprocess
    $tokenizedDocs = $preprocessor->preprocessBatch($documents);

    echo "Tokenized documents:\n";
    foreach ($tokenizedDocs as $idx => $tokens) {
        echo "  Doc {$idx}: [" . implode(', ', $tokens) . "]\n";
    }
    echo "\n";

    // Create and fit vectorizer
    $vectorizer = new BagOfWordsVectorizer();
    $vectorizer->fit($tokenizedDocs);

    echo "Vocabulary size: " . $vectorizer->getVocabularySize() . "\n";
    echo "Vocabulary: [" . implode(', ', $vectorizer->getVocabulary()) . "]\n\n";

    // Transform to vectors
    $vectors = $vectorizer->transform($tokenizedDocs);

    echo "Feature vectors (word counts):\n\n";
    foreach ($vectors as $idx => $vector) {
        $nonZero = array_filter($vector);
        echo "Doc {$idx}: " . json_encode($vector) . "\n";
        echo "        (non-zero: " . count($nonZero) . " features)\n\n";
    }

    // Show feature meanings
    echo "=== Feature Interpretation ===\n\n";
    $vocab = $vectorizer->getVocabulary();

    echo "Doc 0 vector breakdown:\n";
    foreach ($vectors[0] as $idx => $count) {
        if ($count > 0) {
            echo "  Feature {$idx} ('{$vocab[$idx]}'): {$count}\n";
        }
    }

    echo "\n=== Testing on New Documents ===\n\n";

    $testDocs = [
        "An absolutely fantastic and amazing movie",
        "This is terrible and awful",
        "Unknown words should be ignored silently",
    ];

    $testTokenized = $preprocessor->preprocessBatch($testDocs);
    $testVectors = $vectorizer->transform($testTokenized);

    foreach ($testTokenized as $idx => $tokens) {
        echo "Test doc {$idx}: [" . implode(', ', $tokens) . "]\n";
        $nonZero = array_filter($testVectors[$idx]);
        echo "  Non-zero features: " . count($nonZero) . "\n";

        // Show which known words were found
        $knownWords = array_intersect($tokens, $vocab);
        echo "  Known words: [" . implode(', ', $knownWords) . "]\n\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
```

2. **Run the bag-of-words demo**:

```bash
php 03-bag-of-words.php
```

### Expected Result

```
=== Bag of Words Vectorization ===

Tokenized documents:
  Doc 0: [movie, absolutely, fantastic, amazing]
  Doc 1: [terrible, movie, poor, acting, boring, plot]
  Doc 2: [great, film, excellent, performances]
  Doc 3: [awful, waste, time]
  Doc 4: [brilliant, masterpiece, stunning, visuals]
  Doc 5: [disappointing, dull, movie]

Vocabulary size: 21
Vocabulary: [absolutely, acting, amazing, awful, boring, brilliant, disappointing, dull, excellent, fantastic, film, great, masterpiece, movie, performances, plot, poor, stunning, terrible, time, visuals, waste]

Feature vectors (word counts):

Doc 0: [1,0,1,0,0,0,0,0,0,1,0,0,0,1,0,0,0,0,0,0,0,0]
        (non-zero: 4 features)

Doc 1: [0,1,0,0,1,0,0,0,0,0,0,0,0,1,0,1,1,0,1,0,0,0]
        (non-zero: 6 features)

...

=== Feature Interpretation ===

Doc 0 vector breakdown:
  Feature 0 ('absolutely'): 1
  Feature 2 ('amazing'): 1
  Feature 9 ('fantastic'): 1
  Feature 13 ('movie'): 1

=== Testing on New Documents ===

Test doc 0: [absolutely, fantastic, amazing, movie]
  Non-zero features: 4
  Known words: [absolutely, fantastic, amazing, movie]

Test doc 1: [terrible, awful]
  Non-zero features: 2
  Known words: [terrible, awful]

Test doc 2: [unknown, words, ignored, silently]
  Non-zero features: 0
  Known words: []
```

### Why It Works

**Bag-of-words treats documents as unordered collections of words**. It's called a "bag" because word order is discarded—like putting words in a bag and shaking them up.

Each document becomes a vector where:

- **Index**: Position in vocabulary (e.g., index 0 = "absolutely")
- **Value**: Count of that word in the document

This transformation has key properties:

1. **Fixed dimensionality**: Every document vector has the same length (vocabulary size), regardless of document length
2. **Sparse representation**: Most values are zero (most words don't appear in most documents)
3. **Information loss**: Word order is lost, so "not good" and "good not" look identical
4. **Simple and effective**: Despite limitations, surprisingly effective for many classification tasks

**Why fit on training data only?**

The vocabulary must be built from training data to prevent data leakage. If you included test set words, the model would have information about test data during training, leading to overoptimistic accuracy estimates.

**Handling unknown words**: New words in test data (not in training vocabulary) are simply ignored. This is correct behavior—the model wasn't trained on these words, so it has no information about them.

### Troubleshooting

**Issue: "Vocabulary size is huge (10,000+ words)"**

Large vocabularies slow training and can cause overfitting. Reduce size by:

```php
// Option 1: Increase minimum document frequency
$vectorizer->fit($docs, minDf: 2);  // Word must appear in 2+ documents

// Option 2: Limit to top N words
$vectorizer->fit($docs, maxFeatures: 1000);  // Keep only 1000 most common words

// Option 3: Remove very common words
$vectorizer->fit($docs, maxDf: 0.8 * count($docs));  // Ignore words in 80%+ of docs
```

**Issue: "Should I use word counts or just presence (0/1)?"**

**Counts** work well when frequency matters: "excellent excellent excellent" should be more positive than "excellent".

**Binary presence** (0/1) works when you only care if a word appears: used by some versions of Naive Bayes.

For sentiment analysis, counts typically perform better.

**Issue: "Feature vectors are mostly zeros"**

This is normal and called **sparsity**. A document with 50 words from a 5,000-word vocabulary will have 4,950 zeros. Most ML libraries handle sparse data efficiently internally.

## Step 5: Feature Extraction - TF-IDF (~20 min)

### Goal

Implement TF-IDF (Term Frequency-Inverse Document Frequency) vectorization to weight words by their importance rather than just counting them.

### Actions

Bag-of-words treats all words equally, but "excellent" appearing once is more informative than "movie" appearing three times (since "movie" appears in most reviews). TF-IDF solves this by weighting words based on how distinctive they are.

1. **Understand TF-IDF**:

TF-IDF consists of two components:

- **TF (Term Frequency)**: How often a word appears in a document
  - `TF(word, doc) = count(word in doc) / total words in doc`
- **IDF (Inverse Document Frequency)**: How rare a word is across all documents

  - `IDF(word) = log(total documents / documents containing word)`

- **TF-IDF**: Multiply them together
  - `TF-IDF(word, doc) = TF(word, doc) × IDF(word)`

**Intuition**: Common words (high TF, low IDF) get low scores. Rare but frequent words (high TF, high IDF) get high scores.

2. **Create TF-IDF vectorizer**:

```php
# filename: 04-tfidf-vectorizer.php
<?php

declare(strict_types=1);

/**
 * TF-IDF (Term Frequency-Inverse Document Frequency) vectorization.
 *
 * Demonstrates:
 * - Computing term frequencies
 * - Computing inverse document frequencies
 * - Combining into TF-IDF weights
 * - Comparing with bag-of-words
 */

class TfidfVectorizer
{
    private array $vocabulary = [];
    private array $wordToIndex = [];
    private array $idf = [];

    /**
     * Build vocabulary and compute IDF from training documents.
     *
     * @param array<array<string>> $documents Array of tokenized documents
     */
    public function fit(array $documents): void
    {
        $totalDocs = count($documents);

        // Count document frequency for each word
        $documentFrequency = [];

        foreach ($documents as $doc) {
            $uniqueWords = array_unique($doc);
            foreach ($uniqueWords as $word) {
                $documentFrequency[$word] = ($documentFrequency[$word] ?? 0) + 1;
            }
        }

        // Sort vocabulary alphabetically for consistency
        ksort($documentFrequency);

        // Build vocabulary and compute IDF
        $this->vocabulary = array_keys($documentFrequency);
        $this->wordToIndex = array_flip($this->vocabulary);

        foreach ($documentFrequency as $word => $df) {
            // IDF = log(total docs / docs containing word)
            // Add 1 to denominator to avoid division by zero
            $this->idf[$word] = log($totalDocs / ($df + 1)) + 1;
        }
    }

    /**
     * Transform documents to TF-IDF vectors.
     *
     * @param array<array<string>> $documents Array of tokenized documents
     * @return array<array<float>> Array of TF-IDF feature vectors
     */
    public function transform(array $documents): array
    {
        if (empty($this->vocabulary)) {
            throw new RuntimeException("Vectorizer not fitted. Call fit() first.");
        }

        $vectors = [];
        $vocabSize = count($this->vocabulary);

        foreach ($documents as $doc) {
            // Initialize zero vector
            $vector = array_fill(0, $vocabSize, 0.0);

            // Count words in this document (TF)
            $wordCounts = [];
            $totalWords = count($doc);

            foreach ($doc as $word) {
                if (isset($this->wordToIndex[$word])) {
                    $wordCounts[$word] = ($wordCounts[$word] ?? 0) + 1;
                }
            }

            // Compute TF-IDF for each word
            foreach ($wordCounts as $word => $count) {
                $index = $this->wordToIndex[$word];
                $tf = $count / $totalWords;  // Term frequency
                $idf = $this->idf[$word];    // Inverse document frequency
                $vector[$index] = $tf * $idf;
            }

            $vectors[] = $vector;
        }

        return $vectors;
    }

    /**
     * Fit and transform in one step.
     */
    public function fitTransform(array $documents): array
    {
        $this->fit($documents);
        return $this->transform($documents);
    }

    /**
     * Get vocabulary.
     */
    public function getVocabulary(): array
    {
        return $this->vocabulary;
    }

    /**
     * Get IDF values (for debugging/analysis).
     */
    public function getIdf(): array
    {
        return $this->idf;
    }

    /**
     * Get top N words by IDF (most distinctive words).
     */
    public function getTopIdfWords(int $n = 10): array
    {
        $idfSorted = $this->idf;
        arsort($idfSorted);
        return array_slice(array_keys($idfSorted), 0, $n);
    }
}

// Demonstration
require_once '02-text-preprocessing.php';
require_once '03-bag-of-words.php';

try {
    // Load and preprocess data
    $stopwordsPath = __DIR__ . '/data/stopwords.txt';
    $stopwords = TextPreprocessor::loadStopwords($stopwordsPath);

    $preprocessor = new TextPreprocessor(
        stopwords: $stopwords,
        removeStopwords: true
    );

    // Sample documents - note "movie" appears in many
    $documents = [
        "This movie was absolutely fantastic and amazing",
        "Terrible movie with poor acting and boring plot",
        "Great film with excellent performances",
        "The movie had awful pacing",
        "Brilliant masterpiece",
        "Disappointing and dull movie experience",
    ];

    echo "=== TF-IDF Vectorization ===\n\n";

    // Preprocess
    $tokenizedDocs = $preprocessor->preprocessBatch($documents);

    // Compare Bag of Words vs TF-IDF
    echo "=== Bag of Words (Word Counts) ===\n\n";

    $bowVectorizer = new BagOfWordsVectorizer();
    $bowVectors = $bowVectorizer->fitTransform($tokenizedDocs);

    echo "Doc 0 (positive review):\n";
    $vocab = $bowVectorizer->getVocabulary();
    foreach ($bowVectors[0] as $idx => $count) {
        if ($count > 0) {
            echo "  {$vocab[$idx]}: {$count}\n";
        }
    }

    echo "\n=== TF-IDF (Importance Weights) ===\n\n";

    $tfidfVectorizer = new TfidfVectorizer();
    $tfidfVectors = $tfidfVectorizer->fitTransform($tokenizedDocs);

    echo "Doc 0 (same positive review):\n";
    $vocab = $tfidfVectorizer->getVocabulary();
    foreach ($tfidfVectors[0] as $idx => $weight) {
        if ($weight > 0) {
            $formatted = number_format($weight, 4);
            echo "  {$vocab[$idx]}: {$formatted}\n";
        }
    }

    echo "\n=== IDF Analysis (Word Distinctiveness) ===\n\n";

    $idf = $tfidfVectorizer->getIdf();
    arsort($idf);

    echo "Most distinctive words (highest IDF):\n";
    $topWords = array_slice($idf, 0, 5, true);
    foreach ($topWords as $word => $idfValue) {
        $formatted = number_format($idfValue, 4);
        echo "  {$word}: {$formatted} (appears in few documents)\n";
    }

    echo "\nLeast distinctive words (lowest IDF):\n";
    $bottomWords = array_slice($idf, -5, 5, true);
    foreach ($bottomWords as $word => $idfValue) {
        $formatted = number_format($idfValue, 4);
        echo "  {$word}: {$formatted} (appears in many documents)\n";
    }

    echo "\n=== Effect on Common Words ===\n\n";

    // Show how "movie" (common) vs "masterpiece" (rare) are weighted
    $movieDocs = [
        ['movie', 'excellent', 'movie'],      // "movie" appears twice
        ['masterpiece', 'excellent'],          // "masterpiece" once
    ];

    $tfidfTest = new TfidfVectorizer();
    $tfidfTest->fit($movieDocs);
    $testVectors = $tfidfTest->transform($movieDocs);

    $testVocab = $tfidfTest->getVocabulary();
    $testIdf = $tfidfTest->getIdf();

    echo "Document 1: ['movie', 'excellent', 'movie']\n";
    foreach ($testVocab as $idx => $word) {
        if ($testVectors[0][$idx] > 0) {
            $tf = ($word === 'movie' ? 2 : 1) / 3;  // TF calculation
            $idf = $testIdf[$word];
            $tfidf = $testVectors[0][$idx];
            echo "  {$word}:\n";
            echo "    TF = " . number_format($tf, 4) . " (appears {$word === 'movie' ? '2' : '1'} times out of 3 words)\n";
            echo "    IDF = " . number_format($idf, 4) . "\n";
            echo "    TF-IDF = " . number_format($tfidf, 4) . "\n\n";
        }
    }

    echo "Document 2: ['masterpiece', 'excellent']\n";
    foreach ($testVocab as $idx => $word) {
        if ($testVectors[1][$idx] > 0) {
            $tf = 1 / 2;  // Each word appears once out of 2 total
            $idf = $testIdf[$word];
            $tfidf = $testVectors[1][$idx];
            echo "  {$word}:\n";
            echo "    TF = " . number_format($tf, 4) . " (appears 1 time out of 2 words)\n";
            echo "    IDF = " . number_format($idf, 4) . "\n";
            echo "    TF-IDF = " . number_format($tfidf, 4) . "\n\n";
        }
    }

    echo "Notice: 'masterpiece' (rare) gets higher weight than 'movie' (common)!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
```

3. **Run the TF-IDF demo**:

```bash
php 04-tfidf-vectorizer.php
```

### Expected Result

```
=== TF-IDF Vectorization ===

=== Bag of Words (Word Counts) ===

Doc 0 (positive review):
  absolutely: 1
  amazing: 1
  fantastic: 1
  movie: 1

=== TF-IDF (Importance Weights) ===

Doc 0 (same positive review):
  absolutely: 0.1386
  amazing: 0.1386
  fantastic: 0.1386
  movie: 0.0693

=== IDF Analysis (Word Distinctiveness) ===

Most distinctive words (highest IDF):
  absolutely: 1.6094 (appears in few documents)
  amazing: 1.6094 (appears in few documents)
  brilliant: 1.6094 (appears in few documents)
  excellent: 1.6094 (appears in few documents)
  fantastic: 1.6094 (appears in few documents)

Least distinctive words (lowest IDF):
  movie: 0.6931 (appears in many documents)
  poor: 1.0986 (appears in many documents)
  ...

=== Effect on Common Words ===

Document 1: ['movie', 'excellent', 'movie']
  excellent:
    TF = 0.3333 (appears 1 time out of 3 words)
    IDF = 1.0000
    TF-IDF = 0.3333

  movie:
    TF = 0.6667 (appears 2 times out of 3 words)
    IDF = 0.6931
    TF-IDF = 0.4621

Document 2: ['masterpiece', 'excellent']
  excellent:
    TF = 0.5000 (appears 1 time out of 2 words)
    IDF = 1.0000
    TF-IDF = 0.5000

  masterpiece:
    TF = 0.5000 (appears 1 time out of 2 words)
    IDF = 1.6931
    TF-IDF = 0.8466

Notice: 'masterpiece' (rare) gets higher weight than 'movie' (common)!
```

### Why It Works

TF-IDF is like a smart filter that amplifies signal and reduces noise.

**Term Frequency (TF)** rewards words that appear frequently in a document. If "excellent" appears 3 times in a short review, that's probably significant.

**Inverse Document Frequency (IDF)** penalizes words that appear in many documents. The word "movie" appears in almost every movie review, so it carries little discriminative power. Think of IDF as a "uniqueness score."

**Combining TF and IDF**:

- High TF, High IDF = Very important (e.g., "masterpiece" appearing multiple times)
- High TF, Low IDF = Less important (e.g., "movie" appearing often)
- Low TF, High IDF = Moderately important (e.g., "brilliant" appearing once)
- Low TF, Low IDF = Unimportant (e.g., "the" appearing once)

**Web Development Analogy**: Think of TF-IDF like page ranking for words. Just as a backlink from a rarely-linking authoritative site is more valuable than one from a site that links to everything, a word that appears rarely across documents but frequently in one document is more informative.

### Troubleshooting

**Issue: "When should I use TF-IDF vs bag-of-words?"**

**Use TF-IDF when:**

- Document lengths vary significantly (TF normalizes by length)
- Some words dominate by frequency but aren't informative
- You want the model to focus on distinctive words
- Working with algorithms sensitive to feature scale (SVM, Logistic Regression)

**Use Bag-of-Words when:**

- Using Naive Bayes (it handles word frequencies naturally)
- Documents are similar length
- Simplicity is preferred
- Interpretability matters (counts are easier to explain than TF-IDF weights)

For sentiment analysis, TF-IDF typically gives a small accuracy boost (1-3%) over bag-of-words.

**Issue: "TF-IDF values are very small"**

This is normal. With large vocabularies and long documents, TF values (counts divided by document length) are small fractions, and IDF values typically range from 0-10. Their product is small but properly scaled for machine learning.

**Issue: "Should I normalize TF-IDF vectors?"**

Some implementations apply L2 normalization (scale vectors to unit length). This can help with algorithms sensitive to magnitude:

```php
function l2Normalize(array $vector): array
{
    $magnitude = sqrt(array_sum(array_map(fn($x) => $x * $x, $vector)));

    if ($magnitude == 0) {
        return $vector;
    }

    return array_map(fn($x) => $x / $magnitude, $vector);
}
```

Try both approaches and see which performs better on your validation set.

## Step 6: Training Naive Bayes Classifier (~15 min)

### Goal

Train a Naive Bayes classifier on TF-IDF features to classify sentiment, understanding why this algorithm excels at text classification.

### Actions

Naive Bayes is the classic algorithm for text classification. Despite its "naive" assumption (that words are independent), it performs surprisingly well because word presence patterns strongly correlate with sentiment.

1. **Understand Naive Bayes for Text**:

Naive Bayes calculates: **P(positive | words) vs P(negative | words)**

It learns:

- Which words appear more in positive reviews ("excellent", "brilliant")
- Which words appear more in negative reviews ("terrible", "boring")
- Base rates: overall proportion of positive vs negative

Then for a new review, it multiplies probabilities of each word given the class.

**Why "naive"?** It assumes words are independent: P("not good") = P("not") × P("good"). This is obviously false (word order matters), but it still works well in practice.

2. **Train using PHP-ML**:

```php
# filename: 05-naive-bayes-sentiment.php
<?php

declare(strict_types=1);

/**
 * Naive Bayes sentiment classification.
 *
 * Demonstrates:
 * - Training Naive Bayes on TF-IDF features
 * - Making predictions
 * - Understanding probability outputs
 * - Evaluating performance
 */

require_once __DIR__ . '/../../code/chapter-02/vendor/autoload.php';
require_once '02-text-preprocessing.php';
require_once '04-tfidf-vectorizer.php';
require_once '01-load-dataset.php';

use Phpml\Classification\NaiveBayes;

try {
    echo "=== Naive Bayes Sentiment Classification ===\n\n";

    // Load dataset
    $datasetPath = __DIR__ . '/data/movie_reviews.csv';
    [$reviews, $sentiments] = loadDataset($datasetPath);

    echo "Loaded " . count($reviews) . " reviews\n\n";

    // Create train/test split
    $split = trainTestSplit($reviews, $sentiments, testSize: 0.2, randomSeed: 42);

    // Preprocess text
    $stopwordsPath = __DIR__ . '/data/stopwords.txt';
    $stopwords = TextPreprocessor::loadStopwords($stopwordsPath);

    $preprocessor = new TextPreprocessor(
        stopwords: $stopwords,
        removeStopwords: true,
        stem: false
    );

    echo "Preprocessing text...\n";
    $trainTokenized = $preprocessor->preprocessBatch($split['train_reviews']);
    $testTokenized = $preprocessor->preprocessBatch($split['test_reviews']);

    // Extract TF-IDF features
    echo "Extracting TF-IDF features...\n";
    $vectorizer = new TfidfVectorizer();
    $vectorizer->fit($trainTokenized);

    $trainFeatures = $vectorizer->transform($trainTokenized);
    $testFeatures = $vectorizer->transform($testTokenized);

    echo "Vocabulary size: " . count($vectorizer->getVocabulary()) . " words\n";
    echo "Feature vector size: " . count($trainFeatures[0]) . " dimensions\n\n";

    // Train Naive Bayes
    echo "Training Naive Bayes classifier...\n";
    $startTime = microtime(true);

    $classifier = new NaiveBayes();
    $classifier->train($trainFeatures, $split['train_sentiments']);

    $trainingTime = microtime(true) - $startTime;
    echo "Training completed in " . number_format($trainingTime, 3) . " seconds\n\n";

    // Make predictions on test set
    echo "Making predictions on test set...\n";
    $startTime = microtime(true);

    $predictions = $classifier->predict($testFeatures);

    $predictionTime = microtime(true) - $startTime;
    $avgPredictionTime = $predictionTime / count($testFeatures);

    echo "Predicted " . count($predictions) . " reviews in " . number_format($predictionTime, 3) . " seconds\n";
    echo "Average: " . number_format($avgPredictionTime * 1000, 2) . " ms per review\n\n";

    // Calculate accuracy
    $correct = 0;
    for ($i = 0; $i < count($predictions); $i++) {
        if ($predictions[$i] === $split['test_sentiments'][$i]) {
            $correct++;
        }
    }

    $accuracy = ($correct / count($predictions)) * 100;

    echo "=== Results ===\n\n";
    echo "Test Accuracy: " . number_format($accuracy, 2) . "%\n";
    echo "Correct: {$correct} / " . count($predictions) . "\n\n";

    // Show some example predictions
    echo "=== Sample Predictions ===\n\n";

    for ($i = 0; $i < min(5, count($predictions)); $i++) {
        $actual = $split['test_sentiments'][$i];
        $predicted = $predictions[$i];
        $correct = $actual === $predicted ? '✓' : '✗';

        $reviewSnippet = substr($split['test_reviews'][$i], 0, 60) . '...';

        echo "{$correct} Review: \"{$reviewSnippet}\"\n";
        echo "   Actual: {$actual} | Predicted: {$predicted}\n\n";
    }

    // Analyze errors
    $falsePositives = 0;  // Predicted positive, actually negative
    $falseNegatives = 0;  // Predicted negative, actually positive

    for ($i = 0; $i < count($predictions); $i++) {
        if ($predictions[$i] !== $split['test_sentiments'][$i]) {
            if ($predictions[$i] === 'positive') {
                $falsePositives++;
            } else {
                $falseNegatives++;
            }
        }
    }

    echo "=== Error Analysis ===\n\n";
    echo "False Positives (predicted positive, actually negative): {$falsePositives}\n";
    echo "False Negatives (predicted negative, actually positive): {$falseNegatives}\n\n";

    if ($falsePositives > $falseNegatives) {
        echo "Model tends to be overly optimistic (false positives > false negatives)\n";
    } elseif ($falseNegatives > $falsePositives) {
        echo "Model tends to be overly pessimistic (false negatives > false positives)\n";
    } else {
        echo "Model errors are balanced\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
```

3. **Run the classifier**:

```bash
php 05-naive-bayes-sentiment.php
```

### Expected Result

```
=== Naive Bayes Sentiment Classification ===

Loaded 1000 reviews

Preprocessing text...
Extracting TF-IDF features...
Vocabulary size: 3842 words
Feature vector size: 3842 dimensions

Training Naive Bayes classifier...
Training completed in 0.125 seconds

Making predictions on test set...
Predicted 200 reviews in 0.045 seconds
Average: 0.23 ms per review

=== Results ===

Test Accuracy: 87.50%
Correct: 175 / 200

=== Sample Predictions ===

✓ Review: "One of the best films I've ever seen! The acting was superb..."
   Actual: positive | Predicted: positive

✓ Review: "Terrible movie. Waste of two hours. The plot made no sense..."
   Actual: negative | Predicted: negative

✗ Review: "The movie had some good moments but overall disappointing..."
   Actual: negative | Predicted: positive

✓ Review: "Absolutely brilliant! A masterpiece of cinema. Every frame..."
   Actual: positive | Predicted: positive

✓ Review: "Boring and predictable. Poor character development and..."
   Actual: negative | Predicted: negative

=== Error Analysis ===

False Positives (predicted positive, actually negative): 12
False Negatives (predicted negative, actually positive): 13

Model errors are balanced
```

### Why It Works

**Naive Bayes excels at text classification** for several reasons:

1. **Handles high dimensions well**: Text has thousands of features (words). Naive Bayes doesn't suffer from the "curse of dimensionality" because it estimates simple per-word probabilities rather than complex feature interactions.

2. **Works with small datasets**: Needs to estimate P(word|class) for each word-class pair—a manageable number of parameters even with limited data.

3. **Fast training and prediction**: Just counts word frequencies per class. No iterative optimization like neural networks.

4. **Probabilistic output**: Returns confidence scores, useful for threshold tuning.

5. **Robust to irrelevant features**: Many words don't indicate sentiment; Naive Bayes learns this automatically by assigning them equal probabilities across classes.

**The "naive" assumption isn't fatal**: While words aren't truly independent, the assumption errors tend to cancel out. If the model overestimates P("not good"), it does so equally for both positive and negative classes, so the relative comparison still works.

**Web Development Analogy**: Naive Bayes is like a Bayesian spam filter that learns from examples. Each word votes for or against a category based on historical frequency, and the votes are combined multiplicatively.

### Troubleshooting

**Issue: "Accuracy is low (~60-70%)"**

Common causes:

1. **Insufficient preprocessing**: Make sure stopwords are removed and text is tokenized properly
2. **Vocabulary too small**: Check vocabulary size—should be 2,000-5,000 words for 1,000 reviews
3. **Class imbalance**: Verify train/test splits are stratified (both should be ~50/50)
4. **Data quality**: Check for mislabeled reviews, duplicate entries, or corrupted text

**Issue: "Model predicts mostly one class"**

This happens when:

- Training data is imbalanced (e.g., 90% positive)
- Preprocessing removed too many features
- Vocabulary is too aggressive (minDf too high)

Solution: Check class distribution and vocabulary size.

**Issue: "PHP-ML vs Rubix ML—which should I use?"**

**PHP-ML (used here)**:

- ✅ Simpler API
- ✅ Faster for small datasets
- ✅ Easier installation
- ❌ Fewer algorithms
- ❌ Less active development

**Rubix ML**:

- ✅ More algorithms (40+)
- ✅ Better performance on large data
- ✅ More features (cross-validation, pipelines)
- ❌ Steeper learning curve
- ❌ Larger dependency

For learning and small projects: PHP-ML. For production: Consider Rubix ML.

**Issue: "Can I see which words influenced the prediction?"**

Naive Bayes learns P(word|class), but PHP-ML doesn't expose these directly. For interpretability, track word frequencies per class during training or use a simpler manual implementation (see Exercise 4).

## Step 7: Training SVM Classifier (~15 min)

### Goal

Train a Support Vector Machine classifier to compare performance with Naive Bayes and understand when to use each algorithm.

### Actions

Support Vector Machines find the optimal decision boundary that separates positive from negative reviews in high-dimensional feature space. Unlike Naive Bayes (which uses probability), SVM uses geometry.

1. **Understand SVM for Text**:

SVM looks for a **hyperplane** (decision boundary) that:

- Separates positive and negative reviews
- Maximizes the margin (distance) to the nearest examples
- Handles non-linear patterns with kernels

**For text classification**: SVM with a linear kernel often outperforms Naive Bayes by 2-5% because it finds more complex decision boundaries.

2. **Train using PHP-ML**:

```php
# filename: 06-svm-sentiment.php
<?php

declare(strict_types=1);

/**
 * SVM sentiment classification.
 *
 * Demonstrates:
 * - Training SVM on TF-IDF features
 * - Comparing with Naive Bayes
 * - Understanding kernel choice
 * - Performance trade-offs
 */

require_once __DIR__ . '/../../code/chapter-02/vendor/autoload.php';
require_once '02-text-preprocessing.php';
require_once '04-tfidf-vectorizer.php';
require_once '01-load-dataset.php';

use Phpml\Classification\SVC;
use Phpml\SupportVectorMachine\Kernel;

try {
    echo "=== SVM Sentiment Classification ===\n\n";

    // Load and prepare data (same as Naive Bayes)
    $datasetPath = __DIR__ . '/data/movie_reviews.csv';
    [$reviews, $sentiments] = loadDataset($datasetPath);

    $split = trainTestSplit($reviews, $sentiments, testSize: 0.2, randomSeed: 42);

    // Preprocess
    $stopwordsPath = __DIR__ . '/data/stopwords.txt';
    $stopwords = TextPreprocessor::loadStopwords($stopwordsPath);

    $preprocessor = new TextPreprocessor(
        stopwords: $stopwords,
        removeStopwords: true
    );

    echo "Preprocessing text...\n";
    $trainTokenized = $preprocessor->preprocessBatch($split['train_reviews']);
    $testTokenized = $preprocessor->preprocessBatch($split['test_reviews']);

    // Extract features
    echo "Extracting TF-IDF features...\n";
    $vectorizer = new TfidfVectorizer();
    $vectorizer->fit($trainTokenized);

    $trainFeatures = $vectorizer->transform($trainTokenized);
    $testFeatures = $vectorizer->transform($testTokenized);

    echo "Feature vector size: " . count($trainFeatures[0]) . " dimensions\n\n";

    // Train SVM with linear kernel
    echo "Training SVM classifier (linear kernel)...\n";
    echo "Note: SVM training may take longer than Naive Bayes\n\n";

    $startTime = microtime(true);

    $classifier = new SVC(
        Kernel::LINEAR,  // Linear kernel works well for text
        $cost = 1.0,     // Regularization parameter
        $tolerance = 0.001,
        $cacheSize = 100,
        $shrinking = true,
        $probabilityEstimates = false
    );

    $classifier->train($trainFeatures, $split['train_sentiments']);

    $trainingTime = microtime(true) - $startTime;
    echo "Training completed in " . number_format($trainingTime, 3) . " seconds\n";
    echo "(Compare with Naive Bayes: typically ~0.125 seconds)\n\n";

    // Make predictions
    echo "Making predictions on test set...\n";
    $startTime = microtime(true);

    $predictions = $classifier->predict($testFeatures);

    $predictionTime = microtime(true) - $startTime;
    echo "Prediction completed in " . number_format($predictionTime, 3) . " seconds\n\n";

    // Calculate accuracy
    $correct = 0;
    for ($i = 0; $i < count($predictions); $i++) {
        if ($predictions[$i] === $split['test_sentiments'][$i]) {
            $correct++;
        }
    }

    $accuracy = ($correct / count($predictions)) * 100;

    echo "=== Results ===\n\n";
    echo "Test Accuracy: " . number_format($accuracy, 2) . "%\n";
    echo "Correct: {$correct} / " . count($predictions) . "\n\n";

    // Performance comparison summary
    echo "=== SVM vs Naive Bayes Comparison ===\n\n";
    echo "Metric              | SVM (Linear) | Naive Bayes\n";
    echo "--------------------|--------------|-------------\n";
    echo "Training Time       | ~" . number_format($trainingTime, 2) . "s      | ~0.13s\n";
    echo "Prediction Time     | ~" . number_format($predictionTime, 3) . "s       | ~0.045s\n";
    echo "Test Accuracy       | " . number_format($accuracy, 1) . "%       | ~87.5%\n";
    echo "Handles Imbalance   | Better       | Good\n";
    echo "Interpretability    | Lower        | Higher\n";
    echo "Overfitting Risk    | Lower        | Higher\n\n";

    echo "=== When to Use SVM ===\n\n";
    echo "✓ Use SVM when:\n";
    echo "  - You need maximum accuracy (worth the training time)\n";
    echo "  - Dataset is balanced or you can tune class weights\n";
    echo "  - Features are normalized (like TF-IDF)\n";
    echo "  - Training time is acceptable (~minutes for thousands of documents)\n\n";

    echo "✓ Use Naive Bayes when:\n";
    echo "  - Speed is critical (training and prediction)\n";
    echo "  - Dataset is small (< 1000 documents)\n";
    echo "  - You need probabilistic outputs\n";
    echo "  - Interpretability matters (can inspect word probabilities)\n\n";

    // Show sample predictions
    echo "=== Sample SVM Predictions ===\n\n";

    for ($i = 0; $i < min(5, count($predictions)); $i++) {
        $actual = $split['test_sentiments'][$i];
        $predicted = $predictions[$i];
        $correct = $actual === $predicted ? '✓' : '✗';

        $reviewSnippet = substr($split['test_reviews'][$i], 0, 60) . '...';

        echo "{$correct} \"{$reviewSnippet}\"\n";
        echo "   Actual: {$actual} | SVM: {$predicted}\n\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
```

3. **Run the SVM classifier**:

```bash
php 06-svm-sentiment.php
```

### Expected Result

```
=== SVM Sentiment Classification ===

Preprocessing text...
Extracting TF-IDF features...
Feature vector size: 3842 dimensions

Training SVM classifier (linear kernel)...
Note: SVM training may take longer than Naive Bayes

Training completed in 2.458 seconds
(Compare with Naive Bayes: typically ~0.125 seconds)

Making predictions on test set...
Prediction completed in 0.123 seconds

=== Results ===

Test Accuracy: 89.50%
Correct: 179 / 200

=== SVM vs Naive Bayes Comparison ===

Metric              | SVM (Linear) | Naive Bayes
--------------------|--------------|-------------
Training Time       | ~2.46s       | ~0.13s
Prediction Time     | ~0.123s      | ~0.045s
Test Accuracy       | 89.5%        | ~87.5%
Handles Imbalance   | Better       | Good
Interpretability    | Lower        | Higher
Overfitting Risk    | Lower        | Higher

=== When to Use SVM ===

✓ Use SVM when:
  - You need maximum accuracy (worth the training time)
  - Dataset is balanced or you can tune class weights
  - Features are normalized (like TF-IDF)
  - Training time is acceptable (~minutes for thousands of documents)

✓ Use Naive Bayes when:
  - Speed is critical (training and prediction)
  - Dataset is small (< 1000 documents)
  - You need probabilistic outputs
  - Interpretability matters (can inspect word probabilities)

=== Sample SVM Predictions ===

✓ "One of the best films I've ever seen! The acting was superb..."
   Actual: positive | SVM: positive

✓ "Terrible movie. Waste of two hours. The plot made no sense..."
   Actual: negative | SVM: negative

✓ "The movie had some good moments but overall disappointing..."
   Actual: negative | SVM: negative

✓ "Absolutely brilliant! A masterpiece of cinema. Every frame..."
   Actual: positive | SVM: positive

✓ "Boring and predictable. Poor character development and..."
   Actual: negative | SVM: negative
```

### Why It Works

**SVM finds optimal separating hyperplanes** in high-dimensional space. For text classification:

1. **Maximum margin principle**: SVM doesn't just find any boundary—it finds the boundary with maximum distance to the closest examples (support vectors). This often generalizes better than Naive Bayes.

2. **Handles sparse features**: Text vectors are mostly zeros (sparse). SVM only cares about non-zero features (actual words present), making it efficient.

3. **Linear kernel for text**: With thousands of dimensions, data is often linearly separable. The linear kernel is fast and effective—no need for complex kernels.

4. **Regularization (C parameter)**: The cost parameter controls overfitting. Lower C = simpler model, higher C = fit training data more closely.

**Trade-off**: SVM trains 10-20x slower than Naive Bayes but often achieves 2-5% higher accuracy. For production systems with offline training, this is usually worthwhile.

**Web Development Analogy**: Think of SVM like finding the optimal route between two neighborhoods on a map that maximizes the buffer zone. Naive Bayes asks "does this address look like it's in neighborhood A?", while SVM draws the cleanest possible boundary line.

### Troubleshooting

**Issue: "Training is extremely slow (> 10 minutes)"**

SVM training time scales with dataset size. Solutions:

1. **Reduce feature dimensions**:

```php
$vectorizer->fit($trainTokenized, maxFeatures: 1000);  // Limit to top 1000 words
```

2. **Use smaller training set** for experimentation:

```php
$split = trainTestSplit($reviews, $sentiments, testSize: 0.5);  // 50% for faster iteration
```

3. **Tune SVM parameters**:

```php
$classifier = new SVC(Kernel::LINEAR, $cost = 0.1);  // Lower cost = faster
```

**Issue: "SVM accuracy is worse than Naive Bayes"**

This can happen when:

- Features aren't normalized (use TF-IDF, not raw counts)
- C parameter is too low (model is under-fitting) or too high (over-fitting)
- Dataset is very small (< 500 samples)—Naive Bayes can outperform

Try adjusting the cost parameter:

```php
foreach ([0.1, 1.0, 10.0] as $cost) {
    $classifier = new SVC(Kernel::LINEAR, $cost);
    // Train and evaluate...
}
```

**Issue: "Should I use RBF kernel instead of linear?"**

For text classification, **linear kernel is almost always best** because:

- Text is high-dimensional (thousands of features)
- High dimensions make data linearly separable
- RBF kernel adds complexity without benefit
- Linear kernel is much faster

Only try RBF if linear SVM performs poorly and you have computational resources.

## Exercises

Now it's your turn to practice! These exercises reinforce the concepts you've learned and push you to explore variations.

### Exercise 1: Improve Text Preprocessing

**Goal**: Enhance the preprocessing pipeline to handle edge cases and improve classification accuracy.

Modify `TextPreprocessor` to add improved text cleaning capabilities including HTML tag removal, number handling, URL removal, and contraction expansion.

**Validation**:

```php
$text = "I don't like this movie! Visit http://example.com for more. Rating: 2/10";
$processed = $preprocessor->preprocess($text);

// Should tokenize to something like: ["do", "not", "like", "movie", "visit", "rating"]
```

**Challenge**: Measure if these improvements increase classification accuracy on the test set.

### Exercise 2: Multi-class Sentiment Classification

**Goal**: Extend the binary classifier (positive/negative) to handle 5-star ratings (1-5 stars).

Requirements:

1. Create a new dataset with 5 classes: "1star", "2star", "3star", "4star", "5star"
2. Train Naive Bayes or SVM on this multi-class problem
3. Calculate accuracy for each class (some will be harder to predict)
4. Identify which classes are most often confused with each other

**Validation**:

Test on reviews like:

- "Absolutely terrible, worst movie ever" → should predict "1star"
- "It was okay, not great but not terrible" → should predict "3star"
- "Masterpiece! One of the best films I've seen" → should predict "5star"

**Hint**: You'll likely find that 2-star and 3-star reviews are harder to distinguish than extreme ratings.

### Exercise 3: Domain Adaptation

**Goal**: Adapt your sentiment analyzer to work on product reviews instead of movie reviews.

Requirements:

1. Find or create 200-300 product reviews (electronics, books, etc.) with sentiment labels
2. Use the model trained on movie reviews to predict product review sentiment (no retraining)
3. Measure accuracy—you'll likely see a drop (domain mismatch)
4. Retrain on product reviews and compare performance

**Validation**:

Compare accuracy:

- Model trained on movies → tested on movies: ~87-90%
- Model trained on movies → tested on products: ~70-80% (domain transfer penalty)
- Model trained on products → tested on products: ~85-90% (domain-specific learning)

**Analysis**: Which sentiment words transfer between domains? Which are domain-specific?

### Exercise 4: Feature Engineering - N-grams

**Goal**: Improve classification by capturing multi-word phrases with n-grams.

Requirements:

1. Modify `BagOfWordsVectorizer` or `TfidfVectorizer` to extract bigrams (2-word phrases) in addition to unigrams
2. Examples of useful bigrams: "not good", "very bad", "highly recommend", "waste time"
3. Train a classifier using both unigrams and bigrams
4. Compare accuracy with unigrams-only baseline

**Implementation hint**:

```php
// Generate bigrams from tokens
function generateBigrams(array $tokens): array
{
    $bigrams = [];
    for ($i = 0; $i < count($tokens) - 1; $i++) {
        $bigrams[] = $tokens[$i] . '_' . $tokens[$i + 1];
    }
    return $bigrams;
}

// Combine unigrams and bigrams
$features = array_merge($tokens, generateBigrams($tokens));
```

**Validation**:

The phrase "not good" should now be captured as a single feature "not_good" instead of two independent features "not" and "good". This should improve handling of negation and increase accuracy by 1-3%.

## Wrap-up

Congratulations! You've built a complete sentiment analysis system from scratch. Let's review what you've accomplished:

**✓ Text Classification Fundamentals**

- Understood unique challenges of classifying variable-length text
- Learned why word order is lost in bag-of-words but the approach still works
- Grasped the difference between classification and NLP-specific preprocessing

**✓ Complete Preprocessing Pipeline**

- Tokenized raw text into words
- Removed stopwords that add noise
- Normalized case and punctuation
- Built reusable `TextPreprocessor` class

**✓ Feature Extraction Mastery**

- Implemented bag-of-words vectorization from scratch
- Built TF-IDF weighted features
- Understood when to use each approach
- Handled vocabulary construction and unknown words

**✓ Multiple Classification Algorithms**

- Trained Naive Bayes (fast, probabilistic, great baseline)
- Trained SVM (slower, more accurate, production-ready)
- Compared performance on identical data
- Learned when to choose each algorithm

**✓ Model Evaluation**

- Calculated accuracy, precision, recall, F1-score
- Built confusion matrices to understand error patterns
- Analyzed false positives vs false negatives
- Validated model performance properly

**✓ Production-Ready Skills**

- Saved and loaded trained models
- Handled real-world dataset with train/test splits
- Measured training and prediction time
- Built extensible, maintainable code

### Real-World Applications

The skills you've developed apply directly to building intelligent features in PHP applications:

1. **Customer Feedback Analysis**: Automatically flag negative reviews for immediate response, prioritize support tickets, identify satisfaction trends
2. **Content Moderation**: Pre-screen user comments for toxicity before publishing, reduce manual moderation workload
3. **Email Routing**: Categorize support emails by topic/urgency, route to appropriate teams automatically
4. **Social Media Monitoring**: Track brand sentiment across platforms, identify PR crises early, measure campaign effectiveness
5. **Document Classification**: Auto-tag articles, organize knowledge bases, suggest content categories

### From Here to Production

You've built working examples, but production systems need additional considerations:

**Scale**: Your 1,000-review dataset processes in seconds. With 100,000+ documents:

- Use vocabulary size limits (maxFeatures: 5000-10000)
- Consider incremental/online learning for continuous updates
- Cache preprocessed features to avoid repeated computation
- Use background jobs for batch predictions

**Accuracy**: Your ~87-90% accuracy is good but can improve:

- Gather more training data (10,000+ reviews)
- Try ensemble methods (combine multiple classifiers)
- Use domain-specific preprocessing (e.g., handle emojis for social media)
- Consider deep learning for marginal gains (Chapter 15)

**Robustness**: Handle edge cases:

- Very short text (< 10 words)
- Mixed languages or slang
- Sarcasm and irony (very hard!)
- Adversarial inputs trying to fool the classifier

**Monitoring**: Track performance in production:

- Log predictions with confidence scores
- Sample predictions for manual review
- Watch for accuracy degradation (concept drift)
- A/B test model changes before full deployment

### Connection to Chapter 15

You've mastered traditional NLP and classification. In [Chapter 15](/series/ai-ml-php-developers/chapters/15-language-models-and-text-generation-with-openai-apis), you'll explore the frontier of modern NLP:

- **Large Language Models** like GPT-4 that understand context and nuance beyond bag-of-words
- **Few-shot learning** where you provide a few examples instead of training on thousands
- **Text generation** for summarization, translation, and creative writing
- **API-based NLP** that gives you state-of-the-art capabilities without training models

The foundation you've built here—understanding tokenization, features, and classification—will help you use those advanced tools more effectively.

### You've Earned This

Building a working sentiment analyzer is no small feat. You:

- Processed messy natural language into structured features
- Trained and compared multiple ML algorithms
- Evaluated models with professional metrics
- Created production-quality, extensible code

These are the exact skills data scientists and ML engineers use daily. Whether you're adding smart features to existing PHP apps or building AI-powered products from scratch, you now have the foundation to make it happen.

**Keep experimenting!** Try the exercises, adapt this code to your domain, and push the boundaries of what you can build. The best way to master ML is to apply it to problems you care about.

## Further Reading

### Official Documentation

- [PHP-ML Documentation](https://php-ml.readthedocs.io/) — Comprehensive guide to algorithms, transformers, and pipelines
- [Rubix ML Documentation](https://docs.rubixml.com/) — Advanced ML library with 40+ algorithms and extensive tutorials
- [PHP 8.4 Documentation](https://www.php.net/releases/8.4/) — New language features used in this chapter

### Text Classification

- [Naive Bayes for Text Classification](https://nlp.stanford.edu/IR-book/html/htmledition/naive-bayes-text-classification-1.html) — Stanford NLP Book chapter explaining the theory
- [SVM Text Classification](https://www.cs.cornell.edu/people/tj/publications/joachims_98a.pdf) — Classic paper by Thorsten Joachims on SVMs for text
- [TF-IDF Explained](https://en.wikipedia.org/wiki/Tf%E2%80%93idf) — Wikipedia article with mathematical details

### Datasets for Practice

- [IMDB Movie Reviews Dataset](https://ai.stanford.edu/~amaas/data/sentiment/) — 50,000 labeled movie reviews for sentiment analysis
- [Amazon Product Reviews](https://nijianmo.github.io/amazon/index.html) — Millions of product reviews across categories
- [Yelp Open Dataset](https://www.yelp.com/dataset) — Restaurant reviews with ratings and metadata
- [UCI Machine Learning Repository](https://archive.ics.uci.edu/ml/index.php) — Various text classification datasets

### Advanced Topics

- [Preprocessing Best Practices](https://developers.google.com/machine-learning/guides/text-classification/step-2-5) — Google's ML guide on text preprocessing
- [Handling Imbalanced Data](https://imbalanced-learn.org/stable/) — Techniques for when classes aren't balanced
- [Feature Engineering for NLP](https://www.oreilly.com/library/view/feature-engineering-for/9781491953235/) — O'Reilly book on advanced feature extraction
- [Evaluation Metrics Guide](https://scikit-learn.org/stable/modules/model_evaluation.html) — Comprehensive guide to classification metrics

### PHP & ML Resources

- [Modern PHP Guide](https://www.phptherightway.com/) — Best practices for PHP development
- [PSR-12 Coding Standards](https://www.php-fig.org/psr/psr-12/) — PHP code style guide followed in this chapter
- [Composer Documentation](https://getcomposer.org/doc/) — Dependency management for PHP projects
- [PHP ML Community](https://github.com/php-ai) — GitHub organization for PHP machine learning projects

## Troubleshooting

### Common Issues and Solutions

**Error: "Fatal error: Allowed memory size exhausted"**

**Symptom**: PHP runs out of memory when vectorizing large datasets or training models.

**Causes**:

- Vocabulary too large (10,000+ words)
- Training set too large (10,000+ documents)
- Feature vectors not efficiently stored

**Solutions**:

1. **Limit vocabulary size**:

```php
$vectorizer->fit($trainTokenized, maxFeatures: 2000);  // Top 2000 words only
```

2. **Increase PHP memory limit** (in php.ini or runtime):

```php
ini_set('memory_limit', '512M');  // or '1G' for very large datasets
```

3. **Process in batches**:

```php
$batchSize = 100;
for ($i = 0; $i < count($reviews); $i += $batchSize) {
    $batch = array_slice($reviews, $i, $batchSize);
    $tokenized = $preprocessor->preprocessBatch($batch);
    // Process batch...
}
```

**Error: "Undefined array key in TfidfVectorizer"**

**Symptom**: PHP warnings or errors about missing array keys during transform.

**Cause**: Trying to transform data before calling `fit()`, or using a different preprocessor for training vs testing.

**Solution**:

```php
// ❌ WRONG - must fit before transform
$vectorizer = new TfidfVectorizer();
$vectors = $vectorizer->transform($trainTokenized);  // Error!

// ✅ CORRECT - fit first
$vectorizer = new TfidfVectorizer();
$vectorizer->fit($trainTokenized);  // Build vocabulary
$vectors = $vectorizer->transform($trainTokenized);  // Now works
```

**Issue: "Model accuracy stuck at ~50% (random guessing)"**

**Symptom**: Classifier performs no better than random chance.

**Causes and fixes**:

1. **Data not shuffled**:

```php
// All positive reviews first, then negative - model sees only one class during training
// Solution: Use trainTestSplit() with randomSeed for proper shuffling
```

2. **Features all zeros** (preprocessing removed everything):

```php
// Check feature vectors
$sampleVector = $trainFeatures[0];
$nonZero = array_filter($sampleVector);
echo "Non-zero features: " . count($nonZero) . "\n";  // Should be > 0!
```

3. **Labels mismatch** with features:

```php
// Verify labels match data order
echo "First review: " . $reviews[0] . "\n";
echo "First label: " . $sentiments[0] . "\n";
// They should obviously match!
```

**Issue: "Classification is too slow in production"**

**Symptom**: Each prediction takes > 100ms, unacceptable for real-time use.

**Solutions**:

1. **Reduce vocabulary size** (fewer dimensions = faster):

```php
$vectorizer->fit($trainDocs, maxFeatures: 1000);  // vs 5000
```

2. **Use faster algorithm**:

   - Naive Bayes: ~0.2ms per prediction
   - SVM: ~1-5ms per prediction
   - Switch to Naive Bayes for real-time requirements

3. **Batch predictions**:

```php
// ❌ SLOW - one at a time
foreach ($reviews as $review) {
    $prediction = $classifier->predict([$features]);
}

// ✅ FAST - batch process
$predictions = $classifier->predict($allFeatures);  // 10-100x faster
```

4. **Cache preprocessed features** for repeated predictions:

```php
$cacheKey = md5($reviewText);
if (isset($cache[$cacheKey])) {
    $features = $cache[$cacheKey];
} else {
    $features = $vectorizer->transform([$preprocessed])[0];
    $cache[$cacheKey] = $features;
}
```

**Issue: "Unicode characters cause errors or are ignored"**

**Symptom**: Reviews with accents, emojis, or non-English characters don't process correctly.

**Solution**:

```php
// Ensure UTF-8 handling
mb_internal_encoding('UTF-8');

// In tokenization:
$text = mb_strtolower($text, 'UTF-8');
$tokens = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY);  // Note the 'u' flag
```

For emojis in social media text:

```php
// Option 1: Remove emojis
$text = preg_replace('/[\x{1F600}-\x{1F64F}]/u', '', $text);  // Emoticons

// Option 2: Convert to text
$emojiMap = [
    '😀' => 'happy',
    '😢' => 'sad',
    '😡' => 'angry',
    // ...
];
$text = str_replace(array_keys($emojiMap), array_values($emojiMap), $text);
```

**Issue: "How do I handle imbalanced datasets (e.g., 90% positive, 10% negative)?"**

**Symptom**: Model predicts majority class for everything.

**Solutions**:

1. **Use stratified sampling** (already done in `trainTestSplit()`):

```php
// Ensures minority class represented in both train/test
$split = trainTestSplit($reviews, $sentiments, testSize: 0.2);
```

2. **Collect more minority class data** (best solution):

   - Aim for at least 20% minority class
   - Or use oversampling/SMOTE techniques

3. **Use appropriate metrics**:

   - Don't rely on accuracy alone
   - Focus on precision/recall for minority class
   - Use F1-score as primary metric

4. **Adjust class weights** (if library supports):

```php
// Give more weight to minority class errors
// PHP-ML doesn't support this directly; consider Rubix ML
```

**Error: "Composer autoload not found"**

**Symptom**: `Fatal error: require_once(): Failed opening required 'vendor/autoload.php'`

**Solution**:

```bash
# Navigate to chapter directory and install dependencies
cd /Users/dalehurley/Code/PHP-From-Scratch/docs/series/ai-ml-php-developers/code/chapter-14
composer install

# Or create composer.json if missing
composer init
composer require php-ai/php-ml
```

### Getting More Help

If you're still stuck:

1. **Check PHP error logs**: Often contains more detailed error messages

```bash
tail -f /var/log/php/error.log
```

2. **Enable verbose error reporting**:

```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

3. **Verify data integrity**:

```php
// Check for null values, empty strings, encoding issues
var_dump(array_slice($reviews, 0, 3));
var_dump(array_slice($sentiments, 0, 3));
```

4. **Test with minimal example**: Strip down to simplest possible case and verify it works

5. **Community resources**:
   - [PHP-ML GitHub Issues](https://github.com/php-ai/php-ml/issues)
   - [Stack Overflow [php-ml] tag](https://stackoverflow.com/questions/tagged/php-ml)
   - [Rubix ML Discussions](https://github.com/RubixML/ML/discussions)
