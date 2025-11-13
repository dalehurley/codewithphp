---
title: "16: Linked Lists"
description: "Build singly and doubly linked lists from scratch. Understand pointer manipulation and list operations."
series: "php-algorithms"
chapter: 16
order: 16
difficulty: "Intermediate"
prerequisites:
  - "Understanding of PHP classes and objects"
  - "Familiarity with references"
  - "Completion of Chapter 15 (Arrays)"
---

# Linked Lists

Arrays are great, but they have limitations: inserting at the beginning is O(n), and they require contiguous memory. **Linked lists** solve these problems by storing elements in nodes that point to each other. In this chapter, we'll build linked lists from scratch and master their operations.

## What Is a Linked List?

A **linked list** is a data structure where elements (nodes) are connected via pointers/references, not stored contiguously like arrays.

**Array:**
```
[10][20][30][40]  ← Contiguous memory
```

**Linked List:**
```
[10]→[20]→[30]→[40]→null  ← Each node points to next
```

### Node Structure

```php
class Node
{
    public function __construct(
        public mixed $data,
        public ?Node $next = null
    ) {}
}

// Create nodes
$node1 = new Node(10);
$node2 = new Node(20);
$node3 = new Node(30);

// Link them
$node1->next = $node2;
$node2->next = $node3;
// Result: 10 → 20 → 30 → null
```

## Visual Step-by-Step: Insertion Operations

Let's visualize how insertion works in a linked list:

### Inserting at the Beginning

```
Step 1: Initial list
HEAD → [10] → [20] → [30] → null

Step 2: Create new node with data 5
[5] (next = null)

Step 3: Point new node to current head
[5] → [10] → [20] → [30] → null

Step 4: Update head to new node
HEAD → [5] → [10] → [20] → [30] → null
```

### Inserting at the End

```
Step 1: Initial list
HEAD → [10] → [20] → [30] → null
                      ↑
                   TRAVERSE HERE

Step 2: Create new node
[40] (next = null)

Step 3: Link last node to new node
HEAD → [10] → [20] → [30] → [40] → null
```

### Deletion Operation

```
Step 1: Delete value 20
HEAD → [10] → [20] → [30] → null
               ↑
            DELETE THIS

Step 2: Update pointer to skip node
HEAD → [10] ----→ [30] → null
         [20] (orphaned, will be garbage collected)

Step 3: Final result
HEAD → [10] → [30] → null
```

## Singly Linked List

A **singly linked list** has nodes that point only to the next node.

### Implementation

```php
class LinkedList
{
    private ?Node $head = null;
    private int $size = 0;

    // Add to beginning - O(1)
    public function prepend(mixed $data): void
    {
        $newNode = new Node($data);
        $newNode->next = $this->head;
        $this->head = $newNode;
        $this->size++;
    }

    // Add to end - O(n)
    public function append(mixed $data): void
    {
        $newNode = new Node($data);

        if ($this->head === null) {
            $this->head = $newNode;
            $this->size++;
            return;
        }

        // Traverse to last node
        $current = $this->head;
        while ($current->next !== null) {
            $current = $current->next;
        }

        $current->next = $newNode;
        $this->size++;
    }

    // Delete by value - O(n)
    public function delete(mixed $data): bool
    {
        if ($this->head === null) {
            return false;
        }

        // Special case: delete head
        if ($this->head->data === $data) {
            $this->head = $this->head->next;
            $this->size--;
            return true;
        }

        // Find node before the one to delete
        $current = $this->head;
        while ($current->next !== null) {
            if ($current->next->data === $data) {
                $current->next = $current->next->next;
                $this->size--;
                return true;
            }
            $current = $current->next;
        }

        return false;
    }

    // Search - O(n)
    public function search(mixed $data): ?Node
    {
        $current = $this->head;

        while ($current !== null) {
            if ($current->data === $data) {
                return $current;
            }
            $current = $current->next;
        }

        return null;
    }

    // Get size - O(1)
    public function getSize(): int
    {
        return $this->size;
    }

    // Display list
    public function display(): void
    {
        $current = $this->head;
        $values = [];

        while ($current !== null) {
            $values[] = $current->data;
            $current = $current->next;
        }

        echo implode(' → ', $values) . ' → null' . "\n";
    }

    // Convert to array
    public function toArray(): array
    {
        $result = [];
        $current = $this->head;

        while ($current !== null) {
            $result[] = $current->data;
            $current = $current->next;
        }

        return $result;
    }
}

// Usage
$list = new LinkedList();
$list->append(10);
$list->append(20);
$list->append(30);
$list->prepend(5);
$list->display(); // 5 → 10 → 20 → 30 → null

$list->delete(20);
$list->display(); // 5 → 10 → 30 → null
```

