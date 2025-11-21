<?php

declare(strict_types=1);

/**
 * Social Network Analyzer
 *
 * A complete, production-ready example of using graph representations
 * to analyze a social network. Demonstrates practical applications including:
 * - Friend recommendations
 * - Mutual friends calculation
 * - Network influence metrics
 * - Community detection
 *
 * PHP Version: 8.0+
 */

/**
 * Social Network class with advanced analytics
 */
class SocialNetwork
{
    private array $graph = [];
    private array $userProfiles = [];

    /**
     * Add a user to the network
     *
     * @param string $userId Unique user identifier
     * @param array $profile User profile data
     */
    public function addUser(string $userId, array $profile = []): void
    {
        if (!isset($this->graph[$userId])) {
            $this->graph[$userId] = [];
            $this->userProfiles[$userId] = $profile + [
                'id' => $userId,
                'joined' => date('Y-m-d'),
                'friendCount' => 0
            ];
        }
    }

    /**
     * Add a friendship between two users
     *
     * @param string $user1 First user ID
     * @param string $user2 Second user ID
     * @throws InvalidArgumentException if users don't exist
     */
    public function addFriendship(string $user1, string $user2): void
    {
        if (!isset($this->graph[$user1]) || !isset($this->graph[$user2])) {
            throw new InvalidArgumentException("Both users must exist in the network");
        }

        if (!in_array($user2, $this->graph[$user1], true)) {
            $this->graph[$user1][] = $user2;
            $this->userProfiles[$user1]['friendCount']++;
        }

        if (!in_array($user1, $this->graph[$user2], true)) {
            $this->graph[$user2][] = $user1;
            $this->userProfiles[$user2]['friendCount']++;
        }
    }

    /**
     * Remove a friendship
     *
     * @param string $user1 First user ID
     * @param string $user2 Second user ID
     */
    public function removeFriendship(string $user1, string $user2): void
    {
        if (isset($this->graph[$user1])) {
            $this->graph[$user1] = array_values(
                array_filter($this->graph[$user1], fn($id) => $id !== $user2)
            );
            $this->userProfiles[$user1]['friendCount'] = count($this->graph[$user1]);
        }

        if (isset($this->graph[$user2])) {
            $this->graph[$user2] = array_values(
                array_filter($this->graph[$user2], fn($id) => $id !== $user1)
            );
            $this->userProfiles[$user2]['friendCount'] = count($this->graph[$user2]);
        }
    }

    /**
     * Get all friends of a user
     *
     * @param string $userId User ID
     * @return array Array of friend user IDs
     */
    public function getFriends(string $userId): array
    {
        return $this->graph[$userId] ?? [];
    }

    /**
     * Find mutual friends between two users
     *
     * @param string $user1 First user ID
     * @param string $user2 Second user ID
     * @return array Array of mutual friend user IDs
     */
    public function getMutualFriends(string $user1, string $user2): array
    {
        $friends1 = array_flip($this->graph[$user1] ?? []);
        $friends2 = array_flip($this->graph[$user2] ?? []);
        return array_keys(array_intersect_key($friends1, $friends2));
    }

    /**
     * Recommend friends for a user (friends of friends)
     *
     * @param string $userId User ID
     * @param int $limit Maximum number of recommendations
     * @return array Array of recommended users with mutual friend counts
     */
    public function recommendFriends(string $userId, int $limit = 5): array
    {
        $recommendations = [];
        $friends = array_flip($this->graph[$userId] ?? []);

        // Look at friends of friends
        foreach ($this->graph[$userId] ?? [] as $friend) {
            foreach ($this->graph[$friend] ?? [] as $fof) {
                // Skip if already friends or self
                if ($fof === $userId || isset($friends[$fof])) {
                    continue;
                }

                // Count mutual connections
                $mutualCount = count($this->getMutualFriends($userId, $fof));

                if (!isset($recommendations[$fof]) || $recommendations[$fof]['mutualFriends'] < $mutualCount) {
                    $recommendations[$fof] = [
                        'userId' => $fof,
                        'name' => $this->userProfiles[$fof]['name'] ?? $fof,
                        'mutualFriends' => $mutualCount,
                        'connectedThrough' => $friend
                    ];
                }
            }
        }

        // Sort by mutual friends count
        usort($recommendations, fn($a, $b) => $b['mutualFriends'] <=> $a['mutualFriends']);

        return array_slice($recommendations, 0, $limit);
    }

    /**
     * Calculate network centrality metrics for a user
     *
     * @param string $userId User ID
     * @return array Array of centrality metrics
     */
    public function calculateCentrality(string $userId): array
    {
        // Degree centrality (number of direct connections)
        $degreeCentrality = count($this->graph[$userId] ?? []);

        // Closeness centrality (average distance to others)
        $distances = $this->bfsDistances($userId);
        $totalDistance = array_sum($distances);
        $reachable = count($distances) - 1; // Exclude self
        $closenessCentrality = $reachable > 0 && $totalDistance > 0
            ? $reachable / $totalDistance
            : 0;

        // Influence score (combination of metrics)
        $influenceScore = $degreeCentrality * (1 + $closenessCentrality);

        return [
            'userId' => $userId,
            'name' => $this->userProfiles[$userId]['name'] ?? $userId,
            'degreeCentrality' => $degreeCentrality,
            'closenessCentrality' => round($closenessCentrality, 4),
            'influenceScore' => round($influenceScore, 2),
            'rank' => $this->getUserRank($userId)
        ];
    }

