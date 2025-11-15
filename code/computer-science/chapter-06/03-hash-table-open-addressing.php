<?php

declare(strict_types=1);

/**
 * Hash Table with Open Addressing (Linear Probing)
 *
 * Demonstrates:
 * - Hash table using linear probing for collision resolution
 * - Insert, search, delete with probing
 * - Tombstone markers for deletions
 * - Clustering effects
 */

class HashEntry
{
    public const ACTIVE = 'active';
    public const DELETED = 'deleted';

    public function __construct(
        public string $key,
        public mixed $value,
        public string $status = self::ACTIVE
    ) {}
}

class HashTableOpenAddressing
{
    private array $table;
    private int $size;
    private int $count = 0;
    private int $deletedCount = 0;

    public function __construct(int $size = 10)
    {
        $this->size = $size;
        $this->table = array_fill(0, $size, null);
    }

    /**
     * Hash function
     */
    private function hash(string $key, int $i = 0): int
    {
        $h1 = 0;
        $len = strlen($key);

        for ($j = 0; $j < $len; $j++) {
            $h1 = (($h1 << 5) - $h1) + ord($key[$j]);
        }

        // Linear probing: h(key, i) = (h1(key) + i) mod size
        return (abs($h1) + $i) % $this->size;
    }

    /**
     * Insert key-value pair - O(1) average, O(n) worst
     */
    public function put(string $key, mixed $value): bool
    {
        // Check load factor
        if ($this->loadFactor() > 0.7) {
            echo "  ⚠️ Warning: Load factor > 0.7, consider resizing\n";
        }

        for ($i = 0; $i < $this->size; $i++) {
            $index = $this->hash($key, $i);
            $entry = $this->table[$index];

            // Empty slot or deleted slot
            if ($entry === null || $entry->status === HashEntry::DELETED) {
                if ($entry !== null && $entry->status === HashEntry::DELETED) {
                    $this->deletedCount--;
                }
                $this->table[$index] = new HashEntry($key, $value);
                $this->count++;
                return true;
            }

            // Update existing key
            if ($entry->key === $key && $entry->status === HashEntry::ACTIVE) {
                $entry->value = $value;
                return true;
            }
        }

        return false; // Table full
    }

    /**
     * Get value by key - O(1) average, O(n) worst
     */
    public function get(string $key): mixed
    {
        for ($i = 0; $i < $this->size; $i++) {
            $index = $this->hash($key, $i);
            $entry = $this->table[$index];

            if ($entry === null) {
                return null; // Not found
            }

            if ($entry->key === $key && $entry->status === HashEntry::ACTIVE) {
                return $entry->value;
            }
        }

        return null;
    }

    /**
     * Delete key - O(1) average, O(n) worst
     * Uses tombstone (lazy deletion)
     */
    public function delete(string $key): bool
    {
        for ($i = 0; $i < $this->size; $i++) {
            $index = $this->hash($key, $i);
            $entry = $this->table[$index];

            if ($entry === null) {
                return false; // Not found
            }

            if ($entry->key === $key && $entry->status === HashEntry::ACTIVE) {
                $entry->status = HashEntry::DELETED;
                $this->count--;
                $this->deletedCount++;
                return true;
            }
        }

        return false;
    }

    /**
     * Get load factor
     */
    public function loadFactor(): float
    {
        return ($this->count + $this->deletedCount) / $this->size;
    }

    /**
     * Get statistics
     */
    public function getStats(): array
    {
        return [
            'size' => $this->size,
            'count' => $this->count,
            'deleted_count' => $this->deletedCount,
            'load_factor' => $this->loadFactor(),
            'slots_used' => $this->count + $this->deletedCount,
            'slots_free' => $this->size - $this->count - $this->deletedCount,
        ];
    }

    /**
     * Visualize the hash table
     */
    public function visualize(): void
    {
        echo "Hash Table Structure (Open Addressing - Linear Probing):\n";
        echo str_repeat("=", 60) . "\n";

        for ($i = 0; $i < $this->size; $i++) {
            $entry = $this->table[$i];

            if ($entry === null) {
                echo "  Index $i: [empty]\n";
            } elseif ($entry->status === HashEntry::DELETED) {
                echo "  Index $i: [DELETED - tombstone] (was: {$entry->key})\n";
            } else {
                echo "  Index $i: {$entry->key} => {$entry->value}\n";
            }
        }
    }

    /**
     * Show probe sequence for a key
     */
    public function showProbeSequence(string $key): void
    {
        echo "Probe sequence for '$key':\n";

        for ($i = 0; $i < min($this->size, 10); $i++) {
            $index = $this->hash($key, $i);
            $entry = $this->table[$index];

            $status = 'empty';
            if ($entry !== null) {
                $status = $entry->status === HashEntry::ACTIVE
                    ? "occupied ({$entry->key})"
                    : "deleted";
            }

            echo "  Probe $i: index $index [$status]";
            if ($entry && $entry->key === $key && $entry->status === HashEntry::ACTIVE) {
                echo " ← FOUND!";
            }
            echo "\n";

            if ($entry === null || ($entry->key === $key && $entry->status === HashEntry::ACTIVE)) {
                break;
            }
        }
    }
}

// Demo
echo "=== Hash Table with Open Addressing (Linear Probing) ===\n\n";

$hashTable = new HashTableOpenAddressing(11); // Use prime number for better distribution

echo "1. Inserting key-value pairs:\n";
$data = [
    'apple' => 5,
    'banana' => 3,
    'cherry' => 8,
    'date' => 2,
    'elderberry' => 6,
    'fig' => 4,
    'grape' => 7,
];

foreach ($data as $key => $value) {
    $hashTable->put($key, $value);
    echo "  Inserted: '$key' => $value\n";
}
echo "\n";

echo "2. Hash Table Visualization:\n";
$hashTable->visualize();
echo "\n";

echo "3. Probe Sequence Examples:\n";
$hashTable->showProbeSequence('banana');
echo "\n";
$hashTable->showProbeSequence('fig');
echo "\n";

echo "4. Search Operations:\n";
$searchKeys = ['cherry', 'grape', 'mango'];
foreach ($searchKeys as $key) {
    $value = $hashTable->get($key);
    $found = $value !== null ? "Found: $value" : "Not found";
    echo "  Search '$key': $found\n";
}
echo "\n";

echo "5. Delete Operation (with tombstone):\n";
$deleteKey = 'banana';
$deleted = $hashTable->delete($deleteKey);
echo "  Delete '$deleteKey': " . ($deleted ? "✓ Success" : "✗ Failed") . "\n\n";

echo "6. Visualization After Deletion:\n";
$hashTable->visualize();
echo "\n";

echo "7. Probe Sequence After Deletion:\n";
$hashTable->showProbeSequence('banana');
echo "\n";

echo "8. Hash Table Statistics:\n";
$stats = $hashTable->getStats();
foreach ($stats as $key => $value) {
    $displayValue = is_float($value) ? number_format($value, 2) : $value;
    echo "  " . ucfirst(str_replace('_', ' ', $key)) . ": $displayValue\n";
}

echo "\n9. Clustering Demonstration:\n";
echo "Notice how items cluster together due to linear probing.\n";
echo "This is called 'primary clustering' and degrades performance.\n";

echo "\n✅ Complete! Hash table with open addressing demonstrated.\n";