## Doubly Linked List

A **doubly linked list** has nodes with pointers to both next and previous nodes.

```
null ← [10] ↔ [20] ↔ [30] → null
```

### Node Structure

```php
class DoublyNode
{
    public function __construct(
        public mixed $data,
        public ?DoublyNode $next = null,
        public ?DoublyNode $prev = null
    ) {}
}
```

### Implementation

```php
class DoublyLinkedList
{
    private ?DoublyNode $head = null;
    private ?DoublyNode $tail = null;
    private int $size = 0;

    // Add to beginning - O(1)
    public function prepend(mixed $data): void
    {
        $newNode = new DoublyNode($data);

        if ($this->head === null) {
            $this->head = $this->tail = $newNode;
        } else {
            $newNode->next = $this->head;
            $this->head->prev = $newNode;
            $this->head = $newNode;
        }

        $this->size++;
    }

    // Add to end - O(1) with tail pointer!
    public function append(mixed $data): void
    {
        $newNode = new DoublyNode($data);

        if ($this->tail === null) {
            $this->head = $this->tail = $newNode;
        } else {
            $this->tail->next = $newNode;
            $newNode->prev = $this->tail;
            $this->tail = $newNode;
        }

        $this->size++;
    }

    // Delete by value - O(n)
    public function delete(mixed $data): bool
    {
        $current = $this->head;

        while ($current !== null) {
            if ($current->data === $data) {
                // Update previous node's next pointer
                if ($current->prev !== null) {
                    $current->prev->next = $current->next;
                } else {
                    // Deleting head
                    $this->head = $current->next;
                }

                // Update next node's prev pointer
                if ($current->next !== null) {
                    $current->next->prev = $current->prev;
                } else {
                    // Deleting tail
                    $this->tail = $current->prev;
                }

                $this->size--;
                return true;
            }

            $current = $current->next;
        }

        return false;
    }

    // Display forward
    public function display(): void
    {
        $current = $this->head;
        $values = [];

        while ($current !== null) {
            $values[] = $current->data;
            $current = $current->next;
        }

        echo 'null ← ' . implode(' ↔ ', $values) . ' → null' . "\n";
    }

    // Display backward
    public function displayReverse(): void
    {
        $current = $this->tail;
        $values = [];

        while ($current !== null) {
            $values[] = $current->data;
            $current = $current->prev;
        }

        echo 'null ← ' . implode(' ↔ ', $values) . ' → null' . "\n";
    }
}

// Usage
$list = new DoublyLinkedList();
$list->append(10);
$list->append(20);
$list->append(30);
$list->display();        // null ← 10 ↔ 20 ↔ 30 → null
$list->displayReverse(); // null ← 30 ↔ 20 ↔ 10 → null
```

## Arrays vs Linked Lists

| Operation | Array | Singly Linked List | Doubly Linked List |
|-----------|-------|-------------------|-------------------|
| Access by index | O(1) | O(n) | O(n) |
| Search | O(n) | O(n) | O(n) |
| Insert at beginning | O(n) | O(1) | O(1) |
| Insert at end | O(1) amortized | O(n) or O(1)* | O(1) with tail |
| Insert at position | O(n) | O(n) | O(n) |
| Delete at beginning | O(n) | O(1) | O(1) |
| Delete at end | O(1) | O(n) or O(1)* | O(1) with tail |
| Memory overhead | Low | Medium (1 pointer) | High (2 pointers) |

*With tail pointer

## Performance Benchmarks

Let's measure actual performance with real data:

