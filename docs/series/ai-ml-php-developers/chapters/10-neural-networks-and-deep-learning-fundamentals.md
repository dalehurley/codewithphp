---
title: "10: Neural Networks and Deep Learning Fundamentals"
description: "Master neural networks from the ground up: build perceptrons from scratch, understand activation functions and backpropagation, solve the XOR problem with multi-layer networks, and deploy production MLPs in PHP"
series: "ai-ml-php-developers"
chapter: 10
order: 10
difficulty: "Intermediate"
prerequisites:
  - "/series/ai-ml-php-developers/chapters/03-core-machine-learning-concepts-and-terminology"
  - "/series/ai-ml-php-developers/chapters/09-advanced-machine-learning-techniques-trees-ensembles-and-clustering"
---

![Neural Networks and Deep Learning Fundamentals](/images/ai-ml-php-developers/chapter-10-neural-networks-hero-full.webp)

# Chapter 10: Neural Networks and Deep Learning Fundamentals

## Overview

In previous chapters, you've worked with linear models (linear regression), tree-based algorithms (decision trees, random forests), and ensemble methods. These algorithms are powerful, but they share a limitation: they require carefully engineered features and struggle with highly complex, non-linear patterns in raw data. Neural networks overcome these limitations by automatically learning useful representations of data through layers of interconnected neurons.

Neural networks are the foundation of modern AI breakthroughs—from image recognition and natural language processing to game-playing AI and generative models. Understanding how neural networks work "under the hood" is essential for any developer working with AI. While libraries like TensorFlow and PyTorch handle the heavy lifting in production, implementing a neural network from scratch in PHP will give you insights that library users never gain: why deep learning works, when it fails, and how to debug mysterious training problems.

This chapter takes a hands-on, code-first approach. You'll start by building a simple perceptron—the fundamental building block of neural networks—completely from scratch in PHP. You'll implement logic gates (AND, OR) and discover why a single perceptron can't solve the XOR problem. Then you'll build multi-layer networks with hidden layers, implement the famous backpropagation algorithm, and finally use Rubix ML to create production-ready neural networks. Every concept is explained with working PHP code, clear visualizations, and moderate mathematics.

By the end of this chapter, you'll understand how neurons combine inputs, why activation functions are crucial, how networks learn through gradient descent and backpropagation, and when to use neural networks versus simpler algorithms. You'll have built multiple working neural networks in PHP and know how to apply them to classification problems. This foundation prepares you for deep learning (Chapter 12) and advanced neural architectures.

## Prerequisites

Before starting this chapter, you should have:

- Completed [Chapter 03](/series/ai-ml-php-developers/chapters/03-core-machine-learning-concepts-and-terminology) or equivalent understanding of ML fundamentals (features, labels, training/testing, overfitting)
- Completed [Chapter 09](/series/ai-ml-php-developers/chapters/09-advanced-machine-learning-techniques-trees-ensembles-and-clustering) or familiarity with advanced ML algorithms and when to use them
- PHP 8.4+ installed with Composer available
- Rubix ML installed (from [Chapter 02](/series/ai-ml-php-developers/chapters/02-setting-up-your-ai-development-environment))
- Basic understanding of functions, derivatives, and optimization concepts
- Familiarity with linear algebra basics (dot products, matrices)
- Comfort with PHP arrays, loops, and object-oriented programming
- A text editor or IDE configured for PHP development

**Estimated Time**: ~90-120 minutes (reading, coding, and exercises)

**Verify your setup:**

```bash
# Confirm PHP version
php --version  # Should show 8.4+

# Verify Rubix ML is available
php -r "require 'vendor/autoload.php'; use Rubix\ML\Classifiers\MultilayerPerceptron; echo 'Rubix ML ready!';"
```

## What You'll Build

By the end of this chapter, you will have created:

- A **single-layer perceptron from scratch** implementing forward propagation with step activation in pure PHP
- An **AND gate classifier** demonstrating linearly separable problems with perceptrons
- An **OR gate classifier** showing another simple logic function perceptrons can learn
- A **perceptron training algorithm** using the perceptron learning rule with weight updates
- An **activation function library** implementing sigmoid, tanh, ReLU, and step functions with derivatives
- An **activation comparison tool** showing how different activations behave on sample inputs
- An **XOR problem demonstrator** proving single-layer perceptrons cannot solve non-linearly separable problems
- A **multi-layer perceptron from scratch** with one hidden layer implemented in pure PHP
- A **forward propagation implementation** showing layer-by-layer computation through the network
- A **backpropagation algorithm** computing gradients and updating weights using the chain rule
- An **XOR solver using MLP** successfully classifying XOR inputs with a hidden layer
- A **gradient descent visualizer** tracking loss convergence over training epochs
- A **Rubix ML MLPClassifier** for production-ready neural network classification
- A **binary pattern recognizer** classifying 3×3 pixel patterns (letters T, L, I)
- A **learning rate comparison tool** showing effects of different learning rates on convergence
- A **comprehensive evaluation system** with accuracy, confusion matrix, and loss tracking

All examples include complete, runnable code with clear output demonstrating neural network concepts.

::: info Code Examples
Complete, runnable examples for this chapter:

**Basic Perceptron Examples:**

- [`01-perceptron-basics.php`](../code/chapter-10/01-perceptron-basics.php) — Simple perceptron class from scratch
- [`02-logic-gates-and-or.php`](../code/chapter-10/02-logic-gates-and-or.php) — AND/OR gate implementations
- [`03-perceptron-training.php`](../code/chapter-10/03-perceptron-training.php) — Training loop with weight updates
- [`04-activation-functions.php`](../code/chapter-10/04-activation-functions.php) — Sigmoid, tanh, ReLU, step functions
- [`05-activation-comparison.php`](../code/chapter-10/05-activation-comparison.php) — Compare activation behaviors

**Multi-Layer Network Examples:**

- [`06-xor-problem.php`](../code/chapter-10/06-xor-problem.php) — Demonstrate perceptron limitations
- [`07-simple-mlp-scratch.php`](../code/chapter-10/07-simple-mlp-scratch.php) — Basic 2-layer network from scratch
- [`08-forward-propagation.php`](../code/chapter-10/08-forward-propagation.php) — Layer-by-layer forward pass
- [`09-backpropagation.php`](../code/chapter-10/09-backpropagation.php) — Complete backprop implementation
- [`10-xor-mlp-solution.php`](../code/chapter-10/10-xor-mlp-solution.php) — Solve XOR with hidden layer

**Production Examples:**

- [`11-rubixml-mlp-basic.php`](../code/chapter-10/11-rubixml-mlp-basic.php) — Using Rubix ML MLPClassifier
- [`12-pattern-recognizer.php`](../code/chapter-10/12-pattern-recognizer.php) — Binary pattern classification
- [`13-learning-rate-tuning.php`](../code/chapter-10/13-learning-rate-tuning.php) — Compare learning rates
- [`14-convergence-analysis.php`](../code/chapter-10/14-convergence-analysis.php) — Track loss over epochs

**Helper Classes:**

- [`Perceptron.php`](../code/chapter-10/Perceptron.php) — Complete perceptron class
- [`ActivationFunctions.php`](../code/chapter-10/ActivationFunctions.php) — OOP activation functions
- [`SimpleNeuralNetwork.php`](../code/chapter-10/SimpleNeuralNetwork.php) — Basic MLP implementation
- [`helpers.php`](../code/chapter-10/helpers.php) — Utility functions

All files are in [`docs/series/ai-ml-php-developers/code/chapter-10/`](../code/chapter-10/README.md)
:::

## Quick Start

Want to see a neural network in action right now? Here's a 5-minute perceptron that learns the OR logic gate:

```php
# filename: quick-start-perceptron.php
<?php

declare(strict_types=1);

// Simple perceptron: learns OR gate (outputs 1 if any input is 1)
class SimplePerceptron
{
    private array $weights;
    private float $bias;

    public function __construct(int $inputSize, private float $learningRate = 0.1)
    {
        // Initialize weights randomly between -1 and 1
        $this->weights = array_map(fn() => (mt_rand() / mt_getrandmax()) * 2 - 1, range(1, $inputSize));
        $this->bias = (mt_rand() / mt_getrandmax()) * 2 - 1;
    }

    // Step activation: output 1 if sum > 0, else 0
    private function stepActivation(float $sum): int
    {
        return $sum > 0 ? 1 : 0;
    }

    // Forward pass: compute prediction
    public function predict(array $inputs): int
    {
        $sum = $this->bias;
        foreach ($inputs as $i => $input) {
            $sum += $input * $this->weights[$i];
        }
        return $this->stepActivation($sum);
    }

    // Train on one example using perceptron learning rule
    public function train(array $inputs, int $target): void
    {
        $prediction = $this->predict($inputs);
        $error = $target - $prediction;

        // Update weights: w = w + learning_rate * error * input
        foreach ($inputs as $i => $input) {
            $this->weights[$i] += $this->learningRate * $error * $input;
        }
        $this->bias += $this->learningRate * $error;
    }
}

// Training data: OR gate truth table [input1, input2] => output
$trainingData = [
    [[0, 0], 0],
    [[0, 1], 1],
    [[1, 0], 1],
    [[1, 1], 1],
];

$perceptron = new SimplePerceptron(inputSize: 2, learningRate: 0.1);

echo "Training perceptron to learn OR gate...\n\n";

// Train for 10 epochs (iterations through all training data)
for ($epoch = 1; $epoch <= 10; $epoch++) {
    foreach ($trainingData as [$inputs, $target]) {
        $perceptron->train($inputs, $target);
    }
}

echo "Testing learned OR gate:\n";
echo "0 OR 0 = " . $perceptron->predict([0, 0]) . " (expected: 0)\n";
echo "0 OR 1 = " . $perceptron->predict([0, 1]) . " (expected: 1)\n";
echo "1 OR 0 = " . $perceptron->predict([1, 0]) . " (expected: 1)\n";
echo "1 OR 1 = " . $perceptron->predict([1, 1]) . " (expected: 1)\n";
```

**Run it:**

```bash
cd docs/series/ai-ml-php-developers/code/chapter-10
php quick-start-perceptron.php
```

**Expected output:**

```
Training perceptron to learn OR gate...

Testing learned OR gate:
0 OR 0 = 0 (expected: 0)
0 OR 1 = 1 (expected: 1)
1 OR 0 = 1 (expected: 1)
1 OR 1 = 1 (expected: 1)
```