    /**
     * Find the shortest path between two users
     *
     * @param string $start Start user ID
     * @param string $end End user ID
     * @return array|null Array of user IDs in path, or null if no path exists
     */
    public function findPath(string $start, string $end): ?array
    {
        if ($start === $end) {
            return [$start];
        }

        $visited = [$start => true];
        $parent = [$start => null];
        $queue = [$start];

        while (!empty($queue)) {
            $current = array_shift($queue);

            foreach ($this->graph[$current] ?? [] as $friend) {
                if (!isset($visited[$friend])) {
                    $visited[$friend] = true;
                    $parent[$friend] = $current;
                    $queue[] = $friend;

                    if ($friend === $end) {
                        return $this->reconstructPath($parent, $start, $end);
                    }
                }
            }
        }

        return null; // No path found
    }

    /**
     * Calculate degrees of separation between two users
     *
     * @param string $user1 First user ID
     * @param string $user2 Second user ID
     * @return int Number of degrees, or -1 if not connected
     */
    public function getDegreesOfSeparation(string $user1, string $user2): int
    {
        $path = $this->findPath($user1, $user2);
        return $path !== null ? count($path) - 1 : -1;
    }

    /**
     * Find all users within N degrees of separation
     *
     * @param string $userId User ID
     * @param int $maxDegrees Maximum degrees of separation
     * @return array Array of users with their distances
     */
    public function findUsersWithinDegrees(string $userId, int $maxDegrees): array
    {
        $distances = $this->bfsDistances($userId);
        $users = [];

        foreach ($distances as $id => $distance) {
            if ($id !== $userId && $distance <= $maxDegrees) {
                $users[] = [
                    'userId' => $id,
                    'name' => $this->userProfiles[$id]['name'] ?? $id,
                    'degrees' => $distance
                ];
            }
        }

        usort($users, fn($a, $b) => $a['degrees'] <=> $b['degrees']);

        return $users;
    }

    /**
     * Get network statistics
     *
     * @return array Array of network statistics
     */
    public function getNetworkStats(): array
    {
        $totalUsers = count($this->graph);
        $totalFriendships = 0;
        $degrees = [];

        foreach ($this->graph as $friends) {
            $degree = count($friends);
            $totalFriendships += $degree;
            $degrees[] = $degree;
        }

        $totalFriendships = intdiv($totalFriendships, 2); // Each friendship counted twice
        $avgDegree = $totalUsers > 0 ? $totalFriendships * 2 / $totalUsers : 0;

        return [
            'totalUsers' => $totalUsers,
            'totalFriendships' => $totalFriendships,
            'averageFriendsPerUser' => round($avgDegree, 2),
            'mostConnectedUser' => $this->getMostConnectedUser(),
            'networkDensity' => $this->calculateNetworkDensity()
        ];
    }

    /**
     * BFS to find distances from a start user to all reachable users
     *
     * @param string $start Start user ID
     * @return array Associative array of [userId => distance]
     */
    private function bfsDistances(string $start): array
    {
        $distances = [$start => 0];
        $queue = [$start];

        while (!empty($queue)) {
            $current = array_shift($queue);
            $currentDist = $distances[$current];

            foreach ($this->graph[$current] ?? [] as $friend) {
                if (!isset($distances[$friend])) {
                    $distances[$friend] = $currentDist + 1;
                    $queue[] = $friend;
                }
            }
        }

        return $distances;
    }

    /**
     * Reconstruct path from parent array
     *
     * @param array $parent Parent array from BFS
     * @param string $start Start user ID
     * @param string $end End user ID
     * @return array Path as array of user IDs
     */
    private function reconstructPath(array $parent, string $start, string $end): array
    {
        $path = [];
        $current = $end;

        while ($current !== null) {
            array_unshift($path, $current);
            $current = $parent[$current];
        }

        return $path;
    }

    /**
     * Get user rank based on friend count
     *
     * @param string $userId User ID
     * @return string Rank label
     */
    private function getUserRank(string $userId): string
    {
        $friendCount = count($this->graph[$userId] ?? []);

        return match (true) {
            $friendCount >= 100 => 'Influencer',
            $friendCount >= 50 => 'Super Connector',
            $friendCount >= 20 => 'Social Butterfly',
            $friendCount >= 10 => 'Well Connected',
            $friendCount >= 5 => 'Active User',
            default => 'New User'
        };
    }