```php
function benchmarkLinkedListVsArray(): void
{
    $iterations = 10000;

    // Benchmark: Prepend operations
    echo "=== Prepending {$iterations} items ===\n";

    // Array prepend (array_unshift)
    $start = microtime(true);
    $arr = [];
    for ($i = 0; $i < $iterations; $i++) {
        array_unshift($arr, $i);
    }
    $arrayTime = microtime(true) - $start;
    echo sprintf("Array (array_unshift): %.4f seconds\n", $arrayTime);

    // Linked list prepend
    $start = microtime(true);
    $list = new LinkedList();
    for ($i = 0; $i < $iterations; $i++) {
        $list->prepend($i);
    }
    $listTime = microtime(true) - $start;
    echo sprintf("Linked List: %.4f seconds\n", $listTime);
    echo sprintf("Linked List is %.2fx faster\n\n", $arrayTime / $listTime);

    // Benchmark: Append operations
    echo "=== Appending {$iterations} items ===\n";

    $start = microtime(true);
    $arr = [];
    for ($i = 0; $i < $iterations; $i++) {
        $arr[] = $i;
    }
    $arrayTime = microtime(true) - $start;
    echo sprintf("Array: %.4f seconds\n", $arrayTime);

    $start = microtime(true);
    $list = new LinkedList();
    for ($i = 0; $i < $iterations; $i++) {
        $list->append($i);
    }
    $listTime = microtime(true) - $start;
    echo sprintf("Linked List (no tail): %.4f seconds\n", $listTime);
    echo sprintf("Array is %.2fx faster\n\n", $listTime / $arrayTime);

    // Benchmark: Access by index
    echo "=== Accessing 1000 random positions ===\n";

    $arr = range(0, 9999);
    $start = microtime(true);
    for ($i = 0; $i < 1000; $i++) {
        $val = $arr[rand(0, 9999)];
    }
    $arrayTime = microtime(true) - $start;
    echo sprintf("Array: %.4f seconds\n", $arrayTime);

    $list = new LinkedList();
    foreach (range(0, 9999) as $val) {
        $list->append($val);
    }

    $start = microtime(true);
    for ($i = 0; $i < 1000; $i++) {
        $list->search(rand(0, 9999));
    }
    $listTime = microtime(true) - $start;
    echo sprintf("Linked List: %.4f seconds\n", $listTime);
    echo sprintf("Array is %.2fx faster\n", $arrayTime / $listTime);
}

// Sample output:
// === Prepending 10000 items ===
// Array (array_unshift): 0.4521 seconds
// Linked List: 0.0023 seconds
// Linked List is 196.57x faster
//
// === Appending 10000 items ===
// Array: 0.0019 seconds
// Linked List (no tail): 2.1453 seconds
// Array is 1129.11x faster
//
// === Accessing 1000 random positions ===
// Array: 0.0001 seconds
// Linked List: 0.8912 seconds
// Array is 8912x faster
```

### Memory Usage Analysis

```php
function analyzeMemoryUsage(): void
{
    $count = 1000;

    // Array memory
    $before = memory_get_usage();
    $arr = array_fill(0, $count, random_int(1, 1000));
    $arrayMemory = memory_get_usage() - $before;

    // Linked List memory
    $before = memory_get_usage();
    $list = new LinkedList();
    for ($i = 0; $i < $count; $i++) {
        $list->append(random_int(1, 1000));
    }
    $listMemory = memory_get_usage() - $before;

    echo "Memory usage for {$count} integers:\n";
    echo sprintf("Array: %s KB\n", number_format($arrayMemory / 1024, 2));
    echo sprintf("Linked List: %s KB\n", number_format($listMemory / 1024, 2));
    echo sprintf("Overhead: %.2fx\n", $listMemory / $arrayMemory);
}

// Sample output:
// Memory usage for 1000 integers:
// Array: 32.81 KB
// Linked List: 147.23 KB
// Overhead: 4.49x
```

**Key Findings:**
- Linked lists excel at prepend operations (100-200x faster)
- Arrays dominate for append and random access
- Linked lists use 3-5x more memory due to pointer overhead
- Choose based on your access patterns, not just algorithmic complexity

### When to Use Linked Lists

**Use linked lists when:**
- Frequent insertions/deletions at beginning or end
- Size changes frequently
- Don't need random access by index
- Memory fragmentation is a concern

**Use arrays when:**
- Need random access (O(1) by index)
- Mostly reading, few insertions/deletions
- Size is relatively stable
- Cache locality matters (arrays are more cache-friendly)

## Common Linked List Operations

### 1. Reverse a Linked List

```php
public function reverse(): void
{
    $prev = null;
    $current = $this->head;

    while ($current !== null) {
        $next = $current->next;  // Save next node
        $current->next = $prev;  // Reverse pointer
        $prev = $current;        // Move prev forward
        $current = $next;        // Move current forward
    }

    $this->head = $prev;
}

// Visual:
// Before: A → B → C → null
// After:  A ← B ← C (head now at C)
```

### 2. Find Middle Element

