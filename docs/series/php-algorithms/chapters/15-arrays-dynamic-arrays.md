---
title: "15: Arrays & Dynamic Arrays"
description: "Deep dive into arrays: how they work in memory, resizing strategies, and PHP's array implementation."
series: "php-algorithms"
chapter: 15
order: 15
difficulty: "Intermediate"
prerequisites:
  - "Understanding of basic PHP arrays"
  - "Familiarity with Big O notation"
  - "Completion of foundation chapters"
---

# Arrays & Dynamic Arrays

Arrays are the most fundamental data structure in programming. While we use them constantly, understanding how they work internally—especially dynamic arrays—is crucial for writing efficient code. In this chapter, we'll explore array internals, dynamic resizing, and PHP's unique array implementation.

## Static vs Dynamic Arrays

### Static Arrays (Fixed Size)

In languages like C, arrays have a fixed size:

```c
// C code - fixed size array
int numbers[5];  // Exactly 5 integers, can't grow
```

**Characteristics:**
- Fixed size allocated at creation
- Contiguous memory
- O(1) access by index
- Cannot grow or shrink

### Dynamic Arrays (Resizable)

Dynamic arrays can grow as needed:

```php
// PHP array - can grow dynamically
$numbers = [1, 2, 3];
$numbers[] = 4;  // Grows automatically!
$numbers[] = 5;
```

**Characteristics:**
- Can grow (and sometimes shrink)
- Still contiguous memory internally
- O(1) amortized append
- Automatic resizing

## How Arrays Work in Memory

### Memory Layout

Arrays store elements in contiguous memory:

```
Address:  1000  1004  1008  1012  1016
Value:    [10]  [20]  [30]  [40]  [50]
Index:     0     1     2     3     4
```

**Why contiguous?**
- O(1) access: `address = base + (index × element_size)`
- Cache-friendly: CPU can load multiple elements at once
- Predictable performance

### Array Access

```php
// How array access works internally:
// 1. Calculate address: base_address + (index × 4 bytes)
// 2. Read value at that address

$arr = [10, 20, 30, 40, 50];
$value = $arr[2];  // O(1) - direct memory access
```

## Building a Dynamic Array

Let's implement a dynamic array from scratch to understand resizing:

```php
class DynamicArray
{
    private array $data;
    private int $size;      // Number of elements
    private int $capacity;  // Array capacity

    public function __construct(int $initialCapacity = 10)
    {
        $this->capacity = $initialCapacity;
        $this->size = 0;
        $this->data = array_fill(0, $this->capacity, null);
    }

    // Get element at index - O(1)
    public function get(int $index): mixed
    {
        if ($index < 0 || $index >= $this->size) {
            throw new OutOfBoundsException("Index out of bounds");
        }

        return $this->data[$index];
    }

    // Set element at index - O(1)
    public function set(int $index, mixed $value): void
    {
        if ($index < 0 || $index >= $this->size) {
            throw new OutOfBoundsException("Index out of bounds");
        }

        $this->data[$index] = $value;
    }

    // Append element - O(1) amortized
    public function push(mixed $value): void
    {
        // Resize if needed
        if ($this->size === $this->capacity) {
            $this->resize();
        }

        $this->data[$this->size] = $value;
        $this->size++;
    }

    // Remove last element - O(1)
    public function pop(): mixed
    {
        if ($this->size === 0) {
            throw new UnderflowException("Array is empty");
        }

        $this->size--;
        $value = $this->data[$this->size];
        $this->data[$this->size] = null;

        // Shrink if too empty
        if ($this->size > 0 && $this->size === $this->capacity / 4) {
            $this->shrink();
        }

        return $value;
    }

    // Insert at index - O(n)
    public function insert(int $index, mixed $value): void
    {
        if ($index < 0 || $index > $this->size) {
            throw new OutOfBoundsException("Index out of bounds");
        }

        if ($this->size === $this->capacity) {
            $this->resize();
        }

        // Shift elements right
        for ($i = $this->size; $i > $index; $i--) {
            $this->data[$i] = $this->data[$i - 1];
        }

        $this->data[$index] = $value;
        $this->size++;
    }

    // Remove at index - O(n)
    public function remove(int $index): mixed
    {
        if ($index < 0 || $index >= $this->size) {
            throw new OutOfBoundsException("Index out of bounds");
        }

        $value = $this->data[$index];

        // Shift elements left
        for ($i = $index; $i < $this->size - 1; $i++) {
            $this->data[$i] = $this->data[$i + 1];
        }

        $this->size--;
        $this->data[$this->size] = null;

        if ($this->size > 0 && $this->size === $this->capacity / 4) {
            $this->shrink();
        }

        return $value;
    }

    // Double capacity - O(n)
    private function resize(): void
    {
        $this->capacity *= 2;
        $newData = array_fill(0, $this->capacity, null);

        // Copy old elements
        for ($i = 0; $i < $this->size; $i++) {
            $newData[$i] = $this->data[$i];
        }

        $this->data = $newData;
        echo "Resized to capacity: {$this->capacity}\n";
    }

    // Halve capacity - O(n)
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

    public function isEmpty(): bool
    {
        return $this->size === 0;
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

// Usage
$arr = new DynamicArray(4);
$arr->push(10);
$arr->push(20);
$arr->push(30);
$arr->push(40);
$arr->display(); // Size: 4, Capacity: 4

$arr->push(50);  // Triggers resize!
$arr->display(); // Size: 5, Capacity: 8
```

