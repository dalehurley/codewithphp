<?php
declare(strict_types=1);
require_once 'SimpleNeuralNetwork.php';
echo "Learning rate comparison\n";
$rates = [0.1, 0.5, 1.0];
foreach ($rates as $lr) {
    echo "Testing learning rate: $lr\n";
}
