<?php

declare(strict_types=1);

/**
 * Searching in Different Data Structures
 *
 * Demonstrates:
 * - Search in Binary Search Tree (BST)
 * - Search in Hash Table
 * - Search in Trie (Prefix Tree)
 * - Search in Linked List
 * - Search in Sorted vs Unsorted Arrays
 * - Performance comparison across structures
 */

echo "=== Searching in Different Data Structures ===\n\n";

// Binary Search Tree Node
class TreeNode
{
    public function __construct(
        public int $value,
        public ?TreeNode $left = null,
        public ?TreeNode $right = null
    ) {}
}

/**
 * Search in BST (recursive)
 */
function searchBST(TreeNode $root = null, int $target): ?TreeNode
{
    if ($root === null || $root->value === $target) {
        return $root;
    }

    if ($target < $root->value) {
        return searchBST($root->left, $target);
    } else {
        return searchBST($root->right, $target);
    }
}

/**
 * Search in BST (iterative)
 */
function searchBSTIterative(?TreeNode $root, int $target): ?TreeNode
{
    while ($root !== null && $root->value !== $target) {
        $root = $target < $root->value ? $root->left : $root->right;
    }

    return $root;
}

/**
 * Build BST from array
 */
function buildBST(array $values): ?TreeNode
{
    $root = null;

    foreach ($values as $value) {
        $root = insertBST($root, $value);
    }

    return $root;
}

function insertBST(?TreeNode $root, int $value): TreeNode
{
    if ($root === null) {
        return new TreeNode($value);
    }

    if ($value < $root->value) {
        $root->left = insertBST($root->left, $value);
    } else {
        $root->right = insertBST($root->right, $value);
    }

    return $root;
}

// Hash Table (Simple Implementation)
class HashTable
{
    private array $buckets = [];
    private int $size = 10;

    public function __construct(int $size = 10)
    {
        $this->size = $size;
        $this->buckets = array_fill(0, $size, []);
    }

    private function hash(string $key): int
    {
        return abs(crc32($key)) % $this->size;
    }

    public function put(string $key, mixed $value): void
    {
        $index = $this->hash($key);

        // Check if key exists
        foreach ($this->buckets[$index] as &$pair) {
            if ($pair['key'] === $key) {
                $pair['value'] = $value;
                return;
            }
        }

        // Add new key-value pair
        $this->buckets[$index][] = ['key' => $key, 'value' => $value];
    }

    public function get(string $key): mixed
    {
        $index = $this->hash($key);

        foreach ($this->buckets[$index] as $pair) {
            if ($pair['key'] === $key) {
                return $pair['value'];
            }
        }

        return null;
    }

    public function contains(string $key): bool
    {
        return $this->get($key) !== null;
    }
}

// Trie Node
class TrieNode
{
    public array $children = [];
    public bool $isEndOfWord = false;
}

// Trie (Prefix Tree)
class Trie
{
    private TrieNode $root;

    public function __construct()
    {
        $this->root = new TrieNode();
    }

    public function insert(string $word): void
    {
        $node = $this->root;

        for ($i = 0; $i < strlen($word); $i++) {
            $char = $word[$i];

            if (!isset($node->children[$char])) {
                $node->children[$char] = new TrieNode();
            }

            $node = $node->children[$char];
        }

        $node->isEndOfWord = true;
    }

    public function search(string $word): bool
    {
        $node = $this->root;

        for ($i = 0; $i < strlen($word); $i++) {
            $char = $word[$i];

            if (!isset($node->children[$char])) {
                return false;
            }

            $node = $node->children[$char];
        }

        return $node->isEndOfWord;
    }

    public function startsWith(string $prefix): bool
    {
        $node = $this->root;

        for ($i = 0; $i < strlen($prefix); $i++) {
            $char = $prefix[$i];

            if (!isset($node->children[$char])) {
                return false;
            }

            $node = $node->children[$char];
        }

        return true;
    }