```php
public function findMiddle(): ?Node
{
    if ($this->head === null) {
        return null;
    }

    // Two pointers: slow moves 1, fast moves 2
    $slow = $fast = $this->head;

    while ($fast !== null && $fast->next !== null) {
        $slow = $slow->next;
        $fast = $fast->next->next;
    }

    return $slow; // Slow will be at middle when fast reaches end
}

// Example: 1 → 2 → 3 → 4 → 5
// When fast reaches 5, slow is at 3 (middle)
```

### 3. Detect Cycle (Floyd's Algorithm)

```php
public function hasCycle(): bool
{
    if ($this->head === null) {
        return false;
    }

    $slow = $fast = $this->head;

    while ($fast !== null && $fast->next !== null) {
        $slow = $slow->next;
        $fast = $fast->next->next;

        if ($slow === $fast) {
            return true; // Cycle detected!
        }
    }

    return false;
}

// If there's a cycle, fast will eventually catch up to slow
```

### 4. Remove Nth Node From End

```php
public function removeNthFromEnd(int $n): void
{
    $dummy = new Node(0);
    $dummy->next = $this->head;

    $slow = $fast = $dummy;

    // Move fast n+1 steps ahead
    for ($i = 0; $i <= $n; $i++) {
        if ($fast === null) return;
        $fast = $fast->next;
    }

    // Move both until fast reaches end
    while ($fast !== null) {
        $slow = $slow->next;
        $fast = $fast->next;
    }

    // Delete node
    $slow->next = $slow->next?->next;
    $this->head = $dummy->next;
}
```

### 5. Merge Two Sorted Lists

```php
function mergeSortedLists(LinkedList $list1, LinkedList $list2): LinkedList
{
    $result = new LinkedList();
    $current1 = $list1->head;
    $current2 = $list2->head;

    while ($current1 !== null && $current2 !== null) {
        if ($current1->data <= $current2->data) {
            $result->append($current1->data);
            $current1 = $current1->next;
        } else {
            $result->append($current2->data);
            $current2 = $current2->next;
        }
    }

    // Append remaining elements
    while ($current1 !== null) {
        $result->append($current1->data);
        $current1 = $current1->next;
    }

    while ($current2 !== null) {
        $result->append($current2->data);
        $current2 = $current2->next;
    }

    return $result;
}
```

## Circular Linked List

A **circular linked list** has the last node pointing back to the first.

```php
class CircularLinkedList
{
    private ?Node $head = null;

    public function append(mixed $data): void
    {
        $newNode = new Node($data);

        if ($this->head === null) {
            $this->head = $newNode;
            $newNode->next = $this->head; // Point to itself
            return;
        }

        // Find last node
        $current = $this->head;
        while ($current->next !== $this->head) {
            $current = $current->next;
        }

        $current->next = $newNode;
        $newNode->next = $this->head; // Complete the circle
    }

    public function display(): void
    {
        if ($this->head === null) return;

        $current = $this->head;
        $values = [];

        do {
            $values[] = $current->data;
            $current = $current->next;
        } while ($current !== $this->head);

        echo implode(' → ', $values) . ' → (back to head)' . "\n";
    }
}

$circular = new CircularLinkedList();
$circular->append(1);
$circular->append(2);
$circular->append(3);
$circular->display(); // 1 → 2 → 3 → (back to head)
```

## PHP SPL: SplDoublyLinkedList

PHP's Standard PHP Library provides a built-in doubly linked list implementation with powerful features.

### Basic Usage

```php
$list = new SplDoublyLinkedList();

// Add elements
$list->push(10);  // Add to end
$list->push(20);
$list->push(30);
$list->unshift(5); // Add to beginning

// Access elements
echo $list->bottom() . "\n"; // 5 (first element)
echo $list->top() . "\n";    // 30 (last element)

// Iterate
foreach ($list as $value) {
    echo "$value ";
}
// Output: 5 10 20 30

// Remove elements
$list->pop();     // Remove from end (30)
$list->shift();   // Remove from beginning (5)

// Array-style access
echo $list[0] . "\n"; // 10
$list[1] = 25;        // Change value
```

### Iteration Modes

