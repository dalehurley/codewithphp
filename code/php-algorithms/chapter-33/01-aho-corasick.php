<?php
/**
 * Aho-Corasick Algorithm Implementation
 *
 * Efficient multi-pattern string matching algorithm. Searches for multiple
 * patterns simultaneously in O(n + m + z) time.
 *
 * Time Complexity:
 * - Build: O(m) where m is total pattern length
 * - Search: O(n + z) where n is text length, z is matches
 *
 * Use cases:
 * - Content filtering
 * - Virus scanning
 * - Plagiarism detection
 * - Text replacement
 *
 * @category  Algorithms
 * @package   StringAlgorithms
 * @author    PHP Algorithms Series
 * @license   MIT
 */

declare(strict_types=1);

namespace CodeWithPhp\Algorithms\Chapter33;

/**
 * Node in Aho-Corasick trie
 */
class AhoCorasickNode
{
    public array $children = [];
    public ?AhoCorasickNode $failure = null;
    public array $output = [];
    public int $depth = 0;
}

/**
 * Aho-Corasick algorithm for multi-pattern matching
 */
class AhoCorasick
{
    private AhoCorasickNode $root;
    private array $patterns = [];
    private bool $built = false;

    public function __construct()
    {
        $this->root = new AhoCorasickNode();
    }

    /**
     * Add pattern to the automaton
     *
     * @param string $pattern Pattern to add
     * @param mixed $id Pattern identifier (defaults to pattern itself)
     */
    public function addPattern(string $pattern, mixed $id = null): void
    {
        if ($this->built) {
            throw new \RuntimeException("Cannot add patterns after build()");
        }

        $id = $id ?? $pattern;
        $this->patterns[$id] = $pattern;

        $node = $this->root;

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

    /**
     * Build failure links (must be called before search)
     */
    public function build(): void
    {
        if ($this->built) {
            return;
        }

        $queue = new \SplQueue();

        // All children of root fail to root
        foreach ($this->root->children as $child) {
            $child->failure = $this->root;
            $queue->enqueue($child);
        }

        // BFS to build failure links
        while (!$queue->isEmpty()) {
            $current = $queue->dequeue();

            foreach ($current->children as $char => $child) {
                $queue->enqueue($child);

                // Find failure link
                $failure = $current->failure;

                while ($failure !== null && !isset($failure->children[$char])) {
                    $failure = $failure->failure;
                }

                $child->failure = ($failure !== null && isset($failure->children[$char]))
                    ? $failure->children[$char]
                    : $this->root;

                // Merge output from failure node
                $child->output = array_merge($child->output, $child->failure->output);
            }
        }

        $this->built = true;
    }

    /**
     * Search for all patterns in text
     *
     * @param string $text Text to search in
     * @return array Array of matches with pattern, position info
     */
    public function search(string $text): array
    {
        if (!$this->built) {
            $this->build();
        }

        $results = [];
        $node = $this->root;

        for ($i = 0; $i < strlen($text); $i++) {
            $char = $text[$i];

            // Follow failure links
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
                        'end' => $i + 1,
                        'length' => strlen($pattern)
                    ];
                }
            }
        }

        return $results;
    }

    /**
     * Replace all pattern occurrences in text
     *
     * @param string $text Text to process
     * @param array $replacements Map of pattern ID => replacement
     * @return string Processed text
     */
    public function replace(string $text, array $replacements): string
    {
        $matches = $this->search($text);

        // Sort by start position in reverse
        usort($matches, fn($a, $b) => $b['start'] - $a['start']);

        foreach ($matches as $match) {
            $replacement = $replacements[$match['id']] ?? $match['pattern'];
            $text = substr_replace($text, $replacement, $match['start'], $match['length']);
        }

        return $text;
    }

    /**
     * Count pattern occurrences
     *
     * @param string $text Text to search in
     * @return array Count per pattern ID
     */
    public function count(string $text): array
    {
        $matches = $this->search($text);
        $counts = [];

        foreach ($matches as $match) {
            $counts[$match['id']] = ($counts[$match['id']] ?? 0) + 1;
        }

        return $counts;
    }

    /**
     * Get statistics
     *
     * @return array Statistics
     */
    public function getStats(): array
    {
        $nodeCount = $this->countNodes($this->root);

        return [
            'patterns' => count($this->patterns),
            'nodes' => $nodeCount,
            'built' => $this->built
        ];
    }

    /**
     * Count nodes recursively
     *
     * @param AhoCorasickNode $node Node to start from
     * @return int Node count
     */
    private function countNodes(AhoCorasickNode $node): int
    {
        $count = 1;
        foreach ($node->children as $child) {
            $count += $this->countNodes($child);
        }
        return $count;
    }
}

