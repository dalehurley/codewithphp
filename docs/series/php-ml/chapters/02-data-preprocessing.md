---
title: "02: Data Preprocessing and Feature Engineering"
description: "Master data cleaning, normalization, encoding, and feature extraction for optimal ML performance"
series: "php-ml"
chapter: 2
order: 2
difficulty: "Advanced"
prerequisites:
  - "Completion of Chapter 01"
  - "Understanding of basic statistics"
---

![02: Data Preprocessing and Feature Engineering](/images/php-ml/chapter-02-preprocessing-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/php-ml">Machine Learning with PHP-ML</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 02</span>
</div>

# Chapter 02: Data Preprocessing and Feature Engineering

## Overview

Data preprocessing is critical for ML success—models are only as good as the data they're trained on. This chapter teaches you to clean, transform, and engineer features that dramatically improve model performance.

**Estimated time:** 150 minutes

By the end of this chapter, you will:

- Clean and handle missing data with imputation strategies
- Normalize and standardize features for better model performance
- Encode categorical variables (label encoding, one-hot encoding)
- Extract meaningful features from raw data
- Apply feature scaling and transformation techniques
- Build preprocessing pipelines for production use

## Topics Covered

### Data Cleaning
- Handling missing values (imputation strategies)
- Removing outliers and noise
- Dealing with duplicate records
- Type conversions and validation

### Feature Scaling
- Normalization (Min-Max scaling)
- Standardization (Z-score)
- When to use each technique
- PHP-ML Normalizer class

### Encoding Categorical Data
- Label Encoding for ordinal data
- One-Hot Encoding for nominal data
- Binary encoding strategies
- Handling high-cardinality features

### Feature Engineering
- Creating derived features
- Polynomial features
- Feature interactions
- Domain-specific transformations

### Feature Extraction from Text
- Token Count Vectorizer
- TF-IDF Transformer
- N-grams and vocabulary building
- Text preprocessing techniques

## Step 1: Handling Missing Data (~25 min)

Learn imputation strategies for incomplete datasets.

### Mean/Median/Most Frequent Imputation

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Phpml\Preprocessing\Imputer;
use Phpml\Preprocessing\Imputer\Strategy\MeanStrategy;
use Phpml\Preprocessing\Imputer\Strategy\MedianStrategy;
use Phpml\Preprocessing\Imputer\Strategy\MostFrequentStrategy;

// Data with missing values (null)
$samples = [
    [1, 2, 3],
    [4, null, 6],   // Missing value in column 2
    [7, 8, null],   // Missing value in column 3
    [null, 11, 12], // Missing value in column 1
];

echo "Original data:\n";
print_r($samples);

// Strategy 1: Fill with mean
$meanImputer = new Imputer(null, new MeanStrategy());
$samplesCopy = $samples;
$meanImputer->fit($samplesCopy);
$meanImputer->transform($samplesCopy);

echo "\nAfter mean imputation:\n";
print_r($samplesCopy);

// Strategy 2: Fill with median
$medianImputer = new Imputer(null, new MedianStrategy());
$samplesCopy = $samples;
$medianImputer->fit($samplesCopy);
$medianImputer->transform($samplesCopy);

echo "\nAfter median imputation:\n";
print_r($samplesCopy);

// Strategy 3: Fill with most frequent value
$samples2 = [
    ['red', 'M'],
    ['blue', null],
    ['red', 'L'],
    [null, 'M'],
];

$mfImputer = new Imputer(null, new MostFrequentStrategy());
$mfImputer->fit($samples2);
$mfImputer->transform($samples2);

echo "\nAfter most frequent imputation:\n";
print_r($samples2);
```

## Step 2: Feature Normalization (~20 min)

Scale features to [0, 1] range for consistent model input.

### Normalization with PHP-ML

```php
<?php

use Phpml\Preprocessing\Normalizer;

// Features with different scales
// [age, income, purchases]
$samples = [
    [25, 50000, 10],
    [35, 80000, 25],
    [45, 120000, 50],
];

echo "Before normalization:\n";
print_r($samples);

$normalizer = new Normalizer();
$normalizer->transform($samples);

echo "\nAfter normalization (L2 norm):\n";
print_r($samples);

// Normalize using different norms
$normalizer = new Normalizer(Normalizer::NORM_L1);
$samples = [
    [25, 50000, 10],
    [35, 80000, 25],
];
$normalizer->transform($samples);

echo "\nAfter L1 normalization:\n";
print_r($samples);
```

## Step 3: Encoding Categorical Variables (~30 min)

Convert text labels to numerical representations.

### Label Encoding

```php
<?php

use Phpml\Preprocessing\LabelEncoder;

$labels = ['red', 'blue', 'green', 'blue', 'red', 'green'];

$encoder = new LabelEncoder();
$encoder->fit($labels);
$encoded = $encoder->transform($labels);

echo "Original labels:\n";
print_r($labels);

echo "\nEncoded labels:\n";
print_r($encoded);

echo "\nEncoding mapping:\n";
print_r($encoder->classes());

// Decode back to original
$decoded = $encoder->inverseTransform($encoded);
echo "\nDecoded labels:\n";
print_r($decoded);
```

### One-Hot Encoding

```php
<?php

use Phpml\Preprocessing\OneHotEncoder;

$samples = [
    ['red', 'S'],
    ['blue', 'M'],
    ['green', 'L'],
    ['blue', 'S'],
];

$encoder = new OneHotEncoder();
$encoded = $encoder->encode($samples);

echo "Original samples:\n";
print_r($samples);

echo "\nOne-hot encoded:\n";
print_r($encoded);

// Each category becomes a binary column
// red=>[1,0,0], blue=>[0,1,0], green=>[0,0,1]
// S=>[1,0,0], M=>[0,1,0], L=>[0,0,1]
```

## Step 4: Feature Extraction from Text (~35 min)

Transform text into numerical features for ML.

### Token Count Vectorization

```php
<?php

use Phpml\FeatureExtraction\TokenCountVectorizer;
use Phpml\Tokenization\WordTokenizer;

$samples = [
    'Machine learning is awesome',
    'PHP ML is machine learning',
    'Learning PHP is fun',
];

$vectorizer = new TokenCountVectorizer(new WordTokenizer());
$vectorizer->fit($samples);
$vectorizer->transform($samples);

echo "Vocabulary:\n";
print_r($vectorizer->getVocabulary());

echo "\nVectorized samples:\n";
print_r($samples);
```

### TF-IDF Transformation

```php
<?php

use Phpml\FeatureExtraction\TfIdfTransformer;

// After tokenization
$samples = [
    [1, 1, 0, 0],  // Document 1 word counts
    [0, 1, 1, 1],  // Document 2 word counts
    [1, 0, 0, 1],  // Document 3 word counts
];

$transformer = new TfIdfTransformer();
$transformer->fit($samples);
$transformer->transform($samples);

echo "TF-IDF transformed:\n";
print_r($samples);
```

## Step 5: Building Preprocessing Pipelines (~25 min)

Combine multiple preprocessing steps.

### Complete Pipeline

```php
<?php

use Phpml\Preprocessing\Normalizer;
use Phpml\Preprocessing\Imputer;
use Phpml\Preprocessing\Imputer\Strategy\MeanStrategy;

class PreprocessingPipeline
{
    private array $steps = [];

    public function addStep(callable $step): self
    {
        $this->steps[] = $step;
        return $this;
    }

    public function process(array &$samples): void
    {
        foreach ($this->steps as $step) {
            $step($samples);
        }
    }
}

// Build pipeline
$pipeline = new PreprocessingPipeline();

$pipeline
    ->addStep(function(&$samples) {
        // Step 1: Handle missing values
        $imputer = new Imputer(null, new MeanStrategy());
        $imputer->fit($samples);
        $imputer->transform($samples);
    })
    ->addStep(function(&$samples) {
        // Step 2: Normalize
        $normalizer = new Normalizer();
        $normalizer->transform($samples);
    });

$samples = [
    [1, 2, null],
    [4, null, 6],
    [7, 8, 9],
];

echo "Original:\n";
print_r($samples);

$pipeline->process($samples);

echo "\nAfter pipeline:\n";
print_r($samples);
```

## Practical Project: Customer Data Preprocessing

Complete preprocessing pipeline for customer analysis:

```php
<?php

// Load raw customer data
$customers = [
    ['age' => 25, 'income' => 50000, 'city' => 'NYC', 'purchases' => null],
    ['age' => null, 'income' => 80000, 'city' => 'LA', 'purchases' => 25],
    ['age' => 45, 'income' => null, 'city' => 'NYC', 'purchases' => 50],
];

// 1. Handle missing values
// 2. Encode categorical (city)
// 3. Normalize numerical features
// 4. Prepare for ML model

// ... implementation
```

## Exercises

### Exercise 1: Missing Data Strategies
Compare mean, median, and most frequent imputation on a dataset.

### Exercise 2: Feature Scaling Comparison
Normalize vs standardize—when to use which?

### Exercise 3: Text Vectorization
Build a document classifier using TF-IDF.

## Wrap-up

- ✓ Master data imputation strategies
- ✓ Apply feature normalization and standardization
- ✓ Encode categorical variables effectively
- ✓ Extract features from text data
- ✓ Build preprocessing pipelines

### What's Next

[Chapter 03: k-Nearest Neighbors Classification](/series/php-ml/chapters/03-k-nearest-neighbors)

<ChapterCheckbox
  seriesId="php-ml"
  chapterId="02"
  label="You've mastered data preprocessing and feature engineering!"
/>
