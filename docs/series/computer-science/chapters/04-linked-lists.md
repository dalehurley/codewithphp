---
title: "04: Linked Lists"
description: "Build singly and doubly linked lists. Understand pointer-based data structures, node traversal, insertion and deletion operations, and when linked lists outperform arrays."
series: "computer-science"
chapter: 4
order: 4
difficulty: "Intermediate"
prerequisites: ["Arrays", "Stacks and Queues", "Object-oriented programming"]
---

# Chapter 04: Linked Lists

## Introduction

Unlike arrays that store elements in contiguous memory, **linked lists** store elements in nodes scattered throughout memory, connected by references (pointers). This structure makes certain operations more efficient while trading off others.

In this chapter, you'll learn:

- How linked lists work
- Singly vs. doubly linked lists
- Implementation in PHP
- Time complexity analysis
- When to use linked lists vs. arrays

## What is a Linked List?

A **linked list** is a sequence of nodes where each node contains:
1. **Data**: The value stored
2. **Next**: A reference to the next node

```
[10|●]→[20|●]→[30|●]→[40|null]
 head                    tail
```

### Advantages Over Arrays

- **Dynamic size**: No need to pre-allocate space
- **Efficient insertion/deletion**: O(1) when you have the node reference
- **No wasted space**: Grows and shrinks as needed

### Disadvantages Compared to Arrays

- **No random access**: Must traverse from the head — O(n)
- **Extra memory**: Each node needs a pointer
- **Cache inefficiency**: Nodes not contiguous in memory

## Singly Linked List Implementation

```php
<?php

class Node {
    public function __construct(
        public mixed $data,
        public ?Node $next = null
    ) {}
}

class SinglyLinkedList {
    private ?Node $head = null;
    private int $size = 0;

    // Insert at the beginning - O(1)
    public function prepend(mixed $data): void {
        $newNode = new Node($data, $this->head);
        $this->head = $newNode;
        $this->size++;
    }

    // Insert at the end - O(n)
    public function append(mixed $data): void {
        $newNode = new Node($data);

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

    // Insert at specific position - O(n)
    public function insertAt(int $index, mixed $data): void {
        if ($index < 0 || $index > $this->size) {
            throw new OutOfBoundsException("Index out of bounds");
        }

        if ($index === 0) {
            $this->prepend($data);
            return;
        }

        $newNode = new Node($data);
        $current = $this->head;

        for ($i = 0; $i < $index - 1; $i++) {
            $current = $current->next;
        }

        $newNode->next = $current->next;
        $current->next = $newNode;
        $this->size++;
    }

    // Delete from beginning - O(1)
    public function deleteFirst(): mixed {
        if ($this->head === null) {
            throw new UnderflowException("List is empty");
        }

        $data = $this->head->data;
        $this->head = $this->head->next;
        $this->size--;

        return $data;
    }

    // Delete specific value - O(n)
    public function delete(mixed $value): bool {
        if ($this->head === null) {
            return false;
        }

        // If head node has the value
        if ($this->head->data === $value) {
            $this->head = $this->head->next;
            $this->size--;
            return true;
        }

        $current = $this->head;
        while ($current->next !== null) {
            if ($current->next->data === $value) {
                $current->next = $current->next->next;
                $this->size--;
                return true;
            }
            $current = $current->next;
        }

        return false;
    }

    // Search - O(n)
    public function search(mixed $value): bool {
        $current = $this->head;

        while ($current !== null) {
            if ($current->data === $value) {
                return true;
            }
            $current = $current->next;
        }

        return false;
    }

    // Get element at index - O(n)
    public function get(int $index): mixed {
        if ($index < 0 || $index >= $this->size) {
            throw new OutOfBoundsException("Index out of bounds");
        }

        $current = $this->head;
        for ($i = 0; $i < $index; $i++) {
            $current = $current->next;
        }

        return $current->data;
    }

    // Reverse the list - O(n)
    public function reverse(): void {
        $prev = null;
        $current = $this->head;

        while ($current !== null) {
            $next = $current->next;
            $current->next = $prev;
            $prev = $current;
            $current = $next;
        }

        $this->head = $prev;
    }

    public function size(): int {
        return $this->size;
    }

    public function isEmpty(): bool {
        return $this->head === null;
    }

    // Convert to array for display
    public function toArray(): array {
        $result = [];
        $current = $this->head;

        while ($current !== null) {
            $result[] = $current->data;
            $current = $current->next;
        }

        return $result;
    }

    public function display(): void {
        echo implode(" -> ", $this->toArray()) . "\n";
    }
}

// Usage
$list = new SinglyLinkedList();
$list->append(10);
$list->append(20);
$list->append(30);
$list->prepend(5);
$list->display(); // 5 -> 10 -> 20 -> 30

$list->insertAt(2, 15);
$list->display(); // 5 -> 10 -> 15 -> 20 -> 30

$list->delete(15);
$list->display(); // 5 -> 10 -> 20 -> 30

$list->reverse();
$list->display(); // 30 -> 20 -> 10 -> 5
```

