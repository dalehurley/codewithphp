<?php
declare(strict_types=1);
require_once 'SimpleNeuralNetwork.php';
echo "Forward propagation demo - see 10-xor-mlp-solution.php for complete example\n";
$network = new SimpleNeuralNetwork(inputSize: 2, hiddenSize: 2, learningRate: 0.5);
echo "Network created: 2 → 2 → 1\n";