/**
 * Content filter using Aho-Corasick
 */
class ContentFilter
{
    private AhoCorasick $profanityFilter;
    private AhoCorasick $spamFilter;
    private array $bannedWords = [];
    private array $spamPatterns = [];

    public function __construct()
    {
        $this->profanityFilter = new AhoCorasick();
        $this->spamFilter = new AhoCorasick();
    }

    /**
     * Load profanity list
     *
     * @param array $words List of banned words
     */
    public function loadProfanityList(array $words): void
    {
        $this->bannedWords = $words;

        foreach ($words as $word) {
            $this->profanityFilter->addPattern(strtolower($word), $word);
        }

        $this->profanityFilter->build();
    }

    /**
     * Load spam patterns
     *
     * @param array $patterns List of spam patterns
     */
    public function loadSpamPatterns(array $patterns): void
    {
        $this->spamPatterns = $patterns;

        foreach ($patterns as $pattern) {
            $this->spamFilter->addPattern(strtolower($pattern), $pattern);
        }

        $this->spamFilter->build();
    }

    /**
     * Analyze content
     *
     * @param string $content Content to analyze
     * @return array Analysis result
     */
    public function analyze(string $content): array
    {
        $lowerContent = strtolower($content);

        $profanity = $this->profanityFilter->search($lowerContent);
        $spam = $this->spamFilter->search($lowerContent);

        return [
            'is_clean' => empty($profanity) && empty($spam),
            'profanity_count' => count($profanity),
            'spam_count' => count($spam),
            'profanity_matches' => array_unique(array_column($profanity, 'pattern')),
            'spam_matches' => array_unique(array_column($spam, 'pattern')),
            'violations' => array_merge($profanity, $spam)
        ];
    }

    /**
     * Censor content
     *
     * @param string $content Content to censor
     * @param string $replacement Replacement character
     * @return string Censored content
     */
    public function censor(string $content, string $replacement = '*'): string
    {
        $matches = $this->profanityFilter->search(strtolower($content));

        // Sort by position (reverse)
        usort($matches, fn($a, $b) => $b['start'] - $a['start']);

        foreach ($matches as $match) {
            $length = $match['length'];
            $censor = str_repeat($replacement, $length);
            $content = substr_replace($content, $censor, $match['start'], $length);
        }

        return $content;
    }
}

/**
 * DNA sequence matcher
 */
class DNASequenceMatcher
{
    private AhoCorasick $matcher;
    private array $sequences = [];

    public function __construct()
    {
        $this->matcher = new AhoCorasick();
    }

    /**
     * Add DNA sequence to search for
     *
     * @param string $sequence DNA sequence (ACGT)
     * @param string $name Sequence name
     */
    public function addSequence(string $sequence, string $name): void
    {
        $sequence = strtoupper($sequence);

        if (!preg_match('/^[ACGT]+$/', $sequence)) {
            throw new \InvalidArgumentException("Invalid DNA sequence: {$sequence}");
        }

        $this->sequences[$name] = $sequence;
        $this->matcher->addPattern($sequence, $name);
    }

    /**
     * Build matcher
     */
    public function build(): void
    {
        $this->matcher->build();
    }

    /**
     * Find sequences in DNA strand
     *
     * @param string $dna DNA strand to search
     * @return array Matches
     */
    public function findSequences(string $dna): array
    {
        $dna = strtoupper($dna);

        if (!preg_match('/^[ACGT]+$/', $dna)) {
            throw new \InvalidArgumentException("Invalid DNA strand");
        }

        return $this->matcher->search($dna);
    }

    /**
     * Get sequence statistics
     *
     * @param string $dna DNA strand
     * @return array Statistics
     */
    public function getStatistics(string $dna): array
    {
        $matches = $this->findSequences($dna);
        $counts = [];

        foreach ($matches as $match) {
            $name = $match['id'];
            $counts[$name] = ($counts[$name] ?? 0) + 1;
        }

        return [
            'strand_length' => strlen($dna),
            'total_matches' => count($matches),
            'unique_sequences' => count($counts),
            'sequence_counts' => $counts
        ];
    }
}

/**
 * Example usage demonstrating Aho-Corasick algorithm
 */
