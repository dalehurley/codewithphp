<?php

declare(strict_types=1);

/**
 * Example 12: Confusion Matrix for Classification Evaluation
 * 
 * Goes beyond simple accuracy to understand which classes are confused.
 * Introduces precision, recall, and F1-score concepts.
 */

require __DIR__ . '/vendor/autoload.php';

use Phpml\Classification\KNearestNeighbors;

echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║         Confusion Matrix and Advanced Metrics            ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

// Load iris dataset
$csvPath = __DIR__ . '/data/iris.csv';
$file = fopen($csvPath, 'r');
$header = fgetcsv($file, 0, ",", "\"", "\\");

$samples = [];
$labels = [];

while (($row = fgetcsv($file, 0, ",", "\"", "\\")) !== false) {
    // Skip empty rows or rows with missing labels
    if (empty($row[4]) || !isset($row[4])) {
        continue;
    }
    $samples[] = [(float) $row[0], (float) $row[1], (float) $row[2], (float) $row[3]];
    $labels[] = $row[4];
}

fclose($file);

// Get unique classes (filter out any empty values)
$classes = array_values(array_unique(array_filter($labels, fn($label) => !empty($label))));
sort($classes);

echo "Dataset: " . count($samples) . " iris flowers\n";
echo "Classes: " . implode(', ', $classes) . "\n\n";

// Train/test split
$indices = range(0, count($samples) - 1);
shuffle($indices);

$trainSize = (int) round(count($samples) * 0.7);

$trainSamples = [];
$trainLabels = [];
$testSamples = [];
$testLabels = [];

for ($i = 0; $i < count($indices); $i++) {
    $idx = $indices[$i];

    if ($i < $trainSize) {
        $trainSamples[] = $samples[$idx];
        $trainLabels[] = $labels[$idx];
    } else {
        $testSamples[] = $samples[$idx];
        $testLabels[] = $labels[$idx];
    }
}

echo "Training set: " . count($trainSamples) . " samples\n";
echo "Test set: " . count($testSamples) . " samples\n\n";

// Train classifier
$classifier = new KNearestNeighbors(k: 5);
$classifier->train($trainSamples, $trainLabels);

// Make predictions
$predictions = $classifier->predict($testSamples);

// ============================================================
// BUILD CONFUSION MATRIX
// ============================================================

echo "============================================================\n";
echo "CONFUSION MATRIX\n";
echo "============================================================\n\n";

echo "What is a Confusion Matrix?\n";
echo "  A table showing predicted vs. actual classifications.\n";
echo "  Rows = Actual class, Columns = Predicted class\n";
echo "  Diagonal = Correct predictions, Off-diagonal = Errors\n\n";

/**
 * Build confusion matrix
 * 
 * @param array $predictions Predicted labels
 * @param array $actuals Actual labels
 * @param array $classes List of all class names
 * @return array 2D array [actual][predicted] = count
 */
function buildConfusionMatrix(array $predictions, array $actuals, array $classes): array
{
    $matrix = [];

    // Initialize matrix with zeros
    foreach ($classes as $actualClass) {
        foreach ($classes as $predictedClass) {
            $matrix[$actualClass][$predictedClass] = 0;
        }
    }

    // Populate matrix
    for ($i = 0; $i < count($predictions); $i++) {
        $actual = $actuals[$i];
        $predicted = $predictions[$i];
        $matrix[$actual][$predicted]++;
    }

    return $matrix;
}

/**
 * Print formatted confusion matrix
 */