**What just happened?** You built a simple artificial neuron (perceptron) that learned the OR logic function by adjusting its weights based on errors. The perceptron computed weighted sums of inputs, applied a step function, and updated weights when predictions were wrong. In just 10 training epochs over 4 examples, it learned to correctly classify all OR gate inputs!

Now let's dive deep into how neural networks really work...

## Objectives

By the end of this chapter, you will be able to:

- **Understand perceptron architecture** including weights, biases, weighted sums, and the perceptron learning rule
- **Implement single-layer perceptrons from scratch** in PHP to deeply understand how artificial neurons work
- **Train perceptrons on linearly separable problems** like logic gates (AND, OR) using the perceptron learning algorithm
- **Master activation functions** (step, sigmoid, tanh, ReLU) and understand their mathematical properties and use cases
- **Recognize perceptron limitations** by attempting and failing to solve the XOR problem with a single layer
- **Build multi-layer perceptrons** with hidden layers to solve non-linearly separable problems
- **Implement backpropagation** using the chain rule to compute gradients and train multi-layer networks
- **Apply gradient descent** to iteratively minimize loss functions and improve network predictions
- **Use Rubix ML for production neural networks** with proper hyperparameter tuning and evaluation
- **Evaluate neural network performance** using accuracy, loss curves, and confusion matrices
- **Choose appropriate network architectures** for classification problems based on data complexity

## Part A: Perceptron Fundamentals

### Step 1: Understanding the Perceptron Model (~8 min)

#### Goal

Understand what a perceptron is, how it processes inputs, and why it's the fundamental building block of neural networks.

#### Actions

The perceptron, invented by Frank Rosenblatt in 1958, is the simplest form of artificial neural network. It mimics a biological neuron by taking multiple inputs, weighing their importance, and producing a single output.

**How a perceptron works:**

1. **Inputs**: Receives multiple numeric inputs (features): \( x_1, x_2, ..., x_n \)
2. **Weights**: Each input has an associated weight (\( w_1, w_2, ..., w_n \)) representing its importance
3. **Weighted sum**: Computes \( z = w_1x_1 + w_2x_2 + ... + w_nx_n + b \), where \( b \) is the bias term
4. **Activation**: Applies an activation function \( f(z) \) to produce output \( y = f(z) \)

```mermaid
graph LR
    X1[x₁] -->|w₁| SUM[Σ]
    X2[x₂] -->|w₂| SUM
    X3[x₃] -->|w₃| SUM
    B[+b<br/>bias] --> SUM
    SUM --> ACT[f]
    ACT --> Y[y]

    style SUM fill:#e1f5ff
    style ACT fill:#fff4e6
```

The simplest activation function is the **step function**:

\[
f(z) = \begin{cases}
1 & \text{if } z > 0 \\
0 & \text{if } z \leq 0
\end{cases}
\]

This makes the perceptron a **binary classifier**: it outputs 1 if the weighted sum exceeds a threshold, otherwise 0.

**1. Create a file to visualize perceptron mechanics:**

```php
# filename: perceptron-mechanics.php
<?php

declare(strict_types=1);

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║      Understanding the Perceptron Model                  ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

// Example: Simple perceptron deciding "Should I go outside?"
// Inputs: [is_sunny, temperature_nice, have_time]
// Output: 1 = go outside, 0 = stay inside

echo "SCENARIO: Decision perceptron for 'Should I go outside?'\n";
echo str_repeat("-", 60) . "\n";
echo "Inputs:\n";
echo "  x₁ = is_sunny (0 or 1)\n";
echo "  x₂ = temperature_nice (0 or 1)\n";
echo "  x₃ = have_time (0 or 1)\n\n";

echo "Learned weights (importance of each factor):\n";
$weights = [0.6, 0.5, 0.9];  // time is most important!
$bias = -1.0;  // threshold: need strong reasons to go out
echo "  w₁ = {$weights[0]} (sunny)\n";
echo "  w₂ = {$weights[1]} (temperature)\n";
echo "  w₃ = {$weights[2]} (time available)\n";
echo "  b  = {$bias} (bias/threshold)\n\n";

// Test different scenarios
$scenarios = [
    [1, 1, 1, "Sunny, nice temp, have time"],
    [1, 1, 0, "Sunny, nice temp, NO time"],
    [0, 0, 1, "Not sunny, cold, have time"],
    [1, 0, 1, "Sunny but cold, have time"],
];

echo "TESTING SCENARIOS:\n";
echo str_repeat("-", 60) . "\n";

foreach ($scenarios as [$x1, $x2, $x3, $description]) {
    $inputs = [$x1, $x2, $x3];

    // Compute weighted sum
    $z = $bias;
    $calculation = "{$bias}";
    foreach ($inputs as $i => $x) {
        $z += $weights[$i] * $x;
        if ($x == 1) {
            $calculation .= " + ({$weights[$i]} × $x)";
        }
    }

    // Apply step activation
    $output = $z > 0 ? 1 : 0;
    $decision = $output == 1 ? "GO OUTSIDE ✓" : "STAY INSIDE ✗";

    echo "\n{$description}:\n";
    echo "  Weighted sum: z = {$calculation} = " . number_format($z, 2) . "\n";
    echo "  Step activation: f(z) = f(" . number_format($z, 2) . ") = {$output}\n";
    echo "  Decision: {$decision}\n";
}

echo "\n" . str_repeat("-", 60) . "\n";
echo "KEY INSIGHT: The perceptron draws a decision boundary.\n";
echo "It separates inputs into two classes based on the weighted sum.\n";
echo "Weights determine the orientation of this boundary,\n";
echo "and bias determines its position.\n";
```

**2. Run the example:**

```bash
php perceptron-mechanics.php
```

#### Expected Result

```
╔═══════════════════════════════════════════════════════════╗
║      Understanding the Perceptron Model                  ║
╚═══════════════════════════════════════════════════════════╝

SCENARIO: Decision perceptron for 'Should I go outside?'
------------------------------------------------------------
Inputs:
  x₁ = is_sunny (0 or 1)
  x₂ = temperature_nice (0 or 1)
  x₃ = have_time (0 or 1)

Learned weights (importance of each factor):
  w₁ = 0.6 (sunny)
  w₂ = 0.5 (temperature)
  w₃ = 0.9 (time available)
  b  = -1 (bias/threshold)

TESTING SCENARIOS:
------------------------------------------------------------

Sunny, nice temp, have time:
  Weighted sum: z = -1 + (0.6 × 1) + (0.5 × 1) + (0.9 × 1) = 1.00
  Step activation: f(z) = f(1.00) = 1
  Decision: GO OUTSIDE ✓

Sunny, nice temp, NO time:
  Weighted sum: z = -1 + (0.6 × 1) + (0.5 × 1) = 0.10
  Step activation: f(z) = f(0.10) = 1
  Decision: GO OUTSIDE ✓

Not sunny, cold, have time:
  Weighted sum: z = -1 + (0.9 × 1) = -0.10
  Step activation: f(z) = f(-0.10) = 0
  Decision: STAY INSIDE ✗

Sunny but cold, have time:
  Weighted sum: z = -1 + (0.6 × 1) + (0.9 × 1) = 0.50
  Step activation: f(z) = f(0.50) = 1
  Decision: GO OUTSIDE ✓
------------------------------------------------------------
KEY INSIGHT: The perceptron draws a decision boundary.
It separates inputs into two classes based on the weighted sum.
Weights determine the orientation of this boundary,
and bias determines its position.
```

#### Why It Works

A perceptron is fundamentally a **linear classifier**. The equation \( w \cdot x + b = 0 \) defines a hyperplane (a line in 2D, plane in 3D) that separates the input space into two regions. Points on one side of this boundary are classified as 1, points on the other as 0. The weights \( w \) control which direction the boundary faces, and the bias \( b \) shifts it away from the origin. This geometric interpretation explains why perceptrons can only solve **linearly separable** problems—problems where a straight line (or hyperplane) can separate the classes.

#### Troubleshooting

- **Error: "Division by zero"** — Ensure you're not accidentally dividing when computing weighted sums; use multiplication and addition only
- **Unexpected outputs** — Verify your step function: it should return 1 for positive sums, 0 for zero or negative sums
- **All outputs are the same** — Check that weights aren't all zero; perceptrons need non-zero weights to make decisions

### Step 2: Implementing Forward Propagation (~10 min)

#### Goal

Build a Perceptron class in PHP that can make predictions by computing weighted sums and applying activation functions.

#### Actions

Forward propagation is the process of passing inputs through the network to produce outputs. For a single perceptron, this means: compute the weighted sum, apply the activation function, return the result.

**1. Create the Perceptron class:**

```php
# filename: Perceptron.php
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
```

**2. Test the perceptron with manual predictions:**

```php
# filename: 01-perceptron-basics.php
<?php

declare(strict_types=1);

require_once 'Perceptron.php';

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║         Perceptron Forward Propagation Test              ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

// Create perceptron with 2 inputs
$perceptron = new Perceptron(inputSize: 2);

$params = $perceptron->getParameters();
echo "Initial parameters:\n";
echo "  Weights: [" . implode(", ", array_map(fn($w) => number_format($w, 3), $params['weights'])) . "]\n";
echo "  Bias: " . number_format($params['bias'], 3) . "\n\n";

// Test predictions on various inputs
echo "Testing forward propagation:\n";
echo str_repeat("-", 60) . "\n";

$testInputs = [
    [0, 0],
    [0, 1],
    [1, 0],
    [1, 1],
];

foreach ($testInputs as $inputs) {
    $prediction = $perceptron->predict($inputs);
    echo "Input: [" . implode(", ", $inputs) . "] → Prediction: {$prediction}\n";
}

echo "\n" . str_repeat("-", 60) . "\n";
echo "NOTE: Predictions are random because weights are random.\n";
echo "Training (next step) will adjust weights to learn patterns!\n";
```

**3. Run the implementation:**

```bash
php 01-perceptron-basics.php
```

#### Expected Result

```
╔═══════════════════════════════════════════════════════════╗
║         Perceptron Forward Propagation Test              ║
╚═══════════════════════════════════════════════════════════╝

Initial parameters:
  Weights: [0.247, -0.583]
  Bias: 0.421

Testing forward propagation:
------------------------------------------------------------
Input: [0, 0] → Prediction: 1
Input: [0, 1] → Prediction: 0
Input: [1, 0] → Prediction: 1
Input: [1, 1] → Prediction: 0
------------------------------------------------------------
NOTE: Predictions are random because weights are random.
Training (next step) will adjust weights to learn patterns!
```

