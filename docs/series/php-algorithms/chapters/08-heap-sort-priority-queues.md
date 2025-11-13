---
title: "08: Heap Sort & Priority Queues"
description: "Build a heap data structure and use it for efficient sorting. Understand heap properties and operations."
series: "php-algorithms"
chapter: 8
order: 8
difficulty: "Advanced"
prerequisites:
  - "Understanding of trees"
  - "Understanding of Big O notation"
  - "Completion of Chapters 05-07"
---

# Heap Sort & Priority Queues

In this chapter, we'll explore **heaps**—a powerful tree-based data structure that enables efficient sorting and priority management. We'll build heap sort, understand heap operations, and create a priority queue for real-world applications.

## What Is a Heap?

A **heap** is a complete binary tree that satisfies the **heap property**:

- **Max Heap:** Parent node ≥ all children
- **Min Heap:** Parent node ≤ all children

**Example Max Heap:**
```
       90
      /  \
    80    70
   / \    /
  60 50  40
```

**Key characteristics:**
- Complete binary tree (filled left to right, level by level)
- Parent-child relationship determines structure
- Root is always maximum (max heap) or minimum (min heap)

## Array Representation

Heaps are efficiently stored in arrays using index arithmetic:

```
Array: [90, 80, 70, 60, 50, 40]

Tree representation:
         90 (index 0)
        /  \
      80    70
     / \    /
    60 50  40
```

**Index formulas** (0-based indexing):
- Parent of node at index i: `(i - 1) / 2`
- Left child of node at index i: `2 * i + 1`
- Right child of node at index i: `2 * i + 2`

```php
class Heap
{
    private array $heap = [];

    private function parent(int $i): int
    {
        return (int)(($i - 1) / 2);
    }

    private function leftChild(int $i): int
    {
        return 2 * $i + 1;
    }

    private function rightChild(int $i): int
    {
        return 2 * i + 2;
    }
}
```

## Building a Max Heap

### The Heapify Operation

**Heapify** maintains the heap property by "bubbling down" a node:

```php
class MaxHeap
{
    private array $heap = [];

    // O(log n) - bubble down from index i
    private function heapify(int $i, int $heapSize): void
    {
        $left = 2 * $i + 1;
        $right = 2 * $i + 2;
        $largest = $i;

        // Find largest among node, left child, right child
        if ($left < $heapSize && $this->heap[$left] > $this->heap[$largest]) {
            $largest = $left;
        }

        if ($right < $heapSize && $this->heap[$right] > $this->heap[$largest]) {
            $largest = $right;
        }

        // If largest is not current node, swap and continue heapifying
        if ($largest !== $i) {
            [$this->heap[$i], $this->heap[$largest]] =
                [$this->heap[$largest], $this->heap[$i]];

            $this->heapify($largest, $heapSize);
        }
    }

    // Build heap from array - O(n)
    public function buildHeap(array $arr): void
    {
        $this->heap = $arr;
        $n = count($this->heap);

        // Start from last non-leaf node and heapify all
        for ($i = (int)(($n / 2) - 1); $i >= 0; $i--) {
            $this->heapify($i, $n);
        }
    }

    public function getHeap(): array
    {
        return $this->heap;
    }
}

// Usage
$heap = new MaxHeap();
$heap->buildHeap([4, 10, 3, 5, 1]);
print_r($heap->getHeap()); // [10, 5, 3, 4, 1]
```

### Visualizing Heapify

```php
function heapifyVisualized(array &$arr, int $i, int $heapSize, int $depth = 0): void
{
    $indent = str_repeat('  ', $depth);
    echo $indent . "Heapify node at index $i (value: {$arr[$i]})\n";

    $left = 2 * $i + 1;
    $right = 2 * $i + 2;
    $largest = $i;

    if ($left < $heapSize && $arr[$left] > $arr[$largest]) {
        $largest = $left;
    }

    if ($right < $heapSize && $arr[$right] > $arr[$largest]) {
        $largest = $right;
    }

    if ($largest !== $i) {
        echo $indent . "  Swap {$arr[$i]} and {$arr[$largest]}\n";
        [$arr[$i], $arr[$largest]] = [$arr[$largest], $arr[$i]];
        echo $indent . "  Array now: [" . implode(', ', $arr) . "]\n";

        heapifyVisualized($arr, $largest, $heapSize, $depth + 1);
    } else {
        echo $indent . "  Heap property satisfied\n";
    }
}
```

