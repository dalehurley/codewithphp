<?php

declare(strict_types=1);

/**
 * Hash Table with Chaining (Separate Chaining)
 *
 * Demonstrates:
 * - Hash table implementation using linked lists for collisions
 * - Insert, search, delete operations
 * - Handling collisions with chaining
 * - Load factor calculation
 */

class HashNode
{
    public function __construct(
        public string $key,
        public mixed $value,
        public ?HashNode $next = null
    ) {}
}

class HashTableChaining
{
    private array $buckets;
    private int $size;
    private int $count = 0;

    public function __construct(int $size = 10)
    {
        $this->size = $size;
        $this->buckets = array_fill(0, $size, null);
    }

    /**
     * Hash function
     */
    private function hash(string $key): int
    {
        $hash = 0;
        $len = strlen($key);

        for ($i = 0; $i < $len; $i++) {
            $hash = (($hash << 5) - $hash) + ord($key[$i]);
        }

        return abs($hash) % $this->size;
    }

    /**
     * Insert key-value pair - O(1) average
     */
    public function put(string $key, mixed $value): void
    {
        $index = $this->hash($key);
        $newNode = new HashNode($key, $value);

        // Empty bucket
        if ($this->buckets[$index] === null) {
            $this->buckets[$index] = $newNode;
            $this->count++;
            return;
        }

        // Traverse chain to check for existing key or find end
        $current = $this->buckets[$index];
        $prev = null;

        while ($current !== null) {
            if ($current->key === $key) {
                // Update existing key
                $current->value = $value;
                return;
            }
            $prev = $current;
            $current = $current->next;
        }

        // Add to end of chain
        $prev->next = $newNode;
        $this->count++;
    }

    /**
     * Get value by key - O(1) average
     */
    public function get(string $key): mixed
    {
        $index = $this->hash($key);
        $current = $this->buckets[$index];

        while ($current !== null) {
            if ($current->key === $key) {
                return $current->value;
            }
            $current = $current->next;
        }

        return null; // Key not found
    }

    /**
     * Check if key exists - O(1) average
     */
    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    /**
     * Delete key-value pair - O(1) average
     */
    public function delete(string $key): bool
    {
        $index = $this->hash($key);
        $current = $this->buckets[$index];
        $prev = null;

        while ($current !== null) {
            if ($current->key === $key) {
                if ($prev === null) {
                    // Remove head of chain
                    $this->buckets[$index] = $current->next;
                } else {
                    // Remove from middle/end
                    $prev->next = $current->next;
                }
                $this->count--;
                return true;
            }
            $prev = $current;
            $current = $current->next;
        }

        return false; // Key not found
    }

    /**
     * Get all keys
     */
    public function keys(): array
    {
        $keys = [];

        foreach ($this->buckets as $bucket) {
            $current = $bucket;
            while ($current !== null) {
                $keys[] = $current->key;
                $current = $current->next;
            }
        }

        return $keys;
    }

    /**
     * Get load factor
     */
    public function loadFactor(): float
    {
        return $this->count / $this->size;
    }

    /**
     * Get bucket statistics
     */
    public function getStats(): array
    {
        $stats = [
            'size' => $this->size,
            'count' => $this->count,
            'load_factor' => $this->loadFactor(),
            'buckets_used' => 0,
            'max_chain_length' => 0,
            'avg_chain_length' => 0,
        ];

        $totalChainLength = 0;

        foreach ($this->buckets as $bucket) {
            if ($bucket !== null) {
                $stats['buckets_used']++;

                $chainLength = 0;
                $current = $bucket;
                while ($current !== null) {
                    $chainLength++;
                    $current = $current->next;
                }

                $totalChainLength += $chainLength;
                $stats['max_chain_length'] = max($stats['max_chain_length'], $chainLength);
            }
        }

        if ($stats['buckets_used'] > 0) {
            $stats['avg_chain_length'] = $totalChainLength / $stats['buckets_used'];
        }

        return $stats;
    }

    /**
     * Visualize the hash table
     */
    public function visualize(): void
    {
        echo "Hash Table Structure (Chaining):\n";
        echo str_repeat("=", 50) . "\n";

        for ($i = 0; $i < $this->size; $i++) {
            echo "Bucket $i: ";

            if ($this->buckets[$i] === null) {
                echo "null\n";
            } else {
                $current = $this->buckets[$i];
                $chain = [];

                while ($current !== null) {
                    $chain[] = "{$current->key}→{$current->value}";
                    $current = $current->next;
                }

                echo implode(" → ", $chain) . "\n";
            }
        }
    }
}

// Demo
echo "=== Hash Table with Chaining ===\n\n";

$hashTable = new HashTableChaining(7);

echo "1. Inserting key-value pairs:\n";
$data = [
    'apple' => 5,
    'banana' => 3,
    'cherry' => 8,
    'date' => 2,
    'elderberry' => 6,
    'fig' => 4,
    'grape' => 7,
    'honeydew' => 9,
];

foreach ($data as $key => $value) {
    $hashTable->put($key, $value);
    echo "  Inserted: '$key' => $value\n";
}
echo "\n";

echo "2. Hash Table Visualization:\n";
$hashTable->visualize();
echo "\n";

echo "3. Search Operations:\n";
$searchKeys = ['banana', 'fig', 'mango'];
foreach ($searchKeys as $key) {
    $value = $hashTable->get($key);
    $found = $value !== null ? "Found: $value" : "Not found";
    echo "  Search '$key': $found\n";
}
echo "\n";

echo "4. Hash Table Statistics:\n";
$stats = $hashTable->getStats();
foreach ($stats as $key => $value) {
    $displayValue = is_float($value) ? number_format($value, 2) : $value;
    echo "  " . ucfirst(str_replace('_', ' ', $key)) . ": $displayValue\n";
}
echo "\n";

echo "5. Delete Operation:\n";
$deleteKey = 'cherry';
$deleted = $hashTable->delete($deleteKey);
echo "  Delete '$deleteKey': " . ($deleted ? "✓ Success" : "✗ Failed") . "\n";
echo "  Keys after deletion: " . implode(', ', $hashTable->keys()) . "\n\n";

echo "6. Updated Visualization:\n";
$hashTable->visualize();

echo "\n✅ Complete! Hash table with chaining demonstrated.\n";
