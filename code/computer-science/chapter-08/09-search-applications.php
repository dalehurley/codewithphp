<?php

declare(strict_types=1);

/**
 * Search Applications - Real-World Use Cases
 *
 * Demonstrates:
 * - Searching in real-world scenarios
 * - Database query optimization
 * - Text search and pattern matching
 * - Recommendation systems
 * - Spell checking
 * - Auto-complete
 * - Finding duplicates
 * - Range searches
 */

echo "=== Search Applications: Real-World Use Cases ===\n\n";

// Application 1: Database Query Optimization
echo "1. Database Query Optimization\n";
echo "================================\n";

class Database
{
    private array $users = [];
    private array $emailIndex = [];
    private array $ageIndex = [];

    public function addUser(int $id, string $name, string $email, int $age): void
    {
        $this->users[$id] = [
            'id' => $id,
            'name' => $name,
            'email' => $email,
            'age' => $age,
        ];

        // Build indexes
        $this->emailIndex[$email] = $id;

        if (!isset($this->ageIndex[$age])) {
            $this->ageIndex[$age] = [];
        }
        $this->ageIndex[$age][] = $id;
    }

    public function findByEmail(string $email): ?array
    {
        // O(1) hash lookup
        $id = $this->emailIndex[$email] ?? null;
        return $id !== null ? $this->users[$id] : null;
    }

    public function findByAgeRange(int $minAge, int $maxAge): array
    {
        // O(k) where k = number of matching ages
        $results = [];

        for ($age = $minAge; $age <= $maxAge; $age++) {
            if (isset($this->ageIndex[$age])) {
                foreach ($this->ageIndex[$age] as $id) {
                    $results[] = $this->users[$id];
                }
            }
        }

        return $results;
    }
}

$db = new Database();
$db->addUser(1, 'Alice', 'alice@example.com', 25);
$db->addUser(2, 'Bob', 'bob@example.com', 30);
$db->addUser(3, 'Charlie', 'charlie@example.com', 25);
$db->addUser(4, 'Diana', 'diana@example.com', 28);

echo "Database with 4 users:\n";
echo "  1. Alice (alice@example.com, age 25)\n";
echo "  2. Bob (bob@example.com, age 30)\n";
echo "  3. Charlie (charlie@example.com, age 25)\n";
echo "  4. Diana (diana@example.com, age 28)\n\n";

// Query by email
$user = $db->findByEmail('bob@example.com');
echo "SELECT * FROM users WHERE email = 'bob@example.com'\n";
echo "  Result: {$user['name']} (O(1) hash index lookup)\n\n";

// Range query
$users = $db->findByAgeRange(25, 28);
echo "SELECT * FROM users WHERE age BETWEEN 25 AND 28\n";
echo "  Results: " . count($users) . " users\n";
foreach ($users as $user) {
    echo "    - {$user['name']} (age {$user['age']})\n";
}
echo "  (O(k) using age index)\n\n";

// Application 2: Text Search and Auto-complete
echo "2. Text Search and Auto-complete\n";
echo "==================================\n";

class SearchEngine
{
    private array $documents = [];
    private array $invertedIndex = [];

    public function indexDocument(int $id, string $content): void
    {
        $this->documents[$id] = $content;

        // Build inverted index (word → document IDs)
        $words = preg_split('/\s+/', strtolower($content));

        foreach ($words as $word) {
            if (!isset($this->invertedIndex[$word])) {
                $this->invertedIndex[$word] = [];
            }

            if (!in_array($id, $this->invertedIndex[$word])) {
                $this->invertedIndex[$word][] = $id;
            }
        }
    }

    public function search(string $query): array
    {
        $words = preg_split('/\s+/', strtolower($query));
        $results = null;

        foreach ($words as $word) {
            $docIds = $this->invertedIndex[$word] ?? [];

            if ($results === null) {
                $results = $docIds;
            } else {
                // Intersection (AND operation)
                $results = array_intersect($results, $docIds);
            }
        }

        return $results ?? [];
    }

    public function autoComplete(string $prefix): array
    {
        $prefix = strtolower($prefix);
        $suggestions = [];

        foreach (array_keys($this->invertedIndex) as $word) {
            if (str_starts_with($word, $prefix)) {
                $suggestions[] = $word;
            }
        }

        sort($suggestions);
        return array_slice($suggestions, 0, 5); // Top 5
    }
}

