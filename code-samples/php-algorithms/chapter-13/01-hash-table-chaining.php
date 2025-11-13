<?php

/**
 * Hash Table with Separate Chaining
 *
 * Implementation using arrays for collision handling.
 * Time Complexity: O(1) average case for all operations
 *
 * @package CodeWithPHP\Algorithms\Chapter13
 */

declare(strict_types=1);

class HashTableChaining
{
    private array $table;
    private int $size;
    private int $count = 0;

    public function __construct(int $size = 100)
    {
        $this->size = $size;
        $this->table = array_fill(0, $size, []);
    }

    private function hash(string $key): int
    {
        $hash = 0;
        for ($i = 0; $i < strlen($key); $i++) {
            $hash = ($hash * 31 + ord($key[$i])) % $this->size;
        }
        return abs($hash);
    }

    public function set(string $key, mixed $value): void
    {
        $index = $this->hash($key);

        // Check if key exists
        foreach ($this->table[$index] as &$pair) {
            if ($pair['key'] === $key) {
                $pair['value'] = $value;
                return;
            }
        }

        // Add new key-value pair
        $this->table[$index][] = ['key' => $key, 'value' => $value];
        $this->count++;
    }

    public function get(string $key): mixed
    {
        $index = $this->hash($key);

        foreach ($this->table[$index] as $pair) {
            if ($pair['key'] === $key) {
                return $pair['value'];
            }
        }

        return null;
    }

    public function delete(string $key): bool
    {
        $index = $this->hash($key);

        foreach ($this->table[$index] as $i => $pair) {
            if ($pair['key'] === $key) {
                array_splice($this->table[$index], $i, 1);
                $this->count--;
                return true;
            }
        }

        return false;
    }

    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    public function size(): int
    {
        return $this->count;
    }

    public function keys(): array
    {
        $keys = [];
        foreach ($this->table as $bucket) {
            foreach ($bucket as $pair) {
                $keys[] = $pair['key'];
            }
        }
        return $keys;
    }

    public function getLoadFactor(): float
    {
        return $this->count / $this->size;
    }
}

// DEMONSTRATIONS
echo "=== Hash Table with Chaining ===\n\n";

echo "Example 1: Basic operations\n";
echo str_repeat('-', 50) . "\n";
$ht = new HashTableChaining();
$ht->set("name", "Alice");
$ht->set("age", 30);
$ht->set("city", "New York");

echo "Get 'name': " . $ht->get("name") . "\n";
echo "Get 'age': " . $ht->get("age") . "\n";
echo "Size: " . $ht->size() . "\n";
echo "Load factor: " . round($ht->getLoadFactor(), 4) . "\n\n";

echo "Example 2: Collision handling\n";
echo str_repeat('-', 50) . "\n";
$ht = new HashTableChaining(10); // Small size for collisions
for ($i = 0; $i < 20; $i++) {
    $ht->set("key$i", "value$i");
}
echo "Added 20 items to table of size 10\n";
echo "Load factor: " . round($ht->getLoadFactor(), 2) . "\n";
echo "All items stored successfully!\n";
