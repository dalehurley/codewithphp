<?php

declare(strict_types=1);

require_once 'ActivationFunctions.php';

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║         Activation Functions Comparison                  ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

echo "Testing activation functions on sample inputs:\n";
echo str_repeat("-", 80) . "\n";

// Test various input values
$testValues = [-2.0, -1.0, -0.5, 0.0, 0.5, 1.0, 2.0];

printf("%-8s | %-8s | %-8s | %-8s | %-8s\n", "Input", "Step", "Sigmoid", "Tanh", "ReLU");
echo str_repeat("-", 80) . "\n";

foreach ($testValues as $z) {
    $step = ActivationFunctions::step($z);
    $sigmoid = ActivationFunctions::sigmoid($z);
    $tanh = ActivationFunctions::tanh($z);
    $relu = ActivationFunctions::relu($z);
    
    printf("%-8.1f | %-8d | %-8.4f | %-8.4f | %-8.1f\n", $z, $step, $sigmoid, $tanh, $relu);
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "DERIVATIVE COMPARISON (for backpropagation):\n";
echo str_repeat("-", 80) . "\n";

printf("%-8s | %-15s | %-15s | %-15s\n", "Input", "Sigmoid'", "Tanh'", "ReLU'");
echo str_repeat("-", 80) . "\n";

foreach ($testValues as $z) {
    $sigmoid = ActivationFunctions::sigmoid($z);
    $tanh = ActivationFunctions::tanh($z);
    
    $sigmoidDeriv = ActivationFunctions::sigmoidDerivative($sigmoid);
    $tanhDeriv = ActivationFunctions::tanhDerivative($tanh);
    $reluDeriv = ActivationFunctions::reluDerivative($z);
    
    printf("%-8.1f | %-15.4f | %-15.4f | %-15.1f\n", $z, $sigmoidDeriv, $tanhDeriv, $reluDeriv);
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "KEY PROPERTIES:\n\n";

echo "Step:\n";
echo "  ✓ Simple binary output (0 or 1)\n";
echo "  ✗ Not differentiable (can't use gradient descent)\n";
echo "  ✗ Can't train deep networks\n\n";

echo "Sigmoid:\n";
echo "  ✓ Smooth, differentiable everywhere\n";
echo "  ✓ Outputs probabilities in range (0, 1)\n";
echo "  ✗ Vanishing gradients for |z| > 2 (derivatives → 0)\n";
echo "  ✗ Not zero-centered (always positive outputs)\n\n";

echo "Tanh:\n";
echo "  ✓ Smooth, differentiable\n";
echo "  ✓ Zero-centered outputs in (-1, 1)\n";
echo "  ✓ Stronger gradients than sigmoid\n";
echo "  ✗ Still suffers from vanishing gradients for large |z|\n\n";

echo "ReLU:\n";
echo "  ✓ Very fast to compute (just max(0, z))\n";
echo "  ✓ No vanishing gradient for positive values\n";
echo "  ✓ Most popular in deep learning\n";
echo "  ✗ \"Dying ReLU\": neurons can get stuck at 0\n";
echo "  ✗ Not differentiable at z = 0 (but we use 0 in practice)\n\n";

echo "USAGE GUIDELINES:\n";
echo "  • Hidden layers: ReLU (default choice for deep networks)\n";
echo "  • Output layer (binary classification): Sigmoid\n";
echo "  • Output layer (multi-class): Softmax (not shown here)\n";
echo "  • Output layer (regression): Linear (no activation)\n";