function printConfusionMatrix(array $matrix, array $classes): void
{
    // Calculate column widths
    $classWidth = max(array_map('strlen', array_filter($classes, fn($c) => $c !== null && $c !== '')));
    $cellWidth = 6;

    // Header
    echo str_repeat(' ', $classWidth + 2) . "│ PREDICTED\n";
    echo str_repeat(' ', $classWidth + 2) . "│ ";
    foreach ($classes as $class) {
        $shortClass = substr((string)$class, 0, $cellWidth);
        echo str_pad($shortClass, $cellWidth + 1);
    }
    echo "\n";

    echo str_repeat('─', $classWidth + 2) . "┼" . str_repeat('─', ($cellWidth + 1) * count($classes) + 1) . "\n";

    // Rows
    $isFirst = true;
    foreach ($classes as $actual) {
        if ($isFirst) {
            echo "ACTUAL ";
            $isFirst = false;
        } else {
            echo "       ";
        }

        echo str_pad($actual, $classWidth - 7) . " │ ";

        foreach ($classes as $predicted) {
            $count = $matrix[$actual][$predicted];

            // Highlight diagonal (correct predictions)
            if ($actual === $predicted) {
                echo "\033[32m" . str_pad((string) $count, $cellWidth) . "\033[0m ";
            } else {
                echo str_pad((string) $count, $cellWidth) . " ";
            }
        }
        echo "\n";
    }

    echo "\n";
    echo "\033[32m■\033[0m Green = Correct predictions (diagonal)\n";
    echo "  Other cells = Misclassifications\n";
}

$confusionMatrix = buildConfusionMatrix($predictions, $testLabels, $classes);

printConfusionMatrix($confusionMatrix, $classes);

echo "\n";

// ============================================================
// ANALYZE CONFUSION MATRIX
// ============================================================

echo "============================================================\n";
echo "ANALYSIS: What the Confusion Matrix Reveals\n";
echo "============================================================\n\n";

$totalCorrect = 0;
$totalSamples = count($testSamples);

foreach ($classes as $class) {
    $totalCorrect += $confusionMatrix[$class][$class];
}

$accuracy = ($totalCorrect / $totalSamples) * 100;

echo "Overall Accuracy: " . number_format($accuracy, 2) . "% ";
echo "({$totalCorrect} / {$totalSamples} correct)\n\n";

echo "Per-Class Analysis:\n";
echo str_repeat('-', 60) . "\n";

foreach ($classes as $class) {
    $shortClass = substr($class, -10);  // Last 10 chars

    // True Positives: Correctly predicted as this class
    $tp = $confusionMatrix[$class][$class];

    // False Negatives: Actually this class but predicted as something else
    $fn = 0;
    foreach ($classes as $predicted) {
        if ($predicted !== $class) {
            $fn += $confusionMatrix[$class][$predicted];
        }
    }

    // False Positives: Not this class but predicted as this class
    $fp = 0;
    foreach ($classes as $actual) {
        if ($actual !== $class) {
            $fp += $confusionMatrix[$actual][$class];
        }
    }

    // True Negatives: Not this class and correctly predicted as not this class
    $tn = $totalSamples - $tp - $fp - $fn;

    echo "\nClass: {$class}\n";
    echo "  True Positives (TP):  {$tp} (correctly identified as {$shortClass})\n";
    echo "  False Negatives (FN): {$fn} (missed {$shortClass}s, labeled as other)\n";
    echo "  False Positives (FP): {$fp} (incorrectly labeled as {$shortClass})\n";
    echo "  True Negatives (TN):  {$tn} (correctly identified as not {$shortClass})\n";

    // Calculate metrics
    $precision = ($tp + $fp) > 0 ? $tp / ($tp + $fp) : 0;
    $recall = ($tp + $fn) > 0 ? $tp / ($tp + $fn) : 0;
    $f1 = ($precision + $recall) > 0 ? 2 * ($precision * $recall) / ($precision + $recall) : 0;

    echo "\n  Precision: " . number_format($precision, 3);
    echo " (Of all predicted {$shortClass}, " . number_format($precision * 100, 1) . "% were correct)\n";

    echo "  Recall:    " . number_format($recall, 3);
    echo " (Of all actual {$shortClass}, " . number_format($recall * 100, 1) . "% were found)\n";

    echo "  F1-Score:  " . number_format($f1, 3);
    echo " (Harmonic mean of precision and recall)\n";
}

echo "\n";

// ============================================================
// KEY METRICS EXPLAINED
// ============================================================

echo "============================================================\n";
echo "KEY METRICS EXPLAINED\n";
echo "============================================================\n\n";