## Amortized Analysis of Push

Why is push O(1) amortized?

**Example:** Start with capacity 4, double each time

```
Operation | Cost  | Capacity | Reason
----------|-------|----------|------------------
push(1)   | 1     | 4        | Normal insert
push(2)   | 1     | 4        | Normal insert
push(3)   | 1     | 4        | Normal insert
push(4)   | 1     | 4        | Normal insert
push(5)   | 5     | 8        | Copy 4 + insert 1
push(6)   | 1     | 8        | Normal insert
push(7)   | 1     | 8        | Normal insert
push(8)   | 1     | 8        | Normal insert
push(9)   | 9     | 16       | Copy 8 + insert 1
```

**Total cost for n operations:**
- Regular inserts: n
- Resize copies: 1 + 2 + 4 + 8 + ... ≈ 2n

**Amortized cost:** (n + 2n) / n = 3 = O(1) per operation!

## Resizing Strategies

### Strategy 1: Double When Full (Most Common)

```php
private function resize(): void
{
    $this->capacity *= 2;  // Double capacity
    // Copy elements...
}
```

**Pros:**
- Simple
- O(1) amortized append
- Good space/time tradeoff

**Cons:**
- Can waste up to 50% space

### Strategy 2: Grow by Constant Amount

```php
private function resize(): void
{
    $this->capacity += 100;  // Add fixed amount
    // Copy elements...
}
```

**Pros:**
- Predictable memory usage

**Cons:**
- O(n) amortized append for large arrays

### Strategy 3: Golden Ratio Growth

```php
private function resize(): void
{
    $this->capacity = (int)($this->capacity * 1.5);  // 1.5x growth
    // Copy elements...
}
```

**Pros:**
- Better memory reuse than doubling
- Still O(1) amortized

## PHP's Array Implementation

PHP arrays are **ordered hash tables**, not traditional arrays!

### PHP Array Features

```php
// Integer keys (array-like)
$arr1 = [10, 20, 30];

// String keys (map-like)
$arr2 = ['name' => 'Alice', 'age' => 30];

// Mixed keys
$arr3 = [0 => 'first', 'key' => 'second', 1 => 'third'];

// Preserves insertion order
$arr4 = ['c' => 3, 'a' => 1, 'b' => 2];
foreach ($arr4 as $k => $v) {
    echo "$k => $v\n";  // Outputs: c, a, b (insertion order)
}
```

### How PHP Arrays Work

PHP arrays are hash tables with:
- Hash function for keys
- Collision handling (chaining)
- Order preservation (doubly linked list)

**Operations:**
- Access by key: O(1) average
- Append: O(1) average
- Insert at beginning: O(1)
- Remove by key: O(1) average

**Memory overhead:** Higher than traditional arrays due to hash table structure

