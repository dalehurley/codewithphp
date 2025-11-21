<?php

declare(strict_types=1);

/**
 * Priority Queue Implementation using Max Heap
 */

class PriorityQueue
{
    private array $heap = [];

    public function insert(int $value): void
    {
        $this->heap[] = $value;
        $this->bubbleUp(count($this->heap) - 1);
    }

    public function extractMax(): ?int
    {
        if (empty($this->heap)) return null;

        $max = $this->heap[0];
        $this->heap[0] = array_pop($this->heap);

        if (!empty($this->heap)) {
            $this->heapify(0);
        }

        return $max;
    }

    public function peek(): ?int
    {
        return $this->heap[0] ?? null;
    }

    public function size(): int
    {
        return count($this->heap);
    }

    public function isEmpty(): bool
    {
        return empty($this->heap);
    }

    private function bubbleUp(int $i): void
    {
        while ($i > 0) {
            $parent = (int)(($i - 1) / 2);

            if ($this->heap[$i] <= $this->heap[$parent]) {
                break;
            }

            [$this->heap[$i], $this->heap[$parent]] = [$this->heap[$parent], $this->heap[$i]];
            $i = $parent;
        }
    }

    private function heapify(int $i): void
    {
        $n = count($this->heap);
        $left = 2 * $i + 1;
        $right = 2 * $i + 2;
        $largest = $i;

        if ($left < $n && $this->heap[$left] > $this->heap[$largest]) {
            $largest = $left;
        }

        if ($right < $n && $this->heap[$right] > $this->heap[$largest]) {
            $largest = $right;
        }

        if ($largest !== $i) {
            [$this->heap[$i], $this->heap[$largest]] = [$this->heap[$largest], $this->heap[$i]];
            $this->heapify($largest);
        }
    }
}

class Task
{
    public function __construct(
        public string $name,
        public int $priority
    ) {}
}

class TaskQueue
{
    private array $heap = [];

    public function insert(Task $task): void
    {
        $this->heap[] = $task;
        $this->bubbleUp(count($this->heap) - 1);
    }

    public function extractHighestPriority(): ?Task
    {
        if (empty($this->heap)) return null;

        $task = $this->heap[0];
        $this->heap[0] = array_pop($this->heap);

        if (!empty($this->heap)) {
            $this->heapify(0);
        }

        return $task;
    }

    private function bubbleUp(int $i): void
    {
        while ($i > 0) {
            $parent = (int)(($i - 1) / 2);
            if ($this->heap[$i]->priority <= $this->heap[$parent]->priority) break;
            [$this->heap[$i], $this->heap[$parent]] = [$this->heap[$parent], $this->heap[$i]];
            $i = $parent;
        }
    }

    private function heapify(int $i): void
    {
        $n = count($this->heap);
        $left = 2 * $i + 1;
        $right = 2 * $i + 2;
        $largest = $i;

        if ($left < $n && $this->heap[$left]->priority > $this->heap[$largest]->priority) {
            $largest = $left;
        }

        if ($right < $n && $this->heap[$right]->priority > $this->heap[$largest]->priority) {
            $largest = $right;
        }

        if ($largest !== $i) {
            [$this->heap[$i], $this->heap[$largest]] = [$this->heap[$largest], $this->heap[$i]];
            $this->heapify($largest);
        }
    }
}

// ============================================================================
// Examples
// ============================================================================

echo "PRIORITY QUEUE\n";
echo str_repeat('=', 70) . "\n\n";

echo "Example 1: Simple Priority Queue\n";
echo str_repeat('-', 70) . "\n";
$pq = new PriorityQueue();
$pq->insert(10);
$pq->insert(5);
$pq->insert(20);
$pq->insert(1);

echo "Extracting in priority order:\n";
while (!$pq->isEmpty()) {
    echo "  " . $pq->extractMax() . "\n";
}
echo "\n";

echo "Example 2: Task Scheduling\n";
echo str_repeat('-', 70) . "\n";
$taskQueue = new TaskQueue();
$taskQueue->insert(new Task('Low priority task', 1));
$taskQueue->insert(new Task('High priority task', 10));
$taskQueue->insert(new Task('Medium priority task', 5));

echo "Processing tasks in priority order:\n";
while (($task = $taskQueue->extractHighestPriority()) !== null) {
    echo "  Processing: {$task->name} (priority: {$task->priority})\n";
}
echo "\n";

echo "Applications:\n";
echo "✓ Job scheduling\n";
echo "✓ Event-driven simulation\n";
echo "✓ Dijkstra's shortest path\n";
echo "✓ Huffman encoding\n";
echo "✓ Operating system process scheduling\n";
