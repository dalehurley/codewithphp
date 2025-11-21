<?php

declare(strict_types=1);

/**
 * Traveling Salesman Problem - Bitmask Dynamic Programming
 *
 * Finds the shortest route that visits all cities exactly once and returns
 * to the starting city using bitmask DP for efficient state representation.
 *
 * Time Complexity: O(2^n * n^2)
 * Space Complexity: O(2^n * n)
 */
class TravelingSalesman
{
    private const INF = PHP_INT_MAX;

    /**
     * Calculate minimum cost to visit all cities
     *
     * @param array<array<int>> $distances Distance matrix where $distances[i][j]
     *                                       is the distance from city i to city j
     * @return int Minimum cost, or -1 if impossible
     */
    public function minCost(array $distances): int
    {
        $n = count($distances);

        if ($n === 0) {
            return 0;
        }

        $allVisited = (1 << $n) - 1;  // All bits set

        // dp[mask][i] = min cost to reach city i with visited cities in mask
        $dp = array_fill(0, 1 << $n, array_fill(0, $n, self::INF));

        // Start from city 0
        $dp[1][0] = 0;

        for ($mask = 1; $mask <= $allVisited; $mask++) {
            for ($u = 0; $u < $n; $u++) {
                // Skip if city u is not in the current mask
                if (!($mask & (1 << $u))) {
                    continue;
                }

                // Skip if no path found to this state
                if ($dp[$mask][$u] === self::INF) {
                    continue;
                }

                // Try visiting each unvisited city
                for ($v = 0; $v < $n; $v++) {
                    // Skip if already visited
                    if ($mask & (1 << $v)) {
                        continue;
                    }

                    $newMask = $mask | (1 << $v);
                    $newCost = $dp[$mask][$u] + $distances[$u][$v];

                    $dp[$newMask][$v] = min($dp[$newMask][$v], $newCost);
                }
            }
        }

        // Find minimum cost to visit all cities and return to start
        $minCost = self::INF;
        for ($i = 0; $i < $n; $i++) {
            if ($dp[$allVisited][$i] !== self::INF && $distances[$i][0] !== self::INF) {
                $minCost = min($minCost, $dp[$allVisited][$i] + $distances[$i][0]);
            }
        }

        return $minCost === self::INF ? -1 : $minCost;
    }

    /**
     * Find the actual tour (sequence of cities)
     *
     * @param array<array<int>> $distances Distance matrix
     * @return array{tour: array<int>, cost: int}|null Tour and cost, or null if impossible
     */
    public function findTour(array $distances): ?array
    {
        $n = count($distances);

        if ($n === 0) {
            return ['tour' => [], 'cost' => 0];
        }

        $allVisited = (1 << $n) - 1;

        $dp = array_fill(0, 1 << $n, array_fill(0, $n, self::INF));
        $parent = array_fill(0, 1 << $n, array_fill(0, $n, -1));

        $dp[1][0] = 0;

        for ($mask = 1; $mask <= $allVisited; $mask++) {
            for ($u = 0; $u < $n; $u++) {
                if (!($mask & (1 << $u)) || $dp[$mask][$u] === self::INF) {
                    continue;
                }

                for ($v = 0; $v < $n; $v++) {
                    if ($mask & (1 << $v)) {
                        continue;
                    }

                    $newMask = $mask | (1 << $v);
                    $newCost = $dp[$mask][$u] + $distances[$u][$v];

                    if ($newCost < $dp[$newMask][$v]) {
                        $dp[$newMask][$v] = $newCost;
                        $parent[$newMask][$v] = $u;
                    }
                }
            }
        }

        // Find best ending city
        $minCost = self::INF;
        $lastCity = -1;

        for ($i = 0; $i < $n; $i++) {
            if ($dp[$allVisited][$i] !== self::INF && $distances[$i][0] !== self::INF) {
                $totalCost = $dp[$allVisited][$i] + $distances[$i][0];
                if ($totalCost < $minCost) {
                    $minCost = $totalCost;
                    $lastCity = $i;
                }
            }
        }

        if ($lastCity === -1) {
            return null;
        }

        // Reconstruct tour
        $tour = [$lastCity];
        $mask = $allVisited;
        $current = $lastCity;

        while ($parent[$mask][$current] !== -1) {
            $prev = $parent[$mask][$current];
            array_unshift($tour, $prev);
            $mask ^= (1 << $current);
            $current = $prev;
        }

        $tour[] = 0;  // Return to start

        return [
            'tour' => $tour,
            'cost' => $minCost
        ];
    }

