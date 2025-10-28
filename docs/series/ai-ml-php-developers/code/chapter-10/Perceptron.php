<?php

declare(strict_types=1);

/**
 * Simple single-layer perceptron for binary classification.
 * 
 * Implements forward propagation with step activation and
 * training via the perceptron learning rule.
 */
class Perceptron
{
    /** @var array<float> Weights for each input feature */
    private array $weights;

    /** @var float Bias term (threshold offset) */
    private float $bias;

    /**
     * @param int $inputSize Number of input features
     * @param float $learningRate Step size for weight updates (default 0.1)
     */
    public function __construct(
        int $inputSize,
        private float $learningRate = 0.1
    ) {
        // Initialize weights randomly in range [-1, 1]
        $this->weights = [];
        for ($i = 0; $i < $inputSize; $i++) {
            $this->weights[] = (mt_rand() / mt_getrandmax()) * 2 - 1;
        }

        // Initialize bias randomly
        $this->bias = (mt_rand() / mt_getrandmax()) * 2 - 1;
    }

    /**
     * Step activation function: returns 1 if z > 0, else 0.
     * 
     * @param float $z Weighted sum of inputs
     * @return int Binary output (0 or 1)
     */
    private function stepActivation(float $z): int
    {
        return $z > 0 ? 1 : 0;
    }

    /**
     * Forward propagation: compute prediction for given inputs.
     * 
     * Computes z = w·x + b, then applies step activation.
     * 
     * @param array<float> $inputs Input features
     * @return int Predicted class (0 or 1)
     */
    public function predict(array $inputs): int
    {
        if (count($inputs) !== count($this->weights)) {
            throw new InvalidArgumentException(
                "Expected " . count($this->weights) . " inputs, got " . count($inputs)
            );
        }

        // Compute weighted sum: z = w₁x₁ + w₂x₂ + ... + wₙxₙ + b
        $z = $this->bias;
        foreach ($inputs as $i => $input) {
            $z += $this->weights[$i] * $input;
        }

        // Apply activation function
        return $this->stepActivation($z);
    }

    /**
     * Train on a single example using perceptron learning rule.
     * 
     * Updates weights based on error: Δw = η * (target - prediction) * x
     * 
     * @param array<float> $inputs Training example features
     * @param int $target True label (0 or 1)
     */
    public function train(array $inputs, int $target): void
    {
        // Get current prediction
        $prediction = $this->predict($inputs);

        // Compute error
        $error = $target - $prediction;

        // Update rule: w = w + learning_rate * error * input
        foreach ($inputs as $i => $input) {
            $this->weights[$i] += $this->learningRate * $error * $input;
        }

        // Update bias
        $this->bias += $this->learningRate * $error;
    }

    /**
     * Get current weights and bias (for inspection).
     * 
     * @return array{weights: array<float>, bias: float}
     */
    public function getParameters(): array
    {
        return [
            'weights' => $this->weights,
            'bias' => $this->bias,
        ];
    }
}
