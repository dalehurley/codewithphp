<?php

/**
 * Hash Table with Linear Probing
 *
 * Open addressing implementation with automatic resizing.
 *
 * @package CodeWithPHP\Algorithms\Chapter13
 */

declare(strict_types=1);

class HashTableLinearProbing
{
    private array $keys;
    private array $values;
    private int $size;
    private int $count = 0;

    public function __construct(int $size = 100)
    {
        $this->size = $size;
        $this->keys = array_fill(0, $size, null);
        $this->values = array_fill(0, $size, null);
    }

    private function hash(string $key): int
    {
        return abs(crc32($key) % $this->size);
    }

    public function set(string $key, mixed $value): void
    {
        if ($this->count >= $this->size * 0.7) {
            $this->resize();
        }

        $index = $this->hash($key);

        // Linear probing
        while ($this->keys[$index] !== null && $this->keys[$index] !== $key) {
            $index = ($index + 1) % $this->size;
        }

        $isNew = $this->keys[$index] === null;
        $this->keys[$index] = $key;
        $this->values[$index] = $value;

        if ($isNew) {
            $this->count++;
        }
    }

    public function get(string $key): mixed
    {
        $index = $this->hash($key);

        while ($this->keys[$index] !== null) {
            if ($this->keys[$index] === $key) {
                return $this->values[$index];
            }
            $index = ($index + 1) % $this->size;
        }

        return null;
    }

    private function resize(): void
    {
        $oldKeys = $this->keys;
        $oldValues = $this->values;

        $this->size *= 2;
        $this->keys = array_fill(0, $this->size, null);
        $this->values = array_fill(0, $this->size, null);
        $this->count = 0;

        foreach ($oldKeys as $i => $key) {
            if ($key !== null) {
                $this->set($key, $oldValues[$i]);
            }
        }
    }
}

// DEMONSTRATIONS
echo "=== Hash Table with Linear Probing ===\n\n";

$ht = new HashTableLinearProbing(10);
for ($i = 0; $i < 8; $i++) {
    $ht->set("key$i", "value$i");
}
echo "Stored 8 items successfully\n";
echo "Get key3: " . $ht->get("key3") . "\n";