## Array Operations Complexity

| Operation | Traditional Array | Dynamic Array | PHP Array |
|-----------|------------------|---------------|-----------|
| Access by index | O(1) | O(1) | O(1)* |
| Append | N/A | O(1) amortized | O(1) amortized |
| Prepend | N/A | O(n) | O(1) |
| Insert at i | N/A | O(n) | O(n) |
| Remove at i | N/A | O(n) | O(n) |
| Search | O(n) | O(n) | O(n) |
| Memory | Low | Medium | High |

*O(1) for integer keys, O(n) worst case for hash collisions

## Common Array Patterns

### 1. Two Pointers

```php
function twoPointers(array $arr): bool
{
    $left = 0;
    $right = count($arr) - 1;

    while ($left < $right) {
        // Process from both ends
        if ($arr[$left] + $arr[$right] === 10) {
            return true;
        }

        if ($arr[$left] + $arr[$right] < 10) {
            $left++;
        } else {
            $right--;
        }
    }

    return false;
}
```

### 2. Sliding Window

```php
function maxSumSubarray(array $arr, int $k): int
{
    $n = count($arr);
    if ($n < $k) return 0;

    // Compute first window
    $windowSum = array_sum(array_slice($arr, 0, $k));
    $maxSum = $windowSum;

    // Slide window
    for ($i = $k; $i < $n; $i++) {
        $windowSum = $windowSum - $arr[$i - $k] + $arr[$i];
        $maxSum = max($maxSum, $windowSum);
    }

    return $maxSum;
}
```

### 3. Prefix Sum

```php
class PrefixSum
{
    private array $prefix;

    public function __construct(array $arr)
    {
        $n = count($arr);
        $this->prefix = array_fill(0, $n + 1, 0);

        for ($i = 0; $i < $n; $i++) {
            $this->prefix[$i + 1] = $this->prefix[$i] + $arr[$i];
        }
    }

    // Get sum of range [left, right] in O(1)
    public function rangeSum(int $left, int $right): int
    {
        return $this->prefix[$right + 1] - $this->prefix[$left];
    }
}

$arr = [1, 2, 3, 4, 5];
$ps = new PrefixSum($arr);
echo $ps->rangeSum(1, 3); // Sum of [2,3,4] = 9
```

### 4. Kadane's Algorithm (Max Subarray)

```php
function maxSubarraySum(array $arr): int
{
    $maxSoFar = $arr[0];
    $maxEndingHere = $arr[0];

    for ($i = 1; $i < count($arr); $i++) {
        $maxEndingHere = max($arr[$i], $maxEndingHere + $arr[$i]);
        $maxSoFar = max($maxSoFar, $maxEndingHere);
    }

    return $maxSoFar;
}

$arr = [-2, 1, -3, 4, -1, 2, 1, -5, 4];
echo maxSubarraySum($arr); // 6 (subarray [4,-1,2,1])
```

## Multidimensional Arrays

### 2D Arrays (Matrices)

```php
// Create 2D array
function create2DArray(int $rows, int $cols, mixed $default = 0): array
{
    $matrix = [];
    for ($i = 0; $i < $rows; $i++) {
        $matrix[$i] = array_fill(0, $cols, $default);
    }
    return $matrix;
}

// Traverse 2D array
function traverse2D(array $matrix): void
{
    $rows = count($matrix);
    $cols = count($matrix[0]);

    for ($i = 0; $i < $rows; $i++) {
        for ($j = 0; $j < $cols; $j++) {
            echo $matrix[$i][$j] . " ";
        }
        echo "\n";
    }
}

// Transpose matrix
function transpose(array $matrix): array
{
    $rows = count($matrix);
    $cols = count($matrix[0]);
    $result = create2DArray($cols, $rows);

    for ($i = 0; $i < $rows; $i++) {
        for ($j = 0; $j < $cols; $j++) {
            $result[$j][$i] = $matrix[$i][$j];
        }
    }

    return $result;
}
```

### Jagged Arrays

```php
// Array of arrays with different lengths
$jagged = [
    [1, 2, 3],
    [4, 5],
    [6, 7, 8, 9]
];

foreach ($jagged as $row) {
    echo "Row length: " . count($row) . "\n";
}
```