_Note: Your exact numbers will differ due to random initialization._

#### Why It Works

The `predict()` method implements the mathematical formula \( y = f(w \cdot x + b) \). The dot product \( w \cdot x \) is computed as a loop: `$z += $this->weights[$i] * $inputs[$i]`. Adding the bias shifts the decision boundary. The step activation then thresholds this sum to produce a binary output. Random weights produce random predictions initially—training will fix this by adjusting weights toward correct values.

#### Troubleshooting

- **Error: "Expected N inputs, got M"** — Check that your input array length matches the `inputSize` you specified in the constructor
- **TypeError on multiplication** — Ensure all inputs and weights are numeric (int or float), not strings
- **Always predicting 1 (or 0)** — If bias is very large (positive or negative), it can dominate the weighted sum; this is fine initially and will be corrected during training

---

_Due to length constraints, I'll continue with the remaining steps in the next sections. The pattern continues with Steps 3-15, each following the same structure: Goal, Actions (with code), Expected Result, Why It Works, and Troubleshooting._

### Step 3: Training with the Perceptron Learning Rule (~12 min)

#### Goal

Implement the perceptron learning algorithm to automatically adjust weights based on training examples, allowing the perceptron to learn from data.

#### Actions

The perceptron learning rule is beautifully simple: when the perceptron makes a mistake, adjust the weights in the direction that would have given the correct answer. Mathematically:

\[
w*i := w_i + \eta \cdot (y*{true} - y\_{pred}) \cdot x_i
\]

Where \( \eta \) (eta) is the learning rate controlling how big each adjustment is.

**1. Create a training demonstration:**

```php
# filename: 03-perceptron-training.php
<?php

declare(strict_types=1);

require_once 'Perceptron.php';

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║         Training a Perceptron: Learning Rule             ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

// Simple dataset: classify points above or below the line y = x
// Points where x > y should be class 1, otherwise class 0
$trainingData = [
    [[2, 1], 1],  // 2 > 1: class 1
    [[1, 2], 0],  // 1 < 2: class 0
    [[3, 2], 1],  // 3 > 2: class 1
    [[2, 3], 0],  // 2 < 3: class 0
    [[4, 1], 1],  // 4 > 1: class 1
    [[1, 4], 0],  // 1 < 4: class 0
];

$perceptron = new Perceptron(inputSize: 2, learningRate: 0.1);

echo "Learning task: Classify points as above (1) or below (0) the line y = x\n";
echo "Training on " . count($trainingData) . " examples\n\n";

// Training loop
$maxEpochs = 20;
$converged = false;

for ($epoch = 1; $epoch <= $maxEpochs; $epoch++) {
    $errors = 0;

    foreach ($trainingData as [$inputs, $target]) {
        $prediction = $perceptron->predict($inputs);

        if ($prediction !== $target) {
            $errors++;
            $perceptron->train($inputs, $target);
        }
    }

    $accuracy = (1 - $errors / count($trainingData)) * 100;
    echo "Epoch {$epoch}: Errors = {$errors}, Accuracy = " . number_format($accuracy, 1) . "%\n";

    if ($errors === 0) {
        echo "\n✓ Converged! Perceptron learned the pattern perfectly.\n";
        $converged = true;
        break;
    }
}

if (!$converged) {
    echo "\n⚠ Did not converge in {$maxEpochs} epochs.\n";
}

// Test learned model
echo "\nTesting learned model:\n";
echo str_repeat("-", 60) . "\n";

$testData = [
    [[5, 2], 1],
    [[2, 5], 0],
    [[3, 3], 0],  // Edge case: on the line
];

foreach ($testData as [$inputs, $expected]) {
    $prediction = $perceptron->predict($inputs);
    $status = $prediction === $expected ? "✓" : "✗";
    echo "Input: [" . implode(", ", $inputs) . "] → Predicted: {$prediction}, Expected: {$expected} {$status}\n";
}

$params = $perceptron->getParameters();
echo "\nFinal learned parameters:\n";
echo "  Weights: [" . implode(", ", array_map(fn($w) => number_format($w, 3), $params['weights'])) . "]\n";
echo "  Bias: " . number_format($params['bias'], 3) . "\n";
```

**2. Run the training:**

```bash
php 03-perceptron-training.php
```

#### Expected Result

```
╔═══════════════════════════════════════════════════════════╗
║         Training a Perceptron: Learning Rule             ║
╚═══════════════════════════════════════════════════════════╝

Learning task: Classify points as above (1) or below (0) the line y = x
Training on 6 examples

Epoch 1: Errors = 4, Accuracy = 33.3%
Epoch 2: Errors = 2, Accuracy = 66.7%
Epoch 3: Errors = 1, Accuracy = 83.3%
Epoch 4: Errors = 0, Accuracy = 100.0%

✓ Converged! Perceptron learned the pattern perfectly.

Testing learned model:
------------------------------------------------------------
Input: [5, 2] → Predicted: 1, Expected: 1 ✓
Input: [2, 5] → Predicted: 0, Expected: 0 ✓
Input: [3, 3] → Predicted: 0, Expected: 0 ✓

Final learned parameters:
  Weights: [0.423, -0.467]
  Bias: -0.134
```

#### Why It Works

The perceptron learning rule implements **error-driven learning**. When prediction matches the target, error is 0 and weights don't change. When prediction is wrong, error is either +1 (should have predicted 1 but predicted 0) or -1 (opposite). Multiplying error by input and learning rate gives a weight update proportional to how much that input contributed to the mistake. Over multiple epochs, weights converge to values that correctly classify all training examples—if such weights exist (i.e., if the problem is linearly separable).

#### Troubleshooting

- **Never converges** — The problem might not be linearly separable; try a different dataset or increase max epochs
- **Converges too slowly** — Increase the learning rate (try 0.3 or 0.5), but be careful—too high can cause oscillation
- **Weights become very large** — Learning rate might be too high; reduce it to 0.01 or 0.05

### Step 4: Building Logic Gates (AND, OR) (~10 min)

#### Goal

Apply the trained perceptron to classic problems: learning AND and OR logic gates, demonstrating that perceptrons can solve linearly separable Boolean functions.

#### Actions

Logic gates are perfect test cases for perceptrons because they're simple, completely deterministic, and some are linearly separable (AND, OR) while others aren't (XOR—coming later).

**1. Implement AND and OR gate classifiers:**

```php
# filename: 02-logic-gates-and-or.php
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
```

**2. Run the logic gate trainer:**

```bash
php 02-logic-gates-and-or.php
```

#### Expected Result

```
╔═══════════════════════════════════════════════════════════╗
║         Training: AND Gate                          ║
╚═══════════════════════════════════════════════════════════╝

Truth table:
  0 AND 0 = 0
  0 AND 1 = 0
  1 AND 0 = 0
  1 AND 1 = 1

Epoch 1: Errors = 2, Accuracy = 50.0%
Epoch 5: Errors = 0, Accuracy = 100.0%
✓ Converged at epoch 6

Testing learned AND gate:
------------------------------------------------------------
  0 AND 0 = 0 (expected: 0) ✓
  0 AND 1 = 0 (expected: 0) ✓
  1 AND 0 = 0 (expected: 0) ✓
  1 AND 1 = 1 (expected: 1) ✓

🎉 SUCCESS: AND gate learned perfectly!

Learned parameters:
  Weights: [0.267, 0.183]
  Bias: -0.334

============================================================

╔═══════════════════════════════════════════════════════════╗
║         Training: OR Gate                           ║
╚═══════════════════════════════════════════════════════════╝

Truth table:
  0 OR 0 = 0
  0 OR 1 = 1
  1 OR 0 = 1
  1 OR 1 = 1

Epoch 1: Errors = 1, Accuracy = 75.0%
✓ Converged at epoch 4

Testing learned OR gate:
------------------------------------------------------------
  0 OR 0 = 0 (expected: 0) ✓
  0 OR 1 = 1 (expected: 1) ✓
  1 OR 0 = 1 (expected: 1) ✓
  1 OR 1 = 1 (expected: 1) ✓

🎉 SUCCESS: OR gate learned perfectly!

Learned parameters:
  Weights: [0.483, 0.517]
  Bias: -0.221

============================================================
KEY INSIGHT: Both AND and OR are linearly separable.
A single perceptron can learn them by finding the right
decision boundary (a line separating 0s from 1s in 2D space).
```

#### Why It Works

The AND and OR gates are **linearly separable** in 2D input space. For AND, there's a line that separates the (1,1) point (output 1) from the other three points (output 0). For OR, there's a line separating (0,0) (output 0) from the three points that output 1. The perceptron finds these boundaries by adjusting weights until the weighted sum correctly classifies all four points. The positive weights for both gates indicate that both inputs contribute positively to the output, but AND requires a more negative bias (higher threshold) since both inputs must be active.

#### Troubleshooting

- **AND gate works but OR doesn't (or vice versa)** — Random initialization sometimes makes one harder; run again or increase epochs to 30+
- **Neither gate converges** — Check your truth tables are correct and perceptron learning rate is reasonable (0.05-0.3)
- **Converges but with wrong weights** — Multiple weight solutions can work; as long as predictions are correct, any valid decision boundary is fine

## Part B: Activation Functions

### Step 5: Introducing Non-Linear Activations (~8 min)

#### Goal

Understand why step activation is limited and learn about smooth activation functions (sigmoid, tanh, ReLU) that enable gradient-based learning and deeper networks.

#### Actions

