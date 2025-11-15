<?php

declare(strict_types=1);

/**
 * Hash Tables - Practical Applications & Real-World Use Cases
 *
 * Demonstrates:
 * - Word frequency analysis
 * - Database indexing simulation
 * - Spell checker with suggestions
 * - When to use hash tables vs other structures
 * - Performance comparison summary
 */

echo "=== Hash Tables: Practical Applications ===\n\n";

// Application 1: Word Frequency Analyzer
echo "1. Word Frequency Analyzer (Text Analysis)\n";
echo "============================================\n";

function analyzeText(string $text): array
{
    // Remove punctuation and convert to lowercase
    $text = strtolower(preg_replace('/[^a-z0-9\s]/i', '', $text));
    $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);

    $frequencies = [];

    foreach ($words as $word) {
        if (!isset($frequencies[$word])) {
            $frequencies[$word] = 0;
        }
        $frequencies[$word]++;
    }

    // Sort by frequency (descending)
    arsort($frequencies);

    return $frequencies;
}

$sampleText = "To be or not to be that is the question. " .
              "Whether it is nobler in the mind to suffer " .
              "the slings and arrows of outrageous fortune.";

echo "Sample text: \"$sampleText\"\n\n";

$frequencies = analyzeText($sampleText);

echo "Top 10 most frequent words:\n";
$count = 0;
foreach ($frequencies as $word => $freq) {
    if ($count++ >= 10) break;
    $bar = str_repeat('█', $freq);
    echo sprintf("  %-10s %s (%d)\n", $word, $bar, $freq);
}
echo "\n";

// Application 2: Database Index Simulation
echo "2. Database Index Simulation\n";
echo "=============================\n";

class DatabaseIndex
{
    private array $index = [];
    private array $records = [];

    public function insert(int $id, array $record): void
    {
        $this->records[$id] = $record;

        // Index by email
        if (isset($record['email'])) {
            $this->index['email'][$record['email']] = $id;
        }

        // Index by username
        if (isset($record['username'])) {
            $this->index['username'][$record['username']] = $id;
        }
    }

    public function findByEmail(string $email): ?array
    {
        $id = $this->index['email'][$email] ?? null;
        return $id !== null ? $this->records[$id] : null;
    }

    public function findByUsername(string $username): ?array
    {
        $id = $this->index['username'][$username] ?? null;
        return $id !== null ? $this->records[$id] : null;
    }

    public function findById(int $id): ?array
    {
        return $this->records[$id] ?? null;
    }
}

$db = new DatabaseIndex();

// Insert sample users
$users = [
    [1, ['username' => 'alice', 'email' => 'alice@example.com', 'age' => 30]],
    [2, ['username' => 'bob', 'email' => 'bob@example.com', 'age' => 25]],
    [3, ['username' => 'charlie', 'email' => 'charlie@example.com', 'age' => 35]],
];

echo "Inserting users into database...\n";
foreach ($users as [$id, $user]) {
    $db->insert($id, $user);
    echo "  User $id: {$user['username']}\n";
}
echo "\n";

// Fast lookups using indices
echo "Hash table indices enable O(1) lookups:\n";
$start = microtime(true);
$result = $db->findByEmail('bob@example.com');
$time = (microtime(true) - $start) * 1000000;
echo "  findByEmail('bob@example.com'): " . json_encode($result) . "\n";
echo "  Time: " . number_format($time, 3) . " μs\n\n";

$result = $db->findByUsername('charlie');
echo "  findByUsername('charlie'): " . json_encode($result) . "\n\n";

// Application 3: Spell Checker with Suggestions
echo "3. Spell Checker with Suggestions\n";
echo "===================================\n";

class SpellChecker
{
    private array $dictionary = [];

    public function __construct(array $words)
    {
        foreach ($words as $word) {
            $this->dictionary[strtolower($word)] = true;
        }
    }

    public function isCorrect(string $word): bool
    {
        return isset($this->dictionary[strtolower($word)]);
    }

    public function suggest(string $word): array
    {
        $word = strtolower($word);

        if ($this->isCorrect($word)) {
            return [];
        }

        $suggestions = [];

        // Try deleting one character
        for ($i = 0; $i < strlen($word); $i++) {
            $candidate = substr($word, 0, $i) . substr($word, $i + 1);
            if ($this->isCorrect($candidate) && !in_array($candidate, $suggestions)) {
                $suggestions[] = $candidate;
            }
        }

        // Try substituting one character
        for ($i = 0; $i < strlen($word); $i++) {
            for ($c = ord('a'); $c <= ord('z'); $c++) {
                $candidate = substr($word, 0, $i) . chr($c) . substr($word, $i + 1);
                if ($this->isCorrect($candidate) && !in_array($candidate, $suggestions)) {
                    $suggestions[] = $candidate;
                }
            }
        }

        return array_slice($suggestions, 0, 5); // Top 5 suggestions
    }
}

$dictionary = ['hello', 'world', 'hash', 'table', 'spell', 'check', 'example', 'test'];
$spellChecker = new SpellChecker($dictionary);