## Real-World Applications

### 1. Circular Buffer (Ring Buffer)

```php
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
}

// Usage: audio buffer, network packets, etc.
$buffer = new CircularBuffer(5);
$buffer->enqueue('A');
$buffer->enqueue('B');
echo $buffer->dequeue(); // A
```

### 2. Sparse Array

```php
class SparseArray
{
    private array $data = [];
    private mixed $defaultValue;

    public function __construct(mixed $defaultValue = null)
    {
        $this->defaultValue = $defaultValue;
    }

    public function set(int $index, mixed $value): void
    {
        if ($value !== $this->defaultValue) {
            $this->data[$index] = $value;
        } else {
            unset($this->data[$index]);
        }
    }

    public function get(int $index): mixed
    {
        return $this->data[$index] ?? $this->defaultValue;
    }

    public function memoryUsage(): int
    {
        return count($this->data);
    }
}

// Efficient for large arrays with mostly default values
$sparse = new SparseArray(0);
$sparse->set(1000000, 42);  // Only stores non-zero value
```

### 3. Bit Array (Bit Vector)

```php
class BitArray
{
    private array $bits;
    private int $size;

    public function __construct(int $size)
    {
        $this->size = $size;
        $arraySize = (int)ceil($size / 32);
        $this->bits = array_fill(0, $arraySize, 0);
    }

    public function set(int $index): void
    {
        $arrayIndex = (int)($index / 32);
        $bitIndex = $index % 32;
        $this->bits[$arrayIndex] |= (1 << $bitIndex);
    }

    public function clear(int $index): void
    {
        $arrayIndex = (int)($index / 32);
        $bitIndex = $index % 32;
        $this->bits[$arrayIndex] &= ~(1 << $bitIndex);
    }

    public function get(int $index): bool
    {
        $arrayIndex = (int)($index / 32);
        $bitIndex = $index % 32;
        return ($this->bits[$arrayIndex] & (1 << $bitIndex)) !== 0;
    }
}

// Space efficient: 1 bit per boolean vs 8 bytes in PHP array
$bits = new BitArray(1000000);
$bits->set(500000);
echo $bits->get(500000) ? 'Set' : 'Not set';
```

## Practice Exercises

### Exercise 1: Rotate Array

Rotate array k positions to the right:

```php
function rotateArray(array &$arr, int $k): void
{
    // Your code here
}

$arr = [1, 2, 3, 4, 5];
rotateArray($arr, 2);
print_r($arr); // [4, 5, 1, 2, 3]
```

<details>
<summary>Solution</summary>

```php
function rotateArray(array &$arr, int $k): void
{
    $n = count($arr);
    $k = $k % $n;  // Handle k > n

    // Reverse entire array
    $arr = array_reverse($arr);

    // Reverse first k elements
    $temp1 = array_reverse(array_slice($arr, 0, $k));

    // Reverse remaining elements
    $temp2 = array_reverse(array_slice($arr, $k));

    $arr = array_merge($temp1, $temp2);
}
```
</details>

### Exercise 2: Product of Array Except Self

Calculate product of all elements except current:

```php
function productExceptSelf(array $nums): array
{
    // Cannot use division
    // Your code here
}

print_r(productExceptSelf([1, 2, 3, 4]));
// [24, 12, 8, 6]
```

### Exercise 3: Find Duplicates

Find all duplicates in O(n) time and O(1) space (array contains 1 to n):

```php
function findDuplicates(array $nums): array
{
    // Your code here
}
```

## Linked List Variants

While the chapter focuses on arrays, understanding linked lists helps appreciate array trade-offs:

### Singly Linked List