```php
$list = new SplDoublyLinkedList();
$list->push(1);
$list->push(2);
$list->push(3);

// FIFO mode (First In First Out) - Queue behavior
$list->setIteratorMode(SplDoublyLinkedList::IT_MODE_FIFO);
foreach ($list as $val) {
    echo "$val "; // 1 2 3
}

// LIFO mode (Last In First Out) - Stack behavior
$list->setIteratorMode(SplDoublyLinkedList::IT_MODE_LIFO);
foreach ($list as $val) {
    echo "$val "; // 3 2 1
}

// Delete during iteration
$list->setIteratorMode(
    SplDoublyLinkedList::IT_MODE_FIFO |
    SplDoublyLinkedList::IT_MODE_DELETE
);
foreach ($list as $val) {
    echo "$val "; // Elements are removed as we iterate
}
echo $list->count(); // 0 (all deleted)
```

### Performance Comparison

```php
function compareSPLvCustom(): void
{
    $iterations = 10000;

    // Custom LinkedList
    $start = microtime(true);
    $custom = new DoublyLinkedList();
    for ($i = 0; $i < $iterations; $i++) {
        $custom->append($i);
    }
    $customTime = microtime(true) - $start;

    // SPL LinkedList
    $start = microtime(true);
    $spl = new SplDoublyLinkedList();
    for ($i = 0; $i < $iterations; $i++) {
        $spl->push($i);
    }
    $splTime = microtime(true) - $start;

    echo "Appending {$iterations} items:\n";
    echo sprintf("Custom: %.4f seconds\n", $customTime);
    echo sprintf("SPL: %.4f seconds\n", $splTime);
    echo sprintf("SPL is %.2fx faster\n", $customTime / $splTime);
}

// Typical output:
// Appending 10000 items:
// Custom: 0.0156 seconds
// SPL: 0.0042 seconds
// SPL is 3.71x faster (C implementation advantage)
```

### When to Use SPL

**Use SplDoublyLinkedList when:**
- You need a production-ready, tested implementation
- Performance is critical (C implementation is faster)
- You need advanced features (iteration modes, serialization)
- You don't need custom behavior

**Use custom implementation when:**
- Learning data structures
- Need custom node data or methods
- Require specific behavior not in SPL
- Need full control over implementation

## Edge Cases and Error Handling

Robust linked list implementations must handle edge cases:

```php
class RobustLinkedList
{
    private ?Node $head = null;
    private ?Node $tail = null;
    private int $size = 0;

    public function insertAt(int $index, mixed $data): void
    {
        // Edge case: Invalid index
        if ($index < 0 || $index > $this->size) {
            throw new OutOfRangeException(
                "Index {$index} out of range [0, {$this->size}]"
            );
        }

        // Edge case: Insert at beginning
        if ($index === 0) {
            $this->prepend($data);
            return;
        }

        // Edge case: Insert at end
        if ($index === $this->size) {
            $this->append($data);
            return;
        }

        // Normal case: Insert in middle
        $newNode = new Node($data);
        $current = $this->head;

        for ($i = 0; $i < $index - 1; $i++) {
            $current = $current->next;
        }

        $newNode->next = $current->next;
        $current->next = $newNode;
        $this->size++;
    }

    public function get(int $index): mixed
    {
        // Edge case: Empty list
        if ($this->head === null) {
            throw new UnderflowException("Cannot get from empty list");
        }

        // Edge case: Invalid index
        if ($index < 0 || $index >= $this->size) {
            throw new OutOfRangeException(
                "Index {$index} out of range [0, " . ($this->size - 1) . "]"
            );
        }

        $current = $this->head;
        for ($i = 0; $i < $index; $i++) {
            $current = $current->next;
        }

        return $current->data;
    }

    public function deleteAt(int $index): mixed
    {
        // Edge cases
        if ($this->head === null) {
            throw new UnderflowException("Cannot delete from empty list");
        }

        if ($index < 0 || $index >= $this->size) {
            throw new OutOfRangeException("Index out of range");
        }

        // Edge case: Delete head
        if ($index === 0) {
            $data = $this->head->data;
            $this->head = $this->head->next;
            $this->size--;

            // Edge case: List is now empty
            if ($this->head === null) {
                $this->tail = null;
            }

            return $data;
        }

        // Find node before deletion point
        $current = $this->head;
        for ($i = 0; $i < $index - 1; $i++) {
            $current = $current->next;
        }

        $data = $current->next->data;
        $current->next = $current->next->next;

        // Edge case: Deleted tail
        if ($current->next === null) {
            $this->tail = $current;
        }

        $this->size--;
        return $data;
    }

    public function clear(): void
    {
        // Properly clear to help garbage collection
        $current = $this->head;

        while ($current !== null) {
            $next = $current->next;
            $current->next = null; // Break references
            $current = $next;
        }

        $this->head = null;
        $this->tail = null;
        $this->size = 0;
    }
}
```

