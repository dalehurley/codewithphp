<?php

declare(strict_types=1);

require_once 'Perceptron.php';

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║         Perceptron Forward Propagation Test              ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

// Create perceptron with 2 inputs
$perceptron = new Perceptron(inputSize: 2);

$params = $perceptron->getParameters();
echo "Initial parameters:\n";
echo "  Weights: [" . implode(", ", array_map(fn($w) => number_format($w, 3), $params['weights'])) . "]\n";
echo "  Bias: " . number_format($params['bias'], 3) . "\n\n";

// Test predictions on various inputs
echo "Testing forward propagation:\n";
echo str_repeat("-", 60) . "\n";

$testInputs = [
    [0, 0],
    [0, 1],
    [1, 0],
    [1, 1],
];

foreach ($testInputs as $inputs) {
    $prediction = $perceptron->predict($inputs);
    echo "Input: [" . implode(", ", $inputs) . "] → Prediction: {$prediction}\n";
}

echo "\n" . str_repeat("-", 60) . "\n";
echo "NOTE: Predictions are random because weights are random.\n";
echo "Training (next step) will adjust weights to learn patterns!\n";