## Heap Sort Algorithm

Heap sort uses a max heap to sort in ascending order:

1. Build a max heap from the array
2. Repeatedly extract maximum (root) and rebuild heap

```php
function heapSort(array $arr): array
{
    $n = count($arr);

    // Step 1: Build max heap
    for ($i = (int)(($n / 2) - 1); $i >= 0; $i--) {
        heapify($arr, $i, $n);
    }

    // Step 2: Extract elements one by one
    for ($i = $n - 1; $i > 0; $i--) {
        // Move current root (max) to end
        [$arr[0], $arr[$i]] = [$arr[$i], $arr[0]];

        // Heapify the reduced heap
        heapify($arr, 0, $i);
    }

    return $arr;
}

function heapify(array &$arr, int $i, int $heapSize): void
{
    $left = 2 * $i + 1;
    $right = 2 * $i + 2;
    $largest = $i;

    if ($left < $heapSize && $arr[$left] > $arr[$largest]) {
        $largest = $left;
    }

    if ($right < $heapSize && $arr[$right] > $arr[$largest]) {
        $largest = $right;
    }

    if ($largest !== $i) {
        [$arr[$i], $arr[$largest]] = [$arr[$largest], $arr[$i]];
        heapify($arr, $largest, $heapSize);
    }
}

// Test
$numbers = [12, 11, 13, 5, 6, 7];
print_r(heapSort($numbers));
// Output: [5, 6, 7, 11, 12, 13]
```

### Complexity Analysis

- **Time Complexity:**
  - Build heap: O(n)
  - Extract max and heapify: O(log n) × n times = O(n log n)
  - **Total: O(n log n)** for all cases (best, average, worst)

- **Space Complexity:** O(1) - sorts in place

- **Stable:** No - heap sort is not stable

### Why O(n) to Build Heap?

Seems like it should be O(n log n) since we heapify n/2 nodes at O(log n) each, but:

- Most nodes are near bottom (O(1) work)
- Only a few nodes are near top (O(log n) work)
- Mathematical analysis shows total work is O(n)

## Priority Queue

A **priority queue** is a data structure where elements are processed by priority, not insertion order.

### Max Priority Queue Implementation

```php
class PriorityQueue
{
    private array $heap = [];

    // Insert element - O(log n)
    public function insert(int $value): void
    {
        // Add to end
        $this->heap[] = $value;

        // Bubble up
        $this->bubbleUp(count($this->heap) - 1);
    }

    // Extract maximum - O(log n)
    public function extractMax(): ?int
    {
        if (empty($this->heap)) {
            return null;
        }

        $max = $this->heap[0];

        // Move last element to root
        $this->heap[0] = array_pop($this->heap);

        // Bubble down
        if (!empty($this->heap)) {
            $this->heapify(0);
        }

        return $max;
    }

    // Peek at maximum - O(1)
    public function peek(): ?int
    {
        return $this->heap[0] ?? null;
    }

    // Get size - O(1)
    public function size(): int
    {
        return count($this->heap);
    }

    // Check if empty - O(1)
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

            [$this->heap[$i], $this->heap[$parent]] =
                [$this->heap[$parent], $this->heap[$i]];

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
            [$this->heap[$i], $this->heap[$largest]] =
                [$this->heap[$largest], $this->heap[$i]];

            $this->heapify($largest);
        }
    }

    public function display(): void
    {
        echo "[" . implode(', ', $this->heap) . "]\n";
    }
}

// Usage
$pq = new PriorityQueue();
$pq->insert(10);
$pq->insert(5);
$pq->insert(20);
$pq->insert(1);

echo "Max: " . $pq->extractMax() . "\n"; // 20
echo "Max: " . $pq->extractMax() . "\n"; // 10
echo "Max: " . $pq->extractMax() . "\n"; // 5
```

