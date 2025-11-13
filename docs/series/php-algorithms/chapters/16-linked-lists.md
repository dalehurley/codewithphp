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