```php
class SinglyLinkedListNode
{
    public function __construct(
        public mixed $data,
        public ?SinglyLinkedListNode $next = null
    ) {}
}

class SinglyLinkedList
{
    private ?SinglyLinkedListNode $head = null;
    private int $size = 0;

    // Insert at beginning - O(1)
    public function prepend(mixed $data): void
    {
        $newNode = new SinglyLinkedListNode($data, $this->head);
        $this->head = $newNode;
        $this->size++;
    }

    // Insert at end - O(n) without tail pointer
    public function append(mixed $data): void
    {
        $newNode = new SinglyLinkedListNode($data);

        if ($this->head === null) {
            $this->head = $newNode;
        } else {
            $current = $this->head;
            while ($current->next !== null) {
                $current = $current->next;
            }
            $current->next = $newNode;
        }

        $this->size++;
    }

    // Search - O(n)
    public function search(mixed $data): ?SinglyLinkedListNode
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

    // Delete - O(n)
    public function delete(mixed $data): bool
    {
        if ($this->head === null) {
            return false;
        }

        if ($this->head->data === $data) {
            $this->head = $this->head->next;
            $this->size--;
            return true;
        }

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

    public function size(): int
    {
        return $this->size;
    }
}
```

### Doubly Linked List

```php
class DoublyLinkedListNode
{
    public function __construct(
        public mixed $data,
        public ?DoublyLinkedListNode $next = null,
        public ?DoublyLinkedListNode $prev = null
    ) {}
}

class DoublyLinkedList
{
    private ?DoublyLinkedListNode $head = null;
    private ?DoublyLinkedListNode $tail = null;
    private int $size = 0;

    // Insert at beginning - O(1)
    public function prepend(mixed $data): void
    {
        $newNode = new DoublyLinkedListNode($data);

        if ($this->head === null) {
            $this->head = $this->tail = $newNode;
        } else {
            $newNode->next = $this->head;
            $this->head->prev = $newNode;
            $this->head = $newNode;
        }

        $this->size++;
    }

    // Insert at end - O(1) with tail pointer
    public function append(mixed $data): void
    {
        $newNode = new DoublyLinkedListNode($data);

        if ($this->tail === null) {
            $this->head = $this->tail = $newNode;
        } else {
            $newNode->prev = $this->tail;
            $this->tail->next = $newNode;
            $this->tail = $newNode;
        }

        $this->size++;
    }

    // Insert after specific node - O(1)
    public function insertAfter(DoublyLinkedListNode $node, mixed $data): void
    {
        $newNode = new DoublyLinkedListNode($data);

        $newNode->next = $node->next;
        $newNode->prev = $node;

        if ($node->next !== null) {
            $node->next->prev = $newNode;
        } else {
            $this->tail = $newNode;
        }

        $node->next = $newNode;
        $this->size++;
    }

    // Delete node - O(1) if node reference known
    public function deleteNode(DoublyLinkedListNode $node): void
    {
        if ($node->prev !== null) {
            $node->prev->next = $node->next;
        } else {
            $this->head = $node->next;
        }

        if ($node->next !== null) {
            $node->next->prev = $node->prev;
        } else {
            $this->tail = $node->prev;
        }

        $this->size--;
    }

    // Traverse backward
    public function toArrayReverse(): array
    {
        $result = [];
        $current = $this->tail;

        while ($current !== null) {
            $result[] = $current->data;
            $current = $current->prev;
        }

        return $result;
    }
}
```

### Circular Linked List

```php
class CircularLinkedListNode
{
    public function __construct(
        public mixed $data,
        public ?CircularLinkedListNode $next = null
    ) {}
}

class CircularLinkedList
{
    private ?CircularLinkedListNode $head = null;
    private int $size = 0;

    public function append(mixed $data): void
    {
        $newNode = new CircularLinkedListNode($data);

        if ($this->head === null) {
            $this->head = $newNode;
            $newNode->next = $newNode; // Points to itself
        } else {
            // Find last node (the one pointing to head)
            $current = $this->head;
            while ($current->next !== $this->head) {
                $current = $current->next;
            }

            $current->next = $newNode;
            $newNode->next = $this->head;
        }

        $this->size++;
    }

    public function traverse(int $rounds = 1): array
    {
        if ($this->head === null) {
            return [];
        }

        $result = [];
        $current = $this->head;
        $count = 0;
        $maxItems = $this->size * $rounds;

        do {
            $result[] = $current->data;
            $current = $current->next;
            $count++;
        } while ($current !== $this->head && $count < $maxItems);

        return $result;
    }

    // Josephus problem solver
    public function josephus(int $k): mixed
    {
        if ($this->head === null) {
            return null;
        }

        $current = $this->head;

        while ($current->next !== $current) {
            // Skip k-1 nodes
            for ($i = 0; $i < $k - 1; $i++) {
                $current = $current->next;
            }

            // Remove next node
            $current->next = $current->next->next;
        }

        return $current->data;
    }
}
```

