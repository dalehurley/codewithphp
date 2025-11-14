<?php

declare(strict_types=1);

/**
 * Matrix Chain Multiplication - Interval DP
 *
 * Finds the optimal order to multiply a chain of matrices to minimize
 * the number of scalar multiplications.
 *
 * Time Complexity: O(n³)
 * Space Complexity: O(n²)
 */
class MatrixChainMultiplication
{
    /**
     * Calculate minimum number of scalar multiplications needed
     *
     * @param array<int> $dimensions Array of matrix dimensions
     *                               For matrices A1(10×20), A2(20×30), A3(30×40)
     *                               pass [10, 20, 30, 40]
     * @return int Minimum number of multiplications
     */
    public function minMultiplications(array $dimensions): int
    {
        $n = count($dimensions) - 1;  // Number of matrices

        if ($n < 2) {
            return 0;
        }

        // dp[i][j] = min cost to multiply matrices from i to j
        $dp = array_fill(0, $n, array_fill(0, $n, 0));

        // Length of chain (2, 3, ..., n)
        for ($len = 2; $len <= $n; $len++) {
            for ($i = 0; $i < $n - $len + 1; $i++) {
                $j = $i + $len - 1;
                $dp[$i][$j] = PHP_INT_MAX;

                // Try all possible split points
                for ($k = $i; $k < $j; $k++) {
                    $cost = $dp[$i][$k]
                          + $dp[$k + 1][$j]
                          + $dimensions[$i] * $dimensions[$k + 1] * $dimensions[$j + 1];

                    $dp[$i][$j] = min($dp[$i][$j], $cost);
                }
            }
        }

        return $dp[0][$n - 1];
    }

    /**
     * Get optimal parenthesization showing multiplication order
     *
     * @param array<int> $dimensions Matrix dimensions
     * @return string Optimal parenthesization (e.g., "((M1 × M2) × (M3 × M4))")
     */
    public function optimalParenthesization(array $dimensions): string
    {
        $n = count($dimensions) - 1;

        if ($n < 2) {
            return $n === 1 ? "M1" : "";
        }

        $dp = array_fill(0, $n, array_fill(0, $n, 0));
        $split = array_fill(0, $n, array_fill(0, $n, 0));

        for ($len = 2; $len <= $n; $len++) {
            for ($i = 0; $i < $n - $len + 1; $i++) {
                $j = $i + $len - 1;
                $dp[$i][$j] = PHP_INT_MAX;

                for ($k = $i; $k < $j; $k++) {
                    $cost = $dp[$i][$k]
                          + $dp[$k + 1][$j]
                          + $dimensions[$i] * $dimensions[$k + 1] * $dimensions[$j + 1];

                    if ($cost < $dp[$i][$j]) {
                        $dp[$i][$j] = $cost;
                        $split[$i][$j] = $k;
                    }
                }
            }
        }

        return $this->buildParenthesization($split, 0, $n - 1);
    }

    /**
     * Recursively build parenthesization string
     *
     * @param array<array<int>> $split Split point matrix
     * @param int $i Start index
     * @param int $j End index
     * @return string Parenthesization substring
     */
    private function buildParenthesization(array $split, int $i, int $j): string
    {
        if ($i === $j) {
            return "M" . ($i + 1);
        }

        $k = $split[$i][$j];
        $left = $this->buildParenthesization($split, $i, $k);
        $right = $this->buildParenthesization($split, $k + 1, $j);

        return "({$left} × {$right})";
    }

    /**
     * Get detailed breakdown of computation steps
     *
     * @param array<int> $dimensions Matrix dimensions
     * @return array{min_cost: int, order: string, steps: array}
     */
    public function getDetailedSolution(array $dimensions): array
    {
        $n = count($dimensions) - 1;
        $steps = [];

        $dp = array_fill(0, $n, array_fill(0, $n, 0));
        $split = array_fill(0, $n, array_fill(0, $n, 0));

        for ($len = 2; $len <= $n; $len++) {
            for ($i = 0; $i < $n - $len + 1; $i++) {
                $j = $i + $len - 1;
                $dp[$i][$j] = PHP_INT_MAX;

                for ($k = $i; $k < $j; $k++) {
                    $cost = $dp[$i][$k]
                          + $dp[$k + 1][$j]
                          + $dimensions[$i] * $dimensions[$k + 1] * $dimensions[$j + 1];

                    if ($cost < $dp[$i][$j]) {
                        $dp[$i][$j] = $cost;
                        $split[$i][$j] = $k;

                        $steps[] = [
                            'range' => "M" . ($i + 1) . " to M" . ($j + 1),
                            'split_at' => $k + 1,
                            'cost' => $cost
                        ];
                    }
                }
            }
        }

        return [
            'min_cost' => $dp[0][$n - 1],
            'order' => $this->buildParenthesization($split, 0, $n - 1),
            'steps' => $steps
        ];
    }
}

// =============================================================================
// EXAMPLES AND USAGE
// =============================================================================

echo "Matrix Chain Multiplication Examples\n";
echo str_repeat("=", 70) . "\n\n";

$mcm = new MatrixChainMultiplication();

// Example 1: Four matrices
echo "Example 1: Four Matrices\n";
echo "-" . str_repeat("-", 69) . "\n";
echo "Matrices: A1(10×20), A2(20×30), A3(30×40), A4(40×30)\n";
$dimensions1 = [10, 20, 30, 40, 30];

$minOps = $mcm->minMultiplications($dimensions1);
$order = $mcm->optimalParenthesization($dimensions1);

echo "Minimum multiplications: " . number_format($minOps) . "\n";
echo "Optimal order: $order\n\n";

// Example 2: Three matrices
echo "Example 2: Three Matrices\n";
echo "-" . str_repeat("-", 69) . "\n";
echo "Matrices: A1(5×10), A2(10×3), A3(3×12)\n";
$dimensions2 = [5, 10, 3, 12];

$minOps2 = $mcm->minMultiplications($dimensions2);
$order2 = $mcm->optimalParenthesization($dimensions2);

echo "Minimum multiplications: " . number_format($minOps2) . "\n";
echo "Optimal order: $order2\n\n";

// Example 3: Detailed solution
echo "Example 3: Detailed Solution\n";
echo "-" . str_repeat("-", 69) . "\n";
$dimensions3 = [30, 35, 15, 5, 10, 20, 25];
echo "Matrices: A1(30×35), A2(35×15), A3(15×5), A4(5×10), A5(10×20), A6(20×25)\n";

$detailed = $mcm->getDetailedSolution($dimensions3);

echo "Minimum cost: " . number_format($detailed['min_cost']) . "\n";
echo "Optimal order: {$detailed['order']}\n";
echo "\nOptimization steps:\n";

foreach (array_slice($detailed['steps'], -5) as $step) {
    echo "  • {$step['range']}: split at M{$step['split_at']}, cost = "
         . number_format($step['cost']) . "\n";
}

// Performance benchmark
echo "\n" . str_repeat("=", 70) . "\n";
echo "Performance Benchmark\n";
echo str_repeat("=", 70) . "\n";

$sizes = [5, 10, 15, 20];
foreach ($sizes as $size) {
    $dims = range(10, 10 + $size * 5, 5);

    $start = microtime(true);
    $result = $mcm->minMultiplications($dims);
    $time = (microtime(true) - $start) * 1000;

    echo sprintf(
        "%2d matrices: %10s ops, Time: %6.2f ms\n",
        $size,
        number_format($result),
        $time
    );
}

echo "\n✓ All examples completed successfully!\n";
