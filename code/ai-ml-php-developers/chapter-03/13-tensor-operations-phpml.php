<?php

declare(strict_types=1);

/**
 * Tensor-like Operations with PHP-ML
 * 
 * This example demonstrates how to perform tensor/matrix operations
 * using PHP-ML as an alternative to the Tensor extension.
 * 
 * Note: The PECL Tensor extension does not support PHP 8.4 as of 2025.
 * This example shows how PHP-ML can be used for similar operations.
 * 
 * Prerequisites:
 * - PHP 8.4+
 * - PHP-ML: composer require php-ai/php-ml
 */

require_once __DIR__ . '/vendor/autoload.php';

use Phpml\Math\Matrix;
use Phpml\Math\Statistic\Mean;
use Phpml\Math\Statistic\StandardDeviation;

echo "=== Tensor-like Operations with PHP-ML ===\n\n";

// ============================================================================
// 1. Matrix Creation (Tensor Basics)
// ============================================================================
echo "1. Creating Matrices (2D Tensors)\n";
echo str_repeat("-", 50) . "\n";

$matrix1 = new Matrix([
    [1, 2, 3],
    [4, 5, 6],
    [7, 8, 9]
]);

echo "Matrix 1:\n";
printMatrix($matrix1);

$matrix2 = new Matrix([
    [9, 8, 7],
    [6, 5, 4],
    [3, 2, 1]
]);

echo "\nMatrix 2:\n";
printMatrix($matrix2);

// ============================================================================
// 2. Matrix Arithmetic Operations
// ============================================================================
echo "\n\n2. Matrix Addition\n";
echo str_repeat("-", 50) . "\n";

// Element-wise addition
$sum = matrixAdd($matrix1, $matrix2);
echo "Matrix 1 + Matrix 2:\n";
printMatrix($sum);

// ============================================================================
// 3. Matrix Multiplication
// ============================================================================
echo "\n\n3. Matrix Multiplication\n";
echo str_repeat("-", 50) . "\n";

$matrix3 = new Matrix([
    [1, 2],
    [3, 4],
    [5, 6]
]);

$matrix4 = new Matrix([
    [7, 8, 9],
    [10, 11, 12]
]);

$product = $matrix3->multiply($matrix4);
echo "Matrix 3 (3x2):\n";
printMatrix($matrix3);
echo "\nMatrix 4 (2x3):\n";
printMatrix($matrix4);
echo "\nMatrix 3 × Matrix 4:\n";
printMatrix($product);

// ============================================================================
// 4. Matrix Transpose
// ============================================================================
echo "\n\n4. Matrix Transpose\n";
echo str_repeat("-", 50) . "\n";

$original = new Matrix([
    [1, 2, 3],
    [4, 5, 6]
]);

$transposed = $original->transpose();
echo "Original Matrix (2x3):\n";
printMatrix($original);
echo "\nTransposed Matrix (3x2):\n";
printMatrix($transposed);

// ============================================================================
// 5. Determinant Calculation
// ============================================================================
echo "\n\n5. Matrix Determinant\n";
echo str_repeat("-", 50) . "\n";

$squareMatrix = new Matrix([
    [4, 3],
    [2, 1]
]);

echo "Square Matrix:\n";
printMatrix($squareMatrix);
echo "\nDeterminant: " . $squareMatrix->getDeterminant() . "\n";

// ============================================================================
// 6. Matrix Inverse
// ============================================================================
echo "\n\n6. Matrix Inverse\n";
echo str_repeat("-", 50) . "\n";

