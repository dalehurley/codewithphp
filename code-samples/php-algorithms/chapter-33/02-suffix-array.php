<?php
/**
 * Suffix Array Implementation
 *
 * A sorted array of all suffixes of a string, enabling fast pattern matching
 * and various string operations.
 *
 * Time Complexity:
 * - Build: O(n log n) with comparison sort
 * - Search: O(m log n + occ) where occ is occurrences
 *
 * Use cases:
 * - Pattern matching
 * - Longest repeated substring
 * - Longest common substring
 * - Data compression
 *
 * @category  Algorithms
 * @package   StringAlgorithms
 * @author    PHP Algorithms Series
 * @license   MIT
 */

declare(strict_types=1);

namespace CodeWithPhp\Algorithms\Chapter33;

/**
 * Suffix array for advanced string operations
 */
class SuffixArray
{
    private string $text;
    private array $suffixArray;
    private array $lcp = []; // Longest Common Prefix array

    /**
     * @param string $text Input text
     */
    public function __construct(string $text)
    {
        $this->text = $text . '$'; // Add sentinel character
        $this->build();
        $this->buildLCP();
    }

    /**
     * Build suffix array
     */
    private function build(): void
    {
        $n = strlen($this->text);
        $suffixes = [];

        // Create suffix indices
        for ($i = 0; $i < $n; $i++) {
            $suffixes[] = $i;
        }

        // Sort suffixes lexicographically
        usort($suffixes, function ($a, $b) {
            return strcmp(
                substr($this->text, $a),
                substr($this->text, $b)
            );
        });

        $this->suffixArray = $suffixes;
    }

    /**
     * Build LCP array using Kasai's algorithm
     */
    private function buildLCP(): void
    {
        $n = strlen($this->text);
        $this->lcp = array_fill(0, $n, 0);
        $rank = array_flip($this->suffixArray);

        $h = 0;
        for ($i = 0; $i < $n; $i++) {
            if ($rank[$i] > 0) {
                $j = $this->suffixArray[$rank[$i] - 1];

                while ($i + $h < $n && $j + $h < $n && $this->text[$i + $h] === $this->text[$j + $h]) {
                    $h++;
                }

                $this->lcp[$rank[$i]] = $h;

                if ($h > 0) {
                    $h--;
                }
            }
        }
    }

    /**
     * Search for pattern
     *
     * @param string $pattern Pattern to search
     * @return array Positions where pattern occurs
     */
    public function search(string $pattern): array
    {
        $n = strlen($this->text);
        $m = strlen($pattern);
        $positions = [];

        // Binary search for first occurrence
        $left = 0;
        $right = $n - 1;

        while ($left <= $right) {
            $mid = (int)(($left + $right) / 2);
            $suffix = substr($this->text, $this->suffixArray[$mid]);
            $cmp = strncmp($suffix, $pattern, $m);

            if ($cmp < 0) {
                $left = $mid + 1;
            } else {
                $right = $mid - 1;
            }
        }

        $start = $left;

        // Binary search for last occurrence
        $left = $start;
        $right = $n - 1;

        while ($left <= $right) {
            $mid = (int)(($left + $right) / 2);
            $suffix = substr($this->text, $this->suffixArray[$mid]);
            $cmp = strncmp($suffix, $pattern, $m);

            if ($cmp <= 0) {
                $left = $mid + 1;
            } else {
                $right = $mid - 1;
            }
        }

        $end = $right;

        // Collect all occurrences
        for ($i = $start; $i <= $end; $i++) {
            if ($i < count($this->suffixArray)) {
                $suffix = substr($this->text, $this->suffixArray[$i], $m);
                if ($suffix === $pattern) {
                    $positions[] = $this->suffixArray[$i];
                }
            }
        }

        return $positions;
    }

    /**
     * Find longest repeated substring
     *
     * @return string Longest repeated substring
     */
    public function longestRepeatedSubstring(): string
    {
        $maxLen = 0;
        $maxPos = 0;

        for ($i = 1; $i < count($this->lcp); $i++) {
            if ($this->lcp[$i] > $maxLen) {
                $maxLen = $this->lcp[$i];
                $maxPos = $this->suffixArray[$i];
            }
        }

        return substr($this->text, $maxPos, $maxLen);
    }

    /**
     * Count distinct substrings
     *
     * @return int Number of distinct substrings
     */
    public function countDistinctSubstrings(): int
    {
        $n = strlen($this->text) - 1; // Exclude sentinel
        $total = $n * ($n + 1) / 2; // Total possible substrings
        $duplicates = array_sum($this->lcp);

        return (int)($total - $duplicates);
    }

