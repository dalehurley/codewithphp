# Chapter 33: String Algorithms Deep Dive

## Introduction

String algorithms are fundamental to text processing, search engines, bioinformatics, and data compression. This chapter explores advanced string matching and manipulation techniques beyond basic patterns.

## Aho-Corasick Algorithm

The Aho-Corasick algorithm efficiently searches for multiple patterns simultaneously in O(n + m + z) time, where n is text length, m is total pattern length, and z is number of matches.

### Trie-Based Implementation

```php
class AhoCorasickNode {
    public array $children = [];
    public ?AhoCorasickNode $failure = null;
    public array $output = [];
    public int $depth = 0;
}

class AhoCorasick {
    private AhoCorasickNode $root;
    private array $patterns = [];

    public function __construct() {
        $this->root = new AhoCorasickNode();
    }

    public function addPattern(string $pattern, $id = null): void {
        $node = $this->root;
        $id = $id ?? $pattern;
        $this->patterns[$id] = $pattern;

        // Build trie
        for ($i = 0; $i < strlen($pattern); $i++) {
            $char = $pattern[$i];

            if (!isset($node->children[$char])) {
                $node->children[$char] = new AhoCorasickNode();
                $node->children[$char]->depth = $node->depth + 1;
            }

            $node = $node->children[$char];
        }

        // Mark as output node
        $node->output[] = $id;
    }

    public function build(): void {
        // Build failure links using BFS
        $queue = new SplQueue();

        // All children of root fail to root
        foreach ($this->root->children as $child) {
            $child->failure = $this->root;
            $queue->enqueue($child);
        }

        while (!$queue->isEmpty()) {
            $current = $queue->dequeue();

            foreach ($current->children as $char => $child) {
                $queue->enqueue($child);

                // Find failure link
                $failure = $current->failure;

                while ($failure !== null && !isset($failure->children[$char])) {
                    $failure = $failure->failure;
                }

                $child->failure = $failure !== null && isset($failure->children[$char])
                    ? $failure->children[$char]
                    : $this->root;

                // Merge output from failure node
                $child->output = array_merge($child->output, $child->failure->output);
            }
        }
    }

    public function search(string $text): array {
        $results = [];
        $node = $this->root;

        for ($i = 0; $i < strlen($text); $i++) {
            $char = $text[$i];

            // Follow failure links until we find a match or reach root
            while ($node !== $this->root && !isset($node->children[$char])) {
                $node = $node->failure;
            }

            if (isset($node->children[$char])) {
                $node = $node->children[$char];

                // Record all matches at this position
                foreach ($node->output as $patternId) {
                    $pattern = $this->patterns[$patternId];
                    $start = $i - strlen($pattern) + 1;

                    $results[] = [
                        'pattern' => $pattern,
                        'id' => $patternId,
                        'start' => $start,
                        'end' => $i + 1
                    ];
                }
            }
        }

        return $results;
    }

    public function replace(string $text, array $replacements): string {
        $matches = $this->search($text);

        // Sort by start position in reverse to avoid offset issues
        usort($matches, fn($a, $b) => $b['start'] - $a['start']);

        foreach ($matches as $match) {
            $replacement = $replacements[$match['id']] ?? $match['pattern'];
            $text = substr_replace($text, $replacement, $match['start'], strlen($match['pattern']));
        }

        return $text;
    }
}

// Usage
$ac = new AhoCorasick();

$ac->addPattern('he', 'word_he');
$ac->addPattern('she', 'word_she');
$ac->addPattern('his', 'word_his');
$ac->addPattern('hers', 'word_hers');

$ac->build();

$text = 'she sells his hers by the seashore';
$matches = $ac->search($text);

foreach ($matches as $match) {
    echo "Found '{$match['pattern']}' at position {$match['start']}\n";
}

// Replace all patterns
$replacements = [
    'word_he' => 'HE',
    'word_she' => 'SHE',
    'word_his' => 'HIS',
    'word_hers' => 'HERS'
];

$replaced = $ac->replace($text, $replacements);
echo "Replaced: $replaced\n";
```

