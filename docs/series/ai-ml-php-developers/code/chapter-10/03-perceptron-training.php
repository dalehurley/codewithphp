<?php

declare(strict_types=1);

require_once 'Perceptron.php';

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║         Training a Perceptron: Learning Rule             ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

// Simple dataset: classify points above or below the line y = x
// Points where x > y should be class 1, otherwise class 0
$trainingData = [
    [[2, 1], 1],  // 2 > 1: class 1
    [[1, 2], 0],  // 1 < 2: class 0
    [[3, 2], 1],  // 3 > 2: class 1
    [[2, 3], 0],  // 2 < 3: class 0
    [[4, 1], 1],  // 4 > 1: class 1
    [[1, 4], 0],  // 1 < 4: class 0
];

$perceptron = new Perceptron(inputSize: 2, learningRate: 0.1);

echo "Learning task: Classify points as above (1) or below (0) the line y = x\n";
echo "Training on " . count($trainingData) . " examples\n\n";

// Training loop
$maxEpochs = 20;
$converged = false;

for ($epoch = 1; $epoch <= $maxEpochs; $epoch++) {
    $errors = 0;

    foreach ($trainingData as [$inputs, $target]) {
        $prediction = $perceptron->predict($inputs);

        if ($prediction !== $target) {
            $errors++;
            $perceptron->train($inputs, $target);
        }
    }

    $accuracy = (1 - $errors / count($trainingData)) * 100;
    echo "Epoch {$epoch}: Errors = {$errors}, Accuracy = " . number_format($accuracy, 1) . "%\n";

    if ($errors === 0) {
        echo "\n✓ Converged! Perceptron learned the pattern perfectly.\n";
        $converged = true;
        break;
    }
}

if (!$converged) {
    echo "\n⚠ Did not converge in {$maxEpochs} epochs.\n";
}

// Test learned model
echo "\nTesting learned model:\n";
echo str_repeat("-", 60) . "\n";

$testData = [
    [[5, 2], 1],
    [[2, 5], 0],
    [[3, 3], 0],  // Edge case: on the line
];

foreach ($testData as [$inputs, $expected]) {
    $prediction = $perceptron->predict($inputs);
    $status = $prediction === $expected ? "✓" : "✗";
    echo "Input: [" . implode(", ", $inputs) . "] → Predicted: {$prediction}, Expected: {$expected} {$status}\n";
}

$params = $perceptron->getParameters();
echo "\nFinal learned parameters:\n";
echo "  Weights: [" . implode(", ", array_map(fn($w) => number_format($w, 3), $params['weights'])) . "]\n";
echo "  Bias: " . number_format($params['bias'], 3) . "\n";
