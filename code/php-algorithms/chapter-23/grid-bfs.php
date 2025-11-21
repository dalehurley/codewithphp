<?php

declare(strict_types=1);

/**
 * BFS on 2D Grid
 *
 * Common application of BFS for grid-based problems.
 * Used for pathfinding in mazes, games, and navigation.
 *
 * Time Complexity: O(rows × cols)
 * Space Complexity: O(rows × cols)
 *
 * PHP Version: 8.0+
 */

class GridBFS
{
    private const DIRECTIONS = [
        [-1, 0],  // Up
        [0, 1],   // Right
        [1, 0],   // Down
        [0, -1]   // Left
    ];

    /**
     * Find shortest path in grid
     *
     * @param array $grid 2D grid (1 = walkable, 0 = obstacle)
     * @param array $start [row, col]
     * @param array $end [row, col]
     * @return int|null Path length or null if no path
     */
    public function shortestPath(array $grid, array $start, array $end): ?int
    {
        $rows = count($grid);
        $cols = count($grid[0]);
        $visited = [];

        $queue = [[$start[0], $start[1], 0]];
        $visited["{$start[0]},{$start[1]}"] = true;

        while (!empty($queue)) {
            [$row, $col, $distance] = array_shift($queue);

            if ($row === $end[0] && $col === $end[1]) {
                return $distance;
            }

            foreach (self::DIRECTIONS as [$dr, $dc]) {
                $newRow = $row + $dr;
                $newCol = $col + $dc;
                $key = "$newRow,$newCol";

                if ($newRow >= 0 && $newRow < $rows &&
                    $newCol >= 0 && $newCol < $cols &&
                    $grid[$newRow][$newCol] === 1 &&
                    !isset($visited[$key])) {

                    $visited[$key] = true;
                    $queue[] = [$newRow, $newCol, $distance + 1];
                }
            }
        }

        return null;
    }

    /**
     * Count islands (connected 1s)
     *
     * @param array $grid 2D grid
     * @return int Number of islands
     */
    public function countIslands(array $grid): int
    {
        $rows = count($grid);
        $cols = count($grid[0]);
        $visited = [];
        $count = 0;

        for ($row = 0; $row < $rows; $row++) {
            for ($col = 0; $col < $cols; $col++) {
                $key = "$row,$col";

                if ($grid[$row][$col] === 1 && !isset($visited[$key])) {
                    $this->bfsIsland($grid, $row, $col, $visited);
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * BFS to mark an island
     */
    private function bfsIsland(array $grid, int $startRow, int $startCol, array &$visited): void
    {
        $rows = count($grid);
        $cols = count($grid[0]);
        $queue = [[$startRow, $startCol]];
        $visited["$startRow,$startCol"] = true;

        while (!empty($queue)) {
            [$row, $col] = array_shift($queue);

            foreach (self::DIRECTIONS as [$dr, $dc]) {
                $newRow = $row + $dr;
                $newCol = $col + $dc;
                $key = "$newRow,$newCol";

                if ($newRow >= 0 && $newRow < $rows &&
                    $newCol >= 0 && $newCol < $cols &&
                    $grid[$newRow][$newCol] === 1 &&
                    !isset($visited[$key])) {

                    $visited[$key] = true;
                    $queue[] = [$newRow, $newCol];
                }
            }
        }
    }
}

// ============================================================================
// Example Usage
// ============================================================================

echo "=== Grid BFS Examples ===\n\n";

$gridBFS = new GridBFS();

// Pathfinding example
echo "=== Grid Pathfinding ===\n";
$grid = [
    [1, 1, 0, 1],
    [1, 0, 1, 1],
    [1, 1, 1, 0],
    [0, 1, 1, 1]
];

echo "Grid (1=walkable, 0=obstacle):\n";
foreach ($grid as $row) {
    echo implode(' ', $row) . "\n";
}

$distance = $gridBFS->shortestPath($grid, [0, 0], [3, 3]);
echo "\nShortest path from (0,0) to (3,3): " . ($distance ?? "No path") . " steps\n\n";

// Island counting example
echo "=== Island Counting ===\n";
$islandGrid = [
    [1, 1, 0, 0, 0],
    [1, 1, 0, 0, 1],
    [0, 0, 0, 1, 1],
    [0, 0, 0, 0, 0],
    [1, 0, 1, 0, 1]
];

echo "Grid:\n";
foreach ($islandGrid as $row) {
    echo implode(' ', $row) . "\n";
}

$islands = $gridBFS->countIslands($islandGrid);
echo "\nNumber of islands: $islands\n";

echo "\n=== Done ===\n";