echo "ACCURACY:\n";
echo "  Formula: (TP + TN) / Total\n";
echo "  Meaning: Percentage of all predictions that are correct\n";
echo "  Problem: Can be misleading with imbalanced classes\n";
echo "  Example: 95% accuracy sounds great, but if 95% of data is one class,\n";
echo "           a model that always predicts that class gets 95% accuracy!\n\n";

echo "PRECISION (Positive Predictive Value):\n";
echo "  Formula: TP / (TP + FP)\n";
echo "  Meaning: Of all items predicted as positive, how many are actually positive?\n";
echo "  Question: \"When the model says YES, how often is it right?\"\n";
echo "  Use case: Spam filter (don't want to mark ham as spam)\n\n";

echo "RECALL (Sensitivity, True Positive Rate):\n";
echo "  Formula: TP / (TP + FN)\n";
echo "  Meaning: Of all actual positives, how many did we find?\n";
echo "  Question: \"How many of the actual positives did we catch?\"\n";
echo "  Use case: Cancer detection (don't want to miss any cases)\n\n";

echo "F1-SCORE:\n";
echo "  Formula: 2 × (Precision × Recall) / (Precision + Recall)\n";
echo "  Meaning: Harmonic mean of precision and recall\n";
echo "  Balances precision and recall into a single metric\n";
echo "  Range: 0 (worst) to 1 (perfect)\n\n";

// ============================================================
// FINDING MISCLASSIFICATIONS
// ============================================================

echo "============================================================\n";
echo "COMMON MISCLASSIFICATIONS\n";
echo "============================================================\n\n";

echo "Which classes are most often confused?\n\n";

$confusions = [];

foreach ($classes as $actual) {
    foreach ($classes as $predicted) {
        if ($actual !== $predicted && $confusionMatrix[$actual][$predicted] > 0) {
            $confusions[] = [
                'actual' => $actual,
                'predicted' => $predicted,
                'count' => $confusionMatrix[$actual][$predicted],
            ];
        }
    }
}

if (empty($confusions)) {
    echo "✓ No misclassifications! Perfect accuracy!\n\n";
} else {
    usort($confusions, fn($a, $b) => $b['count'] <=> $a['count']);

    foreach ($confusions as $confusion) {
        echo "  • {$confusion['actual']} misclassified as {$confusion['predicted']}: ";
        echo "{$confusion['count']} time(s)\n";
    }

    echo "\n";
    echo "💡 Why This Matters:\n";
    echo "   Knowing which classes are confused helps improve the model:\n";
    echo "   - Add more training examples of confused classes\n";
    echo "   - Engineer features that better distinguish them\n";
    echo "   - Use different algorithm that handles those features better\n\n";
}

// ============================================================
// SUMMARY
// ============================================================

echo "============================================================\n";
echo "SUMMARY: Beyond Accuracy\n";
echo "============================================================\n\n";

echo "Why use confusion matrix and advanced metrics?\n\n";

echo "1. Accuracy alone can be misleading:\n";
echo "   - Doesn't show which classes are problematic\n";
echo "   - Fails with imbalanced datasets\n\n";

echo "2. Confusion matrix reveals:\n";
echo "   - Which classes are confused with each other\n";
echo "   - Whether errors are systematic or random\n";
echo "   - Class-specific performance issues\n\n";

echo "3. Precision vs. Recall trade-off:\n";
echo "   - HIGH PRECISION: Few false positives (strict model)\n";
echo "   - HIGH RECALL: Few false negatives (lenient model)\n";
echo "   - Can't maximize both simultaneously\n";
echo "   - Choose based on cost of each error type\n\n";

echo "Real-world examples:\n";
echo "  Medical diagnosis: Prefer high RECALL (don't miss diseases)\n";
echo "  Spam filter: Prefer high PRECISION (don't block real emails)\n";
echo "  Fraud detection: Balance both (F1-score)\n\n";

echo "🎉 Confusion matrix analysis complete!\n";
echo "   You now have tools to understand classification performance deeply.\n";