**Time Complexity**:
- Build: O(m) where m is total pattern length
- Search: O(n + z) where n is text length, z is number of matches
**Space Complexity**: O(m × σ) where σ is alphabet size

### Real-World Example: Content Filtering

```php
class ContentFilter {
    private AhoCorasick $profanityFilter;
    private AhoCorasick $spamFilter;

    public function __construct() {
        $this->profanityFilter = new AhoCorasick();
        $this->spamFilter = new AhoCorasick();
    }

    public function loadProfanityList(array $words): void {
        foreach ($words as $word) {
            $this->profanityFilter->addPattern(strtolower($word));
        }
        $this->profanityFilter->build();
    }

    public function loadSpamPatterns(array $patterns): void {
        foreach ($patterns as $pattern) {
            $this->spamFilter->addPattern(strtolower($pattern));
        }
        $this->spamFilter->build();
    }

    public function filterContent(string $content): array {
        $lowerContent = strtolower($content);

        $profanity = $this->profanityFilter->search($lowerContent);
        $spam = $this->spamFilter->search($lowerContent);

        return [
            'is_clean' => empty($profanity) && empty($spam),
            'profanity_count' => count($profanity),
            'spam_count' => count($spam),
            'violations' => array_merge($profanity, $spam)
        ];
    }

    public function censorContent(string $content): string {
        $replacements = [];
        $matches = $this->profanityFilter->search(strtolower($content));

        foreach ($matches as $match) {
            $replacements[$match['id']] = str_repeat('*', strlen($match['pattern']));
        }

        return $this->profanityFilter->replace($content, $replacements);
    }
}

// Usage
$filter = new ContentFilter();

$filter->loadProfanityList(['bad', 'worse', 'worst']);
$filter->loadSpamPatterns(['click here', 'buy now', 'limited offer']);

$content = 'Click here to buy now! This is a bad deal with limited offer.';

$analysis = $filter->filterContent($content);
print_r($analysis);

$censored = $filter->censorContent($content);
echo "Censored: $censored\n";
```

## Suffix Array

A suffix array is a sorted array of all suffixes of a string, enabling fast pattern matching and other string operations.

### Construction and Search

```php
class SuffixArray {
    private string $text;
    private array $suffixArray;
    private array $lcp;  // Longest Common Prefix

    public function __construct(string $text) {
        $this->text = $text . '$';  // Add sentinel
        $this->build();
        $this->buildLCP();
    }

    private function build(): void {
        $n = strlen($this->text);
        $suffixes = [];

        // Create suffix indices
        for ($i = 0; $i < $n; $i++) {
            $suffixes[] = $i;
        }

        // Sort suffixes
        usort($suffixes, function ($a, $b) {
            return strcmp(
                substr($this->text, $a),
                substr($this->text, $b)
            );
        });

        $this->suffixArray = $suffixes;
    }

    private function buildLCP(): void {
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

    public function search(string $pattern): array {
        $positions = [];
        $n = strlen($this->text);
        $m = strlen($pattern);

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
            $suffix = substr($this->text, $this->suffixArray[$i], $m);
            if ($suffix === $pattern) {
                $positions[] = $this->suffixArray[$i];
            }
        }

        return $positions;
    }

    public function longestRepeatedSubstring(): string {
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

    public function longestCommonSubstring(string $other): string {
        $combined = $this->text . '#' . $other . '$';
        $sa = new SuffixArray(substr($combined, 0, -1));  // Remove our sentinel

        $n1 = strlen($this->text);
        $maxLen = 0;
        $maxPos = 0;

        for ($i = 1; $i < count($sa->lcp); $i++) {
            $pos1 = $sa->suffixArray[$i];
            $pos2 = $sa->suffixArray[$i - 1];

            // Check if suffixes come from different strings
            if (($pos1 < $n1) !== ($pos2 < $n1)) {
                if ($sa->lcp[$i] > $maxLen) {
                    $maxLen = $sa->lcp[$i];
                    $maxPos = $pos1;
                }
            }
        }

        return substr($combined, $maxPos, $maxLen);
    }

    public function countDistinctSubstrings(): int {
        $n = strlen($this->text);
        $total = $n * ($n - 1) / 2;  // Total possible substrings
        $duplicates = array_sum($this->lcp);

        return $total - $duplicates;
    }
}

// Usage
$sa = new SuffixArray('banana');

// Search for pattern
$positions = $sa->search('ana');
print_r($positions);  // [1, 3]

// Find longest repeated substring
echo $sa->longestRepeatedSubstring() . "\n";  // 'ana'

// Find longest common substring
$sa2 = new SuffixArray('ananas');
echo $sa->longestCommonSubstring('ananas') . "\n";  // 'anana'

// Count distinct substrings
echo $sa->countDistinctSubstrings() . "\n";
```

