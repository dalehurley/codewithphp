<?php

declare(strict_types=1);

require_once 'Perceptron.php';

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║     The XOR Problem: Single Perceptron Limitation        ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

// XOR truth table
$xorData = [
    [[0, 0], 0],
    [[0, 1], 1],
    [[1, 0], 1],
    [[1, 1], 0],
];

echo "XOR Truth Table (target):\n";
echo "  0 XOR 0 = 0\n";
echo "  0 XOR 1 = 1\n";
echo "  1 XOR 0 = 1\n";
echo "  1 XOR 1 = 0\n\n";

echo "Attempting to train single-layer perceptron...\n";
echo str_repeat("-", 60) . "\n";

$perceptron = new Perceptron(inputSize: 2, learningRate: 0.1);

$maxEpochs = 1000;
$converged = false;

for ($epoch = 1; $epoch <= $maxEpochs; $epoch++) {
    $errors = 0;
    
    foreach ($xorData as [$inputs, $target]) {
        $prediction = $perceptron->predict($inputs);
        if ($prediction !== $target) {
            $errors++;
        }
        $perceptron->train($inputs, $target);
    }
    
    if ($epoch % 100 === 0) {
        $accuracy = (1 - $errors / count($xorData)) * 100;
        echo "Epoch {$epoch}: Errors = {$errors}, Accuracy = " . number_format($accuracy, 1) . "%\n";
    }
    
    if ($errors === 0) {
        echo "\n✓ Converged at epoch {$epoch}!\n";
        $converged = true;
        break;
    }
}

if (!$converged) {
    echo "\n✗ FAILED TO CONVERGE after {$maxEpochs} epochs.\n";
}

echo "\nFinal predictions:\n";
echo str_repeat("-", 60) . "\n";

$correctCount = 0;
foreach ($xorData as [$inputs, $expected]) {
    $prediction = $perceptron->predict($inputs);
    $status = $prediction === $expected ? "✓ CORRECT" : "✗ WRONG";
    if ($prediction === $expected) {
        $correctCount++;
    }
    echo "  " . implode(" XOR ", $inputs) . " = {$prediction} (expected: {$expected}) {$status}\n";
}

$finalAccuracy = ($correctCount / count($xorData)) * 100;
echo "\nFinal Accuracy: " . number_format($finalAccuracy, 1) . "%\n";

echo "\n" . str_repeat("=", 60) . "\n";
echo "WHY IT FAILS: XOR is not linearly separable.\n";
echo str_repeat("=", 60) . "\n\n";

echo "Geometric explanation:\n";
echo "  Plot the 4 XOR points in 2D space:\n\n";
echo "    (0,1)=1  ●────●  (1,1)=0\n";
echo "            │ ╲ ╱ │\n";
echo "            │  ╳  │  ← No single line can separate\n";
echo "            │ ╱ ╲ │     1s from 0s!\n";
echo "    (0,0)=0  ●────●  (1,0)=1\n\n";

echo "The 1s (opposite corners) and 0s (other corners) cannot be\n";
echo "separated by any straight line. A perceptron draws a line;\n";
echo "XOR needs a curve or multiple lines.\n\n";

echo "SOLUTION: Add a hidden layer! Multi-layer networks can learn\n";
echo "non-linear decision boundaries. Coming up next...\n";
