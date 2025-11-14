<?php

declare(strict_types=1);

/**
 * Longest Common Subsequence (LCS)
 *
 * Find longest subsequence common to two sequences.
 * Used in diff algorithms, DNA sequence analysis, etc.
 *
 * Time Complexity: O(m × n)
 * Space Complexity: O(m × n) or O(min(m,n)) optimized
 *
 * PHP Version: 8.0+
 */

class LCS
{
    /**
     * Find LCS length
     *
     * @param string $text1 First string
     * @param string $text2 Second string
     * @return int LCS length
     */
    public function length(string $text1, string $text2): int
    {
        $m = strlen($text1);
        $n = strlen($text2);
        $dp = array_fill(0, $m + 1, array_fill(0, $n + 1, 0));

        for ($i = 1; $i <= $m; $i++) {
            for ($j = 1; $j <= $n; $j++) {
                if ($text1[$i - 1] === $text2[$j - 1]) {
                    $dp[$i][$j] = $dp[$i - 1][$j - 1] + 1;
                } else {
                    $dp[$i][$j] = max($dp[$i - 1][$j], $dp[$i][$j - 1]);
                }
            }
        }

        return $dp[$m][$n];
    }

    /**
     * Find actual LCS string
     *
     * @param string $text1 First string
     * @param string $text2 Second string
     * @return string LCS string
     */
    public function find(string $text1, string $text2): string
    {
        $m = strlen($text1);
        $n = strlen($text2);
        $dp = array_fill(0, $m + 1, array_fill(0, $n + 1, 0));

        // Fill DP table
        for ($i = 1; $i <= $m; $i++) {
            for ($j = 1; $j <= $n; $j++) {
                if ($text1[$i - 1] === $text2[$j - 1]) {
                    $dp[$i][$j] = $dp[$i - 1][$j - 1] + 1;
                } else {
                    $dp[$i][$j] = max($dp[$i - 1][$j], $dp[$i][$j - 1]);
                }
            }
        }

        // Reconstruct LCS
        $lcs = '';
        $i = $m;
        $j = $n;

        while ($i > 0 && $j > 0) {
            if ($text1[$i - 1] === $text2[$j - 1]) {
                $lcs = $text1[$i - 1] . $lcs;
                $i--;
                $j--;
            } elseif ($dp[$i - 1][$j] > $dp[$i][$j - 1]) {
                $i--;
            } else {
                $j--;
            }
        }

        return $lcs;
    }
}

// ============================================================================
// Example Usage
// ============================================================================

echo "=== Longest Common Subsequence ===\n\n";

$lcs = new LCS();

$text1 = 'AGGTAB';
$text2 = 'GXTXAYB';

echo "String 1: $text1\n";
echo "String 2: $text2\n\n";

$length = $lcs->length($text1, $text2);
echo "LCS Length: $length\n";

$result = $lcs->find($text1, $text2);
echo "LCS: $result\n\n";

// More examples
$examples = [
    ['ABCDGH', 'AEDFHR'],
    ['ABC', 'AC'],
    ['ABC', 'DEF']
];

echo "=== More Examples ===\n";
foreach ($examples as [$s1, $s2]) {
    $result = $lcs->find($s1, $s2);
    $len = strlen($result);
    echo "'$s1' and '$s2': '$result' (length: $len)\n";
}

echo "\n=== Done ===\n";
