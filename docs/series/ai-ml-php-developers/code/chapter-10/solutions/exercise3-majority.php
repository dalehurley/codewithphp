<?php

declare(strict_types=1);

require_once __DIR__ . '/../Perceptron.php';

echo "Exercise 3: Three-Input Majority Classifier\n";
echo str_repeat("=", 60) . "\n\n";

// Majority function: output 1 if >= 2 inputs are 1
$majorityData = [
    [[0, 0, 0], 0],
    [[0, 0, 1], 0],
    [[0, 1, 0], 0],
    [[0, 1, 1], 1],
    [[1, 0, 0], 0],
    [[1, 0, 1], 1],
    [[1, 1, 0], 1],
    [[1, 1, 1], 1],
];

$perceptron = new Perceptron(inputSize: 3, learningRate: 0.1);

echo "Training majority classifier...\n";

for ($epoch = 1; $epoch <= 50; $epoch++) {
    $errors = 0;
    foreach ($majorityData as [$inputs, $target]) {
        $prediction = $perceptron->predict($inputs);
        if ($prediction !== $target) {
            $errors++;
        }
        $perceptron->train($inputs, $target);
    }

    $accuracy = (1 - $errors / count($majorityData)) * 100;

    if ($errors === 0) {
        echo "Converged at epoch {$epoch}!\n\n";
        break;
    }

    if ($epoch % 10 === 0) {
        echo "Epoch {$epoch}: Accuracy = " . number_format($accuracy, 1) . "%\n";
    }
}

echo "Testing all 8 inputs:\n";
echo str_repeat("-", 60) . "\n";

$correct = 0;
foreach ($majorityData as [$inputs, $expected]) {
    $prediction = $perceptron->predict($inputs);
    $status = $prediction === $expected ? "✓" : "✗";
    echo "  " . implode(",", $inputs) . " → {$prediction} (expected: {$expected}) {$status}\n";
    if ($prediction === $expected) {
        $correct++;
    }
}

$finalAccuracy = ($correct / count($majorityData)) * 100;
echo "\nFinal Accuracy: " . number_format($finalAccuracy, 1) . "%\n";