## Doubly Linked List

A **doubly linked list** has pointers to both the next and previous nodes:

```
[null|10|●]⇄[●|20|●]⇄[●|30|●]⇄[●|40|null]
 head                             tail
```

### Advantages Over Singly Linked Lists

- Can traverse backwards
- Easier deletion (don't need to track previous node)
- More efficient for certain operations

### Implementation

```php
<?php

class DoublyNode {
    public function __construct(
        public mixed $data,
        public ?DoublyNode $next = null,
        public ?DoublyNode $prev = null
    ) {}
}

class DoublyLinkedList {
    private ?DoublyNode $head = null;
    private ?DoublyNode $tail = null;
    private int $size = 0;

    // Insert at the beginning - O(1)
    public function prepend(mixed $data): void {
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

    // Insert at the end - O(1) with tail pointer!
    public function append(mixed $data): void {
        $newNode = new DoublyNode($data);

        if ($this->tail === null) {
            $this->head = $this->tail = $newNode;
        } else {
            $newNode->prev = $this->tail;
            $this->tail->next = $newNode;
            $this->tail = $newNode;
        }

        $this->size++;
    }

    // Delete node - O(1) if you have the node reference
    public function deleteNode(DoublyNode $node): void {
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

    // Delete by value - O(n)
    public function delete(mixed $value): bool {
        $current = $this->head;

        while ($current !== null) {
            if ($current->data === $value) {
                $this->deleteNode($current);
                return true;
            }
            $current = $current->next;
        }

        return false;
    }

    // Traverse forward
    public function toArray(): array {
        $result = [];
        $current = $this->head;

        while ($current !== null) {
            $result[] = $current->data;
            $current = $current->next;
        }

        return $result;
    }

    // Traverse backward
    public function toArrayReverse(): array {
        $result = [];
        $current = $this->tail;

        while ($current !== null) {
            $result[] = $current->data;
            $current = $current->prev;
        }

        return $result;
    }

    public function size(): int {
        return $this->size;
    }

    public function display(): void {
        echo implode(" ⇄ ", $this->toArray()) . "\n";
    }
}

// Usage
$list = new DoublyLinkedList();
$list->append(10);
$list->append(20);
$list->append(30);
$list->prepend(5);
$list->display(); // 5 ⇄ 10 ⇄ 20 ⇄ 30

print_r($list->toArrayReverse()); // [30, 20, 10, 5]
```

## Common Linked List Problems

### 1. Detect a Cycle (Floyd's Cycle Detection)

```php
<?php

function hasCycle(Node $head): bool {
    if ($head === null) return false;

    $slow = $head;
    $fast = $head;

    while ($fast !== null && $fast->next !== null) {
        $slow = $slow->next;
        $fast = $fast->next->next;

        if ($slow === $fast) {
            return true; // Cycle detected
        }
    }

    return false;
}
```

**Time Complexity**: O(n)
**Space Complexity**: O(1)

### 2. Find Middle Element

```php
<?php

function findMiddle(Node $head): ?Node {
    if ($head === null) return null;

    $slow = $head;
    $fast = $head;

    while ($fast !== null && $fast->next !== null) {
        $slow = $slow->next;
        $fast = $fast->next->next;
    }

    return $slow; // Middle node
}
```

### 3. Merge Two Sorted Lists

```php
<?php

function mergeSortedLists(Node $l1, Node $l2): ?Node {
    $dummy = new Node(0);
    $current = $dummy;

    while ($l1 !== null && $l2 !== null) {
        if ($l1->data <= $l2->data) {
            $current->next = $l1;
            $l1 = $l1->next;
        } else {
            $current->next = $l2;
            $l2 = $l2->next;
        }
        $current = $current->next;
    }

    // Attach remaining nodes
    $current->next = $l1 ?? $l2;

    return $dummy->next;
}
```

### 4. Remove Nth Node From End

```php
<?php

function removeNthFromEnd(Node $head, int $n): ?Node {
    $dummy = new Node(0);
    $dummy->next = $head;

    $first = $dummy;
    $second = $dummy;

    // Move first n+1 steps ahead
    for ($i = 0; $i <= $n; $i++) {
        $first = $first->next;
    }

    // Move both until first reaches end
    while ($first !== null) {
        $first = $first->next;
        $second = $second->next;
    }

    // Remove the node
    $second->next = $second->next->next;

    return $dummy->next;
}
```

## Circular Linked List

A **circular linked list** has its tail pointing back to the head:

```
[10|●]→[20|●]→[30|●]→[40|●]
 ↑                      ↓
 └──────────────────────┘
```

Used in: Round-robin scheduling, circular buffers

## Linked List vs. Array

| Operation | Array | Linked List |
|-----------|-------|-------------|
| Random access | O(1) | O(n) |
| Insert at beginning | O(n) | O(1) |
| Insert at end | O(1) | O(1) with tail, O(n) without |
| Insert in middle | O(n) | O(1) if node known, O(n) to find |
| Delete at beginning | O(n) | O(1) |
| Delete at end | O(1) | O(n) for singly, O(1) for doubly |
| Delete in middle | O(n) | O(1) if node known, O(n) to find |
| Search | O(n) or O(log n) if sorted | O(n) |
| Memory | Contiguous, cache-friendly | Scattered, extra pointer overhead |

## When to Use Linked Lists

**Use linked lists when**:
- Frequent insertions/deletions at the beginning
- You don't know the size in advance
- You need efficient insertion/deletion in the middle (with node references)
- Implementing stacks, queues, or graphs

**Avoid linked lists when**:
- Random access is frequent
- Memory overhead is a concern
- Cache performance matters
- You need binary search

## Key Takeaways

- Linked lists use **nodes with pointers** instead of contiguous memory
- **Insertion/deletion at known positions** is O(1)
- **No random access**: Must traverse from head
- **Doubly linked lists** can traverse backwards and have faster deletion
- **Singly linked lists** use less memory
- Great for implementing dynamic data structures

## Exercises

1. **Implement a method** to check if a linked list is a palindrome.

2. **Reverse every k nodes**: Given `1->2->3->4->5` and k=2, return `2->1->4->3->5`.

3. **Intersection of two linked lists**: Find the node where two lists intersect.

4. **Flatten a multilevel doubly linked list**: Each node can have a child list.

5. **LRU Cache**: Implement an LRU cache using a doubly linked list and hash map.

## What's Next?

We've covered linear data structures (arrays, lists, stacks, queues). Now we'll explore **hierarchical data structures** starting with Trees and Binary Search Trees in Chapter 05.

---

**Further Reading**:
- [Linked List (Wikipedia)](https://en.wikipedia.org/wiki/Linked_list)
- [PHP SplDoublyLinkedList](https://www.php.net/manual/en/class.spldoublylinkedlist.php)
- [Linked List Problems (LeetCode)](https://leetcode.com/tag/linked-list/)
