<?php
/**
 * Advanced String Matching Algorithms
 *
 * Collection of advanced string matching algorithms including:
 * - Z-Algorithm
 * - Rabin-Karp
 * - Manacher's Algorithm (palindromes)
 * - KMP (Knuth-Morris-Pratt)
 *
 * @category  Algorithms
 * @package   StringAlgorithms
 * @author    PHP Algorithms Series
 * @license   MIT
 */

declare(strict_types=1);

namespace CodeWithPhp\Algorithms\Chapter33;

/**
 * Z-Algorithm for pattern matching
 */
class ZAlgorithm
{
    /**
     * Compute Z-array for string
     *
     * @param string $s Input string
     * @return array Z-array
     */
    public static function computeZ(string $s): array
    {
        $n = strlen($s);
        $z = array_fill(0, $n, 0);
        $z[0] = $n;

        $left = 0;
        $right = 0;

        for ($i = 1; $i < $n; $i++) {
            if ($i > $right) {
                $left = $right = $i;

                while ($right < $n && $s[$right - $left] === $s[$right]) {
                    $right++;
                }

                $z[$i] = $right - $left;
                $right--;
            } else {
                $k = $i - $left;

                if ($z[$k] < $right - $i + 1) {
                    $z[$i] = $z[$k];
                } else {
                    $left = $i;

                    while ($right < $n && $s[$right - $left] === $s[$right]) {
                        $right++;
                    }

                    $z[$i] = $right - $left;
                    $right--;
                }
            }
        }

        return $z;
    }

    /**
     * Search for pattern in text
     *
     * @param string $text Text to search in
     * @param string $pattern Pattern to find
     * @return array Positions of matches
     */
    public static function search(string $text, string $pattern): array
    {
        $combined = $pattern . '$' . $text;
        $z = self::computeZ($combined);

        $positions = [];
        $patternLen = strlen($pattern);

        for ($i = $patternLen + 1; $i < strlen($combined); $i++) {
            if ($z[$i] === $patternLen) {
                $positions[] = $i - $patternLen - 1;
            }
        }

        return $positions;
    }
}

/**
 * Rabin-Karp algorithm with rolling hash
 */
class RabinKarp
{
    private const BASE = 256;
    private const MOD = 1000000007;

    /**
     * Search for pattern using rolling hash
     *
     * @param string $text Text to search in
     * @param string $pattern Pattern to find
     * @return array Positions of matches
     */
    public static function search(string $text, string $pattern): array
    {
        $n = strlen($text);
        $m = strlen($pattern);

        if ($m > $n) {
            return [];
        }

        $positions = [];

        // Calculate hash values
        $patternHash = self::hash($pattern);
        $textHash = self::hash(substr($text, 0, $m));

        // Precompute BASE^(m-1) % MOD
        $h = 1;
        for ($i = 0; $i < $m - 1; $i++) {
            $h = ($h * self::BASE) % self::MOD;
        }

        // Slide pattern over text
        for ($i = 0; $i <= $n - $m; $i++) {
            // Check hash match
            if ($patternHash === $textHash) {
                // Verify actual string match
                if (substr($text, $i, $m) === $pattern) {
                    $positions[] = $i;
                }
            }

            // Calculate rolling hash for next window
            if ($i < $n - $m) {
                $textHash = (
                    self::BASE * ($textHash - ord($text[$i]) * $h) + ord($text[$i + $m])
                ) % self::MOD;

                // Handle negative values
                if ($textHash < 0) {
                    $textHash += self::MOD;
                }
            }
        }

        return $positions;
    }

    /**
     * Hash string
     *
     * @param string $s String to hash
     * @return int Hash value
     */
    private static function hash(string $s): int
    {
        $hash = 0;
        $n = strlen($s);

        for ($i = 0; $i < $n; $i++) {
            $hash = ($hash * self::BASE + ord($s[$i])) % self::MOD;
        }

        return $hash;
    }

