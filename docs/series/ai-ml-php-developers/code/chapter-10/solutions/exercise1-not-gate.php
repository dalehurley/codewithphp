<?php

declare(strict_types=1);

require_once __DIR__ . '/../Perceptron.php';

echo "Exercise 1: NOT Gate Perceptron\n";
echo str_repeat("=", 60) . "\n\n";

// NOT gate truth table: single input
$notData = [
    [[0], 1],
    [[1], 0],
];

$perceptron = new Perceptron(inputSize: 1, learningRate: 0.1);

echo "Training NOT gate...\n";

for ($epoch = 1; $epoch <= 20; $epoch++) {
    $errors = 0;
    foreach ($notData as [$inputs, $target]) {
        $prediction = $perceptron->predict($inputs);
        if ($prediction !== $target) {
            $errors++;
        }
        $perceptron->train($inputs, $target);
    }

    if ($errors === 0) {
        echo "Epoch {$epoch}: Converged!\n\n";
        break;
    }
}

echo "Testing:\n";
foreach ($notData as [$inputs, $expected]) {
    $prediction = $perceptron->predict($inputs);
    $status = $prediction === $expected ? "✓" : "✗";
    echo "  NOT " . $inputs[0] . " = {$prediction} (expected: {$expected}) {$status}\n";
}

$params = $perceptron->getParameters();
echo "\nLearned weights: [" . number_format($params['weights'][0], 3) . "]\n";
echo "Learned bias: " . number_format($params['bias'], 3) . "\n";