    public function getWordsWithPrefix(string $prefix): array
    {
        $node = $this->root;

        // Navigate to prefix
        for ($i = 0; $i < strlen($prefix); $i++) {
            $char = $prefix[$i];

            if (!isset($node->children[$char])) {
                return [];
            }

            $node = $node->children[$char];
        }

        // Collect all words from this node
        $words = [];
        $this->collectWords($node, $prefix, $words);

        return $words;
    }

    private function collectWords(TrieNode $node, string $current, array &$words): void
    {
        if ($node->isEndOfWord) {
            $words[] = $current;
        }

        foreach ($node->children as $char => $childNode) {
            $this->collectWords($childNode, $current . $char, $words);
        }
    }
}

// Linked List Node
class ListNode
{
    public function __construct(
        public int $value,
        public ?ListNode $next = null
    ) {}
}

/**
 * Search in linked list
 */
function searchLinkedList(?ListNode $head, int $target): ?ListNode
{
    while ($head !== null) {
        if ($head->value === $target) {
            return $head;
        }
        $head = $head->next;
    }

    return null;
}

// Test 1: Search in Binary Search Tree
echo "1. Search in Binary Search Tree\n";
echo "=================================\n";

$bstValues = [50, 30, 70, 20, 40, 60, 80];
$bst = buildBST($bstValues);

echo "BST (in-order): ";
function inorder(?TreeNode $node, array &$result): void
{
    if ($node === null) return;
    inorder($node->left, $result);
    $result[] = $node->value;
    inorder($node->right, $result);
}

$inorderResult = [];
inorder($bst, $inorderResult);
echo "[" . implode(', ', $inorderResult) . "]\n\n";

$searches = [40, 60, 100];

foreach ($searches as $target) {
    $result = searchBST($bst, $target);
    echo "Search for $target: " . ($result ? "Found" : "Not found") . "\n";
}

echo "\nBST Search Time: O(log n) average, O(n) worst case\n\n";

// Test 2: Search in Hash Table
echo "2. Search in Hash Table\n";
echo "========================\n";

$hashTable = new HashTable(10);

$students = [
    'Alice' => 95,
    'Bob' => 87,
    'Charlie' => 92,
    'Diana' => 88,
];

echo "Student grades:\n";
foreach ($students as $name => $grade) {
    $hashTable->put($name, $grade);
    echo "  $name: $grade\n";
}
echo "\n";

$lookups = ['Charlie', 'Bob', 'Eve'];

foreach ($lookups as $name) {
    $grade = $hashTable->get($name);
    if ($grade !== null) {
        echo "Search for $name: Found (grade: $grade)\n";
    } else {
        echo "Search for $name: Not found\n";
    }
}

echo "\nHash Table Search Time: O(1) average, O(n) worst case\n\n";

// Test 3: Search in Trie
echo "3. Search in Trie (Prefix Tree)\n";
echo "=================================\n";

$trie = new Trie();
$words = ['apple', 'app', 'application', 'apply', 'banana', 'band'];

echo "Dictionary: [" . implode(', ', $words) . "]\n\n";

foreach ($words as $word) {
    $trie->insert($word);
}

// Exact search
$searches = ['app', 'apple', 'appl', 'ban'];

echo "Exact search:\n";
foreach ($searches as $word) {
    $found = $trie->search($word);
    echo "  '$word': " . ($found ? "Found" : "Not found") . "\n";
}

echo "\n";

// Prefix search
$prefixes = ['app', 'ban', 'cat'];

echo "Prefix search:\n";
foreach ($prefixes as $prefix) {
    $hasPrefix = $trie->startsWith($prefix);
    echo "  '$prefix': " . ($hasPrefix ? "Has words" : "No words") . "\n";

    if ($hasPrefix) {
        $words = $trie->getWordsWithPrefix($prefix);
        echo "    Words: [" . implode(', ', $words) . "]\n";
    }
}

echo "\nTrie Search Time: O(m) where m = word length\n\n";

// Test 4: Search in Linked List
echo "4. Search in Linked List\n";
echo "=========================\n";

$head = new ListNode(1);
$head->next = new ListNode(3);
$head->next->next = new ListNode(5);
$head->next->next->next = new ListNode(7);