try {
    $inverse = $squareMatrix->inverse();
    echo "Inverse Matrix:\n";
    printMatrix($inverse);

    // Verify: A × A⁻¹ = I (Identity Matrix)
    $identity = $squareMatrix->multiply($inverse);
    echo "\nVerification (A × A⁻¹ should equal Identity):\n";
    printMatrix($identity);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// ============================================================================
// 7. Statistical Operations on Matrices
// ============================================================================
echo "\n\n7. Statistical Operations\n";
echo str_repeat("-", 50) . "\n";

$dataMatrix = new Matrix([
    [1.5, 2.3, 3.7],
    [4.2, 5.8, 6.1],
    [7.9, 8.4, 9.6]
]);

echo "Data Matrix:\n";
printMatrix($dataMatrix);

// Calculate mean for each row
echo "\nRow-wise statistics:\n";
foreach ($dataMatrix->toArray() as $i => $row) {
    $mean = Mean::arithmetic($row);
    $stdDev = StandardDeviation::population($row);
    echo "Row $i: Mean = " . number_format($mean, 2) .
        ", Std Dev = " . number_format($stdDev, 2) . "\n";
}

// Calculate mean for each column
echo "\nColumn-wise statistics:\n";
$transposedData = $dataMatrix->transpose();
foreach ($transposedData->toArray() as $i => $col) {
    $mean = Mean::arithmetic($col);
    $stdDev = StandardDeviation::population($col);
    echo "Column $i: Mean = " . number_format($mean, 2) .
        ", Std Dev = " . number_format($stdDev, 2) . "\n";
}

// ============================================================================
// 8. Practical Example: Feature Scaling with Matrix Operations
// ============================================================================
echo "\n\n8. Practical Example: Feature Scaling\n";
echo str_repeat("-", 50) . "\n";

$features = new Matrix([
    [100, 5.6, 3],
    [200, 6.2, 5],
    [150, 5.9, 4],
    [300, 7.1, 8]
]);

echo "Original Features:\n";
echo "   [Value,  Height, Score]\n";
printMatrix($features);

// Normalize each column (feature) to 0-1 range
$normalized = normalizeMatrix($features);
echo "\nNormalized Features (0-1 range):\n";
printMatrix($normalized);

// ============================================================================
// 9. Practical Example: Distance Matrix for Clustering
// ============================================================================
echo "\n\n9. Distance Matrix Calculation\n";
echo str_repeat("-", 50) . "\n";

$points = [
    [1, 2],
    [3, 4],
    [5, 6],
    [7, 8]
];

echo "Points:\n";
foreach ($points as $i => $point) {
    echo "  Point $i: [" . implode(", ", $point) . "]\n";
}

$distanceMatrix = calculateDistanceMatrix($points);
echo "\nEuclidean Distance Matrix:\n";
printMatrix($distanceMatrix);

// ============================================================================
// 10. Creating Tensors for Neural Networks
// ============================================================================
echo "\n\n10. Tensor Shapes for Neural Networks\n";
echo str_repeat("-", 50) . "\n";

// Input layer: batch of 4 samples, 3 features each
$inputTensor = new Matrix([
    [0.1, 0.2, 0.3],
    [0.4, 0.5, 0.6],
    [0.7, 0.8, 0.9],
    [0.2, 0.4, 0.6]
]);

// Weight matrix: 3 inputs to 2 hidden neurons
$weights = new Matrix([
    [0.5, 0.6],
    [0.7, 0.8],
    [0.9, 1.0]
]);

// Forward pass simulation
$hiddenLayer = $inputTensor->multiply($weights);

echo "Input Tensor (4 samples × 3 features):\n";
printMatrix($inputTensor);
echo "\nWeight Matrix (3 inputs × 2 neurons):\n";
printMatrix($weights);
echo "\nHidden Layer Output (4 samples × 2 neurons):\n";
printMatrix($hiddenLayer);

echo "\n=== Summary ===\n";
echo "PHP-ML provides robust matrix operations for:\n";
echo "  ✓ Matrix creation and manipulation\n";
echo "  ✓ Arithmetic operations (add, multiply)\n";
echo "  ✓ Linear algebra (transpose, inverse, determinant)\n";
echo "  ✓ Statistical operations\n";
echo "  ✓ Feature scaling and normalization\n";
echo "  ✓ Distance calculations\n";
echo "  ✓ Neural network tensor operations\n\n";

echo "Note: For production use with PHP 8.4, PHP-ML is recommended\n";
echo "over the Tensor extension which only supports PHP ≤ 8.3\n";

// ============================================================================
// Helper Functions
// ============================================================================

/**
 * Print a matrix in a readable format
 */
function printMatrix(Matrix $matrix): void
{
    foreach ($matrix->toArray() as $row) {
        echo "  [";
        echo implode(", ", array_map(fn($val) => sprintf("%7.3f", $val), $row));
        echo "]\n";
    }
}

/**
 * Element-wise matrix addition
 */
function matrixAdd(Matrix $a, Matrix $b): Matrix
{
    $aArray = $a->toArray();
    $bArray = $b->toArray();
    $result = [];

    for ($i = 0; $i < count($aArray); $i++) {
        $row = [];
        for ($j = 0; $j < count($aArray[$i]); $j++) {
            $row[] = $aArray[$i][$j] + $bArray[$i][$j];
        }
        $result[] = $row;
    }

    return new Matrix($result);
}

/**
 * Normalize matrix columns to 0-1 range
 */
function normalizeMatrix(Matrix $matrix): Matrix
{
    $data = $matrix->toArray();
    $transposed = $matrix->transpose()->toArray();
    $normalized = [];

    // Normalize each column (feature)
    foreach ($transposed as $col) {
        $min = min($col);
        $max = max($col);
        $range = $max - $min;

        $normalizedCol = [];
        foreach ($col as $value) {
            $normalizedCol[] = $range > 0 ? ($value - $min) / $range : 0;
        }
        $normalized[] = $normalizedCol;
    }

    // Transpose back to original shape
    return (new Matrix($normalized))->transpose();
}

/**
 * Calculate Euclidean distance matrix between points
 */
function calculateDistanceMatrix(array $points): Matrix
{
    $n = count($points);
    $distances = [];

    for ($i = 0; $i < $n; $i++) {
        $row = [];
        for ($j = 0; $j < $n; $j++) {
            if ($i === $j) {
                $row[] = 0.0;
            } else {
                $distance = euclideanDistance($points[$i], $points[$j]);
                $row[] = $distance;
            }
        }
        $distances[] = $row;
    }

    return new Matrix($distances);
}

/**
 * Calculate Euclidean distance between two points
 */
function euclideanDistance(array $p1, array $p2): float
{
    $sum = 0;
    for ($i = 0; $i < count($p1); $i++) {
        $sum += ($p1[$i] - $p2[$i]) ** 2;
    }
    return sqrt($sum);
}