**Time Complexity**:
- Build: O(n log n) with comparison sort, O(n) with specialized algorithms
- Search: O(m log n + occ) where occ is number of occurrences
**Space Complexity**: O(n)

## Suffix Tree

A suffix tree is a compressed trie of all suffixes, enabling linear-time pattern matching and substring queries.

### Simplified Suffix Tree

```php
class SuffixTreeNode {
    public array $children = [];
    public ?int $start = null;
    public ?int $end = null;
    public ?int $suffixIndex = null;
}

class SuffixTree {
    private string $text;
    private SuffixTreeNode $root;

    public function __construct(string $text) {
        $this->text = $text . '$';
        $this->root = new SuffixTreeNode();
        $this->buildNaive();
    }

    private function buildNaive(): void {
        $n = strlen($this->text);

        // Add all suffixes
        for ($i = 0; $i < $n; $i++) {
            $this->addSuffix($i);
        }
    }

    private function addSuffix(int $suffixStart): void {
        $node = $this->root;
        $pos = $suffixStart;
        $n = strlen($this->text);

        while ($pos < $n) {
            $char = $this->text[$pos];

            if (!isset($node->children[$char])) {
                // Create new leaf
                $leaf = new SuffixTreeNode();
                $leaf->start = $pos;
                $leaf->end = $n - 1;
                $leaf->suffixIndex = $suffixStart;

                $node->children[$char] = $leaf;
                return;
            }

            // Follow existing edge
            $child = $node->children[$char];
            $edgeLen = $child->end - $child->start + 1;
            $matched = 0;

            // Match along edge
            while ($matched < $edgeLen && $pos < $n && $this->text[$child->start + $matched] === $this->text[$pos]) {
                $matched++;
                $pos++;
            }

            if ($matched === $edgeLen) {
                // Fully matched edge, continue to child
                $node = $child;
            } else {
                // Partial match, split edge
                $split = new SuffixTreeNode();
                $split->start = $child->start;
                $split->end = $child->start + $matched - 1;

                $child->start += $matched;
                $split->children[$this->text[$child->start]] = $child;

                // Add new leaf for remaining suffix
                $leaf = new SuffixTreeNode();
                $leaf->start = $pos;
                $leaf->end = $n - 1;
                $leaf->suffixIndex = $suffixStart;

                $split->children[$this->text[$pos]] = $leaf;
                $node->children[$char] = $split;

                return;
            }
        }
    }

    public function search(string $pattern): bool {
        $node = $this->root;
        $pos = 0;
        $patternLen = strlen($pattern);

        while ($pos < $patternLen) {
            $char = $pattern[$pos];

            if (!isset($node->children[$char])) {
                return false;
            }

            $child = $node->children[$char];
            $edgeStart = $child->start;
            $edgeEnd = $child->end;

            // Match along edge
            for ($i = $edgeStart; $i <= $edgeEnd && $pos < $patternLen; $i++, $pos++) {
                if ($this->text[$i] !== $pattern[$pos]) {
                    return false;
                }
            }

            $node = $child;
        }

        return true;
    }

    public function getAllOccurrences(string $pattern): array {
        $positions = [];
        $node = $this->findNode($pattern);

        if ($node !== null) {
            $this->collectLeaves($node, $positions);
        }

        return $positions;
    }

    private function findNode(string $pattern): ?SuffixTreeNode {
        $node = $this->root;
        $pos = 0;
        $patternLen = strlen($pattern);

        while ($pos < $patternLen) {
            $char = $pattern[$pos];

            if (!isset($node->children[$char])) {
                return null;
            }

            $child = $node->children[$char];
            $edgeStart = $child->start;
            $edgeEnd = $child->end;

            for ($i = $edgeStart; $i <= $edgeEnd && $pos < $patternLen; $i++, $pos++) {
                if ($this->text[$i] !== $pattern[$pos]) {
                    return null;
                }
            }

            $node = $child;
        }

        return $node;
    }

    private function collectLeaves(SuffixTreeNode $node, array &$positions): void {
        if (empty($node->children)) {
            // Leaf node
            $positions[] = $node->suffixIndex;
            return;
        }

        foreach ($node->children as $child) {
            $this->collectLeaves($child, $positions);
        }
    }

    public function longestRepeatedSubstring(): string {
        $maxLen = 0;
        $maxStr = '';

        $this->dfsLongestRepeat($this->root, '', $maxLen, $maxStr);

        return $maxStr;
    }

    private function dfsLongestRepeat(SuffixTreeNode $node, string $path, int &$maxLen, string &$maxStr): int {
        if (empty($node->children)) {
            return 1;  // Leaf node
        }

        $totalChildren = 0;

        foreach ($node->children as $child) {
            $edgeLabel = substr($this->text, $child->start, $child->end - $child->start + 1);
            $childPath = $path . $edgeLabel;

            $childCount = $this->dfsLongestRepeat($child, $childPath, $maxLen, $maxStr);
            $totalChildren += $childCount;
        }

        // Internal node with multiple children = repeated substring
        if ($totalChildren > 1 && strlen($path) > $maxLen) {
            $maxLen = strlen($path);
            $maxStr = $path;
        }

        return $totalChildren;
    }
}

// Usage
$st = new SuffixTree('banana');

var_dump($st->search('ana'));  // true
var_dump($st->search('xyz'));  // false

$occurrences = $st->getAllOccurrences('ana');
print_r($occurrences);  // [1, 3]

echo $st->longestRepeatedSubstring() . "\n";  // 'ana'
```