$search = new SearchEngine();
$search->indexDocument(1, "PHP is a programming language");
$search->indexDocument(2, "Python is also a programming language");
$search->indexDocument(3, "PHP and Python are both popular");

echo "Indexed 3 documents\n\n";

// Search
$results = $search->search("programming language");
echo "Search: 'programming language'\n";
echo "  Found in documents: [" . implode(', ', $results) . "]\n\n";

// Auto-complete
$suggestions = $search->autoComplete("pr");
echo "Auto-complete: 'pr...'\n";
echo "  Suggestions: [" . implode(', ', $suggestions) . "]\n\n";

// Application 3: Spell Checker
echo "3. Spell Checker (Edit Distance)\n";
echo "==================================\n";

function levenshteinDistance(string $s1, string $s2): int
{
    $len1 = strlen($s1);
    $len2 = strlen($s2);

    $dp = array_fill(0, $len1 + 1, array_fill(0, $len2 + 1, 0));

    for ($i = 0; $i <= $len1; $i++) {
        $dp[$i][0] = $i;
    }

    for ($j = 0; $j <= $len2; $j++) {
        $dp[0][$j] = $j;
    }

    for ($i = 1; $i <= $len1; $i++) {
        for ($j = 1; $j <= $len2; $j++) {
            $cost = $s1[$i - 1] === $s2[$j - 1] ? 0 : 1;

            $dp[$i][$j] = min(
                $dp[$i - 1][$j] + 1,      // Deletion
                $dp[$i][$j - 1] + 1,      // Insertion
                $dp[$i - 1][$j - 1] + $cost  // Substitution
            );
        }
    }

    return $dp[$len1][$len2];
}

function findClosestWords(string $word, array $dictionary, int $maxSuggestions = 3): array
{
    $distances = [];

    foreach ($dictionary as $dictWord) {
        $distance = levenshteinDistance($word, $dictWord);
        $distances[$dictWord] = $distance;
    }

    asort($distances);

    return array_slice(array_keys($distances), 0, $maxSuggestions);
}

$dictionary = ['programming', 'program', 'programmer', 'code', 'coding', 'computer'];
$misspelled = 'programing';

echo "Dictionary: [" . implode(', ', $dictionary) . "]\n";
echo "Misspelled word: '$misspelled'\n\n";

$suggestions = findClosestWords($misspelled, $dictionary, 3);

echo "Suggestions:\n";
foreach ($suggestions as $i => $suggestion) {
    $distance = levenshteinDistance($misspelled, $suggestion);
    echo "  " . ($i + 1) . ". $suggestion (edit distance: $distance)\n";
}
echo "\n";

// Application 4: Finding Duplicates
echo "4. Finding Duplicates\n";
echo "======================\n";

function findDuplicates(array $arr): array
{
    $seen = [];
    $duplicates = [];

    foreach ($arr as $value) {
        if (isset($seen[$value])) {
            if (!in_array($value, $duplicates)) {
                $duplicates[] = $value;
            }
        } else {
            $seen[$value] = true;
        }
    }

    return $duplicates;
}

$data = [1, 2, 3, 4, 2, 5, 3, 6, 7, 1];
echo "Array: [" . implode(', ', $data) . "]\n";

$dupes = findDuplicates($data);
echo "Duplicates: [" . implode(', ', $dupes) . "]\n";
echo "Time: O(n) using hash table\n\n";

// Application 5: Product Recommendation
echo "5. Product Recommendation (Similarity Search)\n";
echo "===============================================\n";

class Product
{
    public function __construct(
        public int $id,
        public string $name,
        public array $tags
    ) {}
}

function findSimilarProducts(Product $product, array $allProducts, int $limit = 3): array
{
    $similarities = [];

    foreach ($allProducts as $other) {
        if ($other->id === $product->id) continue;

        // Calculate Jaccard similarity
        $intersection = count(array_intersect($product->tags, $other->tags));
        $union = count(array_unique(array_merge($product->tags, $other->tags)));

        $similarity = $union > 0 ? $intersection / $union : 0;
        $similarities[$other->id] = $similarity;
    }

    arsort($similarities);

    $results = [];
    foreach (array_slice(array_keys($similarities), 0, $limit) as $id) {
        foreach ($allProducts as $p) {
            if ($p->id === $id) {
                $results[] = $p;
                break;
            }
        }
    }

    return $results;
}

