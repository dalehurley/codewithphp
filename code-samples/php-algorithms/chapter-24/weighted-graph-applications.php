<?php

declare(strict_types=1);

/**
 * Weighted Graph Applications
 *
 * Practical applications of Dijkstra's algorithm:
 * - GPS navigation
 * - Network routing
 * - Flight planning
 *
 * PHP Version: 8.0+
 */

class GPSRouter
{
    private const INF = PHP_INT_MAX;

    /**
     * Find shortest route between two locations
     *
     * @param array $roadNetwork Weighted graph of roads
     * @param string $from Starting location
     * @param string $to Destination location
     * @return array Route information
     */
    public function findRoute(array $roadNetwork, string $from, string $to): array
    {
        $distances = [];
        $previous = [];
        $vertices = array_keys($roadNetwork);

        foreach ($vertices as $vertex) {
            $distances[$vertex] = self::INF;
            $previous[$vertex] = null;
        }
        $distances[$from] = 0;

        $pq = new SplMinHeap();
        $pq->insert([0, $from]);

        while (!$pq->isEmpty()) {
            [$currentDist, $current] = $pq->extract();

            if ($current === $to) {
                break;
            }

            if ($currentDist > $distances[$current]) {
                continue;
            }

            foreach ($roadNetwork[$current] ?? [] as $edge) {
                $neighbor = $edge['city'];
                $distance = $edge['distance'];
                $newDist = $distances[$current] + $distance;

                if ($newDist < $distances[$neighbor]) {
                    $distances[$neighbor] = $newDist;
                    $previous[$neighbor] = $current;
                    $pq->insert([$newDist, $neighbor]);
                }
            }
        }

        // Reconstruct path
        $path = [];
        $current = $to;
        while ($current !== null) {
            array_unshift($path, $current);
            $current = $previous[$current];
        }

        return [
            'path' => $path,
            'distance' => $distances[$to],
            'route' => $this->generateDirections($roadNetwork, $path)
        ];
    }

    /**
     * Generate turn-by-turn directions
     */
    private function generateDirections(array $network, array $path): array
    {
        $directions = [];

        for ($i = 0; $i < count($path) - 1; $i++) {
            $from = $path[$i];
            $to = $path[$i + 1];

            foreach ($network[$from] ?? [] as $edge) {
                if ($edge['city'] === $to) {
                    $directions[] = "Drive from $from to $to ({$edge['distance']} km)";
                    break;
                }
            }
        }

        return $directions;
    }
}

// ============================================================================
// Example Usage
// ============================================================================

echo "=== GPS Navigation System ===\n\n";

$router = new GPSRouter();

// Road network
$roadNetwork = [
    'New York' => [
        ['city' => 'Philadelphia', 'distance' => 95],
        ['city' => 'Boston', 'distance' => 215]
    ],
    'Philadelphia' => [
        ['city' => 'New York', 'distance' => 95],
        ['city' => 'Washington DC', 'distance' => 140]
    ],
    'Boston' => [
        ['city' => 'New York', 'distance' => 215]
    ],
    'Washington DC' => [
        ['city' => 'Philadelphia', 'distance' => 140]
    ]
];

$route = $router->findRoute($roadNetwork, 'Boston', 'Washington DC');

echo "Route from Boston to Washington DC:\n";
echo "Distance: {$route['distance']} km\n";
echo "Path: " . implode(' → ', $route['path']) . "\n\n";
echo "Directions:\n";
foreach ($route['route'] as $i => $direction) {
    echo ($i + 1) . ". $direction\n";
}

echo "\n=== Done ===\n";
