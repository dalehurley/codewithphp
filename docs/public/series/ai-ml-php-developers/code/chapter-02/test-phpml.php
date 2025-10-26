#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Test script for PHP-ML library
 *
 * This script demonstrates basic PHP-ML functionality with a simple
 * k-nearest neighbors classifier for height/weight classification.
 *
 * Demonstrates PHP 8.4 features:
 * - Named arguments in constructor
 * - Array destructuring in foreach loops
 * - Match expressions for classification
 * - Better error handling with specific exceptions
 */

require __DIR__ . '/vendor/autoload.php';

use Phpml\Classification\KNearestNeighbors;
use Phpml\Exception\InvalidArgumentException;

echo "\n🧪 Testing PHP-ML Library\n";
echo "==========================\n\n";

try {
    // Typed training data with better structure
    /** @var array<array{float, float}> $samples */
    $samples = [
        [180, 75],  // tall person
        [182, 80],  // tall person
        [175, 70],  // tall person
        [178, 73],  // tall person
        [160, 55],  // short person
        [155, 50],  // short person
        [158, 52],  // short person
        [162, 57],  // short person
    ];

    /** @var array<string> $labels */
    $labels = ['tall', 'tall', 'tall', 'tall', 'short', 'short', 'short', 'short'];

    // Validate data consistency
    if (count($samples) !== count($labels)) {
        throw new InvalidArgumentException('Number of samples must match number of labels');
    }

    // Display training data summary
    echo "Training data summary:\n";
    echo "  Sample size: " . count($samples) . " people\n";
    echo "  Features: height (cm), weight (kg)\n";
    echo "  Classes: " . implode(', ', array_unique($labels)) . "\n\n";

    // Create and train a k-nearest neighbors classifier (PHP 8.4 named arguments)
    echo "Training K-Nearest Neighbors classifier (k=3)...\n";
    $classifier = new KNearestNeighbors(k: 3);
    $classifier->train($samples, $labels);
    echo "✅ Training complete\n\n";

    // Test predictions with enhanced output
    $testCases = [
        [170, 65, 'medium height person'],
        [185, 85, 'very tall person'],
        [150, 48, 'very short person'],
        [177, 72, 'tall person'],
    ];

    echo "Making predictions:\n";
    echo "-------------------\n";

    foreach ($testCases as [$height, $weight, $description]) {
        $prediction = $classifier->predict([$height, $weight]);
        $confidence = match ($prediction) {
            'tall' => 'likely tall',
            'short' => 'likely short',
            default => 'uncertain'
        };

        echo sprintf(
            "  %s (%d cm, %d kg) → Predicted: %s (%s)\n",
            ucfirst($description),
            $height,
            $weight,
            $prediction,
            $confidence
        );
    }

    echo "\n✅ PHP-ML is working correctly!\n";
    echo "\nWhat this demonstrates:\n";
    echo "  • Loading and using PHP-ML classes with proper imports\n";
    echo "  • Training a simple classifier with validated data\n";
    echo "  • Making predictions on new data\n";
    echo "  • K-Nearest Neighbors algorithm\n";
    echo "  • PHP 8.4 features: named arguments, typed arrays, match expressions\n";
    echo "  • Proper error handling and data validation\n\n";
} catch (InvalidArgumentException $e) {
    echo "❌ Data validation error: {$e->getMessage()}\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Unexpected error: {$e->getMessage()}\n";
    echo "   File: {$e->getFile()}:{$e->getLine()}\n";
    exit(1);
}