### Min Priority Queue

Simply invert the comparisons:

```php
class MinPriorityQueue
{
    private array $heap = [];

    public function insert(int $value): void
    {
        $this->heap[] = $value;
        $this->bubbleUp(count($this->heap) - 1);
    }

    public function extractMin(): ?int
    {
        if (empty($this->heap)) {
            return null;
        }

        $min = $this->heap[0];
        $this->heap[0] = array_pop($this->heap);

        if (!empty($this->heap)) {
            $this->heapify(0);
        }

        return $min;
    }

    private function bubbleUp(int $i): void
    {
        while ($i > 0) {
            $parent = (int)(($i - 1) / 2);

            // Min heap: child < parent
            if ($this->heap[$i] >= $this->heap[$parent]) {
                break;
            }

            [$this->heap[$i], $this->heap[$parent]] =
                [$this->heap[$parent], $this->heap[$i]];

            $i = $parent;
        }
    }

    private function heapify(int $i): void
    {
        $n = count($this->heap);
        $left = 2 * $i + 1;
        $right = 2 * $i + 2;
        $smallest = $i;

        // Min heap: find smallest
        if ($left < $n && $this->heap[$left] < $this->heap[$smallest]) {
            $smallest = $left;
        }

        if ($right < $n && $this->heap[$right] < $this->heap[$smallest]) {
            $smallest = $right;
        }

        if ($smallest !== $i) {
            [$this->heap[$i], $this->heap[$smallest]] =
                [$this->heap[$smallest], $this->heap[$i]];

            $this->heapify($smallest);
        }
    }
}
```

## Priority Queue with Objects

```php
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
        if (empty($this->heap)) {
            return null;
        }

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

            if ($this->heap[$i]->priority <= $this->heap[$parent]->priority) {
                break;
            }

            [$this->heap[$i], $this->heap[$parent]] =
                [$this->heap[$parent], $this->heap[$i]];

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
            [$this->heap[$i], $this->heap[$largest]] =
                [$this->heap[$largest], $this->heap[$i]];

            $this->heapify($largest);
        }
    }
}

// Usage
$queue = new TaskQueue();
$queue->insert(new Task('Low priority task', 1));
$queue->insert(new Task('High priority task', 10));
$queue->insert(new Task('Medium priority task', 5));

$task = $queue->extractHighestPriority();
echo "{$task->name} (priority: {$task->priority})\n";
// Output: High priority task (priority: 10)
```

## Real-World Applications

### 1. Job Scheduling System

```php
class Job
{
    public function __construct(
        public string $id,
        public int $priority,
        public callable $task
    ) {}
}

class JobScheduler
{
    private TaskQueue $queue;

    public function __construct()
    {
        $this->queue = new TaskQueue();
    }

    public function scheduleJob(string $id, int $priority, callable $task): void
    {
        $job = new Job($id, $priority, $task);
        $this->queue->insert($job);
        echo "Scheduled job $id with priority $priority\n";
    }

    public function processNextJob(): void
    {
        $job = $this->queue->extractHighestPriority();

        if ($job === null) {
            echo "No jobs in queue\n";
            return;
        }

        echo "Processing job {$job->id}...\n";
        ($job->task)();
        echo "Job {$job->id} completed\n";
    }

    public function processAll(): void
    {
        while (!$this->queue->isEmpty()) {
            $this->processNextJob();
        }
    }
}

// Usage
$scheduler = new JobScheduler();
$scheduler->scheduleJob('email', 5, fn() => print("Sending email\n"));
$scheduler->scheduleJob('backup', 10, fn() => print("Running backup\n"));
$scheduler->scheduleJob('cleanup', 1, fn() => print("Cleaning up\n"));

$scheduler->processAll();
// Output:
// Processing job backup... (highest priority)
// Processing job email...
// Processing job cleanup...
```