**Time Complexity**:
- Ukkonen's algorithm: O(n) construction
- Search: O(m) where m is pattern length
**Space Complexity**: O(n)

## Z-Algorithm

Fast string matching using preprocessing.

```php
class ZAlgorithm {
    public static function computeZ(string $s): array {
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

    public static function search(string $text, string $pattern): array {
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

// Usage
$positions = ZAlgorithm::search('abababab', 'abab');
print_r($positions);  // [0, 2, 4]
```

**Time Complexity**: O(n + m)
**Space Complexity**: O(n + m)

## Manacher's Algorithm

Find all palindromes in linear time.

```php
class ManacherAlgorithm {
    public static function longestPalindrome(string $s): string {
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

    public static function allPalindromes(string $s): array {
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

// Usage
$longest = ManacherAlgorithm::longestPalindrome('babad');
echo "Longest palindrome: $longest\n";  // 'bab' or 'aba'

$all = ManacherAlgorithm::allPalindromes('ababa');
print_r($all);  // ['a', 'b', 'aba', 'bab', 'ababa']
```

**Time Complexity**: O(n)
**Space Complexity**: O(n)

## Rabin-Karp with Rolling Hash

```php
class RabinKarp {
    private const BASE = 256;
    private const MOD = 1000000007;

    public static function search(string $text, string $pattern): array {
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
                // Verify actual string match (handle collisions)
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

    private static function hash(string $s): int {
        $hash = 0;
        $n = strlen($s);

        for ($i = 0; $i < $n; $i++) {
            $hash = ($hash * self::BASE + ord($s[$i])) % self::MOD;
        }

        return $hash;
    }

    public static function searchMultiple(string $text, array $patterns): array {
        $results = [];

        foreach ($patterns as $pattern) {
            $results[$pattern] = self::search($text, $pattern);
        }

        return $results;
    }
}

// Usage
$text = 'abracadabra';
$positions = RabinKarp::search($text, 'abra');
print_r($positions);  // [0, 7]

$multiple = RabinKarp::searchMultiple($text, ['abra', 'cad', 'bra']);
print_r($multiple);
```

