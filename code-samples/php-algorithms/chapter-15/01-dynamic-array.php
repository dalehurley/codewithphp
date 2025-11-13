<?php

/**
 * Dynamic Array Implementation
 *
 * Array that automatically resizes when capacity is reached.
 * Time Complexity: O(1) amortized for append
 *
 * @package CodeWithPHP\Algorithms\Chapter15
 */

declare(strict_types=1);

class DynamicArray
{
    private array $data;
    private int $size;
    private int $capacity;

    public function __construct(int $initialCapacity = 10)
    {
        $this->capacity = $initialCapacity;
        $this->size = 0;
        $this->data = array_fill(0, $this->capacity, null);
    }

    public function get(int $index): mixed
    {
        if ($index < 0 || $index >= $this->size) {
            throw new OutOfBoundsException("Index out of bounds");
        }

        return $this->data[$index];
    }

    public function set(int $index, mixed $value): void
    {
        if ($index < 0 || $index >= $this->size) {
            throw new OutOfBoundsException("Index out of bounds");
        }

        $this->data[$index] = $value;
    }

    public function push(mixed $value): void
    {
        if ($this->size === $this->capacity) {
            $this->resize();
        }

        $this->data[$this->size] = $value;
        $this->size++;
    }

    public function pop(): mixed
    {
        if ($this->size === 0) {
            throw new UnderflowException("Array is empty");
        }

        $this->size--;
        $value = $this->data[$this->size];
        $this->data[$this->size] = null;

        if ($this->size > 0 && $this->size === (int)($this->capacity / 4)) {
            $this->shrink();
        }

        return $value;
    }

    public function insert(int $index, mixed $value): void
    {
        if ($index < 0 || $index > $this->size) {
            throw new OutOfBoundsException("Index out of bounds");
        }

        if ($this->size === $this->capacity) {
            $this->resize();
        }

        for ($i = $this->size; $i > $index; $i--) {
            $this->data[$i] = $this->data[$i - 1];
        }

        $this->data[$index] = $value;
        $this->size++;
    }

    private function resize(): void
    {
        $this->capacity *= 2;
        $newData = array_fill(0, $this->capacity, null);

        for ($i = 0; $i < $this->size; $i++) {
            $newData[$i] = $this->data[$i];
        }

        $this->data = $newData;
        echo "Resized to capacity: {$this->capacity}\n";
    }

    private function shrink(): void
    {
        $this->capacity = (int)($this->capacity / 2);
        $newData = array_fill(0, $this->capacity, null);

        for ($i = 0; $i < $this->size; $i++) {
            $newData[$i] = $this->data[$i];
        }

        $this->data = $newData;
        echo "Shrunk to capacity: {$this->capacity}\n";
    }

    public function size(): int
    {
        return $this->size;
    }

    public function capacity(): int
    {
        return $this->capacity;
    }

    public function toArray(): array
    {
        return array_slice($this->data, 0, $this->size);
    }

    public function display(): void
    {
        echo "Size: {$this->size}, Capacity: {$this->capacity}\n";
        echo "Data: [" . implode(', ', $this->toArray()) . "]\n";
    }
}

// DEMONSTRATIONS
echo "=== Dynamic Array ===\n\n";

echo "Example 1: Auto-resizing\n";
echo str_repeat('-', 50) . "\n";
$arr = new DynamicArray(4);

for ($i = 1; $i <= 5; $i++) {
    $arr->push($i * 10);
}

$arr->display();
echo "\n";

echo "Example 2: Insert and remove\n";
echo str_repeat('-', 50) . "\n";
$arr = new DynamicArray();
$arr->push(10);
$arr->push(20);
$arr->push(30);
$arr->insert(1, 15);
$arr->display();
echo "Popped: " . $arr->pop() . "\n";
$arr->display();