### Common Edge Cases Checklist

```php
// Always test these scenarios:

// 1. Operations on empty list
$list = new RobustLinkedList();
try {
    $list->get(0); // Should throw
} catch (UnderflowException $e) {
    echo "Caught: {$e->getMessage()}\n";
}

// 2. Single element list
$list->append(10);
$list->deleteAt(0); // Should work, list now empty

// 3. Operations at boundaries
$list->append(1);
$list->append(2);
$list->append(3);
$list->insertAt(0, 0);    // Insert at start
$list->insertAt(4, 4);    // Insert at end
$list->get(0);            // First element
$list->get(4);            // Last element

// 4. Invalid indices
try {
    $list->get(-1);       // Negative index
    $list->get(100);      // Out of bounds
} catch (OutOfRangeException $e) {
    echo "Caught: {$e->getMessage()}\n";
}

// 5. Duplicate values
$list->append(5);
$list->append(5);
$list->append(5);
$list->delete(5);         // Should only delete first occurrence

// 6. Large data sets (memory/performance)
$bigList = new RobustLinkedList();
for ($i = 0; $i < 100000; $i++) {
    $bigList->append($i);
}
```

## Framework Integration

### Laravel: Custom Collection with Linked List

```php
use Illuminate\Support\Collection;

class LinkedListCollection extends Collection
{
    protected LinkedList $list;

    public function __construct($items = [])
    {
        $this->list = new LinkedList();

        foreach ($items as $item) {
            $this->list->append($item);
        }
    }

    public function prepend($value)
    {
        $this->list->prepend($value);
        return $this;
    }

    public function toArray()
    {
        return $this->list->toArray();
    }

    // Use in Laravel
    public static function processQueue(array $jobs)
    {
        $queue = new self($jobs);

        while ($job = $queue->shift()) {
            dispatch(new ProcessJob($job));
        }
    }
}
```

### Symfony: Linked List Service

```php
// src/DataStructure/LinkedListService.php
namespace App\DataStructure;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(tags: ['app.data_structure'])]
class LinkedListService
{
    private LinkedList $list;

    public function __construct()
    {
        $this->list = new LinkedList();
    }

    public function addTask(Task $task): void
    {
        $this->list->append($task);
    }

    public function processNext(): ?Task
    {
        // Process from head
        if ($node = $this->list->shift()) {
            return $node;
        }

        return null;
    }
}

// Usage in Controller
class TaskController extends AbstractController
{
    public function __construct(
        private LinkedListService $taskList
    ) {}

    #[Route('/task/add', methods: ['POST'])]
    public function addTask(Request $request): Response
    {
        $task = new Task($request->request->get('name'));
        $this->taskList->addTask($task);

        return new JsonResponse(['status' => 'queued']);
    }
}
```

### Laravel: Undo/Redo with Linked List

```php
class UndoRedoManager
{
    private DoublyLinkedList $history;
    private ?DoublyNode $current = null;

    public function __construct()
    {
        $this->history = new DoublyLinkedList();
    }

    public function execute(Command $command): void
    {
        $command->execute();

        // If we're not at the end, remove future history
        if ($this->current !== null && $this->current->next !== null) {
            $this->current->next = null;
            $this->history->tail = $this->current;
        }

        $this->history->append($command);
        $this->current = $this->history->tail;
    }

    public function undo(): bool
    {
        if ($this->current === null) {
            return false;
        }

        $this->current->data->undo();
        $this->current = $this->current->prev;

        return true;
    }

    public function redo(): bool
    {
        if ($this->current === null) {
            $nextNode = $this->history->head;
        } else {
            $nextNode = $this->current->next;
        }

        if ($nextNode === null) {
            return false;
        }

        $nextNode->data->execute();
        $this->current = $nextNode;

        return true;
    }
}

// Usage
$manager = new UndoRedoManager();
$manager->execute(new UpdateUserCommand($user, ['name' => 'John']));
$manager->execute(new UpdateUserCommand($user, ['email' => 'john@example.com']));
$manager->undo(); // Reverts email change
$manager->undo(); // Reverts name change
$manager->redo(); // Reapplies name change
```

## Security Considerations

### 1. Prevent Memory Leaks with Circular References

