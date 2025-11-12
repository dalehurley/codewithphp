<?php
declare(strict_types=1);
require_once 'SimpleNeuralNetwork.php';
$network = new SimpleNeuralNetwork(inputSize: 2, hiddenSize: 3, learningRate: 0.5);
echo "Testing MLP with 2 inputs, 3 hidden neurons, 1 output\n";
echo "Input [1, 0] → Output: " . number_format($network->predict([1, 0]), 4) . "\n";
echo "Input [0, 1] → Output: " . number_format($network->predict([0, 1]), 4) . "\n";