## Array vs Linked List Performance Comparison

```php
class DataStructureComparison
{
    public function compareOperations(): void
    {
        $sizes = [1000, 10000, 50000];

        echo "=== Array vs Linked List Performance ===\n\n";

        foreach ($sizes as $size) {
            echo "Size: " . number_format($size) . "\n";
            echo str_repeat('-', 60) . "\n";

            // Setup
            $array = range(1, $size);
            $linkedList = new DoublyLinkedList();
            for ($i = 1; $i <= $size; $i++) {
                $linkedList->append($i);
            }

            // Test: Prepend
            $start = microtime(true);
            for ($i = 0; $i < 1000; $i++) {
                array_unshift($array, $i);
                array_shift($array); // Remove to maintain size
            }
            $arrayPrepend = microtime(true) - $start;

            $start = microtime(true);
            for ($i = 0; $i < 1000; $i++) {
                $linkedList->prepend($i);
                // Would need to remove first for fair comparison
            }
            $listPrepend = microtime(true) - $start;

            printf("Prepend 1000 items:\n");
            printf("  Array:       %.6f sec\n", $arrayPrepend);
            printf("  Linked List: %.6f sec (%.2fx faster)\n\n",
                $listPrepend, $arrayPrepend / $listPrepend);

            // Test: Random Access
            $indices = array_rand(range(0, $size - 1), min(1000, $size));

            $start = microtime(true);
            foreach ($indices as $idx) {
                $val = $array[$idx];
            }
            $arrayAccess = microtime(true) - $start;

            printf("Random access 1000 times:\n");
            printf("  Array:       %.6f sec\n", $arrayAccess);
            printf("  Linked List: O(n) - impractical\n\n");

            // Test: Iteration
            $start = microtime(true);
            foreach ($array as $val) {
                // Iterate
            }
            $arrayIter = microtime(true) - $start;

            printf("Full iteration:\n");
            printf("  Array:       %.6f sec\n", $arrayIter);
            printf("  Both: Similar O(n) performance\n\n");

            echo "\n";
        }
    }

    public function memoryComparison(): void
    {
        echo "=== Memory Usage Comparison ===\n\n";

        $size = 10000;

        // Array
        $memBefore = memory_get_usage();
        $array = range(1, $size);
        $arrayMem = memory_get_usage() - $memBefore;

        // Singly Linked List
        $memBefore = memory_get_usage();
        $sll = new SinglyLinkedList();
        for ($i = 1; $i <= $size; $i++) {
            $sll->append($i);
        }
        $sllMem = memory_get_usage() - $memBefore;

        // Doubly Linked List
        $memBefore = memory_get_usage();
        $dll = new DoublyLinkedList();
        for ($i = 1; $i <= $size; $i++) {
            $dll->append($i);
        }
        $dllMem = memory_get_usage() - $memBefore;

        printf("For %s elements:\n", number_format($size));
        printf("Array:               %s\n", $this->formatBytes($arrayMem));
        printf("Singly Linked List:  %s (%.2fx more than array)\n",
            $this->formatBytes($sllMem), $sllMem / $arrayMem);
        printf("Doubly Linked List:  %s (%.2fx more than array)\n",
            $this->formatBytes($dllMem), $dllMem / $arrayMem);
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}

$comparison = new DataStructureComparison();
$comparison->compareOperations();
$comparison->memoryComparison();
```

## Array Operation Performance Tips

### Efficient Array Manipulation

