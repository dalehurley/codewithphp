<?php

declare(strict_types=1);

/**
 * Coin Change Problem
 *
 * Find minimum coins needed to make a given amount.
 * Classic DP problem demonstrating optimal substructure.
 *
 * Time Complexity: O(amount × coins)
 * Space Complexity: O(amount)
 *
 * PHP Version: 8.0+
 */

class CoinChange
{
    /**
     * Find minimum coins to make amount
     *
     * @param array $coins Available coin denominations
     * @param int $amount Target amount
     * @return int Minimum coins needed, or -1 if impossible
     */
    public function minCoins(array $coins, int $amount): int
    {
        $dp = array_fill(0, $amount + 1, PHP_INT_MAX);
        $dp[0] = 0;

        for ($i = 1; $i <= $amount; $i++) {
            foreach ($coins as $coin) {
                if ($coin <= $i && $dp[$i - $coin] !== PHP_INT_MAX) {
                    $dp[$i] = min($dp[$i], $dp[$i - $coin] + 1);
                }
            }
        }

        return $dp[$amount] === PHP_INT_MAX ? -1 : $dp[$amount];
    }

    /**
     * Get the actual coins used
     *
     * @param array $coins Available coins
     * @param int $amount Target amount
     * @return array|null Coins used or null if impossible
     */
    public function getCoinsUsed(array $coins, int $amount): ?array
    {
        $dp = array_fill(0, $amount + 1, PHP_INT_MAX);
        $usedCoin = array_fill(0, $amount + 1, -1);
        $dp[0] = 0;

        for ($i = 1; $i <= $amount; $i++) {
            foreach ($coins as $coin) {
                if ($coin <= $i && $dp[$i - $coin] !== PHP_INT_MAX) {
                    if ($dp[$i - $coin] + 1 < $dp[$i]) {
                        $dp[$i] = $dp[$i - $coin] + 1;
                        $usedCoin[$i] = $coin;
                    }
                }
            }
        }

        if ($dp[$amount] === PHP_INT_MAX) {
            return null;
        }

        // Reconstruct solution
        $result = [];
        $current = $amount;
        while ($current > 0) {
            $coin = $usedCoin[$current];
            $result[] = $coin;
            $current -= $coin;
        }

        return $result;
    }

    /**
     * Count ways to make amount
     *
     * @param array $coins Available coins
     * @param int $amount Target amount
     * @return int Number of ways
     */
    public function countWays(array $coins, int $amount): int
    {
        $dp = array_fill(0, $amount + 1, 0);
        $dp[0] = 1;

        foreach ($coins as $coin) {
            for ($i = $coin; $i <= $amount; $i++) {
                $dp[$i] += $dp[$i - $coin];
            }
        }

        return $dp[$amount];
    }
}

// ============================================================================
// Example Usage
// ============================================================================

echo "=== Coin Change Problem ===\n\n";

$coinChange = new CoinChange();

$coins = [1, 5, 10, 25];
$amount = 63;

echo "Coins available: [" . implode(', ', $coins) . "]\n";
echo "Target amount: $amount cents\n\n";

echo "=== Minimum Coins ===\n";
$minCoins = $coinChange->minCoins($coins, $amount);
echo "Minimum coins needed: $minCoins\n\n";

echo "=== Coins Used ===\n";
$coinsUsed = $coinChange->getCoinsUsed($coins, $amount);
if ($coinsUsed) {
    echo "Coins: [" . implode(', ', $coinsUsed) . "]\n";
    echo "Breakdown: ";
    $counts = array_count_values($coinsUsed);
    $parts = [];
    foreach ($counts as $coin => $count) {
        $parts[] = "$count × $coin";
    }
    echo implode(' + ', $parts) . "\n\n";
}

echo "=== Number of Ways ===\n";
$ways = $coinChange->countWays($coins, 10);
echo "Ways to make 10 cents: $ways\n";

echo "\n=== Done ===\n";