    /**
     * Search for multiple patterns
     *
     * @param string $text Text to search in
     * @param array $patterns Patterns to find
     * @return array Results per pattern
     */
    public static function searchMultiple(string $text, array $patterns): array
    {
        $results = [];

        foreach ($patterns as $pattern) {
            $results[$pattern] = self::search($text, $pattern);
        }

        return $results;
    }
}

/**
 * Manacher's algorithm for palindrome detection
 */
class ManacherAlgorithm
{
    /**
     * Find longest palindrome in string
     *
     * @param string $s Input string
     * @return string Longest palindrome
     */
    public static function longestPalindrome(string $s): string
    {
        // Transform string: "abc" -> "^#a#b#c#$"
        $t = '^#' . implode('#', str_split($s)) . '#$';
        $n = strlen($t);
        $p = array_fill(0, $n, 0);

        $center = 0;
        $right = 0;

        for ($i = 1; $i < $n - 1; $i++) {
            $mirror = 2 * $center - $i;

            if ($i < $right) {
                $p[$i] = min($right - $i, $p[$mirror]);
            }

            // Expand around center
            while ($t[$i + $p[$i] + 1] === $t[$i - $p[$i] - 1]) {
                $p[$i]++;
            }

            // Update center and right boundary
            if ($i + $p[$i] > $right) {
                $center = $i;
                $right = $i + $p[$i];
            }
        }

        // Find longest palindrome
        $maxLen = 0;
        $centerIndex = 0;

        for ($i = 1; $i < $n - 1; $i++) {
            if ($p[$i] > $maxLen) {
                $maxLen = $p[$i];
                $centerIndex = $i;
            }
        }

        $start = (int)(($centerIndex - $maxLen) / 2);
        return substr($s, $start, $maxLen);
    }

    /**
     * Find all palindromic substrings
     *
     * @param string $s Input string
     * @return array List of palindromes
     */
    public static function allPalindromes(string $s): array
    {
        $t = '^#' . implode('#', str_split($s)) . '#$';
        $n = strlen($t);
        $p = array_fill(0, $n, 0);

        $center = 0;
        $right = 0;

        for ($i = 1; $i < $n - 1; $i++) {
            $mirror = 2 * $center - $i;

            if ($i < $right) {
                $p[$i] = min($right - $i, $p[$mirror]);
            }

            while ($t[$i + $p[$i] + 1] === $t[$i - $p[$i] - 1]) {
                $p[$i]++;
            }

            if ($i + $p[$i] > $right) {
                $center = $i;
                $right = $i + $p[$i];
            }
        }

        $palindromes = [];

        for ($i = 1; $i < $n - 1; $i++) {
            if ($p[$i] > 0) {
                $start = (int)(($i - $p[$i]) / 2);
                $len = $p[$i];
                $palindromes[] = substr($s, $start, $len);
            }
        }

        return array_unique($palindromes);
    }
}

/**
 * KMP (Knuth-Morris-Pratt) algorithm
 */
class KMP
{
    /**
     * Compute prefix function for pattern
     *
     * @param string $pattern Pattern
     * @return array Prefix function array
     */
    private static function computePrefixFunction(string $pattern): array
    {
        $m = strlen($pattern);
        $pi = array_fill(0, $m, 0);

        for ($i = 1; $i < $m; $i++) {
            $j = $pi[$i - 1];

            while ($j > 0 && $pattern[$i] !== $pattern[$j]) {
                $j = $pi[$j - 1];
            }

            if ($pattern[$i] === $pattern[$j]) {
                $j++;
            }

            $pi[$i] = $j;
        }

        return $pi;
    }