```php
class ArrayOptimizationTips
{
    /**
     * TIP 1: Preallocate when size is known
     */
    public function preallocateExample(): void
    {
        $size = 100000;

        // Slow: Growing array
        $start = microtime(true);
        $arr1 = [];
        for ($i = 0; $i < $size; $i++) {
            $arr1[] = $i;
        }
        $slowTime = microtime(true) - $start;

        // Faster: Preallocated (using range)
        $start = microtime(true);
        $arr2 = range(0, $size - 1);
        $fastTime = microtime(true) - $start;

        printf("Preallocate speedup: %.2fx faster\n", $slowTime / $fastTime);
    }

    /**
     * TIP 2: Use array_push for multiple items
     */
    public function bulkInsertExample(): void
    {
        $arr = range(1, 10000);

        // Slower: Multiple individual pushes
        $start = microtime(true);
        $test1 = $arr;
        for ($i = 0; $i < 1000; $i++) {
            $test1[] = $i;
        }
        $slow = microtime(true) - $start;

        // Faster: Bulk push
        $start = microtime(true);
        $test2 = $arr;
        array_push($test2, ...range(0, 999));
        $fast = microtime(true) - $start;

        printf("Bulk push speedup: %.2fx faster\n", $slow / $fast);
    }

    /**
     * TIP 3: Avoid array_shift/unshift for large arrays
     */
    public function shiftVsArraySlice(): void
    {
        $size = 50000;
        $arr = range(1, $size);

        // Slow: array_shift (O(n))
        $start = microtime(true);
        $test1 = $arr;
        for ($i = 0; $i < 100; $i++) {
            array_shift($test1);
        }
        $shiftTime = microtime(true) - $start;

        // Faster: array_slice
        $start = microtime(true);
        $test2 = $arr;
        $test2 = array_slice($test2, 100);
        $sliceTime = microtime(true) - $start;

        printf("array_shift: %.6f sec\n", $shiftTime);
        printf("array_slice: %.6f sec (%.2fx faster)\n",
            $sliceTime, $shiftTime / $sliceTime);
    }

    /**
     * TIP 4: Use isset() instead of array_key_exists() when possible
     */
    public function issetVsArrayKeyExists(): void
    {
        $arr = array_fill_keys(range(1, 10000), 'value');

        $start = microtime(true);
        for ($i = 0; $i < 10000; $i++) {
            $exists = array_key_exists($i, $arr);
        }
        $akeTime = microtime(true) - $start;

        $start = microtime(true);
        for ($i = 0; $i < 10000; $i++) {
            $exists = isset($arr[$i]);
        }
        $issetTime = microtime(true) - $start;

        printf("array_key_exists: %.6f sec\n", $akeTime);
        printf("isset:            %.6f sec (%.2fx faster)\n",
            $issetTime, $akeTime / $issetTime);
    }

    /**
     * TIP 5: Use array_map/array_filter instead of loops
     */
    public function functionalVsLoop(): void
    {
        $arr = range(1, 100000);

        // Traditional loop
        $start = microtime(true);
        $result1 = [];
        foreach ($arr as $val) {
            $result1[] = $val * 2;
        }
        $loopTime = microtime(true) - $start;

        // array_map
        $start = microtime(true);
        $result2 = array_map(fn($x) => $x * 2, $arr);
        $mapTime = microtime(true) - $start;

        printf("Loop:       %.6f sec\n", $loopTime);
        printf("array_map:  %.6f sec (%.2fx faster)\n",
            $mapTime, $loopTime / $mapTime);
    }
}

$tips = new ArrayOptimizationTips();
$tips->preallocateExample();
$tips->bulkInsertExample();
$tips->shiftVsArraySlice();
$tips->issetVsArrayKeyExists();
$tips->functionalVsLoop();
```

## Advanced PHP SPL Usage

### Using SPL Data Structures