echo "Dictionary: [" . implode(', ', $dictionary) . "]\n\n";

$testWords = ['hello', 'helo', 'wrld', 'tabel', 'xyz'];

foreach ($testWords as $word) {
    $correct = $spellChecker->isCorrect($word);
    echo "  '$word': " . ($correct ? "✓ Correct" : "✗ Incorrect") . "\n";

    if (!$correct) {
        $suggestions = $spellChecker->suggest($word);
        if (!empty($suggestions)) {
            echo "    Suggestions: [" . implode(', ', $suggestions) . "]\n";
        }
    }
}
echo "\n";

// Application 4: URL Shortener
echo "4. URL Shortener Service\n";
echo "=========================\n";

class URLShortener
{
    private array $urlToCode = [];
    private array $codeToUrl = [];
    private int $counter = 0;

    public function shorten(string $longUrl): string
    {
        // Check if already shortened
        if (isset($this->urlToCode[$longUrl])) {
            return $this->urlToCode[$longUrl];
        }

        // Generate short code
        $code = $this->generateCode($this->counter++);

        // Store in both directions
        $this->urlToCode[$longUrl] = $code;
        $this->codeToUrl[$code] = $longUrl;

        return $code;
    }

    public function expand(string $code): ?string
    {
        return $this->codeToUrl[$code] ?? null;
    }

    private function generateCode(int $num): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $base = strlen($chars);
        $code = '';

        do {
            $code = $chars[$num % $base] . $code;
            $num = (int)($num / $base);
        } while ($num > 0);

        return $code;
    }

    public function getStats(): array
    {
        return [
            'totalUrls' => count($this->codeToUrl),
            'totalCodes' => count($this->urlToCode),
        ];
    }
}

$shortener = new URLShortener();

$urls = [
    'https://www.example.com/very/long/url/path/to/resource',
    'https://github.com/user/repository/issues/123',
    'https://docs.example.com/api/reference/v2/endpoints',
];

echo "Shortening URLs:\n";
foreach ($urls as $url) {
    $short = $shortener->shorten($url);
    echo "  $url\n";
    echo "  → short.ly/$short\n\n";
}

echo "Expanding short codes:\n";
$code = $shortener->shorten($urls[0]);
$expanded = $shortener->expand($code);
echo "  short.ly/$code\n";
echo "  → $expanded\n\n";

$stats = $shortener->getStats();
echo "Service stats: {$stats['totalUrls']} URLs shortened\n\n";

// Application 5: Caching Mechanism
echo "5. Simple Cache Implementation\n";
echo "===============================\n";

class SimpleCache
{
    private array $cache = [];
    private int $hits = 0;
    private int $misses = 0;

    public function get(string $key, callable $loader): mixed
    {
        if (isset($this->cache[$key])) {
            $this->hits++;
            return $this->cache[$key];
        }

        $this->misses++;
        $value = $loader();
        $this->cache[$key] = $value;

        return $value;
    }

    public function getStats(): array
    {
        $total = $this->hits + $this->misses;
        $hitRate = $total > 0 ? ($this->hits / $total) * 100 : 0;

        return [
            'hits' => $this->hits,
            'misses' => $this->misses,
            'hitRate' => $hitRate,
            'size' => count($this->cache),
        ];
    }
}

$cache = new SimpleCache();

// Simulate expensive database queries
$expensiveQuery = function(int $id) {
    usleep(1000); // Simulate 1ms delay
    return "User data for ID $id";
};

echo "Simulating database queries with cache:\n\n";

$queries = [1, 2, 1, 3, 2, 1, 4, 1];

foreach ($queries as $id) {
    $start = microtime(true);
    $result = $cache->get("user_$id", fn() => $expensiveQuery($id));
    $time = (microtime(true) - $start) * 1000;

    $cached = $time < 0.5 ? "(cached)" : "(query)";
    echo "  Query user $id: " . number_format($time, 3) . " ms $cached\n";
}

echo "\n";
$stats = $cache->getStats();
echo "Cache Statistics:\n";
echo "  Hits:      {$stats['hits']}\n";
echo "  Misses:    {$stats['misses']}\n";
echo "  Hit Rate:  " . number_format($stats['hitRate'], 1) . "%\n";
echo "  Cache Size: {$stats['size']} entries\n\n";

// Performance Comparison Summary
echo "6. When to Use Hash Tables?\n";
echo "============================\n\n";

echo "Use Hash Tables When:\n";
echo "  ✓ Need O(1) average lookups, inserts, deletes\n";
echo "  ✓ Keys are unique or need to map key → value\n";
echo "  ✓ Order doesn't matter (or use LinkedHashMap)\n";
echo "  ✓ Need to check existence quickly (sets)\n";
echo "  ✓ Implementing caches, indices, deduplication\n\n";

echo "Don't Use Hash Tables When:\n";
echo "  ✗ Need sorted order (use BST or sorted array)\n";
echo "  ✗ Need range queries (use BST or B-tree)\n";
echo "  ✗ Memory is extremely limited (arrays more compact)\n";
echo "  ✗ Need worst-case O(1) guarantees (rare collisions can degrade)\n\n";

