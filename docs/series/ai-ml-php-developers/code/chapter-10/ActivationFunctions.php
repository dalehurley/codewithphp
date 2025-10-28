<?php

declare(strict_types=1);

/**
 * Collection of activation functions and their derivatives
 * for neural networks.
 */
class ActivationFunctions
{
    /**
     * Step activation: 1 if z > 0, else 0
     * (Used in classic perceptrons, not differentiable)
     */
    public static function step(float $z): int
    {
        return $z > 0 ? 1 : 0;
    }

    /**
     * Sigmoid activation: σ(z) = 1 / (1 + e^(-z))
     * Outputs in range (0, 1), smooth S-curve
     */
    public static function sigmoid(float $z): float
    {
        // Prevent overflow for very negative values
        if ($z < -50) {
            return 0.0;
        }
        if ($z > 50) {
            return 1.0;
        }
        return 1.0 / (1.0 + exp(-$z));
    }

    /**
     * Sigmoid derivative: σ'(z) = σ(z) * (1 - σ(z))
     * Used in backpropagation
     */
    public static function sigmoidDerivative(float $sigmoidOutput): float
    {
        return $sigmoidOutput * (1.0 - $sigmoidOutput);
    }

    /**
     * Hyperbolic tangent: tanh(z) = (e^z - e^(-z)) / (e^z + e^(-z))
     * Outputs in range (-1, 1), zero-centered
     */
    public static function tanh(float $z): float
    {
        return tanh($z);  // PHP built-in
    }

    /**
     * Tanh derivative: tanh'(z) = 1 - tanh²(z)
     */
    public static function tanhDerivative(float $tanhOutput): float
    {
        return 1.0 - ($tanhOutput ** 2);
    }

    /**
     * ReLU (Rectified Linear Unit): max(0, z)
     * Outputs in range [0, ∞), very fast to compute
     */
    public static function relu(float $z): float
    {
        return max(0.0, $z);
    }

    /**
     * ReLU derivative: 1 if z > 0, else 0
     */
    public static function reluDerivative(float $z): float
    {
        return $z > 0 ? 1.0 : 0.0;
    }

    /**
     * Leaky ReLU: allows small negative values
     * Helps avoid "dying ReLU" problem
     */
    public static function leakyRelu(float $z, float $alpha = 0.01): float
    {
        return $z > 0 ? $z : $alpha * $z;
    }

    /**
     * Leaky ReLU derivative
     */
    public static function leakyReluDerivative(float $z, float $alpha = 0.01): float
    {
        return $z > 0 ? 1.0 : $alpha;
    }
}
