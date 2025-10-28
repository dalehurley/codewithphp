# Chapter 10: Neural Networks and Deep Learning Fundamentals

Complete working examples for building and training neural networks in PHP.

## Prerequisites

- PHP 8.4+ installed
- Composer (optional, for Rubix ML examples)
- Understanding of basic ML concepts from Chapter 3

## Quick Start

```bash
# Test basic perceptron
php 01-perceptron-basics.php

# Train AND/OR gates
php 02-logic-gates-and-or.php

# Solve XOR with MLP
php 10-xor-mlp-solution.php
```

## File Organization

### Core Classes

- `Perceptron.php` — Single-layer perceptron implementation
- `ActivationFunctions.php` — Sigmoid, tanh, ReLU, and derivatives
- `SimpleNeuralNetwork.php` — Basic 2-layer MLP with backpropagation
- `helpers.php` — Utility functions

### Examples (01-05): Perceptron Basics

- `01-perceptron-basics.php` — Forward propagation demo
- `02-logic-gates-and-or.php` — Train AND/OR gates
- `03-perceptron-training.php` — Training loop demonstration
- `04-activation-functions.php` — Activation function comparison
- `05-activation-comparison.php` — Visual activation behavior

### Examples (06-10): Multi-Layer Networks

- `06-xor-problem.php` — Why perceptrons fail on XOR
- `07-simple-mlp-scratch.php` — MLP architecture demo
- `08-forward-propagation.php` — Layer-by-layer forward pass
- `09-backpropagation.php` — Backprop algorithm demo
- `10-xor-mlp-solution.php` — Solve XOR with hidden layer ⭐

### Examples (11-14): Production Networks

- `11-rubixml-mlp-basic.php` — Rubix ML MLPClassifier (requires installation)
- `12-pattern-recognizer.php` — Binary pattern classification (requires Rubix ML)
- `13-learning-rate-tuning.php` — Learning rate comparison
- `14-convergence-analysis.php` — Track loss over epochs

### Data Files

- `data/xor-dataset.csv` — XOR truth table
- `data/logic-gates.csv` — AND, OR, XOR truth tables
- `data/patterns.csv` — 3×3 binary patterns (T, L, I)

### Solutions

- `solutions/exercise1-not-gate.php` — NOT gate implementation
- `solutions/exercise2-tanh.php` — Tanh activation function
- `solutions/exercise3-majority.php` — 3-input majority classifier
- `solutions/exercise4-custom-patterns.php` — Custom pattern recognition

## Running Examples

### Basic Examples (No Dependencies)

Most examples run standalone:

```bash
php 01-perceptron-basics.php
php 02-logic-gates-and-or.php
php 03-perceptron-training.php
php 04-activation-functions.php
php 06-xor-problem.php
php 10-xor-mlp-solution.php
```

### Rubix ML Examples (Requires Installation)

For examples 11-12, install Rubix ML first:

```bash
cd ../chapter-02
composer install
cd ../chapter-10
php 11-rubixml-mlp-basic.php
```

### Running All Examples

```bash
for file in 0*.php; do
    echo "Running $file..."
    php "$file"
    echo ""
    read -p "Press Enter to continue..."
done
```

## Expected Behavior

### Perceptron (Examples 01-04)

- **01**: Shows random predictions (weights not trained)
- **02**: AND and OR gates converge in ~5-10 epochs, 100% accuracy
- **03**: Training demo converges in ~5 epochs
- **04**: Displays activation function values and derivatives

### XOR Problem (Examples 06, 10)

- **06**: Perceptron FAILS to learn XOR (stuck at 50-75% accuracy)
- **10**: MLP SUCCEEDS at learning XOR (100% accuracy in ~1000-3000 epochs)

### Solutions

- **Exercise 1 (NOT)**: Converges in 5-10 epochs
- **Exercise 2 (tanh)**: Shows tanh values matching expected results
- **Exercise 3 (majority)**: Converges in 20-40 epochs, 100% accuracy

## Troubleshooting

### Error: "Class not found"

**Problem**: Missing class file

**Solution**: Ensure you're in the `chapter-10` directory and all class files exist:

```bash
ls -l Perceptron.php ActivationFunctions.php SimpleNeuralNetwork.php
```

### XOR Not Converging (Example 10)

**Problem**: Random initialization unlucky

**Solution**:

- Run again (different random initialization)
- Increase epochs to 10000
- Try learning rate 0.8 or 1.0

### Perceptron Gets Stuck

**Problem**: Learning rate too high or too low

**Solution**:

- Too high (oscillating): Reduce to 0.05
- Too low (slow): Increase to 0.5

### Overflow in Sigmoid

**Problem**: Very large negative z values

**Solution**: Already handled in `ActivationFunctions.php` with bounds checking

### Rubix ML Not Found

**Problem**: Library not installed

**Solution**:

```bash
cd ../chapter-02
composer require rubix/ml
cd ../chapter-10
```

## Key Concepts Demonstrated

- ✓ Single-layer perceptron architecture
- ✓ Forward propagation
- ✓ Perceptron learning rule
- ✓ Logic gate implementation (AND, OR)
- ✓ Activation functions (step, sigmoid, tanh, ReLU)
- ✓ Linear separability limitations
- ✓ XOR problem (non-linearly separable)
- ✓ Multi-layer perceptron (MLP)
- ✓ Hidden layers and non-linear decision boundaries
- ✓ Backpropagation algorithm
- ✓ Gradient descent optimization
- ✓ Training convergence
- ✓ Production neural networks with Rubix ML

## Performance Notes

- Perceptron training: < 0.1 seconds
- XOR MLP training (5000 epochs): 1-3 seconds
- Pattern recognition: 2-5 seconds

## Next Steps

After completing these examples:

1. Experiment with different:

   - Network architectures (more/fewer hidden neurons)
   - Learning rates (0.01 to 1.0)
   - Activation functions
   - Training epochs

2. Try your own datasets:

   - Create custom logic functions
   - Design new 3×3 patterns
   - Generate synthetic classification data

3. Progress to Chapter 11: Integrating PHP with Python for more powerful ML libraries

4. Continue to Chapter 12: Deep Learning with TensorFlow and PHP

## Further Experimentation

Try these challenges:

- **Modify XOR solver** to use 2 hidden neurons instead of 4
- **Implement NAND gate** (opposite of AND)
- **Create 4×4 patterns** for more complex shapes
- **Add momentum** to gradient descent
- **Implement early stopping** based on validation accuracy
- **Visualize decision boundaries** by testing many points

## Support

If examples don't work:

1. Check PHP version: `php --version` (must be 8.4+)
2. Verify file permissions: `chmod +x *.php`
3. Check for typos in require_once paths
4. Review error messages carefully
5. Compare your code with provided solutions

## Additional Resources

- [Neural Networks and Deep Learning](http://neuralnetworksanddeeplearning.com/) — Free online book
- [Rubix ML Documentation](https://docs.rubixml.com/) — Official Rubix ML docs
- [3Blue1Brown: Neural Networks](https://www.youtube.com/playlist?list=PLZHQObOWTQDNU6R1_67000Dx_ZCJB-3pi) — Visual explanations
