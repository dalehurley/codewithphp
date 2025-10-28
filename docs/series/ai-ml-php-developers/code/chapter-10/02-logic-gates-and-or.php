<?php

declare(strict_types=1);

require_once 'Perceptron.php';

/**
 * Train and test a perceptron on a logic gate.
 */
function trainLogicGate(string $gateName, array $truthTable, int $epochs = 20): Perceptron
{
    echo "╔═══════════════════════════════════════════════════════════╗\n";
    echo "║         Training: {$gateName} Gate                          ║\n";
    echo "╚═══════════════════════════════════════════════════════════╝\n\n";

    $perceptron = new Perceptron(inputSize: 2, learningRate: 0.1);

    echo "Truth table:\n";
    foreach ($truthTable as [$inputs, $output]) {
        echo "  " . implode(" {$gateName} ", $inputs) . " = {$output}\n";
    }
    echo "\n";

    // Training loop
    for ($epoch = 1; $epoch <= $epochs; $epoch++) {
        $errors = 0;

        foreach ($truthTable as [$inputs, $target]) {
            $prediction = $perceptron->predict($inputs);
            if ($prediction !== $target) {
                $errors++;
            }
            $perceptron->train($inputs, $target);
        }

        if ($errors === 0 && $epoch > 1) {
            echo "✓ Converged at epoch {$epoch}\n\n";
            break;
        }

        if ($epoch % 5 === 0 || $epoch === 1) {
            $accuracy = (1 - $errors / count($truthTable)) * 100;
            echo "Epoch {$epoch}: Errors = {$errors}, Accuracy = " . number_format($accuracy, 1) . "%\n";
        }
    }

    // Test
    echo "Testing learned {$gateName} gate:\n";
    echo str_repeat("-", 60) . "\n";

    $allCorrect = true;
    foreach ($truthTable as [$inputs, $expected]) {
        $prediction = $perceptron->predict($inputs);
        $status = $prediction === $expected ? "✓" : "✗ WRONG";
        echo "  " . implode(" {$gateName} ", $inputs) . " = {$prediction} (expected: {$expected}) {$status}\n";
        if ($prediction !== $expected) {
            $allCorrect = false;
        }
    }

    if ($allCorrect) {
        echo "\n🎉 SUCCESS: {$gateName} gate learned perfectly!\n";
    } else {
        echo "\n⚠ WARNING: {$gateName} gate not learned correctly.\n";
    }

    $params = $perceptron->getParameters();
    echo "\nLearned parameters:\n";
    echo "  Weights: [" . implode(", ", array_map(fn($w) => number_format($w, 3), $params['weights'])) . "]\n";
    echo "  Bias: " . number_format($params['bias'], 3) . "\n\n";

    return $perceptron;
}

// AND gate: output 1 only if BOTH inputs are 1
$andTruthTable = [
    [[0, 0], 0],
    [[0, 1], 0],
    [[1, 0], 0],
    [[1, 1], 1],
];

$andPerceptron = trainLogicGate("AND", $andTruthTable);

echo str_repeat("=", 60) . "\n\n";

// OR gate: output 1 if ANY input is 1
$orTruthTable = [
    [[0, 0], 0],
    [[0, 1], 1],
    [[1, 0], 1],
    [[1, 1], 1],
];

$orPerceptron = trainLogicGate("OR", $orTruthTable);

echo str_repeat("=", 60) . "\n";
echo "KEY INSIGHT: Both AND and OR are linearly separable.\n";
echo "A single perceptron can learn them by finding the right\n";
echo "decision boundary (a line separating 0s from 1s in 2D space).\n";
