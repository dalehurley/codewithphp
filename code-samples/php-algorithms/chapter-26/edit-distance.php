<?php

declare(strict_types=1);

/**
 * Edit Distance (Levenshtein Distance)
 *
 * Calculates the minimum number of operations (insert, delete, replace)
 * needed to transform one string into another.
 *
 * Time Complexity: O(m × n)
 * Space Complexity: O(m × n) or O(min(m, n)) with optimization
 */
class EditDistance
{
    /**
     * Calculate minimum edit distance between two strings
     *
     * @param string $word1 Source string
     * @param string $word2 Target string
     * @return int Minimum number of operations
     */
    public function minDistance(string $word1, string $word2): int
    {
        $m = strlen($word1);
        $n = strlen($word2);

        // dp[i][j] = min operations to convert word1[0...i-1] to word2[0...j-1]
        $dp = array_fill(0, $m + 1, array_fill(0, $n + 1, 0));

        // Base cases
        for ($i = 0; $i <= $m; $i++) {
            $dp[$i][0] = $i;  // Delete all characters
        }
        for ($j = 0; $j <= $n; $j++) {
            $dp[0][$j] = $j;  // Insert all characters
        }

        // Fill DP table
        for ($i = 1; $i <= $m; $i++) {
            for ($j = 1; $j <= $n; $j++) {
                if ($word1[$i - 1] === $word2[$j - 1]) {
                    $dp[$i][$j] = $dp[$i - 1][$j - 1];  // No operation needed
                } else {
                    $dp[$i][$j] = 1 + min(
                        $dp[$i - 1][$j],      // Delete from word1
                        $dp[$i][$j - 1],      // Insert into word1
                        $dp[$i - 1][$j - 1]   // Replace in word1
                    );
                }
            }
        }

        return $dp[$m][$n];
    }

    /**
     * Space-optimized version using only two rows
     *
     * @param string $word1 Source string
     * @param string $word2 Target string
     * @return int Minimum number of operations
     */
    public function minDistanceOptimized(string $word1, string $word2): int
    {
        $m = strlen($word1);
        $n = strlen($word2);

        // Use shorter string for columns to minimize space
        if ($m < $n) {
            return $this->minDistanceOptimized($word2, $word1);
        }

        // Only need two rows
        $prev = range(0, $n);
        $curr = array_fill(0, $n + 1, 0);

        for ($i = 1; $i <= $m; $i++) {
            $curr[0] = $i;

            for ($j = 1; $j <= $n; $j++) {
                if ($word1[$i - 1] === $word2[$j - 1]) {
                    $curr[$j] = $prev[$j - 1];
                } else {
                    $curr[$j] = 1 + min(
                        $prev[$j],       // Delete
                        $curr[$j - 1],   // Insert
                        $prev[$j - 1]    // Replace
                    );
                }
            }

            [$prev, $curr] = [$curr, $prev];
        }

        return $prev[$n];
    }

    /**
     * Get the actual sequence of edit operations
     *
     * @param string $word1 Source string
     * @param string $word2 Target string
     * @return array<string> List of operations
     */
    public function getOperations(string $word1, string $word2): array
    {
        $m = strlen($word1);
        $n = strlen($word2);

        $dp = array_fill(0, $m + 1, array_fill(0, $n + 1, 0));

        for ($i = 0; $i <= $m; $i++) {
            $dp[$i][0] = $i;
        }
        for ($j = 0; $j <= $n; $j++) {
            $dp[0][$j] = $j;
        }

        for ($i = 1; $i <= $m; $i++) {
            for ($j = 1; $j <= $n; $j++) {
                if ($word1[$i - 1] === $word2[$j - 1]) {
                    $dp[$i][$j] = $dp[$i - 1][$j - 1];
                } else {
                    $dp[$i][$j] = 1 + min(
                        $dp[$i - 1][$j],
                        $dp[$i][$j - 1],
                        $dp[$i - 1][$j - 1]
                    );
                }
            }
        }

        // Reconstruct operations
        $operations = [];
        $i = $m;
        $j = $n;

        while ($i > 0 || $j > 0) {
            if ($i === 0) {
                $operations[] = "Insert '{$word2[$j - 1]}' at position " . count($operations);
                $j--;
            } elseif ($j === 0) {
                $operations[] = "Delete '{$word1[$i - 1]}' at position $i";
                $i--;
            } elseif ($word1[$i - 1] === $word2[$j - 1]) {
                $i--;
                $j--;
            } else {
                $delete = $dp[$i - 1][$j];
                $insert = $dp[$i][$j - 1];
                $replace = $dp[$i - 1][$j - 1];

                if ($replace <= $delete && $replace <= $insert) {
                    $operations[] = "Replace '{$word1[$i - 1]}' with '{$word2[$j - 1]}' at position $i";
                    $i--;
                    $j--;
                } elseif ($delete <= $insert) {
                    $operations[] = "Delete '{$word1[$i - 1]}' at position $i";
                    $i--;
                } else {
                    $operations[] = "Insert '{$word2[$j - 1]}' at position $i";
                    $j--;
                }
            }
        }

        return array_reverse($operations);
    }