**Time Complexity**: O(n + m) average, O(nm) worst case
**Space Complexity**: O(1)

## Longest Common Subsequence (LCS)

```php
class LongestCommonSubsequence {
    public static function compute(string $s1, string $s2): string {
        $m = strlen($s1);
        $n = strlen($s2);

        // Build DP table
        $dp = array_fill(0, $m + 1, array_fill(0, $n + 1, 0));

        for ($i = 1; $i <= $m; $i++) {
            for ($j = 1; $j <= $n; $j++) {
                if ($s1[$i - 1] === $s2[$j - 1]) {
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
            if ($s1[$i - 1] === $s2[$j - 1]) {
                $lcs = $s1[$i - 1] . $lcs;
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

    public static function length(string $s1, string $s2): int {
        return strlen(self::compute($s1, $s2));
    }

    public static function diff(string $s1, string $s2): array {
        $lcs = self::compute($s1, $s2);
        $diff = [];

        $i = 0;
        $j = 0;
        $k = 0;

        while ($i < strlen($s1) || $j < strlen($s2)) {
            if ($k < strlen($lcs) && $i < strlen($s1) && $s1[$i] === $lcs[$k]) {
                $diff[] = ['type' => 'equal', 'char' => $s1[$i]];
                $i++;
                $j++;
                $k++;
            } elseif ($i < strlen($s1) && ($k >= strlen($lcs) || $s1[$i] !== $lcs[$k])) {
                $diff[] = ['type' => 'delete', 'char' => $s1[$i]];
                $i++;
            } else {
                $diff[] = ['type' => 'insert', 'char' => $s2[$j]];
                $j++;
            }
        }

        return $diff;
    }
}

// Usage
$lcs = LongestCommonSubsequence::compute('ABCDGH', 'AEDFHR');
echo "LCS: $lcs\n";  // 'ADH'

$diff = LongestCommonSubsequence::diff('ABCD', 'ACBD');
print_r($diff);
```

**Time Complexity**: O(mn)
**Space Complexity**: O(mn)

## Summary

Advanced string algorithms provide efficient solutions for:
- **Multi-pattern matching**: Aho-Corasick
- **Substring queries**: Suffix arrays/trees
- **Fast search**: Z-algorithm, Rabin-Karp
- **Palindrome detection**: Manacher's algorithm
- **Sequence comparison**: LCS

**Key Takeaways**:
- Aho-Corasick: Best for multiple pattern search
- Suffix structures: Enable complex substring queries
- Rolling hash: Fast average-case matching
- Manacher: Linear-time palindrome finding

## Next Steps

- **Chapter 17: Advanced Sorting** - String sorting algorithms
- **Chapter 23: Dynamic Programming** - Edit distance, pattern matching
- **Chapter 26: Approximate Algorithms** - Fuzzy string matching

## Practice Exercises

1. Implement plagiarism detection using suffix arrays
2. Build an autocomplete system using suffix trees
3. Create DNA sequence matcher with Aho-Corasick
4. Implement diff tool using LCS
5. Build palindrome detector for large texts
