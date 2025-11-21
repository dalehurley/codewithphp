<?php

declare(strict_types=1);

/**
 * 0/1 Knapsack Problem
 *
 * Maximize value of items in knapsack with weight limit.
 * Cannot take fractions of items (0/1 decision).
 *
 * Time Complexity: O(n × capacity)
 * Space Complexity: O(n × capacity) or O(capacity) optimized
 *
 * PHP Version: 8.0+
 */

class Knapsack
{
    /**
     * Find maximum value achievable
     *
     * @param array $weights Item weights
     * @param array $values Item values
     * @param int $capacity Knapsack capacity
     * @return int Maximum value
     */
    public function maxValue(array $weights, array $values, int $capacity): int
    {
        $n = count($weights);
        $dp = array_fill(0, $n + 1, array_fill(0, $capacity + 1, 0));

        for ($i = 1; $i <= $n; $i++) {
            for ($w = 0; $w <= $capacity; $w++) {
                // Don't include item
                $dp[$i][$w] = $dp[$i - 1][$w];

                // Include item if it fits
                if ($weights[$i - 1] <= $w) {
                    $includeValue = $values[$i - 1] + $dp[$i - 1][$w - $weights[$i - 1]];
                    $dp[$i][$w] = max($dp[$i][$w], $includeValue);
                }
            }
        }

        return $dp[$n][$capacity];
    }

    /**
     * Get items included in optimal solution
     *
     * @param array $weights Item weights
     * @param array $values Item values
     * @param int $capacity Knapsack capacity
     * @return array [maxValue, items]
     */
    public function getItems(array $weights, array $values, int $capacity): array
    {
        $n = count($weights);
        $dp = array_fill(0, $n + 1, array_fill(0, $capacity + 1, 0));

        // Fill DP table
        for ($i = 1; $i <= $n; $i++) {
            for ($w = 0; $w <= $capacity; $w++) {
                $dp[$i][$w] = $dp[$i - 1][$w];

                if ($weights[$i - 1] <= $w) {
                    $includeValue = $values[$i - 1] + $dp[$i - 1][$w - $weights[$i - 1]];
                    $dp[$i][$w] = max($dp[$i][$w], $includeValue);
                }
            }
        }

        // Reconstruct solution
        $items = [];
        $w = $capacity;
        for ($i = $n; $i > 0; $i--) {
            if ($dp[$i][$w] !== $dp[$i - 1][$w]) {
                $items[] = $i - 1;
                $w -= $weights[$i - 1];
            }
        }

        return [
            'maxValue' => $dp[$n][$capacity],
            'items' => array_reverse($items)
        ];
    }
}

// ============================================================================
// Example Usage
// ============================================================================

echo "=== 0/1 Knapsack Problem ===\n\n";

$knapsack = new Knapsack();

$weights = [1, 3, 4, 5];
$values = [1, 4, 5, 7];
$capacity = 7;

echo "Items:\n";
for ($i = 0; $i < count($weights); $i++) {
    echo "  Item $i: weight={$weights[$i]}, value={$values[$i]}\n";
}
echo "\nKnapsack capacity: $capacity\n\n";

$maxValue = $knapsack->maxValue($weights, $values, $capacity);
echo "Maximum value: $maxValue\n\n";

$result = $knapsack->getItems($weights, $values, $capacity);
echo "Items to include: [" . implode(', ', $result['items']) . "]\n";
echo "Total weight: ";
$totalWeight = 0;
foreach ($result['items'] as $i) {
    $totalWeight += $weights[$i];
}
echo "$totalWeight\n";
echo "Total value: {$result['maxValue']}\n";

echo "\n=== Done ===\n";
