---
title: "Appendix C: Math Refresher for Machine Learning"
description: "Essential mathematics concepts for understanding ML algorithms"
series: "php-ml"
appendix: "C"
---

# Appendix C: Math Refresher for Machine Learning

Essential math concepts you need to understand machine learning.

## Statistics Basics

### Mean (Average)

```php
function mean(array $numbers): float
{
    return array_sum($numbers) / count($numbers);
}

$data = [2, 4, 6, 8, 10];
echo mean($data);  // 6
```

### Median (Middle Value)

```php
function median(array $numbers): float
{
    sort($numbers);
    $count = count($numbers);
    $middle = floor($count / 2);

    if ($count % 2 == 0) {
        return ($numbers[$middle - 1] + $numbers[$middle]) / 2;
    }

    return $numbers[$middle];
}
```

### Variance and Standard Deviation

```php
function variance(array $numbers): float
{
    $mean = array_sum($numbers) / count($numbers);
    $squaredDiffs = array_map(
        fn($x) => ($x - $mean) ** 2,
        $numbers
    );
    return array_sum($squaredDiffs) / count($numbers);
}

function standardDeviation(array $numbers): float
{
    return sqrt(variance($numbers));
}
```

## Distance Metrics

### Euclidean Distance

```php
function euclideanDistance(array $a, array $b): float
{
    $sum = 0;
    for ($i = 0; $i < count($a); $i++) {
        $sum += ($a[$i] - $b[$i]) ** 2;
    }
    return sqrt($sum);
}

// Distance between [1, 2] and [4, 6]
echo euclideanDistance([1, 2], [4, 6]);  // 5
```

### Manhattan Distance

```php
function manhattanDistance(array $a, array $b): float
{
    $sum = 0;
    for ($i = 0; $i < count($a); $i++) {
        $sum += abs($a[$i] - $b[$i]);
    }
    return $sum;
}
```

## Linear Algebra

### Dot Product

```php
function dotProduct(array $a, array $b): float
{
    $sum = 0;
    for ($i = 0; $i < count($a); $i++) {
        $sum += $a[$i] * $b[$i];
    }
    return $sum;
}

echo dotProduct([1, 2, 3], [4, 5, 6]);  // 32
```

### Matrix Multiplication

```php
function matrixMultiply(array $a, array $b): array
{
    $result = [];
    foreach ($a as $i => $row) {
        $result[$i] = [];
        for ($j = 0; $j < count($b[0]); $j++) {
            $sum = 0;
            for ($k = 0; $k < count($b); $k++) {
                $sum += $row[$k] * $b[$k][$j];
            }
            $result[$i][$j] = $sum;
        }
    }
    return $result;
}
```

## Probability

### Bayes' Theorem

```
P(A|B) = P(B|A) × P(A) / P(B)

Where:
- P(A|B) = Probability of A given B
- P(B|A) = Probability of B given A
- P(A) = Prior probability of A
- P(B) = Prior probability of B
```

Example (Spam Detection):
```php
// P(Spam | "free") = ?
// P("free" | Spam) = 0.8 (80% of spam contains "free")
// P(Spam) = 0.3 (30% of all emails are spam)
// P("free") = 0.4 (40% of all emails contain "free")

$pSpamGivenFree = (0.8 * 0.3) / 0.4;  // 0.6 or 60%
```

## Normalization

### Min-Max Scaling (0 to 1)

```php
function normalize(array $data): array
{
    $min = min($data);
    $max = max($data);
    $range = $max - $min;

    return array_map(
        fn($x) => ($x - $min) / $range,
        $data
    );
}

$ages = [20, 30, 40, 50];
print_r(normalize($ages));
// [0, 0.33, 0.67, 1.0]
```

### Z-Score Standardization

```php
function standardize(array $data): array
{
    $mean = array_sum($data) / count($data);
    $std = standardDeviation($data);

    return array_map(
        fn($x) => ($x - $mean) / $std,
        $data
    );
}
```

## Logarithms

```php
// Natural logarithm (base e)
log($x);  

// Base-10 logarithm
log10($x);

// Custom base logarithm
log($x) / log($base);

// Why logarithms in ML?
// - Convert multiplication to addition
// - Compress large ranges
// - Used in entropy, information gain
```

## Exponentials

```php
// e^x
exp($x);

// a^b
pow($a, $b);

// Used in:
// - Sigmoid activation: 1 / (1 + exp(-x))
// - Softmax: exp(x) / sum(exp(x))
```

## Derivatives (Gradients)

```
d/dx (x²) = 2x
d/dx (x³) = 3x²
d/dx (e^x) = e^x
d/dx (log(x)) = 1/x

Used in:
- Gradient descent optimization
- Backpropagation in neural networks
```

## Common ML Formulas

### Sigmoid Function

```php
function sigmoid(float $x): float
{
    return 1 / (1 + exp(-$x));
}

// Maps any value to (0, 1)
// Used in: logistic regression, neural networks
```

### Cross-Entropy Loss

```php
function crossEntropy(array $actual, array $predicted): float
{
    $loss = 0;
    for ($i = 0; $i < count($actual); $i++) {
        $loss -= $actual[$i] * log($predicted[$i]);
    }
    return $loss;
}

// Measures prediction error in classification
```

### Mean Squared Error (MSE)

```php
function mse(array $actual, array $predicted): float
{
    $sumSquaredErrors = 0;
    for ($i = 0; $i < count($actual); $i++) {
        $error = $actual[$i] - $predicted[$i];
        $sumSquaredErrors += $error ** 2;
    }
    return $sumSquaredErrors / count($actual);
}

// Used in regression problems
```

## Tips for Understanding Math in ML

1. **Don't memorize formulas** — Understand what they represent
2. **Visualize concepts** — Draw graphs and diagrams
3. **Code implementations** — Writing code solidifies understanding
4. **Start simple** — Master basics before advanced topics
5. **Focus on intuition** — Why does this formula make sense?

---

**Related:**
- [Khan Academy: Statistics](https://www.khanacademy.org/math/statistics-probability)
- [3Blue1Brown: Linear Algebra](https://www.youtube.com/playlist?list=PLZHQObOWTQDPD3MizzM2xVFitgF8hE_ab)
- [StatQuest: ML Math](https://www.youtube.com/user/joshstarmer)