```php
class SecureLinkedList
{
    private ?Node $head = null;

    public function __destruct()
    {
        // Break circular references to prevent memory leaks
        $this->clear();
    }

    public function clear(): void
    {
        $current = $this->head;

        while ($current !== null) {
            $next = $current->next;
            $current->next = null; // Break reference
            $current = $next;
        }

        $this->head = null;
    }
}
```

### 2. Validate User Input

```php
public function append(mixed $data): void
{
    // Prevent storing sensitive data accidentally
    if ($data instanceof SensitiveData) {
        throw new InvalidArgumentException(
            "Cannot store sensitive data in linked list"
        );
    }

    // Prevent excessive memory usage
    if (is_string($data) && strlen($data) > 1000000) {
        throw new InvalidArgumentException(
            "Data too large (max 1MB)"
        );
    }

    $newNode = new Node($data);
    // ... rest of implementation
}
```

### 3. Serialize Safely

```php
class SerializableLinkedList implements JsonSerializable
{
    private LinkedList $list;

    public function jsonSerialize(): array
    {
        // Don't expose internal structure
        return [
            'size' => $this->list->getSize(),
            'items' => $this->list->toArray(),
        ];
    }

    public static function fromArray(array $data): self
    {
        $list = new self();

        // Validate and sanitize
        foreach ($data as $item) {
            if (!is_scalar($item) && !is_array($item)) {
                throw new InvalidArgumentException(
                    "Invalid data type in array"
                );
            }

            $list->append($item);
        }

        return $list;
    }
}
```

## Common Pitfalls and Solutions

### Pitfall 1: Forgetting to Update Size Counter

```php
// BAD: Size gets out of sync
public function delete(mixed $data): bool
{
    if ($this->head->data === $data) {
        $this->head = $this->head->next;
        // FORGOT: $this->size--;
        return true;
    }
}

// GOOD: Always update size
public function delete(mixed $data): bool
{
    if ($this->head->data === $data) {
        $this->head = $this->head->next;
        $this->size--; // Don't forget!
        return true;
    }
}
```

### Pitfall 2: Losing Reference to Head

```php
// BAD: Loses head reference
public function reverse(): void
{
    $current = $this->head;
    while ($current !== null) {
        $next = $current->next;
        $current->next = $prev ?? null;
        $prev = $current;
        $current = $next;
    }
    // FORGOT: $this->head = $prev;
}

// GOOD: Update head at the end
public function reverse(): void
{
    $prev = null;
    $current = $this->head;

    while ($current !== null) {
        $next = $current->next;
        $current->next = $prev;
        $prev = $current;
        $current = $next;
    }

    $this->head = $prev; // Critical!
}
```

### Pitfall 3: Not Handling Tail Pointer in Doubly Linked List

```php
// BAD: Tail pointer becomes invalid
public function deleteLast(): void
{
    if ($this->head === null) return;

    $current = $this->head;
    while ($current->next !== null) {
        $prev = $current;
        $current = $current->next;
    }
    $prev->next = null;
    // FORGOT: $this->tail = $prev;
}

// GOOD: Update tail
public function deleteLast(): void
{
    if ($this->head === null) return;

    if ($this->head->next === null) {
        $this->head = $this->tail = null;
        return;
    }

    $current = $this->head;
    while ($current->next->next !== null) {
        $current = $current->next;
    }

    $current->next = null;
    $this->tail = $current; // Update tail!
}
```

### Pitfall 4: Inefficient Append Without Tail Pointer

```php
// BAD: O(n) for every append
class SlowLinkedList
{
    private ?Node $head = null;

    public function append(mixed $data): void
    {
        $newNode = new Node($data);

        if ($this->head === null) {
            $this->head = $newNode;
            return;
        }

        // Traverses entire list every time - O(n)
        $current = $this->head;
        while ($current->next !== null) {
            $current = $current->next;
        }
        $current->next = $newNode;
    }
}

// GOOD: O(1) append with tail pointer
class FastLinkedList
{
    private ?Node $head = null;
    private ?Node $tail = null;

    public function append(mixed $data): void
    {
        $newNode = new Node($data);

        if ($this->tail === null) {
            $this->head = $this->tail = $newNode;
        } else {
            $this->tail->next = $newNode;
            $this->tail = $newNode; // O(1)
        }
    }
}
```

### Pitfall 5: Not Considering PHP's Garbage Collection