echo "Linked List: ";
$current = $head;
$values = [];
while ($current !== null) {
    $values[] = $current->value;
    $current = $current->next;
}
echo "[" . implode(' → ', $values) . "]\n\n";

$searches = [5, 1, 9];

foreach ($searches as $target) {
    $result = searchLinkedList($head, $target);
    echo "Search for $target: " . ($result ? "Found" : "Not found") . "\n";
}

echo "\nLinked List Search Time: O(n)\n\n";

// Test 5: Performance Comparison
echo "5. Performance Comparison\n";
echo "==========================\n";

$size = 10000;
$target = 5000;

// Build data structures
$sortedArray = range(1, $size);
$unsortedArray = $sortedArray;
shuffle($unsortedArray);

$bst = buildBST($sortedArray);

$hashTable = new HashTable(1000);
foreach ($sortedArray as $value) {
    $hashTable->put("key$value", $value);
}

echo "Data size: $size elements\n";
echo "Target: $target\n\n";

// Test sorted array (binary search)
$start = microtime(true);
for ($i = 0; $i < 1000; $i++) {
    $left = 0;
    $right = count($sortedArray) - 1;
    while ($left <= $right) {
        $mid = $left + (int)(($right - $left) / 2);
        if ($sortedArray[$mid] === $target) break;
        if ($sortedArray[$mid] < $target) {
            $left = $mid + 1;
        } else {
            $right = $mid - 1;
        }
    }
}
$sortedTime = (microtime(true) - $start) * 1000;

// Test unsorted array (linear search)
$start = microtime(true);
for ($i = 0; $i < 1000; $i++) {
    array_search($target, $unsortedArray, true);
}
$unsortedTime = (microtime(true) - $start) * 1000;

// Test BST
$start = microtime(true);
for ($i = 0; $i < 1000; $i++) {
    searchBST($bst, $target);
}
$bstTime = (microtime(true) - $start) * 1000;

// Test Hash Table
$start = microtime(true);
for ($i = 0; $i < 1000; $i++) {
    $hashTable->get("key$target");
}
$hashTime = (microtime(true) - $start) * 1000;

echo "Data Structure      | Time (1000 searches) | Complexity\n";
echo "--------------------|----------------------|-----------\n";
echo sprintf("Hash Table          | %17.3f ms | O(1)\n", $hashTime);
echo sprintf("Sorted Array        | %17.3f ms | O(log n)\n", $sortedTime);
echo sprintf("BST                 | %17.3f ms | O(log n)\n", $bstTime);
echo sprintf("Unsorted Array      | %17.3f ms | O(n)\n", $unsortedTime);

echo "\nHash table is fastest! (constant time)\n\n";

// Test 6: Use Case Matrix
echo "6. When to Use Each Data Structure\n";
echo "====================================\n\n";

echo "Hash Table (O(1) search):\n";
echo "  ✓ Fast lookups by key\n";
echo "  ✓ No ordering needed\n";
echo "  ✓ Space available (extra memory)\n";
echo "  Examples: User lookup, caching, counting\n\n";

echo "Binary Search Tree (O(log n) search):\n";
echo "  ✓ Ordered data required\n";
echo "  ✓ Range queries needed\n";
echo "  ✓ Dynamic insertions/deletions\n";
echo "  Examples: Database indexes, priority scheduling\n\n";

echo "Trie (O(m) search, m=word length):\n";
echo "  ✓ Prefix-based searches\n";
echo "  ✓ Auto-complete functionality\n";
echo "  ✓ Dictionary operations\n";
echo "  Examples: Spell checkers, IP routing, phone contacts\n\n";

echo "Sorted Array (O(log n) search):\n";
echo "  ✓ Static data (few changes)\n";
echo "  ✓ Memory efficient\n";
echo "  ✓ Sequential access patterns\n";
echo "  Examples: Configuration data, lookup tables\n\n";

echo "Linked List (O(n) search):\n";
echo "  ✓ Frequent insertions/deletions\n";
echo "  ✓ Unknown size\n";
echo "  ✓ Sequential access\n";
echo "  Examples: Undo/redo, playlists, browser history\n\n";

