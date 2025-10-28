<?php

declare(strict_types=1);

require_once __DIR__ . '/../ActivationFunctions.php';

echo "Exercise 2: Tanh Activation Implementation\n";
echo str_repeat("=", 60) . "\n\n";

echo "Testing tanh activation function:\n\n";

$testValues = [-2, -1, 0, 1, 2];

echo "Input | tanh(z) | tanh'(z)\n";
echo str_repeat("-", 40) . "\n";

foreach ($testValues as $z) {
    $tanhVal = ActivationFunctions::tanh($z);
    $tanhDeriv = ActivationFunctions::tanhDerivative($tanhVal);

    printf("%5d | %7.3f | %8.3f\n", $z, $tanhVal, $tanhDeriv);
}

echo "\nComparison with sigmoid:\n";
echo "Tanh is zero-centered (outputs from -1 to 1)\n";
echo "Sigmoid outputs from 0 to 1 (always positive)\n";
echo "\nUse tanh when:\n";
echo "  - Need zero-centered activations in hidden layers\n";
echo "  - Working with RNNs/LSTMs\n";
echo "  - Stronger gradients than sigmoid needed\n";