echo "Comparison Table:\n";
echo "\n";
echo "Operation    | Hash Table | BST (bal.) | Sorted Array | Linked List\n";
echo "-------------|------------|------------|--------------|-------------\n";
echo "Search       | O(1) avg   | O(log n)   | O(log n)     | O(n)\n";
echo "Insert       | O(1) avg   | O(log n)   | O(n)         | O(1)\n";
echo "Delete       | O(1) avg   | O(log n)   | O(n)         | O(n)\n";
echo "Min/Max      | O(n)       | O(log n)   | O(1)         | O(n)\n";
echo "Range Query  | O(n)       | O(k+log n) | O(k+log n)   | O(n)\n";
echo "Sorted Order | No         | Yes        | Yes          | No\n";
echo "Space        | O(n)       | O(n)       | O(n)         | O(n)\n\n";

// Real-world examples
echo "7. Real-World Hash Table Examples\n";
echo "===================================\n\n";

$examples = [
    'Programming Languages' => [
        'Python dict',
        'Java HashMap/HashSet',
        'JavaScript Object/Map/Set',
        'PHP associative arrays',
        'Ruby Hash',
        'C++ unordered_map',
    ],
    'Databases' => [
        'MySQL: Hash indexes',
        'PostgreSQL: Hash indexes',
        'Redis: Hash data type',
        'MongoDB: Index using hash',
        'Elasticsearch: Term queries',
    ],
    'System Software' => [
        'DNS lookup tables',
        'File system inode tables',
        'Process ID (PID) tables',
        'Symbol tables in compilers',
        'Cache implementations',
    ],
    'Applications' => [
        'Browser history/bookmarks',
        'Autocomplete dictionaries',
        'Duplicate file detection',
        'Session management',
        'Rate limiting (IP tracking)',
    ],
];

foreach ($examples as $category => $items) {
    echo "$category:\n";
    foreach ($items as $item) {
        echo "  • $item\n";
    }
    echo "\n";
}

echo "8. Best Practices\n";
echo "==================\n\n";

echo "Hash Function Design:\n";
echo "  ✓ Use established algorithms (djb2, FNV, MurmurHash)\n";
echo "  ✓ Ensure uniform distribution\n";
echo "  ✓ Fast to compute (O(k) where k = key length)\n";
echo "  ✓ Minimize collisions\n\n";

echo "Collision Resolution:\n";
echo "  ✓ Chaining: Simple, handles high load factors well\n";
echo "  ✓ Open Addressing: Cache-friendly, less memory overhead\n";
echo "  ✓ Monitor load factor, resize when needed\n\n";

echo "Performance Tips:\n";
echo "  ✓ Choose good initial capacity (avoid early resizes)\n";
echo "  ✓ Keep load factor around 0.75 for balance\n";
echo "  ✓ Use immutable keys (strings, numbers)\n";
echo "  ✓ Be aware of hash flooding attacks in production\n\n";

echo "Common Pitfalls:\n";
echo "  ✗ Using mutable objects as keys\n";
echo "  ✗ Poor hash function causing clustering\n";
echo "  ✗ Ignoring load factor degradation\n";
echo "  ✗ Assuming order is preserved (use OrderedDict/LinkedHashMap)\n\n";

// Final performance benchmark
echo "9. Final Performance Benchmark\n";
echo "===============================\n\n";

$sizes = [100, 1000, 10000];

echo "Comparing insert + search performance:\n\n";
echo "Size   | Hash Table | Array      | Speedup\n";
echo "-------|------------|------------|--------\n";

foreach ($sizes as $size) {
    // Hash table
    $start = microtime(true);
    $hash = [];
    for ($i = 0; $i < $size; $i++) {
        $hash["key_$i"] = $i;
    }
    for ($i = 0; $i < $size; $i++) {
        $val = $hash["key_$i"];
    }
    $hashTime = (microtime(true) - $start) * 1000;

    // Array (linear search)
    $start = microtime(true);
    $array = [];
    for ($i = 0; $i < $size; $i++) {
        $array[] = ["key_$i", $i];
    }
    for ($i = 0; $i < $size; $i++) {
        foreach ($array as [$key, $val]) {
            if ($key === "key_$i") break;
        }
    }
    $arrayTime = (microtime(true) - $start) * 1000;

    $speedup = $arrayTime / $hashTime;

    echo sprintf(
        "%6d | %8.3f ms | %8.3f ms | %6.1fx\n",
        $size,
        $hashTime,
        $arrayTime,
        $speedup
    );
}

echo "\nAs data grows, hash table advantage becomes massive!\n\n";

echo "✅ Chapter 6 Complete!\n\n";

echo "Key Takeaways:\n";
echo "  • Hash tables provide O(1) average operations\n";
echo "  • Perfect for lookups, caching, deduplication\n";
echo "  • Trade-off: No ordering, extra memory\n";
echo "  • Used everywhere in modern software\n";
echo "  • Master hash tables to write efficient code!\n";