    /**
     * Find all palindromic substrings
     *
     * @return array List of palindromic substrings
     */
    public function findPalindromes(): array
    {
        $palindromes = [];
        $n = strlen($this->text) - 1;

        for ($center = 0; $center < $n; $center++) {
            // Odd length palindromes
            for ($radius = 0; $center - $radius >= 0 && $center + $radius < $n; $radius++) {
                if ($this->text[$center - $radius] !== $this->text[$center + $radius]) {
                    break;
                }

                $len = 2 * $radius + 1;
                if ($len > 1) {
                    $palindromes[] = substr($this->text, $center - $radius, $len);
                }
            }

            // Even length palindromes
            for ($radius = 0; $center - $radius >= 0 && $center + $radius + 1 < $n; $radius++) {
                if ($this->text[$center - $radius] !== $this->text[$center + $radius + 1]) {
                    break;
                }

                $len = 2 * $radius + 2;
                $palindromes[] = substr($this->text, $center - $radius, $len);
            }
        }

        return array_unique($palindromes);
    }

    /**
     * Get suffix array
     *
     * @return array Suffix array
     */
    public function getSuffixArray(): array
    {
        return $this->suffixArray;
    }

    /**
     * Get LCP array
     *
     * @return array LCP array
     */
    public function getLCP(): array
    {
        return $this->lcp;
    }

    /**
     * Get text
     *
     * @return string Original text
     */
    public function getText(): string
    {
        return substr($this->text, 0, -1); // Remove sentinel
    }
}

/**
 * Longest common substring finder
 */
class LongestCommonSubstring
{
    /**
     * Find longest common substring between two strings
     *
     * @param string $str1 First string
     * @param string $str2 Second string
     * @return array Result with substring and positions
     */
    public static function find(string $str1, string $str2): array
    {
        // Combine strings with separator
        $combined = $str1 . '#' . $str2 . '$';
        $n1 = strlen($str1);

        $sa = new SuffixArray(substr($combined, 0, -1)); // Remove our sentinel
        $suffixArray = $sa->getSuffixArray();
        $lcp = $sa->getLCP();

        $maxLen = 0;
        $maxPos = 0;
        $inStr1 = false;

        for ($i = 1; $i < count($lcp); $i++) {
            $pos1 = $suffixArray[$i];
            $pos2 = $suffixArray[$i - 1];

            // Check if suffixes come from different strings
            if (($pos1 < $n1) !== ($pos2 < $n1)) {
                if ($lcp[$i] > $maxLen) {
                    $maxLen = $lcp[$i];
                    $maxPos = $pos1;
                    $inStr1 = $pos1 < $n1;
                }
            }
        }

        $substring = substr($combined, $maxPos, $maxLen);

        return [
            'substring' => $substring,
            'length' => $maxLen,
            'pos_in_str1' => $inStr1 ? $maxPos : strpos($str1, $substring),
            'pos_in_str2' => !$inStr1 ? ($maxPos - $n1 - 1) : strpos($str2, $substring)
        ];
    }
}

/**
 * Document similarity calculator
 */
class DocumentSimilarity
{
    /**
     * Calculate similarity between documents using common substrings
     *
     * @param string $doc1 First document
     * @param string $doc2 Second document
     * @return array Similarity metrics
     */
    public static function calculate(string $doc1, string $doc2): array
    {
        $lcs = LongestCommonSubstring::find($doc1, $doc2);

        // Calculate Jaccard similarity at character level
        $set1 = array_unique(str_split($doc1));
        $set2 = array_unique(str_split($doc2));
        $intersection = array_intersect($set1, $set2);
        $union = array_unique(array_merge($set1, $set2));

        $jaccardSimilarity = count($union) > 0 ? count($intersection) / count($union) : 0;

        // Calculate overlap coefficient
        $minLength = min(strlen($doc1), strlen($doc2));
        $overlapCoefficient = $minLength > 0 ? $lcs['length'] / $minLength : 0;

        return [
            'longest_common_substring' => $lcs['substring'],
            'lcs_length' => $lcs['length'],
            'jaccard_similarity' => $jaccardSimilarity,
            'overlap_coefficient' => $overlapCoefficient,
            'similarity_score' => ($jaccardSimilarity + $overlapCoefficient) / 2
        ];
    }
}

/**
 * Example usage demonstrating suffix arrays
 */