    /**
     * Calculate similarity percentage between two strings
     *
     * @param string $word1 First string
     * @param string $word2 Second string
     * @return float Similarity percentage (0-100)
     */
    public function similarity(string $word1, string $word2): float
    {
        $maxLen = max(strlen($word1), strlen($word2));

        if ($maxLen === 0) {
            return 100.0;
        }

        $distance = $this->minDistance($word1, $word2);
        return (1 - $distance / $maxLen) * 100;
    }

    /**
     * Find closest match from a list of candidates
     *
     * @param string $target Target string
     * @param array<string> $candidates List of candidate strings
     * @return array{match: string, distance: int, similarity: float}|null
     */
    public function findClosestMatch(string $target, array $candidates): ?array
    {
        if (empty($candidates)) {
            return null;
        }

        $bestMatch = null;
        $bestDistance = PHP_INT_MAX;

        foreach ($candidates as $candidate) {
            $distance = $this->minDistance($target, $candidate);

            if ($distance < $bestDistance) {
                $bestDistance = $distance;
                $bestMatch = $candidate;
            }
        }

        return [
            'match' => $bestMatch,
            'distance' => $bestDistance,
            'similarity' => $this->similarity($target, $bestMatch)
        ];
    }
}

// =============================================================================
// EXAMPLES AND USAGE
// =============================================================================

echo "Edit Distance (Levenshtein Distance)\n";
echo str_repeat("=", 70) . "\n\n";

$ed = new EditDistance();

// Example 1: Basic distance calculation
echo "Example 1: Basic Distance Calculation\n";
echo "-" . str_repeat("-", 69) . "\n";

$pairs = [
    ['horse', 'ros'],
    ['intention', 'execution'],
    ['kitten', 'sitting'],
    ['saturday', 'sunday']
];

foreach ($pairs as [$word1, $word2]) {
    $distance = $ed->minDistance($word1, $word2);
    $similarity = $ed->similarity($word1, $word2);

    echo "'$word1' → '$word2': $distance operations, {$similarity}% similar\n";
}
echo "\n";

// Example 2: Detailed operations
echo "Example 2: Transformation Steps\n";
echo "-" . str_repeat("-", 69) . "\n";

$source = "algorithm";
$target = "altruistic";

echo "Transform '$source' → '$target':\n";
$operations = $ed->getOperations($source, $target);

foreach ($operations as $i => $op) {
    echo "  " . ($i + 1) . ". $op\n";
}

$distance = $ed->minDistance($source, $target);
echo "\nTotal operations: $distance\n\n";

// Example 3: Spell checker (find closest match)
echo "Example 3: Spell Checker\n";
echo "-" . str_repeat("-", 69) . "\n";

$dictionary = [
    'algorithm',
    'data',
    'structure',
    'dynamic',
    'programming',
    'complexity',
    'optimization',
    'recursion'
];

$misspellings = ['algoritm', 'dta', 'structre', 'dinamic', 'progaming'];

foreach ($misspellings as $misspelled) {
    $result = $ed->findClosestMatch($misspelled, $dictionary);

    echo "'$misspelled' → '{$result['match']}' ";
    echo "(distance: {$result['distance']}, similarity: {$result['similarity']}%)\n";
}
echo "\n";

// Example 4: Space optimization comparison
echo "Example 4: Space Optimization\n";
echo "-" . str_repeat("-", 69) . "\n";

$longStr1 = str_repeat('abcdefgh', 100);  // 800 chars
$longStr2 = str_repeat('ijklmnop', 100);  // 800 chars

$memStart = memory_get_usage();
$timeStart = microtime(true);
$dist1 = $ed->minDistance($longStr1, $longStr2);
$time1 = (microtime(true) - $timeStart) * 1000;
$mem1 = (memory_get_usage() - $memStart) / 1024;

$memStart = memory_get_usage();
$timeStart = microtime(true);
$dist2 = $ed->minDistanceOptimized($longStr1, $longStr2);
$time2 = (microtime(true) - $timeStart) * 1000;
$mem2 = (memory_get_usage() - $memStart) / 1024;

echo "String lengths: " . strlen($longStr1) . " × " . strlen($longStr2) . "\n";
echo "Standard:  Distance = $dist1, Time = " . number_format($time1, 2) . " ms, Memory = " . number_format($mem1, 2) . " KB\n";
echo "Optimized: Distance = $dist2, Time = " . number_format($time2, 2) . " ms, Memory = " . number_format($mem2, 2) . " KB\n";
echo "Memory saved: " . number_format($mem1 - $mem2, 2) . " KB (" . number_format(($mem1 - $mem2) / $mem1 * 100, 1) . "%)\n\n";

// Performance benchmark
echo str_repeat("=", 70) . "\n";
echo "Performance Benchmark\n";
echo str_repeat("=", 70) . "\n";

$sizes = [10, 50, 100, 200, 500];

foreach ($sizes as $size) {
    $str1 = substr(str_shuffle(str_repeat('abcdefghijklmnopqrstuvwxyz', 100)), 0, $size);
    $str2 = substr(str_shuffle(str_repeat('abcdefghijklmnopqrstuvwxyz', 100)), 0, $size);

    $start = microtime(true);
    $distance = $ed->minDistanceOptimized($str1, $str2);
    $time = (microtime(true) - $start) * 1000;

    echo sprintf(
        "Length %3d: Distance = %3d, Time = %7.2f ms\n",
        $size,
        $distance,
        $time
    );
}

echo "\n✓ All examples completed successfully!\n";