    /**
     * Search for pattern using KMP
     *
     * @param string $text Text to search in
     * @param string $pattern Pattern to find
     * @return array Positions of matches
     */
    public static function search(string $text, string $pattern): array
    {
        $n = strlen($text);
        $m = strlen($pattern);

        if ($m === 0) {
            return [];
        }

        $pi = self::computePrefixFunction($pattern);
        $positions = [];
        $j = 0;

        for ($i = 0; $i < $n; $i++) {
            while ($j > 0 && $text[$i] !== $pattern[$j]) {
                $j = $pi[$j - 1];
            }

            if ($text[$i] === $pattern[$j]) {
                $j++;
            }

            if ($j === $m) {
                $positions[] = $i - $m + 1;
                $j = $pi[$j - 1];
            }
        }

        return $positions;
    }
}

/**
 * Text processor with multiple algorithms
 */
class AdvancedTextProcessor
{
    /**
     * Find pattern using best algorithm
     *
     * @param string $text Text to search
     * @param string $pattern Pattern to find
     * @param string $algorithm Algorithm to use
     * @return array Results with positions and timing
     */
    public static function findPattern(string $text, string $pattern, string $algorithm = 'auto'): array
    {
        $start = microtime(true);

        $positions = match ($algorithm) {
            'z' => ZAlgorithm::search($text, $pattern),
            'rabin-karp' => RabinKarp::search($text, $pattern),
            'kmp' => KMP::search($text, $pattern),
            'auto', 'native' => self::nativeSearch($text, $pattern),
            default => []
        };

        $time = microtime(true) - $start;

        return [
            'algorithm' => $algorithm,
            'positions' => $positions,
            'count' => count($positions),
            'time' => $time
        ];
    }

    /**
     * Native PHP search for comparison
     *
     * @param string $text Text
     * @param string $pattern Pattern
     * @return array Positions
     */
    private static function nativeSearch(string $text, string $pattern): array
    {
        $positions = [];
        $offset = 0;

        while (($pos = strpos($text, $pattern, $offset)) !== false) {
            $positions[] = $pos;
            $offset = $pos + 1;
        }

        return $positions;
    }

    /**
     * Compare all algorithms
     *
     * @param string $text Text to search
     * @param string $pattern Pattern to find
     * @return array Comparison results
     */
    public static function compareAlgorithms(string $text, string $pattern): array
    {
        $algorithms = ['native', 'z', 'rabin-karp', 'kmp'];
        $results = [];

        foreach ($algorithms as $algo) {
            $results[$algo] = self::findPattern($text, $pattern, $algo);
        }

        return $results;
    }
}

/**
 * Example usage demonstrating advanced string matching
 */
