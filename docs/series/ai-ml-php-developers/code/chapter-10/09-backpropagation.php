<?php
declare(strict_types=1);
require_once 'SimpleNeuralNetwork.php';
$network = new SimpleNeuralNetwork(inputSize: 2, hiddenSize: 2, learningRate: 0.8);
$inputs = [1.0, 0.5];
$target = 1.0;
echo "Backpropagation demo\n";
echo "Before training: " . number_format($network->predict($inputs), 4) . "\n";
$loss = $network->train($inputs, $target);
echo "After training: " . number_format($network->predict($inputs), 4) . "\n";
echo "Loss: " . number_format($loss, 6) . "\n";