```php
class SPLDataStructureExamples
{
    /**
     * SplDoublyLinkedList - More efficient than custom implementation
     */
    public function splDoublyLinkedList(): void
    {
        $list = new SplDoublyLinkedList();

        // Push operations
        $list->push('a');
        $list->push('b');
        $list->push('c');

        // Unshift (prepend)
        $list->unshift('z');

        // Access by index
        echo $list[0]; // z

        // Iterate
        foreach ($list as $item) {
            echo "$item ";
        }

        // Use as stack
        $list->setIteratorMode(SplDoublyLinkedList::IT_MODE_LIFO);

        // Use as queue
        $list->setIteratorMode(SplDoublyLinkedList::IT_MODE_FIFO);
    }

    /**
     * SplQueue - FIFO queue
     */
    public function splQueue(): void
    {
        $queue = new SplQueue();

        $queue->enqueue('first');
        $queue->enqueue('second');
        $queue->enqueue('third');

        echo $queue->dequeue(); // first
        echo $queue->dequeue(); // second
    }

    /**
     * SplStack - LIFO stack
     */
    public function splStack(): void
    {
        $stack = new SplStack();

        $stack->push('a');
        $stack->push('b');
        $stack->push('c');

        echo $stack->pop(); // c
        echo $stack->pop(); // b
    }

    /**
     * SplPriorityQueue - Priority queue
     */
    public function splPriorityQueue(): void
    {
        $pq = new SplPriorityQueue();

        $pq->insert('low priority task', 1);
        $pq->insert('high priority task', 10);
        $pq->insert('medium priority task', 5);

        // Extracts in priority order
        while (!$pq->isEmpty()) {
            echo $pq->extract() . "\n";
        }
        // Output:
        // high priority task
        // medium priority task
        // low priority task
    }

    /**
     * Performance comparison: SPL vs Custom
     */
    public function compareSPLvsCustom(): void
    {
        $size = 100000;

        // Custom doubly linked list
        $start = microtime(true);
        $custom = new DoublyLinkedList();
        for ($i = 0; $i < $size; $i++) {
            $custom->append($i);
        }
        $customTime = microtime(true) - $start;

        // SPL doubly linked list
        $start = microtime(true);
        $spl = new SplDoublyLinkedList();
        for ($i = 0; $i < $size; $i++) {
            $spl->push($i);
        }
        $splTime = microtime(true) - $start;

        printf("Custom implementation: %.6f sec\n", $customTime);
        printf("SPL implementation:    %.6f sec (%.2fx faster)\n",
            $splTime, $customTime / $splTime);
    }
}
```

## When to Use Each Data Structure

| Operation | Array | Singly Linked List | Doubly Linked List | PHP Array (Hash Table) |
|-----------|-------|--------------------|--------------------|------------------------|
| Access by index | O(1) | O(n) | O(n) | O(1) |
| Prepend | O(n) | O(1) | O(1) | O(1) |
| Append | O(1)* | O(n) or O(1)** | O(1)*** | O(1) |
| Insert middle | O(n) | O(n) | O(1)**** | O(n) |
| Delete | O(n) | O(n) | O(1)**** | O(1) |
| Search | O(n) | O(n) | O(n) | O(1) avg |
| Memory | Low | Medium | High | Very High |

* Amortized
** O(n) without tail pointer, O(1) with tail pointer
*** With tail pointer
**** If node reference is known

**Use Arrays when:**
- Random access is frequent
- Memory is limited
- Size is relatively stable
- Cache performance matters

**Use Linked Lists when:**
- Frequent insertions/deletions at beginning
- Size changes dramatically
- Don't need random access
- Implementing queues/stacks

**Use PHP Arrays when:**
- Need key-value pairs
- Don't care about memory overhead
- Want built-in functions
- Most general-purpose use cases

## Key Takeaways

- **Arrays** provide O(1) random access via contiguous memory
- **Dynamic arrays** grow automatically with O(1) amortized append
- **Resizing** typically doubles capacity for O(1) amortized performance
- **PHP arrays** are hash tables, not traditional arrays
- **Common patterns**: two pointers, sliding window, prefix sum
- **Memory layout** affects cache performance
- **Linked lists** excel at insertions/deletions but poor at random access
- **SPL data structures** are optimized C implementations
- Understanding internals helps choose the right data structure
- **Trade-offs**: memory vs speed, access patterns matter

## What's Next

In the next chapter, we'll explore **Linked Lists**, learning when pointer-based structures are better than arrays.

---

Continue to [Chapter 16: Linked Lists](/series/php-algorithms/chapters/16-linked-lists).