$products = [
    new Product(1, 'Laptop', ['electronics', 'computer', 'portable']),
    new Product(2, 'Mouse', ['electronics', 'computer', 'accessory']),
    new Product(3, 'Keyboard', ['electronics', 'computer', 'accessory']),
    new Product(4, 'Tablet', ['electronics', 'portable', 'touchscreen']),
    new Product(5, 'Book', ['education', 'reading']),
];

$targetProduct = $products[0]; // Laptop

echo "Product: {$targetProduct->name}\n";
echo "Tags: [" . implode(', ', $targetProduct->tags) . "]\n\n";

$similar = findSimilarProducts($targetProduct, $products, 3);

echo "Recommended similar products:\n";
foreach ($similar as $i => $product) {
    $intersection = count(array_intersect($targetProduct->tags, $product->tags));
    $union = count(array_unique(array_merge($targetProduct->tags, $product->tags)));
    $similarity = $union > 0 ? round($intersection / $union * 100) : 0;

    echo "  " . ($i + 1) . ". {$product->name} ({$similarity}% similar)\n";
    echo "     Tags: [" . implode(', ', $product->tags) . "]\n";
}
echo "\n";

// Application 6: IP Address Lookup (Prefix Matching)
echo "6. IP Address Lookup (CIDR Matching)\n";
echo "======================================\n";

class IPRange
{
    public function __construct(
        public string $cidr,
        public string $region
    ) {}

    public function contains(string $ip): bool
    {
        [$range, $bits] = explode('/', $this->cidr);

        $rangeInt = ip2long($range);
        $ipInt = ip2long($ip);
        $mask = -1 << (32 - (int)$bits);

        return ($rangeInt & $mask) === ($ipInt & $mask);
    }
}

$ipRanges = [
    new IPRange('192.168.1.0/24', 'US-East'),
    new IPRange('10.0.0.0/8', 'US-West'),
    new IPRange('172.16.0.0/12', 'EU'),
];

$testIPs = ['192.168.1.100', '10.5.10.20', '8.8.8.8'];

echo "IP Ranges:\n";
foreach ($ipRanges as $range) {
    echo "  {$range->cidr} → {$range->region}\n";
}
echo "\n";

foreach ($testIPs as $ip) {
    $found = false;

    foreach ($ipRanges as $range) {
        if ($range->contains($ip)) {
            echo "IP $ip → {$range->region}\n";
            $found = true;
            break;
        }
    }

    if (!$found) {
        echo "IP $ip → Not in any range\n";
    }
}

echo "\n";

// Application 7: Log File Analysis
echo "7. Log File Analysis (Pattern Search)\n";
echo "=======================================\n";

$logLines = [
    "[2024-01-15 10:00:00] INFO: User login successful",
    "[2024-01-15 10:05:00] ERROR: Database connection failed",
    "[2024-01-15 10:10:00] INFO: User logout",
    "[2024-01-15 10:15:00] ERROR: Payment processing failed",
    "[2024-01-15 10:20:00] WARNING: High memory usage",
    "[2024-01-15 10:25:00] ERROR: API timeout",
];

echo "Searching logs for ERROR entries:\n\n";

$errors = [];
foreach ($logLines as $line) {
    if (str_contains($line, 'ERROR')) {
        $errors[] = $line;
    }
}

foreach ($errors as $error) {
    echo "  $error\n";
}

echo "\nFound " . count($errors) . " errors\n\n";

// Application 8: Finding Closest Pair
echo "8. Finding Closest Pair of Points\n";
echo "===================================\n";

class Point
{
    public function __construct(
        public float $x,
        public float $y
    ) {}

    public function distanceTo(Point $other): float
    {
        $dx = $this->x - $other->x;
        $dy = $this->y - $other->y;
        return sqrt($dx * $dx + $dy * $dy);
    }
}

$points = [
    new Point(0, 0),
    new Point(1, 1),
    new Point(2, 2),
    new Point(10, 10),
    new Point(11, 11),
];

echo "Points:\n";
foreach ($points as $i => $p) {
    echo "  P$i: ({$p->x}, {$p->y})\n";
}
echo "\n";

// Find closest pair (brute force O(n²))
$minDistance = PHP_FLOAT_MAX;
$closestPair = null;

for ($i = 0; $i < count($points) - 1; $i++) {
    for ($j = $i + 1; $j < count($points); $j++) {
        $distance = $points[$i]->distanceTo($points[$j]);

        if ($distance < $minDistance) {
            $minDistance = $distance;
            $closestPair = [$i, $j];
        }
    }
}

