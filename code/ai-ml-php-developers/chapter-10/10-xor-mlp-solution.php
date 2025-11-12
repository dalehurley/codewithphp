<?php

declare(strict_types=1);

require_once 'SimpleNeuralNetwork.php';

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║         Solving XOR with Multi-Layer Network             ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

// XOR dataset
$xorData = [
    [[0, 0], 0],
    [[0, 1], 1],
    [[1, 0], 1],
    [[1, 1], 0],
];

echo "Target: Learn the XOR function\n";
echo "  0 XOR 0 = 0\n";
echo "  0 XOR 1 = 1\n";
echo "  1 XOR 0 = 1\n";
echo "  1 XOR 1 = 0\n\n";

// Create MLP with hidden layer
$network = new SimpleNeuralNetwork(
    inputSize: 2,
    hiddenSize: 4,  // 4 hidden neurons for good measure
    learningRate: 0.5
);

echo "Network: 2 inputs → 4 hidden neurons → 1 output\n";
echo "Training for 5000 epochs...\n\n";

$epochs = 5000;
$printInterval = 500;

for ($epoch = 1; $epoch <= $epochs; $epoch++) {
    $totalLoss = 0;
    $correctCount = 0;
    
    foreach ($xorData as [$inputs, $target]) {
        $loss = $network->train($inputs, (float)$target);
        $totalLoss += $loss;
        
        // Check accuracy
        $prediction = $network->predictBinary($inputs);
        if ($prediction === $target) {
            $correctCount++;
        }
    }
    
    $avgLoss = $totalLoss / count($xorData);
    $accuracy = ($correctCount / count($xorData)) * 100;
    
    if ($epoch % $printInterval === 0 || $accuracy === 100.0) {
        echo "Epoch {$epoch}: Loss = " . number_format($avgLoss, 6) . ", Accuracy = " . number_format($accuracy, 1) . "%\n";
        
        if ($accuracy === 100.0 && $epoch < $epochs) {
            echo "\n✓ Perfect accuracy achieved! Stopping early.\n";
            break;
        }
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "FINAL TEST:\n";
echo str_repeat("=", 60) . "\n\n";

$allCorrect = true;
foreach ($xorData as [$inputs, $expected]) {
    $output = $network->predict($inputs);
    $prediction = $network->predictBinary($inputs);
    $status = $prediction === $expected ? "✓" : "✗";
    
    echo "  " . implode(" XOR ", $inputs) . " = {$prediction} ";
    echo "(output: " . number_format($output, 4) . ", expected: {$expected}) {$status}\n";
    
    if ($prediction !== $expected) {
        $allCorrect = false;
    }
}

if ($allCorrect) {
    echo "\n🎉 SUCCESS! MLP solved XOR—something a perceptron cannot do!\n";
} else {
    echo "\n⚠ Not quite there yet. Try more epochs or different learning rate.\n";
}

echo "\nWHY IT WORKS:\n";
echo "  Hidden neurons learned to detect intermediate patterns:\n";
echo "    - Neuron 1 might detect: 'is first input on?'\n";
echo "    - Neuron 2 might detect: 'is second input on?'\n";
echo "    - Neuron 3 might detect: 'are both inputs same?'\n";
echo "  Output neuron combines these features linearly to compute XOR.\n";
echo "  The hidden layer transformed the problem into a linearly\n";
echo "  separable one!\n";
