---
title: "Appendix B: PHP-ML API Reference"
description: "Complete reference for PHP-ML classes, methods, and parameters"
series: "php-ml"
appendix: "B"
---

# Appendix B: PHP-ML API Reference

Quick reference for PHP-ML library classes and methods.

## Classification

### KNearestNeighbors

```php
use Phpml\Classification\KNearestNeighbors;

$classifier = new KNearestNeighbors(
    int $k = 3,
    Phpml\Math\Distance $distanceMetric = new Euclidean()
);

$classifier->train(array $samples, array $labels): void
$prediction = $classifier->predict(array $sample): mixed
$predictions = $classifier->predict(array $samples): array
```

**Parameters:**
- `$k`: Number of nearest neighbors to consider (default: 3)
- `$distanceMetric`: Distance calculation method (Euclidean, Manhattan, Minkowski)

### NaiveBayes

```php
use Phpml\Classification\NaiveBayes;

$classifier = new NaiveBayes();

$classifier->train(array $samples, array $labels): void
$prediction = $classifier->predict(array $sample): mixed
$predictions = $classifier->predict(array $samples): array
```

### SVC (Support Vector Classification)

```php
use Phpml\Classification\SVC;
use Phpml\SupportVectorMachine\Kernel;

$classifier = new SVC(
    int $kernel = Kernel::RBF,
    float $cost = 1.0
);

$classifier->train(array $samples, array $labels): void
$prediction = $classifier->predict(array $sample): mixed
```

**Kernels:**
- `Kernel::LINEAR`
- `Kernel::POLYNOMIAL`
- `Kernel::RBF` (Radial Basis Function, default)
- `Kernel::SIGMOID`

## Regression

### LeastSquares

```php
use Phpml\Regression\LeastSquares;

$regression = new LeastSquares();

$regression->train(array $samples, array $targets): void
$prediction = $regression->predict(array $sample): float
```

### SVR (Support Vector Regression)

```php
use Phpml\Regression\SVR;
use Phpml\SupportVectorMachine\Kernel;

$regression = new SVR(
    int $kernel = Kernel::RBF,
    int $degree = 3,
    float $epsilon = 0.1
);
```

## Clustering

### KMeans

```php
use Phpml\Clustering\KMeans;

$kmeans = new KMeans(
    int $clustersNumber,
    int $initialization = KMeans::INIT_RANDOM
);

$clusters = $kmeans->cluster(array $samples): array
```

### DBSCAN

```php
use Phpml\Clustering\DBSCAN;

$dbscan = new DBSCAN(
    float $epsilon = 0.5,
    int $minSamples = 3,
    Phpml\Math\Distance $distanceMetric = new Euclidean()
);

$clusters = $dbscan->cluster(array $samples): array
```

## Preprocessing

### Normalizer

```php
use Phpml\Preprocessing\Normalizer;

$normalizer = new Normalizer(
    int $norm = Normalizer::NORM_L2
);

$normalizer->transform(array &$samples): void
$normalizer->preprocess(array &$samples): void
```

**Norms:**
- `Normalizer::NORM_L1`
- `Normalizer::NORM_L2` (default)
- `Normalizer::NORM_STD` (standardization)

### Imputer

```php
use Phpml\Preprocessing\Imputer;
use Phpml\Preprocessing\Imputer\Strategy;

$imputer = new Imputer(
    mixed $missingValue = null,
    Strategy $strategy = new Strategy\MeanStrategy()
);

$imputer->fit(array $samples): void
$imputer->transform(array &$samples): void
```

**Strategies:**
- `Strategy\MeanStrategy()`
- `Strategy\MedianStrategy()`
- `Strategy\MostFrequentStrategy()`
- `Strategy\ConstantStrategy($value)`

### LabelEncoder

```php
use Phpml\Preprocessing\LabelEncoder;

$encoder = new LabelEncoder();

$encoder->fit(array $labels): void
$encoded = $encoder->transform(array $labels): array
$decoded = $encoder->inverseTransform(array $encodedLabels): array
$classes = $encoder->classes(): array
```

## Feature Extraction

### TokenCountVectorizer

```php
use Phpml\FeatureExtraction\TokenCountVectorizer;
use Phpml\Tokenization\WordTokenizer;

$vectorizer = new TokenCountVectorizer(
    Tokenizer $tokenizer,
    float $minDF = 0.0
);

$vectorizer->fit(array $samples): void
$vectorizer->transform(array &$samples): void
$vocabulary = $vectorizer->getVocabulary(): array
```

### TfIdfTransformer

```php
use Phpml\FeatureExtraction\TfIdfTransformer;

$transformer = new TfIdfTransformer(array $samples = null);

$transformer->fit(array $samples): void
$transformer->transform(array &$samples): void
```

## Metrics

### Accuracy

```php
use Phpml\Metric\Accuracy;

$score = Accuracy::score(
    array $actualLabels,
    array $predictedLabels,
    bool $normalize = true
): float
```

### ConfusionMatrix

```php
use Phpml\Metric\ConfusionMatrix;

$matrix = ConfusionMatrix::compute(
    array $actualLabels,
    array $predictedLabels,
    array $labels = []
): array
```

### ClassificationReport

```php
use Phpml\Metric\ClassificationReport;

$report = new ClassificationReport(
    array $actualLabels,
    array $predictedLabels
);

echo $report;  // Formatted report
$report->getPrecision(): array
$report->getRecall(): array
$report->getF1score(): array
$report->getSupport(): array
```

## Cross-Validation

### StratifiedRandomSplit

```php
use Phpml\CrossValidation\StratifiedRandomSplit;

$split = new StratifiedRandomSplit(
    array $samples,
    array $labels,
    float $testSize = 0.3,
    int $seed = null
);

$split->getTrainSamples(): array
$split->getTrainLabels(): array
$split->getTestSamples(): array
$split->getTestLabels(): array
```

### RandomSplit

```php
use Phpml\CrossValidation\RandomSplit;

$split = new RandomSplit(
    array $samples,
    array $labels,
    float $testSize = 0.3,
    int $seed = null
);
```

## Model Management

### ModelManager

```php
use Phpml\ModelManager;

$modelManager = new ModelManager();

$modelManager->saveToFile(
    Estimator $estimator,
    string $filepath
): void

$model = $modelManager->restoreFromFile(
    string $filepath
): Estimator
```

## Dataset Loading

### ArrayDataset

```php
use Phpml\Dataset\ArrayDataset;

$dataset = new ArrayDataset(
    array $samples,
    array $targets
);

$dataset->getSamples(): array
$dataset->getTargets(): array
```

### CsvDataset

```php
use Phpml\Dataset\CsvDataset;

$dataset = new CsvDataset(
    string $filepath,
    int $features,
    bool $headingRow = true,
    string $delimiter = ',',
    int $offset = 0
);

$dataset->getSamples(): array
$dataset->getTargets(): array
```

---

**Related:**
- [Appendix A: ML Algorithms Cheat Sheet](/series/php-ml/appendices/a-algorithms-cheat-sheet)
- [PHP-ML Official Documentation](https://php-ml.readthedocs.io/)
