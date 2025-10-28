<?php

declare(strict_types=1);

require_once 'ActivationFunctions.php';

/**
 * Simple 2-layer neural network (1 hidden layer, 1 output layer).
 * 
 * Implements forward and backward propagation for binary classification.
 */
class SimpleNeuralNetwork
{
    /** @var array<array<float>> Weights from input to hidden layer */
    private array $weightsInputHidden;

    /** @var array<float> Biases for hidden layer */
    private array $biasesHidden;

    /** @var array<float> Weights from hidden to output layer */
    private array $weightsHiddenOutput;

    /** @var float Bias for output layer */
    private float $biasOutput;

    // Store activations for backpropagation
    private array $hiddenActivations = [];
    private float $outputActivation = 0.0;

    public function __construct(
        private int $inputSize,
        private int $hiddenSize,
        private float $learningRate = 0.5
    ) {
        $this->initializeWeights();
    }

    private function initializeWeights(): void
    {
        // Initialize weights randomly in range [-1, 1]
        $this->weightsInputHidden = [];
        for ($i = 0; $i < $this->inputSize; $i++) {
            $this->weightsInputHidden[$i] = [];
            for ($h = 0; $h < $this->hiddenSize; $h++) {
                $this->weightsInputHidden[$i][$h] = $this->randomWeight();
            }
        }

        $this->biasesHidden = array_fill(0, $this->hiddenSize, $this->randomWeight());

        $this->weightsHiddenOutput = [];
        for ($h = 0; $h < $this->hiddenSize; $h++) {
            $this->weightsHiddenOutput[$h] = $this->randomWeight();
        }

        $this->biasOutput = $this->randomWeight();
    }

    private function randomWeight(): float
    {
        return (mt_rand() / mt_getrandmax()) * 2 - 1;
    }

    /**
     * Forward propagation: compute output for given inputs.
     */
    public function predict(array $inputs): float
    {
        // Hidden layer activation
        $this->hiddenActivations = [];
        for ($h = 0; $h < $this->hiddenSize; $h++) {
            $sum = $this->biasesHidden[$h];
            for ($i = 0; $i < $this->inputSize; $i++) {
                $sum += $inputs[$i] * $this->weightsInputHidden[$i][$h];
            }
            $this->hiddenActivations[$h] = ActivationFunctions::sigmoid($sum);
        }

        // Output layer activation
        $sum = $this->biasOutput;
        for ($h = 0; $h < $this->hiddenSize; $h++) {
            $sum += $this->hiddenActivations[$h] * $this->weightsHiddenOutput[$h];
        }
        $this->outputActivation = ActivationFunctions::sigmoid($sum);

        return $this->outputActivation;
    }

    /**
     * Train on a single example using backpropagation.
     */
    public function train(array $inputs, float $target): float
    {
        // Forward pass
        $prediction = $this->predict($inputs);

        // Compute loss (mean squared error for this example)
        $loss = 0.5 * (($target - $prediction) ** 2);

        // Backward pass (backpropagation)
        // Output layer error
        $outputError = $prediction - $target;
        $outputDelta = $outputError * ActivationFunctions::sigmoidDerivative($prediction);

        // Hidden layer errors
        $hiddenDeltas = [];
        for ($h = 0; $h < $this->hiddenSize; $h++) {
            $error = $outputDelta * $this->weightsHiddenOutput[$h];
            $hiddenDeltas[$h] = $error * ActivationFunctions::sigmoidDerivative($this->hiddenActivations[$h]);
        }

        // Update weights and biases
        // Output layer
        for ($h = 0; $h < $this->hiddenSize; $h++) {
            $this->weightsHiddenOutput[$h] -= $this->learningRate * $outputDelta * $this->hiddenActivations[$h];
        }
        $this->biasOutput -= $this->learningRate * $outputDelta;

        // Hidden layer
        for ($i = 0; $i < $this->inputSize; $i++) {
            for ($h = 0; $h < $this->hiddenSize; $h++) {
                $this->weightsInputHidden[$i][$h] -= $this->learningRate * $hiddenDeltas[$h] * $inputs[$i];
            }
        }
        for ($h = 0; $h < $this->hiddenSize; $h++) {
            $this->biasesHidden[$h] -= $this->learningRate * $hiddenDeltas[$h];
        }

        return $loss;
    }

    /**
     * Get binary prediction (threshold at 0.5).
     */
    public function predictBinary(array $inputs): int
    {
        return $this->predict($inputs) >= 0.5 ? 1 : 0;
    }
}