### 2. Finding Top K Elements

```php
function findTopK(array $nums, int $k): array
{
    $minHeap = new MinPriorityQueue();

    foreach ($nums as $num) {
        $minHeap->insert($num);

        // Keep only k largest
        if ($minHeap->size() > $k) {
            $minHeap->extractMin();
        }
    }

    // Extract all elements
    $result = [];
    while (!$minHeap->isEmpty()) {
        $result[] = $minHeap->extractMin();
    }

    return array_reverse($result);
}

$numbers = [3, 2, 1, 5, 6, 4];
print_r(findTopK($numbers, 2)); // [5, 6]
```

### 3. Merge K Sorted Arrays

```php
function mergeKSortedArrays(array $arrays): array
{
    $minHeap = new MinPriorityQueue();
    $result = [];

    // Add first element from each array
    $pointers = array_fill(0, count($arrays), 0);

    foreach ($arrays as $i => $arr) {
        if (!empty($arr)) {
            $minHeap->insert(['value' => $arr[0], 'arrayIndex' => $i]);
        }
    }

    // Extract min and add next element from same array
    while (!$minHeap->isEmpty()) {
        $min = $minHeap->extractMin();
        $result[] = $min['value'];

        $arrayIndex = $min['arrayIndex'];
        $pointers[$arrayIndex]++;

        if ($pointers[$arrayIndex] < count($arrays[$arrayIndex])) {
            $minHeap->insert([
                'value' => $arrays[$arrayIndex][$pointers[$arrayIndex]],
                'arrayIndex' => $arrayIndex
            ]);
        }
    }

    return $result;
}
```

## Heap Sort vs Other Sorting Algorithms

| Feature | Heap Sort | Quick Sort | Merge Sort |
|---------|-----------|------------|------------|
| **Best time** | O(n log n) | O(n log n) | O(n log n) |
| **Average time** | O(n log n) | O(n log n) | O(n log n) |
| **Worst time** | O(n log n) | O(n²) | O(n log n) |
| **Space** | O(1) | O(log n) | O(n) |
| **Stable** | No | No | Yes |
| **In-place** | Yes | Yes | No |
| **Cache locality** | Poor | Excellent | Good |

**When to use Heap Sort:**
- Need guaranteed O(n log n)
- Limited memory (in-place sorting)
- Don't need stability
- Finding top K elements

**When NOT to use Heap Sort:**
- Need stability
- Cache performance critical (use quick sort)
- Small arrays (use insertion sort)

## Practice Exercises

### Exercise 1: Kth Largest Element

Find the kth largest element in an unsorted array:

```php
function findKthLargest(array $nums, int $k): int
{
    // Your code here (use min heap of size k)
}

echo findKthLargest([3, 2, 1, 5, 6, 4], 2); // Should output: 5
```

### Exercise 2: Running Median

Calculate median after each element insertion:

```php
class MedianFinder
{
    // Use two heaps: max heap for lower half, min heap for upper half
    public function addNum(int $num): void
    {
        // Your code here
    }

    public function findMedian(): float
    {
        // Your code here
    }
}
```

### Exercise 3: Reorganize String

Rearrange string so no two adjacent characters are the same:

```php
function reorganizeString(string $s): string
{
    // Your code here (use max heap for character frequencies)
}

echo reorganizeString('aab'); // Should output: 'aba' or 'baa'
```

## Key Takeaways

- **Heap** is a complete binary tree with heap property
- **Heap sort** is O(n log n) for all cases, in-place
- **Build heap** is O(n), not O(n log n)
- **Priority queue** enables efficient priority-based processing
- **Not cache-friendly** compared to quick sort
- **Not stable** - equal elements may be reordered
- Excellent for **top K problems** and **job scheduling**

## What's Next

In the next chapter, we'll **Compare All Sorting Algorithms** we've learned, benchmark them against each other, and learn when to use each one.

---

Continue to [Chapter 09: Comparing Sorting Algorithms](/series/php-algorithms/chapters/09-comparing-sorting-algorithms).