function demonstrateAhoCorasick(): void
{
    echo "=== Aho-Corasick Algorithm Demo ===\n\n";

    // Test 1: Basic multi-pattern matching
    echo "1. Basic Multi-Pattern Matching\n";
    $ac = new AhoCorasick();

    $patterns = ['he', 'she', 'his', 'hers'];
    foreach ($patterns as $pattern) {
        $ac->addPattern($pattern);
    }
    $ac->build();

    $text = 'she sells his hers by the seashore';
    $matches = $ac->search($text);

    echo "Text: \"{$text}\"\n";
    echo "Patterns: " . implode(', ', $patterns) . "\n";
    echo "Matches found: " . count($matches) . "\n";

    foreach ($matches as $match) {
        echo "  '{$match['pattern']}' at position {$match['start']}\n";
    }

    $counts = $ac->count($text);
    echo "Pattern frequencies: " . json_encode($counts) . "\n";

    // Test 2: Content filtering
    echo "\n2. Content Filtering\n";
    $filter = new ContentFilter();

    $filter->loadProfanityList(['bad', 'worse', 'terrible']);
    $filter->loadSpamPatterns(['click here', 'buy now', 'limited offer']);

    $content = 'Click here to buy now! This is a bad deal with limited offer. Terrible!';

    echo "Content: \"{$content}\"\n";

    $analysis = $filter->analyze($content);
    echo "Clean: " . ($analysis['is_clean'] ? 'yes' : 'no') . "\n";
    echo "Profanity violations: {$analysis['profanity_count']}\n";
    echo "Spam violations: {$analysis['spam_count']}\n";
    echo "Profanity found: " . implode(', ', $analysis['profanity_matches']) . "\n";
    echo "Spam found: " . implode(', ', $analysis['spam_matches']) . "\n";

    $censored = $filter->censor($content);
    echo "Censored: \"{$censored}\"\n";

    // Test 3: Pattern replacement
    echo "\n3. Pattern Replacement\n";
    $replacer = new AhoCorasick();

    $replacements = [
        'error' => 'ERROR',
        'warning' => 'WARNING',
        'info' => 'INFO'
    ];

    foreach (array_keys($replacements) as $pattern) {
        $replacer->addPattern($pattern);
    }
    $replacer->build();

    $logText = 'error: something went wrong. warning: check this. info: all good.';
    echo "Original: \"{$logText}\"\n";

    $replaced = $replacer->replace($logText, $replacements);
    echo "Replaced: \"{$replaced}\"\n";

    // Test 4: DNA sequence matching
    echo "\n4. DNA Sequence Matching\n";
    $dna = new DNASequenceMatcher();

    // Add known sequences
    $dna->addSequence('ATCG', 'seq1');
    $dna->addSequence('GCTA', 'seq2');
    $dna->addSequence('TATA', 'tata_box');
    $dna->build();

    $strand = 'ATCGATTATAGGCTAATCGCTATAG';
    echo "DNA strand: {$strand}\n";

    $matches = $dna->findSequences($strand);
    echo "Sequences found: " . count($matches) . "\n";

    foreach ($matches as $match) {
        echo "  {$match['id']}: '{$match['pattern']}' at position {$match['start']}\n";
    }

    $stats = $dna->getStatistics($strand);
    echo "\nStatistics:\n";
    echo "  Strand length: {$stats['strand_length']}\n";
    echo "  Total matches: {$stats['total_matches']}\n";
    echo "  Unique sequences: {$stats['unique_sequences']}\n";
    echo "  Counts: " . json_encode($stats['sequence_counts']) . "\n";

    // Test 5: Performance comparison
    echo "\n5. Performance Comparison\n";
    $testPatterns = [];
    for ($i = 0; $i < 100; $i++) {
        $testPatterns[] = 'pattern' . $i;
    }

    $testText = str_repeat('test pattern42 some text pattern7 more pattern99 ', 100);

    // Aho-Corasick
    $acTest = new AhoCorasick();
    foreach ($testPatterns as $p) {
        $acTest->addPattern($p);
    }
    $acTest->build();

    $start = microtime(true);
    $acMatches = $acTest->search($testText);
    $acTime = microtime(true) - $start;

    echo "Text length: " . strlen($testText) . " characters\n";
    echo "Patterns: " . count($testPatterns) . "\n";
    echo "Aho-Corasick time: " . number_format($acTime * 1000, 3) . " ms\n";
    echo "Matches found: " . count($acMatches) . "\n";

    // Naive approach (for comparison)
    $start = microtime(true);
    $naiveMatches = 0;
    foreach ($testPatterns as $p) {
        $naiveMatches += substr_count($testText, $p);
    }
    $naiveTime = microtime(true) - $start;

    echo "Naive approach time: " . number_format($naiveTime * 1000, 3) . " ms\n";
    echo "Speedup: " . number_format($naiveTime / $acTime, 2) . "x\n";
}

// Run if executed directly
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'] ?? '')) {
    try {
        demonstrateAhoCorasick();
    } catch (\Throwable $e) {
        echo "Error: " . $e->getMessage() . "\n";
        echo $e->getTraceAsString() . "\n";
        exit(1);
    }
}