echo "Closest pair: P{$closestPair[0]} and P{$closestPair[1]}\n";
echo "Distance: " . number_format($minDistance, 2) . "\n\n";

// Application 9: Cache with LRU Search
echo "9. Cache Lookup (LRU Cache)\n";
echo "============================\n";

class LRUCache
{
    private array $cache = [];
    private array $usage = [];
    private int $capacity;

    public function __construct(int $capacity)
    {
        $this->capacity = $capacity;
    }

    public function get(string $key): mixed
    {
        if (!isset($this->cache[$key])) {
            return null;
        }

        // Update usage
        $this->usage[$key] = time();

        return $this->cache[$key];
    }

    public function put(string $key, mixed $value): void
    {
        if (count($this->cache) >= $this->capacity && !isset($this->cache[$key])) {
            // Evict least recently used
            asort($this->usage);
            $lruKey = array_key_first($this->usage);
            unset($this->cache[$lruKey]);
            unset($this->usage[$lruKey]);
        }

        $this->cache[$key] = $value;
        $this->usage[$key] = time();
    }

    public function has(string $key): bool
    {
        return isset($this->cache[$key]);
    }
}

$cache = new LRUCache(3);

echo "Cache capacity: 3\n\n";

$cache->put('page1', 'Content 1');
$cache->put('page2', 'Content 2');
$cache->put('page3', 'Content 3');

echo "Added: page1, page2, page3\n";

// Access page1
$content = $cache->get('page1');
echo "Get page1: " . ($content ? "Found" : "Not found") . "\n";

// Add page4 (should evict page2 - LRU)
usleep(1000);
$cache->put('page4', 'Content 4');

echo "Added: page4 (capacity full, evicting LRU)\n\n";

echo "Cache status:\n";
echo "  page1: " . ($cache->has('page1') ? "Present" : "Evicted") . "\n";
echo "  page2: " . ($cache->has('page2') ? "Present" : "Evicted") . "\n";
echo "  page3: " . ($cache->has('page3') ? "Present" : "Evicted") . "\n";
echo "  page4: " . ($cache->has('page4') ? "Present" : "Evicted") . "\n\n";

// Application 10: Summary
echo "10. Search Applications Summary\n";
echo "================================\n\n";

echo "Real-World Search Use Cases:\n\n";

echo "1. Database Queries\n";
echo "   • Index-based lookups (B-tree, hash)\n";
echo "   • Range queries\n";
echo "   • O(1) hash index, O(log n) B-tree\n\n";

echo "2. Text Search\n";
echo "   • Inverted index for full-text search\n";
echo "   • Trie for prefix matching\n";
echo "   • O(m) for word length m\n\n";

echo "3. Spell Checking\n";
echo "   • Edit distance (Levenshtein)\n";
echo "   • Find closest dictionary words\n";
echo "   • O(n*m²) for n words, m length\n\n";

echo "4. Duplicate Detection\n";
echo "   • Hash table for seen elements\n";
echo "   • O(n) single pass\n\n";

echo "5. Recommendation Systems\n";
echo "   • Similarity search (Jaccard, cosine)\n";
echo "   • Find k-nearest neighbors\n";
echo "   • O(n) for similarity calculation\n\n";

echo "6. Network Routing\n";
echo "   • IP prefix matching (CIDR)\n";
echo "   • Trie-based routing tables\n";
echo "   • O(log n) for routing decisions\n\n";

echo "7. Log Analysis\n";
echo "   • Pattern matching\n";
echo "   • Time-based queries\n";
echo "   • Binary search on timestamps\n\n";

echo "8. Geospatial Search\n";
echo "   • Closest pair of points\n";
echo "   • K-d trees, R-trees\n";
echo "   • O(log n) for spatial queries\n\n";

echo "9. Caching\n";
echo "   • LRU cache lookups\n";
echo "   • O(1) get/put with hash + linked list\n";
echo "   • Critical for performance\n\n";

echo "10. E-commerce\n";
echo "    • Product search and filtering\n";
echo "    • Price range queries\n";
echo "    • Faceted search\n\n";

echo "Key Insights:\n";
echo "==============\n";
echo "  • Choose data structure based on access pattern\n";
echo "  • Indexes trade space for speed\n";
echo "  • Hash tables: O(1) but no ordering\n";
echo "  • Trees: O(log n) with ordering\n";
echo "  • Always profile before optimizing\n";

echo "\n✅ Complete! Search applications demonstrated.\n";