    /**
     * Solve TSP with different starting cities
     *
     * @param array<array<int>> $distances Distance matrix
     * @return array{best_start: int, tour: array<int>, cost: int}|null
     */
    public function findBestTour(array $distances): ?array
    {
        $n = count($distances);
        $bestCost = self::INF;
        $bestTour = null;
        $bestStart = 0;

        // Try each city as starting point (for small n only)
        if ($n <= 8) {
            for ($start = 0; $start < $n; $start++) {
                // Swap rows/columns to make $start the new "city 0"
                $rotated = $this->rotateCities($distances, $start);
                $result = $this->findTour($rotated);

                if ($result && $result['cost'] < $bestCost) {
                    $bestCost = $result['cost'];
                    $bestTour = $this->unrotateTour($result['tour'], $start, $n);
                    $bestStart = $start;
                }
            }
        } else {
            $result = $this->findTour($distances);
            if ($result) {
                return [
                    'best_start' => 0,
                    'tour' => $result['tour'],
                    'cost' => $result['cost']
                ];
            }
        }

        if ($bestTour === null) {
            return null;
        }

        return [
            'best_start' => $bestStart,
            'tour' => $bestTour,
            'cost' => $bestCost
        ];
    }

    private function rotateCities(array $distances, int $newStart): array
    {
        $n = count($distances);
        $rotated = array_fill(0, $n, array_fill(0, $n, 0));

        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $origI = ($i + $newStart) % $n;
                $origJ = ($j + $newStart) % $n;
                $rotated[$i][$j] = $distances[$origI][$origJ];
            }
        }

        return $rotated;
    }

    private function unrotateTour(array $tour, int $start, int $n): array
    {
        return array_map(fn($city) => ($city + $start) % $n, $tour);
    }
}

// =============================================================================
// EXAMPLES AND USAGE
// =============================================================================

echo "Traveling Salesman Problem (Bitmask DP)\n";
echo str_repeat("=", 70) . "\n\n";

$tsp = new TravelingSalesman();

// Example 1: Small graph (4 cities)
echo "Example 1: Four Cities\n";
echo "-" . str_repeat("-", 69) . "\n";

$distances1 = [
    [0, 10, 15, 20],
    [10, 0, 35, 25],
    [15, 35, 0, 30],
    [20, 25, 30, 0]
];

echo "Distance Matrix:\n";
foreach ($distances1 as $i => $row) {
    echo "City $i: " . implode(', ', array_map(fn($d) => str_pad((string)$d, 3), $row)) . "\n";
}
echo "\n";

$cost1 = $tsp->minCost($distances1);
echo "Minimum tour cost: $cost1\n";

$tour1 = $tsp->findTour($distances1);
if ($tour1) {
    echo "Tour: " . implode(' → ', $tour1['tour']) . "\n";
    echo "Total distance: {$tour1['cost']}\n";
}
echo "\n";

// Example 2: Five cities
echo "Example 2: Five Cities\n";
echo "-" . str_repeat("-", 69) . "\n";

$distances2 = [
    [0, 12, 10, 19, 8],
    [12, 0, 3, 7, 6],
    [10, 3, 0, 2, 20],
    [19, 7, 2, 0, 4],
    [8, 6, 20, 4, 0]
];

$tour2 = $tsp->findTour($distances2);
if ($tour2) {
    echo "Tour: " . implode(' → ', $tour2['tour']) . "\n";
    echo "Total distance: {$tour2['cost']}\n";
}
echo "\n";

// Example 3: Find best starting city
echo "Example 3: Find Best Starting City\n";
echo "-" . str_repeat("-", 69) . "\n";

$distances3 = [
    [0, 5, 10, 15],
    [5, 0, 20, 30],
    [10, 20, 0, 25],
    [15, 30, 25, 0]
];

$best = $tsp->findBestTour($distances3);
if ($best) {
    echo "Best starting city: {$best['best_start']}\n";
    echo "Optimal tour: " . implode(' → ', $best['tour']) . "\n";
    echo "Total cost: {$best['cost']}\n";
}
echo "\n";

// Performance benchmark
echo str_repeat("=", 70) . "\n";
echo "Performance Benchmark\n";
echo str_repeat("=", 70) . "\n";

foreach ([4, 6, 8, 10] as $n) {
    // Generate random distance matrix
    $distances = array_fill(0, $n, array_fill(0, $n, 0));
    for ($i = 0; $i < $n; $i++) {
        for ($j = $i + 1; $j < $n; $j++) {
            $dist = rand(5, 50);
            $distances[$i][$j] = $dist;
            $distances[$j][$i] = $dist;
        }
    }

    $start = microtime(true);
    $cost = $tsp->minCost($distances);
    $time = (microtime(true) - $start) * 1000;

    $states = (1 << $n) * $n;
    echo sprintf(
        "%2d cities: Cost = %3d, Time = %7.2f ms, States = %s\n",
        $n,
        $cost,
        $time,
        number_format($states)
    );
}

echo "\n✓ All examples completed successfully!\n";
echo "\nNote: TSP is NP-hard. Bitmask DP works well for n ≤ 20 cities.\n";
echo "For larger instances, use approximation algorithms (e.g., Christofides).\n";