function demonstrateAdvancedStringMatching(): void
{
    echo "=== Advanced String Matching Demo ===\n\n";

    // Test 1: Z-Algorithm
    echo "1. Z-Algorithm\n";
    $text = 'abababab';
    $pattern = 'abab';

    $positions = ZAlgorithm::search($text, $pattern);
    echo "Text: \"{$text}\"\n";
    echo "Pattern: \"{$pattern}\"\n";
    echo "Positions: [" . implode(', ', $positions) . "]\n";

    $zArray = ZAlgorithm::computeZ($text);
    echo "Z-array: [" . implode(', ', $zArray) . "]\n";

    // Test 2: Rabin-Karp
    echo "\n2. Rabin-Karp Algorithm\n";
    $text = 'the quick brown fox jumps over the lazy dog';
    $patterns = ['the', 'fox', 'lazy'];

    echo "Text: \"{$text}\"\n";
    $results = RabinKarp::searchMultiple($text, $patterns);

    foreach ($results as $pattern => $positions) {
        echo "  \"{$pattern}\": [" . implode(', ', $positions) . "]\n";
    }

    // Test 3: Manacher's Algorithm
    echo "\n3. Manacher's Algorithm (Palindromes)\n";
    $strings = ['babad', 'cbbd', 'racecar', 'abba'];

    foreach ($strings as $str) {
        $longest = ManacherAlgorithm::longestPalindrome($str);
        echo "  \"{$str}\" → longest: \"{$longest}\"\n";
    }

    echo "\nAll palindromes in 'ababa':\n";
    $palindromes = ManacherAlgorithm::allPalindromes('ababa');
    echo "  " . implode(', ', $palindromes) . "\n";

    // Test 4: KMP Algorithm
    echo "\n4. KMP Algorithm\n";
    $text = 'ababcabcabababd';
    $pattern = 'ababd';

    $positions = KMP::search($text, $pattern);
    echo "Text: \"{$text}\"\n";
    echo "Pattern: \"{$pattern}\"\n";
    echo "Positions: [" . implode(', ', $positions) . "]\n";

    // Test 5: Algorithm comparison
    echo "\n5. Performance Comparison\n";
    $testText = str_repeat('abcdefghij', 1000);
    $testPattern = 'defgh';

    echo "Text length: " . strlen($testText) . " characters\n";
    echo "Pattern: \"{$testPattern}\"\n\n";

    $comparison = AdvancedTextProcessor::compareAlgorithms($testText, $testPattern);

    $fastestTime = min(array_column($comparison, 'time'));

    foreach ($comparison as $algo => $result) {
        $speedup = $fastestTime / $result['time'];
        echo sprintf(
            "  %-15s: %d matches in %.4f ms (%.2fx)\n",
            $algo,
            $result['count'],
            $result['time'] * 1000,
            $speedup
        );
    }

    // Test 6: Pattern matching in DNA sequences
    echo "\n6. DNA Sequence Analysis\n";
    $dnaSequence = 'ATCGATCGATCGATCGTAGCTAGCTAGCT';
    $motif = 'ATCG';

    $methods = ['z' => 'Z-Algorithm', 'kmp' => 'KMP', 'rabin-karp' => 'Rabin-Karp'];

    echo "DNA Sequence: {$dnaSequence}\n";
    echo "Searching for motif: {$motif}\n\n";

    foreach ($methods as $method => $name) {
        $result = AdvancedTextProcessor::findPattern($dnaSequence, $motif, $method);
        echo "  {$name}: {$result['count']} occurrences at positions [" . implode(', ', $result['positions']) . "]\n";
    }

    // Test 7: Palindrome analysis
    echo "\n7. Comprehensive Palindrome Analysis\n";
    $text = 'A man, a plan, a canal: Panama';
    $cleanText = strtolower(preg_replace('/[^a-zA-Z]/', '', $text));

    echo "Original: \"{$text}\"\n";
    echo "Cleaned: \"{$cleanText}\"\n";

    $longest = ManacherAlgorithm::longestPalindrome($cleanText);
    echo "Longest palindrome: \"{$longest}\" (length: " . strlen($longest) . ")\n";

    $allPalindromes = ManacherAlgorithm::allPalindromes($cleanText);
    echo "Total palindromes found: " . count($allPalindromes) . "\n";
    echo "Sample: " . implode(', ', array_slice($allPalindromes, 0, 10)) . "\n";

    // Test 8: Repeated pattern detection
    echo "\n8. Repeated Pattern Detection\n";
    $repeatingText = 'abcabcabcabc';

    echo "Text: \"{$repeatingText}\"\n";
    $zArray = ZAlgorithm::computeZ($repeatingText);

    // Find period
    $n = strlen($repeatingText);
    $period = 0;

    for ($i = 1; $i < $n; $i++) {
        if ($i + $zArray[$i] === $n) {
            $period = $i;
            break;
        }
    }

    if ($period > 0) {
        $repeatingPattern = substr($repeatingText, 0, $period);
        echo "Repeating pattern: \"{$repeatingPattern}\" (period: {$period})\n";
        echo "Repetitions: " . ($n / $period) . "\n";
    } else {
        echo "No repeating pattern found\n";
    }
}

// Run if executed directly
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'] ?? '')) {
    try {
        demonstrateAdvancedStringMatching();
    } catch (\Throwable $e) {
        echo "Error: " . $e->getMessage() . "\n";
        echo $e->getTraceAsString() . "\n";
        exit(1);
    }
}