    /**
     * Find the most connected user
     *
     * @return array|null User info or null if network is empty
     */
    private function getMostConnectedUser(): ?array
    {
        $maxFriends = 0;
        $mostConnected = null;

        foreach ($this->graph as $userId => $friends) {
            $friendCount = count($friends);
            if ($friendCount > $maxFriends) {
                $maxFriends = $friendCount;
                $mostConnected = [
                    'userId' => $userId,
                    'name' => $this->userProfiles[$userId]['name'] ?? $userId,
                    'friendCount' => $friendCount
                ];
            }
        }

        return $mostConnected;
    }

    /**
     * Calculate network density (ratio of actual to possible connections)
     *
     * @return float Network density between 0 and 1
     */
    private function calculateNetworkDensity(): float
    {
        $n = count($this->graph);
        if ($n <= 1) {
            return 0.0;
        }

        $actualEdges = 0;
        foreach ($this->graph as $friends) {
            $actualEdges += count($friends);
        }
        $actualEdges = intdiv($actualEdges, 2); // Undirected edges

        $possibleEdges = $n * ($n - 1) / 2;

        return round($actualEdges / $possibleEdges, 4);
    }
}

// ============================================================================
// Demonstration and Example Usage
// ============================================================================

echo "=== Social Network Analyzer Demo ===\n\n";

try {
    // Create social network
    $network = new SocialNetwork();

    // Add users
    $users = [
        ['id' => 'alice', 'name' => 'Alice Johnson'],
        ['id' => 'bob', 'name' => 'Bob Smith'],
        ['id' => 'charlie', 'name' => 'Charlie Brown'],
        ['id' => 'david', 'name' => 'David Lee'],
        ['id' => 'eve', 'name' => 'Eve Martinez'],
        ['id' => 'frank', 'name' => 'Frank Wilson'],
        ['id' => 'grace', 'name' => 'Grace Taylor'],
        ['id' => 'henry', 'name' => 'Henry Davis']
    ];

    foreach ($users as $user) {
        $network->addUser($user['id'], ['name' => $user['name']]);
    }

    // Add friendships
    $friendships = [
        ['alice', 'bob'], ['alice', 'charlie'], ['alice', 'david'],
        ['bob', 'charlie'], ['bob', 'eve'], ['charlie', 'david'],
        ['david', 'eve'], ['david', 'frank'], ['eve', 'grace'],
        ['frank', 'grace'], ['frank', 'henry'], ['grace', 'henry']
    ];

    foreach ($friendships as [$user1, $user2]) {
        $network->addFriendship($user1, $user2);
    }

    // Network Statistics
    echo "=== Network Statistics ===\n";
    $stats = $network->getNetworkStats();
    echo "Total Users: {$stats['totalUsers']}\n";
    echo "Total Friendships: {$stats['totalFriendships']}\n";
    echo "Average Friends per User: {$stats['averageFriendsPerUser']}\n";
    echo "Most Connected User: {$stats['mostConnectedUser']['name']} ";
    echo "({$stats['mostConnectedUser']['friendCount']} friends)\n";
    echo "Network Density: {$stats['networkDensity']}\n";

    echo "\n" . str_repeat('=', 70) . "\n\n";

    // User Analysis
    echo "=== User Analysis: Alice ===\n";
    $aliceFriends = $network->getFriends('alice');
    echo "Alice's friends: " . implode(', ', $aliceFriends) . "\n";

    $centrality = $network->calculateCentrality('alice');
    echo "Degree Centrality: {$centrality['degreeCentrality']}\n";
    echo "Closeness Centrality: {$centrality['closenessCentrality']}\n";
    echo "Influence Score: {$centrality['influenceScore']}\n";
    echo "Rank: {$centrality['rank']}\n";

    echo "\n" . str_repeat('=', 70) . "\n\n";

    // Friend Recommendations
    echo "=== Friend Recommendations for Alice ===\n";
    $recommendations = $network->recommendFriends('alice', 3);
    foreach ($recommendations as $i => $rec) {
        echo ($i + 1) . ". {$rec['name']} ({$rec['mutualFriends']} mutual friends)\n";
        echo "   Connected through: {$rec['connectedThrough']}\n";
    }

    echo "\n" . str_repeat('=', 70) . "\n\n";

    // Mutual Friends
    echo "=== Mutual Friends ===\n";
    $mutualFriends = $network->getMutualFriends('alice', 'eve');
    echo "Mutual friends between Alice and Eve: " . implode(', ', $mutualFriends) . "\n";

    echo "\n" . str_repeat('=', 70) . "\n\n";

    // Path Finding
    echo "=== Connection Path ===\n";
    $path = $network->findPath('alice', 'henry');
    if ($path) {
        echo "Path from Alice to Henry: " . implode(' → ', $path) . "\n";
        echo "Degrees of separation: " . ($network->getDegreesOfSeparation('alice', 'henry')) . "\n";
    }

    echo "\n" . str_repeat('=', 70) . "\n\n";

    // Users Within Degrees
    echo "=== Users Within 2 Degrees of Alice ===\n";
    $nearby = $network->findUsersWithinDegrees('alice', 2);
    foreach ($nearby as $user) {
        echo "{$user['name']} ({$user['degrees']} degree";
        echo $user['degrees'] > 1 ? 's' : '';
        echo " away)\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n=== Demo Complete ===\n";
