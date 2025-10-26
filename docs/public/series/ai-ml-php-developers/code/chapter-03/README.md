# Chapter 3 Code Examples

Core Machine Learning Concepts and Terminology - Working PHP Examples

## Prerequisites

- PHP 8.4+ installed
- Composer installed
- PHP-ML and/or Rubix ML libraries (installed in chapter-02)

## Setup

Some examples use the autoloader from Chapter 2. If you haven't already set up Chapter 2:

```bash
# Navigate to chapter-02 directory
cd ../chapter-02

# Install dependencies (PHP-ML and Rubix ML)
composer install

# Return to chapter-03
cd ../chapter-03
```

**Note**: Examples 01-03 use PHP-ML. Examples 07-08 use Rubix ML. If you only want to run examples 01-06, PHP-ML is sufficient.

## Examples Overview

### 01-supervised-classification.php

**Concept**: Supervised Learning

- Demonstrates email spam classification
- Shows labeled training data
- Trains a k-NN classifier
- Makes predictions on new emails
- **Run**: `php 01-supervised-classification.php`
- **Time**: ~5 seconds

### 02-unsupervised-clustering.php

**Concept**: Unsupervised Learning

- Customer segmentation using k-Means clustering
- No labels provided to algorithm
- Discovers natural groupings in data
- Interprets business meaning of clusters
- **Run**: `php 02-unsupervised-clustering.php`
- **Time**: ~5 seconds

### 03-feature-extraction.php

**Concept**: Feature Engineering

- Extracts numeric features from text
- Creates derived features from raw data
- Demonstrates min-max normalization
- Shows importance of feature scaling
- **Run**: `php 03-feature-extraction.php`
- **Time**: ~2 seconds

### 04-training-inference.php

**Concept**: Training vs. Inference

- Compares two phases of ML lifecycle
- Measures training time vs inference time
- Shows Euclidean distance calculation
- Explains how k-NN makes predictions
- **Run**: `php 04-training-inference.php`
- **Time**: ~5 seconds

### 05-overfitting-demo.php

**Concept**: Overfitting vs. Generalization

- Demonstrates intentional overfitting
- Compares small vs. large training sets
- Shows train vs. test accuracy gap
- Provides prevention techniques
- **Run**: `php 05-overfitting-demo.php`
- **Time**: ~5 seconds

### 06-train-test-split.php

**Concept**: Proper Data Splitting

- Implements train/test split function
- Compares different split ratios
- Explains best practices
- Lists common mistakes to avoid
- **Run**: `php 06-train-test-split.php`
- **Time**: ~8 seconds

### 07-iris-workflow.php ⭐ **Main Project**

**Concept**: Complete ML Workflow

- End-to-end iris flower classifier
- Covers all 8 steps: define → deploy
- Loads real dataset from CSV
- Trains, evaluates, and saves model
- Demonstrates model persistence
- **Run**: `php 07-iris-workflow.php`
- **Time**: ~10 seconds

### 08-algorithm-comparison.php

**Concept**: Comparing Algorithms

- Tests 4 different algorithms on same data
- Compares accuracy, speed, and characteristics
- Provides use case recommendations
- Explains pros/cons of each approach
- **Run**: `php 08-algorithm-comparison.php`
- **Time**: ~15 seconds

## Data Files

### data/iris.csv

Classic machine learning dataset with 150 iris flower samples:

- **Features**: sepal length, sepal width, petal length, petal width (in cm)
- **Labels**: Iris-setosa, Iris-versicolor, Iris-virginica
- **Size**: 150 samples, 4 features, 3 classes
- **Balance**: 50 samples per class (perfectly balanced)

## Models Directory

The `models/` directory will be created automatically when you run `07-iris-workflow.php`. Trained models are saved here as `.rbx` files (Rubix ML format).

## Running All Examples

To run all examples in sequence:

```bash
for file in 0*.php; do
    echo "Running $file..."
    php "$file"
    echo ""
    echo "Press Enter to continue..."
    read
done
```

Or individually:

```bash
php 01-supervised-classification.php
php 02-unsupervised-clustering.php
php 03-feature-extraction.php
php 04-training-inference.php
php 05-overfitting-demo.php
php 06-train-test-split.php
php 07-iris-workflow.php
php 08-algorithm-comparison.php
```

## Troubleshooting

### Error: "Class not found"

**Problem**: PHP-ML or Rubix ML not installed
**Solution**:

```bash
cd ../chapter-02
composer install
```

### Error: "Iris dataset not found"

**Problem**: Running from wrong directory or file missing
**Solution**:

```bash
# Ensure you're in chapter-03 directory
pwd  # Should show: .../code/chapter-03

# Verify iris.csv exists
ls data/iris.csv
```

### Error: "Cannot create directory models"

**Problem**: Permission issues
**Solution**:

```bash
chmod 755 .
mkdir -p models
chmod 755 models
```

### Low accuracy or strange results

**Problem**: Random data split may produce different results each run
**Solution**: This is normal! ML results vary due to random train/test splits. Run multiple times to see typical range.

## Expected Output

Each script produces formatted output with:

- Clear section headers
- Step-by-step explanations
- Numeric results (accuracy, time, etc.)
- Visual indicators (✓, ✗, →, etc.)
- Key takeaways and lessons

Example output from `07-iris-workflow.php`:

```
╔══════════════════════════════════════════════════════════╗
║   Complete ML Workflow: Iris Flower Classification       ║
╚══════════════════════════════════════════════════════════╝

STEP 1: Define the Problem
------------------------------------------------------------
Goal: Classify iris flowers into 3 species based on measurements
  - Input: 4 numeric features (sepal/petal length & width)
  - Output: Species (Iris-setosa, Iris-versicolor, Iris-virginica)
  ...

✓ Model trained in 0.045 seconds
Test Accuracy: 96.67%
🎉 SUCCESS! Model achieves target accuracy (>90%)
```

## Learning Path

Recommended order for understanding concepts:

1. **Start with supervised/unsupervised**: `01` and `02` to understand learning paradigms
2. **Feature engineering**: `03` to see how raw data becomes ML-ready
3. **Training vs inference**: `04` to understand the two phases
4. **Overfitting**: `05` to learn the most common mistake
5. **Data splitting**: `06` to see proper evaluation
6. **Complete workflow**: `07` to tie everything together
7. **Algorithm comparison**: `08` to understand trade-offs

## Key Concepts Covered

- ✓ Supervised vs. unsupervised learning
- ✓ Features and labels
- ✓ Training vs. inference
- ✓ Overfitting vs. generalization
- ✓ Train/test splitting
- ✓ Feature normalization
- ✓ Model evaluation (accuracy metric)
- ✓ Model persistence (save/load)
- ✓ Algorithm comparison
- ✓ Complete ML workflow

## Further Experimentation

Try modifying the examples:

1. **Change k values**: In k-NN examples, try k=1, k=3, k=5, k=10
2. **Adjust split ratios**: Try 60/40, 70/30, 90/10 splits
3. **Add features**: Create new derived features in feature extraction
4. **Different data**: Replace iris data with your own CSV
5. **More algorithms**: Add other Rubix ML classifiers to comparison

## Next Steps

After completing these examples, proceed to Chapter 4: Data Collection and Preprocessing in PHP, where you'll learn advanced data handling techniques.

## Support

If you encounter issues:

1. Verify PHP version: `php --version` (should be 8.4+)
2. Check libraries: `composer show | grep -E "php-ai|rubix"`
3. Review Chapter 2 setup if autoloader fails
4. Ensure you're in the correct directory