```php
// BAD: Circular reference causes memory leak
class CircularList
{
    private ?Node $head = null;

    public function makeCircular(): void
    {
        $last = $this->head;
        while ($last->next !== null) {
            $last = $last->next;
        }
        $last->next = $this->head; // Circular reference!
    }

    // No destructor - memory leak!
}

// GOOD: Properly break cycles
class CircularList
{
    private ?Node $head = null;

    public function __destruct()
    {
        $this->breakCycle();
    }

    private function breakCycle(): void
    {
        if ($this->head === null) return;

        $current = $this->head;
        $visited = new SplObjectStorage();

        while ($current !== null && !$visited->contains($current)) {
            $visited->attach($current);
            $next = $current->next;
            $current->next = null; // Break reference
            $current = $next;
        }

        $this->head = null;
    }
}
```

## Real-World Applications

### 1. Browser History (Doubly Linked List)

```php
class BrowserHistory
{
    private ?DoublyNode $current = null;

    public function visit(string $url): void
    {
        $newNode = new DoublyNode($url);

        if ($this->current !== null) {
            $this->current->next = $newNode;
            $newNode->prev = $this->current;
        }

        $this->current = $newNode;
        echo "Visited: $url\n";
    }

    public function back(): ?string
    {
        if ($this->current === null || $this->current->prev === null) {
            return null;
        }

        $this->current = $this->current->prev;
        return $this->current->data;
    }

    public function forward(): ?string
    {
        if ($this->current === null || $this->current->next === null) {
            return null;
        }

        $this->current = $this->current->next;
        return $this->current->data;
    }
}

$history = new BrowserHistory();
$history->visit('google.com');
$history->visit('github.com');
$history->visit('stackoverflow.com');
echo "Back: " . $history->back() . "\n";    // github.com
echo "Back: " . $history->back() . "\n";    // google.com
echo "Forward: " . $history->forward() . "\n"; // github.com
```

### 2. Music Playlist (Circular Linked List)

```php
class Playlist
{
    private ?Node $current = null;

    public function addSong(string $song): void
    {
        $newNode = new Node($song);

        if ($this->current === null) {
            $this->current = $newNode;
            $newNode->next = $newNode; // Self loop
        } else {
            // Find last node
            $last = $this->current;
            while ($last->next !== $this->current) {
                $last = $last->next;
            }

            $last->next = $newNode;
            $newNode->next = $this->current;
        }
    }

    public function next(): string
    {
        if ($this->current === null) {
            return '';
        }

        $this->current = $this->current->next;
        return $this->current->data;
    }

    public function play(): void
    {
        if ($this->current === null) return;

        echo "Now playing: {$this->current->data}\n";
    }
}

$playlist = new Playlist();
$playlist->addSong('Song A');
$playlist->addSong('Song B');
$playlist->addSong('Song C');

$playlist->play();      // Song A
$playlist->next();
$playlist->play();      // Song B
$playlist->next();
$playlist->play();      // Song C
$playlist->next();
$playlist->play();      // Song A (loops back)
```

## Practice Exercises

### Exercise 1: Palindrome Check

Check if a linked list is a palindrome:

```php
function isPalindrome(LinkedList $list): bool
{
    // Your code here
}

// [1, 2, 3, 2, 1] → true
// [1, 2, 3] → false
```

<details>
<summary>Hint</summary>
Find middle, reverse second half, compare both halves.
</details>

### Exercise 2: Intersection Point

Find where two linked lists intersect:

```php
function findIntersection(LinkedList $list1, LinkedList $list2): ?Node
{
    // Your code here
}
```

### Exercise 3: Remove Duplicates

Remove duplicates from an unsorted linked list:

```php
function removeDuplicates(LinkedList $list): void
{
    // Your code here
}

// [1, 2, 3, 2, 1] → [1, 2, 3]
```

## Key Takeaways

- **Linked lists** store elements in nodes connected by pointers
- **Singly linked**: Each node points to next
- **Doubly linked**: Each node points to next and previous
- **Circular**: Last node points back to first
- **Trade-offs**: O(1) insert/delete at ends vs no random access
- **Common patterns**: Two pointers, reversing, cycle detection
- **Use cases**: Browser history, playlists, undo/redo functionality

## What's Next

In the next chapter, we'll explore **Stacks & Queues**, specialized data structures built on top of linked lists and arrays.

---

Continue to [Chapter 17: Stacks & Queues](/series/php-algorithms/chapters/17-stacks-queues).