function demonstrateSuffixArray(): void
{
    echo "=== Suffix Array Demo ===\n\n";

    // Test 1: Basic suffix array and pattern search
    echo "1. Basic Suffix Array and Pattern Search\n";
    $text = 'banana';
    $sa = new SuffixArray($text);

    echo "Text: \"{$text}\"\n";
    echo "Suffix Array: [" . implode(', ', $sa->getSuffixArray()) . "]\n";
    echo "LCP Array: [" . implode(', ', $sa->getLCP()) . "]\n\n";

    echo "Suffixes in sorted order:\n";
    foreach ($sa->getSuffixArray() as $i => $pos) {
        $suffix = substr($text . '$', $pos);
        $lcp = $sa->getLCP()[$i];
        echo "  [{$i}] pos={$pos}, lcp={$lcp}: \"{$suffix}\"\n";
    }

    // Pattern search
    $pattern = 'ana';
    $positions = $sa->search($pattern);
    echo "\nSearching for \"{$pattern}\":\n";
    echo "  Found at positions: [" . implode(', ', $positions) . "]\n";

    // Test 2: Longest repeated substring
    echo "\n2. Longest Repeated Substring\n";
    $texts = [
        'banana',
        'abracadabra',
        'mississippi',
        'aaaaaa'
    ];

    foreach ($texts as $text) {
        $sa = new SuffixArray($text);
        $lrs = $sa->longestRepeatedSubstring();
        echo "  \"{$text}\" → \"{$lrs}\"\n";
    }

    // Test 3: Count distinct substrings
    echo "\n3. Count Distinct Substrings\n";
    foreach ($texts as $text) {
        $sa = new SuffixArray($text);
        $count = $sa->countDistinctSubstrings();
        $total = strlen($text) * (strlen($text) + 1) / 2;
        echo "  \"{$text}\": {$count} distinct (out of {$total} total)\n";
    }

    // Test 4: Longest common substring
    echo "\n4. Longest Common Substring\n";
    $pairs = [
        ['banana', 'ananas'],
        ['algorithm', 'logarithm'],
        ['abcdefg', 'xyzabc'],
        ['hello world', 'world hello']
    ];

    foreach ($pairs as [$str1, $str2]) {
        $result = LongestCommonSubstring::find($str1, $str2);
        echo "  \"{$str1}\" & \"{$str2}\"\n";
        echo "    LCS: \"{$result['substring']}\" (length: {$result['length']})\n";
        echo "    Positions: str1[{$result['pos_in_str1']}], str2[{$result['pos_in_str2']}]\n";
    }

    // Test 5: Document similarity
    echo "\n5. Document Similarity\n";
    $doc1 = "The quick brown fox jumps over the lazy dog";
    $doc2 = "The lazy dog sleeps under the quick brown fox";

    echo "Document 1: \"{$doc1}\"\n";
    echo "Document 2: \"{$doc2}\"\n\n";

    $similarity = DocumentSimilarity::calculate($doc1, $doc2);
    echo "Similarity Analysis:\n";
    echo "  Longest common substring: \"{$similarity['longest_common_substring']}\"\n";
    echo "  LCS length: {$similarity['lcs_length']}\n";
    echo "  Jaccard similarity: " . number_format($similarity['jaccard_similarity'], 4) . "\n";
    echo "  Overlap coefficient: " . number_format($similarity['overlap_coefficient'], 4) . "\n";
    echo "  Overall similarity: " . number_format($similarity['similarity_score'], 4) . "\n";

    // Test 6: Palindrome finding
    echo "\n6. Finding Palindromes\n";
    $palindromicTexts = [
        'racecar',
        'abba',
        'banana'
    ];

    foreach ($palindromicTexts as $text) {
        $sa = new SuffixArray($text);
        $palindromes = $sa->findPalindromes();
        echo "  \"{$text}\":\n";

        if (empty($palindromes)) {
            echo "    No palindromes found\n";
        } else {
            $unique = array_unique($palindromes);
            sort($unique);
            echo "    Palindromes: " . implode(', ', array_slice($unique, 0, 10)) . "\n";
        }
    }

    // Test 7: Performance analysis
    echo "\n7. Performance Analysis\n";
    $sizes = [100, 1000, 5000];

    foreach ($sizes as $size) {
        $testText = str_repeat('abcdefgh', $size / 8);

        $start = microtime(true);
        $testSA = new SuffixArray($testText);
        $buildTime = microtime(true) - $start;

        $start = microtime(true);
        $positions = $testSA->search('abc');
        $searchTime = microtime(true) - $start;

        echo "  Text size: {$size} characters\n";
        echo "    Build time: " . number_format($buildTime * 1000, 2) . " ms\n";
        echo "    Search time: " . number_format($searchTime * 1000, 4) . " ms\n";
        echo "    Matches found: " . count($positions) . "\n";
    }
}

// Run if executed directly
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'] ?? '')) {
    try {
        demonstrateSuffixArray();
    } catch (\Throwable $e) {
        echo "Error: " . $e->getMessage() . "\n";
        echo $e->getTraceAsString() . "\n";
        exit(1);
    }
}
