<?php

/**
 * Circular Buffer Implementation
 *
 * Fixed-size FIFO buffer that overwrites oldest data.
 *
 * @package CodeWithPHP\Algorithms\Chapter15
 */

declare(strict_types=1);

class CircularBuffer
{
    private array $buffer;
    private int $size;
    private int $head = 0;
    private int $tail = 0;
    private int $count = 0;

    public function __construct(int $size)
    {
        $this->size = $size;
        $this->buffer = array_fill(0, $size, null);
    }

    public function enqueue(mixed $value): bool
    {
        if ($this->isFull()) {
            return false;
        }

        $this->buffer[$this->tail] = $value;
        $this->tail = ($this->tail + 1) % $this->size;
        $this->count++;

        return true;
    }

    public function dequeue(): mixed
    {
        if ($this->isEmpty()) {
            throw new UnderflowException("Buffer is empty");
        }

        $value = $this->buffer[$this->head];
        $this->buffer[$this->head] = null;
        $this->head = ($this->head + 1) % $this->size;
        $this->count--;

        return $value;
    }

    public function isFull(): bool
    {
        return $this->count === $this->size;
    }

    public function isEmpty(): bool
    {
        return $this->count === 0;
    }

    public function display(): void
    {
        echo "Buffer: [";
        $items = [];
        $current = $this->head;

        for ($i = 0; $i < $this->count; $i++) {
            $items[] = $this->buffer[$current];
            $current = ($current + 1) % $this->size;
        }

        echo implode(', ', $items) . "]\n";
    }
}

// DEMONSTRATIONS
echo "=== Circular Buffer ===\n\n";

$buffer = new CircularBuffer(5);

echo "Enqueuing A, B, C\n";
$buffer->enqueue('A');
$buffer->enqueue('B');
$buffer->enqueue('C');
$buffer->display();

echo "\nDequeuing: " . $buffer->dequeue() . "\n";
$buffer->display();

echo "\nEnqueuing D, E, F\n";
$buffer->enqueue('D');
$buffer->enqueue('E');
$buffer->enqueue('F');
$buffer->display();