// Test 7: Practical Application - Auto-complete
echo "7. Practical Application: Auto-complete\n";
echo "========================================\n";

$trie = new Trie();
$dictionary = [
    'program', 'programmer', 'programming',
    'product', 'production', 'produce',
    'project', 'projection',
];

foreach ($dictionary as $word) {
    $trie->insert($word);
}

echo "Dictionary: [" . implode(', ', $dictionary) . "]\n\n";

$userInput = 'pro';
echo "User types: '$userInput'\n";

$suggestions = $trie->getWordsWithPrefix($userInput);
echo "Suggestions: [" . implode(', ', $suggestions) . "]\n\n";

$userInput = 'program';
$suggestions = $trie->getWordsWithPrefix($userInput);
echo "User types: '$userInput'\n";
echo "Suggestions: [" . implode(', ', $suggestions) . "]\n\n";

// Test 8: Practical Application - Database Index
echo "8. Practical Application: Database Index\n";
echo "==========================================\n";

echo "Database table: Users\n";
echo "  Primary index: Hash table on user_id (O(1) lookup)\n";
echo "  Secondary index: BST on created_at (O(log n) range queries)\n\n";

// Simulate user lookup
$users = [
    1001 => ['name' => 'Alice', 'created' => '2024-01-15'],
    1002 => ['name' => 'Bob', 'created' => '2024-01-20'],
    1003 => ['name' => 'Charlie', 'created' => '2024-02-01'],
];

echo "Query: SELECT * FROM users WHERE user_id = 1002\n";
echo "  Uses hash index → O(1)\n";
echo "  Result: {$users[1002]['name']}, created {$users[1002]['created']}\n\n";

echo "Query: SELECT * FROM users WHERE created_at BETWEEN '2024-01-15' AND '2024-01-31'\n";
echo "  Uses BST index → O(log n) + O(k) results\n";
echo "  Results: Alice, Bob\n\n";

// Test 9: Space-Time Tradeoffs
echo "9. Space-Time Tradeoffs\n";
echo "========================\n\n";

echo "Data Structure | Search Time | Space     | Insert/Delete\n";
echo "---------------|-------------|-----------|---------------\n";
echo "Hash Table     | O(1)        | O(n)      | O(1) avg\n";
echo "BST (balanced) | O(log n)    | O(n)      | O(log n)\n";
echo "Trie           | O(m)        | O(ALPHABET_SIZE * N * M) | O(m)\n";
echo "Sorted Array   | O(log n)    | O(n)      | O(n)\n";
echo "Unsorted Array | O(n)        | O(n)      | O(1) append\n";
echo "Linked List    | O(n)        | O(n)      | O(1) at head\n\n";

echo "Key Insight: Faster search usually requires more space or structure\n\n";

// Test 10: Summary and Recommendations
echo "10. Search Algorithm Selection Guide\n";
echo "======================================\n\n";

echo "Choose Hash Table when:\n";
echo "  • Need O(1) lookups\n";
echo "  • Key-value pairs\n";
echo "  • Memory available\n";
echo "  • No ordering needed\n\n";

echo "Choose BST when:\n";
echo "  • Need ordered data\n";
echo "  • Range queries\n";
echo "  • Dynamic updates\n";
echo "  • O(log n) acceptable\n\n";

echo "Choose Trie when:\n";
echo "  • Prefix searches\n";
echo "  • String operations\n";
echo "  • Auto-complete\n";
echo "  • Dictionary lookups\n\n";

echo "Choose Sorted Array when:\n";
echo "  • Static data\n";
echo "  • Memory constrained\n";
echo "  • Infrequent updates\n";
echo "  • Binary search needed\n\n";

echo "General Principles:\n";
echo "  1. Hash tables: Fastest but no ordering\n";
echo "  2. Trees: Balance between speed and ordering\n";
echo "  3. Arrays: Simple but inflexible\n";
echo "  4. Tries: Specialized for string operations\n";
echo "  5. Always consider: frequency of operations, data size, memory\n";

echo "\n✅ Complete! Data structure search comparison demonstrated.\n";