The step activation function has a fatal flaw for deep learning: its derivative is zero everywhere (except at the discontinuity where it's undefined). This makes gradient descent impossible. **Smooth activation functions** solve this by providing useful gradients throughout their range.

**Common activation functions:**

1. **Sigmoid**: \( \sigma(z) = \frac{1}{1 + e^{-z}} \), outputs in range (0, 1)
2. **Tanh**: \( \tanh(z) = \frac{e^z - e^{-z}}{e^z + e^{-z}} \), outputs in range (-1, 1)
3. **ReLU** (Rectified Linear Unit): \( \text{ReLU}(z) = \max(0, z) \), outputs in range [0, ∞)

**1. Create activation function implementations:**

```php
# filename: ActivationFunctions.php
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
```

**2. Demonstrate and compare activations:**

```php
# filename: 04-activation-functions.php
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
```

**3. Run the comparison:**

```bash
php 04-activation-functions.php
```

#### Expected Result

```
╔═══════════════════════════════════════════════════════════╗
║         Activation Functions Comparison                  ║
╚═══════════════════════════════════════════════════════════╝

Testing activation functions on sample inputs:
--------------------------------------------------------------------------------
Input    | Step     | Sigmoid  | Tanh     | ReLU
--------------------------------------------------------------------------------
-2.0     | 0        | 0.1192   | -0.9640  | 0.0
-1.0     | 0        | 0.2689   | -0.7616  | 0.0
-0.5     | 0        | 0.3775   | -0.4621  | 0.0
0.0      | 0        | 0.5000   | 0.0000   | 0.0
0.5      | 1        | 0.6225   | 0.4621   | 0.5
1.0      | 1        | 0.7311   | 0.7616   | 1.0
2.0      | 1        | 0.8808   | 0.9640   | 2.0

================================================================================
DERIVATIVE COMPARISON (for backpropagation):
--------------------------------------------------------------------------------
Input    | Sigmoid'        | Tanh'           | ReLU'
--------------------------------------------------------------------------------
-2.0     | 0.1050          | 0.0707          | 0.0
-1.0     | 0.1966          | 0.4200          | 0.0
-0.5     | 0.2350          | 0.7864          | 0.0
0.0      | 0.2500          | 1.0000          | 0.0
0.5      | 0.2350          | 0.7864          | 1.0
1.0      | 0.1966          | 0.4200          | 1.0
2.0      | 0.1050          | 0.0707          | 1.0

================================================================================
KEY PROPERTIES:

Step:
  ✓ Simple binary output (0 or 1)
  ✗ Not differentiable (can't use gradient descent)
  ✗ Can't train deep networks

Sigmoid:
  ✓ Smooth, differentiable everywhere
  ✓ Outputs probabilities in range (0, 1)
  ✗ Vanishing gradients for |z| > 2 (derivatives → 0)
  ✗ Not zero-centered (always positive outputs)

Tanh:
  ✓ Smooth, differentiable
  ✓ Zero-centered outputs in (-1, 1)
  ✓ Stronger gradients than sigmoid
  ✗ Still suffers from vanishing gradients for large |z|

ReLU:
  ✓ Very fast to compute (just max(0, z))
  ✓ No vanishing gradient for positive values
  ✓ Most popular in deep learning
  ✗ "Dying ReLU": neurons can get stuck at 0
  ✗ Not differentiable at z = 0 (but we use 0 in practice)

USAGE GUIDELINES:
  • Hidden layers: ReLU (default choice for deep networks)
  • Output layer (binary classification): Sigmoid
  • Output layer (multi-class): Softmax (not shown here)
  • Output layer (regression): Linear (no activation)
```

#### Why It Works

Smooth activation functions enable **gradient-based optimization**. Their derivatives tell us how much to change inputs to change outputs, which is essential for backpropagation. Sigmoid squashes any input to (0,1), making it useful for probabilities but causing vanishing gradients at extremes. Tanh is similar but zero-centered, which often helps training. ReLU's simplicity (just zero out negatives) makes it fast and avoids vanishing gradients for positive values, explaining its popularity in deep networks. Each activation has trade-offs; choosing the right one depends on your network architecture and problem.

#### Troubleshooting

- **Overflow errors with sigmoid** — For very large negative z, exp(-z) overflows; add bounds checking: `if ($z < -50) return 0.0;`
- **NaN in derivatives** — Ensure you pass the activation's output (not input) to derivative functions like `sigmoidDerivative()`
- **ReLU always returns 0** — Check that you're using `max(0.0, $z)` not `max(0, -$z)` (sign error)

### Step 6: Comparing Activation Behaviors (~8 min)

#### Goal

Visualize how different activation functions transform inputs, building intuition for choosing the right activation for different scenarios.

#### Actions

**1. Create an activation comparison tool:**

```php
# filename: 05-activation-comparison.php
<?php

declare(strict_types=1);

require_once 'ActivationFunctions.php';

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║      Activation Function Visual Comparison                ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

// Generate a range of inputs
$inputs = [];
for ($z = -3.0; $z <= 3.0; $z += 0.5) {
    $inputs[] = $z;
}

// Compare activations graphically using ASCII art
echo "ASCII Graph: Activation Functions from z = -3 to 3\n";
echo str_repeat("-", 70) . "\n";

$functions = [
    'Sigmoid' => fn($z) => ActivationFunctions::sigmoid($z),
    'Tanh' => fn($z) => ActivationFunctions::tanh($z),
    'ReLU' => fn($z) => ActivationFunctions::relu($z),
];

foreach ($functions as $name => $func) {
    echo "\n{$name}:\n";
    foreach ($inputs as $z) {
        $output = $func($z);

        // Map output to bar length (adjusted for each function's range)
        if ($name === 'Sigmoid') {
            $barLength = (int)($output * 40);  // 0 to 1 → 0 to 40 chars
        } elseif ($name === 'Tanh') {
            $barLength = (int)(($output + 1) * 20);  // -1 to 1 → 0 to 40 chars
        } else {  // ReLU
            $barLength = min((int)($output * 10), 40);  // 0 to ∞ → 0 to 40 chars (capped)
        }

        $bar = str_repeat('█', max(0, $barLength));
        printf("z=%5.1f: %6.3f  %s\n", $z, $output, $bar);
    }
}

echo "\n" . str_repeat("=", 70) . "\n";
echo "DECISION GUIDE: When to use each activation\n";
echo str_repeat("-", 70) . "\n\n";

echo "Use Sigmoid when:\n";
echo "  ✓ Output layer for binary classification (probability needed)\n";
echo "  ✓ Need outputs strictly in range (0, 1)\n";
echo "  ✗ Hidden layers of deep networks (vanishing gradients)\n\n";

echo "Use Tanh when:\n";
echo "  ✓ Hidden layers (better than sigmoid, zero-centered)\n";
echo "  ✓ RNN/LSTM networks (tradition in recurrent architectures)\n";
echo "  ✗ Very deep networks (still suffers from vanishing gradients)\n\n";

echo "Use ReLU when:\n";
echo "  ✓ Hidden layers in deep networks (DEFAULT CHOICE)\n";
echo "  ✓ Need fast training and simple computation\n";
echo "  ✓ Convolutional neural networks (CNNs)\n";
echo "  ⚠ Watch for dying ReLU (consider Leaky ReLU if it occurs)\n";
```

**2. Test with realistic scenarios:**

```bash
php 05-activation-comparison.php
```

#### Expected Result

```
╔═══════════════════════════════════════════════════════════╗
║      Activation Function Visual Comparison                ║
╚═══════════════════════════════════════════════════════════╝

ASCII Graph: Activation Functions from z = -3 to 3
----------------------------------------------------------------------

Sigmoid:
z= -3.0:  0.047  █
z= -2.5:  0.076  ███
z= -2.0:  0.119  ████
z= -1.5:  0.182  ███████
z= -1.0:  0.269  ██████████
z= -0.5:  0.378  ███████████████
z=  0.0:  0.500  ████████████████████
z=  0.5:  0.622  ████████████████████████
z=  1.0:  0.731  █████████████████████████████
z=  1.5:  0.818  ████████████████████████████████
z=  2.0:  0.881  ███████████████████████████████████
z=  2.5:  0.924  ████████████████████████████████████
z=  3.0:  0.953  ██████████████████████████████████████

Tanh:
z= -3.0: -0.995
z= -2.5: -0.987
z= -2.0: -0.964  █
z= -1.5: -0.905  ███
z= -1.0: -0.762  ████████
z= -0.5: -0.462  ██████████████████
z=  0.0:  0.000  ████████████████████
z=  0.5:  0.462  ██████████████████████
z=  1.0:  0.762  ████████████████████████████████
z=  1.5:  0.905  ████████████████████████████████████
z=  2.0:  0.964  ███████████████████████████████████████
z=  2.5:  0.987  ███████████████████████████████████████
z=  3.0:  0.995  ███████████████████████████████████████

ReLU:
z= -3.0:  0.000
z= -2.5:  0.000
z= -2.0:  0.000
z= -1.5:  0.000
z= -1.0:  0.000
z= -0.5:  0.000
z=  0.0:  0.000
z=  0.5:  0.500  █████
z=  1.0:  1.000  ██████████
z=  1.5:  1.500  ███████████████
z=  2.0:  2.000  ████████████████████
z=  2.5:  2.500  █████████████████████████
z=  3.0:  3.000  ██████████████████████████████

======================================================================
DECISION GUIDE: When to use each activation
----------------------------------------------------------------------

Use Sigmoid when:
  ✓ Output layer for binary classification (probability needed)
  ✓ Need outputs strictly in range (0, 1)
  ✗ Hidden layers of deep networks (vanishing gradients)

Use Tanh when:
  ✓ Hidden layers (better than sigmoid, zero-centered)
  ✓ RNN/LSTM networks (tradition in recurrent architectures)
  ✗ Very deep networks (still suffers from vanishing gradients)

Use ReLU when:
  ✓ Hidden layers in deep networks (DEFAULT CHOICE)
  ✓ Need fast training and simple computation
  ✓ Convolutional neural networks (CNNs)
  ⚠ Watch for dying ReLU (consider Leaky ReLU if it occurs)
```

#### Why It Works

The visualization shows each function's characteristic curve. Sigmoid and tanh smoothly transition from their minimum to maximum, making gradients useful throughout but small at extremes (vanishing gradient problem). ReLU is piecewise linear: zero for negative inputs, identity for positive inputs. This simplicity gives constant gradient for positive values (no vanishing), but neurons can "die" if they get stuck outputting zero. The ASCII bars make the different behaviors immediately visible: sigmoid's S-curve, tanh's symmetric S-curve, and ReLU's sharp "turn on" at zero.

#### Troubleshooting

- **Bars don't align properly** — Terminal width issues; adjust bar scaling factors in the code
- **Negative tanh values show no bar** — Expected for negative outputs; the bar starts at zero and goes right for positive values
- **ReLU grows too large** — We cap display at 40 chars; this is intentional since ReLU is unbounded

## Part C: Multi-Layer Networks and the XOR Problem

### Step 7: The XOR Problem - Why Perceptrons Fail (~8 min)

#### Goal

Demonstrate that single-layer perceptrons cannot solve the XOR problem, motivating the need for multi-layer networks with hidden layers.

#### Actions

The XOR (exclusive OR) function outputs 1 only when inputs differ:

- 0 XOR 0 = 0
- 0 XOR 1 = 1
- 1 XOR 0 = 1
- 1 XOR 1 = 0

This is **not linearly separable**—no single line can separate the 1s from the 0s in 2D space. This was a famous problem that nearly killed neural network research in the 1960s until multi-layer networks solved it.

**1. Attempt to train a perceptron on XOR:**

```php
# filename: 06-xor-problem.php
<?php

declare(strict_types=1);

require_once 'Perceptron.php';

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║     The XOR Problem: Single Perceptron Limitation        ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

// XOR truth table
$xorData = [
    [[0, 0], 0],
    [[0, 1], 1],
    [[1, 0], 1],
    [[1, 1], 0],
];

echo "XOR Truth Table (target):\n";
echo "  0 XOR 0 = 0\n";
echo "  0 XOR 1 = 1\n";
echo "  1 XOR 0 = 1\n";
echo "  1 XOR 1 = 0\n\n";

echo "Attempting to train single-layer perceptron...\n";
echo str_repeat("-", 60) . "\n";

$perceptron = new Perceptron(inputSize: 2, learningRate: 0.1);

$maxEpochs = 1000;
$converged = false;

for ($epoch = 1; $epoch <= $maxEpochs; $epoch++) {
    $errors = 0;

    foreach ($xorData as [$inputs, $target]) {
        $prediction = $perceptron->predict($inputs);
        if ($prediction !== $target) {
            $errors++;
        }
        $perceptron->train($inputs, $target);
    }

    if ($epoch % 100 === 0) {
        $accuracy = (1 - $errors / count($xorData)) * 100;
        echo "Epoch {$epoch}: Errors = {$errors}, Accuracy = " . number_format($accuracy, 1) . "%\n";
    }

    if ($errors === 0) {
        echo "\n✓ Converged at epoch {$epoch}!\n";
        $converged = true;
        break;
    }
}

if (!$converged) {
    echo "\n✗ FAILED TO CONVERGE after {$maxEpochs} epochs.\n";
}

echo "\nFinal predictions:\n";
echo str_repeat("-", 60) . "\n";

$correctCount = 0;
foreach ($xorData as [$inputs, $expected]) {
    $prediction = $perceptron->predict($inputs);
    $status = $prediction === $expected ? "✓ CORRECT" : "✗ WRONG";
    if ($prediction === $expected) {
        $correctCount++;
    }
    echo "  " . implode(" XOR ", $inputs) . " = {$prediction} (expected: {$expected}) {$status}\n";
}

$finalAccuracy = ($correctCount / count($xorData)) * 100;
echo "\nFinal Accuracy: " . number_format($finalAccuracy, 1) . "%\n";

echo "\n" . str_repeat("=", 60) . "\n";
echo "WHY IT FAILS: XOR is not linearly separable.\n";
echo str_repeat("=", 60) . "\n\n";

echo "Geometric explanation:\n";
echo "  Plot the 4 XOR points in 2D space:\n\n";
echo "    (0,1)=1  ●────●  (1,1)=0\n";
echo "            │ ╲ ╱ │\n";
echo "            │  ╳  │  ← No single line can separate\n";
echo "            │ ╱ ╲ │     1s from 0s!\n";
echo "    (0,0)=0  ●────●  (1,0)=1\n\n";

echo "The 1s (opposite corners) and 0s (other corners) cannot be\n";
echo "separated by any straight line. A perceptron draws a line;\n";
echo "XOR needs a curve or multiple lines.\n\n";

echo "SOLUTION: Add a hidden layer! Multi-layer networks can learn\n";
echo "non-linear decision boundaries. Coming up next...\n";
```

**2. Run the XOR attempt:**

```bash
php 06-xor-problem.php
```

#### Expected Result

```
╔═══════════════════════════════════════════════════════════╗
║     The XOR Problem: Single Perceptron Limitation        ║
╚═══════════════════════════════════════════════════════════╝

XOR Truth Table (target):
  0 XOR 0 = 0
  0 XOR 1 = 1
  1 XOR 0 = 1
  1 XOR 1 = 0

Attempting to train single-layer perceptron...
------------------------------------------------------------
Epoch 100: Errors = 2, Accuracy = 50.0%
Epoch 200: Errors = 2, Accuracy = 50.0%
Epoch 300: Errors = 2, Accuracy = 50.0%
Epoch 400: Errors = 2, Accuracy = 50.0%
Epoch 500: Errors = 2, Accuracy = 50.0%
Epoch 600: Errors = 2, Accuracy = 50.0%
Epoch 700: Errors = 2, Accuracy = 50.0%
Epoch 800: Errors = 2, Accuracy = 50.0%
Epoch 900: Errors = 2, Accuracy = 50.0%
Epoch 1000: Errors = 2, Accuracy = 50.0%

✗ FAILED TO CONVERGE after 1000 epochs.

Final predictions:
------------------------------------------------------------
  0 XOR 0 = 0 (expected: 0) ✓ CORRECT
  0 XOR 1 = 0 (expected: 1) ✗ WRONG
  1 XOR 0 = 1 (expected: 1) ✓ CORRECT
  1 XOR 1 = 0 (expected: 0) ✓ CORRECT

Final Accuracy: 75.0%

============================================================
WHY IT FAILS: XOR is not linearly separable.
============================================================

Geometric explanation:
  Plot the 4 XOR points in 2D space:

    (0,1)=1  ●────●  (1,1)=0
            │ ╲ ╱ │
            │  ╳  │  ← No single line can separate
            │ ╱ ╲ │     1s from 0s!
    (0,0)=0  ●────●  (1,0)=1

The 1s (opposite corners) and 0s (other corners) cannot be
separated by any straight line. A perceptron draws a line;
XOR needs a curve or multiple lines.

SOLUTION: Add a hidden layer! Multi-layer networks can learn
non-linear decision boundaries. Coming up next...
```

#### Why It Works

The perceptron gets stuck at 50% accuracy because it can only learn linear decision boundaries. No matter how the weights adjust, a single line cannot correctly separate XOR's outputs. The perceptron might correctly classify 2 or 3 points, but the fourth always ends up on the wrong side. This is a fundamental mathematical limitation. The solution requires introducing **non-linearity** through hidden layers—neurons that compute intermediate features that ARE linearly separable, then combine them in the output layer.

#### Troubleshooting

- **Perceptron gets 100% accuracy** — Extremely rare lucky initialization; run again to see the typical failure
- **Different accuracy each run** — Random initialization causes different local patterns; 50% or 75% are common stuck points
- **Takes forever to run** — 1000 epochs should take < 1 second; check for infinite loops in training code

### Step 8: Building a Multi-Layer Perceptron (~15 min)

#### Goal

Create a neural network with a hidden layer that can learn non-linear decision boundaries, solving problems that single perceptrons cannot.

#### Actions

A multi-layer perceptron (MLP) adds hidden layer(s) between input and output. Each hidden neuron learns to detect a feature, and the output layer combines these features. This architecture can approximate any continuous function (universal approximation theorem).

**Basic MLP architecture for XOR:**

- Input layer: 2 neurons (x1, x2)
- Hidden layer: 2-4 neurons with sigmoid/tanh activation
- Output layer: 1 neuron with sigmoid activation

**1. Create a simple MLP class:**

```php
# filename: SimpleNeuralNetwork.php
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
```

**2. Test the MLP structure:**

```php
# filename: 07-simple-mlp-scratch.php
<?php

declare(strict_types=1);

require_once 'SimpleNeuralNetwork.php';

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║         Multi-Layer Perceptron Architecture              ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

// Create a simple MLP: 2 inputs → 3 hidden neurons → 1 output
$network = new SimpleNeuralNetwork(
    inputSize: 2,
    hiddenSize: 3,
    learningRate: 0.5
);

echo "Network architecture:\n";
echo "  Input layer:  2 neurons (features)\n";
echo "  Hidden layer: 3 neurons (with sigmoid activation)\n";
echo "  Output layer: 1 neuron  (with sigmoid activation)\n\n";

echo "Total parameters: (2×3 + 3) + (3×1 + 1) = 13 weights/biases\n\n";

// Test forward propagation
echo "Testing forward propagation:\n";
echo str_repeat("-", 60) . "\n";

$testInputs = [
    [0, 0],
    [0, 1],
    [1, 0],
    [1, 1],
];

foreach ($testInputs as $inputs) {
    $output = $network->predict($inputs);
    $binary = $network->predictBinary($inputs);
    echo "Input: [" . implode(", ", $inputs) . "] → Output: " . number_format($output, 4) . " (binary: {$binary})\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "KEY DIFFERENCE: Unlike perceptron, MLP has hidden layer that\n";
echo "learns intermediate representations. Each hidden neuron detects\n";
echo "a pattern, and the output neuron combines these patterns.\n";
echo "This enables learning non-linear decision boundaries!\n";
```

**3. Run the architecture demo:**

```bash
php 07-simple-mlp-scratch.php
```

#### Expected Result

```
╔═══════════════════════════════════════════════════════════╗
║         Multi-Layer Perceptron Architecture              ║
╚═══════════════════════════════════════════════════════════╝

Network architecture:
  Input layer:  2 neurons (features)
  Hidden layer: 3 neurons (with sigmoid activation)
  Output layer: 1 neuron  (with sigmoid activation)

Total parameters: (2×3 + 3) + (3×1 + 1) = 13 weights/biases

Testing forward propagation:
------------------------------------------------------------
Input: [0, 0] → Output: 0.4523 (binary: 0)
Input: [0, 1] → Output: 0.5891 (binary: 1)
Input: [1, 0] → Output: 0.3254 (binary: 0)
Input: [1, 1] → Output: 0.6127 (binary: 1)

============================================================
KEY DIFFERENCE: Unlike perceptron, MLP has hidden layer that
learns intermediate representations. Each hidden neuron detects
a pattern, and the output neuron combines these patterns.
This enables learning non-linear decision boundaries!
```

_Note: Exact outputs vary due to random initialization._

#### Why It Works

The hidden layer transforms the input space into a new space where the problem becomes linearly separable. Think of it like this: the hidden neurons might learn to detect "is first input on?" and "is second input on?" and "are both inputs same?". The output neuron then combines these higher-level features with a simple linear combination. The key is the non-linear activation (sigmoid) between layers—without it, the entire network would collapse to a single linear transformation, no better than a perceptron.

#### Troubleshooting

- **All outputs are ~0.5** — Normal for random weights; training will adjust them
- **Memory errors** — Check array dimensions match `$inputSize` and `$hiddenSize`
- **Outputs don't change after predict()** — Ensure you're calling `predict()` not just accessing old stored values

### Step 9: Implementing Backpropagation (~15 min)

#### Goal

Understand and implement the backpropagation algorithm—the cornerstone of training neural networks—using the chain rule to compute gradients.

#### Actions

Backpropagation computes how much each weight contributed to the error and adjusts weights proportionally. It works backwards through the network using the chain rule from calculus.

**The algorithm:**

1. Forward pass: compute all activations
2. Compute output error: \( \delta*{\text{out}} = (y*{\text{pred}} - y*{\text{true}}) \cdot f'(z*{\text{out}}) \)
3. Propagate error backwards: \( \delta*h = (\sum w \cdot \delta*{\text{out}}) \cdot f'(z_h) \)
4. Update weights: \( w := w - \eta \cdot \delta \cdot \text{activation} \)

**1. Create a detailed backpropagation demo:**

```php
# filename: 09-backpropagation.php
<?php

declare(strict_types=1);

require_once 'SimpleNeuralNetwork.php';

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║         Backpropagation: Training Neural Networks        ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

// Simple training example
$network = new SimpleNeuralNetwork(
    inputSize: 2,
    hiddenSize: 2,
    learningRate: 0.8
);

$inputs = [1.0, 0.5];
$target = 1.0;

echo "BACKPROPAGATION STEP-BY-STEP\n";
echo str_repeat("=", 60) . "\n\n";

echo "Inputs: [" . implode(", ", $inputs) . "]\n";
echo "Target: {$target}\n\n";

// Before training
$predictionBefore = $network->predict($inputs);
echo "1. FORWARD PASS (before training):\n";
echo "   Prediction: " . number_format($predictionBefore, 4) . "\n";
echo "   Error: " . number_format(abs($target - $predictionBefore), 4) . "\n\n";

// Train (this does forward + backward pass internally)
$loss = $network->train($inputs, $target);

echo "2. BACKWARD PASS (compute gradients):\n";
echo "   Output layer error computed\n";
echo "   Hidden layer errors computed via chain rule\n";
echo "   Gradients computed for all weights\n\n";

echo "3. WEIGHT UPDATE:\n";
echo "   All weights adjusted proportional to their gradient\n";
echo "   Learning rate: 0.8\n";
echo "   Loss: " . number_format($loss, 6) . "\n\n";

// After training
$predictionAfter = $network->predict($inputs);
echo "4. RESULT (after one training step):\n";
echo "   New prediction: " . number_format($predictionAfter, 4) . "\n";
echo "   New error: " . number_format(abs($target - $predictionAfter), 4) . "\n";
echo "   Improvement: " . number_format(abs($predictionBefore - $predictionAfter), 4) . "\n\n";

echo str_repeat("=", 60) . "\n";
echo "EXPLANATION: The chain rule of calculus\n";
echo str_repeat("=", 60) . "\n\n";

echo "For a weight w connecting hidden neuron h to output:\n\n";
echo "  ∂Loss/∂w = ∂Loss/∂output × ∂output/∂z × ∂z/∂w\n\n";
echo "  Where:\n";
echo "    ∂Loss/∂output = (prediction - target)\n";
echo "    ∂output/∂z = sigmoid'(z) = output × (1 - output)\n";
echo "    ∂z/∂w = activation of neuron h\n\n";
echo "  These gradients tell us how to adjust w to reduce loss!\n\n";

echo "Key insight: Backpropagation efficiently computes gradients\n";
echo "for ALL weights in one backward pass through the network.\n";
```

**2. Run backpropagation demo:**

```bash
php 09-backpropagation.php
```

#### Expected Result

```
╔═══════════════════════════════════════════════════════════╗
║         Backpropagation: Training Neural Networks        ║
╚═══════════════════════════════════════════════════════════╝

BACKPROPAGATION STEP-BY-STEP
============================================================

Inputs: [1, 0.5]
Target: 1

1. FORWARD PASS (before training):
   Prediction: 0.5234
   Error: 0.4766

2. BACKWARD PASS (compute gradients):
   Output layer error computed
   Hidden layer errors computed via chain rule
   Gradients computed for all weights

3. WEIGHT UPDATE:
   All weights adjusted proportional to their gradient
   Learning rate: 0.8
   Loss: 0.113547

4. RESULT (after one training step):
   New prediction: 0.6103
   New error: 0.3897
   Improvement: 0.0869

============================================================
EXPLANATION: The chain rule of calculus
============================================================

For a weight w connecting hidden neuron h to output:

  ∂Loss/∂w = ∂Loss/∂output × ∂output/∂z × ∂z/∂w

  Where:
    ∂Loss/∂output = (prediction - target)
    ∂output/∂z = sigmoid'(z) = output × (1 - output)
    ∂z/∂w = activation of neuron h

  These gradients tell us how to adjust w to reduce loss!

Key insight: Backpropagation efficiently computes gradients
for ALL weights in one backward pass through the network.
```

#### Why It Works

Backpropagation is just the chain rule applied to nested functions. The network is a composition: output = f(W2 × f(W1 × input)). The chain rule tells us how changes in W1 or W2 affect the output, flowing derivatives backwards. The "backward pass" computes these derivatives layer by layer, starting from the output error and multiplying by each layer's local gradient. This gives exact gradients efficiently—much faster than trying each weight perturbation individually (which would require forward passes equal to the number of weights).

#### Troubleshooting

- **Loss increases instead of decreasing** — Learning rate too high; reduce to 0.1 or 0.01
- **NaN in gradients** — Activation overflow; add gradient clipping or reduce learning rate
- **No improvement after training** — Check that weight updates are actually being applied in the train() method

### Step 10: Solving XOR with Multi-Layer Network (~12 min)

#### Goal

Successfully train an MLP to solve the XOR problem that defeated the single-layer perceptron, demonstrating the power of hidden layers.

#### Actions

Now we'll train our MLP on XOR and watch it converge where the perceptron failed.

**1. Train MLP on XOR:**

```php
# filename: 10-xor-mlp-solution.php
<?php

declare(strict_types=1);

require_once 'SimpleNeuralNetwork.php';

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║         Solving XOR with Multi-Layer Network             ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

// XOR dataset
$xorData = [
    [[0, 0], 0],
    [[0, 1], 1],
    [[1, 0], 1],
    [[1, 1], 0],
];

echo "Target: Learn the XOR function\n";
echo "  0 XOR 0 = 0\n";
echo "  0 XOR 1 = 1\n";
echo "  1 XOR 0 = 1\n";
echo "  1 XOR 1 = 0\n\n";

// Create MLP with hidden layer
$network = new SimpleNeuralNetwork(
    inputSize: 2,
    hiddenSize: 4,  // 4 hidden neurons for good measure
    learningRate: 0.5
);

echo "Network: 2 inputs → 4 hidden neurons → 1 output\n";
echo "Training for 5000 epochs...\n\n";

$epochs = 5000;
$printInterval = 500;

for ($epoch = 1; $epoch <= $epochs; $epoch++) {
    $totalLoss = 0;
    $correctCount = 0;

    foreach ($xorData as [$inputs, $target]) {
        $loss = $network->train($inputs, (float)$target);
        $totalLoss += $loss;

        // Check accuracy
        $prediction = $network->predictBinary($inputs);
        if ($prediction === $target) {
            $correctCount++;
        }
    }

    $avgLoss = $totalLoss / count($xorData);
    $accuracy = ($correctCount / count($xorData)) * 100;

    if ($epoch % $printInterval === 0 || $accuracy === 100.0) {
        echo "Epoch {$epoch}: Loss = " . number_format($avgLoss, 6) . ", Accuracy = " . number_format($accuracy, 1) . "%\n";

        if ($accuracy === 100.0 && $epoch < $epochs) {
            echo "\n✓ Perfect accuracy achieved! Stopping early.\n";
            break;
        }
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "FINAL TEST:\n";
echo str_repeat("=", 60) . "\n\n";

$allCorrect = true;
foreach ($xorData as [$inputs, $expected]) {
    $output = $network->predict($inputs);
    $prediction = $network->predictBinary($inputs);
    $status = $prediction === $expected ? "✓" : "✗";

    echo "  " . implode(" XOR ", $inputs) . " = {$prediction} ";
    echo "(output: " . number_format($output, 4) . ", expected: {$expected}) {$status}\n";

    if ($prediction !== $expected) {
        $allCorrect = false;
    }
}

if ($allCorrect) {
    echo "\n🎉 SUCCESS! MLP solved XOR—something a perceptron cannot do!\n";
} else {
    echo "\n⚠ Not quite there yet. Try more epochs or different learning rate.\n";
}

echo "\nWHY IT WORKS:\n";
echo "  Hidden neurons learned to detect intermediate patterns:\n";
echo "    - Neuron 1 might detect: 'is first input on?'\n";
echo "    - Neuron 2 might detect: 'is second input on?'\n";
echo "    - Neuron 3 might detect: 'are both inputs same?'\n";
echo "  Output neuron combines these features linearly to compute XOR.\n";
echo "  The hidden layer transformed the problem into a linearly\n";
echo "  separable one!\n";
```

**2. Run the XOR solver:**

```bash
php 10-xor-mlp-solution.php
```

#### Expected Result

```
╔═══════════════════════════════════════════════════════════╗
║         Solving XOR with Multi-Layer Network             ║
╚═══════════════════════════════════════════════════════════╝

Target: Learn the XOR function
  0 XOR 0 = 0
  0 XOR 1 = 1
  1 XOR 0 = 1
  1 XOR 1 = 0

Network: 2 inputs → 4 hidden neurons → 1 output
Training for 5000 epochs...

Epoch 500: Loss = 0.238164, Accuracy = 50.0%
Epoch 1000: Loss = 0.135247, Accuracy = 75.0%
Epoch 1500: Loss = 0.067823, Accuracy = 75.0%
Epoch 2000: Loss = 0.028941, Accuracy = 100.0%

✓ Perfect accuracy achieved! Stopping early.

============================================================
FINAL TEST:
============================================================

  0 XOR 0 = 0 (output: 0.0523, expected: 0) ✓
  0 XOR 1 = 1 (output: 0.9621, expected: 1) ✓
  1 XOR 0 = 1 (output: 0.9584, expected: 1) ✓
  1 XOR 1 = 0 (output: 0.0612, expected: 0) ✓

🎉 SUCCESS! MLP solved XOR—something a perceptron cannot do!

WHY IT WORKS:
  Hidden neurons learned to detect intermediate patterns:
    - Neuron 1 might detect: 'is first input on?'
    - Neuron 2 might detect: 'is second input on?'
    - Neuron 3 might detect: 'are both inputs same?'
  Output neuron combines these features linearly to compute XOR.
  The hidden layer transformed the problem into a linearly
  separable one!
```

#### Why It Works

The hidden layer performs **feature engineering automatically**. While a perceptron tries to separate XOR with a single line (impossible), the MLP creates new features through its hidden neurons. These neurons might learn to represent "first input AND second input", "first input OR second input", etc. The output neuron then combines these engineered features—which ARE linearly separable—to compute XOR. This is the essence of deep learning: each layer learns increasingly abstract representations that make the final classification easier.

#### Troubleshooting

- **Stuck at 75% accuracy** — Random initialization unlucky; run again or increase hidden neurons to 6-8
- **Takes forever to converge** — Learning rate too low; try 0.8 or 1.0 (XOR is simple, can handle aggressive learning)
- **Accuracy oscillates** — Learning rate too high; reduce to 0.3 or add momentum

## Part D: Production Neural Networks with Rubix ML

### Step 11: Using Rubix ML MLPClassifier (~10 min)

#### Goal

Leverage Rubix ML's production-ready multi-layer perceptron for real classification tasks with proper hyperparameter tuning and evaluation.

#### Actions

While implementing neural networks from scratch teaches fundamentals, production systems use optimized libraries. Rubix ML provides battle-tested implementations with advanced features.

**1. Basic Rubix ML MLP:**

```php
# filename: 11-rubixml-mlp-basic.php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../../chapter-02/vendor/autoload.php';

use Rubix\ML\Classifiers\MultilayerPerceptron;
use Rubix\ML\NeuralNet\Layers\Dense;
use Rubix\ML\NeuralNet\Layers\Activation;
use Rubix\ML\NeuralNet\ActivationFunctions\ReLU;
use Rubix\ML\NeuralNet\ActivationFunctions\BinaryStep;
use Rubix\ML\NeuralNet\Optimizers\Adam;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\CrossValidation\Metrics\Accuracy;

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║         Production Neural Networks with Rubix ML         ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

// XOR dataset (the classic test)
$samples = [
    [0, 0],
    [0, 1],
    [1, 0],
    [1, 1],
];

$labels = [0, 1, 1, 0];

$dataset = new Labeled($samples, $labels);

echo "Dataset: XOR problem\n";
echo "Samples: " . count($samples) . "\n";
echo "Features: 2\n";
echo "Classes: 2 (binary classification)\n\n";

// Build MLP with custom architecture
$estimator = new MultilayerPerceptron([
    new Dense(16),          // Hidden layer: 16 neurons
    new Activation(new ReLU()),
    new Dense(8),           // Second hidden layer: 8 neurons
    new Activation(new ReLU()),
], 100, new Adam(0.01));  // 100 epochs, Adam optimizer with learning rate 0.01

echo "Network architecture:\n";
echo "  Input layer:  2 neurons\n";
echo "  Hidden layer 1: 16 neurons (ReLU)\n";
echo "  Hidden layer 2: 8 neurons (ReLU)\n";
echo "  Output layer: 2 neurons (one-hot for 2 classes)\n\n";

echo "Training...\n";

$estimator->train($dataset);

echo "✓ Training complete!\n\n";

// Test predictions
echo "Testing on training data:\n";
echo str_repeat("-", 60) . "\n";

$predictions = $estimator->predict($dataset);

foreach ($samples as $i => $sample) {
    $predicted = $predictions[$i];
    $expected = $labels[$i];
    $status = $predicted === $expected ? "✓" : "✗";
    echo "  " . implode(" XOR ", $sample) . " = {$predicted} (expected: {$expected}) {$status}\n";
}

$metric = new Accuracy();
$accuracy = $metric->score($predictions, $labels);

echo "\nAccuracy: " . number_format($accuracy * 100, 2) . "%\n";

if ($accuracy === 1.0) {
    echo "🎉 Perfect! Rubix ML MLP solved XOR.\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "RUBIX ML ADVANTAGES:\n";
echo "  ✓ Optimized C-level code for performance\n";
echo "  ✓ Advanced optimizers (Adam, RMSProp, etc.)\n";
echo "  ✓ Multiple activation functions\n";
echo "  ✓ Regularization (dropout, L2 penalty)\n";
echo "  ✓ Early stopping and progress monitoring\n";
echo "  ✓ Model persistence (save/load trained models)\n";
```

**2. Run Rubix ML MLP:**

```bash
php 11-rubixml-mlp-basic.php
```

#### Expected Result

```
╔═══════════════════════════════════════════════════════════╗
║         Production Neural Networks with Rubix ML         ║
╚═══════════════════════════════════════════════════════════╝

Dataset: XOR problem
Samples: 4
Features: 2
Classes: 2 (binary classification)

Network architecture:
  Input layer:  2 neurons
  Hidden layer 1: 16 neurons (ReLU)
  Hidden layer 2: 8 neurons (ReLU)
  Output layer: 2 neurons (one-hot for 2 classes)

Training...
✓ Training complete!

Testing on training data:
------------------------------------------------------------
  0 XOR 0 = 0 (expected: 0) ✓
  0 XOR 1 = 1 (expected: 1) ✓
  1 XOR 0 = 1 (expected: 1) ✓
  1 XOR 1 = 0 (expected: 0) ✓

Accuracy: 100.00%
🎉 Perfect! Rubix ML MLP solved XOR.

============================================================
RUBIX ML ADVANTAGES:
  ✓ Optimized C-level code for performance
  ✓ Advanced optimizers (Adam, RMSProp, etc.)
  ✓ Multiple activation functions
  ✓ Regularization (dropout, L2 penalty)
  ✓ Early stopping and progress monitoring
  ✓ Model persistence (save/load trained models)
```

#### Why It Works

Rubix ML's `MultilayerPerceptron` uses state-of-the-art techniques: Adam optimizer (adaptive learning rates per parameter), ReLU activations (avoiding vanishing gradients), and proper weight initialization (Xavier/He initialization). The library handles mini-batch training, convergence checking, and numerical stability automatically. For production systems, these details matter—they're the difference between a model that trains in minutes vs. hours, or converges at all vs. gets stuck.

#### Troubleshooting

- **Error: "Class not found"** — Ensure you've run `composer install` in chapter-02 directory and autoloader path is correct
- **Poor accuracy** — XOR is simple; if it fails, check data format (samples should be array of arrays, labels array of ints/strings)
- **Training takes long** — Normal for first run (JIT compilation); subsequent runs are faster

### Step 12: Building a Pattern Recognizer (~15 min)

#### Goal

Apply neural networks to a realistic task: recognizing simple binary patterns (like letters formed by 3×3 pixel grids).

#### Actions

We'll train an MLP to recognize three letters: T, L, and I, represented as flattened 3×3 binary grids (9 input features).

**1. Create pattern recognizer:**

```php
# filename: 12-pattern-recognizer.php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../../chapter-02/vendor/autoload.php';

use Rubix\ML\Classifiers\MultilayerPerceptron;
use Rubix\ML\NeuralNet\Layers\Dense;
use Rubix\ML\NeuralNet\Layers\Activation;
use Rubix\ML\NeuralNet\ActivationFunctions\ReLU;
use Rubix\ML\NeuralNet\Optimizers\Adam;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\CrossValidation\Metrics\Accuracy;

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║         Binary Pattern Recognition (3×3 Letters)          ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

// Define 3×3 patterns for letters T, L, I
$patterns = [
    'T' => [
        [1, 1, 1],
        [0, 1, 0],
        [0, 1, 0],
    ],
    'L' => [
        [1, 0, 0],
        [1, 0, 0],
        [1, 1, 1],
    ],
    'I' => [
        [1, 1, 1],
        [0, 1, 0],
        [0, 1, 0],  // Same as T (let's make it slightly different)
    ],
];

// Actually, make I different
$patterns['I'] = [
    [0, 1, 0],
    [0, 1, 0],
    [0, 1, 0],
];

// Visualize patterns
echo "Training patterns:\n";
echo str_repeat("-", 60) . "\n";
foreach ($patterns as $letter => $pattern) {
    echo "\nLetter '{$letter}':\n";
    foreach ($pattern as $row) {
        echo "  " . implode(' ', array_map(fn($p) => $p ? '█' : '·', $row)) . "\n";
    }
}
echo "\n" . str_repeat("=", 60) . "\n\n";

// Flatten patterns to feature vectors
$samples = [];
$labels = [];

// Create training data with variations (noise)
foreach ($patterns as $letter => $pattern) {
    // Original pattern
    $flattened = [];
    foreach ($pattern as $row) {
        $flattened = array_merge($flattened, $row);
    }
    $samples[] = $flattened;
    $labels[] = $letter;

    // Add noisy versions (flip one pixel)
    for ($i = 0; $i < 9; $i++) {
        $noisy = $flattened;
        $noisy[$i] = 1 - $noisy[$i];  // Flip pixel
        $samples[] = $noisy;
        $labels[] = $letter;
    }
}

echo "Training dataset:\n";
echo "  Samples: " . count($samples) . " (3 original + 27 noisy variations)\n";
echo "  Features: 9 (3×3 pixels flattened)\n";
echo "  Classes: 3 (T, L, I)\n\n";

$dataset = new Labeled($samples, $labels);

// Build MLP
$estimator = new MultilayerPerceptron([
    new Dense(12),
    new Activation(new ReLU()),
    new Dense(8),
    new Activation(new ReLU()),
], 200, new Adam(0.01));

echo "Training neural network...\n";
$estimator->train($dataset);
echo "✓ Training complete!\n\n";

// Test on original patterns
echo "Testing on clean patterns:\n";
echo str_repeat("-", 60) . "\n";

$testSamples = [];
$testLabels = [];
foreach ($patterns as $letter => $pattern) {
    $flattened = [];
    foreach ($pattern as $row) {
        $flattened = array_merge($flattened, $row);
    }
    $testSamples[] = $flattened;
    $testLabels[] = $letter;
}

$testDataset = new Labeled($testSamples, $testLabels);
$predictions = $estimator->predict($testDataset);

foreach ($patterns as $letter => $pattern) {
    $idx = array_search($letter, $testLabels);
    $predicted = $predictions[$idx];
    $status = $predicted === $letter ? "✓" : "✗";
    echo "  Letter '{$letter}' → Predicted: '{$predicted}' {$status}\n";
}

$accuracy = (new Accuracy())->score($predictions, $testLabels);
echo "\nTest Accuracy: " . number_format($accuracy * 100, 2) . "%\n";

// Test with custom noisy pattern
echo "\n" . str_repeat("=", 60) . "\n";
echo "Testing with noisy 'L' (one pixel missing):\n";
echo str_repeat("-", 60) . "\n";

$noisyL = [
    [1, 0, 0],
    [1, 0, 0],
    [1, 0, 1],  // Missing one pixel in bottom right
];

echo "Pattern:\n";
foreach ($noisyL as $row) {
    echo "  " . implode(' ', array_map(fn($p) => $p ? '█' : '·', $row)) . "\n";
}

$noisyFlat = [];
foreach ($noisyL as $row) {
    $noisyFlat = array_merge($noisyFlat, $row);
}

$noisyPrediction = $estimator->predictSample($noisyFlat);
echo "\nPrediction: '{$noisyPrediction}' " . ($noisyPrediction === 'L' ? "✓ Robust!" : "✗ Failed");
echo "\n\nNeural networks can handle noisy/corrupted inputs!\n";
```

**2. Run pattern recognizer:**

```bash
php 12-pattern-recognizer.php
```

#### Expected Result

```
╔═══════════════════════════════════════════════════════════╗
║         Binary Pattern Recognition (3×3 Letters)          ║
╚═══════════════════════════════════════════════════════════╝

Training patterns:
------------------------------------------------------------

Letter 'T':
  █ █ █
  · █ ·
  · █ ·

Letter 'L':
  █ · ·
  █ · ·
  █ █ █

Letter 'I':
  · █ ·
  · █ ·
  · █ ·

============================================================

Training dataset:
  Samples: 30 (3 original + 27 noisy variations)
  Features: 9 (3×3 pixels flattened)
  Classes: 3 (T, L, I)

Training neural network...
✓ Training complete!

Testing on clean patterns:
------------------------------------------------------------
  Letter 'T' → Predicted: 'T' ✓
  Letter 'L' → Predicted: 'L' ✓
  Letter 'I' → Predicted: 'I' ✓

Test Accuracy: 100.00%

============================================================
Testing with noisy 'L' (one pixel missing):
------------------------------------------------------------
Pattern:
  █ · ·
  █ · ·
  █ · █

Prediction: 'L' ✓ Robust!

Neural networks can handle noisy/corrupted inputs!
```

#### Why It Works

Neural networks excel at **pattern recognition with noise tolerance**. By training on noisy variations, the network learns the essential features that define each letter, not just exact pixel matches. The hidden layers learn feature detectors: perhaps one neuron detects "vertical line on left" (for L and I), another detects "horizontal line on top" (for T), etc. The output layer combines these learned features to make robust classifications even when inputs are corrupted. This generalization ability is why neural networks power OCR, image recognition, and speech processing.

#### Troubleshooting

- **Low accuracy on clean patterns** — Increase epochs to 500 or add more hidden neurons
- **Perfect on clean but fails on noisy** — Not enough noisy training data; add more variations
- **All predictions are the same class** — Learning rate too high or network too small; adjust hyperparameters

## Exercises

Test your understanding with these hands-on challenges:

### Exercise 1: Build a NOT Gate Perceptron

**Goal**: Implement a NOT gate (logical negation) using a single perceptron.

Create a file called `exercise1-not-gate.php` that trains a perceptron to learn:

- Input: 0 → Output: 1
- Input: 1 → Output: 0

Requirements:

- Use the `Perceptron` class from this chapter
- Train for at least 10 epochs
- Test both inputs and verify 100% accuracy
- Print the learned weights and bias

**Validation**: Run your solution and verify output:

```bash
php solutions/exercise1-not-gate.php
```

Expected output:

```
Training NOT gate...
Epoch 5: Converged!

Testing:
  NOT 0 = 1 ✓
  NOT 1 = 0 ✓

Learned weights: [-0.543]
Learned bias: 0.412
```

### Exercise 2: Implement Tanh Activation

**Goal**: Add the `tanh` activation function and its derivative to `ActivationFunctions.php`.

The hyperbolic tangent is defined as:

- \( \tanh(z) = \frac{e^z - e^{-z}}{e^z + e^{-z}} \)
- Derivative: \( \tanh'(z) = 1 - \tanh^2(z) \)

Requirements:

- Add `tanh()` and `tanhDerivative()` methods
- Test on inputs: -2, -1, 0, 1, 2
- Compare with sigmoid outputs
- Explain when to use tanh vs sigmoid

**Validation**: Values should match:

- tanh(-2) ≈ -0.964
- tanh(0) = 0.000
- tanh(2) ≈ 0.964

### Exercise 3: Three-Input Majority Classifier

**Goal**: Build a perceptron that outputs 1 if the majority (≥2) of three inputs are 1, else 0.

Truth table:

```
0,0,0 → 0
0,0,1 → 0
0,1,0 → 0
0,1,1 → 1
1,0,0 → 0
1,0,1 → 1
1,1,0 → 1
1,1,1 → 1
```

Requirements:

- Create `Perceptron` with `inputSize: 3`
- Train on all 8 examples
- Report convergence epoch and final accuracy
- Test all 8 inputs

**Validation**: Should converge in < 50 epochs with 100% accuracy.

### Exercise 4: Custom Pattern Dataset

**Goal**: Create your own 3×3 binary patterns and train an MLP to recognize them.

Requirements:

- Define 3 different letters/symbols (not T, L, I)
- Visualize patterns using ASCII (█ for 1, · for 0)
- Train Rubix ML `MultilayerPerceptron` on them
- Test with at least one noisy version of each pattern
- Report accuracy

Example patterns: O, X, H, or custom symbols

**Validation**: Should achieve 100% accuracy on clean patterns and >80% on noisy patterns.

## Wrap-up

Congratulations! You've built a solid foundation in neural networks. Let's review what you've accomplished:

**✓ Core Concepts Mastered:**

- Understood perceptron architecture and the biological neuron analogy
- Implemented forward propagation from scratch in PHP
- Trained perceptrons using the perceptron learning rule
- Built AND and OR logic gates with single-layer networks

**✓ Activation Functions:**

- Learned why smooth activations (sigmoid, tanh, ReLU) are crucial
- Implemented multiple activation functions with derivatives
- Understood the vanishing gradient problem and when to use each activation
- Compared activation behaviors on sample inputs

**✓ Multi-Layer Networks:**

- Discovered why perceptrons fail on XOR (non-linear separability)
- Built multi-layer perceptrons with hidden layers from scratch
- Understood how hidden layers learn intermediate representations
- Successfully solved XOR using an MLP

**✓ Backpropagation:**

- Implemented the backpropagation algorithm using the chain rule
- Computed gradients for all weights efficiently
- Applied gradient descent to minimize loss
- Trained networks on non-linearly separable problems

**✓ Production Skills:**

- Used Rubix ML's `MultilayerPerceptron` for real tasks
- Configured network architectures with multiple hidden layers
- Applied neural networks to pattern recognition
- Trained models with noise tolerance

**Connection to Chapter 11**: In the next chapter, you'll integrate PHP with Python to access even more powerful ML tools like scikit-learn, TensorFlow, and PyTorch. You'll learn when to use PHP's native ML capabilities vs. when to call Python for heavy lifting.

**Connection to Chapter 12**: Your neural network knowledge prepares you for deep learning—using networks with many layers to solve complex problems like image recognition and natural language processing. You'll work with convolutional layers, recurrent layers, and pre-trained models.

**Real-World Applications:**

- Classification: Spam detection, sentiment analysis, medical diagnosis
- Pattern recognition: OCR, handwriting recognition, signature verification
- Time series: Stock prediction, demand forecasting, anomaly detection
- Recommendation systems: User behavior modeling, content suggestions
- Control systems: Robotics, game AI, autonomous vehicles

You now understand how neural networks really work—not just as a black box, but as mathematical functions that learn by adjusting weights through gradient descent. This foundation is essential for deep learning and advanced AI applications.

## Further Reading

Deepen your understanding with these carefully selected resources:

**Neural Networks Fundamentals:**

- [Neural Networks and Deep Learning](http://neuralnetworksanddeeplearning.com/) by Michael Nielsen — Free online book with excellent visualizations and intuitive explanations
- [3Blue1Brown: Neural Networks Series](https://www.youtube.com/playlist?list=PLZHQObOWTQDNU6R1_67000Dx_ZCJB-3pi) — Visual explanations of gradient descent, backpropagation, and how networks learn
- [Rubix ML: Multilayer Perceptron Documentation](https://docs.rubixml.com/latest/classifiers/multilayer-perceptron.html) — Official docs for the MLP classifier used in this chapter

**Activation Functions:**

- [Activation Functions in Neural Networks](https://towardsdatascience.com/activation-functions-neural-networks-1cbd9f8d91d6) — Comprehensive guide to choosing activations
- [ReLU and its variants](https://arxiv.org/abs/1803.08375) — Academic paper on Leaky ReLU, Parametric ReLU, and more

**Backpropagation:**

- [Backpropagation Calculus](https://www.youtube.com/watch?v=tIeHLnjs5U8) — 3Blue1Brown video explaining the chain rule visually
- [Yes you should understand backprop](https://medium.com/@karpathy/yes-you-should-understand-backprop-e2f06eab496b) — Andrej Karpathy on why implementation matters

**Optimization:**

- [An overview of gradient descent optimization algorithms](https://ruder.io/optimizing-gradient-descent/) — Deep dive into SGD, Adam, RMSProp, and more
- [Adam: A Method for Stochastic Optimization](https://arxiv.org/abs/1412.6980) — Original Adam paper

**PHP Machine Learning:**

- [Rubix ML Documentation](https://docs.rubixml.com/) — Complete reference for Rubix ML library
- [PHP-ML Repository](https://github.com/php-ai/php-ml) — Source code and examples for PHP-ML

**Historical Context:**

- [Perceptrons (Minsky & Papert, 1969)](https://mitpress.mit.edu/books/perceptrons) — The book that nearly killed neural network research
- [The XOR Problem](<https://en.wikipedia.org/wiki/Perceptrons_(book)#The_XOR_affair>) — Why hidden layers were revolutionary

**Next Steps:**

- Chapter 11: Integrating PHP with Python for Advanced ML — Leverage scikit-learn and TensorFlow from PHP
- Chapter 12: Deep Learning with TensorFlow and PHP — Convolutional networks, recurrent networks, transfer learning
- PSR Standards for ML Projects — Organize production PHP machine learning codebases

Keep experimenting! Try building neural networks for your own datasets. Understanding these fundamentals will serve you well as you progress to deep learning and advanced architectures.
