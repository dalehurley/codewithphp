#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Test script for Rubix ML library
 *
 * This script demonstrates basic Rubix ML functionality using the
 * classic Iris flower dataset for classification.
 *
 * Demonstrates PHP 8.4 features:
 * - Array destructuring in foreach loops
 * - Performance timing with microtime
 * - Enhanced error handling
 * - Better data validation
 */

require __DIR__ . '/vendor/autoload.php';

use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Classifiers\KNearestNeighbors;
use Rubix\ML\Kernels\Distance\Euclidean;
use Rubix\ML\Exception\InvalidArgumentException;

echo "\n🧪 Testing Rubix ML Library\n";
echo "============================\n\n";

try {
    // Typed training data: Iris flower measurements
    // [sepal length, sepal width, petal length, petal width] -> species
    $samples = [
        [5.1, 3.5, 1.4, 0.2],  // Iris setosa
        [4.9, 3.0, 1.4, 0.2],  // Iris setosa
        [4.7, 3.2, 1.3, 0.2],  // Iris setosa
        [7.0, 3.2, 4.7, 1.4],  // Iris versicolor
        [6.4, 3.2, 4.5, 1.5],  // Iris versicolor
        [6.9, 3.1, 4.9, 1.5],  // Iris versicolor
        [6.3, 3.3, 6.0, 2.5],  // Iris virginica
        [5.8, 2.7, 5.1, 1.9],  // Iris virginica
        [7.1, 3.0, 5.9, 2.1],  // Iris virginica
    ];

    $labels = [
        'setosa',
        'setosa',
        'setosa',
        'versicolor',
        'versicolor',
        'versicolor',
        'virginica',
        'virginica',
        'virginica'
    ];

    // Validate data consistency
    if (count($samples) !== count($labels)) {
        throw new InvalidArgumentException('Number of samples must match number of labels');
    }

    // Display dataset statistics
    $uniqueLabels = array_unique($labels);
    echo "Training data: Iris flower dataset\n";
    echo "  Features: sepal length, sepal width, petal length, petal width (cm)\n";
    echo "  Species: " . implode(', ', $uniqueLabels) . "\n";
    echo "  Samples: " . count($samples) . " flowers\n";
    echo "  Classes: " . count($uniqueLabels) . " species\n\n";

    // Performance timing
    $startTime = microtime(true);

    // Create a labeled dataset (Rubix ML's structured data format)
    $dataset = new Labeled($samples, $labels);

    // Train a K-Nearest Neighbors classifier with Euclidean distance
    echo "Training K-Nearest Neighbors classifier (k=3, Euclidean distance)...\n";
    $estimator = new KNearestNeighbors(3, false, new Euclidean());
    $estimator->train($dataset);

    $trainingTime = microtime(true) - $startTime;
    echo "✅ Training complete in " . round($trainingTime * 1000, 2) . "ms\n\n";

    // Test predictions
    $testCases = [
        [[5.0, 3.4, 1.5, 0.2], 'small petals, likely setosa'],
        [[6.5, 3.0, 4.8, 1.5], 'medium petals, likely versicolor'],
        [[6.5, 3.0, 5.8, 2.2], 'large petals, likely virginica'],
    ];

    echo "Making predictions:\n";
    echo "-------------------\n";

    foreach ($testCases as [$features, $description]) {
        $prediction = $estimator->predictSample($features);
        echo sprintf(
            "  Sample [%.1f, %.1f, %.1f, %.1f] (%s)\n",
            $features[0],
            $features[1],
            $features[2],
            $features[3],
            $description
        );
        echo "  → Predicted species: $prediction\n\n";
    }

    // Check if Tensor extension is loaded (performance boost)
    echo "Performance check:\n";
    echo "------------------\n";
    if (class_exists('Tensor\Matrix')) {
        echo "  ✅ Rubix Tensor extension is loaded\n";
        echo "     (Mathematical operations are optimized)\n";
    } else {
        echo "  ⚠️  Rubix Tensor extension not found\n";
        echo "     (Using pure PHP fallback - slower but functional)\n";
        echo "     Install with: composer require rubix/tensor\n";
    }

    echo "\n✅ Rubix ML is working correctly!\n";
    echo "\nWhat this demonstrates:\n";
    echo "  • Loading and using Rubix ML classes with proper imports\n";
    echo "  • Creating labeled datasets with validation\n";
    echo "  • Training with distance kernels (Euclidean)\n";
    echo "  • Making predictions on new samples\n";
    echo "  • Performance monitoring and timing\n";
    echo "  • Checking for performance extensions (Tensor)\n";
    echo "  • PHP 8.4 features: array destructuring, microtime precision\n";
    echo "  • Proper error handling and data validation\n\n";
} catch (InvalidArgumentException $e) {
    echo "❌ Data validation error: {$e->getMessage()}\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Unexpected error: {$e->getMessage()}\n";
    echo "   File: {$e->getFile()}:{$e->getLine()}\n";
    exit(1);
}
